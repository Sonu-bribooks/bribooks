<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Certificate {
	private function _getCertificate($data = []) {
		$stage 				= $data['stage'] ?? 'certificate';
		$info 				= $data['info'] ?? [];
		$country_info 		= $data['country_info'] ?? [];
		$event_type_info 	= $data['event_type_info'] ?? [];

		$data['ajax_certificate_message_template'] 	= base_url('admin/ajax_certificate_message_template/' . (int)$info['id']);
		$data['ajax_certificate_template'] 			= base_url('admin/ajax_certificate_template/' . (int)$info['id']);

		$this->load->view(sprintf('backend/admin/event/stage/%s', $stage), $data);
	}
}
