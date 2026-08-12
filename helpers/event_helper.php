<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('checkBookLeague')) {
	function checkBookLeague($book_id = '', $event_id = '') {
		$CI =& get_instance();

		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('book/Book_model', 'book_model');
		$CI->load->model('common/Site_model', 'site_model');
		$CI->load->model('localisation/City_model', 'city_model');
		$CI->load->model('localisation/State_model', 'state_model');
		$CI->load->model('event/EventBook_model', 'event_book_model');
		$CI->load->model('event/EventOrder_model', 'event_order_model');

		if (empty($book_id) || empty($event_id)) return;

		$event_book_info = $CI->event_book_model->get_all([
			'book_id'	=> (int)$book_id,
			'event_id'	=> (int)$event_id,
		])['rows'][0] ?? [];

		if (empty($event_book_info)) return;

		$book_info = $CI->book_model->get($book_id);

		if (empty($book_info)) return;

		$filter_data = [
			'book_id'	=> (int)$book_id,
			'event_id'	=> (int)$event_id,
			'sort'		=> 'quantity',
			'order'		=> 'DESC',
			'start'		=> 0,
			'limit'		=> 1,
		];

		$top_sold_book = $CI->event_order_model->getSoldByBook($filter_data)['rows'][0] ?? [];

		log_kb(['book_id' => $book_id,'common-helper' => $top_sold_book]);

		$event_info = $CI->db
			->select('
				event_challenge_school.id AS event_challenge_school_id,
				event_challenge_school.book_sold AS school_min_sold,
				event_challenge_school.max_book_sold AS school_max_sold,
				event_challenge_city.id AS event_challenge_city_id,
				event_challenge_city.book_sold AS city_min_sold,
				event_challenge_city.max_book_sold AS city_max_sold,
				event_challenge_state.id AS event_challenge_state_id,
				event_challenge_state.book_sold AS state_min_sold,
				event_challenge_state.max_book_sold AS state_max_sold,
				event_challenge_country.id AS event_challenge_country_id,
				event_challenge_country.book_sold AS country_min_sold,
				event_challenge_country.max_book_sold AS country_max_sold,
			')
			->from('event_challenge_school')
			->join('event_challenge_city', 'event_challenge_city.event_id = event_challenge_school.event_id')
			->join('event_challenge_state', 'event_challenge_state.event_id = event_challenge_school.event_id')
			->join('event_challenge_country', 'event_challenge_country.event_id = event_challenge_school.event_id')
			->where('event_challenge_school.event_id', (int)$event_id)
			->where('event_challenge_school.start_date <= ', date('Y-m-d H:i:s'))
			->where('event_challenge_city.start_date <= ', date('Y-m-d H:i:s'))
			->where('event_challenge_state.start_date <= ', date('Y-m-d H:i:s'))
			->where('event_challenge_country.start_date <= ', date('Y-m-d H:i:s'))
			->get()
			->row_array()
		;

		if (empty($event_info)) {
			return;
		}

		$author_info 	= $CI->user_model->get($book_info['user_id']);
		$site_info 		= $CI->site_model->get($author_info['site_id']);
		$city_info 		= $CI->city_model->get($author_info['city_id']);
		$state_info 	= $CI->state_model->get($author_info['state_id']);

		$league  = [
			'type'			=> 'school',
			'school'		=> $site_info['name'] ?? 'School',
			'city'			=> $city_info['name'] ?? 'City',
			'state'			=> $state_info['name'] ?? 'State',
			'country'		=> 'India',
			'name'			=> $site_info['name'],
			'book_name'		=> $book_info['name'],
			'author_name'	=> $book_info['author_name'],
			'author_info'	=> $author_info,
			'book_info'		=> $book_info,
			'sold'			=> $top_sold_book['quantity'],
		];

		if (
			$top_sold_book['quantity'] >= $event_info['school_min_sold'] &&
			$top_sold_book['quantity'] <= $event_info['school_max_sold']
		) {
			$league['type']  = 'school';
			$league['next_type']  = 'city';

		} elseif (
			$top_sold_book['quantity'] >= $event_info['city_min_sold'] &&
			$top_sold_book['quantity'] <= $event_info['city_max_sold']
		) {
			$league['type']  = 'city';
			$league['next_type']  = 'state';
		} elseif (
			$top_sold_book['quantity'] >= $event_info['state_min_sold'] &&
			$top_sold_book['quantity'] <= $event_info['state_max_sold']
		) {
			$league['type']  = 'state';
			$league['next_type']  = 'country';
		} elseif (
			$top_sold_book['quantity'] >= $event_info['country_min_sold']
		) {
			$league['type']  = 'country';
			$league['next_type']  = 'country';
		}

		return  $league;
	}
}

if (! function_exists('get_event_slug')) {
	function get_event_slug($name, $event_id = 0) {
		$slug = preg_replace(['/[^\w\s]/', '/\s+/'], [' ', '-'], mb_strtolower($name));

		$CI	=&	get_instance();

		if ($result = $CI->db->get_where('event', [
			'slug'	=> $slug
		])->row_array()) {
			if ($result['id'] != $event_id) {
				$slug .= '-' . $event_id;
			}
		}

		return $slug;
	}
}

if (!function_exists('get_bb_score')) {
	function get_bb_score($item = []) {
		$CI	=&	get_instance();

		$result = $CI->db->get_where('user_rank_country', [
			'book_id'	=> $item['id'],
			'user_id'	=> $item['user_id'],
		])->row();

		return (!empty($result)) ? $result->score : 0;
	}
}

if (!function_exists('_eventUrls')) {
	function _eventUrls($slug = '') {
		$buttons = [];

		if (!empty($slug)) {
			$buttons[] = vsprintf('<a href="%s" class="btn btn-secondary btn-sm" target="_blank">%s</a>', [
				sprintf(USER_YAF_URL . 'events/school/signup/%s', $slug),
				_l('school'),
			]);

			$buttons[] = vsprintf('<a href="%s" class="btn btn-secondary btn-sm" target="_blank">%s</a>', [
				sprintf(USER_YAF_URL . 'events/teacher/signup/%s', $slug),
				_l('teacher'),
			]);

			$buttons[] = vsprintf('<a href="%s" class="btn btn-secondary btn-sm" target="_blank">%s</a>', [
				sprintf(USER_YAF_URL . 'events/student/signup/%s', $slug),
				_l('student'),
			]);
		}

		return implode('<p style="margin-bottom: 0.4rem;"></p>', $buttons);
	}
}

if (!function_exists('bookBuyOptions')) {
	function bookBuyOptions($book_id = 0, $country_code = '') {
		$CI =& get_instance();

		$CI->load->model('event/EventBook_model', 'event_book_model');

		$event_book_info = $CI->event_book_model->get_all([
			'book_id'			=> (int)$book_id,
			'is_active_event'	=> 1,
		])['rows'][0] ?? [];

		if (empty($event_book_info)
			|| empty($event_book_info['buying_options'])
			|| empty($country_code)
			|| (strtolower($event_book_info['country_code']) != strtolower($country_code))
		) {
			return [];
		}

		return !empty($event_book_info['buying_options']) ? json_decode($event_book_info['buying_options'], true) : [];
	}
}
