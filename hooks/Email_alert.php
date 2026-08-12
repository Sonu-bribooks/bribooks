<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_alert {
	public function __construct() {
		$this->CI =& get_instance();
		$this->input = $this->CI->input;
		$this->session = $this->CI->session;
		$this->db = $this->CI->db;
		$this->uri = $this->CI->uri;
	}

	public function alert() {
		//trigger_error(print_r($this->uri->ruri_string(), 1));
	}
}
