<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait InternalAlert {
	public function alertInternalISBNAmazonCron($data = []) {
		log_kb([
			'alertInternalISBNAmazonCron::data:: ' => $data
		]);

		if (empty($data)) return;

		$this->load->model('book/Bookstore_model', 'bookstore_model');

		$book_info = $this->bookstore_model->getByBookId($data['book_id']);

		if (empty($book_info)) return;

		$data['title']			= sprintf(_li('%s Eligibility & Sales Update for %s, Location: %s'), str_replace('_', ' & ', mb_strtoupper($data['type'])), $book_info['name'], $book_info['location']);
		$data['heading']		= $data['title'];

		$data['content']		= $this->load->view('common/mail/part/internal_isbn_amazon_alert', [
			'book_name'			=> $book_info['name'],
			'author_name'		=> $book_info['author_name'],
			'sold'				=> $book_info['sold'],
			'location'			=> $book_info['location'],
			'type'				=> $data['type'],
		], true);

		$message 	= $this->load->view('common/mail/templates/2/general', $data, true);

		$email 		= ENVIRONMENT === 'production' ? 'info@bribooks.com' : 'abhishek@youbooks.co';
		$cc 		= [];
		$bcc 		= ENVIRONMENT === 'production' ? ['adarsh@bribooks.com', 'logistics@bribooks.com'] : ['abhishek@youbooks.co'];

		self::email(
			$email,
			$data['title'],
			$message,
			$cc,
			$bcc
		);
	}
}
