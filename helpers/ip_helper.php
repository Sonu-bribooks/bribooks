<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('_check_indian_ip')) {
	function _check_indian_ip($ip = '') {
		if (empty($ip)) return;

		$CI	=&	get_instance();

		$CI->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$cache_key = 'live_indian_ip_list';

		$rows = json_decode($CI->cache->get($cache_key), true);

		if (empty($rows)) {
			$rows 	= [];
			$header = [];

			$file = fopen('assets/citydb/ip_range.csv', 'r');

			while (($line = fgetcsv($file)) !== FALSE) {
				if (empty($header)) {
					$header = $line;
					continue;
				}

				$rows[] = preg_replace('/[^\d\.\/]/', '', trim($line[0]));
			}

			$CI->cache->save($cache_key, json_encode($rows), 30 * 24 * 3600);
		}

		if (empty($rows)) return;

		foreach ($rows as $key => $item) {
			if (_ip_in_range($ip, $item)) {
				return $item;
			}
		}

		return false;
	}
}

if (!function_exists('_ip_in_range')) {
	function _ip_in_range($ip, $range) {
		if (strpos($range, '/') === false) {
			$range .= '/32';
		}

		list( $range, $netmask ) = explode('/', $range, 2);

		$range_decimal = ip2long($range);
		$ip_decimal = ip2long($ip);
		$wildcard_decimal = pow(2, (32 - $netmask)) - 1;
		$netmask_decimal = ~ $wildcard_decimal;
		return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
	}
}
