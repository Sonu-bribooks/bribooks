<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CitySchoolRanking {
	public function updateCityRank($data = []) {
		$book_info 		= $data['book_info'] ?? [];
		$event_info 	= $data['event_info'] ?? [];
		$author_info 	= $this->student_model->get($book_info['user_id']);
		$school_info 	= $this->site_model->get($author_info['site_id']);

		log_kb(compact('book_info', 'event_info', 'author_info', 'school_info'));

		if (empty($book_info) || empty($event_info) || empty($school_info) || ($event_info['direct_site_id'] == $school_info['id'])) {
			return;
		}

		if (empty($this->event_site_model->get_all([
			'event_id' 	=> $event_info['id'],
			'site_id' 	=> $school_info['id'],
		])['rows'][0] ?? '')) {
			return;
		}

		$challenge_info = $this->event_challenge_city_model->get_all([
			'type'					=> 'school',
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (empty($challenge_info)) return;

		$no_published 	= self::_getPublishedCount($event_info['id'], $author_info['site_id']);
		$total_students = self::_getRegisteredCount($event_info['id'], $author_info['site_id']);

		log_kb(compact('challenge_info', 'no_published'));

		if ($no_published < $challenge_info['min_published']) return;
		if (!empty($challenge_info['max_published']) && $no_published > $challenge_info['max_published']) return;

		if ($rank_info = $this->ranking_city_model->get_all([
			'event_challenge_city_id'	=> (int)$challenge_info['id'],
			'event_id'					=> (int)$event_info['id'],
			'school_id'					=> (int)$school_info['id'],
		])['rows'][0] ?? []) {
			$rank_id = $rank_info['id'];

			$this->ranking_city_model->edit($rank_info['id'], [
				'score'					=> $no_published,
				'total_students'		=> $total_students,
			]);
		} else {
			$city_info = $this->city_model->get($school_info['city_id']);

			$rank_id = $this->ranking_city_model->add([
				'event_challenge_city_id'	=> (int)$challenge_info['id'],
				'city_id'					=> (int)$school_info['city_id'],
				'event_id'					=> (int)$event_info['id'],
				'school_id'					=> (int)$school_info['id'],
				'school_code'				=> $school_info['site_code'],
				'name'						=> $school_info['name'],
				'owner_name'				=> $school_info['owner_name'],
				'owner_email'				=> $school_info['owner_email'],
				'owner_mobile'				=> $school_info['owner_mobile'],
				'city'						=> $city_info['name'],
				'state'						=> $city_info['state'],
				'book_published'			=> $no_published,
				'total_students'			=> $total_students,
				'score'						=> $no_published,
			]);
		}

		log_kb(compact('rank_id', 'no_published'));

		self::_pushUpdate($rank_id, $no_published, 'city');
	}

	public function getCityRanks($event_id = 0, $event_challenge_city_id = 0, $city_id = 0, $page = 1, $search = NULL) {
		$ranks 		= [];
		$rank_key 	= self::_getKey(compact('event_id', 'event_challenge_city_id', 'city_id'), 'city');
		$start 		= $page > 0 ? ($page - 1) * $this->limit : 0;
		$end 		= $start + $this->limit - 1;

		if (!empty($search)) {
			$results = [];

			$rank_results = $this->ranking_city_model->get_all([
				'event_id'					=> (int)$event_id,
				'event_challenge_city_id'	=> (int)$event_challenge_city_id,
				'city_id'					=> (int)$city_id,
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
			$rank_info 	= $this->ranking_city_model->get($rank_id);
			$ranks[] 	= self::_formatCityRank(
				$this->redis_lib->getRank($rank_key, $rank_id) + 1,
				$rank_info
			);
		}

		log_kb(['Ranking_lib::getCityRanks::ranks::' => [$results, $ranks]]);

		return ['ranks' => $ranks, 'total' => $total];
	}

	public function getSchoolCityRank($event_id = 0, $event_challenge_city_id = 0, $city_id = 0, $user_id = 0) {
		$rank_key 	= self::_getKey(compact('event_id', 'event_challenge_city_id', 'city_id'), 'city');

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_city_id'	=> (int)$event_challenge_city_id,
			'city_id'					=> (int)$city_id,
			'school_id'					=> (int)$user_id,
		];

		$user_rank 	= $this->ranking_city_model->get_all($filter_data)['rows'][0] ?? [];
		$rank 		= $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);
		$rank 		+= 1;

		log_kb([
			'filter_data' => $filter_data,
			'user_rank' => $user_rank,
			'rank' => $rank,
			'rank_key' => $rank_key,
		]);

		if (!empty($rank) && !empty($user_rank)) {
			$user_rank['rank'] = $rank ?? 0;
		}

		log_kb(['user_rank-new' => $user_rank]);

		$user_rank = !empty($user_rank)
			? self::_formatCityRank($rank , $user_rank)
			: self::_genCityRank(compact('event_id', 'event_challenge_city_id', 'city_id', 'user_id'), 'city')
		;

		log_kb(['user_rank-data' => $user_rank]);

		return $user_rank;
	}

	private function _genCityRank($rank_info = [], $type = 'city') {
		log_kb(['_genCityRank' => $rank_info]);
		if (empty($this->event_site_model->get_all([
			'event_id'	=> $rank_info['event_id'],
			'site_id'	=> $rank_info['user_id'],
		])['total'])) return;

		$school_info 	= $this->site_model->get($rank_info['user_id']);
		$city_info 		= $this->city_model->get($school_info['city_id']);

		return self::_formatCityRank(0, [
			'id'						=> 0,
			'rank'						=> 0,
			'event_challenge_city_id'	=> $rank_info['event_challenge_city_id'],
			'event_id'					=> $rank_info['event_id'],
			'name'						=> $school_info['name'],
			'school_id'					=> $school_info['id'],
			'school_code'				=> $school_info['site_code'],
			'city_id'					=> $school_info['city_id'],
			'city'						=> $city_info['name'],
			'state'						=> $city_info['state'],
			'score'						=> self::_getPublishedCount($rank_info['event_id'], $school_info['id']),
			'total_students'			=> self::_getRegisteredCount($rank_info['event_id'], $school_info['id']),
		]);
	}

	public function getCityUpdate($event_id = 0, $event_challenge_city_id = 0, $city_id = 0, $user_id = 0) {
		self::_updateLiveUser(
			compact('event_id', 'event_challenge_city_id', 'city_id', 'user_id'),
			$user_id,
			'city'
		);

		self::_pushSSE(
			compact('event_id', 'event_challenge_city_id', 'city_id', 'user_id'),
			$user_id,
			'city'
		);
	}

	private function _formatCityRank($rank = 0, $item = []) {
		return [
			'id'						=> $item['id'],
			'rank'						=> $rank,
			'event_challenge_city_id'	=> $item['event_challenge_city_id'],
			'event_id'					=> $item['event_id'],
			'name'						=> $item['name'],
			'school_id'					=> $item['school_id'],
			'school_code'				=> $item['school_code'],
			'city_id'					=> $item['city_id'],
			'city'						=> $item['city'],
			'state'						=> $item['state'],
			'score'						=> $item['score'],
			'registered'				=> $item['total_students'] ?? 0,
			'message' 					=> self::_getCityMessage(array_merge($item, [
				'rank'					=> $rank,
			])),
		];
	}

	public function getCityMessage($rank_info = []) {
		return self::_getCityMessage($rank_info);
	}

	private function _getCityMessage($rank_info = []) {
		$no_published = !empty($rank_info['score'])
			? $rank_info['score']
			: self::_getPublishedCount($rank_info['event_id'], $rank_info['school_id']);
		;

		$challenge_info = $this->event_challenge_city_model->get($rank_info['event_challenge_city_id']);

		if (!empty($challenge_info) && date('Y-m-d H:i:s') > $challenge_info['end_date']) {
			return sprintf(_li('%s is closed now!'), $challenge_info['name']);
		}

		return method_exists($this, sprintf('_getCityEventMessage_%s', $rank_info['event_id']))
			? self::{sprintf('_getCityEventMessage_%s', $rank_info['event_id'])}($no_published, $rank_info, $challenge_info)
			: self::_getCityEventMessage($no_published, $rank_info, $challenge_info);
	}

	private function _getCityEventMessage($no_published = 0, $rank_info = [], $challenge_info = []) {
		if (!empty($challenge_info['max_published']) && $no_published > $challenge_info['max_published'] && !empty($rank_info['rank'])) {
			return vsprintf(_li('Congratulations! Your school has moved to the %s League with %s+ published authors. You’ve earned the Literary Progression Award at %s.'), [
				$rank_info['state'],
				$challenge_info['max_published'] + 1,
				$rank_info['city'] ?? ''
			]);
		}

		if (!empty($challenge_info['max_published']) && $no_published < $challenge_info['max_published'] && !empty($rank_info['rank'])) {
			return vsprintf(_li('You need at least %s published %s to be city-level winner.'), [
				($challenge_info['max_published'] - $no_published),
				self::_getCopyText(($challenge_info['max_published'] - $no_published + 1)),
				$rank_info['city'] ?? ''
			]);
		}

		if (!empty($rank_info['rank']) && $rank_info['rank'] > 3) {
			$top_score 		= self::_getRankScore(3, $rank_info, false, 'city');
			$required_score = $top_score - $no_published + 1;

			return vsprintf(_li('You need at least %s published %s to be in the %s Top 3 Literary Leaders'), [
				$required_score,
				self::_getCopyText($required_score),
				$rank_info['city'],
			]);
		}

		if (empty($rank_info['rank'])) {
			return vsprintf(_li('Your school has not yet reached the City League. You need at least %s published %s to qualify for city-level rankings.'), [
				($challenge_info['min_published'] - $no_published),
				self::_getCopyText(($challenge_info['min_published'] - $no_published)),
			]);
		}
	}
}
