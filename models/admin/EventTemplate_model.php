<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventTemplate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($event_templates_id = 0) {
		$this->db->select('*');

		$this->db->where('event_templates.id', (int)$event_templates_id);
		$this->db->where('event_templates._deleted', 0);

		return $this->db->get('event_templates')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('*');

		if (!empty($data['event_id'])) {
			$this->db->where('event_templates.event_id', $data['event_id']);
		}
		
        $this->db->from('event_templates');

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
			'event_templates.status',
			'event_templates.date_added',
			'event_templates.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'event_templates.date_added';
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
		$this->db->insert('event_templates', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$template_id = $this->db->insert_id();

		return $template_id;
	}

	public function edit($event_templates_id = 0, $data = []) {
		$this->db->where('id', (int)$event_templates_id);
		return $this->db->update('event_templates', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($review_id = 0) {
		$this->db->where('id', (int)$review_id);
		$this->db->update('event_templates',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByTemplateId($event_id = '', $template_id = '') {
		$this->db->where('event_templates.event_id', (int)$event_id);
		$this->db->where('event_templates.template_id', $template_id);

		return $this->db->get('event_templates')->row_array();
	}

	public function upload_image($filename, $name) {
		$this->load->model('Tool_model');

		$res = $this->Tool_model->upload(
			$name,
			$filename . '.jpg',
			'uploads/event_email_templates/',
			'',
			'',
			true
		);

		if (!empty($res['error'])) {
			$this->session->set_flashdata('error_message', $res['error']);
		} else {
			$this->session->set_flashdata('flash_message', _l('email_event_templates_upload_successfully'));
		}
	}

	public function get_event_templates_image_url($filename) {
		if (file_exists('uploads/event_email_templates/' . $filename . '.jpg'))
			return base_url() . 'uploads/event_email_templates/' . $filename . '.jpg?v='.time();
	}
}
