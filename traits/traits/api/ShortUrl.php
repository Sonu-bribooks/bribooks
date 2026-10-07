<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ShortUrl {
    public function getUrlByCode() {
		$this->form_validation->set_rules('code', _l('code'), ['trim']);
		self::_runFormValidation();

		if (!$this->json) {
			$code = trim($this->input->post('code'));
			if (empty($code)) {
				return  $this->json['error'] = _l('short_code_is_required');
			}

			$env_prefix = (ENVIRONMENT === 'production') ? 'live' : 'test';
			$code_cache_key = sprintf('%s_short_code_%s', $env_prefix, $code);

			$short_url_info = null;
			$redis_available = false;

			try {
				if (!isset($this->redis_lib)) {
					$this->load->library('Redis_lib');
				}
				if (isset($this->redis_lib) && method_exists($this->redis_lib, 'get')) {
					$redis_available = true;
					$cached_data = $this->redis_lib->get($code_cache_key);
					if (!empty($cached_data) && is_array($cached_data) && !empty($cached_data['url'])) {
						$short_url_info = $cached_data;
					}
				}
			} catch (Exception $e) {
				$redis_available = false;
			}

			if (empty($short_url_info)) {
				$this->load->model('common/ShortUrl_model', 'short_url_model');
				$short_url_info = $this->short_url_model->getByCode($code);

				if (!empty($short_url_info) && $redis_available) {
					try {
						$this->redis_lib->save($code_cache_key, [
							'id'  => $short_url_info['id'] ?? 0,
							'url' => $short_url_info['url'] ?? '',
						], 86400 * 30);
					} catch (Exception $e) {}
				}
			}
            
			if (empty($short_url_info) || empty($short_url_info['url'])) {
				return  $this->json['error'] = _l('please_use_valid_code');
			}

			$this->json['success']		= _l('success');
			$this->json['short_url'] 	= [
				'id' 	=> $short_url_info['id'] ?? 0,
				'url'	=> $short_url_info['url'] ?? 0,
			];
        }
    }
}

