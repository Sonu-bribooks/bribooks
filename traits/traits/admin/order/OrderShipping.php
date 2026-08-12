<?php defined('BASEPATH') or exit('No direct script access allowed');

trait OrderShipping {
	public function ajax_book_stock_fulfillment() {
		$json = [];

		$order_info = $this->order_model->get($this->input->post('order_id'));

		if ($order_info) {
			$this->order_model->edit($order_info['id'], [
				'status' => 8
			]);

			$this->ajax_move_to_ready_to_ship();

			$json['success'] = _l('order_stock_fulfilled');
		} else {
			$json['error'] = _l('invalid_order');
		}

		output_json($json);
	}

	public function ajax_move_to_ready_to_ship() {
		$json = [];

		$order_info = $this->order_model->get($this->input->post('order_id'));

		if ($order_info) {
			/*if ($order_info['status'] != 8) {
				$json['error'] = _l('not_printed_yet');
			}*/

			if (!$json) {
				$this->order_model->edit($order_info['id'], [
					'status' => 9,
				]);

				$this->order_history_model->add([
					'order_id' 		=> $order_info['id'],
					'description' 	=> _order_history(9),
					'status' 		=> 9,
				]);

				$this->load->model('order/OrderPackingLog_model', 'order_packing_log_model');

				$this->order_packing_log_model->add([
					'order_id'	=> $order_info['id'],
					'user_id'	=> $this->session->userdata('user_id'),
				]);

				// self::_clearStock($order_info['id']);

				$this->alert_model->cron($order_info['id'], 'orderBookReadyToShipCron');

				$json['success'] = _l('order_moved_to_ready_to_ship');
			}
		} else {
			$json['error'] = _l('invalid_order');
		}

		output_json($json);
	}

	public function ajax_fetch_awb() {
		$json = [];

		$order_info = $this->order_model->get($this->input->post('order_id'));

		if ($order_info) {
			$shipping_tracking_info = !empty($order_info['shipping_tracking_info'])
				? json_decode($order_info['shipping_tracking_info'], true)
				: [];

			if (empty($shipping_tracking_info)) {
				$json['error'] = _l('order_not_synced');
			}

			if (!empty($shipping_tracking_info['awb_code'])) {
				$json['error'] = _l('awb_fetched_already');
			}

			if (!$json) {
				$this->load->library('couriers/Shiprocket_lib', 'shiprocket_lib');
				$awb_info = $this->shiprocket_lib->fetchAWB($shipping_tracking_info['order_id'] ?? '');

				log_kb(['ajax_fetch_awb:: ' => $awb_info]);

				if (!empty($awb_info->data->awb_data->awb)) {
					$shipping_tracking_info['awb_code'] = $awb_info->data->awb_data->awb ?? '';

					$this->order_model->editById($order_info['id'], [
						'shipping_tracking_info' => json_encode($shipping_tracking_info),
					]);

					$this->load->model('order/OrderCosting_model', 'order_costing_model');

					$courierInfo = json_decode($order_info['shipping_info'], true);

					$order_costing_data = [
						'bb_order_id'					=> $order_info['id'],
						'order_code'					=> $order_info['order_code'],
						'currency_code'					=> $order_info['currency_code'],
						'order_total'					=> $order_info['total'],
						'shipping_charges'				=> $order_info['shipping_cost'],
						'shipping_weight'				=> $order_info['weight'],
						'order_id'						=> $shipping_tracking_info['order_id'] ?? 0,
						'shipment_id'					=> $awb_info->data->shipments->id ?? 0,
						'customer_courier_id'			=> $courierInfo['courier_company_id'] ?? 0,
						'customer_courier_name'			=> $courierInfo['courier_name'] ?? '',
						'customer_courier_charges'		=> $courierInfo['freight_charge'] ?? '0.00',
						'customer_courier_charges_rto'	=> $courierInfo['rto_charges'] ?? '0.00',
						'customer_courier_zone'			=> $courierInfo['zone'] ?? '',
						'courier_id'					=> $awb_info->data->shipments->courier_id ?? 0,
						'courier_name'					=> $awb_info->data->shipments->courier ?? '',
						'courier_charges'				=> $awb_info->data->awb_data->charges->applied_weight_amount ?? '0.00',
						'courier_charges_rto'			=> $awb_info->data->awb_data->charges->applied_weight_amount_rto ?? '0.00',
						'courier_weight'				=> !empty($awb_info->data->awb_data->charges->applied_weight) ? ($awb_info->data->awb_data->charges->applied_weight * 1000) : '0.00',
						'courier_zone'					=> $awb_info->data->awb_data->charges->zone ?? '',
					];

					$this->order_costing_model->add($order_costing_data);

					log_kb(['ajax_fetch_awb::order_costing_data:: ' => $order_costing_data]);

					$json['success'] = _l('awb_fetched');
				} else {
					$json['error'] = _l('label_not_generated');
				}

				$this->order_history_model->add([
					'order_id' 		=> $order_info['id'],
					'description' 	=> _l('awb_fetched'),
					'status' 		=> $order_info['status'],
					'_deleted' 		=> 1,
				]);

				$this->load->model('order/OrderPackingLog_model', 'order_packing_log_model');

				$this->order_packing_log_model->add([
					'order_id'	=> $order_info['id'],
					'user_id'	=> $this->session->userdata('user_id'),
				]);
			}
		} else {
			$json['error'] = _l('invalid_order');
		}

		output_json($json);
	}

