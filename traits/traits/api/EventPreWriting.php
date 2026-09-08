<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventPreWriting {
	public function getPreWriting() {
		$this->form_validation->set_rules('book_id', _l('book_id'), 'trim|required|numeric');

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_validateWriting()) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			$this->load->model('api/EventPreWriting_model', 'event_pre_writing_model');

			$pre_writing_info = $this->event_pre_writing_model->get_all([
				'book_id'  	=> (int)$this->input->post('book_id'),
				'user_id'  	=> (int)$this->session->userdata('user_id'),
				'start'		=> 0,
				'limit'		=> 1,
			])['rows'][0] ?? [];

			if (!empty($pre_writing_info)) {
				$this->json['success'] 		= _l('success');
				$this->json['pre_writing'] = [
					'pre_writing_id' 	=> $pre_writing_info['id'] ?? 0,
					'event_id'	   		=> $pre_writing_info['event_id'] ?? 0,
					'genre_id'	   		=> $pre_writing_info['genre_id'] ?? 0,
					'category_id'	   	=> $pre_writing_info['category_id'] ?? 0,
					'book_id'			=> $pre_writing_info['book_id'] ?? 0,
					'user_id'			=> $pre_writing_info['user_id'] ?? 0,
					'place'		  		=> $pre_writing_info['place'] ?? '',
					'hero'		   		=> $pre_writing_info['hero'] ?? '',
					'twist'		  		=> $pre_writing_info['twist'] ?? ''
				];
			} else {
				$this->json['error'] = _l('no_pre_writing_found');
			}
		}
	}

	public function updatePreWriting() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);
		$this->input->post('category_id') && $this->form_validation->set_rules('category_id', _l('category_id'), [
			'trim',
			'required',
			'numeric',
			['category', [$this->validate_model, 'category']]
		]);
		$this->input->post('genre_id') && $this->form_validation->set_rules('genre_id', _l('genre_id'), [
			'trim',
			'required',
			'numeric',
			['genre', [$this->validate_model, 'genre']]
		]);
		$this->form_validation->set_rules('place', _l('place'), 'trim|in_list[My School,My City,My State,A Train Journey,A Holiday,Grandparents Home,A Sports Ground,The Internet,A Magical Place,Future India,Underwater World,Space]');
		$this->form_validation->set_rules('hero', _l('hero'), 'trim|in_list[A Student Like Me,he Underdog,The Brain,The Fearless One,The Funny One,The New Kid,A Best-Friend Duo,A Team,Sibling Heroes,A Talking Animal,A Young Inventor,A Hidden Heir]');
		$this->form_validation->set_rules('twist', _l('twist'), 'trim|in_list[A secret is discovered,Something impossible happens,A friend is not who they seem,A race against time begins,The hero must make a brave choice]');

		self::_runFormValidation();

		if (!$this->json) {
			if (!self::_validateWriting()) {
				$this->json['error'] = _l('not_authorized');
				return;
			}

			$this->load->model('api/EventPreWriting_model', 'event_pre_writing_model');

			if (
				!empty($pre_writing_info = $this->event_pre_writing_model->get_all([
					'book_id'   => (int)$this->input->post('book_id'),
					'start'	 	=> 0,
					'limit'	 	=> 1,
				])['rows'][0] ?? [])
			) {
				$this->event_pre_writing_model->edit($pre_writing_info['id'], [
					'genre_id'	  	=> (int)$this->input->post('genre_id'),
					'category_id'   => (int)$this->input->post('category_id'),
					'place'		 	=> $this->input->post('place'),
					'hero'		  	=> $this->input->post('hero'),
					'twist'		 	=> $this->input->post('twist')
				]);
				$pre_writing_id = $pre_writing_info['id'];
			} else {
				$pre_writing_id = $this->event_pre_writing_model->add([
					'event_id'	  	=> (int)($this->input->post('event_id') ?? 0),
					'book_id'	   	=> (int)$this->input->post('book_id'),
					'user_id'	   	=> (int)$this->session->userdata('user_id'),
					'genre_id'	  	=> (int)$this->input->post('genre_id'),
					'category_id'   => (int)$this->input->post('category_id'),
					'place'		 	=> $this->input->post('place'),
					'hero'		  	=> $this->input->post('hero'),
					'twist'		 	=> $this->input->post('twist')
				]);
			}

			$pre_writing_info  		= $this->event_pre_writing_model->get($pre_writing_id);

			$this->json['success'] 	= _li('Pre-writing added successfully');

			$this->json['pre_writing'] = [
				'pre_writing_id' 	=> $pre_writing_info['id'] ?? 0,
				'event_id'	   		=> $pre_writing_info['event_id'] ?? 0,
				'book_id'			=> $pre_writing_info['book_id'] ?? 0,
				'user_id'			=> $pre_writing_info['user_id'] ?? 0,
				'genre_id'			=> $pre_writing_info['genre_id'] ?? 0,
				'category_id'		=> $pre_writing_info['category_id'] ?? 0,
				'place'		  		=> $pre_writing_info['place'] ?? '',
				'hero'		   		=> $pre_writing_info['hero'] ?? '',
				'twist'		  		=> $pre_writing_info['twist'] ?? ''
			];
		}
	}
}
