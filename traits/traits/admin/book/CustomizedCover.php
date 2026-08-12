<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait CustomizedCover {
	public function customized_cover($param1 = NULL, $param2 = NULL,$param3 = NULL, $param4 = NULL) {
        $this->load->model('book/CustomCoverReview_model' , 'custom_cover_review_model');
        $this->load->model('book/CustomCoverReviewLog_model' , 'custom_cover_review_log_model');

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
			$custom_cover_review = $this->custom_cover_review_model->get_all([
				'book_id' 			=> $param2,
				'version' 			=> $param3,
				'book_version_id' 	=> $param4
			])['rows'][0] ?? '';
           
			if (!empty($custom_cover_review)) {
				$this->custom_cover_review_model->edit($custom_cover_review['id'], [
					'manager_id' 		=> (int)$this->session->userdata('user_id'),
					'book_id' 			=> $param2,
					'version' 			=> $param3,
					'book_version_id' 	=> $param4,
					'status'			=> 1
				]);
			} else {
				$this->custom_cover_review_model->add([
					'manager_id' 		=> (int)$this->session->userdata('user_id'),
					'book_id' 			=> $param2,
					'version' 			=> $param3,
					'book_version_id' 	=> $param4,
					'status'		=> 1
				]);
			}

			$this->custom_cover_review_log_model->add([
				'manager_id' 	=> (int)$this->session->userdata('user_id'),
				'book_id' 		=> $param2,
				'version' 		=> $param3,
				'status'		=> 1
			]);

			redirect(base_url('admin/customized_cover'), 'refresh');
		} elseif ($param1 == 'view') {
			$histories = $this->custom_cover_review_log_model->get_all([
                'book_id' => $param2,
				'version' => $param3
			])['rows'] ?? [];

			$data['histories'] 		= $histories;
			$data['page_name'] 		= 'books/custom_cover_review_log';
			$data['page_title'] 	= _l('custom_cover_review_log');
			redirect(base_url('admin/customized_cover'), 'refresh');
		}

		$data['page_name'] 		= 'books/customized_cover';
		$data['page_title'] 	= _l('customized_cover');
		$data['action_ajax'] 	= base_url('admin/ajax_customized_cover');

		$data['actions'] 		= [
			[
				'key'	=> 'accept',
				'type' 	=> 'accept',
				'url'	=> 'admin/customized_cover/accept/',
            ],
            [
				'key'	=> 'reject',
				'type' 	=> 'reject',
				'url'	=> '',
			]
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_customized_cover() {
        $this->load->model('book/CustomCoverReview_model' , 'custom_cover_review_model');
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				    => (int)$this->input->get('start'),
			'limit'				    => (int)$this->input->get('length'),
			'search'			    => $this->input->get('search[value]'),
			'sort'				    => $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				    => mb_strtoupper($this->input->get('order[0][dir]')),
		];
        
		$results = $this->custom_cover_model->getCustomCoverOrderedBook($filter_data);
    
		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$orders_product = $this->order_product_model->get_all([
				'product_id' 	=> $result['id'],
				'order_status' 	=> 1
			])['rows'][0] ?? '';

			$order_flag = false;	
            if ($orders_product) {
				$order_info = $this->order_model->get_all([
					'order_ids' 	=> $orders_product['order_id'],
				])['rows'][0] ?? '';

				$order_flag = (!empty($order_info)) ? true : false;
			}

			$category_info = $this->category_model->get($result['category_id']);
			$user_info = $this->student_model->get($result['user_id']);
			$pages = $this->page_version_model->get_all([
				'book_id' => $result['id'],
				'version' => $result['version']
			])['total'];

			$custom_cover_review  = $this->custom_cover_review_model->get_all([
				'book_id'		=> $result['id'],
				'version'		=> $result['version'],
			])['rows'][0] ?? '';
        
			if (!empty($custom_cover_review) && ($custom_cover_review['status'] == 1)) {
				$status = '<i class="mdi mdi-circle" style="color: #4CAF50; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="Accept"></i>' ;
			} elseif (!empty($custom_cover_review) && ($custom_cover_review['status'] == 2)) {
				$status = '<i class="mdi mdi-circle" style="color: #f12706; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="Reject"></i>' ;
			} else {
				$status = '<i class="mdi mdi-circle" style="color: #f1ee2d; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="Pending"></i>' ;
			}

			$json['data'][] = [
				'sn'				=> $key + 1,
				'id'				=> $result['id'],
				'theme'				=> $category_info['name'],
				'user'				=> ($user_info['first_name'] ?? '') . ' ' . ($user_info['last_name'] ?? ''),
				'country'			=>  $user_info['location'] ?? '',
				'name'				=> isset($result['isbn']) ? sprintf('%s VERSION :%s ISBN: %s', $result['name'], $result['version'], $result['isbn']) . ($result['status'] == 1 ? (vsprintf('<br><a href="%s" class="btn btn-sm btn-danger">%s</a>&nbsp;<a href="%s" class="btn btn-sm btn-info">%s</a>&nbsp;<a href="%s" class="btn btn-sm btn-dark">%s</a>', [
					base_url('admin/printBook/' . $result['id'] . '/' . $result['version']),
					_li('PDF'),
					base_url('admin/printKdpBook/' . $result['id'] . '/' . $result['version']),
					_li('KDP'),
					base_url('admin/printGreyBook/' . $result['id'] . '/' . $result['version']),
					_li('BW'),
				])) : '') : "",
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
                    <a class="dropdown-item accept_book" href="'.base_url('admin/customized_cover/accept/' . $result['id']. '/' . $result['version']. '/' . $result['book_version_id']).'" book_status="1" book_id='.$result['id'].' >Accept</a>
                    </li>
                    <li>
                    <a class="dropdown-item reject_custom_cover" href="#" book_version='.$result['version'].' book_id='.$result['id'].' book_version_id='.$result['book_version_id'].' data-target="#customCoverReject">Reject</a>
                    </li>
					<li>
                    <a class="dropdown-item review_book" href="'.base_url('admin/reviewbook/' . $result['id']. '/' . $result['version']).'" book_status="1" book_id='.$result['id'].' >Review</a>
                    </li>
					<li>
                    <a class="dropdown-item view_book" href="'.base_url('admin/customized_cover_form/view/' . $result['id']).'" book_status="1" book_id='.$result['id'].' >View</a>
                    </li>
                    </ul></div>',
				'is_flag'		=> $order_flag,
			];
		}

		output_json($json);
	}

    public function add_custom_cover_comment() {
        $this->load->model('book/CustomCoverReview_model' , 'custom_cover_review_model');
        $this->load->model('book/CustomCoverReviewLog_model' , 'custom_cover_review_log_model');
        
		$json = [];

		$book_info = $this->book_model->get($this->input->post('book_id'));
        
        if (!empty($this->input->post('book_id')) || !empty($book_info)) {

			$book_review_info = $this->custom_cover_review_model->get_all([
				'book_id' 		=> $this->input->post('book_id'),
				'version' 		=> $this->input->post('version'),
				'book_version_id' 		=> $this->input->post('book_version_id'),
			])['rows'][0] ?? '';
           
			if (!empty($book_review_info)) {
				$this->custom_cover_review_model->edit($book_review_info['id'], [
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'book_id' 		=> $this->input->post('book_id'),
					'version' 		=> $this->input->post('version'),
					'comment' 		=> $this->input->post('comment'),
					'book_version_id' 		=> $this->input->post('book_version_id'),
					'status'		=> 2
				]);
			} else {
				$this->custom_cover_review_model->add([
					'manager_id' 	=> (int)$this->session->userdata('user_id'),
					'book_id' 		=> $this->input->post('book_id'),
					'version' 		=> $this->input->post('version'),
					'comment' 		=> $this->input->post('comment'),
					'book_version_id' 		=> $this->input->post('book_version_id'),
					'status'		=> 2
				]);
			}

			$this->custom_cover_review_log_model->add([
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
			$this->book_model->edit($this->input->post('book_id'), [
				'status' => 2
			]);

			$this->bookstore_model->editByBookId($this->input->post('book_id'), [
				'status' => 2
			]);

			$this->session->set_flashdata('flash_message', _l('order_comment_added'));
		} else {
			$this->session->set_flashdata('error_message', _l('order_not_found'));
		}
		redirect(base_url('admin/customized_cover'), 'refresh');
	}

    public function customized_cover_form($param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$this->load->model('book/CustomCoverReviewLog_model' , 'custom_cover_review_log_model');

		if ($param1 == 'view') {
			$histories = $this->custom_cover_review_log_model->get_all([
				'book_id' => $param2,
				'verion'  => $param3
			])['rows'] ?? [];

			$data['id'] 			= (int)$param2;
			$data['histories'] 		= $histories;
			$data['page_name'] 		= 'books/custom_cover_review_log';
			$data['page_title'] 	= _l('custom_cover_review_log');
		}

		$this->load->view('backend/index', $data);
	}
}
