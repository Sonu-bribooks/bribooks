<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use GeoIp2\Database\Reader;

final class Spam_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->db = $this->CI->db;
		$this->input = $this->CI->input;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$this->cache = $this->CI->cache;

		$this->_cache_ttl 		= ENVIRONMENT === 'production' ? 15 : 15;
		$this->_cache_day_ttl 	= ENVIRONMENT === 'production' ? 24 * 3600 : 600;
	}

	public function validate($level = 3, $suppress = false) {
		if (
			$this->input->post('email') &&
			_check_blacklisted_domain(substr($this->input->post('email'), strrpos($this->input->post('email'), '@') + 1))
		) {
			$this->CI->json['error'] = _li('email_domain_is_blocked');
			return false;
		}

		if (!self::_validateRateLimit($suppress)) {
			$this->CI->json['error'] = _li('You have requested a Code recently. Please wait 30 seconds before trying again.');
			return false;
		}

		if (!self::_validateIpSessionRateLimit($suppress)) {
			$this->CI->json['error'] = _li('Too many Code requests detected from your network. Please try again later.');
			return false;
		}

		if (!self::_validateDailyRateLimit($suppress)) {
			$this->CI->json['error'] = _li('You have reached the maximum number of Code requests for today. Please try again after 24 hours.');
			return false;
		}

		if ($info = $this->db->get_where('blocked_ips', [
			'ip'	=> $this->input->ip_address()
		])->row_array()) {
			if (in_array($this->input->ip_address(), ALLOWED_IPS)) {
				return true;
			}

			if ($info['blocked']) {
				$this->CI->json['error'] = _li('your_ip_is_blocked');
				return false;
			}

			if (strtotime($info['date_modified']) + SPAM_LIMIT > time()) {
				$attempt = $info['attempt'] + 1;
			} else {
				$attempt = $info['attempt'];
			}

			$country_info = self::getCountry();

			// if (in_array(strtolower($country_info['country_code']), WHITELISTED_COUNTRY)) return true;

			$this->db->update('blocked_ips', [
				'attempt'		=> $attempt,
				'country_code'	=> $country_info['country_code'],
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'id'			=> (int)$info['id']
			]);

			if ($attempt > 10 && strtotime($info['date_modified']) + SPAM_LIMIT > time() && !$suppress) {
				$this->db->update('blocked_ips', [
					'blocked'		=> 1,
					'country_code'	=> $country_info['country_code'],
					'date_modified'	=> date('Y-m-d H:i:s'),
				], [
					'id'			=> (int)$info['id']
				]);
			}

			if ($level === 3) {
				if ($attempt > 20 && strtotime($info['date_modified']) + 600 > time() && !$suppress) {
					$this->db->update('blocked_ips', [
						'blocked'		=> 1,
						'country_code'	=> $country_info['country_code'],
						'date_modified'	=> date('Y-m-d H:i:s'),
					], [
						'id'			=> (int)$info['id']
					]);
				}

				if ($attempt > 80 && !$suppress) {
					$this->db->update('blocked_ips', [
						'blocked'		=> 1,
						'country_code'	=> $country_info['country_code'],
						'date_modified'	=> date('Y-m-d H:i:s'),
					], [
						'id'			=> (int)$info['id']
					]);
				}
			}
		} else {
			$country_info = self::getCountry();

			$this->db->insert('blocked_ips', [
				'ip'			=> $this->input->ip_address(),
				'country_code'	=> $country_info['country_code'],
				'attempt'		=> 1,
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);
		}

		return true;
	}

	public function getCountry() {
		try {
			$reader = new Reader(FCPATH . 'assets/citydb/GeoLite2-City.mmdb');
			$record = $reader->city($this->input->ip_address());

			if (!empty($record->country->name)) {
				$json['country'] 		= $record->country->name;
				$json['country_code'] 	= strtolower($record->country->isoCode);
				$json['region'] 		= $record->mostSpecificSubdivision->name;
				$json['city'] 			= $record->city->name;
				$json['zipcode'] 		= $record->postal->code;
			} else {
				$json['country']		= 'USA';
				$json['country_code'] 	= 'us';
			}
		} catch (Exception $e) {
			$json['country']		= 'USA';
			$json['country_code'] 	= 'us';

			log_kb('GeoLite Error:: ' . $e->getMessage());
		}

		return $json;
	}

	private function _validateRateLimit($suppress = false) {
		if ($suppress) return true;
		if (empty($this->input->post('mobile')) && empty($this->input->post('email'))) return true;

		$cache_key = sprintf('otp_rate_limit_%s', $this->input->post('mobile')
			? $this->input->post('mobile')
			: $this->input->post('email')
		);

		$existing_limit = $this->cache->get($cache_key);

		if (!empty($existing_limit)) {
			return false;
		}

		$this->cache->save($cache_key, 1, $this->_cache_ttl);

		return true;
	}

	private function _validateIpSessionRateLimit($suppress = false) {
		if ($suppress) return true;

		$country_info = self::getCountry();

		if (
			strtolower($country_info['country_code']) === 'in' &&
			empty($this->session->userdata('user_id'))
		) {
			return true;
		}

		$cache_key = sprintf('otp_ip_session_rate_limit_%s', $this->session->userdata('user_id')
			? (int)$this->session->userdata('user_id')
			: $this->input->ip_address()
		);

		$existing_limit = $this->cache->get($cache_key);

		if (!empty($existing_limit)) {
			return false;
		}

		$this->cache->save($cache_key, 1, $this->_cache_ttl);

		return true;
	}

	private function _validateDailyRateLimit($suppress = false) {
		if ($suppress) return true;

		$country_info = self::getCountry();

		$limit = 10;

		if (strtolower($country_info['country_code']) === 'in') {
			$limit = ENVIRONMENT === 'production' ? 100 : 10;
		}

		$cache_key = sprintf('otp_day_rate_limit_%s', $this->session->userdata('user_id')
			? (int)$this->session->userdata('user_id')
			: $this->input->ip_address()
		);

		$existing_limit = $this->cache->get($cache_key);

		if (!empty($existing_limit)) {
			if ($existing_limit < $limit) {
				$existing_limit++;
				$this->cache->save($cache_key, $existing_limit, $this->_cache_day_ttl);
			} else {
				return false;
			}
		} else {
			$this->cache->save($cache_key, 1, $this->_cache_day_ttl);
		}

		return true;
	}
}
