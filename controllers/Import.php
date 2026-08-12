<?php defined('BASEPATH') OR exit('No direct script access allowed');

load_trait('import');

class Import extends CI_Controller {

	use ImportInit,
		ImportStep,
		ImportCommon,
		ImportGeneric,
		ImportLocalisation,
		ImportUser,
		ImportSchool,
		ImportOrder,
		ImportInviteCode,
		ImportPincodeZone,
		ImportAuthorCalendar,
		ImportAuthorWall,
		ImportEventExhibition,
		ImportEventCertificate,
		ImportEventLiteraryLeader
	;

	public function __construct() {
		parent::__construct();

		if ($this->session->userdata('admin_login') == false) {
			redirect(base_url('login'), 'refresh');
		}

		self::_init();
	}

	public function index() {
		$data['action_file'] 		= base_url('import/upload');
		$data['action_type'] 		= base_url('import/type');
		$data['action_save'] 		= base_url('import/save');
		$data['action_download'] 	= base_url('import/download');

		$data['page_name'] 			= 'import';
		$data['page_title'] 		= _l('import');

		$data['types'] 				= $this->types;

		$this->load->view('backend/index', $data);
	}
}
