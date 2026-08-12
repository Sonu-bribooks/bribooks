<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ReviewFlag {
	public function review_flag($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'review_id',
			'book_name',
			'reviewer_name',
			'comment',
			'actions',
		];

		if ($param1 == 'delete') {
			$this->review_model->delete($param2);
			redirect(base_url('admin/review_flag'), 'refresh');
		}

		$data['page_name'] 		= 'review_flag/index';
		$data['page_title'] 	= _l('review_flag');
		$data['action_ajax'] 	= base_url('admin/ajax_review_flag');

		$data['actions'] 		= [
			[
				'key'	=> 'view',
				'url'	=> 'admin/review_flag_form/view/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/review_flag/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function review_flag_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'view') {
			$data['page_name'] 						= 'review_flag/view';
			$data['page_title'] 					= _l('review_flag_view');

			$data['id'] 							= (int)$param2;
			$data['info'] 							= $this->review_model->get($param2) ?? [];
			// pr($info);
			$data['review_flags'] 					= $this->review_flag_model->get_all([
				'review_id' => $param2,
				'sort' 		=> 'review_flags.date_added'
			])['rows'] ?? [];
			// pr($review_flags);die;
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_review_flag() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'review_flag'		=> 1,
		];
		
		$results = $this->review_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$book_info = $this->book_model->get($result['book_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'review_id'				=> $result['id'],
				'book_name'				=> $book_info['name'] ?? 'NA',
				'reviewer_name'			=> $result['author'] ?? 'NA',
				'comment'				=> $result['text'] ?? '',
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}
}
