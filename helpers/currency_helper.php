<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (! function_exists('get_exchange_rate')) {
	function get_exchange_rate($currency_code = 'USD') {
		$CI	=&	get_instance();

		if ($result = $CI->db->get_where('currency', [
			'code'	=> $currency_code
		])->row_array()) {
			return $result['exchange_rate'];
		}

		return 1;
	}
}

if (! function_exists('apply_currency_exchange')) {
	function apply_currency_exchange($amount = 0, $currency_code = 'USD', $base_currency_code = 'INR') {
		$exchange_rate 		= get_exchange_rate($currency_code);
		$base_exchange_rate = get_exchange_rate($base_currency_code);

		$amount = round($amount * $base_exchange_rate / $exchange_rate, 2);

		return $amount;
	}
}

if (! function_exists('convert_to_local_currency')) {
	function convert_to_local_currency($amount = 0, $user_id = 0, $local_currency_code = '') {
		$CI	=&	get_instance();

		if (empty($user_id)) {
			$user_id = $CI->session->userdata('user_id');
		}

		if (empty($local_currency_code)) {
			$local_currency_code = $CI->config->item('site_currency_code');
		}

		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('common/Site_model', 'site_model');

		$author_info = $CI->user_model->get($user_id);
		$site_info = $CI->site_model->get($author_info['site_id']);

		$author_currency_code = $site_info['currency_code'];

		if ($local_currency_code != $author_currency_code) {
			$order_exchange_rate = get_exchange_rate($local_currency_code);
			$author_exchange_rate = get_exchange_rate($author_currency_code);
			$amount = round($amount * $order_exchange_rate / $author_exchange_rate, 2);
		}

		return $amount;
	}
}

if (! function_exists('convert_to_author_currency')) {
	function convert_to_author_currency($amount = 0, $user_id = 0, $local_currency_code = 'USD') {
		$CI	=&	get_instance();

		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('common/Site_model', 'site_model');

		$author_info = $CI->user_model->get($user_id);
		$site_info = $CI->site_model->get($author_info['site_id']);

		$author_currency_code = $site_info['currency_code'];

		if ($local_currency_code != $author_currency_code) {
			$order_exchange_rate = get_exchange_rate($local_currency_code);
			$author_exchange_rate = get_exchange_rate($author_currency_code);
			$amount = round($amount * $order_exchange_rate / $author_exchange_rate, 2);
		}

		return $amount;
	}
}

if (! function_exists('get_author_currency_code')) {
	function get_author_currency_code($user_id = 0) {
		$CI	=&	get_instance();

		if (empty($user_id)) {
			$user_id = $CI->session->userdata('user_id');
		}

		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('common/Site_model', 'site_model');

		$author_info = $CI->user_model->get($user_id);
		$site_info = $CI->site_model->get($author_info['site_id'] ?? 0);

		return $site_info['currency_code'] ?? '';
	}
}

if (! function_exists('get_author_currency_id')) {
	function get_author_currency_id($user_id = 0) {
		$CI	=&	get_instance();

		if (empty($user_id)) {
			$user_id = $CI->session->userdata('user_id');
		}

		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('common/Site_model', 'site_model');
		$CI->load->model('localisation/Currency_model', 'currency_model');

		$author_info = $CI->user_model->get($user_id);
		$site_info = $CI->site_model->get($author_info['site_id']);
		$currency_info = $CI->currency_model->getByCode($site_info['currency_code']);

		return $currency_info['id'] ?? '';
	}
}

if (! function_exists('get_author_currency_symbol')) {
	function get_author_currency_symbol($user_id = 0) {
		$CI	=&	get_instance();

		if (empty($user_id)) {
			$user_id = $CI->session->userdata('user_id');
		}

		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('common/Site_model', 'site_model');
		$CI->load->model('localisation/Currency_model', 'currency_model');

		$author_info = $CI->user_model->get($user_id);
		$site_info = $CI->site_model->get($author_info['site_id']);
		$currency_info = $CI->currency_model->getByCode($site_info['currency_code']);

		return $currency_info['symbol'] ?? '';
	}
}


if (!function_exists('currency')) {
	function currency($price = "", $tax = 0, $currency_code = NULL) {
		$CI	=&	get_instance();

		if ($tax) {
			$price = $price * 100 / (100 + $tax);
		}

		if (!$currency_code && $CI->config->item('site_currency_code')) {
			$currency_code = $CI->config->item('site_currency_code');
		}

		if ($currency_code) {
			$currency_code = $currency_code;
		} else {
			$CI->db->where('key', 'system_currency');
			$currency_code = $CI->db->get('settings')->row()->value;
		}

		$CI->db->where('code', $currency_code);
		$symbol = $CI->db->get('currency')->row()->symbol;

		$CI->db->where('key', 'currency_position');
		$position = $CI->db->get('settings')->row()->value;

		$price = number_format($price, 2);

		if ($position == 'right') {
			return $price.$symbol;
		} elseif ($position == 'right-space') {
			return $price.' '.$symbol;
		} elseif ($position == 'left') {
			return $symbol.$price;
		} elseif ($position == 'left-space') {
			return $symbol.' '.$price;
		}
	}
}

if (!function_exists('currency_code_and_symbol')) {
	function currency_code_and_symbol($type = "") {
		$CI	=&	get_instance();
		$CI->db->where('key', 'system_currency');
		$currency_code = $CI->db->get('settings')->row()->value;

		$CI->db->where('code', $currency_code);
		$symbol = $CI->db->get('currency')->row()->symbol;

		if ($type == "") {
			return $symbol;
		} else {
			return $currency_code;
		}
	}
}

if (! function_exists('get_currency_symbol')) {
	function get_currency_symbol($currency_code = '') {
		$CI	=&	get_instance();

		if (empty($currency_code)) return '';

		$CI->load->model('localisation/Currency_model', 'currency_model');

		$currency_info = $CI->currency_model->getByCode($currency_code);

		return $currency_info['symbol'] ?? '';
	}
}

if (! function_exists('convert_to_cross_border_exchange_currency')) {
	function convert_to_cross_border_exchange_currency($author_earning_id = 0) {
		$CI	=&	get_instance();

		$CI->load->model('user/AuthorEarning_model', 'author_earning_model');
		$CI->load->model('user/AuthorEarningExchangeRateHistory_model', 'author_earning_exchange_rate_history_model');

		if (empty($info = $CI->author_earning_model->get($author_earning_id))) return 0;

		$earning_exchange_rate_info = $CI->author_earning_exchange_rate_history_model->get_all([
			'author_earning_id' => $info['id']
		])['rows'][0] ?? [];

		if (!empty($earning_exchange_rate_info)) {
			return round($info['amount'] * $earning_exchange_rate_info['rate'], 2);
		} else {
			return $info['amount'];
		}
	}
}
