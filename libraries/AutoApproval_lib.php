<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class AutoApproval_lib {
	public function __construct() {
		$this->CI = &get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('book/Book_model');
		$this->load->model('Alert_model');
		$this->load->model('user/UserCover_model');

		$this->book_model 		= $this->CI->Book_model;
		$this->alert_model 		= $this->CI->Alert_model;
		$this->user_cover_model = $this->CI->UserCover_model;
	}

	public function approveBook($book_id = 0) {
		$book_info = $this->book_model->get($book_id);

		$text_is_visible = true;

		// if (!empty($book_info['user_cover_id'])) {
		// 	if (
		// 		($user_cover_info = $this->user_cover_model->get($book_info['user_cover_id'])) &&
		// 		!empty($user_cover_info['custom_cover_id'])
		// 	) {
		// 		$design = json_decode($user_cover_info['design'], true);
		// 		$text_is_visible = array_filter($design['objects'] ?? [], function ($item) {
		// 			return !empty($item['visible']);
		// 		});
		// 	}
		// }

		// if (!empty($book_info) && $book_info['status'] == 2 && !empty($text_is_visible)) {
		if (!empty($book_info) && $book_info['status'] == 2) {
			$this->book_model->edit($book_id, [
				'status' 			=> 1,
				'reviewer_id' 		=> 15779,
				'date_approved' 	=> date('Y-m-d H:i:s')
			]);
		}
	}
}
