<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class SchoolDashboard_lib {
	public function __construct() {
		$this->CI 		= &get_instance();
		$this->db 		= $this->CI->db;
		$this->session 	= $this->CI->session;
		$this->load 	= $this->CI->load;
		$this->config 	= $this->CI->config;

		$this->load->model('book/Book_model');
		$this->load->model('book/Bookstore_model');
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
		$this->load->model('user/teacher/Teacher_model');

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$this->book_model 		= $this->CI->Book_model;
		$this->bookstore_model 	= $this->CI->Bookstore_model;
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
		$this->teacher_model 	= $this->CI->Teacher_model;

		$this->cache 			= $this->CI->cache;
		$this->cache_time 		= ENVIRONMENT === 'production' ? 3600 : 60;
	}

	public function getGradeWiseData($user_id = false, $event_id = false, $role_id = 9) {
		if (
			empty($user_id) ||
			empty($user_info = $this->db->get_where('users', [
				'id'		=> (int)$user_id,
				'role_id'	=> (int)$role_id,
				'status'	=> 1,
			])->row_array())
		) return;

		$students 	= [];
		$cache_key 	= sprintf('grade_wise_report_%s_%s', $user_id, $event_id);
		$results 	= json_decode($this->cache->get($cache_key), true);

		if (!empty($results)) return $results;

		$event_info 	= $this->event_model->get($event_id);

		$grades 	= [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
		$sections 	= str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

		if ($event_info && $event_info['country_code'] == 'GB') {
			$grades[] = 13;
		}

		if ($event_info && in_array($event_info['country_code'], ['US', 'AE', 'GB', 'HK'])) {
			$sections = ['A'];
		}

		if ($role_id == 3) {
			$grades 	= [$user_info['grade']];
			$sections 	= [$user_info['section']];
		}

		foreach ($grades as $grade) {
			foreach ($sections as $section) {
				$data = self::_getReportSectionWise($user_info, $event_id, $grade, $section);

				if (!empty($data)) {
					$students[] = $data;
				}
			}
		}

		$total_registered = !empty($event_id) ? $this->event_user_model->get_all([
			'site_id'		=> (int)$user_info['site_id'],
			'event_id'		=> $event_id,
			])['total'] : 0;
			
		$total_published = !empty($event_id) ? $this->event_book_model->get_all([
			'site_id'		=> (int)$user_info['site_id'],
			'event_id'		=> $event_id,
			'active_status'	=> 1,
		]) : 0;

		$total_authors = !empty($total_published['rows'] ?? [])
			? count(array_unique(array_filter(array_column($total_published['rows'], 'user_id'))))
			: 0;

		$total_published = !empty($total_published['rows']) ? count($total_published['rows']) : 0;

		$total_in_writing = !empty($event_id) ? ($this->db->select('count(1) as total')
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
			->row()->total ?? 0) : 0;

		if (empty($total_registered) && !empty($students)) {
			$total_registered = array_sum(array_values(array_column($students, 'total_registered_author')));
		}

		if (empty($total_published) && !empty($students)) {
			$total_published = array_sum(array_values(array_column($students, 'total_published_author')));
		}

		if (empty($total_in_writing) && !empty($students)) {
			$total_in_writing = array_sum(array_values(array_column($students, 'total_in_writing')));
		}

		$data = [
			'user_id'					=> $user_id,
			'school_name'				=> trim($user_info['first_name'] . ' ' . $user_info['last_name']),
			'total_registered'			=> $total_registered,
			'total_authors'				=> $total_authors,
			'total_published'			=> $total_published,
			'total_in_writing'			=> $total_in_writing ?? 0,
			'rank_in_city'				=> 'Coming Soon',
			'rank_in_state'				=> 'Coming Soon',
			'rank_in_country'			=> 'Coming Soon',
			'students'					=> $students,
			'event'						=> $event_info,
			'download_report'			=> base_url('api/downloadSchoolReport/'),
			'email_report'				=> base_url('api/sendEmailReport/'),
			'nmt'						=> date('Y-m-d H:i:s', strtotime('+1 hour')),
		];

		$this->cache->save($cache_key, json_encode($data), $this->cache_time);

		return $data;
	}

	private function _getReportSectionWise($user_info = [], $event_id = 0, $grade = 1, $section = 'A') {
		$total_registered_authors = $this->student_model->get_all([
			'event_id'				=> !empty($event_id) ? $event_id : null,
			'site_id'				=> (int)$user_info['site_id'],
			'grade'					=> (int)$grade,
			'section'				=> $section,
			'student_verified'		=> 1
		])['total'] ?? 0;

		$total_sold_copies = $this->ranking_model->getTotalSolds([
			'site_id'	=> (int)$user_info['site_id'],
			'grade'		=> (int)$grade,
			'section'	=> $section,
			'event_id'	=> !empty($event_id) ? $event_id : null
		]);

		$total_published_books = $this->bookstore_model->get_all([
			'site_id'		=> $user_info['site_id'],
			'event_id'		=> !empty($event_id) ? $event_id : null,
			'grade'			=> $grade,
			'section'		=> $section,
			'status'		=> 1
		])['total'] ?? 0;

		$this->db->select('count(1) as total')
			->where('users.site_id', (int)$user_info['site_id'])
			->where('users.grade', (int)$grade)
			->where('users.section', $section)
			->where('book.status', 0)
			->where('book.archived', 0)
			->where('book._deleted', 0)
			->where('book.date_added between event.start_date and event.book_writing_end_date')
			->from('book')
			->join('event_user', 'event_user.user_id = book.user_id', 'left')
			->join('event', 'event.id = event_user.event_id', 'left')
			->join('users', 'users.id = book.user_id');

		if ($event_id) {
			$this->db->where('event_user.event_id', (int)$event_id);
		}

		$total_in_writing = $this->db->get()->row()->total ?? 0;

		$teacher_info = $this->teacher_model->get_all([
			'site_id'	=> $user_info['site_id'],
			'grade'		=> $grade,
			'section'	=> $section,
		])['rows'][0] ?? [];

		if (EVENT_GRADES_NAME && !empty(EVENT_GRADES_NAME[$event_id])) {
			$grade_name = !empty(EVENT_GRADES_NAME[$event_id][$grade]) ? EVENT_GRADES_NAME[$event_id][$grade] : $grade;
		} else {
			$grade_name = $grade ?? 'NA';
		}

		return [
			'teacher'					=> !empty($teacher_info['first_name']) ? ($teacher_info['first_name'] . ' ' . $teacher_info['last_name']) : '',
			'teacher_email'				=> $teacher_info['email'] ?? '',
			'grade'						=> $grade_name,
			'section'					=> $section,
			'total_registered_author'	=> $total_registered_authors,
			'total_published'			=> $total_published_books,
			'total_published_author'	=> $total_published_books,
			'total_in_writing'			=> $total_in_writing,
			'total_sold_copies'			=> $total_sold_copies ?? 0,
		];
	}

	public function getStudentWiseData($user_id = 0, $event_id = 0, $role_id = 3) {
		if (
			empty($user_id) ||
			empty($user_info = $this->db->get_where('users', [
				'id'		=> (int)$user_id,
				'role_id'	=> (int)$role_id,
				'status'	=> 1,
			])->row_array())
		) return;

		$students 	= [];
		$cache_key 	= sprintf('student_wise_report_%s_%s', $user_id, $event_id);
		$results 	= json_decode($this->cache->get($cache_key), true);

		if (!empty($results)) return $results;

		if ($event_id) {
			$this->db->where('event_user.event_id', (int)$event_id);
		}

		$students = $this->db
			->select('distinct users.id', false)
			->from('users')
			->join('event_user', 'event_user.user_id = users.id', 'left')
			->where('users.grade', (int)$user_info['grade'])
			->where('users.section', $user_info['section'])
			->where('users.site_id', (int)$user_info['site_id'])
			->get()->result_array();

		$data['students'] 	= [];

		$total_published = !empty($event_id) ? $this->event_book_model->get_all([
			'site_id'		=> (int)$user_info['site_id'],
			'event_id'		=> $event_id,
			'grade'			=> $user_info['grade'],
			'section'		=> $user_info['section'],
			'active_status'	=> 1,
		]) : 0;

		$total_authors = !empty($total_published['rows'] ?? [])
			? count(array_unique(array_filter(array_column($total_published['rows'], 'user_id'))))
			: 0;

		$data['total_authors'] 	= $total_authors;

		$grade_sort_order 	= [];
		$section_sort_order = [];

		foreach ($students as $item) {
			$author_info = $this->student_model->get($item['id']);

			if (empty($author_info)) continue;

			if ($event_id) {
				$this->db->where('event_user.event_id', (int)$event_id);
			}

			$book_written = $this->db->select('count(1) as total')
				->where('book.user_id', (int)$item['id'])
				->where('book.status', 0)
				->where('book.archived', 0)
				->where('book._deleted', 0)
				->where('book.date_added between event.start_date and event.book_writing_end_date')
				->from('book')
				->join('event_user', 'event_user.user_id = book.user_id', 'left')
				->join('event', 'event.id = event_user.event_id', 'left')
				->get()->row()->total ?? 0;

			$book_published = $this->bookstore_model->get_all([
				'user_id'	   	=> $item['id'],
				'event_id'		=> !empty($event_id) ? $event_id : null,
				'status'	 	=> 1,
			])['total'];

			$data['students'][] = [
				'id'			=> $item['id'],
				'name'			=> trim($author_info['first_name'] . ' ' . $author_info['last_name']),
				'mobile'		=> $author_info['mobile'],
				'grade'			=> $author_info['grade'],
				'section'		=> $author_info['section'],
				'book_written'	=> $book_written,
				'book_published'=> $book_published,
			];

			$grade_sort_order[] 	= $author_info['grade'];
			$section_sort_order[] 	= $author_info['section'];
		}

		array_multisort($grade_sort_order, $section_sort_order, $data['students']);

		$data['nmt'] 			= date('Y-m-d H:i:s', strtotime(sprintf('+%d seconds', $this->cache_time)));

		$this->cache->save($cache_key, json_encode($data), $this->cache_time);

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
			->where('users.site_id', (int)$site_id)
			->where('event_site.site_id', (int)$site_id)
			->where('event_site.event_id', (int)$event_id)
			->get()->result_array();

		$data['total_registered'] = count($students);

		$data['students'] 	= [];

		$grade_sort_order 	= [];
		$section_sort_order = [];

		foreach ($students as $item) {
			$book_written = $this->db->select('count(1) as total')
				->where('book.user_id', (int)$item['id'])
				->where('book.status', 0)
				->where('book.archived', 0)
				->where('book._deleted', 0)
				->where('book.date_added between event.start_date and event.book_writing_end_date')
				->where('event_user.event_id', (int)$event_id)
				->from('book')
				->join('event_user', 'event_user.user_id = book.user_id', 'left')
				->join('event', 'event.id = event_user.event_id', 'left')
				->get()->row()->total ?? 0;

			$book_published = $this->book_model->get_all([
				'user_id'	   	=> $item['id'],
				'event_id'		=> $event_id,
				'status'	 	=> 1,
			])['total'];

			$data['students'][] = [
				'name'			=> trim($item['first_name'] . ' ' . $item['last_name']),
				'mobile'		=> $item['mobile'],
				'grade'			=> $item['grade'],
				'section'		=> $item['section'],
				'book_written'	=> $book_written,
				'book_published'=> $book_published,
			];

			$grade_sort_order[] 	= $item['grade'];
			$section_sort_order[] 	= $item['section'];
		}

		array_multisort($grade_sort_order, $section_sort_order, $data['students']);

		return $data;
	}
}
