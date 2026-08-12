<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

load_trait('ranking');

final class Ranking_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;
		$this->input = $this->CI->input;

		$this->load->model('book/Book_model');
		$this->load->model('order/Order_model');

		$this->load->model('design/Category_model');
		$this->load->model('design/Genre_model');

		$this->load->model('common/Site_model');
		$this->load->model('common/Cron_model');

		$this->load->model('localisation/City_model');
		$this->load->model('localisation/State_model');
		$this->load->model('localisation/UnionTerritory_model');

		$this->load->model('ranking/RankingWeekly_model');
		$this->load->model('ranking/RankingDaily_model');
		$this->load->model('ranking/RankingCountry_model');
		$this->load->model('ranking/RankingState_model');
		$this->load->model('ranking/RankingCity_model');
		$this->load->model('ranking/RankingSchool_model');
		$this->load->model('ranking/RankingGeneral_model');
		$this->load->model('ranking/RankingGenre_model');
		$this->load->model('ranking/RankingGroup_model');

		$this->load->model('event/Event_model');
		$this->load->model('event/EventUser_model');
		$this->load->model('event/EventBook_model');
		$this->load->model('event/EventOrder_model');
		$this->load->model('event/EventChallengeWeekly_model');
		$this->load->model('event/EventChallengeDaily_model');
		$this->load->model('event/EventChallengeCountry_model');
		$this->load->model('event/EventChallengeState_model');
		$this->load->model('event/EventChallengeCity_model');
		$this->load->model('event/EventChallengeSchool_model');
		$this->load->model('event/EventChallengeWinnersDaily_model');
		$this->load->model('event/EventChallengeGeneral_model');
		$this->load->model('event/EventChallengeGenre_model');
		$this->load->model('event/EventChallengeGroup_model');
		$this->load->model('event/EventBookQualificationPending_model');
		$this->load->model('event/EventGroupBook_model');
		$this->load->model('ranking/LeagueBreakPointMessage_model');

		$this->load->model('user/User_model');
		$this->load->model('user/Student_model');
		$this->load->model('user/UserDeviceToken_model');
		$this->load->model('user/UserAppNotification_model');

		$this->load->library('Redis_lib');

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$this->book_model 								= $this->CI->Book_model;
		$this->student_model 							= $this->CI->Student_model;
		$this->user_model 								= $this->CI->User_model;
		$this->order_model 								= $this->CI->Order_model;
		$this->site_model 								= $this->CI->Site_model;
		$this->city_model 								= $this->CI->City_model;
		$this->state_model 								= $this->CI->State_model;
		$this->union_territory_model 					= $this->CI->UnionTerritory_model;

		$this->ranking_weekly_model 					= $this->CI->RankingWeekly_model;
		$this->ranking_daily_model 						= $this->CI->RankingDaily_model;
		$this->ranking_country_model 					= $this->CI->RankingCountry_model;
		$this->ranking_state_model 						= $this->CI->RankingState_model;
		$this->ranking_city_model 						= $this->CI->RankingCity_model;
		$this->ranking_school_model 					= $this->CI->RankingSchool_model;
		$this->ranking_general_model					= $this->CI->RankingGeneral_model;
		$this->ranking_genre_model						= $this->CI->RankingGenre_model;
		$this->ranking_group_model						= $this->CI->RankingGroup_model;

		$this->event_model								= $this->CI->Event_model;
		$this->event_user_model							= $this->CI->EventUser_model;
		$this->event_book_model							= $this->CI->EventBook_model;
		$this->event_order_model						= $this->CI->EventOrder_model;

		$this->event_challenge_weekly_model				= $this->CI->EventChallengeWeekly_model;
		$this->event_challenge_daily_model				= $this->CI->EventChallengeDaily_model;
		$this->event_challenge_country_model			= $this->CI->EventChallengeCountry_model;
		$this->event_challenge_state_model				= $this->CI->EventChallengeState_model;
		$this->event_challenge_city_model				= $this->CI->EventChallengeCity_model;
		$this->event_challenge_school_model				= $this->CI->EventChallengeSchool_model;
		$this->event_challenge_winners_daily_model		= $this->CI->EventChallengeWinnersDaily_model;
		$this->event_challenge_general_model			= $this->CI->EventChallengeGeneral_model;
		$this->event_challenge_genre_model				= $this->CI->EventChallengeGenre_model;
		$this->event_challenge_group_model				= $this->CI->EventChallengeGroup_model;

		$this->event_book_qualification_pending_model	= $this->CI->EventBookQualificationPending_model;
		$this->event_group_book_model					= $this->CI->EventGroupBook_model;
		$this->league_break_point_message_model			= $this->CI->LeagueBreakPointMessage_model;

		$this->user_device_token_model 					= $this->CI->UserDeviceToken_model;
		$this->user_app_notification_model 				= $this->CI->UserAppNotification_model;

		$this->category_model 							= $this->CI->Category_model;
		$this->genre_model								= $this->CI->Genre_model;

		$this->cron_model 								= $this->CI->Cron_model;
		$this->cache 									= $this->CI->cache;
		$this->redis_lib								= $this->CI->redis_lib;

		$this->limit = ENVIRONMENT === 'production' ? 10 : 10;

		$this->top_sites = ENVIRONMENT === 'production' ? [
			32,
			9,
			20,
			12,
			29,
			26,
			34,
			27,
			11,
			30,
			16,
			2,
		] : [
			2,
			27,
		];
	}

	use
		CountryRanking,
		StateRanking,
		CityRanking,
		SchoolRanking,
		WeeklyRanking,
		DailyRanking,
		GeneralRanking,
		GenreRanking,
		GroupRanking,
		LegendRanking,
		EventBookQualificationPending
	;

	private function _updateBookInfo($table, $id = 0, $data = []) {
		$this->db->update($table, [
			'author_name'	=> $data['author_name'],
			'author_image'	=> $data['author_image'],
			'book_image'	=> $data['book_image'],
			'book_name'		=> $data['book_name'],
			'book_slug'		=> $data['book_slug'],
		], [
			'id'			=> (int)$id
		]);
	}

	public function updateBookInfo($book_id = 0) {
		self::updateWeeklyBookInfo($book_id);
		self::updateDailyBookInfo($book_id);
		self::updateCountryBookInfo($book_id);
		self::updateStateBookInfo($book_id);
		self::updateCityBookInfo($book_id);
		self::updateSchoolBookInfo($book_id);
		self::updateGeneralBookInfo($book_id);
		self::updateGenreBookInfo($book_id);
		self::updateGroupBookInfo($book_id);
	}

	public function buildRanks($event_id = 0, $type = 'country') {
		$quantity_ges[10] = [
			'country'	=> 31,
			'state'		=> 11,
			'city'		=> 6,
			'school'	=> 1,
		];
		$quantity_les[10] = [
			'country'	=> 2000,
			'state'		=> 30,
			'city'		=> 10,
			'school'	=> 5,
		];
		$quantity_ges[12] = [
			'country'	=> 1,
			'school'	=> 1,
		];
		$quantity_les[12] = [
			'country'	=> 2000,
			'school'	=> 5,
		];
		$quantity_ge = $quantity_ges[$event_id][$type];
		$quantity_le = $quantity_les[$event_id][$type];
		$_method = sprintf('update%sRank', ucfirst($type));

		$event_info = $this->event_model->get($event_id);

		if (empty($event_info)) return;

		$results = $this->event_order_model->getSoldByBook([
			'event_id'		=> (int)$event_info['id'],
			'quantity_ge'	=> $quantity_ge,
			'quantity_le'	=> $quantity_le,
			'sort'			=> 'quantity',
			'order'			=> 'DESC',
		])['rows'] ?? [];

		// pr($results, 1);
		// pr(['buildRanks' => [
		// 	$event_info,
		// 	$_method,
		// 	$quantity_ge,
		// 	$quantity_le,
		// ]], 1);
		// return;

		foreach ($results as $item) {
			$book_info = $this->book_model->get($item['book_id']);
			// pr([
			// 	'event_info'	=> $event_info,
			// 	'book_info'		=> $book_info,
			// 	'product'		=> ['qauntity' => 0]
			// ], 1);
			self::{$_method}([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'product'		=> ['qauntity' => 0]
			]);
		}
	}

	public function buildRankByType($data = []) {
		$quantity_ges[14] = [
			'country'	=> 31,
			'state'		=> 11,
			'city'		=> 6,
			'school'	=> 1,
		];
		$quantity_les[14] = [
			'country'	=> 2000,
			'state'		=> 30,
			'city'		=> 10,
			'school'	=> 5,
		];
		$quantity_ges[15] = [
			'country'	=> 1,
		];
		$quantity_les[15] = [
			'country'	=> 2000,
		];
		$event_id = $data['event_id'] ?? 10;
		$type = $data['type'] ?? 'country';
		$type_id = $data[$type . '_id'] ?? 0;
		$quantity_ge = $quantity_ges[$event_id][$type];
		$quantity_le = $quantity_les[$event_id][$type];

		$_model = sprintf('event_challenge_%s_model', $type);
		$_method = sprintf('update%sRank', ucfirst($type));
		$_method_rank_key = sprintf('_get%sKey', ucfirst($type));

		$event_info = $this->event_model->get($event_id);
		$challenge_info = $this->{$_model}->get_all([
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (empty($challenge_info)) return;

		if ($type === 'country') {
			$rank_key = self::{$_method_rank_key}($event_id, $challenge_info['id']);
		} else {
			$rank_key = self::{$_method_rank_key}($event_id, $challenge_info['id'], $type_id);
		}

		// pr(['buildRanks' => [
		// 	$_model,
		// 	$_method,
		// 	$_method_rank_key,
		// 	$rank_key,
		// 	$quantity_ge,
		// 	$quantity_le,
		// ]], 1);
		// return;

		$filter_data = [
			'event_id'		=> (int)$event_info['id'],
			'quantity_ge'	=> $quantity_ge,
			'quantity_le'	=> $quantity_le,
			'sort'			=> 'quantity',
			'order'			=> 'DESC',
		];

		if ($type === 'school') {
			$filter_data['site_id'] = (int)$type_id;
		} elseif ($type === 'city') {
			$filter_data['city_id'] = (int)$type_id;
		} elseif ($type === 'state') {
			$filter_data['state_id'] = (int)$type_id;
		}

		$results = $this->event_order_model->getSoldByBook($filter_data)['rows'] ?? [];

		// pr($results, 1);
		// return;

		$this->redis_lib->removeRangeRank($rank_key, 0, 10000);

		foreach ($results as $item) {
			$book_info = $this->book_model->get($item['book_id']);

			self::{$_method}([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
				'product'		=> ['qauntity' => 0]
			]);
		}
	}

	public function updateRank($order_id = 0, $rank_type = 'all') {
		$order_info = $this->order_model->get($order_id);

		if (empty($order_info['status'])) return;

		log_kb([
			'updateRank'	=> $order_info
		]);

		$products = $this->order_model->getProducts($order_id);

		foreach ($products as $product) {
			$book_info = $this->book_model->get($product['product_id']);

			$user_events = $this->event_user_model->get_all([
				'user_id'	=> $book_info['user_id'],
			])['rows'] ?? [];

			log_kb([
				'updateRank::user_events'	=> $user_events
			]);

			foreach ($user_events as $user_event) {
				$event_info = $this->event_model->get($user_event['event_id']);

				$event_books = $this->event_book_model->get_all([
					'book_id'	=> (int)$book_info['id'],
					'event_id'	=> (int)$event_info['id'],
				])['rows'] ?? [];

				if (
					$event_info['start_date'] <= date('Y-m-d H:i:s') &&
					$event_info['end_date'] >= date('Y-m-d H:i:s') &&
					!empty($event_books)
				) {
					if ($rank_type != 'all') {
						$method = sprintf('update%sRank', ucfirst($rank_type));

						if (method_exists($this, $method)) {
							self::{$method}([
								'event_info'	=> $event_info,
								'book_info'		=> $book_info,
								'product'		=> $product,
							]);
						}

						continue;
					}

					self::updateGroupRank([
						'event_info'	=> $event_info,
						'book_info'		=> $book_info,
						'product'		=> $product,
					]);

					self::updateGeneralRank([
						'event_info'	=> $event_info,
						'book_info'		=> $book_info,
						'product'		=> $product,
					]);

					self::updateGenreRank([
						'event_info'	=> $event_info,
						'book_info'		=> $book_info,
						'product'		=> $product,
					]);

					self::updateWeeklyRank([
						'event_info'	=> $event_info,
						'book_info'		=> $book_info,
						'product'		=> $product,
					]);

					self::updateDailyRank([
						'event_info'	=> $event_info,
						'book_info'		=> $book_info,
						'product'		=> $product,
					]);

					self::updateSchoolRank([
						'event_info'	=> $event_info,
						'book_info'		=> $book_info,
						'product'		=> $product,
					]);

					self::updateCityRank([
						'event_info'	=> $event_info,
						'book_info'		=> $book_info,
						'product'		=> $product,
					]);

					self::updateStateRank([
						'event_info'	=> $event_info,
						'book_info'		=> $book_info,
						'product'		=> $product,
					]);

					self::updateCountryRank([
						'event_info'	=> $event_info,
						'book_info'		=> $book_info,
						'product'		=> $product,
					]);

					// self::_updateBookQualificationPendingStatus($event_info, $book_info);
				}
			}
		}
	}

	private function _moveToUpperLevel($rank_key = '', $rank_info = [], $current_stage = 'school') {

		$this->redis_lib->removeFromRank($rank_key, $rank_info['id']);

		$this->db->update('user_rank_' . $current_stage, [
			'is_moved'	=> 1,
		], [
			'id'		=> $rank_info['id']
		]);

		if ($current_stage = 'general') {
			$alert_payload['rank_data'] = array_merge(
				self::_formatGeneralRank(0, $rank_info),
				[
					'old_rank'	=> 0,
					'new_rank'	=> 0,
					'is_moved'	=> 1,
				]
			);

			self::_saveGeneralAlertForEveryOne($rank_info, $alert_payload);
		}

		self::_buildLegendLeague($current_stage, $rank_info);
	}

	private function _notifyAppUsers($to = '', $payload = []) {
		return;

		$result = send_android_notification(
			sprintf('/topics/%s', $to),
			$payload,
			true
		);
	}

	private function _sendAppNotification($data = []) {
		return;

		if (!RANKING_APP_NOTIFICATION) {
			return;
		}

		$book_info 	= $data['book_info'] ?? [];
		$event_info = $data['event_info'] ?? [];
		$relation 	= $data['relation'] ?? '=';
		$type 		= $data['type'] ?? 'school';

		$no_sold 	= $this->event_order_model->getTotalSoldByBook($event_info['id'], $book_info['id']);

		if ($relation !== '=') {
			$template_info = $this->db->get_where('event_ranking_app_notification_template', [
				'event_id'	=> (int)$event_info['id'],
				'_deleted'	=> 0,
				'type'		=> $type,
				'relation'	=> '=',
				sprintf('book_sold %s ', '=')	=> (int)$no_sold,
			])->row_array();
		}

		$template_info = !empty($template_info)
			? $template_info
			: $this->db->get_where('event_ranking_app_notification_template', [
				'event_id'	=> (int)$event_info['id'],
				'_deleted'	=> 0,
				'type'		=> $type,
				'relation'	=> $relation,
				sprintf('book_sold %s ', $relation)	=> (int)$no_sold,
			])->row_array();

		// log_kb(['_sendAppNotification' => $this->db->last_query()]);

		if (empty($template_info)) {
			return;
		}

		if ($token_info = $this->user_device_token_model->getByUser($book_info['user_id'])) {
			$author_info	= $this->student_model->get($book_info['user_id']);
			$school_info 	= $this->site_model->get($author_info['site_id']);
			$city_info 		= $this->city_model->get($author_info['city_id']);
			$state_info 	= $this->state_model->get($author_info['state_id']);

			$data = [
				'author_name'		=> $book_info['author_name'],
				'book_name'			=> $book_info['name'],
				'school_name'		=> $school_info['name'],
				'city_name'			=> $city_info['name'],
				'state_name'		=> $state_info['name'],
				'achievement_url'	=> sprintf('%saccount/mycertificates?active=league', USER_URL),
				'rank_url'			=> $event_info['rank_url'],
			];

			if ($template_info['type'] === 'school') {
				$data['rank_url']	= sprintf('%s/school/%s?trid=%s', rtrim($event_info['rank_url'], '/'), $author_info['site_id'], $author_info['id']);
			} elseif ($template_info['type'] === 'city') {
				$data['rank_url']	= sprintf('%s/city/%s?trid=%s', rtrim($event_info['rank_url'], '/'), $author_info['city_id'], $author_info['id']);
			} elseif ($template_info['type'] === 'state') {
				$data['rank_url']	= sprintf('%s/state/%s?trid=%s', rtrim($event_info['rank_url'], '/'), $author_info['state_id'], $author_info['id']);
			} else {
				$data['rank_url']	= sprintf('%s/?trid=%s', rtrim($event_info['rank_url'], '/'), $author_info['id']);
			}

			$payload = [
				'title'	=> self::_formatNotificationTemplate($data, $template_info['title']),
				'body'	=> self::_formatNotificationTemplate($data, $template_info['body']),
			];

			$payload['image'] = !empty($template_info['image'])
				? $this->config->item('cloudfront_url') . 'public/EventGallery/EventImages/notifications/' . $template_info['image']
				: ''
			;

			$user_app_notification_id = $this->user_app_notification_model->add([
				'user_id'			=> $author_info['id'],
				'title'				=> $payload['title'],
				'body'				=> $payload['body'],
				'message'			=> self::_formatNotificationTemplate($data, $template_info['message']),
				'url'				=> '',
				'attachment_type'	=> !empty($payload['image']) ? 1 : 0,
				'attachment_file'	=> $payload['image'] ?? '',
			]);

			$notification_info = $this->user_app_notification_model->get($user_app_notification_id);

			$payload['data'] = [
				'id'				=> $notification_info['id'],
				'title'				=> $notification_info['title'],
				'body'				=> $notification_info['body'],
				'message'			=> $notification_info['message'],
				'url'				=> '',
				'attachment_type'	=> $notification_info['attachment_type'],
				'attachment_file'	=> $payload['image'] ?? '',
				'date_added'		=> $notification_info['date_added'],
			];

			$result = send_android_notification($token_info['device_token'], $payload);

			log_kb(['ranking_upgrade_app_notifications:: ' => [
				'type'			=> $template_info['type'],
				'user_id'		=> $author_info['id'],
				'token'			=> $token_info['device_token'],
			]]);

			log_kb(['RankingUpgrade::SendPush::' => $result]);
		}
	}

	private function _formatNotificationTemplate($data = [], $message = '') {
		$find = [
			'{author_name}',
			'{book_name}',
			'{school_name}',
			'{city_name}',
			'{state_name}',
			'{achievement_url}',
			'{rank_url}',
		];

		$replace = [
			'author_name'		=> $data['author_name'] ?? '',
			'book_name'			=> $data['book_name'] ?? '',
			'school_name'		=> $data['school_name'] ?? '',
			'city_name'			=> $data['city_name'] ?? '',
			'state_name'		=> $data['state_name'] ?? '',
			'achievement_url'	=> $data['achievement_url'] ?? '',
			'rank_url'			=> $data['rank_url'] ?? '',
		];

		return str_replace($find, $replace, $message);
	}

	private function _getCopyText($sold = 0) {
		if ($sold > 1) {
			return _l('Copies');
		}

		return _l('Copy');
	}

	private function _isEarlyAccess($book_id = 0) {
		if (empty($book_info = $this->book_model->get($book_id))) return false;

		if ($book_info['date_published'] < '2023-10-31') {
			return true;
		}

		return false;
	}

	private function _isPrimeAuthor($book_id = 0) {
		if (empty($book_info = $this->book_model->get($book_id))) return false;

		if ($book_info['date_published'] > '2023-11-01' && $book_info['date_published'] < '2023-11-30') {
			return true;
		}

		return false;
	}

	private function formatLeagueMessage($message, $data = []) {
		$find = [
			'{required_sold_count}',
			'{copy_text}',
		];

		$replace = [
			'required_sold_count'	=> $data['required_sold_count'] ?? '',
			'copy_text'				=> $data['copy_text'] ?? '',
		];

		return str_replace($find, $replace, $message);
	}
}
