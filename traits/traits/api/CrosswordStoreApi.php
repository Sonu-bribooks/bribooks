<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait CrosswordStoreApi {
	public function getCrosswordStores() {
		if (!$this->json) {
			$filter_data  = [
				'status' => 1
			];

			if (!empty($this->input->post('store_id'))) {
				$filter_data['store_id'] =  $this->input->post('store_id');
			}

			$this->json['stores'] = array_map(function($item) {
				return [
					'id'			=> $item['id'],
					'store_name'	=> $item['store_name'],
					'city'			=> $item['city'],
					'image'			=> !empty($item['image'])
						? $this->config->config['s3_base_url'] . 'public/CrossWordStoreImage/' . $item['image']
						: USER_INVOICE_URL .'assets/images/crossword_store/default.png'
				];
			}, $this->cross_word_store_model->get_all($filter_data)['rows'] ?? []);
		}
	}

	public function getCrosswordStoreBooks() {
		$this->form_validation->set_rules('store_id', _l('store_id'), [
			'required',
			'trim',
			'numeric',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$results = $this->cross_word_book_model->get_all([
				'store_id'		=> (int)$this->input->post('store_id'),
				'status'	 	=> 1
			])['rows'] ?? [];

			foreach($results as $value) {
				$school_name = '';
				$state_name = '';
				$city_name = '';
				$book_info = $this->book_model->get($value['book_id']);

				$book_data = self::_addRatingAndSold($book_info);

				if (!empty($book_info)) {
					if (!empty($user_info = $this->student_model->get($book_info['user_id']))) {
						if (!empty($site_info = $this->site_model->getSchoolBySiteId($user_info['site_id']))) {
							$school_name = $site_info['name'];
							$state_name  = $site_info['state'];
							$city_name   = $site_info['city'];
						}
					}

					$book_data['school_name'] = $school_name;
					$book_data['state']	   = $state_name;
					$book_data['city']		= $city_name;
				}

				$result[] = $book_data;
			}
			$this->json['books'] = !empty($result) ? $result : [];
		}
	}
}
