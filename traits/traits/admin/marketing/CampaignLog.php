<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait CampaignLog {
	public function campaign_logs($campaign_id = 0) {
		$data['page_name'] 		= 'marketing/campaign_log';
		$data['page_title'] 	= _l('campaign_log');
		$data['action_ajax'] 	= site_url('admin/ajax_campaign/' . $campaign_id);
		$data['event_types']	= self::_getEventType($campaign_id);
		$data['campaign_id']	= $campaign_id;

		$this->load->view('backend/index', $data);
	}

	public function ajax_campaign($campaign_id = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		if ($campaign_id) {
			$filter_data['campaign_id'] = $campaign_id;
		}

		$results = $this->campaign_log_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		$marketing_info = $this->marketing_model->get($campaign_id);

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'email'					=> $result['email'] ?? '',
				'campaign_name'			=> $marketing_info['name'],
				'event_type'			=> $result['event_type'],
				'bounce_type'			=> $result['bounce_sub_type'] ?? '--',
				'timestamp'				=> $result['timestamp'],
			];
		}

		output_json($json);
	}

	private function _getEventType($campaign_id = 0) {
		$campaign_info 	= $this->campaign_log_model->get_all(['campaign_id' => $campaign_id]);
		$marketing_info = $this->marketing_model->get($campaign_id);

		$event_counts = [
			'send'	  	=> $marketing_info['sent_users'] ?? 0,
			'delivery'  => 0,
			'open'	  	=> 0,
			'click'	 	=> 0,
			'bounce'	=> 0
		];

		if (!empty($campaign_info['rows'])) {
			foreach ($campaign_info['rows'] as $data) {
				$event_type = strtolower($data['event_type']);

				if (!array_key_exists($event_type, $event_counts)) {
					$event_counts[$event_type] = 0;
				}

				$event_counts[$event_type]++;
			}
		}

		return $event_counts;
	}

	public function download_campaign($event_type = NULL, $campaign_id = 0) {
		if ($event_type == NULL || $campaign_id == 0) return false;

		$campaign_info = $this->campaign_log_model->get_all([
			'event_type' 	=> $event_type,
			'campaign_id' 	=> $campaign_id
		]);

		if (empty($campaign_info['rows'])) return false;

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="campaign_' . strtolower($event_type) . '_' . $campaign_id . '.csv"');
		header('Pragma: no-cache');
		header('Expires: 0');

		$output = fopen('php://output', 'w');

		fputcsv($output, ['ID', 'Email', 'Campaign ID', 'Event Type', 'Bounce Sub Type', 'Timestamp']);

		foreach ($campaign_info['rows'] as $row) {
			fputcsv($output, [
				isset($row['id']) ? $row['id'] : 'N/A',
				isset($row['email']) ? $row['email'] : 'N/A',
				isset($row['campaign_id']) ? $row['campaign_id'] : 'N/A',
				isset($row['event_type']) ? $row['event_type'] : 'N/A',
				isset($row['bounce_sub_type']) ? $row['bounce_sub_type'] : 'N/A',
				isset($row['timestamp']) ? $row['timestamp'] : 'N/A',
			]);
		}

		fclose($output);

		exit();
	}
}
