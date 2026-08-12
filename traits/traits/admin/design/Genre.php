<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Genre {
	public function genre($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$page 	= (int)($this->uri->segment(3) ?? 1);
		$limit 	= 30;

		$filter_data = [
			'parent_id'	=> 0,
			'start' 	=> ($page - 1) * $limit,
			'limit' 	=> $limit,
			'search' 	=> $this->input->get('search[value]'),
		];

		$result 	= $this->genre_model->get_all($filter_data);

		$this->pagination->initialize(self::_pagination([
			'total'		=> $result['total'],
			'limit'		=> $limit,
			'base_url' 	=> base_url('admin/genre')
		]));

		$data['page_name'] 		= 'genre/index';
		$data['page_title'] 	= _l('genres');
		$data['action_ajax'] 	= '';
		$data['genres'] 		= $result['rows'];
		$data['pagination'] 	= $this->pagination->create_links();

		$this->load->view('backend/index', $data);
	}

	public function add_genre($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$genre_info  = [];

		if ($id) {
			$genre_info = $this->genre_model->get($id);

			if (empty($genre_info)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(base_url('admin/genre'), 'refresh');
			}
		}

		$genre_locales = $this->genre_locale_model->get_all(['genre_id'=> $id])['rows'] ?? [];

		$data['page_name'] 			= 'genre/form';
		$data['page_title'] 		= (!$id) ? _l('add_genre') : _l('edit_genre');
		$data['genre_info'] 		= $genre_info;
		$data['genre_locale']		= !empty($genre_locales) ? explode(',', $genre_locales[0]['country_code']) : [];

		$this->load->view('backend/index', $data);
	}

	public function save_genre($id = false) {
		if ($id) {
			$genre_info = $this->genre_model->get($id);

			if (empty($genre_info)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(base_url('admin/genre'), 'refresh');
			}
		}

		$this->form_validation->set_message('alpha_numeric_spaces', 'Only Characters, Numbers & Spaces are  allowed in %s');

		$config = [
			[
				'field' => 'code',
				'label' => 'Category Code',
				'rules' => 'trim|required',
			],
			[
				'field' => 'name',
				'label' => 'Category Name',
				'rules' => 'trim|required',
			],
			[
				'field' => 'sort_order',
				'label' => 'Sort Order',
				'rules' => 'trim|numeric',
			],
			[
				'field' => 'status',
				'label' => 'status',
				'rules' => 'trim|required|in_list[1,0]',
			]
		];

		$this->form_validation->set_rules($config);

		if ($this->form_validation->run()) {
			// upload image
			$image_name 	= NULL;
			$upload_folder 	= $this->config->item('s3_categories');

			if (!empty($_FILES['imageFile']['name'])) {
				$file_temp_name 	= $_FILES['imageFile']['tmp_name'];
				$image_name 		= $_FILES['imageFile']['name'];
				$file_name 			= $this->s3->amazonS3Upload($image_name, $file_temp_name, $upload_folder);
			}

			$save =  [
				'name' 		=> trim($this->input->post('name')),
				'slug' 		=> slugify(html_escape($this->input->post('name'))),
				'sort_order'=> trim($this->input->post('sort_order')),
				'status' 	=> trim($this->input->post('status')),
				'parent_id'	=> trim($this->input->post('parent_id')),
			];

			if (!empty($image_name)) {
				$save['image'] = $image_name;
			}

			if (!$id) {
				$save['code'] = trim($this->input->post('code'));
				$id = $this->genre_model->add($save);

				CI_Events::trigger('system_access_log', [
					'method'	=> 'genre_add_' . (int)$id,
				]);
			} else {
				$this->genre_model->edit($id, $save);

				CI_Events::trigger('system_access_log', [
					'method'	=> 'genre_edit_' . (int)$id,
				]);
			}

			$country_code = empty($this->input->post('country_code')) ? 'all' : implode(',',$this->input->post('country_code'));

			$genre_locale_info = $this->genre_locale_model->get_all(['genre_id'=> $id])['rows'] ?? [];

			if (!empty($genre_locale_info)) {
				$this->genre_locale_model->edit($genre_locale_info[0]['id'], ['country_code'=> $country_code]);
			} else {
				$this->genre_locale_model->add(
					[
						'genre_id' 		=> $id,
						'country_code' 	=> $country_code
					]);
			}

			$this->session->set_flashdata('success_message', 'Data saved successfully');
			redirect(base_url('admin/genre'), 'refresh');
		} else {
			$this->session->set_flashdata('error_message', validation_errors());
			redirect(base_url('admin/add_genre/'.$id), 'refresh');
		}
	}

	public function delete_genre($id = false) {
		if (!$id) {
			redirect('/admin/genre');
			$this->session->set_flashdata('error_message', 'Invalid data.');
		}

		$genre_info = $this->genre_model->get($id);

		if (empty($genre_info)) {
			$this->session->set_flashdata('error_message', 'Invalid data ID.');
			redirect(base_url('admin/genre'), 'refresh');
		}

		$this->genre_model->delete($id);

		CI_Events::trigger('system_access_log', [
			'method'	=> 'genre_delete_' . (int)$id,
		]);

		$this->session->set_flashdata('success_message', _l('Data deleted successfully.'));
		redirect('/admin/genre');
	}

	public function save_genre_categories() {
		$this->genre_model->addCategories($this->input->post());

		$this->session->set_flashdata('success_message', _l('genre_updated_successfully'));
		redirect('/admin/genre');
	}

	public function ajax_search_genres() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->genre_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s)', $result['name'], $result['id']),
			];
		}

		output_json($json);
	}

	public function ajax_genre_categories($genre_id = 0) {
		$json['data'] = [];

		$results = $this->genre_model->getCategories($genre_id);

		$categories = array_map(function($item) {
			return $this->category_model->get($item['category_id']);
		}, $results);

		if (!empty($categories)) {
			$json['data'] = $categories;
		}

		output_json($json);
	}
}
