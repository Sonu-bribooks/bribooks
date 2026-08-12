<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Import {
	public function import() {
		$data['action_file'] 	= site_url('portal/upload');
		$data['action_type'] 	= site_url('portal/importType');
		$data['action_save'] 	= site_url('portal/importSave');
		$data['action_download'] = site_url('portal/download');

		$data['page_name'] 		= 'import';
		$data['page_title'] 	= _l('import');

		$data['types'] 			= $this->types;

		$this->load->view('backend/index', $data);
	}

	public function download($type = 'students') {
		$filename 	= 'sample_student_' . date('Y_m_d_H_i_s') . '.csv';

		$fields 	= $this->columns[$type] ?? [];

		$results[] 	= array_combine($fields, [
			'First Name Last Name',
			'First Name Last Name',
			'abc@example.com',
			'9000000000',
			'1,2,3,4,5,6,7,8,9,10,11,12(use oneof them eg. 1)',
			'A-Z(eg. B)',
		]);

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

		$this->writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	private function writeRowToCsv($results = [], $fp = null, $headers = []) {
		if (is_array($results) && $results && is_resource($fp) && is_array($headers) && $headers) {
			fputs($fp, "\xEF\xBB\xBF");
			fputcsv($fp, $headers);

			foreach ($results as $result) {
				$row = [];

				foreach ($headers as $header) {
					if (!empty($result[$header]) && is_array($result[$header])) {
						//$this->writeRowToCsv($result[$header], $fp, array_keys($result[$header]));
					} else {
						$row[] = !empty($result[$header]) ? $result[$header] : '';
					}
				}

				fputcsv($fp, $row);
			}
		}
	}

	public function importType() {
		$data = [];

		if (
			$this->input->method() == 'post'
			&& $this->input->post('type')
			&& in_array($this->input->post('type'), $this->types)
		) {
			$this->session->set_userdata('type', $this->input->post('type'));

			$data['success']			= _l('successfully updated type');
			$data['next']				= _l('successfully updated type');
		} else {
			$data['error'] 				= _l('invalid_type');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	public function upload() {
		$data['headers'] = [];

		if (!is_dir('uploads/csv')) {
			mkdir('uploads/csv', 0777, TRUE);
		}

		$config = [
			'upload_path' 	=> "uploads/csv/",
			'allowed_types' => "text/plain|text/csv|csv",
			'remove_spaces' => TRUE,
			'max_size' 		=> "5000",
			'file_name' 	=> "student_" . date('d-m-Y_H_i_s')
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
				$data['columns'] 		= $this->columns[$this->session->userdata('type')];
				$data['success'] 		= _l('upload_success');
			} else {
				$data['error'] 			= _l('error_unknown');
			}
		} else {
			$data['error'] 				= $this->upload->display_errors();
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	public function importSave() {
		$data = [];

		if (
			$this->input->method() == 'post'
			&& $this->input->post('mapping')
			&& $this->input->post('csv_file')
			&& $this->input->post('type')
		) {
			$this->load->library('parsecsv');
			$this->parsecsv->auto('uploads/csv/' . $this->input->post('csv_file'));

			$rows = $this->parsecsv->data;
			$map = $this->input->post('mapping');

			switch ($this->input->post('type')) {
				case 'students':
					if (self::_canImport(count($rows))) {
						$data['status'] = self::importStudents($rows, $map);
					} else {
						$data['error'] = _l('check_license_limit');
					}

					break;
				default:
					$data['error'] 	= _l('nothing_to_import');
			}

			$data['finish'] 	= true;

			if (!isset($data['error'])) {
				$data['success'] 	= _l('text_save_success');
			}
		} else {
			$data['error'] 		= _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	private function importStudents($rows = [], $map = []) {
		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if (empty($data['site_id']) || 1) {
				$data['site_id'] = $this->config->item('site_id');
			}

			if (empty($data['student_name'])) continue;
			if (
				!$this->config->item('site_can_add_site')
				&& $data['site_id'] != $this->config->item('site_id')
			) continue;

			$site_info = $this->site_model->get($data['site_id']);

			if (
				$this->config->item('site_can_add_site') &&
				(
					(
						$site_info['parent_id'] &&
						$site_info['parent_id'] != $this->config->item('site_id')
					) ||
					$site_info['id'] != $this->config->item('site_id')
				)
			) continue;

			$data['grade_id'] = $this->grade_model->get_all([
				'site_id'	=> (int)$data['site_id'],
				'name'		=> $data['grade'],
			])['rows'][0]['id'] ?? 0;

			if (empty($data['grade_id'])) continue;

			$data['section_id'] = $this->section_model->get_all([
				'grade_id'	=> (int)$data['grade_id'],
				'name'		=> $data['section'],
			])['rows'][0]['id'] ?? 0;

			if (empty($data['section_id'])) continue;

			// 1. Add student
			$explode = explode(' ', trim($data['student_name']), 2);

			if ($student = $this->db->get_where('users', [
				'mobile'		=> $data['mobile'],
				'site_id'		=> (int)$data['site_id'],
			])->row_array()) {
				$student_id = $student['id'];

				$this->db->update('users', [
					'first_name'		=> $explode[0] ?? '',
					'last_name'			=> $explode[1] ?? '',
					'parent_name'		=> $data['parent_name'],
					'mobile'			=> $data['mobile'],
					'email'				=> $data['email'],
					'grade_id'			=> $data['grade_id'],
					'section_id'		=> $data['section_id'],
					'site_id'			=> (int)$data['site_id'],
					'date_modified'		=> strtotime(date('Y-m-d H:i:s')),
				], [
					'id'				=> (int)$student_id
				]);
			} else {
				$this->db->insert('users', [
					'first_name'		=> $explode[0] ?? '',
					'last_name'			=> $explode[1] ?? '',
					'password'			=> md5($data['email']),
					'role_id'			=> 2,
					'parent_name'		=> $data['parent_name'],
					'grade_id'			=> (int)$data['grade_id'],
					'section_id'		=> (int)$data['section_id'],
					'mobile'			=> $data['mobile'],
					'email'				=> $data['email'],
					'status'			=> 1,
					'site_id'			=> (int)$data['site_id'],
					'date_added'		=> strtotime(date('Y-m-d H:i:s')),
				]);

				$student_id = $this->db->insert_id();
			}
		}
	}

	private function _canImport($new = 0) {
		if (
			$this->config->item('site_license_total') >
			($this->site_model->getTotalLicenseUsedBySite($this->config->item('site_id')) + $new)
		) {
			return true;
		}

		return  false;
	}
}
