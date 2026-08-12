<?php defined('BASEPATH') or exit('No direct script access allowed');

trait HallOfFame {
	public function hall_of_fame($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$this->load->library('HallOfFame_lib');

			$hall_of_fame_info = $this->hall_of_fame_model->get_all([
				'book_id'	=> $param2
			])['rows'] ?? [];

			if(empty($hall_of_fame_info)) {
				if(empty($this->halloffame_lib->addBookToHallOfFame($param2))) {
					$this->session->set_flashdata('error_message', "Invalid book.");
				} else {
					$this->session->set_flashdata('flash_message', "Book added in Hall Of Fame.");
				}
			} else if(!empty($hall_of_fame_info)) {
				$this->cache->clean();
				
				$this->hall_of_fame_model->deleteByBookId($param2);
				$this->session->set_flashdata('flash_message', "Book deleted from Hall Of Fame.");
			} else {
				$this->session->set_flashdata('error_message', "No rows found.");
			}
			
			redirect(base_url('admin/approved_books'), 'refresh');
		}
	}

	public function bulk_hall_of_fame() {
		$ids = $this->input->post('ids');
		if (empty($ids)) {
			echo json_encode(array('status' => false, 'message' => 'Please select at least 1 record'));
			exit();
		}

		$this->load->library('HallOfFame_lib');

		$count = 0;
		for ($i = 0; $i < count($ids); $i++) {
			if(!empty($this->halloffame_lib->addBookToHallOfFame($ids[$i]))) {
				$count++;
			}
		}

		if(!empty($count))
			$this->session->set_flashdata('flash_message', "$count books added successfully.");
		else
			$this->session->set_flashdata('error_message', "No rows found.");

		echo json_encode(array('status' => true));
		exit();
	}
}
