<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BookRating {
	public function book_rating($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'books/book_rating';
		$data['page_title'] 	= _l('book\'s review');
		$data['action_ajax'] 	= base_url('admin/ajax_book_rating');

		$this->load->view('backend/index', $data);
	}

	public function delete_book_review() {
		$ids = $this->input->post('ids');

		if (!empty($ids)) {
			for ($i = 0; $i < count($ids); $i++) {
				$this->review_model->delete($ids[$i]);
			}
			$this->session->set_flashdata('flash_message', 'Data deleted successfully.');
			$json['status'] = true;
			$json['message'] = _l('review_deleted_successfully');
		} else {
			$json['error'] = _li('Please select at least 1 record');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function ajax_book_rating() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]'))
		];
		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}
		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}
		$results = $this->review_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$book_info = $this->book_model->get($result['book_id']);
			$user_info = $this->student_model->get($result['user_id']);
			$json['data'][] = [
				'result'            => $book_info['name'],
				'sn'				=> '<input type="checkbox" class="select-me" value="' . $result['id'] . '">',
				'id'				=> $result['id'],
				'author'			=> $result['author'],
				'book'				=> '<a href="' . USER_URL . "bookstore/" . $book_info['slug'] . '" target="_blank">' . $book_info['name'] . '</a>',
				'rating'			=> $result['rating'],
				'review'	 		=> $result['text'],
				'date_added'       	=> formatDate($result['date_added']),
				'actions'	=> []
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
