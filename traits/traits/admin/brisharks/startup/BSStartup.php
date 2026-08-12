<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BSStartup {
	private function _initStartupFilters(&$data = [], $type = 'user') {
		$data['filters'][] 		= [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> false,
			'value'		=> 0,
			'options'	=> [
				[
					'value' => 0,
					'label' => _l('pending'),
				],
				[
					'value' => 1,
					'label' => _l('accepted'),
				],
				[
					'value' => 2,
					'label' => _l('review'),
				],
				[
					'value' => 3,
					'label' => _l('rejected'),
				],
			],
		];

		$this->_generic_filters = $data['filters'];
	}

	public function bs_startup($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'name',
			'user',
			'slug',
			'logo',
			'founder_name',
			'founder_image',
			'school',
			'grade',
			'city',
			'problem',
			'mission',
			'opportunity',
			'advantage',
			'pitch',
			'html',
			'instagram',
			'twitter',
			'linkedin',
			'status',
			'date_added',
			'actions',
		];

		if ($param1 == 'edit') {
			$data = $this->input->post();
			$data['html'] = strpos($data['html'], $this->config->item('s3_user_gallery')) === false
				? $this->config->item('s3_user_gallery') . $data['html']
				: $data['html'];
			$this->bs_startup_model->edit($param2, $data);
			redirect(base_url('admin/bs_startup'), 'refresh');
		} elseif ($param1 == 'status') {
			$info = $this->bs_startup_model->get($param2);

			$this->bs_startup_model->edit($param2, [
				'status' => in_array($info['status'], [0,2,3,4]) ? 1 : 0
			]);

			redirect(base_url('admin/bs_startup'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->bs_startup_model->delete($param2);
			redirect(base_url('admin/bs_startup'), 'refresh');
		} elseif ($param1 == 'reject') {
			$this->load->model('brisharks/startup/BSStartupHistory_model', 'bs_startup_history_model');

			$data = $this->input->post();

			$this->bs_startup_model->edit($param2, [
				'status' => 3
			]);

			$this->bs_startup_history_model->add([
				'startup_id' 	=> $data['id'],
				'description' 	=> $data['text'],
				'status' 		=> 3,
			]);

			output_json([
				'success' => true,
				'message' => _l('startup_rejected_successfully')
			]);

			return;
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('bs_startup');
		$data['action_ajax'] 	= base_url('admin/ajax_bs_startup');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/bs_startup_form/edit/',
			],
			[
				'key'	=> 'download_prompt',
				'url'	=> 'admin/bs_startup_prompt/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/bs_startup/status/',
			],
			[
				'key'	=> 'notify',
				'type' 	=> 'confirm',
				'url'	=> 'admin/bs_startup_notify/',
			],
			[
				'key'	=> 'view',
				'title' => _l('view'),
				'url'	=> 'admin/bs_startup_details/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/bs_startup/delete/',
			],
			[
				'key'	=> 'reject',
				'type' 	=> 'comment_box',
				'title' => _l('add_comment'),
				'url'	=> 'admin/bs_startup/reject/',
			],
		];

		self::_initStartupFilters($data);

		$this->load->view('backend/index', $data);
	}

	public function bs_startup_details($id = 0) {
		$data['page_name'] 		= 'generic/view';
		$data['page_title'] 	= _l('startup_details');

		$data['id'] 			= (int)$param2;
		$info 					= $this->bs_startup_model->get($id);
		$user_info 				= $this->bs_user_model->get($info['user_id']);

		$data['view_title'] 	= sprintf(_l('startup : %s'), $info['name']);

		$data['details'] = [
			[
				'label' => _l('startup_name'),
				'value' => $info['name'],
			],
			[
				'label' => _l('user'),
				'value' => $user_info['first_name'] . ' ' . $user_info['last_name'],
			],
			[
				'label' => _l('email'),
				'value' => $user_info['email'],
			],
			[
				'label' => _l('mobile'),
				'value' => $user_info['mobile'],
			],
			[
				'label' => _l('slug'),
				'value' => $info['slug'],
			],
			[
				'label' => _l('logo'),
				'value' => sprintf('%sbrisharks/%s-files/uploads/%s',
					$this->config->item('s3_base_url'),
					ENVIRONMENT == 'production' ? 'live' : 'test',
					$info['logo']
				),
				'type'	=> 'image',
			],
			[
				'label' => _l('founder_name'),
				'value' => $info['founder_name'],
			],
			[
				'label' => _l('founder_image'),
				'value' => sprintf('%sbrisharks/%s-files/uploads/%s',
					$this->config->item('s3_base_url'),
					ENVIRONMENT == 'production' ? 'live' : 'test',
					$info['founder_image']
				),
				'type'	=> 'image',
			],
			[
				'label' => _l('school'),
				'value' => $info['school'],
			],
			[
				'label' => _l('grade'),
				'value' => $info['grade'],
			],
			[
				'label' => _l('city'),
				'value' => $info['city'],
			],
			[
				'label' => _l('problem'),
				'value' => $info['problem'],
			],
			[
				'label' => _l('mission'),
				'value' => $info['mission'],
			],
			[
				'label' => _l('opportunity'),
				'value' => $info['opportunity'],
			],
			[
				'label' => _l('advantage'),
				'value' => $info['advantage'],
			],
			[
				'label' => _l('pitch'),
				'value' => sprintf('%sbrisharks/%s-files/uploads/%s',
					$this->config->item('s3_base_url'),
					ENVIRONMENT == 'production' ? 'live' : 'test',
					$info['pitch']
				),
				'type'	=> 'video',
			],
			[
				'label' => _l('website'),
				'value' => vsprintf('https://%s.brisharks.com/founder/%s/', [
					ENVIRONMENT === 'production' ? 'www' : 'uat',
					$info['slug'],
				]),
				'type'	=> 'link',
			],
			[
				'label' => _l('instagram'),
				'value' => $info['instagram'],
				'type'	=> 'link',
			],
			[
				'label' => _l('twitter'),
				'value' => $info['twitter'],
				'type'	=> 'link',
			],
			[
				'label' => _l('linkedIn'),
				'value' => $info['linkedin'],
				'type'	=> 'link',
			],
		];

		$this->load->model('brisharks/startup/BSStartupHistory_model', 'bs_startup_history_model');

		$comments = $this->bs_startup_history_model->get_all([
			'startup_id' 	=> (int)$param2,
			'sort'			=> 'startup_history.id',
			'order'			=> 'ASC'
		])['rows'] ?? [];

		$data['comments'] = $comments;

		$this->load->view('backend/index', $data);
	}

	public function bs_startup_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('bs_startup_form_edit');
			$data['action'] 						= base_url('admin/bs_startup/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->bs_startup_model->get($param2);
		}

		$data['fields'][] = [
			'type'		=> 'file',
			'key'		=> 'html',
			'label'		=> _l('html_url'),
			'required'	=> false,
			'value'		=> $info['html'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> true,
			'value'		=> $info['status'] ?? 1,
			'options'	=> [
				[
					'label'	=> _l('enable'),
					'value'	=> 1,
				],
				[
					'label'	=> _l('disable'),
					'value'	=> 0,
				],
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_bs_startup() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if ($this->input->get('status') !== null && $this->input->get('status') !== '') {
			$filter_data['status'] = (int) $this->input->get('status');
		}

		self::_initStartupFilters();
		self::_formatFilters($filter_data);

		$results = $this->bs_startup_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		$statuses = [
			0 => [
				'name'  => _l('pending'),
				'color' => 'warning',
			],
			1 => [
				'name'  => _l('accepted'),
				'color' => 'success',
			],
			2 => [
				'name'  => _l('review'),
				'color' => 'info',
			],
			3 => [
				'name'  => _l('rejected'),
				'color' => 'danger',
			],
		];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$user_info = $this->bs_user_model->get($result['user_id']);

			$status_info = $statuses[$result['status']] ?? [
				'name'  => _l('unknown'),
				'color' => 'secondary',
			];

			$json['data'][] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],

				'name'				=> $result['name'],

				'user' 				=> sprintf(
					'%s %s<br><small>%s | %s | Grade %s</small>',
					$user_info['first_name'],
					$user_info['last_name'],
					$user_info['email'],
					$user_info['mobile'],
					$user_info['grade']
				),

				'slug'				  => $result['slug'],
				'logo'				  => $result['logo'],

				'founder_name'		=> $result['founder_name'],
				'founder_image'	  => $result['founder_image'],

				'school'				=> $result['school'],
				'grade'				 => $result['grade'],
				'city'				  => $result['city'],

				'problem'			  => $result['problem'],
				'mission'			  => $result['mission'],
				'opportunity'		 => $result['opportunity'],
				'advantage'			=> $result['advantage'],

				'pitch'				 => $result['pitch'],
				'html'				  => render_url(vsprintf('https://%s.brisharks.com/founder/%s/', [
					ENVIRONMENT === 'production' ? 'www' : 'uat',
					$result['slug'],
				]), _l('preview')),

				'instagram'			=> $result['instagram'],
				'twitter'			  => $result['twitter'],
				'linkedin'			 => $result['linkedin'],

				'status' 			=> sprintf('<span class="badge badge-%s">%s</span>',
					$status_info['color'],
					$status_info['name']
				),

				'date_added'		=> formatDate($result['date_added']),
				'actions'			=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function bs_startup_prompt($id = 0) {
		$startup_info = $this->bs_startup_model->get($id);

		$this->load->helper('download');
		$content = $startup_info['prompt'] ?? '';

		force_download(sprintf('prompt_%s.txt', $startup_info['id']), $content);
	}

	public function bs_startup_notify($id = 0) {
		$info 		= $this->bs_startup_model->get($id);
		$code 		= sprintf('startup_notify_%d', $info['user_id']);
		$cron_info 	= $this->bs_cron_model->getByCode($code);
		$user_info 	= $this->bs_user_model->get($info['user_id']);

		$country_info 	= $this->country_model->get($info['country_id']);

		$template_info 	= $this->bs_message_template_model->get_all([
			'code'			=> 'startup_notify',
			'country_code'	=> $country_info['code'] ?? 'US',
			'status'		=> 1,
			'start'			=> 0,
			'limit'			=> 1,
		])['rows'][0] ?? [];

		if (empty($template_info['id'])) {
			$template_info = $this->bs_message_template_model->get_all([
				'code'			=> 'startup_notify',
				'country_code'	=> 'us',
				'status'		=> 1,
				'start'			=> 0,
				'limit'			=> 1,
			])['rows'][0] ?? [];
		}

		if (empty($template_info['id'])) {
			error_message(_l('template_not_found'));
			redirect(base_url('admin/bs_startup'), 'refresh');
		}

		if (!empty($cron_info)) {
			$this->bs_cron_model->edit($cron_info['id'], [
				'status'	=> 0,
				'alert_date'=> date('Y-m-d H:i:s'),
				'data'		=> [
					'payload'	=> [
						'code'			=> 'startup_notify',
						'first_name'	=> $info['founder_name'],
						'user_id'		=> $info['user_id'],
						'email'			=> $user_info['email'],
						'mobile'		=> $user_info['mobile'],
						'url'			=> vsprintf('https://%s.brisharks.com/startup/%s/', [
							ENVIRONMENT === 'production' ? 'www' : 'uat',
							$info['slug'],
						]),
					],
					'template_id'		=> $template_info['id'] ?? 0,
				]
			]);
		} else {
			$this->bs_cron_model->add([
				'code'		=> $code,
				'action'	=> 'alert.message_template_cron',
				'alert_date'=> date('Y-m-d H:i:s'),
				'data'		=> [
					'payload'	=> [
						'code'			=> 'startup_notify',
						'first_name'	=> $info['founder_name'],
						'user_id'		=> $info['user_id'],
						'email'			=> $user_info['email'],
						'mobile'		=> $user_info['mobile'],
						'url'			=> vsprintf('https://%s.brisharks.com/startup/%s/', [
							ENVIRONMENT === 'production' ? 'www' : 'uat',
							$info['slug'],
						]),
					],
					'template_id'		=> $template_info['id'] ?? 0,
				]
			]);
		}

		success_message(_l('user_notified'));
		redirect(base_url('admin/bs_startup'), 'refresh');
	}
}
