<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Cart {
	public function addToCart() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);
		$this->form_validation->set_rules('quantity', _l('quantity'), 'trim|numeric|less_than_equal_to[1000]');
		$this->form_validation->set_rules('option', _l('option'), 'trim|required|in_list[paperback,hard_cover,ebook,black_white,audio_book]');

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($book_info = $this->book_model->getByBookId($this->input->post('book_id')))) {
				return $this->json['error'] = _li('book_not_found');
			}

			if (!self::_validatePritingLimit($this->input->post('book_id'), $this->input->post('option'))) {
				return;
			}

			if ($this->session->userdata('user_id')) {
				// prevent eBook from reorder
				if (
					strtolower($this->input->post('option')) === 'ebook' &&
					self::_hasEBookOrder($this->input->post('book_id'))
				) {
					if ($book_info['user_id'] == $this->session->userdata('user_id')) {
						$this->json['error'] = sprintf(_li('This_EBook_is_already_available_in_your_"%s"_section.'), 'My Books');
					} else {
						$this->json['error'] = sprintf(_li('This_EBook_is_already_available_in_your_"%s"_section.'), 'My Purchased Books');
					}

					return;
				}

				if (
					strtolower($this->input->post('option')) === 'ebook' &&
					$this->book_model->getPrice($this->input->post('book_id'))['ebook_price'] == 0
				) {
					$this->json['error'] = _li('you_can\'t_purchase_this_ebook!');
					return;
				}

				// prevent audioBook from reorder
				if (
					strtolower($this->input->post('option')) === 'audio_book' &&
					self::_hasAudioBookOrder($this->input->post('book_id'))
				) {
					if ($book_info['user_id'] == $this->session->userdata('user_id')) {
						$this->json['error'] = sprintf(_li('This_AudioBook_is_already_available_in_your_"%s"_section.'), 'My Books');
					} else {
						$this->json['error'] = sprintf(_li('This_AudioBook_is_already_available_in_your_"%s"_section.'), 'My Purchased Books');
					}

					return;
				}

				if (
					strtolower($this->input->post('option')) === 'audio_book' &&
					$this->book_model->getPrice($this->input->post('book_id'))['audio_book_price'] == 0
				) {
					$this->json['error'] = _li('you_can\'t_purchase_this_audio_book!');
					return;
				}

				// if current ebook in the cart then remove
				self::_fixCartDuplicateBook();

				$cart_id = $this->cart_lib->add(
					$this->input->post('book_id'),
					$this->input->post('quantity')
						? $this->input->post('quantity')
						: 1,
					$this->input->post('option')
				);

				CI_Events::trigger('cart_created', [
					'cart_id'	=> $cart_id,
					'book_id'	=> (int)$this->input->post('book_id'),
					'quantity'	=> (int)$this->input->post('quantity') ?? 1,
					'option'	=> $this->input->post('option') ?? 'paperback',
				]);

				CI_Events::trigger('access_log', [
					'module'	=> vsprintf('cart_created_%s_%s_%s', [
						(int)$cart_id,
						(int)$this->input->post('book_id'),
						(int)$this->input->post('quantity') ?? 1
					])
				]);

				// clean saved session of the courier id and shipping address id if cart is modified
				self::_cleanShippingSession();

				self::_getCart();
			} else {
				$this->json['login'] = true;
			}
		}
	}

	public function updateCart() {
		$this->form_validation->set_rules('quantity', _l('quantity'), 'trim|numeric|less_than_equal_to[1000]');
		$this->form_validation->set_rules('option', _l('option'), 'trim|required|in_list[paperback,hard_cover,ebook,black_white,audio_book]');

		self::_runFormValidation();

		if (!$this->json) {
			if ($this->session->userdata('user_id')) {
				// prevent eBook from reorder
				if (!$cart_info = $this->cart_lib->get($this->input->post('cart_id'))) {
					return $this->json['error'] = _l('cart_not_found');
				}

				if (!self::_validatePritingLimit($cart_info['product_id'], $this->input->post('option'))) {
					return;
				}

				if (
					strtolower($this->input->post('option')) === 'ebook' &&
					self::_hasEBookOrder($cart_info['product_id'])
				) {
					$book_info = $this->book_model->get($cart_info['product_id']);

					if ($book_info['user_id'] == $this->session->userdata('user_id')) {
						$this->json['error'] = sprintf(_li('This_EBook_is_already_available_in_your_"%s"_section.'), 'My Books');
					} else {
						$this->json['error'] = sprintf(_li('This_EBook_is_already_available_in_your_"%s"_section.'), 'My Purchased Books');
					}

					return;
				}

				// prevent audioBook from reorder
				if (
					strtolower($this->input->post('option')) === 'audio_book' &&
					self::_hasAudioBookOrder($cart_info['product_id'])
				) {
					$book_info = $this->book_model->get($cart_info['product_id']);

					if ($book_info['user_id'] == $this->session->userdata('user_id')) {
						$this->json['error'] = sprintf(_li('This_AudioBook_is_already_available_in_your_"%s"_section.'), 'My Books');
					} else {
						$this->json['error'] = sprintf(_li('This_AudioBook_is_already_available_in_your_"%s"_section.'), 'My Purchased Books');
					}

					return;
				}

				$this->cart_lib->update(
					$this->input->post('cart_id'),
					$this->input->post('quantity')
						? $this->input->post('quantity')
						: 1,
					$this->input->post('option'),
				);

				CI_Events::trigger('cart_updated', [
					'cart_id'	=> (int)$this->input->post('cart_id'),
					'book_id'	=> (int)$cart_info['product_id'],
					'quantity'	=> (int)$this->input->post('quantity'),
					'option'	=> $this->input->post('option') ?? 'paperback',
				]);

				// clean saved session of the courier id and shipping address id if cart is modified
				self::_cleanShippingSession();

				self::_getCart();
			} else {
				$this->json['login'] = true;
			}
		}
	}

	public function useCredit() {
		if (!$this->json) {
			if ($this->session->userdata('user_id')) {
				$this->cart_lib->useCredit(
					$this->input->post('cart_id')
				);

				self::_getCart();
			} else {
				$this->json['login'] = true;
			}
		}
	}

	public function getCart() {
		if (!$this->session->userdata('user_id')) {
			$this->json['login'] = true;
		}

		if (!$this->json) {
			if ($this->input->post('route') == 'payment') {
				self::_initOrderSiteConfig();
			}

			self::_getCart();
		}
	}

	private function _fixCartDuplicateBook() {
		if ($this->input->post('quantity')) {
			$cart_items = $this->cart_lib->getItems();

			$has_book = array_filter($cart_items, function($item) {
				return $item['book']['id'] == $this->input->post('book_id');
			});

			if (!empty($has_book[0]['option'])) {
				log_kb(['_fixCartDuplicateBook::has_book' => $has_book[0]['option'] ?? '']);

				if (strtolower($this->input->post('option')) !== strtolower($has_book[0]['option'])) {
					$this->cart_lib->remove($this->input->post('book_id'));
				}

				log_kb(['Cart::eBook ignored:: ' => $has_book]);
			}
		}
	}

	public function getShippingCredit() {
		if (!$this->json) {
			if ($this->session->userdata('user_id')) {
				if (!self::_validateSubscription()) {
					return;
				}

				$address_info = $this->address_model->get($this->session->userdata('shipping_address_id'));

				if (empty($address_info)) {
					$this->json['shipping_credit'] = 0;
					return;
				}

				$shipping_credit_info = $this->shipping_credit_model->get_all([
					'user_id'		=> (int)$this->session->userdata('user_id'),
					'country_code'	=> _get_country_code_by_name($address_info['country']),
				])['rows'][0] ?? [];

				$this->json['shipping_credit'] = $shipping_credit_info['credit'] ?? 0;
			} else {
				$this->json['login'] = true;
			}
		}
	}

	public function useShippingCredit() {
		$this->form_validation->set_rules('action', _l('action'), 'trim|required|in_list[add,remove]');
		self::_runFormValidation();

		if (!$this->json) {
			if ($this->session->userdata('user_id') && self::_validateSubscription()) {
				$address_info = $this->address_model->get($this->session->userdata('shipping_address_id'));

				if (
					empty($shipping_credit_info = $this->shipping_credit_model->get_all([
						'user_id'		=> (int)$this->session->userdata('user_id'),
						'country_code'	=> _get_country_code_by_name($address_info['country']),
					])['rows'][0] ?? [])
				) {
					$this->json['error'] = sprintf(_l('you_don\'t_have_shipping_credit_for_%s'), $address_info['country']);
					return;
				}

				$this->cart_lib->useShippingCredit($this->input->post('action'));

				self::_getCart();
			} else {
				$this->json['login'] = true;
			}
		}
	}

	private function _getCart() {
		$this->json['cart']['items'] = $this->cart_lib->getItems();
		$this->json['cart']['total'] = $this->cart_lib->getTotal();
		$this->json['cart']['currency'] = $this->config->item('site_currency_symbol');
		$this->json['cart']['currency_code'] = $this->config->item('site_currency_code');
		$this->json['cart']['options'] = [
			[
				'key'	=> 'paperback',
				'name'	=> _l('paperback'),
				'price'	=> 0
			],
			// [
			// 	'key'	=> 'hard_cover',
			// 	'name'	=> _l('hard_cover'),
			// 	'price'	=> $this->config->item('site_hard_cover_price'),
			// ],
		];
		$this->json['cart']['price_per_page'] = $this->config->item('site_price_per_page');
		$this->json['cart']['free_page_limit'] = $this->config->item('site_free_page_limit');
		$this->json['cart']['shipping_address_id'] = $this->session->userdata('shipping_address_id');
		$this->json['cart']['shipping_address'] = $this->session->userdata('shipping_address_id')
			? $this->address_model->get($this->session->userdata('shipping_address_id'))
			: [];

		if (
			!empty($this->json['cart']['total']['coupon']['id']) &&
			!empty($coupon_info = $this->coupon_model->get($this->json['cart']['total']['coupon']['id'])) &&
			!self::_validateCoupon($coupon_info)
		) {
			$this->cart_lib->removeCoupon();

			$this->json['cart']['total'] = $this->cart_lib->getTotal();
			$this->json['cart']['items'] = $this->cart_lib->getItems();
		}
	}

	private function _hasEBookOrder($book_id = 0) {
		if (self::_validateSubscription()) return true;

		// prevent eBook from author buy
		if (
			($book_info = $this->book_model->get($book_id)) &&
			$book_info['user_id'] == $this->session->userdata('user_id')
		) {
			return true;
		}

		return in_array($this->session->userdata('user_id'), BB_UID) ? false : $this->order_model->getAuthorProducts([
			'product_id'	=> $book_id,
			'user_id'		=> $this->session->userdata('user_id'),
			'option_type'	=> 0,
			// 'option'		=> 'ebook',
		]);
	}

	private function _hasAudioBookOrder($book_id = 0) {
		// prevent audioBook from author buy
		if (
			($book_info = $this->book_model->get($book_id)) &&
			$book_info['user_id'] == $this->session->userdata('user_id')
		) {
			return true;
		}

		return $this->order_model->getAuthorProducts([
			'product_id'	=> $book_id,
			'user_id'		=> $this->session->userdata('user_id'),
			'option_type'	=> 3
		]);
	}

	private function _validatePritingLimit($book_id, $option = 'paperback') {
		if ($this->input->post('quantity') <= 0) return true;

		if ($option === 'ebook') return true;

		if ($option === 'audio_book') return true;

		$price_info = $this->book_model->getPrice($book_id);

		if ($price_info['total_pages'] > PRINTING_LIMIT[$option]) return true;

		if ($option === 'paperback') {
			$this->json['error'] = _li('This Book is currently unavailable for purchase due to editorial reasons.');
		} else {
			$this->json['error'] = _li('This book does not meet the minimum requirement of 36 pages for hardcover binding. Please order paperback copies instead!');
		}

		return false;
	}

	private function _cleanShippingSession() {
		$this->session->unset_userdata([
			'couriers',
			'shipping_address_id',
			'shipping_courier_id',
			'shipping_info',
			'shipping_credit',
			'shipping_discount',
		]);
	}
}