	private function _clearStock($order_id = 0) {
		return;
		$this->load->model('book/BookStock_model', 'book_stock_model');

		$products = $this->order_model->getProducts($order_id);

		foreach ($products as $product) {
			$option = json_decode($product['option'], true);
			$quantity_hold_used = $quantity_used = 0;

			$stock_info = $this->book_stock_model->get_all([
				'book_id'	=> $product['product_id'],
				'version'	=> $product['version'],
				'option'	=> !empty($option['name']) ? strtolower($option['name']) : 'paperback',
			])['rows'][0] ?? [];

			if (!empty($stock_info['quantity_hold'])) {
				$quantity_hold_used = $product['quantity'] > $stock_info['quantity_hold']
					? $stock_info['quantity_hold']
					: $product['quantity'];

				$this->book_stock_model->edit($stock_info['id'], [
					'quantity_hold'	=> ($stock_info['quantity_hold'] - $quantity_hold_used),
				]);
			}

			if (!empty($stock_info['quantity']) && $quantity_hold_used != $product['quantity']) {
				$pending_quantity = $product['quantity'] - $quantity_hold_used;

				$quantity_used = $pending_quantity > $stock_info['quantity']
					? $stock_info['quantity']
					: $pending_quantity;

				$this->book_stock_model->edit($stock_info['id'], [
					'quantity'	=> ($stock_info['quantity'] - $quantity_used),
				]);
			}

			if (!empty($quantity_used) || !empty($quantity_hold_used)) {
				$this->book_stock_history_model->add([
					'manager_id'	=> (int)$this->session->userdata('user_id'),
					'order_id'		=> $order_id,
					'book_id'		=> $product['product_id'],
					'version'		=> $product['version'],
					'option'		=> !empty($option['name']) ? strtolower($option['name']) : 'paperback',
					'quantity'		=> $quantity_used,
					'quantity_hold'	=> $quantity_hold_used,
				]);
			}
		}
	}

	public function ajax_sync_order() {
		$json = [];

		$order_info = $this->order_model->get($this->input->post('order_id'));

		if ($order_info) {
			if (!empty($order_info['shipping_status'])) {
				$json['error'] = _l('already_shipped');
			}

			if (strtotime($order_info['date_added']) + 600 > time()) {
				$json['error'] = _l('wait_for_10_minutes');
			}

			if (!$json) {
				$response = $this->alert_model->invoiceOrderCron($this->input->post('order_id'), false);

				if (!empty($response->order_id) && !empty($response->shipment_id)) {
					$json['success'] = json_encode($response);
				} else {
					$json['error'] = json_encode($response);
				}
			}
		} else {
			$json['error'] = _l('invalid_order');
		}

		output_json($json);
	}

