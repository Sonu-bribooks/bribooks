<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AdminValidate_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->load->model('event/EventType_model', 'event_type_model');

		$this->load->library('form_validation');
	}

	public function event_type($str) {
		if (!$this->event_type_model->get($str)) {
			$this->form_validation->set_message('event_type', _l('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_start_date($str) {
		if (strtotime($str) < time()) {
			$this->form_validation->set_message('start_date', _l('The {field} can`t less than current date'));
			return false;
		}

		return true;
	}

	public function event_end_date($str) {
		if (strtotime($str) < time()) {
			$this->form_validation->set_message('end_date', _l('The {field} can`t less than current date'));
			return false;
		}

		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('end_date', _l('The {field} can`t less than start date'));
			return false;
		}

		return true;
	}

	public function event_student_reg_start_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('student_reg_start_date', _l('The {field} can`t less than start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('student_reg_start_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_student_reg_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('student_reg_start_date'))) {
			$this->form_validation->set_message('student_reg_end_date', _l('The {field} can`t less than student reg start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('student_reg_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_school_reg_start_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('school_reg_start_date', _l('The {field} can`t less than start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('school_reg_start_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_school_reg_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('school_reg_start_date'))) {
			$this->form_validation->set_message('school_reg_end_date', _l('The {field} can`t less than school reg start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('school_reg_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_book_writing_start_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('book_writing_start_date', _l('The {field} can`t less than start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('book_writing_start_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_book_writing_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('book_writing_start_date'))) {
			$this->form_validation->set_message('book_writing_end_date', _l('The {field} can`t less than book writing start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('book_writing_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_selling_start_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('selling_start_date', _l('The {field} can`t less than start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('selling_start_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_selling_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('selling_start_date'))) {
			$this->form_validation->set_message('selling_end_date', _l('The {field} can`t less than selling start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('selling_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		if (strtotime($str) < strtotime($this->input->post('book_writing_end_date'))) {
			$this->form_validation->set_message('selling_end_date', _l('The {field} can`t less than book writing end date'));
			return false;
		}

		return true;
	}

	public function event_student_reg_eap_start_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('student_reg_eap_start_date', _l('The {field} can`t less than start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('student_reg_eap_start_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_student_reg_eap_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('student_reg_eap_start_date'))) {
			$this->form_validation->set_message('student_reg_eap_end_date', _l('The {field} can`t less than student reg eap start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('student_reg_eap_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_school_reg_eap_start_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('school_reg_eap_start_date', _l('The {field} can`t less than start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('school_reg_eap_start_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_school_reg_eap_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('school_reg_eap_start_date'))) {
			$this->form_validation->set_message('school_reg_eap_end_date', _l('The {field} can`t less than school reg eap start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('school_reg_eap_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_book_writing_eap_start_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('book_writing_eap_start_date', _l('The {field} can`t less than start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('book_writing_eap_start_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_book_writing_eap_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('book_writing_eap_start_date'))) {
			$this->form_validation->set_message('book_writing_eap_end_date', _l('The {field} can`t less than book writing eap start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('book_writing_eap_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_selling_eap_start_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('selling_eap_start_date', _l('The {field} can`t less than start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('selling_eap_start_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_selling_eap_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('selling_eap_start_date'))) {
			$this->form_validation->set_message('selling_eap_end_date', _l('The {field} can`t less than selling eap start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('selling_eap_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		if (strtotime($str) < strtotime($this->input->post('book_writing_eap_end_date'))) {
			$this->form_validation->set_message('selling_eap_end_date', _l('The {field} can`t less than book writing eap end date'));
			return false;
		}

		return true;
	}

	public function event_student_reg_prime_start_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('student_reg_prime_start_date', _l('The {field} can`t less than start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('student_reg_prime_start_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_student_reg_prime_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('student_reg_prime_start_date'))) {
			$this->form_validation->set_message('student_reg_prime_end_date', _l('The {field} can`t less than student reg prime start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('student_reg_prime_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_school_reg_prime_start_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('school_reg_prime_start_date', _l('The {field} can`t less than start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('school_reg_prime_start_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_school_reg_prime_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('school_reg_prime_start_date'))) {
			$this->form_validation->set_message('school_reg_prime_end_date', _l('The {field} can`t less than school reg prime start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('school_reg_prime_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_book_writing_prime_start_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('book_writing_prime_start_date', _l('The {field} can`t less than start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('book_writing_prime_start_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_book_writing_prime_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('book_writing_prime_start_date'))) {
			$this->form_validation->set_message('book_writing_prime_end_date', _l('The {field} can`t less than book writing prime start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('book_writing_prime_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_selling_prime_start_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('selling_prime_start_date', _l('The {field} can`t less than start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('selling_prime_start_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function event_selling_prime_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('selling_prime_start_date'))) {
			$this->form_validation->set_message('selling_prime_end_date', _l('The {field} can`t less than selling prime start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('selling_prime_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		if (strtotime($str) < strtotime($this->input->post('book_writing_prime_end_date'))) {
			$this->form_validation->set_message('selling_prime_end_date', _l('The {field} can`t less than book writing prime end date'));
			return false;
		}

		return true;
	}

	public function event_national_awards_exhibition_end_date($str) {
		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('national_awards_exhibition_end_date', _l('The {field} can`t less than selling prime start date'));
			return false;
		}

		if (strtotime($str) > strtotime($this->input->post('end_date'))) {
			$this->form_validation->set_message('national_awards_exhibition_end_date', _l('The {field} can`t greater than end date'));
			return false;
		}

		return true;
	}

	public function league_start_date($str) {
		if (strtotime($str) < time()) {
			$this->form_validation->set_message('start_date', _l('The {field} can`t less than current date'));
			return false;
		}

		return true;
	}

	public function league_display_date($str) {
		if (strtotime($str) < time()) {
			$this->form_validation->set_message('display_date', _l('The {field} can`t less than current date'));
			return false;
		}

		return true;
	}

	public function league_end_date($str) {
		if (strtotime($str) < time()) {
			$this->form_validation->set_message('end_date', _l('The {field} can`t less than current date'));
			return false;
		}

		if (strtotime($str) < strtotime($this->input->post('start_date'))) {
			$this->form_validation->set_message('end_date', _l('The {field} can`t less than start date'));
			return false;
		}

		return true;
	}
}
