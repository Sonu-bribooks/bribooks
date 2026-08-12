<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('whatsapp');
load_trait('models/alert');

use Dompdf\Dompdf;

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

trait EventPrep {
	use CommonWhatsapp;

	public function generateLetterHead($school_id, $type = '', $book_id = 0) {
		if (!empty($school_info = $this->school_model->get($school_id))) {

			$reference_school   = !empty($this->input->get('reference_school')) ? urldecode($this->input->get('reference_school')) : '';
			$top_school_1	   	= !empty($this->input->get('top_school_1')) ? urldecode($this->input->get('top_school_1')) : '21K School';
			$top_school_2	   	= !empty($this->input->get('top_school_2')) ? ' & ' .urldecode($this->input->get('top_school_2')) : '';
			$head_name		  	= !empty($this->input->get('head_name')) ? urldecode($this->input->get('head_name')) : '';
			$head_first_name	= !empty($this->input->get('head_first_name')) ? urldecode($this->input->get('head_first_name')) : '';

			$parent_info		= $this->school_model->get($school_info['parent_id']);
			$city_info	  		= $this->city_model->get($school_info['city_id']);
			$state_info	 		= $this->state_model->get($school_info['state_id']);

			$school_name		= explode(',', $school_info['name'])[0] ?? '';
			$parent_school_name = !empty($this->input->get('group_name')) ? urldecode($this->input->get('group_name')) : $school_name;

			$designation 		= !empty($this->input->get('designation')) ? urldecode($this->input->get('designation')) : '';

			$authorized_person  = !empty($school_info['authorized_person']) ? $school_info['authorized_person'] : 'School Leader';

			$school_name_array  = explode(',', $school_info['name']);
			$new_school_name	= $school_name_array[0] . (!empty($school_name_array[1]) ? (', ' . $school_name_array[1]) : (', ' . $city_info['name']));
			$target_school 		= mb_strtoupper($new_school_name);

			$spoc_name   = !empty($this->input->get('spoc_name')) ? urldecode($this->input->get('spoc_name')) : 'School Leader';
			$book_name   = !empty($this->input->get('book_name')) ? urldecode($this->input->get('book_name')) : '';
			$author_name = !empty($this->input->get('author_name')) ? urldecode($this->input->get('author_name')) : '';


			$data = [
				'school_id'					 	=> $school_info['id'],
				'school_name'				   	=> ucwords(strtolower($new_school_name)),
				'spoc_name'				   		=> ucwords(strtolower($spoc_name)),
				'book_name'				   		=> ucwords(strtolower($book_name)),
				'author_name'				   	=> ucwords(strtolower($author_name)),
				'authorized_person'			 	=> ucwords(strtolower($authorized_person)),
				'head_name'					 	=> $head_name,
				'head_first_name'			   	=> $head_first_name,
				'alternate_authorized_person'   => $school_info['alternate_authorized_person'] ?? '',
				'parent_school_name'			=> mb_strtoupper($parent_school_name),
				'reference_school'			  	=> mb_strtoupper(urldecode($reference_school)),
				'target_school'				 	=> $target_school,
				'network_name'				  	=> mb_strtoupper($parent_school_name),
				'top_school_1'				  	=> mb_strtoupper($top_school_1),
				'top_school_2'				  	=> mb_strtoupper($top_school_2),
				'designation'				  	=> $designation,
				'city'						  	=> mb_strtoupper($city_info['name']),
				'state'						 	=> mb_strtoupper($state_info['name']),
				'student_url' 					=> USER_YAF_URL . 'india/2024/school/signup/' . $school_info['id'],
				'qrcode_url' 					=> base_url(generateQrCode('www.yaf.bribooks.com/india/2024/school/signup/' . $school_id . '?utm_source=nyaf2024_ivtn', 20, 2, sprintf('uploads/test/testqr_%s.png', $school_id)))
			];

			if ($type == 'podcast_school') {
				$school_name_array  = explode(',', $school_info['name']);
				$new_school_name	= $school_name_array[0] . (!empty($school_name_array[1]) ? (', ' . $school_name_array[1]) : (', ' . $city_info['name']));

				$data['school_name'] 	= mb_strtoupper($new_school_name);
				$data['target_school'] 	= mb_strtoupper($new_school_name);
			} elseif ($type == 'group_school') {
				$school_name_array  = explode(',', $school_info['name']);
				$new_school_name	= $school_name_array[0] . (!empty($school_name_array[1]) ? (', ' . $school_name_array[1]) : (', ' . $city_info['name']));

				$data['school_name'] 	= mb_strtoupper($new_school_name);
				$data['target_school'] 	= mb_strtoupper($new_school_name);
			} elseif ($type == 'verified_podcast_school') {
				$data['school_name'] 	= mb_strtoupper($school_name);
			} elseif ($type == 'parent_chain') {
				$data['school_name'] 	= $school_info['name'];
				$data['student_url'] 	= USER_YAF_URL . 'india/2024/groupschool/signup/' . $school_info['site_code'];
				$data['qrcode_url'] 	= base_url(generateQrCode('www.yaf.bribooks.com/india/2024/groupschool/signup/' . $school_info['site_code'], 20, 2, sprintf('uploads/test/testqr_%s.png', $school_id)));
			}


			$html = $this->load->view(sprintf('frontend/default/letter_head/letter_head_%s', $type), $data, true);

			// echo $html; die;

			$dompdf = new Dompdf([
				// 'debugLayout' 	=> true,
			]);
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');

			$dompdf->render();

			$file_name = 'school_letter_head_' . $type . '_' . $school_id . '.pdf';

			$dompdf->stream($file_name);
		}
	}

