<?php defined('BASEPATH') or exit('No direct script access allowed');

trait BSLocalisation {
	public function ajax_search_bs_country() {
		$this->load->model('brisharks/localisation/BSCountry_model', 'bs_country_model');

		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->bs_country_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}

	public function ajax_search_bs_state() {
		$this->load->model('brisharks/localisation/BSState_model', 'bs_state_model');

		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->bs_state_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s)', $result['name'], $result['country']),
			];
		}

		output_json($json);
	}

	public function ajax_search_bs_city() {
		$this->load->model('brisharks/localisation/BSCity_model', 'bs_city_model');

		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->bs_city_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> sprintf('%s (%s, %s)', $result['name'], $result['state'], $result['country']),
			];
		}

		output_json($json);
	}
}
