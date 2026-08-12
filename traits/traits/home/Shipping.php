<?php defined('BASEPATH') OR exit('No direct script access allowed');

load_trait('models/alert');

trait Shipping {
	use DirectShipmentsAlert;

	public function createShipments() {
		$dir = FCPATH . 'uploads/bluedart_labels/'.date('Y-m-d');

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		if (!empty($results = $this->db->where(['status' => 0, 'LENGTH(date_added)' => NULL])->order_by('rand()')->limit(20)->get('bluedart_shipments')->result_array())) {

			$this->load->library('Bluedart_lib', 'bluedart_lib');

			foreach ($results as $shipment) {
				$shipment_id = !empty($shipment['CreditReferenceNo']) ? $shipment['CreditReferenceNo'] : (((ENVIRONMENT == 'production') ? 'BB/' : 'TEST-') . sprintf('%08d', $shipment['id']));

				$data = array(
		            'order' 				=> array(
		                'id' 				=> $shipment['id'],
		                'shipment_id'		=> $shipment_id,
		                'payment_method' 	=> 'prepaid',
		                'total' 			=> $shipment->declared_value,
		                'weight'	 		=> !empty($shipment['actual_weight']) ? $shipment['actual_weight'] : '0.5',
		                'length' 			=> !empty($shipment['length']) ? $shipment['length'] : '5',
		                'height' 			=> !empty($shipment['height']) ? $shipment['height'] : '5',
		                'breadth' 			=> !empty($shipment['breadth']) ? $shipment['breadth'] : '5',
		                'product' 			=> !empty($shipment['commodity_detail']) ? $shipment['commodity_detail'] : 'BOOKS',
		                'instruction' 		=> !empty($shipment['special_instruction']) ? $shipment['special_instruction'] : 'NDOX'
		            ),
		            'customer' 				=> array(
		                'name' 				=> $shipment['consignee_name'],
		                'attention_name' 	=> $shipment['consignee_attention'],
		                'address' 			=> _remove_special_charcater($shipment['consignee_address1']),
		                'address_2' 		=> _remove_special_charcater($shipment['consignee_address2']),
		                'address_3' 		=> _remove_special_charcater($shipment['consignee_address3']),
		                'city' 				=> $shipment['consignee_city'] ?? '',
		                'state' 			=> $shipment['consignee_state'] ?? '',
		                'zip' 				=> $shipment['consignee_pincode'],
		                'phone' 			=> $shipment['consignee_mobile'],
		                'email' 			=> $shipment['consignee_email_id']
		            ),
		            'pickup' 				=> PICKUP_ADDRESS
		        );

				$shipping_info = $this->bluedart_lib->createOrder($shipment_data);

				$this->db->where('id', $shipment['id']);
				$this->db->update('bluedart_shipments', [
					'status'					=> empty($shipping_info[$shipment_id]['error']) ? 1 : 0,
					'awb_code'					=> !empty($shipping_info[$shipment_id]['awb_code']) ? $shipping_info[$shipment_id]['awb_code'] : '',
					'shipping_tracking_info'	=> !empty($shipping_info[$shipment_id]) ? json_encode($shipping_info[$shipment_id]) : '',
					'date_added'				=> date('Y-m-d H:i:s'),
					'date_modified'				=> date('Y-m-d H:i:s')
				]);
			}
		}
	}

