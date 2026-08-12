<?php defined('BASEPATH') or exit('No direct script access allowed');

trait CrosswordStore {
	public function crossword_store($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {

            self::check_crossword_store($this->input->post('city_id'),$this->input->post('store_name'));

            $uploadeddata = $this->s3->amazonS3Upload(
				'crossword_store_' . $_FILES["Image"]["name"],
				$_FILES['Image']['tmp_name'],
				rtrim($this->config->item('s3_crossword_store_images'), '/')
			);

		    $city_info	= $this->city_model->get($this->input->post('city_id'));

			$this->cross_word_store_model->add([
                'state_id'   => !empty($city_info) ? $city_info['state_id'] : 0,
				'city_id'    => $this->input->post('city_id'),
				'store_name' => $this->input->post('store_name'),
                'image'      => $uploadeddata["image_name"]
			]);
			redirect(site_url('admin/crossword_store'), 'refresh');
		} elseif ($param1 == 'edit') {

            self::check_crossword_store($this->input->post('city_id'),$this->input->post('store_name'), $param2);

		    $city_info	= $this->city_model->get($this->input->post('city_id'));

            $update_data = [
                'state_id'   => !empty($city_info) ? $city_info['state_id'] : 0,
                'city_id'    => $this->input->post('city_id'),
				'store_name' => $this->input->post('store_name')
			];

            if(!empty($_FILES['Image']['tmp_name'])){
                $uploadeddata = $this->s3->amazonS3Upload(
                    'crossword_store_' . $_FILES["Image"]["name"],
                    $_FILES['Image']['tmp_name'],
                    rtrim($this->config->item('s3_crossword_store_images'), '/')
                );
                $update_data['image'] = $uploadeddata["image_name"];
            }

			$this->cross_word_store_model->edit($param2, $update_data);
			redirect(site_url('admin/crossword_store'), 'refresh');
		} elseif ($param1 == 'delete') {

			$this->cross_word_store_model->edit($param2, ['status' => 0]);
			redirect(site_url('admin/crossword_store'), 'refresh');
		} elseif ($param1 == 'add_book') {
			unset($_SESSION['flash_message']);
			unset($_SESSION['error_message']);

			$book_info = $this->book_model->get_all(['isbn' => $this->input->post('book_isbn')])['rows'][0] ?? '';

			if (!empty($book_info)) {
				if (empty($store_book_info = $this->cross_word_book_model->get_all(['store_id' => (int)$param2, 'book_id' => $book_info['id']])['rows'][0] ?? '')) {
					$this->cross_word_book_model->add([
						'store_id'	=> (int)$param2,
						'book_id'	=> $book_info['id']
					]);
					$this->session->set_flashdata('flash_message', _l('book_added_successfully'));
				}
			} else {
				$this->session->set_flashdata('error_message', _l('book_is_invalid'));
			}
			redirect(site_url('admin/crossword_store_form/add_book/'.(int)$param2), 'refresh');
		}

		$data['page_name'] 			= 'crossword_store/index';
		$data['page_title'] 		= _l('crossword_store');
		$data['action_add'] 		= site_url('admin/crossword_store_form/add');
		$data['action_ajax'] 		= site_url('admin/ajax_crossword_store');
		$data['action_ajax_book'] 	= site_url('admin/ajax_crossword_store_book');

		$this->load->view('backend/index', $data);
	}

	public function crossword_store_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'crossword_store/form';
			$data['page_title'] 					= _l('crossword_store_add');
			$data['action'] 						= site_url('admin/crossword_store/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'crossword_store/form';
			$data['page_title'] 					= _l('crossword_store_edit');
			$data['action'] 						= site_url('admin/crossword_store/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->cross_word_store_model->get($param2);
		} elseif ($param1 == 'view_books') {
			$store_info = $this->cross_word_store_model->get((int)$param2);
			$page_title = !empty($store_info) ? $store_info['store_name'].', '.$store_info['city'] : _l('crossword_store_book');

			$data['page_name'] 						= 'crossword_store/view_book';
			$data['page_title'] 					= $page_title;
			$data['action_ajax'] 					= site_url('admin/ajax_crossword_store_book');
			$data['action_add'] 					= site_url('admin/crossword_store_form/add_book/' . (int)$param2);
			$data['id'] 							= (int)$param2;
		} elseif ($param1 == 'add_book') {

			$store_info = $this->cross_word_store_model->get((int)$param2);
			$page_title = !empty($store_info) ? _l('add_book_for').' '.$store_info['store_name'].', '.$store_info['city'] : _l('add_crossword_store_book');


			$data['page_name'] 						= 'crossword_store/add_book';
			// $data['page_title'] 					= _l('add_crossword_store_book');
			$data['page_title'] 					= $page_title;
			$data['action'] 						= site_url('admin/crossword_store/add_book/'.(int)$param2);
			$data['id'] 							= (int)$param2;
		}

		$data['cities']	= $this->city_model->get_all([
            'city_ids' => [247, 256, 402, 155, 64, 473, 105, 71]
        ])['rows'];

		$this->load->view('backend/index', $data);
	}

	public function ajax_crossword_store() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->cross_word_store_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'city'				    => $result['city'],
				'store_name'			=> $result['store_name'],
				'date_added'			=> formatDate($result['date_added']),
				'date_modified'			=> formatDate($result['date_modified']),
				'status'				=> _sd($result['status'] ? 1 : 0),
				'actions'				=> ['id' => $result['id']],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function ajax_crossword_store_book() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if(!empty($this->input->get('store_id'))) {
			$filter_data['store_id'] = $this->input->get('store_id');
		}

		$results = $this->cross_word_book_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'store_name'				=> $result['store_name'],
				'book_name'				=> $result['book_name'],
				'book_isbn'				=> $result['book_isbn'],
				'date_added'			=> formatDate($result['date_added']),
				'status'				=> '<i class="mdi mdi-circle" style="color: '.($result['status'] == 1 ? "#4CAF50" : "#FFC107").'; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title='.($result['status'] == 1 ? "Enabled" : "Disabled").'></i>',
				'action' => '<div class="dropright dropright">
				<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="mdi mdi-dots-vertical"></i></button>
				<ul class="dropdown-menu">
				<li>
				<a class="dropdown-item statusBtn" href="#" store_book_status='.($result['status'] == 1 ? '0' : '1').' store_id='.$result['id'].'>'.($result['status'] == 1 ? "Disabled" : "Enabled").'</a>
				</ul></div>'
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

    public function check_crossword_store($city_id, $store_name, $id = '') {
        $store_info = $this->cross_word_store_model->get_all([
			'city_id'	    => $city_id,
			'store_name'	=> $store_name
		])['rows'][0] ?? [];
		if(!empty($store_info)) {
            if($id != $store_info['id']){
                $this->session->set_flashdata('error_message', 'Store is already added.');
                redirect('/admin/crossword_store');
            }
        }
	}

	public function update_crossword_book_status() {
		$json = [];

		// pr($this->input->post('id'),1);

		if (!empty($this->input->post('id'))) {
			$this->cross_word_book_model->edit($this->input->post('id'), ['status' => $this->input->post('status')]);
			$json['success'] 	= _l('store_book_updated');
		}

		output_json($json);

	}
}
