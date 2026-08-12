<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BulkDownload {
	public function bulkDownloadBookPdf() {
		$json = [];

		if ($last_download = $this->db->get_where('printer_zip_download', [
			'printer_id'		=> (int)$this->session->userdata('user_id'),
			'date_added > '		=> date('Y-m-d H:i:s', strtotime(vsprintf('+%d %s', [
				ENVIRONMENT === 'production' ? 1 : 5,
				ENVIRONMENT === 'production' ? 'hours' : 'minutes'
			]))),
		])->row_array()) {
			// error
			$json['error'] = sprintf(
				_li('wait for next bulk download till %s'),
				formatDate(date('Y-m-d H:i:s', strtotime(vsprintf('+%d %s', [
					ENVIRONMENT === 'production' ? 1 : 5,
					ENVIRONMENT === 'production' ? 'hours' : 'minutes'
				]), strtotime($last_download['date_added']))))
			);
		} else {
			$tentative_time = (int)(($this->printer_stats_model->printerAssignData([
				'status'			=> 2,
				'assign_printer_id'	=> (int)$this->session->userdata('user_id'),
			])['total'] ?? 0) * 0.5) + 10;

			log_kb(['tentative_time' => $tentative_time]);

			if($tentative_time <= 0) {
				$tentative_time = 15;
			}

			// add scheduler
			$this->printer_zip_download_model->add([
				'printer_id'	=> (int)$this->session->userdata('user_id'),
				'name'			=> 'download_' . date('Y_m_d_H_i_s'),
				'date_tentative'=> date('Y-m-d H:i:s', strtotime("+{$tentative_time} minutes", time())),
			]);

			$json['success'] 		= _li('request_for_bulk_download_added');
			$json['tentative_time'] = _li('Expected_time_for_download ') . formatDate(date('Y-m-d H:i:s', strtotime("+{$tentative_time} minutes", time())));
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function downloadZip($id = 0) {
		if (
			($zip_info = $this->printer_zip_download_model->get($id)) &&
			$zip_info['printer_id'] == $this->session->userdata('user_id')
		) {
			$this->load->library('S3_lib', 's3_lib');

			$this->s3_lib->download(
				basename($zip_info['file']),
				sprintf('%spdfs/bookpdfs_%s', (ENVIRONMENT === 'production' ? '' : 'test'), $zip_info['printer_id']),
				false
			);
		} else {
			$this->session->set_flashdata('error_message', _l('invalid_permission'));
			redirect(site_url('printingPress/in_print_order'), 'refresh');
		}
	}
}
