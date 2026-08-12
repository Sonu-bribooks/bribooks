<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class Discount_lib {
	public function __construct() {
		$this->CI 		=& get_instance();
		$this->db 		= $this->CI->db;
		$this->session 	= $this->CI->session;
		$this->load 	= $this->CI->load;
		$this->config 	= $this->CI->config;

		$this->load->model('book/Book_model');
		$this->load->model('order/Order_model');

		$this->book_model 		= $this->CI->Book_model;
		$this->order_model 		= $this->CI->Order_model;
	}

	public function getAuthorDiscountMessage($book_id = 0, $book_price = [], $quantity = 0, $option_price = 0, $option = 'printed') {
		$book_info = $this->book_model->get($book_id);

		$prices = PRICE_MATRIX[$this->config->item('site_country_code')] ?? PRICE_MATRIX[
			strtolower($this->config->item('site_country_code')) === 'in'
				? 'IN'
				: 'GE'
		];

		if ($book_info['date_published'] < NEW_PRICE_DATE) {
			$prices = PRICE_MATRIX_OLD[$this->config->item('site_country_code')] ?? PRICE_MATRIX_OLD[
				strtolower($this->config->item('site_country_code')) === 'in'
					? 'IN'
					: 'GE'
			];
		}

		$prices = $prices[$option] ?? $prices['printed'];

		$base_price = 0;
		$base_price_index = 0;
		$next_base_price_index = 0;

		log_kb(['getAuthorDiscountMessage::base_prices::' => $prices]);

		$ordered_author_copies = strtolower($this->config->item('site_country_code')) === 'in'
			? $this->order_model->getAuthorProducts([
				'product_id'	=> $book_id,
				// 'user_id'		=> $this->session->userdata('user_id'),
			]) : 0;

		log_kb(['ordered_author_copies'	=> $ordered_author_copies]);

		foreach ($prices as $key => $price) {
			if ($base_price) {
				$next_base_price_index = $key;
				break;
			}
			if (($quantity + $ordered_author_copies) <= $key) {
				$base_price = $price;
				$base_price_index = $key;
			}
		}

		log_kb(['getAuthorDiscountMessage::base_price::' => [
			'base_price'			=> $base_price,
			'base_price_index'		=> $base_price_index,
			'next_base_price_index'	=> $next_base_price_index,
		]]);

		if ($next_base_price_index) {
			if ($this->config->item('site_country_code') === 'KW') {
				if (($base_price_index - $quantity - $ordered_author_copies + 1) > 1) {
					$dis_message = 'Get a %s%% discount on the purchase of an additional %s copies!';
				} else {
					$dis_message = 'Get a %s%% discount on the purchase of an additional %s copy!';
				}

				return vsprintf(_li($dis_message), [
					100 - round(($prices[$next_base_price_index] / $this->config->item('site_base_price')) * 100),
					$base_price_index - $quantity - $ordered_author_copies + 1,
				]);
			}

			if (in_array($this->config->item('site_country_code'), ['MY', 'SG', 'AE', 'US'])) {
				if ($quantity % 2 === 0) {
					return vsprintf(_li('Congratulations! Your book price has been reduced to %s%s'), [
						$this->config->item('site_currency_symbol'),
						$prices[$next_base_price_index - 1] + $option_price + ($book_price[$option . '_ppp_total'] ?? $book_price['ppp_total'])
					]);
				}

				return vsprintf(_li('Purchase an additional %s %s to lower the price per copy to %s%s'), [
					$base_price_index - $quantity - $ordered_author_copies + 1,
					_getCopyText($base_price_index - $quantity - $ordered_author_copies + 1),
					$this->config->item('site_currency_symbol'),
					$prices[$next_base_price_index] + $option_price + ($book_price[$option . '_ppp_total'] ?? $book_price['ppp_total'])
				]);
			}

			return vsprintf(_li('Purchase an additional %s %s to lower the price per copy to %s%s'), [
				$base_price_index - $quantity - $ordered_author_copies + 1,
				_getCopyText($base_price_index - $quantity - $ordered_author_copies + 1),
				$this->config->item('site_currency_symbol'),
				$prices[$next_base_price_index] + $option_price + ($book_price[$option . '_ppp_total'] ?? $book_price['ppp_total'])
			]);
		}

		if ($base_price) {
			if ($this->config->item('site_country_code') === 'KW') {
				return vsprintf(_li('Congratulations! You\'ve unlocked a %s%% discount on your purchase!'), [
					100 - round(($base_price / $this->config->item('site_base_price')) * 100)
				]);
			}

			return vsprintf(_li('Congratulations! Your book price is reduced to %s%s.'), [
				$this->config->item('site_currency_symbol'),
				$base_price + $option_price + ($book_price[$option . '_ppp_total'] ?? $book_price['ppp_total'])
			]);
		}
	}

	public function applyAuthorDiscount($book_id = 0, $book_price = [], $quantity = 0, $option = 'printed') {
		if (!in_array(strtolower($option), ['printed', 'paperback'])) {
			$price_key = $option . '_price';
			$total_key = $option . '_total';
		} else {
			$price_key = 'price';
			$total_key = 'total';
		}

		if (empty($book_price[$price_key]) || empty($book_price[$total_key])) return $book_price;

		$book_info = $this->book_model->get($book_id);

		log_kb(['PRICE_MATRIX' => $this->config->item('site_country_code')]);

		$prices = PRICE_MATRIX[$this->config->item('site_country_code')] ?? PRICE_MATRIX[
			strtolower($this->config->item('site_country_code')) === 'in'
				? 'IN'
				: 'GE'
		];

		if ($book_info['date_published'] < NEW_PRICE_DATE) {
			$prices = PRICE_MATRIX_OLD[$this->config->item('site_country_code')] ?? PRICE_MATRIX_OLD[
				strtolower($this->config->item('site_country_code')) === 'in'
					? 'IN'
					: 'GE'
			];
		}

		$prices = $prices[$option] ?? $prices['printed'];

		$base_price = 0;

		log_kb(['applyAuthorDiscount::base_prices::' => $prices]);

		$ordered_author_copies = strtolower($this->config->item('site_country_code')) === 'in'
			? $this->order_model->getAuthorProducts([
				'product_id'	=> $book_id,
				// 'user_id'		=> $this->session->userdata('user_id'),
			]) : 0;

		log_kb(['ordered_author_copies'	=> $ordered_author_copies]);

		foreach ($prices as $key => $price) {
			if (($quantity + $ordered_author_copies) <= $key) {
				$base_price = $price;
				break;
			}
		}

		log_kb(['applyAuthorDiscount::base_price::' => $base_price]);

		if ($base_price) {
			if ($option !== 'printed') {
				return array_merge($book_price, [
					$option . '_price' 	=> round($base_price, 2),
					$option . '_total' 	=> round($base_price + ($book_price[$option . '_ppp_total'] ?? $book_price['ppp_total']), 2),
				]);
			}

			return array_merge($book_price, [
				'price' 		=> round($base_price, 2),
				'total' 		=> round($base_price + $book_price['ppp_total'], 2),
			]);
		}

		return $book_price;
	}

	public function applySchoolDiscount($book_id = 0, $total = 0, $quantity = 0, $option = 'printed') {
		if ($this->session->userdata('user_role_id') != 9) return;
		if (strtolower($this->config->item('site_country_code')) != 'in') return;
		if (strtolower($option) != 'paperback' && strtolower($option) != 'black_white') return;

		// $book_info 		= $this->book_model->get($book_id);
		// $author_info 	= $this->user_model->get($book_info['user_id']);
		//
		// if ($author_info['site_id'] !== $this->session->userdata('user_site_id')) return;

		return $total * .05;
	}

	public function applyAdditionalDiscount($book_id = 0, $total = 0, $quantity = 0, $option = 'printed') {
		if (strtolower($this->config->item('site_country_code')) == 'in') return;
		if (strtolower($option) != 'paperback' && strtolower($option) != 'black_white') return;

		$book_info 		= $this->book_model->get($book_id);

		if ($book_info['user_id'] !== $this->session->userdata('user_id')) return;

		return $total * .05;
	}
}
