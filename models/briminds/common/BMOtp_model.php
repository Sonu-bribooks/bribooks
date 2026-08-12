<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BMOtp_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->bmdb = $this->load->database('briminds', TRUE);
	}

	public function get($data = []) {
		$this->bmdb->where('send_to', $data['send_to']);
		$this->bmdb->where('otp', $data['otp']);
		$this->bmdb->where('status', 0);

		return $this->bmdb->get('otp')->row_array();
	}

	public function add($data = []) {
		$this->load->library('user_agent');

		$this->bmdb->insert('otp', [
			'send_to'		=> $data['send_to'],
			'otp'			=> $data['otp'],
			'type'			=> $data['type'] ?? 'mobile',
			'country_code'	=> $data['country_code'] ?? 'IN',
			'date_expire'	=> date('Y-m-d H:i:s', strtotime('+10 minutes')),
			'browser'		=> !empty($this->input->post('app_os')) ? (!empty($this->input->post('is_tablet')) ? 'tablet' : 'mobile') : $this->agent->browser(),
			'platform'		=> !empty($this->input->post('app_os')) ? $this->input->post('app_os') : $this->agent->platform(),
			'ip_address'	=> $this->input->ip_address(),
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function edit($data = []) {
		$this->bmdb->where('send_to', $data['send_to']);
		$this->bmdb->where('otp', $data['otp']);
		$this->bmdb->where('status', 0);

		$this->bmdb->update('otp', [
			'status'		=> 1,
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}
}
