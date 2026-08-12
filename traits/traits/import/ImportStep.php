<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportStep {
	public function type() {
		$data = [];

		if ($this->input->method() == 'post' && $this->input->post('type') && in_array($this->input->post('type'), $this->types)) {
			$this->session->set_userdata('type', $this->input->post('type'));

			$data['success']			= _l('successfully updated type');
			$data['next']				= _l('successfully updated type');
		} else {
			$data['error'] 				= _l('invalid_type');
		}

		output_json($data);
	}

	public function upload() {
		ini_set('upload_max_filesize', '20M');
		ini_set('post_max_size', '20M');
		ini_set('memory_limit', -1);
		ini_set('max_execution_time', 0);
		set_time_limit(0);

		$data['headers'] = [];

		if (!is_dir('uploads/csv/tempimport53dddfdf')) {
			mkdir('uploads/csv/tempimport53dddfdf', 0777, TRUE);
		}

		$config = [
			'upload_path' 	=> 'uploads/csv/tempimport53dddfdf',
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
				$data['columns'] 		= $this->columns[$this->session->userdata('type')];
				$data['success'] 		= _l('upload_success');
			} else {
				$data['error'] 			= _l('error_unknown');
			}
		} else {
			$data['error'] 				= $this->upload->display_errors();
		}

		output_json($data);
	}

	public function save() {
		ini_set('memory_limit', -1);
		ini_set('max_execution_time', 0);
		set_time_limit(0);

		$json = [];

		if ($this->input->method() == 'post' && $this->input->post('mapping') && $this->input->post('csv_file') && $this->input->post('type')) {
			$csv = 'uploads/csv/tempimport53dddfdf/' . $this->input->post('csv_file');
			$this->load->library('parsecsv');
			$this->parsecsv->auto($csv);

			$rows 	= $this->parsecsv->data;
			$map 	= $this->input->post('mapping');

			$method = str_replace('_', ' ', $this->input->post('type'));
			$method = '_import' . ucwords($method);
			$method = str_replace(' ', '', $method);

			if (method_exists($this, $method)) {
				self::_generateImportJob([
					'rows' 		=> $rows,
					'map' 		=> $map,
					'action'	=> $method,
					'csv'		=> $csv,
					'name'		=> str_replace('_', ' ', $this->input->post('type')),
					'total'		=> count($rows),
				]);
			} else {
				$json['error'] = _l('nothing_import');
			}

			$json['finish'] 	= true;
			$json['success'] 	= _l('text_save_success');

			if (!empty($json['status']['skipped'])) {
				$json['error'] = sprintf(_l('skipped %s %s due to duplicate entry'), $json['status']['skipped'], $this->input->post('type'));
			}

			if (!empty($json['status']['uploaded'])) {
				$json['success'] = sprintf(_l('uploaded %s %s successfully'), $json['status']['uploaded'], $this->input->post('type'));
			}

		} else {
			$json['error'] 		= _l('error_unknown');
		}

		output_json($json);
	}

	public function saveOld() {
		ini_set('memory_limit', -1);
		ini_set('max_execution_time', 0);
		set_time_limit(0);

		$json = [];

		if ($this->input->method() == 'post' && $this->input->post('mapping') && $this->input->post('csv_file') && $this->input->post('type')) {
			$this->load->library('parsecsv');
			$this->parsecsv->auto('uploads/csv/tempimport53dddfdf/' . $this->input->post('csv_file'));

			$rows 	= $this->parsecsv->data;
			$map 	= $this->input->post('mapping');

			$method = str_replace('_', ' ', $this->input->post('type'));
			$method = '_import' . ucwords($method);


			if (method_exists($this, $method)) {
				self::{$method}($rows, $map);
			} else {
				$json['error'] = _l('nothing_import');
			}

			$json['finish'] 	= true;
			$json['success'] 	= _l('text_save_success');

			if (!empty($json['status']['skipped'])) {
				$json['error'] = sprintf(_l('skipped %s %s due to duplicate entry'), $json['status']['skipped'], $this->input->post('type'));
			}

			if (!empty($json['status']['uploaded'])) {
				$json['success'] = sprintf(_l('uploaded %s %s successfully'), $json['status']['uploaded'], $this->input->post('type'));
			}

		} else {
			$json['error'] 		= _l('error_unknown');
		}

		output_json($json);
	}
}
