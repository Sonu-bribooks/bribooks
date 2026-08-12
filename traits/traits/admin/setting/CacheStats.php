<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait CacheStats {
	private $_cache_keys = [
		'book_front_cover_fonts',
		'spam_words',
		'ajax_dashboard_chart',
		'ajax_dashboard_user_chart',
		'homePageCount',
		'all_categories',
	];

	public function cache_stats($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'name',
			'key',
			'expire',
			'actions',
		];

		if ($param1 == 'delete') {
			$cache_key = substr($param2, strlen(ENVIRONMENT === 'production' ? 'live_' : 'test_'));

			self::_buildCacheKeys();
			self::_buildGenreCacheKeys();

			if (in_array($cache_key, $this->_cache_keys)) {
				$cache_info = $this->cache->delete($param2);
			}

			redirect(base_url('admin/cache_stats'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('cache_stats');
		$data['action_ajax'] 	= base_url('admin/ajax_cache_stats');

		$data['actions'] 		= [
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/cache_stats/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_cache_stats() {
		$json['data'] = [];

		self::_buildCacheKeys();
		self::_buildGenreCacheKeys();

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$key = 0;

		foreach ($this->_cache_keys ?? [] as $result) {
			$cache_key = vsprintf('%s_%s', [
				(ENVIRONMENT === 'production' ? 'live' : 'test'),
				$result,
			]);

			$cache_info = $this->cache->get_metadata($cache_key);

			if (empty($cache_info)) continue;

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'name'					=> _l($cache_key),
				'key'					=> $cache_key,
				'expire'				=> !empty($cache_info['expire']) ? date('Y-m-d H:i:s', $cache_info['expire']) : '',
				'actions'				=> ['id' => $cache_key],
			];

			$key++;
		}

		$json['recordsTotal'] 		= count($json['data']);
		$json['recordsFiltered'] 	= count($json['data']);

		output_json($json);
	}

	private function _buildCacheKeys() {
		$events = $this->event_model->get_all([
			'is_active_event'	=> true,
		])['rows'] ?? [];

		foreach ($events as $key => $event) {
			$this->_cache_keys[] = sprintf('event_landing_page_data_%s', $event['id']);
		}
	}

	private function _buildGenreCacheKeys() {
		$genres = $this->genre_model->get_all([])['rows'] ?? [];

		foreach ($genres as $key => $genre) {
			$this->_cache_keys[] = sprintf('genre_categories_%s', $genre['id']);
		}
	}
}
