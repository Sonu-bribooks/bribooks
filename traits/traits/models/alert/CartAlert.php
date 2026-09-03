<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CartAlert {
	public function abandonCart($cart_id = 0) {
		$code = 'abandonCartCron_' . (int)$this->session->userdata('user_id');

		if (!empty($info = $this->cron_model->getByCode($code))) {
			$this->cron_model->edit($info['id'], [
				'code'			=> $code,
				'action'		=> 'alert_model->abandonCartCron',
				'data'			=> [$cart_id],
				'alert_date'	=> date('Y-m-d H:i:s', strtotime(sprintf('+%d minutes', ENVIRONMENT === 'production' ? 30 : 2))),
			]);
		} else {
			$this->cron_model->add([
				'code'			=> $code,
				'action'		=> 'alert_model->abandonCartCron',
				'data'			=> [$cart_id],
				'alert_date'	=> date('Y-m-d H:i:s', strtotime(sprintf('+%d minutes', ENVIRONMENT === 'production' ? 30 : 2))),
			]);
		}
	}

	public function abandonCartCron($cart_id = 0) {
		if ($cart_info = $this->db->get_where('cart', [
				'id'			=> (int)$cart_id,
			])->row_array()
		) {
			$book_info 		= $this->book_model->get($cart_info['product_id']);
			$user_info 		= $this->user_model->get($cart_info['user_id']);

			if (empty($book_info) || empty($user_info)) {
				return;
			}

			$mobile = $user_info['mobile'];
			$email  = $user_info['email'];

			if ($cart_info['option'] == 'ebook') {
				$cart_url = USER_URL . 'cart';
			} else {
				$cart_url = USER_URL . 'cart/checkout';
			}

			if ($user_info['id'] == $book_info['user_id']) {
				CI_Events::trigger('abandon_cart_author', [
					'cart_id'		=> $cart_id,
					'author_name' 	=> $book_info['author_name'],
					'cart_url'		=> $cart_url
				]);
			} else {
				CI_Events::trigger('abandon_cart_buyer', [
					'cart_id'		=> $cart_id,
					'author_name' 	=> $book_info['author_name'],
					'book_name'		=> $book_info['name'],
					'cart_url'		=> $cart_url
				]);
			}
		}
	}
}
