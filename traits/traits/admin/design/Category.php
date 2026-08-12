<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Category {
	public function category($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$page 	= (int)($this->uri->segment(3) ?? 1);
		$limit 	= 30;

		$filter_data = [
			'parent_id'	=> 0,
			'start' 	=> ($page - 1) * $limit,
			'limit' 	=> $limit,
			'search' 	=> $this->input->get('search[value]'),
		];

		$result 	= $this->category_model->get_all($filter_data);

		$this->pagination->initialize(self::_pagination([
			'total'		=> $result['total'],
			'limit'		=> $limit,
			'base_url' 	=> site_url('admin/category')
		]));

		$data['page_name'] 		= 'category/index';
		$data['page_title'] 	= _l('Categories');
		$data['action_ajax'] 	= '';
		$data['categories'] 	= $result['rows'];
		$data['pagination'] 	= $this->pagination->create_links();

		$this->load->view('backend/index', $data);
	}

	public function add_category($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$category_info  = [];

		if ($id) {
			$category_info = $this->category_model->get($id);

			if (empty($category_info)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/category'), 'refresh');
			}
		}

		$category_locales = $this->category_locale_model->get_all(['category_id'=> $id])['rows'] ?? [];

		$data['page_name'] 			= 'category/form';
		$data['page_title'] 		= (!$id) ? _l('Add Theme') : _l('Edit Theme');
		$data['category_info'] 		= $category_info;
		$data['categories'] 		= $this->category_model->get_all(['parent_id' => 0])['rows'] ?? [];
		$data['category_locale']	= !empty($category_locales) ? explode(',', $category_locales[0]['country_code']) : [];

		$this->load->view('backend/index', $data);
	}

	public function save_category($id = false) {
		if ($id) {
			$category_info = $this->category_model->get($id);

			if (empty($category_info)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/category'), 'refresh');
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
				$id = $this->category_model->add($save);

				CI_Events::trigger('system_access_log', [
					'method'	=> 'category_add_' . (int)$id,
				]);
			} else {
				$this->category_model->edit($id, $save);

				CI_Events::trigger('system_access_log', [
					'method'	=> 'category_edit_' . (int)$id,
				]);
			}

			$country_code = empty($this->input->post('country_code')) ? 'all' : implode(',',$this->input->post('country_code'));

			$category_locale_info = $this->category_locale_model->get_all(['category_id'=> $id])['rows'] ?? [];

			if (!empty($category_locale_info)) {
				$this->category_locale_model->edit($category_locale_info[0]['id'], ['country_code'=> $country_code]);
			} else {
				$this->category_locale_model->add(
					[
						'category_id' 		=> $id,
						'country_code' 	=> $country_code
					]);
			}

			$this->session->set_flashdata('success_message', 'Data saved successfully');
			redirect(site_url('admin/category'), 'refresh');
		} else {
			$this->session->set_flashdata('error_message', validation_errors());
			redirect(site_url('admin/add_category/'.$id), 'refresh');
		}
	}

	public function delete_category($id = false) {
		if (!$id) {
			redirect('/admin/category');
			$this->session->set_flashdata('error_message', 'Invalid data.');
		}

		$category_info = $this->category_model->get($id);

		if (empty($category_info)) {
			$this->session->set_flashdata('error_message', 'Invalid data ID.');
			redirect(site_url('admin/category'), 'refresh');
		}

		$this->category_model->delete($id);

		CI_Events::trigger('system_access_log', [
			'method'	=> 'category_delete_' . (int)$id,
		]);

		$this->session->set_flashdata('success_message', 'Data deleted successfully.');
		redirect('/admin/category');
	}

	public function ajax_subcategories($parent_id = 0) {
		$json['data'] = [];

		$json['data'] = $parent_id
			? $this->category_model->get_all([
				'parent_id' => $parent_id
			])['rows'] ?? []
			: []
		;

		output_json($json);
	}

	public function ajax_all_categories() {
		$json['data'] = $this->category_model->get_all()['rows'] ?? [];

		output_json($json);
	}

	public function ajax_search_categories() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		if ($this->input->get('genre_id')) {
			$filter_data['genre_id'] = (int)$this->input->get('genre_id');
		}

		$results = $this->category_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s)', $result['name'], $result['id']),
			];
		}

		output_json($json);
	}
}
