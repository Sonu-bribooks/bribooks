<?php defined('BASEPATH') or exit('No direct script access allowed');

trait LegendRanking {
	public function getLegendRanks($stage = 'city', $event_id = 0, $event_challenge_id = 0, $region_id = 0, $page = 1, $limit = 0, $search = NULL) {
		$model_name 			= sprintf('ranking_%s_model', strtolower($stage));
		$challenge_model_name 	= sprintf('event_challenge_%s_model', strtolower($stage));
		$event_challenge_info 	= $this->{$challenge_model_name}->get($event_challenge_id);
		$limit 					= ($limit > 0) ? $limit : ($event_challenge_info['limit'] ?: 10);
		$ranks 					= [];

		$rank_key 				= self::_getLegendKey($stage, [
			'event_id'				  	=> (int)$event_id,
			'event_challenge_school_id' => (int)$event_challenge_id,
			'event_challenge_city_id'   => (int)$event_challenge_id,
			'event_challenge_state_id'  => (int)$event_challenge_id,
			'event_challenge_general_id'=> (int)$event_challenge_id,
			'school_id'				   	=> (int)$region_id,
			'city_id'				  	=> (int)$region_id,
			'state_id'				  	=> (int)$region_id,
		]);

		$start 		= $page > 0 ? ($page - 1) * $limit : 0;
		$end 		= $start + $limit - 1;

		if (!empty($search)) {
			$results = [];

			$filter_data = [
				'event_id'					=> (int)$event_id,
				'challenge_id'				=> (int)$event_challenge_id,
				'school_id'					=> (int)$region_id,
				'city_id'					=> (int)$region_id,
				'state_id'					=> (int)$region_id,
				'search'					=> $search,
				'is_moved'					=> 1,
				'start'						=> $page > 0 ? ($page - 1) * $limit : 0,
				'limit'						=> $limit,
			];

			$rank_results 	= $this->{$model_name}->get_all($filter_data);
			$total 			= $rank_results['total'];

			foreach ($rank_results['rows'] ?? [] as $item) {
				$results[$item['id']] = $item;
			}
		} else {
			$results 	= $this->redis_lib->getRanks($rank_key, $start, $end);
			$total 		= $this->redis_lib->getTotal($rank_key);
		}

		foreach ($results as $rank_id => $item) {
			$rank_info 	= $this->{$model_name}->get($rank_id);

			$ranks[] 	= self::_formatLegenRank($stage,
				$this->redis_lib->getRank($rank_key, $rank_id) + 1,
				$rank_info
			);
		}

		log_kb(['Ranking_lib::getLegendRanks::ranks::' => [$results, $ranks]]);

		return ['ranks' => $ranks, 'total' => $total];
	}

	private function _formatLegenRank($stage = 'city', $rank = 0, $item = []) {
		return [
			'id'					=> $item['id'],
			'rank'					=> $rank,
			'event_id'				=> $item['event_id'],
			'school_id'				=> $item['site_id'] ?? 0,
			'city_id'				=> $item['city_id'] ?? 0,
			'user_id'				=> $item['user_id'],
			'author_name'			=> $item['author_name'],
			'author_image'			=> $item['author_image'],
			'book_id'				=> $item['book_id'],
			'state_id'				=> $item['state_id'] ?? 0,
			'book_name'				=> $item['book_name'],
			'book_image'			=> $item['book_image'],
			'book_slug'				=> $item['book_slug'],
			'score'					=> $item['score'],
			'is_moved'				=> 1,
			sprintf('event_challenge_%s_id', $stage) => $item[sprintf('event_challenge_%s_id', $stage)],
		];
	}

	private function _getLegendKey($stage = 'city', $rank_info = []) {
		return vsprintf('live_legendary_%s_ranks_%s_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$stage,
			$rank_info['event_id'],
			$rank_info[sprintf('event_challenge_%s_id', $stage)],
			$rank_info[sprintf('%s_id', $stage)] ?? 0,
		]);
	}

	private function _buildLegendLeague($stage = 'city', $rank_info = []) {
		self::_pushLegendUpdate($stage, $rank_info);
	}

	private function _pushLegendUpdate($stage = 'city', $rank_info = []) {
		$rank_key = self::_getLegendKey(
			$stage,
			$rank_info,
		);

		$old_rank = $this->redis_lib->getRank($rank_key, $rank_info['id']);

		if (empty($old_rank) && $old_rank !== 0) {
			$old_rank = 0;
		} else {
			$old_rank += 1;
		}

		log_kb([
			'Legend New Rank' => [
				$rank_key,
				$rank_info['id']
			]
		]);

		$new_score = $rank_info['score'];

		if (!empty($old_rank)) {
			$this->redis_lib->removeFromRank($rank_key, $rank_info['id']);
		}

		$new_score = $new_score . (99999999999 - strtotime($rank_info['date_modified']));

		$this->redis_lib->updateRank(
			$rank_key,
			-$new_score,
			$rank_info['id']
		);

		$new_rank = $this->redis_lib->getRank($rank_key, $rank_info['id']);

		$new_rank += 1;

		log_kb(['Ranking::_pushLegendUpdate' => [
			'old_rank'		=> $old_rank,
			'new_rank'		=> $new_rank,
		]]);

		// $alert_payload['rank_data'] = array_merge(
		// 	self::_formatCityRank($new_rank, $rank_info),
		// 	[
		// 		'old_rank'	=> $old_rank,
		// 		'new_rank'	=> $new_rank,
		// 	]
		// );

		// self::_saveCityAlertForEveryOne($rank_info, $alert_payload);
	}
}
