<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Game_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->gdb = $this->db;
	}

	public function get($adt_game_id = 0) {
		$this->gdb->select('adt_game.*');

		$this->gdb->where('adt_game.id', (int)$adt_game_id);

		return $this->gdb->get('adt_game')->row_array();
	}

	public function get_all($data = []) {
		$this->gdb->select('
			adt_game.*
		');

		if (isset($data['status'])) {
			$this->gdb->where('adt_game.game_status', (int)$data['status']);
		}

		if (isset($data['game_id'])) {
			$this->gdb->where('adt_game.game_id', (int)$data['game_id']);
		}

		if (isset($data['level'])) {
			$this->gdb->where('adt_game.game_level', $data['level']);
		}

		if (!empty($data['mode'])) {
			$this->gdb->where('adt_game.game_mode', $data['mode']);
		}

		if (!empty($data['type'])) {
			$this->gdb->where('adt_game.game_type', $data['type']);
		}

		if (!empty($data['search'])) {
			$this->gdb->group_start();
			$this->gdb->like('adt_game.game_name', $data['search'], 'after');
			$this->gdb->or_like('adt_game.game_en_name', $data['search'], 'after');
			$this->gdb->or_like('adt_game.game_id', $data['search'], 'after');
			$this->gdb->group_end();
		}

		$this->gdb->from('adt_game');

		$total = $this->gdb->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->gdb->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'id',
			'game_name',
			'game_status',
			'start_time',
			'end_time',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'adt_game.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = "ASC";
		} else {
			$order = "DESC";
		}

		$this->gdb->order_by($sort, $order);

		return ['rows' => $this->gdb->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->gdb->insert('adt_game', $data + [
			'start_timestamp' 	=> strtotime($data['start_time']),
			'end_timestamp' 	=> strtotime($data['end_time']),
		]);

		$adt_game_id = $this->gdb->insert_id();

		$this->session->set_flashdata('flash_message', _l('adt_game_added_successfully'));

		return $adt_game_id;
	}

	public function edit($adt_game_id = 0, $data = []) {
		$data['start_time'] = date('Y-m-d H:i:s', strtotime($data['start_time']));
		$data['end_time'] = date('Y-m-d H:i:s', strtotime($data['end_time']));

		$this->gdb->where('id', (int)$adt_game_id);
		$this->gdb->update('adt_game', $data + [
			'start_timestamp' 	=> strtotime($data['start_time']),
			'end_timestamp' 	=> strtotime($data['end_time']),
		]);

		$this->session->set_flashdata('flash_message', _l('adt_game_edited_successfully'));
	}

	public function delete($adt_game_id = 0) {
		$this->gdb->where('id', (int)$adt_game_id);
		$this->gdb->delete('adt_game');

		$this->session->set_flashdata('flash_message', _l('adt_game_deleted_successfully'));
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['game_status']);
			$this->gdb->where('id', (int)$id);
			$this->gdb->update('adt_game', [
				'game_status'			=> (int)$status,
				// 'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('class_updated_successfully'));
	}
}
