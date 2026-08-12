<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CityRanking {
	public function pushCityUpdateRank($rank_id = 0) {
		self::_pushCityUpdate($rank_id, 0);
	}

	public function removeFromCityRank($rank_id = 0) {
		$rank_info = $this->ranking_city_model->get($rank_id);

		if (empty($rank_info)) return;

		$rank_key = self::_getCityKey(
			$rank_info['event_id'],
			$rank_info['event_challenge_city_id'],
			$rank_info['city_id']
		);

		$this->redis_lib->removeFromRank($rank_key, $rank_info['id']);
	}

	public function updateCityBookInfo($book_id = 0) {
		if ($ranks = $this->ranking_city_model->get_all([
			'book_id'	=> $book_id,
		])['rows'] ?? []) {
			$book_info = $this->book_model->get($book_id);

			if (empty($book_info)) return;

			foreach ($ranks as $rank_info) {
				self::_updateBookInfo(
					'user_rank_city',
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

	public function updateCityRank($data = []) {
		$book_info 	= $data['book_info'] ?? [];
		$event_info = $data['event_info'] ?? [];
		$product 	= $data['product'] ?? [];

		log_kb([
			'updating City Rank::' => $data,
		]);

		if (empty($book_info) || empty($event_info) || empty($product)) {
			return;
		}

		$no_sold = $this->event_order_model->getTotalSoldByBook($event_info['id'], $book_info['id']);

		$author_info = $this->student_model->get($book_info['user_id']);

		if (empty($author_info['city_id'] ?? 0)) return;

		$city_challenge_info = $this->event_challenge_city_model->get_all([
			'type'					=> 'user',
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (!empty($territory_info = $this->union_territory_model->get_all([
			'city_id'				=> (int)$author_info['city_id'] ?? 0,
		])['rows'][0] ?? [])) return;
		

		if (empty($city_challenge_info)) return;

		if ($no_sold < $city_challenge_info['book_sold']) return;

		if (!empty($city_challenge_info['is_moved']) && $city_challenge_info['max_book_sold'] && $no_sold > $city_challenge_info['max_book_sold']) {
			$rank_info = $this->ranking_city_model->get_all([
				'event_challenge_city_id'	=> (int)$city_challenge_info['id'],
				'event_id'					=> (int)$event_info['id'],
				'user_id'					=> (int)$book_info['user_id'],
				'book_id'					=> (int)$book_info['id'],
			])['rows'][0] ?? [];

			if (!empty($city_challenge_info['min_published'])) {
				if (!empty($rank_info)) {
					$rank_id = $this->ranking_city_model->edit($rank_info['id'], [
						'score'					=> $no_sold,
					]);

					$rank_info = $this->ranking_city_model->get($rank_info['id']);
				} else {
					$rank_id = $this->ranking_city_model->add([
						'event_challenge_city_id'=> (int)$city_challenge_info['id'],
						'event_id'				=> (int)$event_info['id'],
						'user_id'				=> (int)$book_info['user_id'],
						'city_id'				=> (int)$author_info['city_id'],
						'author_name'			=> $book_info['author_name'],
						'author_image'			=> $book_info['author_image'],
						'book_id'				=> (int)$book_info['id'],
						'book_name'				=> $book_info['name'],
						'book_slug'				=> $book_info['slug'],
						'book_image'			=> $book_info['cover_image'],
						'score'					=> $no_sold,
					]);

					$rank_info = $this->ranking_city_model->get($rank_id);
				}
			}

			if (!empty($rank_info)) {
				$rank_key = self::_getCityKey(
					$rank_info['event_id'],
					$rank_info['event_challenge_city_id'],
					$rank_info['city_id']
				);

				self::_moveToUpperLevel($rank_key, $rank_info, 'city');
			}
			return;

		}

		$total_book_sold = 0;

		if ($rank_city_info = $this->ranking_city_model->get_all([
			'event_challenge_city_id'	=> (int)$city_challenge_info['id'],
			'event_id'					=> (int)$event_info['id'],
			'user_id'					=> (int)$book_info['user_id'],
			'book_id'					=> (int)$book_info['id'],
		])['rows'][0] ?? []) {
			$rank_id = $rank_city_info['id'];

			// $total_book_sold = (int)($rank_city_info['score'] + $product['quantity']);
			$total_book_sold = (int)$no_sold;

			$this->ranking_city_model->edit($rank_city_info['id'], [
				'score'					=> $total_book_sold,
			]);

			self::_pushCityUpdate($rank_id, $product['quantity']);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '=',
				'type'			=> 'city',
			]);
		} else {
			$total_book_sold = (int)$no_sold;

			$rank_id = $this->ranking_city_model->add([
				'event_challenge_city_id'	=> (int)$city_challenge_info['id'],
				'event_id'				=> (int)$event_info['id'],
				'user_id'				=> (int)$book_info['user_id'],
				'city_id'				=> (int)$author_info['city_id'],
				'author_name'			=> $book_info['author_name'],
				'author_image'			=> $book_info['author_image'],
				'book_id'				=> (int)$book_info['id'],
				'book_name'				=> $book_info['name'],
				'book_slug'				=> $book_info['slug'],
				'book_image'			=> $book_info['cover_image'],
				'score'					=> $total_book_sold,
			]);

			self::_pushCityUpdate($rank_id, $total_book_sold);

			self::_sendAppNotification([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'relation'		=> '>',
				'type'			=> 'city',
			]);
		}
	}

	public function getCityTotal($event_id = 0, $event_challenge_city_id = 0, $city_id = 0) {
		$rank_key = self::_getCityKey($event_id, $event_challenge_city_id, $city_id);
		return $this->redis_lib->getTotal($rank_key);
	}

	public function getCityRanks($event_id = 0, $event_challenge_city_id = 0, $city_id = 0, $page = 1, $limit = 0, $search = NULL) {
		$city_challenge_info = $this->event_challenge_city_model->get($event_challenge_city_id);

		$limit 		= ($limit > 0) ? $limit : ($city_challenge_info['limit'] ?: 10);

		$ranks 		= [];
		$rank_key 	= self::_getCityKey($event_id, $event_challenge_city_id, $city_id);
		$start 		= $page > 0 ? ($page - 1) * $limit : 0;
		$end 		= $start + $limit - 1;

		if (!empty($search)) {
			$results = [];

			$rank_results = $this->ranking_city_model->get_all([
				'event_id'					=> (int)$event_id,
				'event_challenge_city_id'	=> (int)$event_challenge_city_id,
				'city_id'					=> (int)$city_id,
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
			$rank_info 	= $this->ranking_city_model->get($rank_id);
			$ranks[] 	= self::_formatCityRank(
				$this->redis_lib->getRank($rank_key, $rank_id) + 1,
				$rank_info
			);
		}

		log_kb(['Ranking_lib::getCityRanks::ranks::' => [$results, $ranks]]);

		return ['ranks' => $ranks, 'total' => $total];
	}

	public function getCityUpdate($event_id = 0, $event_challenge_city_id = 0, $city_id = 0, $user_id = 0) {
		self::_updateLiveCityUser($event_id, $event_challenge_city_id, $city_id, $user_id);

		$city_rank_key = self::_getCityRankKey($event_id, $event_challenge_city_id, $city_id, $user_id);

		$json = json_decode($this->cache->get($city_rank_key), true);

		log_kb(['Ranking_lib::getCityUpdate::' => [
			$json,
			$city_rank_key,
		]]);

		self::removeCityUserUpdate($event_id, $event_challenge_city_id, $city_id, $user_id);

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

	public function removeCityUserUpdate($event_id = 0, $event_challenge_city_id = 0, $city_id = 0, $user_id = 0) {
		$rank_key = self::_getCityRankKey($event_id, $event_challenge_city_id, $city_id, $user_id);

		log_kb([
			'rank_key' => $rank_key
		]);

		$this->cache->delete($rank_key);
	}

	private function _getCurrentChallengeUserCityRank($event_id = 0, $event_challenge_city_id = 0, $city_id = 0, $user_id = 0, $book_id = 0) {
		$event_challenge_city_info = $this->event_challenge_city_model->get($event_challenge_city_id);
		
		$rank_key = self::_getCityKey($event_id, $event_challenge_city_id, $city_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_city_id'	=> (int)$event_challenge_city_id,
			'city_id'					=> (int)$city_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
		];

		if (empty($event_challenge_city_info['min_published'] ?? 0)) {
			$filter_data['is_moved'] = 0;
		}

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_city_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		return $user_rank;
	}

	public function getUserCityRank($event_id = 0, $event_challenge_city_id = 0, $city_id = 0, $user_id = 0, $book_id = 0) {
		$event_challenge_city_info = $this->event_challenge_city_model->get($event_challenge_city_id);

		$rank_key = self::_getCityKey($event_id, $event_challenge_city_id, $city_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_city_id'	=> (int)$event_challenge_city_id,
			'city_id'					=> (int)$city_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
		];

		if (empty($event_challenge_city_info['min_published']?? 0)) {
			$filter_data['is_moved'] = 0;
		}

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_city_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		if (!empty($user_rank['is_moved'])) {
			if ($current_challenge_rank = self::_getCurrentChallengeUserCityRank(
				$event_id,
				$event_challenge_city_id,
				$city_id,
				$user_id,
				$book_id
			)) {
				$user_rank = $current_challenge_rank;
			} else {
				$user_rank['score'] = $this->event_order_model->getTotalSoldByBook($user_rank['event_id'], $user_rank['book_id']);;
				$user_rank['rank'] 	= 0;
			}
		}

		log_kb(['getUserCityRank::user_rank' => $user_rank]);

		$user_rank = !empty($user_rank)
			? $user_rank
			: self::_genUserCityRank($event_id, $event_challenge_city_id, $city_id, $user_id, $book_id)
		;

		if (!empty($user_rank)) {
			$user_rank = array_merge($user_rank, [
				'is_early_access'			=> self::_isEarlyAccess($user_rank['book_id']),
				'is_prime_author'			=> self::_isPrimeAuthor($user_rank['book_id']),
			]);
		}

		$user_rank['message'] = self::_getCityUserMessage($user_rank);

		return $user_rank;
	}

	public function getUserNoCityRank($event_id = 0, $event_challenge_city_id = 0, $city_id = 0, $user_id = 0, $book_id = 0) {
		$user_rank = self::getUserCityRank($event_id, $event_challenge_city_id, $city_id, $user_id, $book_id);

		$user_rank = !empty($user_rank)
			? $user_rank
			: self::_genUserCityRank($event_id, $event_challenge_city_id, $city_id, $user_id, $book_id)
		;

		$author_info = $this->student_model->get($user_id);

		if (empty($user_rank['book_id'])) {
			$item = $this->student_model->get($user_id);

			$user_rank = [
				'id'					=> 0,
				'rank'					=> 0,
				'event_id'				=> (int)$event_id,
				'event_challenge_city_id'=> (int)$event_challenge_city_id,
				'city_id'				=> (int)$city_id,
				'user_id'				=> (int)$user_id,
				'author_name'			=> trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '')),
				'author_image'			=> $item['image'] ?? '',
				'book_image'			=> '',
				'book_id'				=> 'NA',
				'book_name'				=> 'NA',
				'book_slug'				=> 'NA',
				'score'					=> 0,
				'message'				=> (in_array($event_id, [9]))
					? _li('You haven\'t joined the City Best-Sellers League as your book is yet to be published')
					: _li('Unfortunately, your book wasn\'t submitted for this event, so you can\'t participate in the Best Seller League.'),
				'amazon_url'			=> ''
			];
		}

		return $user_rank;
	}

	private function _formatCityRank($rank = 0, $item = []) {
		return [
			'id'						=> $item['id'] ?? 0,
			'rank'						=> $rank,
			'event_id'					=> $item['event_id'] ?? 0,
			'city_id'					=> $item['city_id'] ?? 0,
			'event_challenge_city_id'	=> $item['event_challenge_city_id'] ?? 0,
			'user_id'					=> $item['user_id'] ?? 0,
			'author_name'				=> $item['author_name'] ?? '',
			'author_image'				=> $item['author_image'] ?? '',
			'book_id'					=> $item['book_id'] ?? 0,
			'book_name'					=> $item['book_name'] ?? '',
			'book_image'				=> $item['book_image'] ?? '',
			'book_slug'					=> $item['book_slug'] ?? '',
			'is_early_access'			=> self::_isEarlyAccess($item['book_id'] ?? 0),
			'is_prime_author'			=> self::_isPrimeAuthor($item['book_id'] ?? 0),
			'score'						=> $item['score'] ?? 0,
			'is_moved'					=> $item['is_moved'] ?? 0,
			'message' 					=> self::_getCityUserMessage(array_merge($item, [
				'rank'					=> $rank,
			])),
		];
	}

	private function _initCityRanks($event_id = 0, $event_challenge_city_id = 0, $city_id = 0) {
		$filter_data = [
			'event_challenge_city_id'	=> (int)$event_challenge_city_id,
			'event_id'					=> (int)$event_id,
			'city_id'					=> (int)$city_id,
			'start'						=> 0,
			'limit'						=> 100,
		];

		$results = $this->ranking_city_model->get_all($filter_data)['rows'] ?? [];

		$ranks = [];

		foreach ($results as $key => $item) {
			$ranks[$item['book_id']] = self::_formatCityRank($key + 1, $item);
		}

		return $ranks;
	}

	private function _pushCityUpdate($rank_id = 0, $new_score = 0) {
		$rank_info = $this->ranking_city_model->get($rank_id);

		$rank_key = self::_getCityKey(
			$rank_info['event_id'],
			$rank_info['event_challenge_city_id'],
			$rank_info['city_id']
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

		log_kb(['Ranking::_pushCityUpdate' => [
			'old_rank'		=> $old_rank,
			'new_rank'		=> $new_rank,
		]]);

		$alert_payload['rank_data'] = array_merge(
			self::_formatCityRank($new_rank, $rank_info),
			[
				'old_rank'	=> $old_rank,
				'new_rank'	=> $new_rank,
			]
		);

		self::_notifyAppUsers(
			sprintf('bb_notifications_ranking_city_%s_%s', $rank_info['event_id'], $rank_info['city_id']),
			[
				'title'	=> _li('city_rank_update'),
				'body'	=> _li('city_rank_update'),
			]
		);

		self::_saveCityAlertForEveryOne($rank_info, $alert_payload);
	}

	private function _genUserCityRank($event_id = 0, $event_challenge_city_id = 0, $city_id = 0, $user_id = 0, $book_id = 0) {
		$event_challenge_city_info = $this->event_challenge_city_model->get($event_challenge_city_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_city_id'	=> (int)$event_challenge_city_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'city_id'					=> (int)$city_id,
		];

		if (empty($event_challenge_city_info['min_published'] ?? 0)) {
			$filter_data['is_moved'] = 0;
		}

		if ($rank_info = $this->ranking_city_model->get_all($filter_data)['rows'][0] ?? []) {
			return self::_formatCityRank(0, $rank_info);
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

		if ($city_id != ($author_info['city_id'] ?? 0)) return;

		$no_sold = (!empty($item['id']) && !empty($event_id)) ? $this->event_order_model->getTotalSoldByBook($event_id, $item['id']) : 0;

		$rank_data = [
			'id'					=> 0,
			'rank'					=> 0,
			'event_challenge_city_id'=> $event_challenge_city_id,
			'event_id'				=> $event_id,
			'user_id'				=> $user_id,
			'city_id'				=> $author_info['city_id'] ?? $city_id,
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
				'message' => self::_getCityUserMessage($rank_data)
			],
		);
	}

	private function _addMessageToCityRanks(&$ranks = []) {
		foreach ($ranks as &$item) {
			$item['message'] = self::_getCityUserMessage($item);
		}
	}

	public function getCityTopRank($score = 100, $rank = [], $full_rank = false) {
		return self::_getCityRankScore($score, $rank, $full_rank);
	}

	private function _getCityRankScore($u_rank = 100, $rank = [], $full_rank = false) {
		$rank_key = self::_getCityKey(
			(int)$rank['event_id'],
			(int)$rank['event_challenge_city_id'],
			(int)$rank['city_id'],
		);

		$result = array_keys($this->redis_lib->getRanks($rank_key, $u_rank - 1, $u_rank - 1));
		$user_rank = $this->ranking_city_model->get($result[0] ?? '');

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

	private function _getCityUserMessage($rank = []) {
		if (empty($rank['event_challenge_city_id'])) return;

		$total_sold = !empty($rank['book_id'])
			? ($this->event_order_model->getTotalSoldByBook($rank['event_id'], $rank['book_id']) ?? 0)
			: 0
		;

		if (in_array($rank['user_id'] ?? 0, BB_UID)) {
			$total_sold = 80 + $total_sold;
		}

		log_kb([
			'_getCityUserMessage' => [
				$total_sold,
				$rank,
			]
		]);

		$city_name = 'City';
		if (!empty($rank['city_id'])) {
			$city_name = $this->city_model->get($rank['city_id'])['name'] ?? 'City';
		}

		$city_challenge_info = $this->event_challenge_city_model->get($rank['event_challenge_city_id']);

		if (!empty($city_challenge_info) && date('Y-m-d H:i:s') > $city_challenge_info['end_date']) {
			return sprintf(_li('%s is closed now!'), $city_challenge_info['name']);
		}

		if (!empty($rank['is_moved']) || ($total_sold > $city_challenge_info['max_book_sold'])) {
			return _li('Your Book has been promoted to the next league');
		}

		$min_book_sold = !empty($city_challenge_info['min_published']) ? $city_challenge_info['min_published'] : $_challenge_info['book_sold'];

		if (empty($rank['score']) || ($rank['score'] < $min_book_sold)) {
			// return sprintf(_li('Buy/Sell at least %s copy to participate in the city bestseller league'), $city_challenge_info['book_sold']);
			$required_sold_count = abs($min_book_sold - $total_sold);
			return sprintf(_li('Buy/Sell %s %s to participate in the Best-Selling Young Authors’ League of %s.'),
			$required_sold_count,
			self::_getCopyText($required_sold_count),
			$city_name);

		} else {
			return method_exists($this, sprintf('_getCityEventMessage_%s', $rank['event_id']))
				? self::{sprintf('_getCityEventMessage_%s', $rank['event_id'])}($total_sold, $rank, $city_challenge_info)
				: self::_getCityEventMessage($total_sold, $rank, $city_challenge_info);
		}
	}

	private function _getCityEventMessage($total_sold = 0, $rank = [], $city_challenge_info = []) {
		if ($total_sold < $city_challenge_info['max_book_sold'] && !empty($rank['rank'])) {
			$rank_breakpoints = $this->league_break_point_message_model->get_all([
				'event_id'		=> (int)$rank['event_id'],
				'challenge_id'	=> (int)$rank['event_challenge_city_id'],
				'type'			=> 'city',
				'sort'			=> 'league_breakpoint_message.breakpoint',
				'order'			=> 'DESC',
			])['rows'] ?? [];

			foreach ($rank_breakpoints as $index => $breakpoint) {
				if ($rank['rank'] > $breakpoint['breakpoint']) {
					$required_sold_count = self::_getCityRankScore($breakpoint['breakpoint'], $rank) - $rank['score'] + 1;

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
			return sprintf(_li('Buy/Sell %s %s more to participate in the bestseller league of %s'), ($city_challenge_info['book_sold'] - $total_sold), self::_getCopyText(($city_challenge_info['book_sold'] - $total_sold)), $city_info['name'] ?? '');
		}

		return _li('Buy/Sell at least one copy more to participate in the City bestseller league');
	}

	private function _saveCityAlertForEveryOne($rank_info = [], $alert_payload = []) {
		$users = self::_getLiveCityUsers($rank_info['event_id'], $rank_info['event_challenge_city_id'], $rank_info['city_id']);

		log_kb(['_saveCityAlertForEveryOne' => $users, [$alert_payload]]);

		foreach ($users as $user_id) {
			$this->cache->save(
				self::_getCityRankKey($rank_info['event_id'], $rank_info['event_challenge_city_id'], $rank_info['city_id'], $user_id),
				json_encode($alert_payload),
				300
			);
		}
	}

	private function _updateLiveCityUser($event_id = 0, $event_challenge_city_id = 0, $city_id = 0, $user_id = 0) {
		$users = self::_getLiveCityUsers($event_id, $event_challenge_city_id, $city_id);

		if (!in_array($user_id, $users)) {
			$users[] = $user_id;
		} else {
			return;
		}

		log_kb(['_updateLiveCityUser::new' => $users, [$user_id, $city_id]]);

		$this->cache->save(self::_getLiveCityUserKey($event_id, $event_challenge_city_id, $city_id), json_encode($users), 900);
	}

	private function _getLiveCityUsers($event_id = 0, $event_challenge_city_id = 0, $city_id = 0) {
		$users = json_decode($this->cache->get(self::_getLiveCityUserKey($event_id, $event_challenge_city_id, $city_id)), true);

		return $users ?? [];
	}

	private function _getLiveCityUserKey($event_id = 0, $event_challenge_city_id = 0, $city_id = 0) {
		return vsprintf('event_live_city_users_%s_%s_%s', [
			(int)$event_id,
			(int)$event_challenge_city_id,
			(int)$city_id,
		]);
	}

	private function _getCityRankKey($event_id = 0, $event_challenge_city_id = 0, $city_id = 0, $user_id = 0) {
		return vsprintf('%s_city_rank_update_%s_%s_%s', [
			(int)$event_id,
			(int)$event_challenge_city_id,
			(int)$city_id,
			$user_id,
		]);
	}

	private function _getCityKey($event_id = 0, $event_challenge_city_id = 0, $city_id = 0) {
		return vsprintf('live_author_city_ranks_%s_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$event_id,
			$event_challenge_city_id,
			$city_id,
		]);
	}
}
