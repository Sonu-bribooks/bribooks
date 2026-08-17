<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('courierwebhook');

class CourierWebhook extends CI_Controller {
	private $_allowed_order_update = [];

	public function __construct() {
		parent::__construct();

		$this->_allowed_order_update = [
			ORDER_STATUS['shipped'],
			ORDER_STATUS['out_for_delivery'],
			ORDER_STATUS['delivered'],
			ORDER_STATUS['undelivered'],
			ORDER_STATUS['returned'],
		];

		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('order/OrderHistory_model', 'order_history_model');
		$this->load->model('order/Payment_model', 'payment_model');
		$this->load->model('order/Coupon_model', 'coupon_model');

		$this->load->model('common/WebhookData_model', 'webhook_data_model');
		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('shipping/Shipment_model', 'shipment_model');

		$this->load->model('school/SchoolOrder_model', 'school_order_model');
		$this->load->model('medallion/MedallionOrder_model', 'medallion_order_model');
	}

	use
		Shiprocket,
		Aramex,
		Dtdc,
		Bluedart,
		BeingShips,
		Delhivery,
		Gokwik
	;

	private function _updateOrder($data = [], $order_info = [], $vendor = 'shiprocket') {
		if (!$this->webhook_data_model->get($data['order_id'], $vendor)) {
			$this->webhook_data_model->add([
				'order_code'	=> $data['order_id'],
				'vendor'		=> $vendor,
				'description'   => json_encode($data)
			]);
		} else {
			$this->webhook_data_model->edit($data['order_id'], $vendor, [
				'description'   => json_encode($data)
			]);
		}

		$shipment_info = json_decode($order_info['shipping_tracking_info'], true);
		
		if (!empty($data['scans'])) {
			foreach ($data['scans'] ?? [] as $value) {
				if (in_array($value['sr-status'], $this->{$vendor . '_scan_status'})) {
					$shipment_info['awb_code'] 		= $data['awb'];
					$shipment_info['status'] 		= $data['shipment_status'] ?? $shipment_info['status'];
					$shipment_info['courier_name']	= $data['courier_name'] ?? $shipment_info['courier_name'];

					$this->order_model->edit($order_info['id'], [
						'shipping_tracking_info' => json_encode($shipment_info)
					]);
				}
			}
		}

		if (!empty($data['awb']) && empty($shipment_info['awb_code']))  {
			$shipment_info['awb_code'] = $data['awb'];

			$this->order_model->edit($order_info['id'], [
				'shipping_tracking_info' => json_encode($shipment_info)
			]);
		}

		if (
			in_array($data['current_status_id'], $this->{$vendor . '_allowed_history_status'}) &&
			!empty($data['current_status'])
		) {
			if ($this->order_history_model->get_all([
				'order_id'		=> $order_info['id'],
				'description'	=> $data['current_status'],
			])['total'] == 0) {
				$this->order_history_model->add([
					'order_id' 		=> $order_info['id'],
					'description' 	=> ucwords(strtolower($data['current_status'])),
					'status' 		=> (int)$order_info['status'],
				]);
			}
		}

		if (in_array($data['order_current_status_id'], $this->_allowed_order_update)) {
			$this->order_model->edit($order_info['id'], [
				'status' => (int)$data['order_current_status_id'],
			]);

			if ($this->order_history_model->get_all([
				'order_id'		=> $order_info['id'],
				'status' 		=> (int)$data['order_current_status_id'],
				'limit'			=> 1,
				'start'			=> 0,
			])['total'] == 0) {
				$this->order_history_model->add([
					'order_id' 		=> $order_info['id'],
					'description' 	=> _order_history($data['order_current_status_id']),
					'status' 		=> $data['order_current_status_id']
				]);
			}
		}
		
		if ($data['order_current_status_id'] == ORDER_STATUS['shipped']) {
			CI_Events::trigger('order_shipped', [
				'order_id'	=> $order_info['id']
			]);
		}

		if ($data['order_current_status_id'] == ORDER_STATUS['undelivered']) {
			CI_Events::trigger('order_undelivered', [
				'order_id'	=> $order_info['id']
			]);
		}

		if ($data['order_current_status_id'] == ORDER_STATUS['delivered']) {
			$this->order_model->edit($order_info['id'], [
				'status' 			=> ORDER_STATUS['delivered'],
				'date_completed'	=> date('Y-m-d H:i:s')
			]);

			CI_Events::trigger('order_delivered', [
				'order_id'	=> $order_info['id']
			]);

			$this->load->library('Royalty_lib', 'royalty_lib');
			$this->royalty_lib->generateCredit($order_info['id']);
		}

		if ($data['order_current_status_id'] == ORDER_STATUS['out_for_delivery']) {
			CI_Events::trigger('order_out_for_delivery', [
				'order_id'	=> $order_info['id']
			]);
		}
	}

