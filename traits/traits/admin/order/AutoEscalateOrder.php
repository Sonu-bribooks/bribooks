<?php defined('BASEPATH') or exit('No direct script access allowed');

trait AutoEscalateOrder {

	private $currency_id = 47;

	private function _initAutoEscalateOrderPageData($param = []) {
		$data['page_name'] 		= 'order/index';
		$data['heading'] 		= _l(sprintf('%s_orders', $param['status']));
		$data['page_title'] 	= _l($param['status']);
		$data['order_type'] 	= $param['type'] ?? 'domestic';
		$data['navigation'] 	= 'nav';
		$data['action_ajax'] 	= base_url('admin/' . $param['action_ajax']);
		$data['action_print'] 	= base_url('admin/' . $param['action_print']);;
		$data['action_reprint'] = base_url('admin/' . $param['action_reprint']);;

		$data['timestamp_start']= strtotime('-30 days', time());
		$data['timestamp_end']	= time();

		$data['nav_base_url']	= base_url('admin/' . $param['nav_base_url'] . '/');
		$data['nav_tabs']['pre']= $param['is_bw'] ? [
			[
				'color'	=> 'transparent',
				'url'	=> base_url('admin/bw_orders'),
				'name'	=> _l('all_bw_orders'),
				'id'	=> 'bw_orders',
			],
		] : [
			[
				'color'	=> 'transparent',
				'url'	=> base_url('admin/all_orders'),
				'name'	=> _l('all_orders'),
				'id'	=> 'all_orders',
			],
			[
				'color'	=> $param['type'] == 'domestic' ? 'info' : 'danger',
				'url'	=> $param['type'] == 'domestic' ? base_url('admin/orders') : base_url('admin/ge_orders'),
				'name'	=> $param['type'] == 'domestic' ? _l('all_domestic') : _l('all_global'),
				'id'	=> $param['type'] == 'domestic' ? 'orders' : 'ge_orders',
			],
		];
		$data['nav_tabs']['post']= [
			[
				'color'	=> 'danger',
				'url'	=> base_url('admin/'. $param['escalated_url']),
				'name'	=> _l('auto_escalated'),
				'id'	=> $param['escalated_url'],
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function auto_escalated_order() {
		self::_initAutoEscalateOrderPageData([
			'status' 			=> 'auto_escalated_order',
			'type' 				=> 'domestic',
			'action_ajax' 		=> 'ajax_auto_escalate_orders',
			'nav_base_url' 		=> 'orders',
			'is_bw' 			=> false,
			'escalated_url'		=> 'auto_escalated_order',
			'action_print'		=> 'ajax_assign_order_to_printer/1',
			'action_reprint'	=> 'ajax_reprint_order/1',
		]);
	}

	public function ge_auto_escalated_order() {
		self::_initAutoEscalateOrderPageData([
			'status' 			=> 'auto_escalated_order',
			'type' 				=> 'global',
			'action_ajax' 		=> 'ajax_auto_escalate_orders/101',
			'nav_base_url' 		=> 'ge_orders',
			'is_bw' 			=> false,
			'escalated_url'		=> 'ge_auto_escalated_order',
			'action_print'		=> 'ajax_assign_order_to_printer/1',
			'action_reprint'	=> 'ajax_reprint_order/1',
		]);
	}

	public function bw_auto_escalated_order(){
		self::_initAutoEscalateOrderPageData([
			'status' 			=> 'auto_escalated_order',
			'type' 				=> 'bw_orders',
			'action_ajax' 		=> 'ajax_auto_escalate_orders/102',
			'nav_base_url' 		=> 'bw_orders',
			'is_bw' 			=> true,
			'escalated_url'		=> 'bw_auto_escalated_order',
			'action_print'		=> 'ajax_assign_order_to_printer/2',
			'action_reprint'	=> 'ajax_reprint_order/2',
		]);
	}

	public function ajax_auto_escalate_orders($status = null) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> trim($this->input->get('search[value]')),
			'sort'				=> 'order.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
			'ne_status'	 		=> 0
		];

		$filter_data['order_type']  	= [1, 2];
		$filter_data['status']  		= 1;
		$filter_data['order_status']  	= 4;

		if ($status == 102) {
			$filter_data['option_type'] = [2];
		} else {
			$filter_data['option_type']	 = [1];
			$filter_data['ne_option_type']  = [2];

			if ($status == 101 && $this->currency_id) {
				$filter_data['ne_currency_id'] = $this->currency_id;
			} else {
				$filter_data['currency_id'] = $this->currency_id;
			}
		}

		$results = $this->auto_escalated_order_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$products   = $this->order_model->getProducts($result['id'], $filter_data);

			$printer_assign_info = $this->printer_assign_log_model->get_all([
				'order_id'	=> $result['id'],
				'option'	=> 'paperback'
			])['rows'];

			$printer_info = !empty($printer_assign_info[0]['printer_id']) ? $this->user_model->get($printer_assign_info[0]['printer_id']) : [];

			$customer_info = $this->user_model->get($result['user_id']);

			$printer_assign_info = !empty($printer_assign_info[0]) ? $printer_assign_info[0] : [];

			$shipping_tracking_info = !empty($result['shipping_tracking_info']) ? json_decode($result['shipping_tracking_info'], true) : '';

			$buttons = [];
			$buttons[] = '<button type="button" class="btn btn-warning btn-sm autoEscalateOrderComment" data-toggle="modal" data-target="#autoEscalateHoldModel" data-id="'.$result["autoEscalateOrderId"].'" data-comment="'.$result['comment'].'">Comment</button>';

			if ($result['escalated_status'] == 1) {
				$buttons[] = '<button type="button" class="btn btn-dark btn-sm closeAutoEscalateOrder" data-id="'.$result["autoEscalateOrderId"].'">Close</button>';
			} else {
				$user =  $this->user_model->get($result['manager_id']);

				if ($user) {
					$full_name 	= ucfirst($user['first_name']).' '.ucfirst($user['last_name']);
					$buttons[] 	= '<strong>'. _l('closed_by').' </strong><span>'.$full_name.'</span> <span>'.$result['date_closed'].'</span>';
				}
			}

			$json['data'][] = [
				'#'					=> self::_renderCheckBox($result, $products),
				'sn'				=> $filter_data['start'] + 1 + $key,
				'order_code'		=> _order_code($result, $shipping_tracking_info, 'printed', 'paperback'),
				'customer'			=> (!empty($customer_info)) ? $customer_info['first_name'] . ' ' . $customer_info['last_name'] . '<br /><small>' . $customer_info['email'] . '<br />' . $customer_info['mobile'] . '</small><br />' . '<strong>(' . $customer_info['source'] . ')</strong>' : '',
				'product'			=> _op_name($products, $result),
				'weight_amount'		=> $result['weight'] . 'gm' . '<br>' . $result['currency_symbol'] . ' ' . $result['total'],
				'status'			=> _sd($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'printer'		  	=> _o_printer($result, $printer_info, $printer_assign_info, 'paperback'),
				'history'			=> $result['comment'],
				'actions'			=> implode('<p style="margin-bottom: 0.4rem;"></p>', $buttons),
				'is_dropshipper'	=> ($result['pickup_location_id'] != $this->config->item('default_pickup_location_id')) ? true : false,
			];
		}

		output_json($json);
	}

	public function ajax_auto_escalate_order_close() {
		$json = [];

		if (!empty($this->input->post('auto_escalate_order_id')) &&
			!empty($order_info = $this->auto_escalated_order_model->get($this->input->post('auto_escalate_order_id')))
		) {
			$this->auto_escalated_order_model->edit($order_info['id'], [
				'status'		=> 0,
				'manager_id'   	=> (int)$this->session->userdata('user_id'),
				'date_closed'	=> date('Y-m-d H:i:s')
			]);

			$json['success'] = _l('auto_escalated_order_closed');
		} else {
			$json['error'] = _l('invalid_order');
		}

		output_json($json);
	}

	public function add_auto_escalate_order_comment() {
		$json = [];

		$request = $this->input->post();

		if (!empty($request['auto_escalate_order_id']) &&
			!empty($request['comment']) &&
			!empty($order_info = $this->auto_escalated_order_model->get($request['auto_escalate_order_id']))
		) {
			$this->auto_escalated_order_model->edit($order_info['id'], [
				'comment'   	=> $request['comment']
			]);

			$json['success'] 	= _l('auto_escalate_order_comment_added');
		} else {
			$json['error'] 		= _l('something_went_wrong!');
		}

		output_json($json);
	}
}
