<?php defined('BASEPATH') OR exit('No direct script access allowed');

class FileManager extends CI_Controller {
	public function __construct() {
		parent::__construct();

		if ($this->session->userdata('admin_login') == false) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->library('Pagination_lib', 'pagination_lib');
		$this->load->model('common/Image_model', 'image_model');

		$this->_admin_role_ids = [1, 5];
	}

	public function index() {
		if (
			$this->session->has_userdata('user_id') &&
			$this->session->has_userdata('role') &&
			$this->session->userdata('user_id') &&
			(!in_array($this->session->userdata('role_id'), $this->_admin_role_ids))
		) {
			$user_direactory = DIR_IMAGE . 'library/user/' . md5($this->session->userdata('user_id'));

			if (!is_dir($user_direactory)) {
				mkdir($user_direactory, 0777);
				chmod($user_direactory, 0777);
				@touch($user_direactory . '/' . 'index.html');
			}
		} else {
			$user_direactory = DIR_IMAGE . 'library';

			if (!is_dir($user_direactory)) {
				mkdir($user_direactory, 0777);
				chmod($user_direactory, 0777);
				@touch($user_direactory . '/' . 'index.html');
			}
		}

		// Find which protocol to use to pass the full image link back
		$server = base_url();

		if ($this->input->get('filter_name')) {
			$filter_name = rtrim(str_replace('*', '', $this->input->get('filter_name')), '/');
		} else {
			$filter_name = null;
		}

		// Make sure we have the correct directory
		if ($this->input->get('directory')) {
			$directory = rtrim($user_direactory . '/' . str_replace('*', '', $this->input->get('directory')), '/');
		} else {
			$directory = $user_direactory;
		}

		if ($this->input->get('page')) {
			$page = $this->input->get('page');
		} else {
			$page = 1;
		}

		$directories = array();
		$files = array();

		$data['images'] = array();

		if (is_dir($directory)) {
			// Get directories
			$directories = glob($directory . '/' . $filter_name . '*', GLOB_ONLYDIR);

			if (!$directories) {
				$directories = array();
			}

			// Get files
			$files = glob($directory . '/' . $filter_name . '*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF,pdf,doc,docx,PDF,DOC,DOCX,mp4}', GLOB_BRACE);

			array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_DESC, $files);

			if (!$files) {
				$files = array();
			}
		}

		// Merge directories and files
		$images = array_merge($directories, $files);

		// Get total number of files and directories
		$image_total = count($images);

		// Split the array based on current page number and max number of items per page of 10
		$images = array_splice($images, ($page - 1) * 16, 16);

		foreach ($images as $image) {
			$name = str_split(basename($image), 14);

			if (is_dir($image)) {
				$url = '';

				if ($this->input->get('target')) {
					$url .= '&target=' . $this->input->get('target');
				}

				if ($this->input->get('thumb')) {
					$url .= '&thumb=' . $this->input->get('thumb');
				}

				$data['images'][] = array(
					'thumb' => '',
					'name'  => implode(' ', $name),
					'type'  => 'directory',
					'path'  => substr($image, strlen(DIR_IMAGE)),
					'href'  => base_url('filemanager' . '?directory=' . urlencode(substr($image, strlen($user_direactory . '/'))) . $url)
				);
			} elseif (is_file($image)) {
				$ext = strtolower(substr(strrchr($image, '.'), 1));

				if (in_array($ext, array('doc', 'docx', 'mp4'))) {
					$data['images'][] = array(
						'thumb' => $this->image_model->resize('no_doc.png', 100, 100),
						'name'  => implode(' ', $name),
						'type'  => 'image',
						'path'  => substr($image, strlen(DIR_IMAGE)),
						'href'  => $server . substr($image, strlen(FCPATH))
					);
				} elseif ($ext === 'pdf') {
					$data['images'][] = array(
						'thumb' => $this->image_model->resize('no_pdf.png', 100, 100),
						'name'  => implode(' ', $name),
						'type'  => 'image',
						'path'  => substr($image, strlen(DIR_IMAGE)),
						'href'  => $server . substr($image, strlen(FCPATH))
					);
				} else {
					$data['images'][] = array(
						'thumb' => $this->image_model->resize(substr($image, strlen(DIR_IMAGE)), 100, 100),
						'name'  => implode(' ', $name),
						'type'  => 'image',
						'path'  => substr($image, strlen(DIR_IMAGE)),
						'href'  => $server . substr($image, strlen(FCPATH))
					);
				}
			}
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

		// Parent
		$url = '';

		if ($this->input->get('directory')) {
			$pos = strrpos($this->input->get('directory'), '/');

			if ($pos) {
				$url .= '&directory=' . urlencode(substr($this->input->get('directory'), 0, $pos));
			}
		}

		if ($this->input->get('target')) {
			$url .= '&target=' . $this->input->get('target');
		}

		if ($this->input->get('thumb')) {
			$url .= '&thumb=' . $this->input->get('thumb');
		}

		$data['parent'] = base_url('filemanager' . '?' . $url);

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

		$data['refresh'] = base_url('filemanager' . '?' . $url);

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

		$this->pagination_lib->total = $image_total;
		$this->pagination_lib->page = $page;
		$this->pagination_lib->limit = 16;
		$this->pagination_lib->url = base_url('filemanager' . '?' . $url . '&page={page}');

		$data['pagination'] = $this->pagination_lib->render();

		$this->load->view('common/filemanager', $data);
	}

