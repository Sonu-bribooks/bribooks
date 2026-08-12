<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Signup {
	private function _getSignup($data = []) {
		$stage 				= $data['stage'] ?? 'signup';
		$event_info 		= $data['info'] ?? [];
		$country_info 		= $data['country_info'] ?? [];
		$event_type_info 	= $data['event_type_info'] ?? [];

		$event_config_info 	= $this->event_config_model->get_all([
			'event_id'	=> $event_info['id']
		])['rows'][0] ?? [];

		$info 				= $this->event_signup_form_model->get_all([
			'event_id'	=> $event_info['id']
		])['rows'][0] ?? [];

		if (!empty($info['school_form'])) {
			$info['school_form'] = json_decode($info['school_form'], true);
		}

		if (!empty($info['community_school_form'])) {
			$info['community_school_form'] = json_decode($info['community_school_form'], true);
		}

		if (!empty($info['teacher_form'])) {
			$info['teacher_form'] = json_decode($info['teacher_form'], true);
		}

		if (!empty($info['user_form'])) {
			$info['user_form'] = json_decode($info['user_form'], true);
		}

		if (!empty($info['email_link'])) {
			$info['email_link'] = json_decode($info['email_link'], true);
		}

		if (!empty($info['page_info'])) {
			$info['page_info'] = json_decode($info['page_info'], true);
		}

		if (!empty($info['user_landing_page'])) {
			$info['user_landing_page'] = json_decode($info['user_landing_page'], true);
		}

		if (!empty($info['school_landing_page'])) {
			$info['school_landing_page'] = json_decode($info['school_landing_page'], true);
		}

		if (!empty($info['country_otp_validation'])) {
			$info['country_otp_validation'] = json_decode($info['country_otp_validation'], true);
		}

		log_kb([
			'country_otp_validation' => $info['country_otp_validation']
		]);

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_form[school]',
			'label'		=> _l('school_name'),
			'required'	=> true,
			'value'		=> $info['school_form']['school'] ?? _l('school_name'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_form[country]',
			'label'		=> _l('country_field'),
			'required'	=> false,
			'value'		=> $info['school_form']['country'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_form[state]',
			'label'		=> _l('state_field'),
			'required'	=> false,
			'value'		=> $info['school_form']['state'] ?? _l('state'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_form[city]',
			'label'		=> _l('city_field'),
			'required'	=> false,
			'value'		=> $info['school_form']['city'] ?? _l('city'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_form[designation]',
			'label'		=> _l('designation_field'),
			'required'	=> false,
			'value'		=> $info['school_form']['designation'] ?? _l('designation'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_form[school_head]',
			'label'		=> _l('school_head'),
			'required'	=> false,
			'value'		=> $info['school_form']['school_head'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_form[authorized_person]',
			'label'		=> _l('authorized_person'),
			'required'	=> false,
			'value'		=> $info['school_form']['authorized_person'] ?? _l('authorized_person'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_form[email]',
			'label'		=> _l('email'),
			'required'	=> false,
			'value'		=> $info['school_form']['email'] ?? _l('email'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'school_form[mobile]',
			'label'		=> _l('mobile'),
			'required'	=> false,
			'value'		=> $info['school_form']['mobile'] ?? _l('mobile'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'school_form[validation]',
			'label'		=> _l('validation'),
			'required'	=> true,
			'value'		=> $info['school_form']['validation'] ?? 'mobile',
			'options'	=> [
				[
					'label'	=> _l('mobile'),
					'value'	=> 'mobile',
				],
				[
					'label'	=> _l('email'),
					'value'	=> 'email',
				],
				[
					'label'	=> _l('email_link'),
					'value'	=> 'email_link',
				],
			],
		];

		$data['signup_fields']['school_form'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		$data['fields'] = self::_communitySchoolForm($info);

		$data['signup_fields']['community_school_form'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);


		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'teacher_form[site]',
			'label'		=> _l('school_name'),
			'required'	=> true,
			'value'		=> $info['teacher_form']['site'] ?? _l('school_name'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'teacher_form[name]',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['teacher_form']['name'] ?? _l('name'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'teacher_form[country]',
			'label'		=> _l('country_field'),
			'required'	=> false,
			'value'		=> $info['teacher_form']['country'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'teacher_form[state]',
			'label'		=> _l('state_field'),
			'required'	=> false,
			'value'		=> $info['teacher_form']['state'] ?? _l('state'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'teacher_form[city]',
			'label'		=> _l('city_field'),
			'required'	=> false,
			'value'		=> $info['teacher_form']['city'] ?? _l('city'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'teacher_form[grade]',
			'label'		=> _l('grade'),
			'required'	=> true,
			'value'		=> $info['teacher_form']['grade'] ?? _l('grade'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'teacher_form[section]',
			'label'		=> _l('section'),
			'required'	=> false,
			'value'		=> $info['teacher_form']['section'] ?? _l('section'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'teacher_form[email]',
			'label'		=> _l('email'),
			'required'	=> false,
			'value'		=> $info['teacher_form']['email'] ?? _l('email'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'teacher_form[mobile]',
			'label'		=> _l('mobile'),
			'required'	=> false,
			'value'		=> $info['teacher_form']['mobile'] ?? _l('mobile'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'teacher_form[validation]',
			'label'		=> _l('validation'),
			'required'	=> true,
			'value'		=> $info['teacher_form']['validation'] ?? 'mobile',
			'options'	=> [
				[
					'label'	=> _l('mobile'),
					'value'	=> 'mobile',
				],
				[
					'label'	=> _l('email'),
					'value'	=> 'email',
				],
				[
					'label'	=> _l('email_link'),
					'value'	=> 'email_link',
				],
			],
		];

		$data['signup_fields']['teacher_form'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[first_name]',
			'label'		=> _l('first_name'),
			'required'	=> true,
			'value'		=> $info['user_form']['first_name'] ?? _l('first_name'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[last_name]',
			'label'		=> _l('last_name'),
			'required'	=> true,
			'value'		=> $info['user_form']['last_name'] ?? _l('last_name'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[parent_name]',
			'label'		=> _l('parent_name'),
			'required'	=> false,
			'value'		=> $info['user_form']['parent_name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[parent_email]',
			'label'		=> _l('parent_email'),
			'required'	=> false,
			'value'		=> $info['user_form']['parent_email'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[dob]',
			'label'		=> _l('date_of_birth'),
			'required'	=> false,
			'value'		=> $info['user_form']['dob'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[site]',
			'label'		=> _l('school_name'),
			'required'	=> true,
			'value'		=> $info['user_form']['site'] ?? _l('school_name'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[country]',
			'label'		=> _l('country_field'),
			'required'	=> false,
			'value'		=> $info['user_form']['country'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[state]',
			'label'		=> _l('state_field'),
			'required'	=> false,
			'value'		=> $info['user_form']['state'] ?? _l('state'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[city]',
			'label'		=> _l('city_field'),
			'required'	=> false,
			'value'		=> $info['user_form']['city'] ?? _l('city'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[grade]',
			'label'		=> _l('grade'),
			'required'	=> true,
			'value'		=> $info['user_form']['grade'] ?? _l('grade'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[section]',
			'label'		=> _l('section'),
			'required'	=> false,
			'value'		=> $info['user_form']['section'] ?? _l('section'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[email]',
			'label'		=> _l('email'),
			'required'	=> false,
			'value'		=> $info['user_form']['email'] ?? _l('email'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_form[mobile]',
			'label'		=> _l('mobile'),
			'required'	=> false,
			'value'		=> $info['user_form']['mobile'] ?? _l('mobile'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'user_form[validation]',
			'label'		=> _l('validation'),
			'required'	=> true,
			'value'		=> $info['user_form']['validation'] ?? 'mobile',
			'options'	=> [
				[
					'label'	=> _l('mobile'),
					'value'	=> 'mobile',
				],
				[
					'label'	=> _l('email'),
					'value'	=> 'email',
				],
				[
					'label'	=> _l('email_link'),
					'value'	=> 'email_link',
				],
			],
		];

		$data['signup_fields']['user_form'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'email_link[subject]',
			'label'		=> _l('email_link_subject'),
			'required'	=> false,
			'value'		=> $info['email_link']['subject'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'email_link[message]',
			'label'		=> _l('email_link_message'),
			'required'	=> false,
			'value'		=> $info['email_link']['message'] ?? '',
		];

		$data['signup_fields']['email_link'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		$country_values = array_map(function($id) {
			$country_info = $this->country_model->get($id);
			return [
				'value' => $id,
				'label' => $country_info['name'] ?? 'Indonesia'
			];
		}, $info['country_otp_validation']['country'] ?? []);

		$data['fields'][] = [
			'type'		=> 'multi_select2',
			'key'		=> 'country_otp_validation[country]',
			'label'		=> _l('country'),
			'required'	=> true,
			'value'		=> $country_values,
			'ajax_url'	=> base_url('admin/ajax_search_country'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'country_otp_validation[validation]',
			'label'		=> _l('validation'),
			'required'	=> false,
			'value'		=> $info['country_otp_validation']['validation'] ?? '',
			'options'	=> [
				[
					'label'	=> _l('mobile'),
					'value'	=> 'mobile',
				],
				[
					'label'	=> _l('email'),
					'value'	=> 'email',
				],
				[
					'label'	=> _l('email_link'),
					'value'	=> 'email_link',
				],
			],
		];

		$data['signup_fields']['country_otp_validation'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'page_info[school_page_title]',
			'label'		=> _l('school_page_title'),
			'required'	=> true,
			'value'		=> $info['page_info']['school_page_title'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'page_info[school_page_description]',
			'label'		=> _l('school_page_description'),
			'required'	=> true,
			'value'		=> $info['page_info']['school_page_description'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'page_info[community_school_page_title]',
			'label'		=> _l('community_school_page_title'),
			'required'	=> true,
			'value'		=> $info['page_info']['community_school_page_title'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'page_info[community_school_page_description]',
			'label'		=> _l('community_school_page_description'),
			'required'	=> true,
			'value'		=> $info['page_info']['community_school_page_description'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'page_info[teacher_page_title]',
			'label'		=> _l('teacher_page_title'),
			'required'	=> true,
			'value'		=> $info['page_info']['teacher_page_title'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'page_info[teacher_page_description]',
			'label'		=> _l('teacher_page_description'),
			'required'	=> true,
			'value'		=> $info['page_info']['teacher_page_description'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'page_info[user_page_title]',
			'label'		=> _l('user_page_title'),
			'required'	=> true,
			'value'		=> $info['page_info']['user_page_title'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'page_info[user_page_description]',
			'label'		=> _l('user_page_description'),
			'required'	=> true,
			'value'		=> $info['page_info']['user_page_description'] ?? '',
		];

		$data['signup_fields']['page_info'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);
		
		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_landing_page[signup_form_title]',
			'label'		=> _l('signup_form_title'),
			'required'	=> false,
			'value'		=> $info['user_landing_page']['signup_form_title'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_landing_page[title]',
			'label'		=> _l('title'),
			'required'	=> true,
			'value'		=> $info['user_landing_page']['title'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_landing_page[sub_title]',
			'label'		=> _l('sub_title'),
			'required'	=> true,
			'value'		=> $info['user_landing_page']['sub_title'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_landing_page[partner_title]',
			'label'		=> _l('partner_title'),
			'required'	=> true,
			'value'		=> $info['user_landing_page']['partner_title'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user_landing_page[award_title]',
			'label'		=> _l('award_title'),
			'required'	=> true,
			'value'		=> $info['user_landing_page']['award_title'] ?? '',
		];

		$partner_ids = explode(',', $event_config_info['partners']);

		$data['fields'][] = [
			'type'		=> 'checkbox',
			'key'		=> 'user_landing_page[partners][]',
			'label'		=> _l('partners'),
			'required'	=> true,
			'value'		=> $info['user_landing_page']['partners'] ?? [],
			'options'	=> array_map(function ($item) {
				return [
					'label'	=> $item['name'],
					'value'	=> $item['id'],
				];
			}, $this->event_partner_model->get_all(['partner_ids' => $partner_ids, 'order' => 'ASC'])['rows'] ?? []),
		];

		$event_config_info['awards'] = json_decode($event_config_info['awards'], true);

		$award_ids = isset($event_config_info['awards']['user']) ? call_user_func_array('array_merge', array_column($event_config_info['awards']['user'], 'awards')) : [];

		$data['fields'][] = [
			'type'		=> 'checkbox',
			'key'		=> 'user_landing_page[awards][]',
			'label'		=> _l('awards'),
			'required'	=> true,
			'value'		=> $info['user_landing_page']['awards'] ?? [],
			'options'	=> array_map(function ($item) {
				return [
					'label'	=> $item['name'],
					'value'	=> $item['id'],
				];
			}, $this->event_award_group_model->get_all(['award_ids' => $award_ids, 'type' 	=> 'user', 'order' => 'ASC'])['rows'] ?? []),
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'user_landing_page[signup_term_and_condition]',
			'label'		=> _l('signup_form_term_and_condition'),
			'required'	=> false,
			'value'		=> $info['user_landing_page']['signup_term_and_condition'] ?? '',
		];

		$data['signup_fields']['user_landing_page'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		if (empty($info['school_landing_page']['images'])) {
			$info['school_landing_page']['images'] = [''];
		}

		$data['multi_images']['school_landing_page'] = [
			'label' 	=> _li('school_signup_landing_page'),
			'images' 	=> $info['school_landing_page']['images'],
			'fields' 	=> [
				[
					'key' 	=> 'title',
					'label' => _li('title'),
					'value' => $info['school_landing_page']['title'],
				],
				[
					'key' 	=> 'sub_title',
					'label' => _li('sub_title'),
					'value' => $info['school_landing_page']['sub_title'],
				]
			],
		];

		$data['action'] = !empty($info)
			? base_url('admin/ajax_event_signup_crud/edit/' . $event_info['id'])
			: base_url('admin/ajax_event_signup_crud/add/' . $event_info['id'])
		;

		$this->load->view(sprintf('backend/admin/event/stage/%s', $stage), $data);
	}

	public function ajax_event_signup_crud($action = NULL, $id = 0) {
		$this->json = [];

		self::_validateEventSignupForm($id);

		if (empty($this->json['errors'])) {
			$data = $this->input->post();

			if (is_array($data['school_form'])) {
				$data['school_form'] = json_encode($data['school_form']);
			}

			if (!empty($data['community_school_form'])) {
				$data['community_school_form'] = json_encode($data['community_school_form'], true);
			}

			if (is_array($data['teacher_form'])) {
				$data['teacher_form'] = json_encode($data['teacher_form']);
			}

			if (is_array($data['user_form'])) {
				$data['user_form'] = json_encode($data['user_form']);
			}

			if (is_array($data['email_link'])) {
				$data['email_link'] = json_encode($data['email_link']);
			}

			if (is_array($data['page_info'])) {
				$data['page_info'] = json_encode($data['page_info']);
			}

			if (is_array($data['user_landing_page'])) {
				$data['user_landing_page'] = json_encode($data['user_landing_page']);
			}

			if (is_array($data['school_landing_page'])) {
				$data['school_landing_page'] = json_encode($data['school_landing_page']);
			}

			if (is_array($data['country_otp_validation'])) {
				$data['country_otp_validation'] = json_encode($data['country_otp_validation']);
			}

			$data['event_id'] = (int)$id;

			if (!empty($info = $this->event_signup_form_model->get_all([
				'event_id'	=> $id,
			])['rows'][0] ?? [])) {
				$this->event_signup_form_model->edit($info['id'], $data);
			} else {
				$this->event_signup_form_model->add($data);
			}
		}

		if (!empty($this->json['errors'])) {
			$this->json['error'] = _l('error_occured');
		} else {
			$this->json['success'] = _l('success');
		}

		output_json($this->json);
	}

	private function _communitySchoolForm($info) {
		$fields = [];

		$fields[] = [
			'type'		=> 'text',
			'key'		=> 'community_school_form[school]',
			'label'		=> _l('school_name'),
			'required'	=> true,
			'value'		=> $info['community_school_form']['school'] ?? _l('school_name'),
		];

		$fields[] = [
			'type'		=> 'text',
			'key'		=> 'community_school_form[country]',
			'label'		=> _l('country_field'),
			'required'	=> false,
			'value'		=> $info['community_school_form']['country'] ?? '',
		];

		$fields[] = [
			'type'		=> 'text',
			'key'		=> 'community_school_form[state]',
			'label'		=> _l('state_field'),
			'required'	=> false,
			'value'		=> $info['community_school_form']['state'] ?? _l('state'),
		];

		$fields[] = [
			'type'		=> 'text',
			'key'		=> 'community_school_form[city]',
			'label'		=> _l('city_field'),
			'required'	=> false,
			'value'		=> $info['community_school_form']['city'] ?? _l('city'),
		];

		$fields[] = [
			'type'		=> 'text',
			'key'		=> 'community_school_form[designation]',
			'label'		=> _l('designation_field'),
			'required'	=> false,
			'value'		=> $info['community_school_form']['designation'] ?? _l('designation'),
		];

		$fields[] = [
			'type'		=> 'text',
			'key'		=> 'community_school_form[school_head]',
			'label'		=> _l('school_head'),
			'required'	=> false,
			'value'		=> $info['community_school_form']['school_head'] ?? '',
		];

		$fields[] = [
			'type'		=> 'text',
			'key'		=> 'community_school_form[authorized_person]',
			'label'		=> _l('authorized_person'),
			'required'	=> false,
			'value'		=> $info['community_school_form']['authorized_person'] ?? _l('authorized_person'),
		];

		$fields[] = [
			'type'		=> 'text',
			'key'		=> 'community_school_form[email]',
			'label'		=> _l('email'),
			'required'	=> false,
			'value'		=> $info['community_school_form']['email'] ?? _l('email'),
		];

		$fields[] = [
			'type'		=> 'text',
			'key'		=> 'community_school_form[mobile]',
			'label'		=> _l('mobile'),
			'required'	=> false,
			'value'		=> $info['community_school_form']['mobile'] ?? _l('mobile'),
		];

		$fields[] = [
			'type'		=> 'select',
			'key'		=> 'community_school_form[validation]',
			'label'		=> _l('validation'),
			'required'	=> true,
			'value'		=> $info['community_school_form']['validation'] ?? 'mobile',
			'options'	=> [
				[
					'label'	=> _l('mobile'),
					'value'	=> 'mobile',
				],
				[
					'label'	=> _l('email'),
					'value'	=> 'email',
				],
				[
					'label'	=> _l('email_link'),
					'value'	=> 'email_link',
				],
			],
		];

		return $fields;
	}

	private function _validateEventSignupForm($id = 0) {
		$this->form_validation->set_rules('school_form[school]', _l('school_name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('school_form[validation]', _l('school_signup_validation_type'), 'trim|required|in_list[email,mobile,email_link]');
		$this->form_validation->set_rules('teacher_form[name]', _l('teacher_name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('teacher_form[site]', _l('school_name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('teacher_form[grade]', _l('grade'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('teacher_form[validation]', _l('teacher_signup_validation_type'), 'trim|required|in_list[email,mobile,email_link]');
		$this->form_validation->set_rules('user_form[first_name]', _l('first_name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('user_form[last_name]', _l('last_name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('user_form[site]', _l('school_name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('user_form[grade]', _l('grade'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('user_form[validation]', _l('user_signup_validation_type'), 'trim|required|in_list[email,mobile,email_link]');
		$this->form_validation->set_rules('page_info[school_page_title]', _l('school_page_title'), 'trim|required');
		$this->form_validation->set_rules('page_info[school_page_description]', _l('school_page_description'), 'trim|required');
		$this->form_validation->set_rules('page_info[teacher_page_title]', _l('teacher_page_title'), 'trim|required');
		$this->form_validation->set_rules('page_info[teacher_page_description]', _l('teacher_page_description'), 'trim|required');
		$this->form_validation->set_rules('page_info[user_page_title]', _l('user_page_title'), 'trim|required');
		$this->form_validation->set_rules('page_info[user_page_description]', _l('user_page_description'), 'trim|required');
		$this->form_validation->set_rules('user_landing_page[title]', _l('title'), 'trim|required');
		$this->form_validation->set_rules('user_landing_page[sub_title]', _l('sub_title'), 'trim|required');
		$this->form_validation->set_rules('user_landing_page[partner_title]', _l('partner_title'), 'trim|required');
		$this->form_validation->set_rules('user_landing_page[award_title]', _l('award_title'), 'trim|required');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['errors'] = $this->form_validation->error_array());
	}
}
