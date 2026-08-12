<?php defined('BASEPATH') or exit('No direct script access allowed');

trait DirectShipmentsAlert
{
	public function directShipmentsCron() {
		$this->load->model('shipping/DirectShipments_model', 'direct_shipments_model');

		if (!empty($shipments = $this->direct_shipments_model->get_all([
			'is_duplicate' 		=> 0,
			'status'			=> 0,
			'cancel_manager_id'	=> 0
		])['rows'] ?? [])) {
			$this->load->library('Bluedart_lib', 'bluedart_lib');
			
			foreach ($shipments as $shipment) {
				$shipment_id = ((ENVIRONMENT == 'production') ? 'BB-DS-' : 'TEST-') . sprintf('%08d', $shipment['id']);

				$data = array(
		            'order' 				=> array(
		                'id' 				=> $shipment['id'],
		                'shipment_id'		=> $shipment_id,
		                'payment_method' 	=> 'prepaid',
		                'total' 			=> !empty($shipment['declared_value']) ? $shipment['declared_value'] : '0.0',
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

				$bluedart_lib = new Bluedart_lib();
				$shipping_info = $bluedart_lib->createOrder($data);

				$request = '';
				if(isset($shipping_info[$shipment_id]['request'])) {
					$request = $shipping_info[$shipment_id]['request'];
					unset($shipping_info[$shipment_id]['request']);
				}

				$this->db->where('id', $shipment['id']);
				$this->db->update('direct_shipments', [
					'status'					=> empty($shipping_info[$shipment_id]['error']) ? 1 : 0,
					'awb_code'					=> !empty($shipping_info[$shipment_id]['awb_code']) ? $shipping_info[$shipment_id]['awb_code'] : '',
					'shipping_info'				=> $request,
					'shipping_tracking_info'	=> !empty($shipping_info[$shipment_id]) ? json_encode($shipping_info[$shipment_id]) : '',
					'date_modified'				=> date('Y-m-d H:i:s'),
					'date_shipment'				=> date('Y-m-d H:i:s')
				]);
			}
		}
	}
}
