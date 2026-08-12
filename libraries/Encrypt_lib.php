<?php defined('BASEPATH') OR exit('No direct script access allowed');

final class Encrypt_lib {
	private $_key = '5#2657656367%@#47Bri#%&@Books&&^)_56~';
	private $_cipher = 'aes-256-cbc';
	private $_encryption_key = '5#ghhhghB#47Bri#%&@Books&&^)_56~';
	private $_encryption_iv = 'bb566ivdata7888';
	private $_options = 0;

	public function __construct() {
		$this->CI =& get_instance();

		$this->_key .= $this->CI->session->userdata('user_mobile');
		$this->_key .= $this->CI->session->userdata('user_email');

		$this->_key = substr($this->_key, 5) . substr($this->_key, 0, 5);

		$this->_encryption_key 	= hash('sha256', $this->_encryption_key);
		$this->_encryption_iv 	= substr(hash('sha256', $this->_encryption_iv), 0, 16);
	}

	public function encrypt($data = '') {
		return openssl_encrypt($data, $this->_cipher, $this->_encryption_key, $this->_options, $this->_encryption_iv);
	}

	public function decrypt($data = '') {
		return openssl_decrypt($data, $this->_cipher, $this->_encryption_key, $this->_options, $this->_encryption_iv);
	}

	public function encode($value = '') {
		$salt = openssl_random_pseudo_bytes(8);
		$salted = '';
		$dx = '';

		while (strlen($salted) < 48) {
			$dx = md5($dx . $this->_key . $salt, true);
			$salted .= $dx;
		}

		$key = substr($salted, 0, 32);
		$iv = substr($salted, 32, 16);

		$encrypted_data = openssl_encrypt(
			json_encode($value),
			'aes-256-cbc',
			$key,
			OPENSSL_RAW_DATA,
			$iv
		);

		$data = [
			'ct' 	=> base64_encode($encrypted_data),
			'iv' 	=> bin2hex($iv),
			's' 	=> bin2hex($salt)
		];

		return json_encode($data);
	}

	public function decode($value = '') {
		$json = json_decode($value, true);

		if (empty($json)) return;

		$salt = hex2bin($json['s']);
		$iv = hex2bin($json['iv']);
		$ct = base64_decode($json['ct']);
		$concated_passphrase = $this->_key . $salt;
		$md5 = [];
		$md5[0] = md5($concated_passphrase, true);
		$result = $md5[0];
		$i = 1;

		while (strlen($result) < 32) {
			$md5[$i] = md5($md5[$i - 1] . $concated_passphrase, true);
			$result .= $md5[$i];
			$i++;
		}

		$key = substr($result, 0, 32);
		$data = openssl_decrypt(
			$ct,
			'aes-256-cbc',
			$key,
			OPENSSL_RAW_DATA,
			$iv
		);

		$decrypted = json_decode($data, true);

		log_kb([
			'$decrypted' 	=> $decrypted,
			'$key'			=> $this->_key,
			'$json'			=> $json,
		]);

		return $decrypted;
	}
}
