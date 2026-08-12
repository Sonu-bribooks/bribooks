<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventFaq {
	public function getEventFaq() {
		$this->form_validation->set_rules('slug', _l('slug'), [
			'trim',
			'required',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($event_info = $this->event_model->get_all([
				'slug' => $this->input->post('slug') ?? ''
			])['rows'][0] ?? [])) {
				return $this->json['error'] = _l('invalid_url');
			}

			if (empty($landing_page_info = $this->event_landing_page_model->get_all([
				'event_id' => $event_info['id']
			])['rows'][0] ?? [])) {
				return $this->json['error'] = _l('invalid_url');
			}

			$this->json['event_name']		= $event_info['name'] ?? '';
			$this->json['faq']				= isset($landing_page_info['faq']) ? json_decode($landing_page_info['faq']) : [];
		}
	}
}
