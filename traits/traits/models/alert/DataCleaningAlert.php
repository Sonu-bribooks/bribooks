<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait DataCleaningAlert {
	public function updateRegisterdSite() {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');
        return;

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/school_clean/NYAF_2024_Registered.csv');
		$rows = $this->parsecsv->data;

		$results = [];

		foreach ($rows as $row) {

			if (!empty($row['site_id']) && !empty($row['school_name']) && !empty($row['state_id']) && !empty($row['city_id'])) {
				$this->site_model->editById($row['site_id'], [
					'name'			=> trim($row['school_name']),
					'state_id'		=> $row['state_id'],
					'city_id'		=> $row['city_id'],
					'site_code' 	=> get_site_code_slug(trim($row['school_name'])) . "-" . $row['site_id'],
					'parent_id'		=> 1
				]);

                $site_info = [];

                $country_site_info = $this->site_model->getSiteByName('India');

                if (!empty($country_site_info)) {
                    $site_info = $this->site_model->get($country_site_info['id']);
                }

                $insert_school_data = [
                    'parent_id' 		  			=> $row['parent_id'] ?? 0,
                    'site_id' 		  				=> $row['site_id'] ?? 0,
                    'name' 				  			=> trim($row['school_name']),
                    'site_code' 		  			=> get_site_code_slug(trim($row['school_name'])) . "-" . $row['site_id'],
                    'site_type' 		  			=> $row['site_type'] ?? 1,
                    'discount_code' 	  			=> $site_info['discount_code'] ?? 0,
                    'discount_percentage' 			=> $site_info['discount_percentage'] ?? 0,
                    'timezone' 			  			=> $site_info['timezone'] ?? '',
                    'payment_gateway' 	  			=> $site_info['payment_gateway'] ?? '',
                    'sms_gateway' 		  			=> $site_info['sms_gateway'] ?? '',
                    'email_alert' 		  			=> $site_info['email_alert'] ?? '',
                    'address' 			  			=> $row['address'] ?? '',
                    'landmark' 			  			=> $row['landmark'] ?? '',
                    'pincode' 			  			=> $row['zipcode'] ?? '',
                    'mobile_length' 	  			=> $site_info['mobile_length'] ?? '',
                    'country_code' 		  			=> $site_info['country_code'] ?? '',
                    'currency_code' 	  			=> $site_info['currency_code'] ?? '',
                    'state_id' 			  			=> $row['state_id'] ?? 0,
                    'city_id' 			  			=> $row['city_id'] ?? 0,
                    'base_price' 		  			=> $site_info['base_price'] ?? 0,
                    'ebook_price' 		  			=> $site_info['ebook_price'] ?? 0,
                    'price_per_page' 	  			=> $site_info['price_per_page']  ?? 0,
                    'free_page_limit' 	  			=> $site_info['free_page_limit'] ?? 0,
                    'hard_cover_price' 	  			=> $site_info['hard_cover_price'] ?? 0,
                    'paperback_price' 	  			=> $site_info['paperback_price'] ?? 0,
                    'tax' 				  			=> $site_info['tax'] ?? '',
                    'tax_text' 			  			=> $site_info['tax_text'] ?? '',
                    'owner_name' 	      			=> !empty($row['owner_name']) ? trim($row['owner_name']) : '',
                    'authorized_person'   			=> !empty($row['authorized_person']) ? trim($row['authorized_person']) : '',
                    'owner_email' 		  			=> $row['email'] ?? '',
                    'owner_mobile' 	      			=> $row['mobile'] ?? '',
                    'alternate_authorized_person'   => $row['alternate_authorized_person'] ?? '',
                    'alternate_owner_email' 		=> $row['alternate_email'] ?? '',
                    'alternate_owner_mobile' 	    => $row['alternate_mobile'] ?? '',
                    'status' 			  			=> 1,
                    'verified' 			  			=> 1,
                    'license_total' 	  			=> 1000,
                    'license_used' 		  			=> 0,
                ];

                $school_id = $this->school_model->add($insert_school_data);

                if (!empty($school_id)) {
                    $this->school_model->edit($school_id, [
                        'site_code' => get_site_code_slug(trim($row['school_name'])) . "-" . $school_id
                    ]);
                }
			}
		}
	}

	public function updateUnRegisterdSite() {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');
        return;

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/school_clean/NYAF_2024_Unregistered.csv');
		$rows = $this->parsecsv->data;

		foreach ($rows as $row) {

			if (!empty($row['site_id']) && !empty($row['school_name']) && !empty($row['state_id']) && !empty($row['city_id'])) {
				$this->site_model->editById($row['site_id'], [
					'name'			=> trim($row['school_name']),
					'state_id'		=> $row['state_id'],
					'city_id'		=> $row['city_id'],
					'site_code' 	=> get_site_code_slug(trim($row['school_name'])) . "-" . $row['site_id'],
					'parent_id'		=> 1
				]);

                $site_info = [];

                $country_site_info = $this->site_model->getSiteByName('India');

                if (!empty($country_site_info)) {
                    $site_info = $this->site_model->get($country_site_info['id']);
                }

                $insert_school_data = [
                    'parent_id' 		  			=> $row['parent_id'] ?? 0,
                    'site_id' 		  				=> $row['site_id'] ?? 0,
                    'name' 				  			=> trim($row['school_name']),
                    'site_code' 		  			=> get_site_code_slug(trim($row['school_name'])) . "-" . $row['site_id'],
                    'site_type' 		  			=> $row['site_type'] ?? 1,
                    'discount_code' 	  			=> $site_info['discount_code'] ?? 0,
                    'discount_percentage' 			=> $site_info['discount_percentage'] ?? 0,
                    'timezone' 			  			=> $site_info['timezone'] ?? '',
                    'payment_gateway' 	  			=> $site_info['payment_gateway'] ?? '',
                    'sms_gateway' 		  			=> $site_info['sms_gateway'] ?? '',
                    'email_alert' 		  			=> $site_info['email_alert'] ?? '',
                    'address' 			  			=> $row['address'] ?? '',
                    'landmark' 			  			=> $row['landmark'] ?? '',
                    'pincode' 			  			=> $row['zipcode'] ?? '',
                    'mobile_length' 	  			=> $site_info['mobile_length'] ?? '',
                    'country_code' 		  			=> $site_info['country_code'] ?? '',
                    'currency_code' 	  			=> $site_info['currency_code'] ?? '',
                    'state_id' 			  			=> $row['state_id'] ?? 0,
                    'city_id' 			  			=> $row['city_id'] ?? 0,
                    'base_price' 		  			=> $site_info['base_price'] ?? 0,
                    'ebook_price' 		  			=> $site_info['ebook_price'] ?? 0,
                    'price_per_page' 	  			=> $site_info['price_per_page']  ?? 0,
                    'free_page_limit' 	  			=> $site_info['free_page_limit'] ?? 0,
                    'hard_cover_price' 	  			=> $site_info['hard_cover_price'] ?? 0,
                    'paperback_price' 	  			=> $site_info['paperback_price'] ?? 0,
                    'tax' 				  			=> $site_info['tax'] ?? '',
                    'tax_text' 			  			=> $site_info['tax_text'] ?? '',
                    'owner_name' 	      			=> !empty($row['owner_name']) ? trim($row['owner_name']) : '',
                    'authorized_person'   			=> !empty($row['authorized_person']) ? trim($row['authorized_person']) : '',
                    'owner_email' 		  			=> $row['email'] ?? '',
                    'owner_mobile' 	      			=> $row['mobile'] ?? '',
                    'alternate_authorized_person'   => $row['alternate_authorized_person'] ?? '',
                    'alternate_owner_email' 		=> $row['alternate_email'] ?? '',
                    'alternate_owner_mobile' 	    => $row['alternate_mobile'] ?? '',
                    'status' 			  			=> 1,
                    'license_total' 	  			=> 1000,
                    'license_used' 		  			=> 0,
                ];

                $school_id = $this->school_model->add($insert_school_data);

                if (!empty($school_id)) {
                    $this->school_model->edit($school_id, [
                        'site_code' => get_site_code_slug(trim($row['school_name'])) . "-" . $school_id
                    ]);
                }
			}
		}
	}

    public function insertEmergingSchool() {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');
        return;

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/school_clean/NYAF_2024_Emerging.csv');
		$rows = $this->parsecsv->data;

		foreach ($rows as $row) {

			if (!empty($row) && !empty($row['school_name'])) {

                // $row['country'] = $country;

                log_kb([
                    'insertEmergingSchool' => $row
                ]);

                if (!empty($row['email']) && !empty($this->site_model->get_all([
                    'owner_email' 			=> trim($row['email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['mobile']) && !empty($this->site_model->get_all([
                    'owner_mobile' => trim($row['mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_email']) && !empty($this->site_model->get_all([
                    'owner_email' => trim($row['alternate_email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_mobile']) && !empty($this->site_model->get_all([
                    'owner_mobile' => trim($row['alternate_mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                }

                $site_id = self::saveSiteData($row);

                if (!empty($site_id)) {
                    log_kb([
                        'insertEmergingSchool-site_id' => $site_id
                    ]);
                    $row['site_id'] = $site_id;
                    $site_id = self::saveSchoolData($row);
                }
			}
		}
	}

    public function insertSmartSchool() {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/school_clean/NYAF_2024_Smart.csv');
		$rows = $this->parsecsv->data;
        return;

		foreach ($rows as $row) {

			if (!empty($row) && !empty($row['school_name'])) {

                // $row['country'] = $country;

                if (!empty($row['email']) && !empty($this->site_model->get_all([
                    'owner_email' 			=> trim($row['email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['mobile']) && !empty($this->site_model->get_all([
                    'owner_mobile' => trim($row['mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_email']) && !empty($this->site_model->get_all([
                    'owner_email' => trim($row['alternate_email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_mobile']) && !empty($this->site_model->get_all([
                    'owner_mobile' => trim($row['alternate_mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                }

                $site_id = self::saveSiteData($row);

                if (!empty($site_id)) {
                    $row['site_id'] = $site_id;
                    $site_id = self::saveSchoolData($row);
                }
			}
		}
	}

    public function insertVintageSchool() {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');
        return;

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/school_clean/NYAF_2024_Vintage.csv');
		$rows = $this->parsecsv->data;


		foreach ($rows as $row) {

			if (!empty($row) && !empty($row['school_name'])) {

                // $row['country'] = $country;

                if (!empty($row['email']) && !empty($this->site_model->get_all([
                    'owner_email' 			=> trim($row['email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['mobile']) && !empty($this->site_model->get_all([
                    'owner_mobile' => trim($row['mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_email']) && !empty($this->site_model->get_all([
                    'owner_email' => trim($row['alternate_email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_mobile']) && !empty($this->site_model->get_all([
                    'owner_mobile' => trim($row['alternate_mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                }

                $site_id = self::saveSiteData($row);

                if (!empty($site_id)) {
                    $row['site_id'] = $site_id;
                    $site_id = self::saveSchoolData($row);
                }
			}
		}
	}

    public function insertSchoolChainSchool() {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');
        return;

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/school_clean/NYAF_2024_School_Chain.csv');
		$rows = $this->parsecsv->data;


		foreach ($rows as $row) {

			if (!empty($row) && !empty($row['school_name'])) {

                // $row['country'] = $country;

                if (!empty($row['email']) && !empty($this->site_model->get_all([
                    'owner_email' 			=> trim($row['email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['mobile']) && !empty($this->site_model->get_all([
                    'owner_mobile' => trim($row['mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_email']) && !empty($this->site_model->get_all([
                    'owner_email' => trim($row['alternate_email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_mobile']) && !empty($this->site_model->get_all([
                    'owner_mobile' => trim($row['alternate_mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                }

                $site_id = self::saveSiteData($row);

                if (!empty($site_id)) {
                    $row['site_id'] = $site_id;
                    $site_id = self::saveSchoolData($row);
                }
			}
		}
	}

    public function insertTSMSchool() {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/school_clean/NYAF_2024_TSM.csv');
		$rows = $this->parsecsv->data;
        return;

		foreach ($rows as $row) {
            log_kb([
                'insertTSMSchool' => $row
            ]);

			if (!empty($row) && !empty($row['school_name'])) {

                $row['email'] = self::cleanEmail($row['email']);
                $row['mobile'] = self::cleanMobileParam($row['mobile']);
                $row['alternate_email'] = self::cleanEmail($row['alternate_email']);
                $row['alternate_mobile'] = self::cleanMobileParam($row['alternate_mobile']);

                log_kb([
                    'insertTSMSchool-data' => $row
                ]);

                if (!empty($row['email']) && !empty($this->school_model->get_all([
                    'owner_email' 			=> trim($row['email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['mobile']) && !empty($this->school_model->get_all([
                    'owner_mobile' => trim($row['mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_email']) && !empty($this->school_model->get_all([
                    'owner_email' => trim($row['alternate_email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_mobile']) && !empty($this->school_model->get_all([
                    'owner_mobile' => trim($row['alternate_mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_email']) && !empty($this->school_model->get_all([
                    'owner_email' 			=> trim($row['alternate_email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_mobile']) && !empty($this->school_model->get_all([
                    'owner_mobile' => trim($row['alternate_mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_email']) && !empty($this->school_model->get_all([
                    'alternate_owner_email' => trim($row['alternate_email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_mobile']) && !empty($this->school_model->get_all([
                    'alternate_owner_mobile' => trim($row['alternate_mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                }

                self::saveSchoolData($row);
			}
		}
	}

    public function insertTSMFreshSchool() {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/school_clean/NYAF_2024_TSMFresh.csv');
		$rows = $this->parsecsv->data;
        return;
		foreach ($rows as $row) {

			if (!empty($row) && !empty($row['school_name'])) {

                $row['email'] = self::cleanEmail($row['email']);
                $row['mobile'] = self::cleanMobileParam($row['mobile']);
                $row['alternate_email'] = self::cleanEmail($row['alternate_email']);
                $row['alternate_mobile'] = self::cleanMobileParam($row['alternate_mobile']);

                if (!empty($row['email']) && !empty($this->school_model->get_all([
                    'owner_email' 			=> trim($row['email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['mobile']) && !empty($this->school_model->get_all([
                    'owner_mobile' => trim($row['mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_email']) && !empty($this->school_model->get_all([
                    'owner_email' => trim($row['alternate_email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_mobile']) && !empty($this->school_model->get_all([
                    'owner_mobile' => trim($row['alternate_mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_email']) && !empty($this->school_model->get_all([
                    'owner_email' 			=> trim($row['alternate_email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_mobile']) && !empty($this->school_model->get_all([
                    'owner_mobile' => trim($row['alternate_mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_email']) && !empty($this->school_model->get_all([
                    'alternate_owner_email' => trim($row['alternate_email']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                } elseif (!empty($row['alternate_mobile']) && !empty($this->school_model->get_all([
                    'alternate_owner_mobile' => trim($row['alternate_mobile']),
                ])['rows'] ?? [])) {
                    self::saveGarbageSchoolData($row);
                    continue;
                }

                self::saveSchoolData($row);
			}
		}
	}

    private function saveSiteData($data = []) {
        $this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');

		if(!empty($data) && !empty($data['school_name'])) {

			$site_info = [];

            $country_site_info = $this->site_model->getSiteByName('India');

			if (!empty($country_site_info)) {
				$site_info = $this->site_model->get($country_site_info['id']);
			}

            $country_id = 0;
			$state_id 	= 0;
			$city_id 	= 0;

            if (!empty($data['state'])) {
				if ($state_info = $this->db->get_where('state', [
					'name' 			=> trim($data['state']),
					'_deleted' 		=> 0
				])->row_array()) {
					$state_id = $state_info['id'];
				}
			}

			if (!empty($data['city'])) {
				if ($city_info = $this->db->get_where('city', [
					'name' 		=> trim($data['city']),
					'_deleted' 	=> 0
				])->row_array()) {
					$city_id = $city_info['id'];
				}
			}

			$data['country_id'] = 1;
			$data['state_id'] 	= $state_id;
			$data['city_id'] 	= $city_id;

			if(!empty($site_info)) {

				$insert_site_data = [
					'parent_id' 		  => $data['parent_id'] ?? $site_info['id'],
					'can_add_site' 		  => 0,
					'name' 				  => trim($data['school_name']),
					'site_code' 		  => $site_info['site_code'] . "-import-" . uniqid(),
					'site_type' 		  => $data['site_type'] ?? 1,
					'discount_code' 	  => $site_info['discount_code'],
					'discount_percentage' => $site_info['discount_percentage'],
					'timezone' 			  => $site_info['timezone'],
					'payment_gateway' 	  => $site_info['payment_gateway'],
					'sms_gateway' 		  => $site_info['sms_gateway'],
					'email_alert' 		  => $site_info['email_alert'],
					'address' 			  => $data['address'] ?? '',
					'landmark' 			  => $data['landmark'] ?? '',
					'pincode' 			  => $data['zipcode'] ?? '',
					'mobile_length' 	  => $site_info['mobile_length'],
					'country_code' 		  => $site_info['country_code'],
					'currency_code' 	  => $site_info['currency_code'],
					'state_id' 			  => $data['state_id'],
					'city_id' 			  => $data['city_id'],
					'base_price' 		  => $site_info['base_price'],
					'ebook_price' 		  => $site_info['ebook_price'],
					'price_per_page' 	  => $site_info['price_per_page'],
					'free_page_limit' 	  => $site_info['free_page_limit'],
					'hard_cover_price' 	  => $site_info['hard_cover_price'],
					'paperback_price' 	  => $site_info['paperback_price'],
					'tax' 				  => $site_info['tax'],
					'tax_text' 			  => $site_info['tax_text'],
					'owner_name' 	      => !empty($data['owner_name']) ? trim($data['owner_name']) : '',
					'authorized_person'   => !empty($data['authorized_person']) ? trim($data['authorized_person']) : '',
					'owner_email' 		  => $data['email'],
					'owner_mobile' 	      => '91' . $data['mobile'],
					'status' 			  => 1,
					'license_total' 	  => 1000,
					'license_used' 		  => 0,
				];

				$site_id = $this->site_model->addSite($insert_site_data);

				if (!empty($site_id)) {
					$this->site_model->editById($site_id, [
						'site_code' => get_site_code_slug(trim($data['school_name'])) . "-" . $site_id
					]);

				}
                return $site_id;
			}
		}
	}

    private function saveSchoolData($data = []) {
        $this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');

		if(!empty($data) && !empty($data['school_name'])) {

			$site_info = [];
            $country_site_info = $this->site_model->getSiteByName('India');

			if (!empty($country_site_info)) {
				$site_info = $this->site_model->get($country_site_info['id']);
			}

            $country_id = 0;
			$state_id 	= 0;
			$city_id 	= 0;

            if (!empty($data['state'])) {
				if ($state_info = $this->db->get_where('state', [
					'name' 			=> trim($data['state']),
					'_deleted' 		=> 0
				])->row_array()) {
					$state_id = $state_info['id'];
				}
			}

			if (!empty($data['city'])) {
				if ($city_info = $this->db->get_where('city', [
					'name' 		=> trim($data['city']),
					'_deleted' 	=> 0
				])->row_array()) {
					$city_id = $city_info['id'];
				}
			}

			$data['country_id'] = 1;
			$data['state_id'] 	= $state_id;
			$data['city_id'] 	= $city_id;

			$insert_school_data = [
				'parent_id' 		  			=> $data['parent_id'] ?? 0,
				'site_id' 		  				=> $data['site_id'] ?? 0,
				'name' 				  			=> trim($data['school_name']),
				'site_code' 		  			=> $site_info['site_code'] . "-import-" . uniqid(),
				'site_type' 		  			=> $data['site_type'] ?? 1,
				'discount_code' 	  			=> $site_info['discount_code'] ?? 0,
				'discount_percentage' 			=> $site_info['discount_percentage'] ?? 0,
				'timezone' 			  			=> $site_info['timezone'] ?? '',
				'payment_gateway' 	  			=> $site_info['payment_gateway'] ?? '',
				'sms_gateway' 		  			=> $site_info['sms_gateway'] ?? '',
				'email_alert' 		  			=> $site_info['email_alert'] ?? '',
				'address' 			  			=> $data['address'] ?? '',
				'landmark' 			  			=> $data['landmark'] ?? '',
				'pincode' 			  			=> $data['zipcode'] ?? '',
				'mobile_length' 	  			=> $site_info['mobile_length'] ?? '',
				'country_code' 		  			=> $site_info['country_code'] ?? '',
				'currency_code' 	  			=> $site_info['currency_code'] ?? '',
				'state_id' 			  			=> $data['state_id'] ?? 0,
				'city_id' 			  			=> $data['city_id'] ?? 0,
				'base_price' 		  			=> $site_info['base_price'] ?? 0,
				'ebook_price' 		  			=> $site_info['ebook_price'] ?? 0,
				'price_per_page' 	  			=> $site_info['price_per_page']  ?? 0,
				'free_page_limit' 	  			=> $site_info['free_page_limit'] ?? 0,
				'hard_cover_price' 	  			=> $site_info['hard_cover_price'] ?? 0,
				'paperback_price' 	  			=> $site_info['paperback_price'] ?? 0,
				'tax' 				  			=> $site_info['tax'] ?? '',
				'tax_text' 			  			=> $site_info['tax_text'] ?? '',
				'owner_name' 	      			=> !empty($data['owner_name']) ? trim($data['owner_name']) : '',
				'authorized_person'   			=> !empty($data['authorized_person']) ? trim($data['authorized_person']) : '',
				'owner_email' 		  			=> self::cleanEmail($data['email'] ?? ''),
				'owner_mobile' 	      			=> self::cleanMobileParam($data['mobile'] ?? ''),
				'alternate_authorized_person'   => $data['alternate_authorized_person'] ?? '',
				'alternate_owner_email' 		=> self::cleanEmail($data['alternate_email'] ?? ''),
				'alternate_owner_mobile' 	    => self::cleanMobileParam($data['alternate_mobile'] ?? ''),
				'status' 			  			=> 1,
				'license_total' 	  			=> 1000,
				'license_used' 		  			=> 0,
			];

			$school_id = $this->school_model->add($insert_school_data);

			if (!empty($school_id)) {
				$this->school_model->edit($school_id, [
					'site_code' => get_site_code_slug(trim($data['school_name'])) . "-" . $school_id
				]);
			}
            return $school_id;
		}
	}

    private function saveGarbageSchoolData($data = []) {
        $this->db->insert('garbage_school', [
            'state_id'		                => $data['state_id'] ?? 1,
            'city_id'		                => $data['city_id'] ?? 1,
            'school_name'		            => $data['school_name'] ?? '',
            'email' 		  			    => $data['email'] ?? '',
            'mobile' 	      			    => $data['mobile'] ?? '',
            'authorized_person'   			=> !empty($data['authorized_person']) ? trim($data['authorized_person']) : '',
            'alternate_email' 		        => $data['alternate_email'] ?? '',
            'alternate_mobile' 	            => $data['alternate_mobile'] ?? '',
            'alternate_authorized_person'   => $data['alternate_authorized_person'] ?? '',
            'date_added'	                => date('Y-m-d H:i:s')
        ]);
	}

    private function cleanEmailParam($email_string = '') {
        if (empty($email_string)) return;

        $email =  str_replace(['"'], '', $email_string);

        $slashPosition = strpos($email, '/');
        $commaPosition = strpos($email, ',');

        if ($slashPosition !== false && ($commaPosition === false || $slashPosition < $commaPosition)) {
            $delimiterPosition = $slashPosition;
        } elseif ($commaPosition !== false) {
            $delimiterPosition = $commaPosition;
        } else {
            $delimiterPosition = strlen($email);
        }

        return substr($email, 0, $delimiterPosition);
    }

    private function cleanEmail($email = '') {
        if (empty($email)) return;

        return str_replace(['"', '-'], '', $email);
    }

    private function cleanMobileParam($mobile = '') {
        if (empty($mobile)) return;

        return str_replace([',', '-'], '', $mobile);
    }

    public function updateSchoolAndSiteDetails() {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/school_clean/school_location_update.csv');
		$rows = $this->parsecsv->data;

		foreach ($rows as $key => $row) {
			if (!empty($row) && !empty($school_info = $this->school_model->get($row['school_id'] ?? 0))) {

                $update_data = [
                    'state_id'		=> $row['state_id'],
					'city_id'		=> $row['city_id'],
                ];

				$this->school_model->edit($school_info['id'], $update_data);

                if (!empty($school_info['site_id'])) {
                    $this->site_model->editById($school_info['site_id'], $update_data);
                }
			}

		}
	}
}
