<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BookAiReviewAlert {
	public function generateAiReviewCron($event_id = 0, $book_id = 0, $version = 0) {
		if (ENVIRONMENT !== 'production') return;
		if (empty($event_id)) return;
		if (empty($book_id)) return;
		if (empty($book_info = $this->book_model->get($book_id))) return;

		$this->load->model('review/BookAiReview_model', 'book_ai_review_model');

		if (empty($book_review_info = $this->book_ai_review_model->get_all([
			'event_id' => $event_id,
			'book_id'  => $book_id
		])['rows'][0] ?? [])) {
			$result = _curl(
				'http://172.31.42.71:5003/bb44khjkhskdjfhs344w56g657686233243Ghghj',
				[
					'event_id'	=> $event_id,
					'id'		=> $book_info['id'],
					'version'	=> $book_info['version']
				],
			);
		}
	}

	public function generateManualAiReviewCron($event_id = 0, $site_ids = '') {
		if (empty($event_id) || empty($site_ids)) return;

		$this->load->model('review/BookAiReview_model', 'book_ai_review_model');

		$results = $this->db->query(sprintf("
			select

			u.site_id,
			eb.book_id,
			b.version
			from event_book eb
			join bookstore  b on b.book_id = eb.book_id
			join users u on u.id = b.user_id

			where
			eb.event_id = %s and
			u.site_id in (%s)
			and eb.book_id not in (select book_id from book_ai_review where event_id = %s)
		", $event_id, $site_ids, $event_id))->result_array();

		log_kb(['generateManualAiReviewCron' => $results, 'qd' => $this->db->last_query()]);

		foreach ($results as $key => $item) {
			// if (!empty($book_review_info = $this->book_ai_review_model->get_all([
			// 	'event_id' => $event_id,
			// 	'book_id'  => $item['book_id'],
			// 	'version'  => $item['version'],
			// ])['rows'][0] ?? [])) continue;

			$result = _curl(
				'http://172.31.42.71:5003/bb44khjkhskdjfhs344w56g657686233243Ghghj',
				[
					'event_id'		=> $event_id,
					'id'			=> $item['book_id'],
					'version'		=> $item['version'],
					'thread_type'	=> 'sync',
				],
			);

			log_kb(['generateManualAiReviewCron::generated: ' => [$key, $result]]);
		}
	}
}
