<?php defined('BASEPATH') or exit('No direct script access allowed');

trait GokwikNdrOrder {
    private function _gokwikNdrAction($order = [], $slot='', $phone = '') {
       
        $shipping_tracking_info = json_decode($order['shipping_tracking_info'], true);

        if (empty($shipping_tracking_info['awb_code'])) {
            return [
                'status'    => false,
                'message'     => _l('order_not_found'),
            ];
        }

        log_kb(['GokwikNdrOrder::awb: ' => $shipping_tracking_info['awb_code']]);

        $awb = $shipping_tracking_info['awb_code'];

        $url = 'https://api.gokwik.co/kwikship/api/v1/ndr/action/'. urlencode($awb);

        if ($slot === 'tomorrow') {

            $deferred_date  = date('Y-m-d',strtotime('+1 day'));
            $comments       = _l('Customer_is_available_tomorrow');

        } elseif ($slot === '3days') {

            $deferred_date  = date('Y-m-d',strtotime('+1 days'));
            $comments       = _l('Customer_is_available_within_3_days');

        } else {

            return [
                'status'    => false,
                'message'   => _l('Invalid_availability_slot'),
            ];
        }

        $payload = [
            'action'        => (string) 're-attempt',
            'comments'      => (string) $comments,
            'phone'         => (string) $phone,
            'deferred_date' => (string) $deferred_date,
        ];

        /*
        * Remove empty optional fields
        */
        $payload = array_filter(
            $payload,
            function ($value) {
                return $value !== null && $value !== '';
            }
        );

        return self::_gokwikCurl($url,'POST',$payload);
    }

    private function _gokwikCurl($url, $method = 'POST', $payload = []) {
        $ch = curl_init();

        $headers = [
            'gk-app-id: c45721cec9325281c030ad8a41a7eba8',
            'gk-app-secret: d8b4fea5d88642dbf11fa23c5f0c1daa',
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        if (!empty($payload)) {
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($payload)
            );
        }

       $raw_response = curl_exec($ch);

		if (curl_errno($ch)) {
			throw new Exception(curl_error($ch));
		}

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        log_kb(['GokwikNDROrder::raw_response: ' =>  $raw_response]);
        curl_close($ch);

        $response = json_decode($raw_response, true);

        return $response;
    }

}