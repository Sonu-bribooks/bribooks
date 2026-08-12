<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Brochure {
	private function _getBrochure($data = []) {
		$stage 				= $data['stage'] ?? 'brochure';
		$event_info 		= $data['info'] ?? [];
		$country_info 		= $data['country_info'] ?? [];
		$event_type_info 	= $data['event_type_info'] ?? [];

		$info 				= $this->event_brochure_model->get_all([
			'event_id'	=> $event_info['id']
		])['rows'][0] ?? [];

		if (!empty($info['personal_note'])) {
			$info['personal_note'] = json_decode($info['personal_note'], true);
		}

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'user_header',
			'label'		=> _l('user_kit_header'),
			'required'	=> false,
			'value'		=> $info['user_header'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'user_footer',
			'label'		=> _l('user_kit_footer'),
			'required'	=> false,
			'value'		=> $info['user_footer'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'user_content',
			'label'		=> _l('user_kit_content'),
			'required'	=> true,
			'value'		=> $info['user_content'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'teacher_header',
			'label'		=> _l('teacher_kit_header'),
			'required'	=> false,
			'value'		=> $info['teacher_header'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'teacher_footer',
			'label'		=> _l('teacher_kit_footer'),
			'required'	=> false,
			'value'		=> $info['teacher_footer'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'teacher_content',
			'label'		=> _l('teacher_kit_content'),
			'required'	=> true,
			'value'		=> $info['teacher_content'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'leaflet',
			'label'		=> _l('leaflet'),
			'required'	=> false,
			'value'		=> $info['leaflet'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'personal_note[header]',
			'label'		=> _l('personal_note_header'),
			'required'	=> false,
			'value'		=> $info['personal_note']['header'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'personal_note[body]',
			'label'		=> _l('personal_note_content'),
			'required'	=> false,
			'value'		=> $info['personal_note']['body'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'personal_note[footer]',
			'label'		=> _l('personal_note_footer'),
			'required'	=> false,
			'value'		=> $info['personal_note']['footer'] ?? '',
		];

		$data['ebrochure'] 			= json_decode($info['ebrochure'], true);
		$data['ebrochure_dynamic'] 	= $info['ebrochure_dynamic'];

		if (empty($data['ebrochure'])) {
			$data['ebrochure'] = [''];
		}

		$data['action'] = !empty($event_info)
			? base_url('admin/ajax_event_brochure_crud/edit/' . $event_info['id'])
			: base_url('admin/ajax_event_brochure_crud/add/' . $event_info['id'])
		;

		$data['fields'] = $this->load->view('backend/admin/event/stage/generic', $data, true);

		$this->load->view(sprintf('backend/admin/event/stage/%s', $stage), $data);
	}

	public function ajax_event_brochure_crud($action = NULL, $id = 0) {
		$this->json = [];

		self::_validateEventBrochureForm($id);

		if (empty($this->json['errors'])) {
			$data = $this->input->post(NULL, FALSE);

			$data['user_content'] 		= _allowSpecificHtmlTags($data['user_content']);
			$data['teacher_content'] 	= _allowSpecificHtmlTags($data['teacher_content']);

			if (is_array($data['ebrochure'])) {
				$data['ebrochure'] = json_encode($data['ebrochure']);
			}

			if (is_array($data['personal_note'])) {
				$data['personal_note'] = json_encode($data['personal_note']);
			}

			$data['event_id'] = (int)$id;

			if (!empty($info = $this->event_brochure_model->get_all([
				'event_id'	=> $id,
			])['rows'][0] ?? [])) {
				$this->event_brochure_model->edit($info['id'], $data);
			} else {
				$this->event_brochure_model->add($data);
			}
		}

		if (!empty($this->json['errors'])) {
			$this->json['error'] = _l('error_occured');
		} else {
			$this->json['success'] = _l('success');
		}

		output_json($this->json);
	}

	private function _validateEventBrochureForm($id = 0) {
		$this->form_validation->set_rules('user_content', _l('user_kit_content'), 'trim|required');
		$this->form_validation->set_rules('teacher_content', _l('teacher_kit_content'), 'trim|required');
		$this->form_validation->set_rules('ebrochure[]', _l('ebrochure'), 'trim|required');
		$this->form_validation->set_rules('ebrochure_dynamic', _l('ebrochure_dynamic_image_index'), 'trim|required|numeric');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['errors'] = $this->form_validation->error_array());
	}
}
