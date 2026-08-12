<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait AjaxLr {
	public function ajax_lr_category() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->lr_category_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'parent_category'		=> $this->lr_category_model->formatName($result['parent_id']),
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function ajax_lr_questionbank($category_id = 0, $level = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'category_id'		=> (int)$category_id,
			'level'				=> $level === 'all' ? '' : $level,
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->lr_questionbank_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'question'				=> _qt($result['question'], 'uploads/' . $result['image']),
				'category'				=> $this->lr_category_model->formatName($result['category_id']),
				'level'					=> $result['level'],
				'answer'				=> $result['answer'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function ajax_lr_assessment() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->lr_assessment_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->student_model->get($result['user_id'])->row_array();
			$user_info = $user_info ? $user_info : $this->teacher_model->get($result['user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'category'				=> $this->lr_category_model->formatName($result['category_id']),
				'user'					=> $user_info['first_name'] . ' ' . $user_info['last_name'] . _unr($user_info['role_id']),
				'marks'					=> $result['marks'] . ' / ' . $result['total_questions'],
				'time_taken'			=> $this->lr_assessment_model->getQuizTime($result['id']),
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function ajax_lr_assessment_answers() {
		$json = [];

		if (
			$this->input->post('assessment_id')
			&& $assessment_info = $this->lr_assessment_model->get($this->input->post('assessment_id'))
		) {
			$user_info = $this->student_model->get($assessment_info['user_id']);

			if (!$user_info) {
				$user_info = $this->teacher_model->get($assessment_info['user_id']);
			}

			$user_answers = $this->lr_assessment_model->get_all_questions([
				'assessment_id'	=> $assessment_info['id'],
			])['rows'] ?? [];

			$json['info'] = vsprintf(_l('%s Score %d/%d'),[
				$assessment_info['category'],
				$assessment_info['marks'],
				$assessment_info['total_questions'],
			]);

			foreach ($user_answers as $key => $user_answer) {
				$questionbank = $this->lr_questionbank_model->get($user_answer['questionbank_id']);

				$json['data'][] = [
					'sn'				=> $key + 1,
					'id'				=> $questionbank['id'],
					'question'			=> $questionbank['question'],
					'correct_answer'	=> $questionbank['answer'],
					'user_answer'		=> $user_answer['user_answer'] ?? '',
					'time_taken'		=> $user_answer['time_taken'] ?? '',
				];
			}

			$json['files'] = [];

			$dir = FCPATH . 'uploads/recording/' . $assessment_info['user_id'] . '/' . $assessment_info['id'] . '/';
			$files = glob($dir . '*.mp4');

			foreach ($files as $file) {
				$json['files'][] = base_url(str_replace(FCPATH, '', $file));
			}
		} else {
			$json['error'] = _l('invalid_assessment');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function ajax_lr_assessment_code() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->lr_assessment_code_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->student_model->get($result['user_id'])->row_array();
			$user_info = $user_info ? $user_info : $this->teacher_model->get($result['user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'code'					=> $result['code'],
				'level'					=> $result['level'],
				'category'				=> $result['category'],
				'user'					=> $user_info['first_name'] . ' ' . $user_info['last_name'] . _unr($user_info['role_id']),
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
