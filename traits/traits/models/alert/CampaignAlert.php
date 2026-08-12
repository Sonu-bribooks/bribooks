<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CampaignAlert
{
	public function campaignSchoolAlertCron($data = []) {
		$this->load->model('school/SchoolDetail_model', 'schooldetail_model');
		$this->load->model('school/SchoolLead_model', 'school_lead_model');

		$school_data = $this->schooldetail_model->get_all(); // 3340

		foreach ($school_data['rows'] ?? [] as $key => $row) {
			if ($state_info = $this->db->get_where('state', ['name' => $row['state']])->row_array()) {
				$state_id = $state_info['id'];
			}

			if ($city_info = $this->db->get_where('city', [
				'name' 		=> $row['city'],
				'state_id'	=> $state_id,
			])->row_array()) {
				$city_id = $city_info['id'];
			}

			$existing = $this->school_lead_model->get_all([
				'name' 		=> $row['school_name'],
				'city_id' 	=> $city_id,
				'state_id' 	=> $state_id,
			])['rows'] ?? [];

			if (!$existing || true) {
				$top_school = $this->db->get_where('campagin_top_school', [
					'state'	=> $row['state'],
				])->row_array()['name'] ?? '';

				// if (!$top_school) continue;

				log_kb([
					'key'						=> $key,
					'sending::CampaignAlert:: ' => $row,
				]);

				self::_campaignSchoolAlertCron($row + [
					'top_school' => $top_school
				], $key);

				sleep(0.2);
			}
		}
	}

	public function testCampaignSchoolAlertCron() {
		$row = [
			'principle_name'	=> 'Abhishek',
			'school_name'		=> 'Test School',
			'email'				=> 'abhishek@youbooks.co',
			'mobile'			=> '919818651520',
			'city'				=> 'Gurugram',
			'state'				=> 'Haryana',
		];

		self::_campaignSchoolAlertCron($row + [
			'top_school' => ''
		], 0);
	}

	private function _campaignSchoolAlertCron($data = [], $key = 0)
	{
		$data['title']		  	= _li('Final Reminder, 2 Hours left! Register now, in the World\'s Largest Book Writing Competition - the National Young Authors Fair');
		$data['heading']		= '';
		$data['subheading']	 	= '';
		$data['subheading']	 	= '';
		$data['content']		= self::formatEmailMessage('lead_data', [
			'author_name'	  	=> $data['principle_name'],
			'name'	    		=> $data['school_name'],
			'email'				=> $data['email'],
			'city'				=> $data['city'] ?? '',
			'state'				=> $data['state'] ?? '',
			// 'school_head'		=> $data['top_school'],
		]);
		$data['link']		   	= '';
		$data['link_text']	  	= '';
		$message				= $this->load->view('common/mail/templates/3/general', $data, true);

		!empty($data['email']) && self::email(
			$data['email'],
			$data['title'],
			$message,
			[],
			[]
		);

		log_kb([
			'key'			=> $key,
			'mobile'		=> $mobile,
			'template'		=> '1226826177908072',
			'parameters'	=> [
				$data['principle_name'],
				$data['school_name'],
				$data['email'],
				$data['school_name'],
			],
		]);

		!empty($data['mobile']) && self::_sendWhatsappImage(
			$data['mobile'],
			[
				'template'		=> '1226826177908072',
				'parameters'	=> [
					$data['principle_name'],
					$data['school_name'],
					// $data['email'],
					// $data['school_name'],
				],
				'document'	=> [
					'name'	=> 'school event',
					'link'	=> base_url('assets/marketing/campaignschool.png')
				]
			]
		);
	}

	public function testCampaignRegisteredSchoolAlertCron() {
		$row = [
			'name'					=> 'Test School',
			'owner_email'			=> 'abhishek@youbooks.co',
			'owner_mobile'			=> '919818651520',
		];

		self::_campaignRegisteredSchoolAlertCron($row, 0);
	}

	public function campaignRegisteredSchoolAlertCron($data = []) {
		$this->load->model('common/Site_model', 'site_model');

		$results = $this->site_model->get_all()['rows'] ?? [];

		foreach ($results as $key => $row) {
			if (strpos($row['site_code'], 'NYAFIND') !== false) {
				self::_campaignRegisteredSchoolAlertCron($row, $key);

				sleep(0.2);
			}
		}
	}

	private function _campaignRegisteredSchoolAlertCron($data = [], $key = 0)
	{
		$school_info = $this->db->get_where('school_lead', [
			'email'		=> $data['owner_email'],
			'mobile'	=> $data['owner_mobile'],
		])->row_array();

		if (empty($school_info)) {
			return;
		}

		$data['title']		  	= sprintf(_li('Congratulations for successfully registering %s in the World\'s Largest Book Writing Competition - The National Young Authors Fair!'), $data['name']);
		$data['heading']		= '';
		$data['subheading']	 	= '';
		$data['subheading']	 	= '';
		$data['content']		= self::formatEmailMessage('school_register', [
			'author_name'	  	=> $school_info['school_head'],
			'name'	    		=> $data['name'],
		]);
		$data['link']		   	= '';
		$data['link_text']	  	= '';
		$message				= $this->load->view('common/mail/templates/3/general', $data, true);

		!empty($data['owner_email']) && self::email(
			$data['owner_email'],
			$data['title'],
			$message,
			[],
			[]
		);

		log_kb([
			'key'			=> $key,
			'template'		=> '2145264675677405',
			'parameters'	=> [
				$data['owner_email'],
				$data['owner_mobile'],
				$data['name'],
				$school_info['school_head'],
			],
		]);

		!empty($data['owner_mobile']) && self::_sendWhatsappImage(
			$data['owner_mobile'],
			[
				'template'		=> '2145264675677405',
				'parameters'	=> [
					$school_info['school_head'],
					$data['name'],
					// $data['email'],
					// $data['school_name'],
				],
				'document'	=> [
					'name'	=> 'school signup',
					'link'	=> base_url('assets/marketing/RegisteredSchool.png')
				]
			]
		);
	}
}
