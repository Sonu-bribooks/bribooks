<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Old {
	public function my_messages($param1 = "", $param2 = "") {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url(), 'refresh');
		}
		if ($param1 == 'read_message') {
			$data['message_thread_code'] = $param2;
		}
		elseif ($param1 == 'send_new') {
			$message_thread_code = $this->crud_model->send_new_private_message();
			$this->session->set_flashdata('flash_message', _l('message_sent!'));
			redirect(site_url('home/my_messages/read_message/' . $message_thread_code), 'refresh');
		}
		elseif ($param1 == 'send_reply') {
			$this->crud_model->send_reply_message($param2); //$param2 = message_thread_code
			$this->session->set_flashdata('flash_message', _l('message_sent!'));
			redirect(site_url('home/my_messages/read_message/' . $param2), 'refresh');
		}
		$data['page_name'] = "my_messages";
		$data['page_title'] = _l('my_messages');
		$this->load->view('frontend/'.get_frontend_settings('theme').'/index', $data);
	}

	public function my_notifications() {
		$data['page_name'] = "my_notifications";
		$data['page_title'] = _l('my_notifications');
		$this->load->view('frontend/'.get_frontend_settings('theme').'/index', $data);
	}

	public function about_us() {
		$data['page_name'] = 'about_us';
		$data['page_title'] = _l('about_us');
		$this->load->view('frontend/'.get_frontend_settings('theme').'/index', $data);
	}

	public function terms_and_condition() {
		$data['page_name'] = 'terms_and_condition';
		$data['page_title'] = _l('terms_and_condition');
		$this->load->view('frontend/'.get_frontend_settings('theme').'/index', $data);
	}

	public function privacy_policy() {
		$data['page_name'] = 'privacy_policy';
		$data['page_title'] = _l('privacy_policy');
		$this->load->view('frontend/'.get_frontend_settings('theme').'/index', $data);
	}

	private function download($filename = "") {
		return;
		$tmp		   = explode('.', $filename);
		$fileExtension = strtolower(end($tmp));
		$yourFile = base_url().'uploads/lesson_files/'.$filename;
		$file = @fopen($yourFile, "rb");

		header('Content-Description: File Transfer');
		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($yourFile));
		while (!feof($file)) {
			print(@fread($file, 1024 * 8));
			ob_flush();
			flush();
		}
	}

	// Version 1.4 codes

	public function login_dev() {
		if ($this->session->userdata('user_login') && $this->session->userdata('user_id')) {
			redirect(site_url('home/parent_dashboard'), 'refresh');
		}

		$data['page_name'] = 'login-dev';
		$data['page_title'] = _l('login');
		$this->load->view('frontend/'.get_frontend_settings('theme').'/index', $data);
	}

    public function parent_dashboard_dev() {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url(), 'refresh');
		} else if (!$this->user_model->get($this->session->userdata('user_id'))) {
			redirect(site_url('login/logout/user'));
		}

		// $this->alert_model->sms('9716120257', str_replace('{otp}', '333333', get_settings('sms_otp')));

		$this->load->helper('cookie');

		$data['page_name'] 		= 'parent_dashboard_new';
		$data['page_title'] 	= _l('dashboard');

		$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
	}
}
