<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('import_download')) {
	function import_download($type = '', $headers = [], $default_values = []) {
		if (empty($type) || empty($headers) || empty($default_values)) return;

		$filename = sprintf('sample_%s_%s.csv', $type, date('Y_m_d_H_i_s'));

		if (!headers_sent()) {
			header('Content-Type: text/csv; charset=UTF-8');
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			header('Pragma: no-cache');
			header('Expires: 0');

			if (ob_get_level()) ob_end_clean();
		} else {
			exit('Error: Headers already sent out!');
		}

		$fp = fopen('php://output', 'w');

		fputs($fp, "\xEF\xBB\xBF");

		fputcsv($fp, $headers);

		foreach ($default_values as $row) {
			$line = [];
			foreach ($headers as $h) {
				$line[] = isset($row[$h]) ? $row[$h] : '';
			}
			fputcsv($fp, $line);
		}

		fclose($fp);
		exit;
	}
}

if (!function_exists('import_upload')) {
	function import_upload($type = '', $columns = '') {
		$CI	=&	get_instance();

		ini_set('upload_max_filesize', '20M');
		ini_set('post_max_size', '20M');
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

		$CI->load->library('upload', $config);

		if ($CI->upload->do_upload('file')) {
			$upload = $CI->upload->data();

			$CI->load->library('parsecsv');

			$CI->parsecsv->auto($config['upload_path'] . '/' . $upload['file_name']);

			$rows = $CI->parsecsv->data;

			$data['csv_file'] 	= $upload['file_name'];
			$data['type'] 		= $type;

			if ($rows) {
				$data['headers'] 		= array_map('trim', array_keys($rows[0]));
				$data['columns'] 		= $columns;
				$data['success'] 		= _l('upload_success');
			} else {
				$data['error'] 			= _l('error_unknown');
			}
		} else {
			$data['error'] 				= $CI->upload->display_errors();
		}

		return $data;
	}
}

if (!function_exists('import_save')) {
	function import_save($type='', $extra = []) {
		$CI	=&	get_instance();

		$CI->load->model('common/ImportJob_model', 'import_job_model');

		ini_set('memory_limit', -1);
		ini_set('max_execution_time', 0);
		set_time_limit(0);

		$result = [];

		if ($CI->input->method() == 'post' && $CI->input->post('mapping') && $CI->input->post('csv_file')) {
			$csv = 'uploads/csv/tempimport53dddfdf/' . $CI->input->post('csv_file');
			$CI->load->library('parsecsv');
			$CI->parsecsv->auto($csv);

			$rows 	= $CI->parsecsv->data;
			$map 	= $CI->input->post('mapping');

			$job_id = $CI->import_job_model->add([
				'name'		=> str_replace('_', ' ', $type),
				'csv'		=> $csv,
				'action'	=> '_importGenericData',
				'map' 		=> json_encode($map ?? []),
				'extra' 	=> json_encode($extra ?? []),
				'total'		=> count($rows),
			]);

			$result = [
				'rows'		=> $rows,
				'name'		=> str_replace('_', ' ', $type),
				'csv'		=> $csv,
				'action'	=> '_importGenericData',
				'map' 		=> $map,
				'extra' 	=> json_encode($extra ?? []),
				'total'		=> count($rows),
				'job_id'	=> $job_id,
			];
		}

		return $result;
	}
}
