<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

load_trait('schoolranking');

final class Medallion_lib {
	public function __construct() {
		$this->CI 		=& get_instance();
		$this->db 		= $this->CI->db;
		$this->session 	= $this->CI->session;
		$this->load 	= $this->CI->load;
		$this->config 	= $this->CI->config;
		$this->input 	= $this->CI->input;

		$this->load->model('book/Book_model');
		$this->load->model('user/Student_model');

		$this->load->model('common/Site_model');
		$this->load->model('common/Cron_model');

		$this->load->model('localisation/City_model');
		$this->load->model('localisation/Country_model');

		$this->load->model('event/Event_model');
		$this->load->model('event/EventSite_model');
		$this->load->model('event/EventBook_model');
		$this->load->model('event/EventUser_model');
		$this->load->model('event/EventChallenge_model');
		$this->load->model('event/EventChallengeCountry_model');
		$this->load->model('event/EventChallengeState_model');
		$this->load->model('event/EventChallengeCity_model');
		$this->load->model('medallion/Medallion_model');
		$this->load->model('medallion/MedallionOrder_model');
		$this->load->model('medallion/MedallionAddress_model');
		$this->load->model('medallion/SchoolMedallionAddress_model');
		$this->load->model('medallion/MedallionStockLog_model');
		$this->load->model('user/User_model');

		$this->book_model 		= $this->CI->Book_model;
		$this->student_model 	= $this->CI->Student_model;
		$this->site_model 		= $this->CI->Site_model;
		$this->city_model 		= $this->CI->City_model;
		$this->country_model 	= $this->CI->Country_model;

		$this->event_model 						= $this->CI->Event_model;
		$this->event_site_model 				= $this->CI->EventSite_model;
		$this->event_book_model 				= $this->CI->EventBook_model;
		$this->event_user_model 				= $this->CI->EventUser_model;
		$this->event_challenge_city_model 		= $this->CI->EventChallengeCity_model;
		$this->event_challenge_state_model 		= $this->CI->EventChallengeState_model;
		$this->event_challenge_country_model	= $this->CI->EventChallengeCountry_model;
		$this->medallion_model					= $this->CI->Medallion_model;
		$this->medallion_order_model			= $this->CI->MedallionOrder_model;
		$this->school_medallion_address_model	= $this->CI->SchoolMedallionAddress_model;
		$this->medallion_address_model			= $this->CI->MedallionAddress_model;
		$this->medallion_stock_log_model		= $this->CI->MedallionStockLog_model;
		$this->user_model						= $this->CI->User_model;

		$this->cron_model 	= $this->CI->Cron_model;
	}

