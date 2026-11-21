<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Consent {
	public function saveUserConsent() {
		$this->form_validation->set_rules('consent_id', _l('consent_id'), 'trim|required');
		self::_runFormValidation();

        $this->load->library('user_agent');

		if (!$this->json) {
            $consent_info = $this->consent_model->get((int)$this->input->post('consent_id'));

            if (empty($consent_info)) {
                $this->json['error'] = _('invalid request');
                return;
            }
            
			$this->consent_audit_log_model->add([
                'consent_id' => $consent_info['id'] ?? 0,
				'user_id'   => (int)($this->session->userdata('user_id') ?? 0),
				'version'	=> $consent_info['version'],
                'ip'		=> $this->input->ip_address(),
				'platform'	=> $this->agent->platform() ?: 'unknown',
				'agent'		=> $this->agent->agent_string() ?: 'unknown',
			]);

			$this->json['success'] = _l('success');
		}
	}

	public function getConsent() {
		if (!$this->json) {
			$consent_data = [];

            if (!empty($consent_info = $this->consent_model->getActiveConsent())) {
                $consent_data = [
                    'id'        => $consent_info['id'],
                    'type'      => $consent_info['type'],
                    'title'     => $consent_info['title'],
                    'content'   => $consent_info['content'],
                    'status'    => $consent_info['status'],
                ];
            }

			$this->json = $consent_data;
		}
	}
}
