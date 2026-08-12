<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('_el')) {
	function _el($phrase = '') {
		echo _l($phrase);
	}
}

if (!function_exists('_eli')) {
	function _eli($phrase = '') {
		echo _li($phrase);
	}
}

if (!function_exists('_l')) {
	function _l($phrase = '') {
		return get_phrase($phrase);
	}
}

if (!function_exists('_li')) {
	function _li($phrase = '') {
		$CI	=&	get_instance();

		if (ENV_API || get_class($CI) === 'Api') {
			return _al($phrase, false);
		}

		$language_code = $CI->db->get_where('settings', ['key' => 'language'])->row()->value;
		$key = strtolower(preg_replace('/\s+/', '_', $phrase));

		$lang_data = openJSONFile($language_code);

		if (array_key_exists($key, $lang_data ?? [])) {
		} else {
			$lang_data[$key] 	= str_replace('_', ' ', $phrase);
			$json_data 			= json_encode($lang_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

			file_put_contents(APPPATH . 'language/' . $language_code . '.json', stripslashes($json_data));
		}

		return $lang_data[$key];
	}
}

if (!function_exists('pr')) {
	function pr($data, $status = false) {
		echo '<pre>';
		print_r($data);
		echo '</pre>';

		if ($status) exit();
	}
}

if (!function_exists('hc')) {
	function hc($data) {
		echo '<!--' . print_r($data, 1) . '-->';
	}
}

if (!function_exists('get_phrase')) {
	function get_phrase($phrase = '') {
		$CI	=&	get_instance();

		if (ENV_API || get_class($CI) === 'Api') {
			return _al($phrase, true);
		}

		$language_code = $CI->db->get_where('settings' , ['key' => 'language'])->row()->value;
		$key = strtolower(preg_replace('/\s+/', '_', $phrase));

		$lang_data = openJSONFile($language_code);

		if (array_key_exists($key, ($lang_data ?? []))) {
		} else {
			$lang_data[$key] 	= ucfirst(str_replace('_', ' ', $key));
			$json_data 			= json_encode($lang_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			file_put_contents(APPPATH . 'language/' . $language_code . '.json', stripslashes($json_data));
		}

		return ucwords($lang_data[$key]);
	}
}

if (!function_exists('openJSONFile')) {
	function openJSONFile($code) {
		$json_string = [];

		if (file_exists(APPPATH . 'language/' . $code . '.json')) {
			$json_string = file_get_contents(APPPATH . 'language/' . $code . '.json');
			$json_string = json_decode($json_string, true);
		}

		return $json_string;
	}
}

// This function helps us to create a new json file for new language
if (!function_exists('saveDefaultJSONFile')) {
	function saveDefaultJSONFile($language_code){
		$language_code = strtolower($language_code);

		if (file_exists(APPPATH . 'language/' . $language_code . '.json')) {
			$new_file 	= APPPATH . 'language/' . $language_code . '.json';
			$en_file 	= APPPATH . 'language/english.json';

			copy($en_file, $new_file);
		} else {
			$fp 		= fopen(APPPATH . 'language/' . $language_code . '.json', 'w');
			$new_file 	= APPPATH . 'language/' . $language_code . '.json';
			$en_file   	= APPPATH . 'language/english.json';

			copy($en_file, $new_file);
			fclose($fp);
		}
	}
}

// This function helps us to update a phrase inside the language file.
if (!function_exists('saveJSONFile')) {
	function saveJSONFile($language_code, $updating_key, $updating_value){
		$json_string = [];

		if (file_exists(APPPATH . 'language/' . $language_code . '.json')) {
			$json_string = file_get_contents(APPPATH . 'language/' . $language_code . '.json');
			$json_string = json_decode($json_string, true);
			$json_string[$updating_key] = $updating_value;
		} else {
			$json_string[$updating_key] = $updating_value;
		}

		$json_data = json_encode($json_string, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
		file_put_contents(APPPATH.'language/' . $language_code . '.json', stripslashes($json_data));
	}
}

if (!function_exists('deleteJSONFile')) {
	function deleteJSONFile($language_code) {
		if ($language_code != 'english' && is_file(APPPATH . 'language/' . $language_code . '.json')) {
			unlink(APPPATH . 'language/' . $language_code . '.json');
		}
	}
}

if (!function_exists('_al')) {
	function _al($phrase = '', $format = true) {
		$cache_ttl = ENVIRONMENT === 'production' ? 24*3600 : 600;

		$phrase = trim($phrase);

		if (empty($phrase)) return;

		$key 	= preg_replace(['/[^\w\s]/', '/\s+/'], [' ', '_'], mb_strtolower($phrase));
		$phrase = preg_replace('/\s+/', '_', $phrase);
		$phrase = $format ? strtolower($phrase) : $phrase;
		$phrase = $format ? ucfirst(str_replace('_', ' ', $phrase)) : str_replace('_', ' ', $phrase);

		$CI =& get_instance();
		$CI->load->model('localisation/Translation_model', 'translation_model');
		$CI->load->model('localisation/Language_model', 'language_model');
		$CI->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$translations = json_decode($CI->cache->get($key), true);

		if (empty($translations)) {
			if ($info = $CI->translation_model->get_all(['text' => $key])['rows'][0] ?? []) {
				$translations = json_decode($info['translations'], true);
			} else {
				$translations = [];

				foreach ($CI->language_model->get_all()['rows'] as $item) {
					$translations[$item['code']] = $phrase;
				}

				$CI->translation_model->add([
					'text'			=> $key,
					'translations'	=> json_encode($translations),
				]);
			}

			$CI->cache->save($key, json_encode($translations), $cache_ttl);
		}

		return $translations[$CI->input->cookie('user_language') ?? 'en'] ?? $phrase;
	}
}

if (!function_exists('_alu')) {
	function _alu($key = '') {
		$CI =& get_instance();
		$CI->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$CI->cache->delete($key);
	}
}
