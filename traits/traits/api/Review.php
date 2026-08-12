<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Review {
	public function writeBookReview() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);
		$this->form_validation->set_rules('text', _l('text'), 'trim|required|min_length[4]|max_length[250]');
		$this->form_validation->set_rules('rating', _l('rating'), 'trim|required|numeric|greater_than_equal_to[1]|less_than_equal_to[5]');

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($this->session->userdata('user_id'))) {
				$this->json['login'] = true;
				return;
			}

			if (!empty($spam_word_data = $this->page_model->checkSpamWords($this->input->post('text')))) {
				$spam_words = implode(', ', array_column($spam_word_data, 'word'));
				$this->json['error'] = _li('Spam Word : ')  . ' ' . $spam_words;
				return;
			}

			$review_id = $this->review_model->add([
				'book_id'	=> (int)$this->input->post('book_id'),
				'rating'	=> (int)$this->input->post('rating'),
				'user_id'	=> (int)$this->session->userdata('user_id'),
				'author'	=> $this->session->userdata('user_name'),
				'text'		=> $this->input->post('text'),
				'status'	=> $this->input->post('rating') > 3 ? 1 : 0,
			]);

			CI_Events::trigger('access_log', [
				'module'	=> 'book_reviewed_' . (int)$this->input->post('book_id')
			]);

			CI_Events::trigger('book_reviewed', [
				'review_id'		=> $review_id
			]);

			$this->json['success'] = _l('your_review_submitted');
		}
	}

	public function getReviewFlagTypes() {
		$this->json['review_flag_types'] = array_map(function($item) {
			return [
				'id'		=> $item['id'],
				'name'		=> ucwords($item['name']),
			];
		}, $this->review_flag_type_model->get_all([
			'status' => 1,
		])['rows'] ?? []);
	}

	public function addReviewFlag() {
		$this->form_validation->set_rules('review_flag_type_id', _l('review_flag_type_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('review_id', _l('review_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('reason', _l('reason'), 'trim|required', [
			'required'	=> _li('The reason is required'),
		]);
		self::_runFormValidation();

		if (!$this->json) {
			if (!$this->session->userdata('user_id')) {
				$this->json['login'] 	= true;
				$this->json['success'] 	= _l('login_to_flag');
			}

			if (!empty($spam_word_data = $this->page_model->checkSpamWords($this->input->post('reason')))) {
				$spam_words = implode(', ', array_column($spam_word_data, 'word'));
				$this->json['error'] = _li('Spam Word : ')  . ' ' . $spam_words;
				return;
			}

			if (empty($this->review_flag_model->get_all([
				'user_id'	=> (int)$this->session->userdata('user_id'),
				'review_id'	=> (int)$this->input->post('review_id'),
			])['rows'][0] ?? [])) {
				$this->review_flag_model->add([
					'user_id'				=> (int)$this->session->userdata('user_id'),
					'review_flag_type_id'	=> $this->input->post('review_flag_type_id'),
					'review_id'				=> $this->input->post('review_id'),
					'reason'				=> $this->input->post('reason'),
				]);

				$this->json['success'] = _l('saved_successfully');
			} else {
				$this->json['error'] = _li('already_reported');
			}
		}
	}
}
