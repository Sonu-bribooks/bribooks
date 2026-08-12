<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

load_trait('whatsapp');

trait NyafInd2022
{
	use CommonWhatsapp;

	public function testElibleUsersForCert($user_id = '')
	{
		return;

		$id = 'all';
		$code = 'createCertificate';

		if(empty($this->cron_model->getByCode($code . '_' . $id))) {
			$this->cron_model->add([
				'code'			=> $code . '_' . $id,
				'action'		=> 'alert_model->' . $code,
				'data'			=> [$id],
				'alert_date'	=> date('Y-m-d H:i:s'),
			]);
		} else {
			$this->cron_model->editByCode($code . '_' . $id, ['status' => 0]);
		}
	}

	public function testAwardsOnBookSold($order_id = '')
	{
		return;

		if(empty($order_id))
			return;

		self::createAwardsOnBookSold($order_id);
	}

	public function testTopRanksTest($start = '', $limit = 1) {
		if(date('Ymd') > '20230315')
			return;

		if(!is_numeric($start) || empty($limit) || !is_numeric($limit))
			return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = $start;
		$filter_data['limit'] = $limit;
		$filter_data['quantity_ge'] = 1;
		$filter_data['end_date'] = '2023-03-15 21:00:00';

		$result = $this->ranking_model->getRanks($filter_data);

		$this->load->model('user/UserDetails_model', 'user_details_model');

		$rank_data = [];
		$i = 0;
		foreach ($result['rows'] ?? [] as $book_info) {
			$user_details_info = $this->user_details_model->getByUid($book_info['user_id']);

			if(!empty($user_details_info))
				continue;

			// pr($book_info, 1);

			$user_info = $this->student_model->get($book_info['user_id']);

			$id = $book_info['id'];
			$code = 'createNyafAuthorImageTest';

			if(empty($this->cron_model->getByCode($code . '_' . $id))) {
				/*$this->cron_model->add([
					'code'			=> $code . '_' . $id,
					'action'		=> 'alert_model->' . $code,
					'data'			=> [$id],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
						? '+1 minutes'
						: '+1 minutes'
					))
				]);*/
			} else {
				$this->cron_model->editByCode($code . '_' . $id, [
					'data' => [$id],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
						? '+1 minutes'
						: '+1 minutes'
					)),
					'status' => 0
				]);
			}

			$rank_data[$i]['book_id'] = $book_info['id'];
			$rank_data[$i]['site_id'] = $user_info['site_id'];
			$rank_data[$i]['site_code'] = $user_info['source'];
			$rank_data[$i]['user_id'] = $book_info['user_id'];
			$rank_data[$i]['name'] = trim($user_info['first_name'] . ' ' . $user_info['last_name']);
			$rank_data[$i]['email'] = $user_info['email'];
			$rank_data[$i]['mobile'] = $user_info['mobile'];
			/*$rank_data[$i]['version'] = $book_info['version'];
			$rank_data[$i]['category_id'] = $book_info['category_id'];*/
			$rank_data[$i]['quantity'] = $book_info['quantity'];
			$rank_data[$i]['url'] = vsprintf(USER_URL . 'registration?uid=%s&code=%s', [
				$user_info['id'],
				$user_info['verification_code'],
			]);

