<?php defined('BASEPATH') or exit('No direct script access allowed');

trait StateChallengeTeacher {
	public function getStateChallengeTeacherRanks() {
		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

		$this->form_validation->set_rules('event_challenge_state_id', _l('event_challenge_state'), [
			'trim',
			'required',
			'numeric',
			['event_challenge_state', [$this->validate_model, 'event_challenge_state']]
		]);

		$this->form_validation->set_rules('state_id', _l('state'), [
			'trim',
			'required',
			'numeric',
			['state', [$this->validate_model, 'state']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->library('TeacherRanking_lib', 'teacherranking_lib');
			$this->load->model('event/EventChallengeState_model', 'event_challenge_state_model');

			$rank_results = $this->teacherranking_lib->getStateRanks(
				$this->input->post('event_id'),
				$this->input->post('event_challenge_state_id'),
				$this->input->post('state_id'),
				$this->input->post('page') ?? 1,
				$this->input->post('search')
			);

			if ($this->input->post('user_id')) {
				$this->json['user_rank'] = $this->teacherranking_lib->getTeacherStateRank(
					$this->input->post('event_id'),
					$this->input->post('event_challenge_state_id'),
					$this->input->post('state_id'),
					$this->input->post('user_id')
				);
			}

			$this->json['ranks'] = array_values($rank_results['ranks']);
			$this->json['total'] = $rank_results['total'];

			$state_info = $this->state_model->get($this->input->post('state_id'));

			$this->json['heading'] 	= $state_info['name'] ?? '';
			$this->json['state'] 	= $state_info['name'] ?? '';
		}
	}

	public function getStateChallengeTeacherUpdate($event_id = 0, $event_challenge_state_id = 0, $state_id = 0) {
		if (!$this->json) {
			$this->load->library('TeacherRanking_lib', 'teacherranking_lib');
			$this->teacherranking_lib->getStateUpdate($event_id, $event_challenge_state_id, $state_id, get_bb_user_id());
		}
	}
}
