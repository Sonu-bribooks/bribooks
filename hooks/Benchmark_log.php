<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Benchmark_log {
	public function __construct() {
		$this->CI 			=& get_instance();
		$this->input 		= $this->CI->input;
		$this->session 		= $this->CI->session;
		$this->db 			= $this->CI->db;
		$this->uri 			= $this->CI->uri;
		$this->benchmark 	= $this->CI->benchmark;
	}

	public function start() {
		//log_message('KB', 'start_' . date('Y-m-d H:i:s'));

		if (!is_null($this->CI) && !is_null($this->benchmark)) {
			log_message('KB', get_class($this->CI) . '_benchmark');
			$this->benchmark->mark('code_start');
		}
	}

	public function stop() {
		//log_message('KB', 'end_' . date('Y-m-d H:i:s'));

		if (!is_null($this->CI) && !is_null($this->benchmark)) {
			$this->benchmark->mark('code_end');
			log_message('KB', get_class($this->CI) . '_benchmark:: ' . $this->benchmark->elapsed_time('code_start', 'code_end'));
		}
	}
}
