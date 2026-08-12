<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait MedallionFeedback {
	public function medallion_feedback($param1 = NULL, $param2 = NULL) {
		$data['fields'] = [
			'sn',
			'id',
			'event',
			'medallion',
			'user',
			'order',
			'type',
			'file',
			'date_added',
			'actions',
		];

		if ($param1 == 'delete') {
			$this->medallion_feedback_model->delete($param2);
			redirect(base_url('admin/medallion_feedback'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('medallion_feedback');
		$data['action_ajax'] 	= base_url('admin/ajax_medallion_feedback');

		$data['actions'] 		= [
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/medallion_feedback/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_medallion_feedback() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if ($this->input->get('event_id')) {
			$filter_data['event_id'] = (int)$this->input->get('event_id');
		}

		$results = $this->medallion_feedback_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info 	= $this->event_model->get($result['event_id']);
			$medallion_info = $this->medallion_model->get($result['medallion_id']);
			$order_info 	= $this->medallion_order_model->get($result['order_id']);
			$product 		= array_reduce($this->medallion_order_model->getProducts($result['order_id']), fn($acc = '', $item = null) => $acc . $item['name'] . '<br>');
			$user_info 		= $this->user_model->get($result['user_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event'					=> sprintf('%s(%s)', $event_info['name'], $event_info['id']),
				'medallion'				=> $product,
				'user'					=> sprintf('%s %s(%s) <br>%s<br>%s<br>%s::%s<br>%s::%s%s', $user_info['first_name'], $user_info['last_name'], $user_info['id'], $user_info['mobile'], $user_info['email'], _l('school'), $user_info['site_id'], _l('grade'), $user_info['grade'], $user_info['section']),
				'order'					=> sprintf('%s(%s)', $order_info['order_code'], $order_info['id']),
				'type'					=> $result['type'],
				'file'					=> sprintf('<a href="%s%s%s" target="_blank">%s</a>', $this->config->item('cloudfront_url'), rtrim($this->config->item('s3_medallion_feedback') . (ENVIRONMENT === 'production' ? '' : 'test'), '/') . '/', $result['file'], $result['file']),
				'date_added'			=> format_date($result['date_added']),
				'actions'				=> ['id' => $result['id']],
			];
		}

		output_json($json);
	}
}
