<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Course {
	public function categories($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$this->crud_model->add_category();
			$this->session->set_flashdata('flash_message', _l('data_added_successfully'));
			redirect(site_url('admin/categories'), 'refresh');
		}
		elseif ($param1 == "edit") {

			$this->crud_model->edit_category($param2);
			$this->session->set_flashdata('flash_message', _l('data_updated_successfully'));
			redirect(site_url('admin/categories'), 'refresh');
		}
		elseif ($param1 == "delete") {
			$this->crud_model->delete_category($param2);
			$this->session->set_flashdata('flash_message', _l('data_deleted'));
			redirect(site_url('admin/categories'), 'refresh');
		}
		$data['page_name'] = 'categories';
		$data['page_title'] = _l('categories');
		$data['categories'] = $this->crud_model->get_categories($param2);
		$this->load->view('backend/index', $data);
	}

	public function category_form($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}
		if ($param1 == "add_category") {
			$data['page_name'] = 'category_add';
			$data['categories'] = $this->crud_model->get_categories()->result_array();
			$data['page_title'] = _l('add_category');
		}
		if ($param1 == "edit_category") {
			$data['page_name'] = 'category_edit';
			$data['page_title'] = _l('edit_category');
			$data['categories'] = $this->crud_model->get_categories()->result_array();
			$data['category_id'] = $param2;
		}

		$this->load->view('backend/index', $data);
	}

	public function sub_categories_by_category_id($category_id = 0) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$category_id = $this->input->post('category_id');
		redirect(site_url("admin/sub_categories/$category_id"), 'refresh');
	}

	public function sub_category_form($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($param1 == 'add_sub_category') {
			$data['page_name'] = 'sub_category_add';
			$data['page_title'] = _l('add_sub_category');
		}
		elseif ($param1 == 'edit_sub_category') {
			$data['page_name'] = 'sub_category_edit';
			$data['page_title'] = _l('edit_sub_category');
			$data['sub_category_id'] = $param2;
		}
		$data['categories'] = $this->crud_model->get_categories();
		$this->load->view('backend/index', $data);
	}

	public function courses() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}


		$data['selected_category_id']	= isset($_GET['category_id']) ? $_GET['category_id'] : "all";
		$data['selected_instructor_id']	= isset($_GET['instructor_id']) ? $_GET['instructor_id'] : "all";
		$data['selected_price']			= isset($_GET['price']) ? $_GET['price'] : "all";
		$data['selected_status']		= isset($_GET['status']) ? $_GET['status'] : "all";
		$data['courses']				= $this->crud_model->filter_course_for_backend($data['selected_category_id'], $data['selected_instructor_id'], $data['selected_price'], $data['selected_status']);
		$data['status_wise_courses']	= $this->crud_model->get_status_wise_courses();
		$data['instructors']			= [];
		$data['page_name']				= 'courses';
		$data['categories']				= $this->crud_model->get_categories();
		$data['page_title']				= _l('active_courses');
		$this->load->view('backend/index', $data);
	}

	public function pending_courses() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['page_name'] = 'pending_courses';
		$data['page_title'] = _l('pending_courses');
		$this->load->view('backend/index', $data);
	}

	public function course_actions($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($param1 == "add") {
			$this->crud_model->add_course();
			redirect(site_url('admin/courses'), 'refresh');
		} elseif ($param1 == "edit") {
			$this->crud_model->update_course($param2);
			redirect(site_url('admin/courses'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->is_drafted_course($param2);
			$this->crud_model->delete_course($param2);
			redirect(site_url('admin/courses'), 'refresh');
		}
	}

	public function course_form($param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($param1 == 'add_course') {
			$data['languages']	= $this->get_all_languages();
			$data['categories'] = $this->crud_model->get_categories();
			$data['page_name'] = 'course_add';
			$data['page_title'] = _l('add_course');
			$this->load->view('backend/index', $data);
		} elseif ($param1 == 'course_edit') {
			$this->is_drafted_course($param2);
			$data['page_name'] = 'course_edit';
			$data['course_id'] =	$param2;
			$data['page_title'] = _l('edit_course');
			$data['languages']	= $this->get_all_languages();
			$data['categories'] = $this->crud_model->get_categories();
			$this->load->view('backend/index', $data);
		}
	}

	private function is_drafted_course($course_id){
		$course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
		if ($course_details['status'] == 'draft') {
			$this->session->set_flashdata('error_message', _l('you_do_not_have_right_to_access_this_course'));
			redirect(site_url('admin/courses'), 'refresh');
		}
	}

	public function change_course_status($updated_status = "") {
		$course_id = $this->input->post('course_id');
		$category_id = $this->input->post('category_id');
		$instructor_id = $this->input->post('instructor_id');
		$price = $this->input->post('price');
		$status = $this->input->post('status');
		if (isset($_POST['mail_subject']) && isset($_POST['mail_body'])) {
			$mail_subject = $this->input->post('mail_subject');
			$mail_body = $this->input->post('mail_body');
			$this->email_model->send_mail_on_course_status_changing($course_id, $mail_subject, $mail_body);
		}
		$this->crud_model->change_course_status($updated_status, $course_id);
		$this->session->set_flashdata('flash_message', _l('course_status_updated'));
		redirect(site_url('admin/courses?category_id='.$category_id.'&status='.$status.'&instructor_id='.$instructor_id.'&price='.$price), 'refresh');
	}

	public function change_course_status_for_admin($updated_status = "", $course_id = "", $category_id = "", $status = "", $instructor_id = "", $price = "") {
		$this->crud_model->change_course_status($updated_status, $course_id);
		$this->session->set_flashdata('flash_message', _l('course_status_updated'));
		redirect(site_url('admin/courses?category_id='.$category_id.'&status='.$status.'&instructor_id='.$instructor_id.'&price='.$price), 'refresh');
	}

	public function sections($param1 = "", $param2 = "", $param3 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($param2 == 'add') {
			$this->crud_model->add_section($param1);
			$this->session->set_flashdata('flash_message', _l('section_has_been_added_successfully'));
		} elseif ($param2 == 'edit') {
			$this->crud_model->edit_section($param3);
			$this->session->set_flashdata('flash_message', _l('section_has_been_updated_successfully'));
		} elseif ($param2 == 'delete') {
			$this->crud_model->delete_section($param1, $param3);
			$this->session->set_flashdata('flash_message', _l('section_has_been_deleted_successfully'));
		}
		redirect(site_url('admin/course_form/course_edit/'.$param1));
	}

	public function lessons($course_id = "", $param1 = "", $param2 = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}
		if ($param1 == 'add') {
			$this->crud_model->add_lesson();
			$this->session->set_flashdata('flash_message', _l('lesson_has_been_added_successfully'));
			redirect('admin/course_form/course_edit/'.$course_id);
		} elseif ($param1 == 'edit') {
			$this->crud_model->edit_lesson($param2);
			$this->session->set_flashdata('flash_message', _l('lesson_has_been_updated_successfully'));
			redirect('admin/course_form/course_edit/'.$course_id);
		} elseif ($param1 == 'delete') {
			$this->crud_model->delete_lesson($param2);
			$this->session->set_flashdata('flash_message', _l('lesson_has_been_deleted_successfully'));
			redirect('admin/course_form/course_edit/'.$course_id);
		} elseif ($param1 == 'filter') {
			redirect('admin/lessons/'.$this->input->post('course_id'));
		}
		$data['page_name'] = 'lessons';
		$data['lessons'] = $this->crud_model->get_lessons('course', $course_id);
		$data['course_id'] = $course_id;
		$data['page_title'] = _l('lessons');
		$this->load->view('backend/index', $data);
	}

	public function watch_video($slugified_title = "", $lesson_id = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}
		$lesson_details					= $this->crud_model->get_lessons('lesson', $lesson_id)->row_array();
		$data['provider']	 = $lesson_details['video_type'];
		$data['video_url']	= $lesson_details['video_url'];
		$data['lesson_id']	= $lesson_id;
		$data['page_name']	= 'video_player';
		$data['page_title'] = _l('video_player');
		$this->load->view('backend/index', $data);
	}
}
