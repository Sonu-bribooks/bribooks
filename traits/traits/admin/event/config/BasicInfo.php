<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BasicInfo {
	private function _getBasicInfo($data = []) {
		$stage 				= $data['stage'] ?? 'basic_info';
		$info 				= $data['info'] ?? [];
		$country_info 		= $data['country_info'] ?? [];
		$currency_info 		= $data['currency_info'] ?? [];
		$event_type_info 	= $data['event_type_info'] ?? [];

		if (!empty($info['buying_options'])) {
			$info['buying_options'] = json_decode($info['buying_options'], true);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'slug',
			'label'		=> _l('slug'),
			'required'	=> true,
			'value'		=> $info['slug'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'label',
			'label'		=> _l('label'),
			'required'	=> true,
			'value'		=> $info['label'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'country_id',
			'label'		=> _l('country'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['country_id'] ?? '',
				'label' => $country_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_country'),
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

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_type_id',
			'label'		=> _l('event_type'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['event_type_id'] ?? '',
				'label' => $event_type_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_event_types'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'start_date',
			'label'		=> _l('start_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'end_date',
			'label'		=> _l('end_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_reg_start_date',
			'label'		=> _l('school_reg_start_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['school_reg_start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_reg_end_date',
			'label'		=> _l('school_reg_end_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['school_reg_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'student_reg_start_date',
			'label'		=> _l('student_reg_start_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['student_reg_start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'student_reg_end_date',
			'label'		=> _l('student_reg_end_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['student_reg_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'book_writing_start_date',
			'label'		=> _l('book_writing_start_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['book_writing_start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'book_writing_end_date',
			'label'		=> _l('book_writing_end_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['book_writing_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'selling_start_date',
			'label'		=> _l('selling_start_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['selling_start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'selling_end_date',
			'label'		=> _l('selling_end_date'),
			'required'	=> true,
			'datetime'	=> true,
			'value'		=> $info['selling_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'has_eap',
			'label'		=> _l('has_early_access'),
			'required'	=> true,
			'value'		=> $info['has_eap'] ?? 0,
			'target'	=> 'eap',
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
			'type'		=> 'text',
			'key'		=> 'school_reg_eap_start_date',
			'label'		=> _l('school_reg_eap_start_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'eap',
			'value'		=> $info['school_reg_eap_start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_reg_eap_end_date',
			'label'		=> _l('school_reg_eap_end_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'eap',
			'value'		=> $info['school_reg_eap_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'student_reg_eap_start_date',
			'label'		=> _l('student_reg_eap_start_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'eap',
			'value'		=> $info['student_reg_eap_start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'student_reg_eap_end_date',
			'label'		=> _l('student_reg_eap_end_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'eap',
			'value'		=> $info['student_reg_eap_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'book_writing_eap_start_date',
			'label'		=> _l('book_writing_eap_start_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'eap',
			'value'		=> $info['book_writing_eap_start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'book_writing_eap_end_date',
			'label'		=> _l('book_writing_eap_end_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'eap',
			'value'		=> $info['book_writing_eap_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'selling_eap_start_date',
			'label'		=> _l('selling_eap_start_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'eap',
			'value'		=> $info['selling_eap_start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'selling_eap_end_date',
			'label'		=> _l('selling_eap_end_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'eap',
			'value'		=> $info['selling_eap_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'has_prime',
			'label'		=> _l('has_prime'),
			'required'	=> true,
			'target'	=> 'prime',
			'value'		=> $info['has_prime'] ?? 0,
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
			'type'		=> 'text',
			'key'		=> 'school_reg_prime_start_date',
			'label'		=> _l('school_reg_prime_start_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'prime',
			'value'		=> $info['school_reg_prime_start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_reg_prime_end_date',
			'label'		=> _l('school_reg_prime_end_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'prime',
			'value'		=> $info['school_reg_prime_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'student_reg_prime_start_date',
			'label'		=> _l('student_reg_prime_start_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'prime',
			'value'		=> $info['student_reg_prime_start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'student_reg_prime_end_date',
			'label'		=> _l('student_reg_prime_end_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'prime',
			'value'		=> $info['student_reg_prime_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'book_writing_prime_start_date',
			'label'		=> _l('book_writing_prime_start_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'prime',
			'value'		=> $info['book_writing_prime_start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'book_writing_prime_end_date',
			'label'		=> _l('book_writing_prime_end_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'prime',
			'value'		=> $info['book_writing_prime_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'selling_prime_start_date',
			'label'		=> _l('selling_prime_start_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'prime',
			'value'		=> $info['selling_prime_start_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'selling_prime_end_date',
			'label'		=> _l('selling_prime_end_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'prime',
			'value'		=> $info['selling_prime_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'has_exhibition',
			'label'		=> _l('has_exhibition'),
			'required'	=> true,
			'target'	=> 'exhibition',
			'value'		=> $info['has_exhibition'] ?? 0,
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
			'type'		=> 'text',
			'key'		=> 'national_awards_exhibition_end_date',
			'label'		=> _l('national_awards_exhibition_end_date'),
			'required'	=> false,
			'datetime'	=> true,
			'group'		=> 'exhibition',
			'value'		=> $info['national_awards_exhibition_end_date'] ?? date('m/d/Y h:i:s A'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'		=> $info['status'] ?? 1,
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

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'publishing_limit',
			'label'		=> _l('publishing_limit'),
			'required'	=> true,
			'value'		=> $info['publishing_limit'] ?? 10,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'force_enrol',
			'label'		=> _l('select_force_enrol'),
			'required'	=> true,
			'value'		=> $info['force_enrol'] ?? 0,
			'options'	=> [
				[
					'label'	=> _l('0').'('._l('no_action').')',
					'value'	=> 0,
				],
				[
					'label'	=> _l('1') .'('._l('backend_enrol_only').')',
					'value'	=> 1,
				],
				[
					'label'	=> _l('2').'('._l('frontend_enrol_only').')',
					'value'	=> 2,
				],
				[
					'label'	=> _l('3').'('._l('both_enrol').')',
					'value'	=> 3,
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'checkbox',
			'key'		=> 'school_medallion_ids[]',
			'label'		=> _l('school_medallions'),
			'required'	=> false,
			'value'		=> $info['school_medallion_ids'] ?? [],
			'options'	=> array_map(function ($item) {
				return [
					'label'	=> $item['name'],
					'value'	=> $item['id'],
				];
			}, $this->medallion_model->get_all(['type' => 'school', 'order' => 'ASC'])['rows'] ?? []),
		];

		$data['fields'][] = [
			'type'		=> 'checkbox',
			'key'		=> 'buying_options[]',
			'label'		=> _l('buying_options'),
			'required'	=> false,
			'value'		=> $info['buying_options'] ?? [],
			'options'	=> [
				[
					'label'	=> _l('printed'),
					'value'	=> 'printed'
				],
				[
					'label'	=> _l('black_white'),
					'value'	=> 'black_white'
				],
				[
					'label'	=> _l('amazon'),
					'value'	=> 'amazon'
				],
				[
					'label'	=> _l('ebook'),
					'value'	=> 'ebook'
				],
				[
					'label'	=> _l('audio_book'),
					'value'	=> 'audio_book'
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'checkbox',
			'key'		=> 'genre_ids[]',
			'label'		=> _l('genre'),
			'required'	=> false,
			'value'		=> $info['genre_ids'] ?? [],
			'options'	=> array_map(function ($item) {
				return [
					'label'	=> $item['name'],
					'value'	=> $item['id'],
				];
			}, $this->genre_model->get_all(['parent_id' => 0, 'order' => 'ASC'])['rows'] ?? []),
		];

		$data['action'] = !empty($info)
			? base_url('admin/ajax_event_basic_info_crud/edit/' . $info['id'])
			: base_url('admin/ajax_event_basic_info_crud/add')
		;

		$fields = $this->load->view('backend/admin/event/stage/generic', $data, true);

		$this->load->view(sprintf('backend/admin/event/stage/%s', $stage), compact('fields'));
	}

	public function ajax_event_basic_info_crud($action = NULL, $id = 0) {
		$this->json = [];

		if ($action == 'add') {
			self::_validateEventBasicInfoForm();

			if (empty($this->json['errors'])) {
				$data = $this->input->post();

				if (is_array($data['genre_ids'])) {
					$data['genre_ids'] = implode(',', $data['genre_ids']);
				} else {
					$data['genre_ids'] = '';
				}

				if (is_array($data['school_medallion_ids'])) {
					$data['school_medallion_ids'] = implode(',', $data['school_medallion_ids']);
				} else {
					$data['school_medallion_ids'] = '';
				}
				
				$data['buying_options'] = json_encode($data['buying_options']);

				$direct_site_info = $this->site_model->get_all([
					'name' 			=> 'Direct Online Registration',
					'country_code' 	=> $data['country_code'],
				])['rows'][0] ?? '';
				$data['direct_site_id'] = $direct_site_info['id'];
				$data['url'] = USER_YAF_URL;

				$event_id = $this->event_model->add($data);
				$this->json['redirect'] = base_url('admin/event_form/edit/' . $event_id . '/config');
			}
		} elseif ($action == 'edit') {
			self::_validateEventBasicInfoForm($id);

			if (empty($this->json['errors'])) {
				$data = $this->input->post();

				if (is_array($data['genre_ids'])) {
					$data['genre_ids'] = implode(',', $data['genre_ids']);
				} else {
					$data['genre_ids'] = '';
				}

				if (is_array($data['school_medallion_ids'])) {
					$data['school_medallion_ids'] = implode(',', $data['school_medallion_ids']);
				} else {
					$data['school_medallion_ids'] = '';
				}

				$data['buying_options'] = json_encode($data['buying_options']);

				$direct_site_info = $this->site_model->get_all([
					'name' 			=> 'Direct Online Registration',
					'country_code' 	=> $data['country_code'],
				])['rows'][0] ?? '';
				$data['direct_site_id'] = $direct_site_info['id'] ?? 0;

				$this->event_model->edit($id, $data);
			}
		}

		if (!empty($this->json['errors'])) {
			$this->json['error'] = _l('error_occured');
		} else {
			$this->json['success'] = _l('success');
		}

		output_json($this->json);
	}

	private function _validateEventBasicInfoForm($id = 0) {
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('slug', _l('slug'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('label', _l('label'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('country_id', _l('country_id'), [
			'trim',
			'required',
			'numeric',
			['country', [$this->validate_model, 'country']]
		]);
		$this->form_validation->set_rules('currency_id', _l('currency'), [
			'trim',
			'required',
		]);
		$this->form_validation->set_rules('event_type_id', _l('event_type_id'), [
			'trim',
			'required',
			'numeric',
			['event_type', [$this->admin_validate_model, 'event_type']]
		]);

		empty($id) && $this->form_validation->set_rules('start_date', _l('start_date'), [
			'trim',
			'required',
			['start_date', [$this->admin_validate_model, 'event_start_date']]
		]);

		$this->form_validation->set_rules('end_date', _l('end_date'), [
			'trim',
			'required',
			['end_date', [$this->admin_validate_model, 'event_end_date']]
		]);

		$this->form_validation->set_rules('student_reg_start_date', _l('student_reg_start_date'), [
			'trim',
			'required',
			['student_reg_start_date', [$this->admin_validate_model, 'event_student_reg_start_date']]
		]);

		$this->form_validation->set_rules('student_reg_end_date', _l('student_reg_end_date'), [
			'trim',
			'required',
			['student_reg_end_date', [$this->admin_validate_model, 'event_student_reg_end_date']]
		]);

		$this->form_validation->set_rules('school_reg_start_date', _l('school_reg_start_date'), [
			'trim',
			'required',
			['school_reg_start_date', [$this->admin_validate_model, 'event_school_reg_start_date']]
		]);

		$this->form_validation->set_rules('school_reg_end_date', _l('school_reg_end_date'), [
			'trim',
			'required',
			['school_reg_end_date', [$this->admin_validate_model, 'event_school_reg_end_date']]
		]);

		$this->form_validation->set_rules('book_writing_start_date', _l('book_writing_start_date'), [
			'trim',
			'required',
			['book_writing_start_date', [$this->admin_validate_model, 'event_book_writing_start_date']]
		]);

		$this->form_validation->set_rules('book_writing_end_date', _l('book_writing_end_date'), [
			'trim',
			'required',
			['book_writing_end_date', [$this->admin_validate_model, 'event_book_writing_end_date']]
		]);

		$this->form_validation->set_rules('selling_start_date', _l('selling_start_date'), [
			'trim',
			'required',
			['selling_start_date', [$this->admin_validate_model, 'event_selling_start_date']]
		]);

		$this->form_validation->set_rules('selling_end_date', _l('selling_end_date'), [
			'trim',
			'required',
			['selling_end_date', [$this->admin_validate_model, 'event_selling_end_date']]
		]);

		$this->form_validation->set_rules('has_eap', _l('has_eap'), 'trim|required|numeric|in_list[0,1]');
		$this->form_validation->set_rules('has_prime', _l('has_prime'), 'trim|required|numeric|in_list[0,1]');
		$this->form_validation->set_rules('has_exhibition', _l('has_exhibition'), 'trim|required|numeric|in_list[0,1]');
		$this->form_validation->set_rules('status', _l('status'), 'trim|required|numeric|in_list[0,1]');

		if ($this->input->post('has_eap')) {
			$this->form_validation->set_rules('student_reg_eap_start_date', _l('student_reg_eap_start_date'), [
				'trim',
				'required',
				['student_reg_eap_start_date', [$this->admin_validate_model, 'event_student_reg_eap_start_date']]
			]);

			$this->form_validation->set_rules('student_reg_eap_end_date', _l('student_reg_eap_end_date'), [
				'trim',
				'required',
				['student_reg_eap_end_date', [$this->admin_validate_model, 'event_student_reg_eap_end_date']]
			]);

			$this->form_validation->set_rules('school_reg_eap_start_date', _l('school_reg_eap_start_date'), [
				'trim',
				'required',
				['school_reg_eap_start_date', [$this->admin_validate_model, 'event_school_reg_eap_start_date']]
			]);

			$this->form_validation->set_rules('school_reg_eap_end_date', _l('school_reg_eap_end_date'), [
				'trim',
				'required',
				['school_reg_eap_end_date', [$this->admin_validate_model, 'event_school_reg_eap_end_date']]
			]);

			$this->form_validation->set_rules('book_writing_eap_start_date', _l('book_writing_eap_start_date'), [
				'trim',
				'required',
				['book_writing_eap_start_date', [$this->admin_validate_model, 'event_book_writing_eap_start_date']]
			]);

			$this->form_validation->set_rules('book_writing_eap_end_date', _l('book_writing_eap_end_date'), [
				'trim',
				'required',
				['book_writing_eap_end_date', [$this->admin_validate_model, 'event_book_writing_eap_end_date']]
			]);

			$this->form_validation->set_rules('selling_eap_start_date', _l('selling_eap_start_date'), [
				'trim',
				'required',
				['selling_eap_start_date', [$this->admin_validate_model, 'event_selling_eap_start_date']]
			]);

			$this->form_validation->set_rules('selling_eap_end_date', _l('selling_eap_end_date'), [
				'trim',
				'required',
				['selling_eap_end_date', [$this->admin_validate_model, 'event_selling_eap_end_date']]
			]);
		}

		if ($this->input->post('has_prime')) {
			$this->form_validation->set_rules('student_reg_prime_start_date', _l('student_reg_prime_start_date'), [
				'trim',
				'required',
				['student_reg_prime_start_date', [$this->admin_validate_model, 'event_student_reg_prime_start_date']]
			]);

			$this->form_validation->set_rules('student_reg_prime_end_date', _l('student_reg_prime_end_date'), [
				'trim',
				'required',
				['student_reg_prime_end_date', [$this->admin_validate_model, 'event_student_reg_prime_end_date']]
			]);

			$this->form_validation->set_rules('school_reg_prime_start_date', _l('school_reg_prime_start_date'), [
				'trim',
				'required',
				['school_reg_prime_start_date', [$this->admin_validate_model, 'event_school_reg_prime_start_date']]
			]);

			$this->form_validation->set_rules('school_reg_prime_end_date', _l('school_reg_prime_end_date'), [
				'trim',
				'required',
				['school_reg_prime_end_date', [$this->admin_validate_model, 'event_school_reg_prime_end_date']]
			]);

			$this->form_validation->set_rules('book_writing_prime_start_date', _l('book_writing_prime_start_date'), [
				'trim',
				'required',
				['book_writing_prime_start_date', [$this->admin_validate_model, 'event_book_writing_prime_start_date']]
			]);

			$this->form_validation->set_rules('book_writing_prime_end_date', _l('book_writing_prime_end_date'), [
				'trim',
				'required',
				['book_writing_prime_end_date', [$this->admin_validate_model, 'event_book_writing_prime_end_date']]
			]);

			$this->form_validation->set_rules('selling_prime_start_date', _l('selling_prime_start_date'), [
				'trim',
				'required',
				['selling_prime_start_date', [$this->admin_validate_model, 'event_selling_prime_start_date']]
			]);

			$this->form_validation->set_rules('selling_prime_end_date', _l('selling_prime_end_date'), [
				'trim',
				'required',
				['selling_prime_end_date', [$this->admin_validate_model, 'event_selling_prime_end_date']]
			]);
		}

		if ($this->input->post('has_exhibition')) {
			$this->form_validation->set_rules('national_awards_exhibition_end_date', _l('national_awards_exhibition_end_date'), [
				'trim',
				'required',
				['national_awards_exhibition_end_date', [$this->admin_validate_model, 'event_national_awards_exhibition_end_date']]
			]);

		}

		$data = $this->input->post();

		if (!empty($data['country_id']) && ($country_info = $this->country_model->get($data['country_id']))) {
			$_POST['country_code'] = $country_info['code'];
		}

		if (!empty($data['currency_id']) && ($currency_info = $this->currency_model->get($data['currency_id']))) {
			$_POST['currency_code'] = $currency_info['code'];
		}

		foreach ($data as $key => $item) {
			if (strpos($key, '_date') !== false) {
				$_POST[$key] = $item ? date('Y-m-d H:i:s', strtotime($item)) : null;
			}
		}

		$valid = $this->form_validation->run();

		!$valid && ($this->json['errors'] = $this->form_validation->error_array());
	}
}
