<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait RoyaltyAlert {
	public function royaltyCreditCron($id = 0) {
		$this->load->model('user/AuthorEarning_model', 'author_earning_model');

		if (
			($info = $this->author_earning_model->get($id)) &&
			$info['status'] == 2
		) {
			$this->load->model('user/UserCredit_model', 'user_credit_model');
			$this->load->model('user/UserCreditHistory_model', 'user_credit_history_model');

			$this->author_earning_model->edit($info['id'], [
				'status' 			=> 1,
				'date_processed'	=> date('Y-m-d H:i:s'),
			]);

			self::_saveAuthorEarningExchangeRate($info);

			$author_currency_code = get_author_currency_code($info['author_id']);

			// $info['amount'] = convert_to_local_currency($info['amount'], $info['author_id'], $info['currency_code']);
			$info['amount'] = convert_to_cross_border_exchange_currency($info['id']);

			$credit_info = $this->user_credit_model->getByUserId($info['author_id']);

			if (!empty($credit_info)) {
				$this->user_credit_model->edit($credit_info['id'], [
					'credit'	=> (double)($credit_info['credit'] + $info['amount']),
				]);
			} else {
				$this->user_credit_model->add([
					'currency_code'	=> $author_currency_code,
					'user_id'		=> (int)$info['author_id'],
					'credit'		=> (double)$info['amount'],
				]);
			}

			$this->user_credit_history_model->add([
				'type'					=> 1,
				'currency_code'			=> $author_currency_code,
				'user_id'				=> (int)$info['author_id'],
				'credit'				=> (double)$info['amount'],
				'order_id'				=> (int)$info['order_id'],
				'note'					=> (int)$info['id'],
			]);
		}
	}

	public function authorRoyaltyCron($id = 0) {
		if ($info = $this->order_model->get($id)) {
			$this->load->library('Royalty_lib', 'royalty_lib');
			$this->load->model('user/AuthorEarning_model', 'author_earning_model');

			$user_info = $this->user_model->get($info['user_id']);

			foreach ($this->order_model->getProducts($id) ?? [] as $product) {
				$book_info 			= $this->book_model->get($product['product_id']);
				// $no_sold = $this->order_model->getTotalProductsByProductId($book_info['id']);
				$author_info 		= $this->user_model->get($book_info['user_id']);
				$author_site_info 	= $this->site_model->get($author_info['site_id']);

				$this->config->set_item('site_country_code', $author_site_info['country_code']);
				$this->config->set_item('site_currency_code', $author_site_info['currency_code']);

				if (empty($author_earning_info = $this->author_earning_model->get_all([
					'user_id' 	=> $info['user_id'],
					'order_id' 	=> $product['order_id'],
					'book_id' 	=> $product['product_id'],
				])['rows'][0] ?? [])) return;

				$author_royalty 		= ($author_earning_info['currency_code'] ?? '') . ' ' . ($author_earning_info['amount'] ?? 0);

				$data['title']			= sprintf(_li('%s %s of your Book has been sold on BriBooks'), $product['quantity'], _getCopyTextLabel($product['quantity']));
				$data['heading']		= sprintf(_li('%s %s of your Book has been sold on BriBooks'), $product['quantity'], _getCopyTextLabel($product['quantity']));

				$data['content']		= $this->load->view('common/mail/part/author_royalty', [
					'author'			=> $author_info,
					'product'			=> $product,
					'book'				=> $book_info,
					'order'				=> $info,
					'no_sold'			=> $product['quantity'] ?? 0,
					'author_royalty'	=> $author_royalty,
					'buyer'				=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				], true);

				$message 	= $this->load->view('common/mail/templates/2/general', $data, true);

				self::email(
					$author_info['email'],
					$data['title'],
					$message,
					[],
					[]
				);
			}

			$this->config->set_item('site_country_code', 'IN');
		}
	}

	private function _saveAuthorEarningExchangeRate($author_earning_info = []) {
		if (empty($author_earning_info)) return;

		$author_currency_code = get_author_currency_code($author_earning_info['author_id']);

		if ($author_currency_code != $author_earning_info['currency_code']) {
			// user_currency_code = AED
			// author_earning_currency_code = USD
			// base currency_code = INR

			$this->load->model('localisation/Currency_model', 'currency_model');

			$author_currency_info 	= $this->currency_model->getByCode($author_currency_code);
			$earning_currency_info 	= $this->currency_model->getByCode($author_earning_info['currency_code']);

			if (empty($author_currency_info) || empty($earning_currency_info)) return;

			$this->load->model('user/AuthorEarningExchangeRateHistory_model', 'author_earning_exchange_rate_history_model');

			// $rate  = round(  $author_currency_info['exchange_rate'] / $earning_currency_info['exchange_rate'], 2);
			$rate  = round($earning_currency_info['exchange_rate'] / $author_currency_info['exchange_rate'], 2);

			$this->author_earning_exchange_rate_history_model->add([
				'author_earning_id' => $author_earning_info['id'] ?? 0,
				'currency_code' 	=> $author_earning_info['currency_code'] ?? '',
				'rate'				=> $rate
			]);
		}
	}
}
