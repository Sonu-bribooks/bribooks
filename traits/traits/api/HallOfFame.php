<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait HallOfFame {
    public function getHallOfFameBooks() {
        $this->form_validation->set_rules('type', _l('type'), 'trim|in_list[global,asia]');
        self::_runFormValidation();
        
		if (!$this->json) {
			$page = (int) $this->input->post('page');

			$filter_data = [
				'status' => 1,
				'start'  => $page > 0 ? ($page - 1) * 16 : 0,
				'limit'  => 16,
				'sort'   => 'priority',
				'order'  => 'ASC'
			];

			// Search filter
			$search = trim((string)$this->input->post('search'));

			if ($search !== '') {
				$filter_data['search'] = $search;
			}

            $filter_data['type'] = !empty($this->input->post('type')) ? trim((string)$this->input->post('type')) : 'global';

			// Country filter: all fame books of selected country
			$country_code = $this->input->post('country_code');

			if ($country_code !== null && $country_code !== '') {
				$filter_data['country_code'] = strtoupper(trim($country_code));
			}

			// Event filter: only selected event
			$event_id = $this->input->post('event_id');

			if ($event_id !== null && $event_id !== '') {
				$filter_data['event_id'] = (int) $event_id;
			}

			$key = vsprintf('%s_%s_%s_%s', [
				(ENVIRONMENT === 'production' ? 'live' : 'test'),
				'hall_of_fame_books',
				implode('_', array_keys($filter_data)),
				str_replace(' ', '', implode('_', array_values($filter_data))),
			]);

			$cache_data = json_decode($this->cache->get($key), true);

			if ($cache_data !== null && $cache_data !== false) {
				$this->json['hall_of_fame'] = $cache_data;
			} else {
				$this->load->model('halloffame/HallOfFame_model', 'hall_of_fame_model');

				$rows = $this->hall_of_fame_model->get_all($filter_data)['rows'] ?? [];

				$hall_of_fame_books = array_map(function ($item) {
					$author_image = empty($item['author_image'])
						? base_url('uploads/user_image/placeholder.png')
						: $this->config->item('cloudfront_url') . 'public/' . $item['author_image'];

					return [
						'book_id'      => $item['id'],
						'user_id'      => $item['user_id'],
						'book_name'    => $item['name'],
						'author_name'  => $item['author_name'],
						'book_image'   => $this->config->item('cloudfront_url') . 'public/' . $item['cover_image'],
						'author_image' => $author_image,
						'book_url'     => USER_URL . 'bookstore/' . $item['slug'],
						'book_sold'    => $item['sold'] ?? 0,
						'priority'     => $item['priority'] ?? 0,
						'country'      => $item['location'] ?? '',
						'country_code' => !empty($item['country_code'])
							? strtolower($item['country_code'])
							: ''
					];
				}, $rows);

				$this->json['hall_of_fame'] = array_values(array_filter($hall_of_fame_books));

				$this->cache->save($key, json_encode($this->json['hall_of_fame']), 600);
			}
		}
	}

}