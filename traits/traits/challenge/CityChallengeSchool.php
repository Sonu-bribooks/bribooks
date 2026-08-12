<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CityChallengeSchool {
	public function getCityChallengeSchoolRanks() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('event_challenge_city_id', _l('event_challenge_city'), [
			'trim',
			'required',
			'numeric',
			['event_challenge_city', [$this->validate_model, 'event_challenge_city']]
		]);

		$this->form_validation->set_rules('city_id', _l('city'), [
			'trim',
			'required',
			'numeric',
			['city', [$this->validate_model, 'city']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('SchoolRanking_lib', 'schoolranking_lib');
			$this->load->model('event/EventChallengeCity_model', 'event_challenge_city_model');

			$rank_results = $this->schoolranking_lib->getCityRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_city_id'),
				$this->input->post('city_id'),
				$this->input->post('page') ?? 1,
				$this->input->post('search')
			);

			if ($this->input->post('user_id')) {
				$this->json['user_rank'] = $this->schoolranking_lib->getSchoolCityRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_city_id'),
					$this->input->post('city_id'),
					$this->input->post('user_id')
				);
			} elseif (
				$this->input->post('school_id') &&
				($school_info = $this->school_model->get($this->input->post('school_id')))
			) {
				if ($school_info['site_id']) {
					$this->json['user_rank'] = $this->schoolranking_lib->getSchoolCityRank(
						$this->input->post('event_id'),
						$this->input->post('event_challenge_city_id'),
						$this->input->post('city_id'),
						$school_info['site_id']
					);
				} else {
					$city_info = $this->city_model->get($school_info['city_id']);

					$rank = [
						'id'						=> 0,
						'rank'						=> 0,
						'event_challenge_city_id'	=> (int)$this->input->post('event_challenge_city_id'),
						'event_id'					=> (int)$this->input->post('event_id'),
						'name'						=> $school_info['name'],
						'school_id'					=> $school_info['id'],
						'school_code'				=> $school_info['site_code'] ?? '',
						'city_id'					=> $school_info['city_id'] ?? 0,
						'city'						=> $city_info['name'] ?? '',
						'state'						=> $city_info['state'] ?? '',
						'score'						=> 0,
					];

					$rank['message'] = $this->schoolranking_lib->getCityMessage($rank);

					$this->json['user_rank'] = $rank;
				}
			}

			$this->json['ranks'] = array_values($rank_results['ranks']);
			$this->json['total'] = $rank_results['total'];

			$city_info 	= $this->city_model->get($this->input->post('city_id'));
			$state_info = $this->state_model->get($city_info['state_id']);

			$this->json['heading'] 	= sprintf('%s, %s', $city_info['name'] ?? '', $state_info['name'] ?? '');
			$this->json['state'] 	= $state_info['name'] ?? '';
		}
	}

	public function getCityChallengeSchoolUpdate($event_id = 0, $event_challenge_city_id = 0, $city_id = 0) {
		if (!$this->json) {
			$this->load->library('SchoolRanking_lib', 'schoolranking_lib');
			$this->schoolranking_lib->getCityUpdate($event_id, $event_challenge_city_id, $city_id, get_bb_user_id());
		}
	}
}
