<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportGeneric {
	private function _importJuryBook($rows = [], $map = [], $job_id = 0) {
		$this->load->model('event/EventJuryBook_model', 'event_jury_book_model');

		$skipped = $uploaded 	= 0;
		$challenge_slug 		= '';

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['event_id'])) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			if (empty($data['book_id'])) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			if (empty($book_info = $this->book_model->get($data['book_id']))) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			if (!empty($data['user_id']) && $book_info['user_id'] != $data['user_id']) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			if (empty($jury_book_info = $this->event_jury_book_model->get_all([
				'event_id' 		=> $data['event_id'],
				'challenge_id' 	=> $data['challenge_id'],
				'type' 			=> $data['type'],
				'book_id' 		=> $data['book_id'],
			])['rows'][0] ?? [])) {
				if (empty($challenge_slug)) {
					$model_file_path = sprintf(APPPATH . 'models/event/EventChallenge%s_model.php', ucwords($data['type']));

					if (!empty($data['challenge_id']) && file_exists($model_file_path)) {
						$this->load->model(sprintf('event/EventChallenge%s_model', ucwords($data['type'])), sprintf('event_challenge_%s_model', strtolower($data['type'])));

						$model_name = sprintf('event_challenge_%s_model', strtolower($data['type']));

						$challenge_info = $this->{$model_name}->get($data['challenge_id']);

						$challenge_slug = $challenge_info['slug'];
					}
				}

				$this->event_jury_book_model->add([
					'type'				=> $data['type'] ?? '',
					'jury_challenge_id'	=> $data['jury_challenge_id'] ?? 0,
					'challenge_id'		=> $data['challenge_id'] ?? 0,
					'event_id'			=> $data['event_id'],
					'challenge_slug'	=> $challenge_slug,
					'book_id'			=> !empty($data['book_id']) ? $data['book_id'] : $book_info['id'],
					'user_id'			=> !empty($data['user_id']) ? $data['user_id'] : $book_info['user_id'],
					'book_name' 		=> !empty($row['book_name']) ? $row['book_name'] : $book_info['name'],
					'author_name' 		=> !empty($row['author_name']) ? $row['author_name'] : $book_info['author_name'],
					'rank' 				=> $row['rank'] ?? 0,
					'opening_score' 	=> $row['opening_score'] ?? 0,
					'middle_score'  	=> $row['middle_score'] ?? 0,
					'ending_score'  	=> $row['ending_score'] ?? 0,
					'page_length_score' => $row['page_length_score'] ?? 0,
					'total_score' 		=> $row['total_score'] ?? 0,
					'summary' 			=> $row['summary'] ?? '',
					'feedback' 			=> $row['feedback'] ?? '',
					'url' 				=> $row['url'] ?? 'https://bribooks.com/bookstore/' . $book_info['slug'],
					'cover_image' 		=> $book_info['cover_image'] ?? '',
					'author_image' 		=> $book_info['author_image'] ?? '',
					'city_id' 			=> $data['city_id'] ?? 0,
					'state_id' 			=> $data['state_id'] ?? 0,
					'country_id' 		=> $data['country_id'] ?? 0,
				]);
			} else {
				self::_updateCounter($job_id, true);

				$skipped++;
			}
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importCoverTag($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['tags']) || empty($data['id'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($cover_info = $this->cover_model->get($data['id']))) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$this->cover_model->edit($cover_info['id'], [
				'tags'	=> $data['tags']
			]);

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importBroadcastPartnerSlot($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		$this->load->model('broadcast/BroadcastPartnerSlot_model', 'broadcast_partner_slot_model');
		$this->load->model('book/Book_model', 'book_model');

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['partner_id']) || empty($data['book_id']) || empty($data['rank']) || empty($data['start_date'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$book_info = $this->book_model->get($data['book_id']);

			if (!empty($book_info)) {
				$this->broadcast_partner_slot_model->add([
					'event_id'		=> (int)$data['event_id'] ?? 0,
					'partner_id'	=> (int)$data['partner_id'] ?? 0,
					'book_id'		=> (int)$book_info['id'] ?? 0,
					'user_id'		=> (int)$book_info['user_id'] ?? 0,
					'rank'			=> (int)$data['rank'],
					'start_date' 	=> date('Y-m-d H:i:s', strtotime(trim($data['start_date']))),
					'end_date' 		=> date('Y-m-d H:i:s', strtotime(trim($data['start_date'])) + 10)
				]);
			} else {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _saveSiteEventData($event_id = false, $site_id = false) {
		if (!empty($event_id) && !empty($site_id) && empty($this->event_site_model->getEventIdBySiteId($event_id, $site_id))){
			return $this->event_site_model->add([
				'event_id'=> $event_id,
				'site_id'=> $site_id
			]);
		}

		return false;
	}

	private function _importEventBookEnrol($rows = [], $map = [], $job_id = 0) {
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('event/EventTemplate_model', 'event_template_model');

		$this->load->library('GenericCertificate_lib');
		$this->load->library('Ranking_lib', 'ranking_lib');

		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['event_id']) || empty($data['book_id'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$event_id 			= (int)$data['event_id'];
			$book_id 			= (int)$data['book_id'];
			$gen_rank 			= (int)$data['gen_rank'];
			$gen_certificate 	= (int)$data['gen_certificate'];

			if (empty($event_info = $this->event_model->get($event_id))) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($book_info = $this->book_model->get($book_id))) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($book_info['status'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (!empty($this->event_book_model->get_all([
				'book_id'	=> (int)$book_id,
				'start'		=> 0,
				'limit'		=> 1,
			])['rows'][0])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($event_book_id = $this->event_book_model->add([
				'event_id'		=> $event_id,
				'book_id'		=> $book_id,
			]))) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$date_published = $this->book_version_model->get_all([
				'version' => 1,
				'book_id' => $book_id,
			])['rows'][0]['date_added'] ?? '';

			if (empty($date_published)) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$this->event_book_model->edit($event_book_id, [
				'date_added'	=> $date_published
			]);

			if (empty($this->event_user_model->get_all([
				'event_id'		=> $event_id,
				'user_id'		=> $book_info['user_id']
			])['rows'][0] ?? '')) {
				$this->event_user_model->add([
					'event_id'		=> $event_id,
					'user_id'		=> $book_info['user_id']
				]);
			}

			if (!empty($products = $this->order_product_model->get_all([
				'product_id'	 => $book_info['id']
			])['rows'] ?? [])) {
				$order_ids = [];

				foreach ($products as $product) {
					$order_info = $this->order_model->get($product['order_id']);

					if (!empty($order_info) && (!in_array($order_info['status'], [0, 91, 92]))) {
						if (empty($this->event_order_model->get_all([
							'event_id'		=> $event_id,
							'book_id'		=> $book_info['id'],
							'order_id'		=> $order_info['id'],
							'start'			=> 0,
							'limit'			=> 1,
						])['rows'][0] ?? '')) {
							$event_order_id = $this->event_order_model->add([
								'event_id'		=> $event_id,
								'order_id'		=> $order_info['id'],
								'book_id'		=> $book_info['id'],
								'quantity'		=> $product['quantity']
							]);

							$this->event_order_model->edit($event_order_id, [
								'date_added'	=> $order_info['date_added']
							]);
						}

						$order_ids[] = $order_info['id'];
					}
				}

				if (!empty($order_ids)) {
					rsort($order_ids);

					if (!empty($gen_rank)) {
						$this->ranking_lib->updateRank($order_ids[0]);
					}

					if (!empty($gen_certificate)) {
						$this->genericcertificate_lib->createCertificate($order_ids[0], false);

						if (!empty($certficates = $this->certificate_model->get_all([
							'event_id'	 => 0,
							'book_id'	 => $book_info['id']
						])['rows'] ?? [])) {
							$this->db->where_in('id', array_column($certficates, 'id'));
							$this->db->update('certificates',  [
								'_deleted'		=> 1,
								'date_deleted'	=> date('Y-m-d H:i:s'),
							]);
						}
					}
				}
			}

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importDeletedUserModify($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['user_id'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['email']) && empty($data['mobile'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (
				empty($user_info = $this->db->get_where('users', ['id' => (int)$data['user_id']])->row_array()) ||
				$user_info['_deleted'] == 0
			) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$this->db->update('users', [
				'email'		=> $data['email'],
				'mobile'	=> $data['mobile'],
			], [
				'id'		=> $data['user_id']
			]);

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importEventRankBuild($rows = [], $map = [], $job_id = 0) {
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventChallengeWeekly_model', 'event_challenge_weekly_model');
		$this->load->model('event/EventChallengeGeneral_model', 'event_challenge_general_model');
		$this->load->model('event/EventChallengeCity_model', 'event_challenge_city_model');
		$this->load->model('event/EventChallengeState_model', 'event_challenge_state_model');
		$this->load->model('event/EventChallengeCountry_model', 'event_challenge_country_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');

		$this->load->library('Ranking_lib', 'ranking_lib');

		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (
				empty($data['event_id']) ||
				empty($data['challenge_id']) ||
				empty($data['challenge_type']) ||
				empty($data['book_id']) ||
				!in_array($data['challenge_type'], ['weekly', 'general', 'city', 'state', 'country'])
			) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$event_id 		= (int)$data['event_id'];
			$challenge_id 	= (int)$data['challenge_id'];
			$book_id 		= (int)$data['book_id'];
			$challenge_type = $data['challenge_type'];

			if (
				empty($event_info = $this->event_model->get($event_id)) ||
				$event_info['end_date'] < date('Y-m-d H:i:s')
			) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$model_name = sprintf('event_challenge_%s_model', $challenge_type);

			if (
				empty($challenge_info = $this->{$model_name}->get($challenge_id)) ||
				$challenge_info['end_date'] < date('Y-m-d H:i:s')
			) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$event_order = $this->event_order_model->get_all([
				'event_id'	=> $event_id,
				'book_id'	=> $book_id,
				'start'		=> 0,
				'limit'		=> 1,
			])['rows'][0] ?? [];

			if (empty($event_order['order_id'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$this->ranking_lib->updateRank($event_order['order_id']);

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _importBookAiSummary($rows = [], $map = [], $job_id = 0) {
		$this->import_job_model->edit($job_id, [
			'extra' => json_encode([
				'model'			=> 'review/BookAiSummary',
				'empty_skips' 	=> 'event_id,book_id,version',
				'duplicates'	=> 'event_id,book_id,version',
			])
		]);

		self::_importGenericData($rows, $map, $job_id);
	}

	private function _importEventVoteBook($rows = [], $map = [], $job_id = 0) {
		$this->import_job_model->edit($job_id, [
			'extra' => json_encode([
				'model'			=> 'event/EventVoteBook',
				'empty_skips' 	=> 'event_id,challenge_id,book_id',
				'duplicates'	=> 'event_id,challenge_id,book_id',
			])
		]);

		self::_importGenericData($rows, $map, $job_id);
	}

	private function _importGenericData($rows = [], $map = [], $job_id = 0) {
		log_kb([
			'_importGenericData::job' => $job_id,
			'map::' => $map,
			'rows::' => $rows,
		]);

		$skipped 	= $uploaded = 0;
		$job_info 	= $this->import_job_model->get($job_id);

		$job_info['extra'] = json_decode($job_info['extra'], true);

		$model 	= $job_info['extra']['model'] ?? '';

		if (empty($model)) return;

		$empty_skips 	= explode(',', $job_info['extra']['empty_skips'] ?? '');
		$duplicates 	= explode(',', $job_info['extra']['duplicates'] ?? '');
		$explodes 		= explode('/', $model);

		if (count($explodes) > 1) {
			$dir 			= array_shift($explodes);
			$model_name 	= array_shift($explodes);
		} else {
			$dir 			= '';
			$model_name 	= array_shift($explodes);
		}

		$full_model_name = strtolower($model_name . '_model');

		log_kb([
			'_importGenericData::full_model_name' => $full_model_name,
		]);

		$model_file_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $model_name)));

		if (!empty($dir)) {
			$this->load->model(sprintf('%s/%s', $dir, ($model_file_name . '_model')), $full_model_name);
		} else {
			$this->load->model(sprintf('%s', $model_file_name . '_model'), $full_model_name);
		}

		foreach ($rows as $index => $row) {
			log_kb([
				'_importGenericData::single_row' => $row,
				'_importGenericData::single_map' => $map,
			]);
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			foreach ($empty_skips as $skip) {
				if (empty($data[$skip])) {
					log_kb(['skipped' => [$job_id, $data, $skip]]);
					self::_updateCounter($job_id, true);
					$skipped++;
					continue;
				}
			}

			if (!empty($duplicates)) {
				$duplicate_filter = [];

				foreach ($duplicates as $duplicate) {
					if (!empty($data[$duplicate])) {
						$duplicate_filter[$duplicate] = $data[$duplicate];
					}
				}

				if (!empty($this->{$full_model_name}->get_all($duplicate_filter + [
					'start'	=> 0,
					'limit'	=> 1,
				])['rows'][0])) {
					log_kb(['duplicates' => [$job_id, $data, $duplicate_filter]]);
					self::_updateCounter($job_id, true);
					$skipped++;
					continue;
				}
			}

			if (!empty($data['id'])) {
				$info = $this->{$full_model_name}->get($data['id']);

				if (!empty($info)) {
					$this->{$full_model_name}->edit($data['id'], $data);
				} else {
					unset($data['id']);
					log_kb([
						'_importGenericData::single_data' => $data,
					]);
					$this->{$full_model_name}->add($data);
				}
			} else {
				$this->{$full_model_name}->add($data);
			}

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}
}
