<?php defined('BASEPATH') OR exit('No direct script access allowed');
use Dompdf\Dompdf;

trait SchoolOrder {
	public function school_order_crud($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			self::_validateSchoolOrderForm();

			$this->school_order_model->add($this->input->post());
		} elseif ($param1 == 'edit') {
			self::_validateSchoolOrderForm($param2);

			$this->school_order_model->edit($param2, $this->input->post());
		}

		redirect(base_url('admin/school_orders'), 'refresh');
	}

	public function school_orders_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'school/orders/form';
			$data['page_title'] 					= _l('school_order_add');
			$data['action'] 						= base_url('admin/school_orders_crud/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'school/orders/form';
			$data['page_title'] 					= _l('school_order_edit');
			$data['action'] 						= base_url('admin/school_orders_crud/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->school_order_model->get($param2);
			$event_info 							= $this->event_model->get($info['event_id']);
			$school_info 							= $this->school_model->get($info['school_id']);
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
				'label' => $event_info['name'],
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'school_id',
			'label'		=> _l('select_school'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['school_id'] ?? '',
				'label' => $school_info['name'],
			],
			'ajax_url'	=> base_url('admin/ajax_search_schools'),
		];

		$this->load->view('backend/index', $data);
	}

	public function school_orders($param1 = 21, $param2 = NULL) {
		$data['page_name'] 		= 'school/orders/index';
		$data['page_title'] 	= _l('school_orders');
		$data['action_add'] 	= base_url('admin/school_orders_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_school_orders/' . (int)$param1);
		$data['nav']			= (int)$param1;
		$data['events']			= $this->event_model->get_all()['rows'] ?? [];

		$data['timestamp_start']= strtotime('-30 days', time());
		$data['timestamp_end']	= time();

		$this->load->view('backend/index', $data);
	}

	public function ajax_school_orders($status = 21) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if (!empty($status)) {
			$filter_data['status'] = (int)$status;
		}

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] = trim($explode[0]);
			$filter_data['enddate'] = trim($explode[1]);
		}

		if ($this->input->get('shipping_status')) {
			$filter_data['shipping_status'] = (int)$this->input->get('shipping_status') == 2 ? 0 : 1;
		}

		if ($this->input->get('school_id')) {
			$filter_data['school_id'] = (int)$this->input->get('school_id');
		}

		if ($this->input->get('status')) {
			$filter_data['status'] = (int)$this->input->get('status');
		}

		if ($this->input->get('ne_status')) {
			$filter_data['ne_status'] = (int)$this->input->get('ne_status');
		}

		if ($this->input->get('order_code')) {
			$filter_data['order_code'] = $this->input->get('order_code');
		}

		if ($this->input->get('event_id')) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}
        
		if ($this->input->get('is_registered') === '0' || $this->input->get('is_registered') === '1') {
			$filter_data['is_registered'] = $this->input->get('is_registered');
		}
		
		$results = $this->school_order_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$total 	= $result['total'];
			$weight = $result['weight'];

			$is_registered = empty($this->school_lead_model->get_all([
				'school_id' 	=> $result['school_id'],
				'verified' 		=> 1,
			])['total']) ? false : true;

			$shipping_tracking_info = !empty($result['shipping_tracking_info']) ? json_decode($result['shipping_tracking_info'], true) : '';

			$json['data'][] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'school_id'			=> $result['school_id'],
				'order_code'		=> _school_order_code($result, $shipping_tracking_info),
				'customer'			=> vsprintf('%s<br>%s<br>%s<br>%s<br>%s<br>%s<br>%s<br>%s', [
					$result['school_name'],
					$result['address'],
					$result['owner_mobile'],
					$result['alternate_owner_mobile'],
					$result['owner_email'],
					$result['alternate_owner_email'],
					($result['authorized_person']) ? $result['authorized_person'] : $result['alternate_authorized_person'],
					$result['date_modified']
				]),
				'product'			=> sprintf('<ul><li>%s</li></ul>', implode('</li><li>', ['Letter'])),
				'weight_amount'		=> vsprintf('%s gm<br>%s%s', [
					$weight,
					$result['currency_symbol'],
					$total,
				]),
				'status'			=> _sd($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'history'			=> self::_getSchoolOrderHistory($result['id']),
				'actions'			=> _so_buttons($result, $shipping_tracking_info),
				'is_registered'		=> ($status == 4) ? $is_registered : ''
			];
		}

		output_json($json);
	}

	public function school_order_details($order_id = 0) {
		$order_info = $this->school_order_model->get($order_id);

		if (empty($order_info)) {
			$this->session->set_flashdata('error_message', _l('invalid_school_order'));
			redirect('refresh');
		}

		$data['order_info'] 	= $order_info;
		$data['school_info']  	= $this->school_model->get($order_info['school_id']);
		$data['user']	  		= $this->user_model->get($order_info['user_id']);
		$data['histories']		= $this->school_order_history_model->get_all([
			'school_order_id'=> $order_info['id']
		])['rows'] ?? [];
		$data['comments']		= $this->school_order_comment_model->get_all([
			'school_order_id'=> $order_info['id']
		])['rows'] ?? [];

		$data['page_name'] 		= 'school/orders/details';
		$data['page_title'] 	= _l('school_order_details');

		$this->load->view('backend/index', $data);
	}

	public function add_school_order_comment() {
		$json = [];

		if ($order_info = $this->school_order_model->get($this->input->post('order_id'))) {
			if (!empty($order_info['status'])) {
				$this->school_order_comment_model->add([
					'manager_id' 			=> (int)$this->session->userdata('user_id'),
					'school_order_id' 		=> (int)$this->input->post('order_id'),
					'description' 			=> ($this->input->post('comment')) ? $this->input->post('comment'):  $this->input->post('status'),
					'status' 				=> $order_info['status'],
				]);

				$json['success'] 	= _l('school_order_comment_added');
			} else {
				$json['error'] 		= _l('school_order_not_processed_yet');
			}
		} else {
			$json['error'] = _l('school_order_not_found');
		}

		output_json($json);
	}

	public function cancel_school_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('order_id'))) {
			$order_info = $this->school_order_model->get($order_id);

			// Add order comment
			$this->school_order_comment_model->add([
				'manager_id' 			=> (int)$this->session->userdata('user_id'),
				'school_order_id' 		=> (int)$order_id,
				'description' 			=> $this->input->post('comment'),
				'status' 				=> $order_info['status'] ?? 91,
			]);

			$this->school_order_model->edit($order_id, [
				'status'		=> 91,
			]);

			$this->school_order_history_model->add([
				'school_order_id' 	=> (int)$order_id,
				'description' 		=> _l('order_cancelled'),
				'status' 			=> $order_info['status'] ?? 91,
			]);

			$json['success'] 	= _l('order_cancellation_request_added');
		}

		output_json($json);
	}

	public function escalate_school_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('order_id'))) {
			$order_info = $this->school_order_model->get($order_id);

			// Add order comment
			$this->school_order_comment_model->add([
				'manager_id' 		=> (int)$this->session->userdata('user_id'),
				'school_order_id' 	=> (int)$order_id,
				'description' 		=> $this->input->post('comment'),
				'status' 			=> $order_info['status'] ?? 93,
			]);

			$this->school_order_model->edit($order_id, [
				'status'		=> 93,
			]);

			$this->school_order_history_model->add([
				'school_order_id' 	=> (int)$order_id,
				'description' 		=> _l('order_escalated'),
				'status' 			=> $order_info['status'] ?? 93,
			]);

			$json['success'] 	= _l('school_order_escalated_request_added');
		}

		output_json($json);
	}

	public function escalate_restore_school_order() {
		$json = [];

		if (!empty($order_id = $this->input->post('order_id'))) {
			$order_info = $this->school_order_model->get($order_id);

			// Add order comment
			$this->school_order_comment_model->add([
				'manager_id' 		=> (int)$this->session->userdata('user_id'),
				'school_order_id' 	=> (int)$order_id,
				'description' 		=> $this->input->post('comment'),
				'status' 			=> $order_info['status'] ?? 93,
			]);

			$this->school_order_history_model->add([
				'school_order_id' 	=> (int)$order_id,
				'description' 		=> _l('order_escalated'),
				'status' 			=> $order_info['status'] ?? 93,
			]);

			$this->school_order_model->edit($order_id, [
				'status'		=> 1,
			]);

			$json['success'] 	= _l('escalated_school_order_restore_request_added');
		}

		output_json($json);
	}

	public function export_school_orders() {
		$json = [];

		$filter_data['parent_id'] = 0;

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] = trim($explode[0]);
			$filter_data['enddate'] = trim($explode[1]);
		}

		if ($this->input->get('shipping_status')) {
			$filter_data['shipping_status'] = (int)$this->input->get('shipping_status') == 2 ? 0 : 1;
		}

		if ($this->input->get('school_id')) {
			$filter_data['school_id'] = (int)$this->input->get('school_id');
		}

		if ($this->input->get('status')) {
			$filter_data['status'] = (int)$this->input->get('status');
		}

		if ($this->input->get('ne_status')) {
			$filter_data['ne_status'] = (int)$this->input->get('ne_status');
		}

		if ($this->input->get('order_code')) {
			$filter_data['order_code'] = $this->input->get('order_code');
		}

		if ($this->input->get('event_id')) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		$results = $this->school_order_model->get_all($filter_data)['rows'] ?? [];

		$orders = [];

		$sn = 1;

		foreach ($results as $key => $order) {
			$order_comments = $this->school_order_comment_model->get_all([
				'school_order_id' => (int)$order['id']
			])['rows'] ?? [];

			$comments = '';

			if (!empty($order_comments)) {
				foreach ($order_comments as $order_comment) {
					$comments .= $order_comment['description'] . "\n";
				}

				$comments = substr($comments, 0, -2);
			}

			$products 		= $this->school_order_model->getProducts($order['id']);
			$school_info 	= $this->school_model->get($order['school_id']);
			$city_info 		= $this->city_model->get($school_info['city_id'] ?? 0);
			$state_info 	= $this->state_model->get($school_info['state_id'] ?? 0);
			$country_info 	= $this->country_model->get_all([
				'code' => $school_info['country_code']
			])['rows'][0] ?? '';

			$address 		= !empty($school_info) ? vsprintf('%s, %s, %s, %s, %s, %s, %s, - %s - %s', [
				$school_info['name'],
				$school_info['mobile'],
				$school_info['address'],
				$school_info['landmark'],
				$city_info['city'],
				$state_info['state'],
				$country_info['name'] ?? 'India',
				$school_info['zipcode'],
				$order['type'],
			]) : '';

			$total 			= round($order['total'], 2);
			$shipping_info 	= json_decode($order['shipping_info'], true);
			$shipping_tracking_info = json_decode($order['shipping_tracking_info'], true);

			$orders[] = [
				'sn'			=> $key + 1,
				'region'		=> strtolower($order['currency_code']) === 'inr'
					? _l('domestic')
					: _l('global'),
				'order_id'		=> $order['id'],
				'order_code'	=> $order['order_code'],
				'school'		=> $school_info['book_name'],
				'status'		=> _os($order['status']),
				'address'		=> $address,
				'c_mobile'		=> $school_info['owner_mobile'] ?? '',
				'c_email'		=> $school_info['owner_email'] ?? '',
				'currency_code'	=> $order['currency_code'],
				'total'			=> $key == 0 ? $total : 0,
				'weight'		=> $order['weight'] . 'gm',
				'awb_code'		=> $shipping_tracking_info['awb_code'] ?? '',
				'shipping_info'	=> $shipping_tracking_info['courier_name'] ?? ($shipping_info['courier_name'] ?? ''),
				'date_added'	=> $order['date_added'],
				'comments'		=> $comments
			];
		}

		self::_downloadCsv($orders, 'school_orders_');

		output_json($json);
	}

	private function _getSchoolOrderHistory($order_id = 0) {
		$comments = $histories = [];

		foreach ($this->school_order_history_model->get_all([
			'school_order_id'	=> $order_id,
		])['rows'] ?? [] as $item) {
			$histories[] = vsprintf('%s - %s', [
				$item['description'],
				formatDate($item['date_added']),
			]);
		}

		foreach ($this->school_order_comment_model->get_all([
			'school_order_id'	=> $order_id,
		])['rows'] ?? [] as $item) {
			$agent_info = !empty($item['manager_id']) ? $this->user_model->get($item['manager_id']) : [];

			$comments[] = vsprintf('%s %s : %s - %s', [
				$agent_info['first_name'] ?? '',
				$agent_info['last_name'] ?? '',
				$item['description'],
				formatDate($item['date_added']),
			]);
		}

		$packing_info 	= $this->school_order_packing_log_model->get_all([
			'school_order_id'	=> $order_id
		])['rows'];

		$packing_info 	= !empty($packing_info[0]) ? $packing_info[0] : [];
		$agent_info 	= !empty($packing_info['user_id']) ? $this->user_model->get($packing_info['user_id']) : [];

		return vsprintf('<a class="text-primary" data-toggle="tooltip" title="%s"><i class="fa fa-info-circle"></i> %s %s</a><i class="text-danger fa fa-exclamation-triangle" data-toggle="tooltip" title="%s"></i><br>%s', [
			implode("\n", $histories),
			$agent_info['first_name'] ?? '',
			$agent_info['last_name'] ?? '',
			implode("\n", array_slice($comments, 1)),
			$comments[0] ?? '',
		]);
	}

	private function _validateSchoolOrderForm($id = 0) {
		$info = $this->school_order_model->get($id);

		if (empty($school_info = $this->school_model->get($this->input->post('school_id')))) {
			$this->session->set_flashdata('error_message', _l('school_not_found'));
			redirect(base_url('admin/school_orders'), 'refresh');
		}

		$book_info 			= $this->book_model->get($this->input->post('book_id'));
		$_POST['user_id'] 	= $book_info['user_id'];

		$currency_info 		= $this->currency_model->getByCode($school_info['currency_code']);

		$_POST['subtotal']	= apply_currency_exchange($this->input->post('price'), $school_info['currency_code']);
		$_POST['weight']	= $this->input->post('weight');
		$_POST['shipping_cost'] = apply_currency_exchange($this->input->post('declared_value'), $school_info['currency_code']);
		$_POST['total']		= apply_currency_exchange($this->input->post('price') + $this->input->post('declared_value'), $school_info['currency_code']);
		$_POST['status']	= $info['status'] ?? 1;

		$_POST['currency_id'] 		= $currency_info['id'];
		$_POST['currency_code'] 	= $currency_info['code'];
		$_POST['currency_symbol'] 	= $currency_info['symbol'];

		$_POST['order_code'] 		= vsprintf('BBL-%s%s%s', [
			time(),
			(int)$this->input->post('event_id'),
			$school_info['id']
		]);
	}

	public function ajax_update_school_order() {
		$json = [];

		$request = $this->input->post();

		if (!empty($request['order_id']) &&
			!empty($request['weight']) &&
			!empty($order_info = $this->school_order_model->get($request['order_id']))
		) {
			$this->school_order_model->edit($order_info['id'], [
				'weight'   	=> $request['weight'] ?? $order_info['weight']
			]);

			$json['success'] 	= _l('order_updated_successfully');
		} else {
			$json['error'] 		= _l('something_went_wrong!');
		}

		output_json($json);
	}

	public function school_address_download($order_id = 0) {
		if ($order_id == 0) return false;

		$order_info = $this->school_order_model->get($order_id);

		$school_info = $this->school_model->getSchoolAddress($order_info['school_id']);

		if (empty($school_info)) return;

		$data = [
			'owner_name' 	=> $school_info['owner_name'] ? ucfirst($school_info['owner_name']) : 'The Principal',
			'name'		 	=> $school_info['name'],
			'mobile'		=> $school_info['mobile'],
			'address'	 	=> $school_info['address'],
			'zipcode' 		=> $school_info['zipcode'],
			'city' 			=> $school_info['city'],
			'state' 		=> $school_info['state'],
		];

		$html = $this->load->view('backend/admin/school/address_label', $data, true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', '><', $html));
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->set_option('isPhpEnabled', true);
		$dompdf->setPaper('A4', 'portrait');

		$dompdf->render();

		$file_name = $school_info['name'] . '_' . time() . '.pdf';
		$dompdf->stream($file_name);
	}

	public function school_label_download($school_id = 0) {
		if($school_id == 0) return false;

		$school_info = $this->school_model->getSchoolAddress($school_id);

		if (empty($school_info)) return;

		$data = [
			'owner_name' 	=> $school_info['owner_name'] ? ucfirst($school_info['owner_name']) : 'The Principal',
			'name'		 	=> $school_info['name'],
			'mobile'		=> $school_info['mobile'],
			'address'	 	=> $school_info['address'],
			'zipcode' 		=> $school_info['zipcode'],
			'city' 			=> $school_info['city'],
			'state' 		=> $school_info['state'],
		];

		$html = $this->load->view('backend/admin/school/address_label', $data, true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', '><', $html));
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->set_option('isPhpEnabled', true);
		$dompdf->setPaper('A4', 'portrait');

		$dompdf->render();

		$file_name = $school_info['name'] . '_' . time() . '.pdf';

		$dompdf->stream($file_name, array('Attachment' => 1));
	}
}
