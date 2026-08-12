<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Export extends CI_Controller {
	private $types = [];

	public function __construct() {
		parent::__construct();

		if ($this->session->userdata('admin_login') == false) {
			redirect(site_url('login'), 'refresh');
		}

		$this->types[] = [
			'code'	=> 'student',
			'value'	=> _l('student')
		];

		$this->types[] = [
			'code'	=> 'teacher',
			'value'	=> _l('teacher')
		];

		$this->types[] = [
			'code'	=> 'class',
			'value'	=> _l('class')
		];

		$this->types[] = [
			'code'	=> 'pending_payments',
			'value'	=> _l('pending_payments')
		];

		$this->types[] = [
			'code'	=> 'fee_collection',
			'value'	=> _l('fee_collection')
		];

		$this->types[] = [
			'code'	=> 'lead',
			'value'	=> _l('lead')
		];

		$this->types[] = [
			'code'	=> 'school',
			'value'	=> _l('school')
		];

		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('user/teacher/Teacher_model', 'teacher_model');
		$this->load->model('common/Class_model', 'class_model');
		$this->load->model('common/Enrol_model', 'enrol_model');
		$this->load->model('user/Lead_model', 'lead_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');
	}

	public function index() {
		$data['types'] = $this->types;

		$data['type'] = '';

		$data['page_name'] = 'export';
		$data['page_title'] = _l('export');

		$data['action'] = site_url('export/download');

		$this->load->view('backend/index', $data);
	}

	public function download() {
		if ($this->input->method() == 'post' && $this->input->post('type') && in_array($this->input->post('type'), array_column($this->types, 'code'))) {
			$filename = 'exported_' . $this->input->post('type') . '_' . date('Y_m_d_H_i_s') . '.csv';

			switch($this->input->post('type')) {
				case 'student':
					$students = $this->student_model->get_all()->result_array();

					$results = [];

					foreach ($students as $student) {
						$classes = array_map(function($value) {
							return $value['class_id'];
						}, $this->student_model->getClassByStudentId($student['id']));

						$enrols = $this->enrol_model->getAll(['user_id' => $student['id']]);

						foreach ($enrols as $enrol) {
							$payment_info = $this->enrol_model->getPaymentByEnrolId($enrol['id']);

							$results[] = [
								'class_id'				=> implode(',', $classes),
								'student_name'			=> $student['first_name'] . ' ' . $student['last_name'],
								'parent_name'			=> $student['parent_name'],
								'gender'				=> $student['gender'],
								'mobile'				=> $student['mobile'],
								'course'				=> $enrol['course'],
								'doj'					=> $enrol['doj'],
								'renewal_date'			=> $enrol['renewal_date'],
								'emi_type'				=> $enrol['emi_type'],
								'amount'				=> $this->enrol_model->getRenewalAmount($enrol['id']),
								'last_payment_date'		=> isset($payment_info['date_added']) ? date('Y-m-d H:i:s', $payment_info['date_added']) : '',
								'payment_type'			=> $payment_info['payment_type'] ?? '',
								'grade'					=> $student['grade'],
							];
						}
					}

					break;
				case 'teacher':
					$results = $this->teacher_model->get_all();
				case 'class':
					$results = $this->class_model->get_all()->result_array();
					break;
				case 'lead':
					$results = $this->lead_model->get_all()['rows'] ?? [];
					break;
				case 'pending_payments':
					$results = [];

					foreach ($this->enrol_model->pendingPayments() as $enrol) {
						$enrol_info = $this->enrol_model->get($enrol['id']);

						$results[] = [
							'enrol_id'		=> $enrol_info['id'],
							'course'		=> $enrol_info['course'],
							'emi_type'		=> $enrol_info['emi_type'],
							'mode'			=> $enrol_info['mode'],
							'renewal_amount'=> $this->enrol_model->getRenewalAmount($enrol_info['id']),
							'student'		=> $enrol_info['user'],
							'student_mobile'=> $enrol_info['mobile'],
							'student_email'	=> $enrol_info['email'],
							'date_renewed'	=> $enrol_info['date_renewed'],
							'doj'			=> $enrol_info['doj'],
							'status'		=> $enrol_info['status'],
							'archived'		=> $enrol_info['archived'],
						];
					}
					break;
				case 'fee_collection':
					$results = [];

					foreach ($this->enrol_model->getPayments() as $payment) {
						$enrol_info = $this->enrol_model->get($payment['enrol_id']);

						$results[] = [
							'enrol_id'		=> $enrol_info['id'],
							'course'		=> $enrol_info['course'],
							'emi_type'		=> $payment['emi_type'],
							'payment_type'	=> $payment['payment_type'],
							'mode'			=> $enrol_info['mode'],
							'renewal_amount'=> $payment['amount'],
							'student'		=> $enrol_info['user'],
							'student_mobile'=> $enrol_info['mobile'],
							'student_email'	=> $enrol_info['email'],
							'date_renewed'	=> $enrol_info['date_renewed'],
							'date_added'	=> date('Y-m-d H:i:s', strtotime($payment['date_added'])),
							'doj'			=> $enrol_info['doj'],
							'status'		=> $enrol_info['status'],
							'archived'		=> $enrol_info['archived'],
						];
					}

					break;
				case 'school':
						$results = [];

						$this->db->select('*');
						$this->db->where('parent_id', 2273);
						$this->db->where('date_added >=', date('Y-m-d'));
						// $this->db->like('date_added', date('Y-m-d'));
						$site_info =  $this->db->get('site')->result_array();

						// pr($site_info,1);

						foreach ($site_info as $site) {
							$state_info = $this->state_model->get($site['state_id']);
							$city_info = $this->city_model->get($site['city_id']);

							$results[] = [
								'site_id'					=> $site['id'],
								'school_name'				=> $site['name'],
								'email'						=> $site['owner_email'],
								'state'						=> !empty($state_info) ? $state_info['name'] : "NA",
								'city'						=> !empty($city_info) ? $city_info['name'] : "NA",
								'school_signup_url'			=> 'https://www.yaf.bribooks.com/india/school/'.$site['id'],
								'communication_kit_url'		=> 'https://www.yaf.bribooks.com/communicationindia/'.$site['id'],
								'date_added'				=> $site['date_added']
							];
						}

						break;
				default:
					exit(_l('error_unknown'));
			}

			if (!headers_sent()) {
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' .  $filename . '"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');

				if (ob_get_level()) {
					ob_end_clean();
				}
			} else {
				exit('Error: Headers already sent out!');
			}

			$headers = isset($results[0]) ? array_keys($results[0]) : [];

			if (!$headers) {
				exit(_l('error_empty'));
			}

			$fp = fopen('php://output', 'w');

			$this->writeRowToCsv($results, $fp, $headers);

			fclose($fp);

			exit();
		}
	}

	private function writeRowToCsv($results = [], $fp = null, $headers = []) {
		fputs($fp, "\xEF\xBB\xBF");

		fputcsv($fp, $headers);

		if (is_array($results) && $results && is_resource($fp) && is_array($headers) && $headers) {
			foreach ($results as $result) {
				$row = [];

				foreach ($headers as $header) {
					if (!empty($result[$header]) && is_array($result[$header])) {
						//$this->writeRowToCsv($result[$header], $fp, array_keys($result[$header]));
					} else {
						$row[] = !empty($result[$header]) ? $result[$header] : '';
					}
				}

				fputcsv($fp, $row);
			}
		}
	}
}
