<?php
defined('BASEPATH') or exit('No direct script access allowed');

final class Bluedart_lib
{
    private $api_url;
    private $licence_key;
    private $login_id;
    private $customer_code;
    private $area_code;
    private $api_version;

    private $account_type;

    public function __construct()
    {
        /*$this->CI =& get_instance();
        $this->db = $this->CI->db;
        $this->session = $this->CI->session;
        $this->load = $this->CI->load;
        $this->config = $this->CI->config;*/

        $this->licence_key = BLUEDART_API['bluedart_licence_key'];
        $this->api_version = BLUEDART_API['bluedart_api_version'];
        $this->login_id = BLUEDART_API['bluedart_login_id'];
        $this->customer_code = BLUEDART_API['bluedart_customer_code'];
        $this->area_code = BLUEDART_API['bluedart_area_code'];
        $api_mode = BLUEDART_API['bluedart_api_mode'];

        $this->api_url = "https://netconnect.bluedart.com/Ver{$this->api_version}/";
        if ($api_mode == 'demo')
            $this->api_url = "https://netconnect.bluedart.com/API-QA/Ver{$this->api_version}/Demo/";
    }

    function createOrder($order = array())
    {
        if (empty($order)) {
            $this->error = 'Invalid Order';
            return false;
        }

        $pickup = $order['pickup'];
        $customer = $order['customer'];
        $ord = $order['order'];
        $rto = $pickup;

        $payment_method = 'prepaid';

        $weight = !empty($ord['weight']) ? $ord['weight'] : '0.5';

        $params = array(
            'Request' => array(
                'Consignee' => array(
                    'ConsigneeAddress1' => !empty($customer['address']) ? $customer['address'] : '',
                    'ConsigneeAddress2' => !empty($customer['address_2']) ? $customer['address_2'] : '',
                    'ConsigneeAddress3' => (!empty($customer['city']) ? $customer['city'] : '') . (!empty($customer['state']) ? ', ' . $customer['state'] : ''),
                    'ConsigneeMobile' => !empty($customer['phone']) ? $customer['phone'] : '',
                    'ConsigneeName' => !empty($customer['name']) ? $customer['name'] : '',
                    'ConsigneePincode' => !empty($customer['zip']) ? $customer['zip'] : '',
                    'ConsigneeAttention' => !empty($customer['attention_name']) ? $customer['attention_name'] : '',
                ),
                'Returnadds' => array(
                    'ReturnAddress1' => $rto['address_1'],
                    'ReturnAddress2' => $rto['address_2'],
                    'ReturnAddress3' => $rto['city'] . ', ' . $rto['state'],
                    'ReturnPincode' => $rto['zip'],
                    'ReturnMobile' => $rto['phone'],
                    'ReturnContact' => $rto['name'],
                ),
                'Services' => array(
                    'ActualWeight' => $weight,
                    'CollectableAmount' => '0',
                    'SubProductCode' => 'P',
                    'Commodity' => array(
                        'CommodityDetail1' => (!empty($ord['product'])) ? $ord['product'] : 'BOOKS',
                        'CommodityDetail2' => '',
                        'CommodityDetail3' => '',
                    ),
                    'CreditReferenceNo' => !empty($ord['shipment_id']) ? $ord['shipment_id'] : rand(1000000000, 9999999999),
                    'DeclaredValue' => (strtolower($ord['instruction']) == 'dox') ? '0' : $ord['total'],
                    'Dimensions' => array(
                        'Breadth' => !empty($ord['breadth']) ? $ord['breadth'] : '5',
                        'Count' => '1',
                        'Height' => !empty($ord['height']) ? $ord['height'] : '5',
                        'Length' => !empty($ord['length']) ? $ord['length'] : '5',
                    ),
                    'PickupDate' => '/Date(' . (strtotime(date("Y-m-d", strtotime("+1 day"))) * 1000) . ')/',
                    'PickupTime' => '1800',
                    'PieceCount' => '1',
                    'ProductCode' => 'A',
                    'ProductType' => (strtolower($ord['instruction']) == 'dox') ? '0' : '1',
                    'PDFOutputNotRequired' => true,
                    /*'PDFOutputNotRequired' => false,
                    'PrinterLableSize' => '3',*/
                    'RegisterPickup' => false,
                    'PackType' => 'L',
                    'SpecialInstruction' => mb_strimwidth($ord['instruction'], 0, 50),
                ),
                'Shipper' => array(
                    'CustomerAddress1' => $pickup['address_1'],
                    'CustomerAddress2' => $pickup['address_2'],
                    'CustomerAddress3' => $pickup['city'] . ', ' . $pickup['state'],
                    'CustomerCode' => $this->customer_code,
                    'CustomerMobile' => $pickup['phone'],
                    'CustomerName' => $pickup['name'],
                    'CustomerPincode' => $pickup['zip'],
                    'IsToPayCustomer' => false,
                    'OriginArea' => $this->area_code,
                    'Sender' => $pickup['name'],
                    'VendorCode' => $this->customer_code
                ),
            ),
            'Profile' => array(
                'Api_type' => 'S',
                'Area' => $this->area_code,
                'Customercode' => $this->customer_code,
                'LicenceKey' => $this->licence_key,
                'LoginID' => $this->login_id,
                'Version' => $this->api_version
            )
        );

        $post_data = json_encode($params);

        $url = $this->api_url . 'ShippingAPI/WayBill/WayBillGeneration.svc/rest/GenerateWayBill';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
            CURLOPT_POSTFIELDS => $post_data,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $result = !empty($response) ? json_decode($response) : [];

        log_kb(['createOrder::'  => [
                'url:: '        => $url,
                'request:: '    => $post_data,
                'response:: '   => $response
            ]
        ]);

        if (empty($result->GenerateWayBillResult) || empty($result->GenerateWayBillResult->Status[0])) {
            $return[$ord['shipment_id']] = array(
                'error' => 'Unable to create shipment',
                'request' => $post_data
            );
            return $return;
        }

        if ($result->GenerateWayBillResult->IsError == '1') {
            if(!empty($ord['shipment_id']) && ($ord['shipment_id'] == $result->GenerateWayBillResult->CCRCRDREF) && !empty($result->GenerateWayBillResult->Status[0]->StatusInformation) && (strpos(strtolower($result->GenerateWayBillResult->Status[0]->StatusInformation), 'waybill already genereated for this creditreferenceno') !== false)) {
                $error_str = str_replace(['Waybill already genereated for this CreditReferenceNo. Waybill No : ',' Dest Area :',' Dest Scrcd :'], ['',',',' / '], $result->GenerateWayBillResult->Status[0]->StatusInformation);

                $error = explode(',', $error_str);

                if(count($error) == 2) {
                    $return[$ord['shipment_id']] = array(
                        'order_id' => $ord['id'],
                        'shipment_id' => $ord['shipment_id'],
                        'awb_code' => $error[0],
                        'status' => 'NEW',
                        'status_code' => '1',
                        'courier_name' => 'Bluedart',
                        'pdf_url' => '',
                        'route_code' => $error[1],
                        'request' => $post_data
                    );
                }
            } else {
                $return[$ord['shipment_id']] = array(
                    'error' => $result->GenerateWayBillResult->Status[0]->StatusInformation,
                    'request' => $post_data
                );   
            }

            return $return;
        }

        if (!empty($awb_number = $result->GenerateWayBillResult->AWBNo)) {
            $pdf_url = '';
            if(!empty($result->GenerateWayBillResult->AWBPrintContent)) {
                $pdf_url = $awb_number.'.pdf';

                $pdf_file = fopen('uploads/bluedart_labels/'.date('Y-m-d').'/'.$pdf_url, 'wb');
                fwrite($pdf_file, implode(array_map('chr', $result->GenerateWayBillResult->AWBPrintContent))); 
                fclose($pdf_file);
            }

            $return[$ord['shipment_id']] = array(
                'order_id' => $ord['id'],
                'shipment_id' => $ord['shipment_id'],
                'awb_code' => $awb_number,
                'status' => 'NEW',
                'status_code' => '1',
                'courier_name' => 'Bluedart',
                'pdf_url' => $pdf_url,
                'route_code' => $result->GenerateWayBillResult->DestinationArea . ' / ' . $result->GenerateWayBillResult->DestinationLocation,
                'request' => $post_data
            );

            return $return;
        }

        $return[$ord['shipment_id']] = array(
            'error' => 'Unable to create shipment'
        );

        return $return;
    }

    function cancelAWB($awb = false)
    {
        if (!$awb)
            return false;

        $params = array(
            'Request' => array(
                'AWBNo' => $awb
            ),
            'Profile' => array(
                'Api_type' => 'S',
                'Area' => $this->area_code,
                'Customercode' => $this->customer_code,
                'LicenceKey' => $this->licence_key,
                'LoginID' => $this->login_id,
                'Version' => $this->api_version
            )
        );

        $post_data = json_encode($params);

        $url = $this->api_url . 'ShippingAPI/WayBill/WayBillGeneration.svc/rest/CancelWaybill';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
            CURLOPT_POSTFIELDS => $post_data,
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $result = !empty($response) ? json_decode($response) : [];

        if (empty($result->CancelWaybillResult)) {
            $this->error = 'Unable to cancel AWB';
            return false;
        }

        if ($result->CancelWaybillResult->IsError == '1') {
            $this->error = $result->CancelWaybillResult->Status[0]->StatusInformation;
            return false;
        }

        return true;
    }
}