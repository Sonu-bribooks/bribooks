<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (! function_exists('get_site_code_slug')) {
	function get_site_code_slug($name) {
		$slug   = preg_replace(['/[^\w\s]/', '/\s+/'], [' ', ' '], ucwords($name));

		$words  = explode(' ', $slug);

		$firstLetters = array_map(function($word) {
			return substr($word, 0, 1);
		}, $words);

		return implode('', $firstLetters);
	}
}
