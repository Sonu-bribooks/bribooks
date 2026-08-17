<?php defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class Authentication {
	public function __construct() {
		$this->CI 		=& get_instance();

		$this->input 	= $this->CI->input;
		$this->output 	= $this->CI->output;
		$this->uri 		= $this->CI->uri;
		$this->security = $this->CI->security;
		$this->session 	= $this->CI->session;
		$this->config 	= $this->CI->config;
		$this->json 	=& $this->CI->json ?? [];

		$this->CI->load->library('form_validation');
		$this->form_validation = $this->CI->form_validation;

		$this->CI->load->model('user/User_model', 'user_model');
		$this->user_model = $this->CI->user_model;

		$this->CI->load->model('user/UserToken_model', 'user_token_model');
		$this->user_token_model = $this->CI->user_token_model;

		$this->allowed_ips 	= [];
		$this->private_apis = [
			'signup',
			'getUserBooks',
			'updateProfile',
			'updateUserSetting',
			'updateUserBank',
			'getUserEarnings',
			'mySubscription',
			'mySubscriptions',
			'getAddresses',
			'addAddress',
			'deleteAddress',
			'saveShippingAddress',
			'createOrder',
			'createPayment',
			'getOrders',
			'getEBookOrders',
			'getPrice',
			'createCompetitionOrder',
			'subscribeCompetition',
			'unPublishBook',
			'canRead',
			'updateUserData',
			'filterCmsOrder',
			'filterCmsBook',
			'addCmsBookStock',
			'cmsBookQaQc',
			'filterAssignmentBook',
			'getPrinters',
			'getAssignments',
			'getAssignment',
			'getOrderPackagingStats',
			'getBook',
			'getLessons',
			'userCreditRequest',
			'getUserBank',
			'updateUserPan',
			'getDonationCertificates',
			'inviteTeacher',
			// 'getSchoolBooks',
			'getUserNotifications',
			'sendMobileOtp',
			'getFonts',
			'checkGrammar',
			'getAppUpdate',
			'updateUserDetails',
			'archiveBook',
			'getEventPass',
			'getUserActiveEvent',
			'getUserBooksByEventId',
			'updateUserDetails',
			'eventCertificateStatus',
			'addEventBookVote',
		];
	}

	public function init() {
		$this->CI->benchmark->mark('api_start');

		if (get_class($this->CI) === 'Api' && $this->input->method() === 'options') {
			$this->json['ok'] = _l('ok');
		}

		if (get_class($this->CI) === 'Api' && $this->input->method() !== 'options') {
			if (strpos(strtolower($this->input->get_request_header('Content-Type', true)), 'multipart/form-data') !== false) {
				$data = $this->security->xss_clean($this->input->post());
			} else {
				$data = $this->input->raw_input_stream;
				$data = !empty($data)
					? $this->security->xss_clean(json_decode($data, 1))
					: '';
			}

			$_POST 			= $data;

			if (empty($_POST)) {
				$_POST = [];
			}

			$session_data = $this->session->userdata();

			unset($session_data['pwds'], $session_data['dbs'], $session_data['queries']);

			LOG_API && log_kb(['Authentication::' => [
				'Content-Type'	=> strtolower($this->input->get_request_header('Content-Type', true)),
				'version'		=> $this->input->get('version'),
				'uri_segment' 	=> $this->uri->uri_string(),
				'post_data' 	=> $_POST,
				'session' 		=> $session_data,
				'cookie_site' 	=> $this->input->cookie('user_site'),
				'method' 		=> $this->input->method(),
				'agent' 		=> $this->input->user_agent(true),
				'ip' 			=> $this->input->ip_address(),
			]]);

			$uri_segment 	= $this->uri->segment_array();
			$first_uri 		= array_shift($uri_segment);
			$last_uri 		= array_pop($uri_segment);

			if ($this->input->get_request_header('Authorization')) {
				self::_initUserSession(true);
			}

			if (in_array($last_uri, $this->private_apis)) {
				self::validateUser();
			}
		}
	}

	private function validateUser() {
		if (!in_array($this->input->server('REMOTE_ADDR'), $this->allowed_ips)) {
			if (!$this->json) {
				self::_initUserSession();
			}

			// Old Authentication session based
			// if (!$this->json && !$this->session->userdata('user_id')) {
			// 	$this->json['unauthorized'] = _l('invalid_token');
			// 	$this->json['error'] 		= _l('invalid_token');
			// }
		}
	}

	private function _initUserSession($suppress = false) {
		try {
			$secret_key = $this->config->item('bb_secret_jwt_token');
			$headers = explode(' ', $this->input->get_request_header('Authorization'));
			// JWT::$timestamp = 0;
			$decoded = JWT::decode($headers[1] ?? '', $secret_key, ['HS256']);
			$decoded = (array)$decoded;

			if (
				!empty($decoded['exp']) &&
				$decoded['exp'] > time() &&
				(
					(!$suppress || empty($this->session->userdata('user_id'))) &&
					($user_info = $this->user_model->get($decoded['user_id'])) &&
					$this->user_token_model->get_all([
						'user_id'	=> (int)$user_info['id'],
						'token'		=> $headers[1] ?? '',
					])['total'] > 0
				)
			) {
				$this->session->set_userdata([
					'user_id'		=> $user_info['id'],
					'user_email'	=> $user_info['email'],
					'user_mobile'	=> $user_info['mobile'],
					'user_role_id'	=> $user_info['role_id'],
					'user_role'		=> get_user_role_by_id($user_info['role_id']),
					'user_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
					'user_site'		=> $user_info['site_id'] ?? 0,
					'user_site_id'	=> $user_info['site_id'] ?? 0,
				]);
			} else {
				if (!$suppress) {
					$this->json['unauthorized'] = _l('invalid_token');
					$this->json['error'] 		= _l('invalid_token');

					self::_removeUserSession();
				}
			}
		} catch (Exception $e) {
			if (!$suppress) {
				$this->json['unauthorized'] = _l('invalid_token');
				$this->json['error'] 		= _l('invalid_token');

				self::_removeUserSession();
			}
		}
	}

	// private function _initUserSession($suppress = false)
	// {
	// 	try {
	// 		$secret_key = $this->config->item('bb_secret_jwt_token');
	// 		log_kb([
	// 			'DECODE_JWT_SECRET_LENGTH' => strlen((string)$secret_key),
	// 			'DECODE_JWT_SECRET_HASH' => hash('sha256', (string)$secret_key),
	// 		]);

	// 		$authorization = trim(
	// 			$this->input->get_request_header('Authorization')
	// 		);

	// 		$headers = preg_split('/\s+/', $authorization);

	// 		$token = $headers[1] ?? '';

	// 		log_kb([
	// 			'DECODE_TOKEN_HASH' => hash('sha256', $token),
	// 			'DECODE_TOKEN_LENGTH' => strlen($token),
	// 		]);

	// 		log_kb([
	// 			'jwt_debug' => [
	// 				'authorization_exists' => !empty($authorization),
	// 				'header_count' => count($headers),
	// 				'has_token' => !empty($token),
	// 				'token_length' => strlen($token),
	// 				'token_parts' => count(explode('.', $token)),
	// 				'secret_length' => strlen((string)$secret_key),
	// 			]
	// 		]);

	// 		$decoded = JWT::decode(
	// 			$token,
	// 			$secret_key,
	// 			['HS256']
	// 		);

	// 		$decoded = (array)$decoded;

	// 		print_r([
	// 			'decoded' => $decoded
	// 		]);
	// 		exit('decode success');

	// 	} catch (Exception $e) {

	// 		log_kb([
	// 			'JWT_EXCEPTION_CLASS'   => get_class($e),
	// 			'JWT_EXCEPTION_MESSAGE' => $e->getMessage(),
	// 			'JWT_EXCEPTION_CODE'    => $e->getCode(),
	// 		]);

	// 		echo '<pre>';
	// 		print_r([
	// 			'exception_class' => get_class($e),
	// 			'exception_message' => $e->getMessage(),
	// 		]);
	// 		exit('JWT decode error');
	// 	}
	// }

	private function _removeUserSession() {
		$this->session->unset_userdata([
			'user_id',
			'user_email',
			'user_mobile',
			'user_role_id',
			'user_role',
			'user_name',
			'user_site',
			'user_site_id',
		]);
	}

	private function validateForm() {
		if (!$this->json) {
			$valid = $this->form_validation->run();

			!$valid && ($this->json['error'] = strip_tags(validation_errors()));
		}
	}
}
