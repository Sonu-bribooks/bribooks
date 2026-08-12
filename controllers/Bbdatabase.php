<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Bbdatabase extends CI_Controller {
	public function __construct() {
		parent::__construct();

		if ($this->session->userdata('admin_login') == false) {
			redirect(base_url('login?back=bbdatabase'), 'refresh');
		}

		if (empty($this->session->userdata('user_role_type'))) {
			redirect(base_url('login?back=bbdatabase'), 'refresh');
		}

		if (empty($this->session->userdata('user_email'))) {
			redirect(base_url('login?back=bbdatabase'), 'refresh');
		}

		if (ENVIRONMENT === 'production' && !in_array($this->session->userdata('user_email'), DB_ACCESS_EMAILS)) {
			redirect(base_url('login'), 'refresh');
		}
	}

	public function index() {
		log_system_access();
		
		require_once(APPPATH . 'third_party/adminer/cms.php');
	}
}