	private function _updateMedallionOrder($data = [], $vendor = 'shiprocket') {
		$this->load->model('medallion/MedallionOrder_model', 'medallion_order_model');
		$this->load->model('medallion/MedallionOrderHistory_model', 'medallion_order_history_model');

		if (!empty($order_info = $this->medallion_order_model->getByCode($data['order_id']))) {
			if (!$this->webhook_data_model->get($data['order_id'], $vendor)) {
				$this->webhook_data_model->add([
					'order_code'	=> $data['order_id'],
					'vendor'		=> $vendor,
					'description'   => json_encode($data)
				]);
			} else {
				$this->webhook_data_model->edit($data['order_id'], $vendor, [
					'description'   => json_encode($data)
				]);
			}

			$shipment_info = json_decode($order_info['shipping_tracking_info'], true);

			if (!empty($data['scans'])) {
				foreach ($data['scans'] ?? [] as $value) {
					if (in_array($value['sr-status'], $this->{$vendor . '_scan_status'})) {
						$shipment_info['awb_code'] 		= $data['awb'];
						$shipment_info['status'] 		= $data['shipment_status'] ?? $shipment_info['status'];
						$shipment_info['courier_name'] 	= $data['courier_name'] ?? $shipment_info['courier_name'];

						$this->medallion_order_model->edit($order_info['id'], [
							'shipping_tracking_info' => json_encode($shipment_info)
						]);
					}
				}
			}

			if (!empty($data['awb']) && empty($shipment_info['awb_code']))  {
				$shipment_info['awb_code'] = $data['awb'];

				$this->medallion_order_model->edit($order_info['id'], [
					'shipping_tracking_info' => json_encode($shipment_info)
				]);
			}

			if (in_array($data['current_status_id'], $this->{$vendor . '_allowed_history_status'})) {
				if ($this->medallion_order_history_model->get_all([
					'medallion_order_id'=> $order_info['id'],
					'description'		=> $data['current_status'],
				])['total'] == 0) {
					$this->medallion_order_history_model->add([
						'medallion_order_id'=> $order_info['id'],
						'description' 		=> ucwords(strtolower($data['current_status'])),
						'status' 			=> (int)$order_info['status'],
					]);
				}

				if ($data['order_current_status_id'] == ORDER_STATUS['returned']) {
					$this->medallion_order_model->edit($order_info['id'], [
						'status' => ORDER_STATUS['returned'],
					]);
				}
			}

			if ($data['order_current_status_id'] == ORDER_STATUS['shipped']) {
				$this->medallion_order_model->edit($order_info['id'], [
					'status' => ORDER_STATUS['shipped']
				]);

				if ($this->medallion_order_history_model->get_all([
					'medallion_order_id'=> $order_info['id'],
					'description'		=> _order_history(ORDER_STATUS['shipped']),
				])['total'] == 0) {
					$this->medallion_order_history_model->add([
						'medallion_order_id'=> $order_info['id'],
						'description' 		=> _order_history(ORDER_STATUS['shipped']),
						'status' 			=> ORDER_STATUS['shipped']
					]);
				}
			} elseif ($data['order_current_status_id'] == ORDER_STATUS['delivered']) {
				$this->medallion_order_model->edit($order_info['id'], [
					'status' 			=> ORDER_STATUS['delivered'],
					'date_completed'	=> date('Y-m-d H:i:s')
				]);
				$this->medallion_order_history_model->add([
					'medallion_order_id'=> $order_info['id'],
					'description' 		=> _order_history(ORDER_STATUS['delivered']),
					'status' 			=> ORDER_STATUS['delivered']
				]);

				CI_Events::trigger('delivered_medallion_order', [
					'order_id'	=> $order_info['id']
				]);

				CI_Events::trigger('after_delivered_medallion_order', [
					'order_id'	=> $order_info['id']
				]);
			}
		}
	}

