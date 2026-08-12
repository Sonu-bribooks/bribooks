<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Schedule_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->load->model('common/Class_model', 'class_model');
		$this->load->model('user/teacher/Teacher_model', 'teacher_model');
	}

	public function get($id = 0) {
		$this->db->select('schedules.*, course.title AS course, course.description AS course_description, classes.center_id, classes.course_id, classes.name AS class, classes.site_id, classes.color AS color, CONCAT(users.first_name, " ", users.last_name) AS name, users.mobile, users.email');

		$this->db->where('schedules.id', (int)$id);

		$this->db->join('users', 'users.id = schedules.user_id', 'left');
		$this->db->join('classes', 'classes.id = schedules.class_id');
		$this->db->join('course', 'course.id = classes.course_id');

		return $this->db->get('schedules')->row_array();
	}

	public function get_all($data = []) {
		if (!empty($data['student_id'])) {
			if (!empty($data['is_demo'])) {
				$this->db->select('schedule_id');
				$this->db->where('student_id', (int)$data['student_id']);
				$this->db->from('demo_lead_schedule');
			} else {
				$this->db->select('class_id');
				$this->db->where('student_id', (int)$data['student_id']);
				$this->db->from('classes_to_students');
			}

			$where_clause = $this->db->get_compiled_select();
		}

		// if (isset($data['site_id'])) {
		// 	$this->db->select('class_id');
		// 	$this->db->where('site_id', (int)$data['site_id']);
		// 	$this->db->from('classes_to_site');
		//
		// 	$site_where_clause = $this->db->get_compiled_select();
		// }

		if (!empty($data['teacher_id'])) {
			$this->db->select('schedule_id');
			$this->db->where('teacher_id', (int)$data['teacher_id']);
			$this->db->from('teacher_schedules');

			$reassign_where_clause = $this->db->get_compiled_select();
		}

		$this->db->select('schedules.*, course.title AS course, course.description AS course_description, classes.center_id, classes.course_id, classes.name AS class, classes.slot_id AS slot_id, classes.color AS color, CONCAT(users.first_name, " ", users.last_name) AS name, users.mobile, users.email');

		if (isset($data['site_id'])) {
			// $this->db->where("`classes`.`id` IN ($site_where_clause)", NULL, FALSE);
			$this->db->where('classes.site_id', (int)$data['site_id']);
		}

		if (!empty($data['schedule_id'])) {
			$this->db->where('schedules.id', (int)$data['schedule_id']);
		}

		if (!empty($data['class_id'])) {
			$this->db->where('schedules.class_id', (int)$data['class_id']);
		}

		if (!empty($data['user_id'])) {
			$this->db->where('schedules.user_id', (int)$data['user_id']);
		}

		if (!empty($data['course_id'])) {
			$this->db->where('classes.course_id', (int)$data['course_id']);
		}

		if (!empty($data['center_id'])) {
			$this->db->where('classes.center_id', (int)$data['center_id']);
		}

		if (isset($data['is_demo'])) {
			$this->db->where('classes.is_demo', (int)$data['is_demo']);
		}

		if (!empty($data['mode'])) {
			$this->db->where('classes.mode', $data['mode']);
		}

		if (isset($data['status'])) {
			$this->db->where('classes.status', (int)$data['status']);
		}

		if (!empty($data['teacher_id'])) {
			// $this->db->where('classes.teacher_id', (int)$data['teacher_id']);
			$this->db->where("(`classes`.`teacher_id` = " . (int)$data['teacher_id'] . " OR `schedules`.`id` IN ($reassign_where_clause))", NULL, FALSE);
		}

		if (!empty($data['student_id'])) {
			if (!empty($data['is_demo'])) {
				$this->db->where("`schedules`.`id` IN ($where_clause)", NULL, FALSE);
			} else {
				$this->db->where("`classes`.`id` IN ($where_clause)", NULL, FALSE);
			}
		}

		if (!empty($data['date_start'])) {
			$this->db->where('schedules.schedule >= ', date('Y-m-d', strtotime($data['date_start'])));
		}

		if (!empty($data['date_end'])) {
			$this->db->where('schedules.schedule < ', date('Y-m-d', strtotime($data['date_end'])));
		}

		if (!empty($data['datetime_start'])) {
			$this->db->where('schedules.schedule = ', date('Y-m-d H:i:s', strtotime($data['datetime_start'])));
		}

		if (!empty($data['range_start']) && !empty($data['range_end'])) {
			$this->db->where('schedules.schedule >= ', date('Y-m-d H:00:00', strtotime($data['range_start'])));
			$this->db->where('schedules.schedule < ', date('Y-m-d H:i:s', strtotime($data['range_end'])));
		}

		if (isset($data['exported'])) {
			$this->db->where('schedules.exported', (int)$data['exported']);
		}

		$this->db->join('users', 'users.id = schedules.user_id', 'left');
		$this->db->join('classes', 'classes.id = schedules.class_id', 'left');
		$this->db->join('course', 'course.id = classes.course_id', 'left');

		if (!empty($data['order']) && !empty($data['sort']) && in_array($data['order'], ['ASC', 'DESC'])) {
			$this->db->order_by($data['sort'], $data['order']);
		} else {
			$this->db->order_by('schedules.date_added', 'DESC');
		}

		if (!empty($data['limit']) && empty($data['offset'])) {
			$this->db->limit((int)$data['limit']);
		}

		if (!empty($data['limit']) && !empty($data['offset']) && $data['offset'] > 0) {
			$this->db->limit((int)$data['offset'], (int)$data['limit']);
		}

		return $this->db->get('schedules');
	}

	public function add($data = []) {
		$validity = $this->check_duplication('on_create');

		$data = $data ? $data : $this->input->post();

		$id = null;

		if ($validity == false) {
			$this->session->set_flashdata('error_message', get_phrase('schedule_duplication'));
		} else {
			if (!empty($data['class_id'])) {
				if ($class_info = $this->class_model->get($data['class_id'])->row_array()) {
					$this->db->insert('schedules', [
						'class_id'		=> (int)$data['class_id'],
						'user_id'		=> (int)$class_info['teacher_id'],
						'is_demo'		=> (int)$class_info['is_demo'],
						'mode'			=> $class_info['mode'],
						'lead_id'		=> $data['lead_id'] ?? 0,
						'schedule'		=> date('Y-m-d H:i:s', strtotime($data['schedule'] . $class_info['slot'])),
						'date_added'	=> date('Y-m-d H:i:s'),
						'date_modified'	=> date('Y-m-d H:i:s'),
					]);

					$id = $this->db->insert_id();

					$this->session->set_flashdata('flash_message', get_phrase('class_schedule_added_successfully'));

					return $id;
				} else {
					$this->session->set_flashdata('error_message', get_phrase('class_not_found'));
				}
			} else {
				$this->load->model('common/Class_model', 'class_model');

				$month 		= $data['month'];
				$last_month = date('Y-m-d', strtotime("+$month months", strtotime($data['schedule'])));
				$timestamp 	= strtotime($data['schedule']);

				foreach ($data['class_ids'] as $key => $class_id) {
					$this->db->where('schedule >= ', date('Y-m-d', strtotime($data['schedule'])));
					$this->db->where('schedule < ', date('Y-m-d', strtotime("+$month months", strtotime($data['schedule']))));

					$this->db->delete('schedules', [
						'class_id' => (int)$class_id,
					]);

					// Reduce Order complexity
					foreach ($data['days'] as $key => $value) {
						$value 	= (int)$value;
						$day 	= date('w', $timestamp);

						if ($day >= $value) {
							$val = (6 - $day) + ($value + 1);
						} else {
							$val = $value - $day;
						}

						$start_date = date('Y-m-d', strtotime("+$val days", strtotime($data['schedule'])));

						while($start_date <= $last_month) {
							if ($class_info = $this->class_model->get($class_id)->row_array()) {
								$this->db->insert('schedules', [
									'class_id'		=> (int)$class_id,
									'user_id'		=> (int)$class_info['teacher_id'],
									'is_demo'		=> (int)$class_info['is_demo'],
									'mode'			=> $class_info['mode'],
									'lead_id'		=> $data['lead_id'] ?? 0,
									'schedule'		=> date('Y-m-d H:i:s', strtotime($start_date . $class_info['slot'])),
									'date_added'	=> date('Y-m-d H:i:s'),
									'date_modified'	=> date('Y-m-d H:i:s'),
								]);

								$id = $this->db->insert_id();

								$this->session->set_flashdata('flash_message', get_phrase('class_schedule_added_successfully'));
							} else {
								$this->session->set_flashdata('error_message', get_phrase('class_not_found'));
							}

							$start_date = date('Y-m-d', strtotime("+7 days", strtotime($start_date)));
						}
					}
				}
			}

			return $id;
		}
	}

	public function edit($id = 0, $data = []) {
		$validity = $this->check_duplication('on_update', $id);

		$data = $data ? $data : $this->input->post();

		if ($validity) {
			$this->load->model('common/Class_model', 'class_model');

			if ($class_info = $this->class_model->get($data['class_id'])->row_array()) {
				$this->db->update('schedules', [
					'class_id'		=> (int)$data['class_id'],
					'is_demo'		=> (int)$class_info['is_demo'],
					'mode'			=> $class_info['mode'],
					'user_id'		=> (int)$class_info['teacher_id'],
					'schedule'		=> date('Y-m-d H:i:s', strtotime($data['schedule'] . $class_info['slot'])),
					'date_modified'	=> date('Y-m-d H:i:s'),
				], [
					'id'			=> (int)$id
				]);

				$this->session->set_flashdata('flash_message', get_phrase('update_successfully'));
			} else {
				$this->session->set_flashdata('error_message', get_phrase('class_not_found'));
			}
		} else {
			$this->session->set_flashdata('error_message', get_phrase('schedule_duplication'));
		}
	}

	public function check_duplication($action = null, $id = 0) {
		if ($class_info = $this->class_model->get($this->input->post('class_id'))->row_array()) {
			$this->db->where("schedule BETWEEN '" . date('Y-m-d H:i:s', strtotime($this->input->post('schedule') . $class_info['slot'])) . "' AND '" . date('Y-m-d H:i:s', strtotime('+40 minutes', strtotime($this->input->post('schedule') . $class_info['slot']))) . "'");

			$duplicate_check = $this->db->get_where('schedules', [
				'class_id'			=> (int)$this->input->post('class_id'),
			]);

			if ($action == 'on_create') {
				if ($duplicate_check->num_rows() > 0) {
					return false;
				} else {
					return true;
				}
			} elseif ($action == 'on_update') {
				if ($duplicate_check->num_rows() > 0) {
					if ($duplicate_check->row()->id == $id) {
						return true;
					} else {
						return false;
					}
				} else {
					return true;
				}
			}
		}
	}

	public function delete($id = 0) {
		$this->db->delete('schedules', ['id' => (int)$id]);

		$this->session->set_flashdata('flash_message', get_phrase('deleted_successfully'));
	}

	public function get_all_students($class_id) {
		$students 	= [];

		$results 	= $this->db->get('schedules_to_students', ['class_id' => (int)$class_id])->result_array();

		foreach ($results as $result) {
			$students[] = $result['student_id'];
		}

		return $students;
	}

	public function reassign($data = []) {
		$data = $data ? $data : $this->input->post();

		$id = null;

		if ($schedule_info = $this->get($data['schedule_id'])) {
			$this->db->insert('request_reassign', [
				'schedule_id'	=> (int)$data['schedule_id'],
				'user_id'		=> (int)$this->session->userdata('user_id'),
				'comment'		=> $data['comment'],
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$id = $this->db->insert_id();

			$this->session->set_flashdata('flash_message', get_phrase('reassign_request_added_successfully'));

			return $id;
		} else {
			$this->session->set_flashdata('error_message', get_phrase('schedule_not_found'));
		}
	}

	public function get_all_reassign($data = []) {
		$this->db->select('request_reassign.*, schedules.class_id AS class_id, schedules.schedule AS schedule, CONCAT(users.first_name, " ", users.last_name) AS name');

		if (!empty($data['schedule_id'])) {
			$this->db->where('request_reassign.id', (int)$data['schedule_id']);
		}

		if (!empty($data['user_id'])) {
			$this->db->where('request_reassign.user_id', (int)$data['user_id']);
		}

		$this->db->join('users', 'users.id = request_reassign.user_id', 'left');
		$this->db->join('schedules', 'schedules.id = request_reassign.schedule_id');

		return $this->db->get('request_reassign')->result_array();
	}

	public function get_reassign($reassign_id = 0) {
		$this->db->select('request_reassign.*, schedules.class_id AS class_id, schedules.schedule AS schedule, CONCAT(users.first_name, " ", users.last_name) AS name');

		$this->db->where('request_reassign.id', (int)$reassign_id);

		$this->db->join('users', 'users.id = request_reassign.user_id', 'left');
		$this->db->join('schedules', 'schedules.id = request_reassign.schedule_id');

		return $this->db->get('request_reassign')->row_array();
	}

	public function getReassignedSchedules($data = []) {
		if (!empty($data['teacher_id'])) {
			$this->db->where('teacher_id', (int)$data['teacher_id']);
		}

		if (!empty($data['schedule_id'])) {
			$this->db->where('schedule_id', (int)$data['schedule_id']);
		}

		if (!empty($data['original_teacher_id'])) {
			$this->db->where('original_teacher_id', (int)$data['original_teacher_id']);
		}

		if (!empty($data['single_row'])) {
			return $this->db->get('teacher_schedules')->row_array();
		} else {
			return $this->db->get('teacher_schedules')->result_array();
		}
	}

	public function reassignTeacher($data = []) {
		$data = $data ? $data : $this->input->post();

		$schedule_info = $this->db->get_where('teacher_schedules', [
			'original_teacher_id'	=> (int)$data['original_teacher_id'],
			'schedule_id'			=> (int)$data['schedule_id']
		])->row_array();

		$id = null;

		if ($schedule_info) {
			$id = $schedule_info['id'];

			$this->db->update('teacher_schedules', [
				'user_id'		=> (int)$this->session->userdata('user_id'),
				'teacher_id'	=> (int)$data['teacher_id'],
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'id'			=> (int)$id
			]);

			$this->db->update('schedule_link', [
				'modified'		=> 1,
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'schedule_id'	=> (int)$data['schedule_id']
			]);
		} else {
			$this->db->insert('teacher_schedules', [
				'teacher_id'			=> (int)$data['teacher_id'],
				'original_teacher_id'	=> (int)$data['original_teacher_id'],
				'schedule_id'			=> (int)$data['schedule_id'],
				'user_id'				=> (int)$this->session->userdata('user_id'),
				'date_added'			=> date('Y-m-d H:i:s'),
				'date_modified'			=> date('Y-m-d H:i:s'),
			]);

			$id = $this->db->insert_id();

			$this->db->update('schedule_link', [
				'modified'		=> 1,
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'schedule_id'	=> (int)$data['schedule_id']
			]);
		}

		$this->session->set_flashdata('flash_message', get_phrase('reassigned_successfully'));

		return $id;
	}

	public function addReschedule($data) {
		$data = $data ? $data : $this->input->post();

		if ($schedule_info = self::get($data['schedule_id'])) {
			$reschedule_info = $this->db->get_where('reschedule', [
				'schedule_id'	=> (int)$data['schedule_id'],
				'student_id'	=> (int)$this->session->userdata('user_id'),
			])->row_array();

			if ($reschedule_info) {
				$this->db->update('reschedule', [
					'reason'		=> $data['reason'],
					'schedule'		=> date('Y-m-d H:i:s', strtotime($data['schedule'])),
					'schedule_id'	=> (int)$data['schedule_id'],
					'date_modified'	=> date('Y-m-d H:i:s'),
				], [
					'id'			=> (int)$reschedule_info['id']
				]);
			} else {
				$this->db->insert('reschedule', [
					'reason'		=> $data['reason'],
					'schedule'		=> date('Y-m-d H:i:s', strtotime($data['schedule'])),
					'schedule_id'	=> (int)$data['schedule_id'],
					'student_id'	=> (int)$this->session->userdata('user_id'),
					'date_added'	=> date('Y-m-d H:i:s'),
					'date_modified'	=> date('Y-m-d H:i:s'),
				]);

				$id = $this->db->insert_id();
			}

			$this->session->set_flashdata('flash_message', get_phrase('reschedule_added_successfully'));

			return $id;
		} else {
			$this->session->set_flashdata('error_message', get_phrase('schedule_not_found'));
		}
	}

	public function editReschedule($reschedule_id, $data = []) {
		$data = $data ? $data : $this->input->post();

		$id = null;

		if ($schedule_info = $this->get($data['schedule_id'])) {
			$this->db->update('reschedule', $data + [
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'id'			=> (int)$reschedule_id
			]);

			$this->session->set_flashdata('flash_message', get_phrase('reschedule_updated_successfully'));

			return $id;
		} else {
			$this->session->set_flashdata('error_message', get_phrase('schedule_not_found'));
		}
	}

	private function getZoomId($users, $email) {
		$user_id = '';

		foreach ($users as $user) {
			if (strtolower($user['email']) === strtolower($email)) {
				$user_id = $user['id'];
			}
		}

		return $user_id;
	}

	public function addScheduleLink($data = []) {
		$link = null;

		if (empty($data['schedule_id'])) return;

		$this->load->model('common/Site_model', 'site_model');

		if ($schedule_info = self::get($data['schedule_id'])) {
			$this->load->library('Zoom_lib', 'zoom_lib');

			if ($result = $this->db->get_where('schedule_link', [
				'schedule_id'	=> (int)$data['schedule_id'],
			])->row_array()) {
				$link = $result['link'];

				if ($result['modified']) {
					// 1. Get users from zoom
					// $users = json_decode($this->zoom_lib->listUsers(), 1);

					$extra 			= json_decode($result['extra'], true);
					$post_data 		= $extra['request'];
					$meeting_info 	= !empty($extra['respose']) ? $extra['respose'] : $extra['response'];

					$alternative_host_ids = [];

					if ($reschedule = self::getReassignedSchedules(['schedule_id' => $schedule_info['id'], 'single_row' => true])) {
						if ($teacher_info = $this->teacher_model->get($reschedule['teacher_id'])) {
							// $post_data['userId'] = self::getZoomId($users['users'] ?? [], $teacher_info['email']);
							$post_data['userId'] = $teacher_info['email'];
						}
					}

					// $post_data['meeting_id'] 			= $meeting_info['id'];
					$post_data['alternative_host_ids'] 	= $alternative_host_ids;

					// $response = json_decode($this->zoom_lib->updateMeetingInfo($post_data), 1);
					$response = json_decode($this->zoom_lib->createAMeeting($post_data), 1);

					$this->db->update('schedule_link', [
						'modified'		=> 0,
						'link'			=> !empty($response['join_url']) ? $response['join_url'] : $link,
						'extra'			=> json_encode(['request' => $post_data, 'response' => $response]),
						'date_modified'	=> date('Y-m-d H:i:s'),
					], [
						'id'			=> (int)$result['id']
					]);

					$link = $response['join_url'] ?? '';
				}
			} else {
				// 1. Get users from zoom
				// $users = json_decode($this->zoom_lib->listUsers(), 1);

				// 2. Generate Static data
				// $user_id			= self::getZoomId($users['users'] ?? [], $schedule_info['email']);
				$password 			= mt_rand(1000000000, 9999999999);
				$start_date			= date('Y-m-d H:i:s', strtotime($schedule_info['schedule']));
				$end_date			= date('Y-m-d H:i:s', strtotime('+2 hours', strtotime($schedule_info['schedule'])));
				$meeting_topic		= $schedule_info['course'] . '-' . $schedule_info['id'];
				$agenda				= $schedule_info['course'] . '-' . $schedule_info['id'];

				$alternative_host_ids = [];

				/*foreach (self::getReassignedSchedules(['schedule_id' => $schedule_info['id']]) as $value) {
					$teacher_info 	= $this->teacher_model->get($value['teacher_id'])->row_array();

					if ($user_id = self::getZoomId($users['users'] ?? [], $teacher_info['email'])) {
						$alternative_host_ids[] = $user_id;
					}
				}*/

				// 3. Add Meeting for enrolment
				$site_info = $this->site_model->get($schedule_info['site_id']);

				$post_data = [
					'userId'                    => $schedule_info['email'],
					'topic'              		=> $meeting_topic,
					'agenda'                    => $agenda,
					'start_date'                => $start_date, // '2020-02-13 12:16'
					'timezone'                  => '', // $site_info['timezone'] ?? 'Asia/Calcutta',
					'password'                  => $password,
					'duration'                  => 40,
					'waiting_room'      		=> 1,
					'join_before_host'          => 1,
					'option_host_video'         => 1,
					'option_participants_video' => 1,
					'option_mute_participants'  => 1,
					'option_enforce_login'      => 1,
					'option_auto_recording'     => 'none',
					'alternative_host_ids'      => $alternative_host_ids,
					// 'schedule_for'				=> $schedule_info['email'],
					'recurrence'				=> [
						//'type'						=> 1,
						//'repeat_interval'			=> 1,
						//'weekly_days'				=> '1,7',
						//'monthly_day'				=> 1,
						//'monthly_week'				=> 1,
						//'monthly_week_day'			=> 1,
						//'end_times'					=> 1,
						//'end_date_time'				=> gmdate('Y-m-d\TH:i:s', strtotime($end_date)),
					],
				];

				// Reschedule handling
				if ($reschedule = self::getReassignedSchedules(['schedule_id' => $schedule_info['id'], 'single_row' => true])) {
					if ($teacher_info = $this->teacher_model->get($reschedule['teacher_id'])) {
						// $post_data['userId'] = self::getZoomId($users['users'] ?? [], $teacher_info['email']);
						$post_data['userId'] 		= $teacher_info['email'];
						$schedule_info['user_id'] 	= $reschedule['teacher_id'];
					}
				}

				$response = json_decode($this->zoom_lib->createAMeeting($post_data), 1);

				// log_message('KB', 'zoom response =>' . print_r($response, 1));
				// log_message('KB', 'zoom response =>' . print_r($schedule_info, 1));

				!empty($response['join_url']) && $this->db->insert('schedule_link', [
					'schedule_id'	=> (int)$schedule_info['id'],
					'class_id'		=> (int)$schedule_info['class_id'],
					'teacher_id'	=> (int)$schedule_info['user_id'],
					'link'			=> $response['join_url'] ?? '',
					'extra'			=> json_encode(['request' => $post_data, 'response' => $response]),
					'password'		=> $password,
					'date_added'	=> date('Y-m-d H:i:s'),
					'date_modified'	=> date('Y-m-d H:i:s'),
				]);

				$link = $response['join_url'] ?? '';
			}
		}

		return $link;
	}

	public function getScheduleLink($data = []) {
		if (!empty($data['schedule_id']) && ($schedule_info = self::get($data['schedule_id']))) {
			return self::addScheduleLink(['schedule_id' => $data['schedule_id']]);
		}
	}
}
