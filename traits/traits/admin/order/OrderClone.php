<?php defined('BASEPATH') or exit('No direct script access allowed');

trait OrderClone {
	public function order_clone($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if (!$id) {
			$this->session->set_flashdata('error_message', 'Invalid request.');
			redirect(base_url('admin/orders'), 'refresh');
		}

		$order_info = $this->order_model->get($id);

		if (empty($order_info) || (strtolower($order_info['order_type']) == 3) || !empty($order_info['parent_order_id']) || in_array($order_info['status'], [0,2,3,8,91,92])) {
			$this->session->set_flashdata('error_message', 'Invalid request.');
			redirect(base_url('admin/orders'), 'refresh');
		}

		$order_clone_results = $this->order_clone_model->get_all([
			'parent_order_id'   => $id
		]);

		$filter_data['option_type'] = [1];

		$last_clone_order = $this->db
			->order_by('id', 'DESC')
			->get_where('order_clone', [
				'parent_order_id'	=> (int)$order_info['id'],
			])
			->row_array()
		;

		$data['parent_order_status'] = !empty($last_clone_order)
			? $last_clone_order['order_status']
			: $order_info['status']
		;

		$data['new_order_code'] = $order_info['order_code'] . '-C' . ($order_clone_results['total'] + 1);
		$data['order_info']		= $order_info;
		$data['products']		= $this->order_model->getProducts($id, $filter_data);
		$data['address']		= $this->address_model->getByID($order_info['address_id']);

		$data['page_name']	  	= 'order/order_clone';
		$data['page_title']	 	= _l('Order Clone');
		$data['action']		 	= base_url('admin/order_clone_edit/'.$id);

		$this->load->view('backend/index', $data);
	}

	public function order_book_detail() {
		$json = [];

		if (empty($old_book_id = $this->input->post('old_book_id')) || empty($book_id = $this->input->post('book_id'))) {
			$json['error'] = _l('error_book_id');
		}

		if (!$json) {
			if (empty($old_book_info = $this->book_model->get($old_book_id)) || empty($book_info = $this->book_model->get($book_id))) {
				$json['error'] = _l('error_book_id');
			} else {
				$old_book_pages = $this->book_model->getTotalPages($old_book_id) * 2 + 5;

				$pages = $this->book_model->getTotalPages($book_id) * 2 + 5;

				$free_page_limit = $this->config->item('site_free_page_limit') ?? 80;

				if(($old_book_pages >= $pages) || ($pages <= $free_page_limit)) {
					$book_info['sku'] = vsprintf('%sV%s%s', [
						$book_id,
						$book_info['version'],
						'P'
					]);

					$book_info['pages'] = $pages;

					$json['book']	   = $book_info;
					$json['success']	= _l('success');
				} else {
					$json['error']	  = _l('error_book_pages');
				}
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function order_clone_edit($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if (!$id) {
			$this->session->set_flashdata('error_message', 'Invalid request.');
			redirect(base_url('admin/orders'), 'refresh');
		}

		$this->load->library('form_validation');

		$this->form_validation->set_rules('customer_name', _l('customer_name'), 'trim|required');
		$this->form_validation->set_rules('customer_mobile', _l('customer_mobile'), 'trim|required');
		$this->form_validation->set_rules('customer_state', _l('customer_state'), 'trim|required');
		$this->form_validation->set_rules('customer_city', _l('customer_city'), 'trim|required');
		$this->form_validation->set_rules('customer_address', _l('customer_address'), 'trim|required');
		$this->form_validation->set_rules('customer_zipcode', _l('customer_zipcode'), 'trim|required');

		if (!$this->form_validation->run()) {
			$this->session->set_flashdata('error_message', 'Invalid request.');
			redirect(base_url('admin/order_clone/'.$id), 'refresh');
		}

		$order_info = $this->order_model->get($id);

		self::_validateClone($order_info['id'], $this->input->post());

		if (
			empty($order_info) || (strtolower($order_info['order_type']) == 3) ||
			!empty($order_info['parent_order_id']) ||
			!in_array($order_info['status'], [1, 15, 21, 94])
		) {
			$this->session->set_flashdata('error_message', 'Invalid request.');
			redirect(base_url('admin/orders'), 'refresh');
		}

		if (
			in_array($order_info['status'], [1]) &&
			$this->session->userdata('role_id') != 1
		) {
			$this->session->set_flashdata('error_message', _l('not_authorized_for_the_new_orders'));
			redirect(base_url('admin/orders'), 'refresh');
		}

		$last_clone_order = $this->db
			->order_by('id', 'DESC')
			->get_where('order_clone', [
				'parent_order_id'	=> (int)$order_info['id'],
			])
			->row_array()
		;

		$parent_order_status = !empty($last_clone_order)
			? $last_clone_order['order_status']
			: $order_info['status']
		;

		if ($parent_order_status == 15 && !empty($last_clone_order)) {
			$this->session->set_flashdata('error_message', _l('not_authorized_to_clone_returned_order'));
			redirect(base_url('admin/orders'), 'refresh');
		}

		$this->load->library('CloneOrder_lib');

		$this->cloneorder_lib->cloneOrderCreated([
			'order_id'  => $id,
			'data'		=> $this->input->post()
		]);

		$this->session->set_flashdata('flash_message', 'Success');

		redirect(base_url('admin/orders'), 'refresh');
	}

	private function _validateClone($order_id = 0, $data = []) {
		$products 			= $this->order_product_model->get_all(['order_id' => (int)$order_id])['rows'] ?? [];
		$cloned_products 	= $this->order_clone_model->get_all(['parent_order_id' => (int)$order_id])['rows'] ?? [];
		$new_products 		= $data['products'];

		$quantity 			= array_sum(array_column($products, 'quantity'));
		$cloned_quantity 	= array_reduce($cloned_products, function($acc, $item) {
			$products = json_decode($item['products'], true);
			$quantity = array_reduce($products, function($acc, $item) {
				$acc += (!empty($item['checkbox']) ? $item['quantity'] : 0);
				return $acc;
			});
			$acc += $quantity;
			return $acc;
		});
		$new_quantity 		= array_reduce($new_products, function($acc, $item) {
			$acc += (!empty($item['checkbox']) ? $item['quantity'] : 0);
			return $acc;
		});

		log_kb(compact(['quantity', 'cloned_quantity', 'new_quantity', 'products', 'cloned_products', 'new_products']));

		if (($cloned_quantity + $new_quantity) > $quantity) {
			$this->session->set_flashdata('error_message', _li('Quantity_exceeds'));
			redirect(base_url('admin/order_clone/' . (int)$order_id), 'refresh');
		}
	}
}
