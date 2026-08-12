<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Coupon {
	public function coupon($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'event',
			'name',
			'coupon_type',
			'book',
			'user',
			'code',
			'discount_type',
			'currency_code',
			'discount',
			'total',
			'used_count',
			'used_limit',
			'start_date',
			'end_date',
			'remaining_time',
			'status',
			'actions',
		];

		if ($param1 == 'add') {
			$data = $this->input->post();

			self::_checkDuplicateCoupon($data['code']);

			if (!empty($data['currency_id']) && !empty($currency_info = $this->currency_model->get($data['currency_id'] ?? 0))) {
				$data['currency_code'] = $currency_info['code'];
			}

			if (!empty($data['date_start'])) {
				$data['date_start'] = date('Y-m-d H:i:s', strtotime($data['date_start']));
			}

			if (!empty($data['date_end'])) {
				$data['date_end'] = date('Y-m-d H:i:s', strtotime($data['date_end']));
			}

			$this->coupon_model->add($data);

			$this->session->set_flashdata('flash_message', 'coupon added successfully!');

			redirect(base_url('admin/coupon'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data = $this->input->post();

			self::_checkDuplicateCoupon($data['code'], $param2);

			if (!empty($data['date_start'])) {
				$data['date_start'] = date('Y-m-d H:i:s', strtotime($data['date_start']));
			}

			if (!empty($data['date_end'])) {
				$data['date_end'] = date('Y-m-d H:i:s', strtotime($data['date_end']));
			}

			if (!empty($data['currency_id']) && !empty($currency_info = $this->currency_model->get($data['currency_id'] ?? 0))) {
				$data['currency_code'] = $currency_info['code'];
			}

			$this->coupon_model->edit($param2, $data);

			$this->session->set_flashdata('flash_message', 'coupon updated successfully!');

			redirect(base_url('admin/coupon'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->coupon_model->enableDisable($param2, $this->input->post());
			redirect(base_url('admin/coupon'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->coupon_model->delete($param2);
			redirect(base_url('admin/coupon'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('coupon');
		$data['action_add'] 	= base_url('admin/coupon_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_coupon');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/coupon_form/edit/',
			],
			[
				'key'	=> 'status',
				'type'	=> 'status',
				'url'	=> 'admin/coupon/status/',
			],
						[
				'key'	=> 'delete',
				'type'	=> 'confirm',
				'url'	=> 'admin/coupon/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function coupon_form($param1 = NULL, $param2 = NULL) {

		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('coupon_form_add');
			$data['action'] 						= base_url('admin/coupon/add/');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('coupon_form_edit');
			$data['action'] 						= base_url('admin/coupon/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->coupon_model->get($param2);

			$user_info 								= $this->user_model->get($info['user_id'] ?? 0);
			$event_info 							= $this->event_model->get($info['event_id'] ?? 0);
			$book_info 								= $this->book_model->get($info['item_id'] ?? 0);
			$currency_info 							= $this->currency_model->get($info['currency_id'] ?? 0);
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> false,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
				'label' => $event_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'item_id',
			'label'		=> _l('select_book'),
			'required'	=> false,
			'value'		=> [
				'value' => $info['item_id'] ?? '',
				'label' => $book_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_books'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'user_id',
			'label'		=> _l('select_user'),
			'required'	=> false,
			'value'		=> [
				'value' => $info['user_id'] ?? '',
				'label' => !empty($info['user_id']) ? sprintf('%s %s (%s)', $user_info['first_name'], $user_info['last_name'], $user_info['email']) : '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_students'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'coupon_type',
			'label'		=> _l('select_coupon_type'),
			'required'	=> true,
			'value'		=> $info['coupon_type'] ?? 'product',
			'options'	=> [
				[
					'value' => 'product',
					'label' => _l('product'),
				],
				[
					'value' => 'subscription',
					'label' => _l('subscription'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'discount_type',
			'label'		=> _l('select_discount_type'),
			'required'	=> true,
			'value'		=> $info['discount_type'] ?? '',
			'options'	=> [
				[
					'value' => 1,
					'label' => _l('flat'),
				],
				[
					'value' => 2,
					'label' => _l('percentage'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'currency_id',
			'label'		=> _l('select_currency'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['currency_id'] ?? '',
				'label' => !empty($currency_info['name']) ? sprintf('%s (%s, %s)', $currency_info['name'], $currency_info['symbol'], $currency_info['code']) : '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_currency'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('coupon_name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'gen_code',
			'key'		=> 'code',
			'label'		=> _l('code'),
			'required'	=> true,
			'value'		=> $info['code'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'discount',
			'label'		=> _l('discount'),
			'required'	=> true,
			'value'		=> $info['discount'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'total',
			'label'		=> _l('total'),
			'required'	=> false,
			'value'		=> $info['total'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'used_limit',
			'label'		=> _l('used_limit'),
			'required'	=> true,
			'value'		=> $info['used_limit'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'user_type',
			'label'		=> _l('select_user_type'),
			'required'	=> false,
			'value'		=> $info['user_type'] ?? 0,
			'options'	=> [
				[
					'value' => 0,
					'label' => _l('all'),
				],
				[
					'value' => 1,
					'label' => _l('new'),
				],
				[
					'value' => 2,
					'label' => _l('existing'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'quantity',
			'label'		=> _l('min_quantity'),
			'required'	=> false,
			'value'		=> $info['quantity'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'max_quantity',
			'label'		=> _l('max_quantity'),
			'required'	=> false,
			'value'		=> $info['max_quantity'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'book_stock_quantity',
			'label'		=> _l('book_stock_quantity'),
			'required'	=> false,
			'value'		=> $info['book_stock_quantity'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'date_start',
			'label'		=> _l('start_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['date_start'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'datetime',
			'key'		=> 'date_end',
			'label'		=> _l('end_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['date_end'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_coupon() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->coupon_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info 								= $this->user_model->get($result['user_id'] ?? 0);
			$event_info 							= $this->event_model->get($result['event_id'] ?? 0);
			$book_info 								= $this->book_model->get($result['item_id'] ?? 0);
			$currency_info 							= $this->currency_model->get($result['currency_id'] ?? 0);

			$start 									= new DateTime($result['date_start']);
			$end 									= new DateTime($result['date_end']);
			$time 									= $start && $end ? $start->diff($end)->days : 0; 

			$json['data'][] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],

				'event'				=> !empty($result['event_id']) ? sprintf('%s | %s', $event_info['name'], $result['event_id']) : 'Generic',
				'name'				=> $result['name'],
				
				'coupon_type'		=> $result['coupon_type'],
				'book'				=> !empty($result['item_id']) ? sprintf('%s | %s', $book_info['name'], $result['item_id']) : 'NA',

				'user' 				=> !empty($result['user_id']) && !empty($user_info)
					? sprintf(
						'%s %s<br><small>%s | %s</small>',
						$user_info['first_name'] ?? '',
						$user_info['last_name'] ?? '',
						$user_info['email'] ?? 'NA',
						$user_info['mobile'] ?? 'NA'
					)
					: 'NA',

				'code'			 	=> $result['code'],
				'discount_type' 	=> ($result['discount_type'] ?? 0) == 1
					? 'Flat'
					: (($result['discount_type'] ?? 0) == 2 ? 'Percentage' : 'NA'),

				'currency_code'		=> $result['currency_code'] ?? '',

				'discount'			=> $result['discount'] ?? 0,
				'total'				=> $result['total'] ?? 0,


				'used_count'		=> $result['used_count'] ?? 0,
				'used_limit'		=> $result['used_limit'] ?? 0,

				'start_date'		=> formatDate($result['date_start']),
				'end_date'			=> formatDate($result['date_start']),
				'remaining_time'	=> $time . ' Days',
				'status'			=> _sd($result['status']),
				'actions'			=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _checkDuplicateCoupon($code = '', $id = 0) {
		if (!empty($code) && !empty($info = $this->coupon_model->getByCouponCode([
			'code' => $code
		]) ?? [])) {
			if (!empty($id) && ($info['id'] == $id)) return;

			$this->session->unset_userdata([
				'flash_message',
				'error_message',
			]);

			$this->session->set_flashdata('error_message', _l('coupon_is_already_exist'));

			redirect(
				!empty($id)
					? base_url('admin/coupon_form/edit/' . $id)
					: base_url('admin/coupon'),
				'refresh'
			);
			return;
		}
	}
}
