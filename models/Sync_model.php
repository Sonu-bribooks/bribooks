<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sync_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('user/teacher/Teacher_model', 'teacher_model');
		$this->load->model('common/Slot_model', 'slot_model');
		$this->load->model('common/Schedule_model', 'schedule_model');
		$this->load->model('common/Class_model', 'class_model');
		$this->load->model('common/Course_model', 'course_model');
		$this->load->model('common/Enrol_model', 'enrol_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('game/GSchool_model', 'game_school_model'); 

		$this->load->library('Api_lib', 'api_lib');

		$this->gdb = $this->db;

		$this->debug = false;
	}

	private function generateSign($data = [], $secret = NULL) {
		array_multisort(array_keys($data), $data);

		$http_query = '';

		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$value = '[' . implode(',', $value) . ']';
			}

			$http_query .= "{$key}={$value}&";
		}

		$http_query = rtrim($http_query, '&');

		return md5($http_query . $secret);
		//return md5(str_replace('+', ' ', http_build_query($data)) . $secret);
	}

	private function prepare(&$data) {
		$data['orgCode'] 	= API_ORGCODE;
		$data['schoolId'] 	= API_SCHOOL_ID;
		$data['sign'] 		= self::generateSign($data, API_SECRET);

		array_multisort(array_keys($data), $data);
	}

	private function callApi($endpoint, $data = []) {
		self::prepare($data);

		$result = $this->api_lib->setHeader([
			'Content-Type'	=> 'application/json',
		])->insert($endpoint, $data)->rows();

		if ($this->debug) {
			echo $endpoint;
			print_r($data);
			print_r($result);
		}

		return $result;
	}

	// Step 0. Add courses
	public function addSchool($id = 0) {
		if ($data = $this->site_model->get($id)) {
			$country_info = $this->country_model->getByCode($data['country_code']);

			if ($school_info = $this->game_school_model->getByExtId($data['id'])) {
				$this->game_school_model->edit($school_info['id'], [
					'name'			=> $data['name'],
					'school_code'	=> $data['site_code'],
					'nation_id'		=> str_replace('+', '', $country_info['tel_code']),
					'ext_id'		=> $data['id'],
				]);
			} else {
				$this->game_school_model->add([
					'name'			=> $data['name'],
					'school_code'	=> $data['site_code'],
					'nation_id'		=> str_replace('+', '', $country_info['tel_code']),
					'ext_id'		=> $data['id'],
				]);
			}
		}
	}

	public function deleteStudent($id = 0) {
		if ($data = $this->student_model->get($id)->row_array()) {
			$this->gdb->delete('adt_user', [
				'email' => $data['email']
			]);
		}
	}

	// Step 1. Add courses
	public function courses() {
		foreach ($this->course_model->get_all(['exported' => 0, 'limit' => 5])->result_array() as $result) {
			if (!($type_id = self::getTypeId($result['title']))) continue;

			$data = [
				'courseCourseType'		=> 1, // 1 for formal lessons, 2 for experience lessons
				'courseStatus'			=> 1, // 1 for enabled, 2 for disabled
				'courseTitle'			=> $result['title'],
				'courseTypeId'			=> (int)$type_id, // 1 for JavaScript,2 for Python,4 for Scratch,5 for C++,6 for App Inventor
				'materialId'			=> self::getMaterialId($result['title']),
			];

			$response = self::callApi('course/create', $data);

			if ($response['code'] == 200 && !empty($response['data'])) {
				self::markExported('course', $result['id'], ($response['data']['id'] ?? 1));
			}
		}
	}

	// Step 2. Add classes
	public function classes() {
		foreach ($this->class_model->get_all(['exported' => 0, 'limit' => 10])->result_array() as $result) {
			self::convertCourseId($result['course_id']);

			if (!$result['course_id']) continue;

			$data = [
				'className'		=> substr(trim(preg_replace(['/[^\w\s]/', '/\s+/'], [' ', ' '], $result['name'])), 0, 50),
				'classType'		=> $result['mode'] == 'online' ? 1 : 50, // 1 for 1V 1,2for 1v2,4for 1v4,6 for 1v6,50 for 1v N
				'classHour'		=> $result['mode'] == 'online' ? 48 : 48,
				'courseId'		=> $result['course_id'],
			];

			$response = self::callApi('class/create', $data);

			if ($response['code'] == 200 && !empty($response['data'])) {
				self::markExported('classes', $result['id'], ($response['data']['classId'] ?? 1));
			}
		}
	}

	// Step 3. Add teachers
	public function teachers() {
		$genders = [
			'M'	=> 1,
			'F'	=> 0,
		];

		foreach ($this->teacher_model->get_all(['exported' => 0, 'limit' => 10])->result_array() as $result) {
			if (empty($result['mobile'])) continue;

			if (strlen($result['mobile']) == 10) {
				$result['mobile'] = '+91' . trim($result['mobile']);
			}

			$data = [
				'name'			=> $result['first_name'] . ' ' . $result['last_name'],
				'nickName'		=> $result['first_name'],
				'password'		=> md5(trim($result['email'])),
				'phone'			=> $result['mobile'],
				'sex'			=> $genders[$result['gender']],
				'status'		=> $result['status'],
				'uno'			=> API_UNO_PREFIX . date('dmY', $result['date_added']) . $result['id'],
			];

			$response = self::callApi('teacher/createAccount', $data);

			if (($response['code'] == 200) && !empty($response['data'])) {
				self::markExported('users', $result['id'], ($response['data']['id'] ?? 1));
			}
		}
	}

	// Step 4. Add students
	public function students() {
		$genders = [
			'M'	=> 1,
			'F'	=> 0,
		];

		foreach ($this->student_model->get_all(['exported' => 0, 'limit' => 10])->result_array() as $result) {
			$result['mobile'] = trim($result['mobile']);

			if (empty($result['mobile']) || !is_numeric($result['mobile'])) continue;

			if (strlen($result['mobile']) == 10) {
				$result['mobile'] = '+91' . $result['mobile'];
			}

			$name = $result['first_name'] . ' ' . $result['last_name'];

			$data = [
				'name'			=> preg_replace(['/[^\w\s]/', '/\s+/'], ['', ' '], trim($name)),
				'nickName'		=> trim($result['first_name']),
				'password'		=> md5($result['mobile']),
				'phone'			=> $result['mobile'],
				'sex'			=> $genders[$result['gender']],
				'status'		=> $result['status'],
				'uno'			=> API_UNO_PREFIX . date('dmY', $result['date_added']) . $result['id'],
			];

			$response = self::callApi('student/createAccount', $data);

			if (($response['code'] == 200) && !empty($response['data'])) {
				self::markExported('users', $result['id'], ($response['data']['id'] ?? 1));
			}
		}
	}

	// Step 5. Student enrol to course
	public function enrols() {
		foreach ($this->enrol_model->getAll([
			'exported' 	=> 0,
			'limit' 	=> 10,
			'status'	=> 1,
			'sort'		=> 'enrol.date_added',
			'order'		=> 'DESC'
		]) as $result) {
			self::convertCourseId($result['course_id']);
			self::convertStudentId($result['user_id']);

			if (!$result['course_id'] || !$result['user_id']) continue;

			$data = [
				'courseHour'		=> 48,
				'courseId'			=> (int)$result['course_id'],
				'stuId'				=> (int)$result['user_id'],
				'teachType'			=> $result['mode'] == 'online' ? 1 : 50, // 1 for 1V 1,2for 1v2,4for 1v4,6 for 1v6,50 for 1v N
			];

			$response = self::callApi('student/enrollCourse', $data);

			if ($response['code'] == 200 && !empty($response['data'])) {
				self::markExported('enrol', $result['id'], ($response['data']['id'] ?? 1));
			}
		}
	}

	// Step 6. Add student to class
	public function addStudentToClass() {
		foreach ($this->class_model->get_filtered_students(['exported' => 0, 'limit' => 10]) as $result) {
			$ext_class_id = $result['class_id'];
			$ext_enrol_id = $result['enrol_id'];

			self::convertClassId($ext_class_id);
			if (!$ext_class_id) continue;

			self::convertEnrolId($ext_enrol_id);
			if (!$ext_enrol_id) continue;

			$data = [
				'classId'		=> (int)$ext_class_id,
				'offialStuIds'	=> [(int)$ext_enrol_id],
			];

			$response = self::callApi('class/addStudent', $data);

			if ($response['code'] == 200 && !empty($response['data'])) {
				$this->db->update('classes_to_students', [
					'exported'			=> $response['data'][0]['attId'] ?? 1,
					'date_exported'		=> date('Y-m-d H:i:s')
				], [
					'class_id'			=> (int)$result['class_id'],
					'student_id'		=> (int)$result['student_id'],
					'enrol_id'			=> (int)$result['enrol_id'],
				]);
			}
		}
	}

	// Step 7. Add schedules
	public function schedules() {
		foreach ($this->schedule_model->get_all(['exported' => 0, 'limit' => 1000])->result_array() as $result) {
			self::convertClassId($result['class_id']);
			self::convertTeacherId($result['user_id']);

			if (!$result['class_id'] || !$result['user_id']) continue;

			$data = [
				'classId'		=> (int)$result['class_id'],
				'teacherId'		=> (int)$result['user_id'],
				'startTime'		=> strtotime($result['schedule']),
				'endTime'		=> strtotime('+60 minutes', strtotime($result['schedule'])),
			];

			$response = self::callApi('schedule/add', $data);

			if ($response['code'] == 200 && !empty($response['data'])) {
				self::markExported('schedules', $result['id'], ($response['data'][0]['id'] ?? 1));
			}
		}
	}

	// Optionals
	// Step 8. Shecules adjust
	public function scheduleAdjust($offset = 0, $limit = 1000) {
		foreach ($this->schedule_model->get_all(['limit' => (int)$limit, 'offset' => $offset])->result_array() as $result) {
			self::convertTeacherId($result['user_id']);

			if (!$result['exported'] || !$result['user_id']) continue;

			$data = [
				'id'			=> (int)$result['exported'],
				'teacherId'		=> (int)$result['user_id'],
				'startTime'		=> strtotime($result['schedule']),
				'endTime'		=> strtotime('+60 minutes', strtotime($result['schedule'])),
			];

			$response = self::callApi('schedule/adjust', $data);
		}
	}

	// Lms Link
	public function getLmsLink($user_info = []) {
		if ($user_info) {
			if (strlen($user_info['mobile']) == 10) {
				$user_info['mobile'] = '+91' . $user_info['mobile'];
			}

			/*log_message('KB', print_r(
				[
					'uno' 		=> API_UNO_PREFIX . date('dmY', $user_info['date_added']) . $user_info['id'],
					'password' 	=> ($user_info['role_id'] == 2 ? $user_info['mobile'] : $user_info['email']),
				], 1
			));*/

			$response 	= self::callApi('/LMS/getToken', [
				'uno' 		=> API_UNO_PREFIX . date('dmY', $user_info['date_added']) . $user_info['id'],
				'password' 	=> md5(($user_info['role_id'] == 2 ? $user_info['mobile'] : $user_info['email'])),
			]);

			/*log_message('KB', print_r([
				'uno' 		=> API_UNO_PREFIX . date('dmY', $user_info['date_added']) . $user_info['id'],
				'password' 	=> md5(($user_info['role_id'] == 2 ? $user_info['mobile'] : $user_info['email'])),
			], 1) . print_r($response, 1));*/

			if ($response['code'] == 200 && !empty($response['data'])) {
				return 'leaplearnerapp://open-url&token=' . $response['data'];
			}
		}
	}

	// Deprecated
	public function slots() {
		foreach ($this->slot_model->get_all(['exported' => 0, 'limit' => 5])->result_array() as $result) {
			$data = [
				'timeSart'		=> $result['slot_start'],
				'timeEnd'		=> $result['slot_end'],
			];

			$response = self::callApi('timeBetween/add', $data);

			if ($response['code'] == 200 && !empty($response['data'])) {
				self::markExported('slots', $result['id'], ($response['data']['id'] ?? 1));
			}
		}
	}

	// Convert to external id
	private function convertStudentId(&$id) {
		if ($row  = $this->student_model->get($id)->row()) {
			$id = $row->exported;
		} else {
			$id = 0;
		}
	}

	private function convertTeacherId(&$id) {
		if ($row  = $this->teacher_model->get($id)->row()) {
			$id = $row->exported;
		} else {
			$id = 0;
		}
	}

	private function convertCourseId(&$id) {
		if ($row  = $this->course_model->get($id)->row()) {
			$id = $row->exported;
		} else {
			$id = 0;
		}
	}

	private function convertClassId(&$id) {
		if ($row  = $this->class_model->get($id)->row()) {
			$id = $row->exported;
		} else {
			$id = 0;
		}
	}

	private function convertEnrolId(&$id) {
		if ($row  = $this->enrol_model->get($id)) {
			$id = $row['exported'];
		} else {
			$id = 0;
		}
	}

	private function getTypeId($course) {
		$courses_map = [
			'python'			=> 2,
			'junior coding' 	=> 4,
			'app development'	=> 6,
			//'robotics & ai'	=> 7,
		];

		return $courses_map[strtolower(trim($course))] ?? '';
	}

	private function getMaterialId($course) {
		$data = [
			'typeId'		=> self::getTypeId($course),
			'materialLang'	=> 2, // 1 for chinese,2 for english
			'courseType'	=> 1, // Textbook category-1: formal class, 2: trial class, 3 test textbook
			'status'		=> 1, // 1 for enabled, 2 for disabled
		];

		$response = self::callApi('material/list', $data);

		if ($response['code'] == 200) {
			return $response['data'][0]['id'] ?? 0;
		}
	}

	private function markExported($table, $id = 0, $exported = 1) {
		$this->db->update($table, [
			'exported'			=> (int)$exported,
			'date_exported'		=> date('Y-m-d H:i:s')
		], [
			'id'				=> (int)$id
		]);
	}

	private function unmarkExported() {
		return;
		$tables = [
			'course',
			'classes',
			'users',
			'enrol',
			'classes_to_students',
			'schedules',
		];

		foreach ($tables as $table) {
			$this->db->update($table, [
				'exported'			=> 0,
				'date_exported'		=> date('Y-m-d H:i:s')
			]);
		}
	}

	// Test Code
	public function materials() {
		$data = [
			'courseCourseType'	=> 1, // 1 for formal lessons, 2 for experience lessons
			'status'			=> 1, // 1 for enabled, 2 for disabled
			'materialLang'		=> 2, // 1 for chinese,2 for english
		];

		$response = self::callApi('material/list', $data);

		pr($response);
	}

	public function courseList() {
		$data = [
			'courseStatus'	=> 1, // 1 for enabled, 2 for disabled
			//'courseTitle'	=> 'python',
		];

		$response = self::callApi('course/list', $data);

		pr($response);
	}

	public function studentList($mobile = 0) {
		$data = [
			'status'	=> 1, // 1 for enabled, 2 for disabled
		];

		if ($mobile) {
			$data['phone']	= $mobile;
		}

		$response = self::callApi('student/list', $data);

		pr($response);
	}

	public function teacherList($mobile = 0) {
		$data = [
			'status'	=> 1, // 1 for enabled, 2 for disabled
		];

		if ($mobile) {
			$data['phone']	= $mobile;
		}

		$response = self::callApi('teacher/list', $data);

		pr($response);
	}

	public function classList() {
		$response = self::callApi('class/list', []);

		pr($response);
	}

	public function enrolList() {
		$response = self::callApi('student/listEnrolment', []);

		pr($response);
	}

	public function schedulelList() {
		$response = self::callApi('schedule/list', [

		]);

		$results = array_map(function($teacher) {
			$result = $this->teacher_model->get_all(['exported' => $teacher['teacherId']]);

			unset($result['password']);

			return [
				"classId" 		=> $teacher['classId'],
				"courseId" 		=> $teacher['courseId'],
				"schoolId" 		=> $teacher['schoolId'],
				"status" 		=> $teacher['status'],
				"teacherId" 	=> $teacher['teacherId'],
				"teacher_info" 	=> implode(',', $result),
			];
		}, $response['data'] ?? []);

		$filename = 'schedules-' . date('Y-m-d-H-i-s') . '.csv';

		$fp = fopen('php://output', 'w');

		$headers = isset($results[0]) ? array_keys($results[0]) : [];

		$fp = fopen('php://output', 'w');

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

		$this->writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();

		//pr($results);
	}

	public function lmsToken() {
		$response = self::callApi('/LMS/getToken', ['uno' => 'LLIN25112019203']);

		pr($response);
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
