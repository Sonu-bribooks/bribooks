<?php defined('BASEPATH') OR exit('No direct script access allowed');

final class Online_lib {
	protected static $_default_config = [
		'socket_type' 	=> 'tcp',
		'host' 			=> '127.0.0.1',
		'password' 		=> NULL,
		'port' 			=> 6379,
		'timeout' 		=> 0
	];
	protected $_redis;
	protected $_serialized = [];
	protected $_key = 'online:user:';

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
				$this->_redis = null;   //added by sonu for test environmen
				log_message('error', 'Redis: Redis connection failed. Check your configuration.');
			}

			if (isset($config['password']) && ! $this->_redis->auth($config['password'])) {
				log_message('error', 'Redis: Redis authentication failed.');
			}
		} catch (RedisException $e) {
			$this->_redis = null;   //added by sonu for test environment
			log_message('error', 'Redis: Redis connection refused ('.$e->getMessage().')');
		}
	}

	public function is_supported() {
		return extension_loaded('redis');
	}

	public function save($user_id = NULL, $data = [], $ttl = 300) {
		if (empty($user_id)) return;

		$data['time'] = time();

		$this->_redis->setex($this->_key . $user_id, $ttl, json_encode($data));
	}

	public function get() {
		$results = $this->_redis->keys($this->_key . '*');

		$onlines = [];

		foreach ($results as $key => $item) {
			$onlines[] = json_decode($this->_redis->get($item), true);
		}

		return $onlines;
	}

	public function total() {
		if (!$this->_redis) {
			return 0;   
		}    //added by sonu for test environment
		$keys = $this->_redis->keys($this->_key . '*');
		return !empty($keys) ? count($keys) : 0;
	}

	public function __destruct() {
		if ($this->_redis) {
			$this->_redis->close();
		}
	}
}
