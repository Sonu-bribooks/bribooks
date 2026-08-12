<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CustomCover {
	public function getCustomCovers() {
		if (!$this->json) {
			$filter_data = [
				'user_id'	=> (int)$this->session->userdata('user_id'),
				'status'	=> 1
			];

			$result = array_map(function ($item) {
				return $item;
			}, $this->custom_cover_model->get_all($filter_data)['rows'] ?? []);

			$this->json['custom_covers'] = $result;
		}
	}

	public function saveCustomCover() {
		if (!$this->json) {
			if (!self::_validateSubscription()) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
				if (self::_validateFileUpload()) {
					$img_name = uniqid() . '_' . $this->session->userdata('user_id') . '.png';

					log_kb($this->s3->amazonS3Upload(
						$img_name,
						$_FILES['image']['tmp_name'],
						rtrim($this->config->item('s3_custom_covers'), '/')
					));

					$custom_cover_id = $this->custom_cover_model->add([
						'user_id'	=> $this->session->userdata('user_id'),
						'image'		=> $img_name,
					]);

					$custom_cover_info = $this->custom_cover_model->get($custom_cover_id);

					$this->json['custom_cover'] = [
						'id'			=> $custom_cover_info['id'],
						'image'			=> $custom_cover_info['image'] ?? '',
						's3Image'		=> !empty($custom_cover_info['image'])
							? $this->config->item('cloudfront_url') . 'public/CustomCovers/' . $custom_cover_info['image']
							: '',
					];

					self::_updateCustomCoverLog();
				}
			} else {
				$this->json['error'] = _l('upload_error');
			}
		}
	}

	public function updateCustomCover() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);
		$this->form_validation->set_rules('custom_cover_id', _l('custom_cover_id'), [
			'trim',
			'required',
			'numeric',
			['custom_cover', [$this->validate_model, 'custom_cover']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_validateWriting()) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			// if (!self::_validateActiveSession()) {
			// 	return;
			// }

			if (!self::_validateSubscription()) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			$book_info = $this->book_model->get($this->input->post('book_id'));

			if (!empty($book_info['user_cover_id'])) {
				$this->user_cover_model->edit($book_info['user_cover_id'], [
					'custom_cover_id'	=> (int)$this->input->post('custom_cover_id'),
				]);
			} else {
				$user_cover_id = $this->user_cover_model->add([
					'user_id'			=> (int)$this->session->userdata('user_id'),
					'custom_cover_id'	=> (int)$this->input->post('custom_cover_id'),
				]);

				$this->book_model->edit($book_info['id'], [
					'user_cover_id'	=> (int)$user_cover_id,
				]);

				$book_info = $this->book_model->get($this->input->post('book_id'));
			}

			$user_cover_info 	= !empty($book_info['user_cover_id']) ? $this->user_cover_model->get($book_info['user_cover_id']) : [];
			$custom_cover_info 	= !empty($user_cover_info['custom_cover_id']) ? $this->custom_cover_model->get($user_cover_info['custom_cover_id']) : [];

			$this->json['book'] 				= $book_info;
			$this->json['book']['design'] 		= json_decode($user_cover_info['design'], true);
			$this->json['book']['custom_cover'] = $custom_cover_info;
			$this->json['success'] 				= _l('book_back_cover_updated');
		}
	}

	public function _updateCustomCoverLog() {
		if (!empty($user_id = (int)$this->session->userdata('user_id'))) {
			if (empty($custom_cover_log_info = $this->custom_cover_log_model->get_all([
				'user_id'	=> (int)$user_id,
			])['rows'][0] ?? [])) {
				$document_id = sha1(md5($user_id . time() . $this->config->item('password_salt')));
				$custom_cover_log_id = $this->custom_cover_log_model->add([
					'user_id'		=> $user_id,
					'book_id'		=> (int)$this->input->post('book_id') ?? 0,
					'document_id'	=> $document_id,
					'ip_address'	=> $this->input->ip_address(),
					'status'		=> 1
				]);

				$code = 'customCoverAlert';

				$this->load->model('common/Cron_model', 'cron_model');

				$this->cron_model->add([
					'code'			=> $code . '_' . $custom_cover_log_id,
					'action'		=> 'alert_model->' . $code,
					'data'			=> [$custom_cover_log_id],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime('+1 minutes'))
				]);
			}
		}
	}
}
