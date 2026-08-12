<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AsyncTask_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);
	}

	public function get($async_task_id = 0) {
		return json_decode($this->cache->get($async_task_id), true);
	}

	public function add($data = []) {
		$async_task_id = 'task_' . uniqid();

		$this->cache->save($async_task_id, json_encode($data), 600);

		self::_createAsyncTaskJob($async_task_id);

		return $async_task_id;
	}

	// private function _createAsyncTaskJob($async_task_id = 0) {
	// 	$path 		= FCPATH . '/index.php AsyncTaskJob job';
	// 	$command 	= 'php ' . $path . ' %s > /dev/null &';

	// 	exec(sprintf($command, $async_task_id));
	// }

	//code added by sonu for window async task run
	private function _createAsyncTaskJob($async_task_id = 0)
	{
		// $php = PHP_BINARY;
		$php = 'C:\xampp\php\php.exe';

		$index = FCPATH . 'index.php';

		$command = sprintf(
			'"%s" "%s" AsyncTaskJob job %s',
			$php,
			$index,
			escapeshellarg($async_task_id)
		);
		log_kb(['AsyncTask_model::_createAsyncTaskJob::' => [$command, $php, $index]]);
		// echo $command;
		// pclose(popen($command, "r"));
		exec($command);
	}

	public function delete($async_task_id = 0) {
		$this->cache->delete($async_task_id);
	}
}