	private function _updateSchoolOrder($data = [], $vendor = 'shiprocket') {
		$this->load->model('school/SchoolOrder_model', 'school_order_model');
		$this->load->model('school/SchoolOrderHistory_model', 'school_order_history_model');

		if (!empty($order_info = $this->school_order_model->getByCode($data['order_id']))) {
			if (!$this->webhook_data_model->get($data['order_id'], $vendor)) {
				$this->webhook_data_model->add([
					'order_code'	=> $data['order_id'],
					'vendor'		=> $vendor,
					'description'   => json_encode($data)
				]);
			} else {
				$this->webhook_data_model->edit($data['order_id'], $vendor, [
					'description'   => json_encode($data)
				]);
			}

			$shipment_info = json_decode($order_info['shipping_tracking_info'], true);

			if (!empty($data['scans'])) {
				foreach ($data['scans'] ?? [] as $value) {
					if (in_array($value['sr-status'], $this->{$vendor . '_scan_status'})) {
						$shipment_info['awb_code'] 		= $data['awb'];
						$shipment_info['status'] 		= $data['shipment_status'] ?? $shipment_info['status'];
						$shipment_info['courier_name'] 	= $data['courier_name'] ?? $shipment_info['courier_name'];

						$this->school_order_model->edit($order_info['id'], [
							'shipping_tracking_info' => json_encode($shipment_info)
						]);
					}
				}
			}

			if (!empty($data['awb']) && empty($shipment_info['awb_code']))  {
				$shipment_info['awb_code'] = $data['awb'];

				$this->school_order_model->edit($order_info['id'], [
					'shipping_tracking_info' => json_encode($shipment_info)
				]);
			}

			if (in_array($data['current_status_id'], $this->{$vendor . '_allowed_history_status'})) {
				if ($this->school_order_history_model->get_all([
					'school_order_id'	=> $order_info['id'],
					'description'		=> $data['current_status'],
				])['total'] == 0) {
					$this->school_order_history_model->add([
						'school_order_id'	=> $order_info['id'],
						'description' 		=> ucwords(strtolower($data['current_status'])),
						'status' 			=> (int)$order_info['status'],
					]);
				}

				if ($data['order_current_status_id'] == ORDER_STATUS['returned']) {
					$this->school_order_model->edit($order_info['id'], [
						'status' => ORDER_STATUS['returned'],
					]);
				}
			}

			if ($data['order_current_status_id'] == ORDER_STATUS['shipped']) {
				$this->school_order_model->edit($order_info['id'], [
					'status' => ORDER_STATUS['shipped']
				]);

				if ($this->school_order_history_model->get_all([
					'school_order_id'	=> $order_info['id'],
					'description'		=> _order_history(ORDER_STATUS['shipped']),
				])['total'] == 0) {
					$this->school_order_history_model->add([
						'school_order_id'=> $order_info['id'],
						'description' 		=> _order_history(ORDER_STATUS['shipped']),
						'status' 			=> ORDER_STATUS['shipped']
					]);
				}
			} elseif ($data['order_current_status_id'] == ORDER_STATUS['delivered']) {
				$this->school_order_model->edit($order_info['id'], [
					'status' 			=> ORDER_STATUS['delivered'],
					'date_completed'	=> date('Y-m-d H:i:s')
				]);
				$this->school_order_history_model->add([
					'school_order_id'	=> $order_info['id'],
					'description' 		=> _order_history(ORDER_STATUS['delivered']),
					'status' 			=> ORDER_STATUS['delivered']
				]);

				$this->load->model('common/Cron_model', 'cron_model');
				$this->cron_model->add([
					'code'			=> 'deliveredSchoolOrderCron_' . $order_info['id'],
					'action'		=> 'alert_model->deliveredSchoolOrderCron',
					'data'			=> [$order_info['id']],
					'site_id'		=> $order_info['site_id'],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime('+30 minutes')),
				]);
			}
		}
	}
}
