<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait PrepSchool {
	public function getPrepSchool() {
		$this->form_validation->set_rules('school_id', _l('school_id'), [
			'trim',
			'numeric',
			'required'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!empty($school_info = $this->school_model->get($this->input->post('school_id')))) {
                $this->json['school'] = [
                    'id'                            => $school_info['id'],
                    'parent_id'                     => $school_info['parent_id'],
                    'site_id'                       => $school_info['site_id'],
                    'name'                          => $school_info['name'],
                    'site_code'                     => $school_info['site_code'],
                    'site_type'                     => $school_info['site_type'],
                    'country_code'                  => $school_info['country_code'],
                    'currency_code'                 => $school_info['currency_code'],
                    'authorized_person'             => $school_info['authorized_person'],
                    'email'                         => $school_info['owner_email'],
                    'mobile'                        => $school_info['owner_mobile'],
                    'alternate_authorized_person'   => $school_info['alternate_authorized_person'],
                    'alternate_email'               => $school_info['alternate_owner_email'],
                    'alternate_mobile'              => $school_info['alternate_owner_mobile'],
                    'country_id'                    => $school_info['country_id'],
                    'state_id'                      => $school_info['state_id'],
                    'city_id'                       => $school_info['city_id'],
                    'country'                       => $school_info['country'],
                    'state'                         => $school_info['state'],
                    'city'                          => $school_info['city'],
                    'verified'                      => $school_info['verified'],
                ];
			} else {
				$this->json['school'] = [];
			}
		}
	}

    public function getPrepSchoolByCode() {
		$this->form_validation->set_rules('site_code', _l('site_code'), [
			'trim',
			'required'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!empty($school_info = $this->school_model->getByCode($this->input->post('site_code')))) {
                $this->json['school'] = [
                    'id'                            => $school_info['id'],
                    'parent_id'                     => $school_info['parent_id'],
                    'site_id'                       => $school_info['site_id'],
                    'name'                          => $school_info['name'],
                    'site_code'                     => $school_info['site_code'],
                    'site_type'                     => $school_info['site_type'],
                    'country_code'                  => $school_info['country_code'],
                    'currency_code'                 => $school_info['currency_code'],
                    'authorized_person'             => $school_info['authorized_person'],
                    'email'                         => $school_info['owner_email'],
                    'mobile'                        => $school_info['owner_mobile'],
                    'alternate_authorized_person'   => $school_info['alternate_authorized_person'],
                    'alternate_email'               => $school_info['alternate_owner_email'],
                    'alternate_mobile'              => $school_info['alternate_owner_mobile'],
                    'country_id'                    => $school_info['country_id'],
                    'state_id'                      => $school_info['state_id'],
                    'city_id'                       => $school_info['city_id'],
                    'country'                       => $school_info['country'],
                    'state'                         => $school_info['state'],
                    'city'                          => $school_info['city'],
                    'verified'                      => $school_info['verified'],
                ];
			} else {
				$this->json['error'] = _li('school_not_found');
			}
		}
	}
    
	public function getPrepSchools() {
		$this->form_validation->set_rules('site_type', _l('site_type'), 'trim|in_list[1,3,4,5,6,7,8,9]');
		$this->form_validation->set_rules('site_code', _l('site_code'), 'trim');
		$this->form_validation->set_rules('country_id', _l('country_id'), [
			'trim',
			'numeric'
		]);
        $this->form_validation->set_rules('state_id', _l('state_id'), [
			'trim',
			'numeric'
		]);
        $this->form_validation->set_rules('city_id', _l('city_id'), [
			'trim',
			'numeric'
		]);
		$this->form_validation->set_rules('school_id', _l('school_id'), [
			'trim',
			'numeric'
		]);

		self::_runFormValidation();

		if (!$this->json) {
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

            if ($this->input->post('site_id')) {
                $filter_data['site_id'] = $this->input->post('site_id');
            }

            if ($this->input->post('site_code')) {
                $filter_data['site_code'] = $this->input->post('site_code');
            }

            if ($this->input->post('site_type')) {
                $filter_data['site_type'] = $this->input->post('site_type');
            }

            if ($this->input->post('parent_id')) {
                $filter_data['parent_id'] = $this->input->post('parent_id');
            }

            if (!empty($school_info = $this->school_model->get_all($filter_data)['rows'] ?? [])) {
                $results = array_map(function($school) {
                    return [
                        'id'                            => $school['id'],
                        'parent_id'                     => $school['parent_id'],
                        'site_id'                       => $school['site_id'],
                        'name'                          => $school['name'],
                        'site_code'                     => $school['site_code'],
                        'site_type'                     => $school['site_type'],
                        'country_code'                  => $school['country_code'],
                        'currency_code'                 => $school['currency_code'],
                        'authorized_person'             => $school['authorized_person'],
                        'email'                         => $school['owner_email'],
                        'mobile'                        => $school['owner_mobile'],
                        'alternate_authorized_person'   => $school['alternate_authorized_person'],
                        'alternate_email'               => $school['alternate_owner_email'],
                        'alternate_mobile'              => $school['alternate_owner_mobile'],
                        'country_id'                    => $school['country_id'],
                        'state_id'                      => $school['state_id'],
                        'city_id'                       => $school['city_id'],
                        'state'                         => $school['state'],
                        'city'                          => $school['city'],
                        'country'                       => $school['country'],
                        'verified'                      => $school['verified'],
                    ];
                }, $school_info);
                
                $this->json['school'] = $results;
            } else {
                $this->json['school'] = [];
            }
		}
	}
}
