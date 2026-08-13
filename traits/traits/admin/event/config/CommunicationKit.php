<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CommunicationKit {
	private function _getCommunicationKit($data = []) {
		$stage 				= $data['stage'] ?? 'communication_kit';
		$event_info 		= $data['info'] ?? [];
		$country_info 		= $data['country_info'] ?? [];
		$event_type_info 	= $data['event_type_info'] ?? [];

		$info 				= $this->event_communication_kit_model->get_all([
			'event_id'	=> $event_info['id']
		])['rows'][0] ?? [];

		if (!empty($info['school'])) {
			$info['school'] = json_decode($info['school'], true);
		}

		if (!empty($info['teacher'])) {
			$info['teacher'] = json_decode($info['teacher'], true);
		}

		if (!empty($info['user'])) {
			$info['user'] = json_decode($info['user'], true);
		}

		if (!empty($info['user_early_access'])) {
			$info['user_early_access'] = json_decode($info['user_early_access'], true);
		}

		if (!empty($info['user_referral'])) {
			$info['user_referral'] = json_decode($info['user_referral'], true);
		}

		if (!empty($info['book'])) {
			$info['book'] = json_decode($info['book'], true);
		}

		if (!empty($info['school_ui_kit'])) {
			$info['school_ui_kit'] = json_decode($info['school_ui_kit'], true);
		}

		if (!empty($info['event_exhibition'])) {
			$info['event_exhibition'] = json_decode($info['event_exhibition'], true);
		}

		if (!empty($info['parent_acknowledge'])) {
			$info['parent_acknowledge'] = json_decode($info['parent_acknowledge'], true);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'teacher[email][subject]',
			'label'		=> _l('email_subject'),
			'required'	=> true,
			'value'		=> $info['teacher']['email']['subject'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'teacher[email][message]',
			'label'		=> _l('email_message'),
			'required'	=> true,
			'value'		=> $info['teacher']['email']['message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'checkbox',
			'key'		=> 'teacher[email][attachment][]',
			'label'		=> _l('email_attachment'),
			'required'	=> false,
			'value'		=> $info['teacher']['email']['attachment'] ?? [],
			'options'	=> [
				[
					'label'	=> _l('leaflet'),
					'value'	=> 'leaflet',
				],
				[
					'label'	=> _l('user_kit'),
					'value'	=> 'user',
				],
				[
					'label'	=> _l('teacher_kit'),
					'value'	=> 'teacher',
				],
				[
					'label'	=> _l('ebrochure'),
					'value'	=> 'ebrochure',
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'teacher[whatsapp][template]',
			'label'		=> _l('whatsapp_template_id'),
			'required'	=> false,
			'value'		=> $info['teacher']['whatsapp']['template'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'textarea',
			'key'		=> 'teacher[whatsapp][message]',
			'label'		=> _l('whatsapp_template_message'),
			'required'	=> false,
			'value'		=> $info['teacher']['whatsapp']['message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'teacher[whatsapp][attachment]',
			'label'		=> _l('whatsapp_attachment'),
			'required'	=> false,
			'value'		=> $info['teacher']['whatsapp']['attachment'] ?? '',
			'options'	=> [
				[
					'label'	=> _l('leaflet'),
					'value'	=> 'leaflet',
				],
				[
					'label'	=> _l('user_kit'),
					'value'	=> 'user',
				],
				[
					'label'	=> _l('teacher_kit'),
					'value'	=> 'teacher',
				],
				[
					'label'	=> _l('ebrochure'),
					'value'	=> 'ebrochure',
				],
			],
		];

		$data['communication_kit_fields']['teacher_communication_kit'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user[laptop_email][subject]',
			'label'		=> _l('laptop_email_subject'),
			'required'	=> true,
			'value'		=> $info['user']['laptop_email']['subject'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'user[laptop_email][message]',
			'label'		=> _l('laptop_email_message'),
			'required'	=> true,
			'value'		=> $info['user']['laptop_email']['message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user[laptop_whatsapp][template]',
			'label'		=> _l('laptop_whatsapp_template_id'),
			'required'	=> false,
			'value'		=> $info['user']['laptop_whatsapp']['template'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user[laptop_whatsapp][message]',
			'label'		=> _l('laptop_whatsapp_template_message'),
			'required'	=> false,
			'value'		=> $info['user']['laptop_whatsapp']['message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user[mobile_email][subject]',
			'label'		=> _l('mobile_email_subject'),
			'required'	=> true,
			'value'		=> $info['user']['mobile_email']['subject'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'user[mobile_email][message]',
			'label'		=> _l('mobile_email_message'),
			'required'	=> true,
			'value'		=> $info['user']['mobile_email']['message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user[mobile_whatsapp][template]',
			'label'		=> _l('mobile_whatsapp_template_id'),
			'required'	=> false,
			'value'		=> $info['user']['mobile_whatsapp']['template'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user[mobile_whatsapp][message]',
			'label'		=> _l('mobile_whatsapp_template_message'),
			'required'	=> false,
			'value'		=> $info['user']['mobile_whatsapp']['message'] ?? '',
		];

		// EARLY ACCESS MESSAGE

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user[laptop_email_earlyaccess][subject]',
			'label'		=> _l('laptop_email_subject_earlyaccess'),
			'required'	=> true,
			'value'		=> $info['user']['laptop_email_earlyaccess']['subject'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'user[laptop_email_earlyaccess][message]',
			'label'		=> _l('laptop_email_earlyaccess_message'),
			'required'	=> true,
			'value'		=> $info['user']['laptop_email_earlyaccess']['message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user[laptop_whatsapp_earlyaccess][template]',
			'label'		=> _l('laptop_whatsapp_earlyaccess_template_id'),
			'required'	=> false,
			'value'		=> $info['user']['laptop_whatsapp_earlyaccess']['template'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user[laptop_whatsapp_earlyaccess][message]',
			'label'		=> _l('laptop_whatsapp_earlyaccess_template_message'),
			'required'	=> false,
			'value'		=> $info['user']['laptop_whatsapp_earlyaccess']['message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user[mobile_email_earlyaccess][subject]',
			'label'		=> _l('mobile_email_subject_earlyaccess'),
			'required'	=> true,
			'value'		=> $info['user']['mobile_email_earlyaccess']['subject'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'user[mobile_email_earlyaccess][message]',
			'label'		=> _l('mobile_email_message_earlyaccess'),
			'required'	=> true,
			'value'		=> $info['user']['mobile_email_earlyaccess']['message'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user[mobile_whatsapp_earlyaccess][template]',
			'label'		=> _l('mobile_whatsapp_earlyaccess_template_id'),
			'required'	=> false,
			'value'		=> $info['user']['mobile_whatsapp_earlyaccess']['template'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'user[mobile_whatsapp_earlyaccess][message]',
			'label'		=> _l('mobile_whatsapp_earlyaccess_template_message'),
			'required'	=> false,
			'value'		=> $info['user']['mobile_whatsapp_earlyaccess']['message'] ?? '',
		];

		$data['communication_kit_fields']['user_communication_kit'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		// USER EARLY ACCESS GROUP COMMUNICATION

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'user_early_access_communication_kit',
			'label'		=> _l('user_early_access_communication_kit'),
			'required'	=> false,
			'fields'	=> self::_renderUserEarlyAccessCommunicationFields($info, 'user_early_access'),
		];

		$data['communication_kit_fields']['user_with_tag_communication_kit'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		// USER REFERRAL GROUP COMMUNICATION

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'referral_communication_kit',
			'label'		=> _l('referral_communication_kit'),
			'required'	=> false,
			'fields'	=> self::_renderReferralCommunicationFields($info, 'user_referral'),
		];

		$data['communication_kit_fields']['user_referral_communication_kit'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		// SCHOOL FIELDS ADD HERE

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'school_communicaton_kit',
			'label'		=> _l('school_communicaton_kit'),
			'required'	=> false,
			'fields'	=> self::_renderSchoolCommunicationFields($info, 'school'),
		];

		$data['communication_kit_fields']['school_communication_kit'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		// BOOK FIELDS ADD HERE

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'book_communicaton_kit',
			'label'		=> _l('book_communicaton_kit'),
			'required'	=> false,
			'fields'	=> self::_renderBookCommunicationFields($info, 'book'),
		];

		$data['communication_kit_fields']['book_communicaton_kit'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		// SCHOOL DASHBOARD FIELDS ADD HERE

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'school_ui_kit',
			'label'		=> _l('school_ui_kit'),
			'required'	=> false,
			'fields'	=> self::_renderSchoolDashboardCommunicationFields($info, 'school'),
		];

		$data['communication_kit_fields']['school_ui_kit'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		// EVENT EXHIBITION FIELDS ADD HERE

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'event_exhibition',
			'label'		=> _l('event_exhibition'),
			'required'	=> false,
			'fields'	=> self::_renderExhibitionCommunicationFields($info, 'event_exhibition'),
		];

		$data['communication_kit_fields']['event_exhibition'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		// EVENT PARENT ACKNOWLEDGE FIELDS ADD HERE

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'parent_acknowledge',
			'label'		=> _l('parent_acknowledge'),
			'required'	=> false,
			'fields'	=> self::_renderParentAcknowledgeFields($info, 'parent_acknowledge'),
		];

		$data['communication_kit_fields']['parent_acknowledge'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		$data['action'] = !empty($info)
			? base_url('admin/ajax_event_communication_kit_crud/edit/' . $event_info['id'])
			: base_url('admin/ajax_event_communication_kit_crud/add/' . $event_info['id'])
		;

		$this->load->view(sprintf('backend/admin/event/stage/%s', $stage), $data);
	}

	public function ajax_event_communication_kit_crud($action = NULL, $id = 0) {
		$this->json = [];

		self::_validateEventCommunicationKitForm($id);

		if (empty($this->json['errors'])) {
			$data = $this->input->post(NULL, FALSE);

			foreach( $data['school'] as $index => $value) {
				$data['school'][$index]['email']['message'] = _allowSpecificHtmlTags($data['school'][$index]['email']['message']);
			}

			if (!empty($data['user_early_access'])) {
				foreach($data['user_early_access'] as $index => $value) {
					$data['user_early_access'][$index]['laptop_email']['message'] = _allowSpecificHtmlTags($data['user_early_access'][$index]['laptop_email']['message']);
					$data['user_early_access'][$index]['mobile_email']['message'] = _allowSpecificHtmlTags($data['user_early_access'][$index]['mobile_email']['message']);
				}
			}

			if (!empty($data['user_referral'])) {
				foreach($data['user_referral'] as $index => $value) {
					$data['user_referral'][$index]['laptop_email']['message'] = _allowSpecificHtmlTags($data['user_referral'][$index]['laptop_email']['message']);
					$data['user_referral'][$index]['mobile_email']['message'] = _allowSpecificHtmlTags($data['user_referral'][$index]['mobile_email']['message']);
				}
			}

			foreach( $data['book'] as $index => $value) {
				$data['book'][$index]['email']['message'] = _allowSpecificHtmlTags($data['book'][$index]['email']['message']);
			}

			$data['teacher']['email']['message'] 		= _allowSpecificHtmlTags($data['teacher']['email']['message']);
			$data['user']['laptop_email']['message'] 	= _allowSpecificHtmlTags($data['user']['laptop_email']['message']);
			$data['user']['mobile_email']['message'] 	= _allowSpecificHtmlTags($data['user']['mobile_email']['message']);

			if (is_array($data['school'])) {
				$data['school'] = json_encode($data['school']);
			}

			if (is_array($data['teacher'])) {
				$data['teacher'] = json_encode($data['teacher']);
			}

			if (is_array($data['user'])) {
				$data['user'] = json_encode($data['user']);
			}

			if (is_array($data['user_early_access'])) {
				$data['user_early_access'] = json_encode($data['user_early_access']);
			}

			if (is_array($data['user_referral'])) {
				$data['user_referral'] = json_encode($data['user_referral']);
			}

			if (is_array($data['book'])) {
				$data['book'] = json_encode($data['book']);
			}

			if (is_array($data['school_ui_kit'])) {
				$data['school_ui_kit'] = json_encode($data['school_ui_kit']);
			}

			if (is_array($data['event_exhibition'])) {
				$data['event_exhibition'] = json_encode($data['event_exhibition']);
			}

			if (is_array($data['parent_acknowledge'])) {
				$data['parent_acknowledge'] = json_encode($data['parent_acknowledge']);
			}

			$data['event_id'] = (int)$id;

			if (!empty($info = $this->event_communication_kit_model->get_all([
				'event_id'	=> $id,
			])['rows'][0] ?? [])) {
				$this->event_communication_kit_model->edit($info['id'], $data);
			} else {
				$this->event_communication_kit_model->add($data);
			}
		}

		if (!empty($this->json['errors'])) {
			$this->json['error'] = _l('error_occured');
		} else {
			$this->json['success'] = _l('success');
		}

		output_json($this->json);
	}

	private function _renderUserEarlyAccessCommunicationFields($info = [], $type = 'user_early_access') {
		$fields 	= [];

		$items 		= $info[$type] ?? [''];

		$tags = array_map(function($tag) {
			return [
				'label' => $tag['name'],
				'value' => $tag['name']
			];
		}, $this->user_tag_model->get_all()['rows']);

		$index = 0;

		foreach ($items as $item) {
			$fields[] = [
				[
					'type'		=> 'multi_select2',
					'key'		=> sprintf('%s[%d][tags]', $type, $index),
					'label'		=> _l('tags'),
					'required'	=> false,
					'value'		=> !empty($info['user_early_access'][$index]['tags']) ? array_map(fn($item) => [
						'value' => $item,
						'label' => $item
					], $info['user_early_access'][$index]['tags']) : '',
					'options'	=> $tags,
					'ajax_url'	=> base_url('admin/ajax_search_user_tag/text')
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][laptop_email_earlyaccess][subject]', $type, $index),
					'label'		=> _l('laptop_email_subject_earlyaccess'),
					'required'	=> true,
					'value'		=> $info['user_early_access'][$index]['laptop_email_earlyaccess']['subject'] ?? '',
				],
				[
					'type'		=> 'html',
					'key'		=> sprintf('%s[%d][laptop_email_earlyaccess][message]', $type, $index),
					'label'		=> _l('laptop_email_earlyaccess_message'),
					'required'	=> true,
					'value'		=> $info['user_early_access'][$index]['laptop_email_earlyaccess']['message'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][laptop_whatsapp_earlyaccess][template]', $type, $index),
					'label'		=> _l('laptop_whatsapp_earlyaccess_template_id'),
					'required'	=> false,
					'value'		=> $info['user_early_access'][$index]['laptop_whatsapp_earlyaccess']['template'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][laptop_whatsapp_earlyaccess][message]', $type, $index),
					'label'		=> _l('laptop_whatsapp_earlyaccess_template_message'),
					'required'	=> false,
					'value'		=> $info['user_early_access'][$index]['laptop_whatsapp_earlyaccess']['message'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][mobile_email_earlyaccess][subject]', $type, $index),
					'label'		=> _l('mobile_email_subject_earlyaccess'),
					'required'	=> true,
					'value'		=> $info['user_early_access'][$index]['mobile_email_earlyaccess']['subject'] ?? '',
				],
				[
					'type'		=> 'html',
					'key'		=> sprintf('%s[%d][mobile_email_earlyaccess][message]', $type, $index),
					'label'		=> _l('mobile_email_message_earlyaccess'),
					'required'	=> true,
					'value'		=> $info['user_early_access'][$index]['mobile_email_earlyaccess']['message'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][mobile_whatsapp_earlyaccess][template]', $type, $index),
					'label'		=> _l('mobile_whatsapp_earlyaccess_template_id'),
					'required'	=> false,
					'value'		=> $info['user_early_access'][$index]['mobile_whatsapp_earlyaccess']['template'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][mobile_whatsapp_earlyaccess][message]', $type, $index),
					'label'		=> _l('mobile_whatsapp_earlyaccess_template_message'),
					'required'	=> false,
					'value'		=> $info['user_early_access'][$index]['mobile_whatsapp_earlyaccess']['message'] ?? '',
				],
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][status]', $type, $index),
					'label'		=> _l('status'),
					'required'	=> true,
					'value'		=> $info['user_early_access'][$index]['status'] ?? '1',
					'options'	=> [
						[
							'label'	=> _l('enable'),
							'value'	=> '1',
						],
						[
							'label'	=> _l('disable'),
							'value'	=> '0',
						],
					],
				]
			];

			++$index;
		}

		return $fields;
	}

	private function _renderSchoolCommunicationFields($info = [], $type = 'user') {
		$fields 	= [];

		$items 		= $info[$type] ?? [''];

		$group_regions = array_map(function($region) {
			return [
				'label' => $region['name'],
				'value' => $region['id']
			];
		}, $this->group_region_model->get_all()['rows']);

		array_unshift($group_regions, [
			'label' => 'ALL',
			'value' => 'ALL'
		]);

		$tags = array_map(function($tag) {
			return [
				'label' => $tag['name'],
				'value' => $tag['name']
			];
		}, $this->school_tag_model->get_all()['rows']);

		$index = 0;

		foreach ($items as $item) {
			$fields[] = [
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][region]', $type, $index),
					'label'		=> _l('group_region'),
					'required'	=> false,
					'value'		=> $info['school'][$index]['region'] ?? '',
					'options'	=> $group_regions,
				],
				[
					'type'		=> 'multi_select2',
					'key'		=> sprintf('%s[%d][tags]', $type, $index),
					'label'		=> _l('tags'),
					'required'	=> false,
					'value'		=> !empty($info['school'][$index]['tags']) ? array_map(fn($item) => [
						'value' => $item,
						'label' => $item
					], $info['school'][$index]['tags']) : '',
					'options'	=> $tags,
					'ajax_url'	=> base_url('admin/ajax_search_school_tag/text')
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][email][subject]', $type, $index),
					'label'		=> _l(sprintf('%s_email_subject', $type)),
					'required'	=> false,
					'value'		=> $info['school'][$index]['email']['subject'] ?? '',
				],
				[
					'type'		=> 'html',
					'key'		=> sprintf('%s[%d][email][message]', $type, $index),
					'label'		=> _l(sprintf('%s_email_message', $type)),
					'required'	=> false,
					'value'		=> $info['school'][$index]['email']['message'] ?? '',
				],
				[
					'type'		=> 'checkbox',
					'key'		=> sprintf('%s[%d][email][attachment][]', $type, $index),
					'label'		=> _l('email_attachment'),
					'required'	=> false,
					'value'		=> $info['school'][$index]['email']['attachment'] ?? [],
					'options'	=> [
						[
							'label'	=> _l('leaflet'),
							'value'	=> 'leaflet',
						],
						[
							'label'	=> _l('user_kit'),
							'value'	=> 'user',
						],
						[
							'label'	=> _l('teacher_kit'),
							'value'	=> 'teacher',
						],
						[
							'label'	=> _l('ebrochure'),
							'value'	=> 'ebrochure',
						],
					],
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][whatsapp][template]', $type, $index),
					'label'		=> _l('whatsapp_template_id'),
					'required'	=> false,
					'value'		=> $info['school'][$index]['whatsapp']['template'] ?? '',
				],
				[
					'type'		=> 'textarea',
					'key'		=> sprintf('%s[%d][whatsapp][message]', $type, $index),
					'label'		=> _l('whatsapp_template_message'),
					'required'	=> false,
					'value'		=> $info['school'][$index]['whatsapp']['message'] ?? '',
				],
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][whatsapp][attachment]', $type, $index),
					'label'		=> _l('whatsapp_attachment'),
					'required'	=> false,
					'value'		=> $info['school'][$index]['whatsapp']['attachment'] ?? '',
					'options'	=> [
						[
							'label'	=> _l('leaflet'),
							'value'	=> 'leaflet',
						],
						[
							'label'	=> _l('user_kit'),
							'value'	=> 'user',
						],
						[
							'label'	=> _l('teacher_kit'),
							'value'	=> 'teacher',
						],
						[
							'label'	=> _l('ebrochure'),
							'value'	=> 'ebrochure',
						],
					],
				]
			];

			++$index;
		}

		return $fields;
	}

	private function _renderBookCommunicationFields($info = [], $type = 'book') {
		$fields 	= [];

		$items 		= $info[$type] ?? [''];

		$index = 0;

		foreach ($items as $item) {
			$fields[] = [
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][template_type]', $type, $index),
					'label'		=> _l(sprintf('%s_template_type', $type)),
					'required'	=> true,
					'value'		=> $info['book'][$index]['template_type'] ?? '',
					'options'	=> [
						[
							'label'	=> _l('debut_book_publish'),
							'value'	=> 'debut_book_publish',
						]
					],
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][template_name]', $type, $index),
					'label'		=> _l(sprintf('%s_template_name', $type)),
					'required'	=> false,
					'value'		=> $info['book'][$index]['template_name'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][coupon_percent]', $type, $index),
					'label'		=> _l(sprintf('%s_coupon_percent', $type)),
					'required'	=> false,
					'value'		=> $info['book'][$index]['coupon_percent'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][max_quantity]', $type, $index),
					'label'		=> _l(sprintf('%s_max_quantity', $type)),
					'required'	=> false,
					'value'		=> $info['book'][$index]['max_quantity'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][coupon_duration]', $type, $index),
					'label'		=> _l(sprintf('%s_coupon_duration', $type)),
					'required'	=> false,
					'value'		=> $info['book'][$index]['coupon_duration'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][alert_duration]', $type, $index),
					'label'		=> _l(sprintf('%s_alert_duration', $type)),
					'required'	=> false,
					'value'		=> $info['book'][$index]['alert_duration'] ?? '',
				],
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][duration_type]', $type, $index),
					'label'		=> _l(sprintf('%s_duration_type', $type)),
					'required'	=> true,
					'value'		=> $info['book'][$index]['duration_type'] ?? 'minutes',
					'options'	=> [
						[
							'label'	=> _l('minutes'),
							'value'	=> 'minutes',
						],
						[
							'label'	=> _l('hours'),
							'value'	=> 'hours',
						],
						[
							'label'	=> _l('days'),
							'value'	=> 'days',
						],
					],
				],
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][alert_condition]', $type, $index),
					'label'		=> _l(sprintf('%s_alert_condition', $type)),
					'required'	=> true,
					'value'		=> $info['book'][$index]['alert_condition'] ?? 'book_published',
					'options'	=> [
						[
							'label'	=> _l('book_published'),
							'value'	=> 'book_published',
						],
						[
							'label'	=> _l('coupon_created'),
							'value'	=> 'coupon_created',
						],
						[
							'label'	=> _l('coupon_not_used'),
							'value'	=> 'coupon_not_used',
						],
						[
							'label'	=> _l('coupon_used'),
							'value'	=> 'coupon_used',
						],
						[
							'label'	=> _l('parent_kit'),
							'value'	=> 'parent_kit',
						],
					],
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][book_version]', $type, $index),
					'label'		=> _l(sprintf('%s_version', $type)),
					'required'	=> false,
					'value'		=> $info['book'][$index]['book_version'] ?? '',
				],
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][repeat]', $type, $index),
					'label'		=> _l(sprintf('%s_repeat', $type)),
					'required'	=> true,
					'value'		=> $info['book'][$index]['repeat'] ?? '0',
					'options'	=> [
						[
							'label'	=> _l('YES'),
							'value'	=> '1',
						],
						[
							'label'	=> _l('NO'),
							'value'	=> '0',
						],
					],
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][email][subject]', $type, $index),
					'label'		=> _l(sprintf('%s_email_subject', $type)),
					'required'	=> false,
					'value'		=> $info['book'][$index]['email']['subject'] ?? '',
				],
				[
					'type'		=> 'html',
					'key'		=> sprintf('%s[%d][email][message]', $type, $index),
					'label'		=> _l(sprintf('%s_email_message', $type)),
					'required'	=> false,
					'value'		=> $info['book'][$index]['email']['message'] ?? '',
				],
				[
					'type'		=> 'checkbox',
					'key'		=> sprintf('%s[%d][email][attachment][]', $type, $index),
					'label'		=> _l(sprintf('%s_email_attachment', $type)),
					'required'	=> false,
					'value'		=> $info['book'][$index]['email']['attachment'] ?? [],
					'options'	=> [
						[
							'label'	=> _l('personal_note'),
							'value'	=> 'personal_note',
						],
					],
				],
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][whatsapp][gateway]', $type, $index),
					'label'		=> _l(sprintf('%s_whatsapp_gateway', $type)),
					'required'	=> true,
					'value'		=> $info['book'][$index]['whatsapp']['gateway'] ?? '',
					'options'	=> [
						[
							'label'	=> _l('imiconnect'),
							'value'	=> 'imiconnect',
						],
						[
							'label'	=> _l('onextel'),
							'value'	=> 'onextel',
						],
					],
				],
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][whatsapp][type]', $type, $index),
					'label'		=> _l(sprintf('%s_whatsapp_type', $type)),
					'required'	=> true,
					'value'		=> $info['book'][$index]['whatsapp']['type'] ?? '',
					'options'	=> [
						[
							'label'	=> _l('text'),
							'value'	=> 'text',
						],
						[
							'label'	=> _l('cta'),
							'value'	=> 'cta',
						],
						[
							'label'	=> _l('document'),
							'value'	=> 'document',
						],
						[
							'label'	=> _l('image'),
							'value'	=> 'image',
						],
						[
							'label'	=> _l('video'),
							'value'	=> 'video',
						],
					],
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][whatsapp][template]', $type, $index),
					'label'		=> _l('whatsapp_template_id'),
					'required'	=> false,
					'value'		=> $info['book'][$index]['whatsapp']['template'] ?? '',
				],
				[
					'type'		=> 'textarea',
					'key'		=> sprintf('%s[%d][whatsapp][message]', $type, $index),
					'label'		=> _l('whatsapp_template_message'),
					'required'	=> false,
					'value'		=> $info['book'][$index]['whatsapp']['message'] ?? '',
				],
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][whatsapp][attachment]', $type, $index),
					'label'		=> _l(sprintf('%s_whatsapp_attachment', $type)),
					'required'	=> false,
					'value'		=> $info['book'][$index]['whatsapp']['attachment'] ?? '',
					'options'	=> [
						[
							'label'	=> _l('personal_note'),
							'value'	=> 'personal_note',
						],
					],
				],
			];

			++$index;
		}

		return $fields;
	}

	private function _renderSchoolDashboardCommunicationFields($info = [], $type = 'school') {
		$fields 	= [];

		$items 		= $info[$type . 'ui_kit'] ?? [''];

		$index = 0;

		foreach ($items as $item) {
			$fields[] = [
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s_ui_kit[%d][headline]', $type, $index),
					'label'		=> _l(sprintf('%s_ui_kit_headline', $type)),
					'required'	=> false,
					'value'		=> $info['school_ui_kit'][$index]['headline'] ?? '',
				],
				[
					'type'		=> 'html',
					'key'		=> sprintf('%s_ui_kit[%d][message]', $type, $index),
					'label'		=> _l(sprintf('%s_ui_kit_message', $type)),
					'required'	=> false,
					'value'		=> $info['school_ui_kit'][$index]['message'] ?? '',
				],
				[
					'type'		=> 'checkbox',
					'key'		=> sprintf('%s_ui_kit[%d][kit_button][]', $type, $index),
					'label'		=> _l('school_ui_kit_button'),
					'required'	=> false,
					'value'		=> $info['school_ui_kit'][$index]['kit_button'] ?? [],
					'options'	=> [
						[
							'label'	=> _l('leaflet'),
							'value'	=> 'leaflet',
						],
						[
							'label'	=> _l('user_kit'),
							'value'	=> 'user',
						],
						[
							'label'	=> _l('teacher_kit'),
							'value'	=> 'teacher',
						],
						[
							'label'	=> _l('ebrochure'),
							'value'	=> 'ebrochure',
						],
					],
				],
			];

			++$index;
		}

		return $fields;
	}

	private function _renderReferralCommunicationFields($info = [], $type = 'user_referral') {
		$fields 	= [];

		$items 		= $info[$type] ?? [''];

		$index = 0;

		foreach ($items as $item) {
			$fields[] = [
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][type]', $type, $index),
					'label'		=> _l('type'),
					'required'	=> true,
					'value'		=> $info['user_referral'][$index]['type'] ?? '',
					'options'	=> [
						[
							'label'	=> _l('referral_welcome'),
							'value'	=> 'referral_welcome',
						],
						[
							'label'	=> _l('referrer_current_stage'),
							'value'	=> 'referrer_current_stage',
						],
						[
							'label'	=> _l('referrer_limit_exceed'),
							'value'	=> 'referrer_limit_exceed',
						],
						[
							'label'	=> _l('referrer_limit_revoked'),
							'value'	=> 'referrer_limit_revoked',
						],
					],
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][laptop_email][subject]', $type, $index),
					'label'		=> _l('laptop_email_subject'),
					'required'	=> true,
					'value'		=> $info['user_referral'][$index]['laptop_email']['subject'] ?? '',
				],
				[
					'type'		=> 'html',
					'key'		=> sprintf('%s[%d][laptop_email][message]', $type, $index),
					'label'		=> _l('laptop_email_message'),
					'required'	=> true,
					'value'		=> $info['user_referral'][$index]['laptop_email']['message'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][laptop_whatsapp][template]', $type, $index),
					'label'		=> _l('laptop_whatsapp_template_id'),
					'required'	=> false,
					'value'		=> $info['user_referral'][$index]['laptop_whatsapp']['template'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][laptop_whatsapp][message]', $type, $index),
					'label'		=> _l('laptop_whatsapp_template_message'),
					'required'	=> false,
					'value'		=> $info['user_referral'][$index]['laptop_whatsapp']['message'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][mobile_email][subject]', $type, $index),
					'label'		=> _l('mobile_email_subject'),
					'required'	=> true,
					'value'		=> $info['user_referral'][$index]['mobile_email']['subject'] ?? '',
				],
				[
					'type'		=> 'html',
					'key'		=> sprintf('%s[%d][mobile_email][message]', $type, $index),
					'label'		=> _l('mobile_email_message'),
					'required'	=> true,
					'value'		=> $info['user_referral'][$index]['mobile_email']['message'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][mobile_whatsapp][template]', $type, $index),
					'label'		=> _l('mobile_whatsapp_template_id'),
					'required'	=> false,
					'value'		=> $info['user_referral'][$index]['mobile_whatsapp']['template'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][mobile_whatsapp][message]', $type, $index),
					'label'		=> _l('mobile_whatsapp_template_message'),
					'required'	=> false,
					'value'		=> $info['user_referral'][$index]['mobile_whatsapp']['message'] ?? '',
				],
				$data['fields'][] = [
					'type'		=> 'checkbox',
					'key'		=> sprintf('%s[%d][message_type]', $type, $index),
					'label'		=> _l('message_type'),
					'required'	=> true,
					'value'		=> $info['user_referral'][$index]['message_type'] ?? '',
					'options'	=> [
						[
							'label'	=> _l('referrer_owner_of_the_link'),
							'value'	=> 'referrer_message'
						],
						[
							'label'	=> _l('referral_person_who_used_the_link'),
							'value'	=> 'referral_message'
						]
					],
				]
			];

			++$index;
		}

		return $fields;
	}

	private function _renderExhibitionCommunicationFields($info = [], $type = 'event_exhibition') {
		$fields 	= [];

		$items 		= $info[$type] ?? [''];

		$index = 0;

		foreach ($items as $item) {
			$fields[] = [
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][type]', $type, $index),
					'label'		=> _l(sprintf('%s_type', $type)),
					'required'	=> true,
					'value'		=> $info['event_exhibition'][$index]['type'] ?? '',
					'options'	=> [
						[
							'label'	=> _l('user'),
							'value'	=> 'user',
						],
						[
							'label'	=> _l('school'),
							'value'	=> 'school',
						],
						[
							'label'	=> _l('teacher'),
							'value'	=> 'teacher',
						]
					],
				],

				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][email][subject]', $type, $index),
					'label'		=> _l(sprintf('%s_email_subject', $type)),
					'required'	=> false,
					'value'		=> $info['event_exhibition'][$index]['email']['subject'] ?? '',
				],
				[
					'type'		=> 'html',
					'key'		=> sprintf('%s[%d][email][message]', $type, $index),
					'label'		=> _l(sprintf('%s_email_message', $type)),
					'required'	=> false,
					'value'		=> $info['event_exhibition'][$index]['email']['message'] ?? '',
				],
				[
					'type'		=> 'checkbox',
					'key'		=> sprintf('%s[%d][email][attachment][]', $type, $index),
					'label'		=> _l(sprintf('%s_email_attachment', $type)),
					'required'	=> false,
					'value'		=> $info['event_exhibition'][$index]['email']['attachment'] ?? [],
					'options'	=> [
						[
							'label'	=> _l('invite_pass'),
							'value'	=> 'invite_pass',
						],
					],
				],
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][whatsapp][type]', $type, $index),
					'label'		=> _l(sprintf('%s_whatsapp_type', $type)),
					'required'	=> true,
					'value'		=> $info['event_exhibition'][$index]['whatsapp']['type'] ?? '',
					'options'	=> [
						[
							'label'	=> _l('text'),
							'value'	=> 'text',
						],
						[
							'label'	=> _l('cta'),
							'value'	=> 'cta',
						],
						[
							'label'	=> _l('document'),
							'value'	=> 'document',
						],
						[
							'label'	=> _l('image'),
							'value'	=> 'image',
						],
						[
							'label'	=> _l('video'),
							'value'	=> 'video',
						],
					],
				],
				[
					'type'		=> 'image',
					'key'		=> sprintf('%s[%d][whatsapp][attachment_file]', $type, $index),
					'label'		=> _l(sprintf('%s_whatsapp_attachment_file', $type)),
					'required'	=> false,
					'value'		=> $info['event_exhibition'][$index]['whatsapp']['attachment_file'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][whatsapp][template_id]', $type, $index),
					'label'		=> _l(sprintf('%s_whatsapp_template_id', $type)),
					'required'	=> false,
					'value'		=> $info['event_exhibition'][$index]['whatsapp']['template_id'] ?? '',
				],
				[
					'type'		=> 'textarea',
					'key'		=> sprintf('%s[%d][whatsapp][message]', $type, $index),
					'label'		=> _l(sprintf('%s_whatsapp_message', $type)),
					'required'	=> false,
					'value'		=> $info['event_exhibition'][$index]['whatsapp']['message'] ?? '',
				],
				[
					'type'		=> 'select',
					'key'		=> sprintf('%s[%d][whatsapp][attachment]', $type, $index),
					'label'		=> _l(sprintf('%s_whatsapp_attachment', $type)),
					'required'	=> false,
					'value'		=> $info['event_exhibition'][$index]['whatsapp']['attachment'] ?? '',
					'options'	=> [
						[
							'label'	=> _l('invite_pass'),
							'value'	=> 'invite_pass',
						],
					],
				],
			];

			++$index;
		}

		return $fields;
	}

	private function _renderParentAcknowledgeFields($info = [], $type = 'parent_acknowledge') {
		$fields 	= [];

		$items 		= $info[$type] ?? [''];

		$index = 0;

		foreach ($items as $item) {
			$fields[] = [
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][email][subject]', $type, $index),
					'label'		=> _l(sprintf('%s_email_subject', $type)),
					'required'	=> false,
					'value'		=> $info['parent_acknowledge'][$index]['email']['subject'] ?? '',
				],
				[
					'type'		=> 'html',
					'key'		=> sprintf('%s[%d][email][message]', $type, $index),
					'label'		=> _l(sprintf('%s_email_message', $type)),
					'required'	=> false,
					'value'		=> $info['parent_acknowledge'][$index]['email']['message'] ?? '',
				],
				[
					'type'		=> 'number',
					'key'		=> sprintf('%s[%d][email][age]', $type, $index),
					'label'		=> _l(sprintf('%s_email_age', $type)),
					'required'	=> false,
					'value'		=> $info['parent_acknowledge'][$index]['email']['age'] ?? '',
				]
			];

			++$index;
		}

		return $fields;
	}

	private function _validateEventCommunicationKitForm($id = 0) {
		// $this->form_validation->set_rules('school[email][subject]', _l('school_email_subject'), 'trim|required|min_length[3]|max_length[128]');
		// $this->form_validation->set_rules('school[email][message]', _l('school_email_subject'), 'trim|required|min_length[3]|max_length[3000]');
		$this->form_validation->set_rules('teacher[email][subject]', _l('teacher_email_subject'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('teacher[email][message]', _l('teacher_email_subject'), 'trim|required|min_length[3]|max_length[3000]');
		$this->form_validation->set_rules('user[laptop_email][subject]', _l('user_laptop_email_subject'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('user[laptop_email][message]', _l('user_laptop_email_message'), 'trim|required|min_length[3]|max_length[3000]');
		$this->form_validation->set_rules('user[mobile_email][subject]', _l('user_mobile_email_subject'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('user[mobile_email][message]', _l('user_mobile_email_message'), 'trim|required|min_length[3]|max_length[3000]');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['errors'] = $this->form_validation->error_array());
	}
}
