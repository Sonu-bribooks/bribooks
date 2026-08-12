<?php defined('BASEPATH') or exit('No direct script access allowed');

trait LandingPage {
	private function _getLandingPage($data = []) {
		$stage 				= $data['stage'] ?? 'landing_page';
		$event_info 		= $data['info'] ?? [];
		$country_info 		= $data['country_info'] ?? [];
		$event_type_info 	= $data['event_type_info'] ?? [];

		$info 				= $this->event_landing_page_model->get_all([
			'event_id'	=> $event_info['id']
		])['rows'][0] ?? [];

		if (!empty($info['landing_page'])) {
			$info['landing_page'] = json_decode($info['landing_page'], true);
		}

		if (!empty($info['thank_you'])) {
			$info['thank_you'] = json_decode($info['thank_you'], true);
		}

		if (!empty($info['publish_page'])) {
			$info['publish_page'] = json_decode($info['publish_page'], true);
		}

		if (!empty($info['term'])) {
			$info['term'] = json_decode($info['term'], true);
		}

		if (!empty($info['faq'])) {
			$info['faq'] = json_decode($info['faq'], true);
		}

		$data['landing_page_fields']['landing_page'] 		= self::_getLandingPageFields($info);
		$data['landing_page_fields']['school_term'] 		= self::_termFields($info, 'school');
		$data['landing_page_fields']['teacher_term'] 		= self::_termFields($info, 'teacher');
		$data['landing_page_fields']['user_term'] 			= self::_termFields($info, 'user');
		$data['landing_page_fields']['school_thank_you'] 	= self::_thankYouFields($info, 'school');
		$data['landing_page_fields']['teacher_thank_you'] 	= self::_thankYouFields($info, 'teacher');
		$data['landing_page_fields']['user_thank_you'] 		= self::_thankYouFields($info, 'user');
		$data['landing_page_fields']['user_publish_page'] 	= self::_publishPageFields($info, 'user');
		// $data['landing_page_fields']['faq'] 				= self::_renderFAQFields($info);

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'faq',
			'label'		=> _l('faq'),
			'required'	=> false,
			'fields'	=> self::_renderFAQFields($info),
		];

		$data['landing_page_fields']['faq'] = $this->load->view('backend/admin/event/stage/generic', $data, true);


		$data['action'] = !empty($info)
			? base_url('admin/ajax_event_landing_page_crud/edit/' . $event_info['id'])
			: base_url('admin/ajax_event_landing_page_crud/add/' . $event_info['id'])
		;

