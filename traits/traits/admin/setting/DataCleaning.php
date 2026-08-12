<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait DataCleaning {
	private $error 		= [];

	public function __construct() {
		parent::__construct();

		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'City_model');

		if ($this->session->userdata('admin_login') == false) {
			redirect(site_url('login'), 'refresh');
		}

		$this->debug = true;
	}

	private function _dataCleanType() {
		return [
			'state',
			'city',
			'site',
			'school',
		];
	}

	private function _columnsData($data) {
		
		if ($data == 'state') {
			return [
				'id',
				'name',
				'_deleted',
				'new_id'
			];
		} else if ($data == 'city') {
			return [
				'id',
				'name',
				'_deleted',
				'new_id'
			];
		} else if ($data == 'site') {
			return [
				'id',
				'name',
				'_deleted',
				'new_id'
			];
		} else if ($data == 'school') {
			return [
				'id',
				'name',
				'_deleted',
				'new_id'
			];
		}
	}
	public function dataCleaning() {
		$data['action_file'] 		= site_url('admin/data_cleaning_upload');
		$data['action_type'] 		= site_url('admin/data_cleaning_type');
		$data['action_save'] 		= site_url('admin/data_cleaning_save');
		$data['action_download'] 	= site_url('admin/data_cleaning_download');
		$data['action_add'] 		= site_url('admin/data_cleaning_form');

		$data['page_name'] 			= 'data_cleaning';
		$data['page_title'] 		= _l('data_cleaning');

		$data['types'] 				= self::_dataCleanType();

		$this->load->view('backend/index', $data);
	}

	public function data_cleaning_form() {
		$data['action_save'] 	= site_url('admin/data_cleaning_merge');

		$data['page_name'] 		= 'data_cleaning_form';
		$data['page_title'] 	= _l('data_cleaning_form');
		$data['action_add'] 	= site_url('admin/data_cleaning_form');
		$data['action'] 		= site_url('admin/data_cleaning_merge');

		$this->load->view('backend/index', $data);
	}

	public function data_cleaning_merge() {
		$data = $this->input->post();

		$this->session->set_flashdata('flash_message', _l('updated_successfully'));

		if (!empty($data) && !empty($data['id']) && !empty($data['new_id']) && !empty($data['type'])) {
			switch (trim($data['type'])) {
				case 'state':
					self::replaceDataInTable('city', [
						'state_id' 		=> (int)$data['new_id'],
						'date_modified' => date('Y-m-d H:i:s')
					], [
						'state_id'		=> (int)$data['id']
					]);
					self::replaceDataInTable('site', [
						'state_id' 		=> (int)$data['new_id'],
						'date_modified' => date('Y-m-d H:i:s')
					], [
						'state_id'		=> (int)$data['id']
					]);
					self::replaceDataInTable('schools', [
						'state_id' 		=> (int)$data['new_id'],
						'date_modified' => date('Y-m-d H:i:s')
					], [
						'state_id'		=> (int)$data['id']
					]);
					self::replaceDataInTable('users', [
						'state_id' 		=> (int)$data['new_id'],
						'date_modified' => date('Y-m-d H:i:s')
					], [
						'state_id'		=> (int)$data['id']
					]);

					// Delete duplicate state
					self::replaceDataInTable('state', [
						'_deleted' 		=> 1,
						'date_modified' => date('Y-m-d H:i:s'),
						'date_deleted' 	=> date('Y-m-d H:i:s')
					], [
						'id'			=> (int)$data['id']
					]);

					break;
				case 'city':
					self::replaceDataInTable('site', [
						'city_id' 		=> (int)$data['new_id'],
						'date_modified' => date('Y-m-d H:i:s')
					], [
						'city_id'		=> (int)$data['id']
					]);
					self::replaceDataInTable('schools', [
						'city_id' 		=> (int)$data['new_id'],
						'date_modified' => date('Y-m-d H:i:s')
					], [
						'city_id'		=> (int)$data['id']
					]);
					self::replaceDataInTable('users', [
						'city_id' 		=> (int)$data['new_id'],
						'date_modified' => date('Y-m-d H:i:s')
					], [
						'city_id'		=> (int)$data['id']
					]);

					// Delete duplicate city
					self::replaceDataInTable('city', [
						'_deleted' 		=> 1,
						'date_modified' => date('Y-m-d H:i:s'),
						'date_deleted' 	=> date('Y-m-d H:i:s')
					], [
						'id'			=> (int)$data['id']
					]);
					break;
				case 'site':
					self::replaceDataInTable('users', [
						'site_id' 		=> (int)$data['new_id'],
						'date_modified' => date('Y-m-d H:i:s')
					], [
						'site_id'		=> (int)$data['id'],
						'role_id'		=> 2
					]);

					if (empty($this->user_model->get_all([
						'site_id'		=> (int)$data['new_id'],
						'role_id'		=> 9
					])['rows'][0] ?? '') && 
					!empty($this->user_model->get_all([
						'site_id'		=> (int)$data['id'],
						'role_id'		=> 9
					])['rows'][0] ?? '')) {
						self::replaceDataInTable('users', [
							'site_id' 		=> (int)$data['new_id'],
							'date_modified' => date('Y-m-d H:i:s')
						], [
							'site_id'		=> (int)$data['id'],
							'role_id'		=> 9
						]);
					}

					// Delete duplicate city
					self::replaceDataInTable('site', [
						'_deleted' 		=> 1,
						'date_modified' => date('Y-m-d H:i:s'),
						'date_deleted' 	=> date('Y-m-d H:i:s')
					], [
						'id'			=> (int)$data['id']
					]);

					if (!empty($this->school_model->get_all([
						'site_id' => $data['id']
					])['rows'] ?? []) && 
					empty($this->school_model->get_all([
						'site_id' => $data['new_id']
					])['rows'] ?? [])) {
						self::replaceDataInTable('schools', [
							'site_id' 		=> (int)$data['new_id'],
							'date_modified' => date('Y-m-d H:i:s'),
						], [
							'site_id'		=> (int)$data['id']
						]);
					} else {
						self::replaceDataInTable('schools', [
							'_deleted' 		=> 1,
							'date_modified' => date('Y-m-d H:i:s'),
							'date_deleted' 	=> date('Y-m-d H:i:s')
						], [
							'site_id'		=> (int)$data['id']
						]);
					}

					break;
				case 'school':
					// Delete duplicate city
					self::replaceDataInTable('schools', [
						'_deleted' 		=> 1,
						'date_modified' => date('Y-m-d H:i:s'),
						'date_deleted' 	=> date('Y-m-d H:i:s')
					], [
						'id'			=> (int)$data['id']
					]);

					break;
				default:
					$this->session->set_flashdata('error_message', _l('nothing_to_merge'));
			}
		}

		redirect(site_url('admin/dataCleaning/data_cleaning_form'), 'refresh');
	}


	public function data_cleaning_type() {
		$data = [];

		if ($this->input->method() == 'post' && $this->input->post('type') && in_array($this->input->post('type'), self::_dataCleanType())) {
			$this->session->set_userdata('type', $this->input->post('type'));

			$data['success']			= _l('successfully updated type');
			$data['next']				= _l('successfully updated type');
		} else {
			$data['error'] 				= _l('invalid_type');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	public function data_cleaning_upload() {
		ini_set('upload_max_filesize', '20M');
		ini_set('post_max_size', '20M');
		set_time_limit(0);

		$data['headers'] = [];

		if (!is_dir('uploads/csv/data_cleaning')) {
			mkdir('uploads/csv/data_cleaning', 0777, TRUE);
		}

		$config = [
			'upload_path' 	=> 'uploads/csv/data_cleaning',
			'allowed_types' => 'text/plain|text/csv|csv',
			'remove_spaces' => TRUE,
			'max_size' 		=> 50000,
			'file_name' 	=> 'data_' . date('d-m-Y_H_i_s')
		];

		$this->load->library('upload', $config);

		if ($this->upload->do_upload('file')) {
			$upload = $this->upload->data();

			$this->load->library('parsecsv');

			$this->parsecsv->auto($config['upload_path'] . '/' . $upload['file_name']);

			$rows = $this->parsecsv->data;

			$data['csv_file'] 	= $upload['file_name'];
			$data['type'] 		= $this->session->userdata('type');

			if ($rows) {
				$data['headers'] 		= array_map('trim', array_keys($rows[0]));
				$data['columns'] 		= self::_columnsData($this->session->userdata('type'));
				$data['success'] 		= _l('upload_success');
			} else {
				$data['error'] 			= _l('error_unknown');
			}
		} else {
			$data['error'] 				= $this->upload->display_errors();
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	public function data_cleaning_download($type = 'state') {
		log_kb([
			'data_cleaning_download' => $type
		]);
		$filename 	= 'sample_' . preg_replace(['/[^\w\s]/', '/\s+/'], [' ', ' '], $type) . '_' . date('Y_m_d_H_i_s') . '.csv';

		$fields 	= self::_columnsData($type) ?? [];

		log_kb([
			'data_cleaning_fields' => $fields
		]);

		$default_values['state'] = [
			1,
			'Delhi State',
			1,
			2,
		];

		$default_values['city'] = [
			1,
			'Delhi City',
			1,
			2,
		];

		$default_values['site'] = [
			1,
			'DPS Delhi',
			0,
			1,
		];

		$default_values['school'] = [
			1,
			'CMS School',
			0,
			1,
		];

		$results[] 	= array_combine($fields, $default_values[$type]);

		if (!headers_sent()) {
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' .  $filename . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			if (ob_get_level()) {
				ob_end_clean();
			}
		} else {
			exit('Error: Headers already sent out!');
		}

		$headers = isset($results[0]) ? array_keys($results[0]) : [];

		if (!$headers) {
			exit($this->lang->line('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		self::writeRowDataToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	private function writeRowDataToCsv($results = [], $fp = null, $headers = []) {
		if (is_array($results) && $results && is_resource($fp) && is_array($headers) && $headers) {
			fputs($fp, "\xEF\xBB\xBF");
			fputcsv($fp, $headers);

			foreach ($results as $result) {
				$row = [];

				foreach ($headers as $header) {
					if (!empty($result[$header]) && is_array($result[$header])) {
						//self::writeRowDataToCsv($result[$header], $fp, array_keys($result[$header]));
					} else {
						$row[] = !empty($result[$header]) ? $result[$header] : '';
					}
				}

				fputcsv($fp, $row);
			}
		}
	}

	public function data_cleaning_save() {
		ini_set('memory_limit', -1);
		ini_set('max_execution_time', 0);
		set_time_limit(0);

		$data = [];

		if ($this->input->method() == 'post' && $this->input->post('mapping') && $this->input->post('csv_file') && $this->input->post('type')) {
			$this->load->library('parsecsv');
			$this->parsecsv->auto('uploads/csv/data_cleaning/' . $this->input->post('csv_file'));

			$rows = $this->parsecsv->data;
			$map = $this->input->post('mapping');

			switch ($this->input->post('type')) {
				case 'state':
					$data['status'] = self::cleanState($rows, $map);
					break;
				case 'city':
					$data['status'] = self::cleanCity($rows, $map);
					break;
				case 'site':
					$data['status'] = self::cleanSites($rows, $map);
					break;
				case 'school':
					$data['status'] = self::cleanSchool($rows, $map);
					break;
				default:
					$data['error'] 	= _l('nothing_to_import_from_csv');
			}

			$data['finish'] 	= true;
			$data['success'] 	= _l('text_save_success');

			if (!empty($data['status']['skipped'])) {
				$data['error'] = sprintf(_l('skipped %s %s due to duplicate entry'), $data['status']['skipped'], $this->input->post('type'));
			}

			if (!empty($data['status']['uploaded'])) {
				$data['success'] = sprintf(_l('cleaned %s %s successfully'), $data['status']['uploaded'], $this->input->post('type'));
			}
		} else {
			$data['error'] 		= _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	private function cleanState($rows = [], $map = []) {
		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if (!empty($data) && !empty($data['_deleted']) && !empty($data['new_id'])) {
				self::replaceDataInTable('city', [
					'state_id' 		=> (int)$data['new_id'],
					'date_modified' => date('Y-m-d H:i:s')
				], [
					'state_id'		=> (int)$data['id']
				]);
				self::replaceDataInTable('site', [
					'state_id' 		=> (int)$data['new_id'],
					'date_modified' => date('Y-m-d H:i:s')
				], [
					'state_id'		=> (int)$data['id']
				]);

				self::replaceDataInTable('schools', [
					'state_id' 		=> (int)$data['new_id'],
					'date_modified' => date('Y-m-d H:i:s')
				], [
					'state_id'		=> (int)$data['id']
				]);

				self::replaceDataInTable('users', [
					'state_id' 		=> (int)$data['new_id'],
					'date_modified' => date('Y-m-d H:i:s')
				], [
					'state_id'		=> (int)$data['id']
				]);

				// Delete duplicate state
				self::replaceDataInTable('state', [
					'_deleted' 		=> 1,
					'date_modified' => date('Y-m-d H:i:s'),
					'date_deleted' 	=> date('Y-m-d H:i:s')
				], [
					'id'			=> (int)$data['id']
				]);

				$uploaded++;
			} else {
				$skipped++;
			}
		}

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function cleanCity($rows = [], $map = []) {
		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if (!empty($data) && !empty($data['_deleted']) && !empty($data['new_id'])) {

				self::replaceDataInTable('site', [
					'city_id' 		=> (int)$data['new_id'],
					'date_modified' => date('Y-m-d H:i:s')
				], [
					'city_id'		=> (int)$data['id']
				]);

				self::replaceDataInTable('schools', [
					'city_id' 		=> (int)$data['new_id'],
					'date_modified' => date('Y-m-d H:i:s')
				], [
					'city_id'		=> (int)$data['id']
				]);

				self::replaceDataInTable('users', [
					'city_id' 		=> (int)$data['new_id'],
					'date_modified' => date('Y-m-d H:i:s')
				], [
					'city_id'		=> (int)$data['id']
				]);

				// Delete duplicate city
				self::replaceDataInTable('city', [
					'_deleted' 		=> 1,
					'date_modified' => date('Y-m-d H:i:s'),
					'date_deleted' 	=> date('Y-m-d H:i:s')
				], [
					'id'			=> (int)$data['id']
				]);

				$uploaded++;
			} else {
				$skipped++;
			}
		}

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function cleanSites($rows = [], $map = []) {
		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if (!empty($data) && !empty($data['_deleted']) && !empty($data['new_id'])) {
				self::replaceDataInTable('users', [
					'site_id' 		=> (int)$data['new_id'],
					'date_modified' => date('Y-m-d H:i:s')
				], [
					'site_id'		=> (int)$data['id'],
					'role_id'		=> 2
				]);

				if (empty($this->user_model->get_all([
					'site_id'		=> (int)$data['new_id'],
					'role_id'		=> 9
				])['rows'][0] ?? '') && 
				!empty($this->user_model->get_all([
					'site_id'		=> (int)$data['id'],
					'role_id'		=> 9
				])['rows'][0] ?? '')) {
					self::replaceDataInTable('users', [
						'site_id' 		=> (int)$data['new_id'],
						'date_modified' => date('Y-m-d H:i:s')
					], [
						'site_id'		=> (int)$data['id'],
						'role_id'		=> 9
					]);
				}

				// Delete duplicate city
				self::replaceDataInTable('site', [
					'_deleted' 		=> 1,
					'date_modified' => date('Y-m-d H:i:s'),
					'date_deleted' 	=> date('Y-m-d H:i:s')
				], [
					'id'			=> (int)$data['id']
				]);

				if (!empty($this->school_model->get_all([
					'site_id' => $data['id']
				])['rows'] ?? []) && 
				empty($this->school_model->get_all([
					'site_id' => $data['new_id']
				])['rows'] ?? [])) {
					self::replaceDataInTable('schools', [
						'site_id' 		=> (int)$data['new_id'],
						'date_modified' => date('Y-m-d H:i:s'),
					], [
						'site_id'		=> (int)$data['id']
					]);
				} else {
					self::replaceDataInTable('schools', [
						'_deleted' 		=> 1,
						'date_modified' => date('Y-m-d H:i:s'),
						'date_deleted' 	=> date('Y-m-d H:i:s')
					], [
						'site_id'		=> (int)$data['id']
					]);
				}

				$uploaded++;
			} else {
				$skipped++;
			}
		}

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function cleanSchool($rows = [], $map = []) {
		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if (!empty($data) && !empty($data['_deleted']) && !empty($data['new_id'])) {

				self::replaceDataInTable('schools', [
					'_deleted' 		=> 1,
					'date_modified' => date('Y-m-d H:i:s'),
					'date_deleted' 	=> date('Y-m-d H:i:s')
				], [
					'site_id'		=> (int)$data['id']
				]);

				$uploaded++;
			} else {
				$skipped++;
			}
		}

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function replaceDataInTable($table_name, $data = [], $where = []){
		$this->db->update($table_name, $data, $where);
	}
}
