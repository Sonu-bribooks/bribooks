<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BroadcastPartnerSlot {
    public function broadcast_partner_slot($param1='', $param2=''){
        $data['fields'] = [
			'sn',
            'id',
			'event_name',
			'broadcast_partner',
			'book_name',
			'author_name',
			'rank',
			'start_date',
			'end_date',
			'actions',
		];

		if ($param1 == 'add') {
			if (!$this->input->post('book_id')) {
					$this->session->set_flashdata('error_message', _l('no_record_found'));
				redirect(base_url('admin/broadcast_partner_slot_form/add'), 'refresh');
			}

			if (!$this->input->post('partner_id')) {
				$this->session->set_flashdata('error_message',  _l('no_record_found'));
				redirect(base_url('admin/broadcast_partner_slot_form/add'), 'refresh');
			}

            $this->broadcast_partner_slot_model->add([
                'event_id'			=> $this->input->post('event_id'),
                'partner_id'		=> $this->input->post('partner_id'),
                'book_id'			=> $this->input->post('book_id'),
                'user_id'			=> $this->input->post('user_id'),
                'rank'				=> $this->input->post('rank'),
                'start_date'		=> $this->input->post('start_date'),
                'end_date'			=> $this->input->post('end_date'),
            ]);

            $this->session->set_flashdata('flash_message', 'Broadcast Partner added successfully!');
			redirect(base_url('admin/broadcast_partner_slot'), 'refresh');
		} elseif ($param1 == 'edit') {
			$broadcast_partner_slot_info	= $this->broadcast_partner_slot_model->get($param2);
            if ($broadcast_partner_slot_info) {
				$this->broadcast_partner_slot_model->edit($param2, [
					'event_id'			=> $this->input->post('event_id'),
					'partner_id'		=> $this->input->post('partner_id'),
					'book_id'			=> $this->input->post('book_id'),
					'user_id'			=> $this->input->post('user_id'),
					'rank'				=> $this->input->post('rank'),
					'start_date'		=> $this->input->post('start_date'),
					'end_date'			=> $this->input->post('end_date'),
				]);
			}

			redirect(base_url('admin/broadcast_partner_slot'), 'refresh');

		} elseif ($param1 == 'delete') {
			$this->broadcast_partner_slot_model->delete($param2);
			redirect(base_url('admin/broadcast_partner_slot'), 'refresh');
		}

		$data['page_name'] 		= 'broadcast_partner_slot/index';
		$data['page_title'] 	= _l('broadcast_partner_slot');
		$data['action_add'] 	= base_url('admin/broadcast_partner_slot_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_broadcast_partner_slot');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/broadcast_partner_slot_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/broadcast_partner_slot/delete/',
			],
		];

		$this->load->view('backend/index', $data);
    }

    public function broadcast_partner_slot_form($param1 = NULL, $param2 = NULL) {
        $data['page_name'] 		= 'broadcast_partner_slot/form';
        $data['page_title'] 	= _l('broadcast_partner_slot_Add');
        $data['action'] 		= base_url('admin/broadcast_partner_slot/add');
        $data['events']         = $this->event_model->get_all()['rows'] ?? [];
        $data['partners']		= $this->broadcast_partner_model->get_all()['rows'] ?? [];
	
        if ($param1 == 'edit') {
			
			$data['page_title'] 	= _l('broadcast_partner_slot_edit');
			$data['action'] 		= base_url('admin/broadcast_partner_slot/edit/' . (int)$param2);
			$data['id'] 			= (int)$param2;
			$data['details'] 		= $this->broadcast_partner_slot_model->get($param2);
			$data['book_details']	= $this->user_rank_country_model->get_all(
											[
												'book_id' => $data['details']['book_id'],
												'event_id' => $data['details']['event_id'],
											])['rows'][0] ?? [];

		}
    
		$this->load->view('backend/index', $data);
	}

	public function ajax_broadcast_partner_slot() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'			
            	=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results	= $this->broadcast_partner_slot_model->get_all($filter_data);
       
        $json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

			$user_rank_country = $this->user_rank_country_model->get_all(
				[
					'book_id' 	=> $result['book_id'],
					'event_id' 	=> $result['event_id'],
				])['rows'] ?? [];

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event_name'			=> $this->event_model->get($user_rank_country[0]['event_id'])['name'],
				'broadcast_partner'		=> $this->broadcast_partner_model->get($result['partner_id'])['name'],
				'book_name'				=> $user_rank_country[0]['book_name'],
				'author_name'		    => $user_rank_country[0]['author_name'],
				'rank'					=> $result['rank'],
				'start_date'			=> $result['start_date'],
				'end_date'				=> $result['end_date'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_get_book_by_event(){
		$json['data'] = [];
		
        if(!$this->input->post('event_id')) return false;

		$user_rank_country = $this->user_rank_country_model->get_all(
			[
				'event_id'  => $this->input->post('event_id'),
				'search'	=> $this->input->post('search') ?? ''
			])['rows'] ?? [];

		if (!empty($user_rank_country)) {
            $json = [
				'success' 	=> true,
				'data'		=> $user_rank_country
			];
		}
		output_json($json);
	}

	public function ajax_user_rank(){
		$json['data'] = [];
      
		$user_rank_country = $this->user_rank_country_model->get_all(
			[
				'book_id' 	=> $this->input->post('book_id'),
				'event_id' 	=> $this->input->post('event_id'),
			])['rows'] ?? [];
	   
		if (!empty($user_rank_country)) {
			$this->load->library('Ranking_lib', 'ranking_lib');

			$rank_result = $this->ranking_lib->getUserCountryRank(
				$user_rank_country[0]['event_id'],
				$user_rank_country[0]['event_challenge_country_id'],
				$user_rank_country[0]['user_id'],
				$user_rank_country[0]['book_id'],
			);
           
            $json = [
				'success' 	=> true,
				'data'		=> $user_rank_country,
				'rank'		=> $rank_result['rank']
			];
		}

		output_json($json);
	}
}