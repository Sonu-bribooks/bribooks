<?php defined('BASEPATH') or exit('No direct script access allowed');

trait WeeklyRanking {
	public function pushWeeklyUpdateRank($rank_id = 0) {
		self::_pushWeeklyUpdate($rank_id, 0);
	}

	public function updateWeeklyBookInfo($book_id = 0) {
		if ($ranks = $this->ranking_weekly_model->get_all([
			'book_id'	=> $book_id,
		])['rows'] ?? []) {
			$book_info = $this->book_model->get($book_id);

			if (empty($book_info)) return;

			foreach ($ranks as $rank_info) {
				self::_updateBookInfo(
					'user_rank_weekly',
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

	public function updateWeeklyRank($data = []) {
		$book_info 	= $data['book_info'] ?? [];
		$event_info = $data['event_info'] ?? [];
		$product 	= $data['product'] ?? [];

		log_kb([
			'updating Weekly Rank::' => $data,
		]);

		if (empty($book_info) || empty($event_info) || empty($product)) {
			return;
		}

		$no_sold = $this->event_order_model->getTotalSoldByBook($event_info['id'], $book_info['id']);

		$author_info = $this->student_model->get($book_info['user_id']);

		$weekly_challenge_info = $this->event_challenge_weekly_model->get_all([
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		log_kb([
			'updating Weekly Rank::info' => $weekly_challenge_info,
		]);

		if (empty($weekly_challenge_info)) return;

		if ($no_sold < $weekly_challenge_info['book_sold']) return;

		if (!empty($weekly_challenge_info['is_moved']) && $weekly_challenge_info['max_book_sold'] && $no_sold > $weekly_challenge_info['max_book_sold']) {
			$rank_info = $this->ranking_weekly_model->get_all([
				'event_challenge_weekly_id'	=> (int)$weekly_challenge_info['id'],
				'event_id'					=> (int)$event_info['id'],
				'user_id'					=> (int)$book_info['user_id'],
				'book_id'					=> (int)$book_info['id'],
			])['rows'][0] ?? [];

			if (!empty($rank_info)) {
				$rank_key = self::_getWeeklyKey(
					$rank_info['event_id'],
					$rank_info['event_challenge_id']
				);

				// self::_moveToUpperLevel($rank_key, $rank_info, 'weekly');
			}

			return;
		}

		$total_book_sold = 0;

		if ($rank_weekly_info = $this->ranking_weekly_model->get_all([
			'event_challenge_id'		=> (int)$weekly_challenge_info['id'],
			'event_id'					=> (int)$event_info['id'],
			'user_id'					=> (int)$book_info['user_id'],
			'book_id'					=> (int)$book_info['id'],
		])['rows'][0] ?? []) {
			log_kb([
				'updating Weekly Rank::rank' => $rank_weekly_info['id'],
			]);

			$rank_id = $rank_weekly_info['id'];

			// $total_book_sold = (int)($rank_weekly_info['score'] + $product['quantity']);
			$total_book_sold = (int)$no_sold;

			$this->ranking_weekly_model->edit($rank_weekly_info['id'], [
				'score'					=> $total_book_sold,
			]);

			self::_pushWeeklyUpdate($rank_id, $product['quantity'], $product['quantity']);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '=',
				'type'			=> 'weekly',
			]);
		} else {

			log_kb([
				'updating Weekly Rank::published' => $weekly_challenge_info['date_published'],
			]);

			$skip_book = false;

			if (!empty($weekly_challenge_info['date_published'])) {
				if (empty($book_info['id'])) return;

				$skip_book = self::_checkRankingConditions($weekly_challenge_info, $book_info['id']);

				// $event_book_info = $this->event_book_model->get_all(['book_id' => $book_info['id']])['rows'][0] ?? [];

				// if (
				// 	$event_book_info['date_added'] > $weekly_challenge_info['date_published'] ||
				// 	$event_book_info['date_added'] < $weekly_challenge_info['start_date']
				// ) {
				// 	$book_first_event_order_info = $this->event_order_model->get_all([
				// 		'event_id' 	=> $event_info['id'],
				// 		'book_id' 	=> $book_info['id'],
				// 		'sort'		=> 'event_order.id',
				// 		'order'		=> 'ASC'
				// 	])['rows'][0] ?? [];

				// 	if (
				// 		$book_first_event_order_info['date_added'] > $weekly_challenge_info['date_published'] ||
				// 		$book_first_event_order_info['date_added'] < $weekly_challenge_info['start_date']
				// 	) {
				// 		$skip_book = true;
				// 	}

				// 	if (!$skip_book) {
				// 		if (!empty($weekly_challenge_info['conditions'])) {
				// 			$league_conditions = json_decode($weekly_challenge_info['conditions'], true);

				// 			foreach ($league_conditions as $condition) {
				// 				$condition_start_date 	= date('Y-m-d H:i:s', strtotime($condition['start_date']));
				// 				$condition_end_date 	= date('Y-m-d H:i:s', strtotime($condition['end_date']));

				// 				if (
				// 					$event_book_info['date_added'] >= $condition_start_date && $event_book_info['date_added'] <= $condition_end_date
				// 				) {
				// 					$skip_book = true;
				// 					break;
				// 				}
				// 			}
				// 		} else {
				// 			$skip_book = true;
				// 		}
				// 	}
				// }
			}

			log_kb([
				'updating Weekly Rank::skip_book' => $skip_book,
			]);

			if ($skip_book) return;

			$total_book_sold = (int)$no_sold;

			$rank_id = $this->ranking_weekly_model->add([
				'event_challenge_id'	=> (int)$weekly_challenge_info['id'],
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

			self::_pushWeeklyUpdate($rank_id, $total_book_sold, $product['quantity']);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '>',
				'type'			=> 'weekly',
			]);
		}
	}

	public function getWeeklyTotal($event_id = 0, $event_challenge_weekly_id = 0) {
		$rank_key = self::_getWeeklyKey($event_id, $event_challenge_weekly_id);
		return $this->redis_lib->getTotal($rank_key);
	}

	public function getWeeklyRanks($event_id = 0, $event_challenge_weekly_id = 0, $page = 1, $limit = 0, $search = NULL) {
		// $this->cache->clean();

		$weekly_challenge_info = $this->event_challenge_weekly_model->get($event_challenge_weekly_id);

		$limit 		= ($limit > 0) ? $limit : ($weekly_challenge_info['limit'] ?: 10);
		$ranks 		= [];
		$rank_key 	= self::_getWeeklyKey($event_id, $event_challenge_weekly_id);
		$start 		= $page > 0 ? ($page - 1) * $limit : 0;
		$end 		= $start + $limit - 1;

		if (!empty($search)) {
			$results = [];

			$rank_results = $this->ranking_weekly_model->get_all([
				'event_id'					=> (int)$event_id,
				'event_challenge_weekly_id'	=> (int)$event_challenge_weekly_id,
				'search'					=> $search,
				'is_moved'					=> 0,
				'start'						=> $page > 0 ? ($page - 1) * $limit : 0,
				'limit'						=> $limit
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
			$rank_info 	= $this->ranking_weekly_model->get($rank_id);
			$ranks[] 	= self::_formatWeeklyRank(
				$this->redis_lib->getRank($rank_key, $rank_id) + 1,
				$rank_info
			);
		}

		log_kb(['Ranking_lib::getWeeklyRanks::ranks::' => [$results, $ranks]]);

		return ['ranks' => $ranks, 'total' => $total];
	}

	public function getWeeklyUpdate($event_id = 0, $event_challenge_weekly_id = 0, $user_id = 0) {
		self::_updateLiveWeeklyUser($event_id, $event_challenge_weekly_id, $user_id);

		$weekly_rank_key = self::_getWeeklyRankKey($event_id, $event_challenge_weekly_id, $user_id);

		$json = json_decode($this->cache->get($weekly_rank_key), true);

		log_kb(['Ranking_lib::getWeeklyUpdate::' => [
			$json,
			$weekly_rank_key,
		]]);

		self::removeWeeklyUserUpdate($event_id, $event_challenge_weekly_id, $user_id);

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

	public function removeWeeklyUserUpdate($event_id = 0, $event_challenge_weekly_id = 0, $user_id = 0) {
		$rank_key = self::_getWeeklyRankKey($event_id, $event_challenge_weekly_id, $user_id);

		log_kb([
			'rank_key' => $rank_key
		]);

		$this->cache->delete($rank_key);
	}

	private function _getCurrentChallengeUserWeeklyRank($event_id = 0, $event_challenge_weekly_id = 0, $user_id = 0, $book_id = 0) {
		$rank_key = self::_getWeeklyKey($event_id, $event_challenge_weekly_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_weekly_id'	=> (int)$event_challenge_weekly_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'is_moved'					=> 0,
		];

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_weekly_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		return $user_rank;
	}

	public function getUserWeeklyRank($event_id = 0, $event_challenge_weekly_id = 0, $user_id = 0, $book_id = 0) {
		$rank_key = self::_getWeeklyKey($event_id, $event_challenge_weekly_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_weekly_id'	=> (int)$event_challenge_weekly_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'is_moved'					=> 0,
		];

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_weekly_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		if (!empty($user_rank['is_moved'])) {
			if ($current_challenge_rank = self::_getCurrentChallengeUserWeeklyRank(
				$event_id,
				$event_challenge_weekly_id,
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
			? $user_rank
			: self::_genUserWeeklyRank($event_id, $event_challenge_weekly_id, $user_id, $book_id)
		;

		$user_rank = array_merge($user_rank, [
			'is_early_access'			=> self::_isEarlyAccess($user_rank['book_id']),
			'is_prime_author'			=> self::_isPrimeAuthor($user_rank['book_id']),
		]);

		$user_rank['message'] = self::_getWeeklyUserMessage($user_rank);

		return $user_rank;
	}

	public function getUserNoWeeklyRank($event_id = 0, $event_challenge_weekly_id = 0, $user_id = 0, $book_id = 0) {
		$user_rank = self::getUserWeeklyRank($event_id, $event_challenge_weekly_id, $user_id, $book_id);

		$user_rank = !empty($user_rank)
			? $user_rank
			: self::_genUserWeeklyRank($event_id, $event_challenge_weekly_id, $user_id, $book_id)
		;

		$author_info = $this->user_model->get($user_id);

		if (empty($user_rank['book_id'])) {
			$user_rank = [
				'id'					=> 0,
				'rank'					=> 0,
				'event_id'				=> (int)$event_id,
				'event_challenge_weekly_id'=> (int)$event_challenge_weekly_id,
				'user_id'				=> (int)$user_id,
				'author_name'			=> trim(($author_info['first_name'] ?? '') . ' ' . ($author_info['last_name'] ?? '')),
				'author_image'			=> $author_info['image'] ?? '',
				'book_image'			=> '',
				'book_id'				=> 'NA',
				'book_name'				=> 'NA',
				'book_slug'				=> 'NA',
				'score'					=> 0,
				'message'				=> _li('Unfortunately, your book wasn\'t submitted for this event, so you can\'t participate in this league.'),
				'amazon_url'			=> ''
			];
		}

		return $user_rank;
	}

	private function _formatWeeklyRank($rank = 0, $item = []) {
		return [
			'id'						=> $item['id'],
			'rank'						=> $rank,
			'event_id'					=> $item['event_id'],
			'event_challenge_id'		=> $item['event_challenge_id'],
			'event_challenge_weekly_id'	=> $item['event_challenge_id'],
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
			'message' 					=> self::_getWeeklyUserMessage(array_merge($item ?? [], [
				'rank'					=> $rank,
			])),
		];
	}

	private function _initWeeklyRanks($event_id = 0, $event_challenge_weekly_id = 0) {
		$filter_data = [
			'event_challenge_weekly_id'	=> (int)$event_challenge_weekly_id,
			'event_id'					=> (int)$event_id,
			'start'						=> 0,
			'limit'						=> 100,
		];

		$results = $this->ranking_weekly_model->get_all($filter_data)['rows'] ?? [];

		$ranks = [];

		foreach ($results as $key => $item) {
			$ranks[$item['book_id']] = self::_formatWeeklyRank($key + 1, $item);
		}

		return $ranks;
	}

	private function _pushWeeklyUpdate($rank_id = 0, $new_score = 0, $current_order_qty = 0) {
		$rank_info = $this->ranking_weekly_model->get($rank_id);

		$rank_key = self::_getWeeklyKey(
			$rank_info['event_id'],
			$rank_info['event_challenge_id'],
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

		log_kb(['Ranking::_pushWeeklyUpdate' => [
			'old_rank'		=> $old_rank,
			'new_rank'		=> $new_rank,
		]]);

		$alert_payload['rank_data'] = array_merge(
			self::_formatWeeklyRank($new_rank, $rank_info),
			[
				'old_rank'		=> $old_rank,
				'new_rank'		=> $new_rank,
				'change_score'	=> $current_order_qty,
			]
		);

		self::_notifyAppUsers(
			sprintf('bb_notifications_ranking_weekly_%s_%s', $rank_info['event_id'], $rank_info['event_challenge_id']),
			[
				'title'	=> _li('weekly_rank_update'),
				'body'	=> _li('weekly_rank_update'),
			]
		);

		self::_saveWeeklyAlertForEveryOne($rank_info, $alert_payload);
	}

	private function _genUserWeeklyRank($event_id = 0, $event_challenge_weekly_id = 0, $user_id = 0, $book_id = 0) {
		if ($rank_info = $this->ranking_weekly_model->get_all([
			'event_id'					=> (int)$event_id,
			'event_challenge_weekly_id'	=> (int)$event_challenge_weekly_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'is_moved'					=> 0,
		])['rows'][0] ?? []) {
			return self::_formatWeeklyRank(0, $rank_info);
		}

		$author_info = $this->student_model->get($user_id);

		if ($top_sold_book = $this->event_order_model->getSoldByBook([
			'user_id'	=> (int)$user_id,
			'event_id'	=> (int)$event_id,
			'book_id'	=> (int)$book_id,
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
				->where('book.id', (int)$book_id)
				->get()->row_array()
			;
		}

		$no_sold = !empty($item['id']) ? $this->event_order_model->getTotalSoldByBook($event_id, $item['id'] ?? 0) : 0;

		$rank_data = [
			'id'					=> 0,
			'rank'					=> 0,
			'event_challenge_id'	=> $event_challenge_weekly_id,
			'event_challenge_weekly_id'=> $event_challenge_weekly_id,
			'event_id'				=> $event_id,
			'user_id'				=> $user_id,
			'author_name'			=> $item['author_name'] ?? '',
			'author_image'			=> $item['author_image'] ?? '',
			'book_id'				=> $item['id'] ?? 0,
			'book_name'				=> $item['name'] ?? '',
			'book_slug'				=> $item['slug'] ?? '',
			'book_image'			=> $item['cover_image'] ?? '',
			'is_early_access'		=> self::_isEarlyAccess($item['id'] ?? 0),
			'is_prime_author'		=> self::_isPrimeAuthor($item['id'] ?? 0),
			'score'					=> $no_sold,
		];

		return array_merge(
			$rank_data,
			[
				'message' => self::_getWeeklyUserMessage($rank_data)
			],
		);
	}

	private function _addMessageToWeeklyRanks(&$ranks = []) {
		foreach ($ranks as &$item) {
			$item['message'] = self::_getWeeklyUserMessage($item);
		}
	}

	public function getWeeklyTopRank($score = 100, $rank = [], $full_rank = false) {
		return self::_getWeeklyRankScore($score, $rank, $full_rank);
	}

	private function _getWeeklyRankScore($u_rank = 100, $rank = [], $full_rank = false) {
		$rank_key = self::_getWeeklyKey(
			(int)$rank['event_id'],
			(int)$rank['event_challenge_id'],
		);

		$result = array_keys($this->redis_lib->getRanks($rank_key, $u_rank - 1, $u_rank - 1));
		$user_rank = $this->ranking_weekly_model->get($result[0] ?? '');

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

	private function _getWeeklyUserMessage($rank = []) {
		$total_sold = !empty($rank['book_id'])
			? ($this->event_order_model->getTotalSoldByBook($rank['event_id'], $rank['book_id']) ?? 0)
			: 0
		;

		if (in_array($rank['user_id'], BB_UID)) {
			$total_sold = 80 + $total_sold;
		}

		log_kb([
			'_getWeeklyUserMessage' => [
				$total_sold,
				$rank,
			]
		]);

		$book_info  = $this->book_model->get($rank['book_id'] ?? 0);
		$weekly_challenge_info = $this->event_challenge_weekly_model->get($rank['event_challenge_id']);

		if (!empty($weekly_challenge_info) && date('Y-m-d H:i:s') > $weekly_challenge_info['end_date']) {
			return sprintf(_li('%s Is Closed Now!'), $weekly_challenge_info['name']);
		}

		if (!empty($rank['is_moved']) || ($total_sold > $weekly_challenge_info['max_book_sold'])) {
			return _li('Your Book has been promoted to the next league');
		}

		$event_book_info = $this->event_book_model->get_all(['book_id' => $rank['book_id']])['rows'][0] ?? [];

		if (!empty($book_info) && !empty($weekly_challenge_info) && !empty($event_book_info)) {
			if (
				$event_book_info['date_added'] > $weekly_challenge_info['date_published'] ||
				$event_book_info['date_added'] < $weekly_challenge_info['start_date']
			) {
				$skip_book = self::_checkRankingConditions($weekly_challenge_info, $book_info['id']);

				if ($skip_book) {
					return _li('Unfortunately, your book wasn\'t submitted in this league.');
				}

			}
		}

		if (empty($rank['score'])) {
			return sprintf(_li('Buy/Sell at least %s copy to participate in %s.'), $weekly_challenge_info['book_sold'], $weekly_challenge_info['name']);
		} else {
			return method_exists($this, sprintf('_getWeeklyEventMessage_%s', $rank['event_id']))
				? self::{sprintf('_getWeeklyEventMessage_%s', $rank['event_id'])}($total_sold, $rank)
				: self::_getWeeklyEventMessage($total_sold, $rank, $weekly_challenge_info);
		}
	}

	private function _getWeeklyEventMessage($total_sold = 0, $rank = [], $challenge_info = []) {
		$rank_breakpoints = $this->league_break_point_message_model->get_all([
			'event_id'		=> (int)$rank['event_id'],
			'challenge_id'	=> (int)$rank['event_challenge_id'] ?? 0,
			'type'			=> 'weekly',
			'sort'			=> 'league_breakpoint_message.breakpoint',
			'order'			=> 'DESC',
		])['rows'] ?? [];

		foreach ($rank_breakpoints as $index => $breakpoint) {
			if ($rank['rank'] > $breakpoint['breakpoint']) {
				$required_sold_count = self::_getWeeklyRankScore($breakpoint['breakpoint'], $rank) - $rank['score'] + 1;

				return self::formatLeagueMessage($breakpoint['message'], [
					'required_sold_count' 	=> $required_sold_count,
					'copy_text' 			=> self::_getCopyText($required_sold_count),
				]);
			}
		}

		$author_info 	= $this->student_model->get($rank['user_id']);
		$state_info 	= $this->state_model->get($author_info['state_id'] ?? 0);

		log_kb(['_getWeeklyEventMessage::' => [
			$rank,
			$challenge_info,
		]]);

		if (empty($rank['rank'])) {
			return sprintf(_li('Buy/Sell %s %s more to participate in the weekly bestseller league'), ($challenge_info['book_sold'] - $total_sold), self::_getCopyText(($challenge_info['book_sold'] - $total_sold)));
		}

		return sprintf(_li('Buy/Sell at least one copy more to participate in the weekly bestseller league of %s'), $challenge_info['name'] ?? '');
	}

	private function _saveWeeklyAlertForEveryOne($rank_info = [], $alert_payload = []) {
		$users = self::_getLiveWeeklyUsers($rank_info['event_id'], $rank_info['event_challenge_id']);

		log_kb(['_saveWeeklyAlertForEveryOne' => $users, [$alert_payload]]);

		foreach ($users as $user_id) {
			$this->cache->save(
				self::_getWeeklyRankKey($rank_info['event_id'], $rank_info['event_challenge_id'], $user_id),
				json_encode($alert_payload),
				300
			);
		}
	}

	private function _updateLiveWeeklyUser($event_id = 0, $event_challenge_weekly_id = 0, $user_id = 0) {
		$users = self::_getLiveWeeklyUsers($event_id, $event_challenge_weekly_id);

		if (!in_array($user_id, $users)) {
			$users[] = $user_id;
		} else {
			return;
		}

		log_kb(['_updateLiveWeeklyUser::new' => $users, [$user_id]]);

		$this->cache->save(self::_getLiveWeeklyUserKey($event_id, $event_challenge_weekly_id), json_encode($users), 900);
	}

	private function _getLiveWeeklyUsers($event_id = 0, $event_challenge_weekly_id = 0) {
		$users = json_decode($this->cache->get(self::_getLiveWeeklyUserKey($event_id, $event_challenge_weekly_id)), true);

		return $users ?? [];
	}

	private function _getLiveWeeklyUserKey($event_id = 0, $event_challenge_weekly_id = 0) {
		return vsprintf('event_live_weekly_users_%s_%s', [
			$event_id,
			$event_challenge_weekly_id,
		]);
	}

	private function _getWeeklyRankKey($event_id = 0, $event_challenge_weekly_id = 0, $user_id = 0) {
		return vsprintf('%s_weekly_rank_update_%s_%s', [
			$event_id,
			$event_challenge_weekly_id,
			$user_id,
		]);
	}

	private function _getWeeklyKey($event_id = 0, $event_challenge_weekly_id = 0) {
		return vsprintf('live_author_weekly_ranks_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$event_id,
			$event_challenge_weekly_id,
		]);
	}

	public function getWeeklyLastUpdatedRanks($event_id = 0, $event_challenge_weekly_id = 0) {
		$rank_results = $this->ranking_weekly_model->get_all([
			'event_id'					=> (int)$event_id,
			'event_challenge_weekly_id'	=> (int)$event_challenge_weekly_id,
			'sort'						=> 'user_rank_weekly.date_modified',
			'order'						=> 'DESC',
			'start'						=> 0,
			'limit'						=> 4
		])['rows'] ?? [];

		$rank_data = [];

		$rank_key 	= self::_getWeeklyKey($event_id, $event_challenge_weekly_id);

		foreach ($rank_results as $item) {
			$rank 	= $this->redis_lib->getRank($rank_key, $item['id']) + 1;

			$book_latest_event_order_info = $this->event_order_model->get_all([
				'event_id' 	=> $item['event_id'],
				'book_id' 	=> $item['book_id'],
				'sort'		=> 'event_order.id',
				'order'		=> 'DESC'
			])['rows'][0] ?? [];

			$rank_data[] = [
				'id'						=> $item['id'],
				'rank'						=> $rank,
				'event_id'					=> $item['event_id'],
				'event_challenge_id'		=> $item['event_challenge_id'],
				'event_challenge_weekly_id'	=> $item['event_challenge_id'],
				'user_id'					=> $item['user_id'],
				'author_name'				=> $item['author_name'],
				'author_image'				=> $item['author_image'],
				'book_id'					=> $item['book_id'],
				'book_name'					=> $item['book_name'],
				'book_image'				=> $item['book_image'],
				'book_slug'					=> $item['book_slug'],
				'score'						=> $item['score'],
				'change_score'				=> $book_latest_event_order_info['quantity'] ?? 0,
			];
		}

		return $rank_data;
	}

	private function _checkRankingConditions($weekly_challenge_info = [], $book_id = 0) {
		if (empty($book_id) || empty($book_info = $this->book_model->get($book_id))) return true;

		$skip_book = false;

		$event_book_info = $this->event_book_model->get_all(['book_id' => $book_info['id']])['rows'][0] ?? [];

		$book_first_event_order_info = $this->event_order_model->get_all([
			'event_id' 	=> $weekly_challenge_info['event_id'],
			'book_id' 	=> $book_info['id'],
			'sort'		=> 'event_order.id',
			'order'		=> 'ASC'
		])['rows'][0] ?? [];

		if (
			$event_book_info['date_added'] > $weekly_challenge_info['date_published'] ||
			$event_book_info['date_added'] < $weekly_challenge_info['start_date']
		) {
			if (!empty($weekly_challenge_info['conditions'])) {
				$league_conditions = json_decode($weekly_challenge_info['conditions'], true);

				if (!empty($league_conditions)) {
					$include_array = array_filter($league_conditions, function ($item) {
						return isset($item['type']) && $item['type'] === 'include';
					});

					$exclude_array = array_filter($league_conditions, function ($item) {
						return isset($item['type']) && $item['type'] === 'exclude';
					});

					if (empty($include_array) && empty($exclude_array)) return true;

					$include_conditions = array_values($include_array);

					foreach ($include_conditions as $include_condition) {
						$condition_start_date 	= date('Y-m-d H:i:s', strtotime($include_condition['start_date']));
						$condition_end_date 	= date('Y-m-d H:i:s', strtotime($include_condition['end_date']));
						$check_date_added		= $include_condition['check_type'] == 'sold' ? $book_first_event_order_info['date_added'] : $event_book_info['date_added'];

						if (
							$check_date_added > $weekly_challenge_info['date_published'] ||
							$check_date_added < $weekly_challenge_info['start_date']
						) {
							$skip_book = true;
							break;
						}
					}

					if (!$skip_book) {

						$exclude_conditions = array_values($exclude_array);

						foreach ($exclude_conditions as $exclude_condition) {
							$condition_start_date 	= date('Y-m-d H:i:s', strtotime($exclude_condition['start_date']));
							$condition_end_date 	= date('Y-m-d H:i:s', strtotime($exclude_condition['end_date']));
							$check_date_added		= $exclude_condition['check_type'] == 'sold' ? $book_first_event_order_info['date_added'] : $event_book_info['date_added'];

							if (
								$check_date_added >= $condition_start_date && $check_date_added <= $condition_end_date
							) {
								$skip_book = true;
								break;
							}
						}
					}

				} else {
					$skip_book = true;
				}

			} else {
				$skip_book = true;
			}
		}

		return $skip_book;
	}
}
