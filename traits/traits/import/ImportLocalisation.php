<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportLocalisation {
	private function _importState($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		$this->load->model('localisation/State_model', 'state_model');

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['name'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$state_info = $this->db->get_where('state', [
				'country_id'	=> trim($data['country_id']),
				'name' 			=> trim($data['name'])
			])->row_array();

			if (empty($state_info)) {
				$this->state_model->add([
					'country_id'	=> trim($data['country_id']),
					'name' 			=> trim($data['name'])
				]);
			} else {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importCity($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		$this->load->model('localisation/City_model', 'city_model');

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['name'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$city_info = $this->db->get_where('city', [
				'state_id'	=> trim($data['state_id']),
				'name' 		=> trim($data['name'])
			])->row_array();

			if (empty($city_info)) {
				$this->city_model->add([
					'state_id'	=> trim($data['state_id']),
					'name' 		=> trim($data['name'])
				]);
			} else {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}
}
