<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait NyafAuthor {
	public function updateImageNYAF() {
		$this->form_validation->set_rules('eid', _l('event_id'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);
		$this->form_validation->set_rules('uid', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('verification_code'), 'trim|required|min_length[8]|max_length[255]');

		self::_runFormValidation();

		if (!$this->json) {
			if (
				$invite_info = $this->db->get_where('event_user_invite_code', [
					'event_id'	=> (int)$this->input->post('eid'),
					'user_id'	=> (int)$this->input->post('uid'),
					'code'		=> $this->input->post('code'),
				])->row_array()
			) {
				$user_info = $this->user_model->get($invite_info['user_id']);

				if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
					list($width, $height) = getimagesize($_FILES['image']['tmp_name']);

					log_kb([
						'updateImageNYAF::' => [
							'user_id' 	=> $this->input->post('uid'),
							'code' 		=> $this->input->post('code'),
							'file' 		=> $_FILES,
							'width' 	=> $width,
							'height' 	=> $height
						]
					]);

					if ($width >= 720 && $height >= 720) {
						if (self::_validateFileUpload()) {
							$image_nyaf = 'user_' . (int)$this->input->post('uid') . '.png';

							log_kb($this->s3->amazonS3Upload(
								$image_nyaf,
								$_FILES['image']['tmp_name'],
								rtrim($this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? '' : 'test'), '/')
							));

							$this->load->model('user/UserDetails_model', 'user_details_model');

							if (
								$user_detail_info = $this->db->get_where('user_details', [
									'user_id'	=> $this->input->post('uid')
								])->row_array()
							) {
								$this->user_details_model->edit($this->input->post('uid'), [
									'image_nyaf'	=> $image_nyaf,
								]);
							} else {
								$this->user_details_model->add([
									'user_id'		=> $user_info['id'],
									'site_id'		=> $user_info['site_id'],
									'image_nyaf'	=> $image_nyaf,
								]);
							}

							$this->json['success'] = _l('image_successfully_saved');
						} else {
							$this->json['error'] = _li('Invalid image format');
						}
					} else {
						$this->json['error'] = _li('Image could not be uploaded! Dimensions should be greater than 1000 X 1000');
					}
				} else {
					$this->json['error'] = _li('Invalid image');
				}
			} else {
				$this->json['error'] = _li('Invalid request');
			}
		}
	}

	public function getGuestDetailsNYAF() {
		$this->form_validation->set_rules('uid', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('verification_code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('bid', _l('book_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('invite', _l('Author Invite'), 'trim|required|in_list[yes,no]');

		if ($this->input->post('invite') === 'yes') {
			$this->form_validation->set_rules('fullName1', _l('Guest Name 1'), 'trim|required|min_length[3]');
			$this->form_validation->set_rules('relation1', _l('Guest Relation 1'), 'trim|required|in_list[father,mother,guardian]');
			$this->form_validation->set_rules('fullName2', _l('Guest Name 2'), 'trim|min_length[3]');
			$this->form_validation->set_rules('relation2', _l('Guest Relation 2'), 'trim|in_list[father,mother,guardian]');

			if (mb_strtolower($this->config->item('site_country_code')) == 'in') {
				$this->form_validation->set_rules('aadharNumber1', _l('Guest ID Proof 1'), 'trim|required|min_length[4]|max_length[16]');
				$this->form_validation->set_rules('aadharNumber2', _l('Guest ID Proof 2'), 'trim|min_length[4]|max_length[16]');
			} else {
				$this->form_validation->set_rules('aadharNumber1', _l('Guest ID Proof 1'), 'trim|required|min_length[4]|max_length[30]');
				$this->form_validation->set_rules('aadharNumber2', _l('Guest ID Proof 2'), 'trim|min_length[4]|max_length[30]');
			}
		}

		self::_runFormValidation();

		if (!$this->json) {
			log_kb([
				'getGuestDetailsNYAF::' => [
					'post' => $_POST,
					'file' => $_FILES
				]
			]);

			if (empty($this->input->post('form_code')) || empty($form_invite_info = $this->db->get_where('event_form_invite_code', [
				'event_id'	=> (int)$this->input->post('eid'),
				'code'		=> $this->input->post('form_code'),
			])->row_array()) || empty($form_invite_info['end_date'])) {
				return $this->json['error'] = _li('Invalid Form');
				
			}

			if (!empty($form_invite_info) && $form_invite_info['end_date'] < date('Y-m-d H:i:s')) {
				return $this->json['error'] = _li('Form Expired');
			}

			$user_invite_info = $this->db->get_where('user_details_nyaf_invites', [
				'user_id'	=> (int)$this->input->post('uid'),
				'book_id'	=> (int)$this->input->post('bid'),
				'event_id'	=> (int)$this->input->post('eid'),
				'_deleted'	=> 0,
			])->row_array();

			if (empty($user_invite_info)) {
				$this->json['error'] = _li('Invalid request');
				return;
			}

			$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');

			$user_details_nyaf_invites_info = $this->user_details_invite_model->get($user_invite_info['id']);

			if ($this->input->post('invite') === 'no') {
				$this->user_details_invite_model->edit($user_invite_info['id'], [
					'status'		=> 2
				]);

				$this->json['success'] = _li('Details submitted');
				return;
			}

			if ($user_details_guest_info = $this->db->get_where('user_details_nyaf_guest', ['user_id'	=> $this->input->post('uid'), 'book_id' => $this->input->post('bid'), 'event_id' => $this->input->post('eid')])->row_array()) {
				$this->json['error'] = _li('Details already submitted');
				return;
			}

			$invite_info = $this->db->get_where('event_user_invite_code', [
				'user_id'	=> (int)$this->input->post('uid'),
				'code'		=> $this->input->post('code'),
			])->row_array();

			if (empty($invite_info)) {
				$this->json['error'] = _li('Invalid user details');
				return;
			}

			$user_info = $this->user_model->get($invite_info['user_id']);

			if (empty($user_info)) {
				$this->json['error'] = _li('Invalid user details');
				return;
			}

			$book_info = $this->db->get_where('book', [
				'id'	=> $this->input->post('bid'),
				'user_id'	=> $this->input->post('uid')
			])->row_array();

			if (empty($book_info)) {
				$this->json['error'] = _li('Invalid book details');
				return;
			}

			$save = [];
			$no_of_guest = 0;

			$code = sha1(md5($user_info['id'] . '_' . $book_info['id'] . $this->config->item('password_salt')));

			if (isset($_FILES['image1']) && ($_FILES['image1']['size'] > 0) && !empty($this->input->post('fullName1')) && !empty($this->input->post('relation1')) && !empty($this->input->post('aadharNumber1'))) {
				if (self::_validateFileUpload('image1')) {
					$no_of_guest = 1;

					$aadhar_image_1 = 'user_' . (int)$this->input->post('uid') . '_' . (int)$this->input->post('bid') . '_1.png';

					log_kb($this->s3->amazonS3Upload(
						$aadhar_image_1,
						$_FILES['image1']['tmp_name'],
						rtrim($this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? 'aadhar_images' : 'aadhar_images/test'), '/')
					));

					$save = [
						'user_id'		=> $user_info['id'],
						'event_id'		=> $user_invite_info['event_id'] ?? 0,
						'site_id'		=> $user_info['site_id'],
						'book_id'		=> $book_info['id'],
						'author_aadhar'	=> $this->input->post('author_aadhar'),
						'guest_name_1'	=> $this->input->post('fullName1'),
						'relation_1'	=> $this->input->post('relation1'),
						'aadhar_no_1'	=> $this->input->post('aadharNumber1'),
						'aadhar_image_1'=> $aadhar_image_1,
						'code'			=> $code,
					];
				}
			}

			if (isset($_FILES['image2']) && ($_FILES['image2']['size'] > 0) && !empty($this->input->post('fullName2')) && !empty($this->input->post('relation2')) && !empty($this->input->post('aadharNumber2'))) {
				if (self::_validateFileUpload('image2')) {
					$no_of_guest = 2;

					$aadhar_image_2 = 'user_' . (int)$this->input->post('uid') . '_' . (int)$this->input->post('bid') . '_2.png';

					log_kb($this->s3->amazonS3Upload(
						$aadhar_image_2,
						$_FILES['image2']['tmp_name'],
						rtrim($this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? 'aadhar_images' : 'aadhar_images/test'), '/')
					));

					$save = $save + [
						'guest_name_2'	=> $this->input->post('fullName2'),
						'relation_2'	=> $this->input->post('relation2'),
						'aadhar_no_2'	=> $this->input->post('aadharNumber2'),
						'aadhar_image_2'=> $aadhar_image_2
					];
				}
			}

			if (isset($_FILES['author_aadhar_image']) && ($_FILES['author_aadhar_image']['size'] > 0)) {
				if (self::_validateFileUpload('author_aadhar_image')) {
					$no_of_guest = 2;

					$author_aadhar_image = 'user_' . (int)$this->input->post('uid') . '_' . (int)$this->input->post('bid') . '_author_aadhar_image.png';

					log_kb($this->s3->amazonS3Upload(
						$author_aadhar_image,
						$_FILES['author_aadhar_image']['tmp_name'],
						rtrim($this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? 'aadhar_images' : 'aadhar_images/test'), '/')
					));

					$save = $save + [
						'author_aadhar_image'	=> $author_aadhar_image
					];
				}
			}

			if (isset($_FILES['author_image']) && ($_FILES['author_image']['size'] > 0)) {
				if (self::_validateFileUpload('author_image')) {
					$no_of_guest = 2;

					$author_image = 'user_' . (int)$this->input->post('uid') . '_' . (int)$this->input->post('bid') . '_author_image.png';

					log_kb($this->s3->amazonS3Upload(
						$author_image,
						$_FILES['author_image']['tmp_name'],
						rtrim($this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? 'aadhar_images' : 'aadhar_images/test'), '/')
					));

					$save = $save + [
						'author_image'	=> $author_image
					];
				}
			}

			if (empty($save)) {
				$this->json['error'] = _li('Invalid request');
				return;
			}

			$this->load->model('user/UserDetailsGuest_model', 'user_details_guest_model');

			if (empty($user_details_guest_info)) {
				$user_details_guest_id = $this->user_details_guest_model->add($save);
			} else {
				$user_details_guest_id = $user_details_guest_info['id'];
				$this->user_details_guest_model->edit($user_details_guest_id, $save);
			}

			$this->user_details_invite_model->edit($user_invite_info['id'], [
				'no_of_guest'	=> $no_of_guest,
				'status'		=> 1
			]);

			$this->load->model('common/Cron_model', 'cron_model');

			// if (empty($this->cron_model->getByCode('userDetailsGuestSC_' . $user_details_guest_id))) {
			// 	$this->cron_model->add([
			// 		'code'			=> 'userDetailsGuestSC_' . $user_details_guest_id,
			// 		'action'		=> 'alert_model->userDetailsGuestSC',
			// 		'data'			=> [$user_details_guest_id],
			// 		'site_id'		=> (int)$this->config->item('site_id'),
			// 		'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
			// 			? '+2 minutes'
			// 			: '+1 minutes'
			// 		)),
			// 	]);
			// } else {
			// 	$this->cron_model->editByCode('userDetailsGuestSC_' . $user_details_guest_id, [
			// 		'data' => [$user_details_guest_id],
			// 		'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
			// 			? '+2 minutes'
			// 			: '+1 minutes'
			// 		)),
			// 		'status' => 0
			// 	]);
			// }

			$user_details_info = $this->db->get_where('user_details', ['user_id' => $user_info['id']])->row_array();

			$author_image = $this->config->item('s3_base_url') . 'public/' . $book_info['author_image'];

			if (!empty($user_details_info['image_nyaf'])) {
				$s3_dirname = $this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf');

				$author_image = $s3_dirname . (ENVIRONMENT === 'production' ? '' : 'test/') . $user_details_info['image_nyaf'];
			}

			$qrcode_url = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), 'author_invite_qr.png');

			$this->json['success'] = _l('details_successfully_saved');
			$this->json['data']['qr_url'] = $qrcode_url;
			$this->json['data']['pdf_url'] = '';
			$this->json['data']['uploaded_image'] = $author_image;
			$this->json['data']['rank'] = $user_details_nyaf_invites_info['book_rank'] ?? '';
			$this->json['data']['fullName1'] = $this->input->post('fullName1');

			if ($this->input->post('fullName2')) {
				$this->json['data']['fullName2'] = $this->input->post('fullName2');
			}
		}
	}

	private function _getQrCode($code = '') {
		// return;

		if (file_exists('uploads/eventpass/qrcode_'.$code.'.png'))
			return base_url().'uploads/eventpass/qrcode_'.$code.'.png';

		$dir = FCPATH . 'uploads/eventpass';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$file = 'uploads/eventpass/qrcode_' . $code . '.png';

		$logo = imagecreatefrompng(FCPATH . 'assets/images/logo.png');
		$logo_width = imagesx($logo);
		$logo_height = imagesy($logo);

		$qr_img = imagecreatefrompng(vsprintf('https://chart.googleapis.com/chart?cht=qr&chld=H|0&chs=512x512&chl=%s', [
			urlencode(USER_URL . 'author_data/' . $code),
		]));

		$qr_img_width = imagesx($qr_img);
		$qr_img_height = imagesy($qr_img);

		imagecopyresampled(
			$qr_img,
			$logo,
			($qr_img_width / 2 - 150),
			($qr_img_height / 2 - 150),
			0,
			0,
			300,
			300,
			$logo_width,
			$logo_height
		);

		imagepng($qr_img, $file);

		return base_url($file);
	}

	private function _getPdf($code = '') {
		// return;

		// if (file_exists('uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf'))
		// 	return base_url().'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf';

		$dir = FCPATH . 'uploads/eventpass/pdfs';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_author_pdf_template', [
			'code' => $code,
			'head_logo' => base_url('assets/images/nyaf_logo_2025.png')
		], true);
		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A3', 'potrait');
		$dompdf->render();
		$file = 'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
		return base_url($file);
	}

	public function getSchoolDetailsNYAF() {
		$this->form_validation->set_rules('site_id', _l('site_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('site_code'), 'trim|required|min_length[6]|max_length[20]');
		$this->form_validation->set_rules('invite', _l('School Invite'), 'trim|required|in_list[yes,no]');

		if ($this->input->post('invite') === 'yes') {
			$this->form_validation->set_rules('fullName1', _l('Guest Name 1'), 'trim|required|min_length[3]');
			$this->form_validation->set_rules('relation1', _l('Guest Relation 1'), 'trim|required|in_list[male,female]');
			$this->form_validation->set_rules('aadharNumber1', _l('Guest ID Proof 1'), 'trim|required|min_length[4]|max_length[30]');
		}

		self::_runFormValidation();

		if (!$this->json) {
			log_kb([
				'getSchoolDetailsNYAF::' => [
					'post' => $_POST,
					'file' => $_FILES
				]
			]);

			$school_invite_info = $this->db->get_where('school_details_nyaf_invites', [
				'site_id'	=> (int)$this->input->post('site_id'),
				'event_id'	=> (int)$this->input->post('eid')
			])->row_array();

			if (empty($school_invite_info)) {
				$this->json['error'] = _li('Invalid request');
				return;
			}

			if ($this->input->post('invite') === 'no') {
				$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');

				$this->school_details_invite_model->edit($school_invite_info['id'], [
					'status'		=> 2
				]);

				$this->json['success'] = _li('Details submitted');
				return;
			}

			if ($school_details_guest_info = $this->db->get_where('school_details_nyaf_guest', ['site_id'	=> $this->input->post('site_id'), 'event_id'	=> $this->input->post('eid')])->row_array()) {
				$this->json['error'] = _li('Details already submitted');
				return;
			}

			$site_info = $this->db->get_where('site', [
				'id'	=> $this->input->post('site_id'),
				'site_code'	=> $this->input->post('code')
			])->row_array();

			if (empty($site_info)) {
				$this->json['error'] = _li('Invalid school details');
				return;
			}

			$save = [];
			$no_of_guest = 0;

			$code = sha1(md5($site_info['id'] . $this->config->item('password_salt')));

			if (isset($_FILES['image1']) && ($_FILES['image1']['size'] > 0) && !empty($this->input->post('fullName1')) && !empty($this->input->post('relation1')) && !empty($this->input->post('aadharNumber1'))) {
				if (self::_validateFileUpload('image1')) {
					$no_of_guest = 1;

					$aadhar_image_1 = 'user_' . (int)$this->input->post('site_id') . '_1.png';

					log_kb($this->s3->amazonS3Upload(
						$aadhar_image_1,
						$_FILES['image1']['tmp_name'],
						rtrim($this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? 'aadhar_images_school' : 'aadhar_images_school/test'), '/')
					));

					$save = [
						'site_id'		=> $site_info['id'],
						'event_id'		=> $this->input->post('eid'),
						'guest_name_1'	=> $this->input->post('fullName1'),
						'relation_1'	=> $this->input->post('relation1'),
						'aadhar_no_1'	=> $this->input->post('aadharNumber1'),
						'aadhar_image_1'=> $aadhar_image_1,
						'code'			=> $code,
					];
				}
			}

			if (empty($save)) {
				$this->json['error'] = _li('Invalid request');
				return;
			}

			$this->load->model('school/SchoolDetailsGuest_model', 'school_details_guest_model');

			if (empty($school_details_guest_info)) {
				$school_details_guest_id = $this->school_details_guest_model->add($save);
			} else {
				$school_details_guest_id = $school_details_guest_info['id'];
				$this->school_details_guest_model->edit($school_details_guest_id, $save);
			}

			$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');

			$this->school_details_invite_model->edit($school_invite_info['id'], [
				'no_of_guest'	=> $no_of_guest,
				'status'		=> 1
			]);

			// $this->load->model('common/Cron_model', 'cron_model');

			// if (empty($this->cron_model->getByCode('schoolDetailsGuestSC_' . $school_details_guest_id))) {
			// 	$this->cron_model->add([
			// 		'code'			=> 'schoolDetailsGuestSC_' . $school_details_guest_id,
			// 		'action'		=> 'alert_model->schoolDetailsGuestSC',
			// 		'data'			=> [$school_details_guest_id],
			// 		'site_id'		=> (int)$this->config->item('site_id'),
			// 		'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
			// 			? '+2 minutes'
			// 			: '+1 minutes'
			// 		)),
			// 	]);
			// } else {
			// 	$this->cron_model->editByCode('schoolDetailsGuestSC_' . $school_details_guest_id, [
			// 		'data' => [$school_details_guest_id],
			// 		'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
			// 			? '+2 minutes'
			// 			: '+1 minutes'
			// 		)),
			// 		'status' => 0
			// 	]);
			// }

			$qrcode_url = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), 'school_invite_qr.png');

			$this->json['success'] = _l('details_successfully_saved');
			$this->json['data']['qr_url'] = $qrcode_url;
			$this->json['data']['pdf_url'] = '';
			$this->json['data']['fullName1'] = $this->input->post('fullName1');
		}
	}

	private function _getQrCodeSchool($code = '') {
		// return;

		if (file_exists('uploads/eventpass/qrcode_'.$code.'.png'))
			return base_url().'uploads/eventpass/qrcode_'.$code.'.png';

		$dir = FCPATH . 'uploads/eventpass';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$file = 'uploads/eventpass/qrcode_' . $code . '.png';

		$logo = imagecreatefrompng(FCPATH . 'assets/images/logo.png');
		$logo_width = imagesx($logo);
		$logo_height = imagesy($logo);

		$qr_img = imagecreatefrompng(vsprintf('https://chart.googleapis.com/chart?cht=qr&chld=H|0&chs=512x512&chl=%s', [
			urlencode(USER_URL . 'school_data/' . $code),
		]));

		$qr_img_width = imagesx($qr_img);
		$qr_img_height = imagesy($qr_img);

		imagecopyresampled(
			$qr_img,
			$logo,
			($qr_img_width / 2 - 150),
			($qr_img_height / 2 - 150),
			0,
			0,
			300,
			300,
			$logo_width,
			$logo_height
		);

		imagepng($qr_img, $file);

		return base_url($file);
	}

	private function _getPdfSchool($code = '') {
		// return;

		if (file_exists('uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf'))
			return base_url().'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf';

		$dir = FCPATH . 'uploads/eventpass/pdfs';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_school_pdf_template', ['code' => $code], true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A3', 'potrait');
		$dompdf->render();
		$file = 'uploads/eventpass/pdfs/entry_pass_'.$code.'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
		return base_url($file);
	}

	public function updateAddressNYAF() {
		return;

		$this->form_validation->set_rules('uid', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('verification_code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('bid', _l('book_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('fullName', _l('Author Name'), 'trim|required');
		$this->form_validation->set_rules('phoneNumber', _l('Phone Number'), 'trim|required|numeric');
		$this->form_validation->set_rules('deliveryDate', _l('Delivery Date'), 'trim|required');
		$this->form_validation->set_rules('address', _l('Delivery Address'), 'trim|required');
		$this->form_validation->set_rules('landmark', _l('Delivery Landmark'), 'trim|required');
		$this->form_validation->set_rules('pincode', _l('Delivery Pincode'), 'trim|required|numeric');

		self::_runFormValidation();

		if (!$this->json) {
			if ($this->db->get_where('users', ['id'	=> $this->input->post('uid'), 'verification_code'	=> $this->input->post('code')])->row_array()) {
				log_kb([
					'updateAddressNYAF::' => [
						'request' => $this->input->post()
					]
				]);

				$this->load->model('user/UserCertificateAddress_model', 'user_certificate_address_model');

				$address = json_encode([
					'full_name'		=> $this->input->post('fullName'),
					'mobile'		=> $this->input->post('phoneNumber'),
					'address'		=> $this->input->post('address'),
					'delivery_date'	=> $this->input->post('deliveryDate'),
					'landmark'		=> $this->input->post('landmark'),
					'pincode'		=> $this->input->post('pincode')
				]);

				if ($user_certificate_address_info = $this->user_certificate_address_model->getByIds($this->input->post('uid'), $this->input->post('bid'))) {
					$this->user_certificate_address_model->edit($user_certificate_address_info['id'], [
						'address'	=> $address
					]);
				} else {
					$this->user_certificate_address_model->add([
						'user_id'		=> $this->input->post('uid'),
						'book_id'		=> $this->input->post('bid'),
						'address'		=> $address,
					]);
				}

				$this->json['success'] = _l('address_successfully_saved');
			} else {
				$this->json['error'] = _li('Invalid request');
			}
		} else {
			$this->json['error'] = _li('Invalid request');
		}
	}

	public function updateSiteAddressNYAF() {
		return;

		$this->form_validation->set_rules('site_id', _l('site_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('site_code'), 'trim|required|min_length[6]|max_length[20]');
		$this->form_validation->set_rules('fullName', _l('School Name'), 'trim|required');
		$this->form_validation->set_rules('phoneNumber', _l('Phone Number'), 'trim|required|numeric');
		$this->form_validation->set_rules('deliveryDate', _l('Delivery Date'), 'trim|required');
		$this->form_validation->set_rules('address', _l('Delivery Address'), 'trim|required');
		$this->form_validation->set_rules('landmark', _l('Delivery Landmark'), 'trim|required');
		$this->form_validation->set_rules('pincode', _l('Delivery Pincode'), 'trim|required|numeric');

		self::_runFormValidation();

		if (!$this->json) {
			if ($this->db->get_where('school_details_nyaf_invites', ['site_id'	=> $this->input->post('site_id')])->row_array()) {
				log_kb([
					'updateSiteAddressNYAF::' => [
						'request' => $this->input->post()
					]
				]);

				$this->load->model('school/SchoolCertificateAddress_model', 'school_certificate_address_model');

				$address = json_encode([
					'full_name'		=> $this->input->post('fullName'),
					'mobile'		=> $this->input->post('phoneNumber'),
					'address'		=> $this->input->post('address'),
					'delivery_date'	=> $this->input->post('deliveryDate'),
					'landmark'		=> $this->input->post('landmark'),
					'pincode'		=> $this->input->post('pincode')
				]);

				if ($school_certificate_address_info = $this->school_certificate_address_model->getByIds($this->input->post('uid'), $this->input->post('bid'))) {
					$this->school_certificate_address_model->edit($school_certificate_address_info['id'], [
						'address'	=> $address
					]);
				} else {
					$this->school_certificate_address_model->add([
						'site_id'		=> $this->input->post('site_id'),
						'address'		=> $address,
					]);
				}

				$this->json['success'] = _l('address_successfully_saved');
			} else {
				$this->json['error'] = _li('Invalid request');
			}
		} else {
			$this->json['error'] = _li('Invalid request');
		}
	}

	public function getTeacherDetailsNYAF() {
		$this->form_validation->set_rules('uid', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('verification_code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('invite', _l('Author Invite'), 'trim|required|in_list[yes,no]');

		if ($this->input->post('invite') === 'yes') {
			$this->form_validation->set_rules('fullName1', _l('Guest Name 1'), 'trim|required|min_length[3]');
			$this->form_validation->set_rules('relation1', _l('Guest Relation 1'), 'trim|required');
		}

		self::_runFormValidation();

		if (!$this->json) {
			log_kb([
				'getGuestDetailsNYAF::' => [
					'post' => $_POST,
					'file' => $_FILES
				]
			]);

			$user_invite_info = $this->db->get_where('user_details_nyaf_invites', [
				'user_id'	=> (int)$this->input->post('uid'),
				'event_id'	=> (int)$this->input->post('eid'),
				'_deleted'	=> 0,
			])->row_array();

			if (empty($user_invite_info)) {
				$this->json['error'] = _li('Invalid request');
				return;
			}

			$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');

			$user_details_nyaf_invites_info = $this->user_details_invite_model->get($user_invite_info['id']);

			if ($this->input->post('invite') === 'no') {
				$this->user_details_invite_model->edit($user_invite_info['id'], [
					'status'		=> 2
				]);

				$this->json['success'] = _li('Details submitted');
				return;
			}

			if ($user_details_guest_info = $this->db->get_where('user_details_nyaf_guest', ['user_id'	=> $this->input->post('uid'), 'book_id' => $this->input->post('bid'), 'event_id' => $this->input->post('eid')])->row_array()) {
				$this->json['error'] = _li('Details already submitted');
				return;
			}

			$user_info = $this->db->get_where('users', [
				'id'				=> $this->input->post('uid'),
				'verification_code'	=> $this->input->post('code')
			])->row_array();

			if (empty($user_info)) {
				$this->json['error'] = _li('Invalid user details');
				return;
			}

			$save = [];
			$no_of_guest = 0;

			$code = sha1(md5($user_info['id'] . '_' . $this->input->post('eid') . $this->config->item('password_salt')));

			$save = [
				'user_id'		=> $user_info['id'],
				'event_id'		=> $user_invite_info['event_id'] ?? 0,
				'site_id'		=> $user_info['site_id'] ?? 0,
				'guest_name_1'	=> $this->input->post('fullName1'),
				'relation_1'	=> $this->input->post('relation1'),
				'author_aadhar'	=> $this->input->post('author_aadhar'),
				'code'			=> $code,
			];

			if (isset($_FILES['author_aadhar_image']) && ($_FILES['author_aadhar_image']['size'] > 0)) {
				if (self::_validateFileUpload('author_aadhar_image')) {
					$no_of_guest = 2;

					$author_aadhar_image = 'user_' . (int)$this->input->post('uid') . '_' . (int)$this->input->post('bid') . '_author_aadhar_image.png';

					log_kb($this->s3->amazonS3Upload(
						$author_aadhar_image,
						$_FILES['author_aadhar_image']['tmp_name'],
						rtrim($this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? 'aadhar_images' : 'aadhar_images/test'), '/')
					));

					$save = $save + [
						'author_aadhar_image'	=> $author_aadhar_image
					];
				}
			}

			if (empty($save)) {
				$this->json['error'] = _li('Invalid request');
				return;
			}

			$this->load->model('user/UserDetailsGuest_model', 'user_details_guest_model');

			if (empty($user_details_guest_info)) {
				$user_details_guest_id = $this->user_details_guest_model->add($save);
			} else {
				$user_details_guest_id = $user_details_guest_info['id'];
				$this->user_details_guest_model->edit($user_details_guest_id, $save);
			}

			$this->user_details_invite_model->edit($user_invite_info['id'], [
				'no_of_guest'	=> $no_of_guest,
				'status'		=> 1
			]);

			$qrcode_url = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), 'teacher_invite_qr.png');

			$this->json['success'] = _l('details_successfully_saved');
			$this->json['data']['qr_url'] = $qrcode_url;
			$this->json['data']['pdf_url'] = '';
			$this->json['data']['fullName1'] = $this->input->post('fullName1');

			if ($this->input->post('fullName2')) {
				$this->json['data']['fullName2'] = $this->input->post('fullName2');
			}
		}
	}
}
