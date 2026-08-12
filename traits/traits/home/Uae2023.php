<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

load_trait('whatsapp');

trait Uae2023
{
	use CommonWhatsapp;

	public function uaeSchoolPDF() {
		return;

		$dir = FCPATH . 'uploads/uae/school_nominations/pdf/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$dir = FCPATH . 'uploads/uae/school_nominations/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		if (!empty($results = $this->db->get_where('school_nominations', ['status' => 0])->result_array())) {
			list($image_width, $image_height) = getimagesize(FCPATH . 'assets/images/UAE-School-Nominations-Letter-Head.jpg');
			$font_path = FCPATH . 'assets/global/fonts/MYRIADPRO-BOLD.OTF';
			$font_path_regular = FCPATH . 'assets/global/fonts/MYRIADPRO-REGULAR.OTF';

			// pr($results, 1);

			foreach ($results as $key => $result) {
				$str1 = $str2 = $str3 = '';
				$str4 = $str5 = $str6 = '';
				$str7 = $str8 = $str9 = '';

				$image_name = $result['nomination_code'] . '.jpeg';

				$p = 'Congratulations! ' . ucwords(trim($result['school_name'])) . ' has been selected to participate in the prestigious National Young Authors Fair, UAE - the Largest Online Book Writing Competition in the GCC region.';

				$school_arr = explode(" ", $p);
				foreach ($school_arr as $school) {
					if(empty(strlen($str2)) && empty(strlen($str3)) && (strlen($str1) + strlen($school)) < 100) {
						$str1 .= ' ' . $school;
					} else if(empty(strlen($str3)) && (strlen($str2) + strlen($school)) < 100) {
						$str2 .= ' ' . $school;
					} else if((strlen($str3) + strlen($school)) < 100) {
						$str3 .= ' ' . $school;
					}
				}

				$p1 = 'Under your exceptional leadership, ' . ucwords(trim($result['school_name'])) . ' and its students have the incredible opportunity to be part of this historic event, where they can win awards and gain global recognition.';

				$school_arr = explode(" ", $p1);
				foreach ($school_arr as $school) {
					if(empty(strlen($str5)) && empty(strlen($str6)) && (strlen($str4) + strlen($school)) < 100) {
						$str4 .= ' ' . $school;
					} else if(empty(strlen($str6)) && (strlen($str5) + strlen($school)) < 100) {
						$str5 .= ' ' . $school;
					} else if((strlen($str6) + strlen($school)) < 100) {
						$str6 .= ' ' . $school;
					}
				}

				$p2 = 'We look forward to your participation and the success of ' . ucwords(trim($result['school_name'])) . ' in this exciting event.';

				$school_arr = explode(" ", $p2);
				foreach ($school_arr as $school) {
					if(empty(strlen($str8)) && empty(strlen($str9)) && (strlen($str7) + strlen($school)) < 100) {
						$str7 .= ' ' . $school;
					} else if(empty(strlen($str9)) && (strlen($str8) + strlen($school)) < 100) {
						$str8 .= ' ' . $school;
					} else if((strlen($str9) + strlen($school)) < 100) {
						$str9 .= ' ' . $school;
					}
				}

				/*$sn_length = strlen($result['school_name']);
				$p_length = strlen($p);*/

				$image 		= imagecreatefromjpeg(FCPATH . 'assets/images/UAE-School-Nominations-Letter-Head.jpg');
				$darkgrey 	= imagecolorallocate($image, 16, 40, 75);
				$grey 		= imagecolorallocate($image, 110, 110, 110);

				// imagettftext($image, 36, 0, 1275, 258, $darkgrey, $font_path, $result['nomination_code']);
				imagettftext($image, 38, 0, 108, 445, $darkgrey, $font_path_regular, $str1);
				imagettftext($image, 38, 0, 108, 515, $darkgrey, $font_path_regular, $str2);

				if($str3) {
					imagettftext($image, 38, 0, 108, 580, $darkgrey, $font_path_regular, $str3);
				}

				imagettftext($image, 38, 0, 108, 825, $darkgrey, $font_path_regular, $str4);
				imagettftext($image, 38, 0, 108, 895, $darkgrey, $font_path_regular, $str5);

				if($str6) {
					imagettftext($image, 38, 0, 108, 960, $darkgrey, $font_path_regular, $str6);
				}

				imagettftext($image, 38, 0, 108, 2545, $darkgrey, $font_path_regular, $str7);
				imagettftext($image, 38, 0, 108, 2615, $darkgrey, $font_path_regular, $str8);

				if($str9) {
					imagettftext($image, 38, 0, 108, 2680, $darkgrey, $font_path_regular, $str9);
				}


				imagejpeg($image, $dir . '/' . $image_name);
				imagedestroy($image);

				// pr($image, 1);
				self::_generateUaeCertificate($image_name);

				$this->db->where('id', (int)$result['id']);
				$this->db->update('school_nominations', [
					'status'		=> 1,
					'date_added'	=> date('Y-m-d H:i:s'),
				]);
			}

			/*return;*/
		}
	}

