<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CustomThemeAlert {
	public function customThemeAlert($id) {
		if (empty($id)) return;

		self::customThemeAlertCron($id);
	}

	public function customThemeAlertCron($id) {
		$this->load->model('book/CustomThemeLog_model', 'custom_theme_log_model');

		if (empty($custom_theme_log_info = $this->custom_theme_log_model->get($id)) || empty($student_info = $this->student_model->get($custom_theme_log_info['user_id']))) {
			return;
		}

		$author_name 			= trim($student_info['first_name'] . ' ' . $student_info['last_name']);
		$duration 				= rand(3, 9);
		$data['document_date'] 	= date('M d Y, H:i:s A', strtotime('-1 minutes', strtotime($custom_theme_log_info['date_modified'])));
		$data['document_title']	= 'Copy of the signed Terms & Conditions for usage of My Own Image Module on <a href="https://www.bribooks.com">BriBooks.com</a>';
		$data['document_id']	= $custom_theme_log_info['document_id'];
		$data['created_date']	= date('M d Y, H:i:s A', strtotime('-1 minutes', strtotime($custom_theme_log_info['date_modified'])));
		$data['signed_by'] 		= $author_name;
		$data['ip_address'] 	= $custom_theme_log_info['ip_address'];
		$data['signed_date'] 	= date('M d Y, H:i:s A', strtotime(sprintf('+%d seconds', $duration), strtotime($custom_theme_log_info['date_modified'])));
		$data['type']			= 'custom_theme';
		
		CI_Events::trigger('tnc_user_image', [
			'user_id'	=> $student_info['id'],
			'data' 		=> $data
		]);

		CI_Events::trigger('access_log', [
			'module'	=> sprintf('tnc_user_image_%d', (int)$student_info['id'])
		]);
	}
}