	public function ship_order($order_id = '') {
		if (!$order_id) {
			echo 'Invalid request.';
			return false;
		}

		$order_info = $this->order_model->get($order_id);

		if (!$order_info['shipping_status']) {
			echo 'Invalid request ID.';
			return false;
		}

		$products = $this->order_model->getProducts($order_info['id']);

		if (empty($products)) {
			echo 'Invalid request, product not found.';
			return false;
		}

		$order_info['products'] = $products;

		$address = $this->address_model->getByID($order_info['address_id']);

		if (empty($address)) {
			echo 'Invalid request, customer address not found.';
			return false;
		}

		$user	 = $this->user_model->get($order_info['user_id']);

		$order_info['address'] 	= $address;
		$order_info['userData'] = $user;

		$this->load->library('couriers/shiprocket_lib');
		$shippment_id = json_decode($order_info['shipping_tracking_info']);

		// $response = $this->shiprocket_lib->bookOrder($order_info);
		$response = $this->shiprocket_lib->getAwbCode($shippment_id->shipment_id);

		if (!empty($response)) {
			$awb_number = $response->tracking_data->shipment_track[0]->awb_code;
			if (empty($awb_number)) {
				return false;
			}
			$shippment_id->awb_code = $awb_number;
			$shippment_id->track_url = $response->tracking_data->track_url;
			$update_arr = ['shipping_tracking_info' => json_encode($shippment_id)];
			$this->order_model->edit($order_info['id'], $update_arr);
			return true;
		} else {
			return false;
		}

		// if (!empty($response->order_id) && !empty($response->shipment_id)) {
		// 	$save = array(
		// 		'shipping_tracking_info' => json_encode((array)$response),
		// 	);
		// 	$this->order_model->edit($order_info['id'], $save);

		// 	if ($this->genrate_awb($order_id)) {
		// 		$save = array(
		// 			'shipping_status' => 1,
		// 			'status' 		  => 3
		// 		);
		// 		$this->order_model->edit($order_info['id'], $save);
		// 	} else {
		// 		echo 'Unable to generate awb number.';
		// 		return false;
		// 	}
		// } else {
		// 	echo 'Unable to book order.';
		// 	return false;
		// }
	}

	public function genrate_awb($order_id = false) {
		if (!$order_id)
			return false;

		$order_info = $this->order_model->get($order_id);

		if (!$order_info['shipping_status'])
			return false;

		$courier_data = json_decode($order_info['shipping_tracking_info'], true);

		if (empty($courier_data) || empty($courier_data['shipment_id']))
			return false;

		$courierInfo = json_decode($order_info['shipping_info'], true);

		if (empty($courierInfo) || empty($courierInfo['courier_company_id']))
			return false;


		$this->load->library('couriers/shiprocket_lib');

		$response = $this->shiprocket_lib->generateAWB($courier_data['shipment_id'], $courierInfo['courier_company_id']);

		if (!empty($response) && !empty($response->awb_assign_status)) {
			$courier_data['error'] = 0;
			$courier_data['message'] = 'AWB generated successfully.';
			$courier_data['awb_number'] = $response->response->data->awb_code;
			$courier_data['tracking_url'] = 'https://shiprocket.co/tracking/' . $response->response->data->awb_code;
			$courier_data['assigned_date_time'] = $response->response->data->assigned_date_time->date;
			$courier_data['invoice_no'] = $response->response->data->invoice_no;

			$save = array(
				'shipping_tracking_info' => json_encode($courier_data),
			);
			$this->order_model->edit($order_info['id'], $save);
			return true;
		} else {
			$courier_data['error'] = 1;
			$courier_data['message'] = (!empty($response->response->data->awb_assign_error)) ? $response->response->data->awb_assign_error : 'Unable to generate AWB';

			$save = [
				'shipping_status' 			=> 4, // error
				'shipping_tracking_info' 	=> json_encode($courier_data),
			];
			$this->order_model->edit($order_info['id'], $save);
			return false;
		}
		return false;
	}

