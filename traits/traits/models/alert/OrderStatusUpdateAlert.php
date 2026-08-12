<?php defined('BASEPATH') or exit('No direct script access allowed');

trait OrderStatusUpdateAlert {
	public function updateOrderStatusMidnightCron() {
		$this->load->library('BriBooksShipping_lib', 'bribooksshipping_lib');
		$this->load->model('order/OrderHistory_model', 'order_history_model');
		$this->load->model('shipping/Shipment_model', 'shimpment_model');

		$results = $this->shimpment_model->get_all([
			'start'							=> 0,
			'status'						=> 1,
			'limit'							=> 500,
			'sort'							=> 'shipment.id',
			'order'							=> 'DESC',
			'order_status'					=> [0, 15, 91, 92, 93, 94],
			'startdate'						=> date('Y-m-d', strtotime('-60 days')),
			'enddate'						=> date('Y-m-d', strtotime('-1 days')),
		])['rows'] ?? [];

		log_kb(['updateOrderStatusMidnightCron:: ' => $results]);

		foreach ($results as $key => $shipment) {
			if ($shipment['order_type'] == 'medallion') {
				$order_model 	= 'medallion_order_model';
				$history_model 	= 'medallion_order_history_model';
			} else if ($order_type == 'school') {
				$order_model 	= 'school_order_model';
				$history_model 	= 'school_order_history_model';
			} else {
				$order_model 	= 'order_model';
				$history_model 	= 'order_history_model';
			}

			$order_info 			= $this->{$order_model}->get($shipment['order_id']);

			log_kb(['updateOrderStatusMidnightCron::order: ' => $order_info]);

			if (in_array($order_info['status'], [94])) continue;

			if (in_array($order_info['status'], [4, 15])) {
				$this->shimpment_model->edit($shipment['id'], [
					'order_status'				=> $order_info['status'],
					'shipping_tracking_info'	=> $order_info['shipping_tracking_info'],
				]);
				continue;
			}

			$tracking_info 			= $this->bribooksshipping_lib->trackOrder($shipment['id']);
			$shipping_tracking_info = json_decode($order_info['shipping_tracking_info'], true);

			$shipping_tracking_info['awb_code'] = $tracking_info['shipment_info']['awb_code'] ?? $shipping_tracking_info['awb_code'];
			$shipping_tracking_info['status'] 	= $tracking_info['shipment_info']['status'] ?? $shipping_tracking_info['status'];

			log_kb(['updateOrderStatusMidnightCron::tracking: ' => $tracking_info]);

			if ($tracking_info['order_status'] == 4) {
				$this->{$order_model}->edit($order_info['id'], [
					'status' 				=> 4,
					'shipping_tracking_info'=> json_encode($shipping_tracking_info),
					'date_completed'		=> date('Y-m-d H:i:s')
				]);

				$this->{$history_model}->add([
					'order_id' 		=> $order_info['id'],
					'description' 	=> _order_history(4),
					'status' 		=> 4
				]);

				$this->shimpment_model->edit($shipment['id'], [
					'order_status'				=> 4,
					'shipping_tracking_info'	=> json_encode($shipping_tracking_info),
				]);

				if ($shipment['order_type'] == 'book') {
					$this->load->library('Royalty_lib', 'royalty_lib');
					$this->royalty_lib->generateCredit($order_info['id']);
				}
			} elseif ($tracking_info['order_status'] == 15) {
				$this->{$order_model}->edit($order_info['id'], [
					'status' => 15,
				]);

				$this->shimpment_model->edit($shipment['id'], [
					'order_status'				=> 15,
					'shipping_tracking_info'	=> json_encode($shipping_tracking_info),
				]);
			}
		}
	}
}
