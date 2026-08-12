<?php defined('BASEPATH') or exit('No direct script access allowed');

trait GlobalChallenge {
	public function getGlobalChallengeRanks() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('event_challenge_country_id', _l('event_challenge_country'), [
			'trim',
			'required',
			'numeric',
			['event_challenge_country', [$this->validate_model, 'event_challenge_country']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');

			$rank_results = $this->ranking_lib->getCountryRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_country_id'),
				$this->input->post('page') ?? 1,
				$this->input->post('search')
			);

			$this->json['ranks'] = array_values($rank_results['ranks']);

			$this->json['total'] = $rank_results['total'];

			if ($this->session->userdata('user_id') || $this->input->post('user_id')) {
				if (empty($this->json['user_rank'] = $this->ranking_lib->getUserCountryRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_country_id'),
					$this->input->post('user_id')
						? $this->input->post('user_id')
						: $this->session->userdata('user_id')
				)) || empty($this->json['user_rank']['book_id'])) {
					$this->json['user_rank'] = $this->ranking_lib->getUserNoCountryRank(
						$this->input->post('event_id'),
						$this->input->post('event_challenge_country_id'),
						$this->input->post('user_id')
							? $this->input->post('user_id')
							: $this->session->userdata('user_id')
					);
				}
			}

			$event_info = $this->event_model->get($this->input->post('event_id'));
			$country_info = $this->country_model->get($event_info['country_id']);

			$this->json['heading'] = sprintf('%s', $country_info['name'] ?? '');
		}
	}

	public function getGlobalChallengeBestSeller() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('event_challenge_country_id', _l('event_challenge_country'), [
			'trim',
			'required',
			'numeric',
			['event_challenge_country', [$this->validate_model, 'event_challenge_country']]
		]);

		$this->form_validation->set_rules('limit', _l('limit'), [
			'trim',
			'required',
			'numeric',
			'min_length[1]',
			'max_length[4]'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('Ranking_lib', 'ranking_lib');

			$rank_results = $this->ranking_lib->getCountryRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_country_id'),
				1,
				'',
				$this->input->post('limit')
			);

			$results = array_values($rank_results['ranks']);

			$this->json['total'] = $rank_results['total'];

			$rankings = array_map(function($item, $key) {
				return [
					'id'			=> $item['id'],
					'rank'			=> $item['rank'],
					'name'			=> ucfirst($item['book_name']),
					'cover_image'	=> $item['book_image'],
					'author_name'	=> $item['author_name'],
					'author_image'	=> $item['author_image'],
					'slug'			=> $item['book_slug'],
					'state'			=> $item['state'],
					'city'			=> $item['city'],
					'school'		=> $item['school'],
					'grade'			=> '',
					'section'		=> '',
					'royalty'		=> 0,
					'sold'			=> $item['score'],
					'total_sold'	=> $item['score'],
					'amazon_url'	=> $item['amazon_url'] ?? '',
					'message'		=> $item['message'] ?? '',
				];
			}, $results, array_keys($results));

			$this->json['rankings'] = array_values($rankings);
		}
	}
}
