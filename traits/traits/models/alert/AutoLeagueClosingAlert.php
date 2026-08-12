<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait AutoLeagueClosingAlert {
	public function scheduleAutoCloseLeague($data = []) {
		if (
			empty($data) ||
			empty($data['challenge_id']) ||
			empty($data['type'])
		) return;

		$this->load->model('common/Cron_model', 'cron_model');

		$model_name = sprintf('event_challenge_%s_model', strtolower($data['type']));
		$this->load->model(sprintf('event/EventChallenge%s_model', ucwords($data['type'])), $model_name);
		$challenge_info = $this->{$model_name}->get($data['challenge_id']);

		if (strtotime($challenge_info['end_date']) < time()) return;

		$code = sprintf('leagueClosingCron_%s_%s_%s', $data['type'], $challenge_info['event_id'], $data['challenge_id']);

		$update_data = [
			'code'			=> $code,
			'action'		=> 'alert_model->leagueClosingCron',
			'data'			=> [[
				'event_id'		=> $challenge_info['event_id'],
				'challenge_id'	=> $challenge_info['id'],
				'type'			=> $data['type'],
				'limit'			=> $challenge_info['rank_limit'] ?? 0,
				'need_invite'	=> $challenge_info['need_invite'] ?? 0,
				'need_image'	=> $challenge_info['need_image'] ?? 0,
				'need_address'	=> $challenge_info['need_address'] ?? 0,
			]],
			'site_id'		=> 1,
			'status'		=> 0,
			'alert_date'	=> date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime($challenge_info['end_date']))),
		];

		if (!empty($cron_info = $this->cron_model->getByCode($code))) {
			$this->cron_model->edit($cron_info['id'], $update_data);
		} else {
			$this->cron_model->add($update_data);
		}
	}

	public function leagueClosingCron($data = []) {
		log_kb([
			'leagueClosingCron' => $data
		]);

		if (
			empty($data) ||
			empty($data['event_id']) ||
			empty($data['challenge_id']) ||
			empty($data['type'])
		) return;

		$multiple_leagues = ['school', 'city', 'state', 'genre', 'group'];

		if (in_array(strtolower($data['type']), $multiple_leagues)) {
			$challenge_key 	= strtolower($data['type']) === 'genre'
				? 'challenge_id'
				: sprintf('event_challenge_%s_id', strtolower($data['type']));
			$results 		= array_column(
				$this->db
					->select(sprintf('distinct %s_id as item_id', strtolower($data['type'])), false)
					->where('event_id', (int)$data['event_id'])
					->where($challenge_key, (int)$data['challenge_id'])
					->where('_deleted', 0)
					->get(sprintf('user_rank_%s', strtolower($data['type'])))
					->result_array(),
				'item_id'
			);

			foreach ($results as $item_id) {
				$data[sprintf('%s_id', strtolower($data['type']))] = $item_id;
				self::_leagueClosingSingle($data);
			}
		} else {
			self::_leagueClosingSingle($data);
		}
	}

	private function _leagueClosingSingle($data = []) {
		log_kb([
			'leagueClosingSingleCron' => $data
		]);

		$this->load->model('event/Event_model', 'event_model');
		$this->load->library('Redis_lib');

		if (
			empty($data) ||
			empty($data['event_id']) ||
			empty($data['challenge_id']) ||
			empty($data['type']) ||
			empty($event_info = $this->event_model->get($data['event_id']))
		) return;

		if (empty($rank_key = self::_getLeagueClosingRankKey($data))) return;

		$rows = [];

		$model_file_path = sprintf(APPPATH . 'models/ranking/Ranking%s_model.php', ucwords($data['type']));

		if (file_exists($model_file_path)) {
			$this->load->model(sprintf('ranking/Ranking%s_model', ucwords($data['type'])), sprintf('ranking_%s_model', strtolower($data['type'])));

			$model_name = sprintf('ranking_%s_model', strtolower($data['type']));

			$filter = [
				'event_id' 		=> (int)$data['event_id'],
				'challenge_id' 	=> (int)$data['challenge_id'],
				'start'			=> 0,
				'limit'			=> (int)(($data['limit'] ?? 200) + 50),
			];

			if (!empty($data['city_id'])) {
				$filter['city_id'] = $data['city_id'] ?? 0;
			}

			if (!empty($data['state_id'])) {
				$filter['state_id'] = $data['state_id'] ?? 0;
			}

			if (!empty($data['genre_id'])) {
				$filter['genre_id'] = $data['genre_id'] ?? 0;
			}

			if (!empty($data['group_id'])) {
				$filter['group_id'] = $data['group_id'] ?? 0;
			}

			$rows = $this->{$model_name}->get_all($filter)['rows'] ?? [];
		}

		if (empty($rows)) return;

		$table_name = sprintf('user_rank_%s', strtolower($data['type']));

		foreach ($rows as $key => $row) {
			if (empty($rank_info = $this->{$model_name}->get($row['id']))) continue;

			$rank = $this->redis_lib->getRank($rank_key, $rank_info['id']) + 1;

			if (!empty($rank)) {
				$this->db->update($table_name, [
					'rank'			=> $rank
				], [
					'id'			=> (int)$rank_info['id']
				]);

				if (!empty($data['need_invite'])) {
					self::_addInviteGuest([
						'event_id'		=> $data['event_id'],
						'challenge_id'	=> $data['challenge_id'],
						'type'			=> $data['type'],
						'user_id'		=> $rank_info['user_id'],
						'book_id'		=> $rank_info['book_id'],
						'book_rank'		=> $rank,
						'score'			=> $rank_info['score'],
					]);
				}
			}
		}

		$this->cron_model->add([
			'code'			=> sprintf('generateLeagueCertificateCron_%s_%s_%s', $data['type'], $data['event_id'], $data['challenge_id']),
			'action'		=> 'alert_model->generateLeagueCertificateCron',
			'data'			=> [$data],
			'site_id'		=> 1,
			'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
				? '+5 minutes'
				: '+1 minutes'
			)),
		]);

		$this->cron_model->add([
			'code'			=> sprintf('sendLeagueMessageCron_%s_%s_%s', $data['type'], $data['event_id'], $data['challenge_id']),
			'action'		=> 'alert_model->sendLeagueMessageCron',
			'data'			=> [$data],
			'site_id'		=> 1,
			'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
				? '+5 minutes'
				: '+1 minutes'
			)),
		]);
	}

	private function _addInviteGuest($data = []) {
		$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');
		$already_invited = $this->event_user_invite_model->get_all([
			'event_id'		=> (int)$data['event_id'],
			'challenge_id'	=> (int)$data['challenge_id'],
			'challenge_type'=> $data['type'],
			'book_id'		=> (int)$data['book_id'],
			'is_jury'		=> 0,
			'start'			=> 0,
			'limit'			=> 1,
		])['rows'][0] ?? [];

		if (empty($already_invited)) {
			$this->event_user_invite_model->add([
				'event_id'		=> (int)$data['event_id'],
				'challenge_id'	=> (int)$data['challenge_id'],
				'challenge_type'=> $data['type'],
				'user_id'		=> (int)$data['user_id'],
				'book_id'		=> (int)$data['book_id'],
				'book_rank'		=> (int)$data['book_rank'],
				'book_sold'		=> (int)$data['score'],
			]);
		}
	}

	private function _getLeagueClosingRankKey($data = []) {
		$rank_key 		= '';
		$environment 	= (ENVIRONMENT === 'production' ? 'live' : 'test');

		extract($data);

		switch ($type) {
			case 'daily':
				$rank_key = vsprintf('live_author_daily_ranks_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
				]);
				break;

			case 'weekly':
				$rank_key = vsprintf('live_author_weekly_ranks_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
				]);
				break;

			case 'general':
				$rank_key = vsprintf('live_author_general_ranks_%s_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
					0,
				]);
				break;

			case 'genre':
				$rank_key = vsprintf('live_author_genre_ranks_%s_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
					$genre_id,
				]);
				break;

			case 'city':
				$rank_key = vsprintf('live_author_city_ranks_%s_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
					$city_id,
				]);
				break;

			case 'group':
				$rank_key = vsprintf('live_author_group_ranks_%s_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
					$group_id,
				]);
				break;

			case 'state':
				$rank_key = vsprintf('live_author_state_ranks_%s_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
					$state_id,
				]);
				break;

			case 'country':
				$rank_key = vsprintf('live_author_country_ranks_%s_%s_%s', [
					$environment,
					$event_id,
					$challenge_id,
				]);
				break;

			default:
				$rank_key = '';
				break;
		}

		return $rank_key;
	}

	public function generateLeagueCertificateCron($data = []) {
		log_kb([
			'generateLeagueCertificateCron' => $data
		]);

		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');
		$this->load->model('user/User_model', 'user_model');

		if (
			empty($data) ||
			empty($data['event_id']) ||
			empty($data['challenge_id']) ||
			empty($data['type']) ||
			empty($data['limit'])
		) return;

		$model_file_path = sprintf(APPPATH . 'models/ranking/Ranking%s_model.php', ucwords($data['type']));

		$ranks = [];

		if (file_exists($model_file_path)) {
			$model_name = sprintf('ranking_%s_model', strtolower($data['type']));
			$this->load->model(sprintf('ranking/Ranking%s_model', ucwords($data['type'])), $model_name);

			$ranks = $this->{$model_name}->get_all([
				'event_id' 		=> $data['event_id'],
				'challenge_id' 	=> $data['challenge_id'],
				'country_id' 	=> $data['country_id'] ?? 0,
				'state_id' 		=> $data['state_id'] ?? 0,
				'city_id' 		=> $data['city_id'] ?? 0,
				'group_id' 		=> $data['group_id'] ?? 0,
				'rank_gte'		=> 1,
				'sort'			=> sprintf('user_rank_%s.rank', strtolower($data['type'])),
				'order'			=> 'ASC',
				'start'			=> 0,
				'limit' 		=> $data['limit'] ?? 50,
			])['rows'] ?? [];

			log_kb([
				'generateLeagueCertificateCron::ranks' => $ranks
			]);

			if (empty($ranks)) return;
			if (empty($template_info = $this->certificate_template_model->get_all([
				'event_id'		=> (int)$data['event_id'],
				'challenge_id'	=> (int)$data['challenge_id'],
				'challenge_type'=> $data['type'],
				'has_rank'		=> 1,
			])['rows'][0] ?? [])) return;

			foreach ($ranks as $key => $rank) {
				if (empty($certificate_info = $this->certificate_model->get_all([
					'book_id' 					=> $rank['book_id'],
					'certificate_template_id' 	=> $template_info['id'],
				])['rows'] ?? [])) {
					$certificate_key = vsprintf('%s_rank_%s_%s_%s_%s', [
						$template_info['challenge_type'],
						$data['event_id'],
						$data['challenge_id'],
						$rank['user_id'],
						$rank['book_id']
					]);
					$user_info = $this->user_model->get($rank['user_id']);

					$this->certificate_model->add([
						'site_id'					=> $user_info['site_id'] ?? 1,
						'event_id'					=> (int)$data['event_id'],
						'book_id'					=> $rank['book_id'],
						'user_id'					=> $rank['user_id'],
						'rank'						=> !empty($rank['rank']) ? $rank['rank'] : ($key + 1),
						'type'						=> $template_info['type'],
						'certificate_template_id'	=> $template_info['id'],
						'achievement'				=> $template_info['achievement'],
						'unique_id'					=> $template_info['id'],
						'name'						=> $template_info['name'],
						'type'						=> $certificate_key,
						'image'						=> $certificate_key,
					]);
				}
			}
		}
	}

	public function sendLeagueMessageCron($data = []) {
		log_kb([
			'sendLeagueMessageCron' => $data
		]);

		if (empty($data)) return;

		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('common/LeagueTemplate_model', 'league_template_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');
		$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventUserInviteCode_model', 'event_user_invite_code_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/Country_model', 'country_model');

		$rows = [];

		$model_file_path = sprintf(APPPATH . 'models/ranking/Ranking%s_model.php', ucwords($data['type']));

		if (file_exists($model_file_path)) {
			$model_name = sprintf('ranking_%s_model', strtolower($data['type']));
			$this->load->model(sprintf('ranking/Ranking%s_model', ucwords($data['type'])), $model_name);

			$rows = $this->{$model_name}->get_all([
				'event_id' 		=> (int)$data['event_id'],
				'challenge_id' 	=> (int)$data['challenge_id'],
				'city_id' 		=> $data['city_id'] ?? 0,
				'state_id' 		=> $data['state_id'] ?? 0,
				'group_id' 		=> $data['group_id'] ?? 0,
				'rank_gte'		=> 1,
				'sort'			=> sprintf('user_rank_%s.rank', strtolower($data['type'])),
				'order'			=> 'ASC',
				'start'			=> $data['start'] ?? 0,
				'limit' 		=> $data['limit'] ?? 50,
			])['rows'] ?? [];
		}

		log_kb([
			'sendLeagueMessageCron::rows' => $rows
		]);

		if (empty($rows)) return;

		$model_name = sprintf('event_challenge_%s_model', strtolower($data['type']));
		$this->load->model(sprintf('event/EventChallenge%s_model', ucwords($data['type'])), $model_name);

		$event_info 		= $this->event_model->get($data['event_id'] ?? 0);
		$challenge_info 	= $this->{$model_name}->get($date['challenge_id'] ?? 0);
		$cert_url			= sprintf('%saccount/mycertificates?active=league', USER_URL);

		if (empty($templates = $this->league_template_model->get_all([
			'event_id'		=> (int)$data['event_id'],
			'challenge_id'	=> (int)$data['challenge_id'],
		])['rows'] ?? [])) {
			return;
		}

		foreach ($rows as $key => $row) {
			if (empty($author_info = $this->user_model->get($row['user_id']))) continue;

			$template_info 	= self::_getLeagueTemplate($templates, $row['rank']);

			if (empty($template_info)) continue;

			$site_info 		= $this->site_model->get($author_info['site_id'] ?? 0);
			$city_info 		= !empty($row['city_id']) ? $this->city_model->get($row['city_id'] ?? 0) : [];
			$state_info 	= !empty($row['state_id']) ? $this->state_model->get($row['state_id'] ?? 0) : [];
			$country_info 	= !empty($row['state_id']) ? $this->country_model->get($row['country_id'] ?? 0) : [];

			if (!empty($data['need_image'])) {
				if (!empty($code_info = $this->event_user_invite_code_model->get_all([
					'event_id' 	=> $row['event_id'],
					'user_id' 	=> $row['user_id'],
				])['rows'][0] ?? [])) {
					$code 		= $code_info['code'];
				} else {
					$password 	= uniqid();
					$code 		= sha1(md5(($data['user_id']) . $password . $this->config->item('password_salt') . $data['event_id']));

					$this->event_user_invite_code_model->add([
						'event_id'	=> $row['event_id'],
						'user_id'	=> $row['user_id'],
						'code'		=> $code,
					]);
				}
			}

			if (!empty($data['need_address'])) {
				$this->load->model('user/UserAwardAddress_model', 'user_award_address_model');

				$address_info = $this->user_award_address_model->get_all([
					'user_id'	=> (int)$row['user_id'],
					'event_id'	=> (int)$row['event_id'] ?? 0,
				])['rows'][0] ?? '';

				if (empty($address_info)) {
					$this->user_award_address_model->add([
						'user_id'	=> (int)$row['user_id'] ?? 0,
						'event_id'	=> (int)$row['event_id'] ?? 0,
						'status'	=> 0,
					]);
				}
			}

			$league_url 	= $challenge_info['base_url'] . $challenge_info['slug'];
			$invite_url 	= !empty($code)
				? vsprintf('/submitdetails/bs?uid=%s&code=%s&bid=%s&eid=%s', [
					$author_info['id'],
					$code,
					$row['book_id'],
					$row['event_id'],
				])
				: '';

			$variables = [
				'first_name'	  		=> $author_info['first_name'],
				'author_name'	  		=> !empty($row['author_name']) ? $row['author_name'] : ($author_info['first_name'] . ' ' . $author_info['last_name']),
				'book_name'	  			=> $row['book_name'],
				'rank'	  				=> !empty($row['rank']) ? $row['rank'] : ($key + 1),
				'invite_url'			=> $invite_url,
				'cert_url'				=> $cert_url,
				'school_name'			=> $site_info['name'] ?? '',
				'city'					=> $city_info['name'] ?? '',
				'state'					=> $state_info['name'] ?? '',
				'country'				=> $country_info['name'] ?? '',
				'league_url'			=> $league_url,
			];

			$subject	= format_message_with_data($template_info['subject'], $variables);
			$content 	= format_message_with_data($template_info['body'], $variables);

			$data['title']		  	= $subject;
			$data['content']		= $content;
			$message				= $this->load->view('common/mail/templates/site/general', $data, true);

			if (!empty($subject) && !empty($content) && !empty($author_info['email'])) {
				self::email(
					$author_info['email'],
					$subject,
					$message,
					[],
					$template_info['bcc'] ?? [],
					[]
				);
			}

			if (!empty($template_info['whatsapp_template_id']) && !empty($author_info['mobile'])) {
				$variables['cert_url'] = $cert_url;

				self::_sendOnextelWhatsapp(
					trim($author_info['mobile']),
					[
						'template_id'	=> $template_info['whatsapp_template_id'],
						'parameters' 	=> format_whatsapp_sms_message($template_info['whatsapp_message'], $variables),
					]
				);
			}
		}
	}

	private function _getLeagueTemplate($templates = [], $rank = 0) {
		foreach ($templates as $item) {
			if ($item['min_rank'] <= $rank && $item['max_rank'] >= $rank) {
				return $item;
			}
		}
	}
}
