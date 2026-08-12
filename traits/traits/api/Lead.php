<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Lead {
	private function _addLead() {
		$country_info = self::getCountry(true);
		$country_name = $country_info['country'];

		if (!empty($this->input->post('event_id')) && !empty($this->input->post('site_id'))) {
			$site_info = $this->site_model->get(($this->input->post('site_id')));
		} elseif ($site_info = $this->site_model->getByCode($this->input->post('source'), $this->input->post('institute_type'), $this->input->post('state_id'))) {
			$site_id = $site_info['id'];
		} else {
			$site_info = $this->site_model->getSiteByName($country_name, $this->input->post('institute_type'));
		}

		$site_id = $site_info['id'] ?? 0;

		if (empty($site_id)) {
			$site_id = $this->config->item('default_site_id');
		}

		$source = $this->input->post('source');
		$event_id = $this->input->post('event_id');

		/*if (!empty($event_id) &&
			!in_array($event_id, [10,11]) &&
			(strtolower($country_name) == 'united states') &&
			(empty($source) || (!empty($source) && (strpos(strtolower($source), 'ge-nyafus') === false)))
		) {
			$event_id 	= 9;
			$site_id 	= 2270;
			$source 	= 'ge-NYAFUS-de';
		}*/

		return $this->lead_model->add([
			'event_id'			=> (int)$event_id,
			'site_type'			=> $this->input->post('site_type') ?? 1,
			'name'				=> $this->input->post('name'),
			'parent_name'		=> $this->input->post('parent_name') ?? '',
			'source'			=> $source,
			'mobile'			=> $this->input->post('mobile') ?? '',
			'email'				=> $this->input->post('email') ?? '',
			'dob'				=> !empty($this->input->post('dob')) ? DateTime::createFromFormat('d/m/Y', $this->input->post('dob'))->format('Y-m-d') : '',
			'grade_id'			=> $this->input->post('grade_id') ?? 0,
			'section_id'		=> $this->input->post('section_id') ?? '',
			'grade'				=> $this->input->post('grade_id') ?? 0,
			'section'			=> $this->input->post('section_id') ?? '',
			'city_id'			=> (int)$this->input->post('city_id'),
			'state_id'			=> (int)$this->input->post('state_id'),
			'country_id'		=> (int)$this->input->post('country_id'),
			'location'			=> $country_name,
			'mobile_verified'	=> 0,
			'site_id'			=> (int)$site_id,
			'ip'				=> $this->input->ip_address(),
			'timezone'			=> $this->input->post('timezone') ?? '',
			'utm_source'		=> $this->input->post('utm_source') ?? '',
			'utm_medium'		=> $this->input->post('utm_medium') ?? '',
			'utm_campaign'		=> $this->input->post('utm_campaign') ?? '',
			'parent_referral_id'=> $this->input->post('referral_id') ?? 0,
			'type'				=> $this->input->post('type') ?? 'mobile',
		]);
	}

	private function _createUser($lead_info = []) {
		$explode = explode(' ', $lead_info['name'], 2);

		$first_name = array_shift($explode);
		$last_name = array_shift($explode);

		$student_id = $this->lead_model->addStudent([
			'first_name'		=> $first_name ?? '',
			'last_name'			=> $last_name ?? '',
			'lead_id'			=> $lead_info['id'],
			'email'				=> $lead_info['email'],
			'mobile'			=> $lead_info['mobile'],
			'location'			=> $lead_info['location'],
		]);

		$this->lead_model->edit($lead_info['id'], [
			'student_id'	=> (int)$student_id,
		]);
	}
}
