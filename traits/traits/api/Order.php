<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Order {
	public function getEBookOrders() {
		if (!$this->json) {
			$books 				= [];
			$enable_audio_book 	= false;

			if ($this->input->post('type') == 'audio_book') {
				$option_type = [3];
			} else {
				$option_type = [0, 1, 2];
			}

			if (!empty($this->order_product_model->getPurchasedBooks( [
				'user_id'			=> $this->session->userdata('user_id'),
				'option_type'		=> [3],
			])['rows'] ?? [])) {
				$enable_audio_book = true;
			}

			$items = $this->order_product_model->getPurchasedBooks( [
				'user_id'			=> $this->session->userdata('user_id'),
				'option_type'		=> $option_type,
				'order_ne_status'	=> [0,91,92],
				'start'				=> $this->input->post('page') > 0
					? ($this->input->post('page') - 1) * 10
					: 0,
				'limit'				=> 100,
				'sort'				=> 'order.id',
				'order'				=> 'DESC',
			]) ?? [];

			foreach (($items['rows'] ?? []) as $item) {
				$book_info 	= $this->book_model->get($item['product_id']);
				$book_info	= self::_addOriginalRatingAndSold($book_info);
				$books[] 	= $book_info;
			}

			$this->json['enable_audio_book'] 	= $enable_audio_book;
			$this->json['books'] 				= $books;
			$this->json['total'] 				= $items['total'];

			if ($this->input->post('type') != 'audio_book') {
				foreach (FREE_BOOKS as $book_id) {
					array_unshift(
						$this->json['books'],
						$this->book_model->get($book_id)
					);
				}
			}
		}
	}

	public function getOrders() {
		if (!$this->json) {
			$this->json['orders'] = array_map(function($item) {
				$address = $this->address_model->get($item['address_id']);
				$books = $this->order_model->getProducts($item['id']);

				$has_printed_copies = array_filter($books, function($item) {
					$option = json_decode($item['option'], true);
					return !in_array(mb_strtolower($option['name']), ['ebook', 'audio book']);
					;
				});

				$shipping_info = json_decode($item['shipping_info'], true);

				$payment_info = $this->payment_model->get_all([
					'order_id'	=> $item['id']
				])['rows'][0] ?? [];

				return [
					'id'					=> $item['id'],
					'address'				=> $address,
					'books'					=> $books,
					'invoice'				=> !in_array($item['status'], [0,15,91,92])
						? USER_INVOICE_URL . 'invoice/download/' . $item['id'] . '/order'
						: '',
					'track'					=> !empty($item['status']) && $has_printed_copies
						? USER_URL . 'trackdelivery/' . $item['order_code']
						: null,
					'order_code'			=> $item['order_code'],
					'currency'				=> $item['currency_symbol'],
					'ppp_total'				=> $item['ppp_total'],
					'credit_discount'		=> $item['credit_discount'],
					'tax'					=> $item['tax'],
					'shipping_cost'			=> $item['shipping_cost'],
					'subtotal'				=> $item['subtotal'],
					'total'					=> $item['total'],
					'status'				=> $item['status'],
					'status_text'			=> $has_printed_copies ? _os($item['status']) : _li('completed'),
					'etd'					=> !in_array($item['status'], [4,15,91,92]) ? ($has_printed_copies ? sprintf(
						_li('~%s days after placing the order'),
						$item['date_added'] < '2023-02-03'
							? '10-14 business'
							: '21 business Days/30 calendar'
					) : _li('NA')) : '',
					'date_added'			=> $payment_info['date_added'] ?? $item['date_added'],
					'date_completed'		=> $item['date_completed'],
				];
			}, $this->order_model->get_all([
				'user_id'	=> $this->session->userdata('user_id'),
				'ne_status'	=> 0,
			])['rows'] ?? []);
		}
	}

	public function createOrder() {
		if (!$this->json) {
			if ($cart_items = $this->cart_lib->getItems()) {
				self::_initOrderSiteConfig();

				$has_printed_copies = array_filter($cart_items, function($item) {
					return $item['total']['type'] == 'printed' || $item['total']['type'] == 'black_white';
				});

				$has_virtual_books = array_filter($cart_items, function($item) {
					return $item['total']['type'] == 'ebook' || $item['total']['type'] == 'audio_book';
				});

				if (
					$has_printed_copies &&
					!$this->address_model->get($this->session->userdata('shipping_address_id'))
				) {
					$this->json['error'] = _l('shipping_address_not_found');
					return;
				}

				if ($has_printed_copies) {
					$results = array_filter($this->session->userdata('couriers')['data'] ?? [], function($item) {
						return $this->session->userdata('shipping_courier_id') == $item['id'];
					});

					if (count($results) === 0) {
						$this->json['error'] = _l('shipping_courier_not_found');
						return;
					}
				}

				$order_type = 1;

				if ($has_printed_copies && $has_virtual_books) {
					$order_type = 2;
				} elseif (!$has_printed_copies && $has_virtual_books) {
					$order_type = 3;
					$this->session->set_userdata([
						'shipping_address_id'	=> 0,
						'shipping_courier_id'	=> 0,
						'shipping_info'			=> [],
					]);
				}

				$cart_total = $this->cart_lib->getTotal();

				if ($order_type == 3) {
					$cart_total['weight'] = 0.00;
				}

				$user_info = $this->user_model->get($this->session->userdata('user_id'));

				$order_id = $this->order_model->add([
					'user_id'				=> (int)$this->session->userdata('user_id'),
					'address_id'			=> (int)$this->session->userdata('shipping_address_id'),
					'currency_id'			=> (int)$this->config->item('site_currency_id'),
					'currency_code'			=> $this->config->item('site_currency_code'),
					'currency_symbol'		=> $this->config->item('site_currency_symbol'),
					'coupon_id'				=> (int)($cart_total['coupon']['id'] ?? 0),
					'ppp_total'				=> (double)$cart_total['ppp_total'],
					'credit_discount'		=> (double)($cart_total['credit_discount'] ?? 0),
					'tax'					=> (double)$cart_total['tax'],
					'shipping_cost'			=> (double)$cart_total['shipping_cost'],
					'subtotal'				=> (double)$cart_total['subtotal'],
					'total'					=> (double)$cart_total['total'],
					'weight'				=> (double)$cart_total['weight'],
					'shipping_info'			=> json_encode($this->session->userdata('shipping_info')),
					'ip'					=> $this->input->ip_address(),
					'provider'				=> self::_getPaymentProvider(),
					'status'				=> 0,
					'order_type'			=> $order_type,
					'discount_desc'			=> json_encode($cart_total['discounts']),
				]);

				$ext_order_id = $cart_total['total'] > 0 ? $this->order_model->generateOrderId(
					$order_id,
					round($cart_total['total'], 2),
					$this->config->item('site_currency_code')
				) : 'free';

				$this->order_model->edit($order_id, [
					'ext_order_id'	=> $ext_order_id,
				]);

				// Add to cart session
				$this->cart_lib->addToCartSession($order_id);

				$stripe_key = (get_settings('payment_provider') == 'stripe_sg') ? STRIPE_KEY_SG : STRIPE_KEY;

				$this->json['order'] = [
					'provider'		=> $this->config->item('site_payment_gateway'),
					'key'			=> ($this->config->item('site_payment_gateway') == 'razorpay')
						? RAZORPAY_KEY
						: $stripe_key,
					'id'			=> $order_id,
					'amount'		=> round($cart_total['total'] * 100),
					'currency'		=> $this->config->item('site_currency_code'),
					'name'			=> preg_replace('/[[:^print:]]/', '', $cart_items[0]['book']['name'] ?? ''),
					'description'	=> preg_replace('/[[:^print:]]/', '', ($cart_items[0]['book']['name'] ?? '')) . (
						count($cart_items) > 1
							? ('+' . (count($cart_items) - 1))
							: ''
					),
					'order_id'		=> $ext_order_id,
					'user'			=> [
						'name'		=>  $user_info['first_name'] . ' ' . $user_info['last_name'],
						'email'		=>  $user_info['email'],
						'mobile'	=>  $user_info['mobile']
					],
					'address'		=> '',
				];

				CI_Events::trigger('access_log', [
					'module'	=> 'cart_order_created_' . (int)$order_id
				]);
			} else {
				$this->json['error'] = _li('cart_is_empty');
			}
		}
	}

	public function createPayment() {
		$this->form_validation->set_rules('order_id', _l('order_id'), [
			'trim',
			'required',
			'numeric',
			['order', [$this->validate_model, 'order']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$order_info = $this->order_model->get($this->input->post('order_id'));

			if (((int)$order_info['total']) === 0) {
				self::_initOrderSiteConfig();

				$cart_items = $this->cart_lib->getItems($order_info['user_id']);

				$has_author_copies = array_filter($cart_items, function($item) use($order_info) {
					return $item['book']['user_id'] == $order_info['user_id'];
				});

				// update order
				self::_createPaymentData();

				$this->json['success'] = _l('order_place_successfully');
				$this->json['redirect'] = $has_author_copies ? '/cart/successauthor' : '/cart/success';
			} else {
				self::_initOrderSiteConfig();

				$data = $this->input->post('payment');

				if ($this->order_model->verifyOrder([
					'order_id'		=> $order_info['ext_order_id'],
					'payment_id'	=> $data['data']['razorpay_payment_id'] ?? '',
					'signature'		=> $data['data']['razorpay_signature'] ?? '',
					'order_info'	=> array_merge($order_info, ['amount' => $order_info['total'] ?? 0]),
					'data'			=> $data['data'],
				])) {
					$cart_items = $this->cart_lib->getItems($order_info['user_id']);

					$has_author_copies = array_filter($cart_items, function($item) use($order_info) {
						return $item['book']['user_id'] == $order_info['user_id'];
					});

					self::_createPaymentData();

					$this->json['success'] = _l('order_place_successfully');
					$this->json['redirect'] = $has_author_copies ? '/cart/successauthor' : '/cart/success';
				} else {
					$this->json['error'] = _l('payment_not_verified');
				}
			}
		}
	}

	private function _createPaymentData() {
		$order_info = $this->order_model->get($this->input->post('order_id'));

		$data = $this->input->post('payment');
		$transaction_key = 'razorpay_payment_id';

		if ($order_info['provider'] == 'phonepe') {
			$transaction_key = 'transactionId';
		}

		if (strpos($order_info['provider'], 'stripe') !== false) {
			$transaction_key = 'id';
		}

		// fallback support for processed order by webhook
		if (!empty($order_info['status'])) return;

		$data['data']['provider'] = $order_info['provider'];

		// Update subscription order status
		$this->order_model->edit($order_info['id'], [
			'order_code'			=> 'BB-' . time() . '-' . $order_info['id'] . 'I' . $order_info['user_id'],
			'status'				=> 1,
			'ext_transaction_id'	=> $data['data'][$transaction_key] ?? 'free',
			'ext_raw_data'			=> json_encode($data['data'] ?? []),
		]);

		// Add order product
		$cart_items = $this->cart_lib->getSessionItems($order_info['id']);

		$used_credit = 0;

		foreach ($cart_items as $item) {
			$this->cart_lib->updateSessionItem($item['id'], [
				'status'	=> 1,
			]);

			$used_credit += $item['total']['used_credit'];

			$book_info = $this->book_model->get($item['book']['id']);

			$item['total']['option']['name'] = str_replace('_', ' ', $item['total']['option']['name']);

			$this->order_model->addProduct([
				'version'			=> (int)$book_info['version'],
				'order_id'			=> (int)$order_info['id'],
				'product_id'		=> (int)$item['book']['id'],
				'quantity'			=> (int)$item['quantity'],
				'price'				=> (double)$item['total']['price'],
				'credit'			=> (int)$item['total']['credit'],
				'used_credit'		=> (int)$item['total']['used_credit'],
				'credit_discount'	=> (double)($item['total']['credit_discount'] ?? 0),
				'ppp_total'			=> (double)$item['total']['ppp_total'],
				'subtotal'			=> (double)$item['total']['subtotal'],
				'total'				=> (double)$item['total']['total'],
				'weight'			=> (double)$item['total']['weight'],
				'option'			=> json_encode($item['total']['option']),
				'option_type'		=> get_option_type($item['total']['type']),
			]);
		}

		// update coupon used count
		if (!empty($order_info['coupon_id'])) {
			$this->coupon_model->updateUsedCount($order_info['coupon_id']);
		}

		// Create payment
		$this->payment_model->add([
			'order_id'				=> (int)$order_info['id'],
			'user_id'				=> (int)$order_info['user_id'],
			'currency_id'			=> (int)$order_info['currency_id'],
			'currency_code'			=> $order_info['currency_code'],
			'currency_symbol'		=> $order_info['currency_symbol'],
			'provider'				=> $order_info['provider'],
			'amount'				=> (double)$order_info['total'],
			'status'				=> 1,
		]);

		CI_Events::trigger('access_log', [
			'module'	=> 'cart_payment_created_' . (int)$order_info['id']
		]);

		if (in_array($order_info['order_type'], [1, 2])) {
			CI_Events::trigger('order_created', [
				'order_id'	=> $order_info['id']
			]);

			CI_Events::trigger('printer_assigned', [
				'order_id'	=> $order_info['id']
			]);
		}


		$this->cart_lib->empty($order_info['user_id']);

		// $this->student_model->updateHardCopy(
		// 	$order_info['user_id'],
		// 	$used_credit
		// );

		$this->session->unset_userdata([
			'couriers',
			'shipping_address_id',
			'shipping_courier_id',
			'shipping_info',
			'shipping_credit',
			'shipping_discount',
		]);

		$this->session->unset_tempdata('order_site_id');

		!empty($cart_items) && $this->alert_model->invoiceOrder($order_info['id']);
	}

	private function _initOrderSiteConfig() {
		if (!empty($this->session->tempdata('order_site_id'))) {
			$this->site_model->initConfig($this->session->tempdata('order_site_id'));
		}
	}

	private function _getPaymentProvider() {
		if ($this->config->item('site_payment_gateway') == 'stripe') {
			return get_settings('payment_provider') ?? 'stripe';
		} else {
			return $this->config->item('site_payment_gateway');
		}
	}

	public function createBaseCampOrder() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);

		self::_runFormValidation();

		if (!$this->json) {

			if (empty($this->session->userdata('user_id'))) {
				return $this->json['login'] = true;
			}

			// if (empty($book_info = $this->book_model->get($this->input->post('book_id'))) || ($book_info['user_id'] != $this->session->userdata('user_id'))) {
			// 	return $this->json['error'] = _li('Invalid Book');
			// }

			$this->cart_lib->empty();

			$cart_id = $this->cart_lib->add(
				$this->input->post('book_id'),
				1,
				'paperback'
			);

			CI_Events::trigger('access_log', [
				'module'	=> vsprintf('cart_created_%s_%s_%s', [
					(int)$cart_id,
					(int)$this->input->post('book_id'),
					1
				])
			]);

			// clean saved session of the courier id and shipping address id if cart is modified
			// self::_cleanShippingSession();
			$this->session->unset_userdata([
				'couriers',
				'shipping_address_id',
				'shipping_courier_id',
				'shipping_info',
				'shipping_credit',
				'shipping_discount',
			]);

			$courier_data = [
				'data' => [
					[
						'id' 					=> 9876543210,
						'city' 					=> 'GURUGRAM',
						'cod' 					=> 1,
						'courier_company_id' 	=> 55,
						'rate' 					=> 76,
						'is_custom_rate' 		=> 1,
						'is_hyperlocal' 		=> '',
						'metro' 				=> 1,
						'pod_available' 		=> 'Instant',
						'postcode' 				=> '122004',
						'state' 				=> 'GURUGRAM',
						'zone' 					=> 'z_b'
					]
				],
				'recommended' => 9876543210
			];

			$this->session->set_userdata('couriers', $courier_data);

			$this->session->set_userdata([
				'shipping_address_id'	=> ENVIRONMENT == 'production' ? 309322 : 2468,
				'shipping_courier_id'	=> 9876543210,
				'shipping_info'			=> [],
			]);

			$this->session->set_tempdata('order_site_id', $this->config->item('site_id'), 3600);

			$this->json['courier'] = [
				'courier_id' => 9876543210,
				'address_id' => ENVIRONMENT == 'production' ? 309322 : 2468
			];

			self::_getCart();
		}
	}
}
