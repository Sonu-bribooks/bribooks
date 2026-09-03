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

			$data['mobile'] 			= $user_info['mobile'];
			$data['email'] 				= $user_info['email'];
			$data['book_name']			= $book_info['name'];
			$data['isbn_number']		= $book_info['isbn'];
			$data['author_name']		= $book_info['author_name'];
			$data['location']			= $user_info['location'];
			$data['unsubscribe_url'] 	= gen_unsubscribe_url($user_info['email']);

			CI_Events::trigger('isbn_allotment', [
				'book_id'	=> $book_info['id'],
				'data'		=> $data
			]);

			CI_Events::trigger('access_log', [
				'module'	=> sprintf('user_isbn_allotment_%d_%d', (int)$user_info['id'], (int)$book_info['id'])
			]);
		}
	}
}
