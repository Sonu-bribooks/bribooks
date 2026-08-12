<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Orders {
	public function order($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('office_manager') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'order/index';
		$data['headeing'] 	= _l('orders');
		$data['page_title'] 	= _l('new_orders');
		$data['action_ajax'] 	= site_url('officemanager/ajax_orders');

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 50
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_orders()
	{
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'status'			=> '1'
		];

		$results = $this->order_model->get_all($filter_data);
		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products = $this->order_model->getProducts($result['id']);
			$productName = '';
			$type  = json_decode($products[0]["option"],1);
			if (!empty($products)) {
				foreach ($products as $product) {
					$productName .= '<p>' . $product['name'] . '<br /> <small><strong>SKU </strong>BRIBOOK_' . $product['product_id'] . '<br /> <strong>Qty </strong>' . $product['quantity'] . '<br /> <strong> Type </strong> : '.$type["name"].'</small> </p>';
				}
			}
			$json['data'][] = [

				'sn'				=> $filter_data['start'] + 1 + $key,
				'order_code'		=> '<a href="/officemanager/order_details/' . $result['id'] . '" target="_blank">' . $result['order_code'] . '</a>',
				'customer'			=> $result['first_name'] . ' ' . $result['last_name'] . ' <small> <br />' . $result['email'] . '</small>' . ' <small>' . $result['mobile'] . '</small>',
				'product'			=> $productName,
				'weight_dimension' => '<p>' . $result['weight'] . ' gm</p>',
				'order_amount'		=> $result['currency_symbol'] . ' ' . $result['total'] . '<small class="badge badge-success">Prepaid</small>',
				'status'			=> _sd($result['status']),
				'order_date'		=> formatDate($result['date_added']),
				//'actions'			=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
				'actions'			=> (!empty($result['mobile'])) ? '<button type="button" name="ship_order" class="btn btn-sm btn-primary ship-order" data-id="' . $result['id'] . '">Ship Order</button>' : '',
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function booked_orders($param1 = NULL, $param2 = NULL)
	{
		if ($this->session->userdata('office_manager') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'order/booked';
		$data['headeing'] 		= _l('orders');
		$data['page_title'] 	= _l('booked_orders');
		$data['action_ajax'] 	= site_url('officemanager/ajax_booked_orders');

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 50
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_booked_orders()
	{
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'status'			=> '2'
		];

		$results = $this->order_model->get_all($filter_data);
		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products = $this->order_model->getProducts($result['id']);
			$productName = '';
			$type  = json_decode($products[0]["option"],1);
			if (!empty($products)) {
				foreach ($products as $product) {
					$productName .= '<p>' . $product['name'] . '<br /> <small><strong>SKU </strong>BRIBOOK_' . $product['product_id'] . '<br /> <strong>Qty </strong>' . $product['quantity'] . '<br /> <strong> Type </strong> : '.$type["name"].'</small> </p>';
				}
			}

			$json['data'][] = [
				'sn'				=> '<input type="checkbox" class="select-me" value="' . $result['id'] . '">',
				'orders'			=> '<input type="checkbox" class="select-me" value="' . $result['id'] . '">',
				'order_code'		=> '<a href="/officemanager/order_details/' . $result['id'] . '" target="_blank">' . $result['order_code'] . '</a>',
				'customer'			=> $result['first_name'] . ' ' . $result['last_name'] . ' <small> <br />' . $result['email'] . '</small>' . ' <small>' . $result['mobile'] . '</small>',
				'product'			=> $productName,
				'weight_dimension' => '<p>' . $result['weight'] . ' gm</p>',
				'order_amount'		=> $result['currency_symbol'] . ' ' . $result['total'] . '<small class="badge badge-success">Prepaid</small>',
				'order_date'		=> formatDate($result['date_added']),
				'status'			=> _sd($result['status']),
				'actions'			=> ''
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	function order_details($id = false)
	{
		if ($this->session->userdata('office_manager') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if (!$id) {
			$this->session->set_flashdata('error_message', 'Invalid request.');
			redirect($_SERVER['HTTP_REFERER'], 'refresh');
		}

		$order_info = $this->order_model->get($id);

		if (empty($order_info)) {
			$this->session->set_flashdata('error_message', 'Invalid request.');
			redirect($_SERVER['HTTP_REFERER'], 'refresh');
		}

		$data['order_info'] = $order_info;
		$data['products'] = $this->order_model->getProducts($id);
		$data['address']  = $this->address_model->get($order_info['address_id']);
		$data['user']	  = $this->user_model->get($order_info['user_id']);
		$data['orderHistory']	  = $this->order_history_model->get_all(['order_id'=>$id])["rows"];
		$data['page_name'] 		= 'order/order_details';
		$data['page_title'] 	= _l('Order Details');
		$this->load->view('backend/index', $data);
	}

	function ship_order($order_id = false)
	{
		if (!$order_id) {
			echo 'Invalid request.';
			return false;
		}

		$order_info = $this->order_model->get($order_id);

		if ($order_info['shipping_status']) {
			echo 'Invalid request ID.';
			return false;
		}

		$products = $this->order_model->getProducts($order_id);

		if (empty($products)) {
			echo 'Invalid request, product not found.';
			return false;
		}

		$order_info['products'] = $products;

		$address = $this->address_model->get($order_info['address_id']);
		if (empty($address)) {
			echo 'Invalid request, customer address not found.';
			return false;
		}

		$user	 = $this->user_model->get($order_info['user_id']);

		$order_info['address'] = $address;
		$order_info['userData'] = $user;

		$this->load->library('couriers/shiprocket_lib');
		$response = $this->shiprocket_lib->bookOrder($order_info);

		if (!empty($response->order_id) && !empty($response->shipment_id)) {
			$save = array(
				'shipping_tracking_info' => json_encode((array)$response),
			);
			$this->order_model->edit($order_info['id'], $save);

			if ($this->genrate_awb($order_id)) {
				$save = array(
					'shipping_status' => 1,
					'status' 		  => 3
				);
				$this->order_model->edit($order_info['id'], $save);
			} else {
				echo 'Unable to generate awb number.';
				return false;
			}
		} else {
			echo 'Unable to book order.';
			return false;
		}
	}

	function genrate_awb($order_id = false)
	{
		if (!$order_id)
			return false;

		$order_info = $this->order_model->get($order_id);

		if (!$order_info['shipping_status'])
			return false;

		$courierData = json_decode($order_info['shipping_tracking_info'], true);

		if (empty($courierData) || empty($courierData['shipment_id']))
			return false;

		$courierInfo = json_decode($order_info['shipping_info'], true);

		if (empty($courierInfo) || empty($courierInfo['courier_company_id']))
			return false;


		$this->load->library('couriers/shiprocket_lib');

		$response = $this->shiprocket_lib->generateAWB($courierData['shipment_id'], $courierInfo['courier_company_id']);

		if (!empty($response) && !empty($response->awb_assign_status)) {
			$courierData['error'] = 0;
			$courierData['message'] = 'AWB generated successfully.';
			$courierData['awb_number'] = $response->response->data->awb_code;
			$courierData['tracking_url'] = 'https://shiprocket.co/tracking/' . $response->response->data->awb_code;
			$courierData['assigned_date_time'] = $response->response->data->assigned_date_time->date;
			$courierData['invoice_no'] = $response->response->data->invoice_no;

			$save = array(
				'shipping_tracking_info' => json_encode($courierData),
			);
			$this->order_model->edit($order_info['id'], $save);
			return true;
		} else {
			$courierData['error'] = 1;
			$courierData['message'] = (!empty($response->response->data->awb_assign_error)) ? $response->response->data->awb_assign_error : 'Unable to generate AWB';

			$save = array(
				'shipping_status' => 4, // error
				'shipping_tracking_info' => json_encode($courierData),
			);
			$this->order_model->edit($order_info['id'], $save);
			return false;
		}
		return false;
	}

	function cancel($order_id = false)
	{
		if (!$order_id)
			return false;

		$order_info = $this->order_model->get($order_id);

		if (!$order_info['shipping_status'])
			return false;

		$courierData = json_decode($order_info['shipping_tracking_info'], true);

		if (empty($courierData) || empty($courierData['shipment_id']))
			return false;

		$this->load->library('couriers/shiprocket_lib');

		if (!empty($courierData['order_id']))
			$cancelOrderResponse = $this->shiprocket_lib->cancelOrder(array($courierData['order_id']));

		if (!empty($courierData['awb_code']))
			$response = $this->shiprocket_lib->cancelShipment(array($courierData['awb_code']));
	}

	function genrate_label()
	{
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

			$courierData = json_decode($order_info['shipping_tracking_info'], true);

			if (empty($courierData['shipment_id']))
				continue;

			$shipment_ids[] = $courierData['shipment_id'];
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

	function genrate_invoice($order_id = false)
	{
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

			$courierData = json_decode($order_info['shipping_tracking_info'], true);

			if (empty($courierData['order_id']))
				continue;

			$shipment_ids[] = $courierData['order_id'];
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

	function genrate_manifest()
	{
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

			$courierData = json_decode($order_info['shipping_tracking_info'], true);

			if (empty($courierData['order_id']))
				continue;

			$shipment_ids[] = $courierData['shipment_id'];
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
