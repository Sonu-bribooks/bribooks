<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Category_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->lrdb = $this->db;
	}

	public function get($category_id = 0) {
		$this->lrdb->select('category.*');

		$this->lrdb->where('category.id', (int)$category_id);

		return $this->lrdb->get('category')->row_array();
	}

	public function get_all($data = []) {
		$this->lrdb->select('
			category.*,
		');

		if (isset($data['status'])) {
			$this->lrdb->where('category.status', (int)$data['status']);
		}

		if (isset($data['parent_id'])) {
			$this->lrdb->where('category.parent_id', (int)$data['parent_id']);
		}

		if (!empty($data['name'])) {
			$this->lrdb->like('category.name', $data['name'], 'after');
		}

		if (!empty($data['search'])) {
			$this->lrdb->group_start();
			$this->lrdb->like('category.name', $data['search'], 'after');
			$this->lrdb->or_like('category.description', $data['search'], 'after');
			$this->lrdb->group_end();
		}

		$this->lrdb->from('category');

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
			'name',
			'status',
			'date_added',
			'date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'category.date_added';
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
		$this->lrdb->insert('category', $data + [
			'date_added' 		=> date('Y-m-d H:i:s'),
			'date_modified' 	=> date('Y-m-d H:i:s'),
		]);

		$category_id = $this->lrdb->insert_id();

		self::updateImage($category_id);

		$this->session->set_flashdata('flash_message', _l('category_added_successfully'));

		return $category_id;
	}

	public function edit($category_id = 0, $data = []) {
		$this->lrdb->where('id', $category_id);
		$this->lrdb->update('category', $data + [
			'date_modified' 		=> date('Y-m-d H:i:s'),
		]);

		self::updateImage($category_id);

		$this->session->set_flashdata('flash_message', _l('category_edited_successfully'));
	}

	public function delete($category_id = 0) {
		$this->lrdb->where('id', $category_id);
		$this->lrdb->delete('category');

		$this->session->set_flashdata('flash_message', _l('category_deleted_successfully'));
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->lrdb->where('id', $id);
			$this->lrdb->update('category', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('class_updated_successfully'));
	}

	private function updateImage($category_id = 0) {
		if (!empty($_FILES['image'])) {
			$file = $this->tool_model->upload(
				'image',
				'',
				'uploads/lr/category/',
			);

			if (!isset($file['error'])) {
				$this->lrdb->update('category', [
					'image'			=> 'lr/category/' . $file['file_name'],
				], [
					'id'			=> (int)$category_id
				]);
			} else {
				$this->session->set_flashdata('error_message', $file['error']);
			}
		}
	}

	public function formatName($category_id = 0, $categories = []) {
		if ($category_id == 0) {
			$categories = array_reverse($categories);

			return implode(' > ', $categories);
		} else {
			if ($category_info = self::get($category_id)) {
				$categories[] = $category_info['name'];
				return self::formatName($category_info['parent_id'], $categories);
			}
		}
	}
}
