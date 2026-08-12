<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class Royalty_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('book/Book_model');
		$this->load->model('user/Student_model');
		$this->load->model('order/Order_model');
		$this->load->model('user/AuthorEarning_model');
		$this->load->model('common/Site_model');
		$this->load->model('user/UserCredit_model');
		$this->load->model('user/UserCreditHistory_model');

		$this->book_model = $this->CI->Book_model;
		$this->student_model = $this->CI->Student_model;
		$this->order_model = $this->CI->Order_model;
		$this->author_earning_model = $this->CI->AuthorEarning_model;
		$this->site_model = $this->CI->Site_model;
		$this->user_credit_model = $this->CI->UserCredit_model;
		$this->user_credit_history_model = $this->CI->UserCreditHistory_model;
	}

	public function applyRoyalty($order_id = 0) {
		$order_info = $this->order_model->get($order_id);

		$ordered_books = $this->order_model->getProducts($order_id);

		foreach ($ordered_books as $ordered_book) {
			// $option = json_decode($ordered_book['option'], true);
			// if (mb_strtolower($option['name']) == 'ebook') continue;

			if (
				($royalty = self::_getRoyaltyAmount($ordered_book)) &&
				!empty($royalty['royalty'])
			) {
				self::_applyRoyalty($royalty, $ordered_book, $order_info);
			}
		}
	}

	private function _applyRoyalty($royalty = [], $ordered_book = [], $order_info = []) {
		log_kb(['_applyRoyalty'=> [
			'book_id'	=> $ordered_book['product_id'],
			'quantity'	=> $ordered_book['quantity'],
			'royalty'	=> $royalty,
		]]);

		if (($royalty['royalty'] ?? 0) < 0) return;

		$book_info = $this->book_model->get($ordered_book['product_id']);

		if ($order_info['user_id'] == $book_info['user_id']) {
			log_kb([
				'Skipping Royalty: Author Copy:: ' => $book_info['name'] ?? '',
			]);
			return;
		}

		if ($this->author_earning_model->get_all([
			'order_id'		=> $order_info['id'],
			'book_id'		=> $ordered_book['product_id'],
			'user_id'		=> $order_info['user_id'],
			'author_id'		=> $book_info['user_id'],
		])['total'] != 0) {
			log_kb([
				'Skipping Royalty: Duplicate Entry:: ' => $order_info['id'] ?? '',
			]);
			return;
		}

		$this->author_earning_model->add([
			'site_id'		=> (int)$order_info['site_id'],
			'currency_id'	=> (int)$order_info['currency_id'],
			'currency_code'	=> $order_info['currency_code'],
			'order_id'		=> $order_info['id'],
			'book_id'		=> $ordered_book['product_id'],
			'user_id'		=> $order_info['user_id'],
			'author_id'		=> $book_info['user_id'],
			'amount'		=> $royalty['royalty'] ?? 0,
			'note'			=> json_encode($royalty['royalty_matrix'] ?? []),
			'quantity'		=> $ordered_book['quantity'],
		]);
	}

	private function _getRoyaltyAmount($ordered_book = []) {
		$book_id = $ordered_book['product_id'];
		$current_orders = $ordered_book['quantity'];

		$book_info = $this->book_model->get($book_id);
		$author_info = $this->student_model->get($book_info['user_id'] ?? 0);
		$author_site_info = $this->site_model->get($author_info['site_id'] ?? 0);

		$royalties = ROYALITY_MATRIX[$author_site_info['currency_code']] ?? ROYALITY_MATRIX[$this->config->item('default_site_currency_code')];

		log_kb(['applyRoyalty::royalties::' => $royalties]);

		$royalty = 0;
		$royalty_matrix = [];
		$last_index = 0;

		$total_orders = $this->order_model->getAuthorProducts([
			'product_id'	=> $book_id,
		]);

		$previous_orders = $total_orders - $current_orders;

		log_kb(['_getRoyaltyAmount:: ' => [
			'total_orders'		=> $total_orders,
			'current_orders'	=> $current_orders,
			'previous_orders'	=> $previous_orders,
			'book_price'		=> $ordered_book['total'],
		]]);

		$order_total = $ordered_book['total'] / $current_orders;

		for ($i = $previous_orders + 1; $i <= $total_orders; $i++) {
			foreach ($royalties as $key => $value) {
				if ($i <= $key) {
					$royalty += ($order_total * $value / 100);

					if (!isset($royalty_matrix[$key]['amount'])) {
						$royalty_matrix[$key]['amount'] = 0;
						$royalty_matrix[$key]['quantity'] = 0;
					}

					$royalty_matrix[$key]['amount'] += ($order_total * $value / 100);
					$royalty_matrix[$key]['quantity'] += 1;

					break;
				}
			}
		}

		log_kb([
			'total_orders'			=> $total_orders,
			'applied_royalty'		=> $royalty,
			'royalty_matrix'		=> $royalty_matrix,
		]);

		return [
			'royalty'			=> $royalty,
			'royalty_matrix'	=> $royalty_matrix,
		];
	}

	public function getBookCurrencyRoyality($data = []) {
		$result 		= [];
		$filter_data 	= [];

		if (empty($data)) return $result;

		if (!empty($data['book_id'])) {
			$filter_data['book_id'] = $data['book_id'];
		}

		if (!empty($data['user_id'])) {
			$filter_data['user_id'] = $data['user_id'];
		}

		if (!empty($data['author_id'])) {
			$filter_data['author_id'] = $data['author_id'];
		}

		if (isset($data['status'])) {
			$filter_data['status'] = $data['status'];
		}

		if (!empty($data['ne_status'])) {
			$filter_data['ne_status'] = $data['ne_status'];
		}

		if (!empty($data['status_in'])) {
			$filter_data['status_in'] = $data['status_in'];
		}

		$earnings = $this->author_earning_model->get_all($filter_data)['rows'] ?? [];

		foreach ($earnings as $earning) {
			$currency 	= $earning['currency_code'];
			$amount 	= (float)$earning['amount'];

			if (!isset($result[$currency])) {
				$result[$currency] = [
					'currency_code' => $currency,
					'symbol' 		=> get_currency_symbol($currency),
					'amount' 		=> 0,
				];
			}

			$result[$currency]['amount'] += $amount;
		}

		return !empty($result) ? array_values($result) : $result;
	}

	public function getBookTotalRoyality($book_id = 0) {
		$total = 0;

		$results = $this->author_earning_model->get_all([
			'book_id'	=> (int)$book_id,
		])['rows'] ?? [];

		foreach ($results as $key => $item) {
			$item['amount'] = self::getRoyaltyInAuthorCurrency($item);

			$total  += $item['amount'];
		}

		return $total;
	}

	public function getRoyaltyInAuthorCurrency($item = []) {
		$item['amount'] = convert_to_local_currency(
			$item['amount'],
			$item['author_id'],
			$item['currency_code']
		);

		return $item['amount'];
	}

	public function generateCredit($order_id = 0) {
		$order_info = $this->order_model->get($order_id);

		if (empty($order_info) || $order_info['status'] != 4) return;

		if (!empty($order_info['parent_order_id'])) {
			$total_orders = $this->order_model->searchProductName([
				'parent_order_id'	=> $order_info['parent_order_id']
			])['total'] ?? 0;

			$total_delivered_orders = $this->order_model->searchProductName([
				'parent_order_id'	=> $order_info['parent_order_id'],
				'status'			=> 4
			])['total'] ?? 0;

			if ($total_orders == $total_delivered_orders) {
				$order_id = $order_info['parent_order_id'];
			}
		}

		$author_earnings = $this->author_earning_model->get_all([
			'order_id' 	=> (int)$order_id,
			'status' 	=> 0,
		])['rows'] ?? [];

		if (empty($author_earnings)) return;

		$this->CI->load->model('common/Cron_model', 'cron_model');

		// loop here
		foreach ($author_earnings as $author_earning_info) {
			// mark as processing
			$this->author_earning_model->edit($author_earning_info['id'], [
				'status' 			=> 2,
				'date_processing'	=> date('Y-m-d H:i:s'),
			]);

			$alert_date_time = mb_strtolower($author_earning_info['currency_code']) == 'inr'
				? 0
				: 90 * 24 * 60
			;

			$this->CI->cron_model->add([
				'code'			=> 'royaltyCreditCron_' . $author_earning_info['id'],
				'action'		=> 'alert_model->royaltyCreditCron',
				'data'			=> [$author_earning_info['id']],
				'site_id'		=> (int)$this->config->item('site_id'),
				'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
					? "+{$alert_date_time} minutes"
					: '+2 minutes'
				)),
			]);
		}
	}

	public function refundUserCredit($order_id = 0, $comment = '') {
		$order_info = $this->order_model->get($order_id);

		if (empty($order_info))
			return;

		if (!in_array($order_info['status'], [4, 15]))
			return;

		$ordered_books = $this->order_model->getProducts($order_id);

		if (empty($ordered_books))
			return;

		foreach ($ordered_books as $ordered_book) {
			$book_info = $this->book_model->get($ordered_book['product_id']);

			if (!empty($author_earning_info = $this->author_earning_model->get_all([
				'order_id'		=> $order_info['id'],
				'book_id'		=> $book_info['id'],
				'user_id'		=> $order_info['user_id'],
				'author_id'		=> $book_info['user_id'],
			])['rows'][0] ?? [])) {
				$credit_info = $this->user_credit_model->getByUserId($book_info['user_id']);

				$author_earning_info['amount'] = convert_to_local_currency(
					$author_earning_info['amount'],
					$author_earning_info['author_id'],
					$author_earning_info['currency_code']
				);

				$this->user_credit_model->edit($credit_info['id'], [
					'credit'	=> (double)($credit_info['credit'] - $author_earning_info['amount']),
				]);

				$this->user_credit_history_model->add([
					'type'					=> 2,
					'user_id'				=> $book_info['user_id'],
					'order_id'				=> $order_info['id'],
					'credit'				=> $author_earning_info['amount'],
					'currency_code'			=> get_author_currency_code($book_info['user_id']),
					'note'					=> $comment,
				]);
			}
		}
	}
}
