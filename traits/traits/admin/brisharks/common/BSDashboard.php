<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BSDashboard {
    public function bs_get_dashboard_count($event_id = 0, $financial_year = '') {
		$event_id = (!empty($event_id) && $event_id != 'all') ? $event_id : 0;

		$data['page_name'] 		= 'brisharks/dashboard/index';
		$data['page_title'] 	= _l('bs_event_dashboard');

		$filter_data = [
			'status' 	=> 1,
			'order' 	=> 'DESC'
		];

		if (!empty($financial_year)) {
			$filter_data['end_date_ge'] 	=  ($financial_year - 1) . '-04-01 00:00:00';
			$filter_data['start_date_le'] 	= ($financial_year) . '-03-31 23:59:59';
		}

		$events 				= $this->bs_event_model->get_all($filter_data)['rows'] ?? [];
        
		$data['events'] 		= $events;
		$data['event_years'] 	= self::_getBSEventYears([
			'status' => 1
		]);
		$data['event_id'] 		= (int)$event_id;
		$data['financial_year'] = $financial_year;
		$data['action_filter'] 	= base_url('admin/bs_get_dashboard_count');
		// $data['school_url'] 	= base_url('admin/sites');
		// $data['order_url'] 		= base_url('admin/all_orders?site_code');
        // print_r($data);exit;
		$this->load->view('backend/index', $data);
	}

    public function ajax_bs_get_dashboard_count($event_id = 0) {
		$cache_key = 'bs_get_dashboard_count' . (int)$event_id;

		$data = json_decode($this->cache->get($cache_key), true);

		if (!empty($data)) {
			output_json(['data' => $data]);
			return;
		}

		$this->load->model('brisharks/common/BSStats_model', 'bs_stats_model');
		$this->load->model('brisharks/event/BSEventStats_model', 'bs_event_stats_model');

		$event_id 	= (!empty($event_id) && $event_id != 'all') ? $event_id : 0;
		$site_code 	= '';
		$event_info = $this->bs_event_model->get($event_id);

		$data['timestamp_start'] 	= time();
		$data['timestamp_end']		= time();
		$today_date 				= date('Y-m-d');

		$today_filter_data['event_id'] 		= $filter_data['event_id'] = $event_info['id'] ?? 0;
		$today_filter_data['date_added']	= date('Y-m-d');

		if (!empty($this->input->get('date_range'))) {
			$explode = explode('-', $this->input->get('date_range'));

			$filter_data['start_date'] 	= date('Y-m-d 00:00:00', strtotime(trim($explode[0])));
			$filter_data['end_date'] 	= date('Y-m-d 23:59:59', strtotime(trim($explode[1])));
		}

		$model = !empty($event_info) ? 'bs_event_stats_model' : 'bs_stats_model';

		$total_registered_school		= $this->{$model}->registered_school($filter_data);
        $total_user_from_school         = $this->{$model}->registered_user_from_school($filter_data);
        $enrolled_user_from_school      = $this->{$model}->registered_user_from_school(array_merge($filter_data, ['paid_user' => 1]));
        $registered_user_from_school    = $total_user_from_school - $enrolled_user_from_school;
		$data['stats']['schools'] = [
			[
				'label'			=> _l('registered_school'),
				'key'			=> 'registered_school',
				'icon'			=> 'dripicons-bookmark',
				'total' 		=> $total_registered_school,
				'today'			=> $this->{$model}->registered_school($today_filter_data),
				'url'			=> '',
			],
            [
				'label'			=> _l('registered_user_from_school'),
				'key'			=> 'registered_user_from_school',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $registered_user_from_school,
				'today' 		=>  $this->{$model}->registered_user_from_school($today_filter_data),
				'url'			=> '',
			],
            [
				'label'			=> _l('enrolled_user_from_school'),
				'key'			=> 'enrolled_user_from_school',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $enrolled_user_from_school,
				'today' 		=> $this->{$model}->registered_user_from_school(array_merge($today_filter_data, ['paid_user' => 1])),
				'url'			=> '',
			],

		];

        $previous_total_user_registered     = $this->{$model}->old_user_enrollemt(array_merge($filter_data, ['is_old' => 1]));
        $previous_total_user_enrolled       = $this->{$model}->old_user_enrollemt(array_merge($filter_data, ['is_old' => 1, 'paid_user' => 1]));
        $previous_total_user_registered     = $previous_total_user_registered - $previous_total_user_enrolled;
		$data['stats']['previous_users'] = [
			[
				'label'			=> _l('previous_registered_users'),
				'key'			=> 'previous_registered_users',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $previous_total_user_registered,
				'today' 		=> $this->{$model}->old_user_enrollemt(array_merge($today_filter_data, ['is_old' => 1])),
				'url'			=> '',
			],
			[
				'label'			=> _l('previous_enrolled_users'),
				'key'			=> 'previous_enrolled_users',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $previous_total_user_enrolled,
				'today' 		=> $this->{$model}->old_user_enrollemt(array_merge($today_filter_data, ['is_old' => 1, 'paid_user' => 1])),
				'url'			=> '',
			],
		];

        $new_total_user_registered     = $this->{$model}->new_user_enrollemt(array_merge($filter_data, ['is_new' => 1]));
        $new_total_user_enrolled       = $this->{$model}->new_user_enrollemt(array_merge($filter_data, ['is_new' => 1, 'paid_user' => 1]));
       // $new_total_user_registered     = $new_total_user_registered - $new_total_user_enrolled;
		

		$data['stats']['new_users'] = [
			[
				'label'			=> _l('new_registered_users'),
				'key'			=> 'new_registered_users',
				'icon'			=> 'dripicons-bookmark',
				'total' 		=> $new_total_user_registered,
				'today'			=> $this->{$model}->new_user_enrollemt(array_merge($today_filter_data, ['is_new' => 1])),
				'url'			=> '',
			],
			[
				'label'			=> _l('new_enrolled_users'),
				'key'			=> 'new_enrolled_users',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $new_total_user_enrolled,
				'today' 		=> $this->{$model}->new_user_enrollemt(array_merge($today_filter_data, ['is_new' => 1, 'paid_user' => 1])),
				'url'			=> '',
			],
			[
				'label'			=> _l('total_enrolled_users'),
				'key'			=> 'total_enrolled_users',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> $previous_total_user_enrolled + $new_total_user_enrolled,
				'today'			=>  $this->{$model}->old_user_enrollemt(array_merge($today_filter_data, ['is_old' => 1, 'paid_user' => 1])) + $this->{$model}->new_user_enrollemt(array_merge($today_filter_data, ['is_new' => 1, 'paid_user' => 1])),
				'url'			=> '',
			]
		];

		$revenue_multiplier = 1;

		$data['stats']['revenue'] = [
			[
				'label'			=> _l('total_revenue'),
				'key'			=> 'total_revenue',
				'icon'			=> 'dripicons-bookmark',
				'total'			=> currency(($this->{$model}->subscription_revenue($filter_data) * $revenue_multiplier), 0, 'INR'),
				'today' 		=> currency(($this->{$model}->subscription_revenue($today_filter_data) * $revenue_multiplier), 0, 'INR'),
				'url'			=> '',
			],
		];

		$this->cache->save($cache_key, json_encode($data), $this->_cache_ttl / 2);

		output_json(['data' => $data]);
	}

    private function _getBSEventYears($filter_data = []) {
		if (empty($events = $this->bs_event_model->get_all($filter_data)['rows'] ?? [])) return [];
        
		$end_dates = array_column($events, 'end_date');

		if (empty($end_dates)) return [];

		$dates_by_year = array_reduce($end_dates, function($carry, $date) {
			$year 		= date('Y', strtotime($date));
			$carry[] 	= $year;

			return $carry;
		}, []);

		if (empty($dates_by_year)) return [];

		$unique_year = array_unique($dates_by_year);

		rsort($unique_year);

		return $unique_year;
	}

    public function ajax_bs_get_events() {
        $filter_data = [
            'sort'			=> 'event.id',
            'order'			=> 'ASC',
        ];

        if(!empty( $this->input->post('event_id'))){
            $filter_data['event_id'] = (int)$this->input->post('event_id');
        }

        if (!empty($this->input->post('start_date_le'))) {
            $filter_data['start_date_le'] = $this->input->post('start_date_le');
        }

        if (!empty($this->input->post('end_date_ge'))) {
            $filter_data['end_date_ge'] = $this->input->post('end_date_ge');
        }

        if (!empty($this->input->post('order'))) {
            $filter_data['order'] = $this->input->post('order');
        }

        $this->json['events'] = array_map(function ($item) {
            return [
                'id'	=> $item['id'],
                'name'	=> $item['name'],
            ];
        }, $this->bs_event_model->get_all($filter_data)['rows'] ?? []);

		output_json($this->json);
	}
}