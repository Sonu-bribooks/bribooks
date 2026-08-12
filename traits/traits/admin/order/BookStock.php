<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BookStock {
	public function book_stocks($param1 = '', $param2 = '') {
		if ($param1 == 'add') {
			if ($this->book_stock_model->get_all([
				'book_id'	=> $this->input->post('book_id'),
				'version'	=> $this->input->post('version'),
				'option'	=> $this->input->post('option'),
			])['rows'] ?? []) {
				$this->session->set_flashdata('error_message', _l('stock_exists_for_this_book'));
			} else {
				$this->book_stock_model->add(array_merge(
					$this->input->post(),
					[
						'manager_id' => $this->session->userdata('user_id'),
					]
				));

				$this->book_stock_log_model->add([
					'manager_id'=> (int)$this->session->userdata('user_id'),
					'book_id'	=> (int)$this->input->post('book_id'),
					'version'	=> (int)$this->input->post('version'),
					'option'	=> $this->input->post('option'),
					'quantity'	=> (int)$this->input->post('quantity'),
					'status'	=> 1,
				]);
			}

			redirect(site_url('admin/book_stocks'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->book_stock_model->edit($param2, $this->input->post());

			$this->book_stock_log_model->add([
				'manager_id'=> (int)$this->session->userdata('user_id'),
				'book_id'	=> (int)$this->input->post('book_id'),
				'version'	=> (int)$this->input->post('version'),
				'option'	=> $this->input->post('option'),
				'quantity'	=> (int)$this->input->post('quantity'),
				'status'	=> 0,
			]);

			redirect(site_url('admin/book_stocks'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->book_stock_model->enableDisable($param2);
			redirect(site_url('admin/book_stocks'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->book_stock_model->delete($param2);
			redirect(site_url('admin/book_stocks'), 'refresh');
		}

		$data['page_name'] 		= 'book_stocks/index';
		$data['page_title'] 	= _l('book_stocks');
		$data['action_add'] 	= site_url('admin/book_stock_form/add');
		$data['action_ajax'] 	= site_url('admin/ajax_book_stocks');

		$this->load->view('backend/index', $data);
	}

	public function book_stock_form($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$data['page_title'] 	= _l('book_stock_add');
			$data['action'] 		= base_url('admin/book_stocks/add');
		} elseif ($param1 == 'edit') {
			$data['book_stock_id'] 	= (int)$param2;
			$data['details'] 		= $this->book_stock_model->get($param2);
			$data['page_title'] 	= _l('book_stock_edit');
			$data['action'] 		= base_url('admin/book_stocks/edit/' . (int)$param2);
		}

		$data['page_name'] 			= 'book_stocks/form';

		$this->load->view('backend/index', $data);
	}

	public function ajax_book_stocks() {
		$json['data'] = [];

		$this->load->model('printer/PrinterStats_model', 'printer_stats_model');

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->book_stock_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$new_orders = $this->printer_stats_model->quantityCount([
				'book_id'			=> $result['book_id'],
				'version'			=> $result['version'],
				'option'			=> $result['option'],
				'order_status'		=> 1,
			]) ?? 0;

			$in_print_orders = $this->printer_stats_model->printerStats([
				'book_id'			=> $result['book_id'],
				'version'			=> $result['version'],
				'option'			=> $result['option'],
				'status'			=> 1,
			]) ?? 0;

			$json['data'][] = [
				'sn'			=> $filter_data['start'] + 1 + $key,
				'id'			=> $result['id'],
				'sku'			=> _o_b_code($result['book_id'], $result['version'], $result['option']),
				'book'			=> $result['book'],
				'version'		=> $result['version'],
				'option'		=> $result['option'],
				'quantity'		=> $result['quantity'],
				'orders'		=> vsprintf('<span class="badge badge-info">%s new</span><br><span class="badge badge-warning">%s in print</span>', [
					$new_orders,
					$in_print_orders,
				]),
				'status'		=> _sd($result['quantity'] ? 1 : 0),
				'date_added'	=> formatDate($result['date_added']),
				'date_modified'	=> formatDate($result['date_modified']),
				'actions'		=> [
					'id' 		=> $result['id'],
					'status' 	=> $result['status'] ?? 0
				],
			];
		}

		output_json($json);
	}

	public function ajax_filter_books() {
		$json['items'] = [];

		if (empty($json['error']) && $this->input->get('search')) {
			foreach ($this->book_model->get_all([
				'search'	=> $this->input->get('search'),
				'status'	=> 1,
			])['rows'] ?? [] as $item) {
				$json['items'][] = [
					'id'		=> $item['id'],
					'text'		=> $item['name'] . ' by  ' . $item['author_name'],
					'version'	=> $item['version'],
				];
			}
		}

		output_json($json);
	}
}
