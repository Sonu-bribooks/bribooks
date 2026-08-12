<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventVote {
	public function getEventBookVote() {
		$this->form_validation->set_rules('slug', _l('slug'), 'trim|required');
		$this->form_validation->set_rules('book_slug', _l('book_slug'), 'trim|required');

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($challenge_info = $this->event_challenge_vote_model->getBySlug($this->input->post('slug')))) {
				$this->json['error'] = _li('invalid_challenge');
				return;
			}

			if (empty($book_info = $this->book_model->getBySlug($this->input->post('book_slug')))) {
				$this->json['error'] = _li('invalid_book');
				return;
			}

			$genre_info = $this->genre_model->get($book_info['genre_id']);

			$this->load->model('event/EventVoteBook_model', 'event_vote_book_model');
			$this->load->model('event/EventUserVote_model', 'event_user_vote_model');
			$this->load->model('review/BookAiSummary_model', 'book_ai_summary_model');

			if (empty($info = $this->event_vote_book_model->get_all([
				'event_id'		=> (int)$challenge_info['event_id'] ?? 0,
				'challenge_id'	=> (int)$challenge_info['id'] ?? 0,
				'book_id'		=> (int)$book_info['id'],
			])['rows'][0] ?? [])) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			$summary_info = $this->book_ai_summary_model->get_all([
				'event_id'	=> (int)$challenge_info['event_id'] ?? 0,
				'book_id'	=> (int)$book_info['id'],
			])['rows'][0] ?? [];

			$votes = $this->event_user_vote_model->getTotalBookVote(
				$challenge_info['event_id'],
				$challenge_info['id'],
				$book_info['id']
			);

			$this->json['success'] 	= _li('success');
			$this->json['book'] 	= [
				'event_id' 		=> $challenge_info['event_id'] ?? 0,
				'challenge_id' 	=> $challenge_info['id'] ?? 0,
				'book_id' 		=> $book_info['id'] ?? 0,
				'genre_id' 		=> $book_info['genre_id'] ?? 0,
				'name' 			=> $book_info['name'] ?? '',
				'author_name' 	=> $book_info['author_name'] ?? '',
				'genre' 		=> $genre_info['name'] ?? '',
				'cover_image' 	=> $book_info['cover_image'] ?? '',
				'author_image' 	=> $book_info['author_image'] ?? '',
				'summary' 		=> !empty($summary_info['summary']) ? $summary_info['summary'] : $book_info['author_bio'],
				'votes' 		=> $votes
			];
		}
	}

	public function addEventBookVote() {
		$this->form_validation->set_rules('slug', _l('slug'), 'trim|required');
		$this->form_validation->set_rules('book_id', _l('book'), 'trim|required|numeric');

		self::_runFormValidation();

		if (!$this->json) {
			$user_id = (int)$this->session->userdata('user_id');

			if (
				!$user_id ||
				empty($user_info = $this->user_model->get($user_id))
			) {
				$this->json['error']		= _l('unauthorized');
				$this->json['unauthorized'] = true;
				return;
			}

			if (empty($challenge_info = $this->event_challenge_vote_model->getBySlug($this->input->post('slug')))) {
				$this->json['error'] = _li('invalid_challenge');
				return;
			}

			if (
				$challenge_info['start_date'] > date('Y-m-d H:i:s') ||
				$challenge_info['end_date'] < date('Y-m-d H:i:s')
			) {
				$this->json['error'] = _li('voting_closed');
				return;
			}

			$this->load->model('event/EventVoteBook_model', 'event_vote_book_model');
			$this->load->model('event/EventUserVote_model', 'event_user_vote_model');

			$info = $this->event_vote_book_model->get_all([
				'event_id'		=> (int)$challenge_info['event_id'] ?? 0,
				'challenge_id'	=> (int)$challenge_info['id'] ?? 0,
				'book_id'		=> (int)$this->input->post('book_id'),
			])['rows'][0] ?? [];

			if (empty($info)) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			$vote_info = $this->event_user_vote_model->get_all([
				'event_id'		=> (int)$challenge_info['event_id'] ?? 0,
				'challenge_id'	=> (int)$challenge_info['id'] ?? 0,
				'book_id'		=> (int)$this->input->post('book_id'),
				'user_id'		=> (int)$user_id,
			])['rows'][0] ?? [];

			if (!empty($vote_info)) {
				return $this->json['error'] = _li('You have already voted');
			}

			$this->event_user_vote_model->add([
				'event_id'		=> (int)$challenge_info['event_id'] ?? 0,
				'challenge_id'	=> (int)$challenge_info['id'] ?? 0,
				'book_id'		=> (int)$this->input->post('book_id'),
				'user_id'		=> (int)$user_id,
			]);

			$this->load->library('Vote_lib');
			$this->vote_lib->updateRank($this->input->post('book_id'), $challenge_info['id']);

			$this->json['success'] 	= _l('vote_successfully_saved');
			$this->json['votes'] 	= $this->event_user_vote_model->getTotalBookVote(
				$challenge_info['event_id'],
				$challenge_info['id'],
				$this->input->post('book_id')
			);

			$this->json['data'] = [
				'book_id'	=> (int)$this->input->post('book_id'),
				'user_name'	=> ($user_info['first_name'] ?? '') . ' ' . ($user_info['last_name'] ?? ''),
			];
		}
	}
}
