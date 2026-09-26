<?php defined('BASEPATH') or exit('No direct script access allowed');

trait AmazonRanking {
    public function updateAmazonRank($data = []) {
		$book_info 	    = $data['book_info'] ?? [];
		$event_info     = $data['event_info'] ?? [];
		$amazon_order 	= $data['amazon_order'] ?? [];

		log_kb([
			'updating Amazon Rank::' => $data,
		]);

		if (empty($book_info) || empty($event_info) || empty($amazon_order)) {
			return;
		}

		$no_sold = $this->event_order_amazon_model->getTotalSoldByBook($event_info['id'], $book_info['id']);

		$amazon_challenge_info = $this->event_challenge_amazon_model->get_all([
			'type'					=> 'user',
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			// 'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (empty($amazon_challenge_info)) return;

		if (
			($no_sold < $amazon_challenge_info['book_sold']) &&
			!in_array($book_info['user_id'], BB_UID)
		) return;

		if ($amazon_challenge_info['max_book_sold'] && $no_sold > $amazon_challenge_info['max_book_sold']) return;

		$total_book_sold = 0;

		if ($rank_amazon_info = $this->ranking_amazon_model->get_all([
			'event_challenge_amazon_id'=> (int)$amazon_challenge_info['id'],
			'event_id'					=> (int)$event_info['id'],
			'user_id'					=> (int)$book_info['user_id'],
			'book_id'					=> (int)$book_info['id'],
		])['rows'][0] ?? []) {
			$rank_id = $rank_amazon_info['id'];

			$total_book_sold = (int)$no_sold;

			$this->ranking_amazon_model->edit($rank_amazon_info['id'], [
				'score'					=> $total_book_sold,
			]);

			self::_pushAmazonUpdate($rank_id, $amazon_order['quantity']);

			// self::_sendAppNotification([
			// 	'event_info'	=> $event_info,
			// 	'book_info'		=> $book_info,
			// 	'relation'		=> '=',
			// 	'type'			=> 'national',
			// ]);
		} else {
			$total_book_sold = $no_sold;

			$rank_id = $this->ranking_amazon_model->add([
				'event_challenge_amazon_id'=> (int)$amazon_challenge_info['id'],
				'event_id'				=> (int)$event_info['id'],
				'user_id'				=> (int)$book_info['user_id'],
				'author_name'			=> $book_info['author_name'],
				'author_image'			=> $book_info['author_image'],
				'book_id'				=> (int)$book_info['id'],
				'book_name'				=> $book_info['name'],
				'book_slug'				=> $book_info['slug'],
				'book_image'			=> $book_info['cover_image'],
				'score'					=> $total_book_sold,
			]);

			self::_pushAmazonUpdate($rank_id, $total_book_sold);

			// self::_sendAppNotification([
			// 	'event_info'	=> $event_info,
			// 	'book_info'		=> $book_info,
			// 	'relation'		=> '>',
			// 	'type'			=> 'national',
			// ]);
		}
	}

    private function _pushAmazonUpdate($rank_id = 0, $new_score = 0) {
		$rank_info = $this->ranking_amazon_model->get($rank_id);

		$old_rank = $new_rank = 0;

		$rank_key = self::_getAmazonKey(
			$rank_info['event_id'],
			$rank_info['event_challenge_amazon_id'],
		);

		$old_rank = $this->redis_lib->getRank($rank_key, $rank_info['id']);

		if (empty($old_rank) && $old_rank !== 0) {
			$old_rank = 0;
		} else {
			$old_rank += 1;
		}

		log_kb([
			'New Rank' => [
				$rank_key,
				-$new_score,
				$rank_info['id']
			]
		]);

		// $new_score = $old_rank ? $new_score : $rank_info['score'];
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

		log_kb(['Ranking::_pushAmazonUpdate' => [
			'old_rank'		=> $old_rank,
			'new_rank'		=> $new_rank,
		]]);

		$alert_payload['rank_data'] = array_merge(
			self::_formatAmazonRank($new_rank, $rank_info),
			[
				'old_rank'	=> $old_rank,
				'new_rank'	=> $new_rank,
			]
		);

		self::_notifyAppUsers(
			sprintf('bb_notifications_ranking_national_%s', $rank_info['event_id']),
			[
				'title'	=> _li('national_rank_update'),
				'body'	=> _li('national_rank_update'),
			]
		);

		self::_saveAmazonAlertForEveryOne($rank_info, $alert_payload);
	}

    private function _getAmazonKey($event_id = 0, $event_challenge_amazon_id = 0) {
		return vsprintf('live_author_amazon_ranks_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$event_id,
			$event_challenge_amazon_id,
		]);
	}

    private function _formatAmazonRank($rank = 0, $item = []) {
		if (empty($item)) return;

		$book_info 		= (in_array($item['event_id'], [9])) ? $this->book_model->get($item['book_id']) : [];

		$author_info 	= $this->student_model->get($item['user_id'] ?? 0);
		$site_info 		= $this->site_model->get($author_info['site_id'] ?? 0);
		$state_info 	= $this->state_model->get($author_info['state_id'] ?? 0);
		$city_info 		= $this->city_model->get($author_info['city_id'] ?? 0);

		return [
			'id'						=> $item['id'],
			'rank'						=> $rank,
			'event_challenge_amazon_id' => $item['event_challenge_amazon_id'],
			'event_id'					=> $item['event_id'],
			'user_id'					=> $item['user_id'],
			'author_name'				=> $item['author_name'],
			'author_image'				=> $item['author_image'],
			'book_image'				=> $item['book_image'],
			'book_id'					=> $item['book_id'],
			'book_name'					=> $item['book_name'],
			'book_slug'					=> $item['book_slug'],
			'is_early_access'			=> self::_isEarlyAccess($item['book_id']),
			'is_prime_author'			=> self::_isPrimeAuthor($item['book_id']),
			'score'						=> $item['score'],
			'message' 					=> self::_getAmazonUserMessage(array_merge($item, [
				'rank'					=> $rank,
			])),
			'amazon_url'				=> $book_info['amazon_url'] ?? '',
			'site_id'					=> $author_info['site_id'] ?? '0',
			'school'					=> $site_info['name'] ?? '',
			'state_id'					=> $author_info['state_id'] ?? '0',
			'state'						=> $state_info['name'] ?? '',
			'city_id'					=> $author_info['city_id'] ?? '0',
			'city'						=> $city_info['name'] ?? ''
		];
	}

	private function _getAmazonUserMessage($rank = []) {
		$total_sold = !empty($rank['book_id'])
			? ($this->event_order_amazon_model->getTotalSoldByBook($rank['event_id'], $rank['book_id']) ?? 0)
			: 0
		;

		if (in_array($rank['user_id'] ?? 0, BB_UID)) {
			$total_sold = 80 + $total_sold;
		}

		log_kb([
			'_getAmazonUserMessage' => [
				$total_sold,
				$rank,
			]
		]);

		$amazon_challenge_info = $this->event_challenge_amazon_model->get($rank['event_challenge_amazon_id']);

		// if (!empty($amazon_challenge_info) && date('Y-m-d H:i:s') > $amazon_challenge_info['end_date']) {
		// 	return sprintf(_li('%s is Closed Now!'), $amazon_challenge_info['name']);
		// }

		if (empty($rank['rank'])) {
			if ($total_sold >= $amazon_challenge_info['book_sold']) {
				return sprintf(_li('Unfortunately, your book wasn\'t submitted for this event, so you can\'t participate in the %s'), $amazon_challenge_info['name']);
			}

			return vsprintf(_li('Buy/Sell %s %s more to participate in %s'), [
				($amazon_challenge_info['book_sold'] - $total_sold),
				self::_getCopyText(($amazon_challenge_info['book_sold'] - $total_sold)),
				$amazon_challenge_info['name']
			]);
		} else {
			return method_exists($this, sprintf('_getAmazonEventMessage_%s', $rank['event_id']))
				? self::{sprintf('_getAmazonEventMessage_%s', $rank['event_id'])}($total_sold, $rank)
				: self::_getAmazonEventMessage($total_sold, $rank, $amazon_challenge_info);
		}
	}

    private function _getAmazonEventMessage($total_sold = 0, $rank = [], $amazon_challenge_info = []) {
		$rank_breakpoints = $this->league_break_point_message_model->get_all([
			'event_id'		=> (int)$rank['event_id'],
			'challenge_id'	=> (int)$rank['event_challenge_amazon_id'],
			'type'			=> 'amazon',
			'sort'			=> 'league_breakpoint_message.breakpoint',
			'order'			=> 'DESC',
		])['rows'] ?? [];

		foreach ($rank_breakpoints as $index => $breakpoint) {
			if ($rank['rank'] > $breakpoint['breakpoint']) {
				$required_sold_count = self::_getAmazonRankScore($breakpoint['breakpoint'], $rank) - $rank['score'] + 1;

				return self::formatLeagueMessage($breakpoint['message'], [
					'required_sold_count' 	=> $required_sold_count,
					'copy_text' 			=> self::_getCopyText($required_sold_count),
				]);
			}
		}
	}

    private function _getAmazonRankScore($u_rank = 100, $rank = [], $full_rank = false) {
		$rank_key = self::_getAmazonKey(
			(int)$rank['event_id'],
			(int)$rank['event_challenge_amazon_id'],
		);

		$result = array_keys($this->redis_lib->getRanks($rank_key, $u_rank - 1, $u_rank - 1));
		$user_rank = $this->ranking_amazon_model->get($result[0] ?? '');

		log_kb([
			'u_rank'	=> $u_rank,
			'result'	=> $result,
			'user_rank'	=> $user_rank,
			'rank'		=> $rank,
		]);

		if ($full_rank) {
			return $user_rank;
		}

		return $user_rank['score'] ?? 0;
	}

    private function _saveAmazonAlertForEveryOne($rank_info = [], $alert_payload = []) {
		$users = self::_getLiveAmazonUsers($rank_info['event_id'], $rank_info['event_challenge_amazon_id']);

		log_kb(['_saveAmazonAlertForEveryOne' => $users, [$alert_payload]]);

		foreach ($users as $user_id) {
			$this->cache->save(
				self::_getAmazonRankKey($rank_info['event_id'], $rank_info['event_challenge_amazon_id'], $user_id),
				json_encode($alert_payload),
				300
			);
		}
	}

    private function _getLiveAmazonUsers($event_id = 0, $event_challenge_amazon_id = 0) {
		$users = json_decode($this->cache->get(self::_getLiveAmazonUserKey($event_id, $event_challenge_amazon_id)), true);

		return $users ?? [];
	}

	private function _getLiveAmazonUserKey($event_id = 0, $event_challenge_amazon_id = 0) {
		return vsprintf('event_live_amazon_users_%s_%s', [
			(int)$event_id,
			(int)$event_challenge_amazon_id,
		]);
	}

    private function _getAmazonRankKey($event_id = 0, $event_challenge_amazon_id = 0, $user_id = 0) {
		return vsprintf('%s_amazon_rank_update_%s_%s', [
			(int)$event_id,
			(int)$event_challenge_amazon_id,
			$user_id,
		]);
	}
}