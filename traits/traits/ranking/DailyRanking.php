<?php defined('BASEPATH') or exit('No direct script access allowed');

trait DailyRanking {
	public function pushDailyUpdateRank($rank_id = 0) {
		self::_pushDailyUpdate($rank_id, 0);
	}

	public function updateDailyBookInfo($book_id = 0) {
		if ($ranks = $this->ranking_daily_model->get_all([
			'book_id'	=> $book_id,
		])['rows'] ?? []) {
			$book_info = $this->book_model->get($book_id);

			if (empty($book_info)) return;

			foreach ($ranks as $rank_info) {
				self::_updateBookInfo(
					'user_rank_daily',
					$rank_info['id'],
					[
						'author_name'	=> $book_info['author_name'],
						'author_image'	=> $book_info['author_image'],
						'book_image'	=> $book_info['cover_image'],
						'book_name'		=> $book_info['name'],
						'book_slug'		=> $book_info['slug'],
					]
				);
			}
		}
	}

	public function updateDailyRank($data = []) {
		$book_info 	= $data['book_info'] ?? [];
		$event_info = $data['event_info'] ?? [];
		$product 	= $data['product'] ?? [];

		log_kb([
			'updating Daily Rank::' => $data,
		]);

		if (empty($book_info) || empty($event_info) || empty($product)) {
			return;
		}

		$no_sold = $product['quantity'];

		$author_info = $this->student_model->get($book_info['user_id']);

		$daily_challenge_info = $this->event_challenge_daily_model->get_all([
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (empty($daily_challenge_info)) return;

		if ($no_sold < $daily_challenge_info['book_sold']) return;

		if (!empty($daily_challenge_info['is_moved']) && $daily_challenge_info['max_book_sold'] && $no_sold > $daily_challenge_info['max_book_sold']) {
			$rank_info = $this->ranking_daily_model->get_all([
				'event_challenge_daily_id'	=> (int)$daily_challenge_info['id'],
				'event_id'					=> (int)$event_info['id'],
				'user_id'					=> (int)$book_info['user_id'],
				'book_id'					=> (int)$book_info['id'],
			])['rows'][0] ?? [];

			if (!empty($rank_info)) {
				$rank_key = self::_getDailyKey(
					$rank_info['event_id'],
					$rank_info['event_challenge_daily_id']
				);

				// self::_moveToUpperLevel($rank_key, $rank_info, 'daily');
			}

			return;
		}

		$total_book_sold = 0;

		if ($rank_daily_info = $this->ranking_daily_model->get_all([
			'event_challenge_daily_id'	=> (int)$daily_challenge_info['id'],
			'event_id'					=> (int)$event_info['id'],
			'user_id'					=> (int)$book_info['user_id'],
			'book_id'					=> (int)$book_info['id'],
		])['rows'][0] ?? []) {
			$rank_id = $rank_daily_info['id'];

			$total_book_sold = (int)($rank_daily_info['score'] + $product['quantity']);

			$this->ranking_daily_model->edit($rank_daily_info['id'], [
				'score'					=> $total_book_sold,
			]);

			self::_pushDailyUpdate($rank_id, $product['quantity']);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '=',
				'type'			=> 'daily',
			]);
		} else {
			$total_book_sold = (int)$no_sold;

			$rank_id = $this->ranking_daily_model->add([
				'event_challenge_daily_id'	=> (int)$daily_challenge_info['id'],
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

			self::_pushDailyUpdate($rank_id, $total_book_sold);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '>',
				'type'			=> 'daily',
			]);
		}
	}

	public function getDailyTotal($event_id = 0, $event_challenge_daily_id = 0) {
		$rank_key = self::_getDailyKey($event_id, $event_challenge_daily_id);
		return $this->redis_lib->getTotal($rank_key);
	}

