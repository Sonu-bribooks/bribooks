<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait User {
	public function getUser() {
		if (!$this->json) {
			if (
				$this->session->userdata('user_id') &&
				$this->session->userdata('user_role_id') == 2
			) {
				self::_formatUser($this->session->userdata('user_id'));
			} elseif (
				$this->session->userdata('user_id') &&
				$this->session->userdata('user_role_id') == 9
			) {
				self::_formatSchool($this->session->userdata('user_id'));
			} elseif (
				$this->session->userdata('user_id') &&
				$this->session->userdata('user_role_id') == 3
			) {
				self::_formatTeacher($this->session->userdata('user_id'));
			} elseif (
				$this->session->userdata('user_id') &&
				$this->session->userdata('user_role_id') == 11
			) {
				self::_formatReviewer($this->session->userdata('user_id'));
			} else {
				$this->json['login'] = true;
			}

			self::_getLocale();
		}
	}

	public function updateUserSetting() {
		$this->form_validation->set_rules('notification', _l('notification'), 'trim|required|numeric|in_list[0,1]');
		$this->form_validation->set_rules('show_country', _l('show_country'), 'trim|required|numeric|in_list[0,1]');

		self::_runFormValidation();

		if (!$this->json) {
			$this->student_model->edit($this->session->userdata('user_id'), [
				'notification'	=> (int)$this->input->post('notification'),
				'show_country'	=> (int)$this->input->post('show_country'),
			]);

			self::_formatUser($this->session->userdata('user_id'));

			$this->json['success'] = _l('settings_updated');
		}
	}

	public function getUserBank() {
		if (!$this->json) {
			$this->json['bank_data'] = $this->bank_model->get_all([
				'user_id'	=> (int)$this->session->userdata('user_id'),
			])['rows'][0] ?? [];

			$this->load->library('Encrypt_lib', 'encrypt_lib');

			if (!empty($this->json['bank_data']['account_number'])) {
				$this->json['bank_data']['account_number'] = substr($this->encrypt_lib->decrypt($this->json['bank_data']['account_number']), -4);
				$this->json['bank_data']['pan_number'] = substr($this->encrypt_lib->decrypt($this->json['bank_data']['pan_number']), -4);
			}
		}
	}

	public function updateUserPan() {
		if ($this->input->method() === 'options') return;

		$this->load->library('Encrypt_lib', 'encrypt_lib');
		$_POST['pan_number'] = $this->encrypt_lib->decode($this->input->post('pan_number'));

		$this->form_validation->set_rules('pan_number', _l('pan_number'), 'trim|required|exact_length[10]');

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($bank_info = $this->bank_model->get_all([
				'user_id'			=> (int)$this->session->userdata('user_id'),
			])['rows'][0] ?? [])) {
				$this->json['error'] = _li('we_don\'t_have_your_bank_details');
				return;
			}

			$this->load->library('BankValidation_lib', 'bankvalidation_lib');

			$pan_info 	= $this->bankvalidation_lib->getPan($this->input->post('pan_number'));

			log_kb([
				'pan_info'	=> $pan_info,
			]);

			if (
				empty($pan_info) ||
				!$pan_info['isValid'] ||
				$pan_info['panStatus'] != 'VALID' ||
				mb_strtolower($pan_info['name']) != mb_strtolower($bank_info['name'])
			) {
				$this->json['error'] = _li('Account holder name should match with the name on PAN card');
				return;
			}

			$this->bank_model->edit($bank_info['id'], [
				'pan_number'		=> $this->encrypt_lib->encrypt($this->input->post('pan_number')),
			]);

			CI_Events::trigger('access_log', [
				'module'	=> 'update_user_pan_' . (int)$bank_info['id']
			]);

			$this->json['success'] = _l('pan_updated');
		}
	}

	public function updateUserBank() {
		if ($this->input->method() === 'options') return;

		$this->load->library('Encrypt_lib', 'encrypt_lib');
		$_POST['account_number'] = $this->encrypt_lib->decode($this->input->post('account_number'));
		$_POST['pan_number'] = $this->encrypt_lib->decode($this->input->post('pan_number'));

		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[3]|max_length[128]');
		$this->form_validation->set_rules('bank_name', _l('bank_name'), 'trim|required|min_length[3]|max_length[196]');
		$this->form_validation->set_rules('branch_name', _l('branch_name'), 'trim|required|min_length[3]|max_length[196]');
		$this->form_validation->set_rules('account_number', _l('account_number'), 'trim|required|min_length[9]|max_length[18]');
		$this->form_validation->set_rules('ifsc_code', _l('ifsc_code'), 'trim|required|min_length[6]|max_length[15]');
		get_author_currency_code() === 'INR' && $this->form_validation->set_rules('pan_number', _l('pan_number'), 'trim|required|exact_length[10]');
		// $this->form_validation->set_rules('ifsc_code', _l('ifsc_code'), [
		// 	'trim',
		// 	'required',
		// 	'min_length[6]',
		// 	'max_length[15]',
		// 	['ifsc_code', [$this->validate_model, 'ifsc_code']]
		// ]);

		self::_runFormValidation();

		if (!$this->json) {
			if ($this->bank_model->get_all([
				'user_id'			=> (int)$this->session->userdata('user_id'),
			])['total'] > 0) {
				$this->json['error'] = _li('you_have_already_added_bank_details, for_update_contact_support_team');
				return;
			}

			if (get_author_currency_code() === 'INR') {
				$this->load->library('BankValidation_lib', 'bankvalidation_lib');

				$pan_info 	= $this->bankvalidation_lib->getPan($this->input->post('pan_number'));

				log_kb([
					'pan_info'	=> $pan_info,
				]);

				if (
					empty($pan_info) ||
					!$pan_info['isValid'] ||
					$pan_info['panStatus'] != 'VALID' ||
					mb_strtolower($pan_info['name']) != mb_strtolower(trim($this->input->post('name')))
				) {
					$this->json['error'] = _li('Account holder name should match with the name on PAN card');
					return;
				}

				$bank_info 	= $this->bankvalidation_lib->getBank($this->input->post());

				log_kb([
					'bank_info'	=> $bank_info,
				]);

				if (
					empty($bank_info) ||
					$bank_info['active'] != 'yes' ||
					empty($bank_info['bankTransfer']['bankRRN'])
				) {
					$this->json['error'] = _li('invalid_bank_details');
					return;
				}
			}

			$bank_id = $this->bank_model->add([
				'user_id'			=> (int)$this->session->userdata('user_id'),
				'name'				=> trim($this->input->post('name')),
				'bank_name'			=> trim($this->input->post('bank_name')),
				'branch_name'		=> trim($this->input->post('branch_name')),
				'account_number'	=> $this->encrypt_lib->encrypt($this->input->post('account_number')),
				'ifsc_code'			=> trim($this->input->post('ifsc_code')),
				'pan_number'		=> $this->encrypt_lib->encrypt($this->input->post('pan_number')),
				'verified'			=> 1,
			]);

			CI_Events::trigger('access_log', [
				'module'	=> 'update_user_bank_' . (int)$bank_id
			]);

			$this->json['success'] = _l('bank_details_updated');
		}
	}

	public function userCreditRequest() {
		if ($this->input->method() === 'options') return;

		$limit = get_author_currency_code() === 'INR' ? 100000 : 5000;

		$this->form_validation->set_rules('amount', _l('amount'), 'trim|required|numeric|greater_than_equal_to[1]|less_than_equal_to[' . $limit . ']');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|numeric|in_list[1,2,3]');
		$this->form_validation->set_rules('donation_type', _l('donation_type'), 'trim|required|numeric|in_list[0,1,2]');

		self::_runFormValidation();

		if (!$this->json) {
			// check if account has that amount
			$this->load->model('user/UserCredit_model', 'user_credit_model');
			$this->load->model('user/UserCreditRequest_model', 'user_credit_request_model');
			$this->load->model('user/UserCreditHistory_model', 'user_credit_history_model');

			$credit_info = $this->user_credit_model->getByUserId($this->session->userdata('user_id'));

			if (empty($credit_info)) {
				$this->json['error'] = _li('You_don\'t_have_sufficient_balance.');
				return;
			}

			if ($credit_info['credit'] < $this->input->post('amount')) {
				$this->json['error'] = _li('You_don\'t_have_sufficient_balance.');
				return;
			}

			if (
				($this->input->post('type') == 1 || $this->input->post('type') == 3) &&
				(TRANSFER_LIMIT[$credit_info['currency_code']] ?? 1) > $this->input->post('amount')
			) {
				$this->json['error'] = sprintf(_li('Minimum_transfer_limit_%s'), TRANSFER_LIMIT[$credit_info['currency_code']]);
				return;
			}

			$user_credit_request_id = $this->user_credit_request_model->add([
				'user_id'		=> (int)$this->session->userdata('user_id'),
				'credit'		=> (double)$this->input->post('amount'),
				'type'			=> (int)$this->input->post('type'),
				'donation_type'	=> ($this->input->post('type') == 1 || $this->input->post('type') == 3)
					? 0
					: (int)$this->input->post('donation_type'),
				'currency_code'	=> get_author_currency_code($this->session->userdata('user_id')),
			]);

			$this->user_credit_model->edit($credit_info['id'], [
				'credit'	=> (double)($credit_info['credit'] - $this->input->post('amount')),
			]);

			$this->user_credit_history_model->add([
				'type'					=> 2,
				'user_id'				=> (int)$this->session->userdata('user_id'),
				'credit'				=> (double)$this->input->post('amount'),
				'user_credit_request_id'=> (int)$user_credit_request_id,
				'currency_code'			=> get_author_currency_code($this->session->userdata('user_id')),
			]);

			$code = 'donationRequestCron';

			$this->load->model('common/Cron_model', 'cron_model');

			$this->cron_model->add([
				'code'			=> $code . '_' . $user_credit_request_id,
				'action'		=> 'alert_model->' . $code,
				'data'			=> [$user_credit_request_id],
				'alert_date'	=> date('Y-m-d H:i:00', strtotime((ENVIRONMENT === 'production') ? '+10 minutes' : '+1 minutes'))
			]);

			CI_Events::trigger('access_log', [
				'module'	=> 'user_credit_request_' . (int)$user_credit_request_id
			]);
		}
	}

	public function getUserEarnings() {
		if (!$this->json) {
			$this->load->library('Royalty_lib', 'royalty_lib');

			$this->load->model('user/UserCredit_model', 'user_credit_model');
			$this->load->model('user/UserCreditRequest_model', 'user_credit_request_model');
			$this->load->model('user/UserCreditHistory_model', 'user_credit_history_model');
			$this->load->model('user/UserCreditRedeem_model', 'user_credit_redeem_model');
			$this->load->model('user/AuthorEarningExchangeRateHistory_model', 'author_earning_exchange_rate_history_model');
			$this->load->model('localisation/ExchangeRateHistory_model', 'exchange_rate_history_model');

			$stats = [
				'total'					=> 0,
				'transferred_to_self'	=> 0,
				'transferred'			=> 0,
				'donated'				=> 0,
				'usable_balance'		=> 0,
				'pending'				=> 0,
				'last_transfer'			=> '',
			];

			$credit_info = $this->user_credit_model->getByUserId($this->session->userdata('user_id'));

			if (empty($credit_info)) {
				$credit_info = [
					'credit'		=> 0,
					'currency_code'	=> get_author_currency_code(),
				];
			}

			$this->json['earnings'] = array_map(function($item) use(&$stats, $credit_info) {
				$book_info = $this->book_model->get($item['book_id']);

				$author_currency_code 		= get_author_currency_code($item['author_id']);
				$author_currency_symnbol 	= get_author_currency_symbol($item['author_id']);
				$earning_currency_symnbol 	= get_currency_symbol($item['currency_code']);

				// $item['amount'] = $this->royalty_lib->getRoyaltyInAuthorCurrency($item);

				$exchange_rate = 0;

				$earning_exchange_rate_info = $this->author_earning_exchange_rate_history_model->get_all([
					'author_earning_id' => $item['id']
				])['rows'][0] ?? [];

				if (!empty($earning_exchange_rate_info)) {
					$exchange_rate = $earning_exchange_rate_info['rate'];
				} else {
					$author_exchange_rate	= mb_strtolower($author_currency_code) === 'inr'
						? 1
						: $this->exchange_rate_history_model->get_all([
							'currency_code' => $author_currency_code,
							'order'			=> 'ASC',
							'start'			=> 0,
							'limit'			=> 1,
						])['rows'][0]['old_rate'] ?? 0
					;
					$earning_exchange_rate 	= mb_strtolower($item['currency_code']) === 'inr'
						? 1
						: $this->exchange_rate_history_model->get_all([
							'currency_code' => $item['currency_code'],
							'order'			=> 'ASC',
							'start'			=> 0,
							'limit'			=> 1,
						])['rows'][0]['old_rate'] ?? 0
					;

					if (!empty($author_exchange_rate) && !empty($earning_exchange_rate)) {
						$rate  			= round($earning_exchange_rate/ $author_exchange_rate, 2);
						$exchange_rate 	= !empty($rate) ? $rate : 0;
					}
				}

				$stats['total'] += ($item['status'] != 3) ? $item['amount'] : 0;

				if ($item['status'] == 1) {
					$stats['transferred'] += $item['amount'];
				} else if ($item['status'] != 3) {
					$stats['pending'] += $item['amount'];
				}

				return [
					'date'						=> formatDate($item['date_added']),
					'book'						=> $book_info['name'] ?? '',
					'quantity'					=> $item['quantity'],
					'amount'					=> (($item['status'] == 3) ? '-' : '') . currency($item['amount'], 0, $item['currency_code']),
					'platform'					=> get_settings('system_name'),
					'status_text'				=> _earning_status($item['status']),
					'status'					=> $item['status'],
					'author_currency_code'		=> $author_currency_code,
					'author_currency_symnbol'	=> $author_currency_symnbol,
					'earning_currency_code'		=> $item['currency_code'],
					'earning_currency_symnbol'	=> $earning_currency_symnbol,
					'exchange_rate'				=> currency($exchange_rate, 0, $author_currency_code),
				];
			}, $this->author_earning_model->get_all([
				'author_id'	=> (int)$this->session->userdata('user_id'),
			])['rows'] ?? []);

			$this->json['transactions'] = array_map(function($item) use(&$stats, $credit_info) {
				return [
					'name'			=> _transfer_type($item['donation_type'], $item['type']),
					'date'			=> formatDate($item['date_added']),
					'amount'		=> currency(
						convert_to_local_currency($item['credit'], 0, $credit_info['currency_code']),
						0,
						$credit_info['currency_code']
					),
					'status_text'	=> _request_status($item['status']),
					'status'		=> $item['status'],
				];
			}, $this->user_credit_request_model->get_all([
				'user_id'	=> (int)$this->session->userdata('user_id'),
			])['rows'] ?? []);

			$last_activity = $this->user_credit_request_model->get_all([
				'user_id' 	=> (int)$this->session->userdata('user_id'),
				'start'		=> 0,
				'limit'		=> 1,
				'sort'		=> 'user_credit_request.date_added',
				'order'		=> 'DESC',
			])['rows'][0] ?? [];

			$transferred_to_self = $this->user_credit_request_model->getAmount([
				'type'		=> 1,
				'user_id'	=> (int)$this->session->userdata('user_id'),
			]);
			$transferred_to_self += $this->user_credit_request_model->getAmount([
				'type'		=> 3,
				'user_id'	=> (int)$this->session->userdata('user_id'),
			]);

			$total_earnings 	= $this->royalty_lib->getBookCurrencyRoyality([
				'author_id' => (int)$this->session->userdata('user_id')
			]);

			$pending_earnings 	= $this->royalty_lib->getBookCurrencyRoyality([
				'author_id' 	=> (int)$this->session->userdata('user_id'),
				'status_in'		=> [0,2]
			]);

			$this->json['stats'] = [
				'total'				=> currency(
					$stats['total'] ?? 0,
					0,
					$credit_info['currency_code']
				),
				'total_earnings' => $total_earnings,
				'transferred'		=> currency(
					$stats['transferred'] ?? 0,
					0,
					$credit_info['currency_code']
				),
				'pending'			=> currency(
					$stats['pending'] ?? 0,
					0,
					$credit_info['currency_code']
				),
				'pending_earning' => $pending_earnings,
				'usable_balance'	=> currency(
					convert_to_local_currency($credit_info['credit'] ?? 0, 0, $credit_info['currency_code']),
					0,
					$credit_info['currency_code']
				),
				'donated'			=> currency(
					convert_to_local_currency(
						$this->user_credit_request_model->getAmount([
							'type'		=> 2,
							'user_id'	=> (int)$this->session->userdata('user_id'),
						]),
						0,
						$credit_info['currency_code']
					),
					0,
					$credit_info['currency_code']
				),
				'transferred_to_self'=> currency(
					convert_to_local_currency(
						$transferred_to_self,
						0,
						$credit_info['currency_code']
					),
					0,
					$credit_info['currency_code']
				),
				'last_transfer'		=> !empty($last_activity['date_added'])
					? date('M j, Y', strtotime($last_activity['date_added']))
					: 'NA',
			];
			$this->json['currency'] = $credit_info['currency_code'];
			$this->json['success'] = _l('bank_details_updated');
		}
	}

	public function updateProfile() {
		$this->form_validation->set_rules('biography', _l('biography'), 'trim|required|min_length[4]|max_length[250]');
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[4]|max_length[64]');

		if ($this->input->post('state_id')) {
			$this->form_validation->set_rules('site_id', _l('site_id'), [
				'trim',
				'required',
				'numeric',
				['site', [$this->validate_model, 'site']]
			]);
			$this->form_validation->set_rules('state_id', _l('state_id'), [
				'trim',
				'numeric',
				['state', [$this->validate_model, 'state']]
			]);
			!empty($this->input->post('city_id')) && $this->form_validation->set_rules('city_id', _l('city_id'), [
				'trim',
				'required',
				'numeric',
				['city', [$this->validate_model, 'city']]
			]);
			$this->form_validation->set_rules('grade_id', _l('grade_id'), [
				'trim',
				'numeric'
			]);
			$this->form_validation->set_rules('section_id', _l('section_id'), [
				'trim'
			]);
		}

		self::_runFormValidation();

		if (!$this->json) {
			if (!empty($spam_word_data = $this->page_model->checkSpamWords($this->input->post('name')))) {
				$spam_words = implode(', ', array_column($spam_word_data, 'word'));
				$this->json['error'] = _li('Spam Word : ') . ' ' . $spam_words;
			}

			if (!empty($spam_word_data = $this->page_model->checkSpamWords($this->input->post('biography')))) {
				$spam_words = implode(', ', array_column($spam_word_data, 'word'));
				$this->json['error'] = _li('Spam Word : ') . ' ' . $spam_words;
			}
		}

		if (!$this->json) {
			$user_info = $this->user_model->get($this->session->userdata('user_id'));

			$user_image = $user_info['image'];

			if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
				if (self::_validateFileUpload()) {
					$filename = sprintf('user_%s_%s.png', uniqid(), (int)$this->session->userdata('user_id'));

					log_kb($this->s3->amazonS3Upload(
						$filename,
						$_FILES['image']['tmp_name'],
						rtrim($this->config->item('s3_users_img'), '/')
					));

					// delete old profile image if not book author image
					if (strpos($user_image, 'author_') === false) {
						log_kb(['DeleteUserImage' => $this->s3->amazonS3Delete(
							str_replace('UserImages/', '', $user_image),
							rtrim($this->config->item('s3_users_img'), '/')
						)]);
					}

					$user_image = 'UserImages/' . $filename;
				}
			}

			$explode 	= explode(' ', trim($this->input->post('name')), 2);
			$first_name = array_shift($explode);
			$last_name 	= array_shift($explode);

			$this->student_model->edit($this->session->userdata('user_id'), [
				'first_name'	=> trim($first_name ?? ''),
				'last_name'		=> trim($last_name ?? ''),
				'timezone'		=> $this->input->post('timezone'),
				'biography'		=> $this->input->post('biography'),
				'image'			=> $user_image,
				'slug'			=> get_user_slug($this->input->post('name'), (int)$this->session->userdata('user_id')),
			]);

			// update author data
			$this->input->post('state_id') && $this->student_model->edit($this->session->userdata('user_id'), [
				'state_id'		=> (int)$this->input->post('state_id'),
				'city_id'		=> (int)$this->input->post('city_id'),
				'site_id'		=> (int)$this->input->post('site_id'),
				'grade_id'		=> $this->input->post('grade_id'),
				'section_id'	=> $this->input->post('section_id'),
				'grade'			=> $this->input->post('grade_id'),
				'section'		=> $this->input->post('section_id'),
			]);

			self::_formatUser($this->session->userdata('user_id'));

			CI_Events::trigger('access_log', [
				'module'	=> 'profile_edit'
			]);

			$this->json['success'] = _l('profile_updated');
		}
	}

	public function mySubscription() {
		if (!$this->json) {
			$user_info = $this->student_model->get($this->session->userdata('user_id'));

			if ($user_info['subscription_plan_id']) {
				$subscription_info = $this->subscription_plan_model->get($user_info['subscription_plan_id']);
				$results = $this->user_subscription_model->get_all([
					'user_id'				=> $this->session->userdata('user_id'),
					'subscription_plan_id'	=> $user_info['subscription_plan_id']
				])['rows'] ?? [];
				$user_subscription_info = array_shift($results);
			}

			$this->json['subscription'] = [
				'name'			=> $subscription_info['name'] ?? '',
				'description'	=> $subscription_info['description'] ?? '',
				'is_upgradable'	=> $subscription_info['is_upgradable'] ?? '',
				'duration_month'=> $subscription_info['duration_month'] ?? 0,
				'price'			=> $subscription_info['price'] ?? 0,
				'currency'		=> $subscription_info['symbol'] ?? '',
				'start_date'	=> $user_subscription_info['start_date'] ??  '',
				'end_date'		=> $user_subscription_info['end_date'] ?? '',
			];
		}
	}

	public function mySubscriptions() {
		if (!$this->json) {

			$user_info = $this->student_model->get($this->session->userdata('user_id'));

			foreach ($this->user_subscription_model->get_all([
				'user_id'	=> (int)$this->session->userdata('user_id')
			])['rows'] ?? [] as $index => $item) {
				$subscription_info = $this->subscription_plan_model->get($item['subscription_plan_id']);

				$this->json['subscriptions'][] = [
					'id'			=> $item['id'] ?? '',
					'name'			=> $subscription_info['name'] ?? '',
					'description'	=> $subscription_info['description'] ?? '',
					'duration_month'=> $subscription_info['duration_month'] ?? 0,
					'is_upgradable'	=> $index === 0
						? $subscription_info['is_upgradable'] ?? ''
						: false,
					'price'			=> $subscription_info['price'] ?? 0,
					'currency'		=> $subscription_info['symbol'] ?? '',
					'start_date'	=> $item['start_date'] ??  '',
					'end_date'		=> $item['end_date'] ?? '',
					'invoice'		=> $subscription_info['price'] > 0
						? base_url('invoice/download/' . $item['id'] . '/subscription')
						: null,
					'is_active'		=> $user_info['subscription_plan_id'] === $item['subscription_plan_id'],
				];
			}
		}
	}

	public function updateUserData() {
		$this->form_validation->set_rules('state_id', _l('state_id'), [
			'trim',
			'required',
			'numeric',
			['state', [$this->validate_model, 'state']]
		]);
		$this->form_validation->set_rules('city_id', _l('city_id'), [
			'trim',
			'required',
			'numeric',
			['city', [$this->validate_model, 'city']]
		]);
		$this->form_validation->set_rules('site_id', _l('site_id'), [
			'trim',
			'required',
			'numeric',
			['site', [$this->validate_model, 'site']]
		]);
		$this->form_validation->set_rules('grade_id', _l('grade_id'), [
			'trim',
			'required',
			'numeric'
		]);
		$this->form_validation->set_rules('section_id', _l('section_id'), [
			'trim',
			'required'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			return $this->json;

			$this->json['success'] = _l('profile_updated');
		}
	}

	public function getAnnouncements() {
		if (!$this->json) {
			$this->load->model('user/UserAnnouncements_model', 'user_announcement_model');
			$this->load->model('common/Marketing_model', 'marketing_model');

			if (!empty($this->input->post('user_id'))) {
				$user_id 	= $this->input->post('user_id') ?? 0;
				$module		= 'user_announcement_url_' . $user_id;
			} else {
				$user_id 	= $this->session->userdata('user_id') ?? 0;
				$module		= 'user_announcement_' . $user_id;
			}

			$announcement_info = $this->user_announcement_model->getByUserId($user_id);

			$this->json['announcement'] = $announcement_info;

			CI_Events::trigger('access_log', [
				'module'	=> $module
			]);
		}
	}

	public function getUserAppNotifications() {
		if (!$this->json) {
			if (!empty($this->session->userdata('user_id'))) {
				$this->load->model('user/UserAppNotification_model', 'user_app_notification_model');

				$attachment_types = [
					1 => 'image',
					2 => 'document',
					3 => 'video',
				];

				$this->json['notifications'] = array_map(function($item) {
					return [
						'id'				=> $item['id'],
						'title'				=> $item['title'],
						'body'				=> $item['body'],
						'url'				=> $item['url'],
						'message'			=> $item['message'],
						'attachment_type'	=> $item['attachment_type'],
						'attachment_file'	=> $item['attachment_type'] ? (strpos($item['attachment_file'], 'https') !== false ? $item['attachment_file'] : base_url(strpos($item['attachment_file'] , 'uploads') !== false
							? $item['attachment_file']
							: 'uploads/gallery/' . $item['attachment_file']
						)) : '',
						'date_added'		=> $item['date_added'],
					];
				}, $this->user_app_notification_model->get_all([
					'user_id' 	=> (int)$this->session->userdata('user_id'),
					'start'		=> 0,
					'limit'		=> 16,
				])['rows'] ?? []);
			} else {
				$this->json['login'] = true;
				$this->json['error'] = _li('Invalid user!');
			}
		}
	}

	public function unsubscribe() {
		if (!$this->json) {

			if (!empty($this->input->post('code'))) {
				$decrpyt_data = decrypt_data($this->input->post('code'));

				if (empty($decrpyt_data) || strpos($decrpyt_data, '|') === false) {
					return $this->json['error'] = _l('something_went_wrong');
				}

				list($type, $email) = explode('|', $decrpyt_data, 2);

				if (!empty($type) && !empty($email) && in_array(strtolower($type), ['user', 'school', 'site'])) {
					$model_name = $type . '_model';

					$info = $this->{$model_name}->get_all([
						'email' => $email
					])['rows'][0] ?? [];

					if (
						!empty($info) &&
						empty($this->unsubscribed_model->get_all([
							'email'		=> $email,
							'start'		=> 0,
							'limit'		=> 1,
						])['rows'][0])
					) {
						$this->unsubscribed_model->add([
							'type'		=> $type,
							'user_id'	=> (int)$info['id'],
							'email'		=> $email,
							'reason'	=> ($this->input->post('reason') ?? '') . '_' . ($this->input->post('utm_campaign') ?? 0),
						]);
					} else {
						return $this->json['error'] = _l('something_went_wrong.');
					}
				} else {
					return $this->json['error'] = _l('something_went_wrong..');
				}
			}

			$this->json['success'] = _l('successfully_unsubscribed');
		}
	}

	public function getUserByCode() {
		$this->form_validation->set_rules('uid', _l('user_id'), 'trim|numeric');
		$this->form_validation->set_rules('code', _l('verification_code'), 'trim|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('bid', _l('book_id'), 'trim|numeric');

		self::_runFormValidation();

		if (!$this->json) {
			if (
				empty($this->input->post('form_code')) ||
				empty($form_invite_info = $this->db->get_where('event_form_invite_code', [
					'event_id'	=> (int)$this->input->post('eid'),
					'code'		=> $this->input->post('form_code'),
				])->row_array()) ||
				empty($form_invite_info['end_date'])
			) {
				return $this->json['error'] = _li('Invalid Form');
			}

			if (!empty($form_invite_info) && $form_invite_info['end_date'] < date('Y-m-d H:i:s')) {
				return $this->json['error'] = _li('Form Expired');
			}

			if (
				$this->input->post('eid') &&
				($user_details_guest_info = $this->db->get_where('user_details_nyaf_guest', [
					'user_id' 	=> (int)$this->input->post('uid'),
					'event_id' 	=> (int)$this->input->post('eid')
				])->row_array())
			) {
				$this->json['error'] = _li('Details already submitted');
				return;
			} elseif (
				$this->input->post('uid') &&
				!empty($this->db->get_where('user_details_nyaf_invites', [
					'user_id' 	=> (int)$this->input->post('uid'),
					'book_id' 	=> (int)$this->input->post('bid'),
					'event_id' 	=> (int)$this->input->post('eid'),
					'status' 	=> 1
				])->row_array())
			) {
				$this->json['error'] = _li('The deadline for accepting the invitation is over, Thank You!');
				return;
			}

			if ((!empty($this->input->post('uid')) && !empty($this->input->post('code'))) && !empty($user_info = $this->db->get_where('event_user_invite_code', [
				'user_id'	=> (int)$this->input->post('uid'),
				'code'		=> $this->input->post('code'),
			])->row_array())) {
				$user_info = $this->user_model->get($user_info['user_id']);
			} else {
				$user_info = $this->user_model->get($this->session->userdata('user_id'));
			}

			if (!empty($user_info)) {
				$book_info = [];

				if ($this->input->post('bid')) {
					$book_info = $this->book_model->get($this->input->post('bid'));

					if (empty($book_info)) {
						$this->json['error'] = _li('Invalid book detail');
						return;
					}
				}

				$site_info 		= $this->site_model->get($user_info['site_id']);
				$state_info 	= $this->state_model->get($user_info['state_id']);
				$city_info 		= $this->city_model->get($user_info['city_id']);
				$referral_info 	= $this->user_referral_list_model->get_all(['user_id' => $user_info['id']]);

				$this->json['user'] = [
					'user_id'		=> $user_info['id'],
					'user_email'	=> $user_info['email'],
					'user_mobile'	=> $user_info['mobile'],
					'user_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
					'parent_name'	=> $user_info['parent_name'],
					'site_code'		=> $site_info['site_code'] ?? '',
					'site_id'		=> $site_info['id'] ?? '',
					'school'		=> $site_info['name'] ?? '',
					'state'			=> $state_info['name'] ?? '',
					'city'			=> $city_info['name'] ?? '',
					'state_id'		=> $state_info['id'] ?? '',
					'city_id'		=> $city_info['id'] ?? '',
					'grade'			=> $user_info['grade'] ?? '',
					'grade_id'		=> $user_info['grade'] ?? '',
					'section'		=> $user_info['section'] ?? '',
					'section_id'	=> $user_info['section'] ?? '',
					'image'			=> empty($user_info['image']) ? base_url('uploads/user_image/placeholder.png') : $this->config->item('s3_base_url') . 'public/' . $user_info['image'],
					'book_id'		=> $book_info['id'] ?? '',
					'book_name'		=> $book_info['name'] ?? '',
					'author_name'	=> $book_info['author_name'] ?? '',
					'author_image'	=> empty($book_info['author_image']) ? base_url('uploads/user_image/placeholder.png') : $this->config->item('s3_base_url') . 'public/' . $book_info['author_image'],
					'user_referral'	=> ($referral_info['total'] >= USER_REFERRAL_LIMIT) ? false : true,
					'form_end_date' => $form_invite_code['end_date'] ?? ''
				];
			} else {
				$this->json['error'] = _li('Invalid url');
			}
		}
	}

	public function getUserProfileByCode() {
		$this->form_validation->set_rules('eid', _l('event_id'), 'trim|numeric');
		$this->form_validation->set_rules('uid', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('form_code', _l('verification_code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('code', _l('code'), 'trim|required|min_length[8]|max_length[255]');

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($form_invite_info = $this->db->get_where('event_form_invite_code', [
				'event_id'	=> (int)$this->input->post('eid'),
				'code'		=> $this->input->post('form_code'),
			])->row_array()) || empty($form_invite_info['end_date'])) {
				return $this->json['error'] = _li('Invalid Form');
			}

			if (!empty($form_invite_info) && $form_invite_info['end_date'] < date('Y-m-d H:i:s')) {
				return $this->json['error'] = _li('Form Expired');
			}

			if (empty($invite_info = $this->db->get_where('event_user_invite_code', [
				'user_id'	=> (int)$this->input->post('uid'),
				'code'		=> $this->input->post('code'),
			])->row_array())) {
				return $this->json['error'] = _li('Invalid Url');
			}

			$user_info 		= $this->user_model->get($this->input->post('uid') ?? 0);
			$site_info 		= $this->site_model->get($user_info['site_id']);
			$state_info 	= $this->state_model->get($user_info['state_id']);
			$city_info 		= $this->city_model->get($user_info['city_id']);
			$referral_info 	= $this->user_referral_list_model->get_all(['user_id' => $user_info['id']]);

			$this->json['user'] = [
				'user_id'		=> $user_info['id'],
				'user_email'	=> $user_info['email'],
				'user_mobile'	=> $user_info['mobile'],
				'user_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'parent_name'	=> $user_info['parent_name'],
				'site_code'		=> $site_info['site_code'] ?? '',
				'site_id'		=> $site_info['id'] ?? '',
				'school'		=> $site_info['name'] ?? '',
				'state'			=> $state_info['name'] ?? '',
				'city'			=> $city_info['name'] ?? '',
				'state_id'		=> $state_info['id'] ?? '',
				'city_id'		=> $city_info['id'] ?? '',
				'grade'			=> $user_info['grade'] ?? '',
				'grade_id'		=> $user_info['grade'] ?? '',
				'section'		=> $user_info['section'] ?? '',
				'section_id'	=> $user_info['section'] ?? '',
				'image'			=> empty($user_info['image']) ? base_url('uploads/user_image/placeholder.png') : $this->config->item('s3_base_url') . 'public/' . $user_info['image'],
				'book_id'		=> $book_info['id'] ?? '',
				'book_name'		=> $book_info['name'] ?? '',
				'author_name'	=> $book_info['author_name'] ?? '',
				'author_image'	=> empty($book_info['author_image']) ? base_url('uploads/user_image/placeholder.png') : $this->config->item('s3_base_url') . 'public/' . $book_info['author_image'],
				'user_referral'	=> ($referral_info['total'] >= USER_REFERRAL_LIMIT) ? false : true,
				'form_end_date' => $form_invite_code['end_date'] ?? ''
			];
		}
	}

	private function _formatUser($user_id = 0) {
		if (
			$user_id &&
			$user_info = $this->db->get_where('users', [
				'id'		=> (int)$user_id,
				'role_id'	=> 2,
				'status'	=> 1,
				// 'verified'	=> 1,
			])->row_array()
		) {
			$user = [
				'user_id'		=> $user_info['id'],
				'user_email'	=> $user_info['email'],
				'user_mobile'	=> $user_info['mobile'],
				'user_role_id'	=> $user_info['role_id'],
				'user_role'		=> get_user_role_by_id($user_info['role_id']),
				'user_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'user_site'		=> $user_info['site_id'] ?? 0,
				'user_site_id'	=> $user_info['site_id'] ?? 0,
			];

			$this->session->set_userdata($user);

			$school_info = $this->site_model->get($user_info['site_id']);

			// Update book temp user id
			self::_updateTempUserId();

			$need_update 	= false;

			$is_author 		= $this->book_model->get_all([
				'user_id' 	=> $user_id,
				'start'		=> 0,
				'limit'		=> 1,
			])['total'] !== 0;

			if (empty($user_info['state_id'])) {
				$need_update = true;
			} else {
				$state_info = $this->state_model->get($user_info['state_id']);
			}

			if (empty($user_info['city_id'])) {
				$need_update = true;
			} else {
				$city_info = $this->city_model->get($user_info['city_id']);
			}

			if (empty($user_info['grade'])) {
				$need_update = true;
			}

			if (empty($user_info['section'])) {
				$need_update = true;
			}

			if ($need_update && $is_author) {
				$need_update = true;
			} else {
				$need_update = false;
			}

			if (!empty($user_info['dob'])) {
				$dob = new DateTime(date('Y-m-d', strtotime($user_info['dob'])));
				$now = new DateTime(date('Y-m-d'));
				$age = $dob->diff($now)->y;
			} else {
				$age = $user_info['age'] ?? 0;
			}

			$this->json['user'] = [
				'id' 					=> $user_info['id'],
				'user_email'			=> $user_info['email'],
				'address_id'			=> $user_info['address_id'],
				'user_mobile'			=> $user_info['mobile'],
				'image'					=> $user_info['image'],
				'name'					=> trim($user_info['first_name'] . ' ' . $user_info['last_name']),
				'first_name'			=> trim($user_info['first_name']),
				'parent_name'			=> $user_info['parent_name'],
				'user_site'				=> $user_info['site_id'] ?? 0,
				'school'				=> $school_info['name'] ?? 0,
				'country_id'			=> $user_info['country_id'] ?? 0,
				'state_id'				=> $user_info['state_id'] ?? 0,
				'state'					=> $state_info['name'] ?? '',
				'city_id'				=> $user_info['city_id'] ?? 0,
				'city'					=> $city_info['name'] ?? '',
				'grade_id'				=> $user_info['grade'] ?? 0,
				'grade'					=> $user_info['grade'] ?? '',
				'section_id'			=> $user_info['section'] ?? '',
				'section'				=> $user_info['section'] ?? '',
				'need_update'			=> $need_update,
				'slug'					=> $user_info['slug'] ?? '',
				'age'					=> $age,
				'minor'					=> $age < 7 && !empty($user_info['parent_name']),
				'relation'				=> $user_info['relation'] ?? '',
				'biography'				=> $user_info['biography'] ?? '',
				'role_id'				=> $user_info['role_id'],
				'role'					=> get_user_role_by_id($user_info['role_id']),
				'notification'			=> $user_info['notification'],
				'show_country'			=> $user_info['show_country'],
				'referral_code'			=> $user_info['referral_code'],
				'verification_code'		=> $user_info['verification_code'],
				'subscription_plan_id'	=> $user_info['subscription_plan_id'] ?? 0,
				'has_bank'				=> $this->bank_model->get_all([
					'user_id'			=> $user_info['id']
				])['total'] != 0,
				'site_code'				=> $school_info['site_code'] ?? '',
				'site_type'				=> $school_info['site_type'] ?? '1',
				'is_custom_theme_enable'=> $this->custom_theme_log_model->get_all([
					'status'	=> 1,
					'user_id'	=> $user_id
				])['total'] != 0,
				'is_author'				=> $is_author,
				'modified'				=> strtotime($user_info['date_modified']),
				// deprecated
				'events'				=> [],
				'is_sc_user'			=> 0,
				'total_copy_buy'		=> 0,
				'is_one_copy_buyer'		=> 0,
				'is_event_user'			=> 0,
				'is_rcw_user'			=> 0,
				'is_pc_user'			=> 0,
				'is_nyaf_us_user'		=> 0,
				'is_bwf_user'			=> 0,
				'is_enrol_user_enable'	=> 0,
				'is_referral'			=> 0,
			];
		}
	}

	private function _formatReviewer($user_id = 0) {
		if (
			$user_id &&
			$user_info = $this->db->get_where('users', [
				'id'		=> (int)$user_id,
				'role_id'	=> 11,
				'status'	=> 1,
			])->row_array()
		) {
			$user = [
				'user_id'		=> $user_info['id'],
				'user_email'	=> $user_info['email'],
				'user_mobile'	=> $user_info['mobile'],
				'user_role_id'	=> $user_info['role_id'],
				'user_role'		=> get_user_role_by_id($user_info['role_id']),
				'user_name'		=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'user_site'		=> $user_info['site_id'] ?? 0,
			];

			$this->session->set_userdata($user);

			$country_id 	= 0;
			$country_code 	= false;
			$state_info 	= [];
			$city_info 		= [];
			$user_events 	= [];

			if (!empty($school_info['state_id'])) {
				$state_info = $this->state_model->get($user_info['state_id']);

				$country_id = $state_info['country_id'];

				$this->load->model('localisation/Country_model', 'country_model');
				$country_info = $this->country_model->get($country_id);
				$country_code = $country_info['country_code'];
			}

			if (empty($country_code) && !empty($user_info['country_code'])) {
				$country_code = $user_info['country_code'];
			}

			if (!empty($user_info['city_id'])) {
				$city_info = $this->city_model->get($user_info['city_id']);
			}

			$this->json['user'] = [
				'id' 					=> $user_info['id'],
				'user_email'			=> $user_info['email'],
				'address_id'			=> $user_info['address_id'],
				'user_mobile'			=> $user_info['mobile'],
				'grade'					=> $user_info['grade'],
				'section'				=> $user_info['section'],
				'image'					=> $user_info['image'],
				'name'					=> trim($user_info['first_name'] . ' ' . $user_info['last_name']),
				'user_site'				=> $user_info['site_id'] ?? 0,
				'school'				=> '',
				'country_id'			=> 1,
				'country_code'			=> 'IN',
				'state_id'				=> $user_info['state_id'] ?? 0,
				'state'					=> $state_info['name'] ?? '',
				'city_id'				=> $user_info['city_id'] ?? 0,
				'city'					=> $city_info['name'] ?? '',
				'role_id'				=> $user_info['role_id'],
				'role'					=> get_user_role_by_id($user_info['role_id']),
				'site_code'				=> '',
				'site_type'				=> '',
				'contact_person_name'	=> '',
				'events'				=> [],
				'is_school'				=> 0,
			];
		}
	}
}
