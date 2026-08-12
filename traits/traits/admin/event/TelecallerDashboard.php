<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait TelecallerDashboard {
	public function telecaller_dashboard($event_id = 0, $financial_year = '') {
		$event_id = (!empty($event_id) && $event_id != 'all') ? $event_id : 0;

		$data['page_name'] 		= 'dashboard/telecaller';
		$data['page_title'] 	= _l('telecaller_dashboard');

		$filter_data = [
			'status' 	=> 1,
			'order' 	=> 'DESC'
		];
		$events 				= $this->event_model->get_all($filter_data)['rows'] ?? [];

		$data['events'] 		= $events;
		$data['event_id'] 		= (int)$event_id;
		$data['action_filter'] 	= base_url('admin/telecaller_dashboard');

		$this->load->view('backend/index', $data);
	}

	public function ajax_telecaller_dashboard($event_id = 0) {
		$cache_key = 'telecaller_dashboard_' . (int)$event_id;

		$data = json_decode($this->cache->get($cache_key), true);

		if (!empty($data)) {
			output_json(['data' => $data]);
			return;
		}

		$event_id 	= (!empty($event_id) && $event_id != 'all') ? $event_id : 0;
		$data 		= [];

		$results 	= $this->db
			->select('DISTINCT user_id', false)
			->get_where('telecaller_school', [
				'event_id'	=> $event_id,
			])->result_array();

		foreach ($results as $item) {
			self::_buildTelecallerData($data, $item['user_id'], $event_id);
		}

		$this->cache->save($cache_key, json_encode($data), ENVIRONMENT === 'production' ? 600 : 0);

		output_json(['data' => $data]);
	}

	private function _buildTelecallerData(&$data, $user_id = 0, $event_id = 0) {
		$user_info 	= $this->user_model->get($user_id);
		$name 		= sprintf('%s %s', $user_info['first_name'], $user_info['last_name']);

		$data['timestamp_start'] 	= time();
		$data['timestamp_end']		= time();
		$today_date 				= date('Y-m-d');

		$today_filter_data['event_id'] 		= $filter_data['event_id'] = $event_id;
		$today_filter_data['user_id'] 		= $filter_data['user_id'] = $user_id;
		$today_filter_data['date_added']	= date('Y-m-d');

		$model 		= 'telecaller_dashboard_model';

		$data['stats'][$name] = [
			[
				'label'			=> _l('registered_school'),
				'key'			=> 'registered_school',
				'icon'			=> 'dripicons-graduation',
				'total' 		=> $this->{$model}->registered_school($filter_data),
				'today'			=> $this->{$model}->registered_school($today_filter_data),
				'user_id'		=> (int)$user_id,
				'url'			=> '#',
			],
			[
				'label'			=> _l('schools_with_authors'),
				'key'			=> 'schools_with_authors',
				'icon'			=> 'dripicons-user-group',
				'total' 		=> $this->{$model}->school_with_authors($filter_data),
				'today'			=> $this->{$model}->school_with_authors($today_filter_data),
				'user_id'		=> (int)$user_id,
				'url'			=> '#',
			],
		];
	}

	public function ajax_telecaller_dashboard_details($event_id = 0) {
		$filter_data = [];

		if (!empty($event_id)) {
			$filter_data['event_id'] = (int)$event_id;
		}

		if ($this->input->get('user_id')) {
			$filter_data['user_id']	= (int)$this->input->get('user_id');
		}

		if ($this->input->get('duration') && $this->input->get('duration') == 'today') {
			$filter_data['date_added']	= date('Y-m-d');
		}

		if ($this->input->get('type')) {
			$method = strtolower($this->input->get('type'));

			if ($method == 'new_school_enrolled') {
				$method = 'verified_school';
				$filter_data['is_new'] 		= 1;
			} elseif ($method == 'old_school_enrolled') {
				$filter_data['is_old'] 		= 1;
				$filter_data['verified'] 	= 1;
			}

			$method .= '_data';
		}

		$model = 'telecaller_dashboard_model';

		$data['results'] = method_exists($this->{$model}, $method)
			? $this->{$model}->{$method}($filter_data)
			: [];

		if (!empty($data['results'])) {
			$json['view'] = $this->load->view('backend/admin/dashboard/info', $data, true);
		}

		output_json($json);
	}
}
