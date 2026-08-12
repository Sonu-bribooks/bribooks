<?php defined('BASEPATH') or exit('No direct script access allowed');

trait GenreRanking {
	public function pushGenreUpdateRank($rank_id = 0) {
		self::_pushGenreUpdate($rank_id, 0);
	}

	public function removeFromGenreRank($rank_id = 0) {
		$rank_info = $this->ranking_genre_model->get($rank_id);

		if (empty($rank_info)) return;

		$rank_key = self::_getgenreKey(
			$rank_info['event_id'],
			$rank_info['challenge_id'],
			$rank_info['genre_id']
		);

		$this->redis_lib->removeFromRank($rank_key, $rank_info['id']);
	}

	public function updateGenreBookInfo($book_id = 0) {
		if ($ranks = $this->ranking_genre_model->get_all([
			'book_id'	=> $book_id,
		])['rows'] ?? []) {
			$book_info = $this->book_model->get($book_id);

			if (empty($book_info)) return;

			foreach ($ranks as $rank_info) {
				self::_updateBookInfo(
					'user_rank_genre',
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

	public function updateGenreRank($data = []) {
		$book_info 	= $data['book_info'] ?? [];
		$event_info = $data['event_info'] ?? [];
		$product 	= $data['product'] ?? [];

		log_kb([
			'updating genre Rank::' => $data,
		]);

		if (empty($book_info) || empty($event_info) || empty($product)) {
			return;
		}

		$no_sold = $this->event_order_model->getTotalSoldByBook($event_info['id'], $book_info['id']);

		$author_info = $this->student_model->get($book_info['user_id']);

		$genre_challenge_info = $this->event_challenge_genre_model->get_all([
			'type'					=> 'user',
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (empty($genre_challenge_info)) return;

		if ($no_sold < $genre_challenge_info['book_sold']) return;

		if (!empty($genre_challenge_info['is_moved']) && $genre_challenge_info['max_book_sold'] && $no_sold > $genre_challenge_info['max_book_sold']) {
			$rank_info = $this->ranking_genre_model->get_all([
				'challenge_id'				=> (int)$genre_challenge_info['id'],
				'event_id'					=> (int)$event_info['id'],
				'user_id'					=> (int)$book_info['user_id'],
				'book_id'					=> (int)$book_info['id'],
			])['rows'][0] ?? [];

			if (!empty($rank_info)) {
				$rank_key = self::_getGenreKey(
					$rank_info['event_id'],
					$rank_info['challenge_id'],
					$rank_info['genre_id']
				);

				self::_moveToUpperLevel($rank_key, $rank_info, 'genre');
			}

			return;
		}

		$total_book_sold = 0;

		if ($rank_genre_info = $this->ranking_genre_model->get_all([
			'challenge_id'				=> (int)$genre_challenge_info['id'],
			'event_id'					=> (int)$event_info['id'],
			'user_id'					=> (int)$book_info['user_id'],
			'book_id'					=> (int)$book_info['id'],
		])['rows'][0] ?? []) {
			$rank_id = $rank_genre_info['id'];

			// $total_book_sold = (int)($rank_genre_info['score'] + $product['quantity']);
			$total_book_sold = (int)$no_sold;

			$this->ranking_genre_model->edit($rank_genre_info['id'], [
				'score'					=> $total_book_sold,
			]);

			self::_pushGenreUpdate($rank_id, $product['quantity']);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '=',
				'type'			=> 'genre',
			]);
		} else {
			if (!empty($genre_challenge_info['date_published'])) {
				if (empty($book_info['id'])) return;

				$event_book_info = $this->event_book_model->get_all(['book_id' => $book_info['id']])['rows'][0] ?? [];

				if (
					$event_book_info['date_added'] > $genre_challenge_info['date_published'] ||
					$book_info['date_added'] < $genre_challenge_info['start_date']
				) return;
			}

			$total_book_sold = (int)$no_sold;

			$rank_id = $this->ranking_genre_model->add([
				'challenge_id'			=> (int)$genre_challenge_info['id'],
				'event_id'				=> (int)$event_info['id'],
				'user_id'				=> (int)$book_info['user_id'],
				'genre_id'				=> (int)$book_info['genre_id'],
				'author_name'			=> $book_info['author_name'],
				'author_image'			=> $book_info['author_image'],
				'book_id'				=> (int)$book_info['id'],
				'book_name'				=> $book_info['name'],
				'book_slug'				=> $book_info['slug'],
				'book_image'			=> $book_info['cover_image'],
				'score'					=> $total_book_sold,
			]);

			self::_pushGenreUpdate($rank_id, $total_book_sold);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '>',
				'type'			=> 'genre',
			]);
		}
	}

	public function getGenreTotal($event_id = 0, $challenge_id = 0, $genre_id = 0) {
		$rank_key = self::_getGenreKey($event_id, $challenge_id, $genre_id);
		return $this->redis_lib->getTotal($rank_key);
	}

	public function getGenreRanks($event_id = 0, $challenge_id = 0, $genre_id = 0, $page = 1, $search = NULL) {
		// $this->cache->clean();

		$ranks 		= [];
		$rank_key 	= self::_getGenreKey($event_id, $challenge_id, $genre_id);
		$start 		= $page > 0 ? ($page - 1) * $this->limit : 0;
		$end 		= $start + $this->limit - 1;

		if (!empty($search)) {
			$results = [];

			$rank_results = $this->ranking_genre_model->get_all([
				'event_id'					=> (int)$event_id,
				'challenge_id'				=> (int)$challenge_id,
				'genre_id'					=> (int)$genre_id,
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
			$rank_info 	= $this->ranking_genre_model->get($rank_id);
			$ranks[] 	= self::_formatGenreRank(
				$this->redis_lib->getRank($rank_key, $rank_id) + 1,
				$rank_info
			);
		}

		log_kb(['Ranking_lib::getGenreRanks::ranks::' => [$results, $ranks]]);

		return ['ranks' => $ranks, 'total' => $total];
	}

	public function getGenreUpdate($event_id = 0, $challenge_id = 0, $genre_id = 0, $user_id = 0) {
		self::_updateLiveGenreUser($event_id, $challenge_id, $genre_id, $user_id);

		$genre_rank_key = self::_getGenreRankKey($event_id, $challenge_id, $genre_id, $user_id);

		$json = json_decode($this->cache->get($genre_rank_key), true);

		log_kb(['Ranking_lib::getGenreUpdate::' => [
			$json,
			$genre_rank_key,
		]]);

		self::removeGenreUserUpdate($event_id, $challenge_id, $genre_id, $user_id);

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

	public function removeGenreUserUpdate($event_id = 0, $challenge_id = 0, $genre_id = 0, $user_id = 0) {
		$rank_key = self::_getGenreRankKey($event_id, $challenge_id, $genre_id, $user_id);

		log_kb([
			'rank_key' => $rank_key
		]);

		$this->cache->delete($rank_key);
	}

	private function _getCurrentChallengeUserGenreRank($event_id = 0, $challenge_id = 0, $genre_id = 0, $user_id = 0, $book_id = 0) {
		$rank_key = self::_getGenreKey($event_id, $challenge_id, $genre_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'challenge_id'				=> (int)$challenge_id,
			'genre_id'					=> (int)$genre_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'is_moved'					=> 0,
		];

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_genre_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		return $user_rank;
	}

	public function getUserGenreRank($event_id = 0, $challenge_id = 0, $genre_id = 0, $user_id = 0, $book_id = 0) {
		$rank_key = self::_getGenreKey($event_id, $challenge_id, $genre_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'challenge_id'				=> (int)$challenge_id,
			'genre_id'					=> (int)$genre_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'is_moved'					=> 0,
		];

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_genre_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		if (!empty($user_rank['is_moved'])) {
			if ($current_challenge_rank = self::_getCurrentChallengeUserGenreRank(
				$event_id,
				$challenge_id,
				$genre_id,
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
			: self::_genUserGenreRank($event_id, $challenge_id, $genre_id, $user_id, $book_id)
		;

		if (!empty($user_rank)) {
			$user_rank = array_merge($user_rank, [
				'is_early_access'			=> self::_isEarlyAccess($user_rank['book_id']),
				'is_prime_author'			=> self::_isPrimeAuthor($user_rank['book_id']),
			]);
		}

		$user_rank['message'] = self::_getGenreUserMessage($user_rank);

		return $user_rank;
	}

	public function getUserNoGenreRank($event_id = 0, $challenge_id = 0, $genre_id = 0, $user_id = 0, $book_id = 0) {
		$user_rank = self::getUserGenreRank($event_id, $challenge_id, $genre_id, $user_id, $book_id);

		$user_rank = !empty($user_rank)
			? $user_rank
			: self::_genUserGenreRank($event_id, $challenge_id, $genre_id, $user_id, $book_id)
		;

		return $user_rank;
	}

	private function _formatGenreRank($rank = 0, $item = []) {
		return [
			'id'						=> $item['id'],
			'rank'						=> $rank,
			'event_id'					=> $item['event_id'],
			'genre_id'					=> $item['genre_id'],
			'challenge_id'				=> $item['challenge_id'],
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
			'message' 					=> self::_getGenreUserMessage(array_merge($item, [
				'rank'					=> $rank,
			])),
		];
	}

	private function _initGenreRanks($event_id = 0, $challenge_id = 0, $genre_id = 0) {
		$filter_data = [
			'challenge_id'				=> (int)$challenge_id,
			'event_id'					=> (int)$event_id,
			'genre_id'					=> (int)$genre_id,
			'start'						=> 0,
			'limit'						=> 100,
		];

		$results = $this->ranking_genre_model->get_all($filter_data)['rows'] ?? [];

		$ranks = [];

		foreach ($results as $key => $item) {
			$ranks[$item['book_id']] = self::_formatGenreRank($key + 1, $item);
		}

		return $ranks;
	}

	private function _pushGenreUpdate($rank_id = 0, $new_score = 0) {
		$rank_info = $this->ranking_genre_model->get($rank_id);

		$rank_key = self::_getGenreKey(
			$rank_info['event_id'],
			$rank_info['challenge_id'],
			$rank_info['genre_id']
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

		log_kb(['Ranking::_pushGenreUpdate' => [
			'old_rank'		=> $old_rank,
			'new_rank'		=> $new_rank,
		]]);

		$alert_payload['rank_data'] = array_merge(
			self::_formatGenreRank($new_rank, $rank_info),
			[
				'old_rank'	=> $old_rank,
				'new_rank'	=> $new_rank,
			]
		);

		self::_notifyAppUsers(
			sprintf('bb_notifications_ranking_genre_%s_%s', $rank_info['event_id'], $rank_info['genre_id']),
			[
				'title'	=> _li('genre_rank_update'),
				'body'	=> _li('genre_rank_update'),
			]
		);

		self::_savegenreAlertForEveryOne($rank_info, $alert_payload);
	}

	private function _genUserGenreRank($event_id = 0, $challenge_id = 0, $genre_id = 0, $user_id = 0, $book_id = 0) {
		if ($rank_info = $this->ranking_genre_model->get_all([
			'event_id'					=> (int)$event_id,
			'challenge_id'				=> (int)$challenge_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'genre_id'					=> (int)$genre_id,
			'is_moved'					=> 0,
		])['rows'][0] ?? []) {
			return self::_formatGenreRank(0, $rank_info);
		}

		$event_book_info = $this->db->select('book.*, event_book.date_added as event_book_date_added')
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

		if (empty($event_book_info)) {
			return;
		}

		$genre_challenge_info = $this->event_challenge_genre_model->get_all([
			'type'					=> 'user',
			'event_id'				=> (int)$event_id,
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (empty($genre_challenge_info)) return;

		$book_genre_id = $event_book_info['genre_id'] ?? 0;

		if ($book_genre_id != $genre_id) return;

		if (
			!empty($genre_challenge_info['date_published']) &&
			(
				$event_book_info['event_book_date_added'] > $genre_challenge_info['date_published'] ||
				$event_book_info['date_added'] < $genre_challenge_info['start_date']
			)
		) return;

		$no_sold = $this->event_order_model->getTotalSoldByBook($event_id, $event_book_info['id']);

		$rank_data = [
			'id'					=> 0,
			'rank'					=> 0,
			'challenge_id'			=> $challenge_id,
			'event_id'				=> $event_id,
			'user_id'				=> $user_id,
			'genre_id'				=> $genre_id,
			'author_name'			=> $event_book_info['author_name'],
			'author_image'			=> $event_book_info['author_image'],
			'book_id'				=> $event_book_info['id'],
			'book_name'				=> $event_book_info['name'],
			'book_slug'				=> $event_book_info['slug'],
			'book_image'			=> $event_book_info['cover_image'],
			'is_early_access'		=> self::_isEarlyAccess($event_book_info['id']),
			'is_prime_author'		=> self::_isPrimeAuthor($event_book_info['id']),
			'score'					=> $no_sold,
		];

		return array_merge(
			$rank_data,
			[
				'message' => self::_getGenreUserMessage($rank_data)
			],
		);
	}

	private function _addMessageToGenreRanks(&$ranks = []) {
		foreach ($ranks as &$item) {
			$item['message'] = self::_getGenreUserMessage($item);
		}
	}

	public function getGenreTopRank($score = 100, $rank = [], $full_rank = false) {
		return self::_getGenreRankScore($score, $rank, $full_rank);
	}

	private function _getGenreRankScore($u_rank = 100, $rank = [], $full_rank = false) {
		$rank_key = self::_getGenreKey(
			(int)$rank['event_id'],
			(int)$rank['challenge_id'],
			(int)$rank['genre_id'],
		);

		$result = array_keys($this->redis_lib->getRanks($rank_key, $u_rank - 1, $u_rank - 1));
		$user_rank = $this->ranking_genre_model->get($result[0] ?? '');

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

	private function _getGenreUserMessage($rank = []) {
		if (empty($rank['book_id'])) return;

		$total_sold = !empty($rank['book_id'])
			? ($this->event_order_model->getTotalSoldByBook($rank['event_id'], $rank['book_id']) ?? 0)
			: 0
		;

		if (in_array($rank['user_id'], BB_UID)) {
			$total_sold = 80 + $total_sold;
		}

		log_kb([
			'_getGenreUserMessage' => [
				$total_sold,
				$rank,
			]
		]);

		$genre_challenge_info = $this->event_challenge_genre_model->get($rank['challenge_id']);

		$genre_name = $this->genre_model->get($rank['genre_id'])['name'] ?? '';

		if (!empty($genre_challenge_info) && date('Y-m-d H:i:s') > $genre_challenge_info['end_date']) {
			return sprintf(_li('%s Author\'s Best Seller League is closed now!'), $genre_name ?? 'Genre League');
		}

		if (!empty($rank['is_moved']) || ($total_sold > $genre_challenge_info['max_book_sold'])) {
			return _li('Your Book has been promoted to the next league');
		}

		if (empty($rank['score'])) {
			return sprintf(_li('Buy/Sell at least %s copy to participate in the %s Best Seller League'), $genre_challenge_info['book_sold'], ucwords($genre_name));
		} else {
			return method_exists($this, sprintf('_getGenreEventMessage_%s', $rank['event_id']))
				? self::{sprintf('_getGenreEventMessage_%s', $rank['event_id'])}($total_sold, $rank, $genre_challenge_info)
				: self::_getGenreEventMessage($total_sold, $rank, $genre_challenge_info);
		}
	}

	private function _getGenreEventMessage($total_sold = 0, $rank = [], $challenge_info = []) {
		$author_info = $this->student_model->get($rank['user_id']);

		$genre_name = '';

		if (!empty($rank['genre_id'])) {
			$genre_name = $this->genre_model->get($rank['genre_id'])['name'] ?? '';
		}

		$rank_breakpoints = $this->league_break_point_message_model->get_all([
			'event_id'		=> (int)$rank['event_id'],
			'challenge_id'	=> (int)$rank['challenge_id'],
			'type'			=> 'genre',
			'sort'			=> 'league_breakpoint_message.breakpoint',
			'order'			=> 'DESC',
		])['rows'] ?? [];

		foreach ($rank_breakpoints as $index => $breakpoint) {
			if ($rank['rank'] > $breakpoint['breakpoint']) {
				$required_sold_count = self::_getGenreRankScore($breakpoint['breakpoint'], $rank) - $rank['score'] + 1;

				return self::formatLeagueMessage($breakpoint['message'], [
					'required_sold_count' 	=> $required_sold_count,
					'copy_text' 			=> self::_getCopyText($required_sold_count),
				]);
			}
		}

		log_kb(['_getgenreEventMessage' => [
			$author_info,
			$genre_name,
			$rank,
		]]);

		if (empty($rank['rank'])) {
			return sprintf(_li('Buy/Sell %s %s more to participate in the %s Best Seller League'), ($challenge_info['book_sold'] - $total_sold), self::_getCopyText(($challenge_info['book_sold'] - $total_sold)), ucwords($genre_name));
		}

		return sprintf(_li('Buy/Sell at least one copy more to participate in the %s Best Seller league'), ucwords($genre_name));
	}

	private function _saveGenreAlertForEveryOne($rank_info = [], $alert_payload = []) {
		$users = self::_getLiveGenreUsers($rank_info['event_id'], $rank_info['challenge_id'], $rank_info['genre_id']);

		log_kb(['_saveGenreAlertForEveryOne' => $users, [$alert_payload]]);

		foreach ($users as $user_id) {
			$this->cache->save(
				self::_getGenreRankKey($rank_info['event_id'], $rank_info['challenge_id'], $rank_info['genre_id'], $user_id),
				json_encode($alert_payload),
				300
			);
		}
	}

	private function _updateLiveGenreUser($event_id = 0, $challenge_id = 0, $genre_id = 0, $user_id = 0) {
		$users = self::_getLiveGenreUsers($event_id, $challenge_id, $genre_id);

		if (!in_array($user_id, $users)) {
			$users[] = $user_id;
		} else {
			return;
		}

		log_kb(['_updateLiveGenreUser::new' => $users, [$user_id, $genre_id]]);

		$this->cache->save(self::_getLiveGenreUserKey($event_id, $challenge_id, $genre_id), json_encode($users), 900);
	}

	private function _getLiveGenreUsers($event_id = 0, $challenge_id = 0, $genre_id = 0) {
		$users = json_decode($this->cache->get(self::_getLiveGenreUserKey($event_id, $challenge_id, $genre_id)), true);

		return $users ?? [];
	}

	private function _getLiveGenreUserKey($event_id = 0, $challenge_id = 0, $genre_id = 0) {
		return vsprintf('event_live_genre_users_%s_%s_%s', [
			(int)$event_id,
			(int)$challenge_id,
			(int)$genre_id,
		]);
	}

	private function _getGenreRankKey($event_id = 0, $challenge_id = 0, $genre_id = 0, $user_id = 0) {
		return vsprintf('%s_genre_rank_update_%s_%s_%s', [
			(int)$event_id,
			(int)$challenge_id,
			(int)$genre_id,
			$user_id,
		]);
	}

	private function _getGenreKey($event_id = 0, $challenge_id = 0, $genre_id = 0) {
		return vsprintf('live_author_genre_ranks_%s_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$event_id,
			$challenge_id,
			$genre_id,
		]);
	}
}
