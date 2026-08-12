<?php defined('BASEPATH') OR exit('No direct script access allowed');

final class Redis_lib {
	protected static $_default_config = [
		'socket_type' 	=> 'tcp',
		'host' 			=> '127.0.0.1',
		'password' 		=> NULL,
		'port' 			=> 6379,
		'timeout' 		=> 0
	];
	protected $_redis;
	protected $_serialized = [];

	protected static $_delete_name;
	protected static $_sRemove_name;

	public function __construct() {
		if (!$this->is_supported()) {
			log_message('error', 'Redis: Failed to create Redis object; extension not loaded?');
			return;
		}

		if (!isset(static::$_delete_name, static::$_sRemove_name)) {
			if (version_compare(phpversion('redis'), '5', '>=')) {
				static::$_delete_name  = 'del';
				static::$_sRemove_name = 'sRem';
			} else {
				static::$_delete_name  = 'delete';
				static::$_sRemove_name = 'sRemove';
			}
		}

		$CI =& get_instance();

		if ($CI->config->load('redis', TRUE, TRUE)) {
			$config = array_merge(self::$_default_config, $CI->config->item('redis'));
		} else {
			$config = self::$_default_config;
		}

		$this->_redis = new Redis();

		try {
			if ($config['socket_type'] === 'unix') {
				$success = $this->_redis->connect($config['socket']);
			} else {
				$success = $this->_redis->connect($config['host'], $config['port'], $config['timeout']);
			}

			if (!$success) {
				log_message('error', 'Redis: Redis connection failed. Check your configuration.');
			}

			if (isset($config['password']) && ! $this->_redis->auth($config['password'])) {
				log_message('error', 'Redis: Redis authentication failed.');
			}
		} catch (RedisException $e) {
			log_message('error', 'Redis: Redis connection refused ('.$e->getMessage().')');
		}
	}

	public function get($key) {
		$value = $this->_redis->get($key);

		if ($value !== FALSE && $this->_redis->sIsMember('_ci_redis_serialized', $key)) {
			return unserialize($value);
		}

		return $value;
	}

	public function save($id, $data, $ttl = 60, $raw = FALSE) {
		if (is_array($data) OR is_object($data)) {
			if (!$this->_redis->sIsMember('_ci_redis_serialized', $id) && !$this->_redis->sAdd('_ci_redis_serialized', $id)) {
				return FALSE;
			}

			isset($this->_serialized[$id]) OR $this->_serialized[$id] = TRUE;
			$data = serialize($data);
		} else {
			$this->_redis->{static::$_sRemove_name}('_ci_redis_serialized', $id);
		}

		return $this->_redis->set($id, $data, $ttl);
	}

	public function delete($key) {
		if ($this->_redis->{static::$_delete_name}($key) !== 1) {
			return FALSE;
		}

		$this->_redis->{static::$_sRemove_name}('_ci_redis_serialized', $key);

		return TRUE;
	}

	public function increment($id, $offset = 1) {
		return $this->_redis->incrBy($id, $offset);
	}

	public function decrement($id, $offset = 1) {
		return $this->_redis->decrBy($id, $offset);
	}

	public function clean() {
		return $this->_redis->flushDB();
	}

	public function cache_info($type = NULL) {
		return $this->_redis->info();
	}

	public function addToRank($key = NULL, $score = 0, $value = NULL) {
		if (!is_array($score)) {
			$score = [
				[
					'score'	=> $score,
					'value'	=> $value,
				]
			];
		}

		foreach ($score as $item) {
			$this->_redis->zAdd($key, $item['score'], $item['value']);
		}
	}

	public function removeFromRank($key = NULL, $value = NULL) {
		return $this->_redis->zRem($key, $value);
	}

	public function updateRank($key = NULL, $score = 0, $value = NULL) {
		return $this->_redis->zIncrBy($key, $score, $value);
	}

	public function getRank($key = NULL, $value = NULL) {
		return $this->_redis->zRank($key, $value);
	}

	public function getScore($key = NULL, $value = NULL) {
		return $this->_redis->zScore($key, $value);
	}

	public function getRanks($key = NULL, $start = 0, $end = -1) {
		return $this->_redis->zRange($key, $start, $end, true);
	}

	public function getTotal($key = NULL, $start = 0, $end = INF) {
		return $this->_redis->zCount($key, '-inf', '+inf');
	}

	public function getRanksByScoreRange($key = NULL, $start_score = 0, $end_score = 1) {
		return $this->_redis->zRangeByScore($key, $start_score, $end_score, ['withscores' => TRUE]);
	}

	public function removeRangeRank($key = NULL, $start_rank = 0, $end_rank = 1) {
		return $this->_redis->zRemRangeByRank($key, $start_rank, $end_rank);
	}

	public function get_metadata($key) {
		$value = $this->get($key);

		if ($value !== FALSE) {
			return [
				'expire' 	=> time() + $this->_redis->ttl($key),
				'data' 		=> $value
			];
		}

		return FALSE;
	}

	public function is_supported() {
		return extension_loaded('redis');
	}

	public function __destruct() {
		if ($this->_redis) {
			$this->_redis->close();
		}
	}
}