	public function generateEventSchoolCertificate($template_id = 0, $site_id = 0) {
		$this->load->model('certificate/SchoolCertificateTemplate_model', 'school_certificate_template_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$date   = !empty($this->input->get('date_added')) ? urldecode($this->input->get('date_added')) : date('d/m/Y');

		if (empty($template_info = $this->school_certificate_template_model->get($template_id)) || empty($site_info = $this->site_model->get($site_id))) return;

		$state_name 	= $this->state_model->get($site_info['state_id'] ?? 0)['name'] ?? 'State';
		$city_name 		= $this->city_model->get($site_info['city_id'] ?? 0)['name'] ?? 'City';

		$school_name_array  = explode(',', $site_info['name']);
		$school_name		= $school_name_array[0];

		$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('s3_base_url'), $template_info['image']);

		list($image_width, $image_height) = getimagesize($image_template);

		$image 		= imagecreatefromjpeg($image_template);

		$darkgrey 	= imagecolorallocate($image, 70, 70, 70);
		$grey 		= imagecolorallocate($image, 110, 110, 110);
		$black 		= imagecolorallocate($image, 0, 0, 0);

		$font_path 	= FCPATH . 'assets/global/fonts/Times-New-Roman.otf';
		
		$school_name_text   = strtoupper($school_name);
		$font_size  		= 70;
		$max_width  		= $image_width * 0.70; // text allowed up to 70% width
		$y_school   		= 1840;

		do {
			$bbox 					= imagettfbbox($font_size, 0, $font_path, $school_name_text);
			$school_name_text_width = abs($bbox[2] - $bbox[0]);

			if ($school_name_text_width > $max_width) {
				$font_size -= 3;
			}
		} while ($school_name_text_width > $max_width);

		$x_school = ($image_width - $school_name_text_width) / 2;

		imagettftext($image, $font_size, 0, $x_school, $y_school, $black, $font_path, $school_name_text);

		$city_text 	= ucfirst($city_name);
		$city_y 	= 2220;

		$city_line_start_x = 980;
		$city_line_end_x   = 2480;

		$city_line_width = $city_line_end_x - $city_line_start_x;

		$bbox 				= imagettfbbox($font_size, 0, $font_path, $city_text);
		$city_text_width 	= abs($bbox[2] - $bbox[0]);

		$city_x = $city_line_start_x + (($city_line_width - $city_text_width) / 2);

		imagettftext($image, $font_size, 0, $city_x, $city_y, $black, $font_path, strtoupper($city_text));

		$state_text = ucfirst($state_name);
		$state_y 	= 2220;

		$state_line_start_x = 2800;
		$state_line_end_x   = 4400;

		$state_line_width = $state_line_end_x - $state_line_start_x;

		$bbox 				= imagettfbbox($font_size, 0, $font_path, $state_text);
		$state_text_width 	= abs($bbox[2] - $bbox[0]);

		$state_x = $state_line_start_x + (($state_line_width - $state_text_width) / 2);

		imagettftext($image, $font_size, 0, $state_x, $state_y, $black, $font_path, strtoupper($state_text));

		imagettftext($image, 55, 0, 4770, 3240, $black, $font_path, $date);

		$filename = FCPATH . sprintf('uploads/test/tempcert_%s.png', uniqid());

		imagejpeg($image, $filename);
		imagedestroy($image);

		if (ENVIRONMENT === 'production') {
			// upload to s3 bucket and share the cloudfront url
			$this->load->library('s3');
	
			log_kb(['SchoolCertififcate::' => $this->s3->amazonS3Upload(
				sprintf('%s_%s_%s.png', str_replace(' ', '_', strtolower($template_info['name'])) , $template_info['event_id'], $site_id),
				$filename,
				rtrim($this->config->item('s3_school_certificates'), '') . (ENVIRONMENT === 'production' ? '' : 'test')
			)]);
		}

		$html = sprintf('<style>@page{margin:0;padding:0;size: 5400px 3846px;}</style><img
			src="%s"
			style="width:100%%;max-height:100%%;"
		/>', base_url(str_replace(FCPATH, '', $filename)));

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->setPaper('A4', 'landscape');

		$dompdf->render();
		$dompdf->stream(str_replace(' ', '_', strtoupper($school_name)) . '.pdf'); // FOR DOWNLOAD
		// $dompdf->stream(str_replace(' ', '_', strtoupper($school_name)) . '.pdf', array("Attachment" => false)); // FOR ONLY STREAM OR BROWSER
	}

	public function generateEventTeacherCertificate($template_id = 0, $teacher_id = 0): void {
		$this->load->model('certificate/TeacherCertificateTemplate_model', 'teacher_certificate_template_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		if (empty($template_info = $this->teacher_certificate_template_model->get($template_id)) || empty($teacher_info = $this->user_model->get($teacher_id))) return;

		$site_info = $this->site_model->get($teacher_info['site_id'] ?? 0);
		$state_name = $this->state_model->get($teacher_info['state_id'] ?? 0)['name'] ?? 'State';
		$city_name 	= $this->city_model->get($teacher_info['city_id'] ?? 0)['name'] ?? 'City';

		$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), $template_info['image']);

		list($image_width, $image_height) = getimagesize($image_template);

		$image 		= imagecreatefromjpeg($image_template);

		$darkgrey 	= imagecolorallocate($image, 70, 70, 70);
		$grey 		= imagecolorallocate($image, 110, 110, 110);
		$black 		= imagecolorallocate($image, 0, 0, 0);

		$font_path 	= FCPATH . 'assets/global/fonts/Times-New-Roman.otf';

		$font_size 	= 90;

		$cert_width_px = 1802;

		$school_name_array  = explode(',', $site_info['name']);
		$school_name		= ucwords($school_name_array[0]);

		$teacher_name = ucwords($teacher_info['first_name'] . ' ' . $teacher_info['last_name']);

		imagettftext($image, $font_size, 0, 270, 1680, $black, $font_path, strtoupper($teacher_name));

		imagettftext($image, $font_size, 0, 270, 2300, $black, $font_path, strtoupper($school_name));

		imagettftext($image, 95, 0, 3800, 2670, $black, $font_path, strtoupper($teacher_info['grade']));

		imagettftext($image, 95, 0, 3900, 2670, $black, $font_path, strtoupper('- ' . $teacher_info['section']));

		imagettftext($image, 55, 0, 4450, 3360, $black, $font_path, '23/02/2025');

		$filename = FCPATH . sprintf('uploads/test/tempcert_%s.png', uniqid());

		imagejpeg($image, $filename);
		imagedestroy($image);

		$html = sprintf('<style>@page{margin:0;padding:0;size: 5400px 3846px;}</style><img
			src="%s"
			style="width:100%%;max-height:100%%;"
		/>', base_url(str_replace(FCPATH, '', $filename)));

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		$dompdf->setPaper('A4', 'landscape');

		$dompdf->render();
		// $dompdf->stream('inspiring_educators_cert_' . $teacher_info['id'] . '.pdf');
		$dompdf->stream(str_replace(' ', '_', strtoupper($teacher_name)) . '.pdf');

	}

	public function getUserInviteCard($code = '', $league_type = 'nyaf_2026_v3', $color_code = '#F4F7FF') {
		if (empty($code)) return;

		$dir = FCPATH . 'uploads/eventpass/pdfs';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		// if ($event_id == 14) {
		// 	$head_logo 	= base_url('assets/images/summer_logo_2025_v4.png');
		// 	$color_code = '#FFDD99';
		// } elseif ($event_id == 18) {
		// 	$head_logo 	= base_url('assets/images/nyaf_sg_logo_2025.png');
		// 	$color_code = '#F4F7FF';
		// } else {
		// 	$head_logo 	= base_url('assets/images/nyaf_logo_2025_v5.png');
		// 	$color_code = '#F4F7FF';
		// }

		
		$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('localisation/State_model', 'state_model');
		
		if (empty($invite_guest_info = $this->event_user_invite_model->getByCode($code))) {
			return;
		}

		$user_info	= $this->user_model->get($invite_guest_info['user_id'] ?? 0);
		$book_info  = $this->book_model->get($invite_guest_info['book_id'] ?? 0);
		$site_info  = $this->site_model->get($user_info['site_id'] ?? 0);
		$city_info  = $this->city_model->get($user_info['city_id'] ?? 0);
		$state_info	= $this->state_model->get($user_info['state_id'] ?? 0);

		$user_details_info = $this->db->get_where('user_details', ['user_id' => $user_info['id']])->row_array();

		$s3_dirname = $this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf');

		$book_rank = sprintf('%s # %s', 
			(!empty($invite_guest_info['is_jury']) ? 'JURY RANK' : 'RANK'), 
			$invite_guest_info['book_rank']
		);

		$grade = $user_info['grade'];

		$ends = array('th','st','nd','rd','th','th','th','th','th','th');
		if (($grade%100) >= 11 && ($grade%100) <= 13)
		$grade = $grade . 'th';
		else
		$grade = $grade . $ends[$grade%10];

		$author_image = empty($book_info['author_image']) ? base_url('uploads/user_image/placeholder.png') : $this->config->item('s3_base_url') . 'public/' . $book_info['author_image'];

		if (!empty($invite_guest_info['author_image'])) {
			$this->load->library('S3_lib', 's3_lib');
			$this->s3_lib->setBucket('bbprivateimagesin');

			$author_image = $this->s3_lib->getUrl($invite_guest_info['author_image'], (ENVIRONMENT === 'production' ? 'aadhaar_images' : 'aadhaar_images/test'), false, 30);
		} elseif(!empty( $user_details_info['image_nyaf'])) {
			$author_image = $s3_dirname . (ENVIRONMENT === 'production' ? '' : 'test/') . $user_details_info['image_nyaf'];
		}

		$head_logo = sprintf(
			'%spublic/EventGallery/logos/invite_logo_%s.png',
			$this->config->item('cloudfront_url'),
			$league_type
		);

		$data = [
			'author_name'   => $book_info['author_name'],
			'school'        => $site_info['name'],
			'state'         => $state_info['name'] ?? '',
			'city'          => $city_info['name'] ?? '',
			'grade'         => $grade,
			'section'       => strtoupper($user_info['section']),
			'book_rank'     => $book_rank,
			'author_image'  => $author_image,
			'guest_1_name'  => $invite_guest_info['guest_1_name'],
			'guest_2_name'  => $invite_guest_info['guest_2_name'],

			'guest_1_image' => ($invite_guest_info['guest_1_relation'] === 'mother')
								? base_url('assets/images/woman.svg')
								: base_url('assets/images/man.svg'),

			'guest_2_image' => ($invite_guest_info['guest_2_relation'] === 'mother')
								? base_url('assets/images/woman.svg')
								: base_url('assets/images/man.svg'),

			'guest_2'       => (
				!empty($invite_guest_info['guest_2_name']) &&
				!empty($invite_guest_info['guest_2_relation']) &&
				!empty($invite_guest_info['guest_2_aadhaar'])
			),

			'qr_code'       => base_url(
				generateQrCode(
					USER_URL . 'author_data/' . $code,
					25,
					2,
					"uploads/test/event_invite_{$code}.png"
				)
			),

			'location_icon' => base_url('assets/images/location.svg'),
			'head_logo'     => $head_logo,
			'color_code'    => $color_code
		];

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_author_pdf_template', $data, true);
		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A3', 'potrait');

		// $dompdf->render();

		$dompdf->render();
		$file = 'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);

		$dompdf->stream(str_replace(' ', '_', strtoupper($book_info['author_name'])) . '_' . $invite_guest_info['event_id'] . '.pdf');

		// echo base_url($file);
	}

	public function getSchoolInviteCard($code = '') {
		if (empty($code)) return;

		$dir = FCPATH . 'uploads/eventpass/pdfs';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$head_logo = base_url('assets/images/nyaf_logo_2025_v5.png');

		$school_details_guest_info = $this->db->get_where('school_details_nyaf_guest', ['code'   => $code, 'event_id' => 21])->row_array();
		$site_info = $this->site_model->get($school_details_guest_info['site_id']);

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_school_pdf_template', [
			'code' 		=> $code,
			'head_logo' => $head_logo
		], true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A3', 'potrait');
		$dompdf->render();

		$dompdf->render();
		$file = 'uploads/eventpass/pdfs/school_entry_pass_'.$code.'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);

		// $dompdf->stream('school_invite.pdf');
		$dompdf->stream(str_replace(' ', '_', strtoupper($site_info['name'])) . '.pdf');

	}

