<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CertificateTemplate {
	public function ajax_certificate_template($event_id = 0) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'country_code',
			'medallion',
			'type',
			'book_sold',
			'thumb',
			'has_isbn',
			'has_rank',
			'achievement',
			'status',
			'date_modified',
			'actions',
		];

		$data['actions'] 		= [
			[
				'key'		=> 'edit',
				'type'		=> 'callback',
				'callback'	=> 'form_edit',
				'url'		=> sprintf('admin/ajax_certificate_template_form/%d/', $event_id),
			],
			[
				'key'		=> 'status',
				'type'		=> 'callback',
				'callback'	=> 'form_status',
				'url'		=> sprintf('admin/ajax_certificate_template_crud/%d/status/', $event_id),
			],
			[
				'key'		=> 'delete',
				'type'		=> 'callback',
				'callback'	=> 'form_delete',
				'url'		=> sprintf('admin/ajax_certificate_template_crud/%d/delete/', $event_id),
			],
		];

		$data['page_title'] 	= _l('certificate_template');
		$data['action_form'] 	= base_url(sprintf('admin/ajax_certificate_template_form/%d/', $event_id));
		$data['action_crud'] 	= base_url(sprintf('admin/ajax_certificate_template_crud/%d/', $event_id));
		$data['action_ajax'] 	= base_url(sprintf('admin/ajax_event_certificate_templates/%d/', $event_id));

		$this->load->view('backend/admin/event/certificate_template/index', $data);
	}

	public function ajax_certificate_template_form($event_id = 0, $id = 0) {
		$info 					= !empty($id) ? $this->certificate_template_model->get($id) : [];
		$medallion_info 		= !empty($info['medallion_id']) ? $this->medallion_model->get($info['medallion_id']) : [];
		$certificate_message_template_info = !empty($info['certificate_message_template_id']) ? $this->certificate_message_template_model->get($info['certificate_message_template_id']) : [];
		$certificate_types = array_map(function($item) {
			return [
				'label' => $item['name'],
				'value' => $item['type']
			];
		}, $this->certificate_type_model->get_all()['rows']);

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('type'),
			'required'	=> true,
			'value'		=> $info['type'] ?? '',
			'options'	=> $certificate_types,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'country_code',
			'label'		=> _l('country'),
			'required'	=> true,
			'value'		=> $info['country_code'] ?? 'IN',
			'options'	=> [
				[
					'label'	=> _l('IN'),
					'value'	=> 'IN',
				],
				[
					'label'	=> _l('GE'),
					'value'	=> 'GE',
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'multi_select2',
			'key'		=> 'genre_ids',
			'label'		=> _l('genres'),
			'required'	=> false,
			'value'		=> !empty($info['genre_ids']) ? array_map(function($item) {
				$genre_info = $this->genre_model->get($item);
				return [
					'value' => $item,
					'label' => $genre_info['name'],
				];
			}, json_decode($info['genre_ids'])) : '',
			'ajax_url'	=> base_url('admin/ajax_search_genres'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'medallion_id',
			'label'		=> _l('medallion_id'),
			'required'	=> true,
			'value'		=> [
				'value' => $medallion_info['id'] ?? 0,
				'label' => $medallion_info['name'] ??  _li('NO'),
			],
			'ajax_url'	=> base_url('admin/ajax_search_medallions'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'certificate_message_template_id',
			'label'		=> _l('certificate_message_template_id'),
			'required'	=> false,
			'value'		=> [
				'value' => $certificate_message_template_info['id'] ?? '',
				'label' => $certificate_message_template_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_certificate_message_templates'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'book_sold',
			'label'		=> _l('book_sold'),
			'required'	=> true,
			'value'		=> $info['book_sold'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'image',
			'label'		=> _l('image'),
			'required'	=> true,
			'value'		=> $info['image'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'has_isbn',
			'label'		=> _l('has_isbn'),
			'required'	=> true,
			'value'		=> $info['has_isbn'] ?? '',
			'options'	=> [
				[
					'label'	=> _l('yes'),
					'value'	=> 1,
				],
				[
					'label'	=> _l('no'),
					'value'	=> 0,
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'has_rank',
			'label'		=> _l('has_rank'),
			'required'	=> true,
			'value'		=> $info['has_rank'] ?? '',
			'options'	=> [
				[
					'label'	=> _l('yes'),
					'value'	=> 1,
				],
				[
					'label'	=> _l('no'),
					'value'	=> 0,
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'achievement',
			'label'		=> _l('achievement'),
			'required'	=> true,
			'value'		=> $info['achievement'] ?? '',
			'options'	=> [
				[
					'label'	=> _l('yes'),
					'value'	=> 1,
				],
				[
					'label'	=> _l('no'),
					'value'	=> 0,
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('status'),
			'required'	=> false,
			'value'		=> $info['status'] ?? '0',
			'options'	=> [
				[
					'label'	=> _l('enable'),
					'value'	=> 1,
				],
				[
					'label'	=> _l('disable'),
					'value'	=> 0,
				],
			],
		];

		$data['page_title'] = _l('certificate_template');
		$data['action'] 	= base_url(sprintf('admin/ajax_certificate_template_crud/%d/%s/%d', $event_id, ($id ? 'edit' : 'add'), $id));
		$data['fields'] 	= $this->load->view('backend/admin/event/stage/generic', $data, true);

		$this->load->view('backend/admin/event/certificate_template/form', $data);
	}

	public function ajax_event_certificate_templates($event_id = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'event_id'			=> (int)$event_id,
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->certificate_template_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$medallion_info = $this->medallion_model->get($result['medallion_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'country_code'			=> $result['country_code'],
				'medallion'				=> $medallion_info['name'],
				'type'					=> $result['type'],
				'book_sold'				=> $result['book_sold'],
				'thumb'					=> $this->image_model->thumb($result['image']),
				'has_isbn'				=> _sd($result['has_isbn']),
				'has_rank'				=> _sd($result['has_rank']),
				'achievement'			=> _sd($result['achievement']),
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_certificate_template_crud($event_id = 0, $action = NULL, $id = 0) {
		if ($action == 'add') {
			self::_validateCertificateTemplateForm();

			if (empty($this->json['errors'])) {
				$data = $this->input->post();

				if (is_array($data['genre_ids']) && !empty($data['genre_ids'])) {
					$data['genre_ids'] 	= json_encode($data['genre_ids']);
				} else {
					$data['genre_ids'] = NULL;
				}

				if (!empty($data['achievement'])) {
					$data['status'] 	= 0;
				}

				$data['event_id'] 		= (int)$event_id;

				$this->certificate_template_model->add($data);
			}
		} elseif ($action == 'edit') {
			self::_validateCertificateTemplateForm($id);

			if (empty($this->json['errors'])) {
				$data = $this->input->post();

				if (is_array($data['genre_ids']) && !empty($data['genre_ids'])) {
					$data['genre_ids'] = json_encode($data['genre_ids']);
				} else {
					$data['genre_ids'] = NULL;
				}

				if (!empty($data['achievement'])) {
					$data['status'] 	= 0;
				}

				$data['event_id'] 		= (int)$event_id;

				$this->certificate_template_model->edit($id, $data);
			}
		} elseif ($action == 'status') {
			$this->certificate_template_model->enableDisable($id, $this->input->post());
		} elseif ($action == 'delete') {
			$this->certificate_template_model->delete($id);
		}

		if (!empty($this->json['errors'])) {
			$this->json['error'] = _l('error_occured');
		} else {
			$this->json['success'] = _l('success');
		}

		output_json($this->json);
	}

	private function _validateCertificateTemplateForm($id = 0) {
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('medallion_id', _l('medallion_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required');
		$this->form_validation->set_rules('country_code', _l('country'), 'trim|required');
		$this->form_validation->set_rules('book_sold', _l('min_sold'), 'trim|required|numeric');
		$this->form_validation->set_rules('image', _l('image'), 'trim|required|min_length[3]|max_length[255]');
		$this->form_validation->set_rules('has_isbn', _l('has_isbn'), 'trim|required|numeric|in_list[0,1]');
		$this->form_validation->set_rules('has_rank', _l('has_rank'), 'trim|required|numeric|in_list[0,1]');
		$this->form_validation->set_rules('achievement', _l('achievement'), 'trim|required|numeric|in_list[0,1]');
		$this->form_validation->set_rules('status', _l('status'), 'trim|required|numeric|in_list[0,1]');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['errors'] = $this->form_validation->error_array());
	}
}
