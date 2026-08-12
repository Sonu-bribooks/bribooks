<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

final class Api_lib {
	private $_endpoint		= NULL;
	private $_ch			= NULL;
	private $_response		= NULL;
	private $_header		= [];
	private $_option		= [];
	private $_set			= [];
	private $_where			= [];
	private $_like			= [];
	private $_select		= NULL;
	private $_table			= NULL;
	private $_limit			= NULL;
	private $_offset		= NULL;
	private $_orderby		= NULL;
	private $_direction		= NULL;
	private $_debug			= false;

	public function __construct() {
		$this->CI =& get_instance();

		//load common config
		$this->CI->load->config('common');

		$this->_endpoint = $this->CI->config->item('api');

		$this->_ch = curl_init();

		$this->_header = [];

		$this->_option = [
			CURLOPT_HEADER		 	=> 0,
			CURLOPT_SSL_VERIFYPEER 	=> 0,
			CURLOPT_RETURNTRANSFER 	=> 1,
			CURLOPT_FOLLOWLOCATION 	=> 1,
			CURLOPT_FORBID_REUSE   	=> 1,
			CURLOPT_FRESH_CONNECT  	=> 1,
			CURLOPT_CONNECTTIMEOUT 	=> 10,
			CURLOPT_TIMEOUT			=> 20
		];
	}

	public function __destruct() {
		curl_close($this->_ch);
	}

	public function debug($status = FALSE) {
		$this->_debug = $status;

		return $this;
	}

	public function setHeader($key = NULL, $value = NULL) {
		if ($key) {
			if (!is_array($key) && $value) {
				$key = [$key => $value];
			}

			foreach ($key as $k => $v) {
				$this->_header[$k] = $v;
			}
		}

		return $this;
	}

	public function setOption($key = NULL, $value = NULL) {
		if ($key && $value) {
			if (!is_array($key)) {
				$key = [$key => $value];
			}

			foreach ($key as $k => $v) {
				$this->_option[$k] = $v;
			}
		}

		return $this;
	}

	public function select($select = '*', $escape = NULL) {
		$this->_select = $select;

		return $this;
	}

	public function get($table, $limit = NULL, $offset = NULL) {
		$this->_table = $table;

		if ($limit) {
			$this->limit($limit, $offset);
		}

		$this->_query();

		return $this;
	}

	public function get_where($table, $where = NULL, $limit = NULL, $offset = NULL) {
		$this->_table = $table;

		$this->where($where);

		if ($limit) {
			$this->limit($limit, $offset);
		}

		$this->_query();

		return $this;
	}

	public function from($table, $where = NULL, $limit = NULL, $offset = NULL) {
		$this->_table = $table;

		$this->where($where);

		if ($limit) {
			$this->limit($limit, $offset);
		}

		return $this;
	}

	public function where($key, $value = NULL, $escape = NULL) {
		if (!is_array($key)) {
			$key = [$key => $value];
		}

		foreach ($key as $k => $v) {
			$this->_where[$k] = $v;
		}

		return $this;
	}

	public function like($field, $match = '', $side = 'both', $escape = NULL) {
		if (!is_array($field)) {
			$field = [$field => $match];
		}

		foreach ($field as $k => $v) {
			$this->_like[$k] = $v;
		}

		return $this;
	}

	public function order_by($orderby, $direction = 'DESC', $escape = NULL) {
		$this->_orderby = $orderby;
		$this->_direction = $direction;

		return $this;
	}

	public function limit($value, $offset = 0) {
		$this->_limit = $value;

		if ($offset) {
			$this->offset($offset);
		}

		return $this;
	}

	public function offset($offset) {
		$this->_offset = $offset;

		return $this;
	}

	public function set($key, $value = '', $escape = NULL) {
		if (!is_array($key)) {
			$key = [$key => $value];
		}

		foreach ($key as $k => $v) {
			$this->_set[$k] = $v;
		}

		return $this;
	}

	public function insert($table, $set = NULL, $escape = NULL) {
		$this->_table = $table;

		$this->set($set);

		$this->_query();

		return $this;
	}

	public function insert_batch($table, $set = NULL, $escape = NULL, $batch_size = 100) {
		$this->_table = $table;

		$this->set($set);

		$this->_query();

		return $this;
	}

	public function update($table = '', $set = NULL, $where = NULL, $limit = NULL) {
		$this->_table = $table;

		$this->set($set);
		$this->where($where);

		$this->_query();

		return $this;
	}

