<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CouponAlert {
	public function sendCouponCron($coupon_id = 0) {
		// $info = $this->coupon_model->get($coupon_id)[0];
		// 	$book_info = $this->book_model->get($info['item_id']) ;
		// 	$user_info = $this->student_model->get($info['user_id']);

		if (
			($info = $this->coupon_model->get($coupon_id)) &&
			$book_info = $this->book_model->get($info['item_id']) &&
			$user_info = $this->student_model->get($info['user_id'])
		) {
			$book_info = $this->book_model->get($info['item_id']);
			$data['title']			= vsprintf(_li('BriBooks: Order your free printed author copy of '.$book_info["name"]), [
				get_settings('system_name')
			]);
			$data['heading']		= '';

			$data['content']		= self::formatEmailMessage('email_send_coupon', [
				'author_name'		=> $user_info['first_name'] . ' ' . $info['last_name'],
				'name'				=> $info['code'],
				'book_name'			=> $book_info['name'],
 				'url'				=> vsprintf(USER_URL . 'bookstore/%s', [
					$book_info['slug'],
				]),
				'password'			=> explode(' ', $info['date_end'])[0]
			]);
			$message 				= $this->load->view('common/mail/templates/2/general', $data, true);
			self::email(
				$user_info['email'],
				$data['title'],
				$message,
				[],
				[]
			);
		}
	}
}
