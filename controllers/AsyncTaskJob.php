<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AsyncTaskJob extends CI_Controller {
	public function __construct() {
		parent::__construct();
	}

	public function job($job_id = 0) {
		if (is_cli()) {
			$start 	= microtime(true);

			log_kb(['AsyncTaskJob::JobID::' => $job_id]);

			$this->load->model('common/AsyncTask_model', 'async_task_model');
			$job_info = $this->async_task_model->get($job_id);

			log_kb(['AsyncTaskJob::JobInfo::' => $job_info]);

			$explode = explode('->', $job_info['action']);

			if (!empty($explode[0]) && !empty($explode[1])) {
				$class_explode 	= explode('/', mb_strtolower($explode[0]));
				$class_alias	= array_pop($class_explode);

				$this->load->model($explode[0], $class_alias);

				$data = $job_info['data'];

				log_kb(['AsyncTaskJob::Data::' => [$data, $class_alias, $explode[0]]]);

				$data = is_array($data) ? $data : [$data];

				$this->{$class_alias}->{$explode[1]}(...$data);
			}

			// sleep(50);

			// delete task from further execution
			$this->async_task_model->delete($job_id);

			$finish 	= microtime(true);
			$duration 	= round($finish - $start, 4);

			log_kb(['AsyncTaskJob::Finished::' => [
				'Job' 		=> $job_id,
				'duration' 	=> $duration,
				'env' 		=> ENVIRONMENT,
			]]);
		}
	}
}
