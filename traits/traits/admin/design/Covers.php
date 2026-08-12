<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Covers {
	public function covers($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$page 	= (int)($this->uri->segment(3) ?? 1);
		$limit 	= 30;

		$filter_data = [
			'start' 	=> ($page - 1) * $limit,
			'limit' 	=> $limit,
			'search' 	=> $this->input->get('search'),
		];

		$results 	= $this->cover_model->get_all($filter_data);

		$this->pagination->initialize(self::_pagination([
			'total'		=> $results['total'],
			'limit'		=> $limit,
			'base_url' 	=> site_url('admin/covers')
		]));

		$data['page_name'] 		= 'covers/index';
		$data['page_title'] 	= _l('covers');
		$data['covers'] 		= $results['rows'];
		$data['search']			= $this->input->get('search') ?? '';
		$data['pagination'] 	= $this->pagination->create_links();

		$this->load->view('backend/index', $data);
	}

	public function add_cover($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$cover = [];

		if ($id) {
			$cover = $this->cover_model->get($id);

			if (empty($cover)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/covers'), 'refresh');
			}
		}

		$cover_locales = $this->cover_locale_model->get_all(['cover_id'=> $id])['rows'] ?? [];

		$data['page_name'] 		= 'covers/form';
		$data['page_title'] 	= _l('Add Cover');
		$data['cover_info'] 	= $cover;
		$data['categories'] 	= $this->category_model->get_all(['parent_id' => 0])['rows'] ?? [];
        $data['cover_locale']	= !empty($cover_locales) ? explode(',', $cover_locales[0]['country_code']) : [];

		$this->load->view('backend/index', $data);
	}

	public function save_cover($id = false) {
		if ($id) {
			$cover = $this->cover_model->get($id);

			if (empty($cover)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/covers'), 'refresh');
			}
		}

		$this->form_validation->set_message('alpha_numeric_spaces', 'Only Characters, Numbers & Spaces are  allowed in %s');

		$config = [
			[
				'field' => 'category',
				'label' => 'Category',
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

		if (!$id) {
			$config[] = [
				'field' => 'imageFile',
				'label' => 'Cover Image',
				'rules' => 'trim|required',
			];
		}

		$this->form_validation->set_rules($config);

		if ($this->form_validation->run()) {
			$category_info = $this->category_model->get(trim($this->input->post('category')));

			// upload image
			$upload_folder 	= $this->config->item('s3_covers');
			$image_name 	= NULL;

			if (!empty($category_info)) {
				$upload_folder .= str_replace(' ', '-', trim($category_info['name'])) . '';
				$image_cat 		= str_replace(' ', '-', trim($category_info['name'])) . '/';
			}

			if (!empty($_FILES['imageFile']['name'])) {
				$file_temp_name = $_FILES['imageFile']['tmp_name'];
				$image_name 	= $_FILES['imageFile']['name'];

				$file_name = $this->s3->amazonS3Upload($image_name, $file_temp_name, $upload_folder);
			} else {
				$image_name = null;
			}

			$save = [
				'category_id' 	=> trim($this->input->post('category')),
				'heading_style' => trim(json_encode($this->input->post('hs'))),
				'footer_style'  => trim(json_encode($this->input->post('fs'))),
				'sort_order' 	=> trim($this->input->post('sort_order')),
				'tags' 			=> trim($this->input->post('tags')) ?? '',
				'status' 		=> trim($this->input->post('status'))
			];

			if (!empty($image_name)) {
				$save['image'] = 'Covers/' . $image_cat . $image_name;
			}

			if (!$id) {
				$id = $this->cover_model->add($save);

				CI_Events::trigger('system_access_log', [
					'method'	=> 'cover_add_' . (int)$id,
				]);
			} else {
				$this->cover_model->edit($id, $save);

				CI_Events::trigger('system_access_log', [
					'method'	=> 'cover_edit_' . (int)$id,
				]);
			}

			$country_code 		= empty($this->input->post('country_code')) ? 'all' : implode(',', $this->input->post('country_code'));

			$cover_locale_info 	= $this->cover_locale_model->get_all(['cover_id'=> $id])['rows'] ?? [];

			if (!empty($cover_locale_info)) {
				$this->cover_locale_model->edit($cover_locale_info[0]['id'], ['country_code'=> $country_code]);
			} else {
				$this->cover_locale_model->add(
					[
						'cover_id' 		=> $id,
						'country_code' 	=> $country_code
					]);
			}

			$this->session->set_flashdata('success_message', 'Data saved successfully');
			redirect(site_url('admin/covers/'), 'refresh');
		} else {
			$this->data['error'] = validation_errors();

			$this->session->set_flashdata('error_message', validation_errors());
			redirect(site_url('admin/add_cover/' . $id), 'refresh');
		}
	}

	public function add_bulk_cover($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$cover = [];

		if ($id) {
			$cover = $this->cover_model->get($id);

			if (empty($cover)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/covers'), 'refresh');
			}
		}

		$data['page_name'] 		= 'covers/add_bulk';
		$data['page_title'] 	= _l('Add Cover');
		$data['cover_info'] 	= $cover;
		$data['categories'] 	= $this->category_model->get_all([
			'parent_id' => 0
		])['rows'] ?? [];

		$this->load->view('backend/index', $data);
	}

	public function save_bulk_cover($id = false) {
		if ($id) {
			$cover = $this->cover_model->get($id);

			if (empty($cover)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/covers'), 'refresh');
			}
		}

		if (!$this->input->post('category')) {
			$this->session->set_flashdata('error_message', _l('category_is_required!.'));
			redirect(site_url('admin/covers'), 'refresh');
		}

		$this->form_validation->set_message('alpha_numeric_spaces', 'Only Characters, Numbers & Spaces are  allowed in %s');

		$config = [
			[
				'field' => 'category',
				'label' => 'Category',
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
			$category_info = $this->category_model->get(trim($this->input->post('category')));

			// upload image
			$upload_folder = $this->config->item('s3_covers');
			$image_cat     = '';

			if (!empty($category_info)) {
				$upload_folder .= str_replace(' ', '-', trim($category_info['name'])) . '';
				$image_cat    	= str_replace(' ', '-', trim($category_info['name'])) . '';
			}

			if (!empty($_FILES['imageFile']['name'])) {
				$file_temp_name = $_FILES['imageFile']['tmp_name'];
				$image_name   	= $_FILES['imageFile']['name'];

				if (!empty($image_name)) {
					for ($i = 0; $i < count($image_name); $i++) {
						$file_name[] = $this->s3->amazonS3Upload($image_name[$i], $file_temp_name[$i], $upload_folder);
					}
				}
			}

			$save =  [
				'category_id' 	=> trim($this->input->post('category')),
				'heading_style' => trim(json_encode($this->input->post('hs'))),
				'footer_style'  => trim(json_encode($this->input->post('fs'))),
				'sort_order' 	=> trim($this->input->post('sort_order')),
				'tags' 			=> trim($this->input->post('tags')) ?? '',
				'status' 		=> trim($this->input->post('status'))
			];

			$country_code = empty($this->input->post('country_code')) ? 'all' : implode(',', $this->input->post('country_code'));

			if (!empty($image_name)) {
				for ($i = 0; $i < count($image_name); $i++) {
					$save['image'] = 'Covers/' . $image_cat . '/' . $image_name[$i];

					if (!$id) {
						$cover_id = $this->cover_model->add($save);

						CI_Events::trigger('system_access_log', [
							'method'	=> 'cover_add_' . (int)$cover_id,
						]);
					} else {
						$this->cover_model->edit($id, $save);

						CI_Events::trigger('system_access_log', [
							'method'	=> 'cover_edit_' . (int)$id,
						]);
					}

					$cover_id 			= ($id >0) ? $id : $cover_id;
					$cover_locale_info 	= $this->cover_locale_model->get_all(['cover_id'=> $cover_id])['rows'] ?? [];

					if (!empty($cover_locale_info)) {
						$this->cover_locale_model->edit($cover_locale_info[0]['id'], ['country_code'=> $country_code]);
					} else {
						$this->cover_locale_model->add(
							[
								'cover_id' 		=> $cover_id,
								'country_code' 	=> $country_code
							]);
					}
				}
			}

			$this->session->set_flashdata('success_message', 'Data saved successfully');
			redirect(site_url('admin/covers/'), 'refresh');
		} else {
			$this->data['error'] = validation_errors();
			$this->session->set_flashdata('error_message', validation_errors());
			redirect(site_url('admin/add_bulk_cover/' . $id), 'refresh');
		}
	}

	public function delete_cover($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if (!$id) {
			$this->session->set_flashdata('error_message', 'Invalid data ID.');
			redirect(site_url('admin/covers'), 'refresh');
		}

		if ($id) {
			$cover = $this->cover_model->get($id);

			if (empty($cover)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/covers'), 'refresh');
			}
		}

		$this->cover_model->delete($id);

		CI_Events::trigger('system_access_log', [
			'method'	=> 'cover_delete_' . (int)$id,
		]);

		$this->session->set_flashdata('success_message', 'Data saved successfully');
		redirect(site_url('admin/covers'), 'refresh');
	}
}
