<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Data {
	public function getCountryCode() {
		$res = $this->country_model->get_country_code($this->input->post('country'));
		$this->json['error'] 	= false;
		$this->json['code'] 	= $res['tel_code'] ?? '+1';
	}

	public function cities() {
		$this->json['cities'] = [];

		$results = $this->city_model->get_all([
			'country_code'	=> $this->config->item('site_country_code')
		]);

		foreach ($results->result_array() as $result) {
			$this->json['cities'][] = [
				'city_id'		=> $result['id'],
				'name'			=> $result['name'],
			];
		}

		array_unshift($this->json['cities'], [
			'city_id'		=> 0,
			'name'			=> _l('not_listed'),
		]);
	}

	public function centers() {
		$this->form_validation->set_rules('city_id', _l('city_id'), [
			'trim',
			'required',
			'numeric',
			['city', [$this->validate_model, 'city']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			$this->json['centers'] = [];

			$results = $this->center_model->get_all([
				'city_id'		=> $this->input->post('city_id')
			]);

			foreach ($results->result_array() as $result) {
				$this->json['centers'][] = [
					'center_id'		=> $result['id'],
					'name'			=> $result['name'],
				];
			}
		}
	}

	public function ages() {
		$this->json['ages'] = DEMO_AGES;
	}

	public function programs() {
		$this->form_validation->set_rules('grade', _l('grade'), 'trim|numeric|required|greater_than[0]|less_than[13]');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			$student_grade = $this->input->post('grade');

			$this->json['programs'] = [];

			$results = $this->course_model->get_all([
				'site_id' => $this->config->item('site_id')
			]);

			foreach ($results->result_array() as $result) {
				if ($result['status'] == 'pending') continue;

				if (
					($student_grade >= 2 && $student_grade <= 6) &&
					strpos(mb_strtolower(trim($result['title'])), 'python beginner') !== false
				) {
					$this->json['programs'][] = [
						'program_id'	=> $result['id'],
						'name'			=> $result['title'],
					];
				}

				if (
					($student_grade >= 7 && $student_grade <= 12) &&
					strpos(mb_strtolower(trim($result['title'])), 'python advance') !== false
				) {
					$this->json['programs'][] = [
						'program_id'	=> $result['id'],
						'name'			=> $result['title'],
					];
				}

				if (
					($student_grade >= 1 && $student_grade <= 3) &&
					strpos(mb_strtolower(trim($result['title'])), 'blockly beginner') !== false
				) {
					$this->json['programs'][] = [
						'program_id'	=> $result['id'],
						'name'			=> $result['title'],
					];
				}

				if (
					($student_grade >= 4 && $student_grade <= 6) &&
					strpos(mb_strtolower(trim($result['title'])), 'blockly advance') !== false
				) {
					$this->json['programs'][] = [
						'program_id'	=> $result['id'],
						'name'			=> $result['title'],
					];
				}
			}
		}
	}

	public function demoDates() {
		$this->form_validation->set_rules('mode', _l('mode'), 'trim|required|in_list[offline,online]');
		$this->form_validation->set_rules('programs', _l('programs'), [
			'trim',
			'required',
			'numeric',
			['programs', [$this->validate_model, 'program']]
		]);

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			if ($this->config->item('site_country_code') === 'AE') {
				$this->demo_dates = DEMO_DATES_IS;
			}

			$this->json['demo_dates'] = $this->demo_dates;
		}
	}

	public function demoTimes() {
		$this->form_validation->set_rules('mode', _l('mode'), 'trim|required|in_list[offline,online]');
		$this->form_validation->set_rules('programs', _l('programs'), [
			'trim',
			'required',
			'numeric',
			['programs', [$this->validate_model, 'program']]
		]);
		$this->form_validation->set_rules('demo_date', _l('demo_date'), 'trim|required');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (!$this->json) {
			$this->json['demo_times'] = $this->demo_times;
		}
	}
}
