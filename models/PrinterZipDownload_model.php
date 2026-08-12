<?php defined('BASEPATH') OR exit('No direct script access allowed');

load_trait('common');

class PrinterZipDownload_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->printer_path = PRINTER_PDF_DIR . 'bookpdfs_';
	}

	use BookPrintCustom, BookPrintGrey;

	public function get($printer_zip_download_id = 0) {
		$this->db->select('printer_zip_download.*,');

		$this->db->where('printer_zip_download.id', (int)$printer_zip_download_id);
		$this->db->where('printer_zip_download._deleted', 0);

		return $this->db->get('printer_zip_download')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('printer_zip_download.*,');

		if (isset($data['printer_id'])) {
			$this->db->where('printer_zip_download.printer_id', (int)$data['printer_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('printer_zip_download.status', (int)$data['status']);
		}

		if (!empty($data['search'])) {
			$this->db->like('printer_zip_download.file', $data['search'], 'after');
			$this->db->or_like('printer_zip_download.name', $data['search'], 'after');
		}

		$this->db->where('printer_zip_download._deleted', 0);

		$this->db->from('printer_zip_download');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'printer_zip_download.name',
			'printer_zip_download.status',
			'printer_zip_download.date_added',
			'printer_zip_download.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'printer_zip_download.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('printer_zip_download', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$printer_zip_download_id = $this->db->insert_id();

		self::_scheduleDownload($printer_zip_download_id, 'add');

		return $printer_zip_download_id;
	}

	public function edit($printer_zip_download_id = 0, $data = []) {
		$this->db->where('id', (int)$printer_zip_download_id);
		$this->db->update('printer_zip_download', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($printer_zip_download_id = 0) {
		$this->db->where('id', (int)$printer_zip_download_id);
		$this->db->update('printer_zip_download',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->gdb->where('id', (int)$id);
			$this->gdb->update('printer_zip_download', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}
	}

	private function _scheduleDownload($printer_zip_download_id = 0, $type = 'add') {
		if ($info = self::get($printer_zip_download_id)) {
			if ($type == 'add') {
				$this->cron_model->add([
					'code'			=> 'printerZipDownload_' . $printer_zip_download_id,
					'action'		=> 'printer_zip_download_model->downloadZipCron',
					'data'			=> [$printer_zip_download_id],
					'site_id'		=> 1,
					'status'		=> 0,
					'alert_date'	=> date('Y-m-d H:i:s'),
				]);
			} else {
				$this->cron_model->editByCode('printerZipDownload_' . $printer_zip_download_id, [
					'status'		=> 1 ^ $info['status'],
					'alert_date'	=> date('Y-m-d H:i:s'),
				]);
			}
		}
	}

	public function downloadZipCron($id = 0) {
		if ($info = self::get($id)) {
			$this->load->model('printer/PrinterStats_model', 'printer_stats_model');
			$this->load->model('user/User_model', 'user_model');
			$this->load->model('dropshipper/DropshipperOrder_model', 'dropshipper_order_model');
			$this->load->model('book/Book_model', 'book_model');
			$this->load->model('book/BookVersion_model', 'book_version_model');
			$this->load->model('book/PageVersion_model', 'page_version_model');
			$this->load->model('design/Cover_model', 'cover_model');

			$this->printer_path .= (int)$info['printer_id'];

			if (!is_dir($this->printer_path)) {
				mkdir($this->printer_path, 0777, TRUE);
				chmod($this->printer_path, 0777);
				@touch($this->printer_path . '/' . 'index.html');
			}

			self::_genBookZipPdfs($info);
		}
	}

	private function _genBookZipPdfs($info = []) {
		$this->load->library('zip');
		$this->load->library('S3_lib', 's3_lib');

		$printer_info = $this->user_model->get($info['printer_id']);

		if ($printer_info['role_id'] == _dropshipper_role()) {
			$products = $this->dropshipper_order_model->printerAssignData([
				'status'			=> 2,
				'assign_printer_id'	=> (int)$info['printer_id'],
			])['rows'] ?? [];
		} else {
			$products = $this->printer_stats_model->printerAssignData([
				'status'			=> 2,
				'assign_printer_id'	=> (int)$info['printer_id'],
			])['rows'] ?? [];
		}

		log_kb([
			'printer_path'	=> $this->printer_path,
		]);

		$exclude = [];

		foreach ($products as $key => $product) {
			$this->s3_lib->setBucket('bbpdfenginefiles');

			$book_info = $this->book_version_model->getByVersion(
				$product['product_id'],
				$product['version']
			);

			if (empty($book_info)) continue;

			$option = json_decode($product['option'], true);

			$option['name'] = str_replace(' ', '', $option['name']);

			// Add Cover
			$filename = vsprintf('%s-v%s-%s', [
				$book_info['slug'],
				$book_info['version'],
				$option['name'],
			]);

			if (in_array($filename, $exclude)) continue;

			$exclude[] = $filename;

			$dirname = vsprintf('%s/%s/%s', [
				'bookpdfs_' . (int)$info['printer_id'],
				$option['name'],
				$filename,
			]);
			$s3_dirname = vsprintf('%s/%s', [
				(ENVIRONMENT === 'production' ? '' : 'test') . 'bookpdfs',
				$filename,
			]);

			$this->s3_lib->setBucket('bbpdfenginefiles');

			$cover_file = sprintf('cover-%s.pdf', $filename);
			$mrp 		= false;

			if ($product['currency_code'] === 'INR') {
				$mrp = true;
				$cover_file = sprintf('cover-mrp-%s.pdf', $filename);
				$this->load->model('common/Site_model', 'site_model');
				$this->site_model->initConfig(1);
			}

			// upload cover to s3
			if (!$this->s3_lib->doesExist($cover_file, $s3_dirname, false)) {
				$pdf_data = mb_strtolower($option['name']) === 'blackwhite' ? self::printGreyCover(
					$book_info['book_id'],
					$book_info['version'],
					false,
					false,
					$mrp
				) : self::printCover(
					$book_info['book_id'],
					$book_info['version'],
					false,
					false,
					$mrp
				);

				$this->s3_lib->putData(
					$cover_file,
					$s3_dirname,
					$pdf_data,
					false
				);
			} else {
				$file = $this->s3_lib->get($cover_file, $s3_dirname, false);
				$pdf_data = $file['Body'];

				$book_modified = $book_info['date_modified'] > date('Y-m-d H:i:s', strtotime((string)$file['LastModified']));

				if (
					date('Y-m-d H:i:s', strtotime((string)$file['LastModified'])) > '2023-12-15 00:00:00' &&
					date('Y-m-d H:i:s', strtotime((string)$file['LastModified'])) < '2024-01-12 18:00:00'
				) {
					$book_modified = true;
				}

				if (
					$book_info['date_published'] > '2023-04-01 00:00:00' &&
					date('Y-m-d H:i:s', strtotime((string)$file['LastModified'])) < '2023-04-24 13:00:00'
				) {
					$book_modified = true;
				}

				if ($book_modified) {
					$pdf_data = mb_strtolower($option['name']) === 'blackwhite' ? self::printGreyCover(
						$book_info['book_id'],
						$book_info['version'],
						false,
						false,
						$mrp
					) : self::printCover(
						$book_info['book_id'],
						$book_info['version'],
						false,
						false,
						$mrp
					);

					$this->s3_lib->putData(
						$cover_file,
						$s3_dirname,
						$pdf_data,
						false
					);
				}
			}

			$cover_zip_file = vsprintf('%s/cover-%s.pdf', [
				$dirname,
				$filename,
			]);

			if ($mrp) {
				$cover_zip_file = vsprintf('%s/cover-mrp-%s.pdf', [
					$dirname,
					$filename,
				]);
			}

			$this->zip->add_data($cover_zip_file, $pdf_data);

			log_kb([
				'PrinterZipDownload::cover' => [$key, $info['printer_id'], $book_modified ?? false]
			]);

			// Add pages
			// upload pages to s3
			$this->s3_lib->setBucket('bbpdfenginefiles');
			if (!$this->s3_lib->doesExist(sprintf('pages-%s.pdf', $filename), $s3_dirname, false)) {
				$pdf_data = mb_strtolower($option['name']) === 'blackwhite' ? self::printGreyBookPages(
					$book_info['book_id'],
					$book_info['version'],
					false,
					false
				) : self::printBookPages(
					$book_info['book_id'],
					$book_info['version'],
					false,
					false
				);

				$this->s3_lib->putData(
					sprintf('pages-%s.pdf', $filename),
					$s3_dirname,
					$pdf_data,
					false
				);
			} else {
				$file = $this->s3_lib->get(sprintf('pages-%s.pdf', $filename), $s3_dirname, false);
				$pdf_data = $file['Body'];

				$book_modified = $book_info['date_modified'] > date('Y-m-d H:i:s', strtotime((string)$file['LastModified']));

				if (
					date('Y-m-d H:i:s', strtotime((string)$file['LastModified'])) > '2023-03-14 00:00:00' &&
					date('Y-m-d H:i:s', strtotime((string)$file['LastModified'])) < '2023-03-20 12:00:00'
				) {
					$book_modified = true;
				}

				if (
					$book_info['date_published'] > '2023-04-01 00:00:00' &&
					date('Y-m-d H:i:s', strtotime((string)$file['LastModified'])) < '2023-04-24 13:00:00'
				) {
					$book_modified = true;
				}

				if ($book_modified) {
					$pdf_data = mb_strtolower($option['name']) === 'blackwhite' ? self::printGreyBookPages(
						$book_info['book_id'],
						$book_info['version'],
						false,
						false
					) : self::printBookPages(
						$book_info['book_id'],
						$book_info['version'],
						false,
						false
					);

					$this->s3_lib->putData(
						sprintf('pages-%s.pdf', $filename),
						$s3_dirname,
						$pdf_data,
						false
					);
				}
			}

			log_kb([
				'PrinterZipDownload::pages' => [$key, $info['printer_id'], $book_modified ?? false]
			]);

			$this->zip->add_data(vsprintf('%s/pages-%s.pdf', [
				$dirname,
				$filename
			]), $pdf_data);

			sleep(0.2);
		}

		$csv_file = self::_genBookZipPdfsCsv($products, vsprintf('%s/metadata_', [
			$this->printer_path,
		]));

		log_kb(['csv_file' => $csv_file]);

		$csv_data = file_get_contents($csv_file);

		// $this->zip->read_file($csv_file);
		$this->zip->add_data(vsprintf('%s/metadata_%s.csv', [
			'bookpdfs_' . (int)$info['printer_id'],
			date('Y_m_d_H_i_s')
		]), $csv_data);

		// $this->zip->archive($this->printer_path . '.zip');

		$this->s3_lib->setBucket('bbpdfenginefiles');
		$s3_filename = $this->s3_lib->putData(
			$this->printer_path . '.zip',
			(ENVIRONMENT === 'production' ? '' : 'test') . 'pdfs/bookpdfs_' . (int)$info['printer_id'],
			$this->zip->get_zip()
		);

		// update file info in the download history
		self::edit($info['id'], [
			'file'		=> $s3_filename,
			'status'	=> 1,
		]);

		// is_file($csv_file) && unlink($csv_file);
		$this->load->model('Alert_model', 'alert_model');
		$this->alert_model->zipReadyToDownloadAlert($info['printer_id'], str_replace(FCPATH, '', $csv_file));
	}

	private function _genBookZipPdfsCsv($products = [], $local_filename = '') {
		$orders = $sort_order = [];

		foreach ($products as $product) {
			$book_info = $this->book_version_model->getByVersion($product['product_id'], $product['version']);

			$total_pages 	= $this->page_version_model->get_all([
				'book_id'	=> $product['product_id'],
				'version'	=> $product['version'],
			])['total'] ?? 0;

			if (empty($book_info)) continue;

			$option = json_decode($product['option'], true);
			$option['name'] = str_replace(' ', '', $option['name']);

			$filename = vsprintf('%s-v%s-%s', [
				$book_info['slug'],
				$book_info['version'],
				$option['name'],
			]);

			if (isset($orders[$filename])) {
				$orders[$filename]['quantity'] += $product['quantity'];
			} else {
				$book_info['unique_id'] = '1' . sprintf('%03d', $book_info['version']) . sprintf('%09d', $book_info['book_id']);

				$latest_book_info = $this->book_model->get($book_info['book_id']);

				if ($latest_book_info['version'] != $book_info['version']) {
					$book_info['isbn'] = '';
				}

				if (!empty($book_info['isbn'])) {
					$book_info['isbn'] = str_replace('-', '', preg_replace('/\s+/', '', $book_info['isbn']));
				}

				$orders[$filename] = [
					'assignment_code'	=> $product['assignment_code'],
					'folder'			=> $filename,
					'sku'				=> _o_b_code($book_info['book_id'], $book_info['version'], $option['name']),
					'ISBN/SN'			=> !empty($book_info['isbn']) ? $book_info['isbn'] : $book_info['unique_id'],
					'book_name'			=> $book_info['name'],
					'author_name'		=> $book_info['author_name'],
					'version'			=> $book_info['version'],
					'option'			=> $option['name'],
					'quantity'			=> $product['quantity'],
					'total_pages'		=> $total_pages * 2 + 1,
					'assigned_date'		=> date('M j, Y', strtotime($product['date_added'])),
					'order_ids'			=> $product['order_ids'],
					'printer_comment'	=> '',
					'BriBooks_comment'	=> '',
				];

				$sort_order[] = $filename;
			}
		}

		array_multisort($sort_order, $orders);

		return self::_downloadCsv(array_values($orders), $local_filename);
	}

	private function _downloadCsv($results = [], $filename = 'download') {
		$filename = $filename . date('Y_m_d_H_i_s') . '.csv';

		$headers = isset($results[0]) ? array_keys($results[0]) : [];

		$fp = fopen($filename, 'w');

		self::_writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		return $filename;
	}

	private function _writeRowToCsv($results = [], $fp = null, $headers = []) {
		fputs($fp, "\xEF\xBB\xBF");

		fputcsv($fp, $headers);

		if (is_array($results) && $results && is_resource($fp) && is_array($headers) && $headers) {
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
}