	public function genrate_label() {
		$order_ids = $this->input->post('order_ids');

		if (empty($order_ids)) {
			echo json_encode(array('status' => false, 'message' => 'Please select at least 1 record'));
			exit();
		}

		$shipment_ids = array();
		//$order_ids = explode(',', $order_ids);

		for ($i = 0; $i < count($order_ids); $i++) {
			$order_info = $this->order_model->get($order_ids[$i]);
			if (!$order_info['shipping_status'])
				continue;

			$courier_data = json_decode($order_info['shipping_tracking_info'], true);

			if (empty($courier_data['shipment_id']))
				continue;

			$shipment_ids[] = $courier_data['shipment_id'];
		}

		if (empty($shipment_ids)) {
			echo json_encode(array('status' => false, 'message' => 'No data found.'));
			exit();
		}

		$this->load->library('couriers/shiprocket_lib');

		$response = $this->shiprocket_lib->generateLabel($shipment_ids);

		if (!empty($response) && !empty($response->label_created)) {
			echo json_encode(array('status' => true, 'url' => $response->label_url));
			exit();
		} else {
			echo json_encode(array('status' => false, 'message' => 'unable to generate label.'));
			exit();
		}
	}

	public function genrate_invoice($order_id = false) {
		$order_ids = $this->input->post('order_ids');

		if (empty($order_ids)) {
			echo json_encode(array('status' => false, 'message' => 'Please select at least 1 record'));
			exit();
		}

		$shipment_ids = [];

		for ($i = 0; $i < count($order_ids); $i++) {
			$order_info = $this->order_model->get($order_ids[$i]);
			if (!$order_info['shipping_status'])
				continue;

			$courier_data = json_decode($order_info['shipping_tracking_info'], true);

			if (empty($courier_data['order_id']))
				continue;

			$shipment_ids[] = $courier_data['order_id'];
		}

		if (empty($shipment_ids)) {
			echo json_encode(array('status' => false, 'message' => 'No data found.'));
			exit();
		}

		$this->load->library('couriers/shiprocket_lib');

		$response = $this->shiprocket_lib->generateInvoice($shipment_ids);

		if (!empty($response) && !empty($response->is_invoice_created)) {
			echo json_encode(array('status' => true, 'url' => $response->invoice_url));
			exit();
		} else {
			echo json_encode(array('status' => false, 'message' => 'unable to generate invoice.'));
			exit();
		}
	}

	public function genrate_manifest() {
		$order_ids = $this->input->post('order_ids');

		if (empty($order_ids)) {
			echo json_encode(array('status' => false, 'message' => 'Please select at least 1 record'));
			exit();
		}

		$shipment_ids = array();

		for ($i = 0; $i < count($order_ids); $i++) {
			$order_info = $this->order_model->get($order_ids[$i]);

			if (!$order_info['shipping_status'])
				continue;

			$courier_data = json_decode($order_info['shipping_tracking_info'], true);

			if (empty($courier_data['order_id']))
				continue;

			$shipment_ids[] = $courier_data['shipment_id'];
		}

		if (empty($shipment_ids)) {
			echo json_encode(array('status' => false, 'message' => 'No data found.'));
			exit();
		}

		$this->load->library('couriers/shiprocket_lib');

		$response = $this->shiprocket_lib->generateManifests($shipment_ids);

		if (!empty($response) && !empty($response->manifest_url)) {
			echo json_encode(array('status' => true, 'url' => $response->manifest_url));
			exit();
		} else {
			echo json_encode(array('status' => false, 'message' => 'unable to generate invoice.'));
			exit();
		}
	}
}
