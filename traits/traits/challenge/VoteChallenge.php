<?php defined('BASEPATH') or exit('No direct script access allowed');

trait VoteChallenge {
	public function getVoteChallenge() {
		$this->form_validation->set_rules('slug', _l('slug'), [
			'trim',
			'required',
			'min_length[3]',
			'max_length[255]',
			['slug', [$this->validate_model, 'event_challenge_vote_slug']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$challenge_info = $this->event_challenge_vote_model->getBySlug($this->input->post('slug'));

			$challenge_data = [
				'id'		=> $challenge_info['id'],
				'event_id'	=> $challenge_info['event_id'],
				'name'		=> $challenge_info['name'],
				'layout'	=> $challenge_info['layout'],
				'is_dark'	=> $challenge_info['is_dark'],
				'event_logo'=> format_gallery_url($challenge_info['event_logo']),
				'background'=> format_gallery_url($challenge_info['background']),
				'start_date'=> $challenge_info['display_date'],
				'end_date'	=> $challenge_info['end_date'],
				'counter'	=> date('Y-m-d\TH:i:s\Z', strtotime('-330 minutes', strtotime($challenge_info['end_date']))),
				'locked'	=> $challenge_info['display_date'] > date('Y-m-d H:i:s'),
				'limit'		=> $challenge_info['limit'],
				'terms'		=> html_entity_decode($challenge_info['terms'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
			];

			$this->json['challenge'] = $challenge_data;
		}
	}

	public function getVoteChallengeRanks() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('event_challenge_vote_id', _l('event_challenge_vote'), [
			'trim',
			'required',
			'numeric',
			['event_challenge_vote', [$this->validate_model, 'event_challenge_vote']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('Vote_lib', 'vote_lib');
			$this->load->model('event/EventChallengeVote_model', 'event_challenge_vote_model');

			$event_challenge_vote_info = $this->event_challenge_vote_model->get($this->input->post('event_challenge_vote_id'));

			$rank_results = $this->vote_lib->getVoteRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_vote_id'),
				0,
				$this->input->post('page') ?? 1,
				$this->input->post('limit') ?? 0,
				$this->input->post('search')
			);

			if (!empty($event_challenge_vote_info) && !empty($event_challenge_vote_info['min_vote'])) {
				$rank_data 	= array_values($rank_results['ranks']);
				$min_vote 	= $event_challenge_vote_info['min_vote'] ?? 0;

				$this->json['ranks'] = array_map(function ($row) use ($min_vote) {
					if (($row['score'] ?? 0) < $min_vote) {
						$row['rank'] = 0;
					}
					return $row;
				}, $rank_data);
			} else {
				$this->json['ranks'] = array_values($rank_results['ranks']);
			}

			$this->json['total'] = $rank_results['total'];

			if ($this->session->userdata('user_id') || $this->input->post('user_id')) {
				$user_id = $this->input->post('user_id')
					? (int)$this->input->post('user_id')
					: (int)$this->session->userdata('user_id');
				$book_id = $this->input->post('book_id')
					? (int)$this->input->post('book_id')
					: 0;

				if (empty($this->json['user_rank'] = $this->vote_lib->getUserVoteRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_vote_id'),
					$this->input->post('league_type_id') ?? 0,
					$user_id,
					$book_id,
				)) || empty($this->json['user_rank']['book_id'])) {
					$this->json['user_rank'] = $this->vote_lib->getUserNoVoteRank(
						$this->input->post('event_id'),
						$this->input->post('event_challenge_vote_id'),
						$this->input->post('league_type_id'),
						$user_id,
						$book_id,
					);
				} else {
					if (!empty(($event_challenge_vote_info['min_vote'] ?? 0))) {
						if (($this->json['user_rank']['score'] ?? 0) < ($event_challenge_vote_info['min_vote'] ?? 0)) {
							$this->json['user_rank']['rank'] = 0;
						}
					}
				}
			}

			$this->json['heading'] = '';
		}
	}

	public function getVoteChallengeUpdate($event_id = 0, $event_challenge_vote_id = 0, $league_type_id = 0) {
		if (!$this->json) {
			$this->load->library('Vote_lib', 'vote_lib');
			$this->vote_lib->getVoteUpdate($event_id, $event_challenge_vote_id, $league_type_id, get_bb_user_id());
		}
	}
}
