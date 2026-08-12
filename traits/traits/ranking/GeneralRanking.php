<?php defined('BASEPATH') or exit('No direct script access allowed');

trait GeneralRanking {
	public function pushGeneralUpdateRank($rank_id = 0) {
		self::_pushGeneralUpdate($rank_id, 0);
	}

	public function updateGeneralBookInfo($book_id = 0) {
		if ($ranks = $this->ranking_general_model->get_all([
			'book_id'	=> $book_id,
		])['rows'] ?? []) {
			$book_info = $this->book_model->get($book_id);

			if (empty($book_info)) return;

			foreach ($ranks as $rank_info) {
				self::_updateBookInfo(
					'user_rank_general',
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

	public function updateGeneralRank($data = []) {
		$book_info 	= $data['book_info'] ?? [];
		$event_info = $data['event_info'] ?? [];
		$product 	= $data['product'] ?? [];

		log_kb([
			'updating General Rank::' => $data,
		]);

		if (empty($book_info) || empty($event_info) || empty($product)) {
			return;
		}

		$no_sold = $this->event_order_model->getTotalSoldByBook($event_info['id'], $book_info['id']);

		$author_info = $this->student_model->get($book_info['user_id']);

		$challenge_info  = $this->event_challenge_general_model->get_all([
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (empty($challenge_info)) return;

		if ($no_sold < $challenge_info['book_sold']) return;

		if (!empty($challenge_info['is_moved']) && $challenge_info['max_book_sold'] && $no_sold > $challenge_info['max_book_sold']) {
			if (!empty($challenge_info['date_published'])) {
				if (empty($book_info['id'])) return;

				$event_book_info = $this->event_book_model->get_all(['book_id' => $book_info['id']])['rows'][0] ?? [];

				if (
					$event_book_info['date_added'] > $challenge_info['date_published'] ||
					$event_book_info['date_added'] < $challenge_info['start_date']
				) return;
			}
			
			$rank_info = $this->ranking_general_model->get_all([
				'challenge_id'	=> (int)$challenge_info['id'],
				'event_id'		=> (int)$event_info['id'],
				'user_id'		=> (int)$book_info['user_id'],
				'book_id'		=> (int)$book_info['id'],
			])['rows'][0] ?? [];

			if (!empty($rank_info)) {
				$rank_id = $this->ranking_general_model->edit($rank_info['id'], [
					'score'					=> $no_sold,
				]);

				$rank_info = $this->ranking_general_model->get($rank_info['id']);
			} else {
				$rank_id = $this->ranking_general_model->add([
					'challenge_id'			=> (int)$challenge_info['id'],
					'event_id'				=> (int)$event_info['id'],
					'user_id'				=> (int)$book_info['user_id'],
					'author_name'			=> $book_info['author_name'],
					'author_image'			=> $book_info['author_image'],
					'book_id'				=> (int)$book_info['id'],
					'book_name'				=> $book_info['name'],
					'book_slug'				=> $book_info['slug'],
					'book_image'			=> $book_info['cover_image'],
					'score'					=> $no_sold,
				]);

				$rank_info = $this->ranking_general_model->get($rank_id);
			}

			if (!empty($rank_info)) {
				$rank_key = self::_getGeneralKey(
					$rank_info['event_id'],
					$rank_info['challenge_id'],
					$rank_info['general_id']
				);

				self::_moveToUpperLevel($rank_key, $rank_info, 'general');
			}

			return;
		}

		$total_book_sold = 0;

		if ($rank_general_info = $this->ranking_general_model->get_all([
			'challenge_id'	=> (int)$challenge_info['id'],
			'event_id'		=> (int)$event_info['id'],
			'user_id'		=> (int)$book_info['user_id'],
			'book_id'		=> (int)$book_info['id'],
		])['rows'][0] ?? []) {
			$rank_id = $rank_general_info['id'];

			// $total_book_sold = (int)($rank_general_info['score'] + $product['quantity']);
			$total_book_sold = (int)$no_sold;

			$this->ranking_general_model->edit($rank_general_info['id'], [
				'score'		=> $total_book_sold,
			]);

			self::_pushGeneralUpdate($rank_id, $product['quantity']);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '=',
				'type'			=> 'general',
			]);
		} else {
			if (!empty($challenge_info['date_published'])) {
				if (empty($book_info['id'])) return;

				$event_book_info = $this->event_book_model->get_all(['book_id' => $book_info['id']])['rows'][0] ?? [];

				if (
					$event_book_info['date_added'] > $challenge_info['date_published'] ||
					$event_book_info['date_added'] < $challenge_info['start_date']
				) return;
			}

			$total_book_sold = (int)$no_sold;

			$rank_id = $this->ranking_general_model->add([
				'challenge_id'	=> (int)$challenge_info['id'],
				'event_id'		=> (int)$event_info['id'],
				'user_id'		=> (int)$book_info['user_id'],
				// 'general_id'	=> (int)$author_info['general_id'],
				'author_name'	=> $book_info['author_name'],
				'author_image'	=> $book_info['author_image'],
				'book_id'		=> (int)$book_info['id'],
				'book_name'		=> $book_info['name'],
				'book_slug'		=> $book_info['slug'],
				'book_image'	=> $book_info['cover_image'],
				'score'			=> $total_book_sold,
			]);

			self::_pushGeneralUpdate($rank_id, $total_book_sold);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '>',
				'type'			=> 'general',
			]);
		}
	}

	public function getGeneralTotal($event_id = 0, $challenge_id = 0, $general_id = 0) {
		$rank_key = self::_getGeneralKey($event_id, $challenge_id, $general_id);
		return $this->redis_lib->getTotal($rank_key);
	}

	public function getGeneralRanks($event_id = 0, $challenge_id = 0, $general_id = 0, $page = 1, $search = NULL) {
		// $this->cache->clean();

		$ranks 		= [];
		$rank_key 	= self::_getGeneralKey($event_id, $challenge_id, $general_id);
		$start 		= $page > 0 ? ($page - 1) * $this->limit : 0;
		$end 		= $start + $this->limit - 1;

		if (!empty($search)) {
			$results = [];

			$rank_results = $this->ranking_general_model->get_all([
				'event_id'					=> (int)$event_id,
				'challenge_id'				=> (int)$challenge_id,
				'general_id'				=> (int)$general_id,
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
			$rank_info 	= $this->ranking_general_model->get($rank_id);
			$ranks[] 	= self::_formatGeneralRank(
				$this->redis_lib->getRank($rank_key, $rank_id) + 1,
				$rank_info
			);
		}

		log_kb(['Ranking_lib::getGeneralRanks::ranks::' => [$results, $ranks]]);

		return ['ranks' => $ranks, 'total' => $total];
	}

	public function getGeneralUpdate($event_id = 0, $challenge_id = 0, $general_id = 0, $user_id = 0) {
		self::_updateLiveGeneralUser($event_id, $challenge_id, $general_id, $user_id);

		$general_rank_key = self::_getGeneralRankKey($event_id, $challenge_id, $general_id, $user_id);

		$json = json_decode($this->cache->get($general_rank_key), true);

		log_kb(['Ranking_lib::getGeneralUpdate::' => [
			$json,
			$general_rank_key,
		]]);

		self::removeGeneralUserUpdate($event_id, $challenge_id, $general_id, $user_id);

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

	public function removeGeneralUserUpdate($event_id = 0, $challenge_id = 0, $general_id = 0, $user_id = 0) {
		$rank_key = self::_getGeneralRankKey($event_id, $challenge_id, $general_id, $user_id);

		log_kb([
			'rank_key' => $rank_key
		]);

		$this->cache->delete($rank_key);
	}

	private function _getCurrentChallengeUserGeneralRank($event_id = 0, $challenge_id = 0, $general_id = 0, $user_id = 0, $book_id = 0) {
		$rank_key = self::_getGeneralKey($event_id, $challenge_id, $general_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'challenge_id'				=> (int)$challenge_id,
			'general_id'				=> (int)$general_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'is_moved'					=> 0,
		];

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_general_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		return $user_rank;
	}

	public function getUserGeneralRank($event_id = 0, $challenge_id = 0, $general_id = 0, $user_id = 0, $book_id = 0) {
		$user_id = (int)$user_id;

		if (empty($user_id)) return;

		$rank_key = self::_getGeneralKey($event_id, $challenge_id, $general_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'challenge_id'				=> (int)$challenge_id,
			'general_id'				=> (int)$general_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'is_moved'					=> 0,
		];

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_general_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		if (!empty($user_rank['is_moved'])) {
			if ($current_challenge_rank = self::_getCurrentChallengeUserGeneralRank(
				$event_id,
				$challenge_id,
				$general_id,
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
			: self::_genUserGeneralRank($event_id, $challenge_id, $general_id, $user_id, $book_id)
		;

		if (!empty($user_rank)) {
			$user_rank = array_merge($user_rank, [
				'is_early_access'			=> self::_isEarlyAccess($user_rank['book_id']),
				'is_prime_author'			=> self::_isPrimeAuthor($user_rank['book_id']),
			]);
		}

		$user_rank['message'] = self::_getGeneralUserMessage($user_rank);

		return $user_rank;
	}

	public function getUserNoGeneralRank($event_id = 0, $challenge_id = 0, $general_id = 0, $user_id = 0, $book_id = 0) {
		$user_rank = self::getUserGeneralRank($event_id, $challenge_id, $general_id, $user_id, $book_id);

		$user_rank = !empty($user_rank)
			? $user_rank
			: self::_genUserGeneralRank($event_id, $challenge_id, $general_id, $user_id, $book_id)
		;

		if (empty($user_rank['book_id'])) {
			$item = $this->student_model->get($user_id);

			$user_rank = [
				'id'					=> 0,
				'rank'					=> 0,
				'event_id'				=> (int)$event_id,
				'challenge_id'			=> (int)$challenge_id,
				'general_id'			=> (int)$general_id,
				'user_id'				=> (int)$user_id,
				'author_name'			=> !empty($item['first_name']) ? trim($item['first_name'] . ' ' . $item['last_name']) : '',
				'author_image'			=> $item['image'] ?? '',
				'book_image'			=> '',
				'book_id'				=> 'NA',
				'book_name'				=> 'NA',
				'book_slug'				=> 'NA',
				'score'					=> 0,
				'message'				=> _li('Unfortunately, your book wasn\'t submitted in this league.'),
				'amazon_url'			=> ''
			];
		}

		return $user_rank;
	}

	private function _formatGeneralRank($rank = 0, $item = []) {
		return [
			'id'						=> $item['id'] ?? 0,
			'rank'						=> $rank,
			'event_id'					=> $item['event_id'] ?? 0,
			'general_id'				=> $item['general_id'] ?? 0,
			'challenge_id'				=> $item['challenge_id'] ?? 0,
			'user_id'					=> $item['user_id'] ?? 0,
			'author_name'				=> $item['author_name'] ?? '',
			'author_image'				=> $item['author_image'] ?? '',
			'book_id'					=> $item['book_id'] ?? 0,
			'book_name'					=> $item['book_name'] ?? '',
			'book_image'				=> $item['book_image'] ?? '',
			'book_slug'					=> $item['book_slug'] ?? '',
			'is_early_access'			=> self::_isEarlyAccess($item['book_id'] ?? ''),
			'is_prime_author'			=> self::_isPrimeAuthor($item['book_id'] ?? ''),
			'score'						=> $item['score'] ?? 0,
			'is_moved'					=> $item['is_moved'] ?? 0,
			'message' 					=> self::_getGeneralUserMessage(array_merge($item, [
				'rank'					=> $rank,
			])),
		];
	}

	private function _initGeneralRanks($event_id = 0, $challenge_id = 0, $general_id = 0) {
		$filter_data = [
			'challenge_id'	=> (int)$challenge_id,
			'event_id'		=> (int)$event_id,
			'general_id'	=> (int)$general_id,
			'start'			=> 0,
			'limit'			=> 100,
		];

		$results = $this->ranking_general_model->get_all($filter_data)['rows'] ?? [];

		$ranks = [];

		foreach ($results as $key => $item) {
			$ranks[$item['book_id']] = self::_formatGeneralRank($key + 1, $item);
		}

		return $ranks;
	}

	private function _pushGeneralUpdate($rank_id = 0, $new_score = 0) {
		$rank_info = $this->ranking_general_model->get($rank_id);

		$rank_key = self::_getGeneralKey(
			$rank_info['event_id'],
			$rank_info['challenge_id'],
			$rank_info['general_id']
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

		log_kb(['Ranking::_pushGeneralUpdate' => [
			'old_rank'		=> $old_rank,
			'new_rank'		=> $new_rank,
		]]);

		$alert_payload['rank_data'] = array_merge(
			self::_formatGeneralRank($new_rank, $rank_info),
			[
				'old_rank'	=> $old_rank,
				'new_rank'	=> $new_rank,
			]
		);

		self::_notifyAppUsers(
			sprintf('bb_notifications_ranking_general_%s_%s', $rank_info['event_id'], $rank_info['general_id']),
			[
				'title'	=> _li('general_rank_update'),
				'body'	=> _li('general_rank_update'),
			]
		);

		self::_saveGeneralAlertForEveryOne($rank_info, $alert_payload);
	}

	private function _genUserGeneralRank($event_id = 0, $challenge_id = 0, $general_id = 0, $user_id = 0, $book_id = 0) {
		if (empty($user_id)) return;

		$event_challenge_info = $this->event_challenge_general_model->get($challenge_id);

		if ($rank_info = $this->ranking_general_model->get_all([
			'event_id'		=> (int)$event_id,
			'challenge_id'	=> (int)$challenge_id,
			'user_id'		=> (int)$user_id,
			'book_id'		=> (int)$book_id,
			'general_id'	=> (int)$general_id,
			'is_moved'		=> $event_challenge_info['is_moved'] ?? 0,
		])['rows'][0] ?? []) {
			return self::_formatGeneralRank(0, $rank_info);
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

		$no_sold = (!empty($item['id']) && !empty($event_id)) ? $this->event_order_model->getTotalSoldByBook($event_id, $item['id']) : 0;

		$rank_data = [
			'id'					=> 0,
			'rank'					=> 0,
			'challenge_id'			=> $challenge_id,
			'event_id'				=> $event_id,
			'user_id'				=> $user_id,
			'general_id'			=> $author_info['general_id'] ?? $general_id,
			'author_name'			=> $item['author_name'] ?? '',
			'author_image'			=> $item['author_image'] ?? '',
			'book_id'				=> $item['id'] ?? 0,
			'book_name'				=> $item['name'] ?? '',
			'book_slug'				=> $item['slug'] ?? '',
			'book_image'			=> $item['cover_image'] ?? '',
			'is_early_access'		=> self::_isEarlyAccess($item['id'] ?? 0),
			'is_prime_author'		=> self::_isPrimeAuthor($item['id'] ?? 0),
			'score'					=> $no_sold,
			'is_moved'				=> $item['is_moved'] ?? 0,
		];

		return array_merge(
			$rank_data,
			[
				'message' => self::_getGeneralUserMessage($rank_data)
			],
		);
	}

	private function _addMessageToGeneralRanks(&$ranks = []) {
		foreach ($ranks as &$item) {
			$item['message'] = self::_getGeneralUserMessage($item);
		}
	}

	public function getGeneralTopRank($score = 100, $rank = [], $full_rank = false) {
		return self::_getGeneralRankScore($score, $rank, $full_rank);
	}

	private function _getGeneralRankScore($u_rank = 100, $rank = [], $full_rank = false) {
		$rank_key = self::_getGeneralKey(
			(int)$rank['event_id'],
			(int)$rank['challenge_id'],
			(int)$rank['general_id'],
		);

		$result = array_keys($this->redis_lib->getRanks($rank_key, $u_rank - 1, $u_rank - 1));
		$user_rank = $this->ranking_general_model->get($result[0] ?? '');

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

	private function _getGeneralUserMessage($rank = []) {
		if (empty($rank['book_id'])) return;

		$total_sold = !empty($rank['book_id'])
			? ($this->event_order_model->getTotalSoldByBook($rank['event_id'], $rank['book_id']) ?? 0)
			: 0
		;

		if (in_array($rank['user_id'], BB_UID)) {
			$total_sold = 80 + $total_sold;
		}

		log_kb([
			'_getGeneralUserMessage' => [
				$total_sold,
				$rank,
			]
		]);

		$book_info  = $this->book_model->get($rank['book_id'] ?? 0);
		$challenge_info  = $this->event_challenge_general_model->get($rank['challenge_id'] ?? 0);

		if (!empty($challenge_info) && date('Y-m-d H:i:s') > $challenge_info['end_date']) {
			return sprintf(_li('%s Is Closed Now!'), $challenge_info['name']);
		}

		if (!empty($rank['is_moved']) || ($total_sold > $challenge_info['max_book_sold'])) {
			return _li('Your Book has been promoted to the next league');
		}

		$event_book_info = $this->event_book_model->get_all(['book_id' => $rank['book_id']])['rows'][0] ?? [];

		if (!empty($book_info) && !empty($challenge_info) && !empty($event_book_info)) {
			if (
				$event_book_info['date_added'] > $challenge_info['date_published'] ||
				$event_book_info['date_added'] < $challenge_info['start_date']
			) {
				return _li('Unfortunately, your book wasn\'t submitted in this league.');
			}
		}

		if (empty($rank['score'])) {
			return sprintf(_li('Buy/Sell at least %s copy to participate in the %s.'), $challenge_info['book_sold'], $challenge_info['name']);
		} else {
			return method_exists($this, sprintf('_getGeneralEventMessage_%s', $rank['event_id']))
				? self::{sprintf('_getGeneralEventMessage_%s', $rank['event_id'])}($total_sold, $rank, $challenge_info )
				: self::_getGeneralEventMessage($total_sold, $rank, $challenge_info );
		}
	}

	private function _getGeneralEventMessage($total_sold = 0, $rank = [], $challenge_info = []) {
		$rank_breakpoints = $this->league_break_point_message_model->get_all([
			'event_id'		=> (int)$rank['event_id'],
			'challenge_id'	=> (int)$rank['challenge_id'],
			'type'			=> 'general',
			'sort'			=> 'league_breakpoint_message.breakpoint',
			'order'			=> 'DESC',
		])['rows'] ?? [];

		foreach ($rank_breakpoints as $index => $breakpoint) {
			if ($rank['rank'] > $breakpoint['breakpoint']) {
				$required_sold_count = self::_getGeneralRankScore($breakpoint['breakpoint'], $rank) - $rank['score'] + 1;

				return self::formatLeagueMessage($breakpoint['message'], [
					'required_sold_count' 	=> $required_sold_count,
					'copy_text' 			=> self::_getCopyText($required_sold_count),
				]);
			}
		}

		$author_info = $this->student_model->get($rank['user_id']);
		$state_info = $this->state_model->get($author_info['state_id'] ?? 0);

		log_kb(['_getGeneralEventMessage::' => [
			$rank,
			$challenge_info,
		]]);

		if (empty($rank['rank'])) {
			return sprintf(_li('Buy/Sell %s %s more to participate in the bestseller league'), ($challenge_info['book_sold'] - $total_sold), self::_getCopyText(($challenge_info['book_sold'] - $total_sold)));
		}

		return sprintf(_li('Buy/Sell at least one copy more to participate in the bestseller league of %s'), $state_info['name'] ?? '');
	}

	private function _saveGeneralAlertForEveryOne($rank_info = [], $alert_payload = []) {
		$users = self::_getLiveGeneralUsers($rank_info['event_id'], $rank_info['challenge_id'], $rank_info['general_id']);

		log_kb(['_saveGeneralAlertForEveryOne' => $users, [$alert_payload]]);

		foreach ($users as $user_id) {
			$this->cache->save(
				self::_getGeneralRankKey($rank_info['event_id'], $rank_info['challenge_id'], $rank_info['general_id'], $user_id),
				json_encode($alert_payload),
				300
			);
		}
	}

	private function _updateLiveGeneralUser($event_id = 0, $challenge_id = 0, $general_id = 0, $user_id = 0) {
		$users = self::_getLiveGeneralUsers($event_id, $challenge_id, $general_id);

		if (!in_array($user_id, $users)) {
			$users[] = $user_id;
		} else {
			return;
		}

		log_kb(['_updateLiveGeneralUser::new' => $users, [$user_id, $general_id]]);

		$this->cache->save(self::_getLiveGeneralUserKey($event_id, $challenge_id, $general_id), json_encode($users), 900);
	}

	private function _getLiveGeneralUsers($event_id = 0, $challenge_id = 0, $general_id = 0) {
		$users = json_decode($this->cache->get(self::_getLiveGeneralUserKey($event_id, $challenge_id, $general_id)), true);

		return $users ?? [];
	}

	private function _getLiveGeneralUserKey($event_id = 0, $challenge_id = 0, $general_id = 0) {
		return vsprintf('event_live_general_users_%s_%s_%s', [
			(int)$event_id,
			(int)$challenge_id,
			(int)$general_id,
		]);
	}

	private function _getGeneralRankKey($event_id = 0, $challenge_id = 0, $general_id = 0, $user_id = 0) {
		return vsprintf('%s_general_rank_update_%s_%s_%s', [
			(int)$event_id,
			(int)$challenge_id,
			(int)$general_id,
			$user_id,
		]);
	}

	private function _getGeneralKey($event_id = 0, $challenge_id = 0, $general_id = 0) {
		return vsprintf('live_author_general_ranks_%s_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$event_id,
			$challenge_id,
			$general_id,
		]);
	}
}
