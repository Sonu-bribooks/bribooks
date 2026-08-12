<?php defined('BASEPATH') OR exit('No direct script access allowed');

load_trait('common');

class MissingCover_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->printer_path = PRINTER_PDF_DIR . 'bookpdfs_';
	}

	use BookPrintCustom;

	public function downloadZipCron($id = 0) {
		$this->load->model('printer/PrinterAssignment_model', 'printer_assignment_model');
		$this->load->model('printer/PrinterStats_model', 'printer_stats_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('book/PageVersion_model', 'page_version_model');
		$this->load->model('design/Cover_model', 'cover_model');

		$info = $this->printer_assignment_model->get($id);

		$this->printer_path .= $info['printer_id'];

		if (!is_dir($this->printer_path)) {
			mkdir($this->printer_path, 0777, TRUE);
			chmod($this->printer_path, 0777);
			@touch($this->printer_path . '/' . 'index.html');
		}

		self::_genBookZipPdfs($info);
	}

	private function _genBookZipPdfs($info = []) {
		$this->load->library('zip');
		$this->load->library('S3_lib', 's3_lib');

		$products = $this->printer_stats_model->printerAssignData([
			'assignment_id'		=> $info['id'],
		])['rows'] ?? [];

		log_kb([
			'printer_path'	=> $this->printer_path,
		]);

		$exclude = [];

		foreach ($products as $product) {
			$book_info = $this->book_version_model->getByVersion(
				$product['product_id'],
				$product['version']
			);

			if (empty($book_info)) continue;

			$option = json_decode($product['option'], true);

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

			// upload cover to s3
			if (!$this->s3_lib->doesExist(sprintf('cover-%s.pdf', $filename), $s3_dirname, false)) {
				$pdf_data = self::printCover(
					$book_info['book_id'],
					$book_info['version'],
					false,
					false
				);

				$this->s3_lib->putData(
					sprintf('cover-%s.pdf', $filename),
					$s3_dirname,
					$pdf_data,
					false
				);
			} else {
				$obj = $this->s3_lib->get(sprintf('cover-%s.pdf', $filename), $s3_dirname, false);
				$pdf_data = $obj['Body'];

				$book_modified = date('Y-m-d', strtotime($product['date_in_print'])) == date('Y-m-d', strtotime((string)$file['LastModified']));

				if ($book_modified) {
					log_kb(['Modified:: ' => sprintf('cover-%s.pdf', $filename)]);

					$pdf_data = self::printCover(
						$book_info['book_id'],
						$book_info['version'],
						false,
						false
					);

					$this->s3_lib->putData(
						sprintf('cover-%s.pdf', $filename),
						$s3_dirname,
						$pdf_data,
						false
					);
				}
			}

			$this->zip->add_data(vsprintf('%s/cover-%s.pdf', [
				$dirname,
				$filename,
			]), $pdf_data);

			// Add pages
			if (0) {
				// upload pages to s3
				if (!$this->s3_lib->doesExist(sprintf('pages-%s.pdf', $filename), $s3_dirname, false)) {
					$pdf_data = self::printBookPages(
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
					$obj = $this->s3_lib->get(sprintf('pages-%s.pdf', $filename), $s3_dirname, false);
					$pdf_data = $obj['Body'];

					$book_modified = $book_info['date_modified'] > date('Y-m-d H:i:s', strtotime((string)$file['LastModified']));

					if ($book_modified) {
						$pdf_data = self::printBookPages(
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

				$this->zip->add_data(vsprintf('%s/pages-%s.pdf', [
					$dirname,
					$filename
				]), $pdf_data);
			}

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

		$s3_filename = $this->s3_lib->putData(
			$this->printer_path . '.zip',
			(ENVIRONMENT === 'production' ? '' : 'test') . 'pdfs/missingbookpdfs_' . (int)$info['printer_id'],
			$this->zip->get_zip()
		);

		// is_file($csv_file) && unlink($csv_file);
		$this->load->model('Alert_model', 'alert_model');
		$this->alert_model->email(
			'abhishek@youbooks.co',
			_l('file_ready_to_download'),
			_l('your_files_are_ready_to_download'),
			[],
			[]
		);
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

				if(!empty($book_info['isbn'])) {
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
