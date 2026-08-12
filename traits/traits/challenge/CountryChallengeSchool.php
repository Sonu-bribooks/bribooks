<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CountryChallengeSchool {
	public function getCountryChallengeSchoolRanks() {
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

		$this->form_validation->set_rules('country_id', _l('country'), [
			'trim',
			'required',
			'numeric',
			['country', [$this->validate_model, 'country']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('SchoolRanking_lib', 'schoolranking_lib');
			$this->load->model('event/EventChallengeCountry_model', 'event_challenge_country_model');

			$rank_results = $this->schoolranking_lib->getCountryRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_country_id'),
				$this->input->post('country_id'),
				$this->input->post('page') ?? 1,
				$this->input->post('search')
			);

			if ($this->input->post('user_id')) {
				$this->json['user_rank'] = $this->schoolranking_lib->getSchoolCountryRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_country_id'),
					$this->input->post('country_id'),
					$this->input->post('user_id')
				);
			}

			$this->json['ranks'] = array_values($rank_results['ranks']);
			$this->json['total'] = $rank_results['total'];

			$country_info 	= $this->country_model->get($this->input->post('country_id'));

			$this->json['heading'] 	= $country_info['name'] ?? '';
			$this->json['country'] 	= $country_info['name'] ?? '';
		}
	}

	public function getCountryChallengeSchoolUpdate($event_id = 0, $event_challenge_country_id = 0, $country_id = 0) {
		if (!$this->json) {
			$this->load->library('SchoolRanking_lib', 'schoolranking_lib');
			$this->schoolranking_lib->getCountryUpdate($event_id, $event_challenge_country_id, $country_id, get_bb_user_id());
		}
	}
}
