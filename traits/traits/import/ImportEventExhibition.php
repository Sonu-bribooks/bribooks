<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportEventExhibition {
	private function _importEventExhibition($rows = [], $map = [], $job_id = 0) {
		$this->load->model('event/EventExhibition_model', 'event_exhibition_model');

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

			if (empty($data['site_id']) && empty($data['book_id'])) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			$filter_data = [
				'event_id'		=> $data['event_id'],
				'type'			=> $data['type'],
			];

			if ($data['type'] === 'school') {
				$filter_data['site_id'] = $data['site_id'];
			} else {
				$filter_data['book_id'] = $data['book_id'];
			}

			if (!empty($info = $this->event_exhibition_model->get_all($filter_data)['rows'][0] ?? [])) {
				$this->event_exhibition_model->edit($info['id'], [
					'event_id'			=> $data['event_id'],
					'type' 				=> $data['type'] ?? 'user',
					'user_id'			=> $data['user_id'],
					'site_id'			=> $data['site_id'],
					'book_id' 			=> $data['book_id'] ?? 0,
					'award' 			=> $data['award'] ?? '',
					'interview' 		=> $data['interview'] ?? '',
					'wall' 				=> $data['wall'] ?? '',
				]);
			} else {
				$this->event_exhibition_model->add([
					'event_id'			=> $data['event_id'],
					'type' 				=> $data['type'] ?? 'user',
					'user_id'			=> $data['user_id'],
					'site_id'			=> $data['site_id'],
					'book_id' 			=> $data['book_id'] ?? 0,
					'award' 			=> $data['award'] ?? '',
					'interview' 		=> $data['interview'] ?? '',
					'wall' 				=> $data['wall'] ?? '',
				]);
			}
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}
}