	public function getTeacherInviteCard($code = '') {
		if (empty($code)) return;

		$dir = FCPATH . 'uploads/eventpass/pdfs';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$head_logo = base_url('assets/images/nyaf_logo_2025_v5.png');


		if ($teacher_details_guest_info = $this->db->get_where('user_details_nyaf_guest', ['code'   => $code])->row_array()) {
			$teacher_info = $this->user_model->get($teacher_details_guest_info['user_id']);

			$school_info = $this->site_model->get($teacher_info['site_id'] ?? 0);
			$state_info = $this->state_model->get($teacher_info['state_id'] ?? 0);
			$city_info = $this->city_model->get($teacher_info['city_id'] ?? 0);

			$guest_1_image = base_url('assets/images/man.svg');
			if($teacher_details_guest_info['relation_1'] === 'female') {
				$guest_1_image = base_url('assets/images/woman.svg');
			}

			$data = [
				'code' 			=> $code,
				'qr_code' 		=> base_url(generateQrCode((USER_URL . 'teacher_data/' . $code), 25, 2, sprintf('uploads/test/event_teacher_invite_%s.png', $code))),
				'head_logo' 	=> $head_logo,
				'school_name' 	=> $school_info['name'],
				'state' 		=> $state_info['name'] ?? '',
				'city' 			=> $city_info['name'] ?? '',
				'grade' 		=> $teacher_info['grade'] ?? '',
				'section' 		=> $teacher_info['section'] ?? '',
				'guest_name_1' 	=> $teacher_details_guest_info['guest_name_1'] ?? '',
				'guest_1_image' => $guest_1_image,
				'location' 		=> base_url('assets/images/location.svg')
			];
		} else {
			return;
		}

		$user_details_guest_info = $this->db->get_where('user_details_nyaf_guest', ['code'   => $code])->row_array();
		$user_info = $this->user_model->get($user_details_guest_info['user_id']);

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_teacher_pdf_template', $data, true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A3', 'potrait');
		$dompdf->render();

		$dompdf->render();
		$file = 'uploads/eventpass/pdfs/teacher_entry_pass_'.$code.'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);

