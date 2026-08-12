<?php defined('BASEPATH') or exit('No direct script access allowed');

trait ExchangeRateAlert {
	public function updateExchangeRateCron() {
		$this->load->model('localisation/Currency_model', 'currency_model');
		$this->load->model('localisation/ExchangeRateHistory_model', 'exchange_rate_history_model');

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$rates = self::_getExchangeRates();

		$base_rate = array_find($rates, function($value, $key) {
			return strtolower($key) == 'inr';
		});

		$results = $this->currency_model->get_all([
			'exchange_rate_gt' => 1,
		])['rows'] ?? [];

		foreach ($results as $key => $item) {
			$rate = array_find($rates, function($value, $key) use($item) {
				return strtolower($key) == strtolower($item['code']);
			});

			if ($rate > 0) {
				$new_rate = $base_rate / $rate;

				log_kb([
					'updateExchangeRateCron' => [
						$item, $rate, $base_rate, $new_rate
					]
				]);

				$this->exchange_rate_history_model->add([
					'currency_id'	=> $item['id'],
					'currency_code'	=> $item['code'],
					'old_rate'		=> (double)$item['exchange_rate'],
					'new_rate'		=> (double)$new_rate,
				]);

				$this->currency_model->edit($item['id'], [
					'exchange_rate' => (double)$new_rate,
				]);
			}
		}
	}

	private function _getExchangeRates() {
		$cache_ttl	= ENVIRONMENT === 'production' ? (24 * 3600) : (72 * 3600);
		$cache_key 	= 'get_exchange_rates_' . date('Y_m_d');
		$rates 		= json_decode($this->cache->get($cache_key), true);

		log_kb(['GetExchangeRates::cache_data::' => $rates]);

		if (empty($rates)) {
			$rates 	= [];
			$result	= _curl('https://v6.exchangerate-api.com/v6/34444069e113f6f2b30994d3/latest/USD', null, 'GET', [], null);

			log_kb(['updateExchangeRateCron' => $result]);

			if ($result['base_code'] === 'USD') {
				$rates = $result['conversion_rates'] ?? [];
				$this->cache->save($cache_key, json_encode($rates), $cache_ttl);
			}
		}

		return $rates;
	}
}
