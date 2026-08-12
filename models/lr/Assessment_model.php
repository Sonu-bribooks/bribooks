<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Assessment_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->lrdb = $this->db;
	}

	public function get($assessment_id = 0) {
		$this->lrdb->select('assessment.*, category.name AS category');

		$this->lrdb->where('assessment.id', (int)$assessment_id);

		$this->lrdb->join('category', 'category.id=assessment.category_id');

		return $this->lrdb->get('assessment')->row_array();
	}

	public function get_all($data = []) {
		$this->lrdb->select('
			assessment.*,
			category.name AS category
		');

		if (isset($data['status'])) {
			$this->lrdb->where('assessment.status', (int)$data['status']);
		}

		if (!empty($data['assessment_code_id'])) {
			$this->lrdb->where('assessment.assessment_code_id', (int)$data['assessment_code_id']);
		}

		if (!empty($data['category_id'])) {
			$this->lrdb->where('assessment.category_id', (int)$data['category_id']);
		}

		if (!empty($data['user_id'])) {
			$this->lrdb->where('assessment.user_id', (int)$data['user_id']);
		}

		if (!empty($data['search'])) {
			$this->lrdb->group_start();
			$this->lrdb->like('assessment.ip', $data['search'], 'after');
			$this->lrdb->or_like('assessment.user_agent', $data['search'], 'after');
			$this->lrdb->group_end();
		}

		$this->lrdb->join('category', 'category.id=assessment.category_id');
		$this->lrdb->from('assessment');

		$total = $this->lrdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->lrdb->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'id',
			'marks',
			'status',
			'date_added',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'assessment.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = "ASC";
		} else {
			$order = "DESC";
		}

		$this->lrdb->order_by($sort, $order);

		return ['rows' => $this->lrdb->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->lrdb->insert('assessment', $data + [
			'date_added' 		=> date('Y-m-d H:i:s'),
			'date_modified' 	=> date('Y-m-d H:i:s'),
		]);

		$assessment_id = $this->lrdb->insert_id();

		// self::updateImage($assessment_id);

		$this->session->set_flashdata('flash_message', _l('assessment_added_successfully'));

		return $assessment_id;
	}

	public function edit($assessment_id = 0, $data = []) {
		$this->lrdb->where('id', $assessment_id);
		$this->lrdb->update('assessment', $data + [
			'date_modified' 		=> date('Y-m-d H:i:s'),
		]);

		// self::updateImage($assessment_id);

		$this->session->set_flashdata('flash_message', _l('assessment_edited_successfully'));
	}

	public function delete($assessment_id = 0) {
		$this->lrdb->where('id', $assessment_id);
		$this->lrdb->delete('assessment');

		$this->session->set_flashdata('flash_message', _l('assessment_deleted_successfully'));
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->lrdb->where('id', $id);
			$this->lrdb->update('assessment', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('assessment_updated_successfully'));
	}

	public function complete($id = 0) {
		if (($row = self::get($id)) && $row['status'] == 0) {
			$total_correct_questions = $this->lrdb->get_where('assessment_questions', [
				'user_answer = correct_answer AND assessment_id = ' => (int)$id,
			])->result_array();

			$this->lrdb->update('assessment', [
				'status'			=> 1,
				'marks'				=> count($total_correct_questions),
				'date_modified' 	=> date('Y-m-d H:i:s'),
			], [
				'id'				=> (int)$id
			]);

			self::processRecording($row);
		}

		$this->session->set_flashdata('flash_message', _l('assessment_updated_successfully'));
	}

	private function processRecording($assessment_info = []) {
		$dir = FCPATH . 'uploads/recording/' . $assessment_info['user_id'] . '/' . $assessment_info['id'] . '/';

		exec(vsprintf('printf "file \'%%s\'\n" %s*.webm > %slist.txt && ffmpeg -f concat -safe 0 -i %slist.txt -c copy -strict experimental %s%s.mp4', [
			$dir,
			$dir,
			$dir,
			$dir,
			$assessment_info['id'],
		]), $res_1);
		$res_2 = shell_exec("rm -fv {$dir}*.webm");
		$res_3 = shell_exec("rm -fv {$dir}*.txt");

		log_message('KB', print_r([
			'res_1' => $res_1,
			'res_2' => $res_2,
			'res_3' => $res_3,
		], 1));
	}

	private function updateImage($assessment_id = 0, $key = 'image') {
		if (!empty($_FILES[$key]['size'])) {
			$file = $this->tool_model->upload(
				$key,
				'',
				'uploads/lr/assessment/',
			);

			if (!isset($file['error'])) {
				$this->lrdb->update('assessment', [
					$key			=> 'lr/assessment/' . $file['file_name'],
				], [
					'id'			=> (int)$assessment_id
				]);
			} else {
				$this->session->set_flashdata('error_message', $file['error']);
			}
		}
	}

	public function get_all_questions($data = []) {
		$this->lrdb->select('
			assessment_questions.*,
		');

		if (isset($data['assessment_id'])) {
			$this->lrdb->where('assessment_questions.assessment_id', (int)$data['assessment_id']);
		}

		if (isset($data['user_answer'])) {
			$this->lrdb->where('assessment_questions.user_answer', (int)$data['user_answer']);
		}

		if (!empty($data['questionbank_id'])) {
			$this->lrdb->where('assessment_questions.questionbank_id', (int)$data['questionbank_id']);
		}

		$this->lrdb->from('assessment_questions');

		$total = $this->lrdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->lrdb->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'assessment_id',
			'questionbank_id',
			'index',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = '';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = '';
		}

		$sort && $order && $this->lrdb->order_by($sort, $order);

		return ['rows' => $this->lrdb->get()->result_array(), 'total' => $total];
	}

	public function addQuestion($data = []) {
		if ($this->lrdb->get_where('assessment_questions', [
			'assessment_id' 	=> (int)$data['assessment_id'],
			'questionbank_id' 	=> (int)$data['questionbank_id'],
		])->row_array()) {
			$this->lrdb->update('assessment_questions', [
				'user_answer' 		=> (int)$data['user_answer'],
				'correct_answer' 	=> (int)$data['correct_answer'],
				'time_taken' 		=> (int)$data['time_taken'],
			], [
				'assessment_id' 	=> (int)$data['assessment_id'],
				'questionbank_id' 	=> (int)$data['questionbank_id'],
			]);
		} else {
			$this->lrdb->insert('assessment_questions', [
				'assessment_id' 	=> (int)$data['assessment_id'],
				'questionbank_id' 	=> (int)$data['questionbank_id'],
				'user_answer' 		=> (int)$data['user_answer'],
				'correct_answer' 	=> (int)$data['correct_answer'],
				'index' 			=> (int)$data['index'],
				'time_taken' 		=> (int)$data['time_taken'],
			]);
		}

		$this->session->set_flashdata('flash_message', _l('assessment_question_added_successfully'));
	}

	public function resetNotAttempted($data = []) {
		$this->lrdb->update('assessment_questions', [
			'user_answer' 		=> 0,
		], [
			'user_answer' 		=> -1,
			'assessment_id' 	=> (int)$data['assessment_id'],
			'index >=' 			=> (int)$data['index'],
		]);
	}

	public function getQuizTime($assessment_id = 0) {
		$this->lrdb->select_sum('time_taken');

		return $this->lrdb->get_where('assessment_questions', [
			'assessment_id' 	=> (int)$assessment_id,
		])->row_array()['time_taken'];
	}

	public function getCategoryLock($category_id = 0) {
		return $this->lrdb->get_where('assessment', [
			'category_id' 	=> (int)$category_id,
			'status' 		=> 1,
			'user_id' 		=> (int)$this->session->userdata('quiz_uid'),
		])->row_array() ? false : true;
	}

	public function getRanking($category_id = 0) {
		$results = self::get_all([
			'category_id' 	=> $category_id,
			'sort'			=> 'marks',
			'order'			=> 'DESC'
		])['rows'] ?? [];

		foreach ($results as $key => $result) {
			if ($result['user_id'] == $this->session->userdata('quiz_uid')) {
				return $key + 1;
			}
		}

		return 0;
	}
}
