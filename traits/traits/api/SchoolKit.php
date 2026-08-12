<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait SchoolKit {
	public function getEventSchoolLandingPageKit() {
		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');

			$site_info  = $this->site_model->get($this->input->post('site_id'));
			$event_info = $this->event_model->get($this->input->post('event_id'));

			$communication_kit_info = $this->event_communication_kit_model->get_all([
				'event_id' => (int)$this->input->post('event_id')
			])['rows'][0]['school_ui_kit'] ?? [];

			$event_site_info = $this->event_site_model->get_all([
				'site_id'  => $this->input->post('site_id'),
				'event_id' => $this->input->post('event_id'),
			])['rows'][0] ?? [];

			if (empty($event_info) || empty($site_info) || empty($event_site_info) || empty($communication_kit_info)) {
				return $this->json['error'] = _l('invalid_url');
			}

			$communication_kit_info = json_decode($communication_kit_info, true);
			$communication_kit_info = $communication_kit_info[0] ?? [];

			$state_info = $this->state_model->get($site_info['state_id']);
			$city_info  = $this->city_model->get($site_info['city_id']);

			$student_url 			= vsprintf('%sevents/student/signup/%s?sid=%d', [
				$event_info['url'],
				$event_info['slug'],
				$site_info['id']
			]);

			$data = [
				'school_name' 		=> $site_info['name'] ?? '',
				'owner_name' 		=> $site_info['owner_name'] ?? '',
				'authorized_person' => $site_info['authorized_person'] ?? '',
				'mobile' 			=> $site_info['owner_mobile'] ?? '',
				'email' 			=> $site_info['owner_email'] ?? '',
				'state' 			=> $state_info['name'] ?? '',
				'city' 				=> $city_info['name'] ?? '',
				'student_url' 		=> $student_url,
				'student_url_link' 	=> sprintf('<a href="%s" target="_blank" style="color:blue">%s</a>', $student_url, $student_url),
			];

			$kit_buttons = [];

			if (!empty($communication_kit_info['kit_button'] ?? [])) {
				$base_url = USER_INVOICE_URL . 'api';

				$kit_map = [
					'user'	  	=> ['key' => 'parent_kit_url', 'endpoint' => 'getSchoolParentKit'],
					'teacher'   => ['key' => 'teacher_kit_url', 'endpoint' => 'getSchoolTeacherKit'],
					'ebrochure' => ['key' => 'ebrochure_url', 'endpoint' => 'getSchoolBrochure'],
					'leaflet'   => ['key' => 'leaflet_url', 'endpoint' => 'getSchoolStudentLeaflet'],
				];

				foreach ($communication_kit_info['kit_button'] as $kit) {
					if (isset($kit_map[$kit])) {
						$kit_buttons[$kit] = sprintf(
							'%s/%s/%d/%d',
							$base_url,
							$kit_map[$kit]['endpoint'],
							$site_info['id'],
							$event_info['id']
						);
					}
				}
			}

			$data['message']							= format_message_with_data($communication_kit_info['message'], $data);
			$this->json['school_ui_kit'] 				= $data;
			$this->json['school_ui_kit']['kit_button'] 	= $kit_buttons;
		}
	}
}
