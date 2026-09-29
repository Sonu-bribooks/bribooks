<?php defined('BASEPATH') or exit('No direct script access allowed');

trait ImportEventOrderAmazon {
	private function _importEventOrderAmazon($rows = [], $map = [], $job_id = 0) {
        $this->load->model('common/Cron_model', 'cron_model');
        $this->load->model('event/EventOrderAmazon_model', 'event_order_amazon_model');
        $this->load->model('event/EventChallengeAmazon_model', 'event_challenge_amazon_model');

		$skipped = $uploaded = $event_id = 0;
        log_kb([
            'IMPORT_EVENT_AMAZON_ORDER_START' => [
                'rows' => count($rows),
                'map' => $map,
                'job_id' => $job_id
            ]
        ]);
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

			if (empty($data['book_id']) || empty($data['user_id'])) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

			if (empty($book_info = $this->book_model->get($data['book_id']))) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

            if ($book_info['user_id'] != $data['user_id']) {
				self::_updateCounter($job_id, true);

				$skipped++;
				continue;
			}

            if (empty($event_book_info = $this->event_book_model->getEventBookByBookId($data['event_id'], $book_info['id']))) {
				self::_updateCounter($job_id, true);
               
				$skipped++;
				continue;
			}

            if (empty($event_user_info = $this->event_user_model->getEventUserByUserId($data['event_id'], $book_info['user_id']))) {
				self::_updateCounter($job_id, true);
               
				$skipped++;
				continue;
			}

            if ( empty($data['currency_id'])  || empty($currency_info = $this->currency_model->get($data['currency_id']))) {
				self::_updateCounter($job_id, true);
               
				$skipped++;
				continue;
			}

            $this->event_order_amazon_model->add([
                'event_id'                    => $data['event_id'],
                'book_id'                     => $data['book_id'],
                'user_id'                     => $data['user_id'],
                'quantity'                    => $data['quantity'],
                'price'                       => $data['price'],
                'currency_id'                 => $data['currency_id'],
            ]);

            $uploaded++;
            $event_id = (int)$data['event_id'];
            
		}

        if (!empty($uploaded)) {

            $challenge_info = $this->event_challenge_amazon_model->get_all([
                'type'			=> 'user',
                'event_id'      => (int)$event_id,
                'end_date_ge'   => date('Y-m-d H:i:s'),
                'start'		    => 0,
				'limit'		    => 1,
            ])['rows'][0] ?? [];

			if (!empty($challenge_info)) {

                $code = sprintf('buildAmazonRank_%s', (int)$challenge_info['id']);

                $update_data = [
                    'code'      => $code,
                    'site_id'   => 1,
                    'action'    => 'alert_model->buildAmazonRankCron',
                    'data'      => [[
                        'event_id'      => (int)$event_id,
                        'challenge_id'  => (int)$challenge_info['id'],
                        'type'          => 'amazon',
                    ]],
                    'status'     => 0,
                    'alert_date' => date('Y-m-d H:i:00', strtotime('+1 minutes')),
                ];

                if (!empty($cron_info = $this->cron_model->getByCode($code))) {
                    $this->cron_model->edit($cron_info['id'], $update_data);
                } else {
                    $this->cron_model->add($update_data);
                }
            }
		}

		self::_updateCompleted($job_id);

		return [
			'skipped'	 	=> $skipped,
			'uploaded'		=> $uploaded,
		];
	}
}