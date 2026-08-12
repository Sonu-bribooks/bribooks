<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Otp_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($data = []) {
		//$this->db->where('expired <=', date('Y-m-d H:i:s'));
		$this->db->where('mobile', $data['mobile']);
		$this->db->where('otp', $data['otp']);
		$this->db->where('status', 0);
		$this->db->where('site_id', (int)$this->config->item('site_id'));

		return $this->db->get('otp')->row_array();
	}

	public function add($data = []) {
		$this->load->library('user_agent');

		$this->db->insert('otp', [
			'user_id'		=> (int)$this->session->userdata('user_id'),
			'mobile'		=> $data['mobile'],
			'otp'			=> $data['otp'],
			'type'			=> $data['type'] ?? 'mobile',
			'country_code'	=> $data['country_code'] ?? '',
			'expired'		=> date('Y-m-d H:i:s', strtotime('+10 minutes')),
			'browser'		=> !empty($this->input->post('app_os')) ? (!empty($this->input->post('is_tablet')) ? 'tablet' : 'mobile') : $this->agent->browser(),
			'platform'		=> !empty($this->input->post('app_os')) ? $this->input->post('app_os') : $this->agent->platform(),
			'ip'			=> $this->input->ip_address(),
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$this->config->item('site_id'),
		]);
	}

	public function edit($data = []) {
		//$this->db->where('expired <=', date('Y-m-d H:i:s'));
		$this->db->where('mobile', $data['mobile']);
		$this->db->where('otp', $data['otp']);
		$this->db->where('status', 0);
		$this->db->where('site_id', (int)$this->config->item('site_id'));

		$this->db->update('otp', [
			'status'		=> 1,
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByMobileNo($mobile = false) {
		$this->db->where('mobile', $mobile);
		$this->db->where('status', 0);
		$this->db->order_by('id', 'DESC');
		$this->db->limit(1);
		return $this->db->get('otp')->row_array();
	}
}
