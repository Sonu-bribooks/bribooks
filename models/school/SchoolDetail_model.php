<?php defined('BASEPATH') or exit('No direct script access allowed');

class SchoolDetail_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('
			campaign_school.*
		');

		$this->db->where('campaign_school.id', (int)$id);

		return $this->db->get('campaign_school')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			campaign_school.*
		');

		if (!empty($data['search'])) {
			$this->db->like('campaign_school.name', $data['search'], 'after');
		}

		if (!empty($data['city_id'])) {
			$this->db->where('campaign_school.city_id', (int)$data['city_id']);
		}

		$this->db->from('campaign_school');

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
			'campaign_school.name',
		];

		// if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		//	 $sort = $data['sort'];
		// } else {
		//	 $sort = 'campaign_school.date_added';
		// }

		// if (isset($data['order']) && ($data['order'] == 'ASC')) {
		//	 $order = "ASC";
		// } else {
		//	 $order = "DESC";
		// }

		// $this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('campaign_school', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$school_input_id = $this->db->insert_id();

		// self::assignTelecaller($school_input_id);

		$this->session->set_flashdata('flash_message', _l('school_input_added_successfully'));

		return $school_input_id;
	}

	public function edit($campaign_school_id = 0, $data = []) {
		$this->db->where('id', $campaign_school_id);
		$this->db->update('campaign_school', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('campaign_school_edited_successfully'));
	}

	public function archived($data = []) {
		if ($campaign_school_info = self::get($data['campaign_school_id'])) {
			$this->db->update('campaign_school', [
				'archived'		=> $campaign_school_info['archived'] ? 0 : 1,
			], [
				'id'			=> (int)$data['campaign_school_id']
			]);
		}
	}

	public function add_status() {
		if ($this->get($this->input->post('campaign_school_id'))) {
			$this->db->insert('campaign_school_status', [
				'campaign_school_id'	=> (int)$this->input->post('campaign_school_id'),
				'status'			=> $this->input->post('status'),
				'comment'			=> $this->input->post('comment'),
				'user_id'			=> (int)$this->session->user_id,
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
			]);

			$this->db->update('campaign_school', [
				'telecaller_id'	=> (int)$this->session->user_id
			], [
				'id'			=> (int)$this->input->post('campaign_school_id')
			]);

			$id = $this->db->insert_id();

			$this->load->model('Alert_model', 'alert_model');

			if ($this->input->post('status') == 'not_responding') {
				// $this->alert_model->demoNotResponding($this->input->post('campaign_school_id'));
			}

			if ($this->input->post('status') == 'course_fee_details') {
				// $this->alert_model->demoFeeDetails($this->input->post('campaign_school_id'));
			}

			$this->session->set_flashdata('flash_message', _l('campaign_school_status_added_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('campaign_school_not_found'));
		}
	}

	public function update_email($data = []) {
		if ($this->get($data['campaign_school_id'])) {
			$this->db->update('campaign_school', [
				'email'				=> $data['email'],
				'telecaller_id'		=> (int)$this->session->userdata('user_id'),
			], [
				'id'		=> (int)$data['campaign_school_id'],
			]);

			$this->session->set_flashdata('flash_message', _l('campaign_school_email_updated_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('campaign_school_not_found'));
		}
	}

	public function get_status($campaign_school_id = 0) {
		$this->db->where('campaign_school_id', (int)$campaign_school_id);

		return $this->db->get('campaign_school_status')->result_array();
	}

	public function delete($campaign_school_id = 0) {
		$this->db->where('id', $campaign_school_id);
		$this->db->delete('campaign_school');

		$this->session->set_flashdata('flash_message', _l('campaign_school_deleted_successfully'));
	}

	private function assignTelecaller($campaign_school_id, $type = 'campaign_school') {
		$this->load->model('user/Telecaller_model', 'telecaller_model');

		/*if (($campaign_school_info = self::get($campaign_school_id)) && $campaign_school_info['telecaller_id']) {
			$telecaller_info = $this->telecaller_model->get($campaign_school_info['telecaller_id'])->row_array();

			$telecaller_id = $telecaller_info['status'] ? $campaign_school_info['telecaller_id'] : 0;
		}*/

		$results = $this->telecaller_model->get_all([
			'status' => 1,
			'site_id' => (int)$this->config->item('site_id')
		])->result_array();

		$index = count($results) > 0 ? mt_rand(0, count($results) - 1) : 0;

		$telecaller_id = $results[$index]['id'] ?? 0;

		if (!isset($results[$index]['id'])) {
			log_message('KB', print_r($results, 1) . '===' . $index);
		}

		// self::assignOldTelecaller($campaign_school_id, $telecaller_id);

		self::edit($campaign_school_id, [
			'telecaller_id'	=> (int)$telecaller_id
		]);
	}

	private function assignOldTelecaller($campaign_school_id, &$telecaller_id) {
		if (($campaign_school_info = self::get($campaign_school_id)) &&
			($row = $this->db->get_where('campaign_school', [
				'mobile'				 => $campaign_school_info['mobile'],
				'email'				 => $campaign_school_info['email'],
				'telecaller_id > '		 => 0,
				'site_id'				=> (int)$this->config->item('site_id'),
			])->row_array())
		) {
			$telecaller_id = $row['telecaller_id'];
		}
	}

	public function reassignTelecaller($data = []) {
		if ($row = $this->db->get_where('reassign_telecaller', [
			'original_telecaller_id'	 => (int)$data['original_telecaller_id'],
			'telecaller_id'			 => (int)$data['telecaller_id'],
			'campaign_school_id'					 => (int)$data['campaign_school_id']
		])->row_array()) {
			$this->db->update('reassign_telecaller', [
				'comment'					=> $data['comment'],
				'date_modified'				=> date('Y-m-d H:i:s'),
			], [
				'id'		=> (int)$row['id']
			]);
		} else {
			$this->db->insert('reassign_telecaller', [
				'original_telecaller_id'	 => (int)$data['original_telecaller_id'],
				'telecaller_id'			 => (int)$data['telecaller_id'],
				'campaign_school_id'					 => (int)$data['campaign_school_id'],
				'comment'					=> $data['comment'],
				'date_added'				=> date('Y-m-d H:i:s'),
				'date_modified'				=> date('Y-m-d H:i:s'),
			]);
		}

		self::edit($data['campaign_school_id'], [
			'telecaller_id'	=> $data['telecaller_id']
		]);
	}

	public function get_all_reassign($data = []) {
		$this->db->select('reassign_telecaller.*, campaign_school.name, campaign_school.mobile');

		if (!empty($data['campaign_school_id'])) {
			$this->db->where('reassign_telecaller.id', (int)$data['schedule_id']);
		}

		if (!empty($data['telecaller_id'])) {
			$this->db->where('reassign_telecaller.telecaller_id', (int)$data['telecaller_id']);
		}

		if (!empty($data['original_telecaller_id'])) {
			$this->db->where('reassign_telecaller.original_telecaller_id', (int)$data['original_telecaller_id']);
		}

		$this->db->join('campaign_school', 'campaign_school.id = reassign_telecaller.campaign_school_id');

		return $this->db->get('reassign_telecaller')->result_array();
	}

	public function schedule($data = []) {
		$this->db->update('schedules', [
			'campaign_school_id'		=> 0,
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$data['old_schedule_id']
		]);

		$this->db->update('schedules', [
			'campaign_school_id'		=> (int)$data['campaign_school_id'],
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$data['schedule_id']
		]);

		$this->db->update('campaign_school', [
			'schedule_id'			=> (int)$data['schedule_id'],
			'confirmed_schedule'	=> date('Y-m-d H:i:s', strtotime($data['schedule'])),
			'date_modified'			=> date('Y-m-d H:i:s'),
			'status'				=> 1,
			'telecaller_id'			=> (int)$this->session->userdata('user_id'),
		], [
			'id'					=> (int)$data['campaign_school_id']
		]);

		$this->session->set_flashdata('flash_message', _l('campaign_school_scheduled_for_demo_successfully'));
	}

	public function addStudent($data = []) {
		if ($student_info = $this->db->get_where('users', ['mobile' => $data['mobile'], 'role_id' => 2])->row_array()) {
			$this->db->update('users', [
				'password'			=> sha1(uniqid()),
				'parent_name'		=> $data['parent_name'],
				'role_id'			=> 2,
				'campaign_school_id'			=> (int)$data['campaign_school_id'],
				'status'			=> 1,
				'email'				=> !empty($data['email']) ? $data['email'] : $student_info['email'],
			], [
				'id'				=> (int)$student_info['id']
			]);

			$student_id = $student_info['id'];
		} else {
			$this->db->insert('users', [
				'first_name'		=> $data['first_name'],
				'last_name'			=> $data['last_name'],
				'parent_name'		=> $data['parent_name'],
				'email'				=> $data['email'],
				'mobile'			=> $data['mobile'],
				'campaign_school_id'			=> (int)$data['campaign_school_id'],
				'password'			=> sha1(uniqid()),
				'role_id'			=> 2,
				'site_id'			=> (int)$this->config->item('site_id'),
				'date_added'		=> strtotime(date('Y-m-d H:i:s')),
				'status'			=> 1,
			]);

			$student_id = $this->db->insert_id();

			// if ($student_id) {
			// 	$this->load->model('common/Site_model', 'site_model');
			//
			// 	$this->site_model->addSitesByTable('users', [
			// 		'column'	=> 'user_id',
			// 		'id'		=> $student_id,
			// 		'sites'		=> [$this->config->item('site_id')]
			// 	]);
			// }
		}

		if ($data['schedule_id']) {
			$this->db->delete('demo_campaign_school_schedule', [
				'campaign_school_id'			=> (int)$data['campaign_school_id'],
				'student_id'		=> (int)$student_id,
			]);

			$this->db->insert('demo_campaign_school_schedule', [
				'campaign_school_id'			=> (int)$data['campaign_school_id'],
				'schedule_id'		=> (int)$data['schedule_id'],
				'student_id'		=> (int)$student_id,
			]);
		}

		return $student_id;
	}

	public function getStudentByLeadId($campaign_school_id = 0) {
		$campaign_school_info = $this->get($campaign_school_id);

		if ($row = $this->db->get_where('demo_campaign_school_schedule', ['campaign_school_id' => (int)$campaign_school_id, 'schedule_id' => (int)$campaign_school_info['schedule_id']])->row_array()) {
			return $row;
		} else {
			$row = $this->db->get_where('users', [
				'role_id'	=> 2,
				'campaign_school_id'	=> (int)$campaign_school_info['id'],
			])->row_array();

			return ['student_id' => $row['id']];
		}
	}

	public function getByCode($code = '') {
		$this->db->select('campaign_school.*, course.title AS course, centers.name AS center, course.price AS price, course.emi AS emi, payment_link.amount AS amount, payment_link.emi_type');

		$this->db->where('payment_link.code', $code);
		//$this->db->where('campaign_school.mobile_verified', 1);

		$this->db->join('course', 'course.id = campaign_school.course_id');
		$this->db->join('centers', 'centers.id = campaign_school.center_id', 'left');
		$this->db->join('payment_link', 'payment_link.campaign_school_id = campaign_school.id');
		// $this->db->join('site', 'site.id = campaign_school.site_id', 'left');

		return $this->db->get('campaign_school')->row_array();
	}

	public function generatePaymentLink($campaign_school_id = 0, $amount = 0, $emi_type = NULL) {
		$emi_type = strtolower($emi_type);

		$payment_info = $this->db->get_where('payment_link', [
			'campaign_school_id'			=> (int)$campaign_school_id,
			'status'			=> 0,
		])->row_array();

		if ($payment_info) {
			$code = $payment_info['code'];

			$this->db->update('payment_link', [
				'amount'			=> (float)$amount,
				'emi_type'			=> $emi_type,
			], [
				'id'				=> $payment_info['id']
			]);
		} else {
			$code = sha1(uniqid());

			$this->db->insert('payment_link', [
				'campaign_school_id'			=> (int)$campaign_school_id,
				'code'				=> $code,
				'amount'			=> (float)$amount,
				'emi_type'			=> $emi_type,
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
				'status'			=> 0,
			]);
		}

		return $code;
	}

	public function getCityByCenter($center_id) {
		$this->db->select('city.name, city.id');
		$this->db->where('center.id', $center_id);
		$this->db->join('cities city', 'city.id= center.city_id');
		return $this->db->get('centers center')->row_array(0);
	}

	public function getDemoStudents($schedule_id) {
		$students = [];

		$this->db->where('demo_campaign_school_schedule.schedule_id', (int)$schedule_id);

		//$this->db->join('campaign_school', 'campaign_school.id= demo_campaign_school_schedule.campaign_school_id AND campaign_school.status = 1');

		$results = $this->db->get('demo_campaign_school_schedule')->result_array();

		foreach ($results as $result) {
			$students[] = (int)$result['student_id'];
		}

		return $students;
	}
}
