<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BookAiReview {
	public function book_ai_review($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'book_id',
			'version',
			'name',
			'author',
			'url',
			'score',
			'creativity_originality',
			'character_development_depth',
			'plot_storytelling',
			'grammatical_errors',
			'imaginative_use_of_language',
			'theme_message',
			'overall_impact',
			'date_added',
			'actions',
		];

		if ($param1 == 'delete') {
			$this->book_ai_review_model->delete($param2);
			redirect(base_url('admin/book_ai_review'), 'refresh');
		}

		$events = $this->event_model->get_all()['rows'] ?? [];

		$data['page_name'] 		= 'book_ai_review/index';
		$data['page_title'] 	= _l('book_ai_review');
		$data['action_ajax'] 	= base_url('admin/ajax_book_ai_review');
		$data['events'] 		= $events;

		$data['actions'] 		= [
			[
				'key'	=> 'gen_summary',
				'type' 	=> 'confirm_ajax',
				'url'	=> 'admin/ajax_book_ai_gen_summary/',
			],
			[
				'key'	=> 'view',
				'url'	=> 'admin/book_ai_review_details/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/book_ai_review/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function book_ai_review_details($id = 0) {
		$data['page_name'] 		= 'book_ai_review/details';
		$data['page_title'] 	= _l('ai_review');

		$data['info']			= $this->book_ai_review_model->get($id);

		if (empty($data['info'])) {
			$this->session->set_flashdata('error_message', _l('invalid_review_id'));
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_book_ai_review() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if ($this->input->get('event_id')) {
			$filter_data['event_id'] = (int)$this->input->get('event_id');
		}

		if ($this->input->get('sku')) {
			$filter_data['book_id'] = (int)$this->input->get('sku');
		}

		if ($this->input->get('league')) {
			list($challenge_type, $challenge_id) = explode('_', $this->input->get('league'));

			$filter_data['challenge_type'] 	= $challenge_type;
			$filter_data['challenge_id'] 	= $challenge_id;
		}

		if ($this->input->get('score_le')) {
			$filter_data['score_le'] = (int)$this->input->get('score_le');
		}

		if ($this->input->get('score_ge')) {
			$filter_data['score_ge'] = (int)$this->input->get('score_ge');
		}

		$results = $this->book_ai_review_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'book_id'				=> $result['book_id'],
				'version'				=> $result['version'],
				'name'					=> $result['name'],
				'author'				=> $result['author'],
				'url'					=> sprintf('<a href="%s" target="_blank">%s</a>', USER_URL . 'bookpreview/' . $result['slug'], _l('preview')),
				'score'					=> $result['score'],
				'creativity_originality'=> $result['creativity_originality'],
				'character_development_depth'=> $result['character_development_depth'],
				'plot_storytelling'		=> $result['plot_storytelling'],
				'grammatical_errors'	=> $result['grammatical_errors'],
				'imaginative_use_of_language'=> $result['imaginative_use_of_language'],
				'theme_message'			=> $result['theme_message'],
				'overall_impact'		=> $result['overall_impact'],
				'date_added'			=> format_date($result['date_added']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}

	public function ajax_book_ai_review_gen() {
		if (
			$this->input->post('book_id') &&
			$book_info = $this->book_model->get($this->input->post('book_id'))
		) {
			$event_id 			= 0;
			$event_book_info 	= $this->event_book_model->get_all([
				'book_id' => $book_info['id']
			])['rows'][0] ?? [];

			if (!empty($event_book_info) && !empty($event_book_info['event_end_date']) && $event_book_info['event_end_date'] < date('Y-m-d H:i:s')) {
				$event_id = $event_book_info['event_id'];
			}

			$result = _curl(
				'http://172.31.42.71:5003/bb44khjkhskdjfhs344w56g657686233243Ghghj',
				[
					'event_id'		=> $event_id,
					'id'			=> $book_info['id'],
					'version'		=> $book_info['version'],
					'thread_type'	=> 'sync',
				],
			);

			output_json($result);
		}
	}

	public function ajax_book_ai_gen_summary($review_id = 0) {
		if (
			$review_id &&
			$info = $this->book_ai_review_model->get($review_id)
		) {
			$result = _curl(
				'http://172.31.42.71:5004/bb55khjkhskdjfhs355w56g657686233243',
				[
					'event_id'		=> $info['event_id'],
					'id'			=> $info['book_id'],
					'version'		=> $info['version'],
					'thread_type'	=> 'sync',
				],
			);

			output_json($result);
		}
	}
}
