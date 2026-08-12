<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait InvoiceDownload {
	private function _subscriptionInvoice($id, $return = false) {
		$info = $this->user_subscription_model->get($id);
		$user_info = $this->student_model->get($info['user_id']);
		$subscription_info = $this->subscription_plan_model->get($info['subscription_plan_id']);

		$order_info = $subscription_info['special']
			? $this->competition_order_model->get($info['order_id'])
			: $this->subscription_order_model->get($info['order_id'])
		;

		$data['user'] = [
			'name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
			'email'		=> $user_info['email'],
		];

		$data['order'] = $order_info;
		$data['plan'] = $subscription_info;

		$html = $this->load->view('common/invoice/invoice_subscription', $data, true);

		if ($return) {
			return output_pdf($html, date('Y_m_d_H_i_s', strtotime($order_info['date_added'])), false);
		} else {
			output_pdf($html, date('Y_m_d_H_i_s', strtotime($order_info['date_added'])));
		}
	}

	private function _orderInvoice($id, $return = false) {
		$info = $this->order_model->get($id);
		$user_info = $this->student_model->get($info['user_id']);
		$address_info = $this->address_model->getByID($info['address_id']);
		$products = $this->order_model->getProducts($info['id']);

		if (!empty($info['parent_order_id'])) {
			$this->load->model('order/OrderClone_model', 'order_clone_model');
			$order_clone_info = $this->order_clone_model->getByIds([
				'parent_order_id'	=> $info['parent_order_id'],
				'clone_order_id'	=> $id
			]);

			if (!empty($order_clone_info = $order_clone_info[0])) {
				if ($order_clone_info['shipment_type'] == '1') {
					$info['total'] = $order_clone_info['total'];
					$info['subtotal'] = $order_clone_info['subtotal'];
					$info['shipping_cost'] = $order_clone_info['shipping_cost'];
				} elseif ($order_clone_info['shipment_type'] == '2') {
					foreach ($products as &$value) {
						$value['total'] = '0.00';
					}
				}
			}
		}

		$data['order'] = $info;
		$data['address'] = $address_info;
		$data['products'] = $products;

		if ($info['order_type'] == 3) {
			$html = $this->load->view('common/invoice/virtual_invoice_order', $data, true);
		} else {
			$html = $this->load->view('common/invoice/invoice_order', $data, true);
		}

		if ($return) {
			return output_pdf($html, date('Y_m_d_H_i_s', strtotime($info['date_added'])), false);
		} else {
			output_pdf($html, date('Y_m_d_H_i_s', strtotime($info['date_added'])));
		}
	}

	private function _orderInvoicesg($id, $return = false) {
		$info = $this->order_model->get($id);
		$user_info = $this->student_model->get($info['user_id']);
		$address_info = $this->address_model->getByID($info['address_id']);
		$products = $this->order_model->getProducts($info['id']);

		if (!empty($info['parent_order_id'])) {
			$this->load->model('order/OrderClone_model', 'order_clone_model');
			$order_clone_info = $this->order_clone_model->getByIds([
				'parent_order_id'	=> $info['parent_order_id'],
				'clone_order_id'	=> $id
			]);

			if (!empty($order_clone_info = $order_clone_info[0])) {
				if ($order_clone_info['shipment_type'] == '1') {
					$info['total'] = $order_clone_info['total'];
					$info['subtotal'] = $order_clone_info['subtotal'];
					$info['shipping_cost'] = $order_clone_info['shipping_cost'];
				} elseif ($order_clone_info['shipment_type'] == '2') {
					foreach ($products as &$value) {
						$value['total'] = '0.00';
					}
				}
			}
		}

		$data['order'] = $info;
		$data['address'] = $address_info;
		$data['products'] = $products;

		$html = $this->load->view('common/invoice/invoice_order_sg', $data, true);

		if ($return) {
			return output_pdf($html, date('Y_m_d_H_i_s', strtotime($info['date_added'])), false);
		} else {
			output_pdf($html, date('Y_m_d_H_i_s', strtotime($info['date_added'])));
		}
	}

	private function _orderManifest($id, $return = false) {
		$info = $this->order_model->get($id);
		$user_info = $this->student_model->get($info['user_id']);
		$address_info = $this->address_model->getByID($info['address_id']);

		$data['order'] = $info;
		$data['barcode'] = self::_getAdminBarcode($info['order_code']);
		$data['address'] = $address_info;
		$data['products'] = $this->order_model->getProducts($info['id']);

		$html = $this->load->view('common/invoice/manifest_order', $data, true);
		// echo $html;die;
		if ($return) {
			return output_manifest($html, date('Y_m_d_H_i_s', strtotime($info['date_added'])), false);
		} else {
			output_manifest($html, date('Y_m_d_H_i_s', strtotime($info['date_added'])));
		}
	}
}
