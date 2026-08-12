<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('_check_blacklisted_domain')) {
	function _check_blacklisted_domain($domain = '') {
		if (empty($domain)) return;

		$CI	=&	get_instance();

		return $CI->db->get_where('blacklisted_domains', [
			'domain' 	=> $domain,
			'_deleted' 	=> 0,
		])->row()->domain ?? false;
	}
}