		$this->load->view(sprintf('backend/admin/event/stage/%s', $stage), $data);
	}

	private function _getLandingPageFields($info) {
		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'landing_page[type]',
			'label'		=> _l('type'),
			'required'	=> true,
			'value'		=> $info['landing_page']['type'] ?? 'A',
			'options'	=> [
				[
					'label'	=> _l('A'),
					'value'	=> 'A',
				],
				[
					'label'	=> _l('B'),
					'value'	=> 'B',
				],
				[
					'label'	=> _l('C'),
					'value'	=> 'C',
				],
				[
					'label'	=> _l('D'),
					'value'	=> 'D',
				],
			],
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> 'landing_page[generic_page_title]',
			'label'			=> _l('generic_page_title'),
			'placeholder'   => _l('enter_generic_page_title'),
			'required'		=> true,
			'value'			=> $info['landing_page']['generic_page_title'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> 'landing_page[generic_page_description]',
			'label'			=> _l('generic_page_description'),
			'placeholder'   => _l('enter_generic_page_description'),
			'required'		=> true,
			'value'			=> $info['landing_page']['generic_page_description'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> 'landing_page[school_page_title]',
			'label'			=> _l('school_page_title'),
			'placeholder'   => _l('enter_school_page_title'),
			'required'		=> true,
			'value'			=> $info['landing_page']['school_page_title'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> 'landing_page[school_page_description]',
			'label'			=> _l('school_page_description'),
			'placeholder'   => _l('enter_school_page_description'),
			'required'		=> true,
			'value'			=> $info['landing_page']['school_page_description'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> 'landing_page[community_school_page_title]',
			'label'			=> _l('community_school_page_title'),
			'placeholder'   => _l('enter_community_school_page_title'),
			'required'		=> true,
			'value'			=> $info['landing_page']['community_school_page_title'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> 'landing_page[community_school_page_description]',
			'label'			=> _l('community_school_page_description'),
			'placeholder'   => _l('enter_community_school_page_description'),
			'required'		=> true,
			'value'			=> $info['landing_page']['community_school_page_description'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> 'landing_page[user_page_title]',
			'label'			=> _l('user_page_title'),
			'placeholder'   => _l('enter_user_page_title'),
			'required'		=> true,
			'value'			=> $info['landing_page']['user_page_title'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> 'landing_page[user_page_description]',
			'label'			=> _l('user_page_description'),
			'placeholder'   => _l('enter_user_page_description'),
			'required'		=> true,
			'value'			=> $info['landing_page']['user_page_description'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> 'landing_page[title]',
			'label'			=> _l('common_page_header'),
			'placeholder'   => _l('enter_common_page_header'),
			'required'		=> true,
			'value'			=> $info['landing_page']['title'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'html',
			'key'			=> 'landing_page[description]',
			'label'			=> _l('common_page_content'),
			'required'		=> false,
			'value'			=> $info['landing_page']['description'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'image',
			'key'			=> 'landing_page[banner_image]',
			'label'			=> _l('common_banner_image'),
			'required'		=> false,
			'value'			=> $info['landing_page']['banner_image'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'image',
			'key'			=> 'landing_page[certificate_image]',
			'label'			=> _l('certificate_image'),
			'required'		=> false,
			'value'			=> $info['landing_page']['certificate_image'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'landing_page[student_recognition]',
			'label'		=> _l('student_recognition'),
			'required'	=> true,
			'value'		=> $info['landing_page']['student_recognition'] ?? 0,
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
			'key'		=> 'landing_page[student_our_legacy]',
			'label'		=> _l('student_our_legacy'),
			'required'	=> true,
			'value'		=> $info['landing_page']['student_our_legacy'] ?? 0,
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
			'key'		=> 'landing_page[student_previous_moments]',
			'label'		=> _l('student_previous_moments'),
			'required'	=> true,
			'value'		=> $info['landing_page']['student_previous_moments'] ?? 0,
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
			'key'		=> 'landing_page[student_national_media_and_news]',
			'label'		=> _l('student_national_media_and_news'),
			'required'	=> true,
			'value'		=> $info['landing_page']['student_national_media_and_news'] ?? 0,
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
			'key'		=> 'landing_page[student_best_seller_awards]',
			'label'		=> _l('student_best_seller_awards'),
			'required'	=> true,
			'value'		=> $info['landing_page']['student_best_seller_awards'] ?? 0,
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
			'key'		=> 'landing_page[school_legacy]',
			'label'		=> _l('school_legacy'),
			'required'	=> true,
			'value'		=> $info['landing_page']['school_legacy'] ?? 0,
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
			'key'		=> 'landing_page[school_previous_moments]',
			'label'		=> _l('school_previous_moments'),
			'required'	=> true,
			'value'		=> $info['landing_page']['school_previous_moments'] ?? 0,
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
			'key'		=> 'landing_page[school_national_media_and_news]',
			'label'		=> _l('school_national_media_and_news'),
			'required'	=> true,
			'value'		=> $info['landing_page']['school_national_media_and_news'] ?? 0,
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

		return $this->load->view('backend/admin/event/stage/generic', $data, true);
	}

	private function _termFields($info = [], $type = 'school') {
		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> sprintf('term[%s][page_title]', $type),
			'label'			=> _l('page_title'),
			'placeholder'   => _l('enter_page_title'),
			'required'		=> true,
			'value'			=> $info['term'][$type]['page_title'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> sprintf('term[%s][page_description]', $type),
			'label'			=> _l('page_description'),
			'placeholder'   => _l('enter_page_description'),
			'required'		=> true,
			'value'			=> $info['term'][$type]['page_description'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> sprintf('term[%s][title]', $type),
			'label'			=> _l('header1'),
			'placeholder'   => _l('enter_title'),
			'required'		=> true,
			'value'			=> $info['term'][$type]['title'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> sprintf('term[%s][heading]', $type),
			'label'			=> _l('header2'),
			'placeholder'   => _l('enter_heading'),
			'required'		=> true,
			'value'			=> $info['term'][$type]['heading'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'html',
			'key'			=> sprintf('term[%s][description]', $type),
			'label'			=> _l('content'),
			'placeholder'   => _l('enter_description'),
			'required'		=> false,
			'value'			=> $info['term'][$type]['description'] ?? '',
		];

		return $this->load->view('backend/admin/event/stage/generic', $data, true);
	}

	private function _thankYouFields($info = [], $type = 'school') {
		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> sprintf('thank_you[%s][page_title]', $type),
			'label'			=> _l('page_title'),
			'placeholder'   => _l('enter_page_title'),
			'required'		=> true,
			'value'			=> $info['thank_you'][$type]['page_title'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> sprintf('thank_you[%s][page_description]', $type),
			'label'			=> _l('page_description'),
			'placeholder'   => _l('enter_page_description'),
			'required'		=> true,
			'value'			=> $info['thank_you'][$type]['page_description'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> sprintf('thank_you[%s][title]', $type),
			'label'			=> _l('header'),
			'placeholder'   => _l('enter_title'),
			'required'		=> true,
			'value'			=> $info['thank_you'][$type]['title'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'html',
			'key'			=> sprintf('thank_you[%s][content]', $type),
			'label'			=> _l('content'),
			'placeholder'   => _l('enter_content'),
			'required'		=> true,
			'value'			=> $info['thank_you'][$type]['content'] ?? '',
		];

		return $this->load->view('backend/admin/event/stage/generic', $data, true);
	}

	private function _publishPageFields($info = [], $type = 'school') {
		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> sprintf('publish_page[%s][page_title]', $type),
			'label'			=> _l('page_title'),
			'placeholder'   => _l('enter_page_title'),
			'required'		=> true,
			'value'			=> $info['publish_page'][$type]['page_title'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'html',
			'key'			=> sprintf('publish_page[%s][content]', $type),
			'label'			=> _l('content'),
			'placeholder'   => _l('enter_content'),
			'required'		=> true,
			'value'			=> $info['publish_page'][$type]['content'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> sprintf('publish_page[%s][book_version]', $type),
			'label'			=> _l('book_version'),
			'placeholder'   => _l('enter_book_version'),
			'required'		=> false,
			'value'			=> $info['publish_page'][$type]['book_version'] ?? '',
		];

		$data['fields'][] = [
			'type'			=> 'text',
			'key'			=> sprintf('publish_page[%s][time_duration]', $type),
			'label'			=> _l('time_duration'),
			'placeholder'   => _l('enter_time_duration'),
			'required'		=> false,
			'value'			=> $info['publish_page'][$type]['time_duration'] ?? '',
		];

		return $this->load->view('backend/admin/event/stage/generic', $data, true);
	}

	private function _renderFAQFields($info = []) {
		$fields 	= [];
		$items 		= $info['faq'] ?? [''];
		$index 		= 0;

		foreach ($items as $item) {
			$fields[] = [
				[
					'type'		=> 'text',
					'key'		=> sprintf('faq[%d][question]', $index),
					'label'		=> _l('faq_question'),
					'required'	=> false,
					'value'		=> $info['faq'][$index]['question'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('faq[%d][answer]', $index),
					'label'		=> _l('faq_answer'),
					'required'	=> false,
					'value'		=> $info['faq'][$index]['answer'] ?? '',
				],
			];

			++$index;
		}

		return $fields;
	}

	public function ajax_event_landing_page_crud($action = NULL, $id = 0) {
		$this->json = [];

		self::_validateLandingPageForm($id);

		if (empty($this->json['errors'])) {
			$data = $this->input->post(NULL, FALSE);

			$data['landing_page']['description'] 		= _allowSpecificHtmlTags($data['landing_page']['description']);
			$data['term']['school']['description'] 		= _allowSpecificHtmlTags($data['term']['school']['description']);
			$data['term']['teacher']['description'] 	= _allowSpecificHtmlTags($data['term']['teacher']['description']);
			$data['term']['user']['description'] 		= _allowSpecificHtmlTags($data['term']['user']['description']);
			$data['thank_you']['school']['content'] 	= _allowSpecificHtmlTags($data['thank_you']['school']['content']);
			$data['thank_you']['teacher']['content'] 	= _allowSpecificHtmlTags($data['thank_you']['teacher']['content']);
			$data['thank_you']['user']['content'] 		= _allowSpecificHtmlTags($data['thank_you']['user']['content']);
			$data['publish_page']['user']['content'] 	= _allowSpecificHtmlTags($data['publish_page']['user']['content']);

			if (is_array($data['landing_page'])) {
				$data['landing_page'] = json_encode($data['landing_page']);
			}

			if (is_array($data['term'])) {
				$data['term'] = json_encode($data['term']);
			}

			if (is_array($data['thank_you'])) {
				$data['thank_you'] = json_encode($data['thank_you']);
			}

			if (is_array($data['publish_page'])) {
				$data['publish_page'] = json_encode($data['publish_page']);
			}

			if (is_array($data['faq'])) {
				$data['faq'] = json_encode($data['faq']);
			}

			$data['event_id'] = (int)$id;

			if (!empty($info = $this->event_landing_page_model->get_all([
				'event_id'	=> $id,
			])['rows'][0] ?? [])) {
				$this->event_landing_page_model->edit($info['id'], $data);
			} else {
				$this->event_landing_page_model->add($data);
			}
		}

		if (!empty($this->json['errors'])) {
			$this->json['error'] = _l('error_occured');
		} else {
			$this->json['success'] = _l('success');
		}

		output_json($this->json);
	}

	private function _validateLandingPageForm($id = 0) {
		$this->form_validation->set_rules('landing_page[title]', _l('landing_page_title'), 'trim|required|min_length[3]|max_length[300]');
		$this->form_validation->set_rules('landing_page[description]', _l('landing_page_description'), 'trim|required|min_length[3]|max_length[1000]');

		$this->form_validation->set_rules('term[school][title]', _l('term_page_title'), 'trim|required|min_length[3]|max_length[300]');
		$this->form_validation->set_rules('term[school][heading]', _l('term_page_heading'), 'trim|required|min_length[3]|max_length[300]');
		$this->form_validation->set_rules('term[school][description]', _l('term_page_description'), 'trim|required|min_length[3]|max_length[10000]');

		$this->form_validation->set_rules('term[teacher][title]', _l('term_page_title'), 'trim|required|min_length[3]|max_length[300]');
		$this->form_validation->set_rules('term[teacher][heading]', _l('term_page_heading'), 'trim|required|min_length[3]|max_length[300]');
		$this->form_validation->set_rules('term[teacher][description]', _l('term_page_description'), 'trim|required|min_length[3]|max_length[10000]');

		$this->form_validation->set_rules('term[user][title]', _l('term_page_title'), 'trim|required|min_length[3]|max_length[300]');
		$this->form_validation->set_rules('term[user][heading]', _l('term_page_heading'), 'trim|required|min_length[3]|max_length[300]');
		$this->form_validation->set_rules('term[user][description]', _l('term_page_description'), 'trim|required|min_length[3]|max_length[10000]');

		$this->form_validation->set_rules('thank_you[school][title]', _l('thank_you_page_title'), 'trim|required|min_length[3]|max_length[300]');
		$this->form_validation->set_rules('thank_you[school][content]', _l('thank_you_page_content'), 'trim|required|min_length[3]|max_length[10000]');

		$this->form_validation->set_rules('thank_you[teacher][title]', _l('thank_you_page_title'), 'trim|required|min_length[3]|max_length[300]');
		$this->form_validation->set_rules('thank_you[teacher][content]', _l('thank_you_page_content'), 'trim|required|min_length[3]|max_length[10000]');

		$this->form_validation->set_rules('thank_you[user][title]', _l('thank_you_page_title'), 'trim|required|min_length[3]|max_length[300]');
		$this->form_validation->set_rules('thank_you[user][content]', _l('thank_you_page_content'), 'trim|required|min_length[3]|max_length[10000]');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['errors'] = $this->form_validation->error_array());
	}
}
