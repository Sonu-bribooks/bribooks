<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class GenericCertificate_lib {
	public function __construct() {
		$this->CI =& get_instance();
		$this->db = $this->CI->db;
		$this->session = $this->CI->session;
		$this->load = $this->CI->load;
		$this->config = $this->CI->config;

		$this->load->model('common/Cron_model');
		$this->load->model('book/Book_model');
		$this->load->model('user/Student_model');
		$this->load->model('order/Order_model');
		$this->load->model('order/OrderProduct_model');
		$this->load->model('event/Event_model');
		$this->load->model('event/EventBook_model');
		$this->load->model('certificate/Certificate_model');
		$this->load->model('certificate/CertificateTemplate_model');
		$this->load->model('certificate/CertificateMessageTemplate_model');
		$this->load->model('medallion/Medallion_model');
		$this->load->model('medallion/MedallionOrder_model');
		$this->load->model('medallion/MedallionAddress_model');
		$this->load->model('ranking/RankingSchool_model');
		$this->load->model('ranking/RankingCity_model');
		$this->load->model('ranking/RankingState_model');
		$this->load->model('ranking/RankingCountry_model');

		$this->cron_model		   	= $this->CI->Cron_model;
		$this->book_model		   	= $this->CI->Book_model;
		$this->student_model		= $this->CI->Student_model;
		$this->order_model		  	= $this->CI->Order_model;
		$this->order_product_model  = $this->CI->OrderProduct_model;
		$this->event_model	 		= $this->CI->Event_model;
		$this->event_book_model	 	= $this->CI->EventBook_model;
		$this->certificate_model	= $this->CI->Certificate_model;
		$this->medallion_model		   		= $this->CI->Medallion_model;
		$this->medallion_order_model	 	= $this->CI->MedallionOrder_model;
		$this->medallion_address_model	 	= $this->CI->MedallionAddress_model;
		$this->certificate_template_model			= $this->CI->CertificateTemplate_model;
		$this->certificate_message_template_model 	= $this->CI->CertificateMessageTemplate_model;
		$this->ranking_school_model 				= $this->CI->RankingSchool_model;
		$this->ranking_city_model 				= $this->CI->RankingCity_model;
		$this->ranking_state_model 				= $this->CI->RankingState_model;
		$this->ranking_country_model 				= $this->CI->RankingCountry_model;
	}

	public function createCertificate($order_id = 0, $alert = true) {
		log_kb([
			'createCertificate' => $order_id
		]);

		$event_id = 0;

		if (
			empty($order_id) ||
			empty($order_info = $this->order_model->get($order_id)) ||
			in_array($order_info['status'], [0, 91, 92])
		) return;

		if (empty($order_products = $this->order_product_model->get_all([
			'order_id' =>  $order_id
		])['rows'] ?? [])) return;

		foreach ($order_products as $order_product) {
			$book_info = $this->book_model->get($order_product['product_id']);

			if (
				empty($book_info) ||
				empty($sold = $this->order_model->getTotalProductsByProductId($book_info['id']))
			) continue;

			if (empty($author_info = $this->student_model->get($book_info['user_id']))) continue;

			if (!empty($event_book_info = $this->event_book_model->get_all(['book_id' => (int)$book_info['id']])['rows'][0] ?? [])) {
				if (
					!empty($event_info = $this->event_model->get($event_book_info['event_id'])) &&
					strtotime($event_info['end_date']) > time()
				) {
					$event_id = $event_info['id'];
				} else {
					continue;
				}
			}

			$certificate_templates = $this->certificate_template_model->get_all([
				'country_code'	=> strtolower(get_author_currency_code($author_info['id'])) === 'inr' ? 'IN' : 'GE',
				'event_id'		=> (int)$event_id,
				'status'		=> 1,
				'has_rank'		=> 0,
				'sort'			=> 'certificate_template.book_sold',
				'order'			=> 'ASC',
			])['rows'] ?? [];

			if (empty($certificate_templates)) continue;

			foreach ($certificate_templates as $key => $template) {
				if ($sold >= $template['book_sold']) {
					if (empty($template['type'])) {
						$template['type'] = $template['challenge_type'] ?? str_replace(' ', '_', strtolower(trim($template['name'])));
					}
					// only create cert with same genre if genre present in cert
					if (!empty($template['genre_ids'])) {
						$genres = json_decode($template['genre_ids'], true);

						if (!empty($genres) && !empty($book_info['genre_id']) && !in_array($book_info['genre_id'], $genres)) {
							continue;
						}
					}

					if (!empty($template['challenge_id']) && !empty($template['challenge_type'])) {
						$model = sprintf('ranking_%s_model', $template['challenge_type']);

						$book_league_info = $this->{$model}->get_all([
							'challenge_id'	=> $template['challenge_id'],
							'book_id'		=> $book_info['id']
						])['rows'][0] ?? [];

						if (empty($book_league_info)) continue;
					}

					$certificate_key = sprintf('%s_user_%s_%s', $template['type'], $book_info['user_id'], $book_info['id']);

					$certificate_info = $this->certificate_model->get_all([
						'book_id'				=> $book_info['id'],
						'event_id'				=> $event_id,
						'user_id'				=> $book_info['user_id'],
						'name'					=> $template['name'],
					])['rows'][0] ?? [];

					log_kb([
						'ExistingCertificate:: ' => $certificate_info
					]);

					if (empty($certificate_info)) {
						$certificate_id = $this->certificate_model->add([
							'site_id'					=> $author_info['site_id'],
							'event_id'					=> $event_id,
							'book_id'					=> $book_info['id'],
							'user_id'					=> $book_info['user_id'],
							'type'						=> $template['type'],
							'certificate_template_id'	=> $template['id'],
							'achievement'				=> $template['achievement'],
							'unique_id'					=> $template['id'],
							'name'						=> $template['name'],
							'image'						=> $certificate_key,
						]);

						$medallion_order_code = '';

						if (!empty($template['medallion_id'])) {
							if (empty($medallion_order_info = $this->medallion_order_model->get_all([
								'book_id'		=> $book_info['id'],
								// 'event_id'		=> $event_id,
								// 'user_id'		=> $book_info['user_id'],
								'medallion_id'	=> $template['medallion_id']
							])['rows'][0] ?? [])) {
								$medallion_info = $this->medallion_model->get($template['medallion_id']);
								$address_info 	= $this->medallion_address_model->get_all([
									'user_id'	=> (int)$book_info['user_id']
								])['rows'][0] ?? [];

								$medallion_order_code = vsprintf('BBM-%s%s%s%s', [
									time(),
									$event_id,
									$template['medallion_id'],
									$book_info['id'],
								]);

								$author_currency_code = get_author_currency_code($book_info['user_id']);

								$this->medallion_order_model->add([
									'order_code'		=> $medallion_order_code,
									'event_id'			=> (int)$event_id,
									'address_id'		=> (int)($address_info['id'] ?? 0),
									'book_id'			=> (int)$book_info['id'],
									'user_id'			=> (int)$book_info['user_id'],
									'medallion_id'		=> (int)($template['medallion_id'] ?? 0),
									'pickup_location_id'=> 1,
									'weight'			=> (double)($medallion_info['weight'] ?? 0),
									'subtotal'			=> (double)apply_currency_exchange($medallion_info['price'] ?? 0, $author_currency_code),
									'shipping_cost'		=> (double)apply_currency_exchange($medallion_info['shipping_cost'] ?? 0, $author_currency_code),
									'total'				=> (double)apply_currency_exchange(($medallion_info['price'] ?? 0) + ($medallion_info['shipping_cost'] ?? 0), $author_currency_code),
									'currency_id'		=> (int)get_author_currency_id($book_info['user_id']),
									'currency_code'		=> $author_currency_code,
									'currency_symbol'	=> get_author_currency_symbol($book_info['user_id']),
								]);
							}
						}

						$alert && self::_sendCertificateGenerateMessage($certificate_id, $sold, $medallion_order_code);
					}
				}
			}

			$alert && self::_sendCertificateFomoMessage([
				'book_id'		=> $book_info['id'],
				'event_id'		=> $event_id,
				'sold'			=> $sold,
				'country_code'	=> strtolower(get_author_currency_code($author_info['id'])) === 'inr' ? 'IN' : 'GE',
			]);
		}
	}

	private function _sendCertificateGenerateMessage($certificate_id = 0, $sold = 0, $medallion_order_code = null) {
		if (empty($certificate_id)) return;
		if (empty($certificate_info = $this->certificate_model->get($certificate_id))) return;

		if (!empty($this->cron_model->getByCode(sprintf('genericCertificateCreatedCron_%s', $certificate_id)))) return;

		$this->cron_model->add([
			'code'			=> sprintf('genericCertificateCreatedCron_%s', $certificate_id),
			'action'		=> 'alert_model->genericCertificateCreatedCron',
			'data'			=> [$certificate_id, $sold, $medallion_order_code],
			'site_id'		=> 1,
			'alert_date'	=> date('Y-m-d H:i:00', strtotime(sprintf('+%d minutes', ENVIRONMENT === 'production' ? 1 : 1))),
		]);
	}

	private function _sendCertificateFomoMessage($data = []) {
		$templates = $this->certificate_message_template_model->get_all([
			'event_id'			=> (int)$data['event_id'],
			'country_code'		=> $data['country_code'],
			'min_sold_le'		=> $data['sold'],
			'max_sold_lt'		=> $data['sold'],
			'fomo'				=> 1,
			'status'			=> 1,
			'sort'				=> 'certificate_message_template.sort_order',
			'order'				=> 'ASC',
		])['rows'] ?? [];

		if (empty($templates)) return;

		foreach ($templates as $template_info) {
			if (!empty($template_info['league'])) {
				if (($data['sold'] >= $template_info['min_sold']) && ($data['sold'] < $template_info['max_sold'])) {
					if (!empty($template_info['challenge_id']) && !empty($template_info['challenge_type'])) {
						$model = sprintf('ranking_%s_model', $template_info['challenge_type']);

						$book_league_info = $this->{$model}->get_all([
							'challenge_id'	=> $template_info['challenge_id'],
							'book_id'		=> $data['book_id']
						])['rows'][0] ?? [];

						if (empty($book_league_info)) return;
					}

					$code = sprintf('genericCertificateLeagueFomoCron_%s_%s', $data['book_id'], $template_info['id']);

					if (!empty($cron_info = $this->cron_model->getByCode($code))) {
						$this->cron_model->edit($cron_info['id'], [
							'data'			=> [$template_info['id'], $data],
							'status'		=> 0,
							'alert_date'	=> date('Y-m-d H:i:00', strtotime(sprintf('+%d minutes', ENVIRONMENT === 'production' ? 10 : 2))),
						]);
					} else {
						$this->cron_model->add([
							'code'			=> $code ,
							'action'		=> 'alert_model->genericCertificateFomoCron',
							'data'			=> [$template_info['id'], $data],
							'site_id'		=> 1,
							'alert_date'	=> date('Y-m-d H:i:00', strtotime(sprintf('+%d minutes', ENVIRONMENT === 'production' ? 10 : 2))),
						]);
					}
				}
			} else {
				if (($data['sold'] >= $template_info['min_sold']) && ($data['sold'] < $template_info['max_sold'])) {
					$code = sprintf('genericCertificateFomoCron_%s', $data['book_id']);

					if (!empty($cron_info = $this->cron_model->getByCode($code))) {
						$this->cron_model->edit($cron_info['id'], [
							'data'			=> [$template_info['id'], $data],
							'status'		=> 0,
							'alert_date'	=> date('Y-m-d H:i:00', strtotime(sprintf('+%d minutes', ENVIRONMENT === 'production' ? 10 : 2))),
						]);
					} else {
						$this->cron_model->add([
							'code'			=> $code ,
							'action'		=> 'alert_model->genericCertificateFomoCron',
							'data'			=> [$template_info['id'], $data],
							'site_id'		=> 1,
							'alert_date'	=> date('Y-m-d H:i:00', strtotime(sprintf('+%d minutes', ENVIRONMENT === 'production' ? 10 : 2))),
						]);
					}
				}
			}
		}
	}
}
