<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Enrol_model extends CI_Model {
	public function get($enrol_id = 0) {
		$this->db->select('enrol.*, , site.country_code, site.currency_code, payment.amount AS amount, course.title AS course, CONCAT(users.first_name, " ", IF(ISNULL(users.last_name), "", users.last_name)) AS user, users.parent_name, users.mobile AS mobile, users.email AS email, site.country_code, site.currency_code, site.name AS site');

		$this->db->join('users', 'users.id = enrol.user_id');
		$this->db->join('course', 'course.id = enrol.course_id');
		$this->db->join('payment', 'payment.enrol_id = enrol.id', 'left');
		$this->db->join('site', 'site.id = enrol.site_id', 'left');

		return $this->db->get_where('enrol', [
			'enrol.id'		=> (int)$enrol_id
		])->row_array();
	}

	public function enrol($data = []) {
		$enrol_id = 0;

		if (!empty($data['enrol_id'])) {
			$enrol_info = $this->db->get_where('enrol', [
				'id'			=> (int)$data['enrol_id'],
			])->row_array();
		} else {
			$enrol_info = $this->db->get_where('enrol', [
				'user_id'		=> (int)$data['user_id'],
				'course_id'		=> (int)$data['course_id'],
				'mode'			=> $data['mode'],
			])->row_array();
		}

		if ($enrol_info) {
			if (!empty($data['emi_type'])) {
				$enrol_info['emi_type'] = $data['emi_type'];
			}

			$enrol_id = $enrol_info['id'];

			$enrol_info['emi_type'] = strtolower($enrol_info['emi_type']);

			// preg_match('/(\w+?)_(\w+?)_(?P<emi_type>\w+?)$/', $enrol_info['emi_type'], $matches);
			// $renewal_period = $enrol_info['emi_type'] == 'other' ? EMI_TYPES[$enrol_info['emi_type']] : EMI_TYPES[$matches['emi_type']];

			$renewal_period = EMI_TYPES[$enrol_info['emi_type']];

			if ($enrol_info['mode'] == 'online') {
				$enrol_info['renewal_date'] = strtotime($enrol_info['renewal_date']) > time() ? $enrol_info['renewal_date'] : date('Y-m-d H:i:s');
			}

			$this->db->update('enrol', [
				'renewal_date'	=> date('Y-m-d H:i:s', strtotime("+{$renewal_period} month", strtotime($enrol_info['renewal_date']))),
				'last_modified'	=> strtotime(date('Y-m-d H:i:s')),
				'status'		=> $data['status'] ?? 1,
				'archived'		=> 0,
				'emi_type'		=> $enrol_info['emi_type'],
				'date_renewed'	=> date('Y-m-d H:i:s'),
			], [
				'id'			=> (int)$enrol_info['id']
			]);

			$data['order_id'] && $this->db->insert('payment', [
				'order_id'		=> (int)$data['order_id'],
				'user_id'		=> (int)$enrol_info['user_id'],
				'course_id'		=> (int)$enrol_info['course_id'],
				'enrol_id'		=> (int)$enrol_info['id'],
				'payment_type'	=> $data['payment_type'],
				'emi_type'		=> $enrol_info['emi_type'],
				'amount'		=> (double)$data['amount'],
				'date_added'	=> strtotime(date('Y-m-d H:i:s')),
				'site_id'		=> (int)$this->config->item('site_id'),
			]);

			$this->session->set_flashdata('flash_message', _l('enrolment_updated_successfully'));
		} else {
			$data['emi_type'] = strtolower($data['emi_type']);

			// preg_match('/(\w+?)_(\w+?)_(?P<emi_type>\w+?)$/', $data['emi_type'], $matches);
			// $renewal_period = $data['emi_type'] == 'other' ? EMI_TYPES[$data['emi_type']] : EMI_TYPES[$matches['emi_type']];

			$renewal_period = EMI_TYPES[$data['emi_type']];

			$this->db->insert('enrol', [
				'user_id'		=> (int)$data['user_id'],
				'course_id'		=> (int)$data['course_id'],
				'mode'			=> $data['mode'],
				'emi_type'		=> $data['emi_type'],
				'status'		=> $data['status'] ?? 1,
				'renewal_date'	=> date('Y-m-d H:i:s', strtotime("+{$renewal_period} month")),
				'doj'			=> date('Y-m-d H:i:s'),
				'date_added'	=> strtotime(date('Y-m-d H:i:s')),
				'site_id'		=> (int)$this->config->item('site_id'),
			]);

			$enrol_id = $this->db->insert_id();

			$data['order_id'] && $this->db->insert('payment', [
				'order_id'		=> (int)$data['order_id'],
				'user_id'		=> (int)$data['user_id'],
				'course_id'		=> (int)$data['course_id'],
				'enrol_id'		=> (int)$enrol_id,
				'payment_type'	=> $data['payment_type'],
				'emi_type'		=> $data['emi_type'],
				'amount'		=> (double)$data['amount'],
				'date_added'	=> strtotime(date('Y-m-d H:i:s')),
				'site_id'		=> (int)$this->config->item('site_id'),
			]);

			$this->session->set_flashdata('flash_message', _l('enrolled_successfully'));
		}

		return $enrol_id;
	}

	public function edit($enrol_id, $data = []) {
		$this->db->update('enrol', $data + [
			'date_modified'	=> date('Y-m-d H:i:s')
		],[
			'id'			=> (int)$enrol_id
		]);
	}

	public function getAll($data = []) {
		/*if (empty($data['no_payment'])) {
			$this->db->select('enrol.*, payment.amount AS amount, course.title AS course, CONCAT(users.first_name, " ", users.last_name) AS user, users.mobile AS mobile, users.email AS email');
		} else {
			$this->db->select('enrol.*, course.title AS course, CONCAT(users.first_name, " ", users.last_name) AS user, users.mobile AS mobile, users.email AS email');
		}*/

		$this->db->select('enrol.*, course.title AS course, CONCAT(users.first_name, " ", IF(ISNULL(users.last_name), "", users.last_name)) AS user, users.mobile AS mobile, users.email AS email, site.name AS site, site.country_code, site.currency_code');

		$this->db->join('users', 'users.id = enrol.user_id');
		$this->db->join('course', 'course.id = enrol.course_id');
		$this->db->join('site', 'site.id = enrol.site_id');

		/*if (empty($data['no_payment'])) {
			$this->db->join('payment', 'payment.enrol_id = enrol.id');
		}*/

		if (!empty($data['date_start'])) {
			$this->db->where('enrol.date_added >=' , $data['date_start']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('enrol.site_id' , (int)$data['site_id']);
		}

		if (!empty($data['date_end'])) {
			$this->db->where('enrol.date_added <=' , $data['date_end']);
		}

		if (!empty($data['user_id'])) {
			$this->db->where('enrol.user_id', (int)$data['user_id']);
		}

		if (!empty($data['course_id'])) {
			$this->db->where('enrol.course_id', (int)$data['course_id']);
		}

		if (!empty($data['emi_type'])) {
			$this->db->where('enrol.emi_type', $data['emi_type']);
		}

		if (isset($data['archived'])) {
			$this->db->where('enrol.archived', (int)$data['archived']);
		}

		if (isset($data['status'])) {
			$this->db->where('enrol.status', (int)$data['status']);
		}

		if (isset($data['exported'])) {
			$this->db->where('enrol.exported', (int)$data['exported']);
		}

		if (!empty($data['order']) && !empty($data['sort']) && in_array($data['order'], ['ASC', 'DESC'])) {
			$this->db->order_by($data['sort'], $data['order']);
		} else {
			$this->db->order_by('enrol.renewal_date', 'ASC');
		}

		if (!empty($data['limit']) && empty($data['offset'])) {
			$this->db->limit((int)$data['limit']);
		}

		if (!empty($data['limit']) && !empty($data['offset']) && $data['offset'] > 0) {
			$this->db->limit((int)$data['offset'], (int)$data['limit']);
		}

		return $this->db->get('enrol')->result_array();
	}

	public function getPaymentByEnrolId($enrol_id) {
		$this->db->order_by('date_added', 'DESC');

		return $this->db->get_where('payment', [
			'enrol_id'		=> (int)$enrol_id,
		])->row_array();
	}

	public function addFeeCollection($data = []) {
		$id = null;

		$payment_info = $this->enrol_model->getPaymentByEnrolId($data['enrol_id']);

		if ($row = $this->db->get_where('fee_collection', [
			'enrol_id'		=> (int)$data['enrol_id'],
		])->row_array()) {
			$this->db->update('fee_collection', [
				'amount'		=> (double)$payment_info['amount'] ?? 0,
				'student_id'	=> (int)$payment_info['user_id'],
				'teacher_id'	=> (int)$this->session->user_id,
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'id'			=> (int)$row['id']
			]);

			$id = $row['id'];
		} else {
			$this->db->insert('fee_collection', [
				'enrol_id'		=> (int)$data['enrol_id'],
				'amount'		=> (double)$payment_info['amount'] ?? 0,
				'student_id'	=> (int)$payment_info['user_id'],
				'teacher_id'	=> (int)$this->session->user_id,
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$id = $this->db->insert_id();
		}

		return $id;
	}

	public function getFeeCollection($data = []) {
		$this->db->select('fee_collection.*, CONCAT(t.first_name, " ", t.last_name) AS teacher, CONCAT(s.first_name, " ", IF(ISNULL(s.last_name), "", s.last_name)) AS student, course.title AS course, course.id AS course_id');

		$this->db->join('users t', 't.id = fee_collection.teacher_id');
		$this->db->join('users s', 's.id = fee_collection.student_id');
		$this->db->join('enrol', 'enrol.id = fee_collection.enrol_id');
		$this->db->join('course', 'course.id = enrol.course_id');

		if (!empty($data['id'])) {
			$this->db->where('id', (int)$data['id']);
		}

		return $this->db->get('fee_collection')->result_array();
	}

	public function approveFeeCollection($id = 0) {
		if (($row = $this->db->get_where('fee_collection', ['id' => (int)$id])->row_array()) && ($enrol_info = self::get($row['enrol_id']))) {
			$this->db->update('fee_collection', [
				'status'		=> 1,
				'admin_id'		=> (int)$this->session->user_id,
				'date_approved'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'id'			=> (int)$id
			]);

			self::renewOffline($enrol_info['id']);
		}
	}

	public function renewOffline($enrol_id = 0, $amount = 0) {
		if ($enrol_info = self::get($enrol_id)) {
			$payment_info = $this->enrol_model->getPaymentByEnrolId($enrol_info['id']);

			$this->load->model('order/Order_model', 'order_model');

			$amount > 0 && ($payment_info['amount'] = $amount);

			$order_id = $this->order_model->add([
				'enrol_id'		=> (int)$enrol_info['id'],
				'user_id'		=> (int)$enrol_info['user_id'],
				'course_id'		=> (int)$enrol_info['course_id'],
				'emi_type'		=> $enrol_info['emi_type'],
				'status'		=> 1,
				'payment_type'	=> 'offline',
				'amount'		=> (double)$payment_info['amount'] ?? 0,
			]);

			self::enrol([
				'enrol_id'		=> (int)$enrol_info['id'],
				'amount'		=> (double)$payment_info['amount'] ?? 0,
				'payment_type'	=> 'offline',
				'order_id'		=> (int)$order_id
			]);

			$this->load->model('Alert_model', 'alert_model');
			$this->alert_model->enrolled($enrol_id);
		}
	}

	public function pendingPaymentsByClass($data = []) {
		return self::pendingPayments($data);
	}

	public function pendingPayments($data = []) {
		if (!empty($data['class_id'])) {
			$this->db->select('classes_to_students.student_id, enrol.id AS enrol_id');
		} else {
			$this->db->select('enrol.*');
		}

		if (!empty($data['class_id'])) {
			$this->db->join('classes_to_students', 'classes_to_students.student_id = enrol.user_id');
			$this->db->where('classes_to_students.class_id', (int)$data['class_id']);
		}

		if (!empty($data['course_id'])) {
			$this->db->where('enrol.course_id', (int)$data['course_id']);
		}

		if (!empty($data['mode'])) {
			$this->db->where('enrol.mode', $data['mode']);
		}

		$this->db->where('enrol.archived', 0);

		$this->db->where('enrol.renewal_date <= ' . ('\'' . date('Y-m-d', strtotime('+7 days')) . '\''), NULL, FALSE);

		return $this->db->get('enrol')->result_array();
	}

	public function expired($data = []) {
		if (!empty($data['class_id'])) {
			$this->db->select('classes_to_students.student_id, enrol.id AS enrol_id');
		} else {
			$this->db->select('enrol.*');
		}

		if (!empty($data['class_id'])) {
			$this->db->join('classes_to_students', 'classes_to_students.student_id = enrol.user_id');
			$this->db->where('classes_to_students.class_id', (int)$data['class_id']);
		}

		if (!empty($data['course_id'])) {
			$this->db->where('enrol.course_id', (int)$data['course_id']);
		}

		if (!empty($data['mode'])) {
			$this->db->where('enrol.mode', $data['mode']);
		}

		$this->db->where('enrol.archived', 0);

		$this->db->where('enrol.renewal_date > ' . ('\'' . date('Y-m-d', strtotime('+1 days')) . '\''), NULL, FALSE);

		return $this->db->get('enrol')->result_array();
	}

	public function getRenewalAmount($enrol_id = 0) {
		$amount = 0;

		$this->db->where([
			'enrol_id'		=> (int)$enrol_id,
		]);

		$this->db->order_by('date_added', 'DESC');

		$last_payment = $this->db->get('payment')->row_array();

		$amount = $last_payment['amount'] ?? 0;

		if (!$amount) {
			$enrol_info = self::get($enrol_id);

			$this->load->model('common/Course_model');

			$enrol_info && ($course_info = $this->Course_model->get($enrol_info['course_id'])->row_array());

			if (!empty($course_info)) {
				$emi = json_decode($course_info['emi'], 1);
				$amount = $emi[$enrol_info['emi_type']] ?? 0;
			}
		}

		return $amount;
	}

	public function getByCode($code = '') {
		$this->db->select('
			enrol.*,
			course.title AS course,
			course.emi AS emi,
			payment_link.amount AS amount,
			payment_link.emi_type,
			payment_link.locked
		');

		$this->db->where('payment_link.code', $code);

		$this->db->join('course', 'course.id = enrol.course_id', 'left');
		$this->db->join('payment_link', 'payment_link.enrol_id = enrol.id');
		// $this->db->join('site', 'site.id = lead.site_id', 'left');

		$this->db->order_by('payment_link.date_added', 'DESC');
		$this->db->limit(1);

		$row = $this->db->get('enrol')->row_array();

		//$row['amount'] = $this->getRenewalAmount($row['id']);

		return $row;
	}

	public function generatePaymentLink($enrol_id = 0, $amount = 0, $emi_type = NULL, $locked = 0) {
		$enrol_info = self::get($enrol_id);

		$payment_info = $this->db->get_where('payment_link', [
			'enrol_id'			=> (int)$enrol_id,
			'status'			=> 0,
		])->row_array();

		!$amount && ($amount = $this->getRenewalAmount($enrol_id));

		if ($payment_info) {
			$code = $payment_info['code'];

			$this->db->update('payment_link', [
				'amount'			=> (double)$amount,
				'locked'			=> (int)$locked,
				'emi_type'			=> $emi_type ? $emi_type : $enrol_info['emi_type'],
			], [
				'id'				=> $payment_info['id']
			]);
		} else {
			$code = sha1(uniqid());

			$this->db->insert('payment_link', [
				'enrol_id'			=> (int)$enrol_id,
				'code'				=> $code,
				'amount'			=> (double)$amount,
				'locked'			=> (int)$locked,
				'emi_type'			=> $emi_type ? $emi_type : $enrol_info['emi_type'],
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
				'status'			=> 0,
			]);
		}

		return $code;
	}

	public function getCenter($enrol_id = 0) {
		$this->db->select('classes_to_students.*, centers.id AS center_id, centers.name AS center');

		$this->db->join('classes', 'classes.id = classes_to_students.class_id');
		$this->db->join('centers', 'centers.id = classes.center_id');

		return $this->db->get_where('classes_to_students', [
			'classes_to_students.enrol_id'		=> (int)$enrol_id
		])->row_array();
	}

	public function getMultipleCenter($enrol_id = 0) {
		$this->db->select('classes_to_students.*, centers.id AS center_id, centers.name AS center');

		$this->db->join('classes', 'classes.id = classes_to_students.class_id');
		$this->db->join('centers', 'centers.id = classes.center_id');

		$centers = [];

		foreach ($this->db->get_where('classes_to_students', [
			'classes_to_students.enrol_id'		=> (int)$enrol_id
		])->result_array() as $center) {
			$centers[] = $center['center'];
		}

		return implode(',', array_unique($centers));
	}

	public function getClassByEnrolId($enrol_id) {
		return $this->db->get_where('classes_to_students', [
			'enrol_id'	=> (int)$enrol_id
		])->result_array();
	}

	public function archive($enrol_id) {
		if ($row = $this->db->get_where('enrol', ['id' => (int)$enrol_id, 'archived' => 1])->row_array()) {
			$this->db->update('enrol', [
				'archived'		=> 0,
				'date_archived'	=> date('Y-m-d H:i:s')
			], [
				'id'		=> (int)$enrol_id
			]);
		} else {
			$this->db->update('enrol', [
				'archived'		=> 1,
				'date_archived'	=> date('Y-m-d H:i:s')
			], [
				'id'		=> (int)$enrol_id
			]);
		}
	}

	public function enrolStudentOffline($data) {
		// user_id, course_id, mode, emi_type, amount, city_id, center_id, class_id, payment_mode
		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('user/Student_model', 'student_model');

		if ($student_info = $this->student_model->get($data['user_id'])->row_array()) {
			$this->site_model->initConfig($student_info['site_id']);
		}

		if ($data['payment_mode'] == 'offline') {
			$this->load->model('order/Order_model', 'order_model');

			$order_id = $this->order_model->add([
				'user_id'		=> (int)$data['user_id'],
				'course_id'		=> (int)$data['course_id'],
				'emi_type'		=> $data['emi_type'],
				'status'		=> 1,
				'payment_type'	=> 'offline_' . (int)$data['center_id'],
				'amount'		=> (double)$data['amount'] ?? 0,
			]);

			$enrol_id = self::enrol([
				'user_id'		=> (int)$data['user_id'],
				'course_id'		=> (int)$data['course_id'],
				'mode'			=> $data['mode'],
				'payment_type'	=> 'offline_' . (int)$data['center_id'],
				'emi_type'		=> $data['emi_type'],
				'amount'		=> (double)$data['amount'] ?? 0,
				'order_id'		=> (int)$order_id
			]);

			$this->order_model->edit($order_id, [
				'enrol_id' 	=> (int)$enrol_id,
				'status' 	=> 1
			]);

			$this->db->insert('classes_to_students', [
				'class_id'		=> (int)$data['class_id'],
				'student_id'	=> (int)$data['user_id'],
				'enrol_id'		=> (int)$enrol_id,
			]);

			$this->alert_model->enrolled($enrol_id);
		} elseif ($data['payment_mode'] == 'online') {
			$enrol_id = self::enrol([
				'user_id'		=> (int)$data['user_id'],
				'course_id'		=> (int)$data['course_id'],
				'mode'			=> $data['mode'],
				'payment_type'	=> 'online_' . (int)$data['center_id'],
				'emi_type'		=> $data['emi_type'],
				'amount'		=> (double)$data['amount'] ?? 0,
				'order_id'		=> 0,
				'status'		=> 0,
			]);

			$this->db->insert('classes_to_students', [
				'class_id'		=> (int)$data['class_id'],
				'student_id'	=> (int)$data['user_id'],
				'enrol_id'		=> (int)$enrol_id,
			]);

			$this->alert_model->renew($enrol_id, (double)$data['amount'] ?? 0);
		}
	}

	public function getPayments($data = []) {
		$this->db->select('payment.*, course.title AS course, CONCAT(users.first_name, " ", IF(ISNULL(users.last_name), "", users.last_name)) AS user, users.mobile AS mobile, users.email AS email');

		$this->db->join('users', 'users.id = payment.user_id');
		$this->db->join('course', 'course.id = payment.course_id');

		return $this->db->get('payment')->result_array();
	}

	public function updateLastPayment($data = []) {
		if ($enrol_info = self::get((int)$data['enrol_id'])) {
			$this->db->order_by('date_added', 'DESC');

			if ($order_info = $this->db->get_where('order', ['enrol_id' => (int)$data['enrol_id']])->row_array()) {
				$this->db->update('order', [
					'emi_type'			=> $data['emi_type'],
					'amount'			=> (double)$data['amount'],
					'date_modified'		=> date('Y-m-d H:i:s')
				], [
					'id'				=> (int)$order_info['id']
				]);
			}

			$this->db->order_by('date_added', 'DESC');

			if ($order_info = $this->db->get_where('payment', ['enrol_id' => (int)$data['enrol_id']])->row_array()) {
				$this->db->update('payment', [
					'emi_type'		=> $data['emi_type'],
					'amount'		=> (double)$data['amount'],
					'last_modified' => strtotime(date('Y-m-d H:i:s'))
				], [
					'id'			=> (int)$order_info['id']
				]);
			}
		}
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

	public function getEnrolCountBySiteId($site_id) {
		return $this->db->get_where('enrol', [
			'site_id'	=> (int)$site_id
		])->num_rows();
	}
}
