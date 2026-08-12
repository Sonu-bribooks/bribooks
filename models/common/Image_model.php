<?php defined('BASEPATH') OR exit('No direct script access allowed');

Class Image_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->load->library('Image_lib', 'Image_lib');
	}

	public function thumb($filename = '') {
		if (empty($filename)) return self::resize('no_image.png', 100, 100);

		return $this->config->item('cloudfront_url') .
			(strpos($filename, $this->config->item('s3_user_gallery')) === 0 ? '' : $this->config->item('s3_user_gallery')) .
			$filename .
			'?width=100&height=100';
	}

	public function resize($filename, $width, $height) {
		if (!filter_var(str_replace(' ', '%20', $filename), FILTER_VALIDATE_URL) === false) {
			return $filename;
		} else if (!is_file(DIR_IMAGE . $filename)) {
			return;
		}

		//var_dump(is_file(DIR_IMAGE . $filename)); die;

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$image_old = $filename;
		$image_new = 'cache/' . substr($filename, 0, strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

		if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
			list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);

			if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
				return DIR_IMAGE . $image_old;
			}

			$path = '';

			$directories = explode('/', dirname($image_new));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			if ($width_orig != $width || $height_orig != $height) {
				$image = new Image_lib(DIR_IMAGE . $image_old);
				$image->resize($width, $height);
				$image->save(DIR_IMAGE . $image_new);
			} else {
				copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
			}
		}

		return base_url(str_replace(FCPATH, '', DIR_IMAGE) . $image_new);
	}
}
