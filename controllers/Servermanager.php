<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

class ServerManager extends CI_Controller {
	public function __construct() {
		parent::__construct();

		if ($this->session->userdata('admin_login') == false) {
			redirect(base_url('login'), 'refresh');
		}

		$this->_allowed_buckets = [
			'bbvideolessons',
			'bbprivateimagesin',
		];

		$this->_base_url 	= $this->config->item('cloudfront_url');
		$this->_acl			= 'public-read';
		$this->_bucket 		= 'youbooks-storage-5fd6173683748-webdev';
		$this->_region 		= 'us-east-1';
		$this->_credentials = new Aws\Credentials\Credentials(
			'',
			''
		);

		$this->_s3 = new Aws\S3\S3Client([
			'version'     	=> 'latest',
			'region'      	=> $this->_region ,
			'credentials' 	=> $this->_credentials,
		]);

		$this->_admin_role_ids = [1, 5];

		$this->load->model('common/Image_model', 'image_model');

		$this->load->library('Pagination_lib', 'pagination_lib');

		if (
			$this->session->userdata('user_id') &&
			!in_array($this->session->userdata('role_id'), $this->_admin_role_ids)
		) {
			$this->_user_directory = $this->config->item('s3_user_gallery') . 'user/' . md5('secure_bb_folder' . $this->session->userdata('user_id')) . '/';

			self::_createUserDirectory($this->_user_directory);
		} else {
			$this->_user_directory = $this->config->item('s3_user_gallery');
		}

		ini_set('memory_limit', -1);
	}

	private function _setBucket($name) {
		$this->_bucket = $name;
	}

	private function _setRegion($region) {
		$this->_region = $region;

		$this->_s3 = new Aws\S3\S3Client([
			'version'     	=> 'latest',
			'region'      	=> $this->_region ,
			'credentials' 	=> $this->_credentials,
		]);
	}

	private function _createUserDirectory($directory = '') {
		if (!$this->_s3->doesObjectExist($this->_bucket, $directory)) {
			$this->_s3->putObject([
				'Bucket' => $this->_bucket,
				'Key'    => $directory,
				'Body'   => '',
				'ACL'    => $this->_acl,
			]);
		}
	}

	private function _initBucket() {
		if ($this->input->get('s3_bucket') && in_array($this->input->get('s3_bucket'), $this->_allowed_buckets)) {
			self::_setBucket($this->input->get('s3_bucket'));

			if ($this->input->get('s3_region')) {
				self::_setRegion($this->input->get('s3_region'));
			}

			$this->_user_directory 	= '';
			$this->_base_url 		= '';
			$this->_acl 			= 'private';
		}
	}

	private function _updateUrlWithBucket(&$url) {
		if ($this->input->get('s3_bucket') && in_array($this->input->get('s3_bucket'), $this->_allowed_buckets)) {
			$url .= '&s3_bucket=' . $this->input->get('s3_bucket');

			if ($this->input->get('s3_region')) {
				$url .= '&s3_region=' . $this->input->get('s3_region');
			}
		}
	}

	private function _fixGalleryUrl($url) {
		if (strpos($url, $this->config->item('s3_user_gallery')) !== false) {
			return substr($url, strlen($this->config->item('s3_user_gallery')));
		}

		return $url;
	}

