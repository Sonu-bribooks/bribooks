<?php defined('BASEPATH') OR exit('No direct script access allowed');

class CampaignLog_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->select('campaign_logs_v3.*');

		$this->db->where('campaign_logs_v3.id', (int)$id);
		$this->db->where('campaign_logs_v3._deleted', 0);

		return $this->db->get('campaign_logs_v3')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('campaign_logs_v3.*');

		if (isset($data['status'])) {
			$this->db->where('campaign_logs_v3.status', (int)$data['status']);
		}

		if (isset($data['campaign_id'])) {
			$this->db->where('campaign_logs_v3.campaign_id', (int)$data['campaign_id']);
		}

		if (isset($data['event_type'])) {
			$this->db->where('campaign_logs_v3.event_type', $data['event_type']);
		}

		if (isset($data['email'])) {
			$this->db->where('campaign_logs_v3.email', $data['email']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('campaign_logs_v3.email', $data['search'], 'after');
			$this->db->or_like('campaign_logs_v3.event_type', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('campaign_logs_v3._deleted', 0);
		$this->db->from('campaign_logs_v3');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'campaign_logs_v3.id',
			'campaign_logs_v3.status',
			'campaign_logs_v3.date_added',
			'campaign_logs_v3.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'campaign_logs_v3.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('campaign_logs_v3', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified' => date('Y-m-d H:i:s'),
		]);

		return $this->db->insert_id();
	}

	public function edit($id = 0, $data = []) {
		$this->db->where('id', $id);
		$this->db->update('campaign_logs_v3', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($id = 0) {
		$this->db->where('id', $id);
		$this->db->update('campaign_logs_v3',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function saveCampaignLog($response = NULL){
		try {
			$campaign_id = null;

			foreach ($response['mail']['headers'] as $header) {
				if ($header['name'] === 'X-BBCampaign') {
					$campaign_id = $header['value'];
					break;
				}
			}

			$email = null;

			foreach ($response['mail']['headers'] as $header) {
				if ($header['name'] === 'To') {
					$email = $header['value'];
					break;
				}
			}

			$event_type 		= $response['eventType'] ?? '';
			$bounce_sub_type 	= $response['bounce']['bounceSubType'] ?? null;
			$timestamp 			= date('Y-m-d H:i:s', strtotime($response['mail']['timestamp'] ?? ''));
			$json_data 			= json_encode($response);

			if (!empty($campaign_id) && !empty(self::get_all([
				'email'			=> $email,
				'event_type'	=> $event_type,
				'campaign_id'	=> $campaign_id,
			])['rows'])) return;

			$insert_data = [
				'email'				=> $email ?? '',
				'campaign_id'	  	=> $campaign_id ?? 0,
				'event_type'	  	=> $event_type,
				'bounce_sub_type'  	=> $bounce_sub_type,
				'timestamp'			=> $timestamp,
				'json_data'			=> $json_data,
			];

			// Save to database
			$save_response = self::add($insert_data);

			log_kb([
				'saveCampaignLog' => compact('campaign_id', 'event_type', 'email')
			]);

			if (!$save_response) {
				throw new Exception('Failed to save data for email_id: ' . ($response['mail']['source'] ?? 'unknown'));
			}

			return 'Data processed and saved successfully';
		} catch (\Exception $e) {
			log_kb(['SES Webhook::error:: ' => $e->getMessage()]);
		}
	}
}
