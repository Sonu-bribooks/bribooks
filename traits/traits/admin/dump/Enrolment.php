<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Enrolment {
	public function enrol_student($param1 = '') {
		if ($param1 == 'enrol') {
			if ($this->input->method() == 'post') {
				// user_id, course_id, mode, emi_type, amount, city_id, center_id, class_id, payment_mode
				$this->form_validation->set_rules('user_id', _l('user_id'), 'trim|required|numeric');
				$this->form_validation->set_rules('course_id', _l('course_id'), 'trim|required|numeric');
				$this->form_validation->set_rules('mode', _l('mode'), 'trim|required|in_list[online,offline]');
				$this->form_validation->set_rules('emi_type', _l('emi_type'), 'trim|required');
				$this->form_validation->set_rules('amount', _l('amount'), 'trim|required|numeric');
				$this->form_validation->set_rules('class_id', _l('class_id'), 'trim|required|numeric');
				$this->form_validation->set_rules('payment_mode', _l('payment_mode'), 'trim|required|in_list[online,offline]');
				//$this->form_validation->set_rules('email', _l('email'), 'trim|required|valid_email');

				$valid = $this->form_validation->run();

				$error = !$valid ? validation_errors() : [];

				if (!$error) {
					$this->enrol_model->enrolStudentOffline($this->input->post());
					$this->session->set_flashdata('flash_message', _l('enrolled_successfully'));
				} else {
					$this->session->set_flashdata('error_message', $error);
				}
			}

			redirect(site_url('admin/enrol_history'), 'refresh');
		}

		$data['page_name'] 	= 'enrol_student';
		$data['page_title'] = _l('enrol_a_student');

		$this->load->view('backend/index', $data);
	}

	public function enrol_history($param1 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($param1 != "") {
			$date_range					= $this->input->get('date_range');
			$date_range					= explode('-', $date_range);
			$data['timestamp_start'] 	= strtotime(trim($date_range[0]));
			$data['timestamp_end']	 	= strtotime('+1 days', strtotime(trim($date_range[1])));
		} else {
			$data['timestamp_start'] 	= ''; //strtotime('-1 month', time());
			$data['timestamp_end']	 	= ''; //strtotime('+1 days', strtotime(date("m/d/Y")));
		}

		$data['page_name'] 		= 'enrol_history';

		$data['enrol_history'] 	= $this->enrol_model->getAll([
			'date_start'	=> $data['timestamp_start'],
			'date_end'		=> $data['timestamp_end'],
			'sort'			=> 'enrol.date_added',
			'order'			=> 'DESC',
		]);

		$data['page_title'] 	= _l('enrol_history');

		$data['timestamp_start'] 	= strtotime('-1 month', time());
		$data['timestamp_end']	 	= strtotime('+1 days', strtotime(date("m/d/Y")));

		$this->load->view('backend/index', $data);
	}

	public function get_enrol() {
		$json = [];

		if ($this->input->post('enrol_id') && ($enrol_info = $this->enrol_model->get($this->input->post('enrol_id')))) {
			if ($course_info = $this->course_model->get($enrol_info['course_id'])->row_array()) {
				$json['emis'] = [];

				foreach (json_decode($course_info['emi'], 1) as $key => $amount) {
					if (!empty($enrol_info['mode'])) {
						if (strpos($key, $enrol_info['mode']) !== false) {
							$json['emis'][] = [
								'key'		=> $key,
								'amount'	=> $amount,
							];
						}
					} else {
						$json['emis'][] = [
							'key'		=> $key,
							'amount'	=> $amount,
						];
					}
				}

				$json['enrol']						= $enrol_info;
				$json['enrol']['renewal_amount']	= $this->enrol_model->getRenewalAmount($enrol_info['id']);
				$json['enrol']['renewal_date']		= date('m/d/Y', strtotime($enrol_info['renewal_date']));
				$json['enrol']['doj']				= date('m/d/Y', strtotime($enrol_info['doj']));
				$json['success']					= _l('text_success');
			} else {
				$json['error'] 						= _l('error_course');
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function update_enrol() {
		$json = [];

		$this->form_validation->set_rules('enrol_id', _l('enrol_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('renewal_date', _l('amount'), 'trim|required');
		$this->form_validation->set_rules('doj', _l('amount'), 'trim|required');

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if (!$json) {
			$this->enrol_model->edit((int)$this->input->post('enrol_id'), [
				'emi_type'		=> $this->input->post('emi_type'),
				'doj'			=> date('Y-m-d H:i:s', strtotime($this->input->post('doj'))),
				'renewal_date'	=> date('Y-m-d H:i:s', strtotime($this->input->post('renewal_date'))),
			]);

			$this->enrol_model->updateLastPayment([
				'emi_type'		=> $this->input->post('emi_type'),
				'amount'		=> (double)$this->input->post('amount'),
				'enrol_id'		=> (int)$this->input->post('enrol_id'),
			]);

			$json['success']	= _l('text_success');
			$json['redirect']	= site_url('admin/enrol_history');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function update_payment() {
		$json = [];

		$this->form_validation->set_rules('enrol_id', _l('enrol_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('emi_type', _l('emi_type'), 'trim|required');
		$this->form_validation->set_rules('amount', _l('amount'), 'trim|required');

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if (!$json) {
			$this->enrol_model->editPayment((int)$this->input->post('enrol_id'), [
				'amount'		=> (double)$this->input->post('amount'),
				'emi_type'		=> $this->input->post('emi_type'),
			]);

			$json['success']	= _l('text_success');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function enrol_history_delete($param1 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}
		$this->crud_model->delete_enrol_history($param1);
		$this->session->set_flashdata('flash_message', _l('data_deleted_successfully'));
		redirect(site_url('admin/enrol_history'), 'refresh');
	}

	public function enrol_archive($param1 = 0) {
		$this->enrol_model->archive($param1);

		$this->session->set_flashdata('flash_message', _l('modified_successfully'));

		redirect(site_url('admin/enrol_history'), 'refresh');
	}
}
