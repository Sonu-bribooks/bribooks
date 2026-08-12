<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

final class S3_lib {
	private $_s3		= NULL;
	private $_bucket	= 'bbpdfenginefiles';

	public function __construct() {
		$this->CI =& get_instance();
		$credentials = new Aws\Credentials\Credentials('', '');

		$this->_s3 = new Aws\S3\S3Client([
			'version'     						=> 'latest',
			'region'      						=> 'ap-south-1',
			'credentials' 						=> $credentials,
			'suppress_php_deprecation_warning'	=> true
		]);
	}

	public function setBucket($name) {
		$this->_bucket = $name;
	}

	private function _getKey($file = '', $dir = '', $encode = true) {
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		$file_name = $encode
			? (md5($file) . '.' . $ext)
			: ($dir ? basename($file) : $file);
		$key = $dir ? $dir . '/' . $file_name : $file_name;

		return $key;
	}

	public function listBuckets() {
		try {
			pr($this->_s3->listObjects([
				'Bucket' => $this->_bucket,
			]));
		} catch (Exception $e) {
			self::_log($e->getMessage());
		}
	}

	public function put($file = '', $dir = '', $encode = true) {
		try {
			$key = self::_getKey($file, $dir, $encode);

			$this->_s3->putObject([
				'Bucket' 		=> $this->_bucket,
				'Key' 			=> $key,
				'SourceFile' 	=> $file,
			]);

			return $key;
		} catch (Exception $e) {
			self::_log($e->getMessage());
		}
	}

	public function putFile($file = '', $dir = '', $encode = true, $ext = '.png') {
		try {
			$key = self::_getKey($file, $dir, $encode);

			$filename = rtrim($key, '.') . $ext;

			$this->_s3->putObject([
				'Bucket' 		=> $this->_bucket,
				'Key' 			=> $filename,
				'SourceFile' 	=> $file,
			]);

			return $filename;
		} catch (Exception $e) {
			self::_log($e->getMessage());
		}
	}

	public function putData($file = '', $dir = '', $data = null, $encode = true) {
		try {
			$key = self::_getKey($file, $dir, $encode);

			$this->_s3->putObject([
				'Bucket' 		=> $this->_bucket,
				'Key' 			=> $key,
				'Body'   		=> $data,
			]);

			return $key;
		} catch (Exception $e) {
			self::_log($e->getMessage());
		}
	}

	public function putStream($file = '', $dir = '', $encode = true) {
		try {
			$key = self::_getKey($file, $dir, $encode);

			$this->_s3->putObject([
				'Bucket' 		=> $this->_bucket,
				'Key' 			=> $key,
				'Body'   		=> fopen($file, 'r+'),
			]);

			return $key;
		} catch (Exception $e) {
			self::_log($e->getMessage());
		}
	}

	public function get($file = '', $dir = '', $encode = true) {
		try {
			return $this->_s3->getObject([
				'Bucket' 		=> $this->_bucket,
				'Key' 			=> self::_getKey($file, $dir, $encode),
			]);
		} catch (Exception $e) {
			self::_log($e->getMessage());
		}
	}

	public function doesExist($file = '', $dir = '', $encode = true) {
		try {
			return $this->_s3->doesObjectExist($this->_bucket, self::_getKey($file, $dir, $encode));
		} catch (Exception $e) {
			self::_log($e->getMessage());
		}
	}

	private function _log($data = []) {
		log_kb('S3_lib:: ' . print_r($data, 1));
	}

	public function download($file = '', $dir = '', $encode = true) {
		$file_name = basename($file);

		$cmd = $this->_s3->getCommand('GetObject', [
			'Bucket' 						=> $this->_bucket,
			'Key' 							=> self::_getKey($file, $dir, $encode),
			'ResponseContentDisposition'    => "attachment; filename=\"{$file_name}\"",
		]);

		$signed_url = $this->_s3->createPresignedRequest($cmd, '+15 minutes')
			->getUri()
			->__toString();

		header("Location: {$signed_url}");
	}

	public function getUrl($file = '', $dir = '', $encode = true, $timeout = 3600) {
		$file_name = basename($file);

		$cmd = $this->_s3->getCommand('GetObject', [
			'Bucket' 						=> $this->_bucket,
			'Key' 							=> self::_getKey($file, $dir, $encode),
			'ResponseContentDisposition'    => "attachment; filename=\"{$file_name}\"",
		]);

		return $this->_s3->createPresignedRequest($cmd, sprintf('+%d minutes', $timeout))
			->getUri()
			->__toString();
	}

	public function listDirObjects($dir = '') {
		return $this->_s3->listObjectsV2([
			'Bucket' 		=> $this->_bucket,
			'Prefix' 		=> $dir . '/',
			'Delimiter' 	=> '/',
		]);
	}
}
