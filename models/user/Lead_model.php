<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Lead_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($lead_id = 0) {
		$this->db->select('lead.*, course.title AS course, centers.name AS center, centers.id AS center_id, site.country_code, site.name AS site, site.currency_code');

		$this->db->where('lead.id', (int)$lead_id);

		$this->db->join('course', 'course.id = lead.course_id', 'left');
		$this->db->join('centers', 'centers.id = lead.center_id', 'left');
		$this->db->join('site', 'site.id = lead.site_id', 'left');

		return $this->db->get('lead')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('lead.*, course.title AS course, centers.name AS center, centers.id AS center_id, site.country_code, site.name AS site, site.currency_code');

		if (!empty($data['event_id'])) {
			$this->db->where('lead.event_id', (int)$data['event_id']);
		}

		if (!empty($data['site_id'])) {
			$this->db->where('lead.site_id', (int)$data['site_id']);
		}

		if (!empty($data['site_ids'])) {
			$this->db->where_in('lead.site_id', $data['site_ids']);
		}

		if (!empty($data['mode'])) {
			$this->db->where('lead.mode', $data['mode']);
		}

		if (!empty($data['schedule_id'])) {
			$this->db->where('lead.schedule_id', (int)$data['schedule_id']);
		}

		if (isset($data['source_not_null'])) {
			$this->db->where('lead.utm_source !=', $data['source_not_null']);
		}

		if (isset($data['utm_source'])) {
			$this->db->where('lead.utm_source', $data['utm_source']);
		}

		if (isset($data['archived'])) {
			$this->db->where('lead.archived', (int)$data['archived']);
		}

		if (isset($data['student_id'])) {
			$this->db->where('lead.student_id', (int)$data['student_id']);
		}

		if (isset($data['grade_id'])) {
			$this->db->where('lead.grade_id', (int)$data['grade_id']);
		}

		if (isset($data['location'])) {
			$this->db->where('lead.location', $data['location']);
		}

		if (isset($data['mobile_verified'])) {
			$this->db->where('lead.mobile_verified', (int)$data['mobile_verified']);
		}

		if (isset($data['email_verified'])) {
			$this->db->where('lead.email_verified', (int)$data['email_verified']);
		}

		if (isset($data['email_mobile_verified'])) {
			$this->db->where('(lead.mobile_verified = ' . (int)$data['email_mobile_verified'] . ' OR lead.email_verified = ' . (int)$data['email_mobile_verified'] . ')');
		}

		if (!empty($data['telecaller_id'])) {
			// $this->db->where('(lead.telecaller_id', (int)$data['telecaller_id']);
			// $this->db->or_where('lead.telecaller_id', 0);
			$this->db->where('(lead.telecaller_id = 0 OR lead.telecaller_id = ' . (int)$data['telecaller_id'] . ')');
		}

		if (isset($data['book_status_writing'])) {
			$this->db->where('lead.student_id !=', 0);
			$this->db->where('lead.student_id NOT IN (select user_id from book where status = 1)');
			$this->db->where('lead.student_id IN (select user_id from book where status = 0)');

			if (isset($data['page_count'])) {
				$this->db->where('lead.student_id IN (
					select book.user_id from page join book on (book.id = page.book_id and book.status = 0)
					where page._deleted = 0
					group by page.book_id
					having count(page.id) > ' . (int)$data['page_count'] . '
				)');
			}
		}

		if (isset($data['book_status_not_writing'])) {
			$this->db->where('lead.student_id !=', 0);
			$this->db->where('lead.student_id NOT IN (select user_id from book)');
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('lead.name', $data['search'], 'after');
			$this->db->or_like('lead.parent_name', $data['search'], 'after');
			$this->db->or_like('lead.email', $data['search'], 'after');
			$this->db->or_like('lead.mobile', $data['search'], 'after');
			$this->db->or_like('lead.grade', $data['search'], 'after');
			$this->db->or_like('lead.location', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->join('course', 'course.id = lead.course_id', 'left');
		$this->db->join('centers', 'centers.id = lead.center_id', 'left');
		$this->db->join('site', 'site.id = lead.site_id', 'left');
		$this->db->from('lead');

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
			'lead.name',
			'lead.status',
			'lead.date_added',
			'lead.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'lead.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = "ASC";
		} else {
			$order = "DESC";
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		// pr($this->db->last_query(), 1);

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('lead', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'site_id'		=> !empty($data['site_id']) ? (int)$data['site_id'] : (int)$this->config->item('site_id'),
		]);

		$lead_id = $this->db->insert_id();

		self::assignTelecaller($lead_id);

		// $this->session->set_flashdata('flash_message', _l('lead_added_successfully'));

		return $lead_id;
	}

	public function edit($lead_id = 0, $data = []) {
		$this->db->where('id', $lead_id);
		$this->db->update('lead', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		// $this->session->set_flashdata('flash_message', _l('lead_edited_successfully'));
	}

	public function editByStudentId($student_id = 0, $data = []) {
		$this->db->where('student_id', $student_id);
		$this->db->update('lead', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		// $this->session->set_flashdata('flash_message', _l('lead_edited_successfully'));
	}

	public function archived($data = []) {
		if ($lead_info = self::get($data['lead_id'])) {
			$this->db->update('lead', [
				'archived'		=> $lead_info['archived'] ? 0 : 1,
			], [
				'id'			=> (int)$data['lead_id']
			]);
		}
	}

	public function add_status() {
		if ($this->get($this->input->post('lead_id'))) {
			$this->db->insert('lead_status', [
				'lead_id'		=> (int)$this->input->post('lead_id'),
				'status'		=> $this->input->post('status'),
				'comment'		=> $this->input->post('comment'),
				'user_id'		=> (int)$this->session->user_id,
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$this->db->update('lead', [
				'telecaller_id'	=> (int)$this->session->user_id
			], [
				'id'			=> (int)$this->input->post('lead_id')
			]);

			$id = $this->db->insert_id();

			$this->load->model('Alert_model', 'alert_model');

			if ($this->input->post('status') == 'not_responding') {
				// $this->alert_model->demoNotResponding($this->input->post('lead_id'));
			}

			if ($this->input->post('status') == 'course_fee_details') {
				$this->alert_model->demoFeeDetails($this->input->post('lead_id'));
			}

			$this->session->set_flashdata('flash_message', _l('lead_status_added_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('lead_not_found'));
		}
	}

	public function update_email($data = []) {
		if ($this->get($data['lead_id'])) {
			$this->db->update('lead', [
				'email'				=> $data['email'],
				'telecaller_id'		=> (int)$this->session->userdata('user_id'),
			], [
				'id'		=> (int)$data['lead_id'],
			]);

			$this->session->set_flashdata('flash_message', _l('lead_email_updated_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('lead_not_found'));
		}
	}

	public function get_status($lead_id = 0) {
		$this->db->where('lead_id', (int)$lead_id);

		return $this->db->get('lead_status')->result_array();
	}

	public function delete($lead_id = 0) {
		$this->db->where('id', $lead_id);
		$this->db->delete('lead');

		$this->session->set_flashdata('flash_message', _l('lead_deleted_successfully'));
	}

	private function assignTelecaller($lead_id, $type = 'lead') {
		$this->load->model('user/Telecaller_model', 'telecaller_model');

		/*if (($lead_info = self::get($lead_id)) && $lead_info['telecaller_id']) {
			$telecaller_info = $this->telecaller_model->get($lead_info['telecaller_id'])->row_array();

			$telecaller_id = $telecaller_info['status'] ? $lead_info['telecaller_id'] : 0;
		}*/

		$results = $this->telecaller_model->get_all([
			'status' => 1,
			'site_id'=> (int)$this->config->item('site_id')
		])['rows'];

		$index = count($results) > 0 ? mt_rand(0, count($results) - 1) : 0;

		$telecaller_id = $results[$index]['id'] ?? 0;

		if (!isset($results[$index]['id'])) {
			log_message('KB', print_r($results, 1) . '===' . $index);
		}

		// self::assignOldTelecaller($lead_id, $telecaller_id);

		self::edit($lead_id, [
			'telecaller_id'	=> (int)$telecaller_id
		]);
	}

	private function assignOldTelecaller($lead_id, &$telecaller_id) {
		if (($lead_info = self::get($lead_id)) &&
			($row = $this->db->get_where('lead', [
				'mobile' 				=> $lead_info['mobile'],
				'email' 				=> $lead_info['email'],
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
			'lead_id' 					=> (int)$data['lead_id']
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
				'lead_id' 					=> (int)$data['lead_id'],
				'comment'					=> $data['comment'],
				'date_added'				=> date('Y-m-d H:i:s'),
				'date_modified'				=> date('Y-m-d H:i:s'),
			]);
		}

		self::edit($data['lead_id'], [
			'telecaller_id'	=> $data['telecaller_id']
		]);
	}

	public function get_all_reassign($data = []) {
		$this->db->select('reassign_telecaller.*, lead.name, lead.mobile');

		if (!empty($data['lead_id'])) {
			$this->db->where('reassign_telecaller.id', (int)$data['schedule_id']);
		}

		if (!empty($data['telecaller_id'])) {
			$this->db->where('reassign_telecaller.telecaller_id', (int)$data['telecaller_id']);
		}

		if (!empty($data['original_telecaller_id'])) {
			$this->db->where('reassign_telecaller.original_telecaller_id', (int)$data['original_telecaller_id']);
		}

		$this->db->join('lead', 'lead.id = reassign_telecaller.lead_id');

		return $this->db->get('reassign_telecaller')->result_array();
	}

	public function schedule($data = []) {
		$this->db->update('schedules', [
			'lead_id'		=> 0,
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$data['old_schedule_id']
		]);

		$this->db->update('schedules', [
			'lead_id'		=> (int)$data['lead_id'],
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$data['schedule_id']
		]);

		$this->db->update('lead', [
			'schedule_id'			=> (int)$data['schedule_id'],
			'confirmed_schedule'	=> date('Y-m-d H:i:s', strtotime($data['schedule'])),
			'date_modified'			=> date('Y-m-d H:i:s'),
			'status'				=> 1,
			'telecaller_id'			=> (int)$this->session->userdata('user_id'),
		], [
			'id'					=> (int)$data['lead_id']
		]);

		$this->session->set_flashdata('flash_message', _l('lead_scheduled_for_demo_successfully'));
	}

	public function addStudent($data = [], $enrol = TRUE) {
		if ($student_info = $this->db->get_where('users', [
			'mobile' 	=> $data['mobile'],
			'email' 	=> $data['email'],
			'role_id' 	=> 2
		])->row_array()) {
			$this->db->update('users', [
				'parent_name'		=> $data['parent_name'],
				'role_id'			=> 2,
				'lead_id'			=> (int)$data['lead_id'],
				'status'			=> 1,
				'grade'				=> $data['grade'] ?? '',
				'mobile'			=> !empty($data['mobile']) ? $data['mobile'] : $student_info['mobile'],
				'email'				=> !empty($data['email']) ? $data['email'] : $student_info['email'],
				'emi_type'			=> $data['emi_type'] ?? 'free',
				'date_modified'		=> date('Y-m-d H:i:s'),
			], [
				'id'				=> (int)$student_info['id']
			]);

			$student_id = $student_info['id'];
		} else {
			$this->db->insert('users', [
				'first_name'		=> $data['first_name'],
				'last_name'			=> $data['last_name'],
				'parent_name'		=> $data['parent_name'],
				'grade'				=> $data['grade'],
				'email'				=> $data['email'],
				'mobile'			=> $data['mobile'],
				'location'			=> $data['location'],
				'emi_type'			=> $data['emi_type'] ?? 'free',
				'lead_id'			=> (int)$data['lead_id'],
				'password'			=> sha1(uniqid()),
				'role_id'			=> 2,
				'site_id'			=> (int)$this->config->item('site_id'),
				'date_added'		=> date('Y-m-d H:i:s'),
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

		if (!empty($data['schedule_id'])) {
			$this->db->delete('demo_lead_schedule', [
				'lead_id'			=> (int)$data['lead_id'],
				'student_id'		=> (int)$student_id,
			]);

			$this->db->insert('demo_lead_schedule', [
				'lead_id'			=> (int)$data['lead_id'],
				'schedule_id'		=> (int)$data['schedule_id'],
				'student_id'		=> (int)$student_id,
			]);
		}

		$enrol && self::enrolToCourse([
			'student_id'		=> $student_id,
			'course_id'			=> $data['course_id'],
			'doj'				=> date('Y-m-d H:i:s'),
			'emi_type'			=> $data['emi_type'] ?? 'free',
		]);

		return $student_id;
	}

	private function enrolToCourse($data = []) {
		// 3. Enrol new or find existing using user_id course_id, mode
		if ($enrol_info = $this->db->get_where('enrol', [
			'user_id'		=> (int)$data['student_id'],
			'course_id'		=> (int)$data['course_id'],
			'site_id'		=> (int)$this->config->item('site_id'),
			'mode'			=> 'online',
			'emi_type'		=> $data['emi_type'] ?? 'free',
		])->row_array()) {
			$enrol_id = $enrol_info['id'];

			$this->db->update('enrol', [
				'site_id'		=> (int)$this->config->item('site_id'),
				'emi_type'		=> $data['emi_type'] ?? 'free',
				// 'doj'			=> date('Y-m-d H:i:s', strtotime($data['doj'])),
				'renewal_date'	=> date('Y-m-d H:i:s', strtotime('+' . EMI_TYPES[$data['emi_type'] ?? 'free'] . ' months', strtotime($data['doj']))),
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'id'			=> (int)$enrol_id
			]);
		} else {
			$this->db->insert('enrol', [
				'site_id'		=> (int)$this->config->item('site_id'),
				'user_id'		=> (int)$data['student_id'],
				'course_id'		=> (int)$data['course_id'],
				'mode'			=> 'online',
				'emi_type'		=> $data['emi_type'] ?? 'free',
				'status'		=> ($data['emi_type'] ?? 'free') == 'free' ? 1 : 0,
				'doj'			=> date('Y-m-d H:i:s', strtotime($data['doj'])),
				'renewal_date'	=> date('Y-m-d H:i:s', strtotime('+' . EMI_TYPES[$data['emi_type'] ?? 'free'] . ' months', strtotime($data['doj']))),
				'date_added'	=> strtotime(date('Y-m-d H:i:s')),
			]);

			$enrol_id = $this->db->insert_id();
		}

		return $enrol_id;
	}

	public function getStudentByLeadId($lead_id = 0) {
		$lead_info = $this->get($lead_id);

		if ($row = $this->db->get_where('demo_lead_schedule', ['lead_id' => (int)$lead_id, 'schedule_id' => (int)$lead_info['schedule_id']])->row_array()) {
			return $row;
		} else {
			$row = $this->db->get_where('users', [
				'role_id'	=> 2,
				'lead_id'	=> (int)$lead_info['id'],
			])->row_array();

			return ['student_id' => $row['id']];
		}
	}

	public function getByCode($code = '') {
		$this->db->select('
			lead.*,
			course.title AS course,
			centers.name AS center,
			course.price AS price,
			course.emi AS emi,
			payment_link.amount AS amount,
			payment_link.emi_type,
			payment_link.locked
		');

		$this->db->where('payment_link.code', $code);
		//$this->db->where('lead.mobile_verified', 1);

		$this->db->join('course', 'course.id = lead.course_id');
		$this->db->join('centers', 'centers.id = lead.center_id', 'left');
		$this->db->join('payment_link', 'payment_link.lead_id = lead.id');
		// $this->db->join('site', 'site.id = lead.site_id', 'left');

		return $this->db->get('lead')->row_array();
	}

	public function generatePaymentLink($lead_id = 0, $amount = 0, $emi_type = NULL, $locked = 0) {
		$emi_type = strtolower($emi_type);

		$payment_info = $this->db->get_where('payment_link', [
			'lead_id'			=> (int)$lead_id,
			'status'			=> 0,
		])->row_array();

		if ($payment_info) {
			$code = $payment_info['code'];

			$this->db->update('payment_link', [
				'amount'			=> (double)$amount,
				'emi_type'			=> $emi_type,
				'locked'			=> (int)$locked,
			], [
				'id'				=> $payment_info['id']
			]);
		} else {
			$code = sha1(uniqid());

			$this->db->insert('payment_link', [
				'lead_id'			=> (int)$lead_id,
				'code'				=> $code,
				'amount'			=> (double)$amount,
				'emi_type'			=> $emi_type,
				'locked'			=> (int)$locked,
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

		$this->db->where('demo_lead_schedule.schedule_id', (int)$schedule_id);

		//$this->db->join('lead', 'lead.id= demo_lead_schedule.lead_id AND lead.status = 1');

		$results = $this->db->get('demo_lead_schedule')->result_array();

		foreach ($results as $result) {
			$students[] = (int)$result['student_id'];
		}

		return $students;
	}

	public function updateByCode($code = NULL, $amount = 0, $emi_type = NULL) {
		$emi_type = strtolower($emi_type);

		$payment_info = $this->db->get_where('payment_link', [
			'code'			=> $code
		])->row_array();

		if ($payment_info) {
			$this->db->update('payment_link', [
				'amount'			=> (double)$amount,
				'emi_type'			=> $emi_type,
			], [
				'code'				=> $code
			]);
		}
	}

	public function getByStudentId($student_id = 0) {
		$this->db->select('lead.*, site.country_code, site.name AS site, site.currency_code');

		$this->db->where('lead.student_id', (int)$student_id);

		$this->db->join('site', 'site.id = lead.site_id', 'left');

		return $this->db->get('lead')->row_array();
	}

	public function getCountByGrade($data = []) {
		$this->db->simple_query('SET SESSION group_concat_max_len=10000');

		$this->db->select('site_grade.id AS grade_id, site_grade.name AS grade_name, count(site_grade.id) AS count, GROUP_CONCAT(lead.name SEPARATOR ",") AS lead_names');

		$this->db->where('lead.site_id', (int)$data['site_id']);

		if (isset($data['event_id'])) {
			$this->db->where('lead.event_id', (int)$data['event_id']);
		}

		if (isset($data['mobile_verified'])) {
			$this->db->where('lead.mobile_verified', (int)$data['mobile_verified']);
		}

		if (isset($data['email_verified'])) {
			$this->db->where('lead.email_verified', (int)$data['email_verified']);
		}

		if (isset($data['email_mobile_verified'])) {
			$this->db->where('(lead.mobile_verified = ' . (int)$data['email_mobile_verified'] . ' OR lead.email_verified = ' . (int)$data['email_mobile_verified'] . ')');
		}

		$this->db->join('site_grade', 'site_grade.id = lead.grade_id', 'left');

		$this->db->order_by('CAST(site_grade.name as UNSIGNED)', 'ASC');

		$this->db->group_by('lead.grade_id');

		return $this->db->get('lead')->result_array();
	}
}
