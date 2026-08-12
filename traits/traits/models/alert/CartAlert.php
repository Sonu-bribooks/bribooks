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
				$template_id 	= '01kspxprf5xvsywwr2663ey59w';
				$parameters 	= [
					$book_info['author_name'],
					'purchase of your discounted Author Copy but didn\'t finish the journey',
					'order',
					$cart_url,
					'purchase will help you grow as an entrepreneur author, win amazing prizes and earn author stipends',
				];
			} else {
				$template_id 	= '01kt60skgqjdpp1a0zd4xdyr0w';
				$parameters 	= [
					ucwords($user_info['first_name'] . ' ' . $user_info['last_name']),
					'started the purchase',
					$book_info['author_name'],
					$book_info['name'],
					'journey',
					'order',
					$cart_url,
					'purchase will help the young author grow as an entrepreneur author, win amazing prizes and earn author stipends'
				];
			}

			// self::_sendWhatsappText(
			// 	$mobile,
			// 	[
			// 		'template'		=> $template_id,
			// 		'parameters'	=> $parameters,
			// 	],
			// );

			self::sendOnextelWhatsappMessage(
				$mobile,
				[
					'template_id'	=> $template_id,
					'parameters'	=> $parameters
				]
			);
		}
	}
}
