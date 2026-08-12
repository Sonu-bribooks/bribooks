<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportOld {
	private function importStudentsOld($rows = [], $map = []) {
		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if (empty($data['student_name'])) continue;

			// 1. Add student
			$explode = explode(' ', trim($data['student_name']), 2);

			if ($student = $this->db->get_where('users', [
				'first_name'	=> $explode[0],
				'last_name'		=> $explode[1] ?? '',
				'mobile'		=> $data['mobile'],
				'grade'			=> $data['grade'],
			])->row_array()) {
				$student_id = $student['id'];

				$this->db->update('users', [
					'first_name'		=> array_shift($explode),
					'last_name'			=> array_shift($explode),
					'parent_name'		=> $data['parent_name'],
					'grade'				=> $data['grade'],
				], [
					'id'				=> (int)$student_id
				]);
			} else {
				$this->db->insert('users', [
					'first_name'		=> array_shift($explode),
					'last_name'			=> array_shift($explode),
					'password'			=> md5(uniqid()),
					'role_id'			=> 2,
					'parent_name'		=> $data['parent_name'],
					'grade'				=> $data['grade'],
					'mobile'			=> $data['mobile'],
					'status'			=> 1,
					'date_added'		=> strtotime(date('Y-m-d H:i:s')),
				]);

				$student_id = $this->db->insert_id();
			}

			$data['renewal_date'] = str_replace('/', '-', $data['renewal_date']);
			$data['doj'] = str_replace('/', '-', $data['doj']);

			if (preg_match('/^(?P<date>\d{1,2})-(?P<month>\d{1,2})-(?P<year>\d{1,2})$/', $data['renewal_date'], $matches)) {
				$data['renewal_date'] = $matches['year'] . '-' . $matches['month'] . '-' . $matches['date'];
			}

			if (preg_match('/^(?P<date>\d{1,2})-(?P<month>\d{1,2})-(?P<year>\d{1,2})$/', $data['doj'], $matches)) {
				$data['doj'] = $matches['year'] . '-' . $matches['month'] . '-' . $matches['date'];
			}

			// 2. Get course_id by course name
			if (!$data['emi_type']) continue;

			$courses = explode(',', $data['course']);

			foreach ($courses as $course) {
				$this->db->like('title', $course);

				if ($course_info = $this->db->get('course')->row_array()) {
					$course_id = $course_info['id'];

					$emis = json_decode($course_info['emi'], true);

					foreach ($emis as $key => $amount) {
						if (strpos($key, 'offline_' . strtolower($data['emi_type'])) !== false) {
							$data['emi_type'] = $key;
						}
					}

					$data['emi_type'] = strtolower($data['emi_type']) == 'monthly' ? 'other' : strtolower($data['emi_type']);
				} else {
					continue;
				}

				// 3. Enrol new or find existing using user_id course_id, mode
				if ($enrol_info = $this->db->get_where('enrol', [
					'user_id'		=> (int)$student_id,
					'course_id'		=> (int)$course_id,
					'mode'			=> 'offline',
				])->row_array()) {
					$enrol_id = $enrol_info['id'];

					$this->db->update('enrol', [
						'emi_type'		=> $data['emi_type'],
						'renewal_date'	=> date('Y-m-d H:i:s', strtotime($data['renewal_date'])),
						'doj'			=> date('Y-m-d H:i:s', strtotime($data['doj'])),
					], [
						'id'			=> (int)$enrol_id
					]);
				} else {
					$this->db->insert('enrol', [
						'user_id'		=> (int)$student_id,
						'course_id'		=> (int)$course_id,
						'mode'			=> 'offline',
						'emi_type'		=> $data['emi_type'],
						'status'		=> 1,
						'renewal_date'	=> date('Y-m-d H:i:s', strtotime($data['renewal_date'])),
						'doj'			=> date('Y-m-d H:i:s', strtotime($data['doj'])),
						'date_added'	=> strtotime(date('Y-m-d H:i:s')),
					]);

					$enrol_id = $this->db->insert_id();
				}

				// 4. Add order data or get by enrol_id, user_id, course_id
				if ($order_info = $this->db->get_where('order', [
					'user_id'		=> (int)$student_id,
					'course_id'		=> (int)$course_id,
					'enrol_id'		=> (int)$enrol_id,
				])->row_array()) {
					$order_id = $order_info['id'];

					$this->db->update('order', [
						'emi_type'		=> $data['emi_type'],
						'payment_type'	=> $data['payment_type'],
						'amount'		=> $data['amount'],
					], [
						'id'			=> (int)$order_id
					]);
				} else {
					$this->db->insert('order', [
						'enrol_id'		=> (int)$enrol_id,
						'user_id'		=> (int)$student_id,
						'course_id'		=> (int)$course_id,
						'emi_type'		=> $data['emi_type'],
						'payment_type'	=> $data['payment_type'],
						'amount'		=> $data['amount'],
						'status'		=> 1,
						'date_added'	=> date('Y-m-d H:i:s'),
						'date_modified'	=> date('Y-m-d H:i:s'),
					]);

					$order_id = $this->db->insert_id();
				}

				// 5. Add Payment
				if ($payment_info = $this->db->get_where('payment', [
					'user_id'		=> (int)$student_id,
					'course_id'		=> (int)$course_id,
					'enrol_id'		=> (int)$enrol_id,
					'order_id'		=> (int)$order_id,
				])->row_array()) {
					$payment_id = $payment_info['id'];

					$this->db->update('order', [
						'emi_type'		=> $data['emi_type'],
						'payment_type'	=> $data['payment_type'],
						'amount'		=> $data['amount'],
					], [
						'id'			=> (int)$payment_id
					]);
				} else {
					$this->db->insert('payment', [
						'enrol_id'		=> (int)$enrol_id,
						'user_id'		=> (int)$student_id,
						'course_id'		=> (int)$course_id,
						'order_id'		=> (int)$order_id,
						'emi_type'		=> $data['emi_type'],
						'payment_type'	=> $data['payment_type'],
						'amount'		=> $data['amount'],
						'date_added'	=> strtotime(date('Y-m-d H:i:s')),
					]);

					$payment_id = $this->db->insert_id();
				}

				// 6. Add Student to Class
				$this->db->delete('classes_to_students', [
					'student_id'		=> (int)$student_id
				]);

				$class_ids = explode(',', $data['class_id']);

				foreach ($class_ids as $class_id) {
					if (($class_info = $this->db->get_where('classes', [
						'id'	=> (int)$class_id
					])->row_array()) && ($enrol_info_i = $this->db->get_where('enrol', [
						'user_id'		=> (int)$student_id,
						'course_id'		=> (int)$class_info['course_id'],
						'mode'			=> 'offline',
					])->row_array())) {
						$this->db->insert('classes_to_students', [
							'class_id'			=> (int)$class_id,
							'student_id'		=> (int)$student_id,
							'enrol_id'			=> (int)$enrol_info_i['id']
						]);
					}
				}
			}
		}
	}

	private function importClasses($rows = [], $map = []) {
		$response['skip_city'] 				= [];
		$response['skip_center']			= [];
		$response['skip_slot'] 				= [];
		$response['skip_course'] 			= [];
		$response['skip_teacher'] 			= [];
		$response['skip_backup_teacher'] 	= [];
		$response['skip_row'] 				= [];

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			$data['mode'] = strtolower($data['mode']);

			if ($data['mode'] == 'offline') {
				if (empty($data['city'])) continue;

				// 0. Get City ID from city name
				$this->db->like('name', trim($data['city']));

				if ($city_info = $this->db->get('cities')->row_array()) {
					$city_id = $city_info['id'];
				} else {
					!in_array($data['city'], $response['skip_city']) && ($response['skip_city'][] = $data['city']);
					!in_array($index, $response['skip_row']) && ($response['skip_row'][] = $index);

					continue;
				}

				// 1. Get Center ID from center name
				$this->db->like('name', trim($data['center']));

				if ($center_info = $this->db->get_where('centers', ['city_id' => (int)$city_id])->row_array()) {
					$center_id = $center_info['id'];
				} else {
					$this->db->insert('centers', [
						'name'		=> trim($data['center']),
						'city_id'	=> (int)$city_id,
					]);

					$center_id = $this->db->insert_id();

					!in_array($data['center'], $response['skip_center']) && ($response['skip_center'][] = $data['center']);
					!in_array($index, $response['skip_row']) && ($response['skip_row'][] = $index);

					//continue;
				}
			} else {
				$center_id = 0;
			}

			// 2. Get Slot ID using slot
			if ($slot_info = $this->db->get_where('slots', [
				'slot_start' => date('H:i:s', strtotime(date('Y-m-d') . ' ' . trim($data['slot']))),
			])->row_array()) {
				$slot_id = $slot_info['id'];
			} else {
				!in_array($data['slot'], $response['skip_slot']) && ($response['skip_slot'][] = $data['slot']);
				!in_array($index, $response['skip_row']) && ($response['skip_row'][] = $index);

				continue;
			}

			// 3. Get Course ID from course name
			$this->db->like('title', trim($data['course']));

			if ($course_info = $this->db->get('course')->row_array()) {
				$course_id = $course_info['id'];
			} else {
				!in_array($data['course'], $response['skip_course']) && ($response['skip_course'][] = $data['course']);
				!in_array($index, $response['skip_row']) && ($response['skip_row'][] = $index);

				continue;
			}

			// 4. Get Teacher ID from teacher email
			$this->db->where('(role_id=3 OR additional_role_id=3)');

			if ($teacher_info = $this->db->get_where('users', [
				'email'		=> trim($data['teacher']),
			])->row_array()) {
				$teacher_id = $teacher_info['id'];
			} else {
				!in_array($data['teacher'], $response['skip_teacher']) && ($response['skip_teacher'][] = $data['teacher']);
				!in_array($index, $response['skip_row']) && ($response['skip_row'][] = $index);

				continue;
			}

			// 5. Get Backup Teacher ID from backup teacher email
			$backup_teachers = explode(',', trim($data['backup_teacher']));

			$backup_teacher_ids = array_map(function($backup_teacher_email) {
				$this->db->where('(role_id=3 OR additional_role_id=3)');

				if ($backup_teacher_info = $this->db->get_where('users', [
					'email'		=> trim($backup_teacher_email),
				])->row_array()) {
					return $backup_teacher_info['id'];
				} else {
					!in_array($backup_teacher_email, $response['skip_backup_teacher']) && ($response['skip_backup_teacher'][] = $backup_teacher_email);
				}
			}, $backup_teachers);

			// 6. Add class and get class id
			if ($class_info = $this->db->get_where('classes', [
				'center_id'		=> (int)$center_id,
				'slot_id'		=> (int)$slot_id,
				'course_id'		=> (int)$course_id,
				'teacher_id'	=> (int)$teacher_id,
				'mode'			=> $data['mode'] == 'online' ? 'online' : 'offline',
				'is_demo'		=> (int)$data['is_demo'],
			])->row_array()) {
				$class_id = $class_info['id'];

				//$backup_teacher_ids = array_unique(array_merge($backup_teacher_ids, json_decode($class_info['backup_teacher_id'], 1)));

				$this->db->update('classes', [
					'name'				=> vsprintf('%s by %s -%s-%s %s-%s', [$course_info['title'], $teacher_info['first_name'], $slot_info['slot_start'], ($center_info['name'] ?? ''), $data['mode'], ($data['is_demo'] ? 'demo' : '')]),
					'mode'				=> $data['mode'] == 'online' ? 'online' : 'offline',
					'slot_id'			=> (int)$slot_id,
					'teacher_id'		=> (int)$teacher_id,
					'backup_teacher_id'	=> json_encode($backup_teacher_ids),
					'course_id'			=> (int)$course_id,
					'center_id'			=> (int)$center_id,
					'is_demo'			=> (int)$data['is_demo'],
					'scheduled_days'	=> trim($data['schedule']),
					'color'				=> 'info',
					'date_modified'		=> date('Y-m-d H:i:s')
				], [
					'id'				=> (int)$class_id
				]);
			} else {
				$this->db->insert('classes', [
					'name'				=> vsprintf('%s by %s -%s-%s %s-%s', [$course_info['title'], $teacher_info['first_name'], $slot_info['slot_start'], ($center_info['name'] ?? ''), $data['mode'], ($data['is_demo'] ? 'demo' : '')]),
					'mode'				=> $data['mode'] == 'online' ? 'online' : 'offline',
					'slot_id'			=> (int)$slot_id,
					'teacher_id'		=> (int)$teacher_id,
					'backup_teacher_id'	=> json_encode($backup_teacher_ids),
					'course_id'			=> (int)$course_id,
					'center_id'			=> (int)$center_id,
					'is_demo'			=> (int)$data['is_demo'],
					'scheduled_days'	=> trim($data['schedule']),
					'color'				=> 'info',
					'date_added'		=> date('Y-m-d H:i:s'),
					'date_modified'		=> date('Y-m-d H:i:s')
				]);

				$class_id = $this->db->insert_id();
			}

			// 7. Add zoom Link for online class
			if ($data['mode'] == 'online') {
				$this->load->model('common/Schedule_model', 'schedule_model');

				$this->schedule_model->addScheduleLink([
					'class_id'		=> $class_id,
					'teacher_id'	=> $teacher_id,
				]);
			}

			// 8. Add schedule for this class id
			$this->load->model('common/Schedule_model', 'schedule_model');

			$data['start_date'] = str_replace('/', '-', $data['start_date']);
			$data['end_date'] 	= str_replace('/', '-', $data['end_date']);

			if (preg_match('/^(?P<date>\d{1,2})-(?P<month>\d{1,2})-(?P<year>\d{1,2})$/', $data['start_date'], $matches)) {
				$data['start_date'] = $matches['year'] . '-' . $matches['month'] . '-' . $matches['date'];
			}

			if (preg_match('/^(?P<date>\d{1,2})-(?P<month>\d{1,2})-(?P<year>\d{1,2})$/', $data['end_date'], $matches)) {
				$data['end_date'] = $matches['year'] . '-' . $matches['month'] . '-' . $matches['date'];
			}

			$start_date = new DateTime(date('Y-m-d', strtotime($data['start_date'])));
			$end_date 	= new DateTime(date('Y-m-d', strtotime($data['end_date'])));
			$interval 	= $start_date->diff($end_date);

			$this->db->delete('schedules', ['class_id' => (int)$class_id]);

			$this->schedule_model->add([
				'class_ids'		=> [$class_id],
				'schedule'		=> date('Y-m-d', strtotime($data['start_date'])),
				'days'			=> explode(',', $data['schedule']),
				'month'			=> round($interval->days / 30),
			]);
		}

		return $response;
	}
}
