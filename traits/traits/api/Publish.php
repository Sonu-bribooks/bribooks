<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Publish {
	private function _errorMessagePublishLimit() {
		CI_Events::trigger('access_log', [
			'module'	=> 'app_reached_publishing_limit_' . ($this->input->post('book_id') ?? 0)
		]);

		return _li('Free Publishing Limit Reached. Buy BriBooks+ to unlock unlimited publishing.');
	}

	public function getPublishMessage() {
		$this->form_validation->set_rules('book_id', _l('book_id'), 'trim|required');

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($book_info = $this->book_model->get($this->input->post('book_id')))) {
				return $this->json['publish_content'] = [];
			}

			if (empty($book_event_info = $this->event_book_model->get_all([
				'book_id'		=> $book_info['id']
			])['rows'][0] ?? [])) {
				return $this->json['publish_content'] = [];
			}

			if (
				empty($event_info = $this->event_model->get($book_event_info['event_id'])) ||
				($event_info['book_writing_end_date'] <= date('Y-m-d H:i:s'))
			) {
				return $this->json['publish_content'] = [];
			}

			if (empty($publish_page_info = $this->event_landing_page_model->get_all([
				'event_id' => $event_info['id']
			])['rows'][0]['publish_page'] ?? [])) {
				return $this->json['publish_content'] = [];
			}

			$coupon_info = $this->coupon_model->get_all([
				'event_id'		=> $event_info['id'],
				'item_id'		=> $book_info['id']
			])['rows'][0] ?? [];

			$publish_page_info = json_decode($publish_page_info, true);

			if (empty($publish_page_info) || empty($publish_page_info['user']['content'])) {
				return $this->json['publish_content'] = [];
			}

			if (!empty($publish_page_info['user']['book_version']) && ($book_info['version'] != $publish_page_info['user']['book_version'])) {
				return $this->json['publish_content'] = [];
			}

			$diff_in_minutes = 0;

			if (!empty($publish_page_info['user']['time_duration'])) {
				$diff_in_minutes = abs(round((strtotime($book_info['date_published']) - strtotime($book_event_info['date_added'])) / 60));

				if ($diff_in_minutes >= $publish_page_info['user']['time_duration']) {
					return $this->json['publish_content'] = [];
				}
			}

			$publish_page_info['user']['content'] = html_entity_decode($publish_page_info['user']['content']);

			$data = [
				'book_name' 	=> $book_info['name'],
				'author_name' 	=> $book_info['author_name'],
				'label' 		=> strtoupper($event_info['label'] ?? ''),
				'discount' 		=> !empty($coupon_info['discount']) ? (int)($coupon_info['discount'] + 5) : 0,
				'duration'		=> !empty($coupon_info['date_end']) ? date('g:i A, d-m-Y', strtotime($coupon_info['date_end'])) : '',
			];

			$find 		= array_map(fn($item) => sprintf('{%s}', $item), array_keys($data));
			$replace 	= $data;
			$message 	= str_replace($find, $replace, $publish_page_info['user']['content']);

			$this->json['publish_content'] = [
				'book_name' 		=> $book_info['name'],
				'author_name' 		=> $book_info['author_name'],
				'event_label' 		=> $event_info['label'] ?? '',
				'discount' 			=> !empty($coupon_info['discount']) ? (int)($coupon_info['discount'] + 5) : 0,
				'coupon_end_date' 	=> $coupon_info['end_date'] ?? '',
				'diff_in_minutes'	=> $diff_in_minutes,
				'message' 			=> $message
			];
		}
	}

	public function checkCanPublish() {
		$this->form_validation->set_rules('book_id', _l('book_id'), 'trim|required');

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($book_info = $this->book_model->get($this->input->post('book_id')))) {
				$this->json['can_publish'] 	= false;
				$this->json['error'] 		= self::_errorMessagePublishLimit();
				return;
			}

			if (validate_user_subscription()) {
				$this->json['can_publish'] = true;
				return;
			}

			if (!empty($book_info['version'])) {
 				$this->json['can_publish'] = true;
				return;
			}

			$user_event_info = $this->event_user_model->get_all([
				'user_id'					=> $book_info['user_id'],
				'is_active_book_writing'	=> 1
			])['rows'][0] ?? [];

			if (!empty($user_event_info)) {
				$user_limit_info 	= $this->user_limit_model->get_all([
					'user_id' 	=> $book_info['user_id'],
					'event_id' 	=> $user_event_info['event_id'],
				])['rows'][0] ?? [];
			} else {
				$user_limit_info 	= $this->user_limit_model->get_all([
					'user_id' 	=> $book_info['user_id'],
					'event_id' 	=> 0,
				])['rows'][0] ?? [];
			}

			if (!empty($user_event_info)) {
				if (empty($user_event_info['publishing_limit'])) {
					$this->json['can_publish'] = true;
					return;
				}

				if (($user_limit_info['current'] ?? 0) < ($user_event_info['publishing_limit'] ?? 0)) {
					$this->json['can_publish'] = true;
					return;
				}

				if (!empty($user_limit_info['can_publish'])) {
					$this->json['can_publish'] = true;
					return;
				}

				$this->json['can_publish'] 	= false;
				$this->json['error'] 		= self::_errorMessagePublishLimit();
				return;
			}

			if (
				!empty($user_limit_info) &&
				(strtolower($user_limit_info['country_code']) != strtolower($this->config->item('site_country_code')))
			) {
				$this->json['can_publish'] = false;
				$this->json['error'] 		= self::_errorMessagePublishLimit();
				return;
			}

			$country_limit = $this->config->item('site_publishing_limit') ?? 0;

			if (empty($country_limit)) {
				$this->json['can_publish'] = true;
				return;
			}

			if (!empty($country_limit)) {
				if (($user_limit_info['current'] ?? 0) < ($country_limit ?? 0)) {
					$this->json['can_publish'] = true;
					return;
				}

				if (!empty($user_limit_info['can_publish'])) {
					$this->json['can_publish'] = true;
					return;
				}

				$this->json['can_publish'] 	= false;
				$this->json['error'] 		= self::_errorMessagePublishLimit();
				return;
			}

			$this->json['can_publish'] 	= false;
			$this->json['error'] 		= self::_errorMessagePublishLimit();

			return;;
		}
	}

	public function checkEventForceEnrol() {
		if ($this->json) {
			return;
		}

		if (
			!empty($book_info = $this->book_model->get($this->input->post('book_id') ?? 0)) &&
			($book_info['version'] != 0)
		) {
			$this->json = [
				'force_enrol' 	=> false,
				'error' 		=> ''
			];
			return;
		}

		$user_id = $this->session->userdata('user_id');

		if (!$user_id ||
			empty($user_info = $this->student_model->get($user_id)) ||
			empty($site_info = $this->site_model->get($user_info['site_id'] ?? 0))
		) {
			$this->json['login'] 	= true;
			$this->json['success'] 	= _l('login_to_publish');
			return;
		}

		$event_info = $this->event_model->get_all([
			'country_code'    => strtoupper($site_info['country_code']),
			'is_active_event' => 1,
			'force_enrol_in'  => [2,3],
			'start'           => 0,
			'limit'           => 1,
		])['rows'][0] ?? '';
		
		if (empty($event_info)) {
			return $this->json = [
				'force_enrol' 	=> false,
				'error' 		=> ''
			];
		}

		$event_user_info = $this->event_user_model->get_all([
			'user_id'			=> $user_info['id'],
			'country_code'		=> strtoupper($site_info['country_code']),
			'is_active_event' 	=> 1,
			'start'		   		=> 0,
			'limit'		   		=> 1,
		])['rows'][0] ?? null;

		if (empty($event_user_info)) {
			$this->json = [
				'force_enrol' 	=> true,
			];

			if (
				!empty($user_info) &&
				empty($user_info['mobile_verified']) &&
				empty($this->input->post('app_os')) &&
				(strtolower($user_info['location']) == 'india')
			) {
				$this->json['mobile'] 	= true;
				$this->json['success'] 	= _l('update_mobile_no');
				return;
			}

			if (
				!empty($user_info) &&
				empty($user_info['email_verified'])
			) {
				$this->json['email'] 	= true;
				$this->json['success'] 	= _l('update_email_address');
				return;
			}
		} else {
			$this->json = [
				'force_enrol' 	=> false,
				'error' 		=> ''
			];
		}
	}
}
