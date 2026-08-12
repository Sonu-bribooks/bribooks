<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Themes {
	public function themes($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'themes/index';
		$data['page_title'] 	= _l('Themes');
		$data['action_ajax'] 	= site_url('admin/ajax_theme');

		$this->load->view('backend/index', $data);
	}

	public function ajax_theme() {
		$json['data'] 	= [];
		$columns 		= $this->input->get('columns');

		$filter_data = [
			'start'				=> (!empty($this->input->get('start'))) ? (int)$this->input->get('start') : 0,
			'limit'				=> (!empty($this->input->get('length'))) ? (int)$this->input->get('length') : 20,
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->theme_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		$image_url = $this->config->item('s3_base_url') . $this->config->item('s3_themes');

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'		=> $filter_data['start'] + 1 + $key,
				'id'		=> $result['id'],
				'image'		=> '<a href="' . $image_url . $result['image'] . '" target="_blank"><img src="' . $image_url . $result['image'] . '" style="width:50px;"></a>',
				'name'		=> $result['name'],
				'category'	=> $result['category'],
				'status'	=> _sd($result['status']),
				'date_added'=> formatDate($result['date_added']),
				'actions'	=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function add_theme($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$theme = [];

		if ($id) {
			$theme = $this->theme_model->get($id);

			if (empty($theme)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/add_theme'), 'refresh');
			}
		}

		$theme_locales = $this->theme_locale_model->get_all(['theme_id'=> $id])['rows'] ?? [];

		$data['page_name'] 		= 'themes/add';
		$data['page_title'] 	= _l('Add Background');
		$data['theme'] 			= $theme;
		$data['categories'] 	= $this->category_model->get_all();
		$data['theme_locale']	= !empty($theme_locales) ? explode(',', $theme_locales[0]['country_code']) : [];

		$this->load->view('backend/index', $data);
	}

	public function delete_theme($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if (!$id) {
			$this->session->set_flashdata('error_message', 'Invalid data ID.');
			redirect(site_url('admin/themes'), 'refresh');
		}

		if ($id) {
			$theme = $this->theme_model->get($id);

			if (empty($theme)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/themes'), 'refresh');
			}
		}

		$this->theme_model->delete($id);

		CI_Events::trigger('system_access_log', [
			'method'	=> 'theme_delete_' . (int)$id,
		]);

		$this->session->set_flashdata('success_message', 'Data saved successfully');

		redirect(site_url('admin/themes'), 'refresh');
	}

	public function save_theme($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($id) {
			$theme = $this->theme_model->get($id);

			if (empty($theme)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/add_theme'), 'refresh');
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
				'field' => 'theme_name',
				'label' => 'Theme Name',
				'rules' => 'trim|required',
			],
			[
				'field' => 'font_family',
				'label' => 'Font Family',
				'rules' => 'trim|required',
			],
			[
				'field' => 'font_size',
				'label' => 'Font Size',
				'rules' => 'trim|required',
			],
			[
				'field' => 'font_color',
				'label' => 'Font Color',
				'rules' => 'trim',
			],
			[
				'field' => 'font_weight',
				'label' => 'Font Color',
				'rules' => 'trim|required',
			],
			[
				'field' => 'status',
				'label' => 'status',
				'rules' => 'trim|required|in_list[1,0]',
			],
		];

		$this->form_validation->set_rules($config);

		if ($this->form_validation->run()) {
			$category_info 	= $this->category_model->get(trim($this->input->post('category')));
			$upload_folder 	= $this->config->item('s3_themes');
			$image_name 	= NULL;

			if (!empty($_FILES['image']['name'])) {
				if (!empty($category_info)) {
					$upload_folder .= str_replace(' ', '-', trim($category_info['name'])) . '';
					// $image_name = str_replace(' ', '-', trim($category_info['name'])) . '/';
				}

				$file_temp_name		= $_FILES['image']['tmp_name'];
				$image_name			.= $_FILES['image']['name'];
				$file_name			= $this->s3->amazonS3Upload($image_name, $file_temp_name, $upload_folder);
			}

			$text_boxes = $this->input->post('parameter');

			if (empty($this->input->post('double_side_writing'))) {
				unset($text_boxes[1]);
			}

			$save =  [
				'category_id' 	=> trim($this->input->post('category')),
				'name' 			=> trim($this->input->post('theme_name')),
				'text_boxes'	=> json_encode($text_boxes),
				'font_family' 	=> trim($this->input->post('font_family')),
				'font_size' 	=> trim($this->input->post('font_size')),
				'font_color' 	=> trim($this->input->post('font_color')),
				'font_weight' 	=> trim($this->input->post('font_weight')),
				'status' 		=> trim($this->input->post('status')),
				'sort_order'	=> trim($this->input->post('sort_order'))
			];

			if (!empty($image_name)) {
				$save['image'] = str_replace('public/Themes/', '', $upload_folder . '/' . $image_name);
			}

			if (!$id) {
				$id = $this->theme_model->add($save);

				CI_Events::trigger('system_access_log', [
					'method'	=> 'theme_add_' . (int)$id,
				]);
			} else {
				$this->theme_model->edit($id, $save);

				CI_Events::trigger('system_access_log', [
					'method'	=> 'theme_edit_' . (int)$id,
				]);
			}

			$country_code 		= empty($this->input->post('country_code')) ? 'all' : implode(',', $this->input->post('country_code'));

			$theme_locale_info 	= $this->theme_locale_model->get_all(['theme_id'=> $id])['rows'] ?? [];

			if (!empty($theme_locale_info)) {
				$this->theme_locale_model->edit($theme_locale_info[0]['id'], ['country_code'=> $country_code]);
			} else {
				$this->theme_locale_model->add(
					[
						'theme_id' 		=> $id,
						'country_code' 	=> $country_code
					]);
			}

			$this->session->set_flashdata('success_message', 'Data saved successfully');
			redirect('/admin/themes/', 'refresh');
		} else {
			$this->data['error'] = validation_errors();

			$this->session->set_flashdata('error_message', validation_errors());
			redirect(base_url('admin/add_theme/'. $id), 'refresh');
		}
	}

	public function add_bulk_theme($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$theme = [];

		if ($id) {
			$theme = $this->theme_model->get($id);

			if (empty($theme)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/add_theme'), 'refresh');
			}
		}

		$data['page_name'] 		= 'themes/add_bulk';
		$data['page_title'] 	= _l('Add Bulk Background');
		$data['theme'] 			= $theme;
		$data['categories'] 	= $this->category_model->get_all([
			'parent_id' => 0
		])['rows'] ?? [];

		$this->load->view('backend/index', $data);
	}

	public function save_bulk_theme($id = false) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 0);
		set_time_limit(0);

		if ($id) {
			$theme = $this->theme_model->get($id);

			if (empty($theme)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/add_theme'), 'refresh');
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
				'field' => 'theme_name',
				'label' => 'Theme Name',
				'rules' => 'trim|required',
			],
			[
				'field' => 'font_family',
				'label' => 'Font Family',
				'rules' => 'trim|required',
			],
			[
				'field' => 'font_size',
				'label' => 'Font Size',
				'rules' => 'trim|required',
			],
			[
				'field' => 'font_color',
				'label' => 'Font Color',
				'rules' => 'trim',
			],
			[
				'field' => 'font_weight',
				'label' => 'Font Color',
				'rules' => 'trim|required',
			],
			[
				'field' => 'status',
				'label' => 'status',
				'rules' => 'trim|required|in_list[1,0]',
			],
		];

		$this->form_validation->set_rules($config);

		if ($this->form_validation->run()) {
			$category_info 	= $this->category_model->get(trim($this->input->post('category')));

			// upload image
			$image_name 	= ($id) ? $theme->image : [];
			$upload_folder 	= $this->config->item('s3_themes');
			$image_cat     	= '';

			if (!empty($category_info)) {
				$upload_folder .= str_replace(' ', '-', trim($category_info['name'])) . '';
				$image_cat    	= str_replace(' ', '-', trim($category_info['name'])) . '';
			}

			if (!empty($_FILES['image']['name'])) {
				$file_temp_name = $_FILES['image']['tmp_name'];
				$image_name   	= $_FILES['image']['name'];

				if (!empty($image_name)) {
					for ($i = 0; $i < count($image_name); $i++) {
						$file_name[] = $this->s3->amazonS3Upload($image_name[$i], $file_temp_name[$i], $upload_folder);
					}
				}
			}

			$text_boxes = $this->input->post('parameter');

			if (empty($this->input->post('double_side_writing'))) {
				unset($text_boxes[1]);
			}

			$save =  [
				'category_id' 	=> trim($this->input->post('category')),
				'name' 			=> trim($this->input->post('theme_name')),
				'text_boxes'	=> json_encode($text_boxes),
				'font_family' 	=> trim($this->input->post('font_family')),
				'font_size' 	=> trim($this->input->post('font_size')),
				'font_color' 	=> trim($this->input->post('font_color')),
				'font_weight' 	=> trim($this->input->post('font_weight')),
				'status' 		=> trim($this->input->post('status')),
				'sort_order'	=> trim($this->input->post('sort_order'))
			];

			$country_code = empty($this->input->post('country_code')) ? 'all' : implode(',', $this->input->post('country_code'));

			if (!empty($image_name)) {
				for ($i = 0; $i < count($image_name); $i++) {
					$save['image'] = $image_cat . '/' . $image_name[$i];

					if (!$id) {
						$theme_id = $this->theme_model->add($save);

						CI_Events::trigger('system_access_log', [
							'method'	=> 'theme_add_' . (int)$theme_id,
						]);
					} else {
						$this->theme_model->edit($id, $save);

						CI_Events::trigger('system_access_log', [
							'method'	=> 'theme_edit_' . (int)$id,
						]);
					}

					$theme_id 			= ($id >0) ? $id : $theme_id;
					$theme_locale_info 	= $this->theme_locale_model->get_all(['theme_id'=> $theme_id])['rows'] ?? [];

					if (!empty($theme_locale_info)) {
						$this->theme_locale_model->edit($theme_locale_info[0]['id'], ['country_code'=> $country_code]);
					} else {
						$this->theme_locale_model->add(
							[
								'theme_id' 		=> $theme_id,
								'country_code' 	=> $country_code
							]);
					}
				}
			}

			$this->session->set_flashdata('success_message', 'Data saved successfully');
			redirect('/admin/themes/', 'refresh');
		} else {
			$this->data['error'] = validation_errors();

			$this->session->set_flashdata('error_message', validation_errors());
			redirect(base_url('admin/add_bulk_theme/' . $id), 'refresh');
		}
	}
}
