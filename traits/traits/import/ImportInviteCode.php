<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportInviteCode {
	private function _importUserInviteCode($rows = [], $map = [], $job_id = 0) {
		$this->load->model('event/EventUserInviteCode_model', 'event_user_invite_code_model');

		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['event_id']) || empty($data['user_id'])) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			if ($student_info = $this->db->get_where('users', [
				'id'		=> $data['user_id'],
			])->row_array()) {
				if (empty($code_info = $this->event_user_invite_code_model->get_all([
					'event_id' 	=> $data['event_id'],
					'user_id' 	=> $data['user_id'],
				])['rows'] ?? [])) {
					$password 	= uniqid();
					$code 		= sha1(md5(($data['user_id']) . $password . $this->config->item('password_salt') . $data['event_id']));

					$this->event_user_invite_code_model->add([
						'event_id'			=> $data['event_id'],
						'user_id'			=> $data['user_id'],
						'code'				=> $code,
						'type' 				=> $data['type'] ?? 'user',
						'referral_limit' 	=> $data['referral_limit'] ?? 0
					]);
				}
			} else {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importSchoolInviteCode($rows = [], $map = [], $job_id = 0) {
		$this->load->model('event/EventSchoolInviteCode_model', 'event_school_invite_code_model');

		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['event_id'])) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			$filter_data = [
				'event_id' => $data['event_id']
			];

			if (!empty($data['school_id'])) {
				$filter_data['school_id'] = $data['school_id'];
			}

			if (!empty($data['site_id'])) {
				$filter_data['site_id'] = $data['site_id'];
			}

			if (empty($code_info = $this->event_school_invite_code_model->get_all($filter_data)['rows'] ?? [])) {
				$password 	= uniqid();
				$code 		= sha1(md5(($data['user_id']) . $password . $this->config->item('password_salt') . $data['event_id']));

				$this->event_school_invite_code_model->add([
					'event_id'	=> $data['event_id'],
					'site_id'	=> $data['site_id'] ?? 0,
					'school_id'	=> $data['school_id'] ?? 0,
					'code'		=> $code,
				]);
			} else {
				self::_updateCounter($job_id, true);

				$skipped++;
			}
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}
}
