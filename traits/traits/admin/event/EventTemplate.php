<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventTemplate {
	public function event_template() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'events/templates/template';
		$data['page_title'] 	= _l('event_email_templates');
		$data['events']         = $this->event_model->get_all()['rows'];

		$this->load->view('backend/index', $data);
	}

	public function event_template_form($param1 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$event_info = $this->event_model->get($param1);

		$data['types'] = [
			'school_registration'
		];

		$data['lables'] = [
			'school_registration' => ['school_name', 'site_id']
		];

		$data['variables'] = [
			'school_name',
			'site_id',
			'author_name',
			'author_name',
			'event_name',
			'owner_name',
			'username',
			'password',
			'book_name',
			'url',
			'author_url',
			'partner_url',
			'rank_url',
			'book_url',
			'date',
			'name',
			'email',
			'mobile',
			'country',
			'state',
			'city',
			'designation',
			'grades',
			'sections',
			'authorized_person',
			'school_name',
			'site_id',
			'certificate_name',
			'my_certificates_url',
			'medallion_name',
			'partner_name'
		];

		$data['values'] = [
			'school_name'			=> 'School Name',
			'site_id'			    => '123'
		];

		$templates_info = $this->event_template_model->get_all(array('event_id' => $param1))['rows'] ?? [];

		$email_template_info = [];

		foreach ($templates_info as $key => $value) {
			$email_template_info[$value['template_id']] = $value;
		}

		$data['event_info'] 	= $event_info;
		$data['templates_info'] = $email_template_info;
		$data['page_name'] 		= 'events/templates/event_template_form';
		$data['page_title'] 	= _l('event_template');
		$data['header_image'] 	= $this->event_template_model->get_event_templates_image_url('header_image_' . $param1);
		$data['footer_image'] 	= $this->event_template_model->get_event_templates_image_url('footer_image_' . $param1);

		// pr($data, 1);

		$this->load->view('backend/index', $data);
	}

	public function save_event_template($param1 = '') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if (empty($param1) || !is_numeric($param1)) {
			$this->session->set_flashdata('error_message', _l('invalid_input'));
			redirect(site_url('admin/templates'), 'refresh');
		}

		$event_info = $this->event_model->get($param1);

		if (empty($event_info)) {
			$this->session->set_flashdata('error_message', _l('invalid_input'));
			redirect(site_url('admin/templates'), 'refresh');
		}

		if (!empty($_FILES['header_image']['name'])) {
			$header_filename = 'header_image_' . $param1;
			$this->event_template_model->upload_image($header_filename, 'header_image');
		}

		if (!empty($_FILES['footer_image']['name'])) {
			$footer_filename = 'footer_image_' . $param1;
			$this->event_template_model->upload_image($footer_filename, 'footer_image');
		}

		$email_template = $this->input->post('email_template', false);

		foreach ($email_template as $key => $value) {
			$template_info = $this->event_template_model->getByTemplateId($param1, $value['template_id']);

			$save = [
				'event_id'      => $param1,
				'name'          => $value['name'],
				'subject'       => $value['subject'],
				'template_id'   => $value['template_id'],
				'body'          => $value['body'],
				'status'        => !empty($value['status']) ? $value['status'] : '0',
				'user_id'       => $this->session->userdata('user_id')
			];

			if (empty($template_info)) {
				$this->event_template_model->add($save);
			} else {
				$this->event_template_model->edit($template_info['id'], $save);
			}
		}

		$this->session->set_flashdata('flash_message', _l('event_template_updated_successfully'));

		redirect(site_url('admin/event_template'), 'refresh');
	}
}
