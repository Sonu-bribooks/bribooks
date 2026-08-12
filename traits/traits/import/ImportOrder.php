<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportOrder {
	private function _importAmazonKdpOrder($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('order/AmazonKdpOrder_model', 'amazon_kdp_order_model');

		foreach ($rows as $data) {
			self::_updateCounter($job_id);

			if (
				empty($data['Royalty Date']) ||
				empty($data['Order Date']) ||
				empty($data['Title']) ||
				empty($data['Author Name']) ||
				empty($data['ISBN']) ||
				empty($data['Marketplace']) ||
				empty($data['Currency']) ||
				empty($data['Net Units Sold']) ||
				($data['Net Units Sold'] < 1)
			) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			$is_duplicate = 0;

			if (!empty($this->amazon_kdp_order_model->get_all([
				'royalty_date'		=> trim($data['Royalty Date']),
				'order_date'		=> trim($data['Order Date']),
				'isbn'				=> (int)trim($data['ISBN']),
				'marketplace'		=> trim($data['Marketplace']),
				'quantity'			=> $data['Net Units Sold']
			])['total'])) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;

				// $is_duplicate = 1;
			}

			$quantity = 0;

			if (!empty($amazon_kdp_order_results = $this->amazon_kdp_order_model->get_all([
				'royalty_date'		=> trim($data['Royalty Date']),
				'order_date'		=> trim($data['Order Date']),
				'isbn'				=> (int)trim($data['ISBN']),
				'marketplace'		=> trim($data['Marketplace'])
			])['rows'])) {
				$quantity = !empty($amazon_kdp_order_results)
					? array_sum(array_column($amazon_kdp_order_results, 'quantity'))
					: 0;
			}

			$net_units_sold = (int)$data['Net Units Sold'] - (int)$quantity;

			if (empty($net_units_sold) || ($net_units_sold <= 0)) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			if (empty($book_info = $this->book_model->get_all([
					'isbn' 			=> (int)trim($data['ISBN'])
				])['rows'][0] ?? [])
			) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			if (empty($event_book_info = $this->event_book_model->getEventBookByBookId('', $book_info['id']))) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			$event_id = $event_book_info['event_id'] ?? '';

			$data = [
				'event_id'				=> $event_id,
				'user_id'				=> $book_info['user_id'],
				'book_id'				=> $book_info['id'],
				'version'				=> $book_info['version'],
				'royalty_date'			=> trim($data['Royalty Date']),
				'order_date'			=> trim($data['Order Date']),
				'book_name'				=> trim($data['Title']),
				'author_name'			=> trim($data['Author Name']),
				'isbn'					=> (int)trim($data['ISBN']),
				'marketplace'			=> trim($data['Marketplace']),
				'quantity'				=> $net_units_sold,
				'price_without_tax'		=> $data['Avg. List Price without tax'],
				'currency'				=> $data['Currency'],
				'is_duplicate'			=> $is_duplicate,
				'json_data'				=> json_encode($data)
			];

			$this->amazon_kdp_order_model->add($data);

			$uploaded++;
		}

		if (!empty($uploaded)) {
			$this->cron_model->add([
				'code'			=> 'amazonKdpOrderCron',
				'action'		=> 'alert_model->amazonKdpOrderCron',
				'data'			=> [count($rows), $skipped, $uploaded],
				'site_id'		=> 1,
				'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
					? '+5 minutes'
					: '+1 minutes'
				)),
			]);
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importSchoolLetter($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['school_id']) || empty($data['price']) || empty($data['weight'])) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			if (empty($school_info = $this->school_model->get($data['school_id']))) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			$currency_info = $this->currency_model->getByCode($school_info['currency_code']);

			$letter_order_code = vsprintf('BBS-%s%s', [
				time(),
				$data['school_id'],
			]);

			$this->school_order_model->add([
				'order_code'		=> $letter_order_code,
				'type'				=> $data['type'],
				'event_id'			=> (int)$data['event_id'] ?? 0,
				'school_id'			=> (int)$school_info['id'],
				'weight'			=> (double)($data['weight'] ?? 0),
				'quantity'			=> (int)($data['quantity'] ?? 0),
				'subtotal'			=> (double)apply_currency_exchange($data['price'] ?? 0, $school_info['currency_code']),
				'shipping_cost'		=> (double)apply_currency_exchange($data['shipping_cost'] ?? 0, $school_info['currency_code']),
				'total'				=> (double)apply_currency_exchange(($data['price'] ?? 0) + ($data['shipping_cost'] ?? 0), $school_info['currency_code']),
				'currency_id'		=> (int)$currency_info['id'] ?? '',
				'currency_code'		=> $school_info['currency_code'],
				'currency_symbol'	=> $currency_info['symbol'] ?? '',
				'length'			=> $data['length'] ?? 0,
				'breadth'			=> $data['breadth'] ?? 0,
				'height'			=> $data['height'] ?? 0,
				'status'			=> 21,
				'pickup_location_id'=> 1

			]);

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importDirectOrder($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('shipping/DirectShipments_model', 'direct_shipments_model');

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['event_name']) || empty($data['consignee_name']) || empty($data['consignee_pincode'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$is_duplicate = 0;

			if (!empty($data['check_duplicate']) && !empty($this->direct_shipments_model->get_all([
				'event_name'		=> trim($data['event_name']),
				'type'				=> trim($data['type']),
				'consignee_name'	=> trim($data['consignee_name']),
				'consignee_pincode'	=> trim($data['consignee_pincode']),
				'actual_weight'		=> $data['actual_weight']
			])['total'])) {
				self::_updateCounter($job_id, true);
				$skipped++;

				$is_duplicate = 1;
			}

			$this->direct_shipments_model->add([
				'reference_no'			=> trim($data['reference_no']),
				'event_name'			=> trim($data['event_name']),
				'type'					=> trim($data['type']),
				'consignee_name'		=> trim($data['consignee_name']),
				'consignee_attention'	=> trim($data['consignee_attention']),
				'consignee_address1'	=> trim($data['consignee_address1']),
				'consignee_address2'	=> trim($data['consignee_address2']),
				'consignee_address3'	=> trim($data['consignee_address3']),
				'consignee_city'		=> trim($data['consignee_city']),
				'consignee_state'		=> trim($data['consignee_state']),
				'consignee_pincode'		=> trim($data['consignee_pincode']),
				'consignee_telephone'	=> trim($data['consignee_telephone']),
				'consignee_mobile'		=> trim($data['consignee_mobile']),
				'consignee_email_id'	=> trim($data['consignee_email_id']),
				'quantity'				=> (int)$data['quantity'],
				'actual_weight'			=> $data['actual_weight'],
				'declared_value'		=> $data['declared_value'],
				'commodity_detail'		=> trim($data['commodity_detail']),
				'special_instruction'	=> trim($data['special_instruction']),
				'length'				=> $data['length'],
				'breadth'				=> $data['breadth'],
				'height'				=> $data['height'],
				'is_duplicate'			=> $is_duplicate,
				'status'				=> 0,
				'_deleted'				=> 0
			]);

			$uploaded++;
		}

		$this->cron_model->add([
			'code'			=> 'directShipmentsCron',
			'action'		=> 'alert_model->directShipmentsCron',
			'data'			=> [count($rows), $skipped, $uploaded],
			'site_id'		=> 1,
			'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
				? '+5 minutes'
				: '+1 minutes'
			)),
		]);

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importCrosswordStore($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['store_location']) && empty($data['store_name']) && empty($data['book_isbn'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (!empty($data['store_location'])) {
				if ($city_info = $this->db->get_where('city', [
					'name' 			=> trim($data['store_location']),
					'_deleted' 		=> 0
				])->row_array()) {
					$city_id = $city_info['id'];

					$store_info = $this->cross_word_store_model->get_all([
						'city_id'	    => $city_info['id'],
						'store_name'	=> trim($data['store_name'])
					])['rows'][0] ?? [];

					if (!empty($store_info)) {
						$store_id = $store_info['id'];
					} else {
						$store_id = $this->cross_word_store_model->add([
							'state_id'   => !empty($city_info) ? $city_info['state_id'] : 0,
							'city_id'    => $city_id,
							'store_name' => trim($data['store_name'])
						]);
						$uploaded++;
					}

					if (!empty($book_info = $this->db->get_where('book', [
						'isbn' 			=> trim($data['book_isbn']),
					])->row_array())) {
						if (empty($store_book_info = $this->cross_word_book_model->get_all([
							'store_id'	=> $store_id,
							'book_id'	=> $book_info['id']
						])['rows'][0] ?? [])) {
							$this->cross_word_book_model->add([
								'store_id'	=> $store_id,
								'book_id'	=> $book_info['id']
							]);

							$uploaded++;
						} else {
							self::_updateCounter($job_id, true);
							$skipped++;
							continue;
						}
					} else {
						self::_updateCounter($job_id, true);
						$skipped++;
						continue;
					}
				}
			}
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importBookStock($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		$this->load->library('Stock_lib', 'stock_lib');
		$this->load->model('book/BookStock_model', 'book_stock_model');

		$book_stocks = [];

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['sku']) || empty($quantity = $data['quantity'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$book_sku = explode('v', strtolower($data['sku']));

			if (empty($book_id = $book_sku[0]) || empty($book_sku[1])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$option = substr($book_sku[1], strlen($book_sku[1]), -1);
			$option = ($option == 'h') ? 'hardcover' : 'paperback';

			$version = substr($book_sku[1], 0, strlen($book_sku[1]) - 1);

			if (empty($this->book_model->get_all([
				'book_id'	=> $book_id,
				'version'	=> $version
			])['rows'][0])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if ($stock_info = $this->book_stock_model->get_all([
				'book_id'	=> $book_id,
				'version'	=> $version,
				'option'	=> $option
			])['rows'][0] ?? []) {
				$this->book_stock_model->edit($stock_info['id'], [
					'manager_id'=> (int)$this->session->userdata('user_id'),
					'quantity'	=> (int)$stock_info['quantity'] + (int)$quantity,
					'status'	=> 1,
					'_deleted'	=> 0
				]);
			} else {
				$this->book_stock_model->add([
					'manager_id'=> (int)$this->session->userdata('user_id'),
					'book_id'	=> $book_id,
					'version'	=> $version,
					'option'	=> $option,
					'quantity'	=> (int)$quantity,
					'status'	=> 1,
					'_deleted'	=> 0
				]);
			}

			$this->stock_lib->stockFulfill(
				$quantity,
				$book_id,
				$version,
				$option,
				0
			);

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}
}
