<?php defined('BASEPATH') OR exit('No direct script access allowed');

class My_Lang extends CI_Lang {
	public function line($line, $log_errors = TRUE) {
		$value = isset($this->language[$line]) ? $this->language[$line] : FALSE;

		if (strpos($line, 'db_') !== false) return $value;

		return _li($value);
	}
}
