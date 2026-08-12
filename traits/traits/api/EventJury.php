<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventJury {
	public function getEventJuryBooks() {
        $this->form_validation->set_rules('event_id', _l('event'), [
			'trim',
			'required',
			'numeric',
			['event', [$this->validate_model, 'event']]
		]);

        $this->form_validation->set_rules('challenge_id', _l('challenge'), [
			'trim',
			'numeric',
			'required',
		]);

		$this->form_validation->set_rules('type', _l('type'), [
			'trim',
			'required'
		]);

		self::_runFormValidation();

		if (!$this->json) {
            $filter_data = [
				'status'	=> 1,
				'sort'		=> 'event_jury_book.rank',
				'order'		=> 'ASC',
			];

            if (!empty($this->input->post('event_id'))) {
				$filter_data['event_id'] = $this->input->post('event_id');
			}

			if (!empty($this->input->post('challenge_id'))) {
                $filter_data['challenge_id'] = $this->input->post('challenge_id');
			}

            if (!empty($this->input->post('genre_id'))) {
                $filter_data['genre_id'] = $this->input->post('genre_id');
			}

			if (!empty($this->input->post('city_id'))) {
                $filter_data['city_id'] = $this->input->post('city_id');
			}

			if (!empty($this->input->post('state_id'))) {
                $filter_data['state_id'] = $this->input->post('state_id');
			}

			if (!empty($this->input->post('country_id'))) {
                $filter_data['country_id'] = $this->input->post('country_id');
			}

			if (!empty($this->input->post('type'))) {
                $filter_data['type'] = $this->input->post('type');
			}

			if (!empty($this->input->post('search'))) {
                $filter_data['search'] = $this->input->post('search');
			}

			$results = $this->event_jury_book_model->get_all($filter_data);

			$this->json['books'] 		= $results;
			$this->json['filter_data'] 	= $filter_data;
        }
    }

	public function getEventJuryBooksBySlug() {
		$this->form_validation->set_rules('slug', _l('slug'), [
			'trim',
			'required',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$results = [];

			$challenge_info = $this->event_challenge_jury_model->get_all([
				'slug' => $this->input->post('slug')
			])['rows'][0] ?? [];

			if (!empty($challenge_info)) {
				$filter_data = [
					'jury_challenge_id'	=> $challenge_info['id'],
					'genre_id'			=> $challenge_info['genre_id'] ?? 0,
					'status'			=> 1,
					'limit'				=> $challenge_info['limit'] ?? 20,
					'sort'				=> 'event_jury_book.rank',
					'order'				=> 'ASC',
				];

				if (!empty($this->input->post('city_id'))) {
					$filter_data['city_id'] = $this->input->post('city_id');
				}

				if (!empty($this->input->post('state_id'))) {
					$filter_data['state_id'] = $this->input->post('state_id');
				}

				if (!empty($this->input->post('country_id'))) {
					$filter_data['country_id'] = $this->input->post('country_id');
				}
	
				if (!empty($this->input->post('search'))) {
					$filter_data['search'] = $this->input->post('search');
				}
	
				$results = $this->event_jury_book_model->get_all($filter_data)['rows'] ?? [];
			}

			$this->json['challenge'] 	= $challenge_info;
			$this->json['books'] 		= $results;
        }
    }
}