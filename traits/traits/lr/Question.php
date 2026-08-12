<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Question {
	public function nextQuestion() {
		if (($assessment_info = $this->lr_assessment_model->get($this->session->userdata('assessment_id'))) &&
			$assessment_info['status'] == 1
		) {
			$this->json['finished'] = _l('assessment_over');

			self::finishQuiz($assessment_info);
		}

		if (empty($this->json['finished'])) {
			if ($this->input->post('skip')) {
				$question_info 		= $this->lr_questionbank_model->get($this->input->post('question_id'));
				$time_taken 		= time() - $this->session->userdata('start_time');

				$this->lr_assessment_model->addQuestion([
					'assessment_id'		=> (int)$this->session->userdata('assessment_id'),
					'questionbank_id'	=> (int)$this->input->post('question_id'),
					'user_answer'		=> -1,
					'correct_answer'	=> (int)($question_info['answer'] ?? 0),
					'time_taken'		=> (int)$time_taken,
				]);
			}

			$data 							= self::getLastQuestionBank($this->session->userdata('assessment_id'));
			$this->json['question'] 		= $data['question'];
			$this->json['summary'] 			= $data['summary'] ?? '';
			$this->json['current_index'] 	= $data['current_index'];

			$this->lr_assessment_model->edit($this->session->userdata('assessment_id'), [
				'current_index' => $data['current_index']
			]);

			$this->session->set_userdata('start_time', time());

			// $assessment_info 	= $this->lr_assessment_model->get($this->session->userdata('assessment_id'));
			//
			// if (($assessment_info['current_index'] - 1) == $assessment_info['total_questions']) {
			// 	self::showSummary($assessment_info);
			// }
		}

		self::setOutput();
	}

	private function getQuestionBanks() {
		$category_id = (int)$this->session->userdata('quiz_category_id');

		$categories = $this->lr_category_model->get_all([
			'parent_id' => (int)$category_id,
			'status'	=> 1,
			'sort'		=> 'id',
			'order'		=> 'ASC'
		])['rows'] ?? [];

		if ($categories) {
			$data['categories'] = array_map(function($item) {
				$is_locked = (int)$category_id === 0
					? false
					: $this->lr_assessment_model->getCategoryLock($item['id']);

				$is_locked = $this->session->userdata('role_id') == 3
					? false
					: $is_locked;

				return [
					'id'			=> $item['id'],
					'name'			=> $item['name'],
					'description'	=> $item['name'],
					'is_locked'		=> $is_locked,
					'image'			=> is_file('uploads' . $item['image']) ? base_url('uploads/' . $item['image']) : base_url('uploads/no_image.png'),
				];
			}, $categories);
		} else {
			$filter_data = [
				'category_id' 		=> (int)$category_id,
				'pending_category'	=> false,
				'level' 			=> $this->session->userdata('quiz_level'),
				'status'			=> 1,
				'start'				=> 0,
				'limit'				=> 10,
				'sort'				=> 'rand()',
				'order'				=> 'ASC'
			];

			if ($this->session->userdata('role_id') == 3) {
				$filter_data['limit'] = 30;
				$filter_data['category_id_ne'] = [20, 21, 22, 13];
				unset($filter_data['level'], $filter_data['category_id']);
			}

			$questionbanks = $this->lr_questionbank_model->get_all($filter_data);

			if (empty($questionbanks['rows'])) return;

			$data['assessment_id'] = $this->lr_assessment_model->add([
				'user_id'			=> (int)$this->session->userdata('quiz_uid'),
				'assessment_code_id'=> (int)$this->session->userdata('quiz_code_id'),
				'category_id'		=> (int)$category_id,
				'ip'				=> $this->input->ip_address(),
				'user_agent'		=> $this->input->user_agent(),
				'total_questions'	=> count($questionbanks['rows']), // $questionbanks['total']
			]);

			// increase attempt
			$this->lr_assessment_code_model->updateAttempt($this->session->userdata('quiz_code_id'));

			foreach ($questionbanks['rows'] ?? [] as $index => $questionbank) {
				$this->lr_assessment_model->addQuestion([
					'assessment_id' 	=> (int)$data['assessment_id'],
					'questionbank_id' 	=> (int)$questionbank['id'],
					'user_answer' 		=> 0,
					'correct_answer' 	=> (int)$questionbank['answer'],
					'index' 			=> $index + 1,
					'time_taken' 		=> 0,
				]);
			}

			$quiz_end_time = date('Y-m-d H:i:s', strtotime('+' . QUIZ_TIME[(int)$this->session->userdata('role_id')] . ' minutes'));

			$this->load->model('common/Cron_model', 'cron_model');
			$this->cron_model->add([
				'code'			=> 'assessment_' . $data['assessment_id'],
				'action'		=> 'lr_assessment_model->complete',
				'data'			=> [$data['assessment_id']],
				'alert_date'	=> $quiz_end_time,
			]);

			$data['question'] 		= $this->load->view('lr/layout', [
				'question'	=> self::formatQuestion($questionbanks['rows'][0] ?? [])
			], TRUE);

			$data['assessment'] 	= $this->lr_assessment_model->get($data['assessment_id']);
			$data['current_index'] 	= 1;

			$this->session->set_userdata([
				'start_time' 					=> time(),
				'quiz_end_time'					=> $quiz_end_time,
				'assessment_id'					=> $data['assessment_id'],
				'cat_' . (int)$category_id		=> (int)$data['assessment_id'],
			]);
		}

		return $data;
	}

	private function formatQuestion($item = []) {
		if (!$item) return $item;

		return [
			'id'					=> $item['id'],
			'question'				=> $item['question'],
			'question_img'			=> is_file('uploads/' . $item['image']) ? base_url('uploads/' . $item['image']) : '',
			'opt_1'					=> $item['option_1'],
			'opt_2'					=> $item['option_2'],
			'opt_3'					=> $item['option_3'],
			'opt_4'					=> $item['option_4'],
			'opt_1_img'				=> is_file('uploads/' . $item['option_1_image']) ? base_url('uploads/' . $item['option_1_image']) : '',
			'opt_2_img'				=> is_file('uploads/' . $item['option_2_image']) ? base_url('uploads/' . $item['option_2_image']) : '',
			'opt_3_img'				=> is_file('uploads/' . $item['option_3_image']) ? base_url('uploads/' . $item['option_3_image']) : '',
			'opt_4_img'				=> is_file('uploads/' . $item['option_4_image']) ? base_url('uploads/' . $item['option_4_image']) : '',
			'layout'				=> $item['layout'],
			'explanation_heading'	=> $item['explanation_heading'],
			'explanation_details'	=> $item['explanation_details'],
			'explanation_img'		=> is_file('uploads/' . $item['explanation_image']) ? base_url('uploads/' . $item['explanation_image']) : '',
		];
	}

	private function getLastQuestionBank($assessment_id = 0) {
		$data = [];

		if ($assessment_info = $this->lr_assessment_model->get($assessment_id)) {
			$questionbanks = $this->lr_assessment_model->get_all_questions([
				'assessment_id' => (int)$assessment_info['id'],
				'user_answer'	=> 0,
				// 'start'			=> $assessment_info['current_index'],
				'limit'			=> 1,
				'sort'			=> 'index',
				'order'			=> 'ASC'
			])['rows'] ?? [];

			$questionbank_info 	= $this->lr_questionbank_model->get($questionbanks[0]['questionbank_id'] ?? 0);

			$data['question'] 	= $this->load->view('lr/layout', [
				'question'	=> self::formatQuestion($questionbank_info ?? [])
			], TRUE);
			$data['assessment'] = $assessment_info;

			$this->session->set_userdata('assessment_id', $data['assessment']['id']);

			if (
				empty($questionbanks)
				&& $assessment_info['status'] !=1
			) {
				self::showSummary($assessment_info);
				$data['summary'] = $this->json['summary'];
				$data['current_index']	= $assessment_info['total_questions'];
			} else {
				$data['current_index'] 	= ($questionbanks[0]['index'] ?? 1);
			}
		}

		return $data;
	}

	public function saveAnswer() {
		$this->form_validation->set_rules('question_id', _l('question_id'), 'trim|required|numeric|callback_questionbank_check');
		$this->form_validation->set_rules('answer', _l('answer'), 'trim|required|numeric|callback_answer_check');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (empty($this->session->userdata('quiz_uid'))) {
			$this->json['error'] = _l('unauthorized');
		}

		if (
			!$this->json &&
			($assessment_info = $this->lr_assessment_model->get($this->session->userdata('assessment_id'))) &&
			$assessment_info['status'] == 1
		) {
			$this->json['finished'] = _l('assessment_over');
			self::finishQuiz($assessment_info);
		}

		if (!$this->json) {
			$assessment_info 			= $this->lr_assessment_model->get($this->session->userdata('assessment_id'));
			$question_info 				= $this->lr_questionbank_model->get($this->input->post('question_id'));
			$assessment_question_info 	= $this->lr_assessment_model->get_all_questions([
				'questionbank_id' 	=> (int)$this->input->post('question_id'),
				'assessment_id' 	=> (int)$this->session->userdata('assessment_id'),
			])['rows'][0] ?? [];

			$time_taken = time() - $this->session->userdata('start_time');

			$this->lr_assessment_model->addQuestion([
				'assessment_id'		=> (int)$this->session->userdata('assessment_id'),
				'questionbank_id'	=> (int)$this->input->post('question_id'),
				'user_answer'		=> (int)$this->input->post('answer'),
				'correct_answer'	=> (int)($question_info['answer'] ?? 0),
				'time_taken'		=> (int)$time_taken,
			]);

			// $options = ['A', 'B', 'C', 'D'];
			//
			// $data['answer'] = [
			// 	'id'					=> $question_info['id'],
			// 	'question'				=> $question_info['question'],
			// 	'question_img'			=> is_file('uploads/' . $question_info['image']) ? base_url('uploads/' . $question_info['image']) : '',
			// 	'correct_answer'		=> $question_info['answer'],
			// 	'user_answer'			=> (int)$this->input->post('answer'),
			// 	'explanation_heading'	=> $question_info['explanation_heading'],
			// 	'explanation_details'	=> $question_info['explanation_details'],
			// 	'answer'				=> $question_info['option_' . (int)$question_info['answer']],
			// 	'answer_img'			=> is_file('uploads/' . $question_info['option_' . (int)$question_info['answer'] . '_image']) ? base_url('uploads/' . $question_info['option_' . (int)$question_info['answer'] . '_image']) : '',
			// 	'answer_icon'			=> site_url('assets/frontend/default/lr/images/24x24-ICON-' . $options[$question_info['answer'] - 1] . '.png'),
			// 	'user_answer_icon'		=> site_url('assets/frontend/default/lr/images/24x24-ICON-' . $options[(int)$this->input->post('answer') - 1] . '.png'),
			// ];
			//
			// $this->json['answer'] 			= $this->load->view('lr/answer', $data, TRUE);

			$this->json['current_index'] 	= $assessment_question_info['index'];

			$this->lr_assessment_model->edit($this->session->userdata('assessment_id'), [
				'current_index' => ($assessment_info['current_index'] + 1)
			]);

			$this->json['success'] = _l('answer_submitted');

			if ($this->json['current_index'] >= $assessment_info['total_questions']) {
				$this->json['last'] = true;
			}
		}

		// self::setOutput();
		self::nextQuestion();
	}

	public function saveAnswerOld() {
		$this->form_validation->set_rules('question_id', _l('question_id'), 'trim|required|numeric|callback_questionbank_check');
		$this->form_validation->set_rules('answer', _l('answer'), 'trim|required|numeric|callback_answer_check');

		$valid = $this->form_validation->run();

		!$valid && ($this->json['error'] = strip_tags(validation_errors()));

		if (empty($this->session->userdata('quiz_uid'))) {
			$this->json['error'] = _l('unauthorized');
		}

		if (
			!$this->json &&
			($assessment_info = $this->lr_assessment_model->get($this->session->userdata('assessment_id'))) &&
			$assessment_info['status'] == 1
		) {
			$this->json['finished'] = _l('assessment_over');
			self::finishQuiz($assessment_info);
		}

		if (!$this->json) {
			$assessment_info 			= $this->lr_assessment_model->get($this->session->userdata('assessment_id'));
			$question_info 				= $this->lr_questionbank_model->get($this->input->post('question_id'));
			$assessment_question_info 	= $this->lr_assessment_model->get_all_questions([
				'questionbank_id' 	=> (int)$this->input->post('question_id'),
				'assessment_id' 	=> (int)$this->session->userdata('assessment_id'),
			])['rows'][0] ?? [];

			$time_taken = time() - $this->session->userdata('start_time');

			$this->lr_assessment_model->addQuestion([
				'assessment_id'		=> (int)$this->session->userdata('assessment_id'),
				'questionbank_id'	=> (int)$this->input->post('question_id'),
				'user_answer'		=> (int)$this->input->post('answer'),
				'correct_answer'	=> (int)($question_info['answer'] ?? 0),
				'time_taken'		=> (int)$time_taken,
			]);

			$options = ['A', 'B', 'C', 'D'];

			$data['answer'] = [
				'id'					=> $question_info['id'],
				'question'				=> $question_info['question'],
				'question_img'			=> is_file('uploads/' . $question_info['image']) ? base_url('uploads/' . $question_info['image']) : '',
				'correct_answer'		=> $question_info['answer'],
				'user_answer'			=> (int)$this->input->post('answer'),
				'explanation_heading'	=> $question_info['explanation_heading'],
				'explanation_details'	=> $question_info['explanation_details'],
				'answer'				=> $question_info['option_' . (int)$question_info['answer']],
				'answer_img'			=> is_file('uploads/' . $question_info['option_' . (int)$question_info['answer'] . '_image']) ? base_url('uploads/' . $question_info['option_' . (int)$question_info['answer'] . '_image']) : '',
				'answer_icon'			=> site_url('assets/frontend/default/lr/images/24x24-ICON-' . $options[$question_info['answer'] - 1] . '.png'),
				'user_answer_icon'		=> site_url('assets/frontend/default/lr/images/24x24-ICON-' . $options[(int)$this->input->post('answer') - 1] . '.png'),
			];

			$this->json['answer'] 			= $this->load->view('lr/answer', $data, TRUE);
			$this->json['current_index'] 	= $assessment_question_info['index'];

			$this->lr_assessment_model->edit($this->session->userdata('assessment_id'), [
				'current_index' => ($assessment_info['current_index'] + 1)
			]);

			$this->json['success'] = _l('answer_submitted');

			if ($this->json['current_index'] >= $assessment_info['total_questions']) {
				$this->json['last'] = true;
			}
		}

		self::setOutput();
	}
}
