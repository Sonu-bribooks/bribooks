<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SchoolLead_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('
			school_lead.*,
			site.country_code,
			site.name AS site,
			site.currency_code
		');

		$this->db->where('school_lead.id', (int)$id);
		$this->db->where('school_lead._deleted', 0);

		$this->db->join('site', 'site.id = school_lead.site_id', 'left');

		return $this->db->get('school_lead')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('
			school_lead.*,
			site.country_code,
			site.name AS site,
			site.currency_code
		');

		if (!empty($data['event_id'])) {
			$this->db->where('school_lead.event_id', (int)$data['event_id']);
		}

		if (!empty($data['site_id'])) {
			$this->db->where('school_lead.site_id', (int)$data['site_id']);
		}

		if (!empty($data['school_id'])) {
			$this->db->where('school_lead.school_id', (int)$data['school_id']);
		}

		if (isset($data['archived'])) {
			$this->db->where('school_lead.archived', (int)$data['archived']);
		}

		if (!empty($data['telecaller_id'])) {
			$this->db->where('school_lead.telecaller_id', (int)$data['telecaller_id']);
		}

		if (isset($data['name'])) {
			$this->db->where('school_lead.name', $data['name']);
		}

		if (!empty($data['email'])) {
			$this->db->where('school_lead.email', trim($data['email']));
		}

		if (!empty($data['mobile'])) {
			$this->db->where('school_lead.mobile', trim($data['mobile']));
		}

		if (isset($data['city_id'])) {
			$this->db->where('school_lead.city_id', (int)$data['city_id']);
		}

		if (isset($data['state_id'])) {
			$this->db->where('school_lead.state_id', (int)$data['state_id']);
		}

		if (isset($data['mobile_verified'])) {
			$this->db->where('school_lead.mobile_verified', (int)$data['mobile_verified']);
		}

		if (isset($data['email_verified'])) {
			$this->db->where('school_lead.email_verified', (int)$data['email_verified']);
		}

		if (isset($data['verified'])) {
			$this->db->where('school_lead.verified', (int)$data['verified']);
		}

		if (isset($data['email_mobile_verified'])) {
			$this->db->where('(school_lead.mobile_verified = ' . (int)$data['email_mobile_verified'] . ' OR school_lead.email_verified = ' . (int)$data['email_mobile_verified'] . ')');
		}

		if (isset($data['source_not_null'])) {
			$this->db->where('school_lead.utm_source !=', $data['source_not_null']);
		}

		if (!empty($data['source'])) {
			$this->db->where('school_lead.utm_source', $data['source']);
		}

		if (isset($data['location'])) {
			$this->db->where('school_lead.country', $data['location']);
		}

		$this->db->where('school_lead._deleted', 0);

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('school_lead.name', $data['search'], 'both');
			$this->db->or_like('school_lead.authorized_person', $data['search'], 'both');
			$this->db->or_like('school_lead.email', $data['search'], 'after');
			$this->db->or_like('school_lead.mobile', $data['search'], 'after');
			$this->db->group_end();
		}
		$this->db->join('site', 'site.id = school_lead.site_id', 'left');

		$this->db->from('school_lead');

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
			'school_lead.name',
			'school_lead.status',
			'school_lead.date_added',
			'school_lead.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'school_lead.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('school_lead', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> (int)$data['site_id'] ?? (int)$this->config->item('site_id'),
		]);

		$school_lead_id = $this->db->insert_id();

		// self::assignTelecaller($school_lead_id);

		$this->session->set_flashdata('flash_message', _l('school_lead_added_successfully'));

		return $school_lead_id;
	}

	public function edit($school_lead_id = 0, $data = []) {
		$this->db->where('id', $school_lead_id);
		$this->db->update('school_lead', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('school_lead_edited_successfully'));
	}

	public function archived($data = []) {
		if ($school_lead_info = self::get($data['school_lead_id'])) {
			$this->db->update('school_lead', [
				'archived'		=> $school_lead_info['archived'] ? 0 : 1,
			], [
				'id'			=> (int)$data['school_lead_id']
			]);
		}
	}

	public function add_status() {
		if ($this->get($this->input->post('school_lead_id'))) {
			$this->db->insert('school_lead_status', [
				'school_lead_id'	=> (int)$this->input->post('school_lead_id'),
				'status'			=> $this->input->post('status'),
				'comment'			=> $this->input->post('comment'),
				'user_id'			=> (int)$this->session->user_id,
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
			]);

			$this->db->update('school_lead', [
				'telecaller_id'	=> (int)$this->session->user_id
			], [
				'id'			=> (int)$this->input->post('school_lead_id')
			]);

			$id = $this->db->insert_id();

			$this->load->model('Alert_model', 'alert_model');

			if ($this->input->post('status') == 'not_responding') {
				// $this->alert_model->demoNotResponding($this->input->post('school_lead_id'));
			}

			if ($this->input->post('status') == 'course_fee_details') {
				// $this->alert_model->demoFeeDetails($this->input->post('school_lead_id'));
			}

			$this->session->set_flashdata('flash_message', _l('school_lead_status_added_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('school_lead_not_found'));
		}
	}

	public function update_email($data = []) {
		if ($this->get($data['school_lead_id'])) {
			$this->db->update('school_lead', [
				'email'				=> $data['email'],
				'telecaller_id'		=> (int)$this->session->userdata('user_id'),
			], [
				'id'		=> (int)$data['school_lead_id'],
			]);

			$this->session->set_flashdata('flash_message', _l('school_lead_email_updated_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('school_lead_not_found'));
		}
	}

	public function get_status($school_lead_id = 0) {
		$this->db->where('school_lead_id', (int)$school_lead_id);

		return $this->db->get('school_lead_status')->result_array();
	}

	public function delete($school_lead_id = 0) {
		$this->db->update('school_lead', [
			'_deleted'	=> 1,
		], [
			'id'		=> (int)$school_lead_id,
		]);

		$this->session->set_flashdata('flash_message', _l('school_lead_deleted_successfully'));
	}

	private function assignTelecaller($school_lead_id, $type = 'school_lead') {
		$this->load->model('user/Telecaller_model', 'telecaller_model');

		/*if (($school_lead_info = self::get($school_lead_id)) && $school_lead_info['telecaller_id']) {
			$telecaller_info = $this->telecaller_model->get($school_lead_info['telecaller_id'])->row_array();

			$telecaller_id = $telecaller_info['status'] ? $school_lead_info['telecaller_id'] : 0;
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

		// self::assignOldTelecaller($school_lead_id, $telecaller_id);

		self::edit($school_lead_id, [
			'telecaller_id'	=> (int)$telecaller_id
		]);
	}

	private function assignOldTelecaller($school_lead_id, &$telecaller_id) {
		if (($school_lead_info = self::get($school_lead_id)) &&
			($row = $this->db->get_where('school_lead', [
				'mobile' 				=> $school_lead_info['mobile'],
				'email' 				=> $school_lead_info['email'],
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
			'school_lead_id' 					=> (int)$data['school_lead_id']
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
				'school_lead_id' 					=> (int)$data['school_lead_id'],
				'comment'					=> $data['comment'],
				'date_added'				=> date('Y-m-d H:i:s'),
				'date_modified'				=> date('Y-m-d H:i:s'),
			]);
		}

		self::edit($data['school_lead_id'], [
			'telecaller_id'	=> $data['telecaller_id']
		]);
	}

	public function get_all_reassign($data = []) {
		$this->db->select('reassign_telecaller.*, school_lead.name, school_lead.mobile');

		if (!empty($data['school_lead_id'])) {
			$this->db->where('reassign_telecaller.id', (int)$data['schedule_id']);
		}

		if (!empty($data['telecaller_id'])) {
			$this->db->where('reassign_telecaller.telecaller_id', (int)$data['telecaller_id']);
		}

		if (!empty($data['original_telecaller_id'])) {
			$this->db->where('reassign_telecaller.original_telecaller_id', (int)$data['original_telecaller_id']);
		}

		$this->db->join('school_lead', 'school_lead.id = reassign_telecaller.school_lead_id');

		return $this->db->get('reassign_telecaller')->result_array();
	}

	public function schedule($data = []) {
		$this->db->update('schedules', [
			'school_lead_id'		=> 0,
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$data['old_schedule_id']
		]);

		$this->db->update('schedules', [
			'school_lead_id'		=> (int)$data['school_lead_id'],
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$data['schedule_id']
		]);

		$this->db->update('school_lead', [
			'schedule_id'			=> (int)$data['schedule_id'],
			'confirmed_schedule'	=> date('Y-m-d H:i:s', strtotime($data['schedule'])),
			'date_modified'			=> date('Y-m-d H:i:s'),
			'status'				=> 1,
			'telecaller_id'			=> (int)$this->session->userdata('user_id'),
		], [
			'id'					=> (int)$data['school_lead_id']
		]);

		$this->session->set_flashdata('flash_message', _l('school_lead_scheduled_for_demo_successfully'));
	}

	public function addStudent($data = []) {
		if ($student_info = $this->db->get_where('users', ['mobile' => $data['mobile'], 'role_id' => 2])->row_array()) {
			$this->db->update('users', [
				'password'			=> sha1(uniqid()),
				'parent_name'		=> $data['parent_name'],
				'role_id'			=> 2,
				'school_lead_id'			=> (int)$data['school_lead_id'],
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
				'school_lead_id'			=> (int)$data['school_lead_id'],
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
			$this->db->delete('demo_school_lead_schedule', [
				'school_lead_id'			=> (int)$data['school_lead_id'],
				'student_id'		=> (int)$student_id,
			]);

			$this->db->insert('demo_school_lead_schedule', [
				'school_lead_id'			=> (int)$data['school_lead_id'],
				'schedule_id'		=> (int)$data['schedule_id'],
				'student_id'		=> (int)$student_id,
			]);
		}

		return $student_id;
	}

	public function getStudentByLeadId($school_lead_id = 0) {
		$school_lead_info = $this->get($school_lead_id);

		if ($row = $this->db->get_where('demo_school_lead_schedule', ['school_lead_id' => (int)$school_lead_id, 'schedule_id' => (int)$school_lead_info['schedule_id']])->row_array()) {
			return $row;
		} else {
			$row = $this->db->get_where('users', [
				'role_id'	=> 2,
				'school_lead_id'	=> (int)$school_lead_info['id'],
			])->row_array();

			return ['student_id' => $row['id']];
		}
	}

	public function getByCode($code = '') {
		$this->db->select('school_lead.*, course.title AS course, centers.name AS center, course.price AS price, course.emi AS emi, payment_link.amount AS amount, payment_link.emi_type');

		$this->db->where('payment_link.code', $code);
		//$this->db->where('school_lead.mobile_verified', 1);

		$this->db->join('course', 'course.id = school_lead.course_id');
		$this->db->join('centers', 'centers.id = school_lead.center_id', 'left');
		$this->db->join('payment_link', 'payment_link.school_lead_id = school_lead.id');
		// $this->db->join('site', 'site.id = school_lead.site_id', 'left');

		return $this->db->get('school_lead')->row_array();
	}

	public function generatePaymentLink($school_lead_id = 0, $amount = 0, $emi_type = NULL) {
		$emi_type = strtolower($emi_type);

		$payment_info = $this->db->get_where('payment_link', [
			'school_lead_id'			=> (int)$school_lead_id,
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
				'school_lead_id'			=> (int)$school_lead_id,
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
		$this->db->join('cities city', 'city.id = center.city_id');
		return $this->db->get('centers center')->row_array(0);
	}

	public function getDemoStudents($schedule_id) {
		$students = [];

		$this->db->where('demo_school_lead_schedule.schedule_id', (int)$schedule_id);

		//$this->db->join('school_lead', 'school_lead.id= demo_school_lead_schedule.school_lead_id AND school_lead.status = 1');

		$results = $this->db->get('demo_school_lead_schedule')->result_array();

		foreach ($results as $result) {
			$students[] = (int)$result['student_id'];
		}

		return $students;
	}

	public function getBySchoolId($school_id = 0) {
		$this->db->select('id');
		$this->db->where('school_lead.site_id!=0');
		$this->db->where('school_lead.school_id', (int)$school_id);
		return $this->db->get('school_lead')->row_array();
	}

	public function getSchoolLeadBySiteId($site_id = 0) {
		$this->db->select('school_lead.site_id, school_lead.school_id, school_lead.name, school_lead.email, school_lead.mobile, school_lead.state_id, school_lead.city_id, state.name as state, city.name as city');
		$this->db->where('school_lead.site_id', (int)$site_id);
		$this->db->where('school_lead.school_id !=', 0);

		$this->db->where('school_lead.mobile_verified', 1);
		$this->db->or_where('school_lead.email_verified', 1);

		$this->db->join('state', 'state.id = school_lead.state_id');
		$this->db->join('city', 'city.id = school_lead.city_id');

		return $this->db->get('school_lead')->row_array();
	}

	public function getSchoolLeadByWhere($where) {
		$this->db->where($where);
		return $this->db->get('school_lead')->row_array();
	}
}
