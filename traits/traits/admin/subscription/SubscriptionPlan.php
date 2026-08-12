<?php defined('BASEPATH') or exit('No direct script access allowed');

trait SubscriptionPlan {
	public function subscription_plan($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'region',
			'name',
			'price',
			'currency_code',
			'country_code',
			'duration_month',
			'hard_copy',
			'shipping_credit',
			'status',
			'actions',
		];

		if ($param1 == 'add') {
			$data = $this->input->post();

			$data['description'] 	= $this->input->post('description', FALSE);
			$data['highlight'] 		= $this->input->post('highlight', FALSE);

			$currency_info = $this->currency_model->get($data['currency_id']);

			if (!empty($currency_info)) {
				$data['currency_code'] 		= $currency_info['code'] ?? '';
				$data['currency_symbol'] 	= $currency_info['symbol'] ?? '';
			}

			$country_info = $this->country_model->get($data['country_id']);

			if (!empty($country_info)) {
				$data['country_code'] 		= $country_info['code'] ?? '';
			}

			$data['description'] 	= _allowSpecificHtmlTags($data['description']);
			$data['highlight'] 		= _allowSpecificHtmlTags($data['highlight']);

			if (is_array($data['benefits'])) {
				$data['benefits'] = json_encode($data['benefits']);
			}

			$this->subscription_plan_model->add($data);

			redirect(base_url('admin/subscription_plan'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data = $this->input->post();

			$data['description'] 	= $this->input->post('description', FALSE);
			$data['highlight'] 		= $this->input->post('highlight', FALSE);

			$currency_info = $this->currency_model->get($data['currency_id']);

			if (!empty($currency_info)) {
				$data['currency_code'] 		= $currency_info['code'] ?? '';
				$data['currency_symbol'] 	= $currency_info['symbol'] ?? '';
			}

			$country_info = $this->country_model->get($data['country_id']);

			if (!empty($country_info)) {
				$data['country_code'] 		= $country_info['code'] ?? '';
			}

			$data['description'] 	= _allowSpecificHtmlTags($data['description']);
			$data['highlight'] 		= _allowSpecificHtmlTags($data['highlight']);

			if (is_array($data['benefits'])) {
				$data['benefits'] = json_encode($data['benefits']);
			}

			$this->subscription_plan_model->edit($param2, $data);

			redirect(base_url('admin/subscription_plan'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->subscription_plan_model->delete($param2);
			
			redirect(base_url('admin/subscription_plan'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('subscription_plan');
		$data['action_add'] 	= base_url('admin/subscription_plan_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_subscription_plan');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/subscription_plan_form/edit/',
			],
			[
				'key'	=> 'delete',
				'type'	=> 'confirm',
				'url'	=> 'admin/subscription_plan/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function subscription_plan_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_subscription_plan');
			$data['action'] 						= base_url('admin/subscription_plan/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('subscription_plan');
			$data['action'] 						= base_url('admin/subscription_plan/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->subscription_plan_model->get($param2);
			$currency_info 							= $this->currency_model->get($info['currency_id']);
			$country_info 							= $this->country_model->get($info['country_id']);

			if (!empty($info['benefits'])) {
				$info['benefits'] = json_decode($info['benefits'], true);
			}
		}

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'site_id',
			'label'		=> _l('select_region'),
			'required'	=> true,
			'value'		=> $info['site_id'] ?? 0,
			'options'	=> array_map(function ($item) {
				return [
					'label'	=> $item['name'],
					'value'	=> $item['id'],
				];
			}, $this->site_model->get_all(['site_type' => 7, 'order' => 'ASC'])['rows'] ?? []),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'name',
			'label'		=> _l('name'),
			'required'	=> true,
			'value'		=> $info['name'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'price',
			'label'		=> _l('price'),
			'required'	=> true,
			'value'		=> $info['price'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'regular_price',
			'label'		=> _l('regular_price'),
			'required'	=> false,
			'value'		=> $info['regular_price'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'currency_id',
			'label'		=> _l('select_currency'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['currency_id'] ?? '',
				'label' => !empty($currency_info['name']) ? sprintf('%s (%s, %s)', $currency_info['name'], $currency_info['symbol'], $currency_info['code']) : '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_currency'),
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'country_id',
			'label'		=> _l('select_country'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['country_id'] ?? '',
				'label' => $country_info['name'] ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_country'),
		];

		$data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'tag',
			'label'		=> _l('tag'),
			'required'	=> false,
			'options'	=> $info['tag'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'featured',
			'label'		=> _l('select_featured'),
			'required'	=> false,
			'value'		=> $info['featured'] ?? 0,
			'options'	=> [
				[
					'value' => 1,
					'label' => _l('yes'),
				],
				[
					'value' => 0,
					'label' => _l('no'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'description',
			'label'		=> _l('description'),
			'required'	=> true,
			'value'		=> $info['description'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'html',
			'key'		=> 'highlight',
			'label'		=> _l('highlight'),
			'required'	=> false,
			'value'		=> $info['highlight'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'duration_month',
			'label'		=> _l('duration_month'),
			'required'	=> true,
			'value'		=> $info['duration_month'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'hard_copy',
			'label'		=> _l('hard_copy'),
			'required'	=> true,
			'value'		=> $info['hard_copy'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'shipping_credit',
			'label'		=> _l('shipping_credit'),
			'required'	=> true,
			'value'		=> $info['shipping_credit'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'sort_order',
			'label'		=> _l('sort_order'),
			'required'	=> true,
			'value'		=> $info['sort_order'] ?? 0,
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'is_upgradable',
			'label'		=> _l('is_upgradable'),
			'required'	=> false,
			'value'		=> $info['is_upgradable'] ?? 0,
			'options'	=> [
				[
					'value' => 1,
					'label' => _l('yes'),
				],
				[
					'value' => 0,
					'label' => _l('no'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'can_be_published',
			'label'		=> _l('can_be_published'),
			'required'	=> false,
			'value'		=> $info['can_be_published'] ?? 0,
			'options'	=> [
				[
					'value' => 1,
					'label' => _l('yes'),
				],
				[
					'value' => 0,
					'label' => _l('no'),
				],
			],
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
					'label' => _l('enable'),
				],
				[
					'value' => 0,
					'label' => _l('disable'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'group',
			'key'		=> 'benefits',
			'label'		=> _l('benefits'),
			'required'	=> false,
			'fields'	=> self::_renderSubscriptionMultiFields($info, 'benefits'),
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_subscription_plan() {
		$json['data'] 	= [];
		$columns 		= $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->subscription_plan_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$site_info = $this->site_model->get($result['site_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'region'				=> $site_info['name'] ?? '',
				'name'					=> $result['name'] ?? '',
				'price'					=> $result['price'] ?? '',
				'currency_code'			=> $result['currency_code'] ?? '',
				'country_code'			=> $result['country_code'] ?? '',
				'duration_month'		=> $result['duration_month'] ?? 0,
				'hard_copy'				=> $result['hard_copy'] ?? 0,
				'shipping_credit'		=> $result['shipping_credit'] ?? 0,
				'status'				=> _sd($result['status']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _renderSubscriptionMultiFields($info = [], $type = '') {
		$fields 	= [];

		$items 		= $info[$type] ?? [''];

		$index = 0;

		foreach ($items as $item) {
			$fields[] = [
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][benefit_heading]', $type, $index),
					'label'		=> _l(sprintf('%s_heading', $type)),
					'required'	=> false,
					'value'		=> $info[$type][$index]['benefit_heading'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][benefit_sub_heading]', $type, $index),
					'label'		=> _l(sprintf('%s_sub_heading', $type)),
					'required'	=> false,
					'value'		=> $info[$type][$index]['benefit_sub_heading'] ?? '',
				],
				[
					'type'		=> 'text',
					'key'		=> sprintf('%s[%d][benefit_btn_text]', $type, $index),
					'label'		=> _l(sprintf('%s_btn_text', $type)),
					'required'	=> false,
					'value'		=> $info[$type][$index]['benefit_btn_text'] ?? '',
				],
				[
					'type'		=> 'image',
					'key'		=> sprintf('%s[%d][icon]', $type, $index),
					'label'		=> _l(sprintf('%s_icon', $type)),
					'required'	=> false,
					'value'		=> $info[$type][$index]['icon'] ?? '',
				],
				[
					'type' 		=> 'color',
					'key' 		=> sprintf('%s[%d][top_strip_bg]', $type, $index),
					'label' 	=> _l(sprintf('%s_top_strip_bg', $type)),
					'required' 	=> false,
					'value' 	=> $info[$type][$index]['top_strip_bg'] ?? '',
				],
				[
					'type' 		=> 'color',
					'key' 		=> sprintf('%s[%d][benefit_btn_bg]', $type, $index),
					'label' 	=> _l(sprintf('%s_btn_bg', $type)),
					'required' 	=> false,
					'value' 	=> $info[$type][$index]['benefit_btn_bg'] ?? '',
				],
				[
					'type'		=> 'number',
					'key'		=> sprintf('%s[%d][sort_order]', $type, $index),
					'label'		=> _l(sprintf('%s_sort_order', $type)),
					'required'	=> false,
					'value'		=> $info[$type][$index]['sort_order'] ?? '',
				],
			];

			++$index;
		}

		return $fields;
	}
}
