<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class Event_lib {
	public function __construct() {
		$this->CI 		=& get_instance();
		$this->db 		= $this->CI->db;
		$this->session 	= $this->CI->session;
		$this->load 	= $this->CI->load;
		$this->config 	= $this->CI->config;
		$this->input 	= $this->CI->input;

		$this->load->model('order/Order_model');
		$this->load->model('book/Book_model');
		$this->load->model('user/Student_model');
		$this->load->model('event/Event_model');
		$this->load->model('event/EventUser_model');
		$this->load->model('event/EventBook_model');
		$this->load->model('event/EventOrder_model');
		$this->load->model('common/Site_model');
		$this->load->model('common/Cron_model');

		$this->order_model 			= $this->CI->Order_model;
		$this->book_model 			= $this->CI->Book_model;
		$this->student_model 		= $this->CI->Student_model;
		$this->event_model 			= $this->CI->Event_model;
		$this->event_user_model 	= $this->CI->EventUser_model;
		$this->event_book_model 	= $this->CI->EventBook_model;
		$this->event_order_model 	= $this->CI->EventOrder_model;
		$this->site_model 			= $this->CI->Site_model;
		$this->cron_model 			= $this->CI->Cron_model;
	}

	private function _enrolUserByBookId($book_id = 0) {
		if (empty($book_info = $this->book_model->get($book_id))) return;

		if (empty($user_info = $this->student_model->get($book_info['user_id']))) return;

		if (empty($site_info = $this->site_model->get($user_info['site_id']))) return;

		if (empty($event_info = $this->event_model->get_all([
			'country_code'		=> strtoupper($site_info['country_code']),
			'is_active_event'	=> 1,
			'force_enrol_in'	=> [1,3],
			'start'				=> 0,
			'limit'				=> 1,
		])['rows'][0] ?? [])) return;

		if (!empty($event_info) && !empty($event_info['category_ids'])) {
			$categories = explode(',', $event_info['category_ids']);

			if (!in_array($book_info['category_id'],$categories)) return;
		}

		if (
			!empty($event_info) &&
			$book_info['date_added'] >= $event_info['start_date'] &&
			$book_info['date_published'] <= $event_info['book_writing_end_date'] &&
			empty($this->event_user_model->getEventUserByUserId($event_info['id'], $user_info['id']))
		) {
			$this->event_user_model->add([
				'event_id'	=> (int)$event_info['id'],
				'user_id'	=> (int)$user_info['id'],
			]);
		}
	}

	public function enrolBook($book_id = 0) {
		// Enrol user which is not in the event
		self::_enrolUserByBookId($book_id);

		$book_info = $this->book_model->get($book_id);

		if ($book_info['version'] > 1) return;

		// if book exist in any event it will skip it
		if ($this->event_book_model->get_all([
			'book_id'	=> (int)$book_info['id'],
			'start'		=> 0,
			'limit'		=> 1
		])['total'] != 0) return;

		$user_events = $this->event_user_model->get_all([
			'user_id'	=> $book_info['user_id'],
		])['rows'] ?? [];

		foreach ($user_events as $key => $value) {
			$event_info = $this->event_model->get($value['event_id']);

			if (
				$event_info['start_date'] <= date('Y-m-d H:i:s') &&
				$event_info['end_date'] >= date('Y-m-d H:i:s') &&
				$book_info['date_added'] >= $event_info['start_date'] &&
				$book_info['date_published'] <= $event_info['book_writing_end_date'] &&
				($this->event_book_model->get_all([
					'event_id'	=> (int)$event_info['id'],
					'book_id'	=> (int)$book_info['id'],
				])['total'] === 0)
			) {
				$this->event_book_model->add([
					'event_id'	=> (int)$event_info['id'],
					'book_id'	=> (int)$book_info['id'],
				]);
			}
		}
	}

	public function enrolOrder($order_id = 0) {
		$order_info = $this->order_model->get($order_id);
		$products = $this->order_model->getProducts($order_id);

		foreach ($products as $product) {
			$events = $this->event_book_model->get_all([
				'book_id'	=> (int)$product['product_id']
			])['rows'] ?? [];

			if ($events) {
				foreach ($events as $event) {
					$event_info = $this->event_model->get($event['event_id']);

					if (
						$event_info['start_date'] <= date('Y-m-d H:i:s') &&
						$event_info['end_date'] >= date('Y-m-d H:i:s') &&
						($this->event_order_model->get_all([
							'event_id'	=> (int)$event_info['id'],
							'order_id'	=> (int)$order_id,
							'book_id'	=> (int)$product['product_id'],
						])['total'] === 0)
					) {
						$this->event_order_model->add([
							'event_id'		=> (int)$event_info['id'],
							'order_id'		=> (int)$order_id,
							'book_id'		=> (int)$product['product_id'],
							'quantity'		=> (int)$product['quantity'],
						]);

						self::_generateAiReview($event_info['id'], $product['product_id']);
					}
				}
			}
		}
	}

	private function _generateAiReview($event_id= 0, $book_id = 0) {
		if (empty($event_id)) return;
		if (empty($book_id)) return;
		if (empty($book_info = $this->book_model->get($book_id))) return;

		$cron_code = sprintf('generateAiReviewCron_%s_%s_%s', $event_id, $book_info['id'], $book_info['version']);

		if (!empty($this->cron_model->getByCode($cron_code))) return;

		$this->cron_model->add([
			'code'			=> $cron_code,
			'action'		=> 'alert_model->generateAiReviewCron',
			'data'			=> [$event_id, $book_info['id'], $book_info['version']],
			'site_id'		=> 1,
			'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
		]);
	}
}
