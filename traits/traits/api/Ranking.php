<?php defined('BASEPATH') OR exit('No direct script access allowed');

load_trait('challenge');

trait Ranking {
	use DailyChallenge,
		WeeklyChallenge,
		SchoolChallenge,
		CityChallenge,
		StateChallenge,
		NationalChallenge,
		GlobalChallenge,
		GeneralChallenge,
		GenreChallenge,
		GroupChallenge,
		CityChallengeSchool,
		StateChallengeSchool,
		CountryChallengeSchool,
		SchoolChallengeTeacher,
		CityChallengeTeacher,
		StateChallengeTeacher,
		CountryChallengeTeacher,
		VoteChallenge
	;

	public function removeUserUpdate() {
		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->ranking_lib->removeUserUpdate(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_id'),
				$this->input->post('user_id')
			);
		}
	}

	public function getUserActiveChallenge() {
		if (!$this->json) {
			$active_event = $this->event_user_model->get_all([
				'user_id'			=> (int)$this->session->userdata('user_id'),
				'is_active_event'	=> 1,
				'sort'				=> 'event_user.event_id',
				'order'				=> 'DESC',
				'start'				=> 0,
				'limit'				=> 1,
			])['rows'][0] ?? [];

			$top_sold_book = $this->event_order_model->getSoldByBook([
				'user_id'	=> (int)$this->session->userdata('user_id'),
				'event_id'	=> (int)$active_event['event_id'],
				'sort'		=> 'quantity',
				'order'		=> 'DESC',
				'start'		=> 0,
				'limit'		=> 1,
			])['rows'][0] ?? [];

			if ($this->config->item('site_country_code') !== 'IN') {
				$challenge_info = $this->event_challenge_country_model->get_all([
					'event_id'				=> (int)$active_event['event_id'],
					'start_date_le'			=> date('Y-m-d H:i:s'),
					'end_date_ge'			=> date('Y-m-d H:i:s'),
				])['rows'][0] ?? [];

				$event_info = $this->event_model->get($active_event['event_id']);

				$this->json['active_challenge'] = [
					'event_id'			=> $active_event['event_id'],
					'challenge_id'		=> $challenge_info['id'],
					'challenge'			=> $challenge_info,
					'type'				=> 'national',
				];

				$this->json['active_challenge'] = array_merge($this->json['active_challenge'], [
					'layout'		=> 1,
					'term_url'		=> $event_info['url'] . 'dashboard/terms',
					'color'			=> '#ffffff',
				]);

				$this->json['active_challenge']['timer'] = strtotime($challenge_info['end_date']) - time();

				return;
			}

			$event_info = $this->db
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
				->where('event_challenge_school.event_id', (int)$active_event['event_id'])
				->where('event_challenge_school.start_date <= ', date('Y-m-d H:i:s'))
				->where('event_challenge_city.start_date <= ', date('Y-m-d H:i:s'))
				->where('event_challenge_state.start_date <= ', date('Y-m-d H:i:s'))
				->where('event_challenge_country.start_date <= ', date('Y-m-d H:i:s'))
				->get()
				->row_array();

			log_kb(['Event_info' => $event_info, $top_sold_book]);

			if (empty($top_sold_book['quantity'])) {
				$challenge_info = $this->event_challenge_school_model->get($event_info['event_challenge_school_id']);

				$this->json['active_challenge'] = [
					'event_id'			=> $active_event['event_id'],
					'challenge_id'		=> $event_info['event_challenge_school_id'],
					'challenge'			=> $challenge_info,
					'type'				=> 'school',
				];
			} else {
				if (
					$top_sold_book['quantity'] >= $event_info['school_min_sold'] &&
					$top_sold_book['quantity'] <= $event_info['school_max_sold']
				) {
					$challenge_info = $this->event_challenge_school_model->get($event_info['event_challenge_school_id']);

					$this->json['active_challenge'] = [
						'event_id'			=> $active_event['event_id'],
						'challenge_id'		=> $event_info['event_challenge_school_id'],
						'challenge'			=> $challenge_info,
						'type'				=> 'school',
					];
				} elseif (
					$top_sold_book['quantity'] >= $event_info['city_min_sold'] &&
					$top_sold_book['quantity'] <= $event_info['city_max_sold']
				) {
					$challenge_info = $this->event_challenge_city_model->get($event_info['event_challenge_city_id']);

					$this->json['active_challenge'] = [
						'event_id'			=> $active_event['event_id'],
						'challenge_id'		=> $event_info['event_challenge_city_id'],
						'challenge'			=> $challenge_info,
						'type'				=> 'city',
					];
				} elseif (
					$top_sold_book['quantity'] >= $event_info['state_min_sold'] &&
					$top_sold_book['quantity'] <= $event_info['state_max_sold']
				) {
					$challenge_info = $this->event_challenge_state_model->get($event_info['event_challenge_state_id']);

					$this->json['active_challenge'] = [
						'event_id'			=> $active_event['event_id'],
						'challenge_id'		=> $event_info['event_challenge_state_id'],
						'challenge'			=> $challenge_info,
						'type'				=> 'state',
					];
				} elseif (
					$top_sold_book['quantity'] >= $event_info['country_min_sold']
				) {
					$challenge_info = $this->event_challenge_country_model->get($event_info['event_challenge_country_id']);

					$this->json['active_challenge'] = [
						'event_id'			=> $active_event['event_id'],
						'challenge_id'		=> $event_info['event_challenge_country_id'],
						'challenge'			=> $challenge_info,
						'type'				=> 'national',
					];
				}
			}

			$event_info = $this->event_model->get($active_event['event_id']);

			$this->json['active_challenge'] = array_merge($this->json['active_challenge'], [
				'layout'		=> $this->config->item('site_country_code') !== 'IN' ? 1 : 2,
				'color'			=> $this->config->item('site_country_code') !== 'IN' ? '#ffffff' : '#000000',
				'term_url'		=> $event_info['url'] . 'dashboard/terms',
			]);

			$this->json['active_challenge']['timer'] = strtotime($challenge_info['end_date']) - time();
		}
	}

	public function getSchoolRanks() {
		if (!$this->json) {
			$this->load->model('ranking/Ranking_model', 'ranking_model');

			$this->load->model('event/Event_model', 'event_model');
			$this->load->model('event/EventUser_model', 'event_user_model');
			$this->load->model('event/EventSite_model', 'event_site_model');

			$event_info = $this->event_model->get($this->input->post('event_id'));

			if (empty($event_info)) {
				$this->json['error'] = _l('event_not_found');
				return;
			}

			$filter_data = [
				'event_id'		=> $event_info['id'],
				'quantity_ge'	=> 250,
				'start_date'	=> $event_info['start_date'],
				'end_date'		=> date('Y-m-d H:i:s', strtotime('+1 days', strtotime($event_info['end_date']))),
			];

			if ($this->input->post('state_id')) {
				$filter_data['state_id'] = (int)$this->input->post('state_id');
				$filter_data['quantity_ge'] = 100;
			}

			$key = 'school_ranks' . (ENVIRONMENT === 'production' ? '_live_' : '_test_') . implode('_', array_keys($filter_data)) . '_' . implode('_', array_values($filter_data));

			$cache_data = json_decode($this->cache->get($key), true);

			if (ENVIRONMENT === 'production' && !empty($cache_data)) {
				$rankings = $cache_data;
			} else {
				$schools = $this->ranking_model->getSchoolRanks($filter_data)['rows'] ?? [];

				$unsorted_rankings = $rankings = $sort_order = [];

				foreach ($schools as $key => $school) {
					$item = $this->site_model->get($school['site_id']);

					if (strpos($item['site_code'], '-de') !== false) continue;
					if (strpos($item['site_code'], 'NYAFIND2022BB') !== false) continue;

					$city_info = $this->city_model->get($item['city_id']);
					$state_info = $this->state_model->get($item['state_id']);

					$total_students = $this->ranking_model->get_students([
						'site_id'	=> $item['id'],
						'event_id'	=> $event_info['id'],
						'start_date'=> $event_info['start_date'],
						'end_date'	=> date('Y-m-d H:i:s', strtotime('+1 days', strtotime($event_info['end_date']))),
					])['total'];
					$book_written = $this->ranking_model->get_books([
						'site_id'	=> $item['id'],
						'event_id'	=> $event_info['id'],
						'start_date'=> $event_info['start_date'],
						'end_date'	=> date('Y-m-d H:i:s', strtotime('+1 days', strtotime($event_info['end_date']))),
					])['total'];
					$book_published = $this->ranking_model->get_books([
						'site_id'	=> $item['id'],
						'status'	=> 1,
						'event_id'	=> $event_info['id'],
						'start_date'=> $event_info['start_date'],
						'end_date'	=> date('Y-m-d H:i:s', strtotime('+1 days', strtotime($event_info['end_date']))),
					])['total'];
					$total_sold = $this->ranking_model->getTotalSolds([
						'site_id'	=> $item['id'],
						'event_id'	=> $event_info['id'],
						'start_date'=> $event_info['start_date'],
						'end_date'	=> date('Y-m-d H:i:s', strtotime('+1 days', strtotime($event_info['end_date']))),
					]);

					$sort_order[] = $book_written * 0.1 + $book_published * 0.45 + $total_sold * 0.45;
					// $sort_order[] = $total_students * 0.5 + $book_published * 0.3 + $book_written * 0.2;

					$unsorted_rankings[] = [
						'id'			=> $item['id'],
						'site_code'		=> $item['site_code'],
						'rank'			=> 0,
						'name'			=> ucfirst($item['name']),
						'owner_name'	=> ucfirst($item['owner_name']),
						'owner_mobile'	=> $item['owner_mobile'],
						'owner_email'	=> $item['owner_email'],
						'city'			=> $city_info['name'],
						'state'			=> $state_info['name'],
						'book_written'	=> $book_written,
						'book_published'=> $book_published,
						'total_students'=> $total_students,
						'sold'			=> readable_format(!empty($total_sold) ? $total_sold : 0),
					];
				}

				array_multisort($sort_order, SORT_DESC, $unsorted_rankings);

				foreach ($unsorted_rankings as $rank => $item) {
					$rankings[] = array_merge($item, ['rank' => ($rank + 1)]);
				}

				$rankings = array_slice($rankings, 0, 50);

				$this->cache->save($key, json_encode($rankings), 60);
			}

			$this->json['rankings'] = $rankings;
		}
	}

	public function getBestSellerRanks() {
		if (!$this->json) {
			$this->load->model('ranking/Ranking_model', 'ranking_model');

			$event_info = $this->event_model->get($this->input->post('event_id'));

			if (empty($event_info)) {
				$this->json['error'] = _l('event_not_found');
				return;
			}

			$next_league = '/india/dashboard/';

			$league_urls = [];

			$type = (int)$this->input->post('type');

			$filter_data = [
				'event_id'	=> $event_info['id'],
				'start_date'=> date('Y-m-d H:i:s', strtotime('+1 days', strtotime($event_info['start_date']))),
				'end_date'	=> date('Y-m-d H:i:s', strtotime('+1 days', strtotime($event_info['end_date']))),
			];

			$filter_data['quantity_ge'] = 10;
			$filter_data['quantity_le'] = 0;

			if (
				$this->input->post('code') &&
				($site_info = $this->site_model->getByCode($this->input->post('code')))
			) {
				$filter_data['site_id'] = $site_info['id'];
				$filter_data['quantity_ge'] = 10;
				$filter_data['quantity_le'] = 19;

				if (!empty($site_info['city_id'])) {
					$league_urls = ['city', $site_info['city_id']];
				}
			}

			if ($this->input->post('site_id')) {
				$filter_data['site_id'] = (int)$this->input->post('site_id');
				$filter_data['quantity_ge'] = 10;
				$filter_data['quantity_le'] = 19;
			}

			if ($this->input->post('school_id')) {
				$filter_data['site_id'] = (int)$this->input->post('school_id');
				$filter_data['quantity_ge'] = 10;
				$filter_data['quantity_le'] = 19;
			}

			if ($this->input->post('grade_id')) {
				$filter_data['grade_id'] = (int)$this->input->post('grade_id');
				$filter_data['quantity_ge'] = 0;
				$filter_data['quantity_le'] = 9;

				$site_info = $site_info
					? $site_info
					: $this->site_model->get($filter_data['site_id']);

				if (!empty($site_info['site_code'])) {
					$league_urls = [$site_info['site_code']];
				}
			}

			if ($this->input->post('grade')) {
				$filter_data['grade'] = (int)$this->input->post('grade');
				$filter_data['quantity_ge'] = 0;
				$filter_data['quantity_le'] = 9;

				$site_info = $site_info
					? $site_info
					: $this->site_model->get($filter_data['site_id']);

				if (!empty($site_info['site_code'])) {
					$league_urls = [$site_info['site_code']];
				}
			}

			if ($this->input->post('section_id')) {
				$filter_data['section_id'] = (int)$this->input->post('section_id');
				$filter_data['quantity_ge'] = 0;
				$filter_data['quantity_le'] = 9;
			}

			if ($this->input->post('city_id')) {
				$filter_data['city_id'] = (int)$this->input->post('city_id');
				$filter_data['quantity_ge'] = 20;
				$filter_data['quantity_le'] = 39;

				$city_info = $this->city_model->get($this->input->post('city_id'));

				if (!empty($city_info['state_id'])) {
					$league_urls = ['state', $city_info['state_id']];
				}
			}

			if ($this->input->post('state_id')) {
				$filter_data['state_id'] = (int)$this->input->post('state_id');
				$filter_data['quantity_ge'] = 40;
				$filter_data['quantity_le'] = 49;
			}

			if ($this->input->post('search')) {
				$filter_data['search'] = $this->input->post('search');
			}

			if ($this->input->post('page')) {
				$filter_data['start'] = $this->input->post('page') > 0
						? ($this->input->post('page') - 1) * 20
						: 0;
				$filter_data['limit'] = 20;
			}

			$key = 'best_seller' . (ENVIRONMENT === 'production' ? '_live_' : '_test_') . implode('_', array_keys($filter_data)) . '_' . implode('_', array_values($filter_data));

			$result = json_decode($this->cache->get($key), true);

			if (empty($result) || ENVIRONMENT !== 'production') {
				$result = $this->ranking_model->getRanks($filter_data);
				log_kb(['rank' => $result]);
				$this->cache->save($key, json_encode($result), 60);
			}

			$this->json['total'] = $result['total'] ?? 0;

			$rankings = [];

			$rank = 1;
			$total = 0;

			if ($this->input->post('page')) {
				$rank += $this->input->post('page') > 0
					? ($this->input->post('page') - 1) * 20
					: 0;
			}

			foreach ($result['rows'] ?? [] as $key => $item) {
				// if (empty($this->input->post('grade')) && empty($item['quantity'])) continue;

				if ($type == 1 && $item['quantity'] < 30) continue;
				if ($type == 2 && ($item['quantity'] < 10 || $item['quantity'] >= 30)) continue;
				if ($type == 3 && ($item['quantity'] >= 10 || $item['quantity'] < 1)) continue;

				$order_total 	= $this->order_model->getTotalProductsByProductId($item['id']);
				$user_info 		= $this->student_model->get($item['user_id']);
				// $grade_info 	= $this->grade_model->get($user_info['grade_id']);
				// $section_info 	= $this->section_model->get($user_info['section_id']);
				$user_site_info = $this->site_model->get($user_info['site_id']);
				$state_info 	= $this->state_model->get($user_info['state_id']);
				$city_info 		= $this->city_model->get($user_info['city_id']);

				if (!empty($item['quantity']) && $rank < 4) {
					$top_rankers[] = [
						'id'			=> $item['id'],
						'rank'			=> $rank,
						'name'			=> ucfirst($item['name']),
						'cover_image'	=> $item['cover_image'],
						'author_name'	=> $item['author_name'],
						'author_image'	=> $item['author_image'],
						'slug'			=> $item['slug'],
						'state'			=> $state_info['name'],
						'city'			=> $city_info['name'],
						'school'		=> $user_site_info['name'],
						'grade'			=> $user_info['grade'],
						'section'		=> $user_info['section'],
						'royalty'		=> 0,
						'sold'			=> !empty($item['quantity']) ? $item['quantity'] : 0,
						'total_sold'	=> $order_total ? $order_total : 0,
					];
					$rank++;
					continue;
				}

				$ranking = [
					'id'			=> $item['id'],
					'rank'			=> $rank < 4 ? $rank + 2 : $rank,
					'name'			=> ucfirst($item['name']),
					'cover_image'	=> $item['cover_image'],
					'author_name'	=> $item['author_name'],
					'author_image'	=> $item['author_image'],
					'slug'			=> $item['slug'],
					'state'			=> $state_info['name'],
					'city'			=> $city_info['name'],
					'school'		=> $user_site_info['name'],
					'grade'			=> $user_info['grade'],
					'section'		=> $user_info['section'],
					'royalty'		=> 0,
					'sold'			=> !empty($item['quantity']) ? $item['quantity'] : 0,
					'total_sold'	=> $order_total ? $order_total : 0,
				];

				$rankings[] = $ranking;
				// $rankings[] = self::_addRatingAndSold($ranking);

				$rank++;

				$total += !empty($item['quantity']) ? $item['quantity'] : 0;
			}

			if (
				$type != 3
				&& empty($this->input->post('search'))
				&& (empty($this->input->post('page')) || $this->input->post('page') == 1)
			) {
				$top_rankers 	= !empty($top_rankers) ? $top_rankers : [];

				$first_rank 	= array_shift($top_rankers);
				$second_rank 	= array_shift($top_rankers);
				$third_rank 	= array_shift($top_rankers);

				$this->json['top_rankers'] = [
					'first'		=> $first_rank,
					'second'	=> $second_rank,
					'third'		=> $third_rank,
				];
			}

			$this->json['rankings'] = empty($this->input->post('search'))
				? $rankings
				: array_merge($top_rankers ?? [], $rankings ?? []);
			// $this->json['total_copies'] = $total;

			// $this->json['info']['heading'] = sprintf(_li('The %s Best Sellers'), $site_info['name'] ?? 'BriBooks');
			$this->json['info']['heading'] = _li('The BriBooks Best Sellers League');
			$this->json['info']['subheading'] = sprintf(_li('of %s'), $site_info['name'] ?? 'India');

			if ($this->input->post('city_id')) {
				$city_info = $this->city_model->get($this->input->post('city_id'));
				$this->json['info']['subheading'] = sprintf(_li('of %s'), $city_info['name'] ?? 'India');
			}

			if ($this->input->post('state_id')) {
				$state_info = $this->state_model->get($this->input->post('state_id'));
				$this->json['info']['subheading'] = sprintf(_li('of %s'), $state_info['name'] ?? 'India');
			}

			if ($this->input->post('grade')) {
				$this->json['info']['subheading'] = sprintf(_li('of Grade %s, %s'), (int)$this->input->post('grade'), $site_info['name'] ?? 'India');
			}

			$this->json['info']['next_league'] = $next_league . implode('/', $league_urls);
		}
	}

	public function getAllRankings() {
		if (!$this->json) {
			$type = (int)$this->input->post('type');

			$this->load->library('Royalty_lib', 'royalty_lib');

			$filter_data = [
				'site_code'	=> 'NYAFIND2022',
				// 'end_date'	=> $competition_info['end_date'] ?? '', // '2022-11-03 21:00:00',
			];

			if (
				$this->input->post('code') &&
				($site_info = $this->site_model->getByCode($this->input->post('code')))
			) {
				$filter_data['site_id'] = $site_info['id'];
			}

			$competition_info = $this->competition_model->get_all([
				'site_id'	=> 0,
			])['rows'][0] ?? [];

			if ($this->input->post('state_id')) {
				$filter_data['state_id'] = (int)$this->input->post('state_id');
			}

			if ($this->input->post('city_id')) {
				$filter_data['city_id'] = (int)$this->input->post('city_id');
			}

			if ($this->input->post('site_id')) {
				$filter_data['site_id'] = (int)$this->input->post('site_id');
			}

			if ($this->input->post('school_id')) {
				$filter_data['site_id'] = (int)$this->input->post('school_id');
			}

			if ($this->input->post('grade_id')) {
				$filter_data['grade_id'] = (int)$this->input->post('grade_id');
			}

			if ($this->input->post('grade')) {
				$filter_data['grade'] = (int)$this->input->post('grade');
			}

			if ($this->input->post('section_id')) {
				$filter_data['section_id'] = $this->input->post('section_id');
			}

			if ($this->input->post('search')) {
				$filter_data['search'] = $this->input->post('search');
			}

			if ($this->input->post('page')) {
				$filter_data['start'] = $this->input->post('page') > 0
						? ($this->input->post('page') - 1) * 50
						: 0;
				$filter_data['limit'] = 50;
			}

			$key = implode('_', array_keys($filter_data)) . '_' . implode('_', array_values($filter_data));

			if ($cache_data = $this->cache->get($key) && false) {
				$result = json_decode($cache_data, true);
			} else {
				$result = $this->order_model->getTopSoldBooks($filter_data);
				$this->cache->save($key, json_encode($result), 60);
			}

			$this->json['total'] = $result['total'] ?? 0;

			$rankings = [];

			$rank = 1;
			$total = 0;

			if ($this->input->post('page')) {
				$rank += $this->input->post('page') > 0
					? ($this->input->post('page') - 1) * 50
					: 0;
			}

			foreach ($result['rows'] ?? [] as $key => $item) {
				// if (empty($item['quantity'])) continue;

				if ($type == 1 && $item['quantity'] < 30) continue;
				if ($type == 2 && ($item['quantity'] < 10 || $item['quantity'] >= 30)) continue;
				if ($type == 3 && ($item['quantity'] >= 10 || $item['quantity'] < 1)) continue;

				$order_total 	= $this->order_model->getTotalProductsByProductId($item['id']);
				$user_info 		= $this->student_model->get($item['user_id']);
				// $grade_info 	= $this->grade_model->get($user_info['grade_id']);
				// $section_info 	= $this->section_model->get($user_info['section_id']);
				$user_site_info = $this->site_model->get($user_info['site_id']);
				$state_info 	= $this->state_model->get($user_info['state_id']);
				$city_info 		= $this->city_model->get($user_info['city_id']);

				if (!empty($item['quantity']) && $rank < 4) {
					$top_rankers[] = [
						'id'			=> $item['id'],
						'rank'			=> $rank,
						'name'			=> ucfirst($item['name']),
						'cover_image'	=> $item['cover_image'],
						'author_name'	=> $item['author_name'],
						'author_image'	=> $item['author_image'],
						'slug'			=> $item['slug'],
						'state'			=> $state_info['name'],
						'city'			=> $city_info['name'],
						'school'		=> $user_site_info['name'],
						'grade'			=> $user_info['grade'],
						'section'		=> $user_info['section'],
						'royalty'		=> 0, // currency($this->royalty_lib->getBookTotalRoyality($item['id']), 0),
						'sold'			=> readable_format(!empty($item['quantity']) ? $item['quantity'] : 0),
						'total_sold'	=> readable_format($order_total ? $order_total : 0),
					];
					$rank++;
					continue;
				}

				$ranking = [
					'id'			=> $item['id'],
					'rank'			=> $rank < 4 ? $rank + 2 : $rank,
					'name'			=> ucfirst($item['name']),
					'cover_image'	=> $item['cover_image'],
					'author_name'	=> $item['author_name'],
					'author_image'	=> $item['author_image'],
					'slug'			=> $item['slug'],
					'state'			=> $state_info['name'] ?? '',
					'city'			=> $city_info['name'] ?? '',
					'school'		=> $user_site_info['name'],
					'grade'			=> $user_info['grade'],
					'section'		=> $user_info['section'],
					'royalty'		=> 0, // currency($this->royalty_lib->getBookTotalRoyality($item['id']), 0),
					'sold'			=> readable_format(!empty($item['quantity']) ? $item['quantity'] : 0),
					'total_sold'	=> readable_format($order_total ? $order_total : 0),
				];

				$rankings[] = $ranking;
				// $rankings[] = self::_addRatingAndSold($ranking);

				$rank++;

				$total += !empty($item['quantity']) ? $item['quantity'] : 0;
			}

			if (
				$type != 3
				&& empty($this->input->post('search'))
				&& (empty($this->input->post('page')) || $this->input->post('page') == 1)
			) {
				$top_rankers 	= !empty($top_rankers) ? $top_rankers : [];

				$first_rank 	= array_shift($top_rankers);
				$second_rank 	= array_shift($top_rankers);
				$third_rank 	= array_shift($top_rankers);

				$this->json['top_rankers'] = [
					'first'		=> $first_rank,
					'second'	=> $second_rank,
					'third'		=> $third_rank,
				];
			}

			$this->json['rankings'] = empty($this->input->post('search'))
				? $rankings
				: array_merge($top_rankers ?? [], $rankings ?? []);
			// $this->json['total_copies'] = $total;

			$this->json['info']['heading'] = sprintf(_li('The %s Best Sellers'), $site_info['name'] ?? 'BriBooks');
			$this->json['info']['subheading'] = sprintf(_li('Authoritatively Ranked lists of books of %s Authors sold on'), $site_info['name'] ?? 'Indian');

			if ($this->input->post('grade')) {
				$this->json['info']['subheading'] = sprintf(_li('Authoritatively Ranked list of books of Grade %s of %s Authors sold on'), (int)$this->input->post('grade'), $site_info['name'] ?? 'Indian');
			}
		}
	}

	public function getRankings() {
		if (!$this->json) {
			$type = (int)$this->input->post('type');

			if (!($site_info = $this->site_model->getByCode($this->input->post('code')))) {
				$this->json['error'] = _li('invalid_franchise_code');
				return;
			}

			$this->load->library('Royalty_lib', 'royalty_lib');

			$competition_info = $this->competition_model->get_all([
				'site_id'	=> $site_info['id'],
			])['rows'][0] ?? [];

			$result = $this->order_model->getTopSoldBooks([
				'site_id'	=> $site_info['id'],
				'end_date'	=> $competition_info['end_date'] ?? '', // '2022-11-03 21:00:00',
			])['rows'] ?? [];

			$rankings = [];

			$rank = 1;
			$total = 0;

			foreach ($result ?? [] as $key => $item) {
				// if (empty($item['quantity'])) continue;

				if ($type == 1 && $item['quantity'] < 30) continue;
				if ($type == 2 && ($item['quantity'] < 10 || $item['quantity'] >= 30)) continue;
				if ($type == 3 && ($item['quantity'] >= 10 || $item['quantity'] < 1)) continue;

				$order_total = $this->order_model->getTotalProductsByProductId($item['id']);

				$ranking = [
					'id'			=> $item['id'],
					'rank'			=> $rank,
					'name'			=> ucfirst($item['name']),
					'cover_image'	=> $item['cover_image'],
					'author_name'	=> $item['author_name'],
					'author_image'	=> $item['author_image'],
					'slug'			=> $item['slug'],
					'royalty'		=> currency($this->royalty_lib->getBookTotalRoyality($item['id']), 0),
					'sold'			=> readable_format(!empty($item['quantity']) ? $item['quantity'] : 0),
					'total_sold'	=> readable_format($order_total ? $order_total : 0),
				];

				$rankings[] = $ranking;
				// $rankings[] = self::_addRatingAndSold($ranking);

				$rank++;

				$total += !empty($item['quantity']) ? $item['quantity'] : 0;
			}

			if ($type != 3) {
				$first_rank 	= array_shift($rankings);
				$second_rank 	= array_shift($rankings);
				$third_rank 	= array_shift($rankings);

				$this->json['top_rankers'] = [
					'first'		=> $first_rank,
					'second'	=> $second_rank,
					'third'		=> $third_rank,
				];
			}

			$this->json['rankings'] = $rankings;
			$this->json['total'] = $total;
			$this->json['info']['heading'] = sprintf(_li('The %s Best Sellers'), $site_info['name']);
			$this->json['info']['subheading'] = sprintf(_li('Authoritatively Ranked lists of books of %s Authors sold on'), $site_info['name']);
		}
	}

	public function getSchoolDashboard() {
		$this->json['error'] = _l('not_allowed');

		if (!$this->json) {
			if (!($site_info = $this->site_model->getByCode($this->input->post('code')))) {
				$this->json['error'] = _li('invalid_franchise_code');
				return;
			}

			$this->json['data']['heading'] = sprintf(_li('Leading Classes in %s'), $site_info['name']);

			// Competition
			$competition_info = $this->competition_model->get_all([
				'site_id'	=> $site_info['id'],
			])['rows'][0] ?? [];

			$this->json['data']['competition'] = [
				'name'			=> $competition_info['name'],
				'status'		=> $competition_info['status'],
				'site_id'		=> $competition_info['site_id'],
				'start_date'	=> $competition_info['start_date'],
				'end_date'		=> $competition_info['end_date'],
			];

			// Section Wise Rankings
			$this->load->model('common/Section_model', 'section_model');
			$this->load->model('common/Grade_model', 'grade_model');

			$results = $this->book_model->getRankingBySections([
				'site_id'	=> $site_info['id'],
			]) ?? [];

			$class_rankings = [];

			foreach ($results as $rank => $item) {
				// if (empty($item['quantity'])) continue;
				// $section_info = $this->section_model->get($item['section_id']);
				// $grade_info = $this->grade_model->get($section_info['grade_id']);

				$class_rankings[] = [
					'rank'			=> $rank + 1,
					'section'		=> $item['section'],
					'grade'			=> $item['grade'],
					'books'			=> $item['books_quantity'],
					'users'			=> $item['users_quantity'],
				];
			}

			$this->json['data']['class_rankings'] = $class_rankings;

			// Rankings
			$this->load->library('Royalty_lib', 'royalty_lib');

			$result = $this->order_model->getTopSoldBooks([
				'site_id'	=> $site_info['id'],
			]);

			$rankings = [];

			foreach ($result ?? [] as $rank => $item) {
				// if (empty($item['quantity'])) continue;

				$ranking = [
					'id'			=> $item['id'],
					'rank'			=> $rank + 1,
					'name'			=> ucfirst($item['name']),
					'cover_image'	=> $item['cover_image'],
					'author_name'	=> $item['author_name'],
					'author_image'	=> $item['author_image'],
					'slug'			=> $item['slug'],
					'royalty'		=> currency($this->royalty_lib->getBookTotalRoyality($item['id']), 0),
					'sold'			=> readable_format(!empty($item['quantity']) ? $item['quantity'] : 0),
				];

				$rankings[] = self::_addRatingAndSold($ranking);
			}

			$first_rank 	= array_shift($rankings);
			$second_rank 	= array_shift($rankings);
			$third_rank 	= array_shift($rankings);

			$this->json['data']['top_rankers'] = [
				'first'		=> $first_rank,
				'second'	=> $second_rank,
				'third'		=> $third_rank,
			];

			$this->json['data']['rankings'] = $rankings;
			$this->json['data']['info']['heading'] = sprintf(_li('The %s Best Sellers'), $site_info['name']);
			$this->json['data']['info']['subheading'] = sprintf(_li('Authoritatively Ranked lists of books of %s Authors sold on'), $site_info['name']);

			// Total data
			$this->json['data']['total'] = [
				'users' => $this->student_model->get_all([
					'site_id'	=> $site_info['id'],
				])['total'],
				'books' => $this->book_model->get_all([
					'site_id'	=> $site_info['id'],
					'ne_status'	=> 0
				])['total'],
			];

			// Royalty
			$results = $this->author_earning_model->get_all([
				'user_site_id'	=> $site_info['id'],
			])['rows'] ?? [];

			$total = $pending = $paid = 0;

			foreach ($results as $earning) {
				$total += $earning['amount'];

				if ($earning['status'] == 0) {
					$pending += $earning['amount'];
				}

				if ($earning['status'] == 1) {
					$paid += $earning['amount'];
				}
			}

			$this->json['data']['royalty'] = [
				'total'		=> currency($total, 0),
				'pending'	=> currency($pending, 0),
				'paid'		=> currency($paid, 0),
			];

			// Jury Winner
			$this->json['data']['info']['winnerHeading'] = sprintf(_li('%s Jury Award'), $site_info['name']);
			$this->json['data']['info']['winnerSubheading'] = sprintf(_li('Authoritatively Ranked lists of books of %s Authors sold on'), $site_info['name']);

			// $this->json['data']['winners'] = [
			// 	'first'		=> $first_rank,
			// 	'second'	=> $second_rank,
			// 	'third'		=> $third_rank,
			// ];
		}
	}

	public function getActiveDailyChallenge() {
		if (empty($this->input->post('event_id'))) return;

		$this->json['status'] = false;

		$this->load->model('event/EventChallengeDaily_model', 'event_challenge_daily_model');

		$event_challenge_daily_results = $this->event_challenge_daily_model->get_all([
			'event_id'				=> (int)$this->input->post('event_id'),
			'event_challenge_id'	=> !empty($this->input->post('event_challenge_id')) ? (int)$this->input->post('event_challenge_id') : 3,
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'display_end_date_ge'	=> date('Y-m-d H:i:s'),
		])['rows'] ?? [];

		$daily_challenge = [];

		if (!empty($event_challenge_daily_results[0])) {
			if (time() < strtotime($event_challenge_daily_results[0]['end_date'])) {
				$image_url = 'https://cms.bribooks.com/assets/images/event_challenges_sc/dc_week3_day1.png';

				$html = vsprintf(_li('<div>Sell <span class="text-success"> %s Copies </span> today & get a chance to do a podcast with <span class="text-warning"> Ami Dror</span></div>'), [
					$event_challenge_daily_results[0]['book_sold']
				]);
			} else {
				$image_url = '';

				$html = _li('<div><span class="text-center fs-5">Closed Now.</span></div>');
			}

			$daily_challenge['html'] = $html;
			$daily_challenge['image_url'] = $image_url;

			$this->json['status'] = true;
		}

		$this->json['daily_challenge'] = $daily_challenge;
	}
}
