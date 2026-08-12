<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SchoolInput_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('
			schools_input.*
		');

		$this->db->where('schools_input.id', (int)$id);

		return $this->db->get('schools_input')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			schools_input.*
		');

		if (!empty($data['search'])) {
			$this->db->like('schools_input.name', $data['search'], 'after');
		}

		if (!empty($data['name'])) {
			$this->db->where('schools_input.name', $data['name']);
		}

		if (!empty($data['country_id'])) {
			$this->db->where('schools_input.state_id IN (SELECT `id` FROM `state` where country_id='.$data['country_id'].')', NULL, FALSE);
		}

		if (!empty($data['state_id'])) {
			$this->db->where('schools_input.state_id', (int)$data['state_id'] );
		}

		if (!empty($data['city_id'])) {
			$this->db->where('schools_input.city_id', (int)$data['city_id'] );
		}

		if (!empty($data['type'])) {
			$this->db->where('schools_input.type', (int)$data['type'] );
		}

		if (isset($data['has_registered'])) {
			if ($data['has_registered'] == '1') {
				$this->db->where('schools_input.id IN (SELECT `school_id` FROM `school_lead` where school_lead.school_id=schools_input.id)', NULL, FALSE);
			} else {
				$this->db->where('schools_input.id NOT IN (SELECT `school_id` FROM `school_lead` where school_lead.school_id=schools_input.id)', NULL, FALSE);
			}
		}

		$this->db->from('schools_input');

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
			'schools_input.name',
			'schools_input.date_added',
			'schools_input.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'schools_input.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = "ASC";
		} else {
			$order = "DESC";
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('schools_input', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$school_input_id = $this->db->insert_id();

		// self::assignTelecaller($school_input_id);

		$this->session->set_flashdata('flash_message', _l('school_input_added_successfully'));

		return $school_input_id;
	}

	public function edit($schools_input_id = 0, $data = []) {
		$this->db->where('id', $schools_input_id);
		$this->db->update('schools_input', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('schools_input_edited_successfully'));
	}

	public function archived($data = []) {
		if ($schools_input_info = self::get($data['schools_input_id'])) {
			$this->db->update('schools_input', [
				'archived'		=> $schools_input_info['archived'] ? 0 : 1,
			], [
				'id'			=> (int)$data['schools_input_id']
			]);
		}
	}

	public function add_status() {
		if ($this->get($this->input->post('schools_input_id'))) {
			$this->db->insert('schools_input_status', [
				'schools_input_id'	=> (int)$this->input->post('schools_input_id'),
				'status'			=> $this->input->post('status'),
				'comment'			=> $this->input->post('comment'),
				'user_id'			=> (int)$this->session->user_id,
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
			]);

			$this->db->update('schools_input', [
				'telecaller_id'	=> (int)$this->session->user_id
			], [
				'id'			=> (int)$this->input->post('schools_input_id')
			]);

			$id = $this->db->insert_id();

			$this->load->model('Alert_model', 'alert_model');

			if ($this->input->post('status') == 'not_responding') {
				// $this->alert_model->demoNotResponding($this->input->post('schools_input_id'));
			}

			if ($this->input->post('status') == 'course_fee_details') {
				// $this->alert_model->demoFeeDetails($this->input->post('schools_input_id'));
			}

			$this->session->set_flashdata('flash_message', _l('schools_input_status_added_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('schools_input_not_found'));
		}
	}

	public function update_email($data = []) {
		if ($this->get($data['schools_input_id'])) {
			$this->db->update('schools_input', [
				'email'				=> $data['email'],
				'telecaller_id'		=> (int)$this->session->userdata('user_id'),
			], [
				'id'		=> (int)$data['schools_input_id'],
			]);

			$this->session->set_flashdata('flash_message', _l('schools_input_email_updated_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('schools_input_not_found'));
		}
	}

	public function get_status($schools_input_id = 0) {
		$this->db->where('schools_input_id', (int)$schools_input_id);

		return $this->db->get('schools_input_status')->result_array();
	}

	public function delete($schools_input_id = 0) {
		$this->db->where('id', $schools_input_id);
		$this->db->delete('schools_input');

		$this->session->set_flashdata('flash_message', _l('schools_input_deleted_successfully'));
	}

	private function assignTelecaller($schools_input_id, $type = 'schools_input') {
		$this->load->model('user/Telecaller_model', 'telecaller_model');

		/*if (($schools_input_info = self::get($schools_input_id)) && $schools_input_info['telecaller_id']) {
			$telecaller_info = $this->telecaller_model->get($schools_input_info['telecaller_id'])->row_array();

			$telecaller_id = $telecaller_info['status'] ? $schools_input_info['telecaller_id'] : 0;
		}*/

		$results = $this->telecaller_model->get_all([
			'status' => 1,
			'site_id'=> (int)$this->config->item('site_id')
		])->result_array();

		$index = count($results) > 0 ? mt_rand(0, count($results) - 1) : 0;

		$telecaller_id = $results[$index]['id'] ?? 0;

		if (!isset($results[$index]['id'])) {
			log_message('KB', print_r($results, 1) . '===' . $index);
		}

		// self::assignOldTelecaller($schools_input_id, $telecaller_id);

		self::edit($schools_input_id, [
			'telecaller_id'	=> (int)$telecaller_id
		]);
	}

	private function assignOldTelecaller($schools_input_id, &$telecaller_id) {
		if (($schools_input_info = self::get($schools_input_id)) &&
			($row = $this->db->get_where('schools_input', [
				'mobile' 				=> $schools_input_info['mobile'],
				'email' 				=> $schools_input_info['email'],
				'telecaller_id > ' 		=> 0,
				'site_id'				=> (int)$this->config->item('site_id'),
			])->row_array())
		) {
			$telecaller_id = $row['telecaller_id'];
		}
	}

	public function reassignTelecaller($data = []) {
		if ($row = $this->db->get_where('reassign_telecaller', [
			'original_telecaller_id' 	=> (int)$data['original_telecaller_id'],
			'telecaller_id' 			=> (int)$data['telecaller_id'],
			'schools_input_id' 					=> (int)$data['schools_input_id']
		])->row_array()) {
			$this->db->update('reassign_telecaller', [
				'comment'					=> $data['comment'],
				'date_modified'				=> date('Y-m-d H:i:s'),
			], [
				'id'		=> (int)$row['id']
			]);
		} else {
			$this->db->insert('reassign_telecaller', [
				'original_telecaller_id' 	=> (int)$data['original_telecaller_id'],
				'telecaller_id' 			=> (int)$data['telecaller_id'],
				'schools_input_id' 					=> (int)$data['schools_input_id'],
				'comment'					=> $data['comment'],
				'date_added'				=> date('Y-m-d H:i:s'),
				'date_modified'				=> date('Y-m-d H:i:s'),
			]);
		}

		self::edit($data['schools_input_id'], [
			'telecaller_id'	=> $data['telecaller_id']
		]);
	}

	public function get_all_reassign($data = []) {
		$this->db->select('reassign_telecaller.*, schools_input.name, schools_input.mobile');

		if (!empty($data['schools_input_id'])) {
			$this->db->where('reassign_telecaller.id', (int)$data['schedule_id']);
		}

		if (!empty($data['telecaller_id'])) {
			$this->db->where('reassign_telecaller.telecaller_id', (int)$data['telecaller_id']);
		}

		if (!empty($data['original_telecaller_id'])) {
			$this->db->where('reassign_telecaller.original_telecaller_id', (int)$data['original_telecaller_id']);
		}

		$this->db->join('schools_input', 'schools_input.id = reassign_telecaller.schools_input_id');

		return $this->db->get('reassign_telecaller')->result_array();
	}

	public function schedule($data = []) {
		$this->db->update('schedules', [
			'schools_input_id'		=> 0,
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$data['old_schedule_id']
		]);

		$this->db->update('schedules', [
			'schools_input_id'		=> (int)$data['schools_input_id'],
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$data['schedule_id']
		]);

		$this->db->update('schools_input', [
			'schedule_id'			=> (int)$data['schedule_id'],
			'confirmed_schedule'	=> date('Y-m-d H:i:s', strtotime($data['schedule'])),
			'date_modified'			=> date('Y-m-d H:i:s'),
			'status'				=> 1,
			'telecaller_id'			=> (int)$this->session->userdata('user_id'),
		], [
			'id'					=> (int)$data['schools_input_id']
		]);

		$this->session->set_flashdata('flash_message', _l('schools_input_scheduled_for_demo_successfully'));
	}

	public function addStudent($data = []) {
		if ($student_info = $this->db->get_where('users', ['mobile' => $data['mobile'], 'role_id' => 2])->row_array()) {
			$this->db->update('users', [
				'password'			=> sha1(uniqid()),
				'parent_name'		=> $data['parent_name'],
				'role_id'			=> 2,
				'schools_input_id'			=> (int)$data['schools_input_id'],
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
				'schools_input_id'			=> (int)$data['schools_input_id'],
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
			$this->db->delete('demo_schools_input_schedule', [
				'schools_input_id'			=> (int)$data['schools_input_id'],
				'student_id'		=> (int)$student_id,
			]);

			$this->db->insert('demo_schools_input_schedule', [
				'schools_input_id'			=> (int)$data['schools_input_id'],
				'schedule_id'		=> (int)$data['schedule_id'],
				'student_id'		=> (int)$student_id,
			]);
		}

		return $student_id;
	}

	public function getStudentByLeadId($schools_input_id = 0) {
		$schools_input_info = $this->get($schools_input_id);

		if ($row = $this->db->get_where('demo_schools_input_schedule', ['schools_input_id' => (int)$schools_input_id, 'schedule_id' => (int)$schools_input_info['schedule_id']])->row_array()) {
			return $row;
		} else {
			$row = $this->db->get_where('users', [
				'role_id'	=> 2,
				'schools_input_id'	=> (int)$schools_input_info['id'],
			])->row_array();

			return ['student_id' => $row['id']];
		}
	}

	public function getByCode($code = '') {
		$this->db->select('schools_input.*, course.title AS course, centers.name AS center, course.price AS price, course.emi AS emi, payment_link.amount AS amount, payment_link.emi_type');

		$this->db->where('payment_link.code', $code);
		//$this->db->where('schools_input.mobile_verified', 1);

		$this->db->join('course', 'course.id = schools_input.course_id');
		$this->db->join('centers', 'centers.id = schools_input.center_id', 'left');
		$this->db->join('payment_link', 'payment_link.schools_input_id = schools_input.id');
		// $this->db->join('site', 'site.id = schools_input.site_id', 'left');

		return $this->db->get('schools_input')->row_array();
	}

	public function generatePaymentLink($schools_input_id = 0, $amount = 0, $emi_type = NULL) {
		$emi_type = strtolower($emi_type);

		$payment_info = $this->db->get_where('payment_link', [
			'schools_input_id'			=> (int)$schools_input_id,
			'status'			=> 0,
		])->row_array();

		if ($payment_info) {
			$code = $payment_info['code'];

			$this->db->update('payment_link', [
				'amount'			=> (double)$amount,
				'emi_type'			=> $emi_type,
			], [
				'id'				=> $payment_info['id']
			]);
		} else {
			$code = sha1(uniqid());

			$this->db->insert('payment_link', [
				'schools_input_id'			=> (int)$schools_input_id,
				'code'				=> $code,
				'amount'			=> (double)$amount,
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

		$this->db->where('demo_schools_input_schedule.schedule_id', (int)$schedule_id);

		//$this->db->join('schools_input', 'schools_input.id= demo_schools_input_schedule.schools_input_id AND schools_input.status = 1');

		$results = $this->db->get('demo_schools_input_schedule')->result_array();

		foreach ($results as $result) {
			$students[] = (int)$result['student_id'];
		}

		return $students;
	}
}
