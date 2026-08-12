<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait BookExhibition {
	public function getInviteSlot(){
		$this->load->model('common/InviteSlot_model', 'invite_slot_model');
		$this->load->model('book/BookExhibition_model', 'book_exhibition_model');

		$slots 		= $this->invite_slot_model->get_all(['order' => 'ASC'])['rows'] ?? [];
		$slot_data 	= [];

		foreach ($slots as $slot) {
			if ($this->book_exhibition_model->get_all([
				'slot_id'  => $slot['id'],
			])['total'] < 200) {
				$slot_data[] = [
					'id'		 => $slot['id'],
					'slot_start' => $slot['slot_start'],
					'slot_end'   => $slot['slot_end']
				];
			}
		}

		if (!empty($slot_data)) {
			$this->json['success'] 	= _l('success');
			$this->json['slots'] 	= $slot_data;
		} else {
			$this->json['error'] 	= _l('Slots_are_not_available');
		}
	}

	public function sendExhibitionOtp() {
		$this->form_validation->set_rules('event_id', _l('Event Id'), 'trim|required|numeric');
		$this->form_validation->set_rules('name', _l('Name'), 'trim|required|min_length[3]');
		$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
			'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
			'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
		]);
		$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
		$this->form_validation->set_rules('guest_count', _l('No of guest'), 'trim|required|numeric');
		$this->form_validation->set_rules('slot_id', _l('Slot'), 'trim|required|numeric');
		$this->form_validation->set_rules('proof_id', _l('Guest ID Proof'), 'trim|required');

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_verifyCaptcha()) {
				$this->json['error'] = _li('Invalid Captcha. Please try again.');
				return;
			}

			if (!$this->spam_lib->validate(1)) {
				return;
			}

			$this->load->model('book/BookExhibition_model', 'book_exhibition_model');

			if (!empty($this->book_exhibition_model->get_all([
				'email'		=> $this->input->post('email')
			])['rows'] ?? [])) {
				$this->json['error'] = _li('Email_is_already_register');
				return;
			}

			if (!empty($this->book_exhibition_model->get_all([
				'mobile'		=> $this->input->post('mobile')
			])['rows'] ?? [])) {
				$this->json['error'] = _li('Mobile_is_already_register');
				return;
			}

			self::_executeOtp('mobile');
		}
	}

	public function updateExhibitionInvite () {
		$this->form_validation->set_rules('invite', _l('Author Invite'), 'trim|required|in_list[yes,no]');

		if ($this->input->post('invite') === 'yes') {
			$this->form_validation->set_rules('event_id', _l('Event Id'), 'trim|required|numeric');
			$this->form_validation->set_rules('name', _l('Name'), 'trim|required|min_length[3]');

			if (!empty($this->input->post('uid'))) {
				$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|numeric|min_length[10]|max_length[15]', [
					'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
					'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
				]);
				$this->form_validation->set_rules('email', _l('email'), 'trim|valid_email|max_length[200]');
			} else {
				$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|numeric|min_length[10]|max_length[15]', [
					'min_length'	=> _li('Please enter a valid 10 digit mobile number'),
					'max_length'	=> _li('Please enter a valid 15 digit mobile number'),
				]);
				$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email|max_length[200]');
			}

			$this->form_validation->set_rules('guest_count', _l('No of guest'), 'trim|required|numeric');
			$this->form_validation->set_rules('slot_id', _l('Slot'), 'trim|required|numeric');
			$this->form_validation->set_rules('proof_id', _l('Guest ID Proof'), 'trim|required');
		}

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('book/BookExhibition_model', 'book_exhibition_model');
			$this->load->model('common/InviteSlot_model', 'invite_slot_model');
			$this->load->library('s3');

			if ($this->input->post('invite') === 'no') {
				if (!empty($this->input->post('uid'))) {
					$this->book_exhibition_model->editByUserEvent($this->input->post('uid'), [
						'status'		=> 2
					]);
				}

				$this->json['success'] = _li('Details submitted');

				return;
			}

			if (!empty($this->input->post('uid'))) {
				if (empty($this->book_exhibition_model->get_all([
					'user_id'  => $this->input->post('uid'),
					'event_id' => $this->input->post('event_id'),
				])['rows'][0] ?? '')) {
					$this->json['error'] = _li('Invalid user details');
					return;
				}

				if (!empty($this->book_exhibition_model->get_all([
					'user_id'  => $this->input->post('uid'),
					'event_id' => $this->input->post('event_id'),
					'status'   => 1
				])['rows'][0] ?? '')) {
					$this->json['error'] = _li('Details already submitted');
					return;
				}
			}

			if (empty($this->input->post('uid')) && !empty($this->input->post('otp'))) {
				if (!self::_verifyOtp('mobile')) {
					$this->json['error'] = _li('please_enter_the_correct_verification_code');
					return;
				}
			}

			$code = sha1(md5(($this->input->post('uid') ?? 0) . '_' . uniqid() . $this->config->item('password_salt')));

			$save = [
				'event_id'		=> $this->input->post('event_id'),
				'user_id'		=> $this->input->post('uid') ?? 0,
				'name'			=> $this->input->post('name'),
				'email'			=> $this->input->post('email'),
				'mobile'		=> $this->input->post('mobile'),
				'guest_count'	=> $this->input->post('guest_count'),
				'slot_id'		=> $this->input->post('slot_id'),
				'proof_id'		=> $this->input->post('proof_id'),
				'code'			=> $code,
				'status'		=> 1,
			];

			$image_name = '';

			if (isset($_FILES['image']) && ($_FILES['image']['size'] > 0)) {
				if (self::_validateFileUpload('image')) {
					$image_name = 'user_' . (int)$this->input->post('event_id') . '_' . uniqid() . '.png';

					log_kb($this->s3->amazonS3Upload(
						$image_name,
						$_FILES['image']['tmp_name'],
						rtrim($this->config->item('s3_exhibition_images') . (ENVIRONMENT === 'production' ? 'exhibition' : 'exhibition/test'), '/')
					));

					$save['image'] = $image_name;
				}
			}

			if (!empty($this->input->post('uid'))) {
				$invite_info  =  $this->book_exhibition_model->get_all([
					'user_id'  => $this->input->post('uid'),
					'event_id' => $this->input->post('event_id'),
				])['rows'][0] ?? [];

				$this->book_exhibition_model->editByUserEvent($this->input->post('uid'),$save);

				$invite_id = !empty($invite_info) ? $invite_info['id'] : 0;
			} else {
				$invite_id = $this->book_exhibition_model->add($save);
			}

			if (empty($this->cron_model->getByCode('bookExhibitionInviteCron_' . $invite_id))) {
				$this->cron_model->add([
					'code'			=> 'bookExhibitionInviteCron_' . $invite_id,
					'action'		=> 'alert_model->bookExhibitionInviteCron',
					'data'			=> [$invite_id],
					'site_id'		=> 1,
					'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
						? '+2 minutes'
						: '+1 minutes'
					)),
				]);
			} else {
				$this->cron_model->editByCode('bookExhibitionInviteCron_' . $invite_id, [
					'data' => [$invite_id],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
						? '+2 minutes'
						: '+1 minutes'
					)),
					'status' => 0
				]);
			}

			$s3_dirname 	= $this->config->item('s3_base_url') . $this->config->item('s3_exhibition_images') . (ENVIRONMENT === 'production' ? 'exhibition/' : 'exhibition/test/');
			$author_image 	= empty($image_name) ? base_url('uploads/user_image/placeholder.png') : $s3_dirname. $image_name;

			$slot_info = $this->invite_slot_model->get($this->input->post('slot_id'));
			$time_slot = date("h:i A", strtotime($slot_info['slot_start'])) . ' - ' . date("h:i A", strtotime($slot_info['slot_end']));

			$pdf_data = [
				'guest_count'   => $this->input->post('guest_count'),
				'name'		  	=> $this->input->post('name'),
				'author_image'  => $author_image,
				'time_slot'	 	=> $time_slot
			];

			$this->json['success']		  	= _l('details_successfully_saved');
			$this->json['data']['qr_url']   = self::_getExhibitionQrCode($code);
			$this->json['data']['pdf_url']  = self::_getExhibitionPdf($code, $pdf_data);
			$this->json['data']['image']	= $author_image;
			$this->json['data']['name']	 	= $this->input->post('name');
			$this->json['data']['email']	= $this->input->post('email');
			$this->json['data']['mobile']   = $this->input->post('mobile');
		}
	}

	private function _getExhibitionQrCode($code = '') {
		$dir = FCPATH . 'uploads/exhibitionpass/pdfs';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$file = 'uploads/exhibitionpass/qrcode_' . $code . '.png';

		// $qr_file 	= generateQrCode((USER_URL . 'author_data/' . $code), 30, 1.5);

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

	private function _getExhibitionPdf($code = '', $data = []) {
		$dir = FCPATH . 'uploads/exhibitionpass/pdfs';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/exhibition_invite_pdf_template', [
			'code'		  	=> $code,
			'head_logo'	 	=> base_url('assets/images/pass_logo.png'),
			'qr_code'	   	=> base_url('uploads/exhibitionpass/qrcode_' . $code . '.png'),
			'location'	  	=> base_url('assets/images/location.svg'),
			'guest_count'   => $data['guest_count'],
			'name'		  	=> $data['name'],
			'author_image'  => $data['author_image'],
			'slot'		  	=> $data['time_slot']
		], true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A3', 'potrait');
		$dompdf->render();

		$file = 'uploads/exhibitionpass/pdfs/entry_pass_' . $code . '.pdf';

		$output = $dompdf->output();
		file_put_contents(FCPATH . $file, $output);

		return base_url($file);
	}

	public function updateEventInviteDetails() {

		$this->form_validation->set_rules('user_id', _l('user'), [
			'trim',
			'required',
			'numeric',
		]);

		$this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
		]);

		$this->form_validation->set_rules('resp', _l('response'), [
			'trim',
			'required',
			'in_list[yes,no]'
		]);

		self::_runFormValidation();

		if (!$this->json) {

			$this->load->model('user/UserEventInvitation_model', 'user_event_invitation_model');

			$invite_info = $this->user_event_invitation_model->get_all([
				'user_id'		=> $this->input->post('user_id'),
				'event_id'		=> $this->input->post('event_id'),
			])['rows'][0] ?? '';

			if (!empty($invite_info)) {

				if ($invite_info['status'] == 1) {
					return $this->json['success'] = _l('already submitted');
				}

				$status = [
					'yes'	=> 1,
					'no' 	=> 2
				];

				$this->user_event_invitation_model->edit($invite_info['id'], [
					'status'				=> $status[$this->input->post('resp')]
				]);

				$this->json['success'] = _li('submitted');
			} else {
				$this->json['error'] = _li('Invalid URL');
			}
		}
	}
}
