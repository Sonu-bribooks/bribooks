<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CustomTheme {
	public function getCustomThemes() {
		if (!$this->json) {
			$filter_data = [
				'user_id'	=> (int)$this->session->userdata('user_id'),
				'status'	=> 1
			];

			$result = array_map(function ($item) {
				return $item;
			}, $this->custom_theme_model->get_all($filter_data)['rows'] ?? []);

			$this->json['custom_themes'] = $result;
		}
	}

	public function saveCustomTheme() {
		$this->form_validation->set_rules('page_id', _l('page_id'), [
			'trim',
			'required',
			'numeric',
			['page', [$this->validate_model, 'page']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
				if (self::_validateFileUpload()) {
					$img_name = uniqid() . '_' . $this->session->userdata('user_id') . '.png';

					log_kb($this->s3->amazonS3Upload(
						$img_name,
						$_FILES['image']['tmp_name'],
						rtrim($this->config->item('s3_custom_themes'), '/')
					));

					$custom_theme_id = $this->custom_theme_model->add([
						'user_id'	=> $this->session->userdata('user_id'),
						'image'		=> $img_name,
					]);

					$this->page_model->edit($this->input->post('page_id'), [
						'custom_theme_id'		=> (int)$custom_theme_id,
					]);

					$custom_theme_info = $this->custom_theme_model->get($custom_theme_id);
					$this->json['custom_theme'] = [
						'id'			=> $custom_theme_info['id'] ?? '',
						'image'			=> $custom_theme_info['image'] ?? '',
						's3Image'		=> !empty($custom_theme_info['image'])
							? $this->config->item('cloudfront_url') . 'public/CustomThemes/' . $custom_theme_info['image']
							: '',
					];
				}
			} else {
				$this->json['error'] = _l('upload_error');
			}
		}
	}

	public function updateUserCustomThemeLog() {
		$this->form_validation->set_rules('status', _l('status'), 'trim|required|numeric|in_list[0,1]');

		self::_runFormValidation();

		if (!$this->json) {
			if (!empty($user_id = (int)$this->session->userdata('user_id'))) {
				if (!empty($custom_theme_log_info = $this->custom_theme_log_model->get_all([
					'user_id'	=> (int)$user_id,
					'start'		=> 0,
					'limit'		=> 1,
				])['rows'][0] ?? [])) {
					return;
				}

				if ($custom_theme_log_info = $this->db->get_where('custom_theme_log', ['user_id' => $user_id])->row_array()) {
					$custom_theme_log_id = $custom_theme_log_info['id'];
					$this->custom_theme_log_model->edit($custom_theme_log_id, [
						'status'	=> (int)$this->input->post('status')
					]);
				} else {
					$document_id = sha1(md5($user_id . time() . $this->config->item('password_salt')));
					$custom_theme_log_id = $this->custom_theme_log_model->add([
						'user_id'		=> $user_id,
						'book_id'		=> (int)$this->input->post('book_id') ?? 0,
						'document_id'	=> $document_id,
						'ip_address'	=> $this->input->ip_address(),
						'status'		=> (int)$this->input->post('status')
					]);
				}

				if ((int)$this->input->post('status') == 1) {
					$code = 'customThemeAlert';

					$this->load->model('common/Cron_model', 'cron_model');

					$this->cron_model->add([
						'code'			=> $code . '_' . $custom_theme_log_id,
						'action'		=> 'alert_model->' . $code,
						'data'			=> [$custom_theme_log_id],
						'alert_date'	=> date('Y-m-d H:i:s', strtotime('+1 minutes'))
					]);
				}

				self::_formatUser($user_id);

				$this->json['success'] = _l('custom_theme_log_updated');
			}
		}
	}
}
