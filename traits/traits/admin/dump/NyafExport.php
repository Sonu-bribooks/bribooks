<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait NyafExport {
	public function export_top_200_csv() {
		$this->load->model('ranking/Ranking_model', 'ranking_model');

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = 0;
		$filter_data['limit'] = 200;
		$filter_data['quantity_ge'] = 50;
		$filter_data['end_date'] = '2023-03-15 21:00:00';
		$results = $this->ranking_model->getRanks($filter_data)['rows'] ?? [];

		$rows = [];

		foreach ($results as $key => $item) {
			$page_info = $this->page_version_model->get_all([
				'book_id'	=> $item['id'],
				'start'		=> 0,
				'limit'		=> 1,
				'sort'		=> 'page_version.sort_order',
				'order'		=> 'ASC',
			])['rows'][0] ?? [];

			$texts = json_decode($page_info['texts']);
			$book_desc = substr(strip_tags(html_entity_decode($texts[0])), 0, 250);

			$rows[] = [
				'book_name'		=> $item['name'],
				'author_name'	=> $item['author_name'],
				'author_bio'	=> $item['author_bio'],
				'book_desc'		=> $item['author_bio'],
			];
		}

		self::_downloadCsv($rows, 'top_200_');
	}
}
