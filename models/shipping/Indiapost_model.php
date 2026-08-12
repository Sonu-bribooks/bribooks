<?php defined('BASEPATH') or exit('No direct script access allowed');

class Indiapost_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($country_name) {
		$this->db->select('*');
		$this->db->where('country_name', $country_name);
		$this->db->where('_deleted', 0);
		$this->db->from('indiapost_rate');
		return $this->db->get()->row_array();
	}

	public function countryList()
	{
		$query = $this->db->query("SELECT * from indiapost_rate WHERE _deleted=0");
		return $query->result();
	}
}
