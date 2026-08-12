<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class Cart_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('book/Book_model');
		$this->load->model('book/Bookstore_model');
		$this->load->model('user/User_model');
		$this->load->model('order/Coupon_model');
		$this->load->model('order/Order_model');
		$this->load->model('book/BookStock_model');
		$this->load->model('address/Address_model');

		$this->load->model('event/EventBook_model');

		$this->load->model('shipping/ShippingCredit_model');
		$this->load->model('shipping/ShippingCreditHistory_model');

		$this->load->library('Discount_lib');

		$this->book_model 		= $this->CI->Book_model;
		$this->bookstore_model 	= $this->CI->Bookstore_model;
		$this->coupon_model 	= $this->CI->Coupon_model;
		$this->user_model 		= $this->CI->User_model;
		$this->order_model 		= $this->CI->Order_model;
		$this->book_stock_model = $this->CI->BookStock_model;
		$this->address_model 	= $this->CI->Address_model;

		$this->event_book_model = $this->CI->EventBook_model;

		$this->discount_lib 	= $this->CI->discount_lib;

		$this->shipping_credit_model 			= $this->CI->ShippingCredit_model;
		$this->shipping_credit_history_model 	= $this->CI->ShippingCreditHistory_model;

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$this->cache = $this->CI->cache;

		// $this->db->where('date_added < DATE_SUB(NOW(), INTERVAL 7 DAY)');
		// $this->db->delete('cart', [
		// 	'user_id'		=> (int)$this->session->userdata('user_id'),
		// ]);
	}

	private function _resetUserCredit() {
		$this->user_info = $this->session->userdata('user_id')
			? $this->user_model->get($this->session->userdata('user_id'))
			: []
		;

		$used_credit = $this->db->select_sum('used_credit')
			->where('user_id', (int)$this->session->userdata('user_id'))
			->get('cart')
			->row()->used_credit;
		$this->available_credit = ($this->user_info['hard_copy'] ?? 0) - $used_credit;
	}

	public function get($cart_id = 0) {
		return $this->db->get_where('cart', [
			'user_id'		=> (int)$this->session->userdata('user_id'),
			'id'			=> (int)$cart_id,
		])->row_array();
	}

	public function getItems($user_id = 0) {
		self::_resetUserCredit();

		$items = $this->db->get_where('cart', [
			'user_id'		=> (int)($user_id ? $user_id : $this->session->userdata('user_id')),
		])->result_array();

		$cart_items = [];

		foreach ($items as $item) {
			$book_info 			= $this->bookstore_model->getByBookId($item['product_id']);

			if (!empty($book_info['book_id'])) {
				$book_info['id'] 		= $book_info['book_id'];
			} else {
				$book_info 				= $this->book_model->get($item['product_id']);
				$book_info['book_id'] 	= $book_info['id'];
			}

			$total 				= self::getItemTotal($item, $book_info);

			if (empty($total['total'])) {
				$this->db->delete('cart', [
					'id'			=> (int)$item['id']
				]);

				continue;
			}

			$cart_items[] = [
				'id' 			=> $item['id'],
				'book' 			=> format_book_info($book_info),
				'quantity' 		=> $item['quantity'],
				'used_credit' 	=> $item['used_credit'],
				'total'			=> $total,
				'option' 		=> $item['option'],
				'book_types' 	=> self::_getBookTypes($item, $book_info),
			];
		}

		return $cart_items;
	}

	private function _getBookTypes($item = [], $book_info = []) {
		$book_price = $this->book_model->getPrice($item['product_id']);

		if ($book_price['ebook_price'] > 0 || $book_price['black_white_price'] > 0 || $book_price['audio_book_price'] > 0) {
			$return = [
				[
					'key'				=> 'printed',
					'option'			=> 'paperback',
					'name'				=> _l('printed'),
					'price'				=> $book_price['total'],
					'price_per_page'	=> $this->config->item('site_price_per_page'),
					'free_page_limit'	=> $this->config->item('site_free_page_limit'),
				]
			];

			if ($book_price['black_white_price'] > 0) {
				$return[] = [
					'key'				=> 'black_white',
					'option'			=> 'black_white',
					'name'				=> _l('black_&_white'),
					'price'				=> $book_price['black_white_total'],
					'price_per_page'	=> $this->config->item('site_black_white_price_per_page'),
					'free_page_limit'	=> BLACK_WHITE_FREE_LIMIT,
				];
			}

			if (
				$book_info['user_id'] != $this->session->userdata('user_id') &&
				$book_price['ebook_price'] > 0 &&
				!self::_validateSubscription()
			) {
				$return[] = [
					'key'				=> 'ebook',
					'option'			=> 'ebook',
					'name'				=> _li('e-Book'),
					'price'				=> $book_price['ebook_price'],
					'price_per_page'	=> 0,
					'free_page_limit'	=> 0,
				];
			}

			if (
				$book_info['user_id'] != $this->session->userdata('user_id') &&
				$book_price['audio_book_price'] > 0
			) {
				$return[] = [
					'key'				=> 'audio_book',
					'option'			=> 'audio_book',
					'name'				=> _li('Audiobook'),
					'price'				=> $book_price['audio_book_price'],
					'price_per_page'	=> 0,
					'free_page_limit'	=> 0,
				];
			}

			return $return;
		}

		return [
			[
				'key'				=> 'printed',
				'option'			=> 'paperback',
				'name'				=> _l('printed'),
				'price'				=> $book_price['total'] ?? 0,
				'price_per_page'	=> $this->config->item('site_price_per_page'),
				'free_page_limit'	=> $this->config->item('site_free_page_limit'),
			]
		];
	}

	private function _validateSubscription() {
		$this->load->model('subscription/UserSubscription_model');
		$this->user_subscription_model = $this->CI->UserSubscription_model;

		if ($this->session->userdata('user_id') &&
			($user_info = $this->user_model->get($this->session->userdata('user_id'))) &&
			($user_subscription_info = $this->user_subscription_model->get_all([
				'user_id'				=> $user_info['id'],
				'subscription_plan_id'	=> $user_info['subscription_plan_id'],
			])['rows'][0] ?? []) &&
			strtotime($user_subscription_info['end_date']) > time()
		) {
			return true;
		}

		return false;
	}

	public function add($product_id = 0, $quantity = 1, $option = '', $inc = true) {
		$cart_id = 0;

		if ($option == 'ebook') {
			$inc = false;
			$quantity = 1;
		}

		if ($option == 'audio_book') {
			$inc = false;
			$quantity = 1;
		}

		if ($row = $this->db->get_where('cart', [
			'option'			=> $option,
			'product_id'		=> (int)$product_id,
			'user_id'			=> (int)$this->session->userdata('user_id'),
		])->row_array()) {
			if ($quantity <= 0) {
				$this->db->delete('cart', [
					'id'			=> (int)$row['id']
				]);
			} else {
				$this->db->update('cart', [
					'quantity'		=> $inc ? ($row['quantity'] + (int)$quantity) : (int)$quantity,
					'date_modified'	=> date('Y-m-d H:i:s'),
				], [
					'id'			=> (int)$row['id']
				]);

				$cart_id = $row['id'];
			}
		} else {
			$this->db->insert('cart', [
				'product_id'	=> (int)$product_id,
				'quantity'		=> (int)$quantity,
				'option'		=> $option,
				'user_id'		=> (int)$this->session->userdata('user_id'),
				'site_id'		=> (int)$this->config->item('site_id'),
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);

			$cart_id = $this->db->insert_id();
		}

		if (
			$cart_id &&
			($book_info = $this->book_model->get($product_id)) &&
			$book_info['user_id'] == $this->session->userdata('user_id')
		) {
			self::useCredit($cart_id);
		}

		return $cart_id;
	}

	public function update($cart_id = 0, $quantity = 1, $option = '') {
		if ($quantity > 0) {
			if ($option == 'ebook') {
				$inc = false;
				$quantity = 1;
			}

			if ($option == 'audio_book') {
				$quantity = 1;
			}

			$this->db->update('cart', [
				'quantity'		=> (int)$quantity,
				'option'		=> $option,
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'id'			=> (int)$cart_id,
				'user_id'		=> (int)$this->session->userdata('user_id'),
			]);
		} else {
			$this->db->delete('cart', [
				'id'			=> (int)$cart_id,
				'user_id'		=> (int)$this->session->userdata('user_id'),
			]);
		}
	}

	public function useCredit($cart_id = 0) {
		self::_resetUserCredit();

		if (($this->db->get_where('cart', [
			'used_credit > ' 	=> 0,
			'id'				=> (int)$cart_id,
			'user_id'			=> (int)$this->session->userdata('user_id'),
		])->row()->used_credit ?? 0) > 0) {
			$this->db->update('cart', [
				'used_credit'	=> 0,
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'id'			=> (int)$cart_id,
				'user_id'		=> (int)$this->session->userdata('user_id'),
			]);
		} else {
			if ($this->available_credit > 0) {
				$this->db->update('cart', [
					'used_credit'	=> (int)$this->available_credit,
					'quantity'		=> (int)$this->available_credit,
					'date_modified'	=> date('Y-m-d H:i:s'),
				], [
					'id'			=> (int)$cart_id,
					'user_id'		=> (int)$this->session->userdata('user_id'),
				]);
			}
		}
	}

	public function useShippingCredit($action = 'add') {
		$user_id = $this->session->userdata('user_id');

		if ($action === 'remove') {
			$this->cache->delete('shipping_credit_' . (int)$user_id);
			$this->session->unset_userdata([
				'shipping_credit',
				'shipping_discount'
			]);

			return;
		}

		$address_info = $this->address_model->get($this->session->userdata('shipping_address_id'));

		if (
			!empty($shipping_credit_info = $this->shipping_credit_model->get_all([
				'user_id'		=> (int)$user_id,
				'country_code'	=> _get_country_code_by_name($address_info['country']),
			])['rows'][0] ?? []) &&
			!empty((double)$shipping_credit_info['credit'])
		) {
			self::_resetUserCredit();

			$locked_credit_info = json_decode($this->cache->get('shipping_credit_' . (int)$user_id), true);

			$quantity = $this->db->select_sum('quantity')
				->where('user_id', (int)$user_id)
				->get('cart')
				->row()->quantity;

			// remove locked credit for further use
			$shipping_credit_info['credit'] -= $locked_credit_info['shipping_credit'] ?? 0;

			$shipping_info = $this->session->userdata('shipping_info');
			$shipping_cost = $shipping_info['rate'] ?? 0;

			$this->session->set_userdata('shipping_credit', $quantity >= $shipping_credit_info['credit']
				? $shipping_credit_info['credit']
				: $quantity
			);

			$shipping_discount = round(($this->session->userdata('shipping_credit') * $shipping_cost / $quantity), 2);

			$this->session->set_userdata('shipping_discount', $shipping_discount);

			log_kb(['useShippingCredit' => [
				compact(['quantity', 'shipping_credit_info', 'shipping_cost', 'shipping_discount', 'locked_credit_info'])
			]]);

			$this->cache->save('shipping_credit_' . (int)$user_id, json_encode([
				'user_id'			=> (int)$user_id,
				'shipping_credit'	=> (int)$this->session->userdata('shipping_credit'),
			]), 600);
		} else {
			$this->cache->delete('shipping_credit_' . (int)$user_id);
			$this->session->unset_userdata([
				'shipping_credit',
				'shipping_discount'
			]);
		}
	}

	public function remove($product_id = 0) {
		$this->db->delete('cart', [
			'product_id'	=> (int)$product_id,
			'user_id'		=> (int)$this->session->userdata('user_id'),
		]);
	}

	public function empty($user_id = 0) {
		$user_id = !empty($user_id)
			? $user_id
			: $this->session->userdata('user_id');

		$this->db->delete('cart', [
			'user_id'		=> (int)$user_id,
		]);
	}

	public function getItemTotal($item = [], $book_info = []) {
		$total = self::_calculateTotal($item, $book_info);

		return [
			'price'					=> round($total['price'], 2),
			'credit'	 			=> $total['credit'],
			'coupon'	 			=> $total['coupon'],
			'used_credit'	 		=> $total['used_credit'],
			'credit_discount' 		=> $total['credit_discount'],
			'total_pages'			=> $total['total_pages'],
			'ppp_total'				=> $total['ppp_total'],
			'subtotal'				=> round($total['subtotal'], 2),
			'total'					=> round($total['total'], 2),
			'weight'				=> round($total['weight'], 2),
			'discounts'	 			=> $total['discounts'],
			'option'				=> $total['option'],
			'type'					=> $total['type'],
			'message'				=> $total['message'] ?? '',
		];
	}

	public function getTotal($user_id = 0) {
		if (!($cart_items = self::getItems($user_id))) return;

		$discounts = [];

		$price = $ppp_total = $weight = $option_total = $tax = $shipping_cost = $subtotal = $total = $credit_discount = $quantity = 0;

		$shipping_cost = $this->session->userdata('shipping_info')['rate'] ?? 0;

		$coupon = [];

		foreach ($cart_items as $item) {
			$item_total = $item['total'];

			$price += ($item_total['price'] * $item['quantity']);
			$ppp_total += ($item_total['ppp_total'] * $item['quantity']);
			$option_total += (($item_total['option']['price'] ?? 0) * $item['quantity']);
			$credit_discount += $item_total['credit_discount'];
			$subtotal += $item_total['subtotal'];
			$total += $item_total['total'];
			$weight += $item_total['weight'];
			$quantity += $item['quantity'];

			if (!empty($item_total['coupon'])) {
				if (empty($coupon)) {
					$coupon = $item_total['coupon'];
				} else {
					$coupon['amount'] += $item_total['coupon']['amount'];
				}
			}

			foreach ($item['total']['discounts'] as $discount_key => $discount_value) {
				if (isset($discounts[$discount_key])) {
					$discounts[$discount_key] += $discount_value;
				} else {
					$discounts[$discount_key] = $discount_value;
				}
			}
		}

		if (!empty((double)$this->session->userdata('shipping_discount'))) {
			$shipping_cost -= (double)$this->session->userdata('shipping_discount');

			$shipping_info = $this->session->userdata('shipping_info');

			$shipping_info['shipping_discount']	= (double)$this->session->userdata('shipping_discount');
			$shipping_info['shipping_credit']	= (int)$this->session->userdata('shipping_credit');

			$this->session->set_userdata('shipping_info', $shipping_info);

			$discounts['shipping'] = round((double)$this->session->userdata('shipping_discount'), 2);
		}

		return [
			'price'					=> round($price, 2),
			'ppp_total'				=> $ppp_total,
			'option_total'			=> $option_total,
			'credit_discount' 		=> $credit_discount,
			'coupon'				=> $coupon,
			'tax'					=> $tax,
			'shipping_cost'			=> round($shipping_cost, 2),
			'shipping_credit'		=> !empty((double)$this->session->userdata('shipping_discount'))
				? (int)$this->session->userdata('shipping_credit')
				: 0,
			'subtotal'				=> round($subtotal, 2),
			'discounts'				=> $discounts,
			'total'					=> round(($total + $shipping_cost + $tax), 2),
			'weight'				=> round($weight, 2) + BOOK_WEIGHT['packing'],
			'quantity'				=> $quantity,
		];
	}

	private function _calculateTotal($item = [], $book_info = []) {
		$discounts = [];

		$book_price = $original_book_price = $this->book_model->getPrice($item['product_id']);

		if (
			strtolower($this->config->item('site_country_code')) === 'in' &&
			($book_info['user_id'] == $this->user_info['id']) &&
			$item['option'] != 'ebook' &&
			$item['option'] != 'audio_book'
		) {
			$book_price = $this->discount_lib->applyAuthorDiscount(
				$item['product_id'],
				$book_price,
				$item['quantity'],
				$item['option']
			);

			$message = $this->discount_lib->getAuthorDiscountMessage(
				$item['product_id'],
				$book_price,
				$item['quantity'],
				0,
				$item['option']
			);
		} elseif (
			strtolower($this->config->item('site_country_code')) !== 'in' &&
			$item['option'] != 'ebook' &&
			$item['option'] != 'audio_book'
		) {
			$book_price = $this->discount_lib->applyAuthorDiscount(
				$item['product_id'],
				$book_price,
				$item['quantity'],
				$item['option']
			);

			$message = $this->discount_lib->getAuthorDiscountMessage(
				$item['product_id'],
				$book_price,
				$item['quantity'],
				0,
				$item['option']
			);
		}

		$used_credit = $author_discount = $school_discount = 0;

		if ($item['option'] == 'ebook') {
			$subtotal = $book_price['ebook_price'];
		} elseif ($item['option'] == 'audio_book') {
			$subtotal = $book_price['audio_book_price'];
		} else {
			$subtotal = ($original_book_price[$item['option'] . '_total'] ?? $original_book_price['total']) * $item['quantity'];
			$author_discount = $subtotal - (($book_price[$item['option'] . '_total'] ?? $book_price['total']) * $item['quantity']);

			if ($author_discount) {
				$discounts[
					strtolower($this->config->item('site_country_code')) == 'in'
						? 'author'
						: 'special'
				] = round($author_discount, 2);
			}
		}

		$school_discount = $this->discount_lib->applySchoolDiscount(
			$item['product_id'],
			$subtotal,
			$item['quantity'],
			$item['option']
		);

		if ($school_discount) {
			$discounts['school'] = round($school_discount, 2);
		}

		$additional_discount = $this->discount_lib->applyAdditionalDiscount(
			$item['product_id'],
			$subtotal - $author_discount - $school_discount,
			$item['quantity'],
			$item['option']
		);

		if ($additional_discount) {
			$discounts['author'] = round($additional_discount, 2);
		}

		if ($book_info['user_id'] == $this->user_info['id'] && $item['option'] != 'ebook') {
			// $used_credit = $this->available_credit > $item['quantity']
			// 	? $item['quantity']
			// 	: $this->available_credit
			// ;
			$used_credit = $item['used_credit'];
			$credit_discount = $used_credit * ($book_price[$item['option'] . '_price'] ?? $book_price['price']);

			$this->available_credit -= $used_credit;
		} else {
			$credit_discount = 0;
		}

		if ($credit_discount) {
			$discounts['credit'] = round($credit_discount, 2);
		}

		$coupon_discount = 0;
		$coupon = [];

		if (
			$item['coupon_id'] &&
			($coupon_info = $this->coupon_model->get($item['coupon_id']))
		) {
			$is_coupon_applied = true;

			if ($is_coupon_applied) {
				if ($coupon_info['discount_type'] == 2) {
					// percentage discount
					$coupon_discount = ($subtotal - $author_discount - $additional_discount - $school_discount) * $coupon_info['discount'] / 100;
				} else {
					// flat discount
					$coupon_discount = $coupon_info['discount'];
				}

				$coupon = [
					'id'		=> $coupon_info['id'],
					'code'		=> $coupon_info['code'],
					'value'		=> $coupon_info['discount'],
					'amount'	=> $coupon_discount,
				];
			}
		}

		if ($coupon_discount) {
			$discounts['coupon'] = round($coupon_discount, 2);
		}

		$total = $subtotal - $author_discount - $additional_discount - $school_discount - $credit_discount - $coupon_discount;

		$weight = $item['option'] == 'ebook' ? 0 : ((
			$book_price['total_pages'] * (BOOK_WEIGHT[$item['option']] ?? BOOK_WEIGHT['page']) * 2 +
			BOOK_WEIGHT['cover'][$item['option']]
		) * $item['quantity']);

		return [
			'price'				=> $item['option'] == 'ebook'
				? $book_price['ebook_price']
				: ($book_price[$item['option'] . '_price'] ?? $book_price['price']),
			'ppp_total'			=> $item['option'] == 'ebook'
				? 0
				: ($book_price[$item['option'] . '_ppp_total'] ?? $book_price['ppp_total']),
			'total_pages'		=> $book_price['total_pages'],
			'credit'			=> $book_info['user_id'] == $this->user_info['id']
				? ($used_credit > 0
					? $used_credit
					: ($this->available_credit > 0
						? $this->available_credit
						: 0
					)
				)
				: 0,
			'coupon'			=> $coupon,
			'used_credit'		=> $used_credit,
			'credit_discount' 	=> $credit_discount,
			'option'			=> [
				'name'			=> $item['option'],
				'price'			=> 0,
			],
			'subtotal'			=> round($subtotal, 2),
			'discounts'			=> $discounts,
			'total'				=> round($total, 2),
			'weight'			=> round($weight, 2),
			'message'			=> $message ?? '',
			'type'				=> $item['option'] === 'paperback' ? 'printed' : $item['option'],
		];
	}

	public function applyCoupon($coupon = NULL) {
		$status = false;

		$this->db->update('cart', [
			'coupon_id'		=> 0,
		], [
			'user_id'		=> (int)$this->session->userdata('user_id'),
		]);

		if (!$coupon) return $status;

		$coupon_info = $this->coupon_model->getByCouponCode([
			'code'			=> $coupon,
			'coupon_type'	=> 'product',
		]);

		if (!$coupon_info) return $status;

		$cart_items = $this->db->get_where('cart', [
			'user_id'		=> (int)$this->session->userdata('user_id'),
		])->result_array();

		if ($coupon_info['user_id'] && $coupon_info['user_id'] != $this->session->userdata('user_id')) return $status;
		// if (!$coupon_info['item_id']) return $coupon_info;

		foreach ($cart_items as $item) {
			if ($coupon_info['item_id']) {
				if ($coupon_info['item_id'] == $item['product_id']) {
					$this->db->update('cart', [
						'coupon_id'		=> $coupon_info['id'],
					], [
						'id'			=> $item['id']
					]);

					$status = true;
				}
			} else {
				$this->db->update('cart', [
					'coupon_id'		=> $coupon_info['id'],
				], [
					'id'			=> $item['id']
				]);

				$status = true;
			}
		}

		return $status;
	}

	public function removeCoupon() {
		$this->db->update('cart', [
			'coupon_id'		=> 0,
		], [
			'user_id'		=> (int)$this->session->userdata('user_id'),
		]);
	}

	public function getSessionItems($order_id = 0) {
		$cart_items = [];

		$items = $this->db->get_where('cart_session', [
			'order_id'		=> (int)$order_id,
			'status'		=> 0,
		])->result_array();

		foreach ($items as $item) {
			$book_info 			= $this->bookstore_model->getByBookId($item['product_id']);
			$book_info['id'] 	= $book_info['book_id'];

			$cart_items[] = [
				'id' 			=> $item['id'],
				'book' 			=> format_book_info($book_info),
				'order_id' 		=> $item['order_id'],
				'ext_order_id' 	=> $item['ext_order_id'],
				'quantity' 		=> $item['quantity'],
				'total'			=> json_decode($item['total'], true),
			];
		}

		return $cart_items;
	}

	public function addToCartSession($order_id = 0) {
		if (($order_info = $this->order_model->get($order_id)) && (
			$items = self::getItems($order_info['user_id'])
		)) {
			foreach ($items as $item) {
				$this->db->insert('cart_session', [
					'user_id'		=> (int)$order_info['user_id'],
					'order_id'		=> (int)$order_info['id'],
					'ext_order_id'	=> $order_info['ext_order_id'],
					'product_id'	=> (int)$item['book']['id'],
					'quantity'		=> (int)$item['quantity'],
					'total'			=> json_encode($item['total']),
					'date_added'	=> date('Y-m-d H:i:s'),
					'date_modified'	=> date('Y-m-d H:i:s'),
				]);
			}
		}
	}

	public function updateSessionItem($id = 0, $data = []) {
		$this->db->update('cart_session', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'	=> (int)$id,
		]);
	}
}
