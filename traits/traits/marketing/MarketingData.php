<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait MarketingData {
	private function _getRows($info = []) {
		$rows = [];

		$this->load->model('event/EventUser_model', 'event_user_model');

		if ($info['user_type'] == 'csv') {
			$this->load->library('parsecsv');
			$this->parsecsv->auto('uploads/' . $info['csv_file']);
			$rows = $this->parsecsv->data;
		} else if (strpos($info['user_type'], 'marketing_dataset') !== false) {
			$this->load->model('common/MarketingDataset_model', 'marketing_dataset_model');

			$explode = explode('_', $info['user_type']);
			$marketing_dataset_id = end($explode);

			$sql_query = $this->marketing_dataset_model->get($marketing_dataset_id) ?? [];

			if (empty($sql_query)) {
				return [];
			}

			$rdb = $this->load->database('replica', TRUE);

			$rows = $rdb->query($sql_query['sql_query'])->result_array();
		}

		if ($info['testing']) {
			log_kb([
				'user_type' 	=> $info['user_type'],
				'rows' 			=> $rows[0] ?? [],
				'qd' 			=> $this->db->last_query()
			]);
		}

		return $rows;
	}

	private function _getDataSetAttachment($info = []) {
		if (strpos($info['user_type'], 'marketing_dataset') !== false) {
			$this->load->model('common/MarketingDataset_model', 'marketing_dataset_model');

			$explode = explode('_', $info['user_type']);
			$marketing_dataset_id = end($explode);

			$sql_query = $this->marketing_dataset_model->get($marketing_dataset_id) ?? [];

			if (empty($sql_query) || empty($sql_query['attachment_query'])) {
				return [];
			}

			$query = $sql_query['attachment_query'];

			$query = str_ireplace('users.role_id,', '', $query);
			$query = str_ireplace(', users.role_id', '', $query);
			$query = str_ireplace('users.role_id', '', $query);
			$query = str_ireplace(', 2 as role_id', '', $query);
			$query = str_ireplace(', 9 as role_id', '', $query);
			$query = str_ireplace(', 3 as role_id', '', $query);
			$query = str_ireplace(',2 as role_id', '', $query);
			$query = str_ireplace(',9 as role_id', '', $query);
			$query = str_ireplace(',3 as role_id', '', $query);

			$rdb = $this->load->database('replica', TRUE);

			$rows = $rdb->query($query)->result_array();

			return _saveCsv($rows);
		}

	}
}
