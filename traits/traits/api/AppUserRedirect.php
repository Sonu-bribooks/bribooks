<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait AppUserRedirect {
	public function saveAppUserRedirect() {
		$this->form_validation->set_rules('action', _l('action'), 'trim|required');
		self::_runFormValidation();

		if (!$this->json) {
			$this->app_user_redirect_model->add([
				'user_id'   => $this->session->userdata('user_id') ?? 0,
				'action'	=> $this->input->post('action'),
				'data'	  	=> json_encode($this->input->post('data')),
			]);

			$this->json['success'] = _l('success');
		}
	}

	public function getAppUserRedirect() {
		if (!$this->json) {
			$app_data = [];

			if (!empty($this->session->userdata('user_id'))) {
				if (!empty($info = $this->app_user_redirect_model->getByUserId($this->session->userdata('user_id')))) {
					$app_data = [
						'action' => $info['action'],
						'data'   => json_decode($info['data'])
					];

					$this->app_user_redirect_model->deleteByUserId($info['user_id']);
				}
			}

			$this->json = $app_data;
		}
	}
}
