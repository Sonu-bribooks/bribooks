<?php defined('BASEPATH') OR exit('No direct script access allowed');

final class BankValidation_lib {
	private $_api_url		= ENVIRONMENT === 'production' ? 'https://api.signzy.app/api/v3/' : 'https://api-preproduction.signzy.app/api/v3/';
	private $_token_file	= FCPATH . 'uploads/bank_val_token_file_briboo_kb_tok_file.php';
	private $_token			= ENVIRONMENT === 'production' ? '' : '';
	private $_error			= '';
	private $_version		= 'V3';

	public function __construct() {
	}

	public function __call($action, $params = []) {
		$method = $action . $this->_version;

		if (method_exists($this, $method)) {
			return $this->{$method}(...$params);
		}
	}

	public function getPanV3($pan_no = '') {
		if (empty($pan_no)) return;

		$data = [
			'number'	=> $pan_no
		];

		$response = self::_curl(
			'pan/fetchV2',
			$data,
			'POST',
			true
		);

		return $response['result'] ?? [];
	}

	public function getPanV2($pan_no = '') {
		if (empty($pan_no)) return;

		$user_id = self::_generateToken('userId');

		$data = [
			'task' 			=> 'fetch',
			'essentials' 	=> [
				'number'	=> $pan_no
			]
		];

		$response = self::_curl(
			sprintf('%s/panv2', $user_id),
			$data,
			'POST',
			true
		);

		return $response['result'] ?? [];
	}

	public function getBankV3($data = []) {
		if (empty($data)) return;

		$data = [
			'beneficiaryAccount'	=> $data['account_number'],
			'beneficiaryIFSC'		=> $data['ifsc_code'],
			'beneficiaryMobile'		=> '',
			'beneficiaryName'		=> $data['name'],
			'nameFuzzy'				=> 'true',
			'nameMatchScore'		=> '0.9',
		];

		$response = self::_curl(
			'bankaccountverification/bankaccountverifications',
			$data,
			'POST',
			true
		);

		return $response['result'] ?? [];
	}

	public function getBankV2($data = []) {
		if (empty($data)) return;

		$user_id = self::_generateToken('userId');

		$data = [
			'task' 			=> 'bankTransfer',
			'essentials' 	=> [
				'beneficiaryName'		=> $data['name'],
				'beneficiaryAccount'	=> $data['account_number'],
				'beneficiaryIFSC'		=> $data['ifsc_code'],
			]
		];

		$response = self::_curl(
			sprintf('%s/bankaccountverifications', $user_id),
			$data,
			'POST',
			true
		);

		return $response['result'] ?? [];
	}

	private function _generateToken($type = 'id') {
		if (!is_file($this->_token_file) || (filemtime($this->_token_file) + (360 * 24 * 3600)) < time()) {
			$data = [
				'username' => 'YouBooks_prod',
				'password' => 'N4RQqyTqykcYtuQp'
			];

			$response = self::_curl('login', $data, 'POST', false);

			if (!empty($response) && !empty($response[$type])) {
				file_put_contents($this->_token_file, json_encode($response));

				return $response['id'];
			}
		} else {
			$data = json_decode(file_get_contents($this->_token_file), true);

			return $data[$type];
		}
	}

	private function _curl($endpoint = '', $data = NULL, $method = 'POST', $token = TRUE) {
		if (is_array($data)) {
			$data = json_encode($data);
		}

		log_kb(['BankValidation::_curl::request:: ' => [
			'endpoint'		=> $endpoint,
			'data'			=> $data,
			'method'		=> $method,
			'token'			=> $token,
		]]);

		if ($token) {
			$bearer_token = $this->_version == 'V3' ? $this->_token : self::_generateToken();

			if (empty($bearer_token)) {
				$this->_error = 'Invalid credentials';
				return false;
			}

			$headers = [
				'Content-Type: application/json',
				'Authorization: ' . $bearer_token
			];
		} else {
			$headers = [
				'Content-Type: application/json',
			];
		}

		$ch = curl_init();

		if (!empty($data)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		curl_setopt_array($ch, [
			CURLOPT_URL 			=> $this->_api_url . $endpoint,
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_ENCODING 		=> '',
			CURLOPT_MAXREDIRS 		=> 10,
			CURLOPT_TIMEOUT 		=> 30,
			CURLOPT_SSL_VERIFYHOST 	=> 0,
			CURLOPT_SSL_VERIFYPEER 	=> 0,
			CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST 	=> $method,
			CURLOPT_HTTPHEADER 		=> $headers,
		]);

		$response 	= curl_exec($ch);
		$curl_error = curl_error($ch);

		curl_close($ch);

		if (!empty($curl_error)) {
			$this->_error = $curl_error;
			return false;
		}

		log_kb(['BankValidation::_curl::response:: ' => [
			'endpoint'		=> $endpoint,
			'response'		=> $response,
			'error'			=> $this->_error,
		]]);

		$output = json_decode($response, true);

		update_thirdparty_status('signzy', !empty($output['result']), $output['message'] ?? '');

		return $output;
	}
}
