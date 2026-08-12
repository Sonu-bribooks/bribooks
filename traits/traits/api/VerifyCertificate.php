<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait VerifyCertificate {
	public function verifyCertificate() {
		$this->form_validation->set_rules('certificate_id', _l('certificate_id'), [
			'trim',
			'required',
		]);
		self::_runFormValidation();

		if (!$this->json) {
			if (
				!empty($certificate_info = $this->certificate_model->getByCode($this->input->post('certificate_id')))
			) {
				$book_info 		= $this->bookstore_model->get_all([
					'book_id' => $certificate_info['book_id']
				])['rows'][0] ?? '';
				
				if ($certificate_info['achievement'] == 2) {
					$author_info 	= $this->student_model->get($certificate_info['user_id']);
					$author_name = !empty($author_info) ? ucwords($author_info['first_name'] . ' ' . $author_info['last_name']) : '';
				} else {
					$author_name = !empty($book_info) ? $book_info['author_name'] : '';
				}

				$this->json['certificate_info'] = [
					'certificate_number' 	=> $certificate_info['unique_id'],
					'author_name' 			=> $author_name,
					'book_name' 			=> !empty($book_info) ? $book_info['name'] : '',
				];
			} else {
				$this->json['error'] = _l('certificate_not_found');
			}
		}
	}
}
