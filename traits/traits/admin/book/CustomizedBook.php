<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait CustomizedBook {
	public function customized_book($param1 = NULL, $param2 = NULL,$param3 = NULL) {
		$this->load->model('book/CustomThemeBook_model' , 'custom_theme_book_model');
		$this->load->model('book/CustomThemeBookLogs_model' , 'custom_theme_book_logs_model');

		$data['fields'] = [
			'sn',
			'id',
			'theme',
			'user',
			'country',
			'name',
			'author_name',
            'date_added',
			'date_published',
			'date_approved',
			'status',
			'page_count',
			'actions',
		];

		if ($param1 == 'accept') {
			$book_review_info = $this->custom_theme_book_model->get_all([
				'book_id' 		=> $param2,
				'version' 		=> $param3,
			])['rows'][0] ?? '';

			if (!empty($book_review_info)) {
				$this->custom_theme_book_model->edit($book_review_info['id'], [
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'book_id' 		=> $param2,
					'version' 		=> $param3,
					'status'		=> 1
				]);
			} else {
				$this->custom_theme_book_model->add([
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'book_id' 		=> $param2,
					'version' 		=> $param3,
					'status'		=> 1
				]);
			}

			$this->custom_theme_book_logs_model->add([
				'manager_id' 	=> (int)$this->session->userdata('user_id'),
				'book_id' 		=> $param2,
				'version' 		=> $param3,
				'status'		=> 1
			]);

			redirect(base_url('admin/customized_book'), 'refresh');
		} elseif ($param1 == 'view') {
			$histories = $this->custom_theme_book_logs_model->get_all([
				'book_id' => $param2,
				'version' => $param3
			])['rows'] ?? [];

			$data['histories'] 		= $histories;
			$data['page_name'] 		= 'books/custom_theme_book_review_log';
			$data['page_title'] 	= _l('custom_theme_book_review_log');
			redirect(base_url('admin/customized_book'), 'refresh');
		}

		$data['page_name'] 		= 'books/customized_book';
		$data['page_title'] 	= _l('customized_books');
		$data['action_ajax'] 	= base_url('admin/ajax_customized_book');

		$data['actions'] 		= [
			[
				'key'	=> 'accept',
				'type' 	=> 'accept',
				'url'	=> 'admin/customized_book/accept/',
            ],
            [
				'key'	=> 'reject',
				'type' 	=> 'reject',
				'url'	=> '',
			]
		];

		$this->load->view('backend/index', $data);
	}

	public function customized_book_form($param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$this->load->model('book/CustomThemeBookLogs_model' , 'custom_theme_book_logs_model');

		if ($param1 == 'view') {
			$histories = $this->custom_theme_book_logs_model->get_all([
				'book_id' => $param2,
				'verion'  => $param3
			])['rows'] ?? [];

			$data['id'] 			= (int)$param2;
			$data['histories'] 		= $histories;
			$data['page_name'] 		= 'books/custom_theme_book_review_log';
			$data['page_title'] 	= _l('custom_theme_book_review_log');
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_customized_book() {

		$this->load->model('book/BookTitleVerso_model', 'book_title_verso_model');
		$this->load->model('book/CustomThemeBook_model' , 'custom_theme_book_model');

		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
            'status'            => 1,
            'custom_theme'      => 1
		];

		if (!empty($this->input->get('custom_review_status'))) {
			$filter_data['custom_review_status'] = $this->input->get('custom_review_status');
		}

		$results = $this->custom_theme_book_model->getCustomThemeOrderedBook($filter_data);

		// log_kb([
		// 	'custom_theme-query' => $this->db->last_query()
		// ]);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

			$orders = $this->order_product_model->get_all([
				'product_id' 	=> $result['id'],
				'order_status' 	=> 1
			])['rows'][0] ?? '';

			$category_info = $this->category_model->get($result['category_id']);
			$user_info = $this->student_model->get($result['user_id']);
			$pages = $this->page_version_model->get_all([
				'book_id' => $result['id'],
				'version' => $result['version']
			])['total'];

			$custom_theme_book_review  = $this->custom_theme_book_model->get_all([
				'book_id'		=> $result['id'],
				'version'		=> $result['version'],
			])['rows'][0] ?? '';

			if (!empty($custom_theme_book_review) && ($custom_theme_book_review['status'] == 1)) {
				$status = '<i class="mdi mdi-circle" style="color: #4CAF50; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="Accept"></i>' ;
			} elseif (empty($orders) && !empty($custom_theme_book_review) && ($custom_theme_book_review['status'] == 2)) {
				$status = '<i class="mdi mdi-circle" style="color: #f12706; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="Reject"></i>' ;
			} else {
				$status = '<i class="mdi mdi-circle" style="color: #f1ee2d; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="Pending"></i>' ;
			}

			$json['data'][] = [
				'sn'				=> $key + 1,
				'id'				=> $result['id'],
				'theme'				=> $category_info['name'],
				'user'				=> ($user_info['first_name'] ?? '') . ' ' . ($user_info['last_name'] ?? ''),
				'country'			=> $user_info['location'] ?? '',
				'name'				=> sprintf('%s VERSION :%s ISBN: %s', $result['name'], $result['version'], $result['isbn']) . ($result['status'] == 1 ? (vsprintf('<br><a href="%s" class="btn btn-sm btn-danger">%s</a>&nbsp;<a href="%s" class="btn btn-sm btn-info">%s</a>&nbsp;<a href="%s" class="btn btn-sm btn-dark">%s</a>', [
					base_url('admin/printBook/' . $result['id'] . '/' . $result['version']),
					_li('PDF'),
					base_url('admin/printKdpBook/' . $result['id'] . '/' . $result['version']),
					_li('KDP'),
					base_url('admin/printGreyBook/' . $result['id'] . '/' . $result['version']),
					_li('BW'),
				])) : ''),
				'author_name'		=> $result['author_name'],
				'date_added'		=> formatDate($result['date_added']),
				'date_published'	=> formatDate($result['date_published']),
				'date_approved'		=> formatDate($result['date_approved']),
				'status' 			=> $status,
				'page_count' 		=> $pages,
				'actions'			=> '<div class="dropright dropright">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-dots-vertical"></i></button>
                    <ul class="dropdown-menu">
                    <li>
                    <a class="dropdown-item accept_book" href="'.base_url('admin/customized_book/accept/' . $result['id']. '/' . $result['version']).'" book_status="1" book_id='.$result['id'].' >Accept</a>
                    </li>
                    <li>
                    <a class="dropdown-item reject_book" href="#" book_version='.$result['version'].' book_id='.$result['id'].' >Reject</a>
                    </li>
					<li>
                    <a class="dropdown-item review_book" href="'.base_url('admin/reviewbook/' . $result['id']. '/' . $result['version']).'" book_status="1" book_id='.$result['id'].' >Review</a>
                    </li>
					<li>
                    <a class="dropdown-item view_book" href="'.base_url('admin/customized_book_form/view/' . $result['id']).'" book_status="1" book_id='.$result['id'].' >View</a>
                    </li>
                    </ul></div>',
			];
		}

		output_json($json);
	}

	public function add_custom_theme_book_comment() {
		$this->load->model('book/CustomThemeBook_model' , 'custom_theme_book_model');
		$this->load->model('book/CustomThemeBookLogs_model' , 'custom_theme_book_logs_model');

		$json = [];

		$book_info = $this->book_model->get($this->input->post('book_id'));

        if (!empty($this->input->post('book_id')) || !empty($book_info)) {

			$book_review_info = $this->custom_theme_book_model->get_all([
				'book_id' 		=> $this->input->post('book_id'),
				'version' 		=> $this->input->post('version'),
			])['rows'][0] ?? '';

			if (!empty($book_review_info)) {
				$this->custom_theme_book_model->edit($book_review_info['id'], [
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'book_id' 		=> $this->input->post('book_id'),
					'version' 		=> $this->input->post('version'),
					'comment' 		=> $this->input->post('comment'),
					'status'		=> 2
				]);
			} else {
				$this->custom_theme_book_model->add([
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'book_id' 		=> $this->input->post('book_id'),
					'version' 		=> $this->input->post('version'),
					'comment' 		=> $this->input->post('comment'),
					'status'		=> 2
				]);
			}

			$this->custom_theme_book_logs_model->add([
				'manager_id' 	=> (int)$this->session->userdata('user_id'),
				'book_id' 		=> $this->input->post('book_id'),
				'version' 		=> $this->input->post('version'),
				'comment' 		=> $this->input->post('comment'),
				'status'		=> 2
			]);

			$orders = $this->order_product_model->get_all([
				'product_id' 	=> $this->input->post('book_id'),
				'version' 		=> $this->input->post('version'),
				'order_status' 	=> 1
			])['rows'] ?? [];

			foreach ($orders as $order) {
				$this->order_model->edit($order['order_id'], ['status' => 93]);

				$this->order_comment_model->add([
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'order_id' 		=> $order['order_id'],
					'description' 	=> $this->input->post('comment'),
					'status' 		=> 1
				]);
			}

			$this->session->set_flashdata('flash_message', _l('order_comment_added'));
		} else {
			$this->session->set_flashdata('error_message', _l('order_not_found'));
		}
		redirect(base_url('admin/customized_book'), 'refresh');
	}
}
