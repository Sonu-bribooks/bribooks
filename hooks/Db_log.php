<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Db_log {
	private $CI = NULL;

	public function __construct() {
		$this->CI = & get_instance();
	}

	public function logQueries() {
		if (is_null($this->CI)) return;

		$filepath 	= APPPATH . 'logs/QueryLog-' . date('Y-m-d') . '.php';
		$handle 	= fopen($filepath, "a+");

		$times 		= $this->CI->db->query_times;

		foreach ($this->CI->db->queries as $key => $query) {
			if ($times[$key] > 0.2) {
				$sql = date('Y-m-d H:i:s') . '::' . $this->CI->uri->uri_string() . '::' . $query . " \n Execution Time:" . round($times[$key], 4);
				fwrite($handle, $sql . "\n\n");
			}
		}

		fclose($handle);
	}
}