		$dompdf->stream(str_replace(' ', '_', strtoupper(($user_info['first_name'] . ' ' . $user_info['last_name']))) . '_' . $user_details_guest_info['event_id'] . '.pdf');
	}

	public function generateEventPinnacleCertificate($cert_id = 0) {
		$this->load->model('certificate/SchoolCertificateTemplate_model', 'school_certificate_template_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('user/User_model', 'user_model');

		if (empty($cert_id)) return;

		if (empty($cert_info = $this->db->get_where('certificates', ['id' => $cert_id])->row_array())) return;

		if (empty($book_info = $this->book_model->get($cert_info['book_id']))) return;

		// pr($cert_info);
		// pr($book_info);

		$book_position = 610;
		$author_position = 810;
		$rank_position = 922;
		$code_position = 1040;
		$date_position = 1070;

		if ($cert_info['event_id'] == 21) {
			if ($cert_info['certificate_template_id'] == 255) {
				$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), 'certificate_template/event21/nyaf_national_event_jury_cert.jpg');
			} else {
				$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), 'certificate_template/event21/nyaf_national_event_cert.jpg');
			}
		} elseif ($cert_info['event_id'] == 14) {
			if ($cert_info['certificate_template_id'] == 257) {
				$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), 'certificate_template/event14/sbwf_national_event_jury_cert.jpg');
			} else {
				$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), 'certificate_template/event14/sbwf_national_event_cert.jpg');
			}
		} elseif ($cert_info['event_id'] == 18) {
			$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), 'certificate_template/event18/nyaf_sg_national_event_cert.jpg');
		} elseif ($cert_info['event_id'] == 18) {
			if ($cert_info['certificate_template_id'] == 255) {
				$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), 'certificate_template/event21/nyaf_national_event_jury_cert.jpg');
			} else {
				$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), 'certificate_template/event21/nyaf_national_event_cert.jpg');
			}
		} else {
			return;
		}

		// echo $image_template;die;

		list($image_width, $image_height) = getimagesize($image_template);

		$image 		= imagecreatefromjpeg($image_template);

		$qr_file 	= generateQrCode('http://www.bribooks.com/verifycertificate/' . $cert_info['unique_id'], 20, 2);
		$qr_image 	= imagecreatefrompng(FCPATH . $qr_file);

		$darkgrey 	= imagecolorallocate($image, 70,  70, 70);
		$grey 		= imagecolorallocate($image, 110, 110, 110);
		$black 		= imagecolorallocate($image, 0, 0, 0);


		$image_width	= imagesx($image);
		$image_height	= imagesy($image);
		$qr_image_width		= imagesx($qr_image);
		$qr_image_height 	= imagesy($qr_image);

		$font_path 	= FCPATH . 'assets/global/fonts/Poppins-SemiBold.otf';

		$font_size = 90;

		imagettftext($image, $font_size, 0, 370, 1820, $darkgrey, $font_path, strtoupper($book_info['author_name']));
		imagettftext($image, $font_size, 0, 370, 2420, $darkgrey, $font_path, strtoupper($book_info['name']));
		imagettftext($image, 70, 0, 3010, 2770, $black, $font_path, sprintf('%02d', strtoupper($cert_info['rank'])));
		if ($cert_info['event_id'] == 14) {
			imagettftext($image, 38, 0, 4340, 3110, $darkgrey, $font_path, $cert_info['unique_id']);
		} else {
			imagettftext($image, 38, 0, 4330, 3110, $darkgrey, $font_path, $cert_info['unique_id']);
		}
		imagettftext($image, 50, 0, 4335, 3220, $darkgrey, $font_path, date('d/m/Y', strtotime($cert_info['date_added'])));

		$zoom = 1.9;

		if ($cert_info['event_id'] == 14) {
			$qr_x_axis = 630;
			$qr_y_axis = 793;
		} else {
			$qr_x_axis = 630;
			$qr_y_axis = 800;
		}

		imagecopyresampled(
			$image,
			$qr_image,
			($image_width - $qr_image_width / $zoom - $qr_x_axis),
			($image_height - $qr_image_height / $zoom - $qr_y_axis),
			0,
			0,
			$qr_image_width / $zoom,
			$qr_image_height / $zoom,
			$qr_image_width,
			$qr_image_height
		);

		$filename = FCPATH . sprintf('uploads/test/tempcert_%s.png', uniqid());

		imagejpeg($image, $filename);
		imagedestroy($image);

		// // upload to s3 bucket and share the cloudfront url
		// log_kb(['GenerareCertififcate::' => $this->s3->amazonS3Upload(
		// 	$data['image_name'] . '.png',
		// 	$filename,
		// 	rtrim($this->config->item('s3_author_certificates'), '/')
		// )]);

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
		$dompdf->stream(str_replace(' ', '_', strtoupper($book_info['name'])) . '_' . $cert_info['event_id'] . '.pdf');
	}

	public function generateEventPrepCertificate($cert_id = 0) {
		$this->load->model('certificate/EventPrepCertificateTemplate_model', 'event_prep_certificate_template_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('user/User_model', 'user_model');

		if (empty($cert_id)) return;

		if (empty($cert_info = $this->db->get_where('certificates', ['id' => $cert_id])->row_array())) return;

		if (empty($cert_info) || empty($cert_temp_info = $this->event_prep_certificate_template_model->get_all([
			'certificate_template_id' => $cert_info['certificate_template_id'],
		])['rows'][0] ?? []) || empty($cert_temp_info['image'])) return;

		if (empty($book_info = $this->book_model->get($cert_info['book_id']))) return;

		$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), $cert_temp_info['image']);

		// echo $image_template;die;

		list($image_width, $image_height) = getimagesize($image_template);

		$image 		= imagecreatefromjpeg($image_template);

		$qr_file 	= generateQrCode('http://www.bribooks.com/verifycertificate/' . $cert_info['unique_id'], 20, 2);
		$qr_image 	= imagecreatefrompng(FCPATH . $qr_file);

		$darkgrey 	= imagecolorallocate($image, 70,  70, 70);
		$grey 		= imagecolorallocate($image, 110, 110, 110);
		$black 		= imagecolorallocate($image, 0, 0, 0);


		$image_width	= imagesx($image);
		$image_height	= imagesy($image);
		$qr_image_width		= imagesx($qr_image);
		$qr_image_height 	= imagesy($qr_image);

		$font_path 	= FCPATH . 'assets/global/fonts/Poppins-SemiBold.otf';

		$font_size = 90;

		$rank_x_axis 		= $cert_temp_info['rank_x_axis'] ?? 2890;
		$rank_y_axis 		= $cert_temp_info['rank_y_axis'] ?? 2770;
		$unique_id_x_axis 	= $cert_temp_info['unique_id_x_axis'] ?? 4325;
		$unique_id_y_axis 	= $cert_temp_info['unique_id_y_axis'] ?? 3070;

		$date   = !empty($this->input->get('date_added')) ? urldecode($this->input->get('date_added')) : $cert_info['date_added'];

		imagettftext($image, $font_size, 0, 370, 1820, $darkgrey, $font_path, strtoupper($book_info['author_name']));
		imagettftext($image, $font_size, 0, 370, 2420, $darkgrey, $font_path, strtoupper($book_info['name']));
		imagettftext($image, 70, 0, $rank_x_axis, $rank_y_axis, $black, $font_path, sprintf('%02d', strtoupper($cert_info['rank'])));

		imagettftext($image, 38, 0, $unique_id_x_axis, $unique_id_y_axis, $darkgrey, $font_path, $cert_info['unique_id']);
		imagettftext($image, 50, 0, 4355, 3230, $darkgrey, $font_path, date('d/m/Y', strtotime($date)));

		$zoom = 1.9;

		if ($cert_info['event_id'] == 14) {
			$qr_x_axis = 630;
			$qr_y_axis = 793;
		} else {
			$qr_x_axis = 630;
			$qr_y_axis = 800;
		}

		imagecopyresampled(
			$image,
			$qr_image,
			($image_width - $qr_image_width / $zoom - $qr_x_axis),
			($image_height - $qr_image_height / $zoom - $qr_y_axis),
			0,
			0,
			$qr_image_width / $zoom,
			$qr_image_height / $zoom,
			$qr_image_width,
			$qr_image_height
		);

		$filename = FCPATH . sprintf('uploads/test/tempcert_%s.png', uniqid());

		imagejpeg($image, $filename);
		imagedestroy($image);

		// // upload to s3 bucket and share the cloudfront url
		log_kb(['EventPrepCertififcate::' => $this->s3->amazonS3Upload(
			$cert_info['image'] . '.png',
			$filename,
			rtrim($this->config->item('s3_author_certificates'), '/')
		)]);

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
		$dompdf->setPaper('A3', 'landscape');

		// Render the HTML as PDF
		$dompdf->render();
		// $dompdf->stream($data['image_name'] . '.pdf');
		$dompdf->stream(str_replace(' ', '_', strtoupper($book_info['name'])) . '_' . $cert_info['event_id'] . '.pdf');

	}

	public function getSingleSchoolInvitePass($code = '') {
		$this->load->model('common/Site_model', 'site_model');

		$dir = FCPATH . 'uploads/eventpass/pdfs';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$head_logo = base_url('assets/images/nyaf_logo_2025_v5.png');
		$school_details_guest_info = $this->db->get_where('school_details_nyaf_guest', ['code'   => $code, 'event_id' => 21])->row_array();
		$site_info = $this->site_model->get($school_details_guest_info['site_id']);

		// $html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_single_school_pdf_template', ['code' => $code], true);

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_single_school_pdf_template', [
			'code' 		=> $code,
			'head_logo' => $head_logo
		], true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		// $dompdf->setPaper(array(0, 0, 430, 610), 'potrait');
		$dompdf->setPaper(array(0, 0, 360, 513.2), 'potrait');

		$dompdf->render();
		// $file = 'uploads/eventpass/pdfs/entry_pass_'.$code;
		// $output = $dompdf->output();
		// file_put_contents(FCPATH.$file, $output);
		$dompdf->render();
		$dompdf->stream(str_replace(' ', '_', strtoupper($site_info['name'])) . '.pdf');
		// return base_url($file);
	}

    public function getSingleUserInvitePass($code = '', $league_type = 'nyaf_2026_v3', $color_code = '#F4F7FF') {
		$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('localisation/State_model', 'state_model');

		if (empty($code)) {
			echo "Something went wrong!";die;
		}

		$dir = FCPATH . 'uploads/eventpass/pdfs';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		if (empty($invite_guest_info = $this->event_user_invite_model->getByCode($code))) {
			echo "Something went wrong in code!";die;
		}

		$user_info	= $this->user_model->get($invite_guest_info['user_id'] ?? 0);
		$book_info  = $this->book_model->get($invite_guest_info['book_id'] ?? 0);
		$site_info  = $this->site_model->get($user_info['site_id'] ?? 0);
		$city_info  = $this->city_model->get($user_info['city_id'] ?? 0);
		$state_info = $this->state_model->get($user_info['state_id'] ?? 0);

		$user_details_info = $this->db->get_where('user_details', ['user_id' => $user_info['id']])->row_array();

		$s3_dirname = $this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf');

		$book_rank = sprintf('%s # %s', 
			(!empty($invite_guest_info['is_jury']) ? 'JURY RANK' : 'RANK'), 
			$invite_guest_info['book_rank']
		);

		$grade = $user_info['grade'];

		$ends = array('th','st','nd','rd','th','th','th','th','th','th');
		if (($grade%100) >= 11 && ($grade%100) <= 13)
		$grade = $grade . 'th';
		else
		$grade = $grade . $ends[$grade%10];

		$author_image = empty($book_info['author_image']) ? base_url('uploads/user_image/placeholder.png') : $this->config->item('s3_base_url') . 'public/' . $book_info['author_image'];

		if (!empty($invite_guest_info['author_image'])) {
			$this->load->library('S3_lib', 's3_lib');
			$this->s3_lib->setBucket('bbprivateimagesin');

			$author_image = $this->s3_lib->getUrl($invite_guest_info['author_image'], (ENVIRONMENT === 'production' ? 'aadhaar_images' : 'aadhaar_images/test'), false, 30);
		} elseif(!empty( $user_details_info['image_nyaf'])) {
			$author_image = $s3_dirname . (ENVIRONMENT === 'production' ? '' : 'test/') . $user_details_info['image_nyaf'];
		}

		$head_logo = sprintf(
			'%spublic/EventGallery/logos/invite_logo_%s.png',
			$this->config->item('cloudfront_url'),
			$league_type
		);

		$data = [
			'author_name'   => $book_info['author_name'] ?? '',
			'school'        => $site_info['name'] ?? '',
			'state'         => $state_info['name'] ?? '',
			'city'          => $city_info['name'] ?? '',
			'grade'         => $grade,
			'section'       => strtoupper($user_info['section']),
			'book_rank'     => $book_rank,
			'author_image'  => $author_image,
			'guest_1_name'  => $invite_guest_info['guest_1_name'],
			'guest_2_name'  => $invite_guest_info['guest_2_name'],

			'guest_1_image' => ($invite_guest_info['guest_1_relation'] === 'mother')
								? base_url('assets/images/woman.svg')
								: base_url('assets/images/man.svg'),

			'guest_2_image' => ($invite_guest_info['guest_2_relation'] === 'mother')
								? base_url('assets/images/woman.svg')
								: base_url('assets/images/man.svg'),

			'guest_2'       => (
				!empty($invite_guest_info['guest_2_name']) &&
				!empty($invite_guest_info['guest_2_relation']) &&
				!empty($invite_guest_info['guest_2_aadhaar'])
			),

			'qr_code'       => base_url(
				generateQrCode(
					USER_URL . 'author_data/' . $code,
					25,
					2,
					"uploads/test/event_invite_{$code}.png"
				)
			),

			'location_icon' => base_url('assets/images/location.svg'),
			'head_logo'     => $head_logo,
			'color_code'    => $color_code
		];

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_author_single_pdf', $data, true);
		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		// $dompdf->setPaper('A4', 'potrait');
		// $dompdf->setPaper(array(0, 0, 430, 600), 'potrait');
		$dompdf->setPaper(array(0, 0, 360, 513.2), 'potrait');

		$dompdf->render();
		$file = 'uploads/eventpass/pdfs/entry_pass_'.$code;
		$output = $dompdf->output();
		$dompdf->render();
		$dompdf->stream(str_replace(' ', '_', strtoupper($book_info['author_name'])) . '_' . $invite_guest_info['event_id'] . '.pdf');
	}

	public function getSingleTeacherInvitePass($code = '') {
		if (empty($code)) return;

		$dir = FCPATH . 'uploads/eventpass/pdfs';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$head_logo = base_url('assets/images/nyaf_logo_2025_v5.png');


		if ($teacher_details_guest_info = $this->db->get_where('user_details_nyaf_guest', ['code'   => $code])->row_array()) {
			$teacher_info = $this->user_model->get($teacher_details_guest_info['user_id']);

			$school_info = $this->site_model->get($teacher_info['site_id'] ?? 0);
			$state_info = $this->state_model->get($teacher_info['state_id'] ?? 0);
			$city_info = $this->city_model->get($teacher_info['city_id'] ?? 0);

			$guest_1_image = base_url('assets/images/man.svg');
			if($teacher_details_guest_info['relation_1'] === 'female') {
				$guest_1_image = base_url('assets/images/woman.svg');
			}

			$data = [
				'code' 			=> $code,
				'qr_code' 		=> base_url(generateQrCode((USER_URL . 'teacher_data/' . $code), 25, 2, sprintf('uploads/test/event_teacher_invite_%s.png', $code))),
				'head_logo' 	=> $head_logo,
				'school_name' 	=> $school_info['name'],
				'state' 		=> $state_info['name'] ?? '',
				'city' 			=> $city_info['name'] ?? '',
				'grade' 		=> $teacher_info['grade'] ?? '',
				'section' 		=> $teacher_info['section'] ?? '',
				'guest_name_1' 	=> $teacher_details_guest_info['guest_name_1'] ?? '',
				'guest_1_image' => $guest_1_image,
				'location' 		=> base_url('assets/images/location.svg')
			];
		} else {
			return;
		}

		$user_details_guest_info = $this->db->get_where('user_details_nyaf_guest', ['code'   => $code])->row_array();
		$user_info = $this->user_model->get($user_details_guest_info['user_id']);

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_single_teacher_pdf_template', $data, true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		// $dompdf->setPaper('A3', 'potrait');
		// $dompdf->setPaper(array(0, 0, 430, 610), 'potrait');
		$dompdf->setPaper(array(0, 0, 360, 513.2), 'potrait');

		$dompdf->render();

		$dompdf->render();
		$file = 'uploads/eventpass/pdfs/teacher_entry_pass_'.$code.'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);

		$dompdf->stream(str_replace(' ', '_', strtoupper(($user_info['first_name'] . ' ' . $user_info['last_name']))) . '_' . $user_details_guest_info['event_id'] . '.pdf');
	}

	private function _generateCertQrCode($data = NULL) {
		if (empty($data)) return;

		$file = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), $data['image']);

		return generateQrCode('http://www.bribooks.com/verifycertificate/' . $data['cert_unique_id'], 20,2, $file);
	}

	public function generateImageLetterHead($event_id = 0, $site_id = 0, $version = 1) {
		if (empty($event_info = $this->event_model->get($event_id)) || empty($site_info = $this->site_model->get($site_id))) return;

		// $image_template = sprintf('%spublic/EventGallery/letter_head/event%s/letter_head_v%s.jpg', $this->config->item('cloudfront_url'), $event_info['id'], $version);
		$image_template = sprintf('%spublic/EventGallery/letter_head/event%s/letter_head_v%s.jpg', $this->config->item('cloudfront_url'), 92, $version);

		list($image_width, $image_height) = getimagesize($image_template);

		$image 		= imagecreatefromjpeg($image_template);

		$darkgrey 	= imagecolorallocate($image, 70, 70, 70);
		$grey 		= imagecolorallocate($image, 110, 110, 110);
		$black 		= imagecolorallocate($image, 0, 0, 0);
		// $blue 		= imagecolorallocate($image, 30, 144, 255);
		// $blue = imagecolorallocate($image, 11, 26, 51);
		$blue = imagecolorallocate($image, 16, 40, 75);


		$image_width	= imagesx($image);
		$image_height	= imagesy($image);

		$font_path 	= FCPATH . 'assets/global/fonts/Poppins-SemiBold.otf';

		$font_size 	= 28;

		// echo $this->input->get('spoc_name');die;

		$authorized_person   = !empty($this->input->get('spoc_name')) ? urldecode($this->input->get('spoc_name')) : 'School Leader';

		$spoc_name = ucwords(strtolower('Dear' . ' ' . $authorized_person));

		// $school_name_array  = explode(',', $site_info['name']);
		// $school_name		= $school_name_array[0] ?? $site_info['name'];
		$school_name		= $site_info['name'];

		// imagettftext($image, 40, 0, 280, 480, $black, $font_path, $spoc_name);
		imagettftext($image, 40, 0, 122, 480, $blue, $font_path, $spoc_name);
		imagettftext($image, 28, 0, 2300, 470, $blue, $font_path, $site_info['id']);
		imagettftext($image, 33, 0, 570, 2473, $blue, $font_path, ucwords(strtolower($school_name)));

		$zoom = 5.5;

		$filename = FCPATH . sprintf('uploads/test/tempcert_%s.png', uniqid());

		imagejpeg($image, $filename);
		imagedestroy($image);

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
		$dompdf->setPaper('A4', 'portrait');

		// Render the HTML as PDF
		$dompdf->render();
		// $dompdf->stream($site_info['name'] . '.pdf');
		$dompdf->stream(str_replace(' ', '_', strtoupper($site_info['name'])) . '.pdf');
	}

	public function exportOrdersData($option_type = 1) {
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('addres/Address_model', 'address_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('book/PageVersion_model', 'page_version_model');


		$start_date 	= !empty($this->input->get('start_date')) ? urldecode($this->input->get('start_date')) : '';
		$end_date 		= !empty($this->input->get('end_date')) ? urldecode($this->input->get('end_date')) : '';

		if (!empty($start_date)) {
			$filter_data['startdate'] = $start_date;
		}

		if (!empty($end_date)) {
			$filter_data['enddate'] = $end_date;
		}

		if (empty($filter_data)) return;

		$filter_data['option_type'] = [$option_type];

		$results = $this->order_model->searchProductName($filter_data)['rows'] ?? [];

		$orders = [];

		$sn = 1;

		foreach ($results as $order) {

			$filter_data = [];
			$filter_data['order_id'] = (int)$order['id'];

			$products = $this->order_model->getProducts($order['id'], $filter_data);

			$address_info = $this->address_model->getByID($order['address_id']);
			$user_info = $this->student_model->get($order['user_id']);

			$total = round($order['total'], 2);

			$printer_info = $this->user_model->get($order['assign_printer_id']);

			$shipping_info = json_decode($order['shipping_info'], true);

			$discount_info = json_decode($order['discount_desc'], true);

			$shipping_tracking_info = json_decode($order['shipping_tracking_info'], true);

			foreach ($products as $key => $product) {
				$option = json_decode($product['option'], true);

				$total_pages 	= $this->page_version_model->get_all([
					'book_id'	=> $product['product_id'],
					'version'	=> $product['version'],
				])['total'] ?? 0;

				$orders[] = [
					'sn'			=> $sn,
					'region'		=> strtolower($order['currency_code']) === 'inr'
						? _l('domestic')
						: _l('global'),
					'order_id'		=> $order['id'],
					'order_code'	=> $order['order_code'],
					'book_name'		=> $product['name'],
					'version'		=> $product['version'],
					'sku'			=> _o_b_code($product['product_id'], $product['version'], $option['name']),
					'isbn/sn'		=> !empty($product['isbn']) ? $product['isbn'] : $product['unique_id'],
					'option'		=> $option['name'],
					'pages'			=> $total_pages * 2 + 1,
					'author_name'	=> $product['author_name'],
					'status'		=> _os($order['status']),
					'quantity'		=> $product['quantity'],
					'state'			=> $address_info['state'] ?? '',
					'country'		=> $address_info['country'] ?? '',
					'c_mobile'		=> $user_info['mobile'] ?? '',
					'c_email'		=> $user_info['email'] ?? '',
					'currency_code'	=> $order['currency_code'],
					'total'			=> $key == 0 ? $total : 0,
					'weight'		=> $product['weight'] . 'gm',
					'product_total'	=> $product['total'] ?? 0,
					'discount '		=> $key == 0 ? $discount_info['coupon'] : 0,
					'shipping_cost'	=> $key == 0 ? $order['shipping_cost'] : 0,
					'handling_charge'=> strtolower($order['currency_code']) === 'inr' ? 20 : 50,
					'printer'		=> $printer_info['first_name'] ?? '',
					'awb_code'		=> $shipping_tracking_info['awb_code'] ?? '',
					'shipping_info'	=> $shipping_tracking_info['courier_name'] ?? ($shipping_info['courier_name'] ?? ''),
					'date_added'	=> $order['date_added'],
				];

				$sn++;
			}
		}

		self::_downloadPrepCsv($orders, 'orders_');
	}

	public function createBookSample() {
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/PageVersion_model', 'page_version_model');

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/book_automation_sample_2025.csv');
		$rows = $this->parsecsv->data;

		// pr($rows, 1);

		$books = [];

		$template = '1. Creativity & Originality (1-10):
a. Strengths: %s
b. Areas for Improvement: %s
c. Suggestions: %s
d. Grade: %s

2. Character Development & Depth (1-10):
a. Strengths: %s
b. Areas for Improvement: %s
c. Suggestions: %s
d. Grade: %s

3. Plot & Storytelling (1-10):
a. Strengths: %s
b. Areas for Improvement: %s
c. Suggestions: %s
d. Grade: %s

4. Grammatical Errors (1-10):
a. Strengths: %s
b. Areas for Improvement: %s
c. Suggestions: %s
d. Grade: %s

5. Imaginative Use of Language (1-10):
a. Strengths: %s
b. Areas for Improvement: %s
c. Suggestions: %s
d. Grade: %s

6. Theme and Message (1-10):
a. Strengths: %s
b. Areas for Improvement: %s
c. Suggestions: %s
d. Grade: %s

7. Overall Impact (1-10):
a. Strengths: %s
b. Areas for Improvement: %s
c. Suggestions: %s
d. Grade: %s

8. Overall Grade (1-10): %s
9. General Feedback: %s';

		$template_grade = '1. Creativity & Originality (1-10): %s
2. Character Development & Depth (1-10): %s
3. Plot & Storytelling (1-10): %s
4. Grammatical Errors (1-10): %s
5. Imaginative Use of Language (1-10): %s
6. Theme and Message (1-10): %s
7. Overall Impact (1-10): %s
8. Overall Grade (1-10): %s';

		function _clean_text_page($text) {
			return preg_replace(['/[\n]/', '/\s+/'], [' ', ' '], strip_tags($text));
		}

		function _clean_page_text_single($text) {
			// Replace HTML tags and common escaped/unicode sequences
			$text = preg_replace('/<.*?>|\\\\n|\\\\u2013|\\\\u00a0|\n|\xE2\x80\x93|\xC2\xA0/', ' ', $text);

			// Remove HTML entities like &nbsp;, &amp;, etc.
			$text = preg_replace('/&\w+;/', ' ', $text);

			// Remove emojis
			$emoji_regex = '/[\x{1F600}-\x{1F64F}' . // Emoticons
							'\x{1F300}-\x{1F5FF}' . // Symbols & pictographs
							'\x{1F680}-\x{1F6FF}' . // Transport & map symbols
							'\x{1F700}-\x{1F77F}' . // Alchemical symbols
							'\x{1F780}-\x{1F7FF}' . // Geometric Shapes Extended
							'\x{1F800}-\x{1F8FF}' . // Supplemental Arrows-C
							'\x{1F900}-\x{1F9FF}' . // Supplemental Symbols & Pictographs
							'\x{1FA00}-\x{1FA6F}' . // Chess symbols, etc.
							'\x{1FA70}-\x{1FAFF}' . // Symbols and Pictographs Extended-A
							'\x{2700}-\x{27BF}' .	// Dingbats
							'\x{24C2}-\x{1F251}' .  // Enclosed characters
							']/u';
			$text = preg_replace($emoji_regex, '', $text);

			// Allow only specific characters and symbols
			$text = preg_replace('/[^\w\s!@#\$%\^&\*\)\'",\(+=._\-?~\[\]`\\\\\{\}|;:\/]/u', ' ', $text);

			// Normalize whitespace
			$text = preg_replace('/\s+/', ' ', $text);

			// Remove stray backslashes
			$text = str_replace('\\', '', $text);

			// Remove isolated lowercase 'u'
			$text = preg_replace('/\bu\b/', '', $text);

			// Remove trailing space + 'n'
			$text = preg_replace('/\s+n$/', '', $text);

			// Trim trailing periods and spaces
			$text = rtrim($text, '. ');

			return trim($text);
		}

		function _clean_text_group($json_text) {
			$parts = json_decode($json_text, true);

			if (!is_array($parts)) {
				return '';
			}

			$clean_texts = [];

			foreach ($parts as $part) {
				if (is_string($part)) {
					$clean_texts[] = _clean_page_text_single($part);
				}
			}

			// Join cleaned strings with a space
			$cleaned = implode(' ', $clean_texts);

			// Normalize whitespace
			$cleaned = preg_replace('/\s+/', ' ', $cleaned);

			return trim($cleaned);
		}

		foreach($rows as $row) {
			if (empty($book_info = $this->book_model->get($row['book_id']))) continue;

			$pages 	= $this->page_version_model->get_all([
				'book_id'	=> $book_info['id'],
				'version'	=> $book_info['version'],
				'sort'		=> 'page_version.sort_order',
				'order'		=> 'ASC'
			])['rows'] ?? [];

			$full_text = '';

			foreach ($pages as $page) {
				$full_text .= _clean_text_group($page['texts']) . ' ';
			}

			$output = vsprintf($template, [
				_clean_text_page($row['creativity_originality_strengths'] ?? ''),
				_clean_text_page($row['creativity_originality_areas_for_improvement'] ?? ''),
				_clean_text_page($row['creativity_originality_suggestions'] ?? ''),
				_clean_text_page($row['creativity_originality_grade_10'] ?? ''),

				_clean_text_page($row['character_development_depth_strengths'] ?? ''),
				_clean_text_page($row['character_development_depth_areas_for_improvement'] ?? ''),
				_clean_text_page($row['character_development_depth_suggestions'] ?? ''),
				_clean_text_page($row['character_development_depth_grade_10'] ?? ''),

				_clean_text_page($row['plot_storytelling_strengths'] ?? ''),
				_clean_text_page($row['plot_storytelling_areas_for_improvement'] ?? ''),
				_clean_text_page($row['plot_storytelling_suggestions'] ?? ''),
				_clean_text_page($row['plot_storytelling_grade_10'] ?? ''),

				_clean_text_page($row['grammatical_errors_strengths'] ?? ''),
				_clean_text_page($row['grammatical_errors_areas_for_improvement'] ?? ''),
				_clean_text_page($row['grammatical_errors_suggestions'] ?? ''),
				_clean_text_page($row['grammatical_errors_grade_10'] ?? ''),

				_clean_text_page($row['imaginative_use_of_language_strengths'] ?? ''),
				_clean_text_page($row['imaginative_use_of_language_areas_for_improvement'] ?? ''),
				_clean_text_page($row['imaginative_use_of_language_suggestions'] ?? ''),
				_clean_text_page($row['imaginative_use_of_language_grade_10'] ?? ''),

				_clean_text_page($row['theme_ideas_and_message_strengths'] ?? ''),
				_clean_text_page($row['theme_and_message_areas_for_improvement'] ?? ''),
				_clean_text_page($row['theme_and_message_suggestions'] ?? ''),
				_clean_text_page($row['theme_and_message_grade_10'] ?? ''),

				_clean_text_page($row['overall_impact_strengths'] ?? ''),
				_clean_text_page($row['overall_impact_areas_for_improvement'] ?? ''),
				_clean_text_page($row['overall_impact_suggestions'] ?? ''),
				_clean_text_page($row['overall_impact_grade_10'] ?? ''),

				round((($row['creativity_originality_grade_10'] ?? 1) +
				($row['character_development_depth_grade_10'] ?? 1) +
				($row['plot_storytelling_grade_10'] ?? 1) +
				($row['grammatical_errors_grade_10'] ?? 1) +
				($row['imaginative_use_of_language_grade_10'] ?? 1) +
				($row['theme_and_message_grade_10'] ?? 1) +
				($row['overall_impact_grade_10'] ?? 1)) / 7, 1),

				_clean_text_page($row['feedback'] ?? ''),
			]);

			$output_grade = vsprintf($template_grade, [
				_clean_text_page($row['creativity_originality_grade_10'] ?? ''),
				_clean_text_page($row['character_development_depth_grade_10'] ?? ''),
				_clean_text_page($row['plot_storytelling_grade_10'] ?? ''),
				_clean_text_page($row['grammatical_errors_grade_10'] ?? ''),
				_clean_text_page($row['imaginative_use_of_language_grade_10'] ?? ''),
				_clean_text_page($row['theme_and_message_grade_10'] ?? ''),
				_clean_text_page($row['overall_impact_grade_10'] ?? ''),

				round((($row['creativity_originality_grade_10'] ?? 1) +
				($row['character_development_depth_grade_10'] ?? 1) +
				($row['plot_storytelling_grade_10'] ?? 1) +
				($row['grammatical_errors_grade_10'] ?? 1) +
				($row['imaginative_use_of_language_grade_10'] ?? 1) +
				($row['theme_and_message_grade_10'] ?? 1) +
				($row['overall_impact_grade_10'] ?? 1)) / 7, 1),
			]);

			// pr([$output, $row], 1);

			$books[] = [
				'Book ID'		=> $book_info['id'],
				'Version'		=> $book_info['version'],
				'input'			=> $full_text,
				'output'		=> $output,
				'output_grade'	=> '',
			];

			$books[] = [
				'Book ID'		=> $book_info['id'],
				'Version'		=> $book_info['version'],
				'input'			=> $full_text,
				'output'		=> '',
				'output_grade'	=> $output_grade,
			];

			// pr($books, 1);

			// $books[] = [
			// 	'Book ID'	  					=> $book_info['id'],
			// 	'Book Name'						=> $book_info['name'],
			// 	'Author Name' 					=> $book_info['author_name'],
			// 	'Text'							=> $full_text,
			// 	'Genre'		 					=> $row['genre'] ?? '',
			// 	'Creativity & Originality' 		=> [
			// 		'strengths'				  	=> $row['creativity_originality_strengths'] ?? '',
			// 		'areas_for_improvement' 	=> $row['creativity_originality_areas_for_improvement'] ?? '',
			// 		'suggestions'			  	=> $row['creativity_originality_suggestions'] ?? '',
			// 		'grade'				  		=> $row['creativity_originality_grade_10'] ?? '',
			// 	],
			//
			// 	'Character Development & Depth' 	=> [
			// 		'strengths'				  	=> $row['character_development_depth_strengths'] ?? '',
			// 		'areas_for_improvement' 	=> $row['character_development_depth_areas_for_improvement'] ?? '',
			// 		'suggestions'			  	=> $row['character_development_depth_suggestions'] ?? '',
			// 		'grade'				  		=> $row['character_development_depth_grade_10'] ?? '',
			// 	],
			//
			// 	'Plot & Storytelling' 			=> [
			// 		'strengths'				  	=> $row['plot_storytelling_strengths'] ?? '',
			// 		'areas_for_improvement' 	=> $row['plot_storytelling_areas_for_improvement'] ?? '',
			// 		'suggestions'			  	=> $row['plot_storytelling_suggestions'] ?? '',
			// 		'grade'				  		=> $row['plot_storytelling_grade_10'] ?? '',
			// 	],
			//
			// 	'Grammatical Errors' 			=> [
			// 		'strengths'				  	=> $row['grammatical_errors_strengths'] ?? '',
			// 		'areas_for_improvement' 	=> $row['grammatical_errors_areas_for_improvement'] ?? '',
			// 		'suggestions'			  	=> $row['grammatical_errors_suggestions'] ?? '',
			// 		'grade'				  		=> $row['grammatical_errors_grade_10'] ?? '',
			// 	],
			//
			// 	'Imaginative Use of Language' 	=> [
			// 		'strengths'				  	=> $row['imaginative_use_of_language_strengths'] ?? '',
			// 		'areas_for_improvement' 	=> $row['imaginative_use_of_language_areas_for_improvement'] ?? '',
			// 		'suggestions'			  	=> $row['imaginative_use_of_language_suggestions'] ?? '',
			// 		'grade'				  		=> $row['imaginative_use_of_language_grade_10'] ?? '',
			// 	],
			//
			// 	'Theme and Message' 			=> [
			// 		'strengths'				  	=> $row['theme_ideas_and_message_strengths'] ?? '',
			// 		'areas_for_improvement' 	=> $row['theme_and_message_areas_for_improvement'] ?? '',
			// 		'suggestions'			  	=> $row['theme_and_message_suggestions'] ?? '',
			// 		'grade'				  		=> $row['theme_and_message_grade_10'] ?? '',
			// 	],
			//
			// 	'Overall Impact' 				=> [
			// 		'strengths'				  	=> $row['overall_impact_strengths'] ?? '',
			// 		'areas_for_improvement' 	=> $row['overall_impact_areas_for_improvement'] ?? '',
			// 		'suggestions'			  	=> $row['overall_impact_suggestions'] ?? '',
			// 		'grade'				  		=> $row['overall_impact_grade_10'] ?? '',
			// 	],
			//
			// 	'Grade' 						=> $row['overall_grade_10'] ?? '',
			// 	'Feedback' 						=> $row['feedback'] ?? '',
			// ];
		}

		// $json_data = json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

		// $file = FCPATH . 'uploads/book_sample_data.php';
		//
		// file_put_contents($file, $json_data);

		// pr($books[0], 1);

		self::_downloadPrepCsv($books, 'book_csv');
	}

	public function buildSingleBookRank($order_id = 0) {
		$this->load->model('order/Order_model', 'order_model');
		$this->load->library('Ranking_lib', 'ranking_lib');

		if (empty($order_info = $this->order_model->get($order_id))) return;

		$this->ranking_lib->updateRank($order_info['id']);
	}

	private function _downloadPrepCsv($results = [], $filename = 'download') {
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

		self::_writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	public function getBookGiftCard($book_id = 0) {
		$this->load->model('book/Book_model', 'book_model');

		if (!empty($book_info = $this->book_model->get($book_id))) {

			$data['book_name'] 		= ucwords($book_info['name']);
			$data['author_name'] 	= ucwords($book_info['author_name']);
			$data['sku'] 			= $book_info['id'];
			// $data['image'] 			= sprintf('%s%sGift_Card/%s.png', $this->config->item('cloudfront_url') , $this->config->item('s3_user_gallery'), $image);

			$data['front_image'] 	= 'https://youbooks-storage-5fd6173683748-webdev.s3.amazonaws.com/public/EventGallery/Gift_Card/2025/front_diwali_2025_v3.png';
			$data['back_image'] 	= 'https://youbooks-storage-5fd6173683748-webdev.s3.amazonaws.com/public/EventGallery/Gift_Card/2025/back_diwali_2025_v3.png';

			$html = $this->load->view('common/image_template', $data, true);

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

			$dompdf->setPaper(
				[
					0,
					0,
					432,
					648
				],
				'portrait'
			);

			$dompdf->render();

			$file_name = 'Book_' . $book_id . '.pdf';

			$dompdf->stream($file_name);
		}
	}

	public function generateBookGiftCard($coupon_id = 0) {
		try {
			$this->load->model('book/Book_model', 'book_model');

			if (empty($coupon_id)) {
				echo 'Coupon ID not valid'; 
				return;
			} 

			$pickup_location 	= !empty($this->input->get('pickup_location')) ? urldecode($this->input->get('pickup_location')) : '1,2,3';
			$start_data 		= !empty($this->input->get('start_date')) ? urldecode($this->input->get('start_date')) : '2025-08-23 00:00:00';
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
			AND `order`.`coupon_id` = $coupon_id
			AND `order`.`pickup_location_id`IN (" . $pickup_location . ")
			AND `order`.date_added > '" . $start_data . "'
			AND `order`.date_added < '" . $end_data . "'")->result_array();

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
						'url'		=> 'https://cms.bribooks.com/home/getBookGiftCard/' . $row['product_id']
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

	public function generateBusinessCard($book_id = 0, $event_id = 0, $version = 'v1') {
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');

		if (!empty($book_info = $this->book_model->get($book_id))) {
			$book_info = $this->book_version_model->getByVersion($book_info['id'], $book_info['version']);

			$data['book_name'] 		= ucwords($book_info['name']);
			$data['author_name'] 	= ucwords($book_info['author_name']);
			$data['sku'] 			= $book_info['book_id'];
			$data['cover_image'] 	= $this->config->item('cloudfront_url') . 'public/' . $book_info['cover_image'];

			$data['front_image'] 	= base_url('assets/images/business_card_front_3x.jpg');
			// $data['front_image'] 	= base_url('assets/images/business_card_front.jpg');
			$data['inside_image'] 	= base_url('assets/images/business_card_inside.jpg');
			$data['logo'] 			= sprintf($this->config->item('s3_base_url') . $this->config->item('s3_user_gallery') . 'Business_Card/logo_%d_%s.png', (int)$event_id, $version);
			$data['qr_code']		= base_url(generateQrCode(USER_URL . 'bookstore/' . $book_info['slug'], 20, 2, 'uploads/pdfs/qrcode_' . $book_info['slug'] . '.png'));

			$data['width'] 		= 255.118;
			$data['height'] 	= 155.906;

			$html = $this->load->view('common/business_card', $data, true);

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

			$file_name = sprintf('Book_businees_card_%d_%d.pdf', $event_id , $book_id);

			$dompdf->stream($file_name);

			// $dompdf->stream('document.pdf', ['Attachment' => false]);
		}

	}
	public function checkUserPan($pan_no = '') {

		if (empty($pan_no)) echo 'Pan is required';

		$this->load->library('BankValidation_lib', 'bankvalidation_lib');

		$pan_info 	= $this->bankvalidation_lib->getPan($pan_no);

		pr($pan_info);

	}
}
