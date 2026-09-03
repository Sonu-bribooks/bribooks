<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CustomCoverAlert {
	public function customCoverAlert($id = 0) {
		if (empty($id)) return;

		self::customCoverAlertCron($id);
	}

	public function customCoverAlertCron($id) {
		$this->load->model('book/CustomCoverLog_model', 'custom_cover_log_model');

		if (
			empty($info = $this->custom_cover_log_model->get($id)) ||
			empty($user_info = $this->user_model->get($info['user_id']))
		) {
			return;
		}

		$author_name 			= trim($user_info['first_name'] . ' ' . $user_info['last_name']);
		$duration 				= rand(3, 9);
		$data['document_date'] 	= date('M d Y, H:i:s A', strtotime('-1 minutes', strtotime($info['date_modified'])));
		$data['document_title']	= 'Copy of the signed Terms & Conditions for usage of Custom Cover on <a href="https://www.bribooks.com">BriBooks.com</a>';
		$data['document_id']	= $info['document_id'];
		$data['created_date']	= date('M d Y, H:i:s A', strtotime('-1 minutes', strtotime($custom_theme_log_info['date_modified'])));
		$data['signed_by'] 		= $author_name;
		$data['ip_address'] 	= $info['ip_address'];
		$data['signed_date'] 	= date('M d Y, H:i:s A', strtotime(sprintf('+%d seconds', $duration), strtotime($info['date_modified'])));
		$data['type']			= 'custom_cover';
		
		CI_Events::trigger('tnc_user_image', [
			'user_id'	=> $user_info['id'],
			'data' 		=> $data
		]);

		CI_Events::trigger('access_log', [
			'module'	=> sprintf('tnc_user_image_%d', (int)$user_info['id'])
		]);
	}
}
