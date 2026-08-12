<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait ImportAuthorCalendar {
	private function _importAuthorCalendar($rows = [], $map = [], $job_id = 0) {
		$this->load->library('zip');
		$this->load->library('S3_lib', 's3_lib');

		$skipped = $uploaded = 0;
		$headers = [];
		$front_page = $page_1 = $page_2 = $page_3 = $page_4 = $page_5 = $page_6 = null;
		$start	 = $end = null;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if (empty($headers)) {
				$headers = array_map('trim', array_keys($row ?? []));
			}

			if (!isset($start)) {
				$start = $index;
			}

			$end = $index;

			if (!empty($data['front_page'])) {
				$front_page = $data['front_page'];
			} else {
				if (!empty($front_page)) {
					$data['front_page'] = $front_page;
				}
			}

			if (!empty($data['page_1'])) {
				$page_1 = $data['page_1'];
			} else {
				if (!empty($page_1)) {
					$data['page_1'] = $page_1;
				}
			}

			if (!empty($data['page_2'])) {
				$page_2 = $data['page_2'];
			} else {
				if (!empty($page_2)) {
					$data['page_2'] = $page_2;
				}
			}

			if (!empty($data['page_3'])) {
				$page_3 = $data['page_3'];
			} else {
				if (!empty($page_3)) {
					$data['page_3'] = $page_3;
				}
			}

			if (!empty($data['page_4'])) {
				$page_4 = $data['page_4'];
			} else {
				if (!empty($page_4)) {
					$data['page_4'] = $page_4;
				}
			}

			if (!empty($data['page_5'])) {
				$page_5 = $data['page_5'];
			} else {
				if (!empty($page_5)) {
					$data['page_5'] = $page_5;
				}
			}

			if (!empty($data['page_6'])) {
				$page_6 = $data['page_6'];
			} else {
				if (!empty($page_6)) {
					$data['page_6'] = $page_6;
				}
			}

			self::_updateCounter($job_id);

			if (empty($data['user_id'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($user_info = $this->user_model->get($data['user_id']))) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['book_id'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($book_info = $this->book_model->get($data['book_id']))) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['front_page'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['page_1'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['page_2'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['page_3'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['page_4'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['page_5'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['page_6'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			self::_generateAuthorCalendar(array_merge($row, $data), $index);

			$uploaded++;

			// break;
		}

		$this->s3_lib->setBucket('bbpdfenginefiles');
		$zip_data = $this->zip->get_zip();

		if (!empty($zip_data)) {
			$s3_filename = $this->s3_lib->putData(
				sprintf('authorcalendar_%s_%s.zip', $start, $end),
				sprintf('%sauthorcalendar_%s/%s', (ENVIRONMENT === 'production' ? '' : 'test'), date('Y'), $job_id),
				$zip_data,
				false
			);
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	public function _generateAuthorCalendar($data = [], $index = 0) {
		$data['width'] 	= (5.5 + 0.19685) * 72;
		$data['height'] = (9 + 0.19685) * 72;
		$html 			= $this->load->view(sprintf('common/author_calendar/index'), $data, true);

		// echo $html; die;

		$dompdf = new Dompdf([
			// 'debugLayout' 	=> true,
		]);
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper(
			[
				0,
				0,
				$data['width'],
				$data['height']
			],
			'portrait'
		);

		$dompdf->render();
		$pdf_data = $dompdf->output();

		$filename = vsprintf('author_calendar_%s_%s.pdf', [
			date('Y'),
			$data['book_id'],
		]);

		$this->zip->add_data($filename, $pdf_data);
	}
}
