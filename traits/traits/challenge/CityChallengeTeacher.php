<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CityChallengeTeacher {
	public function getCityChallengeTeacherRanks() {
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
			$this->load->library('TeacherRanking_lib', 'teacherranking_lib');
			$this->load->model('event/EventChallengeCity_model', 'event_challenge_city_model');

			$rank_results = $this->teacherranking_lib->getCityRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_city_id'),
				$this->input->post('city_id'),
				$this->input->post('page') ?? 1,
				$this->input->post('search')
			);

			if ($this->input->post('user_id')) {
				$this->json['user_rank'] = $this->teacherranking_lib->getTeacherCityRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_city_id'),
					$this->input->post('city_id'),
					$this->input->post('user_id')
				);
			}

			$this->json['ranks'] = array_values($rank_results['ranks']);
			$this->json['total'] = $rank_results['total'];

			$city_info 	= $this->city_model->get($this->input->post('city_id'));
			$state_info = $this->state_model->get($city_info['state_id']);

			$this->json['heading'] 	= sprintf('%s, %s', $city_info['name'] ?? '', $state_info['name'] ?? '');
			$this->json['state'] 	= $state_info['name'] ?? '';
		}
	}

	public function getCityChallengeTeacherUpdate($event_id = 0, $event_challenge_city_id = 0, $city_id = 0) {
		if (!$this->json) {
			$this->load->library('TeacherRanking_lib', 'teacherranking_lib');
			$this->teacherranking_lib->getCityUpdate($event_id, $event_challenge_city_id, $city_id, get_bb_user_id());
		}
	}
}
