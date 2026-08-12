<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('whatsapp');
load_trait('models/alert');

use Dompdf\Dompdf;

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

trait Test2024
{
	use CommonWhatsapp, DonationAlert;

	public function clearCacheKey() {
		$key   = !empty($this->input->get('key')) ? urldecode($this->input->get('key')) : '';

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$this->cache->delete($key);

	}
	public function sendEmailToVerifySchool() {

		$this->load->model('Alert_model', 'alert_model');

		$event_id = 14;

        $rows = $this->db->get_where('sc_verified_school', ['status' => 0], 200)->result_array();
		// pr($rows, 1);

        foreach ($rows as $row) {

            self::communicationKitParentSCPdfTest([
                'id'    => $row['site_id'],
                'name'  => $row['name'],
                'city'  => $row['city'],
                'state' => $row['state']
            ]);

            $master_class_link          = "https://www.camp.bribooks.com/india/2024/masterclasses";
            $student_link               = "https://www.camp.bribooks.com/india/2024/student/" . $row['site_id'];
            $customised_message_link    = "https://www.camp.bribooks.com/india/2024/communication/" . $row['site_id'];
            $dashboard_link 	        = "https://www.bribooks.com/school/login";

			$subject 						= '7 Days Left to Register Your Students in the 2024 Edition of SBWF';

			$message						= $this->load->view('common/mail/part/verified_school_reminder', [
				'authorized_person' 		=> ucwords($row['authorized_person']),
				'school_name' 				=> ucwords($row['name']),
				'city' 						=> ucwords($row['city']),
				'student_link'  			=> $student_link,
				'dashboard_link' 			=> $dashboard_link,
				'customised_message_link' 	=> $customised_message_link
			], true);

			if ($row['owner_email']) {

				$this->alert_model->email(
					trim($row['owner_email']),
					$subject,
					$message,
					[],
					[],
                    [
                        FCPATH . 'uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$row['site_id'].'.pdf'
                    ]
				);
			}

            // A TYPE
            // self::_sendWhatsappText(
            //     '918794521181',
			// 	// trim($row['owner_mobile']),
            //     [
            //         'template'		=> '1358113871567490',
            //         'parameters'	=> [
            //             $row['authorized_person'],
            //             $row['name'],
			// 			$student_link,
			// 			$dashboard_link,
            //         ]
            //     ]
            // );

            // B TYPE
            self::_sendWhatsappDocument(
                // '918794521181',
				trim($row['owner_mobile']),
                [
                    'template'		=> '1358113871567490',
                    'parameters'	=> [
                        $row['authorized_person'],
                        $row['name'],
                        $student_link,
                        $dashboard_link,
                    ],
                    'document'	=> [
                        'name'	=> 'Communication Kit Parents',
                        'link'	=>  base_url('uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$row['site_id'].'.pdf')
                    ]
                ]
            );

            $this->db->update('sc_verified_school', [
				'status'		=> '1',
			], [
				'id'			=> (int)$row['id']
			]);

            // break;


        }
	}

    private function communicationKitParentSCPdfTest($data = []) {


		if (file_exists('uploads/communication_kit/parent/Communication_Kit_Parents_14_' .$data['id'].'.pdf')) {
			return;
		}

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$event_id = 14;

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/communication_kit_parent_sc_2024', [], true);

		generateQrCode('https://www.camp.bribooks.com/india/2024/student/' . $data['id'] , 20,2, 'uploads/communication_kit/qrcodes/qrcode_'.$data['id'].'.png');

		$html = str_replace(
			[
				'{school_name}',
				'{student_url}',
				'{qrcode_url}',
                '{city}',
				'{state}'
			],
			[
				$data['name'],
				'https://www.camp.bribooks.com/india/2024/student/' . $data['id'],
				base_url() . 'uploads/communication_kit/qrcodes/qrcode_'.$data['id'].'.png',
                $data['city'] ?? '',
				$data['state'] ?? ''
			],
			$html
		);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$file = 'uploads/communication_kit/parent/Communication_Kit_Parents_'. $event_id . '_' .$data['id'].'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
	}

    public function getSingleSchoolPDFTestt($code = '') {

		$dir = FCPATH . 'uploads/eventpass/pdfs';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_single_school_pdf_template', ['code' => $code], true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper(array(0, 0, 430, 500), 'potrait');
		// $dompdf->setPaper('A3', 'potrait');
		$dompdf->render();
		$file = 'uploads/eventpass/pdfs/entry_pass_'.$code;
		// $output = $dompdf->output();
		// file_put_contents(FCPATH.$file, $output);
		$dompdf->render();
		$dompdf->stream($file . '.pdf');
		return base_url($file);
	}

    public function getUserInviteSinglePdfTest($code = '', $jury_rank = '') {

		if (empty($code)) {
			echo "Something went wrong!";
		}

		$dir = FCPATH . 'uploads/eventpass/pdfs';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		// echo $jury_rank;;die;

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_author_single_pdf', [
			'code' => $code,
			'jury_rank' => !empty($jury_rank) ? '#' . $jury_rank : '',
			'head_logo' => base_url('assets/images/nyaf_logo.png'),
		], true);
		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		// $dompdf->setPaper('A4', 'potrait');
		$dompdf->setPaper(array(0, 0, 430, 500), 'potrait');

		$dompdf->render();
		$file = 'uploads/eventpass/pdfs/entry_pass_'.$code;
		// $file = 'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);

		$dompdf->render();
		$dompdf->stream($file . '.pdf');

		// $filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'entry_passes_author_pdf.pdf';

		// $this->zip->add_data('entry_passes_author_pdf.pdf', @file_get_contents(base_url($file)));
		// $this->zip->download($filename);
		// $this->zip->download('newdata.zip');

