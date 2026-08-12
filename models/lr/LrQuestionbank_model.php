<?php defined('BASEPATH') OR exit('No direct script access allowed');

class LrQuestionbank_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->lrdb = $this->db;
	}

	public function get($questionbank_id = 0) {
		$this->lrdb->select('questionbank.*');

		$this->lrdb->where('questionbank.id', (int)$questionbank_id);

		return $this->lrdb->get('questionbank')->row_array();
	}

	public function get_all($data = []) {
		$this->lrdb->select('
			questionbank.*,
		');

		if (isset($data['status'])) {
			$this->lrdb->where('questionbank.status', (int)$data['status']);
		}

		if (!empty($data['category_id'])) {
			$this->lrdb->where('questionbank.category_id', (int)$data['category_id']);
		}

		if (!empty($data['category_id_ne'])) {
			$this->lrdb->where_not_in('questionbank.category_id', $data['category_id_ne']);
		}

		if (isset($data['pending_category']) && $data['pending_category'] === false) {
			$this->lrdb->where('questionbank.category_id NOT IN (SELECT id FROM category WHERE status = 0)');
		}

		if (!empty($data['level'])) {
			$this->lrdb->where('questionbank.level', $data['level']);
		}

		if (!empty($data['question'])) {
			$this->lrdb->like('questionbank.question', $data['question'], 'after');
		}

		if (!empty($data['search'])) {
			$this->lrdb->group_start();
			$this->lrdb->like('questionbank.question', $data['search'], 'after');
			$this->lrdb->or_like('questionbank.explanation_heading', $data['search'], 'after');
			$this->lrdb->or_like('questionbank.explanation_details', $data['search'], 'after');
			$this->lrdb->group_end();
		}

		$this->lrdb->from('questionbank');

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
			'rand()',
			'question',
			'status',
			'date_added',
			'date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'questionbank.date_added';
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
		$this->lrdb->insert('questionbank', $data + [
			'date_added' 		=> date('Y-m-d H:i:s'),
			'date_modified' 	=> date('Y-m-d H:i:s'),
		]);

		$questionbank_id = $this->lrdb->insert_id();

		self::updateImage($questionbank_id);
		self::updateImage($questionbank_id, 'option_1_image');
		self::updateImage($questionbank_id, 'option_2_image');
		self::updateImage($questionbank_id, 'option_3_image');
		self::updateImage($questionbank_id, 'option_4_image');
		self::updateImage($questionbank_id, 'explanation_image');

		$this->session->set_flashdata('flash_message', _l('questionbank_added_successfully'));

		return $questionbank_id;
	}

	public function edit($questionbank_id = 0, $data = []) {
		$this->lrdb->where('id', $questionbank_id);
		$this->lrdb->update('questionbank', $data + [
			'date_modified' 		=> date('Y-m-d H:i:s'),
		]);

		self::updateImage($questionbank_id);
		self::updateImage($questionbank_id, 'option_1_image');
		self::updateImage($questionbank_id, 'option_2_image');
		self::updateImage($questionbank_id, 'option_3_image');
		self::updateImage($questionbank_id, 'option_4_image');
		self::updateImage($questionbank_id, 'explanation_image');

		$this->session->set_flashdata('flash_message', _l('questionbank_edited_successfully'));
	}

	public function delete($questionbank_id = 0) {
		$this->lrdb->where('id', $questionbank_id);
		$this->lrdb->delete('questionbank');

		$this->session->set_flashdata('flash_message', _l('questionbank_deleted_successfully'));
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->lrdb->where('id', $id);
			$this->lrdb->update('questionbank', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('class_updated_successfully'));
	}

	private function updateImage($questionbank_id = 0, $key = 'image') {
		if (!empty($_FILES[$key]['size'])) {
			$file = $this->tool_model->upload(
				$key,
				'',
				'uploads/lr/questionbank/',
			);

			if (!isset($file['error'])) {
				$this->lrdb->update('questionbank', [
					$key			=> 'lr/questionbank/' . $file['file_name'],
				], [
					'id'			=> (int)$questionbank_id
				]);
			} else {
				$this->session->set_flashdata('error_message', $file['error']);
			}
		}
	}
}
