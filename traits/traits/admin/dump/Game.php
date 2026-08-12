<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Game {
	public function game($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$this->game_model->add($this->input->post());
			redirect(site_url('admin/game'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->game_model->edit($param2, $this->input->post());
			redirect(site_url('admin/game'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->game_model->enableDisable($param2, $this->input->post());
			redirect(site_url('admin/game'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->game_model->delete($param2);
			redirect(site_url('admin/game'), 'refresh');
		}

		$data['page_name'] 		= 'game/game/index';
		$data['page_title'] 	= _l('game');
		$data['action_add'] 	= site_url('admin/game_form/add');
		$data['action_ajax'] 	= site_url('admin/ajax_game');

		$this->load->view('backend/index', $data);
	}

	public function game_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'game/game/form';
			$data['page_title'] 					= _l('game_add');
			$data['action'] 						= site_url('admin/game/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'game/game/form';
			$data['page_title'] 					= _l('game_edit');
			$data['action'] 						= site_url('admin/game/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->game_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_game() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->game_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'game_id'				=> $result['game_id'],
				'game_name'				=> $result['game_name'],
				'status'				=> _sd($result['game_status']),
				'start_time'			=> formatDate($result['start_time']),
				'end_time'				=> formatDate($result['end_time']),
				'mode'					=> $result['game_mode'] == 0 ? _l('blockly') : _l('python'),
				'max_level'				=> $result['max_level'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['game_status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
