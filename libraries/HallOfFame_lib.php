<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class HallOfFame_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('book/Book_model');
		$this->load->model('user/Student_model');
		$this->load->model('common/Cron_model');
		$this->load->model('order/Order_model');
		$this->load->model('localisation/Country_model');
		$this->load->model('order/OrderProduct_model');
		$this->load->model('event/EventBook_model');
		$this->load->model('halloffame/HallOfFame_model');
		$this->load->model('halloffame/HallOfFameCountry_model');

		$this->book_model = $this->CI->Book_model;
		$this->student_model = $this->CI->Student_model;
		$this->cron_model = $this->CI->Cron_model;
		$this->order_model = $this->CI->Order_model;
		$this->country_model = $this->CI->Country_model;
		$this->order_product_model = $this->CI->OrderProduct_model;
		$this->event_book_model = $this->CI->EventBook_model;
		$this->hall_of_fame_model = $this->CI->HallOfFame_model;
		$this->hall_of_fame_country_model = $this->CI->HallOfFameCountry_model;
	}

	public function addBookToHallOfFame($book_id = false) {
		log_kb([
			'addBookToHallOfFame::book_id:: '	=> $book_id
		]);

		if (
			$book_id &&
			!empty($book_info = $this->book_model->get($book_id)) &&
			!empty($user_info = $this->student_model->get($book_info['user_id']))
		) {
			$min_book_sold = 1;
			$event_id = $hall_of_fame_id = 0;

			if (empty($book_sold = $this->order_model->getTotalProductsByProductId($book_id)))
				return;

			if (empty($country_info = $this->country_model->get_country_code($user_info['location'])))
				return;

			if (!empty($hall_of_fame_country_info = $this->hall_of_fame_country_model->get_all(['country_code' => $country_info['code']])['rows'][0] ?? [])) {
				$min_book_sold = $hall_of_fame_country_info['book_sold'];
			}

			if ($min_book_sold > $book_sold)
				return;

			if (!empty($event_book_info = $this->event_book_model->getEventBookByBookId(0, $book_id))) {
				$event_id = $event_book_info['event_id'];
			}

			if (empty($hall_of_fame_info = $this->hall_of_fame_model->getByBookId($book_id))) {
				$hall_of_fame_id = $this->hall_of_fame_model->add([
					'book_id'		=> $book_id,
					'event_id'		=> $event_id,
					'user_id'		=> $user_info['id'] ?? 0,
					'location'		=> $user_info['location'] ?? '',
					'country_code'	=> $country_info['code'] ?? '',
					'book_sold'		=> $book_sold ?? 0,
					'manager_id'	=> $this->session->userdata('user_id') ?? 0
				]);

				if (!empty($user_info['location']) && (strtolower($user_info['location']) == 'india')) {
					// $this->cron_model->add([
					//	 'code'		  => 'enrolToHallOfFame_' . $book_id,
					//	 'action'		=> 'alert_model->enrolToHallOfFame',
					//	 'data'		  => [$book_id],
					//	 'site_id'	   => '1',
					//	 'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
					// ]);
				}
			} else {
				$hall_of_fame_id = $hall_of_fame_info['id'];

				$this->hall_of_fame_model->edit($hall_of_fame_id, [
					'event_id'		=> $event_id,
					'book_sold'		=> $book_sold ?? 0
				]);
			}

			return $hall_of_fame_id;
		}
	}

	public function enrolToHallOfFame($order_id = false) {
		log_kb([
			'enrolToHallOfFame::order_id:: '	=> $order_id
		]);

		if (
			$order_id &&
			!empty($order_info = $this->order_model->get($order_id)) &&
			!empty($order_product_results = $this->order_product_model->getOrderProductByOrderId($order_id))
		) {
			foreach ($order_product_results as $order_product_info) {
				self::addBookToHallOfFame($order_product_info['product_id']);
			}
		}
	}
}
