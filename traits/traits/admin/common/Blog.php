<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Blog {
	public function blog($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'image',
			'genre',
			'category',
			'author',
			'url',
			'status',
			'date_added',
			'date_modified',
			'actions',
		];

		if ($param1 == 'add') {
			$data 					= $this->input->post();
			$data['description'] 	= $this->input->post('description', FALSE);

			$this->blog_model->add($data);
			redirect(base_url('admin/blog'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data 					= $this->input->post();
			$data['description'] 	= $this->input->post('description', FALSE);

			$this->blog_model->edit($param2, $data);
			redirect(base_url('admin/blog'), 'refresh');
		} elseif ($param1 == 'status') {
			$this->blog_model->enableDisable($param2);
			redirect(base_url('admin/blog'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->blog_model->delete($param2);
			redirect(base_url('admin/blog'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('blog');
		$data['action_add'] 	= base_url('admin/blog_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_blog');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/blog_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/blog/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/blog/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function blog_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_blog');
			$data['action'] 						= base_url('admin/blog/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('blog');
			$data['action'] 						= base_url('admin/blog/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->blog_model->get($param2);
			$genre_info 							= $this->genre_model->get($info['genre_id']);
			$genre_name								= ($info['genre_id'] == 0) ? 'All' : $genre_info['name'];
			$category_info 							= $this->category_model->get($info['category_id']);
			$category_name							= ($info['category_id'] == 0) ? 'All' : $category_info['name'];
		}

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'genre_id',
			'label'		=> _l('select_genre'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['genre_id'] ?? '',
				'label' => $genre_name ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_genres'),
			'ajax_options'=> base_url('admin/ajax_search_categories?target=category_id&input=select2&includes=genre_id'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'category_id',
			'label'		=> _l('select_category'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['category_id'] ?? '',
				'label' => $category_name ?? '',
			]
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'image',
			'label'		=> _l('image'),
			'required'	=> true,
			'value'		=> $info['image'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'description',
			'label'		=> _l('description'),
			'required'	=> true,
			'value'		=> $info['description'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'meta_description',
			'label'		=> _l('meta_description'),
			'required'	=> true,
			'value'		=> $info['meta_description'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'		=> $info['status'] ?? 1,
			'options'	=> [
				[
					'value' => 1,
					'label' => _l('enabled'),
				],
				[
					'value' => 0,
					'label' => _l('disabled'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'multi_select2',
			'key'		=> 'related',
			'label'		=> _l('select_related'),
			'required'	=> true,
			'value'		=> !empty($info['related']) ? array_map(function($item) {
				$blog_info = $this->blog_model->get($item);
				return [
					'value' => $item,
					'label' => $blog_info['name'],
				];
			}, explode(',', $info['related'])) : '',
			'ajax_url'	=> base_url('admin/ajax_search_blogs'),
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_blog() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->blog_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$genre_info 	= $this->genre_model->get($result['genre_id']);
			$category_info 	= $this->category_model->get($result['category_id']);
			$user_info 		= $this->user_model->get($result['user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'image'					=> $result['image'] ? sprintf('<img src="%s" class="img-thumbnail"/>', $this->config->item('cloudfront_url') . $this->config->item('s3_user_gallery') . $result['image'] ?? '') : '',
				'genre'					=> $genre_info['name'],
				'category'				=> $category_info['name'],
				'author'				=> $user_info['first_name'] ?? '',
				'url'					=> !empty($result['slug']) ? sprintf('<a href="%s" target="_blank">%s</a>', USER_URL . 'blog/' . $result['slug'], _l('visit')) : '',
				'status'				=> _sd($result['status']),
				'date_added'			=> formatDate($result['date_added']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function ajax_search_blogs() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->blog_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s)', $result['name'], $result['id']),
			];
		}

		output_json($json);
	}
}