	public function createMedallion($book_id = 0, $type = '') {
		if ($book_id <= 0) return;

		log_kb([
			'createMedallion::book_id'	=> $book_id
		]);

		$book_info 		= $this->book_model->get($book_id);
		$author_info 	= $this->student_model->get($book_info['user_id'] ?? 0);
		$school_info 	= $this->site_model->get($author_info['site_id'] ?? 0);

		$school_user_id = $school_info['user_id'] ?? 0;

		log_kb(compact('book_info', 'author_info', 'school_info'));

		if (empty($book_info) || empty($school_info) || empty($school_user_id)) {
			log_kb(['error' => 'Missing book or school info']);
			return;
		}

		$book_events 	= $this->event_book_model->get_all([
			'book_id'			=> $book_id,
			'is_active_event'   => 1
		])['rows'] ?? [];

		foreach ($book_events as $book_event) {
			$event_info = $this->event_model->get($book_event['event_id']);

			if (empty($event_info) || empty($event_info['school_medallion_ids']) || ($event_info['direct_site_id'] == $school_info['id'])) continue;

			if (empty($this->event_site_model->get_all([
				'event_id' 	=> $event_info['id'],
				'site_id' 	=> $school_info['id'],
			])['rows'][0] ?? '')) {
				continue;
			}

			$medallion_ids  = explode(',', $event_info['school_medallion_ids']);
			$no_published 	= self::_getPublishedCount($event_info['id'], $author_info['site_id']);

			if (empty($medallion_ids)) return;

			foreach($medallion_ids as $medallion_id) {
				if (empty($medallion_info = $this->medallion_model->get($medallion_id))) continue;

				if (empty($medallion_info['min_published']) || $no_published < $medallion_info['min_published']) continue;
				if (!empty($medallion_info['max_published']) && $no_published > $medallion_info['max_published']) continue;

				if (empty($medallion_order_info = $this->medallion_order_model->get_all([
					'event_id'		=> $event_info['id'],
					'user_id'		=> $school_user_id,
					'medallion_id'	=> $medallion_info['id']
				])['rows'][0] ?? [])) {
					$address_info 	= $this->medallion_address_model->get_all([
						'user_id'	=> (int)$school_user_id
					])['rows'][0] ?? [];

					$medallion_order_code = vsprintf('BBSM-%s%s%s%s', [
						time(),
						$event_info['id'],
						$medallion_info['id'],
						$school_info['id'],
					]);

					$school_currency_code = $school_info['currency_code'];

					$this->medallion_order_model->add([
						'type'				=> 'school',
						'order_code'		=> $medallion_order_code,
						'event_id'			=> (int)$event_info['id'],
						'address_id'		=> (int)($address_info['id'] ?? 0),
						'user_id'			=> (int)$school_user_id,
						'medallion_id'		=> (int)($medallion_info['id'] ?? 0),
						'pickup_location_id'=> 1,
						'status'			=> !empty($address_info['id']) ? 21 : 1,
						'weight'			=> (double)($medallion_info['weight'] ?? 0),
						'subtotal'			=> (double)apply_currency_exchange($medallion_info['price'] ?? 0, $school_currency_code),
						'shipping_cost'		=> (double)apply_currency_exchange($medallion_info['shipping_cost'] ?? 0, $school_currency_code),
						'total'				=> (double)apply_currency_exchange(($medallion_info['price'] ?? 0) + ($medallion_info['shipping_cost'] ?? 0), $school_currency_code),
						'currency_id'		=> (int)$school_info['country_id'] ?? 0,
						'currency_code'		=> $school_info['currency_code'],
						'currency_symbol'	=> get_currency_symbol($school_info['currency_code']),
					]);

					if (!empty($address_info)) {
						self::_confirmMedallionOrderStatus(($school_user_id ?? 0), ($address_info['id'] ?? 0));
					}
				}
			}
		}
	}

	private function _getPublishedCount($event_id = 0, $school_id = 0) {
		return count(array_unique(array_column($this->event_book_model->get_all([
			'event_id'	=> $event_id,
			'site_id'	=> $school_id,
		])['rows'] ?? [], 'book_id')));
	}

	private function _confirmMedallionOrderStatus($user_id = 0, $address_id = 0) {
		$orders = $this->medallion_order_model->get_all([
			'user_id'			=> $user_id,
			'shipping_status'	=> 0,
			'ne_status'			=> [0, 4, 15, 91, 92, 93],
			'sort'				=> 'medallion_order.id',
			'order'				=> 'ASC',
		])['rows'] ?? [];

		if (empty($orders)) return;

		$parent_id = 0;

		$parent_order = array_filter($orders, function($item) {
			return !empty($item['parent_id']);
		});

		$parent_id = $parent_order['id'] ?? $orders[0]['id'] ?? 0;

		foreach ($orders as $order) {
			$this->medallion_order_model->edit($order['id'], [
				'parent_id'		=> $parent_id == $order['id'] ? 0 : $parent_id,
				'shipping_cost'	=> $parent_id == $order['id'] ? $order['shipping_cost'] : 0,
				'total'			=> $parent_id == $order['id'] ? (double)$order['total'] : (double)$order['subtotal'],
				'status'		=> 21,
				'address_id'	=> (int)$address_id,
			]);

			if ($order['status'] == 1) {
				// reduce medallion stock
				$medallion_info = $this->medallion_model->get($order['medallion_id']);

				$this->medallion_model->edit($medallion_info['id'], [
					'quantity'	=> ($medallion_info['quantity'] - 1)
				]);

				$this->medallion_stock_log_model->add([
					'medallion_id'			=> (int)$order['medallion_id'],
					'medallion_order_id'	=> (int)$order['id'],
					'quantity'				=> $medallion_info['quantity'],
					'quantity_order'		=> 1,
				]);
			}
		}
	}
}
