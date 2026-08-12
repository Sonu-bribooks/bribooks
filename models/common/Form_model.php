<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Form_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->load->dbforge();

		$fields = [
			'form_id' 		=> [
				'type' 			=> 'INT',
				'constraint' 	=> 5,
				'unsigned' 		=> TRUE,
				'auto_increment'=> TRUE
			],
			'name' 			=> [
				'type'			=> 'VARCHAR',
				'constraint' 	=> '128',
			],
			'theme' 			=> [
				'type'			=> 'VARCHAR',
				'constraint' 	=> '128',
			],
			'fields' 		=> [
				'type' 			=> 'TEXT',
			],
			'date_added' 	=> [
				'type' 			=>'DATETIME',
			],
			'date_modified' => [
				'type' 			=>'DATETIME',
			],
		];

		/*$columns = [
			'seo' 			=> [
				'type'			=> 'VARCHAR',
				'constraint' 	=> '255',
			]
		];

		$this->dbforge->add_column('form', $columns);*/

		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('form_id', TRUE);
		//$this->dbforge->drop_table('form', TRUE);
		$this->dbforge->create_table('form', TRUE);
	}

	public function get_form($form_id = 0) {
		if ($form_id > 0) {
			$this->db->where('form_id', $form_id);
		}

		return $this->db->get('form')->row_array();
	}

	public function get_form_by_seo($seo) {
		$this->db->where('seo', $seo);
		return $this->db->get('form')->row_array();
	}

	public function get_all_form($form_id = 0) {
		if ($form_id > 0) {
			$this->db->where('form_id', $form_id);
		}

		$this->db->order_by('date_added', 'DESC');

		return $this->db->get('form')->result_array();
	}

	public function add_form($data = []) {
		$this->db->insert('form', [
			'name'			=> $data['name'],
			'seo'			=> $data['seo'],
			'fields'		=> json_encode($data['field']),
			'theme'			=> (int)$data['theme'],
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$form_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('form_added_successfully'));
	}

	public function edit_form($form_id = 0, $data = []) {
		$this->db->where('form_id', $form_id);
		$this->db->update('form', [
			'name'			=> $data['name'],
			'seo'			=> $data['seo'],
			'fields'		=> json_encode($data['field']),
			'theme'			=> $data['theme'],
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('form_edited_successfully'));
	}

	public function delete_form($form_id = "") {
		$this->db->where('form_id', $form_id);
		$this->db->delete('form');

		$this->session->set_flashdata('flash_message', _l('form_deleted_successfully'));
	}
}
