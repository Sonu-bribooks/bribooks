<?php defined('BASEPATH') or exit('No direct script access allowed');

trait AmazonKdpOrderAlert {
	public function amazonKdpOrderCron() {
		$this->load->model('order/AmazonKdpOrder_model', 'amazon_kdp_order_model');

		if (!empty($amazon_kdp_order_results = $this->amazon_kdp_order_model->get_all([
			'is_duplicate' 	=> 0,
			'status'		=> 0
		])['rows'] ?? [])) {
			$skipped = $uploaded = $not_matched = 0;

			foreach ($amazon_kdp_order_results as $amazon_kdp_order_info) {
				if (!empty($book_info = $this->book_model->get_all([
					'isbn' 			=> $amazon_kdp_order_info['isbn']
				])['rows'] ?? [])) {
					if (count($book_info) > 1) {
						$skipped++;
						continue;
					}

					$book_info = $book_info[0];
					$user_info = $this->user_model->get($book_info['user_id']);

					if (empty($user_info)) {
						$skipped++;
						continue;
					}

					$total_pages = $this->book_model->getTotalPages($book_info['id']) * 2 + 5;

					$base_price 		= 20;
					$free_page_limit 	= 80;
					$price_per_page 	= 0.1;

					if ($total_pages > $free_page_limit) {
						$ppp_total = (
							$total_pages - $free_page_limit
						) * $price_per_page;

						$total = $base_price + $ppp_total;

						$book_price = [
							'price' 		=> round($base_price, 2),
							'total' 		=> round($total, 2),
							'ppp_total' 	=> round($ppp_total, 2),
							'total_pages' 	=> $total_pages,
						];
					} else {
						$book_price = [
							'price' 		=> round($base_price, 2),
							'total' 		=> round($base_price, 2),
							'ppp_total' 	=> 0,
							'total_pages' 	=> $total_pages,
						];
					}

					$update = [];
					$update['book_id']			= $book_info['id'];
					$update['version']			= $book_info['version'];
					$update['user_id']			= $book_info['user_id'];
					$update['site_id']			= $user_info['site_id'];
					$update['currency_code']	= 'USD';
					$update['price']			= $book_price['total'];
					$update['pages']			= $total_pages;
					$update['status']			= '1';
					$update['date_modified']	= date('Y-m-d H:i:s');

					$this->amazon_kdp_order_model->edit($amazon_kdp_order_info['id'], $update);

					$uploaded++;
				} else {
					$not_matched++;
				}
			}

			$this->cron_model->add([
				'code'			=> 'amazonKdpOrderCreateCron',
				'action'		=> 'alert_model->amazonKdpOrderCreateCron',
				'data'			=> [count($amazon_kdp_order_results), $skipped, $uploaded, $not_matched],
				'site_id'		=> 2,
				'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
					? '+2 minutes'
					: '+1 minutes'
				)),
			]);
		}
	}

	public function amazonKdpOrderCreateCron() {
		$this->load->model('order/AmazonKdpOrder_model', 'amazon_kdp_order_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->library('Ranking_lib', 'ranking_lib');

		if (!empty($amazon_kdp_order_results = $this->amazon_kdp_order_model->get_all([
			'is_duplicate' 	=> 0,
			'status'		=> 1
		])['rows'] ?? [])) {
			$this->load->library('Royalty_lib', 'royalty_lib');

			foreach ($amazon_kdp_order_results as $item) {
				$event_book_info = $this->event_book_model->get_all([
					'book_id' 	=> (int)$item['book_id']
				])['rows'] ?? [];

				$event_id 			= !empty($event_book_info[0]['event_id']) ? $event_book_info[0]['event_id'] : 0;
				$total_pages 		= $item['pages'];

				$base_price 		= 20;
				$free_page_limit 	= 80;
				$price_per_page 	= 0.1;

				if ($total_pages > $free_page_limit) {
					$ppp_total = (
						$total_pages - $free_page_limit
					) * $price_per_page;

					$total = $base_price + $ppp_total;

					$book_price = [
						'price' 		=> round($base_price, 2),
						'total' 		=> round($total, 2),
						'ppp_total' 	=> round($ppp_total, 2),
						'total_pages' 	=> $total_pages,
					];
				} else {
					$book_price = [
						'price' 		=> round($base_price, 2),
						'total' 		=> round($base_price, 2),
						'ppp_total' 	=> 0,
						'total_pages' 	=> $total_pages,
					];
				}

				$weight = (
					$total_pages * BOOK_WEIGHT['page'] * 2 +
					BOOK_WEIGHT['cover']['paperback']
				) * $item['quantity'];

				$user_id = 383799;

				$ppp_total 		= $book_price['ppp_total'] * $item['quantity'];
				$shipping_cost 	= '0.00';
				$total 			= round(((($book_price['price']) * $item['quantity']) + $ppp_total), 2);

				$order_data = [
					'user_id'				=> $user_id,
					'site_id'				=> 2,
					'address_id'			=> 109106,
					'currency_id'			=> 2,
					'currency_code'			=> 'USD',
					'currency_symbol'		=> '$',
					'coupon_id'				=> 0,
					'ppp_total'				=> (double)$ppp_total,
					'credit_discount'		=> '0.00',
					'tax'					=> '0.00',
					'shipping_cost'			=> (double)$shipping_cost,
					'subtotal'				=> (double)$total,
					'total'					=> (double)$total,
					'weight'				=> (double)$weight,
					'shipping_info'			=> '{"id": 9876543210, "rate": '.$shipping_cost.', "courier_name": "BriBooks Amazon Shipping"}',
					'ip'					=> $this->input->ip_address(),
					'provider'				=> 'stripe',
					'status'				=> 1,
					'order_type'			=> 1,
					'ext_order_id'			=> 'pi_3MzhDuGfHyFVxD3I1kxwpbEx_secret_h2PRb9HEwZZTjPyljnbJUnELl',
					'ext_transaction_id'	=> 'pi_3MzhDuGfHyFVxD3I1kxwpbEx',
					'date_added'			=> date('Y-m-d H:i:s'),
					'date_modified'			=> date('Y-m-d H:i:s')
				];

				$order_id = 0;

				if (1) {
					$this->db->insert('order', $order_data);
					$order_id = $this->db->insert_id();

					$update = [];
					$update['order_code']		= 'BB-' . time() . '-' . $order_id . 'I' . $user_id;
					$update['status']			= 4;
					$update['shipping_status']	= 1;
					$update['date_modified']	= date('Y-m-d H:i:s');
					$update['date_completed']	= date('Y-m-d H:i:s');

					$this->db->where('id', (int)$order_id);
					$this->db->update('order', $update);
				}

				$total_op = round((($book_price['total'] * $item['quantity'])), 2);

				$order_product_data = [
					'version'			=> (int)$item['version'],
					'order_id'			=> (int)$order_id,
					'product_id'		=> (int)$item['book_id'],
					'quantity'			=> (int)$item['quantity'],
					'price'				=> (double)$book_price['price'],
					'credit'			=> 0,
					'used_credit'		=> 0,
					'credit_discount'	=> '0.00',
					'ppp_total'			=> (double)$ppp_total,
					'subtotal'			=> (double)$total_op,
					'total'				=> (double)$total_op,
					'weight'			=> $weight,
					'option'			=> '{"name":"Paperback","price":0}'
				];

				$order_comment_data = [
					'order_id'			=> (int)$order_id,
					'description'		=> 'Manually Created & Delivered',
					'status'			=> 4,
					'date_added'		=> date('Y-m-d H:i:s'),
					'date_modified'		=> date('Y-m-d H:i:s'),
				];

				$order_history_data = [
					'order_id'			=> (int)$order_id,
					'description'		=> 'Amazon Order Completed',
					'status'			=> 4,
					'date_added'		=> date('Y-m-d H:i:s'),
					'date_modified'		=> date('Y-m-d H:i:s'),
				];

				$payment_data = [
					'site_id'			=> 2,
					'user_id'			=> $user_id,
					'order_id'			=> (int)$order_id,
					'currency_id'		=> 2,
					'currency_code'		=> 'USD',
					'currency_symbol'	=> '$',
					'provider'			=> 'stripe',
					'amount'			=> (double)$total,
					'status'			=> 1,
					'date_added'		=> date('Y-m-d H:i:s'),
					'date_modified'		=> date('Y-m-d H:i:s'),
				];

				$event_order_data = [
					'event_id'			=> (int)$event_id,
					'order_id'			=> (int)$order_id,
					'book_id'			=> (int)$item['book_id'],
					'quantity'			=> (int)$item['quantity'],
					'date_added'		=> date('Y-m-d H:i:s'),
					'date_modified'		=> date('Y-m-d H:i:s'),
				];

				if (1 && $order_id) {
					$this->db->insert('order_product', $order_product_data);
					$this->db->insert('order_comment', $order_comment_data);
					$this->db->insert('order_history', $order_history_data);
					$this->db->insert('payment', $payment_data);

					if ($event_id) {
						$this->db->insert('event_order', $event_order_data);
					}

					// $this->royalty_lib->applyRoyalty($order_id);
					// $this->royalty_lib->generateCredit($order_id);

					$update = [];
					$update['order_id']			= $order_id;
					$update['status']			= '2';
					$update['date_modified']	= date('Y-m-d H:i:s');

					$this->db->where('id', (int)$item['id']);
					$this->db->update('amazon_kdp_order', $update);

					if (date('YmdHis') <= NYAF_US_END_DATE) {
						self::cron($order_id, 'createCertificateNyafUs');
						self::cron($order_id, 'createAwardsOnBookSoldNyafUs');

						$this->ranking_lib->updateRank($order_id, 'amazon_kdp_order');
					}
				}
			}
		}
	}
}
