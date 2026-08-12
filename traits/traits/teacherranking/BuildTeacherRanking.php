<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BuildTeacherRanking {
	public function cleanRank($event_id = 0, $item_id = 0, $type = 'city') {
		$event_info 	= $this->event_model->get($event_id);

		if (empty($event_info)) return;

		$challenge_info = $this->{sprintf('event_challenge_%s_model', $type)}->get_all([
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (empty($challenge_info)) return;

		$rank_key 	= self::_getKey([
			'event_id'								=> $event_id,
			sprintf('event_challenge_%s_id', $type)	=> $challenge_info['id'],
			sprintf('%s_id', $type)					=> $item_id,
		], $type);

		// pr([$rank_key, $challenge_info], 1);

		$this->redis_lib->removeRangeRank($rank_key, 0, 10000);
	}

	public function buildRanks($event_id = 0, $type = 'country') {
		if (empty($event_id)) return;

		$_method 		= sprintf('update%sRank', ucfirst($type));
		$event_info 	= $this->event_model->get($event_id);

		if (empty($event_info)) return;

		$challenge_info = $this->{sprintf('event_challenge_%s_model', $type)}->get_all([
			'event_id'				=> (int)$event_info['id'],
			'start_date_le'			=> date('Y-m-d H:i:s'),
			'end_date_ge'			=> date('Y-m-d H:i:s'),
		])['rows'][0] ?? [];

		if (empty($challenge_info)) return;

		$results = $this->event_site_model->get_all([
			'event_id'	=> (int)$event_info['id'],
		])['rows'] ?? [];

		// pr($results, 1);

		// $this->redis_lib->removeRangeRank($rank_key, 0, 10000);

		foreach ($results as $item) {
			$event_books = $this->event_book_model->get_all([
				'event_id'	=> (int)$item['event_id'],
				'site_id'	=> (int)$item['site_id'],
			])['rows'] ?? [];

			if (empty($event_books)) continue;
			if (count($event_books) < $challenge_info['min_published']) continue;

			$book_info = $this->book_model->get($event_books[0]['book_id']);

			self::{$_method}([
				'event_info'	=> $event_info,
				'book_info'		=> $book_info,
			]);

			pr(compact('event_info', 'book_info', 'event_book', 'item'));
		}
	}
}
