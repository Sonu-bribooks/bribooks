<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook extends CI_Controller {
	public function __construct() {
		parent::__construct();

		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('order/Payment_model', 'payment_model');
		$this->load->model('order/Coupon_model', 'coupon_model');
		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('common/CampaignLog_model', 'campaign_log_model');
		$this->load->model('marketing/MarketingSurvey_model', 'marketing_survey_model');
	}

	public function index() {
		log_kb([
			'Webhook Unverified Data:: ' => [
				$this->security->xss_clean($this->input->raw_input_stream),
				$this->input->get_request_header('X-Razorpay-Signature')
			]
		]);

		if (self::_verifySignature(
			$this->input->raw_input_stream,
			$this->input->get_request_header('X-Razorpay-Signature')
		)) {
			$data = json_decode($this->security->xss_clean($this->input->raw_input_stream), true);

			log_kb([
				'Webhook Verified Data:: ' => $data
			]);

			// verify payment information using payment id hit razorpay api
			self::_createPaymentData($data['payload']['payment']['entity'] ?? []);
		}
	}

	public function manualOrderProcess() {
		return;
		$data = [
			'id'		=> 'pay_KLjRc6w6vOVds0', // payment id
			'order_id'	=> 'order_KLjRReDiZchXjk', // order id
		];

		self::_createPaymentData($data);
	}

	private function _verifySignature($payload = NULL, $received_signature = NULL) {
		$webhook_secret = 'q@HPn7Z39-QT5Xn';

		$expected_signature = hash_hmac('sha256', $payload, $webhook_secret);

		return hash_equals($expected_signature, $received_signature);
	}

	private function _createPaymentData($data = []) {
		if ($this->cron_model->get_all([
			'code'			=> 'processWebhookOrderCron_' . $data['order_id'],
		])) return;

		$this->cron_model->add([
			'code'			=> 'processWebhookOrderCron_' . $data['order_id'],
			'action'		=> 'alert_model->processWebhookOrderCron',
			'data'			=> [$data],
			'site_id'		=> 1,
			'alert_date'	=> date('Y-m-d H:i:s', strtotime('+2 minutes')),
		]);
	}

	public function whatsapp_webhook($type = 'imiconnect', $token = '') {
		$stream_clean	= $this->security->xss_clean($this->input->raw_input_stream);
		$response		= json_decode($stream_clean);

		log_kb(['Whatsapp Webhook::response:: ' => [$response, $type, $token]]);

		if ($type === 'onextel' && $token !== '3gj4jh5sj6dg5fj6hw81t8ertI39ycgf69af') exit('not_ok');

		if ($type === 'onextel') {
			if ($response->message->type == 'text') {
				$response->message = $response->message->text->body ?? '';
			}

			$response->waid = $response->from ?? '';
		}

		if (isset($response->message->button->payload)) {
			$button_payload = $response->message->button->payload;

			log_kb(['Whatsapp Webhook::Button Payload:: ' => $button_payload]);

			if (!empty($button_payload)) {
				$button_payload_array = explode('-', $button_payload);

				$this->marketing_survey_model->add([
					'marketing_id' => $button_payload_array[0] ?? 0,
					'user_id'      => $button_payload_array[1] ?? 0,
					'data'         => $button_payload ?? '',
				]);
			}
		}

		if (
			!empty($response) &&
			(
				strtolower($response->buttonPayload) == 'stop' ||
				(is_string($response->message) && strtolower($response->message) == 'stop')
			)
		) {
			$this->load->model('user/Unsubscribed_model', 'unsubscribed_model');

			if (empty($this->unsubscribed_model->get_all([
				'email' => $response->waid
			])['total'])) {
				$save = [];
				$save['email'] = $response->waid;

				$this->unsubscribed_model->add($save);
			}
		}

		if (!empty($response->buttonPayload) && in_array(trim($response->buttonPayload), [
			'9:00AM-9:30AM',
			'9:30AM-10:00AM',
			'10:00AM-10:30AM',
			'1:00PM-1:30PM',
			'1:30PM-2:00PM',
			'2:00PM-2:30PM',
		])) {
			$invite_info = $this->db->get_where('event_user_invite_slots', [
				'mobile' => $response->waid
			])->row_array();

			log_kb(['Whatsapp Webhook::response::invite_info ' => $invite_info]);

			if (empty($invite_info)) return;

			$this->db->update('event_user_invite_slots', [
				'time_slot' => $response->buttonPayload
			], [
				'id'		=> $invite_info['id']
			]);
		}
	}

	public function stripe($token = '') {
		try {
			$token = 'stripeadgfjgsjhdgfjh565763';

			if ($token != $token) {
				return 'Invalid token';
			}

			$stream_clean = $this->security->xss_clean($this->input->raw_input_stream);

			$data = json_decode($stream_clean, true);

			log_kb(['Stripe Webhook::response:: ' => $data]);

			$data = $data['data']['object'];
			$data['order_id'] = $data['client_secret'];

			self::_createPaymentData($data);
		} catch (\Exception $e) {
			log_kb(['Stripe Webhook::error:: ' => $e->getMessage()]);
			throw $e;
		}
	}

	public function ses($token = '') {
		try {
			$ses_token = 'q12BBn7Z45679QT5Xn';

			if ($token != $ses_token) {
				return 'Invalid token';
			}

			$stream_clean = $this->security->xss_clean($this->input->raw_input_stream);

			$response = json_decode($stream_clean, true);
			$response = json_decode($response['Message'], true);

			// log_kb(['SES Webhook::response:: ' => $response]);

			if (empty($response)) {
				return 'No data found in the response';
			}

			$this->campaign_log_model->saveCampaignLog($response);
		} catch (\Exception $e) {
			log_kb(['SES Webhook::error:: ' => $e->getMessage()]);
			throw $e;
		}
	}

	public function bluedart_webhook() {
		$stream_clean	= $this->security->xss_clean($this->input->raw_input_stream);
		$response		= json_decode($stream_clean);

		log_kb(['Bluedart Webhook::response:: ' => $response]);

		echo "true";
	}

	public function vonage_webhook() {
		$stream_clean	= $this->security->xss_clean($this->input->raw_input_stream);
		$response		= json_decode($stream_clean);

		log_kb(['Vonage Webhook::response:: ' => $response]);

		echo "true";
	}

	public function phonepe($token = ''){
		try {
			$token = 'stripeadgfjgsjhdgfjh565763';

			if ($token != $token) {
				return 'Invalid token';
			}

			$stream_clean = $this->security->xss_clean($this->input->raw_input_stream);

			if (empty($stream_clean)) return false;

			$decode_data = json_decode($stream_clean, true);

			$data = json_decode(base64_decode($decode_data['response']), true);

			log_kb(['Phonepe Webhook::response:: ' => $data]);

			if (isset($data['code']) && $data['code'] == 'PAYMENT_SUCCESS') {
				// call check status curl request
				$merchant_id 				= $data['data']['merchantId'];
				$merchant_transaction_id 	= $data['data']['merchantTransactionId'];

				$salt_key 		= PHONEPE_SALT_KEY;
				$salt_index 	= PHONEPE_SALT_INDEX;
				$path 			= '/pg/v1/status/' . $merchant_id . '/' . $merchant_transaction_id;
				$verify_hash 	= hash('sha256', $path . $salt_key) . '###' . $salt_index;

				$ch = curl_init();

				curl_setopt_array($ch, [
					CURLOPT_URL 			=> PHONEPE_URL . $path,
					CURLOPT_RETURNTRANSFER 	=> true,
					CURLOPT_ENCODING 		=> '',
					CURLOPT_MAXREDIRS 		=> 10,
					CURLOPT_TIMEOUT 		=> 30,
					CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST 	=> 'GET',
					CURLOPT_HTTPHEADER 		=> [
						'Content-Type: application/json',
						'X-VERIFY:' . $verify_hash,
						'X-MERCHANT-ID:' . $merchant_id
					],
				]);

				$response 	= curl_exec($ch);
				$err 		= curl_error($ch);

				curl_close($ch);

				log_kb(['Webhook::phonepe::curl' => $response]);

				if ($err) {
					log_kb(['Webhook::phonepe::curl_error' => $err]);
					return false;
				} else {
					$response = json_decode($response, true);

					if ($response['code'] == 'PAYMENT_SUCCESS' && isset($response['data'])) {
						$data 				= $response['data'] ?? [];
						$data['id'] 		= $data['transactionId'] ?? '';
						$data['order_id'] 	= $data['merchantTransactionId'] ?? '';

						self::_createPaymentData($data);
					}
				}
			}
		} catch (\Exception $e) {
			log_kb(['Phonepe Webhook::error:: ' => $e->getMessage()]);
			throw $e;
		}
	}
}
