<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BuildRankAlert {
	public function buildRankCron($data = []) {
		$event_id		= $data['event_id'] ?? 0;
		$challenge_id	= $data['challenge_id'] ?? 0;
		$type 			= $data['type'] ?? 'country';

		if (empty($event_id)) return;

		log_kb(['buildRankCron' => [$event_id, $challenge_id, $type]]);

		$this->load->library('Ranking_lib', 'ranking_lib');

		$results = $this->db
			->select('
				event_order.event_id,
				event_order.book_id,
				MAX(event_order.order_id) as order_id,
				SUM(event_order.quantity) as sold_count
			')
			->from('event_order')
			->join('order', 'order.id=event_order.order_id')
			->join('book', 'book.id=event_order.book_id')
			->join('users', 'users.id=book.user_id')
			->where('event_order._deleted', 0)
			->where('event_order.event_id', (int)$event_id)
			->where('order._deleted', 0)
			->where_not_in('order.status', [0, 91, 92])
			->where('book._deleted', 0)
			->where('book.archived', 0)
			->where('book.status', 1)
			->where('users._deleted', 0)
			->group_by('event_order.book_id')
			->get()
			->result_array();

		log_kb(['buildRankCron' => [$event_id, $challenge_id, $type, $results]]);

		foreach ($results as $key => $item) {
			if (
				!empty($book_info = $this->book_model->get($item['book_id'])) &&
				!empty($author_info = $this->student_model->get($book_info['user_id']))
			) {
				$this->ranking_lib->updateRank($item['order_id'], $type);
			}
		}
	}

	public function buildJuryCertCron($data = []) {

		$event_id		= $data['event_id'] ?? 0;
		$challenge_id	= $data['challenge_id'] ?? 0;
		$challenge_type = $data['challenge_type'] ?? 'country';
		$limit 			= $data['limit'] ?? 20;

		if (empty($event_id)) return;

		log_kb(['buildJuryCertCron' => [$event_id, $challenge_id, $challenge_type]]);

		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventJuryBook_model', 'event_jury_book_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

		if (empty($challenge_type) || empty($event_info = $this->event_model->get($event_id))) return;

		if (empty($template_info = $this->certificate_template_model->get_all([
			'event_id'			=> $event_id,
			'challenge_id'		=> $challenge_id,
			'challenge_type'	=> $challenge_type,
			'is_jury'			=> 1
		])['rows'][0] ?? [])) return;

		$rows = $this->event_jury_book_model->get_all([
			'type' 			=> $challenge_type,
			'event_id' 		=> $event_id,
			'challenge_id' 	=> $challenge_id,
			'start' 		=> 0,
			'limit' 		=> $limit,
			'sort'			=> 'event_jury_book.rank',
			'order'			=> 'ASC',
		])['rows'] ?? [];

		foreach ($rows as $key => $row) {
			if (empty($certificate_info = $this->certificate_model->get_all([
				'book_id' 					=> $row['book_id'],
				'certificate_template_id' 	=> $template_info['id'],
			])['rows'][0] ?? [])) {

				$certificate_key = sprintf('%s_user_%s_%s', $template_info['type'], $row['user_id'], $row['book_id']);

				$this->certificate_model->add([
					'site_id'					=> 1,
					'event_id'					=> $row['event_id'],
					'book_id'					=> $row['book_id'],
					'user_id'					=> $row['user_id'],
					'rank'						=> $row['rank'],
					'type'						=> $certificate_key,
					'certificate_template_id'	=> $template_info['id'],
					'achievement'				=> $template_info['achievement'],
					'unique_id'					=> $template_info['id'],
					'name'						=> $template_info['name'],
					'image'						=> $certificate_key,
				]);
			}
		}
	}

	
	public function buildLiteraryLeaderCertCron($data = []) {

		$event_id		= $data['event_id'] ?? 0;
		$challenge_id	= $data['challenge_id'] ?? 0;
		$challenge_type = $data['challenge_type'] ?? 'country';
		$limit 			= $data['limit'] ?? 20;

		if (empty($event_id)) return;

		log_kb(['buildLiteraryLeaderCertCron' => [$event_id, $challenge_id, $challenge_type]]);

		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventLiteraryLeader_model', 'event_literary_leader_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');

		if (empty($challenge_type) || empty($event_info = $this->event_model->get($event_id))) return;

		if (empty($template_info = $this->certificate_template_model->get_all([
			'event_id'			=> $event_id,
			'challenge_type'	=> $challenge_type,
			'user_type'			=> 'school'
		])['rows'][0] ?? [])) return;

		$filter_data = [
			'start' 		=> 0,
			'limit' 		=> $limit,
			'sort'			=> 'event_literary_leader.rank',
			'order'			=> 'ASC',
		];

		$rows = $this->event_literary_leader_model->get_all([
			'type' 							=> $challenge_type,
			'event_id' 						=> $event_id,
			'literary_leader_challenge_id' 	=> $challenge_id
		],$filter_data)['rows'] ?? [];

		log_kb(['buildLiteraryLeaderCertCron::LiteraryLeaderdata' => [$rows]]);

		foreach ($rows as $key => $row) {
			if (empty($certificate_info = $this->certificate_model->get_all([
				'site_id' 					=> $row['site_id'],
				'event_id'					=> $row['event_id'],
				'certificate_template_id' 	=> $template_info['id'],
			])['rows'][0] ?? [])) {

				$certificate_key = sprintf('%s_school_%s_%s', $template_info['type'], $row['site_id'], $row['literary_leader_challenge_id']);

				$this->certificate_model->add([
					'site_id'					=> $row['site_id'],
					'event_id'					=> $row['event_id'],
					'rank'						=> $row['rank'],
					'type'						=> $certificate_key,
					'certificate_template_id'	=> $template_info['id'],
					'achievement'				=> $template_info['achievement'],
					'unique_id'					=> $template_info['id'],
					'name'						=> $template_info['name'],
					'image'						=> $certificate_key,
				]);
			}
		}
	}
}
