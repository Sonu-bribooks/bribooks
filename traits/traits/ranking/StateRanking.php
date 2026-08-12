<?php defined('BASEPATH') or exit('No direct script access allowed');

trait StateRanking {
	public function pushStateUpdateRank($rank_id = 0) {
		self::_pushStateUpdate($rank_id, 0);
	}

	public function removeFromStateRank($rank_id = 0) {
		$rank_info = $this->ranking_state_model->get($rank_id);

		if (empty($rank_info)) return;

		$rank_key = self::_getStateKey(
			$rank_info['event_id'],
			$rank_info['event_challenge_state_id'],
			$rank_info['state_id']
		);

		$this->redis_lib->removeFromRank($rank_key, $rank_info['id']);
	}

	public function updateStateBookInfo($book_id = 0) {
		if ($ranks = $this->ranking_state_model->get_all([
			'book_id'	=> $book_id,
		])['rows'] ?? []) {
			$book_info = $this->book_model->get($book_id);

			if (empty($book_info)) return;

			foreach ($ranks as $rank_info) {
				self::_updateBookInfo(
					'user_rank_state',
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

	public function updateStateRank($data = []) {
		// Limit total sold >= 5
		// falling in top 12 sites
		$book_info 	= $data['book_info'] ?? [];
		$event_info = $data['event_info'] ?? [];
		$product 	= $data['product'] ?? [];

		log_kb([
			'updating State Rank::' => $data,
		]);

		if (empty($book_info) || empty($event_info) || empty($product)) {
			return;
		}

		$no_sold = $this->event_order_model->getTotalSoldByBook($event_info['id'], $book_info['id']);

		$author_info = $this->student_model->get($book_info['user_id']);

		if (empty($author_info['state_id'] ?? 0)) return;

		$state_challenge_info = $this->event_challenge_state_model->get_all([
			'type'					=> 'user',
			'event_id'				=> (int)$event_info['id'],
			'state_id'				=> $author_info['state_id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (empty($state_challenge_info)) return;

		if ($no_sold < $state_challenge_info['book_sold']) return;

		log_kb([
			'updating State Rank::info' => $state_challenge_info,
		]);

		if (!empty($state_challenge_info['is_moved']) && $state_challenge_info['max_book_sold'] && $no_sold > $state_challenge_info['max_book_sold']) {
			$rank_info = $this->ranking_state_model->get_all([
				'event_challenge_state_id'	=> (int)$state_challenge_info['id'],
				'event_id'					=> (int)$event_info['id'],
				'user_id'					=> (int)$book_info['user_id'],
				'book_id'					=> (int)$book_info['id'],
			])['rows'][0] ?? [];

			if (!empty($state_challenge_info['min_published'])) {
				if (!empty($rank_info)) {
					$rank_id = $this->ranking_state_model->edit($rank_info['id'], [
						'score'					=> $no_sold,
					]);

					$rank_info = $this->ranking_state_model->get($rank_info['id']);
				} else {
					$rank_id = $this->ranking_state_model->add([
						'event_challenge_state_id'	=> (int)$state_challenge_info['id'],
						'event_id'				=> (int)$event_info['id'],
						'user_id'				=> (int)$book_info['user_id'],
						'state_id'				=> (int)$author_info['state_id'],
						'author_name'			=> $book_info['author_name'],
						'author_image'			=> $book_info['author_image'],
						'book_id'				=> (int)$book_info['id'],
						'book_name'				=> $book_info['name'],
						'book_slug'				=> $book_info['slug'],
						'book_image'			=> $book_info['cover_image'],
						'score'					=> $no_sold,
					]);

					$rank_info = $this->ranking_state_model->get($rank_id);
				}
			}

			if (!empty($rank_info)) {
				$rank_key = self::_getStateKey(
					$rank_info['event_id'],
					$rank_info['event_challenge_state_id'],
					$rank_info['state_id']
				);

				self::_moveToUpperLevel($rank_key, $rank_info, 'state');
			}

			return;
		}

		$total_book_sold = 0;

		if ($rank_state_info = $this->ranking_state_model->get_all([
			'event_challenge_state_id'	=> (int)$state_challenge_info['id'],
			'event_id'					=> (int)$event_info['id'],
			'user_id'					=> (int)$book_info['user_id'],
			'book_id'					=> (int)$book_info['id'],
		])['rows'][0] ?? []) {
			$rank_id = $rank_state_info['id'];

			// $total_book_sold = (int)($rank_state_info['score'] + $product['quantity']);
			$total_book_sold = (int)$no_sold;

			$this->ranking_state_model->edit($rank_state_info['id'], [
				'score'					=> $total_book_sold,
			]);

			self::_pushStateUpdate($rank_id, $product['quantity']);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '=',
				'type'			=> 'state',
			]);
		} else {
			$total_book_sold = (int)$no_sold;

			$rank_id = $this->ranking_state_model->add([
				'event_challenge_state_id'	=> (int)$state_challenge_info['id'],
				'event_id'				=> (int)$event_info['id'],
				'user_id'				=> (int)$book_info['user_id'],
				'state_id'				=> (int)$author_info['state_id'],
				'author_name'			=> $book_info['author_name'],
				'author_image'			=> $book_info['author_image'],
				'book_id'				=> (int)$book_info['id'],
				'book_name'				=> $book_info['name'],
				'book_slug'				=> $book_info['slug'],
				'book_image'			=> $book_info['cover_image'],
				'score'					=> $total_book_sold,
			]);

			self::_pushStateUpdate($rank_id, $total_book_sold);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '>',
				'type'			=> 'state',
			]);
		}
	}

	public function getStateTotal($event_id = 0, $event_challenge_state_id = 0, $state_id = 0) {
		$rank_key = self::_getStateKey($event_id, $event_challenge_state_id, $state_id);
		return $this->redis_lib->getTotal($rank_key);
	}

	public function getStateRanks($event_id = 0, $event_challenge_state_id = 0, $state_id = 0, $page = 1, $limit=0, $search = NULL) {
		$state_challenge_info = $this->event_challenge_state_model->get($event_challenge_state_id);

		$limit 		= ($limit > 0) ? $limit : ($state_challenge_info['limit'] ?: 10);

		$ranks 		= [];
		$rank_key 	= self::_getStateKey($event_id, $event_challenge_state_id, $state_id);
		$start 		= $page > 0 ? ($page - 1) * $limit : 0;
		$end 		= $start + $limit - 1;

		if (!empty($search)) {
			$results = [];

			$rank_results = $this->ranking_state_model->get_all([
				'event_id'					=> (int)$event_id,
				'event_challenge_state_id'	=> (int)$event_challenge_state_id,
				'state_id'					=> (int)$state_id,
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
			$rank_info 	= $this->ranking_state_model->get($rank_id);
			$ranks[] 	= self::_formatStateRank(
				$this->redis_lib->getRank($rank_key, $rank_id) + 1,
				$rank_info
			);
		}

		log_kb(['Ranking_lib::getStateRanks::ranks::' => [$results, $ranks]]);

		return ['ranks' => $ranks, 'total' => $total];
	}

	public function getStateUpdate($event_id = 0, $event_challenge_state_id = 0, $state_id = 0, $user_id = 0) {
		self::_updateLiveStateUser($event_id, $event_challenge_state_id, $state_id, $user_id);

		$state_rank_key = self::_getStateRankKey($event_id, $event_challenge_state_id, $state_id, $user_id);

		$json = json_decode($this->cache->get($state_rank_key), true);

		log_kb(['Ranking_lib::getStateUpdate::' => [
			$json,
			$state_rank_key,
		]]);

		self::removeStateUserUpdate($event_id, $event_challenge_state_id, $state_id, $user_id);

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

	public function removeStateUserUpdate($event_id = 0, $event_challenge_state_id = 0, $state_id = 0, $user_id = 0) {
		$rank_key = self::_getStateRankKey($event_id, $event_challenge_state_id, $state_id, $user_id);

		log_kb([
			'rank_key' => $rank_key
		]);

		$this->cache->delete($rank_key);
	}

	private function _getCurrentChallengeUserStateRank($event_id = 0, $event_challenge_state_id = 0, $state_id = 0, $user_id = 0, $book_id = 0) {
		$event_challenge_state_info = $this->event_challenge_state_model->get($event_challenge_state_id);

		$rank_key = self::_getStateKey($event_id, $event_challenge_state_id, $state_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_state_id'	=> (int)$event_challenge_state_id,
			'state_id'					=> (int)$state_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
		];

		if (empty($event_challenge_state_info['min_published']?? 0)) {
			$filter_data['is_moved'] = 0;
		}

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_state_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		return $user_rank;
	}

	public function getUserStateRank($event_id = 0, $event_challenge_state_id = 0, $state_id = 0, $user_id = 0, $book_id = 0) {
		$event_challenge_state_info = $this->event_challenge_state_model->get($event_challenge_state_id);

		$rank_key = self::_getStateKey($event_id, $event_challenge_state_id, $state_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_state_id'	=> (int)$event_challenge_state_id,
			'state_id'					=> (int)$state_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
		];

		if (empty($event_challenge_state_info['min_published']?? 0)) {
			$filter_data['is_moved'] = 0;
		}

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_state_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		if (!empty($user_rank['is_moved'])) {
			if ($current_challenge_rank = self::_getCurrentChallengeUserStateRank(
				$event_id,
				$event_challenge_state_id,
				$state_id,
				$user_id,
				$book_id
			)) {
				$user_rank = $current_challenge_rank;
			} else {
				$user_rank['score'] = $this->event_order_model->getTotalSoldByBook($user_rank['event_id'], $user_rank['book_id']);;
				$user_rank['rank'] 	= 0;
			}
		}

		log_kb(['getUserStateRank::user_rank' => $user_rank]);

		$user_rank = !empty($user_rank)
			? $user_rank
			: self::_genUserStateRank($event_id, $event_challenge_state_id, $state_id, $user_id, $book_id)
		;

		if (!empty($user_rank)) {
			$user_rank = array_merge($user_rank, [
				'is_early_access'			=> self::_isEarlyAccess($user_rank['book_id']),
				'is_prime_author'			=> self::_isPrimeAuthor($user_rank['book_id']),
			]);
		}

		$user_rank['message'] = self::_getStateUserMessage($user_rank);

		return $user_rank;
	}

	public function getUserNoStateRank($event_id = 0, $event_challenge_state_id = 0, $state_id = 0, $user_id = 0, $book_id = 0) {
		$user_rank = self::getUserStateRank($event_id, $event_challenge_state_id, $state_id, $user_id, $book_id);

		$user_rank = !empty($user_rank)
			? $user_rank
			: self::_genUserStateRank($event_id, $event_challenge_state_id, $state_id, $user_id, $book_id)
		;

		$author_info = $this->student_model->get($user_id);

		if (empty($user_rank['book_id'])) {
			$item = $this->student_model->get($user_id);

			$user_rank = [
				'id'					=> 0,
				'rank'					=> 0,
				'event_id'				=> (int)$event_id,
				'event_challenge_state_id'=> (int)$event_challenge_state_id,
				'state_id'				=> (int)$state_id,
				'user_id'				=> (int)$user_id,
				'author_name'			=> trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '')),
				'author_image'			=> $item['image'] ?? '',
				'book_image'			=> '',
				'book_id'				=> 'NA',
				'book_name'				=> 'NA',
				'book_slug'				=> 'NA',
				'score'					=> 0,
				'message'				=> _li('Unfortunately, your book wasn\'t submitted for this event, so you can\'t participate in the Best Seller League.'),
				'amazon_url'			=> ''
			];
		}

		return $user_rank;
	}

	private function _formatStateRank($rank = 0, $item = []) {
		return [
			'id'						=> $item['id'],
			'rank'						=> $rank,
			'event_id'					=> $item['event_id'],
			'state_id'					=> $item['state_id'],
			'event_challenge_state_id'	=> $item['event_challenge_state_id'],
			'user_id'					=> $item['user_id'],
			'author_name'				=> $item['author_name'],
			'author_image'				=> $item['author_image'],
			'book_id'					=> $item['book_id'],
			'state_id'					=> $item['state_id'],
			'book_name'					=> $item['book_name'],
			'book_image'				=> $item['book_image'],
			'book_slug'					=> $item['book_slug'],
			'is_early_access'			=> self::_isEarlyAccess($item['book_id']),
			'is_prime_author'			=> self::_isPrimeAuthor($item['book_id']),
			'score'						=> $item['score'],
			'message' 					=> self::_getStateUserMessage(array_merge($item ?? [], [
				'rank'					=> $rank,
			])),
		];
	}

	private function _initStateRanks($event_id = 0, $event_challenge_state_id = 0, $state_id = 0) {
		$filter_data = [
			'event_challenge_state_id'	=> (int)$event_challenge_state_id,
			'event_id'					=> (int)$event_id,
			'state_id'					=> (int)$state_id,
			'start'						=> 0,
			'limit'						=> 100,
		];

		$results = $this->ranking_state_model->get_all($filter_data)['rows'] ?? [];

		$ranks = [];

		foreach ($results as $key => $item) {
			$ranks[$item['book_id']] = self::_formatStateRank($key + 1, $item);
		}

		return $ranks;
	}

	private function _pushStateUpdate($rank_id = 0, $new_score = 0) {
		$rank_info = $this->ranking_state_model->get($rank_id);

		$rank_key = self::_getStateKey(
			$rank_info['event_id'],
			$rank_info['event_challenge_state_id'],
			$rank_info['state_id']
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

		log_kb(['Ranking::_pushStateUpdate' => [
			'old_rank'		=> $old_rank,
			'new_rank'		=> $new_rank,
		]]);

		$alert_payload['rank_data'] = array_merge(
			self::_formatStateRank($new_rank, $rank_info),
			[
				'old_rank'	=> $old_rank,
				'new_rank'	=> $new_rank,
			]
		);

		self::_notifyAppUsers(
			sprintf('bb_notifications_ranking_state_%s_%s', $rank_info['event_id'], $rank_info['state_id']),
			[
				'title'	=> _li('state_rank_update'),
				'body'	=> _li('state_rank_update'),
			]
		);

		self::_saveStateAlertForEveryOne($rank_info, $alert_payload);
	}

	private function _genUserStateRank($event_id = 0, $event_challenge_state_id = 0, $state_id = 0, $user_id = 0, $book_id = 0) {
		$event_challenge_state_info = $this->event_challenge_state_model->get($event_challenge_state_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_state_id'	=> (int)$event_challenge_state_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'state_id'					=> (int)$state_id,
		];

		if (empty($event_challenge_state_info['min_published']?? 0)) {
			$filter_data['is_moved'] = 0;
		}

		if ($rank_info = $this->ranking_state_model->get_all($filter_data)['rows'][0] ?? []) {
			return self::_formatStateRank(0, $rank_info);
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

		if ($state_id != ($author_info['state_id'] ?? 0)) return;

		$no_sold = (!empty($item['id']) && !empty($event_id)) ? $this->event_order_model->getTotalSoldByBook($event_id, $item['id']) : 0;

		$rank_data = [
			'id'					=> 0,
			'rank'					=> 0,
			'event_challenge_state_id'=> $event_challenge_state_id,
			'event_id'				=> $event_id,
			'user_id'				=> $user_id,
			'state_id'				=> $author_info['state_id'] ?? $state_id,
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
				'message' => self::_getStateUserMessage($rank_data)
			],
		);
	}

	private function _addMessageToStateRanks(&$ranks = []) {
		foreach ($ranks as &$item) {
			$item['message'] = self::_getStateUserMessage($item);
		}
	}

	public function getStateTopRank($score = 100, $rank = [], $full_rank = false) {
		return self::_getStateRankScore($score, $rank, $full_rank);
	}

	private function _getStateRankScore($u_rank = 100, $rank = [], $full_rank = false) {
		$rank_key = self::_getStateKey(
			(int)$rank['event_id'],
			(int)$rank['event_challenge_state_id'],
			(int)$rank['state_id'],
		);

		$result = array_keys($this->redis_lib->getRanks($rank_key, $u_rank - 1, $u_rank - 1));
		$user_rank = $this->ranking_state_model->get($result[0] ?? '');

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

	private function _getStateUserMessage($rank = []) {
		if (empty($rank['event_challenge_state_id'])) return;

		$total_sold = !empty($rank['book_id'])
			? ($this->event_order_model->getTotalSoldByBook($rank['event_id'], $rank['book_id']) ?? 0)
			: 0
		;

		if (in_array($rank['user_id'] ?? 0, BB_UID)) {
			$total_sold = 80 + $total_sold;
		}

		log_kb([
			'_getStateUserMessage' => [
				$total_sold,
				$rank,
			]
		]);

		$state_name = '';
		if (!empty($rank['state_id'])) {
			$state_name = $this->state_model->get($rank['state_id'])['name'] ?? 'State';
		}

		$state_challenge_info = $this->event_challenge_state_model->get($rank['event_challenge_state_id']);

		if (!empty($state_challenge_info) && date('Y-m-d H:i:s') > $state_challenge_info['end_date']) {
			return sprintf(_li('%s is closed now!'), $state_challenge_info['name']);
		}

		if (!empty($rank['is_moved']) || ($total_sold > $state_challenge_info['max_book_sold'])) {
			return _li('Your Book has been promoted to the next league');
		}

		$min_book_sold = !empty($state_challenge_info['min_published']) ? $state_challenge_info['min_published'] : $state_challenge_info['book_sold'];

		if (empty($rank['score']) || ($rank['score'] < $min_book_sold)) {
			$required_sold_count = abs($min_book_sold - $total_sold);
			return sprintf(_li('Buy/Sell %s %s to participate in the Best-Selling Young Authors’ League %s.'),
			$required_sold_count,
			self::_getCopyText($required_sold_count),
			!empty($state_name) ? (_li('of_') . $state_name) : '');
		} else {
			return method_exists($this, sprintf('_getStateEventMessage_%s', $rank['event_id']))
				? self::{sprintf('_getStateEventMessage_%s', $rank['event_id'])}($total_sold, $rank)
				: self::_getStateEventMessage($total_sold, $rank, $state_challenge_info);
		}
	}

	private function _getStateEventMessage($total_sold = 0, $rank = [], $state_challenge_info = []) {
		if ($total_sold < $state_challenge_info['max_book_sold'] && !empty($rank['rank'])) {
			$rank_breakpoints = $this->league_break_point_message_model->get_all([
				'event_id'		=> (int)$rank['event_id'],
				'challenge_id'	=> (int)$rank['event_challenge_state_id'],
				'type'			=> 'state',
				'sort'			=> 'league_breakpoint_message.breakpoint',
				'order'			=> 'DESC',
			])['rows'] ?? [];

			foreach ($rank_breakpoints as $index => $breakpoint) {
				if ($rank['rank'] > $breakpoint['breakpoint']) {
					$required_sold_count = self::_getStateRankScore($breakpoint['breakpoint'], $rank) - $rank['score'] + 1;

					return self::formatLeagueMessage($breakpoint['message'], [
						'required_sold_count' 	=> $required_sold_count,
						'copy_text' 			=> self::_getCopyText($required_sold_count),
					]);
				}
			}
		}

		$author_info 	= $this->student_model->get($rank['user_id']);
		$state_info 	= $this->state_model->get($author_info['state_id']);

		if (empty($rank['rank'])) {
			return sprintf(_li('Buy/Sell %s %s more to participate in the bestseller league of %s'), ($state_challenge_info['book_sold'] - $total_sold), self::_getCopyText(($state_challenge_info['book_sold'] - $total_sold)), $state_info['name'] ?? '');
		}

		return _li('Buy/Sell at least one copy more to participate in the National bestseller league');
	}

	private function _saveStateAlertForEveryOne($rank_info = [], $alert_payload = []) {
		$users = self::_getLiveStateUsers($rank_info['event_id'], $rank_info['event_challenge_state_id'], $rank_info['state_id']);

		log_kb(['_saveStateAlertForEveryOne' => $users, [$alert_payload]]);

		foreach ($users as $user_id) {
			$this->cache->save(
				self::_getStateRankKey($rank_info['event_id'], $rank_info['event_challenge_state_id'], $rank_info['state_id'], $user_id),
				json_encode($alert_payload),
				300
			);
		}
	}

	private function _updateLiveStateUser($event_id = 0, $event_challenge_state_id = 0, $state_id = 0, $user_id = 0) {
		$users = self::_getLiveStateUsers($event_id, $event_challenge_state_id, $state_id);

		if (!in_array($user_id, $users)) {
			$users[] = $user_id;
		} else {
			return;
		}

		log_kb(['_updateLiveStateUser::new' => $users, [$user_id, $state_id]]);

		$this->cache->save(self::_getLiveStateUserKey($event_id, $event_challenge_state_id, $state_id), json_encode($users), 900);
	}

	private function _getLiveStateUsers($event_id = 0, $event_challenge_state_id = 0, $state_id = 0) {
		$users = json_decode($this->cache->get(self::_getLiveStateUserKey($event_id, $event_challenge_state_id, $state_id)), true);

		return $users ?? [];
	}

	private function _getLiveStateUserKey($event_id = 0, $event_challenge_state_id = 0, $state_id = 0) {
		return vsprintf('event_live_state_users_%s_%s_%s', [
			(int)$event_id,
			(int)$event_challenge_state_id,
			(int)$state_id,
		]);
	}

	private function _getStateRankKey($event_id = 0, $event_challenge_state_id = 0, $state_id = 0, $user_id = 0) {
		return vsprintf('%s_state_rank_update_%s_%s_%s', [
			(int)$event_id,
			(int)$event_challenge_state_id,
			(int)$state_id,
			$user_id,
		]);
	}

	private function _getStateKey($event_id = 0, $event_challenge_state_id = 0, $state_id = 0) {
		return vsprintf('live_author_state_ranks_%s_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$event_id,
			$event_challenge_state_id,
			$state_id,
		]);
	}
}
