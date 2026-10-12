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

	// $this->form_validation->set_rules('event_id', _l('event'), [
	// 		'trim',
	// 		'required',
	// 		'numeric',
	// 		['event', [$this->validate_model, 'event']]
	// 	]);

	// public function getUrlByCode() {
	// 	$this->form_validation->set_rules('code', _l('code'), ['trim', 'required']);
	// 	$this->form_validation->set_rules('event_id', _l('event'), [
	// 		'trim',
	// 		'required',
	// 		'numeric',
	// 		['event', [$this->validate_model, 'event']]
	// 	]);		
		
	// 	self::_runFormValidation();

	// 	if (!$this->json) {
	// 		$this->load->library('Redis_lib');
	// 		$code = trim($this->input->post('code'));
	// 		$event_id = (int) $this->input->post('event_id');

	// 		if (empty($code)) {
	// 			$this->json['error'] = _l('short_code_is_required');
	// 			return;
	// 		}

	// 		$env_prefix = (ENVIRONMENT === 'production') ? 'live' : 'test';
	// 		$code_cache_key = sprintf('%s_short_code_%s_%s', $env_prefix, $event_id, $code);
	// 		$code_bloom_key = sprintf('%s_short_code_bloom_%s', $env_prefix, $event_id);

	// 		$short_url_info = null;
	// 		$code_bloom_filter = null;
	// 		$this->load->library('BloomFilter_lib');

	// 		$serialized_code_bloom = $this->redis_lib->get($code_bloom_key);
	// 		if (!empty($serialized_code_bloom)) {
	// 			$code_bloom_filter = $this->bloomfilter_lib->unserializeFilter($serialized_code_bloom);
	// 		}
	
	// 		if ($code_bloom_filter !== null) {
	// 			if (!$code_bloom_filter->contains($code)) {
	// 				$this->json['error'] = _l('please_use_valid_code');
	// 				return;
	// 			}
	// 		}
		
	// 		$cached_data = $this->redis_lib->get($code_cache_key);
	// 		if (!empty($cached_data) && is_array($cached_data) && !empty($cached_data['url'])) {
	// 			$short_url_info = $cached_data;
	// 		}
			
	// 		// Step 4: Database Fallback (Agar cache me nahi mila)
	// 		if (empty($short_url_info)) {
				
	// 			$this->load->model('common/ShortUrl_model', 'short_url_model');
				
	// 			$record = $this->short_url_model->getByCode($code);

	// 			if (!empty($record) && !empty($record['url'])) {
	// 				$short_url_info = [
	// 					'id'  => $record['id'] ?? 0,
	// 					'url' => $record['url']
	// 				];
	// 				$this->redis_lib->save($code_cache_key, $short_url_info, 86400 * 30);
	// 			}
	// 		}

	// 		// Step 5: Final Check & Response
	// 		if (empty($short_url_info) || empty($short_url_info['url'])) {
	// 			$this->json['error'] = _l('please_system_use_valid_code'); // ya jo bhi error message ho
	// 			return;
	// 		}

	// 		$this->json['success']   = _l('success');
	// 		$this->json['short_url'] = [
	// 			'id'    => $short_url_info['id'] ?? 0,
	// 			'url'   => $short_url_info['url'],
	// 		];
	// 	}
	// }
}


