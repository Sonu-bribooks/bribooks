<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Import {
	public function import_job($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'action',
			'csv',
			'total',
			'counter',
			'skipped',
			'status',
			'date_added',
			'date_modified',
			'actions',
		];

		if ($param1 == 'delete') {
			$this->import_job_model->delete($param2);
			redirect(base_url('admin/import_job'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('import_job');
		$data['action_ajax'] 	= base_url('admin/ajax_import_job');

		$data['actions'] 		= [
			[
				'type'	=> 'confirm',
				'key'	=> 'execute_thread',
				'url'	=> 'admin/execute_thread/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_import_job() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->import_job_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> in_array($result['action'], ['_importSchoolLetterHead', '_importAuthorCalendar', '_importAuthorWall']) && !empty($result['status'])
					? vsprintf('%s <a href="%s" class="btn-popup" data-title="%s">%s</a>', [
						$result['name'],
						base_url('admin/download_attachment/' . $result['id'] . '/' . strtolower(str_replace('_import', '', $result['action']))),
						_l('attachments'),
						_l('download'),
					])
					: $result['name'],
				'action'				=> $result['action'],
				'csv'					=> $result['csv'],
				'total'					=> $result['total'],
				'counter'				=> $result['counter'],
				'skipped'				=> $result['skipped'],
				'status'				=> _sd($result['status']),
				'date_added'			=> format_date($result['date_added']),
				'date_modified'			=> format_date($result['date_modified']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function execute_thread($job_id = 0) {
		if (!empty($job_info = $this->import_job_model->get($job_id))) {
			$this->load->library('parsecsv');
			$this->parsecsv->auto($job_info['csv']);

			$this->import_job_model->edit($job_info['id'], [
				'status'	=> 0,
				'skipped'	=> 0,
				'counter'	=> 0,
			]);

			$data['rows'] 	= $this->parsecsv->data;
			$data['map'] 	= json_decode($job_info['map'], true);
			$data['total'] 	= count($data['rows']);
			$data['action'] = $job_info['action'];
			$data['job_id'] = $job_info['id'];

			$this->import_job_model->generateImportChunk($data);

			redirect('admin/import_job', 'refresh');
		}
	}

	public function download_attachment($job_id = 0, $type = 'schoolletterhead') {
		$hrefs = [];

		$this->load->library('S3_lib', 's3_lib');
		$this->load->library('zip');

		$this->s3_lib->setBucket('bbpdfenginefiles');

		$directory 	= sprintf('%s%s_%s/%s', (ENVIRONMENT === 'production' ? '' : 'test'), $type, date('Y'), $job_id);
		$result 	= $this->s3_lib->listDirObjects($directory);

		foreach ($result['Contents'] ?? [] as $key => $item) {
			if (substr($item['key'], -1) === '/') continue;

			if (!empty($item['Key'])) {
				$hrefs[] = vsprintf('<a href="%s">%s</a>', [
					$this->s3_lib->getUrl($item['Key'], $directory, false, 120),
					$item['Key']
				]);
			}
		}

		echo implode('<br>', $hrefs);
	}

	private function _download_attachment($job_id = 0, $type = 'schoolletterhead') {
		$this->load->library('S3_lib', 's3_lib');
		$this->load->library('zip');

		$this->s3_lib->setBucket('bbpdfenginefiles');

		$directory 	= sprintf('%s%s_%s/%s', (ENVIRONMENT === 'production' ? '' : 'test'), $type, date('Y'), $job_id);
		$result 	= $this->s3_lib->listDirObjects($directory);

		// pr([$directory, $result['Contents']], 1);

		foreach ($result['Contents'] ?? [] as $key => $item) {
			if (substr($item['key'], -1) === '/') continue;

			if (!empty($item['Key'])) {
				$file = $this->s3_lib->get($item['Key'], $directory, false);
				$zip_data = $file['Body'];

				$this->zip->add_data($item['Key'], $zip_data);
			}
		}

		$this->zip->download($directory . '.zip');

		// $this->s3_lib->download(
		// 	'letterhead_' . date('Y') . '.zip',
		// 	(ENVIRONMENT === 'production' ? '' : 'test') . 'letterhead_' . date('Y'),
		// 	false
		// );
	}
}