	public function update_batch($table, $set = NULL, $value = NULL, $batch_size = 100) {
		$this->_table = $table;
		$this->set($set);
		$this->where($where);

		return $this;
	}

	public function truncate($table = '') {
		$this->_table = $table;

		return $this;
	}

	public function delete($table = '', $where = '', $limit = NULL, $reset_data = TRUE) {
		$this->_table = $table;

		$this->where($where);

		if ($limit) {
			$this->limit($limit);
		}

		$this->_query();

		return $this;
	}

	public function empty_table($table = '') {
		$this->_table = $table;

		return $this;
	}

	public function last_query() {
		return [
			'tabel' 	=> $this->_table,
			'data'		=> $this->_set + $this->_where + [
			'limit'		=> $this->_limit,
			'offset'	=> $this->_offset,
			'sort'		=> $this->_orderby,
			'order'		=> $this->_direction,
		]];
	}

	private function _query() {
		$additional = [];
		$this->_limit && $additional['limit'] = $this->_limit;
		$this->_offset && $additional['offset'] = $this->_offset;
		$this->_orderby && $additional['sort'] = $this->_orderby;
		$this->_direction && $additional['order'] = $this->_direction;

		$this->_post($this->_table, $this->_set + $this->_where + $this->_like + $additional);

		!$this->_debug && $this->_reset();
	}

	private function _reset() {
		$this->_table = $this->_direction = $this->_offset = $this->_limit = $this->_orderby = '';
		$this->_set = $this->_where = [];
	}

	private function _get($endpoint = NULL, $data = []) {
		$this->_option[CURLOPT_CUSTOMREQUEST] = 'GET';

		$this->curl($endpoint, $data);

		return $this;
	}

	private function _post($endpoint = NULL, $data = []) {
		$this->_option[CURLOPT_CUSTOMREQUEST] = 'POST';

		$this->curl($endpoint, json_encode($data));

		return $this;
	}

	private function _put($endpoint = NULL, $data = []) {
		$this->_option[CURLOPT_CUSTOMREQUEST] = 'PUT';

		$this->curl($endpoint, $data);

		return $this;
	}

	private function _delete($endpoint = NULL, $data = []) {
		$this->_option[CURLOPT_CUSTOMREQUEST] = 'DELETE';

		$this->curl($endpoint, $data);

		return $this;
	}

	public function insert_id() {
		return isset($this->rows()['data']) ? $this->rows()['data'] : 0;
	}

	public function row() {
		return isset($this->rows()['data']) ? $this->rows()['data'][0] : [];
	}

	public function rows() {
		return json_decode($this->_response, true);
	}

	public function raw() {
		return $this->_response;
	}

	public function info() {
		return curl_getinfo($this->_ch);
	}

	public function debugLog() {
		return [
			'headers'	=> $this->_header,
			'data'		=> $this->_set + $this->_where + $this->_like ,
			'endpoint'	=> $this->_table,
			'option'	=> $this->_option,
			'info'		=> $this->info(),
			'raw'		=> $this->raw(),
		];
	}

	private function curl($endpoint, $data = NULL) {
		$this->_option[CURLOPT_URL] = $this->_endpoint . $endpoint;

		if ($data) {
			$this->_option[CURLOPT_POSTFIELDS] = $data;
		}

		if ($this->_header) {
			$headers = [];

			foreach ($this->_header as $k => $v) {
				$headers[] = $k . ': ' . trim($v);
			}

			$this->_option[CURLOPT_HTTPHEADER] = $headers;
		}

		curl_setopt_array($this->_ch, $this->_option);

		$this->_response = curl_exec($this->_ch);

		if (defined('DEBUG_API') && DEBUG_API) {
			log_message('API', sprintf('API Call with endpoint:: %s data :: %s response :: %s', $endpoint, print_r($data, 1), print_r($this->_response, 1)));

			if (defined('DEBUG_API_DEEP') && DEBUG_API_DEEP) {
				log_message('API', print_r(array_map(function($debug) {
					return [
						'file'		=> $debug['file'] ?? '',
						'line'		=> $debug['line'] ?? '',
					];
				}, array_filter(debug_backtrace(), function($debug) {
					if (isset($debug['class']) && strpos($debug['file'], 'application') !== false && strpos($debug['file'], 'core') === false && $debug['class'] != 'Api_lib') {
						return true;
					}
				})), 1));
			}
		}
	}
}
