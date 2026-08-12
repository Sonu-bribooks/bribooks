<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Log extends CI_Controller {
	private $error = array();

	public function __construct() {
		parent::__construct();
		ini_set('memory_limit', '-1');

		if ($this->session->userdata('admin_login') == false) {
			redirect(base_url('login'), 'refresh');
		}

		$this->log_path 	= $this->config->item('log_path') ? $this->config->item('log_path') : APPPATH . '/logs/';
		$this->extension	= $this->config->item('log_file_extension') ? $this->config->item('log_file_extension') : 'php';
	}

	public function index() {
		if ($this->session->has_userdata('error')) {
			$data['error_warning'] = $this->session->userdata('error');
			$this->session->unset_userdata('error');
		} elseif (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if ($this->session->has_userdata('success')) {
			$data['success'] = $this->session->userdata('success');
			$this->session->unset_userdata('success');
		} else {
			$data['success'] = '';
		}

		$data['filename'] 	= $this->input->get('filename');
		$data['limit'] 		= (int)($this->input->get('limit') ?? 1000);
		$data['search'] 	= (string)($this->input->get('search') ?? '');

		$data['download'] 	= base_url('log/download');
		$data['clear'] 		= base_url('log/clear');
		$data['log'] 		= '';

		$data['extension'] = $this->extension;

		$files = glob($this->log_path . '*.' . $this->extension);

		$data['files'] 	= [];
		$data['limits']	= [1000, 2000, 5000, 100000];

		if ($files) {
			array_multisort(array_map('filemtime', $files), SORT_NUMERIC, SORT_DESC, $files);

			if (!empty($data['filename'])) {
				$file = $this->log_path . $data['filename'] . '.' . $this->extension;
			} else {
				$file = $files[0];
			}

			if (file_exists($file)) {
				$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

				if (isset($lines[0]) && strpos($lines[0], 'defined(\'BASEPATH\'') !== false) {
					array_shift($lines);
				}

				if (!empty($data['search'])) {
					$lines = array_filter($lines, fn($line) => strpos($line, $data['search']) !== false);
				}

				$last_lines = array_slice($lines, -$data['limit']);

				$data['log'] = str_repeat('=', 30) . ' ' . date('M d, Y', filemtime($file)) . ' ' . str_repeat('=', 30) . "\n";
				$data['log'] .= implode("\n", $last_lines);
			}

			$data['files'] 		= $files;
			$data['filename'] 	= basename($file, '.' . $this->extension);
		}

		$data['action_filter'] 	= base_url('log');
		$data['download']		= base_url('log/download/' . $data['filename']);

		$data['page_name']  	= 'log';
		$data['page_title'] 	= _l('log');

		$this->load->view('backend/index', $data);
	}

	public function download($filename = '') {
		if (!empty($filename)) {
			$file = $this->log_path . $filename . '.' . $this->extension;

			if (!file_exists($file)) {
				$this->session->set_userdata('error', _li('file_not_found'));
				redirect(base_url('log'));
			}

			$content = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);

			$this->load->library('zip');

			$this->zip->add_data(vsprintf('error-%s.log', [
				$filename,
			]), $content);

			$zip_name = vsprintf('%s.zip', [
				$filename
			]);

			$this->zip->download($zip_name);
		} else {
			$this->session->set_userdata('error', _li('No log content found to download.'));
			redirect(base_url('log'));
		}
	}

	public function clear() {
		// $extension = $this->config->item('log_file_extension') ? $this->config->item('log_file_extension') : 'php';
		//
		// $files = glob($this->log_path . '*.' . $extension);
		//
		// foreach ($files as $file) {
		// 	@unlink($file);
		// }
		//
		// $this->session->set_userdata('success', _l('text_success'));
		// redirect(base_url('log'));
	}

	private function _allLogs($files = [], &$data = []) {
		foreach ($files as $file) {
			if (file_exists($file)) {
				$size = filesize($file);

				if ($size >= 5242880) {
					$suffix = array(
						'B',
						'KB',
						'MB',
						'GB',
						'TB',
						'PB',
						'EB',
						'ZB',
						'YB'
					);
					$i = 0;

					while (($size / 1024) > 1) {
						$size /= 1024;
						$i++;
					}

					$data['error_warning'] = sprintf(
						_li('Log file "%s" is too large (%s%s) to display. Please download it.'),
						basename($file),
						round($size, 2),
						$suffix[$i]
					);
				} else {
					$log_contents = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
					$log_contents = str_replace('<?php defined(\'BASEPATH\') OR exit(\'No direct script access allowed\'); ?>', '', $log_contents);

					$data['log'] .= str_repeat("=", 30) . ' ' . date('M d, Y', filemtime($file)) . ' ' . str_repeat("=", 30) . "\n" . $log_contents . "\n\n";
				}
			}
		}
	}
}
