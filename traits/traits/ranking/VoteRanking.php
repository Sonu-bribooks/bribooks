<?php defined('BASEPATH') or exit('No direct script access allowed');

trait VoteRanking {
	public function cleanRanks($rank_id = 0) {
		$rank_info = $this->ranking_vote_model->get($rank_id);
		
		$rank_key = self::_getVoteKey(
			$rank_info['event_id'],
			$rank_info['event_challenge_vote_id'],
			$rank_info['league_type_id']
		);

		$this->redis_lib->removeRangeRank($rank_key, 0, 10000);
	}

	public function pushVoteUpdateRank($rank_id = 0) {
		self::_pushVoteUpdate($rank_id, 0);
	}

	public function removeFromVoteRank($rank_id = 0) {
		$rank_info = $this->ranking_vote_model->get($rank_id);

		if (empty($rank_info)) return;

		$rank_key = self::_getVoteKey(
			$rank_info['event_id'],
			$rank_info['event_challenge_vote_id'],
			$rank_info['league_type_id']
		);

		$this->redis_lib->removeFromRank($rank_key, $rank_info['id']);
	}

	public function updateVoteBookInfo($book_id = 0) {
		if ($ranks = $this->ranking_vote_model->get_all([
			'book_id'	=> $book_id,
		])['rows'] ?? []) {
			$book_info = $this->book_model->get($book_id);

			if (empty($book_info)) return;

			foreach ($ranks as $rank_info) {
				self::_updateBookInfo(
					'user_rank_vote',
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

	public function updateVoteRank($data = []) {
		$book_info 		= $data['book_info'] ?? [];
		$challenge_info = $data['challenge_info'] ?? [];

		log_kb([
			'updating Vote Rank::' => $data,
		]);

		if (empty($book_info) || empty($challenge_info)) {
			return;
		}

		$vote 			= $this->event_user_vote_model->getTotalBookVote($challenge_info['event_id'], $challenge_info['id'], $book_info['id']);

		$author_info 	= $this->student_model->get($book_info['user_id']);
		$city_info 		= $this->city_model->get($author_info['city_id'] ?? 0);
		$state_info 	= $this->state_model->get($author_info['state_id'] ?? 0);

		if ($vote < $challenge_info['min_vote']) return;
		if (!empty($challenge_info['max_vote']) && ($vote > $challenge_info['max_vote'])) return;

		if ($rank_vote_info = $this->ranking_vote_model->get_all([
			'event_challenge_vote_id'	=> (int)$challenge_info['id'],
			'event_id'					=> (int)$challenge_info['event_id'],
			'user_id'					=> (int)$book_info['user_id'],
			'book_id'					=> (int)$book_info['id'],
		])['rows'][0] ?? []) {
			$rank_id = $rank_vote_info['id'];

			$this->ranking_vote_model->edit($rank_vote_info['id'], [
				'score'					=> $vote,
			]);

			self::_pushVoteUpdate($rank_id, $vote);
		} else {
			$var_name = sprintf('%s_info', $challenge_info['league_type']);

			$league_type_id = (int) ($var_name['id'] ?? 0);

			$rank_id = $this->ranking_vote_model->add([
				'event_challenge_vote_id'	=> (int)$challenge_info['id'],
				'league_type_id'			=> (int)$league_type_id,
				'genre_id'					=> (int)$book_info['genre_id'],
				'category_id'				=> (int)$book_info['category_id'],
				'event_id'					=> (int)$challenge_info['event_id'],
				'user_id'					=> (int)$book_info['user_id'],
				'author_name'				=> $book_info['author_name'],
				'author_image'				=> $book_info['author_image'],
				'book_id'					=> (int)$book_info['id'],
				'book_name'					=> $book_info['name'],
				'book_slug'					=> $book_info['slug'],
				'book_image'				=> $book_info['cover_image'],
				'score'						=> $vote,
			]);

			self::_pushVoteUpdate($rank_id, $vote);
		}
	}

	public function getVoteTotal($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0) {
		$rank_key = self::_getVoteKey($event_id, $event_challenge_vote_id, $league_type_id);
		return $this->redis_lib->getTotal($rank_key);
	}

	public function getVoteRanks($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0, $page = 1, $limit = 0, $search = NULL) {
		$challenge_info = $this->event_challenge_vote_model->get($event_challenge_vote_id);

		$limit 		= ($limit > 0) ? $limit : ($challenge_info['limit'] ?: 10);

		$ranks 		= [];
		$rank_key 	= self::_getVoteKey($event_id, $event_challenge_vote_id, $league_type_id);
		$start 		= $page > 0 ? ($page - 1) * $limit : 0;
		$end 		= $start + $limit - 1;

		if (!empty($search)) {
			$results = [];

			$rank_results = $this->ranking_vote_model->get_all([
				'event_id'					=> (int)$event_id,
				'event_challenge_vote_id'	=> (int)$event_challenge_vote_id,
				'league_type_id'			=> (int)$league_type_id,
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
			$rank_info 	= $this->ranking_vote_model->get($rank_id);
			$ranks[] 	= self::_formatVoteRank(
				$this->redis_lib->getRank($rank_key, $rank_id) + 1,
				$rank_info
			);
		}

		log_kb(['Ranking_lib::getVoteRanks::ranks::' => [$results, $ranks]]);

		return ['ranks' => $ranks, 'total' => $total];
	}

	public function getVoteUpdate($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0, $user_id = 0) {
		self::_updateLiveVoteUser($event_id, $event_challenge_vote_id, $league_type_id, $user_id);

		$vote_rank_key = self::_getVoteRankKey($event_id, $event_challenge_vote_id, $league_type_id, $user_id);

		$json = json_decode($this->cache->get($vote_rank_key), true);

		log_kb(['Ranking_lib::getVoteUpdate::' => [
			$json,
			$vote_rank_key,
		]]);

		self::removeVoteUserUpdate($event_id, $event_challenge_vote_id, $league_type_id, $user_id);

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

	public function removeVoteUserUpdate($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0, $user_id = 0) {
		$rank_key = self::_getVoteRankKey($event_id, $event_challenge_vote_id, $league_type_id, $user_id);

		log_kb([
			'rank_key' => $rank_key
		]);

		$this->cache->delete($rank_key);
	}

	private function _getCurrentChallengeUserVoteRank($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0, $user_id = 0, $book_id = 0) {
		$event_challenge_vote_info = $this->event_challenge_vote_model->get($event_challenge_vote_id);

		$rank_key = self::_getVoteKey($event_id, $event_challenge_vote_id, $league_type_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_vote_id'	=> (int)$event_challenge_vote_id,
			'league_type_id'			=> (int)$league_type_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
		];

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_vote_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$user_rank['rank'] = $result ?? 0;
		}

		return $user_rank;
	}

	public function getUserVoteRank($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0, $user_id = 0, $book_id = 0) {
		$event_challenge_vote_info = $this->event_challenge_vote_model->get($event_challenge_vote_id);

		$rank_key = self::_getVoteKey($event_id, $event_challenge_vote_id, $league_type_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_vote_id'	=> (int)$event_challenge_vote_id,
			'league_type_id'			=> (int)$league_type_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
		];

		if (empty($book_id)) {
			unset($filter_data['book_id']);
		}

		$user_rank = $this->ranking_vote_model->get_all($filter_data)['rows'][0] ?? [];

		$result = $this->redis_lib->getRank($rank_key, $user_rank['id'] ?? 0);

		$result += 1;

		if (!empty($result) && !empty($user_rank)) {
			$genre_info 	= $this->genre_model->get($user_rank['genre_id'] ?? 0);

			$category_info 	= $this->category_model->get($user_rank['category_id'] ?? 0);

			$user_rank['genre'] = $genre_info['name'];
			$user_rank['category'] = $category_info['name'];
			$user_rank['rank'] = $result ?? 0;
		}

		if (!empty($user_rank['is_moved'])) {
			if ($current_challenge_rank = self::_getCurrentChallengeUserVoteRank(
				$event_id,
				$event_challenge_vote_id,
				$league_type_id,
				$user_id,
				$book_id
			)) {
				$user_rank = $current_challenge_rank;
			} else {
				$user_rank['score'] = $this->event_user_vote_model->getTotalBookVote($user_rank['event_id'], $user_rank['event_challenge_vote_id'], $user_rank['book_id']);;
				$user_rank['rank'] 	= 0;
			}
		}

		log_kb(['getUserVoteRank::user_rank' => $user_rank]);

		$user_rank = !empty($user_rank)
			? $user_rank
			: self::_genUserVoteRank($event_id, $event_challenge_vote_id, $league_type_id, $user_id, $book_id)
		;

		$user_rank['message'] = self::_getVoteUserMessage($user_rank);

		return $user_rank;
	}

	public function getUserNoVoteRank($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0, $user_id = 0, $book_id = 0) {
		$user_rank = self::getUserVoteRank($event_id, $event_challenge_vote_id, $league_type_id, $user_id, $book_id);

		$user_rank = !empty($user_rank)
			? $user_rank
			: self::_genUserVoteRank($event_id, $event_challenge_vote_id, $league_type_id, $user_id, $book_id)
		;

		$author_info = $this->student_model->get($user_id);

		if (empty($user_rank['book_id'])) {
			$item = $this->student_model->get($user_id);

			$user_rank = [
				'id'					=> 0,
				'rank'					=> 0,
				'event_id'				=> (int)$event_id,
				'event_challenge_vote_id'=> (int)$event_challenge_vote_id,
				'league_type_id'		=> (int)$league_type_id,
				'genre_id'				=> 'NA',
				'category_id'			=> 'NA',
				'genre'					=> 'NA',
				'category'				=> 'NA',
				'user_id'				=> (int)$user_id,
				'author_name'			=> trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '')),
				'author_image'			=> $item['image'] ?? '',
				'book_image'			=> '',
				'book_id'				=> 'NA',
				'book_name'				=> 'NA',
				'book_slug'				=> 'NA',
				'score'					=> 0,
				'message'				=> (in_array($event_id, [9]))
					? _li('You haven\'t joined the Vote Book Vote League as your book is yet to be published')
					: _li('Unfortunately, your book wasn\'t submitted for this event, so you can\'t participate in the Book Vote League.'),
				'amazon_url'			=> ''
			];
		}

		return $user_rank;
	}

	private function _formatVoteRank($rank = 0, $item = []) {
		$genre_info 	= $this->genre_model->get($item['genre_id'] ?? 0);
		$category_info 	= $this->category_model->get($item['category_id'] ?? 0);

		return [
			'id'						=> $item['id'] ?? 0,
			'rank'						=> $rank,
			'event_id'					=> $item['event_id'] ?? 0,
			'league_type_id'			=> $item['league_type_id'] ?? 0,
			'genre_id'					=> $item['genre_id'] ?? 0,
			'category_id'				=> $item['category_id'] ?? 0,
			'genre'						=> $genre_info['name'] ?? '',
			'category'					=> $category_info['name'] ?? '',
			'event_challenge_vote_id'	=> $item['event_challenge_vote_id'] ?? 0,
			'user_id'					=> $item['user_id'] ?? 0,
			'author_name'				=> $item['author_name'] ?? '',
			'author_image'				=> $item['author_image'] ?? '',
			'book_id'					=> $item['book_id'] ?? 0,
			'book_name'					=> $item['book_name'] ?? '',
			'book_image'				=> $item['book_image'] ?? '',
			'book_slug'					=> $item['book_slug'] ?? '',
			'score'						=> $item['score'] ?? 0,
			'is_moved'					=> $item['is_moved'] ?? 0,
			'message' 					=> self::_getVoteUserMessage(array_merge($item ?? [], [
				'rank'					=> $rank,
			])),
		];
	}

	private function _initVoteRanks($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0) {
		$filter_data = [
			'event_challenge_vote_id'	=> (int)$event_challenge_vote_id,
			'event_id'					=> (int)$event_id,
			'league_type_id'			=> (int)$league_type_id,
			'start'						=> 0,
			'limit'						=> 100,
		];

		$results = $this->ranking_vote_model->get_all($filter_data)['rows'] ?? [];

		$ranks = [];

		foreach ($results as $key => $item) {
			$ranks[$item['book_id']] = self::_formatVoteRank($key + 1, $item);
		}

		return $ranks;
	}

	private function _pushVoteUpdate($rank_id = 0, $new_score = 0) {
		$rank_info = $this->ranking_vote_model->get($rank_id);

		$rank_key = self::_getVoteKey(
			$rank_info['event_id'],
			$rank_info['event_challenge_vote_id'],
			$rank_info['league_type_id']
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

		log_kb(['Ranking::_pushVoteUpdate' => [
			'old_rank'		=> $old_rank,
			'new_rank'		=> $new_rank,
		]]);

		$alert_payload['rank_data'] = array_merge(
			self::_formatVoteRank($new_rank, $rank_info),
			[
				'old_rank'	=> $old_rank,
				'new_rank'	=> $new_rank,
			]
		);

		self::_saveVoteAlertForEveryOne($rank_info, $alert_payload);
	}

	private function _genUserVoteRank($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0, $user_id = 0, $book_id = 0) {
		$event_challenge_vote_info = $this->event_challenge_vote_model->get($event_challenge_vote_id);

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_vote_id'	=> (int)$event_challenge_vote_id,
			'user_id'					=> (int)$user_id,
			'book_id'					=> (int)$book_id,
			'league_type_id'			=> (int)$league_type_id,
		];

		if ($rank_info = $this->ranking_vote_model->get_all($filter_data)['rows'][0] ?? []) {
			return self::_formatVoteRank(0, $rank_info);
		}

		$author_info = $this->student_model->get($user_id);

		if ($top_vote_book = $this->event_user_vote_model->get_all([
			'user_id'	=> (int)$user_id,
			'event_id'	=> (int)$event_id,
			'book_id'	=> (int)$book_id,
			'start'		=> 0,
			'limit'		=> 1,
		])['rows'][0] ?? []) {
			$item = $this->book_model->get($top_vote_book['book_id']);
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

		$vote = (!empty($item['id']) && !empty($event_id)) ? $this->event_user_vote_model->getTotalBookVote($event_id, $event_challenge_vote_id, $item['id']) : 0;

		$genre_info 	= $this->genre_model->get($item['genre_id'] ?? 0);

		$category_info 	= $this->category_model->get($item['category_id'] ?? 0);

		$rank_data = [
			'id'						=> 0,
			'rank'						=> 0,
			'event_challenge_vote_id'   => $event_challenge_vote_id,
			'event_id'					=> $event_id,
			'user_id'					=> $user_id,
			'league_type_id'			=> (int)$league_type_id,
			'genre_id'					=> $item['genre_id'] ?? 0,
			'category_id'				=> $item['category_id'] ?? 0,
			'genre'						=> $genre_info['name'] ?? '',
			'category'					=> $category_info['name'] ?? '',
			'author_name'				=> $item['author_name'] ?? '',
			'author_image'				=> $item['author_image'] ?? '',
			'book_id'					=> $item['id'] ?? 0,
			'book_name'					=> $item['name'] ?? '',
			'book_slug'					=> $item['slug'] ?? '',
			'book_image'				=> $item['cover_image'] ?? '',
			'score'						=> $vote,
			'is_moved'					=> $item['is_moved'] ?? 0,
		];

		return array_merge(
			$rank_data,
			[
				'message' => self::_getVoteUserMessage($rank_data)
			],
		);
	}

	private function _addMessageToVoteRanks(&$ranks = []) {
		foreach ($ranks as &$item) {
			$item['message'] = self::_getVoteUserMessage($item);
		}
	}

	public function getVoteTopRank($score = 100, $rank = [], $full_rank = false) {
		return self::_getVoteRankScore($score, $rank, $full_rank);
	}

	private function _getVoteRankScore($u_rank = 100, $rank = [], $full_rank = false) {
		$rank_key = self::_getVoteKey(
			(int)$rank['event_id'],
			(int)$rank['event_challenge_vote_id'],
			(int)$rank['league_type_id'],
		);

		$result = array_keys($this->redis_lib->getRanks($rank_key, $u_rank - 1, $u_rank - 1));
		$user_rank = $this->ranking_vote_model->get($result[0] ?? '');

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

	private function _getVoteUserMessage($rank = []) {
		if (empty($rank['event_challenge_vote_id'])) return;

		$total_vote = !empty($rank['book_id'])
			? ($this->event_user_vote_model->getTotalBookVote($rank['event_id'], $rank['event_challenge_vote_id'], $rank['book_id']) ?? 0)
			: 0
		;

		if (in_array($rank['user_id'] ?? 0, BB_UID)) {
			$total_vote = 80 + $total_vote;
		}

		log_kb([
			'_getVoteUserMessage' => [
				$total_vote,
				$rank,
			]
		]);

		$challenge_info = $this->event_challenge_vote_model->get($rank['event_challenge_vote_id']);

		if (!empty($challenge_info) && date('Y-m-d H:i:s') > $challenge_info['end_date']) {
			return sprintf(_li('%s is closed now!'), $challenge_info['name']);
		}

		if (!empty($rank['is_moved']) || ($total_vote > $challenge_info['max_vote'])) {
			return _li('Your Book has been promoted to the next league');
		}

		$min_vote = !empty($challenge_info['min_vote']) ?? 0;

		if (empty($rank['score']) || ($rank['score'] < $min_vote)) {
			$required_vote_count = abs($min_vote - $total_vote);
			return sprintf(_li('Buy/Sell %s %s to participate in the Best-Selling Young Authors’ League.'),
				$required_vote_count,
				self::_getVoteText($required_vote_count)
			);

		} else {
			return method_exists($this, sprintf('_getVoteEventMessage_%s', $rank['event_id']))
				? self::{sprintf('_getVoteEventMessage_%s', $rank['event_id'])}($total_vote, $rank, $challenge_info)
				: self::_getVoteEventMessage($total_vote, $rank, $challenge_info);
		}
	}

	private function _getVoteEventMessage($total_vote = 0, $rank = [], $challenge_info = []) {
		if ($total_vote < $challenge_info['max_vote'] && !empty($rank['rank'])) {
			$rank_breakpoints = $this->league_break_point_message_model->get_all([
				'event_id'		=> (int)$rank['event_id'],
				'challenge_id'	=> (int)$rank['event_challenge_vote_id'],
				'type'			=> 'vote',
				'sort'			=> 'league_breakpoint_message.breakpoint',
				'order'			=> 'DESC',
			])['rows'] ?? [];

			foreach ($rank_breakpoints as $index => $breakpoint) {
				if ($rank['rank'] > $breakpoint['breakpoint']) {
					$required_vote_count = self::_getVoteRankScore($breakpoint['breakpoint'], $rank) - $rank['score'] + 1;

					return self::_formatVoteLeagueMessage($breakpoint['message'], [
						'required_vote_count' 	=> $required_vote_count,
						'vote_text' 			=> self::_getVoteText($required_vote_count),
					]);
				}
			}
		}

		$author_info 	= $this->student_model->get($rank['user_id']);
		$state_info 	= $this->state_model->get($author_info['state_id']);

		if (empty($rank['rank'])) {
			return sprintf(_li('Buy/Sell %s %s more to participate in the bestseller league of %s'), ($challenge_info['min_vote'] - $total_vote), self::_getVoteText(($challenge_info['min_vote'] - $total_vote)), $challenge_info['name'] ?? '');
		}

		return _li('Buy/Sell at least one copy more to participate in the Vote league');
	}

	private function _saveVoteAlertForEveryOne($rank_info = [], $alert_payload = []) {
		$users = self::_getLiveVoteUsers($rank_info['event_id'], $rank_info['event_challenge_vote_id'], $rank_info['league_type_id']);

		log_kb(['_saveVoteAlertForEveryOne' => $users, [$alert_payload]]);

		foreach ($users as $user_id) {
			$this->cache->save(
				self::_getVoteRankKey($rank_info['event_id'], $rank_info['event_challenge_vote_id'], $rank_info['league_type_id'], $user_id),
				json_encode($alert_payload),
				300
			);
		}
	}

	private function _updateLiveVoteUser($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0, $user_id = 0) {
		$users = self::_getLiveVoteUsers($event_id, $event_challenge_vote_id, $league_type_id);

		if (!in_array($user_id, $users)) {
			$users[] = $user_id;
		} else {
			return;
		}

		log_kb(['_updateLiveVoteUser::new' => $users, [$user_id, $league_type_id]]);

		$this->cache->save(self::_getLiveVoteUserKey($event_id, $event_challenge_vote_id, $league_type_id), json_encode($users), 900);
	}

	private function _getLiveVoteUsers($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0) {
		$users = json_decode($this->cache->get(self::_getLiveVoteUserKey($event_id, $event_challenge_vote_id, $league_type_id)), true);

		return $users ?? [];
	}

	private function _getLiveVoteUserKey($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0) {
		return vsprintf('event_live_vote_users_%s_%s_%s', [
			(int)$event_id,
			(int)$event_challenge_vote_id,
			(int)$league_type_id,
		]);
	}

	private function _getVoteRankKey($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0, $user_id = 0) {
		return vsprintf('%s_vote_rank_update_%s_%s_%s', [
			(int)$event_id,
			(int)$event_challenge_vote_id,
			(int)$league_type_id,
			$user_id,
		]);
	}

	private function _getVoteKey($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0) {
		return vsprintf('live_author_vote_ranks_%s_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$event_id,
			$event_challenge_vote_id,
			$league_type_id,
		]);
	}
}
