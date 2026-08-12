<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Setting {
	public function external_crm($param1 = 'briminds') {
		$shared_secret = 'FEKBjHhCa;nxC:X=56%A8p$(wx1^Vv_$sKd1r&%an0U';

		$payload = [
			'email'			  	=> $this->session->userdata('user_email'),
			'name'			 	=> $this->session->userdata('name'),
			'role_id'		  	=> (int)$this->session->userdata('role_id'),
			'allowed_projects' 	=> ['briminds', 'brisharks'],
			'target_project'   	=> $param1,
			'iat'			  	=> time(),
			'exp'			  	=> time() + 60
		];

		$token = \Firebase\JWT\JWT::encode($payload, $shared_secret, 'HS256');

		$this->load->model('admin/SystemUserToken_model', 'system_user_token_model');

		$this->system_user_token_model->add([
			'user_id'	=> $this->session->userdata('user_id'),
			'token'		=> $token,
		]);

		redirect(sprintf('https://%s.briminds.ai/sso/login?token=%s', ENVIRONMENT === 'production' ? 'crm' : 'crm-dev', $token));
	}

	public function data_chart($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 	= 'reports/data_charts';
		$data['page_title'] = _l('data_chart');

		$this->load->view('backend/index', $data);
	}

	public function centers($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('localisation/Center_model', 'center_model');

		if ($param1 == 'add') {
			$this->center_model->add();
			redirect(base_url('admin/centers'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->center_model->edit($param2);
			redirect(base_url('admin/centers'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->center_model->delete($param2);
			redirect(base_url('admin/centers'), 'refresh');
		}

		$data['page_name'] 		= 'center/index';
		$data['page_title'] 	= _l('center');
		$data['centers'] 		= $this->center_model->get_all();

		$this->load->view('backend/index', $data);
	}

	public function center_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('localisation/City_model', 'city_model');
		$data['cities'] = $this->city_model->get_all()['rows'];

		if ($param1 == 'add') {
			$data['page_name'] 	= 'center/form';
			$data['page_title'] = _l('center_add');
			$data['action'] 	= base_url('admin/centers/add');

			$this->load->view('backend/index', $data);
		} elseif ($param1 == 'edit') {
			$this->load->model('localisation/Center_model', 'center_model');

			$data['page_name'] 	= 'center/form';
			$data['slot_id'] 	= $param2;
			$data['action'] 	= base_url('admin/centers/edit/' . (int)$param2);
			$data['details'] 	= $this->center_model->get($param2)->row_array();
			$data['page_title'] = _l('center_edit');

			$this->load->view('backend/index', $data);
		}
	}

	public function system_settings($param1 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->library('Cron_lib', 'cron_lib');

		$this->cron_lib
			->add(sprintf("* * * * * curl -k %s", base_url('cron')), base_url('cron'))
			->save();

		// $this->cron_lib
		// 	->add(sprintf("*/15 * * * * curl -k %s", base_url('cron/cron15Miniute')), base_url('cron/cron15Miniute'))
		// 	->save();
		//
		// $this->cron_lib
		// 	->add(sprintf("0 11 * * * curl -k %s", base_url('cron/cronDaily')), base_url('cron/cronDaily'))
		// 	->save();

		if ($param1 == 'system_update') {
			$this->crud_model->update_system_settings();

			$this->crud_model->update_setting_template('bb_shipping', $this->input->post('bb_shipping'));

			$this->session->set_flashdata('flash_message', _l('system_settings_updated'));
			redirect(base_url('admin/system_settings'), 'refresh');
		}

		if ($param1 == 'logo_upload') {
			move_uploaded_file($_FILES['logo']['tmp_name'], 'assets/backend/logo.png');
			$this->session->set_flashdata('flash_message', _l('backend_logo_updated'));
			redirect(base_url('admin/system_settings'), 'refresh');
		}

		if ($param1 == 'favicon_upload') {
			move_uploaded_file($_FILES['favicon']['tmp_name'], 'assets/favicon.png');
			$this->session->set_flashdata('flash_message', _l('favicon_updated'));
			redirect(base_url('admin/system_settings'), 'refresh');
		}

		$data['languages']	 	= $this->get_all_languages();
		$data['page_name'] 		= 'system_settings';
		$data['page_title'] 	= _l('system_settings');

		$this->load->view('backend/index', $data);
	}

	public function frontend_settings($param1 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'frontend_update') {
			$this->crud_model->update_frontend_settings();
			$this->session->set_flashdata('flash_message', _l('frontend_settings_updated'));
			redirect(base_url('admin/frontend_settings'), 'refresh');
		}

		if ($param1 == 'banner_image_update') {
			$this->crud_model->update_frontend_banner();
			$this->session->set_flashdata('flash_message', _l('banner_image_update'));
			redirect(base_url('admin/frontend_settings'), 'refresh');
		}

		if ($param1 == 'light_logo') {
			$this->crud_model->update_light_logo();
			$this->session->set_flashdata('flash_message', _l('logo_updated'));
			redirect(base_url('admin/frontend_settings'), 'refresh');
		}

		if ($param1 == 'dark_logo') {
			$this->crud_model->update_dark_logo();
			$this->session->set_flashdata('flash_message', _l('logo_updated'));
			redirect(base_url('admin/frontend_settings'), 'refresh');
		}

		if ($param1 == 'small_logo') {
			$this->crud_model->update_small_logo();
			$this->session->set_flashdata('flash_message', _l('logo_updated'));
			redirect(base_url('admin/frontend_settings'), 'refresh');
		}

		if ($param1 == 'favicon') {
			$this->crud_model->update_favicon();
			$this->session->set_flashdata('flash_message', _l('favicon_updated'));
			redirect(base_url('admin/frontend_settings'), 'refresh');
		}

		$data['page_name'] 	= 'frontend_settings';
		$data['page_title'] = _l('frontend_settings');

		$this->load->view('backend/index', $data);
	}

	public function smtp_settings($param1 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'update') {
			$this->crud_model->update_smtp_settings();
			$this->session->set_flashdata('flash_message', _l('smtp_settings_updated_successfully'));
			redirect(base_url('admin/smtp_settings'), 'refresh');
		}

		$data['page_name'] 	= 'smtp_settings';
		$data['page_title'] = _l('smtp_settings');

		$this->load->view('backend/index', $data);
	}

	public function setting_site($param1 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['ranges'] = [
			'daily',
			'weekly',
			'monthly',
		];

		if ($param1 == 'update') {
			$this->load->library('Cron_lib', 'cron_lib');

			$this->crud_model->update_setting_template('auto_report_range', $this->input->post('report_range'));

			$this->cron_lib
				->add(sprintf("28 2 * * * curl -k %s", base_url('cron/cronMidnight')), base_url('cron/cronMidnight'))
				->save();

			$this->session->set_flashdata('flash_message', _l('auto_report_updated_successfully'));
			redirect(base_url('admin/auto_report'), 'refresh');
		}

		$data['page_name'] 		= 'setting_site';
		$data['page_title'] 	= _l('setting_site');

		$this->load->view('backend/index', $data);
	}

	public function auto_report($param1 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['ranges'] = [
			'daily',
			'weekly',
			'monthly',
		];

		if ($param1 == 'update') {
			$this->load->library('Cron_lib', 'cron_lib');

			$this->crud_model->update_setting_template('auto_report_range', $this->input->post('report_range'));

			$this->cron_lib
				->add(sprintf("28 2 * * * curl -k %s", base_url('cron/cronMidnight')), base_url('cron/cronMidnight'))
				->save();

			$this->session->set_flashdata('flash_message', _l('auto_report_updated_successfully'));
			redirect(base_url('admin/auto_report'), 'refresh');
		}

		$data['page_name'] 		= 'auto_report';
		$data['page_title'] 	= _l('auto_report');

		$this->load->view('backend/index', $data);
	}

	public function sms_template($param1 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['types'] = [
			'sms_otp',
			'sms_order',
			'sms_pages_printed',
			'sms_book_in_folding',
			'sms_book_in_binding',
			'sms_book_in_qa',
			'sms_book_ready_to_ship',
		];

		$data['variables'] = [
			'name',
			'book_name',
			'order_id',
		];

		$data['values'] = [
			'name'			=> 'Customer Name',
			'book_name'		=> 'Book Name',
			'order_id'		=> 'BB-1674641295-XXXXXXXXX',
		];

		if ($param1 == 'update') {
			foreach ($this->input->post('sms_template') as $key => $value) {
				$this->crud_model->update_setting_template($key, $value);
			}

			$this->session->set_flashdata('flash_message', _l('sms_template_updated_successfully'));
			redirect(base_url('admin/sms_template'), 'refresh');
		}

		$data['page_name'] 		= 'sms_template';
		$data['page_title'] 	= _l('sms_template');

		$this->load->view('backend/index', $data);
	}

	public function sms_test() {
		$json = [];

		if ($this->input->post('sms_data') && $this->input->post('message') && $this->input->post('mobile')) {
			$sms_data = $this->input->post('sms_data');

			$find = [
				'{name}',
				'{book_name}',
				'{order_id}',
			];

			$replace = [
				'name'				=> $sms_data['name'],
				'book_name'			=> $sms_data['book_name'],
				'order_id'			=> $sms_data['order_id'],
			];

			$message = str_replace($find, $replace, $this->input->post('message'));

			$this->load->model('Alert_model', 'alert_model');

			$json = $this->alert_model->sms([
				'mobile' 	=> $this->input->post('mobile'),
				'message' 	=> $message,
			]);
			$json['message'] = $message;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function instructor_settings($param1 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'update') {
			$this->crud_model->update_instructor_settings();
			$this->session->set_flashdata('flash_message', _l('instructor_settings_updated'));
			redirect(base_url('admin/instructor_settings'), 'refresh');
		}

		$data['page_name'] 	= 'instructor_settings';
		$data['page_title'] = _l('instructor_settings');

		$this->load->view('backend/index', $data);
	}

	public function export_site_books_details($site_id = '') {
		if (empty($site_id))
			return;

		$site_info = $this->site_model->get($site_id);

		if (empty($site_info))
			return;

		$books = $this->book_model->get_all([
			'site_id'	=> $site_id,
			'status'	=> 1
		])['rows'];

		$results = [];

		foreach ($books ?? [] as $item) {
			$student_info = $this->student_model->get($item['user_id']);

			$book_sold = $this->order_model->getTotalProductsByProductId($item['id']);

			$results[] = [
				'site_id'				=> $site_info['id'],
				'site_name'				=> $site_info['name'],
				'book_id'				=> $item['id'],
				'author_id'				=> $item['user_id'],
				'book_name'				=> $item['name'],
				'author_name'			=> $item['author_name'],
				'book_sold'				=> $book_sold ?? 0,
				'mobile'				=> $student_info['mobile'],
				'email'					=> $student_info['email'],
				'book_url'				=> USER_URL . 'bookstore/' . $item['slug'],
				'book_added'			=> $item['date_added'],
				'book_published'		=> $item['date_published'],
			];
		}

		if (!headers_sent()) {
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="exports_' . preg_replace(['/[^\w\s]/', '/\s+/'], ['', '_'], mb_strtolower($site_info['name'])) . date('Y-m-d') . '.csv"');
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

		$this->writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	public function order_privy_setting($param1 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$order_privy_value = get_settings('order_privy');
		$order_privy_alert = get_settings('order_privy_alert');

		$data['order_privy_value'] = !empty($order_privy_value) ? $order_privy_value : '' ;
		$data['order_privy_alert'] = !empty($order_privy_alert) ? $order_privy_alert : '' ;


		if ($param1 == 'update') {
			if (!empty($this->input->post('order_privy'))) {
				$this->crud_model->update_setting_template('order_privy', $this->input->post('order_privy'));
			}

			if (!empty($this->input->post('order_privy_alert'))) {
				$this->crud_model->update_setting_template('order_privy_alert', $this->input->post('order_privy_alert'));
			}

			$this->session->set_flashdata('flash_message', _l('order_privy_setting_updated_successfully'));

			redirect(base_url('admin/order_privy_setting'), 'refresh');
		}

		$data['page_name'] 		= 'order_privy_setting';
		$data['page_title'] 	= _l('order_privy_setting');

		$this->load->view('backend/index', $data);
	}

	public function stripe_payment($param1 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['payment_provider'] = get_settings('payment_provider') ?? '';

		if ($param1 == 'update') {
			$country = $this->input->post('country');

			if (!empty($country)) {
				$key_field = $country === 'UAE' ? 'stripe' : 'stripe_sg';

				$this->crud_model->update_setting_template('payment_provider', $key_field);

				$this->session->set_flashdata('flash_message', _l('payment_setting_updated_successfully'));
			} else {
				$this->session->set_flashdata('error_message', _l('invalid_input_provided'));
			}

			redirect(base_url('admin/stripe_payment'), 'refresh');
		}

		$data['page_name'] = 'stripe_payment';
		$data['page_title'] = _l('stripe_payment_switcher');
		$this->load->view('backend/index', $data);
	}

}
