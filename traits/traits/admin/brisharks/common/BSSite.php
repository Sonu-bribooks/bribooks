<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BSSite {
	public function ajax_search_bs_site() {
		$this->load->model('brisharks/school/BSSite_model', 'bs_site_model');

		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->bs_site_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