		return base_url($file);
	}

    public function getSingleExhibitionPdfff() {

        $this->load->model('book/BookExhibition_model', 'book_exhibition_model');
        $this->load->model('common/InviteSlot_model', 'invite_slot_model');

        $results = $this->book_exhibition_model->get_all(['status' => 1])['rows'] ?? [];

        $dir = FCPATH . 'uploads/exhibitionpass/pdfs';

        if (!is_dir($dir)) {
            mkdir($dir, 0777, TRUE);
            chmod($dir, 0777);
            @touch($dir . '/' . 'index.html');
        }

        foreach ($results as $result) {

            $slot_info = $this->invite_slot_model->get($result['slot_id']);
		    $time_slot = date("h:i A", strtotime($slot_info['slot_start'])) . ' - ' . date("h:i A", strtotime($slot_info['slot_end']));

            $html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/single_exhibition_invite_pdf_template', [
                'code'          => $result['code'],
                'head_logo'     => base_url('assets/images/pass_logo.png'),
                'qr_code'       => base_url('uploads/exhibitionpass/qrcode_' . $result['code'] . '.png'),
                'location'      => base_url('assets/images/location.svg'),
                'guest_count'   => $result['guest_count'],
                'name'          => $result['name'],
                'author_image'  => $result['author_image'],
                'slot'          => $time_slot
            ], true);
            $dompdf = new Dompdf();
            $dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
            $dompdf->set_option('isJavascriptEnabled', true);
            $dompdf->set_option('isRemoteEnabled', true);
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $dompdf->setPaper('A3', 'potrait');
            $dompdf->render();
            // $file = 'uploads/exhibitionpass/pdfs/entry_pass_'.$result['code'].'.pdf';
            $file = 'uploads/exhibitionpass/pdfs/entry_pass_'.$result['code'];
            $output = $dompdf->output();
            file_put_contents(FCPATH.$file, $output);

            break;
            // return base_url($file);
        }
	}

	public function brochureInd24() {

		$font_path_regular = FCPATH . 'assets/global/fonts/Poppins-Bold.ttf';

		// $dir = FCPATH . 'uploads/in-nyafbro/brochure/';
		$dir = FCPATH . 'uploads/pdfs';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		// $schoolDetails = $this->db->where('id','3916')->get('brochure_2023')->result_array();
		// // pr($schoolDetails, 1);

		// foreach($schoolDetails as $data) {
		// 	if(empty($data['school_name']))
		// 		continue;

		$img_brochure_1 = imagecreatefromjpeg(FCPATH . 'assets/images/bro-india23/brochure_1.jpg');

		$qr_file 	= generateQrCode('https://www.yaf.bribooks.com/uk/2024/student/' . '142432', 20, 2);
		$qr_image 	= imagecreatefrompng(FCPATH . $qr_file);

		$image_width	= imagesx($img_brochure_1);
		$image_height	= imagesy($img_brochure_1);
		$qr_image_width		= imagesx($qr_image);
		$qr_image_height 	= imagesy($qr_image);

		$zoom = 3;

		imagecopyresampled(
			$img_brochure_1,
			$qr_image,
			($image_width - $qr_image_width / $zoom - 360),
			($image_height - $qr_image_height / $zoom - 350),
			0,
			0,
			$qr_image_width / $zoom,
			$qr_image_height / $zoom,
			$qr_image_width,
			$qr_image_height
		);



			$schoolLength = "https://www.yaf.bribooks.com/uk/2024/student/142432";

			// $darkgrey 	= imagecolorallocate($img_brochure_1, 255, 0, 255);
			$darkgrey 	= imagecolorallocate($img_brochure_1, 0, 0, 0);

			imagettftext($img_brochure_1, 16, 0, 70, 1520, $darkgrey, $font_path_regular, "https://www.yaf.bribooks.com/uk/2024/student/142432");

			imagejpeg($img_brochure_1, $dir . "/brochure_12345_1.jpeg");
			// imagejpeg($img_brochure_1, $dir . "/brochure_".$data['id']."_1.jpeg");
			imagedestroy($img_brochure_1);
		// }
	}

	public function generateBrochurePDFIndia24() {

		$schoolDetails = $this->db->where('status','0')->get('brochure_2023')->result_array();

		$dir = FCPATH . 'uploads/in-nyafbro/school_pdf/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		foreach($schoolDetails as $key => $data) {
			if(empty($data['school_name']))
				continue;

			$file1 = 'uploads/in-nyafbro/brochure/brochure_'.$data['id'].'_1.jpeg';
			$file2 = 'assets/images/bro-india23/brochure_2.jpg';
			$file3 = 'assets/images/bro-india23/brochure_3.jpg';
			$file4 = 'uploads/in-nyafbro/brochure/brochure_'.$data['id'].'_4.jpeg';

			$pdf = new TCPDF();

			pr($pdf, 1);

			$pdf->setTitle('Brochure');
			$pdf->SetMargins(0,0,0,0);
			$pdf->SetAutoPageBreak(true);
			$pdf->SetPrintHeader(false);
			$pdf->setPrintFooter(false);

			$pdf->AddPage('p', 'A4');
			$pdf->Image($file1,0,0,0,0,'','','',true,700,'',false,false,false,false);
			$pdf->AddPage('p', 'A4');
			$pdf->Image($file2,0,0,0,0,'','','',true,700,'',false,false,false,false);
			$pdf->AddPage('p', 'A4');
			$pdf->Image($file3,0,0,0,0,'','','',true,700,'',false,false,false,false);
			$pdf->AddPage('p', 'A4');
			$pdf->Image($file4,0,0,0,0,'','','',true,700,'',false,false,false,false);

			$fname = 'school' . sprintf('%04d', $data['id']);

			pr($fname, 1);

			$pdf_string = $pdf->Output('pseudo.pdf', 'S');
			file_put_contents($dir.$fname.'.pdf', $pdf_string);

			// $this->db->update('brochure_2023', [
			// 	'status'		=> '1',
			// 	'date_added'	=> date('Y-m-d H:i:s'),
			// ], [
			// 	'id'			=> (int)$data['id']
			// ]);

			// unlink(FCPATH . 'uploads/in-nyafbro/brochure/brochure_'.$data['id'].'_1.jpeg');
			// unlink(FCPATH . 'uploads/in-nyafbro/brochure/brochure_'.$data['id'].'_4.jpeg');
		}
	}

	public function userEventInvitee () {
		echo "userEventInvite";die;
		$this->load->model('user/UserEventInvitation_model', 'user_event_invitation_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('user/Student_model', 'student_model');

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/uat_user_event_invite.csv');
		$rows = $this->parsecsv->data;

		pr($rows,1);

		foreach ($rows as $row) {

			// echo $row['user_id'];die;
			if (empty($this->event_user_model->getEventUserByUserId(14, $row['id']))) {
				$this->event_user_model->add([
					'event_id'	=> 14,
					'user_id'	=> $row['user_id']
				]);
			}

			if (empty($this->user_event_invitation_model->get_all([
				'event_id'		=> 14,
				'user_id'		=> $row['user_id']
			])['rows'][0] ?? '')) {
				$this->user_event_invitation_model->add([
					'event_id'		=> 14,
					'user_id'		=> $row['user_id']
				]);
			};

			$reject_url = sprintf(SC_USER_ADDRESS_URL . 'india/2024/signup?uid=%s&code=%s&resp=%s',
				$row['user_id'],
				$row['verification_code'],
				'no'
			);

			$accept_url = sprintf(SC_USER_ADDRESS_URL . 'india/2024/signup?uid=%s&code=%s&resp=%s',
				$row['user_id'],
				$row['verification_code'],
				'yes'
			);

			$subject = ' Important Notification: Please Update Your Details to Continue in SBWF 2024';

			$message			= $this->load->view('common/mail/part/user_event_invitation', [
				'author_name' 	=> $row['name'],
				'reject_url' 	=> $reject_url,
				'accept_url' 	=> $accept_url
			], true);

			!empty($row['email']) && $this->alert_model->email(
				$row['email'],
				$subject,
				$message,
				[],
				(ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
			);

			$user_info = $this->student_model->get($row['user_id']);

			if (!empty($user_info) && !empty($user_info['mobile'])) {
				$url_parameter = sprintf('?uid=%s&code=%s&resp=%s',
					$row['user_id'],
					$row['verification_code'],
					'yes'
				);

				self::_sendWhatsappText(
					// $user_info['mobile'],
					// '919935343128',
					'917367916262',
					[
						'template'		=> '7854362971321686',
						'parameters'	=> [
							trim($row['name']),
						],
						'url_parameters'=> $url_parameter,
					]
				);
			}

			// break;
		}
	}

	public function createManifestPDF () {
		$this->load->library('zip');
		$this->load->model('shipping/PickupData_model', 'pickup_data_model');
		$this->load->model('shipping/Shipment_model', 'shipment_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('shipping/Courier_model', 'courier_model');


		$results = $this->pickup_data_model->get_all([
			'scheduled_date'		=> date('Y-m-d')
		])['rows'];

		// echo $data = $this->db->last_query();

		// pr($results,1);

		$courier_data= [];

		foreach ($results as $key => $item) {
			$shipment_info 	= $this->shipment_model->get($item['shipment_id']);
			$order_info 	= $this->order_model->get($shipment_info['order_id']);
			$order_products = $this->order_model->getProducts($order_info['id']);
			$book_ids = [];
 			array_map(function ($order_products) use (&$book_ids){
				$book_ids[] = $order_products['product_id'];
			}, $order_products);
		}
		// pr($order_products);
		// pr($book_ids,1);

		// $generator = _get_label_barcode($shipment->awb_number, 360, 70);
		$courier_data[$shipment_info['courier_id']][] = [
			'order_id' 		=> $shipment_info['order_id'],
			'order_code' 	=> $order_info['order_code'],
			'awb_number' 	=> $shipment_info['awb_number'],
			'barcode' 		=> _get_label_barcode($shipment_info['awb_number'], 360, 70),
			'sku' 			=> implode(',', $book_ids)
		];

		foreach ($courier_data as $key => $value) {
			$courier_info 	= $this->courier_model->get($shipment_info['courier_id']);

			$html = $this->load->view('common/invoice/manifest_order_print', [
				'courier_name' 	=> $courier_info['name'] ?? 'NA',
				'orders'	  	=> $value
			], true);

			$dompdf = new Dompdf();

			// Load HTML content
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();

			$this->zip->add_data('courier_data_' . $key . '.pdf', $dompdf->output());

		}
		$this->zip->download('manifest.zip');
		// $this->zip->download('newdata.zip');
		// pr($courier_data);
		// pr($data,1);
	}

	public function downloadSchoolReport($event_id = 0, $user_id = 0) {
		if (!empty($event_id) && !empty($user_id)) {
			self::_getSchoolReport($event_id, true, $user_id);
		}
	}

	private function _getSchoolReport($event_id = 0, $download = true, $user_id = 0) {
		$user_id = !empty($user_id) ? (int)$user_id : (int)$user_id;

		if (
			$user_id && $event_id &&
			$user_info = $this->db->get_where('users', [
				'id'		=> $user_id,
				'role_id'	=> 9,
				'status'	=> 1,
			])->row_array()
		) {

			$this->load->library('Common_lib', 'common_lib');

			$data = $this->common_lib->getGradeWiseData($user_id, $event_id);
			$data['event_id'] = $event_id;

			$new_html = '';

			if (in_array($event_id, [NYAF_IN_EVENT_ID, YABWF_EVENT_ID, 14])) {
				$html = $this->load->view('common/report/grade_wise_indian_student_pdf', $data, true);
				$new_data = $this->common_lib->getSchoolDashboardReport($user_info['site_id'], $event_id);
				$new_html = $this->load->view('common/report/student_pdf', $new_data, true);
			}else{
				$html = $this->load->view('common/report/grade_wise_student_pdf', $data, true);
			}

			$dompdf = new Dompdf();
			// Load HTML content
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html . $new_html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();

			$file_name = sprintf('uploads/pdfs/%s-%s.pdf', date('Y-m-d'), $event_id);

			if ($download) {
				$dompdf->stream($file_name);
			} else {
				return $dompdf->output();
			}
		}
	}

	public function createParticipationCert () {
		echo "createParticipationCert";
		// $this->load->model('certificate/Certificate_model', 'certificate_model');
		// $this->load->model('user/Student_model', 'student_model');

		// $this->load->library('parsecsv');
		// $this->parsecsv->auto('assets/csv/participation.csv');
		// $rows = $this->parsecsv->data;

		// // pr($rows);

		// foreach ($rows as $row) {

		// 	$cert_info = $this->certificate_model->get_all([
		// 		'event_id'		=> 10,
		// 		'book_id'		=> 0,
		// 		'user_id'		=> $row['uid']
		// 	])['rows'];

		// 	// pr($cert_info,1);

		// 	if (empty($this->certificate_model->get_all([
		// 		'event_id'		=> 10,
		// 		'book_id'		=> 0,
		// 		'user_id'		=> $row['uid']
		// 	])['rows'][0] ?? '')) {
		// 		$author_info = $this->student_model->get($row['uid']);
		// 		$certificate_key = sprintf('participation_cert_user_%s_%s', $row['uid'], 10);

		// 		$certificate_id = $this->certificate_model->add([
		// 			'site_id'					=> $author_info['site_id'] ?? 1,
		// 			'event_id'					=> 10,
		// 			'book_id'					=> 0,
		// 			'user_id'					=> $row['uid'],
		// 			'type'						=> 'participation_cert',
		// 			'certificate_template_id'	=> 164,
		// 			'unique_id'					=> 164,
		// 			'name'						=> $certificate_key,
		// 			'image'						=> $certificate_key,
		// 		]);

		// 		if (!empty($certificate_id)) {
		// 			$unique_id = 'BB/' . sprintf('%08d', $certificate_id) . '/11' ;
		// 			$this->certificate_model->edit($certificate_id, [
		// 				'unique_id' 	=> $unique_id,
		// 				'date_added' 	=> '2024-02-18 11:05:00'
		// 			]);
		// 		}
		// 	};
		// 	break;
		// }
	}

	public function enrolTestBookInSummer2024() {
		return
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');

		$this->load->library('GenericCertificate_lib');
		$this->load->library('Ranking_lib', 'ranking_lib');
		$this->load->library('parsecsv');

		$this->parsecsv->auto('assets/csv/enrol_summer_book.csv');
		$rows = $this->parsecsv->data;

		// pr($rows);

		foreach ($rows as $key =>$row) {
			// echo $row['book_id'];
			$book_info = $this->book_model->get($row['book_id']);
			// pr($book_info,1);

			if (!empty($book_info) && empty($this->event_book_model->get_all([
				'book_id'		=> $row['book_id']
			])['rows'][0] ?? '')) {
				if (!empty($this->event_book_model->add([
					'event_id'		=> 14,
					'book_id'		=> $row['book_id']
				]))) {
					if (empty($this->event_user_model->get_all([
						'event_id'		=> 14,
						'user_id'		=> $book_info['user_id']
					])['rows'][0] ?? '')) {
						$this->event_user_model->add([
							'event_id'		=> 14,
							'user_id'		=> $book_info['user_id']
						]);
					}

					if (!empty($products = $this->order_product_model->get_all([
						'product_id'	 => $book_info['id']
					])['rows'] ?? [])) {
						$order_ids = [];
						foreach ($products as $product) {
							$order_info = $this->order_model->get($product['order_id']);

							if (!empty($order_info) && (!in_array($order_info['status'], [0, 91, 92]))) {
								$this->event_order_model->add([
									'event_id'		=> 14,
									'order_id'		=> $order_info['id'],
									'book_id'		=> $book_info['id'],
									'quantity'		=> $product['quantity']
								]);

								$order_ids[] = $order_info['id'];
							}
						}

						if (!empty($order_ids)) {
							rsort($order_ids);
							$this->ranking_lib->updateRank($order_ids[0]);
							$this->genericcertificate_lib->createCertificate($order_ids[0], false);

							if (!empty($certficates = $this->certificate_model->get_all([
								'event_id'	 => 0,
								'book_id'	 => $book_info['id']
							])['rows'] ?? [])) {

								$this->db->where_in('id', array_column($certficates, 'id'));
								$this->db->update('certificates',  [
									'_deleted'		=> 1,
									'date_deleted'	=> date('Y-m-d H:i:s'),
								]);
							}
						}
					}
				}
			}
			if ($key == 3) {

				break;
			}
		}
	}

	public function enrolBookInFestivals() {
		return;
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('user/Student_model', 'student_model');

		$this->load->library('Ranking_lib', 'ranking_lib');
		$this->load->library('parsecsv');

		// $this->parsecsv->auto('assets/csv/brooklyn_user_live.csv');
		$rows = $this->parsecsv->data;

		// pr($rows);

		foreach ($rows as $key =>$row) {
			$book_info = $this->book_model->get($row['book_id']);

			if (!empty($book_info)) {
				$event_order_info  = $this->event_order_model->get_all([
					'book_id'		=> $row['book_id']
				])['rows'][0] ?? '';
				// pr($event_order_info,1);
				if (!empty($event_order_info)) {
					$this->ranking_lib->updateRank($event_order_info['order_id']);
				}
			}
			// break;
		}
	}

	public function genAppriciationCertificatePdf($site_id = 0, $download = false) {
		$this->load->model('common/Site_model', 'site_model');
		$site_info  = $this->site_model->get($site_id);

		if (empty($site_id) || empty($site_info)) {
			echo "site_not_found";
		}

		// $image_template = sprintf('%spublic/EventGallery/certificate_template/%s', $this->config->item('cloudfront_url'), $data['image']);
		$image_template = FCPATH . 'assets/images/appriciation.jpg';

		list($image_width, $image_height) = getimagesize($image_template);

		$image 		= imagecreatefromjpeg($image_template);

		$darkgrey 	= imagecolorallocate($image, 70, 70, 70);
		$grey 		= imagecolorallocate($image, 110, 110, 110);

		$font_path 	= FCPATH . 'assets/global/fonts/Poppins-SemiBold.otf';

		if (!empty($site_info['name']) && strlen($site_info['name']) > 40) {
			$font_size 	= 20;
		} else {
			$font_size 	= 28;
		}
		$font_size 	= 20;

		imagettftext($image, $font_size, 0, 120, 610, $darkgrey, $font_path, strtoupper($site_info['authorized_person']));
		imagettftext($image, $font_size, 0, 120, 810, $darkgrey, $font_path, strtoupper($site_info['name']));
		imagettftext($image, 18, 0, 1450, 1070, $darkgrey, $font_path, '18/02/2024');

		$zoom = 5.5;

		$filename = FCPATH . sprintf('uploads/test/tempcert_%s.png', uniqid());

		imagejpeg($image, $filename);
		imagedestroy($image);
		// $this->load->library('s3');

		// upload to s3 bucket and share the cloudfront url


		$html = sprintf('<style>@page{margin:0;padding:0;}</style><img
			src="%s"
			style="width:100%%;max-height:100%%;"
		/>', base_url(str_replace(FCPATH, '', $filename)));

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		if ($download) {
			$dompdf->stream('school_' . $site_info['id'] . '.pdf');
		} else {
			$this->load->library('S3_lib', 's3_lib');
			$this->s3_lib->setBucket('authorcertificates');

			$file_name = 'school_' . $site_info['id'] . '.pdf';
			$s3_dirname = 'appreciation';

			$s3_dirname = ((ENVIRONMENT === 'production') ? $s3_dirname . '/live' : $s3_dirname . '/test');

			$this->s3_lib->putData(
				$file_name,
				$s3_dirname,
				$dompdf->output(),
				false
			);

			echo 'https://authorcertificates.s3.ap-south-1.amazonaws.com/appreciation/live/'. $file_name;
		}

		// unlink($filename);
	}

	public function inviteUserInEvent() {
		// $this->load->model('user/UserEventInvitation_model', 'user_event_invitation_model');

		// $this->load->library('parsecsv');
		// $this->parsecsv->auto('assets/csv/user_invite_new.csv');
		// $rows = $this->parsecsv->data;

		// pr($rows);

		// foreach ($rows as $row) {
		// 	$user_invite_info 	= $this->user_event_invitation_model->get_all([
		// 		'event_id' 		=> $row['event_id'],
		// 		'user_id' 		=> $row['user_id']
		// 	])['rows'][0] ?? '';

		// 	if (empty($user_invite_info)) {
		// 		$this->user_event_invitation_model->add([
		// 			'event_id' 		=> $row['event_id'],
		// 			'user_id' 		=> $row['user_id']
		// 		]);
		// 	}
		// 	// break;
		// }
	}

	public function updatedSchoolsDetails () {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('event/EventSite_model', 'event_site_model');

	}

	public function insertNewUsers () {

		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('common/Cron_model', 'cron_model');

		$this->load->library('parsecsv');
		// $this->parsecsv->auto('assets/csv/insert_users_s2.csv');
		// $this->parsecsv->auto('assets/csv/insert_users_s3.csv');
		$this->parsecsv->auto('assets/csv/insert_direct_users.csv');
		$rows = $this->parsecsv->data;

		// pr($rows,1);

		$users = [];

		foreach ($rows as $key => $row) {

			$user_info = $this->db->get_where('users', [
				'email'		=> $row['email'],
				'_deleted'  => 0
			])->row_array();

			if (empty($user_info)) {

				$this->db->select_max('id');
				$last_user_id = $this->db->get('users')->row_array()['id'];
				$last_user_id++;

				$last_user_id = sprintf('%06d', $last_user_id);

				$username = strtolower(trim(
					substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $row['first_name']), 0, 2) .
					substr($last_user_id, -6)
				));

				$password 			= uniqid();
				$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
				$verification_code 	= sha1(md5($username . $password . $this->config->item('password_salt')));

				$user_id = $this->student_model->add([
					'first_name'	=> trim($row['first_name']) ?? '',
					'last_name'		=> trim($row['last_name']) ?? '',
					'parent_name'	=> '',
					'slug'			=> get_user_slug($username),
					'username'		=> $username,
					'password'		=> $encoded_password,
					'mobile'		=> $row['mobile'] ?? '',
					'email'			=> trim($row['email']) ?? '',
					'source'		=> 'forced_school',
					'country_id'	=> (int)$row['country_id'] ?? 0,
					'state_id'		=> (int)$row['state_id'] ?? 0,
					'city_id'		=> (int)$row['city_id'] ?? 0,
					'grade_id'		=> trim($row['grade']) ?? 0,
					'section_id'	=> $row['section'] ?? '',
					'grade'			=> trim($row['grade']) ?? 0,
					'section'		=> $row['section'] ?? '',
					'role_id'		=> 2,
					'site_id'		=> $row['site_id'],
					'status'		=> 1,
					'location'		=> 'United Kingdom',
					'referral_code'	=> mb_strtoupper(uniqid()),
					'verification_code'	=> $verification_code,
					'ip'			=> '',
					'timezone'		=> $row['timezone'] ?? '',
					'mobile_verified'	=> 0,
					'email_verified'	=> 1
				]);

				$event_id = 15;
				// add to event
				if (
					$user_id &&
					empty($this->event_user_model->getEventUserByUserId($event_id, $user_id))
				) {
					$this->event_user_model->add([
						'event_id'	=> $event_id,
						'user_id'	=> (int)$user_id,
					]);
				}

				$this->cron_model->add([
					'code'			=> 'welcomeDirectUser_' . $user_id,
					'action'		=> 'alert_model->welcomeDirectUser',
					'data'			=> [$user_id],
					'site_id'		=> 1,
					'alert_date'	=> date('Y-m-d H:i:s', strtotime('+'.($key + 1).' minutes')),
				]);
			} else {
				$users[] = [
					'sn'			=> $key + 1,
					'first_name'	=> $row['first_name'],
					'last_name'		=> $row['last_name'],
					'email'			=> $row['email'],
				];
			}

			// if ($key == 9) {
			// }
			// break;
		}
		self::_downloadCsvTest($users, 'missed_users');
	}

	private function _downloadCsvTest($results = [], $filename = 'download') {
		$filename = $filename . date('Y_m_d_h_i_s') . '.csv';

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
			exit(_l('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		self::_writeRowToCsvTest($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	private function _writeRowToCsvTest($results = [], $fp = null, $headers = []) {
		fputs($fp, "\xEF\xBB\xBF");

		fputcsv($fp, $headers);

		if (is_array($results) && $results && is_resource($fp) && is_array($headers) && $headers) {
			foreach ($results as $result) {
				$row = [];

				foreach ($headers as $header) {
					if (!empty($result[$header]) && is_array($result[$header])) {
						//self::_writeRowToCsv($result[$header], $fp, array_keys($result[$header]));
					} else {
						$row[] = !empty($result[$header]) ? $result[$header] : '';
					}
				}

				fputcsv($fp, $row);
			}
		}
	}

	public function uploadLiteraryCert() {
		$this->load->library('S3_lib', 's3_lib');
			$this->s3_lib->setBucket('authorcertificates');

			$file_name = 'school_27.pdf';
			$s3_dirname = 'literary';

			$s3_dirname = ((ENVIRONMENT === 'production') ? $s3_dirname . '/live' : $s3_dirname . '/test');


			$this->s3_lib->putData(
				$file_name,
				$s3_dirname,
				base_url('assets/images/literary/27.pdf'),
				// FCPATH . 'assets/images/literary/27.pdf',
				false
			);

			echo 'https://authorcertificates.s3.ap-south-1.amazonaws.com/literary/live/' . $file_name;

	}


	public function uploadPdfTest($site_id = 0, $event_id = 0) {

		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$site_info = $this->site_model->get(172592);
		$template_id 	= '1676522836435946';
		$parameters 	= [
			$site_info['name'],
			$site_info['authorized_person'],
			$site_info['name'],
			USER_YAF_URL . 'sg/2024/student/' . $site_info['id'],
			USER_YAF_URL . 'sg/2024/communication/' . $site_info['id']
		];
		$document	= [
			'name'	=> 'Brochure',
			'link'	=> base_url('uploads/pdfs/School_18_' .$site_info['id'].'.pdf'),
		];


		self::_sendWhatsappDocument(
			'919935343128',
			[
				'template'		=> $template_id,
				'parameters'	=> $parameters,
				'document'		=> $document
			],
		);die;

		if(empty($site_id) || empty($site_id) || empty($site_info = $this->site_model->get($site_id)) || empty($event_info = $this->event_model->get($event_id)))
			return;

		$dir = FCPATH . 'uploads/communication_kit/parent';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$state_info = $this->state_model->get($site_info['state_id']);
		$city_info = $this->city_model->get($site_info['city_id']);

		$event_url 						= ENVIRONMENT != 'production' ?  'https://uat.events.bribooks.com/' :  'https://www.events.bribooks.com/';

		generateQrCode($event_url . 'student/' . $site_info['id'] , 20,2, 'uploads/communication_kit/qrcodes/qrcode_'.$site_info['id'].'.png');

		$data = [
			'authorized_person' 		=> ucwords($site_info['authorized_person']),
			'school_name' 				=> ucwords($site_info['name']),
			'student_url'  				=> $event_url . 'student/' . $site_info['id'],
			'student_reg_end_date' 		=> 	date('d M Y', strtotime($event_info['student_reg_end_date'])),
			'state' 					=> $state_info['name'],
			'city' 						=> $city_info['name'],
			'qrcode_url' 				=> base_url(generateQrCode('www.events.bribooks.com/student/' . $site_id, 20, 2))
		];

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/general_communication_kit_parent_school', $data, true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();

		$file_name 	= 'Kit_'. $event_id . '_' .$site_info['id'].'.pdf';
		$s3_dirname = 'parent_communication_kit';
		$s3_dirname = ((ENVIRONMENT === 'production') ? $s3_dirname . '/live' : $s3_dirname . '/test');


		$this->load->library('S3_lib', 's3_lib');
		$this->s3_lib->setBucket('authorcertificates');

		// KIT URL LINK = { 'https://authorcertificates.s3.ap-south-1.amazonaws.com/parent_communication_kit/live/'. $file_name };
		$this->s3_lib->putData(
			$file_name,
			$s3_dirname,
			$dompdf->output(),
			false
		);

		echo 'https://authorcertificates.s3.ap-south-1.amazonaws.com/parent_communication_kit/live/'. $file_name;
	}

	public function uploadBrochureTest() {

		$data = [
			'image1' 	=> sprintf('%spublic/EventGallery/brochure/general_teacher/brochure_1.jpg', $this->config->item('cloudfront_url')),
			'image2' 	=> sprintf('%spublic/EventGallery/brochure/general_teacher/brochure_2.jpg', $this->config->item('cloudfront_url')),
			'image3' 	=> sprintf('%spublic/EventGallery/brochure/general_teacher/brochure_3.jpg', $this->config->item('cloudfront_url')),
			'image4' 	=> sprintf('%spublic/EventGallery/brochure/general_teacher/brochure_4.jpg', $this->config->item('cloudfront_url')),
			'image5' 	=> sprintf('%spublic/EventGallery/brochure/general_teacher/brochure_5.jpg', $this->config->item('cloudfront_url')),
			'url' 		=> 'www.events.bribooks.com/student/' . 732,
			'qr_file' 	=> base_url(generateQrCode('www.events.bribooks.com/student/' . 732, 20, 2))
		];

		// $html = $this->load->view('frontend/default/brochure', $data, true);
		$html = $this->load->view('frontend/default/general_brochure', $data, true);

		$dompdf = new Dompdf([]);

		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		$dompdf->setPaper(
			[
				0,
				0,
				390,
				844
			],
			'portrait'
		);

		$dompdf->render();

		$this->load->library('S3_lib', 's3_lib');
		$this->s3_lib->setBucket('authorcertificates');
		$file_name = 'school_14_732.pdf';

		$s3_dirname = ((ENVIRONMENT === 'production') ? 'brochure/live' : 'brochure/test');

		$this->s3_lib->putData(
			$file_name,
			$s3_dirname,
			$dompdf->output(),
			false
		);

		echo 'https://authorcertificates.s3.ap-south-1.amazonaws.com/brochure/live/'. $file_name;

	}

	public function enrolUserAndBookInEvent() {
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');

		$this->load->library('GenericCertificate_lib');
		$this->load->library('Ranking_lib', 'ranking_lib');
		$this->load->library('parsecsv');

		$rows = $this->db->query("SELECT users.id
		FROM `users`
		WHERE users.`_deleted` = '0'
		AND users.role_id = 2
		AND users.`date_added` > '2024-04-14'
		AND users.`location` = 'United Kingdom'
		AND users.id NOT IN (select user_id from event_user where _deleted = 0)")->result_array();

		// pr($rows,1);die;

		foreach ($rows as $key =>$row) {
			$author_info = $this->student_model->get($row['id']);

			if (!empty($author_info) && empty($this->event_user_model->get_all([
				'event_id'		=> 15,
				'user_id'		=> $author_info['id']
			])['rows'][0] ?? '')) {
				$this->event_user_model->add([
					'event_id'		=> 15,
					'user_id'		=> $author_info['id']
				]);

				$books = $this->book_model->get_all([
					'user_id' => $author_info['id']
				]);

				foreach ($books as $book) {
					if (empty($this->event_book_model->get_all([
						'book_id'		=> $book['id']
					])['rows'][0] ?? '')) {
						if (!empty($this->event_book_model->add([
							'event_id'		=> 15,
							'book_id'		=> $book['id']
						]))) {

							if (!empty($products = $this->order_product_model->get_all([
								'product_id'	 => $book['id']
							])['rows'] ?? [])) {
								$order_ids = [];
								foreach ($products as $product) {
									$order_info = $this->order_model->get($product['order_id']);

									if (!empty($order_info) && (!in_array($order_info['status'], [0, 91, 92]))) {
										$this->event_order_model->add([
											'event_id'		=> 15,
											'order_id'		=> $order_info['id'],
											'book_id'		=> $book['id'],
											'quantity'		=> $product['quantity']
										]);

										$order_ids[] = $order_info['id'];
									}
								}

								if (!empty($order_ids)) {
									rsort($order_ids);
									$this->genericcertificate_lib->createCertificate($order_ids[0], false);

									if (!empty($certficates = $this->certificate_model->get_all([
										'event_id'	 => 0,
										'book_id'	 => $book['id']
									])['rows'] ?? [])) {

										$this->db->where_in('id', array_column($certficates, 'id'));
										$this->db->update('certificates',  [
											'_deleted'		=> 1,
											'date_deleted'	=> date('Y-m-d H:i:s'),
										]);
									}
								}
							}
						}
					}
				}
			}
			// break;
		}
	}

	public function DBTEST($cert_id = 0, $sold = 0) {
			$this->load->model('book/Book_model', 'book_model');
			$this->load->library('Student_model', 'student_model');
			$this->load->library('Alert_model', 'alert_model');

			$rows = $this->db->query("SELECT event_book.event_id, event_book.book_id, event_book._deleted, book.user_id
			FROM `event_book`
			join book on book.id = event_book.book_id
			join users on users.id = book.user_id
			WHERE `event_id` = '14' AND event_book.`_deleted` = '0'
			AND book._deleted = 0
			AND book.archived = 0
			AND users._deleted = 0
			GROUP BY book.user_id limit 1")->result_array();

			// pr($rows,1);

			foreach ($rows as $key =>$row) {
				if (!empty($row['user_id']) && !empty($author_info = $this->student_model->get($row['user_id']))) {
					$books = $this->book_model->get_all([
						'user_id' 	=> $row['user_id'],
						'event_id'	=> $row['event_id']
					])['rows'] ?? [];

					if (!empty($books) && !empty($author_info['email'])) {
						$subject 						= "Publishing Deadline for Summer Book Writing Festival 2024";
						$message						= $this->load->view('common/mail/part/event_book_confirm', [
							'author_name' 	=> ucwords($author_info['first_name'] . ' ' . $author_info['last_name']),
							'books' 		=> $books,
						], true);

						$this->alert_model->email(
							trim($author_info['email']),
							$subject,
							$message,
							[],
							[],
							[]
						);
					}
				}
			}
	}

	public function makeRegisterSchoolDataCSV() {
		$this->load->library('State_model', 'state_model');
		$this->load->library('City_model', 'city_model');
		$this->load->library('parsecsv');

		$this->parsecsv->auto('assets/csv/school_clean/nyaf_2024_register.csv');
		$rows = $this->parsecsv->data;

		// pr($rows,1);

		$results = [];

		foreach ($rows as $key => $row) {
			$state_info = $this->state_model->get_all(['name' => $row['state']])['rows'][0] ?? '';
			$city_info = $this->city_model->get_all(['name' => $row['city']])['rows'][0] ?? '';

			$results[] = [
				'id' => $key + 1,
				'site_id' => $row['site_id'],
				'school_name' => $row['school_name'],
				'state' => $row['state'],
				'city' => $row['city'],
				'state_id' => $state_info['id'],
				'city_id' => $city_info['id'],
				'state_name' => $state_info['name'] . '(' .$state_info['id'] . ')',
				'city_name' => $city_info['name'] . ' (' .$city_info['id'] . ')',
				'email' => $row['email'],
				'mobile' => $row['mobile'],
				'authorized_person' => $row['authorized_person'],
				'alternate_email' => $row['alternate_email'],
				'alternate_mobile' => $row['alternate_mobile'],
				'alternate_authorized_person' => $row['alternate_authorized_person'],
				'address' => $row['address'],
				'zipcode' => $row['zipcode']
			];

			// if ($key > 5) {
			// 	break;
			// }
		}

		$headers = [
			'id',
			'site_id',
			'school_name',
			'state',
			'city',
			'state_id',
			'city_id',
			'state_name',
			'city_name',
			'email',
			'mobile',
			'authorized_person',
			'alternate_email',
			'alternate_mobile',
			'alternate_authorized_person',
			'address',
			'zipcode'
		];

		$filename 	= 'sample_nyaf2024_live_regsiter_school_' . date('Y_m_d_H_i_s') . '.csv';


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

		if (!$headers) {
			exit($this->lang->line('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		$this->writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	public function makeUnRegisterSchoolDataCSV() {
		$this->load->library('State_model', 'state_model');
		$this->load->library('City_model', 'city_model');
		$this->load->library('parsecsv');

		$this->parsecsv->auto('assets/csv/school_clean/nyaf_2024_unregister.csv');
		$rows = $this->parsecsv->data;

		// pr($rows,1);

		$results = [];

		foreach ($rows as $key => $row) {
			$state_info = $this->state_model->get_all(['name' => $row['state']])['rows'][0] ?? '';
			$city_info = $this->city_model->get_all(['name' => $row['city']])['rows'][0] ?? '';

			$results[] = [
				'id' => $key + 1,
				'site_id' => $row['site_id'],
				'school_name' => $row['school_name'],
				'state' => $row['state'],
				'city' => $row['city'],
				'state_id' => $state_info['id'],
				'city_id' => $city_info['id'],
				'state_name' => $state_info['name'] . '(' .$state_info['id'] . ')',
				'city_name' => $city_info['name'] . ' (' .$city_info['id'] . ')',
				'email' => $row['email'],
				'mobile' => $row['mobile'],
				'authorized_person' => $row['authorized_person'],
				'alternate_email' => $row['alternate_email'],
				'alternate_mobile' => $row['alternate_mobile'],
				'alternate_authorized_person' => $row['alternate_authorized_person'],
				'address' => $row['address'],
				'zipcode' => $row['zipcode']
			];

			// if ($key > 5) {
			// 	break;
			// }
		}

		$headers = [
			'id',
			'site_id',
			'school_name',
			'state',
			'city',
			'state_id',
			'city_id',
			'state_name',
			'city_name',
			'email',
			'mobile',
			'authorized_person',
			'alternate_email',
			'alternate_mobile',
			'alternate_authorized_person',
			'address',
			'zipcode'
		];

		$filename 	= 'sample_nyaf2024_live_unregister_school_' . date('Y_m_d_H_i_s') . '.csv';


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

		if (!$headers) {
			exit($this->lang->line('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		$this->writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	public function makeNewSiteDataCSV($type = '') {
		$this->load->library('State_model', 'state_model');
		$this->load->library('City_model', 'city_model');
		$this->load->library('parsecsv');

		if ($type == 'emerging') {
			$this->parsecsv->auto('assets/csv/school_clean/NYAF_2024_Emerging.csv');
		} elseif ($type == 'smart') {
			$this->parsecsv->auto('assets/csv/school_clean/NYAF_2024_Smart.csv');
		} elseif ($type == 'vintage') {
			$this->parsecsv->auto('assets/csv/school_clean/NYAF_2024_Vintage.csv');
		} else {
			$this->parsecsv->auto('assets/csv/school_clean/NYAF_2024_School_Chains.csv');
		}

		$rows = $this->parsecsv->data;

		pr($rows,1);

		$results = [];

		foreach ($rows as $key => $row) {
			$state_info = $this->state_model->get_all(['name' => $row['State']])['rows'][0] ?? '';
			$city_info = $this->city_model->get_all(['name' => $row['City']])['rows'][0] ?? '';

			$results[] = [
				'parent_id' => $row['parent_id'],
				'country_id' => $row['parent_id'],
				'state' => $row['State'],
				'city' => $row['City'],
				'state_id' => $state_info['id'],
				'city_id' => $city_info['id'],
				'state_name' => $state_info['name'] . '(' .$state_info['id'] . ')',
				'city_name' => $city_info['name'] . ' (' .$city_info['id'] . ')',
				'school_name' => $row['school_name'],
				'email' => $row['email'],
				'mobile' => $row['mobile'],
				'address' => $row['address'],
				'landmark' => $row['landmark'],
				'zipcode' => $row['zipcode'],
				'authorized_person' => $row['authorized_person'],
				'owner_name' => $row['owner_name'],
				'event_id' => $row['event_id'],
				'is_school_lead' => $row['is_school_lead'],
			];

			if ($key > 5) {
				break;
			}
		}

		$headers = [
			'parent_id',
			'state',
			'city',
			'state_id',
			'city_id',
			'state_name',
			'city_name',
			'school_name',
			'email',
			'mobile',
			'address',
			'landmark',
			'zipcode',
			'site_type',
			'authorized_person',
			'owner_name',
			'event_id',
			'is_school_lead'
		];

		$filename 	= 'sample_nyaf2024_regsiterschool_' . date('Y_m_d_H_i_s') . '.csv';

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

		if (!$headers) {
			exit($this->lang->line('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		$this->writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	public function makeCloneSchoolDataCSV($type = '') {
		$this->load->library('State_model', 'state_model');
		$this->load->library('City_model', 'city_model');
		$this->load->library('parsecsv');

		if ($type == 'tsm_fresh') {
			$this->parsecsv->auto('assets/csv/school_clean/TSM_Fresh.csv');
		} else {
			$this->parsecsv->auto('assets/csv/school_clean/TSM.csv');
		}

		$rows = $this->parsecsv->data;

		// pr($rows,1);

		$results = [];

		foreach ($rows as $key => $row) {
			$state_info = $this->state_model->get_all(['name' => $row['State']])['rows'][0] ?? '';
			$city_info = $this->city_model->get_all(['name' => $row['City']])['rows'][0] ?? '';

			$results[] = [
				'site_id' => $row['site_id'],
				'parent_id' => $row['parent_id'],
				'country_id' => $row['parent_id'],
				'state' => $row['State'],
				'city' => $row['City'],
				'state_id' => $state_info['id'],
				'city_id' => $city_info['id'],
				'state_name' => $state_info['name'] . '(' .$state_info['id'] . ')',
				'city_name' => $city_info['name'] . ' (' .$city_info['id'] . ')',
				'school_name' => $row['school_name'],
				'email' => $row['email'],
				'mobile' => $row['mobile'],
				'authorized_person' => $row['authorized_person'],
				'alternate_email' => $row['alternate_email'],
				'alternate_mobile' => $row['alternate_mobile'],
				'alternate_authorized_person' => $row['alternate_authorized_person'],
				'owner_name' => $row['owner_name'],
				'address' => $row['address'],
				'landmark' => $row['landmark'],
				'zipcode' => $row['zipcode'],
				'site_type' => $row['site_type'],
			];

			// if ($key > 5) {
			// 	break;
			// }
		}

		$headers = [
			'site_id',
			'parent_id',
			'country_id',
			'state',
			'city',
			'state_id',
			'city_id',
			'state_name',
			'city_name',
			'school_name',
			'email',
			'mobile',
			'authorized_person',
			'alternate_email',
			'alternate_mobile',
			'alternate_authorized_person',
			'owner_name',
			'address',
			'landmark',
			'zipcode',
			'site_type',
		];

		$filename 	= 'sample_nyaf2024_regsiterschool_' . date('Y_m_d_H_i_s') . '.csv';


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

		if (!$headers) {
			exit($this->lang->line('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		$this->writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	public function addAndUpdateTableData($certificate_template_id = 0) {
		return;
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');
		$this->load->model('book/Book_model', 'book_model');


		$this->load->library('parsecsv');
		$this->parsecsv->auto(sprintf('assets/csv/league/prime_jury_21.csv'));
		$rows = $this->parsecsv->data;

		if (empty($template_info = $this->certificate_template_model->get($certificate_template_id))) return;

		pr($rows);
		foreach ($rows as $row) {
			if (empty($certificate_info = $this->certificate_model->get_all([
				'book_id' 					=> $row['book_id'],
				'certificate_template_id' 	=> $template_info['id'],
			])['rows'] ?? [])) {
				$book_info = $this->book_model->get($row['book_id']);
		// pr($book_info);


				$certificate_key = sprintf('%s_user_%s_%s', $template_info['type'], $book_info['user_id'], $row['book_id']);

				$this->certificate_model->add([
					'site_id'					=> 1,
					'event_id'					=> $template_info['event_id'],
					'book_id'					=> $row['book_id'],
					'user_id'					=> $book_info['user_id'],
					'rank'						=> $row['rank'],
					'type'						=> $template_info['type'],
					'certificate_template_id'	=> $template_info['id'],
					'achievement'				=> $template_info['achievement'],
					'unique_id'					=> $template_info['id'],
					'name'						=> $certificate_key,
					'image'						=> $certificate_key,
				]);
			}
			// die;
		}

	}

	public function createLeagueCert($event_id = 0) {
		if (empty($event_id)) return;

		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/School_model', 'school_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/table_update/cert_event_' . $event_id . '.csv');
		$rows = $this->parsecsv->data;

		// pr($rows,1);

		foreach ($rows as $row) {
			$template_info = $this->certificate_template_model->get_all([
				'event_id' 	=> $event_id,
				'type'		=> 'National_Best_Seller_Certificate',
			])['rows'][0] ?? '';

			if (!empty($template_info)) {
				$author_info = $this->student_model->get($row['user_id']);
				$certificate_key = sprintf('national_author_league_cert_user_%s_%s', $row['user_id'], $row['book_id']);

				$certificate_info = $this->certificate_model->get_all([
					'book_id'				=> $row['book_id'],
					'event_id'				=> $event_id,
					'user_id'				=> $row['user_id'],
					'name'					=> $certificate_key,
				])['rows'][0] ?? [];

				if (empty($certificate_info)) {
					$certificate_id = $this->certificate_model->add([
						'site_id'					=> $author_info['site_id'] ?? 1,
						'event_id'					=> $event_id,
						'book_id'					=> $row['book_id'],
						'user_id'					=> $row['user_id'],
						'certificate_template_id'	=> $template_info['id'] ?? 0,
						'type'						=> 'National_Best_Seller_Certificate',
						'rank'						=> $row['rank'] ?? 0,
						'name'						=> $certificate_key,
						'image'						=> $certificate_key,
						'unique_id'					=> $template_info['id'] ?? 0,
						'achievement'				=> $template_info['achievement'] ?? 0
					]);

					if (!empty($certificate_id)) {
						$unique_id = 'BB/' . sprintf('%08d', $certificate_id) . '/' . ($template_info['id'] ?? '12') ;
						$this->certificate_model->edit($certificate_id, [
							'unique_id' 	=> $unique_id,
							'date_added' 	=> '2024-08-04 11:50:00'
						]);
					}

				}
			}
		}
	}

	public function generateInvoiceTest($order_id = 0) {
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('address/Address_model', 'address_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('shipping/Shipment_model', 'shipment_model');
		$this->load->model('order/OrderClone_model', 'order_clone_model');

		$order_info 	= $this->order_model->get($order_id);
		$address_info 	= $this->address_model->getByID($order_info['address_id'] ?? 0);
		$user_info 		= $this->user_model->get($order_info['user_id'] ?? 0);
		$products 		= $this->order_model->getProducts($order_info['id']);

		if (empty($order_info)) {
			echo 'INVALID ORDER';
		}
		if (empty($user_info)) {
			echo 'INVALID USER';
		}

		if (empty($address_info)) {
			echo 'INVALID AADRES';
		}

		$shipping_info = json_decode($order_info['shipping_info'], true);

		if (empty($shipment_info = $this->shipment_model->get($shipping_info['bb_shipment_id']))) {
			echo 'INVALID SHIPMENT';
		}

		// clone orders
		if (!empty($order_info['parent_order_id'])) {
			$parent_order_info = $this->order_model->get($order_info['parent_order_id']);

			$clone_orders_count = $this->order_clone_model->get_all([
				'parent_order_id' => $order_info['parent_order_id'],
			])['total'] ?? 0;

			$order_info['total'] = $parent_order_info['total'] / $clone_orders_count;
		}

		$amount 						= $order_info['total'] * (get_exchange_rate($order_info['currency_code']));
		$shipping_cost_amount 			= $order_info['shipping_cost'] * (get_exchange_rate($order_info['currency_code']));

		$order_info['total'] 			= $amount;
		$order_info['amount'] 			= $amount;
		$order_info['shipping_cost'] 	= $shipping_cost_amount;
		$order_info['currency_symbol'] 	= 'INR';

		foreach ($products as $index => $item) {
			$products[$index]['total'] = $item['total'] * (get_exchange_rate($order_info['currency_code']));
		}

		$data['order'] 		= $order_info;
		$data['address'] 	= $address_info;
		$data['products'] 	= $products;


		$shipping_tracking_info = !empty($order_info['shipping_tracking_info'])
			? json_decode($order_info['shipping_tracking_info'], 1)
			: []
		;

		$data['awb_number'] = $shipping_tracking_info['awb_code'] ?? '';

		$html = $this->load->view('common/invoice/invoice_order_print', $data, true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->set_paper([0, 0, 296, 450]);

		$dompdf->render();

		$file 	= 'invoice_' . $order_info['order_code'] . '_' . date('Y_m_d_H_i_s', strtotime($order_info['date_added'])) . '.pdf';

		$dompdf->stream($file);
	}

	public function getFestivalGiftCard($book_id = 0) {
		$this->load->model('book/Book_model', 'book_model');

		if (!empty($book_info = $this->book_model->get($book_id))) {

			$data['book_name'] 		= ucwords($book_info['name']);
			$data['author_name'] 	= ucwords($book_info['author_name']);
			$data['sku'] 			= $book_info['id'];
			$data['image'] 			= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery') . 'new_year_gift_2025_v3.png';

			$html = $this->load->view('common/image_template', $data, true);

			// $dompdf = new Dompdf([]);
			$dompdf = new Dompdf([
				// 'debugLayout' 	=> true,
				// 'debugCss'		=> true,
				// 'debugPng'		=> true,
			]);

			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('dpi', 300);
			$dompdf->set_option('isHtml5ParserEnabled', true);

			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

			// $dompdf->setPaper(
			// 	'A4',
			// 	'portrait'
			// );

			$dompdf->setPaper(
				[
					0,
					0,
					269.3,
					382.69
				],
				'landscape'
			);

			$dompdf->render();

			$file_name = 'Book_' . $book_id . '.pdf';

			$dompdf->stream($file_name);
		}
	}

	public function generateFestivalGiftCard() {
		try {
			$this->load->model('book/Book_model', 'book_model');

			$pickup_location 	= !empty($this->input->get('pickup_location')) ? urldecode($this->input->get('pickup_location')) : '1,2';
			$start_data 		= !empty($this->input->get('start_date')) ? urldecode($this->input->get('start_date')) : '2024-12-21 16:59:59';
			$end_data 			= !empty($this->input->get('end_date')) ? urldecode($this->input->get('end_date')) : date('Y-m-d H:i:s');

			$rows = $this->db->query("SELECT order_product.version, order_product.order_id, `order`.`order_code`, order_product.product_id, order_product.quantity, `order`.pickup_location_id, `order`.status, `order`.date_added,
			`order`.site_id,
			`order`.currency_code,
			address.country,
			address.address,
			address.city,
			address.state
			FROM `order_product`
			JOIN `order` ON `order`.id = order_product.order_id
			JOIN address ON address.id = `order`.address_id
			WHERE `order`.status IN (1,2,8,10,21,93)
			AND `order`._deleted = 0
			AND `order`.order_type != 3
			AND `order`.`coupon_id` = 26134
			AND `order`.`pickup_location_id`IN (" . $pickup_location . ")
			AND `order`.date_added > '" . $start_data . "'
			AND `order`.date_added < '" . $end_data . "'")->result_array();

			// echo $this->db->last_query();die;

			$filteredRows = [];
	        $k = 1;
			foreach($rows as $row) {
				$quantity = $row['quantity'];

				for ($i = 0; $i < $quantity; $i++) {
					$book_info = $this->book_model->get($row['product_id']);
					$filteredRows[] = [
						'sn' 		=> $k,
						'version' 	=> $row['version'],
						'order_id' 	=> $row['order_id'],
						'order_code'=> $row['order_code'],
						'book_id' 	=> $row['product_id'],
						'book_name' => $book_info['name'] ?? '',
						'url'		=> 'https://cms.bribooks.com/home/getFestivalGiftCard/' . $row['product_id']
					];
					$k++;
				}
			}

			$filename = 'sample_festival_gift_card_' . date('Y_m_d_H_i_s') . '.csv';

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

			$headers = ['sn', 'version', 'order_id', 'order_code', 'book_id', 'book_name', 'url'];

			if (!$headers) {
				exit($this->lang->line('error_empty'));
			}

			$fp = fopen('php://output', 'w');

			$this->writeRowToCsv($filteredRows, $fp, $headers);

			fclose($fp);

			exit();

		} catch (\Throwable $th) {
			log_message('error', $th->getMessage());
		}
	}

	public function getBusinessCard($book_id = 0, $event_id = 0) {
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');

		if (!empty($book_info = $this->book_model->get($book_id))) {
			$book_info = $this->book_version_model->getByVersion($book_info['id'], $book_info['version']);

			$data['book_name'] 		= ucwords($book_info['name']);
			$data['author_name'] 	= ucwords($book_info['author_name']);
			$data['sku'] 			= $book_info['book_id'];
			// $data['image'] 			= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery') . 'new_year_gift_2025_v3.png';
			$data['cover_image'] 	= $this->config->item('cloudfront_url') . 'public/' . $book_info['cover_image'];
			// $data['front_image'] 	= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery') . 'business_card_front.jpg';
			// $data['inside_image'] 	= $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery') . 'business_card_inside.jpg';

			$data['front_image'] 	= base_url('assets/images/business_card_front.jpg');
			$data['inside_image'] 	= base_url('assets/images/business_card_inside.jpg');
			$data['logo'] 			= sprintf($this->config->item('s3_base_url') . $this->config->item('s3_user_gallery') . 'Business_Card/logo_%d.png', (int)$event_id);
			$data['qr_code']		= base_url(generateQrCode(USER_URL . 'bookstore/' . $book_info['slug'], 20, 2, 'uploads/pdfs/qrcode_' . $book_info['slug'] . '.png'));

			$data['width'] 			= 255.118;
			$data['height'] 		= 155.906;

			$html = $this->load->view('common/business_card', $data, true);

			// echo $html; die;

			// $dompdf = new Dompdf([]);
			$dompdf = new Dompdf([
				// 'debugLayout' 	=> true,
				// 'debugCss'		=> true,
				// 'debugPng'		=> true,
			]);

			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('dpi', 300);
			$dompdf->set_option('isHtml5ParserEnabled', true);

			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

			// $dompdf->setPaper(
			// 	'A4',
			// 	'portrait'
			// );

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

			$file_name = 'Book_businees_card_' . $book_id . '.pdf';

			$dompdf->stream($file_name);
		}
	}

	public function checkCertificateGen($order_id = 0, $alert = false) {
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');

		$event_id = 0;

		$this->load->library('GenericCertificate_lib');

		$this->genericcertificate_lib->createCertificate($order_id, false);



		if (
			empty($order_id) ||
			empty($order_info = $this->order_model->get($order_id)) ||
			in_array($order_info['status'], [0, 91, 92])
		) {
			echo 'invalid order'; die;
		}

		if (empty($order_products = $this->order_product_model->get_all([
			'order_id' =>  $order_id
		])['rows'] ?? [])) {
			echo 'empty order products'; die;
		}

		foreach ($order_products as $order_product) {
			$book_info = $this->book_model->get($order_product['product_id']);

			if (
				empty($book_info) ||
				empty($sold = $this->order_model->getTotalProductsByProductId($book_info['id']))
			) {
				echo "empty book info";
				continue;
			}

			if (empty($author_info = $this->student_model->get($book_info['user_id'])))  {
				echo "empty author info";
				continue;
			}

			if (!empty($event_book_info = $this->event_book_model->get_all(['book_id' => (int)$book_info['id']])['rows'][0] ?? [])) {
				if (
					!empty($event_info = $this->event_model->get($event_book_info['event_id'])) &&
					strtotime($event_info['selling_end_date']) > time()
				) {
					$event_id = $event_info['id'];
				} else {
					echo "empty event info";
					continue;
				}
			}

			$certificate_templates = $this->certificate_template_model->get_all([
				'country_code'	=> strtolower(get_author_currency_code($author_info['id'])) === 'inr' ? 'IN' : 'GE',
				'event_id'		=> (int)$event_id,
				'status'		=> 1,
				'sort'			=> 'certificate_template.book_sold',
				'order'			=> 'ASC',
			])['rows'] ?? [];

			if (empty($certificate_templates))  {
				echo "empty certificate templates";
				continue;
			}

			foreach ($certificate_templates as $key => $template) {
				if ($sold >= $template['book_sold']) {
					$certificate_key = sprintf('%s_user_%s_%s', $template['type'], $book_info['user_id'], $book_info['id']);

					$certificate_info = $this->certificate_model->get_all([
						'book_id'				=> $book_info['id'],
						'event_id'				=> $event_id,
						'user_id'				=> $book_info['user_id'],
						'name'					=> $certificate_key,
					])['rows'][0] ?? [];

				echo 'ExistingCertificate:: ' ;
				pr($certificate_info);

					if (empty($certificate_info)) {
						echo "NEW CERT";
						pr([
							'site_id'					=> $author_info['site_id'],
							'event_id'					=> $event_id,
							'book_id'					=> $book_info['id'],
							'user_id'					=> $book_info['user_id'],
							'type'						=> $template['type'],
							'certificate_template_id'	=> $template['id'],
							'achievement'				=> $template['achievement'],
							'unique_id'					=> $template['id'],
							'name'						=> $certificate_key,
							'image'						=> $certificate_key,
						]);
					}
				}
			}
		}
	}

	public function getAuthorCertificatesTest() {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('ranking/RankingGeneral_model', 'ranking_general_model');
		$data = [
			'achievement' => 1,
			'event_id' => 21,
			'book_id' => 900151,
			'user_id' => 698289,
		];

		if (!$data['user_id']) {
			echo 'please login';
			return;
		}

		if (
			$data['event_id'] &&
			empty($event_info = $this->event_model->get($data['event_id']))
		) {
			echo 'invalid event';
			return;
		}

		$author_info 	= $this->student_model->get($data['user_id']);

		if ($data['achievement'] == 2) {
			$site_info = $this->site_model->get($author_info['site_id']);

			if (!empty($site_info) && strlen($site_info['name']) > 72) {
				$strcount 		= strlen($site_info['name']);
				$school_name 	= substr($site_info['name'],0,(72 - $strcount)) . '...';
			} else {
				$school_name 	= $site_info['name'];
			}
		}

		$book_info 		= $this->book_model->get($data['book_id']);

		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

		$certificate_templates = $this->certificate_template_model->get_all([
			'event_id'		=> (int)$data['event_id'],
			'country_code'	=> strtolower(get_author_currency_code($author_info['id'])) === 'inr' ? 'IN' : 'GE',
			'achievement'	=> (int)$data['achievement'],
			'status'		=> 1,
			'sort'			=> 'certificate_template.book_sold',
			'order'			=> 'ASC',
		])['rows'] ?? [];

		// echo $this->db->last_query();

		if (!empty($event_info['id'])) {
			$sold = $this->event_order_model->getTotalSoldByBook($event_info['id'], $book_info['id']);
		} else {
			$sold = $this->order_model->getTotalProductsByProductId($book_info['id']);
		}

		// pr($certificate_templates);
		$result['certificates'] = [];

		foreach ($certificate_templates as $key => $template) {
			if (!empty($template['challenge_id'])) {
				$model = sprintf('ranking_%s_model', $template['challenge_type']);

				$this->load->model(sprintf('ranking/Ranking%s_model', ucwords($template['challenge_type'])), $model);

				$book_league_info = $this->{$model}->get_all([
					'challenge_id'	=> $template['challenge_id'],
					'book_id'		=> $data['book_id']
				])['rows'][0] ?? [];

				echo '$book_league_info_' . $template['challenge_id'];
				pr($book_league_info);

				if (empty($book_league_info)) continue;
			}

			$certificate = $this->certificate_model->get_all([
				'user_id' 					=> $data['user_id'],
				'book_id'					=> $data['book_id'],
				'event_id'					=> $data['event_id'] ?? 0,
				'achievement'				=> (int)$data['achievement'],
				'certificate_template_id' 	=> $template['id'],
			])['rows'][0] ?? [];

			$unlocked = !empty($certificate['id']);

			if (!empty($template['has_isbn']) && empty($book_info['isbn'])) {
				$unlocked = false;
			}

			if (!empty($event_info) && strtotime($event_info['selling_end_date']) < time() && !$unlocked) {
				echo 'invalid selling date';
				continue;
			}

			if (
				$unlocked &&
				empty(@getimagesize($this->config->item('cloudfront_url') .   $this->config->item('s3_author_certificates') . $certificate['image'] . '.png'))
			) {

			}

			$result['certificates'][] = $unlocked ? [
				'template_id'	=> $template['id'],
				'unlock' 		=> $unlocked,
				'id' 			=> $certificate['id'],
				'slug' 			=> str_replace(['_cert', '_'], ['_certificate', '-'], $certificate['type']) . '-' . $certificate['id'],
				'name' 			=> _l(str_replace('_cert', '_certificate', $certificate['type'])),
				'image' 		=> $this->config->item('cloudfront_url') .   $this->config->item('s3_author_certificates') . $certificate['image'] . '.png',
				'pdf' 			=> USER_INVOICE_URL . 'api/downloadAuthorCertfifcate/?code=' . urlencode($certificate['unique_id']),
			] : [
				'template_id'	=> $template['id'],
				'unlock' 		=> $unlocked,
				'message'		=> 'jhbcjdbjc',
			];
		}
		pr($result);
	}

	public function globalApiTest() {
		$this->load->model('event/LeadVerificationCode_model', 'lead_verification_code_model');
		$this->load->model('user/Lead_model', 'lead_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventUser_model', 'event_user_model');

		// $data = [
		// 	"lid" => "1234322",
		// 	"code" => "f4bba7297335f13ebb2a44de6f9fbe575cc1a749",
		// 	"type" => "email",
		// 	"timezone" => -330,
		// 	"captcha_token" => "",
		// 	"utm_source" => "NYAF2024-StudentUS",
		// 	"utm_medium" => "desktop",
		// ];


		$data = [
			"lid" => "1234322",
			"code" => "f4bba7297335f13ebb2a44de6f9fbe575cc1a749",
			"type" => "email",
		];

		$lead_info = $this->lead_model->get($data['lid']);

		if (!empty($valid_student = $this->lead_verification_code_model->get_all([
			'lead_id'   => $data['lid'],
			'code'      => $data['code'],
			'type'      => 'student'
		])['rows'][0] ?? '') && !empty($lead_info)) {
			$user_info = $this->db->get_where('users', [
				'email' => $lead_info['email'],
				'_deleted'	=> 0
			])->row_array();

			if (!empty($user_info)) {
				if (
					!empty($lead_info['event_id']) &&
					!empty($user_info['id']) &&
					!empty($this->event_user_model->getEventUserByUserId($lead_info['event_id'], $user_info['id']))
				) {
					echo _l( 'You_are_already_registered_in_event');
					return;
				}

				$explode = explode(' ', ($lead_info['name'] ?? ''), 2);

				$first_name = array_shift($explode);
				$last_name = array_shift($explode);

				$password = uniqid();
				$verification_code = sha1(md5(($user_info['username'] ?? '') . $password . $this->config->item('password_salt')));

				// $this->student_model->edit($user_info['id'], [
				// 	'first_name'	=> $first_name ?? '',
				// 	'last_name'		=> $last_name ?? '',
				// 	'parent_name'	=> $lead_info['parent_name'] ?? '',
				// 	'mobile'		=> $lead_info['mobile'] ?? $user_info['mobile'],
				// 	'email'			=> $lead_info['email'] ?? $user_info['email'],
				// 	// 'source'		=> $lead_info['source'] ?? '',
				// 	'country_id'	=> (int)($lead_info['country_id'] ?? 0),
				// 	'state_id'		=> (int)($lead_info['state_id'] ?? 0),
				// 	'city_id'		=> (int)($lead_info['city_id'] ?? 0),
				// 	'grade_id'		=> $lead_info['grade'] ?? $user_info['grade'],
				// 	'section_id'	=> $lead_info['section'] ?? $user_info['grade'],
				// 	'grade'			=> $lead_info['grade'] ?? $user_info['grade'],
				// 	'section'		=> $lead_info['section'] ?? $user_info['grade'],
				// 	'role_id'		=> 2,
				// 	'site_id'		=> (int)$lead_info['site_id'],
				// 	'status'		=> 1,
				// 	'ip'			=> $this->input->ip_address(),
				// 	'timezone'		=> $lead_info['timezone'] ?? '',
				// 	'verification_code'	=> $verification_code,
				// 	'mobile_verified'	=> ($lead_info['type'] ?? 'mobile') == 'mobile',
				// 	'email_verified'	=> ($lead_info['email'] == $user_info['email'])? $user_info['email_verified'] : 0,
				// ]);

				$user_id = $user_info['id'];

				echo 'EXIST USER _' . $user_id;
				pr($user_info);

				// $this->alert_model->signup($user_id, $lead_info['utm_medium'] ?? '', $lead_info['event_id'] ?? 0);

				// self::_formatUser($user_info['id']);
				// self::_addToken($this->student_model->get($user_info['id']));
			} else {
				$lead_info['source'] = $lead_info['utm_medium'] ?? '';

				// $user_id = self::_doLogin($lead_info + [
				// 	'type' => $this->input->post('type')
				// ]);

				self::_doLoginTest($lead_info[]);

				echo 'NEW USER';
				pr($lead_info);
			}

			echo 'LAST STAGE ' . $user_id;


			// $this->lead_model->edit($lead_info['id'], [
			// 	'student_id'		=> (int)$user_id,
			// 	'mobile_verified' 	=> $this->input->post('type') == 'mobile' ? 1 : 0,
			// 	'email_verified' 	=> $this->input->post('type') == 'email' ? 1 : 0
			// ]);

			// CI_Events::trigger('access_log', [
			// 	'module'	=> 'event_user_signup_' . (int)$this->input->post('event_id')
			// ]);

			// add to event
			// if (
			// 	!empty($lead_info['event_id']) &&
			// 	$user_id &&
			// 	empty($this->event_user_model->getEventUserByUserId($lead_info['event_id'], $user_id))
			// ) {

			// 	$event_user_id = $this->event_user_model->add([
			// 		'event_id'	=> (int)$lead_info['event_id'],
			// 		'user_id'	=> (int)$user_id,
			// 	]);
			// }

			echo 'you_are_successfully_verified';

			$search_data = [
				'status'			=> 1,
				'_deleted'			=> 0,
			];

			$this->db->group_start();
			$this->db->where('role_id', 2);
			$this->db->or_where('role_id', 9);
			$this->db->or_where('role_id', 3);
			$this->db->or_where('role_id', 11);
			$this->db->group_end();

			$search_data = [
				'status'			=> 1,
				'_deleted'			=> 0,
				'email_verified'	=> 1,
				'email'				=> ''
			];

			$new_user_info = $this->db->get_where('users', $search_data)->row_array();

			pr([]);
			echo 'new_user_info';
			pr($new_user_info);
		} else {
			echo 'invalid_url';
		}
	}

	private function _doLoginTest($data = [], $alert = true) {
		echo '_doLoginTest';
		pr($data);
		$search_data = [
			'status'			=> 1,
			'_deleted'			=> 0,
		];

		if (in_array(($data['type'] ?? 'mobile'), ['mobile', 'whatsapp'])) {
			$search_data['mobile'] 			= $data['mobile'];
			$update_data['mobile_verified'] = 1;
		} else {
			$search_data['email'] 			= $data['email'];
			$update_data['email_verified'] 	= 1;
		}

		$this->db->group_start();
		$this->db->where('role_id', 2);
		$this->db->or_where('role_id', 9);
		$this->db->or_where('role_id', 3);
		$this->db->or_where('role_id', 11);
		$this->db->group_end();

		if ($user_info = $this->db->get_where('users', $search_data)->row_array()) {

			// $this->user_model->edit($user_info['id'], $update_data);

			// if ($user_info['role_id'] == 9) {
			// 	self::_formatSchool($user_info['id']);
			// } elseif ($user_info['role_id'] == 3) {
			// 	self::_formatTeacher($user_info['id']);
			// } elseif ($user_info['role_id'] == 11) {
			// 	self::_formatReviewer($user_info['id']);
			// } else {
			// 	self::_formatUser($user_info['id']);
			// }

			// self::_addToken($user_info);

			echo 'search-data';
			pr($search_data);
			echo 'do-login-exist-user';
			pr($user_info);
		} else {
			$site_id = $data['site_id'] ?? 0;

			if (empty($site_id)) {
				$site_id = $this->config->item('default_site_id');
			}

			$explode 	= explode(' ', ($data['name'] ?? ''), 2);
			$first_name = array_shift($explode);
			$last_name 	= array_shift($explode);

			if (!empty($data['mobile'])) {
				$username = strtolower(trim(
					substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $data['name']), 0, 4) .
					substr($data['mobile'], -4)
				));
			} else {
				$this->db->select_max('id');
				$last_user_id = $this->db->get('users')->row_array()['id'];
				$last_user_id++;

				$last_user_id = sprintf('%06d', $last_user_id);

				$username = strtolower(trim(
					substr(preg_replace(['/[^\w\s]/', '/\s+/'], '', $data['name']), 0, 2) .
					substr($last_user_id, -6)
				));
			}

			$password 			= uniqid();
			$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
			$verification_code 	= sha1(md5($username . $password . $this->config->item('password_salt')));

			$user_data = [
				'first_name'	=> $first_name ?? '',
				'last_name'		=> $last_name ?? '',
				'parent_name'	=> $data['parent_name'] ?? '',
				'slug'			=> get_user_slug($username),
				'username'		=> $username,
				'password'		=> $encoded_password,
				'mobile'		=> $data['mobile'] ?? '',
				'email'			=> $data['email'] ?? '',
				'source'		=> $data['source'] ?? '',
				'country_id'	=> (int)($data['country_id'] ?? 0),
				'state_id'		=> (int)($data['state_id'] ?? 0),
				'city_id'		=> (int)($data['city_id'] ?? 0),
				'grade_id'		=> $data['grade'],
				'section_id'	=> $data['section'],
				'grade'			=> $data['grade'],
				'section'		=> $data['section'],
				'role_id'		=> 2,
				'site_id'		=> (int)$site_id,
				'status'		=> 1,
				'location'		=> $data['location'] ?? '',
				'referral_code'	=> mb_strtoupper(uniqid()),
				'verification_code'	=> $verification_code,
				'ip'			=> $this->input->ip_address(),
				'timezone'		=> $data['timezone'] ?? '',
				'mobile_verified'	=> in_array(($data['type'] ?? 'mobile'), ['mobile', 'whatsapp']),
				'email_verified'	=> in_array(($data['type'] ?? 'mobile'), ['email', 'email_link']),
				'parent_referral_id'=> (int)($data['parent_referral_id'] ?? 0)
			];

			// self::_formatUser($user_id);
			// self::_addToken($this->student_model->get($user_id));

			echo 'NEW ADD USER FORM DO LOGIN';
			pr($user_data);
		}
	}

	public function buildQualificationPendingRank($site_id = 0, $event_id = 21) {
		if (empty($site_id)) return;
		if (empty($event_id)) return;

		$this->load->model('event/EventBookQualificationPending_model', 'event_book_qualification_pending_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->library('Ranking_lib', 'ranking_lib');


		$rows = $this->db->query("SELECT event_book.event_id, event_book.book_id, users.site_id
		,(SELECT `order_id`
		FROM `event_order`
		WHERE `book_id` = event_book.book_id AND `_deleted` = '0'
		ORDER BY `order_id` DESC
		LIMIT 1) as order_id
		FROM `event_book`
		JOIN book ON book.id = event_book.book_id
		JOIN users ON users.id = book.user_id
		WHERE event_book.`event_id` = '21'
		AND event_book.`_deleted` = '0'
		AND book.`_deleted` = '0'
		AND users.site_id = " . $site_id)->result_array();

		pr($rows);

		foreach($rows as $row) {
		    $book_info = $this->book_model->get($row['book_id']);
		    $author_info = $this->student_model->get($book_info['user_id']);
			$no_sold = $this->event_order_model->getTotalSoldByBook($event_id, $book_info['id']);


			if ($qualified_user_info = $this->event_book_qualification_pending_model->get_all([
				'event_id'		=> (int)$event_id,
				'book_id'		=> (int)$row['id'],
			])['rows'][0] ?? []) {
				$this->event_book_qualification_pending_model->edit($qualified_user_info['id'], [
					'site_id'		=> (int)$author_info['site_id'] ?? 0,
					'city_id'		=> (int)$author_info['city_id'] ?? 0,
					'state_id'		=> (int)$author_info['state_id'] ?? 0,
					'country_id'	=> (int)$author_info['country_id'] ?? 0,
					'book_name'		=> $book_info['name'] ?? '',
					'author_name'	=> $book_info['author_name'] ?? '',
					'book_slug'		=> $book_info['slug'] ?? '',
					'book_image'	=> $book_info['cover_image'] ?? '',
					'author_image'	=> $book_info['author_image'] ?? '',
					'score'			=> (int)$no_sold,
				]);
			} else {

				// $this->event_book_qualification_pending_model->add([
				// 	'event_id'		=> (int)$event_id,
				// 	'site_id'		=> (int)$author_info['site_id'] ?? 0,
				// 	'city_id'		=> (int)$author_info['city_id'] ?? 0,
				// 	'state_id'		=> (int)$author_info['state_id'] ?? 0,
				// 	'country_id'	=> (int)$author_info['country_id'] ?? 0,
				// 	'user_id'		=> (int)$book_info['user_id'] ?? 0,
				// 	'book_id'		=> (int)$book_info['id'] ?? 0,
				// 	'book_name'		=> $book_info['name'] ?? '',
				// 	'author_name'	=> $book_info['author_name'] ?? '',
				// 	'book_slug'		=> $book_info['slug'] ?? '',
				// 	'book_image'	=> $book_info['cover_image'] ?? '',
				// 	'author_image'	=> $book_info['author_image'] ?? '',
				// 	'score'			=> (int)$no_sold,
				// ]);
			}

			if (!empty($row['order_id'])) {
				$this->ranking_lib->updateRank($row['order_id']);
			}
		}
	}

	public function generateGenreLeagueCertificate() {
		return ;
		$genre_id = 112;
		$data = [
			'certificate_template_id' => 240,
			'genre_id' 		=> $genre_id,
			'event_id' 		=> 21,
			'challenge_id' 	=> 1,
			'type' 			=> 'genre',
			'limit' 		=> 10,
		];

		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

		if (empty($data) || empty($data['event_id']) || empty($data['challenge_id']) || empty($data['type']) || empty($data['limit']) || empty($data['certificate_template_id'])) return;

		$model_file_path = sprintf(APPPATH . 'models/ranking/Ranking%s_model.php', ucwords($data['type']));

		if (file_exists($model_file_path)) {
			$this->load->model(sprintf('ranking/Ranking%s_model', ucwords($data['type'])), sprintf('ranking_%s_model', strtolower($data['type'])));

			$model_name = sprintf('ranking_%s_model', strtolower($data['type']));

			$ranks = $this->{$model_name}->get_all([
				'event_id' 		=> $data['event_id'],
				'challenge_id' 	=> $data['challenge_id'],
				'genre_id' 		=> $genre_id,
				'start'			=> 0,
				'limit' 		=> $data['limit'] ?? 100,
			])['rows'] ?? [];

			pr($ranks);

			if (empty($template_info = $this->certificate_template_model->get($data['certificate_template_id']))) return;

			// foreach ($ranks as $key => $rank) {
			// 	if (empty($certificate_info = $this->certificate_model->get_all([
			// 		'book_id' 					=> $rank['book_id'],
			// 		'certificate_template_id' 	=> $template_info['id'],
			// 	])['rows'] ?? [])) {
			// 		$certificate_key = sprintf('%s_user_%s_%s', $template_info['type'], $rank['user_id'], $rank['book_id']);

			// 		$this->certificate_model->add([
			// 			'site_id'					=> 1,
			// 			'event_id'					=> $data['event_id'],
			// 			'book_id'					=> $rank['book_id'],
			// 			'user_id'					=> $rank['user_id'],
			// 			'rank'						=> $key + 1,
			// 			'type'						=> $template_info['type'],
			// 			'certificate_template_id'	=> $template_info['id'],
			// 			'achievement'				=> $template_info['achievement'],
			// 			'unique_id'					=> $template_info['id'],
			// 			'name'						=> $certificate_key,
			// 			'image'						=> $certificate_key,
			// 		]);
			// 	}
			// 	// break;
			// }
		}
	}

	public function genreMessage_1($genre_id = 0, $temp_id = 0) {
		return;
		$whatsapp_template_id = '2015604168918166';


		if (empty($genre_id)) return;

		if (empty($temp_id)) return;

		$this->load->model('common/LeagueTemplate_model', 'league_template_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('design/Category_model', 'category_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('Alert_model', 'alert_model');

		$this->load->model('ranking/RankingGenre_model', 'ranking_genre_model');

		$ranks = $this->ranking_genre_model->get_all([
			'event_id' 		=> 21,
			'challenge_id' 	=> 1,
			'genre_id' 		=> $genre_id,
			'start'			=> 0,
			'limit' 		=> 1,
		])['rows'] ?? [];

		pr($ranks);

		foreach ($ranks as $row) {
			if(empty($author_info = $this->student_model->get($row['user_id']))) continue;

			if(empty($book_info = $this->book_model->get($row['book_id']))) continue;

			if(empty($category_info = $this->category_model->get($row['genre_id']))) continue;



			$cert_url = USER_URL . '/account/mycertificates';

			$student_url = sprintf('https://www.bribooks.com/imagerequest?uid=%s&code=%s', $author_info['id'], $author_info['verification_code']);

			$whatsapp_parameter = [
				$category_info['name'],
				$book_info['author_name'],
				$category_info['name'],
				'https://www.bribooks.com/account/mycertificates?active=league',
				$student_url
			];

			if (!empty($certificate_info = $this->certificate_model->get_all([
				'book_id' 					=> $row['book_id'],
				'certificate_template_id' 	=> $temp_id,
			])['rows'][0] ?? '')) {
				$cert_url = 'https://youbooks-storage-5fd6173683748-webdev.s3.amazonaws.com/public/AuthorCertificate/' . $certificate_info['name'] . '.png';
			}

			$subject = 'Yayy! You Are the #1 Best-Selling Author of the "' . $category_info['name'] . '" Genre of NYAF—Congratulations!';

			$content = '<p>Dear ' . $book_info['author_name'] . ',</p>
			<p>We are so proud of you! You have achieved the ultimate milestone in creativity—earning the <strong>#1 Best-Selling Author</strong> position in the <strong>' . $category_info['name'] . '</strong> genre of NYAF. <strong>Congratulations!</strong></p>
			<p><strong>Here are your rewards:<strong></p>
			<ul>
			<li><strong>Exclusive Invitation:</strong> You are invited to the National Awards Exhibition event, where you will win all the awards listed in the link below: <a href="https://www.yaf.bribooks.com/india/2024/student#genre-awards">https://www.yaf.bribooks.com/india/2024/student#genre-awards</a></li>
			<li><strong>Additional Awards (available before the event):</strong></li>
			<ul>
				<li><strong>Best-Seller Certificate (Digital Version):</strong> <a href="' . $cert_url . '">(Click here to download)</a></li>
				<li><strong>The Hindu Print Feature:</strong> A printed copy will be shipped to you.</li>
				<li><strong>Feature on Discovery Channel:</strong> Schedule and channel details will be shared soon.</li>
			</ul>
			</ul>
			<p><strong>Action Required:</strong></p>
			<p>Please upload a <strong>high-resolution close-up photo</strong> for media features using the link below by <strong>31st January 2025</strong>:<br />
			<a href="' . $student_url . '">' . $student_url . '</a></p>
			<p><strong>Note:</strong> The media feature is subject to us receiving your photo. For reference, the required resolution and a sample photo are attached.</p>
			<p>For any queries regarding details not listed above, please contact us at <a href="mailto:support@bribooks.com">support@bribooks.com</a>.</p>
			<p><strong>Congratulations once again!</strong></p>
			<p>Best regards,<br />
			Author Support Team<br />
			NYAF India<br />
			2024-25</p>';



			$data['title']		  	= $subject;
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['content']		= $content;
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$message				= $this->load->view('common/mail/templates/site/general', $data, true);

			$attachment 			= [];

			if (ENVIRONMENT === 'production') {
				$email  = $author_info['email'];
				$mobile = $author_info['mobile'];
				$cc 	= 'communication@bribooks.com';
			}
		}
	}

	public function genreMessage_2_5($genre_id = 0, $temp_id = 0) {
		return;
		$whatsapp_template_id = '937249018549024';


		if (empty($genre_id)) return;

		if (empty($temp_id)) return;

		$this->load->model('common/LeagueTemplate_model', 'league_template_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('design/Category_model', 'category_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('Alert_model', 'alert_model');

		$this->load->model('ranking/RankingGenre_model', 'ranking_genre_model');

		$ranks = $this->ranking_genre_model->get_all([
			'event_id' 		=> 21,
			'challenge_id' 	=> 1,
			'genre_id' 		=> $genre_id,
			'start'			=> 1,
			'limit' 		=> 4,
		])['rows'] ?? [];

		pr($ranks);


		foreach ($ranks as $row) {
			if(empty($author_info = $this->student_model->get($row['user_id']))) continue;

			if(empty($book_info = $this->book_model->get($row['book_id']))) continue;

			if(empty($category_info = $this->category_model->get($row['genre_id']))) continue;



			$cert_url = USER_URL . '/account/mycertificates';

			$student_url = sprintf('https://www.bribooks.com/imagerequest?uid=%s&code=%s', $author_info['id'], $author_info['verification_code']);

			$whatsapp_parameter = [
				$book_info['author_name'],
				$category_info['name'],
				'https://www.bribooks.com/account/mycertificates?active=league',
				$student_url
			];

			if (!empty($certificate_info = $this->certificate_model->get_all([
				'book_id' 					=> $row['book_id'],
				'certificate_template_id' 	=> $temp_id,
			])['rows'][0] ?? '')) {
				$cert_url = 'https://youbooks-storage-5fd6173683748-webdev.s3.amazonaws.com/public/AuthorCertificate/' . $certificate_info['name'] . '.png';
			}

			$subject = 'Congratulations! You’re a TOP 5 Best-Selling Author in the "' . $category_info['name'] . '" Genre of NYAF!';

			$content = '<p>Dear ' . $book_info['author_name'] . ',</p>
			<p>We are so proud of you! You have achieved the penultimate milestone in creativity—winning the <strong>TOP 5 Best-Selling Author position</strong> in the <strong>' . $category_info['name'] . '</strong> genre of NYAF. <strong>Congratulations!</strong></p>
			<p>Here are your rewards:</p>
			<ul>
			<li><strong>Best-Seller Certificate:</strong> <a href="' . $cert_url . '">(Click here to download)</a></li>
			<li><strong>The Hindu Print Feature:</strong> A printed copy will be shipped to you.</li>
			<li><strong>Feature on Discovery Channel:</strong> Schedule and channel details will be shared soon.</li>
			<li><strong>The Genre League Trophy</strong></li>
			</ul>
			<p><strong>Action Required:</strong></p>
			<p>Please <strong>upload a high-resolution close-up photo</strong> for the media features using the link below by <strong>31st January 2025</strong>. Note that the media feature is subject to us receiving your photo:<br />
			<a href="' . $student_url . '">' . $student_url . '</a></p>
			<p>For reference, the required resolution and a sample photo are attached.</p>
			<p>For any queries regarding details not listed above, please contact us at <a href="mailto:support@bribooks.com">support@bribooks.com</a>.</p>
			<p>Congratulations again!</p>
			<p>Best regards,<br />
			Author Support Team<br />
			NYAF India<br />
			2024-25</p>';



			$data['title']		  	= $subject;
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['content']		= $content;
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$message				= $this->load->view('common/mail/templates/site/general', $data, true);

			$attachment = [];

			if (ENVIRONMENT === 'production') {
				$email  = $author_info['email'];
				$mobile = $author_info['mobile'];
				$cc 	= 'communication@bribooks.com';
			}
		}
	}

	public function genreMessage_6_10($genre_id = 0, $temp_id = 0) {
		return;
		$whatsapp_template_id = '1244077459988645';

		if (empty($genre_id)) return;

		if (empty($temp_id)) return;

		$this->load->model('common/LeagueTemplate_model', 'league_template_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('design/Category_model', 'category_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('Alert_model', 'alert_model');

		$this->load->model('ranking/RankingGenre_model', 'ranking_genre_model');

		$ranks = $this->ranking_genre_model->get_all([
			'event_id' 		=> 21,
			'challenge_id' 	=> 1,
			'genre_id' 		=> $genre_id,
			'start'			=> 5,
			'limit' 		=> 5,
		])['rows'] ?? [];

		pr($ranks);

		foreach ($ranks as $row) {
			if(empty($author_info = $this->student_model->get($row['user_id']))) continue;

			if(empty($book_info = $this->book_model->get($row['book_id']))) continue;

			if(empty($category_info = $this->category_model->get($row['genre_id']))) continue;



			$cert_url = USER_URL . '/account/mycertificates';

			$student_url = sprintf('https://www.bribooks.com/imagerequest?uid=%s&code=%s', $author_info['id'], $author_info['verification_code']);

			$whatsapp_parameter = [
				$book_info['author_name'],
				$category_info['name'],
				'https://www.bribooks.com/account/mycertificates?active=league',
				$student_url
			];

			if (!empty($certificate_info = $this->certificate_model->get_all([
				'book_id' 					=> $row['book_id'],
				'certificate_template_id' 	=> $temp_id,
			])['rows'][0] ?? '')) {
				$cert_url = 'https://youbooks-storage-5fd6173683748-webdev.s3.amazonaws.com/public/AuthorCertificate/' . $certificate_info['name'] . '.png';
			}

			$subject = 'Congratulations! You’re a TOP 10 Best-Selling Author in the "' . $category_info['name'] . '" Genre of NYAF!';

			$content = '<p>Dear ' . $book_info['author_name'] . ',</p>
			<p>We are so proud of you! You have achieved the penultimate milestone in creativity—winning the <strong>TOP 10 Best-Selling Author position</strong> in the <strong>' . $category_info['name'] . '</strong> genre of NYAF. <strong>Congratulations!</strong></p>
			<p>Here are your rewards:</p>
			<ul>
			<li><strong>Best-Seller Certificate:</strong> <a href="' . $cert_url . '">(Click here to download)</a></li>
			<li><strong>Feature on Discovery Channel:</strong> Schedule and channel details will be shared soon.</li>
			<li><strong>The Genre League Trophy</strong></li>
			</ul>
			<p><strong>Action Required:</strong></p>
			<p>Please upload a <strong>high-resolution close-up photo</strong> for the media features using the link below by <strong>31st January 2025</strong>. Note that the media feature is subject to us receiving your photo:<br />
			<a href="' . $student_url . '">' . $student_url . '</a></p>
			<p>For reference, the required resolution and a sample photo are attached.</p>
			<p>For any queries regarding details not listed above, please contact us at <a href="mailto:support@bribooks.com">support@bribooks.com</a>.</p>
			<p>Congratulations again!</p>
			<p>Best regards,<br />
			Author Support Team<br />
			NYAF India<br />
			2024-25</p>';



			$data['title']		  	= $subject;
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['content']		= $content;
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$message				= $this->load->view('common/mail/templates/site/general', $data, true);

			$attachment = [];

			if (ENVIRONMENT === 'production') {
				$email  = $author_info['email'];
				$mobile = $author_info['mobile'];
				$cc 	= 'communication@bribooks.com';
			}
		}
	}

	public function genreMessage_11_40($genre_id = 0, $temp_id = 0) {
		return;
		$whatsapp_template_id = '1766063413964512';

		if (empty($genre_id)) return;

		if (empty($temp_id)) return;

		$this->load->model('common/LeagueTemplate_model', 'league_template_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('design/Category_model', 'category_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('Alert_model', 'alert_model');

		$this->load->model('ranking/RankingGenre_model', 'ranking_genre_model');

		$ranks = $this->ranking_genre_model->get_all([
			'event_id' 		=> 21,
			'challenge_id' 	=> 1,
			'genre_id' 		=> $genre_id,
			'start'			=> 10,
			'limit' 		=> 30,
		])['rows'] ?? [];

		pr($ranks);

		foreach ($ranks as $row) {
			if(empty($author_info = $this->student_model->get($row['user_id']))) continue;

			if(empty($book_info = $this->book_model->get($row['book_id']))) continue;

			if(empty($category_info = $this->category_model->get($row['genre_id']))) continue;

			$cert_url = USER_URL . '/account/mycertificates';

			$student_url = sprintf('https://www.bribooks.com/imagerequest?uid=%s&code=%s', $author_info['id'], $author_info['verification_code']);

			$whatsapp_parameter = [
				$book_info['author_name'],
				$category_info['name']
			];

			if (!empty($certificate_info = $this->certificate_model->get_all([
				'book_id' 					=> $row['book_id'],
				'certificate_template_id' 	=> $temp_id,
			])['rows'][0] ?? '')) {
				$cert_url = 'https://youbooks-storage-5fd6173683748-webdev.s3.amazonaws.com/public/AuthorCertificate/' . $certificate_info['name'] . '.png';
			}

			$subject = 'Congratulations! You’re a TOP 40 Best-Selling Author in the "' . $category_info['name'] . '" Genre of NYAF!';

			$content = '<p>Dear ' . $book_info['author_name'] . ',</p>
			<p>We are so proud of you! You have achieved the penultimate milestone in creativity—winning the <strong>TOP 40 Best-Selling Author position</strong> in the <strong>' . $category_info['author_name'] . '</strong> genre of NYAF. <strong>Congratulations!</strong></p>
			<p>You have won <strong>The Genre League Trophy. We will ship it to you soon</strong>.</p>
			<p>For any queries regarding details not listed above, please contact us at <a href="mailto:support@bribooks.com">support@bribooks.com</a>.</p>
			<p>Congratulations again!</p>
			<p>Best regards,<br />
			Author Support Team<br />
			NYAF India<br />
			2024-25</p>';



			$data['title']		  	= $subject;
			$data['heading']		= '';
			$data['subheading']	 	= '';
			$data['subheading']		= '';
			$data['content']		= $content;
			$data['link']		   	= '';
			$data['link_text']	  	= '';
			$message				= $this->load->view('common/mail/templates/site/general', $data, true);

			$attachment = [];

			if (ENVIRONMENT === 'production') {
				$email  = $author_info['email'];
				$mobile = $author_info['mobile'];
				$cc 	= 'communication@bribooks.com';
			}

			// if (!empty($subject) && !empty($content)) {
			// 	$this->alert_model->email(
			// 		$email,
			// 		$subject,
			// 		$message,
			// 		[],
			// 		[$cc],
			// 		$attachment
			// 	);
			// }

			// if (!empty($whatsapp_template_id)) {
			// 	self::_sendWhatsappText(
			// 		$mobile,
			// 		[
			// 			'template'		=> $whatsapp_template_id,
			// 			'parameters'	=> $whatsapp_parameter,
			// 		]
			// 	);
			// }

		}
	}
}
