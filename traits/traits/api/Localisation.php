<?php defined('BASEPATH') or exit('No direct script access allowed');

use GeoIp2\Database\Reader;

trait Localisation {
	public function getLangs() {
		if (!$this->json) {
			$country_info = self::getCountry(true);
			$country_code = $country_info['country_code'];
			$country_info = $this->country_model->getByCode($country_code);

			if (($country_info['lang_code'] ?? 'en') === 'en') return;

			$cache_ttl 	= ENVIRONMENT === 'production' ? 14400 : 1;
			$cache_key 	= 'get_languages_' . $country_info['code'];
			$languages 	= json_decode($this->cache->get($cache_key), true);

			log_kb(['GetLanguages::cache_data::' => $languages]);

			if (empty($languages)) {
				$languages = array_map(function($item) {
					return [
						'id'	=> $item['id'],
						'code'	=> $item['code'],
						'name'	=> $item['name'],
					];
				}, $this->language_model->get_all()['rows'] ?? []);

				$this->cache->save($cache_key, json_encode($languages), $cache_ttl);
			}

			$this->json['languages'] = $languages;
		}
	}

	public function setLang() {
		if (!$this->json) {
			if (
				$this->input->post('code') &&
				($info = $this->language_model->get_all(['code' => $this->input->post('code')])['rows'][0] ?? [])
			) {
				$locale_ttl = 30 * 24 * 3600;

				$this->input->set_cookie('user_language', $info['code'], $locale_ttl);
			}
		}
	}

	public function getLocale() {
		if (!$this->json) {
			self::_getLocale();
		}
	}

	private function _setLocale() {
		if (
			$this->input->method() !== 'options' &&
			empty($this->session->userdata('user_site'))
		) {
			$country_info = self::getCountry(true);
			$country_name = $country_info['country'];

			$site_info = $this->site_model->getSiteByName($country_name);

			$site_id = $site_info['id'] ?? 0;

			if (empty($site_id)) {
				$site_id = $this->config->item('default_site_id');
			}

			self::_resetUserLocale($site_id, $country_info);
		}
	}

	private function _resetUserLocale($site_id = 0, $country_info = []) {
		$this->session->set_userdata('user_site', (int)$site_id);

		$locale_ttl = 30 * 24 * 3600;

		$this->input->set_cookie('user_site', (int)$site_id, $locale_ttl);
		$this->input->set_cookie('user_country', $country_info['country'], $locale_ttl);
		$this->input->set_cookie('user_country_code', $country_info['country_code'], $locale_ttl);

		if (empty($this->input->cookie('user_language'))) {
			$this->input->set_cookie('user_language', $country_info['language'], $locale_ttl);
		}

		empty($this->input->cookie('bb_user_id')) && $this->input->set_cookie('bb_user_id', uniqid(), $locale_ttl);
	}

	public function getCountry($return = FALSE) {
		try {
			$reader = new Reader(FCPATH . 'assets/citydb/GeoLite2-City.mmdb');
			$record = $reader->city($this->input->ip_address());

			if (!empty($record->country->name)) {
				$json['country'] 		= $record->country->name;
				$json['country_code'] 	= strtolower($record->country->isoCode);
				$json['region'] 		= $record->mostSpecificSubdivision->name;
				$json['city'] 			= $record->city->name;
				$json['zipcode'] 		= $record->postal->code;
				$json['language'] 		= 'en';

				// if ($country_info = $this->db->get_where('country', [
				// 	'code'		=> $json['country_code'],
				// 	'_deleted'	=> 0,
				// ])->row_array()) {
				// 	$json['language'] = $country_info['lang_code'];
				// }
			} else {
				$json['country']		= 'USA';
				$json['country_code'] 	= 'us';
				$json['language'] 		= 'en';
			}
		} catch (Exception $e) {
			$json['country']		= 'USA';
			$json['country_code'] 	= 'us';
			$json['language'] 		= 'en';

			log_kb('GeoLite Error:: ' . $e->getMessage());
		}

		// Indian timezone fallback plan
		if (
			$this->input->post('timezone') &&
			$this->input->post('timezone') == -330 &&
			$json['country'] !== 'india' &&
			_check_indian_ip($this->input->ip_address())
		) {
			$json['country']		= 'India';
			$json['country_code'] 	= 'in';
			$json['language'] 		= 'en';
		}

		log_kb(['IP Location::' => [$json]]);

		if ($return) {
			return $json;
		} else {
			$this->json = $json;
		}
	}

