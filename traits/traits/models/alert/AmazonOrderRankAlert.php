<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait AmazonOrderRankAlert {
    public function buildAmazonRankCron($data = []) {
		$event_id		= $data['event_id'] ?? 0;
		$challenge_id	= $data['challenge_id'] ?? 0;
		$type 			= $data['type'] ?? 'amazon';

		if (empty($event_id)) return;

		log_kb(['buildAmazonRankCron' => [$event_id, $challenge_id, $type]]);

		$this->load->library('Ranking_lib', 'ranking_lib');

		$results = $this->db
			->select('
				event_order_amazon.event_id,
				event_order_amazon.book_id,
				MAX(event_order_amazon.id) as order_id,
				SUM(event_order_amazon.quantity) as sold_count
			')
			->from('event_order_amazon')
			->join('book', 'book.id=event_order_amazon.book_id')
			->join('users', 'users.id=event_order_amazon.user_id')
			->where('event_order_amazon._deleted', 0)
			->where('event_order_amazon.event_id', (int)$event_id)
			->where('book._deleted', 0)
			->where('book.archived', 0)
			->where('book.status', 1)
			->where('users._deleted', 0)
			->group_by('event_order_amazon.book_id')
			->get()
			->result_array();

		log_kb(['buildAmazonRankCron' => [$event_id, $challenge_id, $type, $results]]);

		foreach ($results as $key => $item) {
			if (
				!empty($book_info   = $this->book_model->get($item['book_id'])) &&
				!empty($author_info = $this->student_model->get($book_info['user_id']))
			) {
				$this->ranking_lib->updateRankForAmazon($item['order_id'], $type);
			}
		}

        $this->scheduleAmazonCloseLeague([
			'type'			=> 'amazon',
			'challenge_id'	=> $challenge_id,
		]);

	}

    public function scheduleAmazonCloseLeague($data = []) {
		if (
			empty($data) ||
			empty($data['challenge_id']) ||
			empty($data['type'])
		) return;

		$this->load->model('common/Cron_model', 'cron_model');
        $this->load->model('event/EventChallengeAmazon_model', 'event_challenge_amazon_model');

		$challenge_info = $this->event_challenge_amazon_model->get($data['challenge_id']);

		// if (strtotime($challenge_info['end_date']) < time()) return;

		$code = sprintf('AmazonleagueClosingCron_%s_%s_%s', $data['type'], $challenge_info['event_id'], $data['challenge_id']);
        $now = date('Y-m-d H:i:s');
        $end_date = $challenge_info['end_date'];

        $alert_date = (strtotime($now) >= strtotime($end_date))
            ? date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime($now)))
            : date('Y-m-d H:i:s', strtotime('+5 minutes', strtotime($end_date)));

		$update_data = [
			'code'			=> $code,
			'action'		=> 'alert_model->AmazonleagueClosingCron',
			'data'			=> [[
				'event_id'		=> $challenge_info['event_id'],
				'challenge_id'	=> $challenge_info['id'],
				'type'			=> $data['type'],
				'is_moved'		=> $challenge_info['is_moved'] ?? 0,
				'limit'			=> $challenge_info['rank_limit'] ?? 0,
				'need_invite'	=> $challenge_info['need_invite'] ?? 0,
				'need_image'	=> $challenge_info['need_image'] ?? 0,
				'need_address'	=> $challenge_info['need_address'] ?? 0,
			]],
			'site_id'		=> 1,
			'status'		=> 0,
			'alert_date'	=> $alert_date,
		];

		if (!empty($cron_info = $this->cron_model->getByCode($code))) {
			$this->cron_model->edit($cron_info['id'], $update_data);
		} else {
			$this->cron_model->add($update_data);
		}
	}

    public function AmazonleagueClosingCron($data = []) {
		log_kb([
			'AmazonleagueClosingCron' => $data
		]);

		if (
			empty($data) ||
			empty($data['event_id']) ||
			empty($data['challenge_id']) ||
			empty($data['type'])
		) return;
        
        $this->load->model('event/Event_model', 'event_model');
		

        if(empty($this->event_model->get($data['event_id']))) return;

        if (empty($rank_key = self::_getAmazonLeagueClosingRankKey($data))) return;

		self::_buildAmazonLeagueRank($data, $rank_key);
		
	}

    private function _buildAmazonLeagueRank($data = [], $rank_key = '', $is_moved = 0) {
		$rows = [];

		$this->load->model('ranking/RankingAmazon_model', 'ranking_amazon_model');
        $this->load->library('Redis_lib');

		$filter = [
			'event_id' 		=> (int)$data['event_id'],
			'challenge_id' 	=> (int)$data['challenge_id'],
			'start'			=> 0,
			'limit'			=> (int)(($data['limit'] ?? 200) + 50),
		];

		if ($is_moved) {
			$filter['is_moved'] = $is_moved;
			$filter['limit']	= 1000;
		}

		$rows = $this->ranking_amazon_model->get_all($filter)['rows'] ?? [];

		if (empty($rows)) return;

		foreach ($rows as $key => $row) {
			if (empty($rank_info = $this->ranking_amazon_model->get($row['id']))) continue;

			$rank = $this->redis_lib->getRank($rank_key, $rank_info['id']) + 1;

			if (!empty($rank)) {
				$this->db->update('user_rank_amazon', [
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
			'code'			=> sprintf('generateAmazonLeagueCertificateCron_%s_%s_%s', $data['type'], $data['event_id'], $data['challenge_id']),
			'action'		=> 'alert_model->generateAmazonLeagueCertificateCron',
			'data'			=> [$data],
			'site_id'		=> 1,
			'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
				? '+5 minutes'
				: '+1 minutes'
			)),
		]);

		$this->cron_model->add([
			'code'			=> sprintf('sendAmazonLeagueMessageCron_%s_%s_%s', $data['type'], $data['event_id'], $data['challenge_id']),
			'action'		=> 'alert_model->sendAmazonLeagueMessageCron',
			'data'			=> [$data],
			'site_id'		=> 1,
			'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
				? '+5 minutes'
				: '+1 minutes'
			)),
		]);
	}

    private function _getAmazonLeagueClosingRankKey($data = []) {
		extract($data);

		return vsprintf('live_author_amazon_ranks_%s_%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$event_id,
			$challenge_id,
		]);
	}

    public function generateAmazonLeagueCertificateCron($data = []) {
		self::_generateAmazonLeagueCertificate($data);

		if (!empty($data['is_moved'])) {
			self::_generateAmazonLeagueCertificate($data, $data['is_moved']);
		}
	}

	private function _generateAmazonLeagueCertificate($data = [], $is_moved = 0) {
		log_kb([
			'generateAmazonLeagueCertificateCron' => $data
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

		$ranks = [];

        $this->load->model('ranking/RankingAmazon_model', 'ranking_amazon_model');

        $ranks = $this->ranking_amazon_model->get_all([
            'event_id' 		=> $data['event_id'],
            'challenge_id' 	=> $data['challenge_id'],
            'country_id' 	=> $data['country_id'] ?? 0,
            'is_moved' 		=> $is_moved,
            'rank_gte'		=> 1,
            'sort'			=> 'user_rank_amazon.rank',
            'order'			=> 'ASC',
            'start'			=> 0,
            'limit' 		=> $is_moved ? 1000 : ($data['limit'] ?? 50),
        ])['rows'] ?? [];

        log_kb([
            'generateAmazonLeagueCertificateCron::ranks' => $ranks
        ]);

        if (empty($ranks)) return;
        if (empty($template_info = $this->certificate_template_model->get_all([
            'event_id'		=> (int)$data['event_id'],
            'challenge_id'	=> (int)$data['challenge_id'],
            'challenge_type'=> $data['type'],
            'is_moved' 		=> $is_moved,
            'has_rank'		=> 1,
            'is_jury'		=> 0,
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

	public function sendAmazonLeagueMessageCron($data = []) {
		self::_sendAmazonLeagueMessageCron($data);

		if (!empty($data['is_moved'])) {
			self::_sendAmazonLeagueMessageCron($data, $data['is_moved']);
		}
	}

	private function _sendAmazonLeagueMessageCron($data = [], $is_moved = 0) {
		log_kb([
			'sendAmazonLeagueMessageCron' => $data
		]);

		if (empty($data)) return;

		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('common/LeagueTemplate_model', 'league_template_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventUserInviteCode_model', 'event_user_invite_code_model');
		$this->load->model('localisation/Country_model', 'country_model');

		$rows = [];

        $this->load->model('ranking/RankingAmazon_model', 'ranking_amazon_model');
        
        $rows = $this->ranking_amazon_model->get_all([
            'event_id' 		=> (int)$data['event_id'],
            'challenge_id' 	=> (int)$data['challenge_id'],
            'is_moved'		=> $is_moved,
            'rank_gte'		=> 1,
            'sort'			=> 'user_rank_amazon.rank',
            'order'			=> 'ASC',
            'start'			=> $data['start'] ?? 0,
            'limit' 		=> $is_moved ? 1000 : ($data['limit'] ?? 50),
        ])['rows'] ?? [];
		

		log_kb([
			'sendAmazonLeagueMessageCron::rows' => $rows
		]);

		if (empty($rows)) return;

		$this->load->model('event/EventChallengeAmazon_model', 'event_challenge_amazon_model');

		$event_info 		= $this->event_model->get($data['event_id'] ?? 0);
		$challenge_info 	= $this->event_challenge_amazon_model->get($data['challenge_id'] ?? 0);
		$cert_url			= sprintf('%saccount/mycertificates?active=league', USER_URL);

		if (empty($templates = $this->league_template_model->get_all([
			'event_id'		=> (int)$data['event_id'],
			'challenge_id'	=> (int)$data['challenge_id'],
			'is_moved'		=> (int)$is_moved,
		])['rows'] ?? [])) {
			return;
		}

		foreach ($rows as $key => $row) {
			if (empty($author_info = $this->user_model->get($row['user_id']))) continue;

			$template_info 	= self::_getLeagueTemplate($templates, $row['rank']);

			if (empty($template_info)) continue;

			$site_info 		= $this->site_model->get($author_info['site_id'] ?? 0);
			$country_info 	= !empty($row['country_id']) ? $this->country_model->get($row['country_id'] ?? 0) : [];

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
}