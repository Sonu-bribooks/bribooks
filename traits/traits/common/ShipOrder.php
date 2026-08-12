<?php defined('BASEPATH') or exit('No direct script access allowed');
use Dompdf\Dompdf;

trait ShipOrder {
	public function get_delivery_info() {
		$order_type = $this->input->post('order_type') ?? 'book';

		if (($order_type == 'medallion')) {
			$order_info = $this->medallion_order_model->get($this->input->post('order_id'));
		} elseif (($order_type == 'school')) {
			$order_info = $this->school_order_model->get($this->input->post('order_id'));
		} else {
			$order_info = $this->order_model->get($this->input->post('order_id'));
		}

		if (!empty($order_id = $this->input->post('order_id')) && !empty($order_info)) {
			// BriBooksShipping load
			$this->load->library('BriBooksShipping_lib');

			$couriers = $this->bribooksshipping_lib->getCourierServiceability($order_id, $order_type);

			$data = [
				'order_id'				=> $order_id,
				'order_weight'			=> $order_info['weight'],
				'pickup_location_id'	=> $order_info['pickup_location_id'],
				'couriers'				=> $couriers,
				'order_type'			=> $order_type
			];

			$json['couriers'] = $this->load->view('backend/admin/order/delivery_info', $data, true);
		} else {
			$json['error'] = _l('invalid_order_id');
		}

		output_json($json);
	}

	public function ship() {
		$json = [];

		$this->form_validation->set_rules('order_id', _l('order_id'), 'trim|required|integer|min_length[1]|max_length[20]');
		$this->form_validation->set_rules('courier_id', _l('courier_id'), 'trim|required|integer|min_length[1]|max_length[10]');
		$this->form_validation->set_rules('pickup_location_id', _l('pickup_location_id'), 'trim|required|integer|min_length[1]|max_length[10]');

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if (!$json) {
			$order_id 			= (int)$this->input->post('order_id');
			$order_type 		= $this->input->post('order_type') ?? 'book';
			$courier_id 		= (int)$this->input->post('courier_id');
			$pickup_location_id = (int)$this->input->post('pickup_location_id');

			$vendor 			= $this->input->post('vendor');

			if ($order_type == 'medallion') {
				$order_info = $this->medallion_order_model->get($order_id);
			} elseif ($order_type == 'school') {
				$order_info = $this->school_order_model->get($order_id);
			} else {
				$order_info = $this->order_model->get($order_id);
			}

			if (empty($order_info)) {
				$json['error'] = 'Invalid Request';
			} elseif ($order_info['status'] != 21) {
				$json['error'] = 'Order is already booked';
			} else {
				$this->load->library('BriBooksShipping_lib');

				if ($order_type == 'book') {
					if ($order_info['pickup_location_id'] != $this->config->item('default_pickup_location_id')) {
						$this->printer_assign_log_model->editByOrderId($order_id, [
							'status' 			=> 9
						]);
					}
				}

				if (!empty($response = $this->bribooksshipping_lib->processOrderShipment(
					$order_id,
					$courier_id,
					$pickup_location_id,
					$vendor,
					$order_type
				))) {
					$json['success'] = $response['message'] ?? 'Booked';
				} else {
					$json['error'] = $this->bribooksshipping_lib->error ?? 'Unable to generate AWB';
				}
			}
		}

		output_json($json);
	}

	public function generate_label() {
		$ids 		= $this->input->post('ids');
		$order_type = $this->input->post('order_type') ?? 'book';

		if (!empty($ids)) {
			$json['success'] = base_url('admin/download_generate_label/' . (int)$ids[0] . '/' . $order_type);
		} else {
			$json['error'] = 'Invalid order';
		}

		output_json($json);
	}

	public function download_generate_label($order_id = 0, $order_type = 'book') {
		$this->load->library('BriBooksShipping_lib');
		$this->bribooksshipping_lib->generateLabel($order_id, 'thermal', $order_type);
	}

	public function generate_invoice() {
		$id 		= $this->input->post('id');
		$order_type = $this->input->post('order_type') ?? 'book';

		if (!empty($id)) {
			$json['success'] = base_url('admin/download_generate_invoice/' . (int)$id . '/' . $order_type);
		} else {
			$json['error'] = 'Invalid order';
		}

		output_json($json);
	}

	public function download_generate_invoice($order_id = 0, $order_type = 'book') {
		$this->load->library('BriBooksShipping_lib');
		$this->bribooksshipping_lib->generateInvoice($order_id, $order_type);
	}

	public function generate_manifest() {
		$id = $this->input->post('id');

		if (!empty($id)) {
			$json['success'] = base_url('admin/download_generate_manifest/' . (int)$id);
		} else {
			$json['error'] = 'Invalid order';
		}

		output_json($json);
	}

	public function download_generate_manifest($order_id = 0) {
		$this->load->library('BriBooksShipping_lib');
		$this->bribooksshipping_lib->generateManifest($order_id);
	}

