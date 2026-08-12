<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Config_setting {
	public function __construct() {
		$this->CI =& get_instance();
		$this->input = $this->CI->input;
		$this->session = $this->CI->session;
		$this->db = $this->CI->db;
		$this->uri = $this->CI->uri;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('common/Site_model', 'site_model');

		$this->site_model = $this->CI->site_model;
	}

	public function init() {
		// trigger_error(print_r($this->uri->ruri_string(), 1));
		// log_message('error', print_r($this->input->get(), 1));
		$session_data = $this->session->userdata();

		unset($session_data['pwds'], $session_data['dbs'], $session_data['queries']);

		// log_kb(['Config' => [
		// 	'session'		=> $session_data,
		// 	'method'		=> $this->input->method(),
		// 	'uri_segment' 	=> $this->uri->uri_string(),
		// ]]);

		if (
			!empty($this->input->post('api_site_id'))
			&& ($result = $this->site_model->get($this->input->post('api_site_id')))
		) {
			$this->site_model->initConfig($result['id']);
		} elseif (
			!empty($this->input->get_request_header('x-locale', true))
			&& strtolower($this->input->get_request_header('x-locale', true)) !== 'in'
			&& ($result = $this->site_model->getByCountryCode($this->input->get_request_header('x-locale', true), 7))
		) {
			$this->site_model->initConfig($result['id']);
		} elseif (
			!empty($this->input->get('country_code'))
			&& ($result = $this->site_model->getByCountryCode($this->input->get('country_code'), 7))
		) {
			$this->site_model->initConfig($result['id']);
		} elseif (
			!empty($this->session->userdata('portal_site'))
			&& ($result = $this->site_model->get($this->session->userdata('portal_site')))
		) {
			$this->site_model->initConfig($result['id']);
		} elseif (
			!empty($this->input->cookie('user_site', TRUE))
			&& ($result = $this->site_model->get($this->input->cookie('user_site', TRUE)))
		) {
			$this->site_model->initConfig($result['id']);
		} elseif (
			!empty($this->session->userdata('user_site'))
			&& ($result = $this->site_model->get($this->session->userdata('user_site')))
		) {
			$this->site_model->initConfig($result['id']);
		}
	}

	public function reset() {
		// Remove bugs due to existing country code
		// file_put_contents(APPPATH . 'country_code.php', '');
	}
}
