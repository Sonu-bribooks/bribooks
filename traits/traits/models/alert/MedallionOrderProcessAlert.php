<?php defined('BASEPATH') or exit('No direct script access allowed');

trait MedallionOrderProcessAlert {
	public function medallionOrderReadyToShipCron($id = 0) {
		$this->load->model('medallion/MedallionOrder_model', 'medallion_order_model');

		$order_info = $this->medallion_order_model->get($id);
		$user_info 	= $this->student_model->get($order_info['user_id']);

		if (empty($user_info['email'])) return;

		$products = $this->medallion_order_model->getProducts($order_info['id']);

		$message = $this->load->view('common/mail/part/medallion_order_readytoship_alert', compact([
			'user_info',
			'products',
			'order_info',
		]), true);

		$bcc = ['communication@bribooks.com'];

		self::email(
			$user_info['email'],
			_li('Congratulations! Your Medallion Is On Its Way | BriBooks'),
			$message,
			[],
			$bcc
		);
	}

	public function deliveredMedallionOrderCron($id = '') {
		$this->load->model('medallion/MedallionOrder_model', 'medallion_order_model');

		$order_info = $this->medallion_order_model->get($id);

		if (empty($order_info)) return;

		$user_info = $this->student_model->get($order_info['user_id']);

		if (empty($user_info['email'])) return;

		$products = $this->medallion_order_model->getProducts($order_info['id']);

		$message = $this->load->view('common/mail/part/medallion_order_delivered_alert', compact([
			'user_info',
			'products',
			'order_info',
		]), true);

		$bcc = ['communication@bribooks.com'];

		self::email(
			$user_info['email'],
			_li('Congratulations! Your Medallion Has Been Delivered Successfully | BriBooks'),
			$message,
			[],
			$bcc
		);
	}

	public function updateMedallionOrderStatusCron() {
		return;
		$this->load->model('medallion/MedallionOrder_model', 'medallion_order_model');
		$this->load->model('medallion/MedallionOrderHistory_model', 'medallion_order_history_model');

		$results = $this->medallion_order_model->get_all([
			'shipping_status'	=> 1,
			'startdate'			=> date('Y-m-d', strtotime('-180 days')),
			'enddate'			=> date('Y-m-d', strtotime('-1 days')),
			'ne_status'			=> [0, 4, 15, 91, 92, 93],
			'start'				=> 0,
			'limit'				=> 500,
		]);

		$this->load->library('couriers/shiprocket_lib');

		foreach ($results['rows'] ?? [] as $key => $order_info) {
			$shippment_id = json_decode($order_info['shipping_tracking_info']);

			$response = $this->shiprocket_lib->getAwbCode($shippment_id->shipment_id);

			if (empty($data = $response->tracking_data->shipment_track[0])) {
				continue;
			}

			if ($response->tracking_data->shipment_status == 7) {
				$shipment_info = json_decode($order_info['shipping_tracking_info'], true);

				$shipment_info['awb_code'] 		= $data->awb_code;
				$shipment_info['status'] 		= $data->current_status ?? $shipment_info['status'];
				$shipment_info['courier_name'] 	= $data->courier_name ?? $shipment_info['courier_name'];

				$this->medallion_order_model->edit($order_info['id'], [
					'status' 				=> 4,
					'shipping_tracking_info'=> json_encode($shipment_info),
					'date_completed'		=> date('Y-m-d H:i:s')
				]);

				$this->medallion_order_history_model->add([
					'medallion_order_id' 	=> $order_info['id'],
					'description' 			=> _order_history(4),
					'status' 				=> 4
				]);
			} elseif (strtolower($data->current_status) === 'rto delivered') {
				$this->medallion_order_model->edit($order_info['id'], [
					'status' => 15,
				]);
			}
		}
	}
}
