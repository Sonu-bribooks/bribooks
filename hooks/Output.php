<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Output {
	public function __construct() {
		$this->CI 		=& get_instance();
		$this->output 	= $this->CI->output;
		$this->input 	= $this->CI->input;
		$this->json 	= $this->CI->json ?? [];
	}

	public function setOutput() {
		if (get_class($this->CI) === 'Api') {
			$this->CI->benchmark->mark('api_end');

			log_kb([
				'api_end'	=> $this->CI->uri->uri_string() . ' :: ' . $this->CI->benchmark->elapsed_time('api_start', 'api_end'),
			]);

			!empty($this->json['error']) && $this->output->set_status_header(400);
			!empty($this->json['unauthorized']) && $this->output->set_status_header(401);

			$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
			$this->output->set_header('Pragma: no-cache');
			$this->output->set_header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS');
			// $this->output->set_header('Access-Control-Allow-Headers: Content-Type, Authorization');
			$this->output->set_header('Access-Control-Allow-Headers: x-requested-with, x-locale, Accept, Content-Type, Authorization, Origin');
			$this->output->set_header('Access-Control-Allow-Credentials: true');
			$this->output->set_header('Access-Control-Allow-Origin: ' . $this->input->get_request_header('Origin', true));
			// $this->output->set_header('Access-Control-Allow-Origin: *');
			$this->output->set_content_type('application/json')->set_output(json_encode($this->json));
		}
	}
}
