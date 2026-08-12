<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Dashboard {
	private $_cache_ttl = 600;

	public function ajax_dashboard_report() {
		$json = [];

		$this->load->model('admin/Dashboard_model', 'dashboard_model');
		$this->load->library('Online_lib');

		$json['registrations'] 			= $json['users'] = $this->dashboard_model->getTotalUsers();
		$json['new_registrations'] 		= $this->dashboard_model->getTotalNewUsers();
		$json['online_users'] 			= $this->online_lib->total();
		$json['books'] 					= $this->dashboard_model->getTotalBooks();
		$json['published_books'] 		= $this->dashboard_model->getTotalPublishedBooks();
		$json['new_published_books'] 	= $this->dashboard_model->getTotalNewPublishedBooks();
		$json['subscribers'] 			= $this->dashboard_model->getTotalSubscribers();
		$json['new_subscribers'] 		= $this->dashboard_model->getTotalNewSubscribers();
		$json['orders'] 				= $this->dashboard_model->getTotalOrders();
		$json['new_orders'] 			= $this->dashboard_model->getTotalNewOrders();

		output_json($json);
	}

	public function ajax_dashboard_chart() {
		$cache_key = 'ajax_dashboard_chart';

		$json = json_decode($this->cache->get($cache_key), true);

		if (!empty($json)) {
			output_json($json);
			return;
		}

		$json = [];

		$last_results = $this->db
			->select("
				DATE_FORMAT(payment.date_added, '%M') AS label,
				SUM(payment.amount * currency.exchange_rate) as amount",
				false
			)
			->where('payment.date_added >' , date('Y-01-01', strtotime('-1 year')))
			->where('payment.date_added <' , date('Y-01-01'))
			->where('payment._deleted', 0)
			->where('order.parent_order_id', 0)
			->where_not_in('order.status', [0, 91, 92])
			->join('currency', 'currency.id = payment.currency_id')
			->join('order', 'order.id = payment.order_id')
			->group_by('label')
			->order_by("DATE_FORMAT(payment.date_added, '%m')")
			->get('payment')
			->result_array();

		$results = $this->db
			->select("
				DATE_FORMAT(payment.date_added, '%M') AS label,
				SUM(payment.amount * currency.exchange_rate) as amount",
				false
			)
			->where('payment.date_added >' , date('Y-01-01'))
			->where('payment.date_added <' , date('Y-01-01', strtotime('+1 year')))
			->where('payment._deleted', 0)
			->where('order._deleted', 0)
			->where('order.parent_order_id', 0)
			->where_not_in('order.status', [0, 91, 92])
			->join('currency', 'currency.id = payment.currency_id')
			->join('order', 'order.id = payment.order_id')
			->group_by('label')
			->order_by("DATE_FORMAT(payment.date_added, '%m')")
			->get('payment')
			->result_array();

		foreach ($last_results as $key => $item) {
			$json['chart']['previous_data'][] 	= $item['amount'];
			$json['chart']['data'][] 			= $results[$key]['amount'] ?? null;
			$json['chart']['labels'][] 			= $item['label'];
		}

		$json['doughnut']['labels'] = [
			_l('active_course'),
			_l('pending_course')
		];
		$json['doughnut']['data'] = [0, 0];

		$this->cache->save($cache_key, json_encode($json), $this->_cache_ttl);

		output_json($json);
	}

	public function ajax_dashboard_user_chart() {
		$cache_key = 'ajax_dashboard_user_chart';

		$json = json_decode($this->cache->get($cache_key), true);

		if (!empty($json)) {
			output_json($json);
			return;
		}

		$json = [];

		$last_results = $this->db
			->select("
				DATE_FORMAT(users.date_added, '%M') AS label,
				count(users.id) as total",
				false
			)
			->where('users.date_added >' , date('Y-01-01', strtotime('-1 year')))
			->where('users.date_added <' , date('Y-01-01'))
			->where('users._deleted', 0)
			->group_by('label')
			->order_by("DATE_FORMAT(users.date_added, '%m')")
			->get('users')
			->result_array();

		$results = $this->db
			->select("
				DATE_FORMAT(users.date_added, '%M') AS label,
				count(users.id) as total",
				false
			)
			->where('users.date_added >' , date('Y-01-01'))
			->where('users.date_added <' , date('Y-01-01', strtotime('+1 year')))
			->where('users._deleted', 0)
			->group_by('label')
			->order_by("DATE_FORMAT(users.date_added, '%m')")
			->get('users')
			->result_array();

		foreach ($last_results as $key => $item) {
			$json['chart']['previous_data'][] 	= $item['total'];
			$json['chart']['data'][] 			= $results[$key]['total'] ?? null;
			$json['chart']['labels'][] 			= $item['label'];
		}

		$this->cache->save($cache_key, json_encode($json), $this->_cache_ttl);

		output_json($json);
	}

	public function ajax_dashboard_book_chart() {
		$cache_key = 'ajax_dashboard_book_chart';

		$json = json_decode($this->cache->get($cache_key), true);

		if (!empty($json)) {
			output_json($json);
			return;
		}

		$json = [];

		$last_results = $this->db
			->select("
				DATE_FORMAT(book.date_published, '%M') AS label,
				count(book.id) as total",
				false
			)
			->where('book.date_published >' , date('Y-01-01', strtotime('-1 year')))
			->where('book.date_published <' , date('Y-01-01'))
			->where('book.status', 1)
			->where('book.archived', 0)
			->where('book._deleted', 0)
			->group_by('label')
			->order_by("DATE_FORMAT(book.date_published, '%m')")
			->get('book')
			->result_array();

		$results = $this->db
			->select("
				DATE_FORMAT(book.date_published, '%M') AS label,
				count(book.id) as total",
				false
			)
			->where('book.date_published >' , date('Y-01-01'))
			->where('book.date_published <' , date('Y-01-01', strtotime('+1 year')))
			->where('book.status', 1)
			->where('book.archived', 0)
			->where('book._deleted', 0)
			->group_by('label')
			->order_by("DATE_FORMAT(book.date_published, '%m')")
			->get('book')
			->result_array();

		foreach ($last_results as $key => $item) {
			$json['chart']['previous_data'][] 	= $item['total'];
			$json['chart']['data'][] 			= $results[$key]['total'] ?? null;
			$json['chart']['labels'][] 			= $item['label'];
		}

		$this->cache->save($cache_key, json_encode($json), $this->_cache_ttl);

		output_json($json);
	}

	public function get_dashboard_count($event_id = 0, $financial_year = '') {
		$event_id = (!empty($event_id) && $event_id != 'all') ? $event_id : 0;

		$data['page_name'] 		= 'dashboard/index';
		$data['page_title'] 	= _l('event_dashboard');

		$filter_data = [
			'status' 	=> 1,
			'order' 	=> 'DESC'
		];

		if (!empty($financial_year)) {
			$filter_data['selling_end_date_ge'] 	=  ($financial_year - 1) . '-04-01 00:00:00';
			$filter_data['selling_start_date_le'] 	= ($financial_year) . '-03-31 23:59:59';
		}

		$events 				= $this->event_model->get_all($filter_data)['rows'] ?? [];

		$data['events'] 		= $events;
		$data['event_years'] 	= self::_getEventYears([
			'status' => 1
		]);
		$data['event_id'] 		= (int)$event_id;
		$data['financial_year'] = $financial_year;
		$data['action_filter'] 	= base_url('admin/get_dashboard_count');
		$data['school_url'] 	= base_url('admin/sites');
		$data['order_url'] 		= base_url('admin/all_orders?site_code');

		$this->load->view('backend/index', $data);
	}

	public function ajax_get_dashboard_count($event_id = 0) {
		$cache_key = 'get_dashboard_count_' . (int)$event_id;

		$data = json_decode($this->cache->get($cache_key), true);

		if (!empty($data)) {
			output_json(['data' => $data]);
			return;
		}

		$event_id 	= (!empty($event_id) && $event_id != 'all') ? $event_id : 0;
		$site_code 	= '';
		$event_info = $this->event_model->get($event_id);
		$site_code 	= $this->site_model->get($event_info['parent_site_id'] ?? '')['site_code'] ?? '';

		$this->load->model('common/Stats_model', 'stats_model');
		$this->load->model('event/EventStats_model', 'event_stats_model');

		$data['timestamp_start'] 	= time();
		$data['timestamp_end']		= time();
		$today_date 				= date('Y-m-d');

		$today_filter_data['event_id'] 		= $filter_data['event_id'] = $event_info['id'] ?? 0;
		$today_filter_data['date_added']	= date('Y-m-d');

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));

			$filter_data['start_date'] 	= date('Y-m-d 00:00:00', strtotime(trim($explode[0])));
			$filter_data['end_date'] 	= date('Y-m-d 23:59:59', strtotime(trim($explode[1])));
		}

		$model = !empty($event_info) ? 'event_stats_model' : 'stats_model';

		$total_registered_school			= $this->{$model}->registered_school($filter_data);


		if ($event_info['country_id'] == 1) {
			$south_registered_school			= $this->{$model}->registered_school(array_merge($filter_data, ['is_south' => true]));
			$north_registered_school			= $total_registered_school - $south_registered_school;
		}

		$total_school_with_authors 			= $this->{$model}->school_with_authors($filter_data);
		$registered_school_with_authors 	= $this->{$model}->school_with_authors(array_merge($filter_data, ['school_in_event' => true]));
		$registered_school_with_0_authors 	= $total_registered_school - $registered_school_with_authors;
		$unregistered_school_with_authors 	= $total_school_with_authors - $registered_school_with_authors;

		$data['stats']['schools'] = [
			[
				'label'			=> _l('registered_school'),
				'key'			=> 'registered_school',
				'icon'			=> 'dripicons-bookmark',
				'total' 		=> $total_registered_school,
				'today'			=> $this->{$model}->registered_school($today_filter_data),
				'url'			=> base_url('admin/sites'),
				'extra'			=> $event_info['country_id'] == 1 ? vsprintf(_l('south: %s, north: %s'), [
					$south_registered_school,
					$north_registered_school,
				]) : '',
			],
			[
				'label'			=> _l('verified_school'),
				'key'			=> 'verified_school',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $this->{$model}->verified_school($filter_data),
				'today' 		=> $this->{$model}->verified_school($today_filter_data),
				'url'			=> base_url('admin/sites'),
			],
			[
				'label'			=> _l('schools_with_authors'),
				'key'			=> 'schools_with_authors',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $total_school_with_authors,
				'today'			=> $this->{$model}->school_with_authors($today_filter_data),
				'url'			=> base_url('admin/sites'),
				'extra'			=> vsprintf(_l('reg with author: %s, unreg with author: %s'), [
					$registered_school_with_authors,
					$unregistered_school_with_authors,
				]),
			],
		];

		$data['stats']['enrolled_schools'] = [
			[
				'label'			=> _l('new_school_enrolled'),
				'key'			=> 'new_school_enrolled',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $this->{$model}->verified_school(array_merge($filter_data, ['is_new' => 1])),
				'today' 		=> $this->{$model}->verified_school(array_merge($today_filter_data, ['is_new' => 1])),
				'url'			=> base_url('admin/sites'),
			],
			[
				'label'			=> _l('old_school_enrolled'),
				'key'			=> 'old_school_enrolled',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $this->{$model}->old_school_enrolled(array_merge($filter_data, ['is_old' => 1, 'verified' => 1])),
				'today' 		=> $this->{$model}->old_school_enrolled(array_merge($today_filter_data, ['is_old' => 1, 'verified' => 1])),
				'url'			=> base_url('admin/sites'),
			],
		];

		$data['stats']['authors'] = [
			[
				'label'			=> _l('registered_authors'),
				'key'			=> 'registered_authors',
				'icon'			=> 'dripicons-bookmark',
				'total' 		=> $this->{$model}->registered_authors($filter_data),
				'today'			=> $this->{$model}->registered_authors($today_filter_data),
				'url'			=> base_url('admin/users'),
			],
			[
				'label'			=> _l('new_authors'),
				'key'			=> 'new_authors',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $this->{$model}->registered_authors(array_merge($filter_data, ['is_new' => 1])),
				'today' 		=> $this->{$model}->registered_authors(array_merge($today_filter_data, ['is_new' => 1])),
				'url'			=> base_url('admin/users'),
			],
			[
				'label'			=> _l('old_authors'),
				'key'			=> 'old_authors',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $this->{$model}->registered_authors(array_merge($filter_data, ['is_old' => 1])),
				'today'			=> $this->{$model}->registered_authors(array_merge($today_filter_data, ['is_old' => 1])),
				'url'			=> base_url('admin/users'),
			],
			[
				'label'			=> _l('subscriptions'),
				'key'			=> 'subscriptions',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $this->{$model}->subscribed_authors($filter_data),
				'today'			=> $this->{$model}->subscribed_authors($today_filter_data),
				'url'			=> base_url('admin/users'),
			],
		];

		$author_stats = self::_buildSourceStats(
			$event_id,
			'user',
			$model,
			'registered_authors',
			$filter_data,
			$today_filter_data,
			['is_new' => 1],
			base_url('admin/users')
		);

		if (!empty($author_stats)) {
			$data['stats']['enrolled_authors'] = $author_stats;
		}

		$data['stats']['books'] = [
			[
				'label'			=> _l('written'),
				'key'			=> 'written',
				'icon'			=> 'dripicons-bookmark',
				'total' 		=> $this->{$model}->written_books($filter_data),
				'today'			=> $this->{$model}->written_books($today_filter_data),
				'url'			=> base_url('admin/books'),
			],
			[
				'label'			=> _l('published'),
				'key'			=> 'published',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $this->{$model}->published_books($filter_data),
				'today' 		=> $this->{$model}->published_books($today_filter_data),
				'url'			=> base_url('admin/books'),
			],
		];

		$revenue_multiplier = 1;
		$ebook_order_total 	= 0;
		$ebook_order_today 	= 0;

		$data['stats']['orders'] = [
			[
				'label'			=> _l('total_ordered_copies'),
				'key'			=> 'total_ordered_copies',
				'icon'			=> 'dripicons-bookmark',
				'total' 		=> $this->{$model}->ordered_copies($filter_data),
				'today'			=> $this->{$model}->ordered_copies($today_filter_data),
				'url'			=> base_url('admin/orders'),
			],
			[
				'label'			=> _l('total_orders'),
				'key'			=> 'total_orders',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $this->{$model}->order_total($filter_data),
				'today' 		=> $this->{$model}->order_total($today_filter_data),
				'url'			=> base_url('admin/orders'),
			],
			[
				'label'			=> _l('total_revenue'),
				'key'			=> 'total_revenue',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> currency(($this->{$model}->order_revenue($filter_data) * $revenue_multiplier) + $ebook_order_total, 0, 'INR'),
				'today' 		=> currency(($this->{$model}->order_revenue($today_filter_data) * $revenue_multiplier) + $ebook_order_today, 0, 'INR'),
				'url'			=> base_url('admin/orders'),
			],
		];

		if ($event_id == NYAF_IN_EVENT_ID) {
			$data['stats']['exclusive_invitee'] = [
				[
					'label'			=> _l('exclusive_joinee'),
					'key'			=> 'exclusive_joinee',
					'icon'			=> 'dripicons-bookmark',
					'total'			=> $this->user_referral_list_model->get_all()['total'],
					'today' 		=> $this->user_referral_list_model->get_all([
						'startdate' => date('Y-m-d'),
						'enddate'   => date('Y-m-d')
					])['total'],
					'url'			=> base_url('admin/referral_user'),
				],
				[
					'label'			=> _l('exclusive_invitee'),
					'key'			=> 'exclusive_invitee',
					'icon'			=> 'dripicons-bookmark',
					'total'			=> $this->user_referral_model->get_all()['total'],
					'today' 		=> $this->user_referral_model->get_all([
						'startdate' => date('Y-m-d'),
						'enddate'   => date('Y-m-d')
					])['total'],
					'url'			=> base_url('admin/referral_user'),
				],
			];
		}

		$this->cache->save($cache_key, json_encode($data), $this->_cache_ttl / 2);

		output_json(['data' => $data]);
	}

	public function get_updated_dashboard_count($site_code = '') {
		$site_code = (!empty($site_code) && $site_code != 'all') ? $site_code : '';

		$all_school_register_total   = 0;
		$school_register_total 	 	 = 0;
		$all_users_total 			 = 0;
		$users_total 				 = 0;
		$old_users_total 			 = 0;
		$books_total 				 = 0;
		$publish_book_total 		 = 0;
		$ordered_books_total 		 = 0;
		$orders_total 			 	 = 0;


		foreach ($this->event_model->get_all()['rows'] ?? [] as $key => $event_info) {
			if (!empty($site_code_data = $this->site_model->get($event_info['parent_site_id']))) {
				if (in_array($event_info['id'], ['5'])) {
					$self_sites = 3;
				} elseif (in_array($event_info['id'], ['6','7','8','10'])) {
					$self_sites = 1;
				} else{
					$self_sites = 2;
				}

				$school_register_data = $this->crud_model->sum_school_register(!empty($site_code), $site_code_data['site_code']);

				$detail= [
					'event_name'			=> $event_info['name'],
					'all_school_register' 	=> $this->crud_model->event_sum_all_school_register(!empty($site_code), $event_info),
					'school_register' 		=> empty($site_code) ? (!empty($school_register_data) ? ($school_register_data - $self_sites) : $school_register_data) : $school_register_data,
					// 'school_register' 		=> empty($site_code) ?  ($this->crud_model->sum_school_register(!empty($site_code), $site_code_data['site_code']) - $self_sites) :  $this->crud_model->sum_school_register(!empty($site_code), $site_code_data['site_code']),
					'all_users' 			=> $this->crud_model->event_sum_all_school_students(!empty($site_code), $event_info),
					'users' 				=> $this->crud_model->event_sum_school_students(!empty($site_code), $site_code_data['site_code'], $event_info),
					'old_users' 			=> $this->crud_model->event_sum_old_school_students(!empty($site_code), $site_code_data['site_code'], $event_info),
					'books' 				=> $this->crud_model->event_sum_school_books(!empty($site_code), $event_info),
					'publish_book' 			=> $this->crud_model->event_sum_school_books_published(!empty($site_code), $event_info),
					'ordered_books' 		=> $this->crud_model->event_sum_nyaf_ordered_book(!empty($site_code), $event_info),
					'orders' 				=> $this->crud_model->event_sum_nyaf_orderes(!empty($site_code), $event_info),
				];

				$data['data'][] = $detail;

				$all_school_register_total 	= $all_school_register_total + $detail['all_school_register'];
				$school_register_total 		= $school_register_total + $detail['school_register'];
				$all_users_total 			= $all_users_total + $detail['all_users'];
				$users_total 				= $users_total + $detail['users'];
				$old_users_total 			= $old_users_total + $detail['old_users'];
				$books_total 				= $books_total + $detail['books'];
				$publish_book_total 		= $publish_book_total + $detail['publish_book'];
				$ordered_books_total 		= $ordered_books_total + $detail['ordered_books'];
				$orders_total 				= $orders_total + $detail['orders'];

			}
		}

		$direct_data = [
			'event_name'			=> 'Direct',
			'all_school_register'	=> 0,
			'school_register' 		=> abs($this->crud_model->sum_school_register(!empty($site_code),'') - $school_register_total),
			'all_users'				=> 0,
			'users' 				=> abs($this->crud_model->sum_school_students(!empty($site_code), '') - $users_total),
			'old_users'				=> 0,
			'books' 				=> abs($this->crud_model->sum_school_books(!empty($site_code), '') - $books_total),
			'publish_book' 			=> abs($this->crud_model->sum_school_books_published(!empty($site_code), '') - $publish_book_total),
			'ordered_books' 		=> abs($this->crud_model->sum_nyaf_ordered_book(!empty($site_code), '') - $ordered_books_total),
			'orders' 				=> abs($this->crud_model->sum_nyaf_orderes(!empty($site_code), '') - $orders_total),
		];


		$all_school_register_total 	= $all_school_register_total + $direct_data['all_school_register'];
		$school_register_total 		= $school_register_total + $direct_data['school_register'];
		$all_users_total 			= $all_users_total + $direct_data['all_users'];
		$users_total 				= $users_total + $direct_data['users'];
		$old_users_total 			= $old_users_total + $direct_data['old_users'];
		$books_total 				= $books_total + $direct_data['books'];
		$publish_book_total 		= $publish_book_total + $direct_data['publish_book'];
		$ordered_books_total 		= $ordered_books_total + $direct_data['ordered_books'];
		$orders_total 				= $orders_total + $direct_data['orders'];


		$data['data'][] = $direct_data;

		$total_data = [
			'event_name'			=> 'Total',
			'all_school_register' 	=> $all_school_register_total,
			'school_register' 		=> $school_register_total,
			'all_users' 			=> $all_users_total,
			'users' 				=> $users_total,
			'old_users' 			=> $old_users_total,
			'books' 				=> $books_total,
			'publish_book' 			=> $publish_book_total,
			'ordered_books' 		=> $ordered_books_total,
			'orders' 				=> $orders_total,
		];

		$data['data'][] 		= $total_data;

		$data['page_name'] 		= 'new_count_dashboard';
		$data['page_title'] 	= _l('count_dashboard');


		$data['site_code'] 		= $site_code;
		$data['self_sites'] 	= $self_sites;
		$data['action_filter'] 	= base_url('admin/get_updated_dashboard_count');
		$data['school_url'] 	= base_url('admin/sites?site_code='.$site_code);
		$data['order_url'] 		= base_url('admin/all_orders?site_code='.$site_code);

		$this->load->view('backend/index', $data);
	}

	public function get_logistic_dashboard_int() {
		$filter_data = [];
		$filter_data['startdate'] 	= date('Y-m-d');
		$filter_data['enddate'] 	= date('Y-m-d');

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] 	= !empty($explode[0]) ? date('Y-m-d', strtotime(trim($explode[0]))) : date('Y-m-d');;
			$filter_data['enddate'] 	= !empty($explode[1]) ? date('Y-m-d', strtotime(trim($explode[1]))) : date('Y-m-d');
		}

		$data['page_name'] 		= 'logistic/logistic';
		$data['page_title'] 	= _l('logistic');
		$data['action_ajax']	= base_url('admin/get_logistic_dashboard_int');

		$data['timestamp_start'] = time();
		$data['timestamp_end']	 = time();

		$data['startdate']	 = $filter_data['startdate'];
		$data['enddate']	 = $filter_data['enddate'];

		$this->load->model('order/OrderPackingLog_model', 'order_packing_log_model');

		$results = $this->student_model->get_by_role_id(10);

		$data['users'] = [];

		foreach ($results as $item) {
			$data['users'][] = [
				'name'	=> $item['first_name'] . ' ' . $item['last_name'],
				'total'	=> $this->order_packing_log_model->get_all([
					'user_id'		=> (int)$item['id'],
					'month'			=> date('Y-m-d'),
				])['total'] ?? 0,
				'new'	=> $this->order_packing_log_model->get_all([
					'user_id'		=> (int)$item['id'],
					'startdate'		=> $filter_data['startdate'],
					'enddate'		=> $filter_data['enddate']
				])['total'] ?? 0,
			];
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_export_logistic_dashboard_int() {
		$json = [];

		$filter_data = [];
		$filter_data['startdate'] 	= date('Y-m-d');
		$filter_data['enddate'] 	= date('Y-m-d');

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));

			$filter_data['startdate'] 	= !empty($explode[0]) ? date('Y-m-d', strtotime(trim($explode[0]))) : date('Y-m-d');;
			$filter_data['enddate'] 	= !empty($explode[1]) ? date('Y-m-d', strtotime(trim($explode[1]))) : date('Y-m-d');
		}

		$sn = 1;

		$this->load->model('order/OrderPackingLog_model', 'order_packing_log_model');

		$results = $this->student_model->get_by_role_id(10);

		$data = [];

		foreach ($results as $item) {
			$data[] = [
				'sn'				=> $sn,
				'startdate'			=> $filter_data['startdate'],
				'enddate'			=> $filter_data['enddate'],
				'name'				=> $item['first_name'] . ' ' . $item['last_name'],
				'total'				=> $this->order_packing_log_model->get_all([
					'user_id'		=> (int)$item['id'],
					'month'			=> date('Y-m-d'),
				])['total'] ?? 0,
				'new'				=> $this->order_packing_log_model->get_all([
					'user_id'		=> (int)$item['id'],
					'startdate'		=> $filter_data['startdate'],
					'enddate'		=> $filter_data['enddate']
				])['total'] ?? 0,
			];

			$sn++;
		}

		self::_downloadCsv($data, 'data');

		output_json($json);
	}

	public function get_logistic_qaqc_int() {
		$filter_data = [];
		$filter_data['startdate'] = date('Y-m-d');
		$filter_data['enddate'] = date('Y-m-d');

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));

			$filter_data['startdate'] 	= !empty($explode[0]) ? date('Y-m-d', strtotime(trim($explode[0]))) : date('Y-m-d');;
			$filter_data['enddate'] 	= !empty($explode[1]) ? date('Y-m-d', strtotime(trim($explode[1]))) : date('Y-m-d');
		}

		$data['page_name'] 		= 'logistic/qa_qc_dash';
		$data['page_title'] 	= _l('logistic');
		$data['action_ajax']	= base_url('admin/get_logistic_qaqc_int');

		$data['timestamp_start'] = time();
		$data['timestamp_end']	 = time();

		$data['startdate']	 = $filter_data['startdate'];
		$data['enddate']	 = $filter_data['enddate'];

		$this->load->model('printer/QaQcLogs_model', 'qa_qc_logs_model');

		$results = $this->student_model->get_by_role_id(10);

		$data['users'] = [];

		foreach ($results as $item) {
			$data['users'][] = [
				'name'			=> $item['first_name'] . ' ' . $item['last_name'],
				'total'			=> $this->qa_qc_logs_model->get_all([
					'manager_id'	=> (int)$item['id'],
					'_deleted'		=> 0,
					'month'			=> date('Y-m-d'),
				])['total'] ?? 0,
				'new'			=> $this->qa_qc_logs_model->get_all([
					'manager_id'	=> (int)$item['id'],
					'_deleted'		=> 0,
					'startdate'		=> $filter_data['startdate'],
					'enddate'		=> $filter_data['enddate']
				])['total'] ?? 0,
				'books_title'	=> $this->qa_qc_logs_model->getQaQcBookTitles([
					'manager_id'	=> (int)$item['id'],
					'_deleted'		=> 0,
					'startdate'		=> $filter_data['startdate'],
					'enddate'		=> $filter_data['enddate'],
				])['total'] ?? 0,
			];
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_export_logistic_qaqc_int() {
		$json = [];

		$filter_data = [];
		$filter_data['startdate'] 	= date('Y-m-d');
		$filter_data['enddate'] 	= date('Y-m-d');

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));

			$filter_data['startdate'] 	= !empty($explode[0]) ? date('Y-m-d', strtotime(trim($explode[0]))) : date('Y-m-d');;
			$filter_data['enddate'] 	= !empty($explode[1]) ? date('Y-m-d', strtotime(trim($explode[1]))) : date('Y-m-d');
		}

		$sn = 1;

		$this->load->model('printer/QaQcLogs_model', 'qa_qc_logs_model');

		$results = $this->student_model->get_by_role_id(10);

		$data = [];

		foreach ($results as $item) {
			$data[] = [
				'sn'				=> $sn,
				'startdate'			=> $filter_data['startdate'],
				'enddate'			=> $filter_data['enddate'],
				'name'				=> $item['first_name'] . ' ' . $item['last_name'],
				'new'				=> $this->qa_qc_logs_model->get_all([
					'manager_id'	=> (int)$item['id'],
					'_deleted'		=> 0,
					'startdate'		=> $filter_data['startdate'],
					'enddate'		=> $filter_data['enddate']
				])['total'] ?? 0,
				'books_title'	=> $this->qa_qc_logs_model->getQaQcBookTitles([
					'manager_id'	=> (int)$item['id'],
					'_deleted'		=> 0,
					'startdate'		=> $filter_data['startdate'],
					'enddate'		=> $filter_data['enddate'],
				])['total'] ?? 0,
			];

			$sn++;
		}

		self::_downloadCsv($data, 'data');

		output_json($json);
	}

	public function ajax_export_dashboard_sc_medallians($type = '') {
		$json = [];

		if ($type == 'shipped') {
			$sc_medals = $this->db->select('sc_user_medallion_address.id, sc_user_medallion_address.type, sc_user_medallion_address.user_id, sc_user_medallion_address.book_id, sc_user_medallion_address.address, sc_user_medallion_address.date_added, sc_user_medallion_address.date_modified, sc_user_medallion_address.shipped_by, sc_user_medallion_address.date_shipped')
			->from('sc_user_medallion_address')
			->where('sc_user_medallion_address.ship_status = 1')
			->where('sc_user_medallion_address.address IS NOT NULL')
			->order_by('sc_user_medallion_address.user_id, sc_user_medallion_address.book_id, sc_user_medallion_address.id')
			->get()
			->result_array();
		} else {
			$sc_medals = $this->db->select('sc_user_medallion_address.id, sc_user_medallion_address.type, sc_user_medallion_address.user_id, sc_user_medallion_address.book_id, sc_user_medallion_address.address, sc_user_medallion_address.date_added, sc_user_medallion_address.date_modified, sc_user_medallion_address.shipped_by, sc_user_medallion_address.date_shipped')
			->from('sc_user_medallion_address')
			->where('sc_user_medallion_address.ship_status = 0')
			->where('sc_user_medallion_address.address IS NOT NULL')
			->order_by('sc_user_medallion_address.user_id, sc_user_medallion_address.book_id, sc_user_medallion_address.id')
			->get()
			->result_array();
		}

		$sn = 1;

		$data = [];

		$this->load->model('address/SCMedallionAddress_model', 'sc_medallion_address_model');

		foreach ($sc_medals as $sc_medal) {
			$book_info = $this->book_model->get($sc_medal['book_id']);

			if (empty($book_info)) continue;

			$user_info = $this->user_model->get($sc_medal['user_id']);

			if (empty($user_info)) continue;

			$address = json_decode($sc_medal['address'], 1);

			$book_sold = $this->db->select('SUM(quantity) AS total')
				->from('order_product')
				->where('order_product.product_id', $sc_medal['book_id'])
				->get()
				->row()->total;

			$sc_data = [
				'sn'				=> $sn,
				'user_id'			=> $sc_medal['user_id'],
				'book_id'			=> $sc_medal['book_id'],
				'book_name'			=> $book_info['name'],
				'author_name'		=> $book_info['author_name'],
				'full_name'			=> $address['full_name'],
				'mobile'			=> substr($address['mobile'], 2, 12),
				'email'				=> !empty($sc_medal['email']) ? $sc_medal['email'] : $user_info['email'],
				'address'			=> $address['address'],
				'pincode'			=> $address['pincode'],
				'landmark'			=> $address['landmark'],
				'book_sold'			=> $book_sold,
				'medal_name'		=> str_replace('_', ' ', strtoupper($sc_medal['type'])),
				'date_added'		=> $sc_medal['date_added'],
				'date_modified'		=> $sc_medal['date_modified']
			];

			if ($type == 'shipped') {
				$shipped_user_info 		= !empty($sc_medal['shipped_by']) ? $this->user_model->get($sc_medal['shipped_by']) : [];

				$sc_data['shipped_by'] 	= !empty($shipped_user_info['first_name']) ? trim($shipped_user_info['first_name'] . ' ' . $shipped_user_info['last_name']) : '';
				$sc_data['date_shipped']= !empty($sc_medal['date_shipped']) ? $sc_medal['date_shipped'] : $sc_medal['date_modified'];
				$sc_data['user_mobile'] = $user_info['mobile'];
			} else {
				$this->sc_medallion_address_model->edit($sc_medal['id'], [
					'ship_status'	=> 1,
					'shipped_by'	=> $this->session->userdata('user_id'),
					'date_shipped'	=> date('Y-m-d H:i:s')
				]);
			}

			$data[] = $sc_data;

			$sn++;
		}

		self::_downloadCsv($data, 'medallians_data_');

		output_json($json);
	}

	public function ajax_isbn_eligible_books() {
		$json = [];

		$books = $this->db->select('sold, book_id')
			->from('bookstore')
			->where('bookstore.book_id in (select id from book where isbn = "" and _deleted = 0)')
			->where('sold >= 45')
			->order_by('sold', 'DESC')
			->get()
			->result_array();

		$json['recordsTotal'] 		= count($books);
		$json['recordsFiltered'] 	= count($books);
		$json['data']				= [];

		foreach ($books as $key => $book) {
			$book_info = $this->book_model->get($book['book_id']);

			if (!empty($book_info['isbn']) || empty($book_info)) continue;

			$user_info = $this->user_model->get($book_info['user_id']);

			if (empty($user_info)) continue;

			if ((strtolower($user_info['location']) === 'india') && ($book['sold'] < (ISBN_LIMIT - 5))) {
				continue;
			} elseif ((strtolower($user_info['location']) !== 'india') && ($book['sold'] < (((strtolower($user_info['location']) === 'kuwait') ? ISBN_LIMIT : GLOBAL_ISBN_LIMIT ) - 5))) {
				continue;
			}

			$book = array_merge($book, $book_info);

			$json['data'][] = [
				'id'					=> $book['book_id'],
				'book_name'				=> $book_info['name'],
				'author_name'			=> $book_info['author_name'],
				'version'				=> $book_info['version'],
				'sold'					=> $book['sold'],
				'location'				=> $user_info['location'],
				'source'				=> $user_info['source'],
				'actions'				=> vsprintf('<a href="%s" class="btn btn-warning mr-2" target="_blank">%s</a><a href="%s" class="btn btn-danger mr-2" target="_blank">%s</a><a href="%s" class="btn btn-info" target="_blank">%s</a>', [
					base_url('admin/title_verso/' . $book['book_id']),
					_l('title_verso'),
					base_url('admin/printBook/' . $book['book_id'] . '/' . $book_info['version']),
					_l('pdf'),
					base_url('admin/book_form/edit/' . $book['book_id']),
					_l('assign_isbn'),
				]),
			];
		}

		output_json($json);
	}

	public function ajax_get_total_for_source() {
		$json = [];

		if ($this->input->get('source')) {
			$verified_key 	= ($this->input->get('model_name') == 'school_lead_model') ? 'verified' : 'email_mobile_verified';
			$source_key 	= ($this->input->get('model_name') == 'school_lead_model') ? 'source' : 'utm_source';

			$filter_data = [
				$source_key => ($this->input->get('source') != 'all') ? $this->input->get('source') : '' ,
				'event_id' 	=> $this->input->get('event_id'),
				$verified_key => 1
			];

			$json['total']  = $this->{$this->input->get('model_name')}->get_all($filter_data)['total'] ?? 0;

			output_json($json);
		}
	}

	private function _getEventYears($filter_data = []) {
		if (empty($events = $this->event_model->get_all($filter_data)['rows'] ?? [])) return [];

		$selling_end_dates = array_column($events, 'selling_end_date');

		if (empty($selling_end_dates)) return [];

		$dates_by_year = array_reduce($selling_end_dates, function($carry, $date) {
			$year 		= date('Y', strtotime($date));
			$carry[] 	= $year;

			return $carry;
		}, []);

		if (empty($dates_by_year)) return [];

		$unique_year = array_unique($dates_by_year);

		rsort($unique_year);

		return $unique_year;
	}

	public function ajax_dashboard_details_report($event_id = 0, $download = false) {
		$filter_data = [];

		if (!empty($event_id)) {
			$filter_data['event_id'] = (int)$event_id;
		}

		if ($this->input->get('duration') && $this->input->get('duration') == 'today') {
			$filter_data['date_added']	= date('Y-m-d');
		}

		if ($this->input->get('type')) {
			$method = strtolower($this->input->get('type'));

			if ($method == 'new_school_enrolled') {
				$method = 'verified_school';
				$filter_data['is_new'] 		= 1;
			} elseif ($method == 'old_school_enrolled') {
				$filter_data['is_old'] 		= 1;
				$filter_data['verified'] 	= 1;
			}

			$method .= '_data';
		}

		$this->load->model('common/Stats_model', 'stats_model');
		$this->load->model('event/EventStats_model', 'event_stats_model');

		$model = !empty($event_id) ? 'event_stats_model' : 'stats_model';

		$data['results'] = method_exists($this->{$model}, $method)
			? $this->{$model}->{$method}($filter_data)
			: [];

		if ($download) {
			self::_downloadCsv($data['results'], 'download_report');
		} else{
			if (!empty($data['results'])) {
				$json['view'] = $this->load->view('backend/admin/dashboard/info', $data, true);
			}
		}

		output_json($json);
	}

	private function _buildSourceStats(
		$event_id = 0,
		$type = '',
		$model = '',
		$method = '',
		$filter_data = [],
		$today_filter_data = [],
		$extra_filters = [],
		$url = ''
	) {

		if (empty($model) || empty($method) || !method_exists($this->{$model}, $method)) {
			return [];
		}

		$utm_sources = $this->utm_source_model->get_all([
			'event_id' => $event_id,
			'type'     => $type,
			'status'   => 1,
		])['rows'] ?? [];

		if (empty($utm_sources)) {
			return [];
		}

		$base_filter       	= array_merge($filter_data, $extra_filters);
		$base_today_filter  = array_merge($today_filter_data, $extra_filters);

		$stats = [];

		foreach ($utm_sources as $row) {

			if (empty($row['key'])) {
				continue;
			}

			$source_key   = $row['key'];
			$source_label = $row['value'] ?? ucfirst(str_replace('_', ' ', $source_key));

			$filters       	= $base_filter + ['utm_source' => $source_key];
			$today_filters  = $base_today_filter + ['utm_source' => $source_key];

			$total = $this->{$model}->{$method}($filters);

			$today = $this->{$model}->{$method}($today_filters);

			if ((int)$total === 0 && (int)$today === 0) {
				continue;
			}

			$stats[] = [
				'label' => $source_label,
				'key'   => $source_key,
				'icon'  => 'dripicons-bookmark',
				'total' => (int)$total,
				'today' => (int)$today,
				'url'   => $url ? $url . '?source=' . urlencode($source_key) : ''
			];
		}

		return $stats;
	}
}
