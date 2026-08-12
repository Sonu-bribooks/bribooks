<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Font {
	public function font($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'image',
			'url',
			'tags',
			'status',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$_POST['tags'] = is_array($this->input->post('tags'))
				? implode(',', $this->input->post('tags'))
				: $this->input->post('tags');
			$this->font_model->add($this->input->post());
			redirect(base_url('admin/font'), 'refresh');
		} elseif ($param1 == 'edit') {
			$_POST['tags'] = is_array($this->input->post('tags'))
				? implode(',', $this->input->post('tags'))
				: $this->input->post('tags');
			$this->font_model->edit($param2, $this->input->post());
			redirect(base_url('admin/font'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->font_model->delete($param2);
			redirect(base_url('admin/font'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('font');
		$data['action_add'] 	= base_url('admin/font_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_font');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/font_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/font/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function font_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_font');
			$data['action'] 						= base_url('admin/font/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('font');
			$data['action'] 						= base_url('admin/font/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->font_model->get($param2);
			$parent_info 							= $this->font_model->get($info['parent_id']);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'file',
			'no_file'	=> 'no_doc.png',
			'key'		=> 'url',
			'label'		=> _l('font'),
			'required'	=> true,
			'value'		=> $info['url'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'image',
			'label'		=> _l('image'),
			'required'	=> true,
			'value'		=> $info['image'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'multi_select2',
			'key'		=> 'tags',
			'label'		=> _l('tags'),
			'required'	=> true,
			'value'		=> $info['tags'] ?? '',
			'options'	=> [
				[
					'value' => 'bookName',
					'label' => _l('bookName'),
				],
				[
					'value' => 'authorName',
					'label' => _l('authorName'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'		=> $info['status'] ?? 1,
			'options'	=> [
				[
					'value' => 1,
					'label' => _l('enabled'),
				],
				[
					'value' => 0,
					'label' => _l('disabled'),
				],
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_font() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->font_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'image'					=> sprintf('<img src="%s%s%s" style="height:60px" />', $this->config->item('cloudfront_url'), $this->config->item('s3_user_gallery'), $result['image'] ?? ''),
				'url'					=> $result['url'],
				'tags'					=> $result['tags'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function ajax_search_font() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->font_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
