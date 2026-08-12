<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Achievement
{
	public function getUserAchievements() {
		$this->form_validation->set_rules('event_id', _l('event_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('book_id', _l('book_id'), 'trim|required|numeric');
		self::_runFormValidation();

		if (!$this->json && $this->session->userdata('user_id')) {

			$event_info = $this->event_model->get($this->input->post('event_id'));

			if (!empty($event_info) && $event_info['achievement'] == 1) {
				$certificate_info = $this->certificate_model->get_all([
					'user_id'		=> (int)$this->session->userdata('user_id'),
					'book_id'		=> (int)$this->input->post('book_id'),
					'event_id'		=> (int)$this->input->post('event_id'),
					'achievement'	=> 1,
				])['rows'][0] ?? [];

				$result = [];

				$s3_dirname = 'authorcertificates';

				if(($this->input->post('event_id') == NYAF_IN_EVENT_ID) && !empty($nyafin_event_user_info = $this->event_user_model->getEventUserByUserId(NYAF_IN_EVENT_ID, $this->session->userdata('user_id')))) {
					$s3_dirname = 'authorcertificates/all_certificate_nyafin';
				} else if(($this->input->post('event_id') == YABWF_EVENT_ID) && !empty($nyafyabwf_event_user_info = $this->event_user_model->getEventUserByUserId(YABWF_EVENT_ID, $this->session->userdata('user_id')))) {
					$s3_dirname = 'authorcertificates/all_certificate_Yabwf';
				}

				$s3_dirname = (ENVIRONMENT === 'production') ? $s3_dirname : $s3_dirname . '/test';

				foreach ($certificate_info as $certificate) {
					$book_info = $this->book_model->get($certificate['book_id']);

					$res = [];
					$res['unlock'] = true;
					$res['id'] = $certificate['id'];
					$res['slug'] = str_replace(
						['_cert', '_'],
						['_certificate', '-'],
						$certificate['type']
					) . '-' . $certificate['id'];
					$res['name'] = _l(str_replace('_cert', '_certificate', $certificate['type']));
					$res['image'] = S3_CERTIFICATE_URL . $s3_dirname . '/' . $certificate['name'];
					$res['pdf'] = S3_CERTIFICATE_URL . $s3_dirname . '/pdf/' . str_replace('jpeg', 'pdf', $certificate['name']);

					$result[] = $res;
				}

				if((date('YmdHis') <= NYAF_IN_END_DATE) && ($this->input->post('event_id') == NYAF_IN_EVENT_ID) && !empty($this->event_user_model->getEventUserByUserId(NYAF_IN_EVENT_ID, $this->session->userdata('user_id')))) {
					$unlock_certificate_info = $this->certificate_model->getLockedAchievementsNyafIn($this->session->userdata('user_id'), $this->input->post('book_id'));
				} else if((date('YmdHis') <= YABWF_END_DATE) && ($this->input->post('event_id') == YABWF_EVENT_ID) && !empty($this->event_user_model->getEventUserByUserId(YABWF_EVENT_ID, $this->session->userdata('user_id')))) {
					$unlock_certificate_info = $this->certificate_model->getLockedAchievementsYabwf($this->session->userdata('user_id'), $this->input->post('book_id'));
				} else {
					$unlock_certificate_info = [];
				}

				if(!empty($unlock_certificate_info)) {
					foreach ($unlock_certificate_info as $book_name => $unlock_certificate) {
						foreach ($unlock_certificate as $message) {
							$res = [];
							$res['unlock'] = false;
							$res['message'] = $message;

							$result[] = $res;
						}
					}
				}
				$this->json['achievements'] = $result;
				$this->json['is_achievements'] = true;
			} else {
				$this->json['achievements'][] = [
					'message' => 'The "My Achievements" section in the author\'s profile is applicable for events initiated on or after August 2023.'
				];
				$this->json['is_achievements'] = false;
			}
		}
	}

	public function getUserRankLink() {

		$this->form_validation->set_rules('event_id', _l('event_id'), 'trim|required|numeric');
		self::_runFormValidation();

		if (!$this->json && $this->session->userdata('user_id')) {
			$top_sold_book = $this->event_order_model->getSoldByBook([
				'user_id'	=> (int)$this->session->userdata('user_id'),
				'event_id'	=> (int)$this->input->post('event_id'),
				'sort'		=> 'quantity',
				'order'		=> 'DESC',
				'start'		=> 0,
				'limit'		=> 1,
			])['rows'][0] ?? [];

			if ($this->input->post('event_id') == YABWF_EVENT_ID) {
				$event_info = $this->db
				->select('
					event_challenge_school.id AS event_challenge_school_id,
					event_challenge_school.book_sold AS school_min_sold,
					event_challenge_school.max_book_sold AS school_max_sold,
					event_challenge_country.id AS event_challenge_country_id,
					event_challenge_country.book_sold AS country_min_sold,
					event_challenge_country.max_book_sold AS country_max_sold,
				')
				->from('event_challenge_school')
				->join('event_challenge_country', 'event_challenge_country.event_id = event_challenge_school.event_id')
				->where('event_challenge_school.event_id', (int)$this->input->post('event_id'))
				->where('event_challenge_school.start_date <= ', date('Y-m-d H:i:s'))
				->where('event_challenge_country.start_date <= ', date('Y-m-d H:i:s'))
				->get()
				->row_array();
			} else {
				$event_info = $this->db
				->select('
					event_challenge_school.id AS event_challenge_school_id,
					event_challenge_school.book_sold AS school_min_sold,
					event_challenge_school.max_book_sold AS school_max_sold,
					event_challenge_city.id AS event_challenge_city_id,
					event_challenge_city.book_sold AS city_min_sold,
					event_challenge_city.max_book_sold AS city_max_sold,
					event_challenge_state.id AS event_challenge_state_id,
					event_challenge_state.book_sold AS state_min_sold,
					event_challenge_state.max_book_sold AS state_max_sold,
					event_challenge_country.id AS event_challenge_country_id,
					event_challenge_country.book_sold AS country_min_sold,
					event_challenge_country.max_book_sold AS country_max_sold,
				')
				->from('event_challenge_school')
				->join('event_challenge_city', 'event_challenge_city.event_id = event_challenge_school.event_id')
				->join('event_challenge_state', 'event_challenge_state.event_id = event_challenge_school.event_id')
				->join('event_challenge_country', 'event_challenge_country.event_id = event_challenge_school.event_id')
				->where('event_challenge_school.event_id', (int)$this->input->post('event_id'))
				->where('event_challenge_school.start_date <= ', date('Y-m-d H:i:s'))
				->where('event_challenge_city.start_date <= ', date('Y-m-d H:i:s'))
				->where('event_challenge_state.start_date <= ', date('Y-m-d H:i:s'))
				->where('event_challenge_country.start_date <= ', date('Y-m-d H:i:s'))
				->get()
				->row_array();
			}

			log_kb(['Event_info-link' => $event_info, $top_sold_book]);


			log_kb([
				'user-id' => $this->session->userdata('user_id'),
				'event-info' => $event_info,
				'top_sold_book' => $top_sold_book,
			]);

			$user_info = $this->user_model->get($this->session->userdata('user_id'));

			$school_id 	= '';
			$city_id 	= '';
			$state_id 	= '';
			$league_url = NULL;
			if (!empty($user_info['site_id'])) {
				$site_info 	= $this->site_model->get($user_info['site_id']);
				$school_id 	= $site_info['id'];
				$city_id 	= $user_info['city_id'];
				$state_id 	= $user_info['state_id'];
			}

			if (empty($top_sold_book['quantity'])) {

				if ($this->input->post('event_id') == NYAF_IN_EVENT_ID) {
					$league_url = (!empty($state_id) && !empty($city_id)) ? vsprintf(USER_YAF_URL . 'india/dashboard/school/%s?trid=%s', [
						$school_id,
						$this->session->userdata('user_id')
					]) : NULL;
				} else if ($this->input->post('event_id') == YABWF_EVENT_ID) {
					$league_url = vsprintf(((ENVIRONMENT != 'production') ? 'https://uat.' :  'https://www.'). 'events.bribooks.com/kw/BVBM/dashboard/school/%s?trid=%s', [
						$school_id,
						$this->session->userdata('user_id')
					]);
				}

				$this->json['active_challenge'] = [
					'event_id'			=> $this->input->post('event_id'),
					'challenge_id'		=> $event_info['event_challenge_school_id'],
					'type'				=> 'school',
					'url'				=> $league_url
				];

				return;
			}

			if (
				$top_sold_book['quantity'] >= $event_info['school_min_sold'] &&
				$top_sold_book['quantity'] <= $event_info['school_max_sold']
			) {

				if ($this->input->post('event_id') == NYAF_IN_EVENT_ID) {
					$league_url = (!empty($state_id) && !empty($city_id)) ? vsprintf(USER_YAF_URL . 'india/dashboard/school/%s?trid=%s', [
						$school_id,
						$this->session->userdata('user_id')
					]) : NULL;
				} else if ($this->input->post('event_id') == YABWF_EVENT_ID) {
					$league_url = vsprintf(((ENVIRONMENT != 'production') ? 'https://uat.' :  'https://www.'). 'events.bribooks.com/kw/BVBM/dashboard/school/%s?trid=%s', [
						$school_id,
						$this->session->userdata('user_id')
					]);
				}

				$this->json['active_challenge'] = [
					'event_id'			=> $this->input->post('event_id'),
					'challenge_id'		=> $event_info['event_challenge_school_id'],
					'type'				=> 'school',
					'url'				=> $league_url
				];

			} elseif (
				$top_sold_book['quantity'] >= $event_info['city_min_sold'] &&
				$top_sold_book['quantity'] <= $event_info['city_max_sold']
			) {
				$this->json['active_challenge'] = [
					'event_id'			=> $this->input->post('event_id'),
					'challenge_id'		=> $event_info['event_challenge_city_id'],
					'type'				=> 'city',
					'url'				=> (!empty($state_id) && !empty($city_id)) ? vsprintf(USER_YAF_URL . 'india/dashboard/city/%s?trid=%s', [
						$city_id,
						$this->session->userdata('user_id')
					]) : NULL
				];
			} elseif (
				$top_sold_book['quantity'] >= $event_info['state_min_sold'] &&
				$top_sold_book['quantity'] <= $event_info['state_max_sold']
			) {
				$this->json['active_challenge'] = [
					'event_id'			=> $this->input->post('event_id'),
					'challenge_id'		=> $event_info['event_challenge_state_id'],
					'type'				=> 'state',
					'url'				=> (!empty($state_id) && !empty($city_id)) ? vsprintf(USER_YAF_URL . 'india/dashboard/state/%s?trid=%s', [
						$state_id,
						$this->session->userdata('user_id')
					]) : NULL
				];
			} elseif (
				$top_sold_book['quantity'] >= $event_info['country_min_sold']
			) {

				if ($this->input->post('event_id') == NYAF_IN_EVENT_ID) {
					$league_url = (!empty($state_id) && !empty($city_id)) ? vsprintf(USER_YAF_URL . 'india/dashboard?trid=%s', [
						$this->session->userdata('user_id')
					]) : NULL;
				} else if ($this->input->post('event_id') == YABWF_EVENT_ID) {
					$league_url = vsprintf(((ENVIRONMENT != 'production') ? 'https://uat.' :  'https://www.'). 'events.bribooks.com/kw/BVBM/dashboard?trid=%s', [
						$this->session->userdata('user_id')
					]);
				}
				$this->json['active_challenge'] = [
					'event_id'			=> $this->input->post('event_id'),
					'challenge_id'		=> $event_info['event_challenge_country_id'],
					'type'				=> 'national',
					'url'				=> $league_url
				];
			}
		}
	}
}