	private function _getLocale() {
		if (
			empty($this->input->cookie('user_country_code')) ||
			empty($this->session->userdata('user_site')) ||
			$this->input->post('code') ||
			empty($this->session->userdata('user_id')) ||
			true
		) {
			// multi franchise locale
			$country_info = self::getCountry(true);
			$country_name = $country_info['country'];

			$site_info = $this->site_model->getSiteByName($country_name);

			$site_id = $site_info['id'] ?? 0;

			if (empty($site_id)) {
				$site_id = $this->config->item('default_site_id');
			}

			$this->json['user_site'] 			= $site_id;
			$this->json['user_country'] 		= $country_info['country'];
			$this->json['user_country_code'] 	= $country_info['country_code'];
			$this->json['user_language'] 		= $this->input->cookie('user_language') ? $this->input->cookie('user_language', true) : $country_info['language'];
			$this->json['logo'] 				= [
				'logo'		=> '',
				'width'		=> 220,
				'height'	=> 40,
			];

			log_kb(['getLocale:: ' => [
				'site_id'		=> $site_id,
				'country_name'	=> $country_name,
			]]);

			self::_resetUserLocale($site_id, $country_info);
		}
	}

	public function getCountries() {
		if (!$this->json) {
			$this->json['countries'] = array_map(function ($item) {
				return [
					'id'	=> $item['id'],
					'code'	=> strtolower($item['code']),
					'name'	=> $item['name'],
				];
			}, $this->country_model->get_all([
				'sort'			=> 'country.name',
				'order'			=> 'ASC',
			])['rows'] ?? []);
		}
	}

	public function getStates() {
		if (!$this->json) {
			$this->json['states'] = array_map(function ($item) {
				return [
					'id'	=> $item['id'],
					'name'	=> $item['name'],
				];
			}, $this->state_model->get_all([
				'country_id' 	=> $this->input->post('country_id') ?? $this->config->item('site_country_id'),
				'sort'			=> 'state.name',
				'order'			=> 'ASC',
			])['rows'] ?? []);
		}
	}

	public function getCities() {
		$this->form_validation->set_rules('state_id', _l('state_id'), [
			'trim',
			'required',
			'numeric',
			['state', [$this->validate_model, 'state']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->json['cities'] = array_map(function ($item) {
				return [
					'id'	=> $item['id'],
					'name'	=> $item['name'],
				];
			}, $this->city_model->get_all([
				'state_id' 	=> $this->input->post('state_id'),
				'sort'		=> 'city.name',
				'order'		=> 'ASC',
			])['rows'] ?? []);
		}
	}

	public function getSchoolStates() {
		if (!$this->json) {
			$country_info = $this->country_model->get($this->input->post('country_id') ?? $this->config->item('site_country_id'));
			$this->json['states'] = $this->school_model->getSchoolStates($country_info['code'], ($this->input->post('site_type') ?? 0), ($this->input->post('parent_id') ?? 0));
		}
	}

	public function getSchoolCities() {
		$this->form_validation->set_rules('state_id', _l('state_id'), [
			'trim',
			'required',
			'numeric',
			['state', [$this->validate_model, 'state']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->json['cities'] = $this->school_model->getSchoolCities($this->input->post('state_id'), ($this->input->post('site_type') ?? 0), ($this->input->post('parent_id') ?? 0));
		}
	}
}
