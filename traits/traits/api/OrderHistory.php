<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait orderHistory {
	public function getOrderTracking() {
		$this->form_validation->set_rules('order_code', _l('order_code'), 'trim|required|min_length[3]');
		self::_runFormValidation();

		if (!$this->json) {
			$order_info = $this->order_model->getOrderByCode($this->input->post('order_code'));

			if (!$order_info) {
				return $this->json['error'] = _l('order_not_found');
			}

			$shipping_tracking_info = json_decode($order_info['shipping_tracking_info'], true);
			$shipping_info = json_decode($order_info['shipping_info'], true);

			$order_history_result = $this->order_history_model->get_all([
				'order_id' 	=> $order_info['id'],
				'order'		=> 'ASC'
			]);

			$history = $order_history_result['rows'];

			array_unshift($history, [
				'description' 	=> _li('order_received'),
				'status'		=> ORDER_STATUS['new'],
				'order_id'   	=> $order_info['id'],
				'date_added' 	=> $order_info['date_added']
			]);

			$result = [];

			foreach ($history as $value) {
				if (!empty($value['description']) && (strtolower($value['description']) == 'clone order created')) {
					continue;
				}

				$value['description'] = _li($value['description']);

				unset($value['_deleted']);
				unset($value['date_deleted']);

				$value['date_added'] = date('d-M-Y h:ia', strtotime($value['date_added']));
				array_push($result, $value);
			}

			if (!empty($clone_order_results = $this->db->get_where('order_clone', [
				'parent_order_id'	=> $order_info['id'],
				'_deleted'			=> 0
			])->result_array())) {
				$clone_orders = [];

				foreach ($clone_order_results as $clone_order_result) {
					$clone_order_info = $this->order_model->get($clone_order_result['clone_order_id']);

					if (!empty($clone_order_info['order_code'])) {
						$clone_orders[] = [
							'clone_order_id'	=> $clone_order_info['id'],
							'clone_order_code'	=> $clone_order_info['order_code'],
							'clone_status'		=> USER_URL . 'trackdelivery/' . $clone_order_info['order_code'],
							'date_added'		=> date('d-M-Y h:ia', strtotime($clone_order_info['date_added']))
						];
					}
				}

				array_push($result, [
					'description' 	=> _li('clone order created'),
					'order_id'   	=> $order_info['id'],
					'date_added' 	=> $clone_orders[0]['date_added'] ?? date('d-M-Y h:ia', strtotime($order_info['date_added'])),
					'clone_orders'	=> $clone_orders
				]);
			}

			$this->json['message'] 		= _l('order_history_found.');
			$this->json['order_code'] 	= $order_info['order_code'];
			$this->json['awb_code'] 	= $shipping_tracking_info['awb_code'] ?? '';
			$this->json['track_url'] 	= (!empty($shipping_tracking_info['awb_code']))
				? self::_getTrackingUrl($shipping_info)
				: '';
			$this->json['result'] 		= $result;
		}
	}

	private function _getTrackingUrl($shipping_info = []) {
		if (empty($shipping_info['bb_shipment_id'])) return;

		$this->load->library('BriBooksShipping_lib');
		return $this->bribooksshipping_lib->trackingUrl($shipping_info['bb_shipment_id']) ?? '';
	}
}
