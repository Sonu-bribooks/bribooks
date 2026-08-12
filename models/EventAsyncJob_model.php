<?php defined('BASEPATH') OR exit('No direct script access allowed');

class EventAsyncJob_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function bookPublished($book_id = 0) {
		log_kb([
			'EventAsyncJob_model::bookPublished' => $book_id
		]);

		if (empty($book_id)) return ;

		self::sendMessageAfterPublish($book_id);
	}

	private function sendMessageAfterPublish($book_id = 0) {
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('common/Cron_model', 'cron_model');

		$book_info = $this->book_model->get($book_id);
		$user_info = $this->user_model->get($book_info['user_id'] ?? 0);
		$site_info = $this->site_model->get($user_info['site_id'] ?? 0);

		if (empty($user_info)) return;

		if (empty($user_info) || (!in_array(strtolower($user_info['location']), ['india']))) return;
		if (empty($site_info) || (!in_array(strtolower($site_info['country_code']), ['in']))) return;

		$country_code = [
			'india' 	=> 'IN',
		];

		if (empty($active_events = $this->event_model->get_all([
			'country_code'		=> $country_code[strtolower($user_info['location'])],
			'event_type_id'		=> 7,
			'is_active_event'	=> 1,
		])['rows'] ?? [])) return;

		foreach ($active_events as $event_info) {
			if (
				!empty($event_info) &&
				$event_info['book_writing_end_date'] >= date('Y-m-d H:i:s') &&
				empty($this->event_book_model->get_all([
					'book_id'	=> (int)$book_info['id'],
				])['rows'][0] ?? [])
			) {
				if (!empty($this->cron_model->getByCode(sprintf('messageAfterBookPublish_%s', $book_info['id'])))) continue;

				$this->cron_model->add([
					'code'			=> 'messageAfterBookPublish_' . $book_info['id'],
					'action'		=> 'alert_model->messageAfterBookPublish',
					'data'			=> [['event_id' => $event_info['id'], 'user_id' => $user_info['id'], 'book_id' => $book_info['id'], 'attempt' => 1]],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime('+2 minutes', strtotime(date('Y-m-d H:i:s')))),
				]);
			}
		}
	}

	public function createBookCoupon($book_id = 0, $cart_id = 0) {
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');
		$this->load->model('order/Coupon_model', 'coupon_model');

		if (empty($book_info = $this->book_model->get($book_id)) || $book_info['version'] != 1) return;

		if (empty($event_book_info = $this->event_book_model->get_all([
			'book_id' => $book_id
		])['rows'][0] ?? [])) return;

		if (
			!empty($event_info = $this->event_model->get($event_book_info['event_id'])) &&
			!empty($event_info['book_writing_end_date']) &&
			$event_info['book_writing_end_date'] >= date('Y-m-d H:i:s')
		) {
			$communication_kit_info = $this->event_communication_kit_model->get_all([
				'event_id' => $event_info['id']
			])['rows'][0]['book'] ?? '';

			if (empty($communication_kit_info)) return;

			$communication_kit_info = json_decode($communication_kit_info, true);
			$communication_kit_info = $communication_kit_info[0] ?? [];

			if (empty($communication_kit_info['coupon_percent'])) return;

			if (
				!empty($expired_coupon_info = $this->coupon_model->get_all([
					'event_id' 		=> $event_info['id'],
					'item_id' 		=> $book_info['id'],
					'user_id' 		=> $book_info['user_id'],
				])['rows'][0] ?? []) &&
				strtotime($expired_coupon_info['date_end']) < time()
			) return;

			if (!empty($coupon_info = $this->coupon_model->get_all([
				'event_id' 		=> $event_info['id'],
				'item_id' 		=> $book_info['id'],
				'user_id' 		=> $book_info['user_id'],
				'end_date_ge'	=> date('Y-m-d H:i:s'),
			])['rows'][0] ?? []))  {
				$coupon_id = $coupon_info['id'];
			} else {
				$author_currency_code = get_author_currency_code($book_info['user_id']);

				$coupon 	= 'SBWF' . $book_info['id'];
				$duration 	= !empty($communication_kit_info['coupon_duration']) ? (int)$communication_kit_info['coupon_duration'] : 24;

				$coupon_id = $this->coupon_model->add([
					'event_id'			  => $event_info['id'],
					'name'                => $coupon,
					'coupon_type'         => 'product',
					'item_id'             => $book_info['id'],
					'user_id'             => $book_info['user_id'],
					'code'                => $coupon,
					'discount_type'       => 2, // 1=flat,2=percentage
					'currency_code'       => $author_currency_code,
					'discount'            => $communication_kit_info['coupon_percent'] ?? 0,
					'total'               => 0.00,
					'max_quantity'        => $communication_kit_info['max_quantity'] ?? 0,
					'status'              => 1,
					'used_count'          => 0,
					'used_limit'          => 1,
					'date_start'          => date('Y-m-d H:i:s'),
					'date_end'            => date('Y-m-d H:i:s', strtotime("+{$duration} hour")),
					'date_added'          => date('Y-m-d H:i:s'),
					'date_modified'       => date('Y-m-d H:i:s'),
				]);
			}

			if (
				!empty($coupon_id) &&
				!empty($cart_id)
			) {
				$this->db->update('cart', [
					'coupon_id'		=> 0,
				], [
					'user_id'		=> (int)$book_info['user_id']
				]);

				$this->db->update('cart', [
					'coupon_id'		=> (int)$coupon_id,
				], [
					'id'			=> (int)$cart_id
				]);
			}
		}
	}
}