	public function index() {
		// Find which protocol to use to pass the full image link back
		$server = base_url();

		self::_initBucket();

		if ($this->input->get('filter_name')) {
			$filter_name = rtrim(str_replace('*', '', $this->input->get('filter_name')), '/');
		} else {
			$filter_name = null;
		}

		// Make sure we have the correct directory
		if ($this->input->get('directory')) {
			$directory = $this->_user_directory . str_replace('*', '', $this->input->get('directory'));
		} else {
			$directory = $this->_user_directory;
		}

		if ($this->input->get('page')) {
			$page = $this->input->get('page');
		} else {
			$page = 1;
		}

		$directories 	= [];
		$files 			= [];

		$data['images'] = [];

		try {
			$result = $this->_s3->listObjectsV2([
				'Bucket' 		=> $this->_bucket,
				'Prefix' 		=> $directory,
				'Delimiter' 	=> '/',
			]);

			// pr($result, 1);

			$directories = array_map(function ($item) {
				return $item['Prefix'];
			}, $result['CommonPrefixes'] ?? []);

			$url = '';

			if ($this->input->get('target')) {
				$url .= '&target=' . $this->input->get('target');
			}

			if ($this->input->get('thumb')) {
				$url .= '&thumb=' . $this->input->get('thumb');
			}

			self::_updateUrlWithBucket($url);

			foreach ($result['CommonPrefixes'] ?? [] as $key => $item) {
				if (!empty($item['Prefix'])) {
					$data['images'][] = [
						'thumb' => $this->_base_url . ($item['Key'] ?? ''),
						'name'  => basename($item['Prefix']),
						'type'  => 'directory',
						'path'  => self::_fixGalleryUrl($item['Prefix']),
						'href'  => base_url('servermanager' . '?directory=' . urlencode(substr($item['Prefix'], strlen($this->_user_directory))) . $url)
					];
				}
			}

			foreach ($result['Contents'] ?? [] as $key => $item) {
				if (!empty($item['Key'])) {
					if ($key > 0) {
						$ext = strtolower(substr(strrchr($item['Key'], '.'), 1));

						if (in_array($ext, ['doc', 'docx', 'mp4', 'ttf', 'otf', 'html'])) {
							$data['images'][] = [
								'thumb' => $this->image_model->resize('no_doc.png', 100, 100),
								'name'  => basename($item['Key']),
								'type'  => 'image',
								'path'  => self::_fixGalleryUrl($item['Key']),
								'href'  => $this->_base_url . $item['Key']
							];
						} elseif ($ext === 'pdf') {
							$data['images'][] = [
								'thumb' => $this->image_model->resize('no_pdf.png', 100, 100),
								'name'  => basename($item['Key']),
								'type'  => 'image',
								'path'  => self::_fixGalleryUrl($item['Key']),
								'href'  => $this->_base_url . $item['Key']
							];
						} else {
							$data['images'][] = [
								'thumb' => $this->_base_url ? $this->image_model->thumb($item['Key']) : $this->image_model->resize('no_doc.png', 100, 100),
								'name'  => basename($item['Key']),
								'type'  => 'image',
								'path'  => self::_fixGalleryUrl($item['Key']),
								'href'  => $this->_base_url . $item['Key']
							];
						}
					}
				}
			}
		} catch (Exception $e) {
			self::_log($e->getMessage());
		}

		$data['heading_title'] = _l('file_manager');

		if ($this->input->get('directory')) {
			$data['directory'] = urlencode($this->input->get('directory'));
		} else {
			$data['directory'] = '';
		}

		if ($this->input->get('filter_name')) {
			$data['filter_name'] = $this->input->get('filter_name');
		} else {
			$data['filter_name'] = '';
		}

		// Return the target ID for the file manager to set the value
		if ($this->input->get('target')) {
			$data['target'] = $this->input->get('target');
		} else {
			$data['target'] = '';
		}

		// Return the thumbnail for the file manager to show a thumbnail
		if ($this->input->get('thumb')) {
			$data['thumb'] = $this->input->get('thumb');
		} else {
			$data['thumb'] = '';
		}

		if ($this->input->get('s3_bucket')) {
			$data['s3_bucket'] = $this->input->get('s3_bucket');
		} else {
			$data['s3_bucket'] = '';
		}

		if ($this->input->get('s3_region')) {
			$data['s3_region'] = $this->input->get('s3_region');
		} else {
			$data['s3_region'] = '';
		}

		// Parent
		$url = '';

		if ($this->input->get('directory')) {
			$pos = strrpos(rtrim($this->input->get('directory'), '/'), '/');
			$parent_dir = substr($this->input->get('directory'), 0, $pos);

			if ($pos && $this->input->get('directory') !== $parent_dir) {
				$url .= '&directory=' . urlencode($parent_dir . '/');
			}
		}

		if ($this->input->get('target')) {
			$url .= '&target=' . $this->input->get('target');
		}

		if ($this->input->get('thumb')) {
			$url .= '&thumb=' . $this->input->get('thumb');
		}

		self::_updateUrlWithBucket($url);

		$data['parent'] = base_url('servermanager' . '?r=1' . $url);

		// Refresh
		$url = '';

		if ($this->input->get('directory')) {
			$url .= '&directory=' . urlencode($this->input->get('directory'));
		}

		if ($this->input->get('target')) {
			$url .= '&target=' . $this->input->get('target');
		}

		if ($this->input->get('thumb')) {
			$url .= '&thumb=' . $this->input->get('thumb');
		}

		self::_updateUrlWithBucket($url);

		$data['refresh'] = base_url('servermanager' . '?r=1' . $url);

		$url = '';

		if ($this->input->get('directory')) {
			$url .= '&directory=' . urlencode(html_entity_decode($this->input->get('directory'), ENT_QUOTES, 'UTF-8'));
		}

		if ($this->input->get('filter_name')) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->input->get('filter_name'), ENT_QUOTES, 'UTF-8'));
		}

		if ($this->input->get('target')) {
			$url .= '&target=' . $this->input->get('target');
		}

		if ($this->input->get('thumb')) {
			$url .= '&thumb=' . $this->input->get('thumb');
		}

		self::_updateUrlWithBucket($url);

		$this->pagination_lib->total = 1000;
		$this->pagination_lib->page = $page;
		$this->pagination_lib->limit = 1000;
		$this->pagination_lib->url = base_url('servermanager' . '?r=1' . $url . '&page={page}');

		$data['pagination'] = $this->pagination_lib->render();

		$url = '';

		if ($this->input->get('directory')) {
			$url .= '&directory=' . urlencode($this->input->get('directory'));
		}

		self::_updateUrlWithBucket($url);

		$data['action_upload'] = base_url('servermanager/upload') . '?r=1' . $url;
		$data['action_folder'] = base_url('servermanager/folder') . '?r=1' . $url;
		$data['action_delete'] = base_url('servermanager/delete') . '?r=1' . $url;

		$this->load->view('common/servermanager', $data);
	}

	public function upload() {
		$json = [];

		self::_initBucket();

		// Make sure we have the correct directory
		if ($this->input->get('directory')) {
			$directory = $this->_user_directory . $this->input->get('directory');
		} else {
			$directory = $this->_user_directory;
		}

		// Check its a directory
		if (!$this->_s3->doesObjectExist($this->_bucket, $directory)) {
			$json['error'] = _l('error_user_directory_not_exists');
		}

		if (!$json) {
			// Check if multiple files are uploaded or just one
			$files = [];

			if (!empty($_FILES['file']['name']) && is_array($_FILES['file']['name'])) {
				foreach (array_keys($_FILES['file']['name']) as $key) {
					$files[] = [
						'name'     => $_FILES['file']['name'][$key],
						'type'     => $_FILES['file']['type'][$key],
						'tmp_name' => $_FILES['file']['tmp_name'][$key],
						'error'    => $_FILES['file']['error'][$key],
						'size'     => $_FILES['file']['size'][$key]
					];
				}
			}

			foreach ($files as $file) {
				if (is_file($file['tmp_name'])) {
					// Sanitize the filename
					$filename = basename(html_entity_decode(str_replace(' ', '-', trim($file['name'])), ENT_QUOTES, 'UTF-8'));

					// Validate the filename length
					if ((strlen($filename) < 3) || (strlen($filename) > 255)) {
						$json['error'] = _l('error_filename');
					}

					// Allowed file extension types
					$allowed = [
						'jpg',
						'jpeg',
						'gif',
						'png',
						'pdf',
						'doc',
						'docx',
						'mp4',
						'ttf',
						'otf',
						'avif',
						'html',
					];

					if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
						$json['error'] = _l('error_filetype');
					}

					// Allowed file mime types
					$allowed = [
						'image/jpeg',
						'image/pjpeg',
						'image/png',
						'image/x-png',
						'image/gif',
						'application/pdf',
						'application/x-pdf',
						'application/acrobat',
						'applications/vnd.pdf',
						'text/pdf',
						'text/x-pdf',
						'application/msword',
						'application/doc',
						'appl/text',
						'application/vnd.msword',
						'application/winword',
						'application/word',
						'application/x-msw6',
						'application/x-msword',
						'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
						'video/mp4',
						'application/mp4',
						'application/x-font-ttf',
						'application/octet-stream',
						'font/ttf',
						'font/otf',
						'image/avif',
						'text/html',
					];

					if (!in_array($file['type'], $allowed)) {
						$json['error'] = _l('error_mime_type');
					}

					if (preg_match('/<\?php/ims', file_get_contents($file['tmp_name']))) {
						$json['error'] = _l('code_injection_detected');
					}

					// Return any upload error
					if ($file['error'] != UPLOAD_ERR_OK) {
						$json['error'] = _l('error_upload_' . $file['error']);
					}
				} else {
					$json['error'] = _l('error_upload');
				}

				if (!$json) {
					try {
						$filename = preg_replace(['/[^\w\s.]/', '/\s+/'], [' ', '-'], $filename);

						$this->_s3->putObject([
							'Bucket' 	=> $this->_bucket,
							'Key'    	=> $directory . $filename,
							'ACL'    	=> $this->_acl,
							'SourceFile'=> $file['tmp_name']
						]);
					} catch (Exception $e) {
						$json['error'] = $e->getMessage();
						self::_log($e->getMessage());
					}
				}
			}
		}

		if (!$json) {
			$json['success'] = _l('uploaded_successfully');
		}

		output_json($json);
	}

	public function folder() {
		$json = [];

		self::_initBucket();

		// Make sure we have the correct directory
		if ($this->input->get('directory')) {
			$directory = $this->_user_directory . $this->input->get('directory');
		} else {
			$directory = $this->_user_directory;
		}

		// Check its a directory
		if (!$this->_s3->doesObjectExist($this->_bucket, $directory)) {
			$json['error'] = _l('error_user_directory_not_exists');
		}

		if ($this->input->server('REQUEST_METHOD') == 'POST') {
			// Sanitize the folder name
			$folder = basename(html_entity_decode($this->input->post('folder'), ENT_QUOTES, 'UTF-8'));
			$folder = preg_replace(['/[^\w\s.]/', '/\s+/'], [' ', '-'], $folder);

			// Validate the filename length
			if ((strlen($folder) < 3) || (strlen($folder) > 128)) {
				$json['error'] = _l('error_folder');
			}

			// Check if directory already exists or not
			if ($this->_s3->doesObjectExist($this->_bucket, $directory . '/' . $folder)) {
				$json['error'] = _l('error_exists');
			}
		}

		if (empty($json['error'])) {
			$this->_s3->putObject([
				'Bucket' => $this->_bucket,
				'Key'    => $directory . $folder . '/',
				'Body'   => '',
				'ACL'    => $this->_acl,
			]);

			$json['success'] = _l('directory_created');
		}

		output_json($json);
	}

	public function delete() {
		$json = [];

		self::_initBucket();

		if ($this->input->post('path')) {
			$paths = $this->input->post('path');
		} else {
			$paths = [];
		}

		// Loop through each path to run validations
		foreach ($paths as $path) {
			// Check path exsists
			if ($path == $this->_user_directory) {
				$json['error'] = _l('error_delete');

				break;
			}
		}

		if (!$json) {
			// Loop through each path
			try {
				foreach ($paths as $path) {
					$result = $this->_s3->deleteObject([
						'Bucket' => $this->_bucket,
						'Key'    => $this->_user_directory . $path
					]);
				}
				$json['success'] = _l('deleted_successfully');
			} catch (Exception $e) {
				$json['error'] = $e->getMessage();
				self::_log($e->getMessage());
			}
		}

		output_json($json);
	}

	private function _log($data = []) {
		log_kb('servermanager:: ' . print_r($data, 1));
	}
}
