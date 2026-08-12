<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class Common_lib {
	public function __construct() {
		$this->CI 		= &get_instance();
		$this->db 		= $this->CI->db;
		$this->session 	= $this->CI->session;
		$this->load 	= $this->CI->load;
		$this->config 	= $this->CI->config;

		$this->load->model('book/Book_model');
		$this->load->model('user/Lead_model');
		$this->load->model('user/Student_model');
		$this->load->model('event/Event_model');
		$this->load->model('event/EventUser_model');
		$this->load->model('event/EventSite_model');
		$this->load->model('event/EventBook_model');
		$this->load->model('ranking/Ranking_model');
		$this->load->model('school/SchoolLead_model');
		$this->load->model('common/Grade_model');
		$this->load->model('common/Site_model');
		$this->load->model('common/Section_model');

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$this->book_model 		= $this->CI->Book_model;
		$this->lead_model 		= $this->CI->Lead_model;
		$this->ranking_model 	= $this->CI->Ranking_model;
		$this->event_model 		= $this->CI->Event_model;
		$this->event_user_model = $this->CI->EventUser_model;
		$this->event_site_model = $this->CI->EventSite_model;
		$this->event_book_model = $this->CI->EventBook_model;
		$this->school_lead_model= $this->CI->SchoolLead_model;
		$this->grade_model 		= $this->CI->Grade_model;
		$this->student_model 	= $this->CI->Student_model;
		$this->site_model 		= $this->CI->Site_model;
		$this->section_model 	= $this->CI->Section_model;

		$this->cache 			= $this->CI->cache;
	}

	public function getGradeWiseData($user_id = false, $event_id = false, $role_id = 9) {
		if (
			empty($user_id) ||
			empty($event_id) ||
			empty($user_info = $this->db->get_where('users', [
				'id'		=> (int)$user_id,
				'role_id'	=> (int)$role_id,
				'status'	=> 1,
			])->row_array())
		) return;

		$students = [];

		$cache_key = sprintf('grade_wise_report_%s_%s', $user_id, $event_id);

		$results = json_decode($this->cache->get($cache_key), true);

		if (!empty($results)) return $results;

		$total_registered = $this->event_user_model->get_all([
			'site_id'	=> (int)$user_info['site_id'],
			'event_id'	=> (int)$event_id,
		])['total'];

		$total_published = $this->event_book_model->get_all([
			'site_id'	=> (int)$user_info['site_id'],
			'event_id'	=> (int)$event_id,
		]);

		$total_in_writing = $this->db->select('count(1) as total')
			->where('event_user.event_id', (int)$event_id)
			->where('users.site_id', (int)$user_info['site_id'])
			->where('book.status', 0)
			->where('book.archived', 0)
			->where('book._deleted', 0)
			->where('book.date_added between event.start_date and event.book_writing_end_date')
			->from('book')
			->join('event_user', 'event_user.user_id = book.user_id')
			->join('event', 'event.id = event_user.event_id')
			->join('users', 'users.id = book.user_id')
			->get()
			->row()->total ?? 0;

		$total_published_ids = !empty($total_published['rows']) ? array_values(array_unique(array_column($total_published['rows'], 'user_id'))) : [];

		$total_grade = 12;

		$event_info = $this->event_model->get($event_id);

		if ($event_info['country_code'] == 'GB') {
			$total_grade = 13;
		}

		for ($i = 1; $i <= $total_grade; $i++) {
			$grade_id = $i;

			$grade_total_registered_students = $this->student_model->get_all([
				'site_id'				=> (int)$user_info['site_id'],
				'grade'					=> (int)$grade_id,
				'event_id'				=> (int)$event_id,
				'student_verified'		=> 1
			]);

			$grade_total_registered_students_ids = array_values(array_unique(array_column($grade_total_registered_students['rows'], 'id')));

			$grade_total_published_author_ids = [];

			if (!empty($grade_total_registered_students_ids)) {
				$grade_total_published_author = $this->book_model->get_all([
					'user_ids'			=> $grade_total_registered_students_ids,
					'status'			=> 1
				]);

				if (!empty($grade_total_published_author)) {
					foreach ($grade_total_published_author['rows'] ?? [] as $published_author_info) {
						$event_book_info = $this->event_book_model->get_all([
							'event_id' => $event_id,
							'book_id' => $published_author_info['id']
						])['rows'][0] ?? [];

						if (!empty($event_book_info) && !in_array($published_author_info['user_id'], $grade_total_published_author_ids)) {
							array_push($grade_total_published_author_ids, $published_author_info['user_id']);
						}
					}
				}
			}

			$total_sold_copies = $this->ranking_model->getTotalSolds([
				'site_id'	=> (int)$user_info['site_id'],
				'grade_id'	=> (int)$grade_id,
				'event_id'	=> (int)$event_id
			]);

			if (EVENT_GRADES_NAME && !empty(EVENT_GRADES_NAME[$event_id])) {
				$grade_name = !empty(EVENT_GRADES_NAME[$event_id][$i]) ? EVENT_GRADES_NAME[$event_id][$i] : $i;
			} else {
				$grade_name = $i ?? 'NA';
			}

			$grade_total_in_writing = $this->db->select('count(1) as total')
				->where('event_user.event_id', (int)$event_id)
				->where('users.site_id', (int)$user_info['site_id'])
				->where('users.grade', $i)
				->where('book.status', 0)
				->where('book.archived', 0)
				->where('book._deleted', 0)
				->where('book.date_added between event.start_date and event.book_writing_end_date')
				->from('book')
				->join('event_user', 'event_user.user_id = book.user_id')
				->join('event', 'event.id = event_user.event_id')
				->join('users', 'users.id = book.user_id')
				->get()
				->row()->total ?? 0;

			$students[] = [
				'grade'						=> $grade_name,
				'total_registered_author'	=> $grade_total_registered_students['total'],
				'total_published'			=> count($grade_total_published_author_ids),
				'total_published_author'	=> count($grade_total_published_author_ids),
				'total_in_writing'			=> $grade_total_in_writing,
				'total_sold_copies'			=> $total_sold_copies ?? 0,
			];
		}

		$data = [
			'user_id'					=> $user_id,
			'school_name'				=> trim($user_info['first_name'].' '.$user_info['last_name']),
			'total_registered'			=> $total_registered,
			'total_published'			=> count($total_published_ids),
			'total_in_writing'			=> $total_in_writing ?? 0,
			'rank_in_city'				=> 'Coming Soon',
			'rank_in_state'				=> 'Coming Soon',
			'rank_in_country'			=> 'Coming Soon',
			'students'					=> $students,
			'event'						=> $event_info,
			'download_report'			=> base_url('api/downloadReport/'),
			'email_report'				=> base_url('api/sendEmailReport/'),
		];

		$this->cache->save($cache_key, json_encode($data), ENVIRONMENT === 'production' ? 3600 : 120);

		return $data;
	}

	public function getSchoolDashboardReport($site_id = '', $event_id = '') {
		$data['site_info'] = $this->site_model->get($site_id);

		$students = $this->db
			->select('users.*')
			->from('users')
			->join('event_user', 'event_user.user_id = users.id', 'left')
			->join('event_site', 'event_site.site_id = users.site_id', 'left')
			->where('event_user.event_id', (int)$event_id)
			->where('event_site.site_id', (int)$site_id)
			->where('event_site.event_id', (int)$event_id)
			->get()->result_array();

		$data['total_registered'] = count($students);

		$data['students'] = [];

		$grade_sort_order = [];
		$section_sort_order = [];

		$filename = '/StudentPDF';

		foreach ($students as $item) {
			// $grade_info = $this->grade_model->get($item['grade_id']);
			// $section_info = $this->section_model->get($item['section_id']);

			$book_written = $this->book_model->get_all([
				'user_id'	   	=> $item['id'],
				'grade'	  		=> $item['grade'],
				'section'		=> $item['section'],
				'event_id'		=> $event_id,
			])['total'];

			$book_published = $this->book_model->get_all([
				'user_id'	   	=> $item['id'],
				'grade'	  		=> $item['grade'],
				'section'		=> $item['section'],
				'event_id'		=> $event_id,
				'ne_status'	 	=> 0,
			])['total'];

			$data['students'][] = [
				'name'			=> trim($item['first_name'] . ' ' . $item['last_name']),
				'mobile'		=> $item['mobile'],
				'grade'			=> $item['grade'],
				'section'		=> $item['section'],
				'book_written'	=> $book_written,
				'book_published'=> $book_published,
			];

			$grade_sort_order[] = $item['grade'];
			$section_sort_order[] = $item['section'];
		}

		array_multisort($grade_sort_order, $section_sort_order, $data['students']);

		// $html = $this->load->view('frontend/' . get_frontend_settings('theme') . $filename, $data, true);
		// return $html;

		return $data;
	}
}
