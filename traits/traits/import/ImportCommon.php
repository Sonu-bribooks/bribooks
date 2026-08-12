<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportCommon {
	public function generateImportChunk($data = []) {
		if (empty($data)) return;

		$results 	= $data['rows'] ?? [];
		$map 		= $data['map'] ?? [];
		$action 	= $data['action'] ?? '';
		$job_id 	= $data['job_id'] ?? '';
		$limit		= ENVIRONMENT == 'production' ? 10 : 2;

		$no_threads = ceil(count($results) / $limit);

		$this->load->model('common/AsyncTask_model', 'async_task_model');

		foreach (array_chunk($results, $no_threads, true) as $rows) {
			$this->async_task_model->add([
				'action'	=> 'common/ImportJob_model->importThreadExecute',
				'data' 		=> [[
					'rows'		=> $rows,
					'map'		=> $map,
					'action'	=> $action,
					'job_id'	=> $job_id,
				]]
			]);
		}
	}

	public function importThreadExecute($data = []) {

		$rows 	= $data['rows'] ?? [];
		$map 	= $data['map'] ?? [];
		$action = $data['action'] ?? '';
		$job_id = $data['job_id'] ?? '';

		log_kb(['ImportCommon::importThreadExecute::' => [$action, $map, $job_id, $rows]]);

		log_kb([
			'ACTION' => $action,
			'METHOD_EXISTS' => method_exists($this, $action),
			'CLASS' => get_class($this),
		]);

		if (method_exists($this, $action)) {
			self::{$action}($rows, $map, $job_id);
		}
	}

	private function _updateCounter($job_id = 0, $skipped = false) {
		if (!empty($job_id)) {
			if ($skipped) {
				$this->import_job_model->updateSkipped($job_id);
			} else {
				$this->import_job_model->updateCounter($job_id);
			}
		}
	}

	private function _updateCompleted($job_id = 0) {
		if (!empty($job_id)) {
			$job_info = $this->import_job_model->get($job_id);

			if ($job_info['total'] == $job_info['counter']) {
				$this->import_job_model->edit($job_id, [
					'status' => 1,
				]);
			}
		}
	}
}
