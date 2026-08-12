<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AddTemplate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($templates_id = 0) {
		$this->db->select('*');

		$this->db->where('templates.id', (int)$templates_id);
		$this->db->where('templates._deleted', 0);

		return $this->db->get('templates')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('*');

		if (!empty($data['site_id'])) {
			$this->db->where('templates.site_id', $data['site_id']);
		}

		$this->db->where('templates._deleted', 0);
		
        $this->db->from('templates');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'templates.status',
			'templates.date_added',
			'templates.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'templates.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('templates', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$template_id = $this->db->insert_id();

		return $template_id;
	}

	public function edit($templates_id = 0, $data = []) {
		$this->db->where('id', (int)$templates_id);
		return $this->db->update('templates', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($review_id = 0) {
		$this->db->where('id', (int)$review_id);
		$this->db->update('templates',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByTemplateId($site_id = '', $template_id = '') {
		$this->db->where('templates.site_id', (int)$site_id);
		$this->db->where('templates.template_id', $template_id);
		$this->db->where('templates._deleted', 0);

		return $this->db->get('templates')->row_array();
	}

	public function getEventTemplate($event_id = '', $template_id = '') {
		$this->db->where('templates.event_id', (int)$event_id);
		$this->db->where('templates.template_id', $template_id);
		$this->db->where('templates._deleted', 0);

		return $this->db->get('templates')->row_array();
	}

	public function upload_image($filename, $name) {
		$this->load->model('Tool_model');

		$res = $this->Tool_model->upload(
			$name,
			$filename . '.jpg',
			'uploads/email_templates/',
			'',
			'',
			true
		);

		if (!empty($res['error'])) {
			$this->session->set_flashdata('error_message', $res['error']);
		} else {
			$this->session->set_flashdata('flash_message', _l('email_templates_upload_successfully'));
		}
	}

	public function get_email_templates_image_url($filename)
	{
		if (file_exists('uploads/email_templates/' . $filename . '.jpg'))
			return base_url() . 'uploads/email_templates/' . $filename . '.jpg?v='.time();
	}
}
