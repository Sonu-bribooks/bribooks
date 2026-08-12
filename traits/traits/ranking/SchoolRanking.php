<?php defined('BASEPATH') or exit('No direct script access allowed');

trait SchoolRanking {
	public function pushSchoolUpdateRank($rank_id = 0) {
		self::_pushSchoolUpdate($rank_id, 0);
	}

	public function removeFromSchoolRank($rank_id = 0) {
		$rank_info = $this->ranking_school_model->get($rank_id);

		if (empty($rank_info)) return;

		$rank_key = self::_getSchoolKey(
			$rank_info['event_id'],
			$rank_info['event_challenge_school_id'],
			$rank_info['school_id']
		);

		$this->redis_lib->removeFromRank($rank_key, $rank_info['id']);
	}

	public function updateSchoolBookInfo($book_id = 0) {
		if ($ranks = $this->ranking_school_model->get_all([
			'book_id'	=> $book_id,
		])['rows'] ?? []) {
			$book_info = $this->book_model->get($book_id);

			if (empty($book_info)) return;

			foreach ($ranks as $rank_info) {
				self::_updateBookInfo(
					'user_rank_school',
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

	public function updateSchoolRank($data = []) {
		$book_info 	= $data['book_info'] ?? [];
		$event_info = $data['event_info'] ?? [];
		$product 	= $data['product'] ?? [];

		log_kb([
			'updating School Rank::' => $data,
		]);

		if (empty($book_info) || empty($event_info) || empty($product)) {
			return;
		}

		$no_sold = $this->event_order_model->getTotalSoldByBook($event_info['id'], $book_info['id']);

		$author_info 	= $this->student_model->get($book_info['user_id']);
		$site_info 		= $this->site_model->get($author_info['site_id'] ?? 0);

		if (empty($site_info) || (($site_info['id'] ?? 0) == 7)) return;

		$school_challenge_info = $this->event_challenge_school_model->get_all([
			'type'					=> 'user',
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		log_kb([
			'updating School Rank::no_sold' => $no_sold,
			'updating School Rank::challenge' => $school_challenge_info,
		]);

		if (empty($school_challenge_info)) return;

		if ($no_sold < $school_challenge_info['book_sold']) return;

		if (!empty($school_challenge_info['is_moved']) && $school_challenge_info['max_book_sold'] && $no_sold > $school_challenge_info['max_book_sold']) {
			$rank_info = $this->ranking_school_model->get_all([
				'event_challenge_school_id'	=> (int)$school_challenge_info['id'],
				'event_id'					=> (int)$event_info['id'],
				'user_id'					=> (int)$book_info['user_id'],
				'book_id'					=> (int)$book_info['id'],
			])['rows'][0] ?? [];

			if (!empty($school_challenge_info['min_published'])) {
				if (!empty($rank_info)) {
					$rank_id = $this->ranking_school_model->edit($rank_info['id'], [
						'score'					=> $no_sold,
					]);

					$rank_info = $this->ranking_school_model->get($rank_info['id']);
				} else {
					$rank_id = $this->ranking_school_model->add([
						'event_challenge_school_id'	=> (int)$school_challenge_info['id'],
						'event_id'				=> (int)$event_info['id'],
						'user_id'				=> (int)$book_info['user_id'],
						'school_id'				=> (int)$author_info['site_id'],
						'author_name'			=> $book_info['author_name'],
						'author_image'			=> $book_info['author_image'],
						'book_id'				=> (int)$book_info['id'],
						'book_name'				=> $book_info['name'],
						'book_slug'				=> $book_info['slug'],
						'book_image'			=> $book_info['cover_image'],
						'score'					=> $no_sold,
					]);

					$rank_info = $this->ranking_school_model->get($rank_id);
				}
			}

			if (!empty($rank_info)) {
				$rank_key = self::_getSchoolKey(
					$rank_info['event_id'],
					$rank_info['event_challenge_school_id'],
					$rank_info['school_id']
				);

				self::_moveToUpperLevel($rank_key, $rank_info, 'school');
			}

			return;
		}

		$total_book_sold = 0;

		if ($rank_school_info = $this->ranking_school_model->get_all([
			'event_challenge_school_id'	=> (int)$school_challenge_info['id'],
			'event_id'					=> (int)$event_info['id'],
			'user_id'					=> (int)$book_info['user_id'],
			'book_id'					=> (int)$book_info['id'],
		])['rows'][0] ?? []) {
			$rank_id = $rank_school_info['id'];

			// $total_book_sold = (int)($rank_school_info['score'] + $product['quantity']);
			$total_book_sold = (int)$no_sold;

			$this->ranking_school_model->edit($rank_school_info['id'], [
				'score'					=> $total_book_sold,
			]);

			self::_pushSchoolUpdate($rank_id, $product['quantity']);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '=',
				'type'			=> 'school',
			]);
		} else {
			$total_book_sold = (int)$no_sold;

			$rank_id = $this->ranking_school_model->add([
				'event_challenge_school_id'	=> (int)$school_challenge_info['id'],
				'event_id'				=> (int)$event_info['id'],
				'user_id'				=> (int)$book_info['user_id'],
				'school_id'				=> (int)$author_info['site_id'],
				'author_name'			=> $book_info['author_name'],
				'author_image'			=> $book_info['author_image'],
				'book_id'				=> (int)$book_info['id'],
				'book_name'				=> $book_info['name'],
				'book_slug'				=> $book_info['slug'],
				'book_image'			=> $book_info['cover_image'],
				'score'					=> $total_book_sold,
			]);

			self::_pushSchoolUpdate($rank_id, $total_book_sold);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '>',
				'type'			=> 'school',
			]);
		}
	}

	public function getSchoolTotal($event_id = 0, $event_challenge_school_id = 0, $school_id = 0) {
		$rank_key = self::_getSchoolKey($event_id, $event_challenge_school_id, $school_id);
		return $this->redis_lib->getTotal($rank_key);
	}

	public function getSchoolRanks($event_id = 0, $event_challenge_school_id = 0, $school_id = 0, $page = 1, $search = NULL, $limit = 0) {
		$page 		= (int)$page;
		$limit 		= (int)$limit;
		$limit 		= min(max($limit, 10), 50);

		$ranks 		= [];
		$rank_key 	= self::_getSchoolKey($event_id, $event_challenge_school_id, $school_id);
		$start 		= $page > 0 ? ($page - 1) * $limit : 0;
		$end 		= $start + $limit - 1;

		if (!empty($search)) {
			$results = [];

			$rank_results = $this->ranking_school_model->get_all([
				'event_id'					=> (int)$event_id,
				'event_challenge_school_id'	=> (int)$event_challenge_school_id,
				'school_id'					=> (int)$school_id,
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
			$rank_info 	= $this->ranking_school_model->get($rank_id);
			$ranks[] 	= self::_formatSchoolRank(
				$this->redis_lib->getRank($rank_key, $rank_id) + 1,
				$rank_info
			);
		}

		log_kb(['Ranking_lib::getSchoolRanks::ranks::' => [$results, $ranks]]);

		return ['ranks' => $ranks, 'total' => $total];
	}

	public function getSchoolUpdate($event_id = 0, $event_challenge_school_id = 0, $school_id = 0, $user_id = 0) {
		self::_updateLiveSchoolUser($event_id, $event_challenge_school_id, $school_id, $user_id);

		$school_rank_key = self::_getSchoolRankKey($event_id, $event_challenge_school_id, $school_id, $user_id);

		$json = json_decode($this->cache->get($school_rank_key), true);

		log_kb(['Ranking_lib::getSchoolUpdate::' => [
			$json,
			$school_rank_key,
		]]);

		self::removeSchoolUserUpdate($event_id, $event_challenge_school_id, $school_id, $user_id);

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

	public function removeSchoolUserUpdate($event_id = 0, $event_challenge_school_id = 0, $school_id = 0, $user_id = 0) {
		$rank_key = self::_getSchoolRankKey($event_id, $event_challenge_school_id, $school_id, $user_id);

		log_kb([
			'rank_key' => $rank_key
		]);

		$this->cache->delete($rank_key);
	}

	private function _getCurrentChallengeUserSchoolRank($event_id = 0, $event_challenge_school_id = 0, $school_id = 0, $user_id = 0, $book_id = 0) {
		$event_challenge_school_info = $this->event_challenge_school_model->get($event_challenge_school_id);

		$rank_key = self::_getSchoolKey($event_id, $event_challenge_school_id, $school_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_school_id'	=> (int)$event_challenge_school_id,
			'school_id'					=> (int)$school_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
		];

		if (empty($event_challenge_school_info['min_published'] ?? 0)) {
			$filter_data['is_moved'] = 0;
		}

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_school_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		return $user_rank;
	}

	public function getUserSchoolRank($event_id = 0, $event_challenge_school_id = 0, $school_id = 0, $user_id = 0, $book_id = 0) {
		$event_challenge_school_info = $this->event_challenge_school_model->get($event_challenge_school_id);
		
		$rank_key = self::_getSchoolKey($event_id, $event_challenge_school_id, $school_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_school_id'	=> (int)$event_challenge_school_id,
			'school_id'					=> (int)$school_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
		];

		if (empty($event_challenge_school_info['min_published'] ?? 0)) {
			$filter_data['is_moved'] = 0;
		}

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_school_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		if (!empty($user_rank['is_moved'])) {
			if ($current_challenge_rank = self::_getCurrentChallengeUserSchoolRank(
				$event_id,
				$event_challenge_school_id,
				$school_id,
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
			: self::_genUserSchoolRank($event_id, $event_challenge_school_id, $school_id, $user_id, $book_id)
		;

		if (!empty($user_rank)) {
			$user_rank = array_merge($user_rank, [
				'is_early_access'			=> self::_isEarlyAccess($user_rank['book_id']),
				'is_prime_author'			=> self::_isPrimeAuthor($user_rank['book_id']),
			]);
		}

		$user_rank['message'] = self::_getSchoolUserMessage($user_rank);

		return $user_rank;
	}

	public function getUserNoSchoolRank($event_id = 0, $event_challenge_school_id = 0, $school_id = 0, $user_id = 0, $book_id = 0) {

		$user_rank = self::getUserSchoolRank($event_id, $event_challenge_school_id, $school_id, $user_id, $book_id);
		log_kb([
			'getUserNoSchoolRank' => $user_rank
		]);

		$user_rank = !empty($user_rank)
			? $user_rank
			: self::_genUserSchoolRank($event_id, $event_challenge_school_id, $school_id, $user_id, $book_id)
		;

		$author_info = $this->student_model->get($user_id);

		if (empty($user_rank['book_id'])) {
			$item = $this->student_model->get($user_id);

			$user_rank = [
				'id'					=> 0,
				'rank'					=> 0,
				'event_id'				=> (int)$event_id,
				'event_challenge_school_id'=> (int)$event_challenge_school_id,
				'school_id'				=> (int)$school_id,
				'user_id'				=> (int)$user_id,
				'author_name'			=> trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '')),
				'author_image'			=> $item['image'] ?? '',
				'book_image'			=> '',
				'book_id'				=> 'NA',
				'book_name'				=> 'NA',
				'book_slug'				=> 'NA',
				'score'					=> 0,
				'message'				=> (in_array($event_id, [9]))
					? _li('You haven\'t joined the School Best-Sellers League as your book is yet to be published')
					: _li('Unfortunately, your book wasn\'t submitted for this event, so you can\'t participate in the Best Seller League.'),
				'amazon_url'			=> ''
			];
		}

		return $user_rank;
	}

	private function _formatSchoolRank($rank = 0, $item = []) {
		return [
			'id'						=> $item['id'] ?? 0,
			'rank'						=> $rank,
			'event_id'					=> $item['event_id'] ?? 0,
			'school_id'					=> $item['school_id'] ?? 0,
			'event_challenge_school_id'	=> $item['event_challenge_school_id'] ?? 0,
			'user_id'					=> $item['user_id'] ?? 0,
			'author_name'				=> $item['author_name'] ?? '',
			'author_image'				=> $item['author_image'] ?? '',
			'book_id'					=> $item['book_id'] ?? '',
			'book_name'					=> $item['book_name'] ?? '',
			'book_image'				=> $item['book_image'] ?? '',
			'book_slug'					=> $item['book_slug'] ?? '',
			'is_early_access'			=> self::_isEarlyAccess($item['book_id'] ?? 0),
			'is_prime_author'			=> self::_isPrimeAuthor($item['book_id'] ?? 0),
			'score'						=> $item['score'] ?? 0,
			'is_moved'					=> $item['is_moved'] ?? 0,
			'message' 					=> self::_getSchoolUserMessage(array_merge($item ?? [], [
				'rank'					=> $rank,
			])),
		];
	}

	private function _initSchoolRanks($event_id = 0, $event_challenge_school_id = 0, $school_id = 0) {
		$filter_data = [
			'event_challenge_school_id'	=> (int)$event_challenge_school_id,
			'event_id'					=> (int)$event_id,
			'school_id'					=> (int)$school_id,
			'start'						=> 0,
			'limit'						=> 100,
		];

		$results = $this->ranking_school_model->get_all($filter_data)['rows'] ?? [];

		$ranks = [];

		foreach ($results as $key => $item) {
			$ranks[$item['book_id']] = self::_formatSchoolRank($key + 1, $item);
		}

		return $ranks;
	}

	private function _pushSchoolUpdate($rank_id = 0, $new_score = 0) {
		$rank_info = $this->ranking_school_model->get($rank_id);

		$rank_key = self::_getSchoolKey(
			$rank_info['event_id'],
			$rank_info['event_challenge_school_id'],
			$rank_info['school_id']
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

		log_kb(['Ranking::_pushSchoolUpdate' => [
			'old_rank'		=> $old_rank,
			'new_rank'		=> $new_rank,
		]]);

		$alert_payload['rank_data'] = array_merge(
			self::_formatSchoolRank($new_rank, $rank_info),
			[
				'old_rank'	=> $old_rank,
				'new_rank'	=> $new_rank,
			]
		);

		self::_notifyAppUsers(
			sprintf('bb_notifications_ranking_school_%s_%s', $rank_info['event_id'], $rank_info['school_id']),
			[
				'title'	=> _li('school_rank_update'),
				'body'	=> _li('school_rank_update'),
			]
		);

		self::_saveSchoolAlertForEveryOne($rank_info, $alert_payload);
	}

	private function _genUserSchoolRank($event_id = 0, $event_challenge_school_id = 0, $school_id = 0, $user_id = 0, $book_id = 0) {
		$event_challenge_school_info = $this->event_challenge_school_model->get($event_challenge_school_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_school_id'	=> (int)$event_challenge_school_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'school_id'					=> (int)$school_id,
		];

		if (empty($event_challenge_school_info['min_published'] ?? 0)) {
			$filter_data['is_moved'] = 0;
		}


		if ($rank_info = $this->ranking_school_model->get_all($filter_data)['rows'][0] ?? []) {
			return self::_formatSchoolRank(0, $rank_info);
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

		if ($school_id != ($author_info['site_id'] ?? 0)) return;

		$no_sold = (!empty($item['id']) && !empty($event_id)) ? $this->event_order_model->getTotalSoldByBook($event_id, $item['id']) : 0;

		$rank_data = [
			'id'					=> 0,
			'rank'					=> 0,
			'event_challenge_school_id'=> $event_challenge_school_id,
			'event_id'				=> $event_id,
			'user_id'				=> $user_id,
			'school_id'				=> $author_info['school_id'] ?? $school_id,
			'author_name'			=> $item['author_name'] ?? '',
			'author_image'			=> $item['author_image'] ?? '',
			'book_id'				=> $item['id'] ?? '',
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
				'message' => self::_getSchoolUserMessage($rank_data)
			],
		);
	}

	private function _addMessageToSchoolRanks(&$ranks = []) {
		foreach ($ranks as &$item) {
			$item['message'] = self::_getSchoolUserMessage($item);
		}
	}

	public function getSchoolTopRank($score = 100, $rank = [], $full_rank = false) {
		return self::_getSchoolRankScore($score, $rank, $full_rank);
	}

	private function _getSchoolRankScore($u_rank = 100, $rank = [], $full_rank = false) {
		$rank_key = self::_getSchoolKey(
			(int)$rank['event_id'],
			(int)$rank['event_challenge_school_id'],
			(int)$rank['school_id'],
		);

		$result = array_keys($this->redis_lib->getRanks($rank_key, $u_rank - 1, $u_rank - 1));
		$user_rank = $this->ranking_school_model->get($result[0] ?? '');

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

	private function _getSchoolUserMessage($rank = []) {
		if (empty($rank['event_challenge_school_id'])) return;

		$total_sold = !empty($rank['book_id'])
			? ($this->event_order_model->getTotalSoldByBook($rank['event_id'], $rank['book_id']) ?? 0)
			: 0;

		if (in_array($rank['user_id'] ?? 0, BB_UID)) {
			$total_sold = 80 + $total_sold;
		}

		log_kb([
			'_getSchoolUserMessage' => [
				$total_sold,
				$rank,
			]
		]);

		$schoo_name = 'School';
		if (!empty($rank['school_id'])) {
			$schoo_name = $this->site_model->get($rank['school_id'])['name'] ?? 'School';
		}

		$school_challenge_info = $this->event_challenge_school_model->get($rank['event_challenge_school_id']);

		if (!empty($school_challenge_info) && date('Y-m-d H:i:s') > $school_challenge_info['end_date']) {
			return sprintf(_li('%s is closed now!'), $school_challenge_info['name']);
		}

		if (!empty($rank['is_moved']) || ($total_sold > $school_challenge_info['max_book_sold'])) {
			return _li('Your Book has been promoted to the next league');
		}

		if (empty($rank['score'])) {
			return sprintf(_li('Buy/Sell 1 copy to participate in the Best-Selling Young Authors’ League of %s'), $schoo_name);
		} else {
			return method_exists($this, sprintf('_getSchoolEventMessage_%s', $rank['event_id']))
				? self::{sprintf('_getSchoolEventMessage_%s', $rank['event_id'])}($total_sold, $rank, $school_challenge_info)
				: self::_getSchoolEventMessage($total_sold, $rank, $school_challenge_info);
		}
	}

	private function _getSchoolEventMessage($total_sold = 0, $rank = [], $school_challenge_info = []) {
		if ($total_sold < $school_challenge_info['max_book_sold'] && !empty($rank['rank'])) {
			$rank_breakpoints = $this->league_break_point_message_model->get_all([
				'event_id'		=> (int)$rank['event_id'],
				'challenge_id'	=> (int)$rank['event_challenge_school_id'],
				'type'			=> 'school',
				'sort'			=> 'league_breakpoint_message.breakpoint',
				'order'			=> 'DESC',
			])['rows'] ?? [];

			foreach ($rank_breakpoints as $index => $breakpoint) {
				if ($rank['rank'] > $breakpoint['breakpoint']) {
					$required_sold_count = self::_getSchoolRankScore($breakpoint['breakpoint'], $rank) - $rank['score'] + 1;

					return self::formatLeagueMessage($breakpoint['message'], [
						'required_sold_count' 	=> $required_sold_count,
						'copy_text' 			=> self::_getCopyText($required_sold_count),
					]);
				}
			}
		}

		$author_info 	= $this->student_model->get($rank['user_id']);
		$site_info 		= $this->site_model->get($author_info['site_id'] ?? 0);

		if (empty($rank['rank'])) {
			return sprintf(_li('Buy/Sell %s %s more to participate in the bestseller league of %s'), ($school_challenge_info['book_sold'] - $total_sold), self::_getCopyText(($school_challenge_info['book_sold'] - $total_sold)), $site_info['name'] ?? '');
		}

		return _li('Buy/Sell at least one copy more to participate in the School bestseller league');
		// if ($total_sold < $school_challenge_info['max_book_sold'] && !empty($rank['rank'])) {
		// 	if ($rank['rank'] <= '10') {
		// 		$required_sold_count = (self::_getSchoolRankScore(1, $rank) - $rank['score']) + 1;
		// 		return vsprintf(_li('Buy/Sell %s more %s to secure the #1 Ranking Author position!'), [
		// 			$required_sold_count,
		// 			self::_getCopyText($required_sold_count),
		// 		]);
		// 	} else {
		// 		$required_sold_count = (self::_getSchoolRankScore(10, $rank) - $rank['score']) + 1;
		// 		return vsprintf(_li('Buy/Sell %s %s more to be in top 10 Ranking Authors'), [
		// 			$required_sold_count,
		// 			self::_getCopyText($required_sold_count),
		// 		]);
		// 	}
		// }

		// if (empty($rank['rank'])) {
		// 	return sprintf(_li('Buy/Sell %s %s more to be the bestseller of your school'), ($school_challenge_info['max_book_sold'] - $total_sold), self::_getCopyText(($school_challenge_info['max_book_sold'] - $total_sold)));
		// }

		// $author_info = $this->student_model->get($rank['user_id']);
		// $city_info = $this->city_model->get($author_info['city_id']);

		// return sprintf(_li('Buy/Sell at least one copy more to participate in the bestseller league of %s'), $city_info['name'] ?? '');
	}

	private function _getSchoolEventMessage_12($total_sold = 0, $rank = [], $school_challenge_info = []) {
		if ($total_sold < $school_challenge_info['max_book_sold']) {
			return sprintf(_li('Buy/Sell %s %s more to be the bestseller of your school'), ($school_challenge_info['max_book_sold'] - $total_sold), self::_getCopyText(($school_challenge_info['max_book_sold'] - $total_sold)));
		}

		$author_info = $this->student_model->get($rank['user_id']);
		$city_info = $this->city_model->get($author_info['city_id']);

		return _li('Buy/Sell at least one copy more to participate in the bestseller league of Bhavans Kuwait');
	}

	private function _saveSchoolAlertForEveryOne($rank_info = [], $alert_payload = []) {
		$users = self::_getLiveSchoolUsers($rank_info['event_id'], $rank_info['event_challenge_school_id'], $rank_info['school_id']);

		log_kb(['_saveSchoolAlertForEveryOne' => $users, [$alert_payload]]);

		foreach ($users as $user_id) {
			$this->cache->save(
				self::_getSchoolRankKey($rank_info['event_id'], $rank_info['event_challenge_school_id'], $rank_info['school_id'], $user_id),
				json_encode($alert_payload),
				300
			);
		}
	}

	private function _updateLiveSchoolUser($event_id = 0, $event_challenge_school_id = 0, $school_id = 0, $user_id = 0) {
		$users = self::_getLiveSchoolUsers($event_id, $event_challenge_school_id, $school_id);

		if (!in_array($user_id, $users)) {
			$users[] = $user_id;
		} else {
			return;
		}

		log_kb(['_updateLiveSchoolUser::new' => $users, [$user_id, $school_id]]);

		$this->cache->save(self::_getLiveSchoolUserKey($event_id, $event_challenge_school_id, $school_id), json_encode($users), 900);
	}

	private function _getLiveSchoolUsers($event_id = 0, $event_challenge_school_id = 0, $school_id = 0) {
		$users = json_decode($this->cache->get(self::_getLiveSchoolUserKey($event_id, $event_challenge_school_id, $school_id)), true);

		return $users ?? [];
	}

	private function _getLiveSchoolUserKey($event_id = 0, $event_challenge_school_id = 0, $school_id = 0) {
		return vsprintf('event_live_school_users_%s_%s_%s', [
			(int)$event_id,
			(int)$event_challenge_school_id,
			(int)$school_id,
		]);
	}

	private function _getSchoolRankKey($event_id = 0, $event_challenge_school_id = 0, $school_id = 0, $user_id = 0) {
		return vsprintf('%s_school_rank_update_%s_%s_%s', [
			(int)$event_id,
			(int)$event_challenge_school_id,
			(int)$school_id,
			$user_id,
		]);
	}

	private function _getSchoolKey($event_id = 0, $event_challenge_school_id = 0, $school_id = 0) {
		return vsprintf('live_author_school_ranks_%s_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$event_id,
			$event_challenge_school_id,
			$school_id,
		]);
	}
}
