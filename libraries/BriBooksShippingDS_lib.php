<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Dompdf\Dompdf;

final class BriBooksShippingDS_lib {
	public function __construct() {
		$this->CI = &get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('common/Cron_model');
		$this->load->model('shipping/DirectShipments_model');

		$this->direct_shipments_model = $this->CI->DirectShipments_model;
		$this->cron_model = $this->CI->Cron_model;
	}

	public function generateLabelDS($ids = false, $format = 'thermal') {
		$dir = FCPATH . 'uploads/label/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		ini_set("pcre.backtrack_limit", "5000000");

		$shipment_data = [];

		foreach ($ids as $key => $id) {
			if(!empty($direct_shipment_info = $this->direct_shipments_model->get($id))) {
				if (($direct_shipment_info['status'] == '1') && ($direct_shipment_info = self::getShipmentDataDS($id))) {
					$shipment_data[] = $direct_shipment_info;
				}
			}
		}

		// pr(_get_label_barcode('12345', 85, 30), 1);

		$html = $this->load->view('backend/admin/order/order_label', array('shipments' => $shipment_data, 'format' => $format), true);

		// print_r($html); die;

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		// (Optional) Setup the paper size and orientation
		// $dompdf->setPaper('A4', 'potrait');

		$dompdf->set_paper(array(0,0,296,450));

		// Render the HTML as PDF
		$dompdf->render();

		$file = 'uploads/label/'.date('Y-m-d').'-'.@$ids[0].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);

		return base_url($file);
	}

	public function getShipmentDataDS($direct_shipment_id = false) {
		if (empty($direct_shipment_id))
			return false;

		if(empty($direct_shipment_info = $this->direct_shipments_model->get($direct_shipment_id))) {
			return false;
		}

		if ($direct_shipment_info['status'] == '0' || empty($direct_shipment_info['awb_code'])) {
			return false;
		}

		$shipment_info_json = !empty($direct_shipment_info['shipping_tracking_info']) ? json_decode($direct_shipment_info['shipping_tracking_info'], 1) : [];

		// pr($shipment_info_json, 1);

		$order = new stdClass();
		$order->order_id = $shipment_info_json['shipment_id'];
		$order->order_date = strtotime($direct_shipment_info['date_shipment']);
		$order->shipping_fname = $direct_shipment_info['consignee_name'];
		$order->shipping_lname = '';
		$order->shipping_company_name = '';
		$order->shipping_address = $direct_shipment_info['consignee_address1'];
		$order->shipping_address_2 = $direct_shipment_info['consignee_address2'];
		$order->shipping_address_3 = $direct_shipment_info['consignee_address3'];
		$order->shipping_city = $direct_shipment_info['consignee_city'];
		$order->shipping_state = $direct_shipment_info['consignee_state'];
		$order->shipping_country = 'India';
		$order->shipping_phone = $direct_shipment_info['consignee_mobile'];
		$order->shipping_zip = $direct_shipment_info['consignee_pincode'];
		$order->order_payment_type = 'prepaid';
		$order->order_amount = $direct_shipment_info['declared_value'];
		$order->package_weight = $direct_shipment_info['actual_weight']*1000;
		$order->shipping_charges = '0.0';
		$order->cod_charges = '';
		$order->tax_amount = '';
		$order->discount = '';
		$order->package_length = $direct_shipment_info['length'] ?? '10';
		$order->package_height = $direct_shipment_info['height'] ?? '10';
		$order->package_breadth = $direct_shipment_info['breadth'] ?? '10';
		$order->channels_brand_logo = '';
		$order->currency_code = 'INR';
		$order->currency_symbol = 'Rs';

		$product_arr = [];
		$product_arr[] = (object) [
			'product_name'		=> $direct_shipment_info['type'],
			'product_sku'		=> 'NA',
			'product_qty'		=> (int)$direct_shipment_info['quantity'],
			'product_price'		=> round($direct_shipment_info['declared_value']/$direct_shipment_info['quantity'], 1),
		];

		$products = (object)$product_arr;

		$shipment = new stdClass();
		$shipment->id = $direct_shipment_info['id'];
		$shipment->awb_number = $direct_shipment_info['awb_code'];
		$shipment->routing_code = $shipment_info_json['route_code'] ?? '';
		$shipment->shipment_info_1 = '';
		$shipment->is_rto_different = '';
		$shipment->shipment_date = strtotime($direct_shipment_info['date_shipment']);

		$courier = new stdClass();
		$courier->id = '';
		$courier->code = 'bluedart';
		$courier->carrier_id = '';
		$courier->carrier_code = 'Bluedart';
		$courier->display_name = 'Bluedart';
		$courier->vendor_name = 'Bluedart';

		$pickup_location_info = PICKUP_ADDRESS;

		$warehouse = new stdClass();
		$warehouse->name = $pickup_location_info['name'];
		$warehouse->contact_name = $pickup_location_info['contact_name'];
		$warehouse->address_1 = $pickup_location_info['address_1'];
		$warehouse->address_2 = $pickup_location_info['address_2'];
		$warehouse->city = $pickup_location_info['city'];
		$warehouse->state = $pickup_location_info['state'];
		$warehouse->country = 'India';
		$warehouse->zip = $pickup_location_info['zip'];
		$warehouse->phone = $pickup_location_info['phone'];
		$warehouse->gst_number = '06AABCY5072A1ZN';
		$warehouse->support_phone = '';
		$warehouse->support_email = '';
		$warehouse->hide_label_products = '';
		$warehouse->hide_label_address = '';
		$warehouse->hide_label_pickup_mobile = '';
		$warehouse->logo = base_url('assets/images/logo-black.png');

		$rto_warehouse = $warehouse;

		$user = new stdClass();
		$user->id = '';
		$user->support_category = '';

		$company = new stdClass();
		$company->cmp_logo = '';

		$channel_brand_logo = [];

		$return = array(
			'order' => $order,
			'products' => $products,
			'shipment' => $shipment,
			'courier' => $courier,
			'warehouse' => $warehouse,
			'rto_warehouse' => $rto_warehouse,
			'company' => $company,
			'user' => $user,
			'channels_brand_logo' => (object)$channel_brand_logo
		);

		// pr($return, 1);

		return (object) $return;
	}

	public function cancelShipmentDS($direct_shipment_id = false) {
		if (empty($direct_shipment_id))
			return false;

		if(empty($direct_shipment_info = $this->direct_shipments_model->get($direct_shipment_id))) {
			return false;
		}

		if ($direct_shipment_info['status'] == '0' || empty($direct_shipment_info['awb_code']) || !empty($direct_shipment_info['cancel_manager_id'])) {
			return false;
		}

		$this->load->library('Bluedart_lib');
		$bluedart_lib = new Bluedart_lib();

		if(empty($response = $bluedart_lib->cancelAWB($direct_shipment_info['awb_code']))) {
			$this->error = $bluedart_lib->error ?? 'Unable to cancel AWB';
			return false;
		}

		return true;
	}
}
