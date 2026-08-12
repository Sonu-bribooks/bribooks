<?php defined('BASEPATH') or exit('No direct script access allowed');

trait AuthorWall {
	private $_author_wall_filters = [];

	private function _initAuthorWallFilters(&$data = []) {
		$data['filters'][] 		= [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
				'label' => $event_name ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$data['filters'][]		= [
			'type'		=> 'select',
			'key'		=> 'is_jury',
			'label'		=> _l('select_is_jury'),
			'required'	=> false,
			'value'		=> 0,
			'options'	=> [
				[
					'value' => 0,
					'label' => _l('no'),
				],
				[
					'value' => 1,
					'label' => _l('yes'),
				],
			],
		];

		$this->_author_wall_filters = $data['filters'];
	}

	public function author_wall($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'event_id',
			'event',
			'site_id',
			'school',
			'name',
			'book_rank',
			'type',
			'status',
			'date_modified',
			'actions',
		];

		if ($param1 == 'edit') {
			$data = $this->input->post();

			$this->author_wall_model->edit($param2, $data);
			redirect(base_url('admin/author_wall'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->author_wall_model->delete($param2);
			redirect(base_url('admin/author_wall'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('author_wall');
		$data['action_add'] 	= '';
		$data['action_ajax'] 	= base_url('admin/ajax_author_wall');
		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/author_wall_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/author_wall/delete/',
			],
		];

		self::_initAuthorWallFilters($data);

		$this->load->view('backend/index', $data);
	}

	public function author_wall_form($param1 = null, $param2 = null) {
		if ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('author_wall');
			$data['action'] 						= base_url('admin/author_wall/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->author_wall_model->get($param2);
		}

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'book_name',
			'label'		=> _l('book_name'),
			'required'	=> true,
			'value'		=> $info['book_name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'author_name',
			'label'		=> _l('author_name'),
			'required'	=> true,
			'value'		=> $info['author_name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'textarea',
			'key'		=> 'about_the_book',
			'label'		=> _l('about_the_book'),
			'required'	=> true,
			'value'		=> $info['about_the_book'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'cover_image',
			'label'		=> _l('cover_image'),
			'required'	=> false,
			'value'		=> $info['cover_image'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'author_image',
			'label'		=> _l('author_image'),
			'required'	=> false,
			'value'		=> $info['author_image'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_author_wall() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$json = self::_format_author_wall($filter_data);

		output_json($json);
	}

	private function _format_author_wall($filter_data = []) {
		$temp_data = [];
		self::_initAuthorWallFilters($temp_data);

		foreach ($this->_author_wall_filters as $key => $item) {
			if ($this->input->get($item['key'])) {
				$filter_data[$item['key']] = is_numeric($this->input->get($item['key']))
					? (int)$this->input->get($item['key'])
					: $this->input->get($item['key']);
			}
		}

		$this->load->library('S3_lib', 's3_lib');
		$this->s3_lib->setBucket('bbprivateimagesin');

		$dir_name = (ENVIRONMENT === 'production' ? 'aadhaar_images' : 'aadhaar_images/test');

		$results = $this->author_wall_model->get_all($filter_data);

		$data['data'] 				= [];
		$data['recordsTotal'] 		= $results['total'];
		$data['recordsFiltered'] 	= $results['total'];

		$br_tag = '<br>';

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info = $this->event_model->get($result['event_id'] ?? 0);
			$site_info = $this->site_model->get($result['site_id'] ?? 0);
			$user_info = $this->user_model->get($result['user_id'] ?? 0);

			$name = vsprintf(_l("%s {$br_tag}%s {$br_tag}%s {$br_tag}%s{$br_tag}") . ($export ? '%s-%s' : '<span class="badge badge-%s">%s</span>'), [
				$result['book_name'],
				$result['author_name'],
				$user_info['email'],
				$user_info['mobile'],
				$result['is_jury'] ? 'warning' : 'success',
				$result['is_jury'] ? _l('jury') : _l('best_seller'),
			]);

			$data['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event_id'				=> $result['event_id'],
				'event'					=> $event_info['name'] ?? '',
				'site_id'				=> $result['site_id'],
				'school'				=> $site_info['name'] ?? '',
				'name'					=> $name,
				'book_rank'				=> $result['book_rank'],
				'type'					=> $result['type'],
				'status'				=> _sd($result['status']),
				'date_modified'			=> $result['date_modified'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		return $data;
	}
}
