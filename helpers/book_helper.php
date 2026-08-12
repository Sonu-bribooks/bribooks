<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('format_book_info')) {
	function format_book_info($book_info = []) {
		unset(
			$book_info['date_deleted'],
			$book_info['date_approved'],
			$book_info['date_modified'],
			$book_info['can_be_published'],
			$book_info['archived'],
			$book_info['author_bio'],
			$book_info['editing'],
			$book_info['featured'],
			$book_info['preview_token'],
			$book_info['reviewer_id'],
			$book_info['reviewer_rating'],
			$book_info['site_id'],
			$book_info['temp_user_id'],
			$book_info['_deleted'],
			$book_info['isbn_country_code'],
		);

		return $book_info;
	}
}

if (!function_exists('format_bookstore_info')) {
	function format_bookstore_info($book_info = []) {
		unset(
			$book_info['amazon_price'],
			$book_info['amazon_url'],
			$book_info['author_image'],
			$book_info['back_color'],
			$book_info['category_id'],
			$book_info['cover_id'],
			$book_info['date_added'],
			$book_info['date_published'],
			$book_info['date_deleted'],
			$book_info['date_approved'],
			$book_info['date_modified'],
			$book_info['can_be_published'],
			$book_info['archived'],
			$book_info['genre_id'],
			$book_info['isbn'],
			$book_info['status'],
			$book_info['unique_id'],
			$book_info['user_cover_id'],
			$book_info['user_id'],
			$book_info['author_bio'],
			$book_info['editing'],
			$book_info['featured'],
			$book_info['preview_token'],
			$book_info['reviewer_id'],
			$book_info['reviewer_rating'],
			$book_info['reading_count'],
			$book_info['site_id'],
			$book_info['temp_user_id'],
			$book_info['_deleted'],
			$book_info['isbn_country_code'],
		);

		return $book_info;
	}
}

if (!function_exists('get_book_slug')) {
	function get_book_slug($name, $book_id = 0) {
		$slug = preg_replace(['/[^\w\s]/', '/\s+/'], [' ', '-'], mb_strtolower($name));

		$CI	=&	get_instance();

		if ($result = $CI->db->get_where('book_version', [
			'slug'	=> $slug
		])->row_array()) {
			if ($result['book_id'] != $book_id) {
				$slug .= '-' . uniqid();
			}
		}

		return $slug;
	}
}

if (!function_exists('_maskedWord')) {
	function _maskWord($word) {
		return preg_replace('/\w(?<=\w{2})/', '*', $word);
	}
}

if (!function_exists('maskSpamWord')) {
	function maskSpamWord($text) {
		if (empty(strip_tags($text))) return;

		$CI	=&	get_instance();

		$CI->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$cache_key = vsprintf('%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			'spam_words',
		]);

		$spam_words = json_decode($CI->cache->get($cache_key), true);

		if (empty($spam_words)) {
			// $spam_words = array_column($CI->db->get('spam_words')->result_array(), 'word');

			$spam_words = array_column($CI->db->where('_deleted', 0)->get('spam_words')->result_array(),'word');

			$CI->cache->save($cache_key, json_encode($spam_words), 3600);
		}

		foreach ($spam_words as $spam_word) {
			$spam_word = preg_quote($spam_word, '/');
			preg_match("/\b{$spam_word}\b/ims", $text, $result);

			if (!empty($result)) {
				$masked_word = _maskWord($spam_word);
				$text = preg_replace("/\b{$spam_word}\b/ims", $masked_word, $text);
			}
		}

		// // Split the text into words
		// $words = explode(' ', strip_tags($text));
		//
		// // Loop through each word
		// foreach ($words as $word) {
		// 	if (in_array(strtolower($word), $spam_words ?? [])) {
		// 		if (strlen($word) < 2) {
		// 			$masked_word =  $word;
		// 		} else {
		// 			$masked_word =  preg_replace('/\w(?<=\w{2})/', '*', $word);
		// 		}
		//
		// 		$text = str_replace($word, $masked_word, $text);
		// 	}
		// }

		return $text;
	}
}