	public function generate_label_ds() {
		$ids = $this->input->post('ids');

		if (!empty($ids)) {
			$this->load->library('BriBooksShippingDS_lib');
			if(!empty($pdf = $this->bribooksshippingds_lib->generateLabelDS($ids))) {
				$json['success'] = $pdf;

				$this->load->model('shipping/DirectShipments_model', 'direct_shipments_model');
				foreach ($ids as $key => $id) {
					if(!empty($direct_shipment_info = $this->direct_shipments_model->get($id))) {
						if (!empty($direct_shipment_info['awb_code']) && ($direct_shipment_info['status'] == '1')) {
							$this->direct_shipments_model->edit($id, [
								'date_label'	=> date('Y-m-d H:i:s'),
								'manager_id'	=> $this->session->userdata('user_id')
							]);
						}
					}
				}
			} else {
				$json['error'] = $this->bribooksshippingds_lib->error ?? 'Unable to generate label';
			}
		} else {
			$json['error'] = 'Invalid order';
		}

		output_json($json);
	}

	public function cancel_shipment_ds() {
		$json['error'] = 'Invalid shipment';

		$id = $this->input->post('shipment_id');

		if (!empty($id)) {
			$this->load->model('shipping/DirectShipments_model', 'direct_shipments_model');

			if (!empty($direct_shipment_info = $this->direct_shipments_model->get($id))) {
				if (empty($direct_shipment_info['cancel_manager_id'])) {
					$this->load->library('BriBooksShippingDS_lib');

					if (!empty($this->bribooksshippingds_lib->cancelShipmentDS($id))) {
						$json['success'] = 'Cancelled shipment';

						$this->direct_shipments_model->edit($id, [
							'cancel_remark'	 	=> json_encode($this->input->post('comment')),
							'date_cancel'	   	=> date('Y-m-d H:i:s'),
							'cancel_manager_id' => $this->session->userdata('user_id')
						]);
					} else {
						$json['error'] = $this->bribooksshippingds_lib->error ?? 'Unable to cancel shipment';
					}
				} else {
					$json['error'] = 'Already cancelled';
				}
			}
		}

		output_json($json);
	}

	public function bulk_cancel_shipment_ds() {
		$json['error'] = 'Unable to cancelled shipments.';

		$ids = $this->input->post('ids');

		if (!empty($ids)) {
			$count_total_ids = count($ids);
			$count_cancel_ids = 0;

			$this->load->library('BriBooksShippingDS_lib');
			$this->load->model('shipping/DirectShipments_model', 'direct_shipments_model');

			foreach ($ids as $id) {
				if (!empty($direct_shipment_info = $this->direct_shipments_model->get($id))) {
					if (empty($direct_shipment_info['cancel_manager_id'])) {
						if (!empty($this->bribooksshippingds_lib->cancelShipmentDS($id))) {
							$this->direct_shipments_model->edit($id, [
								'cancel_remark'	 	=> json_encode('Bulk Cancelled'),
								'date_cancel'	   	=> date('Y-m-d H:i:s'),
								'cancel_manager_id' => $this->session->userdata('user_id')
							]);

							$count_cancel_ids++;
						}
					}
				}
			}

			if ($count_cancel_ids) {
				unset($json['error']);
				$json['success'] = $count_cancel_ids . ' out of ' . $count_total_ids . ' shipment cancelled.';
			}
		}

		output_json($json);
	}
	
	public function downloadManifestPrint() {
		$this->load->library('zip');
		$this->load->model('shipping/PickupData_model', 'pickup_data_model');
		$this->load->model('shipping/Shipment_model', 'shipment_model');
		$this->load->model('shipping/Courier_model', 'courier_model');

		$results = $this->pickup_data_model->get_all([
			'scheduled_date'		=> date('Y-m-d')
		])['rows'];

		$courier_data= [];

		foreach ($results as $key => $item) {
			$shipment_info 	= $this->shipment_model->get($item['shipment_id']);
			$order_info 	= $this->order_model->get($shipment_info['order_id']);
			$order_products = $this->order_model->getProducts($order_info['id']);
			$book_ids = [];
 			array_map(function ($order_products) use (&$book_ids){
				$book_ids[] = $order_products['product_id'];
			}, $order_products);

			$courier_data[$shipment_info['courier_id']][] = [
				'order_id' 		=> $shipment_info['order_id'],
				'order_code' 	=> $order_info['order_code'],
				'awb_number' 	=> $shipment_info['awb_number'],
				'barcode' 		=> _get_label_barcode($shipment_info['awb_number'], 360, 70),
				'sku' 			=> implode(',', $book_ids)
			];
		}

		foreach ($courier_data as $key => $value) {
			$courier_info 	= $this->courier_model->get($shipment_info['courier_id']);

			$html = $this->load->view('common/invoice/manifest_order_print', [
				'courier_name' 	=> $courier_info['name'] ?? 'NA',
				'orders'	  	=> $value
			], true);

			$dompdf = new Dompdf();

			// Load HTML content
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();

			$this->zip->add_data('courier_data_' . $key . '.pdf', $dompdf->output());

		}
		$this->zip->download('manifest.zip');
	}
}
