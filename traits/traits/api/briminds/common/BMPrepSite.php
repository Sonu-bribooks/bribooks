<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BMPrepSite {
	public function getBMPrepSites() {
		$this->form_validation->set_rules('state_id', _l('state_id'), [
			'trim',
			'numeric',
		]);

		$this->form_validation->set_rules('city_id', _l('city_id'), [
			'trim',
			'numeric',
			'required',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('briminds/school/BMSite_model', 'bm_site_model');

            $filter_data = [];

			if ($this->input->post('country_id')) {
				$filter_data['country_id'] = $this->input->post('country_id');
			}

			if ($this->input->post('city_id')) {
				$filter_data['city_id'] = $this->input->post('city_id');
			}

			if ($this->input->post('state_id')) {
				$filter_data['state_id'] = $this->input->post('state_id');
			}

            if (!empty($sites = $this->bm_site_model->get_all($filter_data)['rows'] ?? [])) {
				$results = array_map(function($school) {
					return [
						'id'						=> $school['id'],
						'parent_id'					=> $school['parent_id'],
						'site_id'					=> $school['site_id'],
						'name'						=> $school['name'],
						'site_code'					=> $school['site_code'],
						'site_type'					=> $school['site_type'],
						'country_code'				=> $school['country_code'],
						'currency_code'				=> $school['currency_code'] ?? '',
						'authorized_person'			=> '',
						'email'						=> '',
						'mobile'					=> '',
						'alternate_authorized_person'=> '',
						'alternate_email'			=> '',
						'alternate_mobile'			=> '',
						'country_id'				=> $school['country_id'],
						'state_id'					=> $school['state_id'],
						'city_id'					=> $school['city_id'],
						'country'					=> $school['country'] ?? '',
						'state'						=> $school['state'] ?? '',
						'city'						=> $school['city'] ?? '',
						'verified'					=> $school['verified'],
					];
				}, $sites);

				$this->json['school'] = $results;
			} else {
				$this->json['school'] = [];
			}
		}
	}

	public function getBMSite() {
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'numeric',
			'required',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('briminds/school/BMSite_model', 'bm_site_model');

            if (!empty($site_info = $this->bm_site_model->get($this->input->post('site_id')))) {
				$result = [
					'id'						=> $site_info['id'],
					'parent_id'					=> $site_info['parent_id'],
					'site_id'					=> $site_info['id'],
					'name'						=> $site_info['name'],
					'site_code'					=> $site_info['site_code'],
					'site_type'					=> $site_info['site_type'],
					'country_code'				=> $site_info['country_code'],
					'currency_code'				=> $site_info['currency_code'] ?? '',
					'authorized_person'			=> '',
					'email'						=> '',
					'mobile'					=> '',
					'alternate_authorized_person'=> '',
					'alternate_email'			=> '',
					'alternate_mobile'			=> '',
					'country_id'				=> $site_info['country_id'],
					'state_id'					=> $site_info['state_id'],
					'city_id'					=> $site_info['city_id'],
					'country'					=> $site_info['country'] ?? '',
					'state'						=> $site_info['state'] ?? '',
					'city'						=> $site_info['city'] ?? '',
					'verified'					=> $site_info['verified'] ?? '',
				];

				$this->json['school'] = $result;
			} else {
				$this->json['school'] = [];
			}
		}
	}
}