	public function upload() {
		if (
			$this->session->has_userdata('user_id') &&
			$this->session->has_userdata('role') &&
			$this->session->userdata('user_id') &&
			(!in_array($this->session->userdata('role_id'), $this->_admin_role_ids))
		) {
			$user_direactory = DIR_IMAGE . 'library/user/' . md5($this->session->userdata('user_id'));
		} else {
			$user_direactory = DIR_IMAGE . 'library';
		}

		$json = array();

		// Make sure we have the correct directory
		if ($this->input->get('directory')) {
			$directory = rtrim($user_direactory . '/' . $this->input->get('directory'), '/');
		} else {
			$directory = $user_direactory;
		}

		// Check its a directory
		if (!is_dir($directory)) {
			$json['error'] = _l('error_user_directory_not_exists') . $directory;
		}

		if (!$json) {
			// Check if multiple files are uploaded or just one
			$files = array();

			if (!empty($_FILES['file']['name']) && is_array($_FILES['file']['name'])) {
				foreach (array_keys($_FILES['file']['name']) as $key) {
					$files[] = array(
						'name'     => $_FILES['file']['name'][$key],
						'type'     => $_FILES['file']['type'][$key],
						'tmp_name' => $_FILES['file']['tmp_name'][$key],
						'error'    => $_FILES['file']['error'][$key],
						'size'     => $_FILES['file']['size'][$key]
					);
				}
			}

			foreach ($files as $file) {
				if (is_file($file['tmp_name'])) {
					// Sanitize the filename
					$filename = basename(html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8'));

					// Validate the filename length
					if ((strlen($filename) < 3) || (strlen($filename) > 255)) {
						$json['error'] = _l('error_filename');
					}

					// Allowed file extension types
					$allowed = array(
						'jpg',
						'jpeg',
						'gif',
						'png',
						'pdf',
						'doc',
						'docx',
						'mp4',
					);

					if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
						$json['error'] = _l('error_filetype');
					}

					// Allowed file mime types
					$allowed = array(
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
					);

					if (!in_array($file['type'], $allowed)) {
						$json['error'] = _l('error_filetype');
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
					//$json['error'] = $directory . '/' . $filename;
					$filename = preg_replace(['/[^\w\s.]/', '/\s+/'], [' ', '-'], $filename);

					move_uploaded_file($file['tmp_name'], $directory . '/' . $filename);
				}
			}
		}

		if (!$json) {
			$json['success'] = _l('uploaded_successfully');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function folder() {
		$json = array();

		if (
			$this->session->has_userdata('user_id') &&
			$this->session->has_userdata('role') &&
			$this->session->userdata('user_id') &&
			(!in_array($this->session->userdata('role_id'), $this->_admin_role_ids))
		) {
			$user_direactory = DIR_IMAGE . 'library/user/' . md5($this->session->userdata('user_id'));
		} else {
			$user_direactory = DIR_IMAGE . 'library';
		}

		// Make sure we have the correct directory
		if ($this->input->get('directory')) {
			$directory = rtrim($user_direactory . '/' . $this->input->get('directory'), '/');
		} else {
			$directory = $user_direactory;
		}

		// Check its a directory
		if (!is_dir($directory)) {
			$json['error'] = _l('error_user_directory_not_exists');
		}

		if ($this->input->server('REQUEST_METHOD') == 'POST') {
			// Sanitize the folder name
			$folder = basename(html_entity_decode($this->input->post('folder'), ENT_QUOTES, 'UTF-8'));

			// Validate the filename length
			if ((strlen($folder) < 3) || (strlen($folder) > 128)) {
				$json['error'] = _l('error_folder');
			}

			// Check if directory already exists or not
			if (is_dir($directory . '/' . $folder)) {
				$json['error'] = _l('error_exists');
			}
		}

		if (empty($json['error'])) {
			mkdir($directory . '/' . $folder, 0777);
			chmod($directory . '/' . $folder, 0777);

			@touch($directory . '/' . $folder . '/' . 'index.html');

			$json['success'] = _l('directory_created');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function delete() {
		$json = array();

		if (
			$this->session->has_userdata('user_id') &&
			$this->session->has_userdata('role') &&
			$this->session->userdata('user_id') &&
			(!in_array($this->session->userdata('role_id'), $this->_admin_role_ids))
		) {
			$user_direactory = DIR_IMAGE . 'library/user/' . md5($this->session->userdata('user_id'));
		} else {
			$user_direactory = DIR_IMAGE . 'library';
		}

		if ($this->input->post('path')) {
			$paths = $this->input->post('path');
		} else {
			$paths = array();
		}

		// Loop through each path to run validations
		foreach ($paths as $path) {
			// Check path exsists
			//if ($path == DIR_IMAGE . 'library' || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $path)), 0, strlen(DIR_IMAGE . 'library')) != DIR_IMAGE . 'library') {
			if ($path == $user_direactory) {
				$json['error'] = _l('error_delete');

				break;
			}
		}

		if (!$json) {
			// Loop through each path
			foreach ($paths as $path) {
				$path = rtrim(DIR_IMAGE . $path, '/');

				// If path is just a file delete it
				if (is_file($path)) {
					unlink($path);

				// If path is a directory beging deleting each file and sub folder
				} elseif (is_dir($path)) {
					$files = array();

					// Make path into an array
					$path = array($path . '*');

					// While the path array is still populated keep looping through
					while (count($path) != 0) {
						$next = array_shift($path);

						foreach (glob($next) as $file) {
							// If directory add to path array
							if (is_dir($file)) {
								$path[] = $file . '/*';
							}

							// Add the file to the files to be deleted array
							$files[] = $file;
						}
					}

					// Reverse sort the file array
					rsort($files);

					foreach ($files as $file) {
						// If file just delete
						if (is_file($file)) {
							unlink($file);

						// If directory use the remove directory function
						} elseif (is_dir($file)) {
							rmdir($file);
						}
					}
				}
			}

			$json['success'] = _l('deleted_successfully');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
