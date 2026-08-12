<?php defined('BASEPATH') or exit('No direct script access allowed');

trait NotRegisterAlert
{
	public function notRegisterAlertCron($data = []) {
		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/schools_not_registered.csv');
		$rows = $this->parsecsv->data;

		foreach ($rows as $key => $row) {
			log_kb([
				'key'								=> $key,
				'sending::notRegisterAlertCron:: ' 	=> $row,
			]);

			self::_notRegisterAlertCron([
				'name'				=> $row['Name'],
				'school_name'		=> $row['School Names'],
				'email'				=> $row['Email ID '],
				'mobile'			=> $row['Phone Number'],
			], $key);

			sleep(0.2);
		}
	}

	public function testCampaignNotSchoolAlertCron() {
		$row = [
			'name'				=> 'Abhishek',
			'school_name'		=> 'Test School',
			'email'				=> 'abhishek@youbooks.co',
			'mobile'			=> '919818651520',
		];

		self::_notRegisterAlertCron($row, 0);
	}

	private function _notRegisterAlertCron($data = [], $key = 0) {
		$data['title']			= _li('School Registration for National Young Authors Fair is now CLOSED!');
		$data['heading']		= '';
		$data['subheading']		= '';
		$data['subheading']		= '';
		$data['content']		= self::formatEmailMessage('school_not_register', [
			'author_name'		=> $data['name'],
			'name'				=> $data['school_name'],
		]);
		$data['link']			= '';
		$data['link_text']		= '';
		$message				= $this->load->view('common/mail/templates/3/general', $data, true);

		!empty($data['email']) && false && self::email(
			$data['email'],
			$data['title'],
			$message,
			[],
			[]
		);

		log_kb([
			'key'			=> $key,
			'template'		=> '665787161682820',
			'parameters'	=> [
				$data['name'],
				$data['school_name'],
			],
		]);

		!empty($data['mobile']) && false && self::_sendWhatsappText(
			$data['mobile'],
			[
				'template'		=> '665787161682820',
				'parameters'	=> [
					$data['name'],
					$data['school_name'],
				],
			]
		);
	}
}
