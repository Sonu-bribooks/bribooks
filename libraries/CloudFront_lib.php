<?php defined('BASEPATH') OR exit('No direct script access allowed');

final class CloudFront_lib {
	private $_private_key_file	= '';
	private $_key_pair_id		= '';

	public function __construct() {

	}

	private function _rsa_sha1_sign($policy, $private_key_filename) {
		$signature = "";

		// load the private key
		$fp = fopen($private_key_filename, "r");
		$priv_key = fread($fp, 8192);
		fclose($fp);
		$pkeyid = openssl_get_privatekey($priv_key);

		// compute signature
		openssl_sign($policy, $signature, $pkeyid);

		// free the key from memory
		openssl_free_key($pkeyid);

		return $signature;
	}

	private function _url_safe_base64_encode($value) {
		$encoded = base64_encode($value);
		// replace unsafe characters +, = and / with the safe characters -, _ and ~
		return str_replace(
			array('+', '=', '/'),
			array('-', '_', '~'),
			$encoded);
	}

	private function _create_stream_name($stream, $policy, $signature, $key_pair_id, $expires) {
		$result = $stream;
		// if the stream already contains query parameters, attach the new query parameters to the end
		// otherwise, add the query parameters
		$separator = strpos($stream, '?') == FALSE ? '?' : '&';
		// the presence of an expires time means we're using a canned policy
		if ($expires) {
			$result .= $path . $separator . "Expires=" . $expires . "&Signature=" . $signature . "&Key-Pair-Id=" . $key_pair_id;
		}
		// not using a canned policy, include the policy itself in the stream name
		else {
			$result .= $path . $separator . "Policy=" . $policy . "&Signature=" . $signature . "&Key-Pair-Id=" . $key_pair_id;
		}

		// new lines would break us, so remove them
		return str_replace('\n', '', $result);
	}

	private function _encode_query_params($stream_name) {
		// Adobe Flash Player has trouble with query parameters being passed into it,
		// so replace the bad characters with their URL-encoded forms
		return str_replace(
			array('?', '=', '&'),
			array('%3F', '%3D', '%26'),
			$stream_name);
	}

	private function _get_canned_policy_stream_name($file_path, $private_key_filename, $key_pair_id, $expires) {
		// this policy is well known by CloudFront, but you still need to sign it, since it contains your parameters
		$canned_policy = '{"Statement":[{"Resource":"' . $file_path . '","Condition":{"DateLessThan":{"AWS:EpochTime":'. $expires . '}}}]}';
		// the policy contains characters that cannot be part of a URL, so we base64 encode it
		$encoded_policy = self::_url_safe_base64_encode($canned_policy);
		// sign the original policy, not the encoded version
		$signature = self::_rsa_sha1_sign($canned_policy, $private_key_filename);
		// make the signature safe to be included in a URL
		$encoded_signature = self::_url_safe_base64_encode($signature);

		// combine the above into a stream name
		$stream_name = self::_create_stream_name($file_path, null, $encoded_signature, $key_pair_id, $expires);
		// URL-encode the query string characters to support Flash Player
		return $stream_name;
		// return self::_encode_query_params($stream_name);
	}

	private function _get_custom_policy_stream_name($file_path, $private_key_filename, $key_pair_id, $policy) {
		// the policy contains characters that cannot be part of a URL, so we base64 encode it
		$encoded_policy = self::_url_safe_base64_encode($policy);
		// sign the original policy, not the encoded version
		$signature = self::_rsa_sha1_sign($policy, $private_key_filename);
		// make the signature safe to be included in a URL
		$encoded_signature = self::_url_safe_base64_encode($signature);

		// combine the above into a stream name
		$stream_name = self::_create_stream_name($file_path, $encoded_policy, $encoded_signature, $key_pair_id, null);
		// URL-encode the query string characters to support Flash Player
		return $stream_name;
		// return self::_encode_query_params($stream_name);
	}

	public function getUrl($file_path = '') {
		// Path to your private key.  Be very careful that this file is not accessible
		// from the web!

		$expires = time() + 6000; // 5 min from now
		$canned_policy_stream_name = self::_get_canned_policy_stream_name($file_path, $this->_private_key_file, $this->_key_pair_id, $expires);

		$client_ip = $_SERVER['REMOTE_ADDR'];
		$policy =
		'{'.
			'"Statement":['.
				'{'.
					'"Resource":"'. $file_path . '",'.
					'"Condition":{'.
						'"IpAddress":{"AWS:SourceIp":"' . $client_ip . '/32"},'.
						'"DateLessThan":{"AWS:EpochTime":' . $expires . '}'.
					'}'.
				'}'.
			']' .
		'}';

		return self::_get_custom_policy_stream_name($file_path, $this->_private_key_file, $this->_key_pair_id, $policy);
	}
}