	public function createDirectShipments() {
		if (!empty($shipments = $this->db->where([
			'is_duplicate' => 0,
			'status' => 0,
			'_deleted' => 0
		])->order_by('rand()')->limit(1)->get('direct_shipments')->result())) {
			$this->load->library('Bluedart_lib', 'bluedart_lib');

			foreach ($shipments as $shipment) {
				$shipment_id = ((ENVIRONMENT == 'production') ? 'BB/' : 'TEST-') . sprintf('%08d', $shipment->id);

				$data = array(
		            'order' 				=> array(
		                'id' 				=> $shipment->id,
		                'shipment_id'		=> $shipment_id,
		                'payment_method' 	=> 'prepaid',
		                'total' 			=> $shipment->declared_value,
		                'weight'	 		=> !empty($shipment->actual_weight) ? $shipment->actual_weight : '0.5',
		                'length' 			=> !empty($shipment->length) ? $shipment->length : '5',
		                'height' 			=> !empty($shipment->height) ? $shipment->height : '5',
		                'breadth' 			=> !empty($shipment->breadth) ? $shipment->breadth : '5',
		                'product' 			=> !empty($shipment->commodity_detail) ? $shipment->commodity_detail : 'BOOKS',
		                'instruction' 		=> !empty($shipment->special_instruction) ? $shipment->special_instruction : 'NDOX'
		            ),
		            'customer' 				=> array(
		                'name' 				=> $shipment->consignee_name,
		                'attention_name' 	=> $shipment->consignee_attention,
		                'address' 			=> _remove_special_charcater($shipment->consignee_address1),
		                'address_2' 		=> _remove_special_charcater($shipment->consignee_address2),
		                'address_3' 		=> _remove_special_charcater($shipment->consignee_address3),
		                'city' 				=> $shipment->consignee_city ?? '',
		                'state' 			=> $shipment->consignee_state ?? '',
		                'zip' 				=> $shipment->consignee_pincode,
		                'phone' 			=> $shipment->consignee_mobile,
		                'email' 			=> $shipment->consignee_email_id
		            ),
		            'pickup' 				=> PICKUP_ADDRESS
		        );

				// pr($data, 1);

				$bluedart_lib = new bluedart_lib();
				$shipping_info = $bluedart_lib->createOrder($data);

				$this->db->where('id', $shipment->id);
				$this->db->update('direct_shipments', [
					'status'					=> empty($shipping_info[$shipment_id]['error']) ? 1 : 0,
					'awb_code'					=> !empty($shipping_info[$shipment_id]['awb_code']) ? $shipping_info[$shipment_id]['awb_code'] : '',
					'shipping_tracking_info'	=> !empty($shipping_info[$shipment_id]) ? json_encode($shipping_info[$shipment_id]) : '',
					'date_modified'				=> date('Y-m-d H:i:s'),
					'date_shipment'				=> date('Y-m-d H:i:s')
				]);
			}
		}
	}

	public function cancelDirectShipments() {
		return;
		
		if (!empty($shipments = $this->db->where([
			'status' 			=> 1,
			'_deleted' 			=> 0,
			'manager_id' 		=> 0,
			'cancel_manager_id' => 0,
			'date_shipment >= '	=> '2023-09-30 00:00:00',
			'date_shipment <= '	=> '2023-09-30 23:59:59'
		])->get('direct_shipments')->result())) {
			// pr(count($shipments), 1);
			$this->load->library('Bluedart_lib', 'bluedart_lib');

            $this->load->model('shipping/DirectShipments_model', 'direct_shipments_model');

			foreach ($shipments as $shipment) {
				if(empty($shipment->awb_code))
					continue;

				$bluedart_lib = new bluedart_lib();
				if(!empty($response = $bluedart_lib->cancelAWB($shipment->awb_code))) {
		            /*$this->direct_shipments_model->edit($shipment->id, [
                        'cancel_remark'     => json_encode("Cancelled AWB as per Yogesh said."),
                        'date_cancel'       => date('Y-m-d H:i:s'),
                        'cancel_manager_id' => 149071
                    ]);*/

                    pr($shipment->awb_code);
		        } else if (!empty($bluedart_lib->error) && ($bluedart_lib->error == 'Cannot RTO, Shipment already delivered')) {
		        	/*$this->direct_shipments_model->edit($shipment->id, [
                        'cancel_remark'     => json_encode("Cancelled AWB as per Yogesh said."),
                        'date_cancel'       => date('Y-m-d H:i:s'),
                        'cancel_manager_id' => 149071
                    ]);*/

                    pr($shipment->awb_code);
		        } else {
		        	pr($bluedart_lib->error ?? '');
		        }
			}
		}
	}
}