	private function _generateUaeCertificate($file = '') {
		if(empty($file))
			return;

		$html = '<style>@page{margin:0;padding:0;}</style><img
			src="' . site_url('uploads/uae/school_nominations/') . $file . '"
			style="width:100%;max-height:100%;"
		/>';

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'potrait');

		// Render the HTML as PDF
		$dompdf->render();

		$path_info = pathinfo($file);

		$dir = FCPATH . 'uploads/uae/school_nominations/pdf/';

		file_put_contents(
			$dir . $path_info['filename'] . '.pdf',
			$dompdf->output()
		);
	}

	public function uaeCertificates() {
		return;

		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('event/EventUser_model', 'event_user_model');

		$order_products = $this->order_product_model->getBookIdsByEventIdNotInCertificates(UAE_EVENT_ID);

		if(!empty($order_products)) {
			// pr(count($order_products));
			// pr($order_products, 1);

			$chunk_size = (ENVIRONMENT === 'production') ? 1 : 10;

			$data = [];
			$count_certificate = 0;
			$count_medallion = 0;

			foreach(array_chunk($order_products, $chunk_size) as $order_product) {
				foreach ($order_product as $book_info) {
					$event_user_info = $this->event_user_model->getEventUserByUserId(UAE_EVENT_ID, $book_info['user_id']);
					$certificate_info = []; // $this->certificate_model->getByUserId($book_info['user_id'], $book_info['book_id']);

					$data[$book_info['book_id']]['book_info'] = $book_info;
					$data[$book_info['book_id']]['event_user_info'] = $event_user_info;
					$data[$book_info['book_id']]['certificate_info'] = $certificate_info;

					if(empty($certificate_info)) {
						if(empty($cron_info = $this->cron_model->getByCode('genericMsgUAEBookSoldCron_' . $book_info['book_id']))) {
							$this->cron_model->add([
								'code'			=> 'createCertificateUAECron' . $book_info['order_id'],
								'action'		=> 'alert_model->createCertificateUAECron',
								'data'			=> [$book_info['order_id']],
								'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
							]);
							$this->cron_model->add([
								'code'			=> 'genericMsgUAEBookSoldCron_' . $book_info['book_id'],
								'action'		=> 'alert_model->genericMsgUAEBookSoldCron',
								'data'			=> [$book_info['book_id'], $book_info],
								'alert_date'	=> date('Y-m-d H:i:00', strtotime('+5 minutes')),
							]);

							$count_certificate++;

							// pr('genericMsgUAEBookSoldCron');
							// pr($book_info);
						}
					}
				}

				// die;
			}

			pr($count_certificate);
			pr($count_medallion);
			// pr($order_products, 1);
		}
	}
}
