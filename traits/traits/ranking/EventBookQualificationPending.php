<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventBookQualificationPending{
    private function _updateBookQualificationPendingStatus($event_info = [], $book_info = []) {
		if (empty($event_info) || empty($book_info) || ($event_info['selling_end_date'] <= date('Y-m-d H:i:s'))) {
			return;
		}

		$no_sold = $this->event_order_model->getTotalSoldByBook($event_info['id'], $book_info['id']);

		if ($qualified_user_info = $this->event_book_qualification_pending_model->get_all([
			'event_id'		=> (int)$event_info['id'],
			'book_id'		=> (int)$book_info['id'],
		])['rows'][0] ?? []) {
			$this->event_book_qualification_pending_model->edit($qualified_user_info['id'], [
				'book_name'		=> $book_info['name'] ?? '',
				'author_name'	=> $book_info['author_name'] ?? '',
				'book_slug'		=> $book_info['slug'] ?? '',
				'book_image'	=> $book_info['cover_image'] ?? '',
				'author_image'	=> $book_info['author_image'] ?? '',
				'score'			=> (int)$no_sold,
			]);
		} else {
		    $author_info = $this->student_model->get($book_info['user_id']);

			$this->event_book_qualification_pending_model->add([
				'event_id'		=> (int)$event_info['id'],
				'site_id'		=> (int)$author_info['site_id'] ?? 0,
				'city_id'		=> (int)$author_info['city_id'] ?? 0,
				'state_id'		=> (int)$author_info['state_id'] ?? 0,
				'country_id'	=> (int)$author_info['country_id'] ?? 0,
				'user_id'		=> (int)$book_info['user_id'] ?? 0,
				'book_id'		=> (int)$book_info['id'] ?? 0,
				'book_name'		=> $book_info['name'] ?? '',
				'author_name'	=> $book_info['author_name'] ?? '',
				'book_slug'		=> $book_info['slug'] ?? '',
				'book_image'	=> $book_info['cover_image'] ?? '',
				'author_image'	=> $book_info['author_image'] ?? '',
				'score'			=> (int)$no_sold,
			]);
		}
	}
}