<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BookAppreciation
{
	public function bookAppreciation()
	{
		$this->form_validation->set_rules('type', _l('type'), 'trim|numeric|in_list[1,2,3]');
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if ($res = $this->book_appreciation_model->get_all([
				'book_id' 	=> $this->input->post('book_id'),
				'user_id' 	=> $this->session->userdata('user_id'),
				'ip' 		=> $this->input->ip_address(),
			])['rows'][0] ?? []) {
				$this->book_appreciation_model->enableDisable($res['id'], $this->input->post());
				$this->json['success'] = _li('updated');
			} else {
				$this->book_appreciation_model->add([
					'user_id' 	=> (int)$this->session->userdata('user_id'),
					'book_id' 	=> (int)$this->input->post('book_id'),
					'type' 		=> (int)$this->input->post('type'),
					'ip' 		=> $this->input->ip_address(),
				]);
				$this->json['success'] = _li('thanks_for_react');
			}
		}
	}
}
