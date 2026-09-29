<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait ImportDiwaliGiftBook {
	private function _importDiwaliGiftBook($rows = [], $map = [], $job_id = 0) {
		$this->load->library('zip');
		$this->load->library('S3_lib', 's3_lib');

		$skipped = $uploaded = 0;
        $start = $end = null;
        
		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

            if (!isset($start)) {
				$start = $index;
			}

			$end = $index;
			self::_updateCounter($job_id);

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

			if (empty($data['quantity'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

            if (empty($data['front_page'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

            if (empty($data['back_page'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

            $data['book_name'] 		= ucwords($book_info['name']);
			$data['author_name'] 	= ucwords($book_info['author_name']);
			$data['sku'] 			= $book_info['id'];
            $data['front_image']    = $data['front_page'] ?? '';
            $data['back_image']     = $data['back_page'] ?? '';

            $quantity = (int) $data['quantity'];
            for ($i = 1; $i <= $quantity; $i++) {
                $data['gift_index'] = $i; 
                self::_generateDiwaliGift($data);
            }

			$uploaded++;

			// break;
		}

		// $this->s3_lib->setBucket('bbpdfenginefiles');
		// $zip_data = $this->zip->get_zip();

		// if (!empty($zip_data)) {
		// 	$s3_filename = $this->s3_lib->putData(
		// 		sprintf('giftcard_%s_%s.zip', $start, $end),
		// 		sprintf('%sgiftcard_%s/%s', (ENVIRONMENT === 'production' ? '' : 'test'), date('Y'), $job_id),
		// 		$zip_data,
		// 		false
		// 	);
		// }

        $zip_data = $this->zip->get_zip();

        if (!empty($zip_data)) {
            // Folder path define karein jahan zip save karni hai (e.g., project ke root ya public folder me)
            $local_folder = FCPATH . 'uploads/gift_cards/'; 
            
            // Agar folder nahi hai toh auto-create kar dega
            if (!is_dir($local_folder)) {
                mkdir($local_folder, 0777, true);
            }

            $local_filename = sprintf('diwaligiftbook_%s_%s.zip', $start, $end);
            $full_path = $local_folder . $local_filename;

            // ZIP file ko local disk par write karein
            file_put_contents($full_path, $zip_data);
            
            log_kb(['LOCAL_ZIP_SAVED' => $full_path]);
        }

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	public function _generateDiwaliGift($data = []) {
        $data['width'] 	=  432;
		$data['height'] = 648;
		log_kb([
            'IMPORT_DIWALI_GIFT_BOOK' => ['data' => $data]
        ]);
					
		$html 			= $this->load->view('common/gift_card', $data, true);

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

		$filename = vsprintf('gift_card_%s_%s_%s.pdf', [
			date('Y'),
			$data['book_id'],
            $data['gift_index'] ?? 1,
		]);

		$this->zip->add_data($filename, $pdf_data);
	}
}
