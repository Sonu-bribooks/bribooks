<?php defined('BASEPATH') or exit('No direct script access allowed');

trait DirectShipments {
	public function direct_shipments($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'shipment/direct_shipments';
		$data['heading'] 		= _l('direct_shipments');
		$data['page_title'] 	= _l('direct_shipments');
		$data['status'] 		= '';
		$data['action_ajax'] 	= base_url('admin/ajax_direct_shipments');

		$data['timestamp_start'] 	= strtotime('-30 days', time());
		$data['timestamp_end']	 	= time();

		$this->load->view('backend/index', $data);
	}

	public function ajax_direct_shipments() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> trim($this->input->get('search[value]')),
			'sort'				=> 'direct_shipments.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]'))
		];

		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] = trim($explode[0]);
			$filter_data['enddate'] = trim($explode[1]);
		}

		if ($this->input->get('shipping_status')) {
			$filter_data['shipping_status'] = (int)$this->input->get('shipping_status') == 2 ? 0 : 1;
		}

		if ($this->input->get('status')) {
			$filter_data['status'] = (int)$this->input->get('status');
		}

		if ($this->input->get('ne_status')) {
			$filter_data['ne_status'] = (int)$this->input->get('ne_status');
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

		$results = $this->direct_shipments_model->get_all($filter_data);

		// pr($results, 1);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = [];
			$format_date = '';

			if(!empty($result['cancel_manager_id'])) {
				$user_info = $this->user_model->get($result['cancel_manager_id']);
				$format_date = formatDate($result['date_cancel']);
			} else if(!empty($result['manager_id'])) {
				$user_info = $this->user_model->get($result['manager_id']);
				$format_date = formatDate($result['date_label']);
			}

			$shipping_tracking_info = !empty($result['shipping_tracking_info']) ? json_decode($result['shipping_tracking_info'], true) : '';

			$buttons = [];

			/*if(empty($result['is_duplicate'])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-info btn-sm" data-id="%s">%s</button>', [
						$result['id'],
						_l('resend'),
					]);
			}*/

			if(!empty($result['awb_code']) && empty($result['cancel_manager_id'])) {
				$buttons[] = vsprintf('<button type="button" class="btn btn-info btn-sm btn-cancel" data-toggle="modal" data-target="#cancelModal" data-id="%s">%s</button>', [
						$result['id'],
						_l('cancel'),
					]);

				$buttons[] = vsprintf('<button type="button" class="btn btn-warning btn-sm generate-singlelabel-ds" data-id="%s">%s</button>', [
						$result['id'],
						_l('label'),
					]);
			}

			$buttons = !empty($buttons) ? implode('<br /><br />', $buttons) . '<br />' : '';

			if(!empty($result['cancel_manager_id'])) {
				$buttons = '<strong>Cancelled</strong><br />';
			} else if(empty($result['awb_code']) && !empty($result['is_duplicate'])) {
				$buttons = '<strong>Duplicate</strong>';
			}

			$json['data'][] = [
				'#'					=> self::_renderCheckBox($result),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'reference_no'		=> $result['reference_no'],
				'customer'			=> (!empty($result['consignee_name'])) ? $result['consignee_name'] . '<br /><small>' . $result['consignee_email_id'] . '<br />' . $result['consignee_mobile'] . '</small><br />' . '<strong>(' . $result['event_name'] . ')</strong>' : '',
				'address'			=> (!empty($result['consignee_address1'])) ? $result['consignee_address1'] . ',<br /><small>' . $result['consignee_address2'] . ',<br />' . $result['consignee_city'] . ', ' . $result['consignee_state'] . ',<br />' . $result['consignee_pincode'] . '</small>' : '',
				'product'			=> $result['type']. '<br />' . ($result['actual_weight'] * 1000) . 'gm<br />' . $result['length'] . 'x' . $result['breadth'] . 'x' . $result['height'],
				'awb_number'		=> !empty($result['awb_code']) ? '<a href="https://www.bluedart.com/trackdartresultthirdparty?trackFor=0&trackNo=' . $result['awb_code'] . '" target="_blank">' . $result['awb_code'] . '</a>' : (!empty($shipping_tracking_info['error']) ? $shipping_tracking_info['error'] : ''),
				'status'			=> $result['status'],
				'date_added'		=> formatDate($result['date_added']),
				'actions'			=> $buttons . (!empty($user_info['first_name']) ? '<small>' . $format_date . '<br />( ' . trim($user_info['first_name'] . ' ' . $user_info['last_name'])  . ' )</small>' : ''),
				'is_duplicate'		=> ($result['is_duplicate'] == '1') ? '1' : ''
			];
		}

		output_json($json);
	}

	public function export_direct_shipments() {
		if (!empty($this->input->get('startdate'))) {
			$filter_data['startdate'] = $this->input->get('startdate');
		}

		if (!empty($this->input->get('enddate'))) {
			$filter_data['enddate'] = $this->input->get('enddate');
		}

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));
			$filter_data['startdate'] = trim($explode[0]);
			$filter_data['enddate'] = trim($explode[1]);
		}

		$results = $this->direct_shipments_model->get_all($filter_data);

		$direct_shipments = [];

		foreach ($results['rows'] ?? [] as $key => $result) {
			if(!empty($result['manager_id'])) {
				$user_info = $this->user_model->get($result['manager_id']);
				$result['manager_id'] = trim($user_info['first_name'] . ' ' . $user_info['last_name']);
			}

			if(!empty($result['cancel_manager_id'])) {
				$user_info = $this->user_model->get($result['cancel_manager_id']);
				$result['cancel_manager_id'] = trim($user_info['first_name'] . ' ' . $user_info['last_name']);
			}

			$result['cancel_remark'] = !empty($result['cancel_remark']) ? json_decode($result['cancel_remark']) : '';

			$shipping_tracking_info = !empty($result['shipping_tracking_info']) ? json_decode($result['shipping_tracking_info'], true) : '';

			$result['error'] = !empty($shipping_tracking_info['error']) ? $shipping_tracking_info['error'] : '';

			unset($result['commodity_detail']);
			unset($result['special_instruction']);
			unset($result['shipping_tracking_info']);
			unset($result['_deleted']);
			unset($result['date_deleted']);

			$direct_shipments[] = $result;
		}

		self::_downloadCsv($direct_shipments, 'direct_shipments_');

		output_json([]);
	}
}