	public function getDailyRanks($event_id = 0, $event_challenge_daily_id = 0, $page = 1, $search = NULL) {
		// $this->cache->clean();

		$ranks 		= [];
		$rank_key 	= self::_getDailyKey($event_id, $event_challenge_daily_id);
		$start 		= $page > 0 ? ($page - 1) * $this->limit : 0;
		$end 		= $start + $this->limit - 1;

		if (!empty($search)) {
			$results = [];

			$rank_results = $this->ranking_daily_model->get_all([
				'event_id'					=> (int)$event_id,
				'event_challenge_daily_id'	=> (int)$event_challenge_daily_id,
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
			$rank_info 	= $this->ranking_daily_model->get($rank_id);
			$ranks[] 	= self::_formatDailyRank(
				$this->redis_lib->getRank($rank_key, $rank_id) + 1,
				$rank_info
			);
		}

		log_kb(['Ranking_lib::getDailyRanks::ranks::' => [$results, $ranks]]);

		return ['ranks' => $ranks, 'total' => $total];
	}

	public function getDailyUpdate($event_id = 0, $event_challenge_daily_id = 0, $user_id = 0) {
		self::_updateLiveDailyUser($event_id, $event_challenge_daily_id, $user_id);

		$daily_rank_key = self::_getDailyRankKey($event_id, $event_challenge_daily_id, $user_id);

		$json = json_decode($this->cache->get($daily_rank_key), true);

		log_kb(['Ranking_lib::getDailyUpdate::' => [
			$json,
			$daily_rank_key,
		]]);

		self::removeDailyUserUpdate($event_id, $event_challenge_daily_id, $user_id);

		$data = json_encode($json ?? []);
		$event = 'rank_update';

		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
		header('Connection: keep-alive');
		header('Pragma: no-cache');
		header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS');
		header('Access-Control-Allow-Headers: x-requested-with, Accept, Content-Type, Authorization, Origin');
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Allow-Origin: ' . $this->input->get_request_header('Origin', true));

		echo "event: {$event}\ndata: {$data}\n\n";
		exit;
	}

	public function removeDailyUserUpdate($event_id = 0, $event_challenge_daily_id = 0, $user_id = 0) {
		$rank_key = self::_getDailyRankKey($event_id, $event_challenge_daily_id, $user_id);

		log_kb([
			'rank_key' => $rank_key
		]);

		$this->cache->delete($rank_key);
	}

	private function _getCurrentChallengeUserDailyRank($event_id = 0, $event_challenge_daily_id = 0, $user_id = 0, $book_id = 0) {
		$rank_key = self::_getDailyKey($event_id, $event_challenge_daily_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_daily_id'	=> (int)$event_challenge_daily_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'is_moved'					=> 0,
		];

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_daily_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		return $user_rank;
	}

	public function getUserDailyRank($event_id = 0, $event_challenge_daily_id = 0, $user_id = 0, $book_id = 0) {
		$rank_key = self::_getDailyKey($event_id, $event_challenge_daily_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_daily_id'	=> (int)$event_challenge_daily_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'is_moved'					=> 0,
		];

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_daily_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		if (!empty($user_rank['is_moved'])) {
			if ($current_challenge_rank = self::_getCurrentChallengeUserDailyRank(
				$event_id,
				$event_challenge_daily_id,
				$user_id,
				$book_id
			)) {
				$user_rank = $current_challenge_rank;
			} else {
				$user_rank['score'] = $this->event_order_model->getTotalSoldByBook($user_rank['event_id'], $user_rank['book_id']);;
				$user_rank['rank'] 	= 0;
			}
		}

		log_kb(['user_rank' => $user_rank]);

		$user_rank = !empty($user_rank)
			? self::_formatDailyRank($user_rank['rank'], $user_rank)
			: self::_genUserDailyRank($event_id, $event_challenge_daily_id, $user_id)
		;

		return $user_rank;
	}

	public function getUserNoDailyRank($event_id = 0, $event_challenge_daily_id = 0, $user_id = 0) {
		// Book in the event but not in the league
		$user_rank = self::_genUserDailyRank($event_id, $event_challenge_daily_id, $user_id);

		// Book not in the event
		if (empty($user_rank['book_id'])) {
			$author_info = $this->student_model->get($user_id);

			$user_rank = [
				'id'						=> 0,
				'rank'						=> 0,
				'event_id'					=> (int)$event_id,
				'event_challenge_daily_id'	=> (int)$event_challenge_daily_id,
				'user_id'					=> (int)$user_id,
				'author_name'				=> trim($author_info['first_name'] . ' ' . $author_info['last_name']),
				'author_image'				=> $author_info['image'],
				'book_image'				=> '',
				'book_id'					=> 'NA',
				'book_name'					=> 'NA',
				'book_slug'					=> 'NA',
				'score'						=> 0,
				'total_sold'				=> 0,
				'message'					=> _li('Unfortunately, your book wasn\'t submitted for this event, so you can\'t participate in the Best Seller League.'),
			];
		}

		return $user_rank;
	}

	private function _genUserDailyRank($event_id = 0, $event_challenge_daily_id = 0, $user_id = 0) {
		if ($rank_info = $this->ranking_daily_model->get_all([
			'event_id'					=> (int)$event_id,
			'event_challenge_daily_id'	=> (int)$event_challenge_daily_id,
			'user_id'					=> (int)$user_id,
			'is_moved'					=> 0,
		])['rows'][0] ?? []) {
			return self::_formatDailyRank(0, $rank_info);
		}

		if ($top_sold_book = $this->event_order_model->getSoldByBook([
			'user_id'	=> (int)$user_id,
			'event_id'	=> (int)$event_id,
			'sort'		=> 'quantity',
			'order'		=> 'DESC',
			'start'		=> 0,
			'limit'		=> 1,
		])['rows'][0] ?? []) {
			$item = $this->book_model->get($top_sold_book['book_id']);
		} else {
			$item = $this->db->select('book.*')
				->from('event_book')
				->join('book', 'book.id = event_book.book_id')
				->where('book.status', 1)
				->where('book.archived', 0)
				->where('book._deleted', 0)
				->where('event_book.event_id', (int)$event_id)
				->where('book.user_id', (int)$user_id)
				->get()->row_array()
			;
		}

		if (empty($item)) {
			return [];
		}

		$total_sold = $this->event_order_model->getTotalSoldByBook($event_id, $item['id']);

		return self::_formatDailyRank(0, array_merge($item, [
			'id'						=> 0,
			'event_challenge_daily_id'	=> $event_challenge_daily_id,
			'event_id'					=> $event_id,
			'user_id'					=> $user_id,
			'author_name'				=> $item['author_name'],
			'author_image'				=> $item['author_image'],
			'book_id'					=> $item['id'],
			'book_name'					=> $item['name'],
			'book_slug'					=> $item['slug'],
			'book_image'				=> $item['cover_image'],
			'score'						=> $total_sold,
			'total_sold'				=> $total_sold,
		]));
	}

	private function _formatDailyRank($rank = 0, $item = []) {
		$league_info = checkBookLeague($item['book_id'], $item['event_id']);
		$total_sold = $this->event_order_model->getTotalSoldByBook($item['event_id'], $item['book_id']);

		return [
			'id'						=> $item['id'],
			'rank'						=> $rank,
			'event_id'					=> $item['event_id'],
			'event_challenge_daily_id'	=> $item['event_challenge_daily_id'],
			'user_id'					=> $item['user_id'],
			'author_name'				=> $item['author_name'],
			'author_image'				=> $item['author_image'],
			'book_id'					=> $item['book_id'],
			'book_name'					=> $item['book_name'],
			'book_image'				=> $item['book_image'],
			'book_slug'					=> $item['book_slug'],
			'is_early_access'			=> self::_isEarlyAccess($item['book_id']),
			'is_prime_author'			=> self::_isPrimeAuthor($item['book_id']),
			'score'						=> $item['score'],
			'total_sold'				=> $total_sold,
			'current_league'			=> $league_info['type'] == 'country' ? _l('national') : _l($league_info['type']),
			'message' 					=> self::_getDailyUserMessage(array_merge($item ?? [], [
				'rank'					=> $rank,
			])),
		];
	}

	private function _initDailyRanks($event_id = 0, $event_challenge_daily_id = 0) {
		$filter_data = [
			'event_challenge_daily_id'	=> (int)$event_challenge_daily_id,
			'event_id'					=> (int)$event_id,
			'start'						=> 0,
			'limit'						=> 100,
		];

		$results = $this->ranking_daily_model->get_all($filter_data)['rows'] ?? [];

		$ranks = [];

		foreach ($results as $key => $item) {
			$ranks[$item['book_id']] = self::_formatDailyRank($key + 1, $item);
		}

		return $ranks;
	}

	private function _pushDailyUpdate($rank_id = 0, $new_score = 0) {
		$rank_info = $this->ranking_daily_model->get($rank_id);

		$rank_key = self::_getDailyKey(
			$rank_info['event_id'],
			$rank_info['event_challenge_daily_id'],
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

		log_kb(['Ranking::_pushDailyUpdate' => [
			'old_rank'		=> $old_rank,
			'new_rank'		=> $new_rank,
		]]);

		$alert_payload['rank_data'] = array_merge(
			self::_formatDailyRank($new_rank, $rank_info),
			[
				'old_rank'	=> $old_rank,
				'new_rank'	=> $new_rank,
			]
		);

		self::_notifyAppUsers(
			sprintf('bb_notifications_ranking_daily_%s_%s', $rank_info['event_id'], $rank_info['event_challenge_daily_id']),
			[
				'title'	=> _li('daily_rank_update'),
				'body'	=> _li('daily_rank_update'),
			]
		);

		self::_saveDailyAlertForEveryOne($rank_info, $alert_payload);
	}

	private function _addMessageToDailyRanks(&$ranks = []) {
		foreach ($ranks as &$item) {
			$item['message'] = self::_getDailyUserMessage($item);
		}
	}

	public function getDailyTopRank($score = 100, $rank = [], $full_rank = false) {
		return self::_getDailyRankScore($score, $rank, $full_rank);
	}

	private function _getDailyRankScore($score = 100, $rank = [], $full_rank = false) {
		$rank_key = self::_getDailyKey(
			(int)$rank['event_id'],
			(int)$rank['event_challenge_daily_id'],
		);

		$result = array_keys($this->redis_lib->getRanks($rank_key, $u_rank - 1, $u_rank - 1));
		$user_rank = $this->ranking_daily_model->get($result[0] ?? '');

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

	private function _getDailyUserMessage($rank = []) {
		$total_sold = !empty($rank['book_id'])
			? ($this->event_order_model->getTotalSoldByBook($rank['event_id'], $rank['book_id']) ?? 0)
			: 0
		;

		if (in_array($rank['user_id'], BB_UID)) {
			$total_sold = 80 + $total_sold;
		}

		log_kb([
			'_getDailyUserMessage' => [
				$total_sold,
				$rank,
			]
		]);

		$daily_challenge_info = $this->event_challenge_daily_model->get($rank['event_challenge_daily_id']);

		if (!empty($rank['is_moved']) || ($total_sold > $daily_challenge_info['max_book_sold'])) {
			return _li('Your Book has been promoted to the next league');
		}

		if (empty($rank['score'])) {
			return sprintf(_li('Buy/Sell at least %s copy to participate in the daily bestseller league.'), $daily_challenge_info['book_sold']);
		} else {
			return method_exists($this, sprintf('_getDailyEventMessage_%s', $rank['event_id']))
				? self::{sprintf('_getDailyEventMessage_%s', $rank['event_id'])}($total_sold, $rank)
				: self::_getDailyEventMessage($total_sold, $rank, $daily_challenge_info);
		}
	}

	private function _getDailyEventMessage($total_sold = 0, $rank = [], $daily_challenge_info = []) {
		$author_info = $this->student_model->get($rank['user_id']);

		if ($total_sold < $daily_challenge_info['max_book_sold'] && !empty($rank['rank'])) {
			return sprintf(_li('Buy/Sell %s %s more to be the bestseller of %s'), ($daily_challenge_info['max_book_sold'] - $total_sold), self::_getCopyText(($daily_challenge_info['max_book_sold'] - $total_sold)), $daily_challenge_info['name'] ?? '');
		}

		if (empty($rank['rank'])) {
			return sprintf(_li('Buy/Sell %s %s more to participate in the bestseller league of %s'), ($daily_challenge_info['book_sold'] - $total_sold), self::_getCopyText(($daily_challenge_info['book_sold'] - $total_sold)), $daily_challenge_info['name'] ?? '');
		}

		return _li('Buy/Sell at least one copy more to participate in the National bestseller league');
	}

	private function _saveDailyAlertForEveryOne($rank_info = [], $alert_payload = []) {
		$users = self::_getLiveDailyUsers($rank_info['event_id'], $rank_info['event_challenge_daily_id']);

		log_kb(['_saveDailyAlertForEveryOne' => $users, [$alert_payload]]);

		foreach ($users as $user_id) {
			$this->cache->save(
				self::_getDailyRankKey($rank_info['event_id'], $rank_info['event_challenge_daily_id'], $user_id),
				json_encode($alert_payload),
				300
			);
		}
	}

	private function _updateLiveDailyUser($event_id = 0, $event_challenge_daily_id = 0, $user_id = 0) {
		$users = self::_getLiveDailyUsers($event_id, $event_challenge_daily_id);

		if (!in_array($user_id, $users)) {
			$users[] = $user_id;
		} else {
			return;
		}

		log_kb(['_updateLiveDailyUser::new' => $users, [$user_id]]);

		$this->cache->save(self::_getLiveDailyUserKey($event_id, $event_challenge_daily_id), json_encode($users), 900);
	}

	private function _getLiveDailyUsers($event_id = 0, $event_challenge_daily_id = 0) {
		$users = json_decode($this->cache->get(self::_getLiveDailyUserKey($event_id, $event_challenge_daily_id)), true);

		return $users ?? [];
	}

	private function _getLiveDailyUserKey($event_id = 0, $event_challenge_daily_id = 0) {
		return vsprintf('event_live_daily_users_%s_%s', [
			(int)$event_id,
			(int)$event_challenge_daily_id,
		]);
	}

	private function _getDailyRankKey($event_id = 0, $event_challenge_daily_id = 0, $user_id = 0) {
		return vsprintf('%s_daily_rank_update_%s_%s', [
			(int)$event_id,
			(int)$event_challenge_daily_id,
			$user_id,
		]);
	}

	private function _getDailyKey($event_id = 0, $event_challenge_daily_id = 0) {
		return vsprintf('live_author_daily_ranks_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$event_id,
			$event_challenge_daily_id,
		]);
	}
}
