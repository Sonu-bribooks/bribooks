<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class QaQc_lib {
	public function __construct()
	{
		$this->CI = &get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

        $this->load->model('order/Order_model');
        $this->load->model('printer/PrinterStats_model');
		$this->load->model('printer/PrinterAssignment_model');

        $this->order_model = $this->CI->Order_model;
        $this->printer_stats_model = $this->CI->Custom_model;
		$this->printer_assignment_model = $this->CI->PrinterAssignment_model;

		// $CI->load->model('printer/QaQcLogs_model', 'qa_qc_logs_model');
		// $CI->load->model('printer/QaQcLots_model', 'qa_qc_lots_model');
		// $CI->load->model('order/Order_model', 'order_model');
		// $CI->load->model('order/OrderProduct_model', 'order_product_model');
		// $CI->load->model('book/Book_model', 'book_model');
		// $CI->load->model('book/BookStock_model', 'book_stock_model');
	}

	public function qaqcRejectOrderByBook($data = []) {
        return false;

		pr($data);

		if (empty($data)) {
            $this->error = _l('invalid_details');
            return false;
		}

		if (empty($quantity = (int)$data['quantity']) || ($quantity <= 0)) {
            $this->error = _l('invalid_quantity');
            return false;
		}

		if (empty($action = (int)$data['action']) || ($action != 2)) {
            $this->error = _l('invalid_action');
            return false;
		}

		if (empty($assignment_info = $this->printer_assignment_model->get($data['assignment_id']))) {
            $this->error = _l('invalid_details');
            return false;
		}

		pr($assignment_info);

		$filter_data = [];
		$filter_data['book_id'] = $data['book_id'];
		$filter_data['assignment_id'] = $data['assignment_id'];
		$filter_data['version'] = $data['version'];
		$filter_data['option'] = $data['option'];

		if (empty($qa_qc_lots_info = $this->printer_stats_model->getQaQcCount($filter_data))) {
            $this->error = _l('invalid_details');
			return false;
		}

		pr($qa_qc_lots_info);

		$accepted_count = $qa_qc_lots_info['accepted_quantity'] ?? 0;
		$accepted_count += $qa_qc_lots_info['accepted_short_quantity'] ?? 0;

		if($quantity > $accepted_count) {
            $this->error = _l('invalid_quantity');
            return false;
		}

		$filter_data = [];
		$filter_data['book_id'] = $data['book_id'];
		$filter_data['assignment_id'] = $data['assignment_id'];
		$filter_data['version'] = $data['version'];
		$filter_data['assign_printer_id'] = $assignment_info['printer_id'];

		if (empty($result = $this->printer_stats_model->printerAssignData($filter_data)['rows'][0])) {
            $this->error = _l('invalid_details');
			return false;
		}

		if (empty($order_ids = $result['order_ids'])) {
            $this->error = _l('invalid_details');
			return false;
		}

		$filter_data = [];
		$filter_data['order_ids'] = $order_ids;
		// $filter_data['order_status'] = [8,9,21];

		if (empty($order_results = $this->order_model->get_all($filter_data)['rows'])) {
            $this->error = _l('invalid_details');
			return false;
		}


		pr($order_results);
		pr($order_ids);
		pr($quantity);
		pr($accepted_count);
		pr($result);
		die;
	}
}
