<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('load_trait')) {
	function load_trait($module = '') {
		if (is_dir(APPPATH . "/traits/traits/{$module}/")) {
			$files = glob(APPPATH . "/traits/traits/{$module}/" . '*.php');

			foreach ($files as $file) {
				if (file_exists($file) && filesize($file) > 0) {
					require_once $file;
				}
			}
		}
	}
}
