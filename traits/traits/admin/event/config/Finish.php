<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Finish {
	private function _getFinish($data = []) {
		$stage 				= $data['stage'] ?? 'finish';
		$event_info 		= $data['info'] ?? [];

		$data['action'] 	= base_url('admin/event');

		$this->load->view(sprintf('backend/admin/event/stage/%s', $stage), $data);
	}
}
