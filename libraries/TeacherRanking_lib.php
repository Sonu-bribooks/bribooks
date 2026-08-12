<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

load_trait('teacherranking');

final class TeacherRanking_lib {
	public function __construct() {
		$this->CI 		=& get_instance();
		$this->db 		= $this->CI->db;
		$this->session 	= $this->CI->session;
		$this->load 	= $this->CI->load;
		$this->config 	= $this->CI->config;
		$this->input 	= $this->CI->input;

		$this->load->model('book/Book_model');
		$this->load->model('user/Student_model');
		$this->load->model('user/Teacher_model');

		$this->load->model('common/Site_model');
		$this->load->model('common/Cron_model');

		$this->load->model('localisation/City_model');
		$this->load->model('localisation/Country_model');

		$this->load->model('ranking/teacher/TeacherRankingCountry_model');
		$this->load->model('ranking/teacher/TeacherRankingState_model');
		$this->load->model('ranking/teacher/TeacherRankingCity_model');
		$this->load->model('ranking/teacher/TeacherRankingSchool_model');

		$this->load->model('event/Event_model');
		$this->load->model('event/EventSite_model');
		$this->load->model('event/EventTeacher_model');
		$this->load->model('event/EventBook_model');
		$this->load->model('event/EventUser_model');
		$this->load->model('event/EventChallenge_model');
		$this->load->model('event/EventChallengeCountry_model');
		$this->load->model('event/EventChallengeState_model');
		$this->load->model('event/EventChallengeCity_model');
		$this->load->model('event/EventChallengeSchool_model');

		$this->load->library('Redis_lib');

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$this->book_model 		= $this->CI->Book_model;
		$this->student_model 	= $this->CI->Student_model;
		$this->teacher_model 	= $this->CI->Teacher_model;
		$this->site_model 		= $this->CI->Site_model;
		$this->city_model 		= $this->CI->City_model;
		$this->country_model 	= $this->CI->Country_model;

		$this->ranking_school_model 	= $this->CI->TeacherRankingSchool_model;
		$this->ranking_city_model 		= $this->CI->TeacherRankingCity_model;
		$this->ranking_state_model 		= $this->CI->TeacherRankingState_model;
		$this->ranking_country_model 	= $this->CI->TeacherRankingCountry_model;

		$this->event_model 					= $this->CI->Event_model;
		$this->event_site_model 			= $this->CI->EventSite_model;
		$this->event_teacher_model 			= $this->CI->EventTeacher_model;
		$this->event_book_model 			= $this->CI->EventBook_model;
		$this->event_user_model 			= $this->CI->EventUser_model;
		$this->event_challenge_school_model = $this->CI->EventChallengeSchool_model;
		$this->event_challenge_city_model 	= $this->CI->EventChallengeCity_model;
		$this->event_challenge_state_model 	= $this->CI->EventChallengeState_model;
		$this->event_challenge_country_model= $this->CI->EventChallengeCountry_model;

		$this->cron_model 	= $this->CI->Cron_model;
		$this->cache 		= $this->CI->cache;
		$this->redis_lib 	= $this->CI->redis_lib;

		$this->limit 		= ENVIRONMENT === 'production' ? 10 : 10;

		$this->top_sites 	= [];
	}

	use
		StateTeacherRanking,
		CountryTeacherRanking,
		CityTeacherRanking,
		SchoolTeacherRanking,
		BuildTeacherRanking
	;

	public function updateRank($book_id = 0) {
		log_kb([
			'updateRank'	=> $book_id
		]);

		$book_info 		= $this->book_model->get($book_id);
		$book_events 	= $this->event_book_model->get_all([
			'book_id'	=> $book_id,
		])['rows'] ?? [];

		foreach ($book_events as $book_event) {
			$event_info = $this->event_model->get($book_event['event_id']);

			self::updateCountryRank([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
			]);

			self::updateStateRank([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
			]);

			self::updateCityRank([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
			]);

			self::updateSchoolRank([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
			]);
		}
	}

	private function _getPublishedCount($filter = []) {
		return count(array_unique(array_column($this->event_book_model->get_all([
			'event_id'	=> $filter['event_id'] ?? 0,
			'site_id'	=> $filter['site_id'] ?? 0,
			'grade'		=> $filter['grade'] ?? 0,
			'section'	=> $filter['section'] ?? '',
		])['rows'] ?? [], 'book_id')));
	}

