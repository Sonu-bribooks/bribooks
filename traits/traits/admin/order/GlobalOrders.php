<?php defined('BASEPATH') or exit('No direct script access allowed');

trait GlobalOrders {
	public function ge_orders($status = 'all_orders') {
		self::_initOrderPageData($status, 'global');
	}

	public function ajax_ge_orders($status = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> trim($this->input->get('search[value]')),
			'pickup_location_id'			=> 1,
			'sort'				=> 'order.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'ne_status'	 		=> 0
		];

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if (!empty($this->input->get('filter_printer_id'))) {
			$filter_data['assign_printer_id'] = (int)$this->input->get('filter_printer_id');
		}

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] = trim($explode[0]);
			$filter_data['enddate'] = trim($explode[1]);
		}

		if ($this->input->get('assign_printer_id')) {
			$filter_data['assign_printer_id'] = $this->input->get('assign_printer_id') == 'NA'
				? 0
				: (int)$this->input->get('assign_printer_id');
		}

		if ($this->input->get('printing_status')) {
			$filter_data['printing_status'] = $this->input->get('printing_status') == 2 ? 0 : 1;
		}

		if ($this->input->get('shipping_status')) {
			$filter_data['shipping_status'] = $this->input->get('shipping_status') == 2 ? 0 : 1;
		}

		if ($this->input->get('book_id')) {
			$filter_data['book_id'] = (int)$this->input->get('book_id');
		}

		if ($this->input->get('book_slug')) {
			$filter_data['book_slug'] = $this->input->get('book_slug');
		}

		if ($this->input->get('book_isbn')) {
			$filter_data['book_isbn'] = $this->input->get('book_isbn');
		}

		if ($this->input->get('status')) {
			$filter_data['status'] = (int)$this->input->get('status');
		}

		if ($this->input->get('ne_status')) {
			$filter_data['ne_status'] = (int)$this->input->get('ne_status');
		}

		if ($this->input->get('quantity_le')) {
			$filter_data['quantity_le'] = (int)$this->input->get('quantity_le');
		}

		if ($this->input->get('quantity_ge')) {
			$filter_data['quantity_ge'] = (int)$this->input->get('quantity_ge');
		}

		if ($this->input->get('page_count_le')) {
			$filter_data['page_count_le'] = (int)$this->input->get('page_count_le');
		}

		if ($this->input->get('page_count_ge')) {
			$filter_data['page_count_ge'] = (int)$this->input->get('page_count_ge');
		}

		if ($this->input->get('stock_status')) {
			$filter_data['stock_status'] = $this->input->get('stock_status') == 2 ? 0 : 1;
		}

		if ($this->input->get('ext_transaction_id')) {
			$filter_data['ext_transaction_id'] = $this->input->get('ext_transaction_id');
		}

		if ($this->input->get('order_code')) {
			$filter_data['order_code'] = $this->input->get('order_code');
		}

		if ($this->input->get('has_isbn')) {
			$filter_data['has_isbn'] = $this->input->get('has_isbn') == 2 ? 0 : 1;
		}

		if ($this->input->get('has_amazon_url')) {
			$filter_data['has_amazon_url'] = $this->input->get('has_amazon_url') == 2 ? 0 : 1;
		}

		if ($this->input->get('mobile')) {
			$filter_data['mobile'] = $this->input->get('mobile');
		}

		if ($this->input->get('email')) {
			$filter_data['email'] = $this->input->get('email');
		}

		if ($this->input->get('name')) {
			$filter_data['name'] = $this->input->get('name');
		}

		if ($this->input->get('assignment_code')) {
			$filter_data['assignment_code'] = $this->input->get('assignment_code');
		}

		if ($this->input->get('event_id')) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		if ($this->input->get('site_id')) {
			$filter_data['site_id'] = $this->input->get('site_id');
		}

		if ($this->input->get('site_code')) {
			$filter_data['site_code'] = $this->input->get('site_code');
		}

		if ($this->input->get('order_country')) {
			$filter_data['order_country'] = $this->input->get('order_country');
		}

		if ($this->input->get('order_state')) {
			$filter_data['order_state'] = $this->input->get('order_state');
		}

		if ($this->input->get('customer_info')) {
			$filter_data['customer_info'] = $this->input->get('customer_info');
		}

		if ($status) {
			$filter_data['status'] = (int)$status;

			if ($status == 21) {
				$filter_data['sort'] = 'order.date_added';
				$filter_data['order'] = 'ASC';
			}
		}

		// printed and printed with ebook
		$filter_data['order_type'] = [1, 2];

		$filter_data['option_type'] = [1];

		$filter_data['ne_option_type'] = [2];

		// global orders
		// $filter_data['currency_id'] = 2;
		$filter_data['ne_currency_id'] = 47;

		$results = $this->order_model->searchProductName($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products = $this->order_model->getProducts($result['id']);

			$site_info = $this->site_model->get($result['site_id']);

			$printer_info = $this->user_model->get($result['assign_printer_id']);
			$printer_assign_info = $this->printer_assign_log_model->get_all([
				'order_id'	=> $result['id'],
			])['rows'];

			$customer_info = $this->user_model->get($result['user_id']);

			$printer_assign_info = !empty($printer_assign_info[0]) ? $printer_assign_info[0] : [];

			$shipping_tracking_info = json_decode($result['shipping_tracking_info'], true);

			$json['data'][] = [
				'#'					=> self::_renderCheckBox($result, $products),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'order_code'		=> _order_code($result, $shipping_tracking_info),
				'customer'			=> (!empty($customer_info)) ? $customer_info['first_name'] . ' ' . $customer_info['last_name'] . '<br /><small>' . $customer_info['email'] . '<br />' . $customer_info['mobile'] . '</small><br />' . '<strong>(' . $customer_info['source'] . ')</strong>' : '',
				'product'			=> _op_name($products, $result),
				'weight_amount'		=> $result['weight'] . 'gm' . '<br>' . $result['currency_symbol'] . ' ' . $result['total'],
				'status'			=> _sd($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'printer'          	=> _o_printer($result, $printer_info, $printer_assign_info),
				'history'			=> self::_getHistory($result['id']),
				'actions'			=> _oa_buttons($result, $products, $shipping_tracking_info),
			];
		}

		output_json($json);
	}

	public function add_order_awb() {
		$json = [];

		if ($order_info = $this->order_model->get($this->input->post('order_id'))) {
			if (!empty($order_info['status'])) {
				$this->order_model->edit($this->input->post('order_id'), [
					'shipping_tracking_info' => json_encode([
						'awb_code'	=> $this->input->post('awb')
					])
				]);

				$json['success'] 	= _l('awb_assigned_to_the_order');
			} else {
				$json['error'] 		= _l('unknown_error');
			}
		} else {
			$json['error'] = _l('order_not_found');
		}

		output_json($json);
	}

	public function download_global_manifest($only_csv = false) {
		$this->load->library('zip');

		$orders = [];

		$results = $this->order_model->searchProductName([
			'status'			=> 9,
			/*'printing_status'	=> 1,*/
			'currency_id'		=> 2,
		])['rows'] ?? [];

		foreach ($results as $key => $item) {
			$shipping_tracking_info = json_decode($item['shipping_tracking_info'], true);

			if (empty($shipping_tracking_info['awb_code'])) continue;

			if (!$only_csv) {
				$manifest_data = self::_orderManifest($item['id'], true);

				$this->zip->add_data(vsprintf('manifest_%s.pdf', [
					$item['id'],
				]), $manifest_data);
			}

			$address_info = $this->address_model->getByID($item['address_id']);

			$orders[] = [
				'S. no'			=> $key,
				'Date'			=> date('d/m/Y'),
				'Order Code'	=> $item['order_code'],
				'AWB No.'		=> $shipping_tracking_info['awb_code'],
				'C Name'		=> 'Reg Post',
				'Sender'		=> 'BriBooks',
				'Consignee'		=> $address_info['name'],
				'Mobile'		=> $address_info['mobile'],
				'Address'		=> $address_info['address'],
				'City'			=> vsprintf('%s, %s', [
					$address_info['city'],
					$address_info['state']
				]),
				'Pincode'		=> $address_info['zipcode'],
				'weight'		=> $item['weight'] . 'gm',
				'Status'		=> 'Shipped',
			];
		}

		$csv_file = self::_getManifestCsv($orders, vsprintf('%s/metadata_', [
			FCPATH . 'uploads/pdfs',
		]));

		$csv_data = file_get_contents($csv_file);
		$this->zip->add_data('YouBooks Edtech India Pvt. Ltd.csv', $csv_data);

		$this->zip->download(vsprintf('order_manifest_%s', [
			date('Y_m_d_H_i_s'),
		]));

		is_file($csv_file) && unlink($csv_file);
	}

	private function _getManifestCsv($results = [], $filename = 'download') {
		$filename = $filename . date('Y_m_d_H_i_s') . '.csv';

		$headers = isset($results[0]) ? array_keys($results[0]) : [];

		$fp = fopen($filename, 'w');

		self::_writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		return $filename;
	}
}
