<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Icode_model extends CI_Model {
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
		$this->db->insert('otp', [
			'mobile'		=> $data['mobile'],
			'otp'			=> $data['otp'],
			'expired'		=> date('Y-m-d H:i:s', strtotime('+5 minutes')),
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
}
?>
