<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BroadcastPartnerSlot {
	public function getBroadcastPartners() {
		if (!$this->json) {
			if (!empty($partner_info = $this->broadcast_partner_model->get_all())) {
				$results = array_map(function($partner) {
					return [
						'id'		=> $partner['id'],
						'name'		=> $partner['name']
					];
				}, $partner_info);

				$this->json['broadcast_partner'] = $results;
			} else {
				$this->json['broadcast_partner'] = [];
			}
		}
	}

	public function getBroadcastPartnerSlots() {
		$this->form_validation->set_rules('partner_id', _l('partner_id'), [
			'trim',
			'numeric'
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$filter_data = [
				'sort' 	=> 'start_date',
				'order' => 'DESC'
			];

			if ($this->input->post('partner_id')) {
				$filter_data['partner_id'] = $this->input->post('partner_id');
			}

			if (!empty($results = $this->broadcast_partner_slot_model->get_all($filter_data)['rows'] ?? [])) {
				$slots = [];

				$this->load->model('ranking/RankingCountry_model', 'ranking_country_model');

				foreach ($results as $item) {
					$rank_info = $this->ranking_country_model->get_all([
						'book_id'	=> $item['book_id'],
						'event_id'	=> $item['event_id'],
					])['rows'][0] ?? [];

					$book_info = $this->book_model->get($item['book_id']);

					$slots[date('Y-m-d h:i:s', strtotime($item['start_date']))][] = [
						'id'			=> $item['id'],
						'partner_id'	=> $item['partner_id'],
						'event_id'		=> $item['event_id'],
						'book_id'		=> $item['book_id'],
						'book_name'		=> $rank_info['book_name'] ?? ($book_info['name'] ?? ''),
						'book_slug'		=> $rank_info['book_slug'] ?? ($book_info['slug'] ?? ''),
						'book_image'	=> $rank_info['book_image'] ?? ($book_info['cover_image'] ?? ''),
						'author_name'	=> $rank_info['author_name'] ?? ($book_info['author_name'] ?? ''),
						'author_image'	=> $rank_info['author_image'] ?? ($book_info['author_image'] ?? ''),
						'rank'			=> $item['rank'],
						'is_jury'		=> $item['is_jury'] ?? 0,
						'start_date'	=> $item['start_date'],
						'end_date'		=> $item['end_date'],
					];
				}

				foreach ($slots as $key => $item) {
					$start_slots 	= array_column($item, 'start_date');
					$end_slots 		= array_column($item, 'end_date');
					$ranks 			= array_column($item, 'rank');

					$this->json['slots'][] = [
						'date'			=> $key,
						'start_time'	=> min($start_slots),
						'end_time'		=> max($end_slots),
						'start_rank'	=> min($ranks),
						'end_rank'		=> max($ranks),
						'slots'			=> $item,
					];
				}
			} else {
				$this->json['slots'] = [];
			}
		}
	}
}
