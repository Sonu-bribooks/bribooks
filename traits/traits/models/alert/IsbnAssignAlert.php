<?php defined('BASEPATH') or exit('No direct script access allowed');

trait IsbnAssignAlert {
	public function isbnAssignAlert($id = 0) {
		self::cron($id, 'isbnAssignAlertCron');
	}

	public function isbnAssignAlertCron($id = 0) {
		if (
			($book_info = $this->book_model->get($id)) &&
			!empty($book_info['isbn']) &&
			$user_info = $this->student_model->get($book_info['user_id'])
		) {
			$this->load->model('event/EventBook_model', 'event_book_model');

			$event_book_info = $this->event_book_model->get_all([
				'book_id'	=> $book_info['id']
			])['rows'][0] ?? [];

			$data['title']			= sprintf(_li('ISBN has been allotted to your book %s.'), $book_info['name']);
			$data['heading']		= sprintf(_li('ISBN has been allotted to your book %s.'), $book_info['name']);

			$data['content']		= $this->load->view('common/mail/part/isbn_assign_alert', [
				'location'		=> $user_info['location'],
				'book'			=> $book_info,
			], true);

			$data['unsubscribe_url']= gen_unsubscribe_url($user_info['email']);

			$message 	= $this->load->view('common/mail/templates/2/general', $data, true);

			$mobile = $user_info['mobile'];
			$email 	= $user_info['email'];

			if (!$this->db->get_where('unsubscribed', [
				'email'		=> $user_info['email'],
				'_deleted'	=> 0
			])->row_array()) {
				self::email(
					$email,
					$data['title'],
					$message,
					[],
					[]
				);
			}

			if($mobile) {
				if(!empty($user_info['location']) && strtolower($user_info['location']) == 'india') {
					self::sendOnextelWhatsappMessage(
						$mobile,
						[
							'template_id'	=> '01key49zfa6phq0yg8e640ecrg',
							'parameters'	=> [
								$book_info['name'],
								$book_info['isbn'],
							]
						],
					);
				} else {
					self::_sendWhatsappText(
						$mobile,
						[
							'template_id'	=> '01kevfv6jv4572w5bk2kqgem0w',
							'parameters'	=> [
								$book_info['author_name'],
								$book_info['name'],
								$book_info['isbn'],
							]
						],
					);
				}
			}
		}
	}
}