			$i++;
		}

		pr($rank_data, 1);
	}

	public function testTopRanks($start = '', $limit = 1) {
		/*if(date('Ymd') > '20230329')
			return;*/

		if(!is_numeric($start) || empty($limit) || !is_numeric($limit))
			return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = $start;
		$filter_data['limit'] = $limit;
		$filter_data['quantity_ge'] = 50;
		$filter_data['end_date'] = '2023-03-15 21:00:00';

		$result = $this->ranking_model->getRanks($filter_data);

		// $this->load->model('user/UserDetails_model', 'user_details_model');

		$rank_data = [];
		$i = 0;
		foreach ($result['rows'] ?? [] as $book_info) {
			/*$user_details_info = $this->user_details_model->getByUid($book_info['user_id']);

			if(!empty($user_details_info))
				continue;*/

			// pr($book_info, 1);

			$user_info = $this->student_model->get($book_info['user_id']);

			$id = $book_info['id'];
			$code = 'createNyafAuthorImage';

			if(empty($this->cron_model->getByCode($code . '_' . $id))) {
				$this->cron_model->add([
					'code'			=> $code . '_' . $id,
					'action'		=> 'alert_model->' . $code,
					'data'			=> [$id],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
						? '+10 minutes'
						: '+1 minutes'
					))
				]);
			} else {
				$this->cron_model->editByCode($code . '_' . $id, [
					'data' => [$id],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
						? '+10 minutes'
						: '+1 minutes'
					)),
					'status' => 0
				]);
			}

			$rank_data[$i]['book_id'] = $book_info['id'];
			$rank_data[$i]['site_id'] = $user_info['site_id'];
			$rank_data[$i]['site_code'] = $user_info['source'];
			$rank_data[$i]['user_id'] = $book_info['user_id'];
			$rank_data[$i]['name'] = trim($user_info['first_name'] . ' ' . $user_info['last_name']);
			$rank_data[$i]['email'] = $user_info['email'];
			$rank_data[$i]['mobile'] = $user_info['mobile'];
			/*$rank_data[$i]['version'] = $book_info['version'];
			$rank_data[$i]['category_id'] = $book_info['category_id'];*/
			$rank_data[$i]['quantity'] = $book_info['quantity'];
			$rank_data[$i]['url'] = vsprintf(USER_URL . 'registration?uid=%s&code=%s', [
				$user_info['id'],
				$user_info['verification_code'],
			]);

			$i++;
		}

		pr($rank_data, 1);
	}

	public function getTopRankAuthorsCron($start = '', $limit = 1) {
		/*if(date('Ymd') > '20230329')
			return;*/

		if(!is_numeric($start) || empty($limit) || !is_numeric($limit))
			return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = $start;
		$filter_data['limit'] = $limit;
		$filter_data['quantity_ge'] = 1;
		$filter_data['end_date'] = '2023-03-15 21:00:00';

		$result = $this->ranking_model->getRanks($filter_data);
		pr($result, 1);

		$rank_data = [];
		$i = 0;
		foreach ($result['rows'] ?? [] as $book_info) {
			$user_info = $this->student_model->get($book_info['user_id']);

			if (
				empty($this->db->get_where('user_details_nyaf_guest', [
					'user_id'	=> $user_info['id'],
					'book_id'	=> $book_info['id']
				])->row_array())
			) {
				$invite_id = 0;

				if (
					empty($user_details_invite_info = $this->db->get_where('user_details_nyaf_invites', [
						'user_id'	=> $user_info['id'],
						'book_id'	=> $book_info['id']
					])->row_array())
				) {
					$save = [
						'user_id'		=> $user_info['id'],
						'site_id'		=> $user_info['site_id'],
						'book_id'		=> $book_info['id'],
						'status'		=> 0,
					];

					$invite_id = $this->user_details_invite_model->add($save);
				}

				$id = $user_details_invite_info['id'] ?? $invite_id;

				if(!$id) {
					continue;
				}

				$code = 'userDetailsAuthorInvite';

				if(empty($this->cron_model->getByCode($code . '_' . $id))) {
					/*$this->cron_model->add([
						'code'			=> $code . '_' . $id,
						'site_id'		=> 1,
						'action'		=> 'alert_model->' . $code,
						'data'			=> [$id],
						'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
							? '+10 minutes'
							: '+1 minutes'
						))
					]);*/
				} else {
					$this->cron_model->editByCode($code . '_' . $id, [
						'data' => [$id],
						'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
							? '+10 minutes'
							: '+1 minutes'
						)),
						'status' => 0
					]);
				}

				$rank_data[$i]['book_id'] = $book_info['id'];
				$rank_data[$i]['site_id'] = $user_info['site_id'];
				$rank_data[$i]['site_code'] = $user_info['source'];
				$rank_data[$i]['user_id'] = $book_info['user_id'];
				$rank_data[$i]['name'] = trim($user_info['first_name'] . ' ' . $user_info['last_name']);
				$rank_data[$i]['email'] = $user_info['email'];
				$rank_data[$i]['mobile'] = $user_info['mobile'];
				$rank_data[$i]['quantity'] = $book_info['quantity'];
				$rank_data[$i]['url'] = vsprintf(USER_URL . 'registration/details?uid=%s&code=%s&bid=%s', [
					$user_info['id'],
					$user_info['verification_code'],
					$book_info['id'],
				]);

				$i++;
			}
		}

		pr($rank_data, 1);
	}

	public function getJuryInviteCron($start = '', $limit = 1) {
		if(date('Ymd') > '20230329')
			return;

		if(!is_numeric($start) || empty($limit) || !is_numeric($limit))
			return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('user/UserDetailsInvite_model', 'user_details_invite_model');

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = $start;
		$filter_data['limit'] = $limit;
		$filter_data['quantity_ge'] = 0;
		$filter_data['end_date'] = '2023-03-15 21:00:00';
		// $filter_data['book_ids'] = [59629,36994,7165,8919,25306,2532,26328,48254,67485,64125,28550,17642,27886,15929,24889,3654,26099,25100,49697,35422,61202,52759,75236,75819,28036,30086,9330,37498,61826,19741,77677,45020,51057,59006,47023,62191,30402,11405,62051,57157,16225,41601,35149,59402,60365,54940];
		$filter_data['book_ids'] = [26753,7165,67770,36506,4490,45190,67957,41601,35149,76313,61826,42074,16009,28407,57157,22864,37441,35422,26713,35717,14616,11749,58970,27795,4451,50334,75256,43370,36161,67721,46414,8710,75324,39811,71415,28736,11070,55303,24682,27555,53351,11032,47469,30640,4153,52225,1138,28036,9721,58727,40521,8418,34226,52342,5615,42929,36943,2602,57612,3952,4001,37444,39596,40251,62541,67485,32265,2375,37498,49457,32021,66927,57263,33945,32749,9958,75660,11247,31101,30369,9103,63047,21091,45812,34776,52079,30402,63470,14444,26923,44900,3509,13282,7167,17847,67044,2354,39784,21333,58798,25432,36355,39000,61674,75797,25337,6762,2606,35097,39644,43321,2641,12283,25416,55775,15823,43267,62191,26185,73919,53883,12509,9330,42523,60137,36986,67114,24352,16537,23158,37398,31082,53062,2374,46032,47599,25376,6062,21595,34396,26554,11216,74853,32224,9960,56641,5061,47875,63338,12591,62301,8540,59102,63129,8780,37562,8447,20789,25945,44034,51910,65260,59715,19075,48519,73945,28550,58477,47023,26449,65696,44891,52568,72547,35789,44161,2401,71924,27402,11329,25031,57392,28033,44196,48233,12736,12732,14115,31794,50619,53674,20248,10831,60338,59643,15976,2096,34219,22524,14056,22915,55919,34085,63001,55169,66413,48652,21065,6957,54762,16477,40934,56053,62191,9330,47023,28550,51057,45020,24889,25100,26328,60365,64125,26099,11405,2532,75819,52759,19741];

		$result = $this->ranking_model->getRanks($filter_data);

		$rank_data = [];
		$i = 0;
		foreach ($result['rows'] ?? [] as $book_info) {
			$user_info = $this->student_model->get($book_info['user_id']);

			if (
				empty($this->db->get_where('user_certificate_address', [
					'user_id'	=> $user_info['id'],
					'book_id'	=> $book_info['id']
				])->row_array())
			) {
				$invite_id = 0;

				if (
					empty($user_details_invite_info = $this->db->get_where('user_details_nyaf_invites', [
						'user_id'	=> $user_info['id'],
						'book_id'	=> $book_info['id']
					])->row_array())
				) {
					$save = [
						'user_id'		=> $user_info['id'],
						'site_id'		=> $user_info['site_id'],
						'book_id'		=> $book_info['id'],
						'status'		=> 0,
					];

					$invite_id = $this->user_details_invite_model->add($save);
				}

				$id = $user_details_invite_info['id'] ?? $invite_id;

				if(!$id) {
					continue;
				}

				$code = 'userDetailsAuthorInvite';

				if(empty($this->cron_model->getByCode($code . '_' . $id))) {
					$this->cron_model->add([
						'code'			=> $code . '_' . $id,
						'site_id'		=> 1,
						'action'		=> 'alert_model->' . $code,
						'data'			=> [$id],
						'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
							? '+1 minutes'
							: '+1 minutes'
						))
					]);
				} else {
					$this->cron_model->editByCode($code . '_' . $id, [
						'data' => [$id],
						'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
							? '+1 minutes'
							: '+1 minutes'
						)),
						'status' => 0
					]);
				}

				$rank_data[$i]['book_id'] = $book_info['id'];
				$rank_data[$i]['site_id'] = $user_info['site_id'];
				$rank_data[$i]['site_code'] = $user_info['source'];
				$rank_data[$i]['user_id'] = $book_info['user_id'];
				$rank_data[$i]['name'] = trim($user_info['first_name'] . ' ' . $user_info['last_name']);
				$rank_data[$i]['email'] = $user_info['email'];
				$rank_data[$i]['mobile'] = $user_info['mobile'];
				$rank_data[$i]['quantity'] = $book_info['quantity'];
				$rank_data[$i]['url'] = vsprintf(USER_URL . 'addressrequest?uid=%s&code=%s&bid=%s', [
					$user_info['id'],
					$user_info['verification_code'],
					$book_info['id'],
				]);

				$i++;
			}
		}

		pr($rank_data, 1);
	}

	public function createPopularCertificate() {
		// return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('common/Grade_model', 'grade_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('Alert_model', 'alert_model');

		/*$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = 0;
		$filter_data['limit'] = 1;
		$filter_data['quantity_ge'] = 50;
		$filter_data['quantity_le'] = 0;
		$filter_data['end_date'] = '2023-03-15 21:00:00';
		$most_popular_india_result = $this->ranking_model->getRanks($filter_data);
		$most_popular_india_result = $most_popular_india_result['rows'] ?? [];

		pr($most_popular_india_result);*/

		/*$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = 1;
		$filter_data['limit'] = 10;
		$filter_data['quantity_ge'] = 50;
		$filter_data['quantity_le'] = 0;
		$filter_data['end_date'] = '2023-03-15 21:00:00';
		$popular_india_result = $this->ranking_model->getRanks($filter_data);
		$popular_india_result = $popular_india_result['rows'] ?? [];

		pr($popular_india_result);*/

		$popular_state_result = [];
		$states = $this->state_model->get_all(['country_id' => 1, 'sort' => 'name', 'order' => 'ASC']);
		foreach ($states['rows'] ?? [] as $state) {
			$filter_data = [];
			$filter_data['site_code'] = 'NYAFIND2022';
			/*$filter_data['start'] = 0;
			$filter_data['limit'] = 1;
			$filter_data['quantity_ge'] = 40;
			$filter_data['quantity_le'] = 49;*/
			$filter_data['quantity_ge'] = 49;
			$filter_data['end_date'] = '2023-03-15 21:00:00';
			$filter_data['state_id'] = $state['id'];

			if(!empty($state_wise_result = $this->ranking_model->getRanks($filter_data))) {
				/*if(!empty($state_wise_result['rows'][0])) {
					$state_wise_result['rows'][0]['state_id'] = $state['id'];
					$state_wise_result['rows'][0]['state'] = $state['name'];
					$popular_state_result[] = $state_wise_result['rows'][0];
				}*/
				foreach ($state_wise_result['rows'] ?? [] as $state_result) {
					$state_result['state_id'] = $state['id'];
					$state_result['state'] = $state['name'];
					$popular_state_result[] = $state_result;
				}
			}
		}

		pr($popular_state_result);

		/*$popular_city_result = [];
		$cities = $this->city_model->get_all(['country_id' => 1]);
		foreach ($cities['rows'] ?? [] as $city) {
			$filter_data = [];
			$filter_data['site_code'] = 'NYAFIND2022';
			$filter_data['start'] = 0;
			$filter_data['limit'] = 1;
			$filter_data['quantity_ge'] = 20;
			$filter_data['quantity_le'] = 39;
			$filter_data['end_date'] = '2023-03-15 21:00:00';
			$filter_data['city_id'] = $city['id'];

			if(!empty($city_wise_result = $this->ranking_model->getRanks($filter_data))) {
				if(!empty($city_wise_result['rows'][0])) {
					$city_wise_result['rows'][0]['state'] = $city['state_id'];
					$city_wise_result['rows'][0]['city_id'] = $city['id'];
					$city_wise_result['rows'][0]['city'] = $city['name'];
					$popular_city_result[] = $city_wise_result['rows'][0];
				}
			}
		}

		pr($popular_city_result);*/

		/*$popular_school_result = [];
		$sites = $this->site_model->get_all(['country_code' => 'in', 'site_code' => 'NYAFIND2022']);
		foreach ($sites as $site) {
			$filter_data = [];
			$filter_data['site_code'] = 'NYAFIND2022';
			$filter_data['start'] = 0;
			$filter_data['limit'] = 1;
			$filter_data['quantity_ge'] = 10;
			$filter_data['quantity_le'] = 19;
			$filter_data['end_date'] = '2023-03-15 21:00:00';
			$filter_data['site_id'] = $site['id'];

			if(!empty($school_wise_result = $this->ranking_model->getRanks($filter_data))) {
				if(!empty($school_wise_result['rows'][0])) {
					$school_wise_result['rows'][0]['school'] = $site['name'];
					$school_wise_result['rows'][0]['state'] = $site['state_id'];
					$school_wise_result['rows'][0]['city'] = $site['city_id'];
					$school_wise_result['rows'][0]['site_code'] = $site['site_code'];
					$popular_school_result[] = $school_wise_result['rows'][0];
				}
			}
		}

		pr($popular_school_result);*/

		/*$popular_class_result = [];
		$grades = $this->grade_model->get_all(['site_code' => 'NYAFIND2022']);
		foreach ($grades['rows'] ?? [] as $grade) {
			$filter_data = [];
			$filter_data['site_code'] = 'NYAFIND2022';
			$filter_data['start'] = 0;
			$filter_data['limit'] = 1;
			$filter_data['quantity_ge'] = 1;
			$filter_data['quantity_le'] = 9;
			$filter_data['end_date'] = '2023-03-15 21:00:00';
			$filter_data['grade_id'] = $grade['id'];

			if(!empty($class_wise_result = $this->ranking_model->getRanks($filter_data))) {
				if(!empty($class_wise_result['rows'][0])) {
					$class_wise_result['rows'][0]['site_id'] = $grade['site_id'];
					$class_wise_result['rows'][0]['site_code'] = $this->site_model->get($grade['site_id'])['site_code'];
					$class_wise_result['rows'][0]['class'] = $grade['name'];
					$popular_class_result[] = $class_wise_result['rows'][0];
				}
			}
		}

		pr($popular_class_result, 1);*/
	}

	public function createPopularCertificateClass() {
		return;

		if(ENVIRONMENT !== 'production')
			return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('common/Grade_model', 'grade_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('Alert_model', 'alert_model');

		$popular_class_result = [];
		/*$grades = $this->grade_model->get_all(['site_code' => 'NYAFIND2022']);
		foreach ($grades['rows'] ?? [] as $grade) {
			$filter_data = [];
			$filter_data['site_code'] = 'NYAFIND2022';
			$filter_data['start'] = 0;
			$filter_data['limit'] = 1;
			$filter_data['quantity_ge'] = 1;
			$filter_data['quantity_le'] = 9;
			$filter_data['end_date'] = '2023-03-15 21:00:00';
			$filter_data['grade_id'] = $grade['id'];

			if(!empty($class_wise_result = $this->ranking_model->getRanks($filter_data))) {
				if(!empty($class_wise_result['rows'][0])) {
					$class_wise_result['rows'][0]['site_id'] = $grade['site_id'];
					$class_wise_result['rows'][0]['site_code'] = $this->site_model->get($grade['site_id'])['site_code'];
					$class_wise_result['rows'][0]['class'] = $grade['name'];
					$popular_class_result[] = $class_wise_result['rows'][0];
				}
			}
		}*/

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		/*$filter_data['start'] = 0;
		$filter_data['limit'] = 1;
		$filter_data['quantity_ge'] = 1;
		$filter_data['quantity_le'] = 9;*/
		$filter_data['quantity_ge'] = 10;
		$filter_data['end_date'] = '2023-03-15 21:00:00';

		if(!empty($class_wise_result = $this->ranking_model->getRanks($filter_data))) {
			$popular_class_result = $class_wise_result['rows'] ?? [];
		}

		foreach(array_chunk($popular_class_result, 200) as $popular_class_res) {
			foreach ($popular_class_res as $book_info) {
				$user_info = $this->user_model->get($book_info['user_id']);

				$mobile = $user_info['mobile'];
				$email = $user_info['email'];

				$image_name = $this->certificate_model->createPopularAuthorCertificate($book_info, 'most_popular_author_in_class_cert');
				if($image_name) {
					$save = [];
					$save['book_id'] 	= $book_info['id'];
					$save['site_id'] 	= $user_info['site_id'];
					$save['user_id'] 	= $book_info['user_id'];
					$save['type'] 		= 'most_popular_author_in_class_cert';
					$save['name'] 		= $image_name;

					$this->certificate_model->add($save);

					$my_certificates_url = USER_URL . 'account/mycertificates';
					$author_name = explode(" ", trim($book_info['author_name']));

					self::_sendWhatsappText(
						$mobile,
						[
							'template'		=> '1030534948333794',
							'parameters'	=> [
								ucfirst($author_name[0]),
								strtoupper('most popular author in class certificate'),
								$book_info['name'],
								$my_certificates_url
							]
						]
					);

					$subject = ucfirst($author_name[0]) . ' Congratulations, You are the WINNER!';

					$content = '<p>Hey <strong>'.ucfirst($author_name[0]).'</strong></p>
<p>Congratulations, You WON!!</p>
<p>You have won the <strong>'.strtoupper('most popular author in class certificate').'</strong> with your book, <strong>'.$book_info['name'].'</strong>.</p>
<p>You can download your e-certificates from the <strong>My Certificate</strong> section on your profile <strong>'.$my_certificates_url.'</strong></p><br />
<p>Well done!</p>
<p>National Young Authors Fair, India</p>';

					$this->alert_model->email(
						$email,
						$subject,
						$content,
						[],
						[]
					);
				}
			}
		}

		pr(count($popular_class_result), 1);
	}

	public function createPopularCertificateSchool() {
		return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('Alert_model', 'alert_model');

		$popular_school_result = [];
		/*$sites = $this->site_model->get_all(['country_code' => 'in', 'site_code' => 'NYAFIND2022']);
		foreach ($sites as $site) {
			$filter_data = [];
			$filter_data['site_code'] = 'NYAFIND2022';
			$filter_data['start'] = 0;
			$filter_data['limit'] = 1;
			$filter_data['quantity_ge'] = 10;
			$filter_data['quantity_le'] = 19;
			$filter_data['end_date'] = '2023-03-15 21:00:00';
			$filter_data['site_id'] = $site['id'];

			if(!empty($school_wise_result = $this->ranking_model->getRanks($filter_data))) {
				if(!empty($school_wise_result['rows'][0])) {
					$school_wise_result['rows'][0]['school'] = $site['name'];
					$school_wise_result['rows'][0]['state'] = $site['state_id'];
					$school_wise_result['rows'][0]['city'] = $site['city_id'];
					$school_wise_result['rows'][0]['site_code'] = $site['site_code'];
					$popular_school_result[] = $school_wise_result['rows'][0];
				}
			}
		}*/

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		/*$filter_data['start'] = 0;
		$filter_data['limit'] = 1;
		$filter_data['quantity_ge'] = 10;
		$filter_data['quantity_le'] = 19;*/
		$filter_data['quantity_ge'] = 20;
		$filter_data['end_date'] = '2023-03-15 21:00:00';

		if(!empty($school_wise_result = $this->ranking_model->getRanks($filter_data))) {
			$popular_school_result = $school_wise_result['rows'] ?? [];
		}

		foreach(array_chunk($popular_school_result, 50) as $popular_school_res) {
			foreach ($popular_school_res as $book_info) {
				$user_info = $this->user_model->get($book_info['user_id']);

				$mobile = $user_info['mobile'];
				$email = $user_info['email'];

				$image_name = $this->certificate_model->createPopularAuthorCertificate($book_info, 'most_popular_author_in_school_cert');
				if($image_name) {
					$save = [];
					$save['book_id'] 	= $book_info['id'];
					$save['site_id'] 	= $user_info['site_id'];
					$save['user_id'] 	= $book_info['user_id'];
					$save['type'] 		= 'most_popular_author_in_school_cert';
					$save['name'] 		= $image_name;

					$this->certificate_model->add($save);

					$my_certificates_url = USER_URL . 'account/mycertificates';
					$author_name = explode(" ", trim($book_info['author_name']));

					self::_sendWhatsappText(
						$mobile,
						[
							'template'		=> '1030534948333794',
							'parameters'	=> [
								ucfirst($author_name[0]),
								strtoupper('most popular author in school certificate'),
								$book_info['name'],
								$my_certificates_url
							]
						]
					);

					$subject = ucfirst($author_name[0]) . ' Congratulations, You are the WINNER!';

					$content = '<p>Hey <strong>'.ucfirst($author_name[0]).'</strong></p>
<p>Congratulations, You WON!!</p>
<p>You have won the <strong>'.strtoupper('most popular author in school certificate').'</strong> with your book, <strong>'.$book_info['name'].'</strong>.</p>
<p>You can download your e-certificates from the <strong>My Certificate</strong> section on your profile <strong>'.$my_certificates_url.'</strong></p><br />
<p>Well done!</p>
<p>National Young Authors Fair, India</p>';

					$this->alert_model->email(
						$email,
						$subject,
						$content,
						[],
						[]
					);
				}
			}
		}

		pr(count($popular_school_result), 1);
	}

	public function createPopularCertificateCity() {
		return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('Alert_model', 'alert_model');

		$popular_city_result = [];
		// $cities = $this->city_model->get_all(['country_id' => 1]);
		// foreach ($cities['rows'] ?? [] as $city) {
		// 	$filter_data = [];
		// 	$filter_data['site_code'] = 'NYAFIND2022';
		// 	/*$filter_data['start'] = 0;
		// 	$filter_data['limit'] = 1;
		// 	$filter_data['quantity_ge'] = 20;
		// 	$filter_data['quantity_le'] = 39;*/
		// 	$filter_data['quantity_ge'] = 39;
		// 	$filter_data['end_date'] = '2023-03-15 21:00:00';
		// 	$filter_data['city_id'] = $city['id'];

		// 	if(!empty($city_wise_result = $this->ranking_model->getRanks($filter_data))) {
		// 		/*if(!empty($city_wise_result['rows'][0])) {
		// 			$city_wise_result['rows'][0]['state'] = $city['state_id'];
		// 			$city_wise_result['rows'][0]['city_id'] = $city['id'];
		// 			$city_wise_result['rows'][0]['city'] = $city['name'];
		// 			$popular_city_result[] = $city_wise_result['rows'][0];
		// 		}*/

		// 		foreach ($city_wise_result['rows'] ?? [] as $city_result) {
		// 			$city_result['state'] = $city['state_id'];
		// 			$city_result['city_id'] = $city['id'];
		// 			$city_result['city'] = $city['name'];
		// 			$popular_city_result[] = $city_result;
		// 		}
		// 	}
		// }

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		/*$filter_data['start'] = 0;
		$filter_data['limit'] = 1;
		$filter_data['quantity_ge'] = 20;
		$filter_data['quantity_le'] = 39;*/
		$filter_data['quantity_ge'] = 40;
		$filter_data['end_date'] = '2023-03-15 21:00:00';

		if(!empty($city_wise_result = $this->ranking_model->getRanks($filter_data))) {
			/*if(!empty($city_wise_result['rows'][0])) {
				$city_wise_result['rows'][0]['state'] = $city['state_id'];
				$city_wise_result['rows'][0]['city_id'] = $city['id'];
				$city_wise_result['rows'][0]['city'] = $city['name'];
				$popular_city_result[] = $city_wise_result['rows'][0];
			}*/

			foreach ($city_wise_result['rows'] ?? [] as $city_result) {
				$city_result['state'] = $city['state_id'];
				$city_result['city_id'] = $city['id'];
				$city_result['city'] = $city['name'];
				$popular_city_result[] = $city_result;
			}
		}

		foreach(array_chunk($popular_city_result, 100) as $popular_city_res) {
			foreach ($popular_city_res as $book_info) {
				$user_info = $this->user_model->get($book_info['user_id']);

				$mobile = $user_info['mobile'];
				$email = $user_info['email'];

				$image_name = $this->certificate_model->createPopularAuthorCertificate($book_info, 'most_popular_author_in_city_cert');
				if($image_name) {
					$save = [];
					$save['book_id'] 	= $book_info['id'];
					$save['site_id'] 	= $user_info['site_id'];
					$save['user_id'] 	= $book_info['user_id'];
					$save['type'] 		= 'most_popular_author_in_city_cert';
					$save['name'] 		= $image_name;

					$this->certificate_model->add($save);

					$my_certificates_url = USER_URL . 'account/mycertificates';
					$author_name = explode(" ", trim($book_info['author_name']));

					self::_sendWhatsappText(
						$mobile,
						[
							'template'		=> '1030534948333794',
							'parameters'	=> [
								ucfirst($author_name[0]),
								strtoupper('most popular author in city certificate'),
								$book_info['name'],
								$my_certificates_url
							]
						]
					);

					$subject = ucfirst($author_name[0]) . ' Congratulations, You are the WINNER!';

					$content = '<p>Hey <strong>'.ucfirst($author_name[0]).'</strong></p>
<p>Congratulations, You WON!!</p>
<p>You have won the <strong>'.strtoupper('most popular author in city certificate').'</strong> with your book, <strong>'.$book_info['name'].'</strong>.</p>
<p>You can download your e-certificates from the <strong>My Certificate</strong> section on your profile <strong>'.$my_certificates_url.'</strong></p><br />
<p>Well done!</p>
<p>National Young Authors Fair, India</p>';

					$this->alert_model->email(
						$email,
						$subject,
						$content,
						[],
						[]
					);
				}
			}
		}

		pr(count($popular_city_result), 1);
	}

	public function createPopularCertificateState() {
		return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('Alert_model', 'alert_model');

		$popular_state_result = [];
		$states = $this->state_model->get_all(['country_id' => 1, 'sort' => 'name', 'order' => 'ASC']);
		foreach ($states['rows'] ?? [] as $state) {
			$filter_data = [];
			$filter_data['site_code'] = 'NYAFIND2022';
			/*$filter_data['start'] = 0;
			$filter_data['limit'] = 1;
			$filter_data['quantity_ge'] = 40;
			$filter_data['quantity_le'] = 49;*/
			$filter_data['quantity_ge'] = 49;
			$filter_data['end_date'] = '2023-03-15 21:00:00';
			$filter_data['state_id'] = $state['id'];

			if(!empty($state_wise_result = $this->ranking_model->getRanks($filter_data))) {
				/*if(!empty($state_wise_result['rows'][0])) {
					$state_wise_result['rows'][0]['state_id'] = $state['id'];
					$state_wise_result['rows'][0]['state'] = $state['name'];
					$popular_state_result[] = $state_wise_result['rows'][0];
				}*/

				foreach ($state_wise_result['rows'] ?? [] as $state_result) {
					$state_result['state_id'] = $state['id'];
					$state_result['state'] = $state['name'];
					$popular_state_result[] = $state_result;
				}
			}
		}

		$total_alert = 0;
		foreach(array_chunk($popular_state_result, 50) as $popular_state_res) {
			foreach ($popular_state_res as $book_info) {
				$user_info = $this->user_model->get($book_info['user_id']);

				$mobile = $user_info['mobile'];
				$email = $user_info['email'];

				$image_name = $this->certificate_model->createPopularAuthorCertificate($book_info, 'most_popular_author_in_state_cert');
				if($image_name) {
					$save = [];
					$save['book_id'] 	= $book_info['id'];
					$save['site_id'] 	= $user_info['site_id'];
					$save['user_id'] 	= $book_info['user_id'];
					$save['type'] 		= 'most_popular_author_in_state_cert';
					$save['name'] 		= $image_name;

					$this->certificate_model->add($save);

					$my_certificates_url = USER_URL . 'account/mycertificates';
					$author_name = explode(" ", trim($book_info['author_name']));

					self::_sendWhatsappText(
						$mobile,
						[
							'template'		=> '1030534948333794',
							'parameters'	=> [
								ucfirst($author_name[0]),
								strtoupper('most popular author in state certificate'),
								$book_info['name'],
								$my_certificates_url
							]
						]
					);

					$subject = ucfirst($author_name[0]) . ' Congratulations, You are the WINNER!';

					$content = '<p>Hey <strong>'.ucfirst($author_name[0]).'</strong></p>
<p>Congratulations, You WON!!</p>
<p>You have won the <strong>'.strtoupper('most popular author in state certificate').'</strong> with your book, <strong>'.$book_info['name'].'</strong>.</p>
<p>You can download your e-certificates from the <strong>My Certificate</strong> section on your profile <strong>'.$my_certificates_url.'</strong></p><br />
<p>Well done!</p>
<p>National Young Authors Fair, India</p>';

					$this->alert_model->email(
						$email,
						$subject,
						$content,
						[],
						[]
					);

					$total_alert++;
				}
			}
		}

		pr($total_alert);
		pr(count($popular_state_result));
		// pr($popular_state_result, 1);
	}

	public function createPopularCertificateIndia() {
		return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('Alert_model', 'alert_model');

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = 0;
		$filter_data['limit'] = 1;
		$filter_data['quantity_ge'] = 1;
		$filter_data['end_date'] = '2023-03-15 21:00:00';
		$most_popular_india_result = $this->ranking_model->getRanks($filter_data);
		$most_popular_india_result = $most_popular_india_result['rows'] ?? [];

		foreach ($most_popular_india_result as $book_info) {
			$user_info = $this->user_model->get($book_info['user_id']);

			$mobile = $user_info['mobile'];
			$email = $user_info['email'];

			$image_name = $this->certificate_model->createPopularAuthorCertificate($book_info, 'most_popular_author_in_india_cert');
			if($image_name) {
				$save = [];
				$save['book_id'] 	= $book_info['id'];
				$save['site_id'] 	= $user_info['site_id'];
				$save['user_id'] 	= $book_info['user_id'];
				$save['type'] 		= 'most_popular_author_in_india_cert';
				$save['name'] 		= $image_name;

				$this->certificate_model->add($save);

				$my_certificates_url = USER_URL . 'account/mycertificates';
				$author_name = explode(" ", trim($book_info['author_name']));

				self::_sendWhatsappText(
					$mobile,
					[
						'template'		=> '1030534948333794',
						'parameters'	=> [
							ucfirst($author_name[0]),
							strtoupper('most popular author in india'),
							$book_info['name'],
							$my_certificates_url
						]
					]
				);

				$subject = ucfirst($author_name[0]) . ' Congratulations, You are the WINNER!';

				$content = '<p>Hey <strong>'.ucfirst($author_name[0]).'</strong></p>
<p>Congratulations, You WON!!</p>
<p>You have won the <strong>'.strtoupper('most popular author in india').'</strong> with your book, <strong>'.$book_info['name'].'</strong>.</p>
<p>You can download your e-certificates from the <strong>My Certificate</strong> section on your profile <strong>'.$my_certificates_url.'</strong></p><br />
<p>Well done!</p>
<p>National Young Authors Fair, India</p>';

				$this->alert_model->email(
					$email,
					$subject,
					$content,
					[],
					[]
				);
			}
		}

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = 1;
		$filter_data['limit'] = 10;
		$filter_data['quantity_ge'] = 1;
		$filter_data['end_date'] = '2023-03-15 21:00:00';
		$popular_india_result = $this->ranking_model->getRanks($filter_data);
		$popular_india_result = $popular_india_result['rows'] ?? [];

		foreach ($popular_india_result as $book_info) {
			$user_info = $this->user_model->get($book_info['user_id']);

			$mobile = $user_info['mobile'];
			$email = $user_info['email'];

			$image_name = $this->certificate_model->createPopularAuthorCertificate($book_info, 'popular_author_in_india_cert');
			if($image_name) {
				$save = [];
				$save['book_id'] 	= $book_info['id'];
				$save['site_id'] 	= $user_info['site_id'];
				$save['user_id'] 	= $book_info['user_id'];
				$save['type'] 		= 'popular_author_in_india_cert';
				$save['name'] 		= $image_name;

				$this->certificate_model->add($save);

				$my_certificates_url = USER_URL . 'account/mycertificates';
				$author_name = explode(" ", trim($book_info['author_name']));

				self::_sendWhatsappText(
					$mobile,
					[
						'template'		=> '1030534948333794',
						'parameters'	=> [
							ucfirst($author_name[0]),
							strtoupper('popular author in india'),
							$book_info['name'],
							$my_certificates_url
						]
					]
				);

				$subject = ucfirst($author_name[0]) . ' Congratulations, You are the WINNER!';

				$content = '<p>Hey <strong>'.ucfirst($author_name[0]).'</strong></p>
<p>Congratulations, You WON!!</p>
<p>You have won the <strong>'.strtoupper('popular author in india').'</strong> with your book, <strong>'.$book_info['name'].'</strong>.</p>
<p>You can download your e-certificates from the <strong>My Certificate</strong> section on your profile <strong>'.$my_certificates_url.'</strong></p><br />
<p>Well done!</p>
<p>National Young Authors Fair, India</p>';

				$this->alert_model->email(
					$email,
					$subject,
					$content,
					[],
					[]
				);
			}
		}

		pr(count($most_popular_india_result));
		pr(count($popular_india_result), 1);
	}

	public function getSchoolInvite() {

		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('school/SchoolDetailsInvite_model', 'school_details_invite_model');

		if(ENVIRONMENT === 'production') {
			$site_ids = [
				162,156031,151,129,327,561,156,155559,38,155907,334,74307,135907,2015,146473,167,153614,148067,117,156036,1935,2206,155838,72538,144135,1265,119,1425,73881,534,156313,135586,1399,73,1190,96,71745,1267,74358,551,137699
			];
		} else {
			$site_ids = [
				853,888,886,163,170,203,222,252,855,290,210,299,352,348,303,300,205,50,35,46,743,807,235,902,98,168,897,879,891,372,206,153,884,217,165,96,889,281,45,555,71,100,143,40510,2313,111,13,162,123,40511,149,142,269,2325,38,230
			];
		}

		$filter_data = [];
		$filter_data['site_ids'] = $site_ids;

		$results = $this->site_model->get_all($filter_data)['rows'] ?? [];

		foreach ($results as $site_info) {

			$invite_id = 0;

			if (
				empty($school_details_invite_info = $this->db->get_where('school_details_nyaf_invites', [
					'site_id'	=> $site_info['id'],
					'event_id'	=> 10
				])->row_array())
			) {
				$save = [
					'site_id'		=> $site_info['id'],
					'status'		=> 0,
					'event_id'		=> 10,
				];

				$invite_id = $this->school_details_invite_model->add($save);
			}

			$id = $school_details_invite_info['id'] ?? $invite_id;

			if(!$id) {
				continue;
			}

			$code = 'schoolDetailsInvite';

			if(empty($this->cron_model->getByCode($code . '_' . $id))) {
				$this->cron_model->add([
					'code'			=> $code . '_' . $id,
					'site_id'		=> 1,
					'action'		=> 'alert_model->' . $code,
					'data'			=> [$id],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
						? '+10 minutes'
						: '+1 minutes'
					))
				]);
			} else {
				$this->cron_model->editByCode($code . '_' . $id, [
					'data' => [$id],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime(ENVIRONMENT === 'production'
						? '+10 minutes'
						: '+1 minutes'
					)),
					'status' => 0
				]);
			}
		}
	}

	public function downloadTop200CertificateZip() {
		return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = 0;
		$filter_data['limit'] = 200;
		$filter_data['quantity_ge'] = 50;
		$filter_data['end_date'] = '2023-03-15 21:00:00';
		$most_popular_200_result = $this->ranking_model->getRanks($filter_data);
		$most_popular_200_result = $most_popular_200_result['rows'] ?? [];

		$results = [];

		$s3_pdf_url = 'https://authorcertificates.s3.ap-south-1.amazonaws.com/authorcertificates/pdf/';

		foreach ($most_popular_200_result as $key => $result) {
			if($key == 0) {
				$results[] = $s3_pdf_url . 'most_popular_author_in_india_cert_user_'.$result['user_id'].'_'.$result['id'].'.pdf';
			}
			if($key > 0 && $key <= 10) {
				$results[] = $s3_pdf_url . 'popular_author_in_india_cert_user_'.$result['user_id'].'_'.$result['id'].'.pdf';
			}
			$results[] = $s3_pdf_url . 'writing_prodigy_author_cert_user_'.$result['user_id'].'_'.$result['id'].'.pdf';
		}

		if(empty($results))
			return;

		$this->load->library('zip');

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'nyaf_authors_certificates.zip';

		foreach ($results as $result) {
			$this->zip->add_data(str_replace($s3_pdf_url, '', $result), @file_get_contents($result));
		}

		$this->zip->download($filename);

		is_file($filename) && unlink($filename);
	}

	public function downloadNyafAllAuthorPDF() {
		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_all_author_pdf_template', [], true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper(array(0, 0, 1248, 1824), 'potrait');
		$dompdf->render();
		$file = 'uploads/eventpass/pdfs/entry_pass_authors.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'entry_passes_authors_pdf.zip';

		$this->load->library('zip');
		$this->zip->add_data('entry_pass_authors.pdf', @file_get_contents(base_url($file)));
		$this->zip->download($filename);

		is_file($filename) && unlink($filename);

		is_file(FCPATH.$file) && unlink(FCPATH.$file);
	}

	public function downloadNyafAllSchoolPDF() {
		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_all_school_pdf_template', [], true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper(array(0, 0, 1248, 1824), 'potrait');
		$dompdf->render();
		$file = 'uploads/eventpass/pdfs/entry_pass_schools.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'entry_passes_schools_pdf.zip';

		$this->load->library('zip');
		$this->zip->add_data('entry_pass_schools.pdf', @file_get_contents(base_url($file)));
		$this->zip->download($filename);

		is_file($filename) && unlink($filename);

		is_file(FCPATH.$file) && unlink(FCPATH.$file);
	}

	public function download_nyaf_author_images_zip() {
		$this->load->library('zip');

		$this->load->model('user/UserDetailsGuest_model', 'user_details_guest_model');

		$results = $this->user_details_guest_model->get_user_details_guest();

		if(empty($results)) {
			die(_l('no_record_found'));
		}

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'nyaf_authors_images.zip';

		foreach ($results as $result) {
			$book_info = $this->book_model->get($result['book_id']);

			$author_name_image = preg_replace("/[^a-zA-Z_]+/", "", str_replace(' ', '_', trim($book_info['author_name']))) . '_' . (int)$result['user_id'] . '.png';

			$user_image = 'user_' . (int)$result['user_id'] . '.png';

			if($result = @file_get_contents($this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf') . (ENVIRONMENT === 'production' ? '' : 'test/') . $user_image))
				$this->zip->add_data($author_name_image, $result);
		}

		$this->zip->download($filename);

		is_file($filename) && unlink($filename);
	}

	public function getNyafAuthorAddress() {
		if (!empty($results = $this->db->get('user_certificate_address')->result_array())) {
			$rows = [];

			// $table = '<table border="2"><tr><th>#</th><th>User Name</th><th>Book Name</th><th>Author Name</th><th>Full Name</th><th>Mobile</th><th>Address</th><th>Delivery Date</th><th>Landmark</th><th>Pincode</th><th>Date Added</th></tr>';

			$columns = array_fill_keys(['BookName','AuthorName','OriginArea','CustomerCode','CustomerName','CustomerAddress1','CustomerAddress2','CustomerAddress3','CustomerPincode','CustomerTelephone','CustomerMobile','CustomerEmailID','Sender','VendorCode','CustomerGSTNumber','ConsigneeName','ConsigneeAttention','ConsigneeAddress1','ConsigneeAddress2','ConsigneeAddress3','ConsigneePincode','ConsigneeTelephone','ConsigneeMobile','ConsigneeEmailID','ProductCode','SubProductCode','ProductType','PackType','PieceCount','ActualWeight','DeclaredValue','CollectableAmount','InvoiceNo','CreditReferenceNo','CommodityDetail1','CommodityDetail2','CommodityDetail3','SpecialInstruction','Length','Breadth','Height','Count','PickupDate','PickupTime','PreferredDeliveryDate','ItemCount','ItemDetails','IsToPayCustomer','IsReversePickup','RegisterPickup','WaybillNumber','DestinationArea','DestinationLocation','ErrorMessage','IsError','IsErrorInPickup','ErrorMessageForPU','PickupMode','CustomerRequestPUDate','Officecutofftime','ShipmentPickupDate','PickupTokenNumber','PreferredPickupTimeSlot','PickupType','IsForcePickup','DeliveryTimeSlot','AWBNo','CustomerLatitude','CustomerLongitude','CustomerAddressinfo','CustomerFiscalIDTypeonlyforIntlEcommerceproduct','CustomerFiscalIDonlyforIntlEcommerceproduct','CustomerRegistrationNumber','CustomerRegistrationNumberIssuerCountryCode','CustomerRegistrationNumberTypeCode','CustomerBusinessPartyTypeCode','ConsigneeLatitude','ConsigneeLongitude','ConsigneeAddressinfo','ConsigneeCountryCode','ConsigneeStateCode','ConsigneeCityName','ConsigneeGSTNumber','ConsigneeMaskedContactNumber','ConsigneeIDTypeuseforIntlEcommerce','ConsigneeIDuseforIntlEcommerce','ConsigneeFiscalIDTypeuseforIntlEcommerce','ConsigneeFiscalIDuseforIntlEcommerce','ConsigneeAddressType','ConsingeeFederalTaxIduseforBrazil ','ConsingeeStateTaxIdforBrazil ','ConsingeeRegistrationNumberuseforEurpean','ConsingeeRegistrationNumberTypeCodeuseforEurpean','ConsingeeRegistrationNumberIssuerCountryCodeuseforEurpean','ConsigneeBusinessPartyTypeCode','AvailableTiming','AvailableDays','Total_IGST_Paid','SupplyOfIGST','SupplyOfwoIGST','IncotermCode','ReturnAddress1','ReturnAddress2','ReturnAddress3','ReturnPincode','ReturnTelephone','ReturnMobile','ReturnEmailID','ReturnContact','ManifestNumber','ReturnLatitude','ReturnLongitude','ReturnAddressinfo','IsChequeDD','InsurancePaidBy','FavouringName','PayableAt','ParcelShopCode','ForwardAWBNo','ForwardLogisticCompName','CreditReferenceNo2','CreditReferenceNo3','IsPartialPickup','TotalCashPaytoCustomer','DeferredDeliveryDays','IsDedicatedDeliveryNetwork','CustomerEDD','ExchangeWaybillNo','OTPBasedDelivery','OTPCode','IsIntlEcomCSBUser','ExportImportCode','TermsOfTrade','IsEcomUser','InsuranceAmount','AuthorizedDealerCode','CurrencyCode','OrderURL','EsellerPlatformName','BillingReference1','BillingReference2','MarketplaceURL','MarketplaceName','BillToCompanyName','BillToContactName','BillToAddressLine1','BillToCity','BillToPostcode','BillToSuburb','BillToState','BillToCountryName','BillToCountryCode','BillToPhoneNumber','BillToFederalTaxID','ExporterCompanyName','ExporterSuiteDepartmentName','ExporterAddressLine1','ExporterAddressLine2','ExporterAddressLine3','ExporterCity','ExporterDivision','ExporterDivisionCode','ExporterPostalCode','ExporterCountryCode','ExporterCountryName','ExporterPersonName','ExporterPhoneNumber','ExporterFaxNumber','ExporterEmail','ExporterMobilePhoneNumber','ExporterRegistrationNumber','ExporterRegistrationNumberTypeCode','ExporterRegistrationNumberIssuerCountryCode','ExporterBusinessPartyTypeCode','SignatureName','SignatureTitle','ECCN','FreightCharge','InsurenceCharge','CessCharge','ReverseCharge','PayerGSTVAT','AdditionalDeclaration','NotificationMessage','IsCargoShipment','ExportReason','BankAccountNumber','GovNongovType','NFEIFlag','TransactionAmount','AvailableAmountForBooking','AvailableBalance','ClusterCode','IDColumn'],  null);

			$i = 0;

			foreach ($results as $key => $result) {
				// $user_info = $this->db->get_where('users', [
				// 	'id'	=> $result['user_id']
				// ])->row_array();

				$book_info = $this->db->get_where('book', [
					'id'	=> $result['book_id']
				])->row_array();

				$json = json_decode($result['address'], 1);
				// $table .= '<tr><td>'.($key+1).'</td><td>'.trim($user_info['first_name'].' '.$user_info['last_name']).'</td><td>'.$book_info['name'].'</td><td>'.$book_info['author_name'].'</td><td>'.$json['full_name'].'</td><td>'.$json['mobile'].'</td><td>'.$json['address'].'</td><td>'.$json['delivery_date'].'</td><td>'.$json['landmark'].'</td><td>'.$json['pincode'].'</td><td>'.$result['date_added'].'</td></tr>';

				$unique_id = 'BB' . date('hi') . sprintf('%04d', $i+1);

				$rows[$i] = array_merge($columns, [
					'BookName'				=> $book_info['name'],
					'AuthorName'			=> $book_info['author_name'],
					'OriginArea'			=> 'GGN',
					'CustomerCode'			=> '988551',
					'CustomerName'			=> 'YOU BOOKS EDTECH INDIA P LTD',
					'CustomerAddress1'		=> '2117, TOWER - 1, DLF CORPORATE GREENS',
					'CustomerAddress2'		=> 'SEC 74',
					'CustomerAddress3'		=> 'GURGAON, HARYANA',
					'CustomerPincode'		=> 122004,
					'CustomerTelephone'		=> 9818651520,
					'CustomerMobile'		=> 9818651520,
					'ConsigneeName'			=> $json['full_name'],
					'ConsigneeAddress1'		=> $json['address'],
					'ConsigneeAddress2'		=> $json['landmark'],
					'ConsigneePincode'		=> (int)$json['pincode'],
					'ConsigneeMobile'		=> (strlen($json['mobile']) == '12') ? (int)substr($json['mobile'],2,10) : (int)$json['mobile'],
					'ProductCode'			=> 'D',
					'ProductType'			=> 'DOX',
					'PieceCount'			=> 1,
					'ActualWeight'			=> '1.25',
					'DeclaredValue'			=> '500',
					'CreditReferenceNo'		=> $unique_id,
					'CommodityDetail1'		=> 'Certificate',
					'Length'				=> '50',
					'Breadth'				=> '37',
					'Height'				=> '6',
					'Count'					=> 1,
					'PickupDate'			=> date('m/d/Y'),
					'PickupTime'			=> '1800',
					'ItemCount'				=> 1,
					'IsToPayCustomer'		=> 0,
					'IsReversePickup'		=> 0,
					'RegisterPickup'		=> 1,
					'ClusterCode'			=> 1,
					'IDColumn'				=> $i+1
				]);

				$i++;
			}

			// $table .= '</table>';
			// echo $table;

			// pr($rows);

			$filename = 'author_certificates_address_' . date('Y_m_d_H_i_s') . '.csv';

			if (!headers_sent()) {
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' .  $filename . '"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');

				if (ob_get_level()) {
					ob_end_clean();
				}
			} else {
				exit('Error: Headers already sent out!');
			}

			$headers = isset($rows[0]) ? array_keys($rows[0]) : [];

			if (!$headers) {
				exit(_l('error_empty'));
			}

			$fp = fopen('php://output', 'w');

			self::_writeRowToCsv($rows, $fp, $headers);

			fclose($fp);

			exit();
		}
	}

	public function getNyafSchoolAddress() {
		if (!empty($results = $this->db->get('school_certificate_address')->result_array())) {
			$rows = [];

			// $table = '<table border="2"><tr><th>#</th><th>School Name</th><th>Full Name</th><th>Mobile</th><th>Address</th><th>Delivery Date</th><th>Landmark</th><th>Pincode</th><th>Date Added</th></tr>';

			$columns = array_fill_keys(['SchoolName','SiteCode','OriginArea','CustomerCode','CustomerName','CustomerAddress1','CustomerAddress2','CustomerAddress3','CustomerPincode','CustomerTelephone','CustomerMobile','CustomerEmailID','Sender','VendorCode','CustomerGSTNumber','ConsigneeName','ConsigneeAttention','ConsigneeAddress1','ConsigneeAddress2','ConsigneeAddress3','ConsigneePincode','ConsigneeTelephone','ConsigneeMobile','ConsigneeEmailID','ProductCode','SubProductCode','ProductType','PackType','PieceCount','ActualWeight','DeclaredValue','CollectableAmount','InvoiceNo','CreditReferenceNo','CommodityDetail1','CommodityDetail2','CommodityDetail3','SpecialInstruction','Length','Breadth','Height','Count','PickupDate','PickupTime','PreferredDeliveryDate','ItemCount','ItemDetails','IsToPayCustomer','IsReversePickup','RegisterPickup','WaybillNumber','DestinationArea','DestinationLocation','ErrorMessage','IsError','IsErrorInPickup','ErrorMessageForPU','PickupMode','CustomerRequestPUDate','Officecutofftime','ShipmentPickupDate','PickupTokenNumber','PreferredPickupTimeSlot','PickupType','IsForcePickup','DeliveryTimeSlot','AWBNo','CustomerLatitude','CustomerLongitude','CustomerAddressinfo','CustomerFiscalIDTypeonlyforIntlEcommerceproduct','CustomerFiscalIDonlyforIntlEcommerceproduct','CustomerRegistrationNumber','CustomerRegistrationNumberIssuerCountryCode','CustomerRegistrationNumberTypeCode','CustomerBusinessPartyTypeCode','ConsigneeLatitude','ConsigneeLongitude','ConsigneeAddressinfo','ConsigneeCountryCode','ConsigneeStateCode','ConsigneeCityName','ConsigneeGSTNumber','ConsigneeMaskedContactNumber','ConsigneeIDTypeuseforIntlEcommerce','ConsigneeIDuseforIntlEcommerce','ConsigneeFiscalIDTypeuseforIntlEcommerce','ConsigneeFiscalIDuseforIntlEcommerce','ConsigneeAddressType','ConsingeeFederalTaxIduseforBrazil ','ConsingeeStateTaxIdforBrazil ','ConsingeeRegistrationNumberuseforEurpean','ConsingeeRegistrationNumberTypeCodeuseforEurpean','ConsingeeRegistrationNumberIssuerCountryCodeuseforEurpean','ConsigneeBusinessPartyTypeCode','AvailableTiming','AvailableDays','Total_IGST_Paid','SupplyOfIGST','SupplyOfwoIGST','IncotermCode','ReturnAddress1','ReturnAddress2','ReturnAddress3','ReturnPincode','ReturnTelephone','ReturnMobile','ReturnEmailID','ReturnContact','ManifestNumber','ReturnLatitude','ReturnLongitude','ReturnAddressinfo','IsChequeDD','InsurancePaidBy','FavouringName','PayableAt','ParcelShopCode','ForwardAWBNo','ForwardLogisticCompName','CreditReferenceNo2','CreditReferenceNo3','IsPartialPickup','TotalCashPaytoCustomer','DeferredDeliveryDays','IsDedicatedDeliveryNetwork','CustomerEDD','ExchangeWaybillNo','OTPBasedDelivery','OTPCode','IsIntlEcomCSBUser','ExportImportCode','TermsOfTrade','IsEcomUser','InsuranceAmount','AuthorizedDealerCode','CurrencyCode','OrderURL','EsellerPlatformName','BillingReference1','BillingReference2','MarketplaceURL','MarketplaceName','BillToCompanyName','BillToContactName','BillToAddressLine1','BillToCity','BillToPostcode','BillToSuburb','BillToState','BillToCountryName','BillToCountryCode','BillToPhoneNumber','BillToFederalTaxID','ExporterCompanyName','ExporterSuiteDepartmentName','ExporterAddressLine1','ExporterAddressLine2','ExporterAddressLine3','ExporterCity','ExporterDivision','ExporterDivisionCode','ExporterPostalCode','ExporterCountryCode','ExporterCountryName','ExporterPersonName','ExporterPhoneNumber','ExporterFaxNumber','ExporterEmail','ExporterMobilePhoneNumber','ExporterRegistrationNumber','ExporterRegistrationNumberTypeCode','ExporterRegistrationNumberIssuerCountryCode','ExporterBusinessPartyTypeCode','SignatureName','SignatureTitle','ECCN','FreightCharge','InsurenceCharge','CessCharge','ReverseCharge','PayerGSTVAT','AdditionalDeclaration','NotificationMessage','IsCargoShipment','ExportReason','BankAccountNumber','GovNongovType','NFEIFlag','TransactionAmount','AvailableAmountForBooking','AvailableBalance','ClusterCode','IDColumn'],  null);

			$i = 0;

			foreach ($results as $key => $result) {
				$site_info = $this->db->get_where('site', [
					'id'	=> $result['site_id']
				])->row_array();

				$json = json_decode($result['address'], 1);
				// $table .= '<tr><td>'.($key+1).'</td><td>'.$site_info['name'].'</td><td>'.$json['full_name'].'</td><td>'.$json['mobile'].'</td><td>'.$json['address'].'</td><td>'.$json['delivery_date'].'</td><td>'.$json['landmark'].'</td><td>'.$json['pincode'].'</td><td>'.$result['date_added'].'</td></tr>';

				$unique_id = 'BB' . date('hi') . sprintf('%04d', $i+1);

				$rows[$i] = array_merge($columns, [
					'SchoolName'			=> $site_info['name'],
					'SiteCode'				=> $site_info['site_code'],
					'OriginArea'			=> 'GGN',
					'CustomerCode'			=> '988551',
					'CustomerName'			=> 'YOU BOOKS EDTECH INDIA P LTD',
					'CustomerAddress1'		=> '2117, TOWER - 1, DLF CORPORATE GREENS',
					'CustomerAddress2'		=> 'SEC 74',
					'CustomerAddress3'		=> 'GURGAON, HARYANA',
					'CustomerPincode'		=> 122004,
					'CustomerTelephone'		=> 9818651520,
					'CustomerMobile'		=> 9818651520,
					'ConsigneeName'			=> $json['full_name'],
					'ConsigneeAddress1'		=> $json['address'],
					'ConsigneeAddress2'		=> $json['landmark'],
					'ConsigneePincode'		=> (int)$json['pincode'],
					'ConsigneeMobile'		=> (strlen($json['mobile']) == '12') ? (int)substr($json['mobile'],2,10) : (int)$json['mobile'],
					'ProductCode'			=> 'D',
					'ProductType'			=> 'DOX',
					'PieceCount'			=> 1,
					'ActualWeight'			=> '1.25',
					'DeclaredValue'			=> '500',
					'CreditReferenceNo'		=> $unique_id,
					'CommodityDetail1'		=> 'Certificate',
					'Length'				=> '50',
					'Breadth'				=> '37',
					'Height'				=> '6',
					'Count'					=> 1,
					'PickupDate'			=> date('m/d/Y'),
					'PickupTime'			=> '1800',
					'ItemCount'				=> 1,
					'IsToPayCustomer'		=> 0,
					'IsReversePickup'		=> 0,
					'RegisterPickup'		=> 1,
					'ClusterCode'			=> 1,
					'IDColumn'				=> $i+1,
					'BookName'				=> $json['landmark'],
					'AuthorName'			=> $json['landmark']
				]);

				$i++;
			}

			// $table .= '</table>';
			// echo $table;

			// pr($rows);

			$filename = 'school_certificates_address_' . date('Y_m_d_H_i_s') . '.csv';

			if (!headers_sent()) {
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="' .  $filename . '"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');

				if (ob_get_level()) {
					ob_end_clean();
				}
			} else {
				exit('Error: Headers already sent out!');
			}

			$headers = isset($rows[0]) ? array_keys($rows[0]) : [];

			if (!$headers) {
				exit(_l('error_empty'));
			}

			$fp = fopen('php://output', 'w');

			self::_writeRowToCsv($rows, $fp, $headers);

			fclose($fp);

			exit();
		}
	}

	private function _writeRowToCsv($results = [], $fp = null, $headers = []) {
		fputs($fp, "\xEF\xBB\xBF");

		fputcsv($fp, $headers);

		if (is_array($results) && $results && is_resource($fp) && is_array($headers) && $headers) {
			foreach ($results as $result) {
				$row = [];

				foreach ($headers as $header) {
					if (!empty($result[$header]) && is_array($result[$header])) {
						//$this->writeRowToCsv($result[$header], $fp, array_keys($result[$header]));
					} else {
						$row[] = !empty($result[$header]) ? $result[$header] : '';
					}
				}

				fputcsv($fp, $row);
			}
		}
	}

	public function testTopRanksSchoolWise($start = '', $limit = 1) {
		return;

		if(!is_numeric($start) || empty($limit) || !is_numeric($limit))
			return;

		$this->load->model('ranking/Ranking_model', 'ranking_model');
		$this->load->model('common/Grade_model', 'grade_model');
		$this->load->model('common/Section_model', 'section_model');

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = $start;
		$filter_data['limit'] = $limit;
		$filter_data['quantity_ge'] = 50;
		$filter_data['end_date'] = '2023-03-15 21:00:00';

		$result1 = $this->ranking_model->getRanks($filter_data);

		$jury_book_ids = [59629,36994,7165,8919,25306,2532,26328,48254,67485,64125,28550,17642,27886,15929,24889,3654,26099,25100,49697,35422,61202,52759,75236,75819,28036,30086,9330,37498,61826,19741,77677,45020,51057,59006,47023,62191,30402,11405,62051,57157,16225,41601,35149,59402,60365,54940];

		$filter_data = [];
		$filter_data['site_code'] = 'NYAFIND2022';
		$filter_data['start'] = $start;
		$filter_data['limit'] = $limit;
		$filter_data['quantity_ge'] = 0;
		$filter_data['end_date'] = '2023-03-15 21:00:00';
		$filter_data['book_ids'] = $jury_book_ids;

		$result2 = $this->ranking_model->getRanks($filter_data);

		$result = array_merge($result1['rows'] ?? [], $result2['rows'] ?? []);

		$result = array_values(array_map("unserialize", array_unique(array_map("serialize", $result))));

		// pr($result, 1);

		$school_data = [];
		$i = 0;
		foreach ($result as $book_info) {
			$user_info = $this->student_model->get($book_info['user_id']);

			$grade_info = $this->grade_model->get($user_info['grade_id']);

			$section_info = $this->section_model->get($user_info['section_id']);

			$book_written = $this->book_model->get_all([
				'user_id'	   	=> $user_info['id'],
				'grade_id'	  	=> $grade_info['id'],
				'section_id'	=> $section_info['id'],
			])['total'];

			$book_published = $this->book_model->get_all([
				'user_id'	   	=> $user_info['id'],
				'grade_id'	  	=> $grade_info['id'],
				'section_id'	=> $section_info['id'],
				'ne_status'	 	=> 0,
			])['total'];

			$site_info = $this->site_model->get($user_info['site_id']);

			$certificate = 'writing prodigy author certificate';
			$rank = $i + 1;

			if(in_array($book_info['id'], $jury_book_ids)) {
				$certificate = 'The Jury’s Certificate of Excellence';
			}

			if($book_info['id'] == 59629) {
				$certificate = 'The Jury Award for Best Book';
			} else if($book_info['id'] == 36994) {
				$certificate = 'The Best Story - Teller Award';
			} else if($book_info['id'] == 7165) {
				$certificate = 'Most Inspiring Book';
			} else if($book_info['id'] == 8919) {
				$certificate = 'The Most Creative Book Award';
			} else if($book_info['id'] == 25306) {
				$certificate = 'The Most Innovative Book';
			} else {}

			if($rank == 1) {
				$certificate = 'most popular author in india certificate';
			} else if($rank > 1 && $rank < 12) {
				$certificate = 'popular author in india certificate';
			}

			$data = [
				'rank'			=> ($rank <= 325) ? $rank : '-',
				'site_id'		=> $user_info['site_id'],
				'school_email'	=> $site_info['owner_email'],
				'book_id'		=> $book_info['id'],
				'author_name'	=> !empty($book_info['author_name']) ? trim($book_info['author_name']) : trim($user_info['first_name'] . ' ' . $user_info['last_name']),
				'book_name'		=> $book_info['name'],
				'quantity'		=> $book_info['quantity'],
				'grade'			=> $grade_info['name'],
				'section'		=> $section_info['name'],
				'certificate'	=> strtoupper($certificate)
			];

			$school_data[trim($site_info['name'])][] = $data;

			$i++;
		}

		foreach ($school_data as $school_name => $students) {
			$data['school_name'] = $school_name;
			$data['students'] = $students;

			$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/SchoolWiseStudentPDF', $data, true);

			$dompdf = new Dompdf();
			// Load HTML content
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			// $dompdf->stream(); exit;
			$file = 'uploads/pdfs/National_Ranking_Authors_Report_' . $students[0]['site_id'] . '.pdf';
			$output = $dompdf->output();
			file_put_contents(FCPATH . $file, $output);

			$email = (ENVIRONMENT === 'production') ? $students[0]['school_email'] : '';

			$subject = 'BriBooks - National Young Authors Fair Winner Report';

			$content = '<p>Dear Educator,</p>
<p>We express our profound gratitude for your notable involvement in the illustrious National Young Authors Fair, India, alongside your accomplished students. Your unwavering support and motivation for the budding authors is much appreciated.</p>
<p>We are sharing a report in the form of a PDF file, highlighting the National Award Winners who have etched their names in the coveted Best Selling League.</p>
<p>We extend our heartiest congratulations to you and your distinguished wards for this exceptional achievement.</p>
<p>Best regards,</p>
<p>BriBooks Team.</p>';

			$this->alert_model->email(
				$email,
				$subject,
				$content,
				[],
				['communication@bribooks.com'],
				FCPATH . $file
			);
		}

		pr(count($school_data));
		pr($school_data, 1);
	}

	public function bookStockProcessed() {
		return;

		$results = $this->db->query("
			SELECT book_in_stock.id, book.id AS book_id, book.user_id, users.site_id, book_in_stock.book_name, book_in_stock.author_name, book_in_stock.version, book_stock.version as book_stock_version, book.version as book_version, book_in_stock.quantity, book_stock.quantity as book_stock_qty,	book_stock.`option` as book_type, users.source, users.location
			FROM book_in_stock
			JOIN book on book.name=book_in_stock.book_name AND book.author_name=book_in_stock.author_name AND book.version=book_in_stock.version
			JOIN users on users.id=book.user_id
			LEFT JOIN book_stock on book_stock.book_id=book.id AND book_stock.version=book_in_stock.version AND book_stock.quantity>0
			WHERE book_in_stock.status=0
			ORDER BY book.id ASC
		")->result_array();

		/*pr($results, 1);

		foreach ($results as $key => $item) {
			$update = [];
			$update['book_id']			= $item['book_id'];
			$update['user_id']			= $item['user_id'];
			$update['site_id']			= $item['site_id'];
			$update['status']			= '1';
			$update['coupon_code']		= 'NYAF'.$item['book_id'].'D50';
			$update['date_modified']	= date('Y-m-d H:i:s');

			$this->db->where('id', (int)$item['id']);
			$this->db->update('book_in_stock', $update);
		}*/

		pr(count($results), 1);
	}

	public function bookCouponProcessed() {
		return;

		$this->load->model('book/BookStock_model', 'book_stock_model');
		$this->load->model('order/Coupon_model', 'coupon_model');

		$results = $this->db->query("SELECT * FROM book_in_stock WHERE book_in_stock.status=1")->result_array();

		pr($results, 1);

		/*foreach ($results as $key => $item) {
			pr($item, 1);

			if(0 && empty($coupon_info = $this->coupon_model->getByCouponCode(['code' => $item['coupon_code']]))) {
				pr($item, 1);

				if(!empty($book_stock_results = $this->book_stock_model->get_all(
					[
						'book_id'	=> $item['book_id'],
						'version'	=> $item['version'],
						'option'	=> 'paperback'
					]
				)['rows'] ?? [])) {
					if($book_stock_results[0]['quantity'] != $item['quantity']) {
						$this->book_stock_model->edit($book_stock_results[0]['id'], [
							'quantity' 	=> $item['quantity']
						]);
					}
				} else {
					$this->book_stock_model->add([
						'book_id'				=> $item['book_id'],
						'version' 				=> $item['version'],
						'option' 				=> 'paperback',
						'quantity' 				=> $item['quantity'],
						'pickup_location_id	' 	=> 1,
						'status	' 				=> 1
					]);
				}

				$save = [];
				$save['name']				= $item['coupon_code'];
				$save['coupon_type']		= 'product';
				$save['item_id']			= $item['book_id'];
				$save['code']				= $item['coupon_code'];
				$save['discount_type']		= '2';
				$save['currency_code']		= 'INR';
				$save['discount']			= '50.00';
				$save['total']				= '0.00';
				$save['status']				= '1';
				$save['used_count']			= '0';
				$save['used_limit']			= '1';
				$save['book_stock_quantity']= $item['quantity'];
				$save['book_sku']			= _o_b_code($item['book_id'], $item['version'], 'paperback');
				$save['user_id']			= $item['user_id'];
				$save['date_start']			= date('Y-m-d 00:00:00');
				$save['date_end']			= date('2023-12-31 23:59:59');

				// pr($save, 1);

				// $this->coupon_model->add($save);
			}
		}*/

		pr(count($results), 1);
	}
}