	private function _getRegisteredCount($filter = []) {
		if (in_array($filter['site_id'], [1, 2])) return 0;

		return $this->event_user_model->get_all([
			'event_id'	=> $filter['event_id'] ?? 0,
			'site_id'	=> $filter['site_id'] ?? 0,
			'grade'		=> $filter['grade'] ?? 0,
			'section'	=> $filter['section'] ?? '',
		])['total'] ?? 0;
	}

	private function _pushSSE($rank_info = [], $user_id = 0, $type = 'city') {
		$alert_key = self::_getAlertUserKey($rank_info, $user_id, $type);

		$json = json_decode($this->cache->get($alert_key), true);

		log_kb(['Ranking_lib::getUpdate::' => [
			$json,
			$alert_key,
		]]);

		self::_removeUserUpdate($rank_info, $user_id, $type);

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

	private function _removeUserUpdate($rank_info = [], $user_id = 0, $type = 'city') {
		$rank_key = self::_getAlertUserKey($rank_info, $user_id, $type);

		log_kb([
			'rank_key' => $rank_key
		]);

		$this->cache->delete($rank_key);
	}

	private function _pushUpdate($rank_id = 0, $new_score = 0, $type = 'city') {
		$rank_info 	= $this->{sprintf('ranking_%s_model', $type)}->get($rank_id);
		$rank_key 	= self::_getKey($rank_info, $type);
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

		log_kb(['Ranking::_pushUpdate' => [
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

		self::_saveAlertForEveryOne($rank_info, $alert_payload, $type);
	}

	private function _getRankScore($u_rank = 1, $rank_info = [], $full_rank = false, $type = 'city') {
		$rank_key 	= self::_getKey($rank_info, $type);

		$result 	= array_keys($this->redis_lib->getRanks($rank_key, $u_rank - 1, $u_rank - 1));
		$user_rank 	= $this->{sprintf('ranking_%s_model', $type)}->get($result[0] ?? '');

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

	private function _saveAlertForEveryOne($rank_info = [], $alert_payload = [], $type = 'city') {
		$users = self::_getLiveUsers($rank_info, $type);

		log_kb(['_saveAlertForEveryOne' => $users, [$alert_payload]]);

		foreach ($users as $user_id) {
			// save alery for every one
			$this->cache->save(
				self::_getAlertUserKey($rank_info, $user_id, $type),
				json_encode($alert_payload),
				300
			);
		}
	}

	private function _updateLiveUser($rank_info = [], $user_id = 0, $type = 'city') {
		$users = self::_getLiveUsers($rank_info, $type);

		if (!in_array($user_id, $users)) {
			$users[] = $user_id;
		} else {
			return;
		}

		log_kb(['_updateLiveUser::new' => $users, [$user_id, $type, $rank_info]]);

		$this->cache->save(self::_getAlertLiveUserKey($rank_info, $type), json_encode($users), 900);
	}

	private function _getLiveUsers($rank_info = [], $type = 'city') {
		$users = json_decode($this->cache->get(self::_getAlertLiveUserKey($rank_info, $type)), true);

		return $users ?? [];
	}

	private function _getKey($rank_info = [], $type = 'city') {
		return vsprintf('live_teacher_%s_ranks_%s_%s_%s_%s', [
			$type,
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$rank_info['event_id'],
			$rank_info[sprintf('event_challenge_%s_id', $type)],
			$rank_info[sprintf('%s_id', $type)],
		]);
	}

	private function _getAlertUserKey($rank_info = [], $user_id = 0, $type = 'city') {
		return vsprintf('%s_%s_teacher_rank_update_%s_%s_%s', [
			$type,
			(int)$rank_info['event_id'],
			(int)$rank_info[sprintf('event_challenge_%s_id', $type)],
			(int)$rank_info[sprintf('%s_id', $type)],
			$user_id,
		]);
	}

	private function _getAlertLiveUserKey($rank_info = [], $type = 'city') {
		return vsprintf('event_live_%s_teacher_%s_%s_%s', [
			$type,
			(int)$rank_info['event_id'],
			(int)$rank_info[sprintf('event_challenge_%s_id', $type)],
			(int)$rank_info[sprintf('%s_id', $type)],
		]);
	}

	private function _getCopyText($sold = 0) {
		if ($sold > 1) {
			return _l('Books');
		}

		return _l('Book');
	}
}
