<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Webpush {
	public function ajax_webpush_save() {
		if (!empty($info = $this->web_push_subscriber_model->get_all([
			'user_id' 		=> (int)$this->session->userdata('user_id'),
			'item_type'		=> 'bb_cms',
		])['rows'][0] ?? '')) {
			$this->web_push_subscriber_model->edit($info['id'], [
				'user_id'		=> (int)$this->session->userdata('user_id') ?? 0,
				'item_type'		=> 'bb_cms',
				'token'			=> $this->input->post('token'),
				'ip'			=> $this->input->ip_address(),
			]);
		} else {
			$this->web_push_subscriber_model->add([
				'user_id'		=> (int)$this->session->userdata('user_id') ?? 0,
				'item_type'		=> 'bb_cms',
				'token'			=> $this->input->post('token'),
				'ip'			=> $this->input->ip_address(),
			]);
		}

		output_json(['success' => true]);
	}
}
