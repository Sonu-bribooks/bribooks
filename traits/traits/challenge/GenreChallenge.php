<?php defined('BASEPATH') or exit('No direct script access allowed');

trait GenreChallenge {
	public function getGenreChallenge() {
		$this->form_validation->set_rules('slug', _l('slug'), [
			'trim',
			'required',
			'min_length[10]',
			'max_length[255]',
			['slug', [$this->validate_model, 'event_challenge_genre_slug']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$challenge_info = $this->event_challenge_genre_model->getBySlug($this->input->post('slug'));

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

	public function getGenreChallenges() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$event_info = $this->event_model->get($this->input->post('event_id'));

			if (!($event_info['start_date'] <= date('Y-m-d H:i:s'))) {
				$this->json['error'] = _l('event_not_started');
				return;
			}

			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->load->model('event/EventChallengeGenre_model', 'event_challenge_genre_model');

			$results = $this->event_challenge_genre_model->get_all([
				'event_id'	    => (int)$this->input->post('event_id'),
				'type'			=> $this->input->post('type') ?? 'user',
			])['rows'] ?? [];

			$sort_order = $this->json['challenges'] = [];

			foreach ($results as $item) {
				$challenge_data = [
					'id'		    => $item['id'],
					'name'		    => $item['name'],
					'start_date'    => $item['display_date'],
					'end_date'	    => $item['end_date'],
					'counter'		=> date('Y-m-d\TH:i:s\Z', strtotime('-330 minutes', strtotime($item['end_date']))),
					'locked'	    => $item['display_date'] > date('Y-m-d H:i:s'),
				];

				$sort_order[] = $item['display_date'];

				if ($item['display_date'] <= date('Y-m-d H:i:s') && $item['end_date'] >= date('Y-m-d H:i:s')) {
					$this->json['active_challenge'] = $challenge_data;
					$this->json['active_counter'] 	= $challenge_data;
				} else if ($item['display_date'] >= date('Y-m-d H:i:s') && $item['end_date'] >= date('Y-m-d H:i:s')) {
					$this->json['active_counter'] 	= $challenge_data;
				}

				$this->json['challenges'][] = $challenge_data;
			}

			array_multisort($sort_order, $this->json['challenges']);
		}
	}

	public function getGenreChallengeRanks() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('event_challenge_genre_id', _l('event_challenge_genre'), [
			'trim',
			'required',
			'numeric',
		]);

		$this->form_validation->set_rules('genre_id', _l('genre'), [
			'trim',
			'required',
			'numeric',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->load->model('event/EventChallengeGenre_model', 'event_challenge_genre_model');

			$rank_results = $this->ranking_lib->getGenreRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_genre_id'),
				$this->input->post('genre_id'),
				$this->input->post('page') ?? 1,
				$this->input->post('search')
			);

			$this->json['ranks'] = array_values($rank_results['ranks']);

			$this->json['total'] = $rank_results['total'];

			if ($this->session->userdata('user_id') || $this->input->post('user_id')) {
				$user_id = $this->input->post('user_id')
					? (int)$this->input->post('user_id')
					: (int)$this->session->userdata('user_id');
				$book_id = $this->input->post('book_id')
					? (int)$this->input->post('book_id')
					: 0;

				if (empty($this->json['user_rank'] = $this->ranking_lib->getUserGenreRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_genre_id'),
					$this->input->post('genre_id'),
					$user_id,
					$book_id
				)) || empty($this->json['user_rank']['book_id'])) {
					$this->json['user_rank'] = $this->ranking_lib->getUserNoGenreRank(
						$this->input->post('event_id'),
						$this->input->post('event_challenge_genre_id'),
						$this->input->post('genre_id'),
						$user_id,
						$book_id
					);
				}
			}

			$this->json['heading'] 	= _l('Genre List');
		}
	}

	public function getGenreChallengeUpdate($event_id = 0, $event_challenge_genre_id = 0, $genre_id = 0) {
		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');
			$this->ranking_lib->getGenreUpdate($event_id, $event_challenge_genre_id, $genre_id, get_bb_user_id());
		}
	}
}
