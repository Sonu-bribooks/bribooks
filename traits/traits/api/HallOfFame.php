<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait HallOfFame {
	public function getHallOfFameBooks() {
		$this->form_validation->set_rules('type', _l('type'), 'trim|in_list[global,asia]');
		self::_runFormValidation();

		if (!$this->json) {
			$page = (int)$this->input->post('page');

			$filter_data = [
				'status'=> 1,
				'start'	=> $page > 0 ? ($page - 1) * 10 : 0,
				'limit'	=> 10,
				'sort'	=> 'priority',
				'order'	=> 'ASC'
			];

			$search = trim((string)$this->input->post('search'));

			if ($search !== '') {
				$filter_data['search'] = $search;
			}

			$country_code			= $this->input->post('country_code');
			$filter_data['type']	= !empty($this->input->post('type')) ? trim((string)$this->input->post('type')) : 'global';

			if ($country_code !== null && $country_code !== '') {
				$filter_data['country_code'] = strtoupper(trim($country_code));
			}

			$event_id = $this->input->post('event_id');

			if ($event_id !== null && $event_id !== '') {
				$filter_data['event_id'] = (int)$event_id;
			}

			$key = vsprintf('%s_%s_%s_%s', [
				(ENVIRONMENT === 'production' ? 'live' : 'test'),
				'hall_of_fame_books',
				implode('_', array_keys($filter_data)),
				str_replace(' ', '', implode('_', array_values($filter_data))),
			]);

			$cache_data = json_decode($this->cache->get($key), true);

			if (!empty($cache_data)) {
				$this->json['hall_of_fame'] 		= $cache_data['rows'] ?? [];
				$this->json['total'] 				= (int)($cache_data['total'] ?? 0);
				$this->json['country_wise_data'] 	= $cache_data['country_wise_data'] ?? [];
			} else {
				$this->load->model('halloffame/HallOfFame_model', 'hall_of_fame_model');

				$hall_of_fame_data = $this->hall_of_fame_model->get_all($filter_data) ?? [];

				$hall_of_fame_books = array_map(function ($item) {
					$author_image = empty($item['author_image'])
						? base_url('uploads/user_image/placeholder.png')
						: $this->config->item('cloudfront_url') . 'public/' . $item['author_image'];

					return [
						'book_id'		=> $item['book_id'],
						'user_id'		=> $item['user_id'],
						'book_name'	 	=> $item['name'],
						'author_name'  	=> $item['author_name'],
						'book_image'	=> $this->config->item('cloudfront_url') . 'public/' . $item['cover_image'],
						'author_image' 	=> $author_image,
						'book_url'	  	=> USER_URL . 'bookstore/' . $item['slug'] . '/',
						'book_sold'	 	=> $item['sold'] ?? 0,
						'priority'	  	=> $item['priority'] ?? 0,
						'country'		=> $item['location'] ?? '',
						'country_code' 	=> !empty($item['country_code'])
							? strtolower($item['country_code'])
							: '',
						'type'			=> $item['hall_of_fame_type'] ?? 'global',
						'event_id'	  	=> (int)($item['event_id'] ?? 0),
					];
				}, $hall_of_fame_data['rows'] ?? []);

				$country_wise_data = $this->hall_of_fame_model->getCountryWiseData($filter_data['type']) ?? [];

				$this->json['hall_of_fame'] 		= array_values(array_filter($hall_of_fame_books));
				$this->json['total'] 				= (int)($hall_of_fame_data['total'] ?? 0);
				$this->json['country_wise_data'] 	= $country_wise_data;

				$prepare_cache_data = [
					'rows'  			=> $this->json['hall_of_fame'],
					'total' 			=> $this->json['total'],
					'country_wise_data' => $this->json['country_wise_data']
				];

				$this->cache->save($key, json_encode($prepare_cache_data), 600);
			}
		}
	}
}
