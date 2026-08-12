<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

load_trait('ranking');

final class Vote_lib {
	public function __construct() {
		$this->CI       =& get_instance();
		$this->db       = $this->CI->db;
		$this->session  = $this->CI->session;
		$this->load     = $this->CI->load;
		$this->config   = $this->CI->config;
		$this->input    = $this->CI->input;

		$this->load->model('book/Book_model');

		$this->load->model('common/Site_model');
		$this->load->model('common/Cron_model');

		$this->load->model('design/Genre_model');
		$this->load->model('design/Category_model');

		$this->load->model('localisation/City_model');
		$this->load->model('localisation/State_model');
		$this->load->model('localisation/UnionTerritory_model');

		$this->load->model('ranking/RankingVote_model');

		$this->load->model('event/Event_model');
		$this->load->model('event/EventUser_model');
		$this->load->model('event/EventVoteBook_model');
		$this->load->model('event/EventUserVote_model');
		$this->load->model('event/EventChallengeVote_model');;
		$this->load->model('ranking/LeagueBreakPointMessage_model');

		$this->load->model('user/User_model');
		$this->load->model('user/Student_model');
		$this->load->model('user/UserDeviceToken_model');
		$this->load->model('user/UserAppNotification_model');

		$this->load->library('Redis_lib');

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$this->book_model 								= $this->CI->Book_model;
		$this->student_model 							= $this->CI->Student_model;
		$this->user_model 								= $this->CI->User_model;
		$this->site_model 								= $this->CI->Site_model;
		$this->city_model 								= $this->CI->City_model;
		$this->state_model 								= $this->CI->State_model;

		$this->ranking_vote_model 						= $this->CI->RankingVote_model;

		$this->event_model								= $this->CI->Event_model;
		$this->event_user_model							= $this->CI->EventUser_model;
		$this->event_book_model							= $this->CI->EventBook_model;
		$this->event_user_vote_model					= $this->CI->EventUserVote_model;
		$this->event_vote_book_model					= $this->CI->EventVoteBook_model;

		$this->event_challenge_vote_model				= $this->CI->EventChallengeVote_model;

		$this->league_break_point_message_model			= $this->CI->LeagueBreakPointMessage_model;

		$this->genre_model 								= $this->CI->Genre_model;
		$this->category_model 							= $this->CI->Category_model;

		$this->cache 									= $this->CI->cache;
		$this->redis_lib								= $this->CI->redis_lib;

		$this->limit = ENVIRONMENT === 'production' ? 10 : 10;
	}

	use VoteRanking;

	private function _updateBookInfo($table, $id = 0, $data = []) {
		$this->db->update($table, [
			'author_name'	=> $data['author_name'],
			'author_image'	=> $data['author_image'],
			'book_image'	=> $data['book_image'],
			'book_name'		=> $data['book_name'],
			'book_slug'		=> $data['book_slug'],
		], [
			'id'			=> (int)$id
		]);
	}

	public function updateBookInfo($book_id = 0) {
		self::updateVoteBookInfo($book_id);
	}

	public function buildRanks($event_id = 0, $type = 'country') {
		return [];
	}

	public function buildRankByType($data = []) {
		return [];
	}

	public function updateRank($book_id = 0, $challenge_id = 0) {
		$book_info = $this->book_model->get($book_id);

		if (empty($book_info)) return;

		log_kb([
			'updateVoteRank'	=> $book_info
		]);

		$challenge_info = $this->event_challenge_vote_model->get($challenge_id);

		if (
			empty($challenge_info) ||
			$challenge_info['start_date'] > date('Y-m-d H:i:s') ||
			$challenge_info['end_date'] < date('Y-m-d H:i:s')
		) return;

		log_kb([
			'updateVoteRank::challenge: '	=> $challenge_info
		]);

		$user_events = $this->event_user_model->get_all([
			'event_id'	=> $challenge_info['event_id'],
			'user_id'	=> $book_info['user_id'],
			'start'		=> 0,
			'limit'		=> 1,
		])['rows'] ?? [];

		if (empty($user_events)) return;

		log_kb([
			'updateVoteRank::user_events: '	=> $user_events
		]);

		$event_books = $this->event_book_model->get_all([
			'book_id'	=> (int)$book_info['id'],
			'event_id'	=> (int)$challenge_info['event_id'],
			'start'		=> 0,
			'limit'		=> 1,
		])['rows'] ?? [];

		if (empty($event_books)) return;

		$event_vote_book_info = $this->event_vote_book_model->get_all([
			'event_id'		=> $challenge_info['event_id'],
			'challenge_id'	=> $challenge_info['id'],
			'book_id'		=> $book_info['id'],
			'start'			=> 0,
			'limit'			=> 1,
		])['rows'][0] ?? [];

		if (empty($event_vote_book_info)) return;

		self::updateVoteRank([
			'challenge_info'	=> $challenge_info,
			'book_info'			=> $book_info,
		]);
	}

	private function _formatNotificationTemplate($data = [], $message = '') {
		$find = [
			'{author_name}',
			'{book_name}',
			'{school_name}',
			'{city_name}',
			'{state_name}',
			'{achievement_url}',
			'{rank_url}',
		];

		$replace = [
			'author_name'		=> $data['author_name'] ?? '',
			'book_name'			=> $data['book_name'] ?? '',
			'school_name'		=> $data['school_name'] ?? '',
			'city_name'			=> $data['city_name'] ?? '',
			'state_name'		=> $data['state_name'] ?? '',
			'achievement_url'	=> $data['achievement_url'] ?? '',
			'rank_url'			=> $data['rank_url'] ?? '',
		];

		return str_replace($find, $replace, $message);
	}

	private function _getVoteText($sold = 0) {
		if ($sold > 1) {
			return _l('Votes');
		}

		return _l('Vote');
	}

	private function _formatVoteLeagueMessage($message, $data = []) {
		$find = [
			'{required_vote_count}',
			'{vote_text}',
		];

		$replace = [
			'required_vote_count'	=> $data['required_vote_count'] ?? '',
			'vote_text'				=> $data['vote_text'] ?? '',
		];

		return str_replace($find, $replace, $message);
	}
}
