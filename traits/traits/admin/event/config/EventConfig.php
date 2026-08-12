<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventConfig {
	private function _getConfig($data = []) {
		$stage 				= $data['stage'] ?? 'config';
		$event_info 		= $data['info'] ?? [];
		$country_info 		= $data['country_info'] ?? [];
		$event_type_info 	= $data['event_type_info'] ?? [];

		$info 				= $this->event_config_model->get_all([
			'event_id'	=> $event_info['id']
		])['rows'][0] ?? [];

		if (!empty($info['partners'])) {
			$info['partners'] = explode(',', $info['partners']);
		}

		if (!empty($info['awards'])) {
			$info['awards'] = json_decode($info['awards'], true);
		}

		if (!empty($info['grades'])) {
			$info['grades'] = json_decode($info['grades'], true);
		}

		if (!empty($info['genre'])) {
			$info['genre'] = json_decode($info['genre'], true);
		}

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'event_logo',
			'label'		=> _l('event_logo'),
			'required'	=> true,
			'value'		=> $info['event_logo'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'logo_dark',
			'label'		=> _l('event_logo_dark'),
			'required'	=> true,
			'value'		=> $info['logo_dark'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'logo_light',
			'label'		=> _l('event_logo_light'),
			'required'	=> true,
			'value'		=> $info['logo_light'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'checkbox',
			'key'		=> 'partners[]',
			'label'		=> _l('partners'),
			'required'	=> true,
			'value'		=> $info['partners'] ?? [],
			'options'	=> array_map(function ($item) {
				return [
					'label'	=> $item['name'],
					'value'	=> $item['id'],
				];
			}, $this->event_partner_model->get_all(['order' => 'ASC'])['rows'] ?? []),
		];

		$data['config_fields']['basic'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		unset($data['fields']);

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'awards_school_group',
			'label'		=> _l('school_award_group'),
			'required'	=> false,
			'fields'	=> self::_renderAwardFields($info, 'school'),
		];

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'awards_teacher_group',
			'label'		=> _l('teacher_award_group'),
			'required'	=> false,
			'fields'	=> self::_renderAwardFields($info, 'teacher'),
		];

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'awards_user_group',
			'label'		=> _l('user_award_group'),
			'required'	=> false,
			'fields'	=> self::_renderAwardFields($info, 'user'),
		];

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'awards_sub_user_group',
			'label'		=> _l('awards_sub_user_group'),
			'required'	=> false,
			'fields'	=> self::_renderAwardFields($info, 'sub_user'),
		];

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'user_grade_group',
			'label'		=> _l('user_grade_group'),
			'required'	=> false,
			'fields'	=> self::_renderGradeFields($info, 'user'),
		];

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'teacher_grade_group',
			'label'		=> _l('teacher_grade_group'),
			'required'	=> false,
			'fields'	=> self::_renderGradeFields($info, 'teacher'),
		];

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'genre',
			'label'		=> _l('genre'),
			'required'	=> false,
			'fields'	=> self::_renderGenreFields($info, 'user'),
		];

		// pr($data, 1);

		$data['config_fields']['awards'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		$data['action'] = !empty($info)
			? base_url('admin/ajax_event_config_crud/edit/' . $event_info['id'])
			: base_url('admin/ajax_event_config_crud/add/' . $event_info['id'])
		;

		$this->load->view(sprintf('backend/admin/event/stage/%s', $stage), $data);
	}

	private function _renderAwardFields($info = [], $type = 'school') {
		$fields 	= [];
		$options 	= array_map(function ($item) {
			return [
				'label'	=> $item['name'],
				'value'	=> $item['id'],
			];
		}, $this->event_award_group_model->get_all([
			'type' 	=> $type,
			'order' => 'ASC'
			])['rows'] ?? []);
		$items 		= $info['awards'][$type] ?? [''];

		foreach ($items  as $index => $item) {
			if ($type == 'user') {
				$fields[] = [
					[
						'type'		=> 'text',
						'key'		=> sprintf('awards[%s][%d][name]', $type, $index),
						'label'		=> _l(sprintf('%s_award_group', $type)),
						'required'	=> false,
						'value'		=> $info['awards'][$type][$index]['name'] ?? '',
					],
					[
						'type'		=> 'text',
						'key'		=> sprintf('awards[%s][%d][title]', $type, $index),
						'label'		=> _l(sprintf('%s_award_title', $type)),
						'required'	=> false,
						'value'		=> $info['awards'][$type][$index]['title'] ?? '',
					],
					[
						'type'		=> 'checkbox',
						'key'		=> sprintf('awards[%s][%d][awards][]', $type, $index),
						'label'		=> _l(sprintf('%s_awards', $type)),
						'required'	=> false,
						'value'		=> $info['awards'][$type][$index]['awards'] ?? [],
						'options'	=> $options
					],
				];
			} elseif ($type == 'sub_user') {
				$fields[] = [
					[
						'type'		=> 'text',
						'key'		=> sprintf('awards[%s][%d][name]', $type, $index),
						'label'		=> _l(sprintf('%s_award_group', $type)),
						'required'	=> false,
						'value'		=> $info['awards'][$type][$index]['name'] ?? '',
					],
					[
						'type'		=> 'text',
						'key'		=> sprintf('awards[%s][%d][title]', $type, $index),
						'label'		=> _l(sprintf('%s_award_title', $type)),
						'required'	=> false,
						'value'		=> $info['awards'][$type][$index]['title'] ?? '',
					],
					[
						'type'		=> 'checkbox',
						'key'		=> sprintf('awards[%s][%d][awards][]', $type, $index),
						'label'		=> _l(sprintf('%s_awards', $type)),
						'required'	=> false,
						'value'		=> $info['awards'][$type][$index]['awards'] ?? [],
						'options'	=> $options
					],
				];
			} else {
				$fields[] = [
					[
						'type'		=> 'text',
						'key'		=> sprintf('awards[%s][%d][name]', $type, $index),
						'label'		=> _l(sprintf('%s_award_group', $type)),
						'required'	=> false,
						'value'		=> $info['awards'][$type][$index]['name'] ?? '',
					],
					[
						'type'		=> 'checkbox',
						'key'		=> sprintf('awards[%s][%d][awards][]', $type, $index),
						'label'		=> _l(sprintf('%s_awards', $type)),
						'required'	=> false,
						'value'		=> $info['awards'][$type][$index]['awards'] ?? [],
						'options'	=> $options
					],
				];
			}
			
		}

		return $fields;
	}

	private function _renderGradeFields($info = [], $type = 'user') {
		$fields 	= [];
		
		$items 		= $info['grades'][$type] ?? [''];

		foreach ($items  as $index => $item) {
			$fields[] = [
				[
					'type'		=> 'text',
					'key'		=> sprintf('grades[%s][%d][title]', $type, $index),
					'label'		=> _l(sprintf('%s_grade_title', $type)),
					'required'	=> false,
					'value'		=> $info['grades'][$type][$index]['title'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('grades[%s][%d][grade]', $type, $index),
					'label'		=> _l(sprintf('%s_grade_value', $type)),
					'required'	=> false,
					'value'		=> $info['grades'][$type][$index]['grade'] ?? '',
				],
			];
			
		}

		return $fields;
	}

	private function _renderGenreFields($info = [], $type = 'user') {
		$fields 	= [];
		
		$items 		= $info['genre'][$type] ?? [''];

		foreach ($items  as $index => $item) {
			$fields[] = [
				[
					'type'		=> 'text',
					'key'		=> sprintf('genre[%s][%d][title]', $type, $index),
					'label'		=> _l(sprintf('%s_genre_title', $type)),
					'required'	=> false,
					'value'		=> $item['title'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('genre[%s][%d][start_date]', $type, $index),
					'label'		=> _l(sprintf('%s_start_date', $type)),
					'required'	=> false,
					'datetime'	=> true,
					'value'		=> $item['start_date'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('genre[%s][%d][end_date]', $type, $index),
					'label'		=> _l(sprintf('%s_end_date', $type)),
					'required'	=> false,
					'datetime'	=> true,
					'value'		=> $item['end_date'] ?? '',
				],
				[
					'type'		=> 'checkbox',
					'key'		=> sprintf('genre[%s][%d][genre_ids][]', $type, $index),
					'label'		=> _l(sprintf('%s_select_genre', $type)),
					'required'	=> false,
					'value'		=> $item['genre_ids'] ?? [],
					'options'	=> array_map(function ($item) {
						return [
							'label'	=> $item['name'],
							'value'	=> $item['id'],
						];
					}, $this->genre_model->get_all(['parent_id' => 0, 'order' => 'ASC'])['rows'] ?? []),
				],
			];
			
		}

		return $fields;
	}

	public function ajax_event_config_crud($action = NULL, $event_id = 0) {
		$this->json = [];

		self::_validateEventConfigForm($action);

		if (empty($this->json['errors'])) {
			$data = $this->input->post();

			if (is_array($data['partners'])) {
				$data['partners'] = implode(',', $data['partners']);
			}

			if (is_array($data['awards'])) {
				$data['awards'] = json_encode($data['awards']);
			}

			if (is_array($data['grades'])) {
				$data['grades'] = json_encode($data['grades']);
			}

			if (is_array($data['genre'])) {
				$data['genre'] = json_encode($data['genre']);
			}

			$data['event_id'] = (int)$event_id;

			if (!empty($info = $this->event_config_model->get_all([
				'event_id'	=> $event_id,
			])['rows'][0] ?? [])) {
				$this->event_config_model->edit($info['id'], $data);
			} else {
				$this->event_config_model->add($data);
			}
		}

		if (!empty($this->json['errors'])) {
			$this->json['error'] = _l('error_occured');
		} else {
			$this->json['success'] = _l('success');
		}

		output_json($this->json);
	}

	private function _validateEventConfigForm($action = 'add') {
		$this->form_validation->set_rules('logo_dark', _l('event_logo_dark'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('logo_light', _l('event_logo_light'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('partners[]', _l('event_partners'), 'trim|required');
		$this->form_validation->set_rules('grades[]', _l('gardes'), 'trim|required');
		$this->form_validation->set_rules('genre[]', _l('genre'), 'trim|required');
		$this->form_validation->set_rules('event_logo', _l('event_logo'), 'trim|required|min_length[3]|max_length[128]');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['errors'] = $this->form_validation->error_array());
	}
}
