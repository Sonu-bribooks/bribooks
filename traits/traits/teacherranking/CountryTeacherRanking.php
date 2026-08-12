<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CountryTeacherRanking {
	public function updateCountryRank($data = []) {
		$book_info 		= $data['book_info'] ?? [];
		$event_info 	= $data['event_info'] ?? [];
		$author_info 	= $this->student_model->get($book_info['user_id']);
		$teacher_info 	= $this->teacher_model->get_all([
			'site_id'	=> $author_info['site_id'],
			'grade'		=> $author_info['grade'],
			'section'	=> $author_info['section'],
		])['rows'][0] ?? [];
		$school_info 	= $this->site_model->get($author_info['site_id']);

		log_kb(compact('book_info', 'event_info', 'author_info', 'school_info', 'teacher_info'));

		if (empty($book_info) || empty($event_info) || empty($school_info) || empty($teacher_info) || ($event_info['direct_site_id'] == $school_info['id'])) {
			return;
		}

		if (empty($this->event_site_model->get_all([
			'event_id' 	=> $event_info['id'],
			'site_id' 	=> $school_info['id'],
		])['rows'][0] ?? '')) {
			return;
		}

		$challenge_info = $this->event_challenge_country_model->get_all([
			'type'					=> 'teacher',
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (empty($challenge_info)) return;

		$no_published 	= self::_getPublishedCount([
			'event_id'	=> $event_info['id'],
			'site_id'	=> $teacher_info['site_id'],
			'grade'		=> $teacher_info['grade'],
			'section'	=> $teacher_info['section'],
		]);
		$total_students = self::_getRegisteredCount([
			'event_id'	=> $event_info['id'],
			'site_id'	=> $teacher_info['site_id'],
			'grade'		=> $teacher_info['grade'],
			'section'	=> $teacher_info['section'],
		]);

		log_kb(compact('challenge_info', 'no_published'));

		if (empty($challenge_info['min_published']) || $no_published < $challenge_info['min_published']) return;
		if (!empty($challenge_info['max_published']) && $no_published > $challenge_info['max_published']) return;

		if ($rank_info = $this->ranking_country_model->get_all([
			'event_challenge_country_id'=> (int)$challenge_info['id'],
			'event_id'					=> (int)$event_info['id'],
			'teacher_id'				=> (int)$teacher_info['id'],
		])['rows'][0] ?? []) {
			$rank_id = $rank_info['id'];

			$this->ranking_country_model->edit($rank_info['id'], [
				'score'					=> $no_published,
				'total_students'		=> $total_students,
			]);
		} else {
			$country_info = $this->country_model->get_all([
				'code'	=> $school_info['country_code'] ?? 'IN',
			])['rows'][0] ?? [];

			$city_info = $this->city_model->get($school_info['city_id']);

			$rank_id = $this->ranking_country_model->add([
				'country_id'				=> (int)$country_info['id'] ?? 1,
				'event_id'					=> (int)$event_info['id'],
				'event_challenge_country_id'=> (int)$challenge_info['id'],
				'teacher_id'				=> (int)$teacher_info['id'],
				'school_id'					=> (int)$teacher_info['site_id'],
				'school_name'				=> $school_info['name'],
				'name'						=> $teacher_info['first_name'] . ' ' . $teacher_info['last_name'],
				'email'						=> $teacher_info['email'],
				'mobile'					=> $teacher_info['mobile'],
				'grade'						=> $teacher_info['grade'],
				'section'					=> $teacher_info['section'],
				'city'						=> $city_info['name'],
				'state'						=> $city_info['state'],
				'book_published'			=> $no_published,
				'total_students'			=> $total_students,
				'score'						=> $no_published,
			]);
		}

		log_kb(compact('rank_id', 'no_published'));

		self::_pushUpdate($rank_id, $no_published, 'country');
	}

	public function getCountryRanks($event_id = 0, $event_challenge_country_id = 0, $country_id = 0, $page = 1, $search = NULL) {
		$ranks 		= [];
		$rank_key 	= self::_getKey(compact('event_id', 'event_challenge_country_id', 'country_id'), 'country');
		$start 		= $page > 0 ? ($page - 1) * $this->limit : 0;
		$end 		= $start + $this->limit - 1;

		if (!empty($search)) {
			$results = [];

			$rank_results = $this->ranking_country_model->get_all([
				'event_id'					=> (int)$event_id,
				'event_challenge_country_id'=> (int)$event_challenge_country_id,
				'country_id'				=> (int)$country_id,
				'search'					=> $search,
				'is_moved'					=> 0,
				'start'						=> $page > 0 ? ($page - 1) * $this->limit : 0,
				'limit'						=> $this->limit
			]);

			$total = $rank_results['total'];

			foreach ($rank_results['rows'] ?? [] as $item) {
				$results[$item['id']] = $item;
			}
		} else {
			$results 	= $this->redis_lib->getRanks($rank_key, $start, $end);
			$total 		= $this->redis_lib->getTotal($rank_key);
		}

		foreach ($results as $rank_id => $item) {
			$rank_info 	= $this->ranking_country_model->get($rank_id);
			$ranks[] 	= self::_formatCountryRank(
				$this->redis_lib->getRank($rank_key, $rank_id) + 1,
				$rank_info
			);
		}

		log_kb(['Ranking_lib::getCountryRanks::ranks::' => [$results, $ranks]]);

		return ['ranks' => $ranks, 'total' => $total];
	}

	public function getTeacherCountryRank($event_id = 0, $event_challenge_country_id = 0, $country_id = 0, $user_id = 0) {
		$rank_key 	= self::_getKey(compact('event_id', 'event_challenge_country_id', 'country_id'), 'country');

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_country_id'=> (int)$event_challenge_country_id,
			'country_id'				=> (int)$country_id,
			'teacher_id'				=> (int)$user_id,
		];

		$user_rank 	= $this->ranking_country_model->get_all($filter_data)['rows'][0] ?? [];
		$rank 		= $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);
		$rank 		+= 1;

		if (!empty($rank) && !empty($user_rank)) {
			$user_rank['rank'] = $rank ?? 0;
		}

		log_kb(['user_rank' => $user_rank]);

		$user_rank = !empty($user_rank)
			? self::_formatCountryRank($rank , $user_rank)
			: self::_genCountryRank(compact('event_id', 'event_challenge_country_id', 'country_id', 'user_id'), 'country')
		;

		return $user_rank;
	}

	private function _genCountryRank($rank_info = [], $type = 'country') {
		if (empty($this->event_teacher_model->get_all([
			'event_id'		=> $rank_info['event_id'],
			'teacher_id'	=> $rank_info['user_id'],
		])['total'])) return;

		$teacher_info 	= $this->teacher_model->get($rank_info['user_id']);
		$school_info 	= $this->site_model->get($teacher_info['site_id']);
		$city_info 		= $this->city_model->get($school_info['city_id']);

		return self::_formatCountryRank(0, [
			'id'						=> 0,
			'rank'						=> 0,
			'country_id'				=> $school_info['country_id'],
			'event_id'					=> $rank_info['event_id'],
			'event_challenge_country_id'=> $rank_info['event_challenge_country_id'],
			'teacher_id'				=> $teacher_info['id'],
			'school_id'					=> $school_info['id'],
			'school_name'				=> $school_info['name'],
			'name'						=> $teacher_info['first_name'] . ' ' . $teacher_info['last_name'],
			'grade'						=> $teacher_info['grade'],
			'section'					=> $teacher_info['section'],
			'city'						=> $city_info['name'],
			'state'						=> $city_info['state'],
			'score'						=> self::_getPublishedCount([
				'event_id'	=> $rank_info['event_id'],
				'site_id'	=> $teacher_info['site_id'],
				'grade'		=> $teacher_info['grade'],
				'section'	=> $teacher_info['section'],
			]),
			'total_students'			=> self::_getRegisteredCount([
				'event_id'	=> $rank_info['event_id'],
				'site_id'	=> $teacher_info['site_id'],
				'grade'		=> $teacher_info['grade'],
				'section'	=> $teacher_info['section'],
			]),
		]);
	}

	public function getCountryUpdate($event_id = 0, $event_challenge_country_id = 0, $country_id = 0, $user_id = 0) {
		self::_updateLiveUser(
			compact('event_id', 'event_challenge_country_id', 'country_id', 'user_id'),
			$user_id,
			'country'
		);

		self::_pushSSE(
			compact('event_id', 'event_challenge_country_id', 'country_id', 'user_id'),
			$user_id,
			'country'
		);
	}

	private function _formatCountryRank($rank = 0, $item = []) {
		return [
			'id'						=> $item['id'],
			'rank'						=> $rank,
			'country_id'				=> $item['country_id'],
			'event_id'					=> $item['event_id'],
			'event_challenge_country_id'=> $item['event_challenge_country_id'],
			'teacher_id'				=> $item['teacher_id'],
			'school_id'					=> $item['school_id'],
			'school_name'				=> $item['school_name'],
			'name'						=> $item['name'],
			'grade'						=> $item['grade'],
			'section'					=> $item['section'],
			'city'						=> $item['city'],
			'state'						=> $item['state'],
			'score'						=> $item['score'],
			'registered'				=> $item['total_students'] ?? 0,
			'message' 					=> self::_getCountryMessage(array_merge($item, [
				'rank'					=> $rank,
			])),
		];
	}

	private function _getCountryMessage($rank_info = []) {
		$no_published = !empty($rank_info['score'])
			? $rank_info['score']
			: self::_getPublishedCount([
				'event_id'	=> $rank_info['id'],
				'site_id'	=> $rank_info['school_id'],
				'grade'		=> $rank_info['grade'],
				'section'	=> $rank_info['section'],
			]);
		;

		$challenge_info = $this->event_challenge_country_model->get($rank_info['event_challenge_country_id']);

		if (!empty($challenge_info) && date('Y-m-d H:i:s') > $challenge_info['end_date']) {
			return sprintf(_li('%s is closed now!'), $challenge_info['name']);
		}

		return method_exists($this, sprintf('_getCountryEventMessage_%s', $rank_info['event_id']))
			? self::{sprintf('_getCountryEventMessage_%s', $rank_info['event_id'])}($no_published, $rank_info, $challenge_info)
			: self::_getCountryEventMessage($no_published, $rank_info, $challenge_info);
	}

	private function _getCountryEventMessage($no_published = 0, $rank_info = [], $challenge_info = []) {
		if (!empty($challenge_info['max_published']) && $no_published > $challenge_info['max_published'] && !empty($rank_info['rank'])) {
			return vsprintf(_li('Congratulations! Your school has moved to the %s League with %s+ published authors. You’ve earned the Literary Progression Award at %s.'), [
				$rank_info['country'],
				$challenge_info['max_published'] + 1,
				$rank_info['country'] ?? ''
			]);
		}

		if (!empty($challenge_info['max_published']) && $no_published < $challenge_info['max_published'] && !empty($rank_info['rank'])) {
			return vsprintf(_li('You need at least %s published %s to be country-level winner.'), [
				($challenge_info['max_published'] - $no_published),
				self::_getCopyText(($challenge_info['max_published'] - $no_published + 1)),
				$rank_info['country'] ?? ''
			]);
		}

		if (!empty($rank_info['rank']) && $rank_info['rank'] > 3) {
			$top_score 		= self::_getRankScore(3, $rank_info, false, 'country');
			$required_score = $top_score - $no_published + 1;

			return vsprintf(_li('You need at least %s published %s to be in the %s Top 3 Literary Leaders'), [
				$required_score,
				self::_getCopyText($required_score),
				$rank_info['country'],
			]);
		}

		if (empty($rank_info['rank'])) {
			return vsprintf(_li('Your school has not yet reached the Country League. You need at least %s published %s to qualify for country-level rankings.'), [
				($challenge_info['min_published'] - $no_published),
				self::_getCopyText(($challenge_info['min_published'] - $no_published)),
			]);
		}
	}
}
