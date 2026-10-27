<?php defined('BASEPATH') or exit('No direct script access allowed');

trait GradeSchoolRanking {
	public function updateGradeRank($data = []) {
		$book_info 		= $data['book_info'] ?? [];
		$event_info 	= $data['event_info'] ?? [];
		$author_info 	= $this->student_model->get($book_info['user_id']);
		$school_info 	= $this->site_model->get($author_info['site_id']);

		log_kb(compact('book_info', 'event_info', 'author_info', 'school_info'));

		if (empty($book_info) || empty($event_info) || empty($school_info) || ($event_info['direct_site_id'] == $school_info['id'])) {
			return;
		}

		if (empty($this->event_site_model->get_all([
			'event_id' 	=> $event_info['id'],
			'site_id' 	=> $school_info['id'],
		])['rows'][0] ?? '')) {
			return;
		}

		$challenge_info = $this->event_challenge_grade_model->get_all([
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (empty($challenge_info)) return;

		$no_published 	= self::_getGradePublishedCount([
			'event_id'	=> $event_info['id'],
			'site_id'	=> $author_info['site_id'],
			'grade'		=> $author_info['grade'],
			'section'	=> $author_info['section']
		]);

		$total_students = self::_getGradeRegisteredCount([
			'event_id'	=> $event_info['id'],
			'site_id'	=> $author_info['site_id'],
			'grade'		=> $author_info['grade'],
			'section'	=> $author_info['section']
		]);
        
        $published_author = self::_getGradePublishedAuthorCount([
            'event_id'	=> $event_info['id'],
			'site_id'	=> $author_info['site_id'],
			'grade'		=> $author_info['grade'],
			'section'	=> $author_info['section']
        ]);

		log_kb(compact('challenge_info', 'published_author'));

		// if ($no_published < $challenge_info['min_published']) return;
		// if (!empty($challenge_info['max_published']) && $no_published > $challenge_info['max_published']) return;

		if ($rank_info = $this->ranking_grade_model->get_all([
			'event_challenge_grade_id'	=> (int)$challenge_info['id'],
			'event_id'					=> (int)$event_info['id'],
			'school_id'					=> (int)$school_info['id'],
            'grade'		                => (int)$author_info['grade'],
			'section'	                => $author_info['section']
		])['rows'][0] ?? []) {
			$rank_id = $rank_info['id'];

			$this->ranking_grade_model->edit($rank_info['id'], [
				'score'					=> $published_author,
				'total_students'		=> $total_students,
				'total_published_author'=> $published_author
			]);
		} else {
			$city_info = $this->city_model->get($school_info['city_id']);

			$rank_id = $this->ranking_grade_model->add([
				'event_challenge_grade_id'	=> (int)$challenge_info['id'],
				'event_id'					=> (int)$event_info['id'],
				'school_id'					=> (int)$school_info['id'],
				'school_code'				=> $school_info['site_code'],
				'school_name'				=> $school_info['name'],
				'owner_name'				=> $school_info['owner_name'],
				'owner_email'				=> $school_info['owner_email'],
				'owner_mobile'				=> $school_info['owner_mobile'],
                'grade'		                => (int)$author_info['grade'],
			    'section'	                => $author_info['section'],
				'city'						=> $city_info['name'],
				'state'						=> $city_info['state'],
				'book_published'			=> $no_published ?? 0,
				'total_students'			=> $total_students ?? 0,
				'score'						=> $published_author ?? 0,
                'total_published_author'    => $published_author ?? 0
			]);
		}

		log_kb(compact('rank_id', 'published_author'));

		self::_pushGradeUpdate($rank_id, $published_author, 'grade');
	}

	public function getGradeRanks($data = []) {
		
        $event_id                   = $data['event_id'];
        $event_challenge_grade_id   = $data['event_challenge_grade_id']; 
        $school_id                  = $data['school_id']; 
        $page                       = $data['page'] ?? 1;
       	$search                   	= $data['search'] ?? '';
    	$grade                    	= $data['grade'] ?? '';

		$ranks 		= [];
		$rank_key 	= self::_getGradeKey(compact('event_id', 'event_challenge_grade_id', 'school_id'), 'grade');
		$start 		= $page > 0 ? ($page - 1) * $this->limit : 0;
		$data['end'] 		= $start + $this->limit - 1;
		$data['limit']		= $this->limit;

    	$has_filter = (!empty($search)) || (!empty($grade) && $grade != 'all');

		if (!empty($has_filter)) {
			$results = [];

			$query_params = [
				'event_id'                 => (int)$event_id,
				'event_challenge_grade_id' => (int)$event_challenge_grade_id,
				'school_id'                => (int)$school_id,
				// 'start'                    => $start,
				// 'limit'                    => $this->limit
			];

			if (!empty($search)) {
				$query_params['search'] = $search;
			}

			if (!empty($grade) && $grade != 'all') {
				$query_params['grade'] = $grade;
			}

			$rank_results = $this->ranking_grade_model->get_all($query_params);

			$total = $rank_results['total'];

			foreach ($rank_results['rows'] ?? [] as $item) {
				$results[$item['id']] = $item;
			}
		} else {
			// $results 	= $this->redis_lib->getRanks($rank_key, $start, $data['end']);
            $results 	= $this->redis_lib->getRanks($rank_key);
			$total 		= $this->redis_lib->getTotal($rank_key);
		}

		foreach ($results as $rank_id => $item) {
			$rank_info 	= $this->ranking_grade_model->get($rank_id);
			$ranks[] 	= self::_formatGradeRank(
				$this->redis_lib->getRank($rank_key, $rank_id) + 1,
				$rank_info
			);
		}

		log_kb(['GradeSchoolRanking::getGradeRanks::ranks::' => [$results, $ranks]]);

		return ['ranks' => $ranks, 'total' => $total];
	}

	public function getSchoolGradeRank($event_id = 0, $event_challenge_grade_id = 0, $grade = 0, $section = '', $school_id = 0) {
		$rank_key 	= self::_getGradeKey(compact('event_id', 'event_challenge_grade_id', 'school_id'), 'grade');

		$filter_data = [
			'event_id'					=> (int)$event_id,
			'event_challenge_grade_id'	=> (int)$event_challenge_grade_id,
			'grade'					    => (int)$grade,
            'section'                   => $section,
			'school_id'					=> (int)$school_id,
		];

		$grade_rank 	= $this->ranking_grade_model->get_all($filter_data)['rows'][0] ?? [];
		$rank 		= $this->redis_lib->getRank($rank_key, $grade_rank['id'] ?? 0);
		$rank 		+= 1;

		log_kb([
			'filter_data' => $filter_data,
			'grade_rank' => $grade_rank,
			'rank' => $rank,
			'rank_key' => $rank_key,
		]);

		if (!empty($rank) && !empty($grade_rank)) {
			$grade_rank['rank'] = $rank ?? 0;
		}

		log_kb(['grade_rank-new' => $grade_rank]);

		$grade_rank = !empty($grade_rank)
			? self::_formatGradeRank($rank , $grade_rank)
			: self::_genGradeRank(compact('event_id', 'event_challenge_grade_id', 'school_id', 'grade', 'section'), 'grade')
		;

		log_kb(['grade_rank-data' => $grade_rank]);

		return $grade_rank;
	}

	private function _genGradeRank($rank_info = [], $type = 'city') {
		log_kb(['_genGradeRank' => $rank_info]);
		if (empty($this->event_site_model->get_all([
			'event_id'	=> $rank_info['event_id'],
			'site_id'	=> $rank_info['school_id'],
		])['total'])) return;

		$school_info 	= $this->site_model->get($rank_info['school_id']);
		$city_info 		= $this->city_model->get($school_info['city_id']);

		return self::_formatGradeRank(0, [
			'id'						=> 0,
			'rank'						=> 0,
			'event_challenge_grade_id'	=> $rank_info['event_challenge_grade_id'],
			'event_id'					=> $rank_info['event_id'],
			'school_name'				=> $school_info['name'],
			'school_id'					=> $school_info['id'],
            'grade'                     => $rank_info['grade'],
            'section'                   => $rank_info['section'],
			'school_code'				=> $school_info['site_code'],
			'city'						=> $city_info['name'],
			'state'						=> $city_info['state'],
			'score'						=> self::_getGradePublishedAuthorCount(['event_id' => $rank_info['event_id'], 'site_id' => $school_info['id'], 'grade' => $rank_info['grade'], 'section' => $rank_info['section']]),
			'total_students'			=> self::_getGradeRegisteredCount(['event_id' => $rank_info['event_id'], 'site_id' => $school_info['id'], 'grade' => $rank_info['grade'], 'section' => $rank_info['section']]),
		]);
	}

	private function _formatGradeRank($rank = 0, $item = []) {
		return [
			'id'						=> $item['id'],
			'rank'						=> $rank,
			'event_challenge_grade_id'	=> $item['event_challenge_grade_id'],
			'event_id'					=> $item['event_id'],
			'name'						=> $item['school_name'],
			'school_id'					=> $item['school_id'],
			'school_code'				=> $item['school_code'],
            'grade'                     => $item['grade'],
            'section'                   => $item['section'],
			'city'						=> $item['city'],
			'state'						=> $item['state'],
			'score'						=> $item['score'],
			'total_registered_author'	=> $item['total_students'] ?? 0,
			'total_published_author'	=> $item['score']
			
		];
	}

    private function _getGradePublishedCount($filter = []) {
		return count(array_unique(array_column($this->event_book_model->get_all([
			'event_id'	=> $filter['event_id'] ?? 0,
			'site_id'	=> $filter['site_id'] ?? 0,
			'grade'		=> $filter['grade'] ?? 0,
			'section'	=> $filter['section'] ?? '',
		])['rows'] ?? [], 'book_id')));
	}

	private function _getGradeRegisteredCount($filter = []) {
		if (in_array($filter['site_id'], [1, 2])) return 0;

		return $this->event_user_model->get_all([
			'event_id'	=> $filter['event_id'] ?? 0,
			'site_id'	=> $filter['site_id'] ?? 0,
			'grade'		=> $filter['grade'] ?? 0,
			'section'	=> $filter['section'] ?? '',
		])['total'] ?? 0;
	}

    private function _getGradePublishedAuthorCount($filter = []) {
		return count(array_unique(array_column($this->event_book_model->get_all([
			'event_id'	=> $filter['event_id'] ?? 0,
			'site_id'	=> $filter['site_id'] ?? 0,
			'grade'		=> $filter['grade'] ?? 0,
			'section'	=> $filter['section'] ?? '',
		])['rows'] ?? [], 'user_id')));
	}

    private function _pushGradeUpdate($rank_id = 0, $new_score = 0, $type = 'grade') {
		$rank_info 	= $this->{sprintf('ranking_%s_model', $type)}->get($rank_id);
		$rank_key 	= self::_getGradeKey($rank_info, $type);
		$old_rank 	= $this->redis_lib->getRank($rank_key, $rank_info['id']);

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

		log_kb(['Ranking::_pushGradeUpdate' => [
			'old_rank'		=> $old_rank,
			'new_rank'		=> $new_rank,
			'type'			=> $type,
		]]);

        $alert_payload['rank_data'] = array_merge(
			self::{sprintf('_format%sRank', ucwords($type))}($new_rank, $rank_info),
			[
				'old_rank'	=> $old_rank,
				'new_rank'	=> $new_rank,
			]
		);

		self::_saveGradeAlertForEveryOne($rank_info, $alert_payload, $type);
	}

    private function _getGradeKey($rank_info = [], $type = 'grade') {
		return vsprintf('live_school_%s_ranks_%s_%s_%s_%s', [
			$type,
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$rank_info['event_id'],
			$rank_info[sprintf('event_challenge_%s_id', $type)],
            $rank_info['school_id']
		]);
	}

    public function getSchoolGradeUpdate($event_id = 0, $event_challenge_grade_id = 0, $school_id = 0, $user_id = 0) {
        self::_updateLiveSchoolGrade(
			compact('event_id', 'event_challenge_grade_id', 'school_id', 'user_id'),
			$user_id,
			'grade'
		);

		self::_pushGradeSSE(
			compact('event_id', 'event_challenge_grade_id', 'school_id', 'user_id'),
			$user_id,
			'grade'
		);   
    }

    private function _saveGradeAlertForEveryOne($rank_info = [], $alert_payload = [], $type = 'grade') {
		$users = self::_getLiveSchoolGrade($rank_info, $type);

		log_kb(['_saveGradeAlertForEveryOne' => $users, [$alert_payload]]);

		foreach ($users as $user_id) {
			$this->cache->save(
				self::_getAlertSchoolgradeKey($rank_info, $user_id, $type),
				json_encode($alert_payload),
				300
			);
		}
	}

	private function _updateLiveSchoolGrade($rank_info = [], $user_id = 0, $type = 'grade') {
		$users = self::_getLiveSchoolGrade($rank_info, $type);

		if (!in_array($user_id, $users)) {
			$users[] = $user_id;
		} else {
			return;
		}

		log_kb(['_updateLiveSchoolGrade::new' => $users, [$user_id, $type, $rank_info]]);

		$this->cache->save(self::_getLiveSchoolGradeKey($rank_info, $type), json_encode($users), 900);
	}

	private function _getLiveSchoolGrade($rank_info = [], $type = 'grade') {
		$users = json_decode($this->cache->get(self::_getLiveSchoolGradeKey($rank_info, $type)), true);

		return $users ?? [];
	}

	private function _getLiveSchoolGradeKey($rank_info = [], $type = 'grade') {
		return vsprintf('event_live_%s_school_%s_%s_%s', [
			$type,
			(int)$rank_info['event_id'],
			(int)$rank_info[sprintf('event_challenge_%s_id', $type)],
			(int)$rank_info['school_id'],
		]);
	}

	private function _pushGradeSSE($rank_info = [], $user_id = 0, $type = 'grade') {
		$alert_key = self::_getAlertSchoolgradeKey($rank_info, $user_id, $type);

		$json = json_decode($this->cache->get($alert_key), true);

		log_kb(['SchoolRanking_lib::getUpdate::' => [
			$json,
			$alert_key,
		]]);

		self::_removeSchoolgradeUpdate($rank_info, $user_id, $type);

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

	private function _getAlertSchoolgradeKey($rank_info = [], $user_id = 0, $type = 'grade') {
		return vsprintf('%s_%s_school_rank_update_%s_%s_%s', [
			$type,
			(int)$rank_info['event_id'],
			(int)$rank_info[sprintf('event_challenge_%s_id', $type)],
			(int)$rank_info['school_id'],
			$user_id,
		]);
	}

	private function _removeSchoolgradeUpdate($rank_info = [], $user_id = 0, $type = 'grade') {
		$rank_key = self::_getAlertSchoolgradeKey($rank_info, $user_id, $type);

		log_kb([
			'rank_key' => $rank_key
		]);

		$this->cache->delete($rank_key);
	}
 
    public function syncGradeRankAfterProfileUpdate($data = []) {
        $user_id = (int) ($data['user_id'] ?? 0);
        $old_profile = $data['old_profile'] ?? [];
        $new_profile = $data['new_profile'] ?? [];

        if (!$user_id || empty($old_profile) || empty($new_profile)) {
            log_kb([
                'syncGradeRankAfterProfileUpdate::invalid_input' => $data
            ]);
            return;
        }

        $CI =& get_instance();
        $CI->load->model('event/Event_model');

        $profiles = [$old_profile, $new_profile];

        foreach ($profiles as $profile) {
            $site_id = $profile['site_id'];
            $grade = $profile['grade'];
            $section = $profile['section'];

            if (!$site_id) {
                continue;
            }

            $school_info = $this->site_model->get($site_id);

            if (empty($school_info)) {
                continue;
            }

            $event_rows = $this->event_challenge_grade_model->getActiveChallengeBySite($site_id);
			if (empty($event_rows)) continue;

            foreach ($event_rows as $event_row) {
                $event_id = (int) $event_row['event_id'];
                $challenge_id = (int) $event_row['challenge_id'];

                $event_info = $CI->event_model->get($event_id);

                if (empty($event_info)) continue;

                $filter = [
                    'event_id' => $event_id,
                    'site_id' => $site_id,
                    'grade' => $grade,
                    'section' => $section,
                ];

                $book_published     = self::_getGradePublishedCount($filter);
                $total_students     = self::_getGradeRegisteredCount($filter);
                $published_author   = self::_getGradePublishedAuthorCount($filter);

                $rank_info = $this->ranking_grade_model->get_all([
                    'event_challenge_grade_id' => $challenge_id,
                    'event_id' => $event_id,
                    'school_id' => $site_id,
                    'grade' => $grade,
                    'section' => $section,
                ])['rows'][0] ?? [];

                if ($total_students <= 0 ) {
                    if (!empty($rank_info['id'])) {
                        $this->ranking_grade_model->delete($rank_info['id']);
                        self::_removeSchoolgradeUpdate($rank_info, $site_id, 'grade');
                    }
                    continue;
                }

                if (!empty($rank_info['id'])) {
                    $rank_id = $rank_info['id'];

                    $this->ranking_grade_model->edit($rank_id, [
                        'score'                     => $published_author,
                        'book_published'            => $book_published,
                        'total_students'            => $total_students,
                        'total_published_author'    => $published_author,
                    ]);
                } else {
                    $city_info  = $this->city_model->get($school_info['city_id']);

                    $rank_id = $this->ranking_grade_model->add([
                        'event_challenge_grade_id'  => $challenge_id,
                        'event_id'                  => $event_id,
                        'school_id'                 => $site_id,
                        'school_code'               => $school_info['site_code'],
                        'school_name'               => $school_info['name'],
                        'owner_name'                => $school_info['owner_name'],
                        'owner_email'               => $school_info['owner_email'],
                        'owner_mobile'              => $school_info['owner_mobile'],
                        'grade'                     => $grade,
                        'section'                   => $section,
                        'city'                      => $city_info['name'] ?? '',
                        'state'                     => $city_info['state'] ?? '',
                        'book_published'            => $book_published,
                        'total_students'            => $total_students,
                        'score'                     => $published_author,
                        'total_published_author'    => $published_author,
                    ]);
                }

                if (!empty($rank_id)) {
                    self::_pushGradeUpdate($rank_id, $published_author, 'grade');
                }
            }
        }
    }
}
