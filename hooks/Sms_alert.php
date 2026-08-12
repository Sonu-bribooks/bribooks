<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms_alert {
	public function __construct() {
		$this->CI =& get_instance();
		$this->input = $this->CI->input;
		$this->session = $this->CI->session;
		$this->db = $this->CI->db;
		$this->uri = $this->CI->uri;
	}

	public function alert() {

	}
}
