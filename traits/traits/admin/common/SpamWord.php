<?php defined('BASEPATH') or exit('No direct script access allowed');

trait SpamWord {
	public function spam_word($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'word',
			'actions',
		];

		if ($param1 == 'add') {
			$this->spam_word_model->add($this->input->post());
			redirect(base_url('admin/spam_word'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->spam_word_model->edit($param2, $this->input->post());
			redirect(base_url('admin/spam_word'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->spam_word_model->delete($param2);
			redirect(base_url('admin/spam_word'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('spam_word');
		$data['action_add'] 	= base_url('admin/spam_word_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_spam_word');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/spam_word_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/spam_word/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function spam_word_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_spam_word');
			$data['action'] 						= base_url('admin/spam_word/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('spam_word');
			$data['action'] 						= base_url('admin/spam_word/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->spam_word_model->get($param2);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'word',
			'label'		=> _l('word'),
			'required'	=> true,
			'value'		=> $info['word'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_spam_word() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->spam_word_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$parent_info = $this->spam_word_model->get($result['parent_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'word'					=> $result['word'],
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function ajax_search_spam_word() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->spam_word_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['word'],
			];
		}

		output_json($json);
	}
}
