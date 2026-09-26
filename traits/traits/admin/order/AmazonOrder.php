<?php defined('BASEPATH') or exit('No direct script access allowed');

trait AmazonOrder {
	public function amazon_order($param1 = null, $param2 = null) {
        $this->load->model('event/EventOrderAmazon_model', 'event_order_amazon_model');
		$data['fields'] = [
			'sn',
			'id',
			'event_id',
			'event',
			'customer',
			'product',
			'amount',
			'book_sold',
            'currency_code',
			'date_added',
			'actions',
		];

		if ($param1 == 'add') {
			$data 			= $this->input->post();

            if (empty($this->event_book_model->getEventBookByBookId($data['event_id'], $data['book_id']))) {
                $this->session->set_flashdata('error_message', _li('book_is_not_enrolled_this_event'));
                redirect(base_url('admin/amazon_order_form/add'), 'refresh');
            }

            $book_info = $this->book_model->get($data['book_id']);

            if ($book_info['user_id'] != $data['user_id']) {
                $this->session->set_flashdata('error_message', _li('user_is_not_author_of_this_book'));
                redirect(base_url('admin/amazon_order_form/add'), 'refresh');
            }

			$this->event_order_amazon_model->add($data);
			redirect(base_url('admin/amazon_order'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data 			= $this->input->post();

            if (empty($this->event_book_model->getEventBookByBookId($data['event_id'], $data['book_id']))) {
                $this->session->set_flashdata('error_message', _li('book_is_not_enrolled_this_event'));
                redirect(base_url('admin/amazon_order_form/edit/' . (int)$param2), 'refresh');
            }

            $book_info = $this->book_model->get($data['book_id']);

            if ($book_info['user_id'] != $data['user_id']) {
                $this->session->set_flashdata('error_message', _li('user_is_not_author_of_this_book'));
                redirect(base_url('admin/amazon_order_form/edit/' . (int)$param2), 'refresh');
            }

			$this->event_order_amazon_model->edit($param2, $data);
			redirect(base_url('admin/amazon_order'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->event_order_amazon_model->delete($param2);
			redirect(base_url('admin/amazon_order'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('amazon_order');
		$data['action_add'] 	= base_url('admin/amazon_order_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_amazon_order');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/amazon_order_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/amazon_order/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function amazon_order_form($param1 = null, $param2 = null) {
        $this->load->model('event/EventOrderAmazon_model', 'event_order_amazon_model');
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_amazon_order');
			$data['action'] 						= base_url('admin/amazon_order/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('edit_amazon_order');
			$data['action'] 						= base_url('admin/amazon_order/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->event_order_amazon_model->get($param2);
			$event_info 							= $this->event_model->get($info['event_id']);
			$user_info 							    = $this->user_model->get($info['user_id'] ?? 0);
            $book_info								= $this->book_model->get($info['book_id']);
            $currency_info                          = $this->currency_model->get($info['currency_id']);

			$event_name							 	= ($info['event_id'] == 0) ? 'Generic' : $event_info['name'];
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
				'label' => $event_name ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'user_id',
			'label'		=> _l('select_user'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['user_id'] ?? '',
				'label' => !empty($info['user_id']) ? sprintf('%s %s (%s)', $user_info['first_name'], $user_info['last_name'], $user_info['email']) : '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_students'),
		];

       $data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'book_id',
			'label'		=> _l('select_book'),
			'required'	=> false,
			'value'		=> [
				'value' => $info['book_id'] ?? '',
				'label' => $book_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_books'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'price',
			'label'		=> _l('amount'),
			'required'	=> true,
			'value'		=> $info['price'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'quantity',
			'label'		=> _l('book_sold'),
			'value'		=> $info['quantity'] ?? '',
			'required'	=> false,
		];

        $data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'currency_id',
			'label'		=> _l('currency'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['currency_id'] ?? '',
				'label' => $currency_info['code'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_currency'),
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_amazon_order() {
        $this->load->model('event/EventOrderAmazon_model', 'event_order_amazon_model');
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->event_order_amazon_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info     = $this->event_model->get($result['event_id']);
            $customer_info  = $this->user_model->get($result['user_id']);
            $book_info      = $this->book_model->get($result['book_id']);
            $currency_info  = $this->currency_model->get($result['currency_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event_id'				=> $result['event_id'],
				'event'					=> $event_info['name'] ?? '',
				'customer'				=> (!empty($customer_info)) ? $customer_info['first_name'] . ' ' . $customer_info['last_name'] . '<br /><small>' . $customer_info['email'] . '<br />' . $customer_info['mobile'] . '</small><br />' : '',
				'product'				=> (!empty($book_info)) ? $book_info['name'] . '<br /><small>' .'by '. $book_info['author_name'] ?? $customer_info['first_name'] . '<br />' : '',
                'amount'                => $result['price'],
				'book_sold'				=> $result['quantity'],
                'currency_code'         => $currency_info['code'],
				'date_added'			=> formatDate($result['date_added']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}
}
