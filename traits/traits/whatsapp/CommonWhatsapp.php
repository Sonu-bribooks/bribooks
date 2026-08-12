<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait CommonWhatsapp {
	private function _formatParameters($data = []) {
		$parameters_data = [];

		foreach ($data['parameters'] ?? [] as $key => $value) {
			$parameters_data['variable' . ($key + 1)] = (string)$value;
		}

		if (!empty($data['url_parameters'])) {
			$parameters_data['URLvariable1'] = [
				'type'		=> 'url',
				'payload'	=> (string)$data['url_parameters']
			];
		}

		return $parameters_data;
	}

	public function sendWhatsappGeneric($to, $data = []) {
		if (strpos($to, '+') !== 0) {
			$to = '+' . $to;
		}

		$destination = [[
			'waid'	=> explode(',', $to)
		]];

		$message = [
			'template' 		=> $data['template'] ?? '',
			'parameters' 	=> $data['parameters'] ?? []
		];

		$this->tool_model->whatsapp($destination, $message);
	}

	private function _sendWhatsappText($to, $data = []) {
		if (strpos($to, '+') !== 0) {
			$to = '+' . $to;
		}

		$destination = [[
			'waid'	=> explode(',', $to)
		]];

		$message = [
			'template' 		=> $data['template'] ?? '',
			'parameters' 	=> self::_formatParameters($data ?? [])
		];

		$this->tool_model->whatsapp($destination, $message);
	}

	private function _sendWhatsappDocument($to, $data = []) {
		if (strpos($to, '+') !== 0) {
			$to = '+' . $to;
		}

		$destination = [[
			'waid'	=> explode(',', $to)
		]];

		$message = [
			'template' 		=> $data['template'] ?? '',
			'parameters' 	=> array_merge(self::_formatParameters($data), [
				'document'	=> [
					'link'		=> $data['document']['link'] ?? '',
					'filename'	=> $data['document']['name'] ?? '',
				]
			]),
		];

		$this->tool_model->whatsapp($destination, $message);
	}

	private function _sendWhatsappImage($to, $data = []) {
		if (strpos($to, '+') !== 0) {
			$to = '+' . $to;
		}

		$destination = [[
			'waid'	=> explode(',', $to)
		]];

		$message = [
			'template' 		=> $data['template'] ?? '',
			'parameters' 	=> array_merge(self::_formatParameters($data), [
				'image'	=> [
					'link'		=> $data['document']['link'] ?? '',
					'filename'	=> $data['document']['name'] ?? '',
				]
			]),
		];

		$this->tool_model->whatsapp($destination, $message);
	}

	private function _sendWhatsappVideo($to, $data = []) {
		if (strpos($to, '+') !== 0) {
			$to = '+' . $to;
		}

		$destination = [[
			'waid'	=> explode(',', $to)
		]];

		$message = [
			'template' 		=> $data['template'] ?? '',
			'parameters' 	=> array_merge(self::_formatParameters($data), [
				'video'	=> [
					'link'		=> $data['document']['link'] ?? '',
					'filename'	=> $data['document']['name'] ?? '',
				]
			]),
		];

		$this->tool_model->whatsapp($destination, $message);
	}

	public function sendOnextelWhatsappMessage($to = '', $data = [], $company = 'bribooks') {
		if (empty($data) || empty($to)) return;

		$to = ENVIRONMENT === 'production' ? $to : get_settings('testing_mobile');

		self::_sendOnextelWhatsapp($to, $data, $company);
	}

	private function _sendOnextelWhatsapp($to = '', $data = [], $company = 'bribooks') {
		if (empty($data) || empty($data['template_id']) || empty($to)) return;

		$to = ENVIRONMENT === 'production' ? $to : get_settings('testing_mobile');

		if ($company 		== 'brisharks') {
			$journey_id 	= '69f8a1d27cc450180bac9142';
			$from_number 	= '919717317005';
		} elseif ($company 	== 'briminds') {
			$journey_id 	= '6a5790dc40b941074c65e657';
			$from_number 	= '918796689878';
		} else {
			$journey_id 	= '68778e17fba2453bc2692ee2';
			$from_number 	= '919910735297';
		}

		$auth_token 	= self::_generateOnextelToken();
		// $journey_id 	= $company == 'brisharks' ? '69f8a1d27cc450180bac9142' :'68778e17fba2453bc2692ee2';
		$url 			= 'https://365cx.io/chatbird/api/message/send';

		$template_data 	= [
			'templateId' 		=> $data['template_id'],
		];

		if (!empty($data['parameters'])) {
			$template_data['parameterValues'] = (object)$data['parameters'];
		}

		if (!empty($data['media'])) {
			$template_data['media'] = (object)$data['media'];
		}

		if (!empty($data['buttons'])) {
			$template_data['buttons'] = $data['buttons'];
		}

		$payload = [
			// 'from' 			=> $company == 'brisharks' ? '9717317005' :'919910735297',
			'from' 			=> $from_number,
			'to'			=> $to,
			'journeyId' 	=> $journey_id,
			'message' 		=> [
				'template' 	=> $template_data
			]
		];

		$headers = [
			'authentication-token: ' . $auth_token,
			'Content-Type: application/json'
		];

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_PRETTY_PRINT));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close($ch);

		$output = json_decode($response, true);

		log_kb([
			'OnextelWhatsapp:: ' => [
				'input' 		=> $data,
				'payload' 		=> json_encode($payload, JSON_PRETTY_PRINT),
				'output' 		=> $output,
			]
		]);

		update_thirdparty_status('onextel', $output['status'] == 1, $output['message'] ?? '');

		return $output;
	}

	private function _generateOnextelToken() {
		$token_file = FCPATH . 'uploads/onextel_whatsapp_token_file_briboo_kb_tok_file.php';

		if (!is_file($token_file) || (filemtime($token_file) + (3600)) < time()) {
			$url = 'https://365cx.io/account/enterprise/login';

			$data = http_build_query([
				'email' 	=> '',
				'password' 	=> ''
			]);

			$headers = [
				'Content-Type: application/x-www-form-urlencoded'
			];

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$result = curl_exec($ch);

			$err = curl_error($ch);

			curl_close($ch);

			if (!empty($err)) {
				log_kb([
					'_generateOnextelToken::error' => $err
				]);
				return false;
			}

			$response = json_decode($result, true);

			log_kb([
				'_generateOnextelToken::response' => $response
			]);

			if (!empty($response) && !empty($response['response']) && !empty($token = $response['response']['token'])) {
				file_put_contents($token_file, $token);

				return $token;
			}

			return false;
		} else {
			return file_get_contents($token_file);
		}
	}
}
