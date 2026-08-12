<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventSignupData {
	public function getEventInfo() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_isActiveEvent($this->input->post('event_id'))) {
				return;
			}

			$event_info 	= $this->event_model->get($this->input->post('event_id'));
			$country_info 	= $this->country_model->get($event_info['country_id']);

			if ($event_info['event_type_id'] == 1) {
				$site_info 		= $this->site_model->get($event_info['parent_site_id']);
				$state_info 	= $this->state_model->get($site_info['state_id']);
				$city_info 		= $this->city_model->get($site_info['city_id']);
			} elseif ($event_info['event_type_id'] == 6) {
				$teacher_info 	= $this->teacher_model->get($event_info['parent_site_id']);
				$site_info 		= $this->site_model->get($teacher_info['site_id']);
				$state_info 	= $this->state_model->get($teacher_info['state_id']);
				$city_info 		= $this->city_model->get($teacher_info['city_id']);
			}

			$this->json['country'] = [
				'id'	=> $country_info['id'],
				'code'	=> strtolower($country_info['code']),
				'name'	=> $country_info['name'],
			];

			$this->json['state'] = [
				'id'	=> $state_info['id'],
				'name'	=> $state_info['name'],
			];

			$this->json['city'] = [
				'id'	=> $city_info['id'],
				'name'	=> $city_info['name'],
			];

			$this->json['school'] = [
				'id'	=> $site_info['id'] ?? 0,
				'name'	=> $site_info['name'] ?? _l('independent_educator'),
			];

			$this->json['teacher'] = [
				'id'	=> $teacher_info['id'] ?? 0,
				'name'	=> $teacher_info['first_name'] ?? '',
			];

			if ($event_info['event_type_id'] == 1) {
				$grades	= $event_info['country_code'] === 'GB'
					? [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13]
					: [1, 2, 3, 5, 6, 7, 8, 9, 10, 11, 12]
				;
			} elseif ($event_info['event_type_id'] == 6) {
				$grades	= explode(',', $teacher_info['section_id']);
			}
			
			$this->json['grades'] = $grades;
			
			$this->json['event'] 	= [
				'id' 					=> $event_info['id'],
				'label' 				=> $event_info['label'],
				'event_name' 			=> $event_info['name'],
				'theme_category_ids' 	=> $event_info['category_ids'] ?? '',
				'event_type_id' 		=> $event_info['event_type_id'],
				'start_date' 			=> $event_info['start_date'],
				'end_date' 				=> $event_info['end_date'],
				'student_reg_end_date' 	=> $event_info['student_reg_end_date'],
				'school_reg_end_date' 	=> $event_info['school_reg_end_date'],
				'book_writing_end_date' => $event_info['book_writing_end_date'],
				'selling_end_date' 		=> $event_info['selling_end_date'],
			];
		}
	}

	private function _isActiveEvent($event_id = 0, $key = 'student_reg_end_date') {
		$event_info = $this->event_model->get($event_id);

		if (
			$event_info['end_date'] > date('Y-m-d H:i:s') &&
			$event_info[$key] > date('Y-m-d H:i:s')
		) {
			return true;
		}

		$this->json['error'] = _l('event_expired');

		return false;
	}
}
