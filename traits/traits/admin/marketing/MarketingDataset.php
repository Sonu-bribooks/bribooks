<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait MarketingDataset {
	public $blacklist = [
		'describe',
		'show',
		'drop',
		'delete',
		'update',
		'insert',
		'replace',
		'truncate',
		'alter',
		'create',
		'rename',
		'commit',
		'rollback',
		'grant',
		'revoke',
		'save point',
		'set transaction',
		'settings',
		'user_token',
		'system_access_log',
		'role',
		'access_log',
		'bb_shipping_rate',
		'bot_logs',
		'user_bank',
	];

	public function marketing_dataset($param1='', $param2=''){
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'sql_query',
			'actions',
		];

		if ($this->input->post('sql_query')) {
			$query = trim(html_entity_decode($this->input->post('sql_query'), ENT_QUOTES, 'UTF-8'));

			foreach ($this->blacklist as $word) {
				if (preg_match('/\b' . preg_quote($word) . '\b/ims', strtolower($query))) {
					$this->session->set_flashdata('error_message', _l('Unauthorized SQL operation detected'));

					redirect(base_url('admin/marketing_dataset'), 'refresh');
				}
			}
		}

		$attachment_query = '';
		
		if ($this->input->post('attachment_query')) {
			$attachment_query = trim(html_entity_decode($this->input->post('attachment_query'), ENT_QUOTES, 'UTF-8'));

			foreach ($this->blacklist as $word) {
				if (preg_match('/\b' . preg_quote($word) . '\b/ims', strtolower($query))) {
					$this->session->set_flashdata('error_message', _l('Unauthorized SQL operation detected'));

					redirect(base_url('admin/marketing_dataset'), 'refresh');
				}
			}
		}

		if ($param1 == 'add') {
			$check_name = $this->marketing_dataset_model->get_all([
				'name' => $this->input->post('name')
			])['rows'] ?? [];

			if (!empty($check_name)) {
				$this->session->set_flashdata('error_message', _l('Name already exists!'));
				redirect(base_url('admin/marketing_dataset'), 'refresh');
			}

			$this->marketing_dataset_model->add([
				'name'				=> $this->input->post('name'),
				'sql_query'			=> $query,
				'attachment_query'	=> $attachment_query,
			]);

			$this->session->set_flashdata('flash_message', _l('Query added successfully!'));

			redirect(base_url('admin/marketing_dataset'), 'refresh');
		} elseif ($param1 == 'edit') {
			$marketing_dataset_info	= $this->marketing_dataset_model->get($param2);

			$this->marketing_dataset_model->edit($param2, [
				'sql_query'		  	=> $query,
				'attachment_query'	=> $attachment_query,
			]);

			redirect(base_url('admin/marketing_dataset'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->marketing_dataset_model->delete($param2);

			redirect(base_url('admin/marketing_dataset'), 'refresh');
		}

		$data['page_name'] 		= 'marketing_dataset/index';
		$data['page_title'] 	= _l('marketing_dataset');
		$data['action_add'] 	= base_url('admin/marketing_dataset_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_marketing_dataset');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/marketing_dataset_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/marketing_dataset/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function marketing_dataset_form($param1 = NULL, $param2 = NULL) {
		$data['page_name'] 		= 'marketing_dataset/form';
		$data['page_title'] 	= _l('marketing_dataset_Add');
		$data['action'] 		= base_url('admin/marketing_dataset/add');

		if ($param1 == 'edit') {
			$data['page_title'] 	= _l('marketing_dataset_edit');
			$data['action'] 		= base_url('admin/marketing_dataset/edit/' . (int)$param2);
			$data['id'] 			= (int)$param2;
			$data['details'] 		= $this->marketing_dataset_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_marketing_dataset() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]') ?? $this->input->get('search'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]') ?? ''),
		];

		$results = $this->marketing_dataset_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'sql_query'				=> $result['sql_query'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function test_query() {
		$json['data'] = [];

		$query = html_entity_decode(trim($this->input->post('sql_query')), ENT_QUOTES, 'UTF-8');

		if (empty($query)) {
			output_json(['error' => _l('SQL query cannot be empty')]);
			return;
		}

		foreach ($this->blacklist as $word) {
			if (preg_match('/\b' . preg_quote($word) . '\b/ims', strtolower($query))) {
				output_json(['error' => 'Unauthorized SQL operation detected']);
				return;
			}
		}

		if (!preg_match('/\sLIMIT\s+\d+$/i', $query)) {
			$query .= " LIMIT 10";
		}

		try {
			$rdb = $this->load->database('replica', TRUE);

			$result = $rdb->query($query);

			if (!$result) {
				throw new Exception($rdb->error()['message']);
			}

			$results = $result->result_array();

			if (strpos(strtolower($query), 'users') !== false) {
				foreach ($results as $key => $item) {
					if (!in_array($item['role_id'], [2, 3, 9])) {
						unset($results[$key]);
					}

					unset($results[$key]['password']);
					unset($results[$key]['verification_code']);
				}
			}

			$json['data'] = $results;
		} catch (Exception $e) {
			$json['error'] = 'SQL Error: ' . $e->getMessage();
		}

		output_json($json);
	}
}
