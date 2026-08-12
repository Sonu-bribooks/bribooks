<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('whatsapp');
load_trait('models/alert');

use Dompdf\Dompdf;

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;
use GeoIp2\Database\Reader;
use Meilisearch\Client;

trait Test
{
	use CommonWhatsapp, DonationAlert;

	public function buid_vote_rank() {
		return;
		$this->load->library('Vote_lib');
		$results = $this->db
			->where('_deleted', 0)
			->group_by('book_id')
			->get('event_user_vote')
			->result_array();

		foreach ($results as $value) {
			$book_id = $value['book_id'];
			$challenge_id = $value['challenge_id'];
			$this->vote_lib->updateRank($book_id, $challenge_id);
		}
	}

	public function fix_bs_rank() {
		$this->load->library('Redis_lib');
		// pr($this->redis_lib->removeFromRank('production:ranking:1:2', 4), 1);
		// pr($this->redis_lib->removeRangeRank('production:ranking:1:2', 0, 1), 1);
	}

	public function reward_ads() {
		$this->load->view('frontend/' . get_frontend_settings('theme') . '/ad_temp_2', []);
	}

	public function launch_crm($target_product = 'briminds') {
		// $this->load->library('Vote_lib');
		// $this->vote_lib->cleanRanks(36);

		// pr($this->session->userdata(), 1);
		if (!$this->session->userdata('user_id')) exit('unauthorized');

		$shared_secret = 'FEKBjHhCa;nxC:X=56%A8p$(wx1^Vv_$sKd1r&%an0U';

		$payload = [
			'email'			  	=> $this->session->userdata('user_email'),
			'name'			 	=> $this->session->userdata('name'),
			'role_id'		  	=> (int)$this->session->userdata('role_id'),
			'allowed_products' 	=> ['briminds', 'brisharks'],
			'target_product'   	=> $target_product,
			'iat'			  	=> time(),
			'exp'			  	=> time() + 60
		];

		$token = \Firebase\JWT\JWT::encode($payload, $shared_secret, 'HS256');

		$this->load->model('admin/SystemUserToken_model', 'system_user_token_model');

		$this->system_user_token_model->add([
			'user_id'	=> $this->session->userdata('user_id'),
			'token'		=> $token,
		]);

		redirect('https://crm-dev.briminds.ai/sso/login?token=' . $token);
	}

	public function testSearch() {
		try {
			$client = new Client(
				'https://bbml.bribooks.com/search',
				'bbsearchfgbcspig61965berbh4424as'
			);

			$index = $client->index('books');

			// $index->addDocuments([
			// 	[
			// 		'id' => 1,
			// 		'name' => 'Test Book',
			// 		'author' => 'Author Name',
			// 		'genre' => 'Test Genre',
			// 		'category' => 'Test Category',
			// 		'location' => 'india',
			// 		'view' => 150,
			// 		'sold' => 10,
			// 	]
			// ]);

			// $health = $client->health();
			// print_r($health);

			$result = $index->search('category');

			pr($result->getHits(), 1);


		} catch (Exception $e) {
			print_r($e->getMessage());
		}
	}

	public function getLevel($level = 1) {
		$json = [
			'grid_size' => [12, 12],
			'tile_size' => 2,
			'player' => [
				'position'  => [1, 0, 1],
				'direction' => 'NORTH'
			],
			'tiles' => [
				[1, 0, 1], [2, 0, 1], [3, 0, 1], [4, 0, 1],
				[1, 0, 2], [4, 0, 2],
				[1, 0, 3], [4, 0, 3],
				[1, 0, 4], [4, 0, 4], [5, 0, 4], [6, 0, 4], [7, 0, 4], [8, 0, 4]
			],
			'background' => [
				'type' => 'space',
				'sky_top_color' => '#1b1f3b',
				'sky_horizon_color' => '#3a3f7d',
				'sky_energy' => 0.8,
				'stars' => true,
				'star_speed' => 12,
				'float_intensity' => 0.3
			],
			'walls' => [
				[1, 1, 2],
				[11, 0, 3]
			],

			'rewards' => [
				[2, 0, 1],
				[8, 0, 4]
			]
		];

		output_json($json);
	}

	public function testgencert() {
		return;
		$this->alert_model->sendLeagueMessageCron([
			'event_id' => '241',
			'challenge_id' => '17',
			'type' => 'weekly',
			'limit' => '20',
			'need_image' => '1',
			'need_address' => '0',
		]);
	}

	public function testEncr() {
		return;
		$encoded = encrypt_data('test|hello');
		pr([decrypt_data($encoded), $encoded], 1);
	}

	public function testRankdata() {
		$data['type'] = 'state';
		$data['event_id'] = 241;
		$data['challenge_id'] = 49;

		$challenge_key = strtolower($data['type']) === 'genre' ? 'challenge_id' : sprintf('event_challenge_%s_id', strtolower($data['type']));

		$results = array_column($this->db
			->select(sprintf('distinct %s_id as item_id', strtolower($data['type'])), false)
			->where('event_id', (int)$data['event_id'])
			->where($challenge_key, (int)$data['challenge_id'])
			->where('_deleted', 0)
			->get(sprintf('user_rank_%s', strtolower($data['type'])))
			->result_array(), 'item_id');

		pr($results, 1);
	}

	public function importPincodes() {
		return;
		$rows = [];
		$header = [];

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/citydb/pincode_data.csv');
		$rows = $this->parsecsv->data;

		// pr($rows, 1);

		foreach ($rows as $key => $item) {
			$this->db->insert('pincodes', [
				'pincode'	=> $item['pincode'],
				'state'		=> ucwords(strtolower($item['state'])),
				'district'	=> ucwords(strtolower($item['district'])),
				'office'	=> ucwords(strtolower($item['office'])),
				'division'	=> ucwords(strtolower($item['division'])),
				'region'	=> ucwords(strtolower($item['region'])),
				'circle'	=> ucwords(strtolower($item['circle'])),
				'type'		=> $item['officetype'],
				'latitude'	=> $item['latitude'],
				'longitude'	=> $item['longitude'],
				'status'	=> $item['delivery'] == 'Delivery',
				'date_added'=> date('Y-m-d H:i:s'),
			]);
		}
	}

	public function generateAuthorCalendar() {
		$data = [
			'year'			=> '2026',
			'user_id'		=> '253230',
			'book_id'		=> '713051',
			'book_name'		=> 'Vihaan and his friends',
			// 'book_name'		=> str_repeat('W', 30),
			'author_name'	=> 'Vihaan Kanodia',
			// 'author_name'	=> str_repea	t('W', 40),
			'cover_image'	=> 'https://media.bribooks.com/public/AuthorCoverImages/user_cover_690f0df75891d_b0_v713051.png',
			'author_image'	=> 'https://media.bribooks.com/public/AuthorImages/author_690f0e6c65829_0_713051.png',
			'front_page'	=> 'https://media.bribooks.com/public/EventGallery/Author-Calendar/2026/front_cover_page_final_500.jpg',
			'page_1'		=> 'https://media.bribooks.com/public/EventGallery/Author-Calendar/2026/calendar_1_500.jpg',
			'page_2'		=> 'https://media.bribooks.com/public/EventGallery/Author-Calendar/2026/calendar_2_500.jpg',
			'page_3'		=> 'https://media.bribooks.com/public/EventGallery/Author-Calendar/2026/calendar_3_500.jpg',
			'page_4'		=> 'https://media.bribooks.com/public/EventGallery/Author-Calendar/2026/calendar_4_500.jpg',
			'page_5'		=> 'https://media.bribooks.com/public/EventGallery/Author-Calendar/2026/calendar_5_500.jpg',
			'page_6'		=> 'https://media.bribooks.com/public/EventGallery/Author-Calendar/2026/calendar_6_500.jpg',
		];
		$data['width'] 	= (5.5 + 0.19685) * 72;
		$data['height'] = (9 + 0.19685) * 72;
		$html 			= $this->load->view(sprintf('common/author_calendar/index'), $data, true);

		// echo $html; die;

		$dompdf = new Dompdf([
			// 'debugLayout' 	=> true,
		]);
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper(
			[
				0,
				0,
				$data['width'],
				$data['height']
			],
			'portrait'
		);

		$dompdf->render();

		$filename = vsprintf('author_calendar_%s_%s.pdf', [
			date('Y'),
			$data['book_id'],
		]);

		$dompdf->stream($filename, [
			'Attachment' => false,
		]);

	}

	public function testError() {
		trigger_error('something_went_wrong', E_USER_ERROR);
		// log_message('error', 'Severity: something_went_wrong');
	}

	public function patchData() {
		$rows = $this->db->query("
			select site_id,
			count(u_city_id) d_total,
			u_city_id,
			u_state_id,
			(select count(id) from users where role_id = 2 and city_id = c.u_city_id and site_id = c.site_id and _deleted = 0) as u_total,
			s_city_id,
			s_state_id,
			(select count(id) from users where role_id = 2 and city_id = c.s_city_id and site_id = c.site_id and _deleted = 0) as s_total
			from (
			SELECT
			event_user_invite_code.id,
			event_user_invite_code.user_id,
			users.state_id as u_state_id,
			users.city_id as u_city_id,
			site.state_id as s_state_id,
			site.city_id as s_city_id,
			site.id as site_id,
			site.name as site_name
			FROM event_user_invite_code
			join users on users.id = event_user_invite_code.user_id
			join site on site.id = users.site_id
			where ( users.state_id != site.state_id or users.city_id != site.city_id)
			and site.name not like '%direct%'
			and site.id not in (1, 2266, 2268, 162)
			) as c group by site_id
			having u_total > 30
			order by d_total desc
		")->result_array();
	}

	public function testAndroidPush() {
		$payload['data'] = [
			'id'				=> 1,
			'title'				=> 'Test Title',
			'body'				=> 'Test Body',
			'message'			=> 'Test message',
			'date_added'		=> '2025-01-06',
		];

		$result = send_android_notification('d6VtwK2YQjqOt0-zl32RMj:APA91bGzCHL9fhXwgucAjcr3W7aZYvAb-UJLX4vAp02qj7IN-YzLjKUHQrMzTYbp03LyFbmSccN6EahGxMbyB4G-DUSnhhO1zY98IvnIT_FkyYXwHimGhr4', $payload);
		// $result = send_android_notification_new();
		pr($result, 1);
	}

	public function testIP() {
		return;
		pr(_check_indian_ip('140.248.8.2'), 1);

		$rows = [];
		$header = [];

		$file = fopen('assets/csv/test_ip.csv', 'r');

		while (($line = fgetcsv($file)) !== FALSE) {
			if (empty($header)) {
				$header = $line;
				continue;
			}

			$rows[] = array_combine($header, $line);
		}

		$ips = [];

		$sort_order = [];

		foreach ($rows as $key => $item) {
			if ($info = _check_indian_ip($item['ip'])) {
				pr(['in' => [$info, $item]]);
				log_kb(['in' => [$info, $item]]);
			} else {
				pr(['not' => $item]);
				log_kb(['not' => $item]);
				if (!in_array($item['ip'], $ips)) {
					$ips[] = $item['ip'];
					$sort_order[] = ip2long($item['ip']);
				}
			}
		}
		array_multisort($sort_order, $ips);

		pr($ips, 1);
	}

	public function testIPRange() {
		return;
		$rows = [];
		$header = [];

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/ip_range.csv');
		$rows = $this->parsecsv->data;

		$ips = [];
		$sort_order = [];

		foreach ($rows as $key => $item) {
			$result = self::iprange2cidr($item['start_ip'], $item['end_ip']);

			$results = is_array($result) ? $result : [$result];

			foreach ($results as $key => $value) {
				$ips[] = ['range' => $value];
				$explode = explode('/', $value, 2);
				$sort_order[] = ip2long($explode[0]);
			}
		}

		array_multisort($sort_order, $ips);
		// pr($ips, 1);

		self::_downloadCsv($ips, 'ip_range');
	}

	private function iprange2cidr($ipStart, $ipEnd){
		if (is_string($ipStart) || is_string($ipEnd)){
			$start = ip2long($ipStart);
			$end = ip2long($ipEnd);
		}
		else{
			$start = $ipStart;
			$end = $ipEnd;
		}

		$result = array();

		while($end >= $start){
			$maxSize = 32;
			while ($maxSize > 0){
				$mask = hexdec(self::iMask($maxSize - 1));
				$maskBase = $start & $mask;
				if($maskBase != $start) break;
				$maxSize--;
			}
			$x = log($end - $start + 1)/log(2);
			$maxDiff = floor(32 - floor($x));

			if($maxSize < $maxDiff){
				$maxSize = $maxDiff;
			}

			$ip = long2ip($start);
			array_push($result, "$ip/$maxSize");
			$start += pow(2, (32-$maxSize));
		}
		return $result;
	}

	private function iMask($s){
		return base_convert((pow(2, 32) - pow(2, (32-$s))), 10, 16);
	}

	private function _getPincodeInfo($pincodes = [], $pincode = '') {
		$filtered = array_filter($pincodes, function($item) use($pincode) {
			return $item['Pincode'] == $pincode;
		});

		return array_shift(array_values($filtered));
	}

	public function checkPincode() {
		return;
		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/pincodes.csv');
		$pincodes = $this->parsecsv->data;

		// pr(self::_getPincodeInfo($pincodes, '695310'), 1);

		$this->parsecsv->auto('assets/csv/check_pincode.csv');
		$rows = $this->parsecsv->data;

		$bugs = [];

		// ob_start();

		foreach ($rows as $key => $item) {
			// pr($item['zipcode'], 1);
			$pincode_info = self::_getPincodeInfo($pincodes, $item['zipcode']);

			if (
				strtolower($item['state']) != strtolower($pincode_info['StateName']) ||
				strtolower($item['city']) != strtolower($pincode_info['District'])
			) {
				// echo vsprintf('%s-%s-%s-%s-%s-%s-%s-%s<br>', [
				// 	$key,
				// 	$item['zipcode'],
				// 	strtolower($item['state']),
				// 	strtolower($pincode_info['StateName']),
				// 	strtolower($item['state']) != strtolower($pincode_info['StateName']) ? 1 : 0,
				// 	strtolower($item['city']),
				// 	strtolower($pincode_info['District']),
				// 	strtolower($item['city']) != strtolower($pincode_info['District']) ? 1 : 0,
				// ]);

				log_kb(['checkPincode' => vsprintf('%s-%s-%s-%s-%s-%s-%s-%s<br>', [
					$key,
					$item['zipcode'],
					strtolower($item['state']),
					strtolower($pincode_info['StateName']),
					strtolower($item['state']) != strtolower($pincode_info['StateName']) ? 1 : 0,
					strtolower($item['city']),
					strtolower($pincode_info['District']),
					strtolower($item['city']) != strtolower($pincode_info['District']) ? 1 : 0,
				])]);
				$bugs[] = array_merge($item, [
					'new_state' 	=> $pincode_info
						? (strtolower($item['state']) != strtolower($pincode_info['StateName']) ? $pincode_info['StateName'] : '')
						: 'not_found',
					'new_city' 		=> $pincode_info
						? (strtolower($item['city']) != strtolower($pincode_info['District']) ? $pincode_info['District'] : '')
						: 'not_found',
				]);
				// flush();
				// ob_flush();
			}
		}

		// pr($bugs, 1);

		self::_downloadCsv($bugs, 'bugs');
	}

	public function testLocale() {
		$reader = new Reader(FCPATH . 'assets/citydb/GeoLite2-City.mmdb');
		// $record = $reader->city($this->input->ip_address());
		$record = $reader->city('38.137.22.133');
		$json['country'] 		= $record->country->name;
		$json['country_code'] 	= strtolower($record->country->isoCode);
		$json['region'] 		= $record->mostSpecificSubdivision->name;
		$json['city'] 			= $record->city->name;
		$json['zipcode'] 		= $record->postal->code;
		pr($json, 1);
	}

	public function testWebpush() {
		send_webpush_notification(
			'c6s4tDaPYgMS0uiKDYP1YN:APA91bGOlWibJ5OdXT3RW3h9n5f1MpyoiaTR08CykZTBCSpbQZ7PN0ZJq0i_Q97rzMqGWLDa2unQqJ3WD4-33M6rilBuvmE-r5kba0g66vs2lnltPCx5uO0APgNtjA__bKWGVTqiNAUt',
			[
				'title' => 'Firebase',
				'body' => 'Firebase is awesome',
				'url' => 'https://www.bribooks.com',
			]
		);
	}

	public function testOrderStatusUpdate() {
		// $this->alert_model->updateOrderStatusMidnightCron();
	}

	public function buildOrderRank($order_id = '') {
		if(empty($order_id))
			return;

		$this->load->library('Ranking_lib', 'ranking_lib');
		$this->ranking_lib->updateRank($order_id);
	}

	public function genGreyPdf() {
		$image = new Imagick(FCPATH . 'input.pdf');
		$image->setColorspace(imagick::COLORSPACE_GRAY);
		$image->writeImage(FCPATH . 'uploads/pdf/output.pdf');
		$image->clear();
		$image->destroy();
	}

	public function generateQrCode($data = '') {
		if(empty($data))
			return;

		echo sprintf('<img src="%s?v=%s" />', base_url(generateQrCode($data, 30, 20)), time());
		// echo generateQrCode('test');
	}

	public function generateBookQrCode($book_id = '') {
		if(empty($book_id))
			return;

		$this->load->model('book/Book_model', 'book_model');
		$book_info = $this->book_model->get($book_id);

		if(empty($book_info))
			return;

		$book_url = USER_URL . 'bookstore/' . $book_info['slug'];

		// pr($book_url);
		// pr(generateQrCode($book_url, 30, 20), 1);

		$qr_img = base_url(generateQrCode($book_url, 20, 3));

		echo sprintf('<img src="%s?v=%s" />', $qr_img, time());
	}

	public function testAppLink() {
		// echo '<script>window.location="bribooks://home/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoiMTkyMyIsInJvbGVfaWQiOiIyIiwiaWF0IjoxNzExMDE4NDczLCJleHAiOjE3MTE4ODI0NzN9.SXEc3snevtFgFdsigC6fZ2JUaOnBMNFQVzCk_CXZUxo/MyBooks/book_id/2366";</script>';
		echo '<script>window.location="bribooks://login/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoiMTkyMyIsInJvbGVfaWQiOiIyIiwiaWF0IjoxNzExMDE4NDczLCJleHAiOjE3MTE4ODI0NzN9.SXEc3snevtFgFdsigC6fZ2JUaOnBMNFQVzCk_CXZUxo";</script>';
		// echo '<script>window.location="bribooks://Leaderboard";</script>';
		// echo '<script>window.location="bribooks://MyBooks/2366";</script>';
		// echo '<script>window.location="bribooks://BookReader/moon-light-64943e724a4ba";</script>';
		// echo '<script>window.location="bribooks://SelectCategory";</script>';
	}

	public function testVonage() {
		// pr($this->alert_model->sms('+919625416974', 'test sms', 'vonage'));
	}

	public function testCreateCertificate($user_id = '') {
		if(!$user_id)
			return;

		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->certificate_model->createCertificates($user_id);
	}

	public function genGenericCertificates() {
		return;
		$results = $this->db->query("
			select max(order_product.order_id) as order_id, order_product.product_id as book_id,
			sum(order_product.quantity) as total,
			book.name, book.author_name,
			book.date_added,
			book.date_published,
			group_concat(order_product.order_id) as all_order_ids

			from order_product
			join book on book.id = order_product.product_id
			where
			product_id not in (select book_id from event_book)
			and product_id not in (select book_id from user_rank_country)
			and product_id not in (select book_id from user_rank_state)
			and product_id not in (select book_id from user_rank)
			and product_id not in (select book_id from user_rank_city)
			and product_id not in (select book_id from user_rank_school)
			and product_id not in (select book_id from certificates)
			and product_id > 1000
			and order_id not in (select id from `order` where status in (0, 91, 92))
			group by order_product.product_id
			order by total desc
		")->result_array();

		pr($results, 1);

		$this->load->library('GenericCertificate_lib');

		foreach ($results as $item) {
			$this->genericcertificate_lib->createCertificate($item['order_id'], false);
			break;
		}
	}

	public function missingEventBookEnrolmentCase_3() {
		return;

		$event_id = 10;

		$results = $this->db->query("SELECT
			book.id as book_id,
			book.user_id,
			concat(users.first_name, ' ', users.last_name) as author_name,
			users.email,
			users.mobile,
			concat('https://www.bribooks.com/eventinvite/10?uid=', users.id, '&code=', users.verification_code) as invite_url,
			(select count(page.id) from page where page.book_id = book.id) as total
			FROM `book`
			join users on users.id = book.user_id
			WHERE book.`status` = '0'
			and book.date_added > '2023-05-01'
			and book.user_id not in (select user_id from book where status = 1)
			and book.user_id in (select user_id from event_user where event_id in (4))
			and users.location = 'india'
			and users.state_id > 0
			having total >= 8
		")->result_array();

		// pr($results, 1);

		foreach ($results as $item) {
			if (!($this->db->get_where('event_user', [
				'user_id' 	=> (int)$item['user_id'],
				'event_id' 	=> (int)$event_id,
			])->row_array())) {
				pr([
					'event_id'		=> (int)$event_id,
					'user_id'		=> (int)$item['user_id'],
					'date_added'	=> date('Y-m-d H:i:s'),
					'date_modified'	=> date('Y-m-d H:i:s'),
				]);
				/*$this->db->insert('event_user', [
					'event_id'		=> (int)$event_id,
					'user_id'		=> (int)$item['user_id'],
					'date_added'	=> date('Y-m-d H:i:s'),
					'date_modified'	=> date('Y-m-d H:i:s'),
				]);*/
			}
		}
	}

	public function missingEventBookEnrolmentCase_2() {
		return;

		$event_id = 10;

		$results = $this->db->query("
			SELECT
			book.id as book_id,
			book.user_id,
			concat(users.first_name, ' ', users.last_name) as author_name,
			users.email,
			users.mobile,
			concat('https://www.bribooks.com/eventinvite/10?uid=', users.id, '&code=', users.verification_code) as invite_url,
			(select count(page.id) from page where page.book_id = book.id) as total
			FROM `book`
			join users on users.id = book.user_id
			WHERE book.`status` = '0'
			and book.date_added > '2023-05-01'
			and users.location = 'india'
			-- and users.state_id > 0
			and book.user_id not in (select user_id from book where status = 1)
			and book.user_id not in (select user_id from event_user where event_id in (2,4,10))
			having total >= 8
		")->result_array();

		// pr($results, 1);

		foreach ($results as $item) {
			if (!($this->db->get_where('event_user', [
				'user_id' 	=> (int)$item['user_id'],
				'event_id' 	=> (int)$event_id,
			])->row_array())) {
				pr([
					'event_id'		=> (int)$event_id,
					'user_id'		=> (int)$item['user_id'],
					'date_added'	=> date('Y-m-d H:i:s'),
					'date_modified'	=> date('Y-m-d H:i:s'),
				]);
				/*$this->db->insert('event_user', [
					'event_id'		=> (int)$event_id,
					'user_id'		=> (int)$item['user_id'],
					'date_added'	=> date('Y-m-d H:i:s'),
					'date_modified'	=> date('Y-m-d H:i:s'),
				]);*/
			}
		}
	}

	public function missingEventBookEnrolmentCase_1() {
		return;

		$this->load->model('common/Cron_model', 'cron_model');

		// return;
		$event_id = 10;

		$results = $this->db->select('book.id, book.user_id, book.name, book.author_name, book.date_added, book.date_published')
			->from('book')
			->join('users', 'users.id=book.user_id')
			->where('book.status', 1)
			->where('book._deleted', 0)
			->where('book.id not in (select book_id from event_book)')
			->where('users.location', 'india')
			->where('book.date_added >=', '2023-05-01')
			->order_by('book.id', 'ASC')
			->get()
			->result_array();

		// pr($this->db->last_query());
		// pr($results, 1);

		foreach ($results as $item) {
			pr($item);

			$orders = $this->db->get_where('order_product', [
				'product_id'	=> (int)$item['id']
			])->result_array();

			// pr($orders, 1);

			if(!empty($orders)) {
				foreach ($orders as $order) {
					pr($order);

					$order_info = $this->db->get_where('order', [
						'id'	=> (int)$order['order_id']
					])->row_array();

					if(!empty($orders)) {
						pr([
							'event_id'		=> (int)$event_id,
							'order_id'		=> (int)$order['order_id'],
							'book_id'		=> (int)$order['product_id'],
							'quantity'		=> (int)$order['quantity'],
							'date_added'	=> $order_info['date_added'],
							'date_modified'	=> $order_info['date_added'],
						]);

						// pr($order_info, 1);

						if (!($this->db->get_where('event_order', [
							'event_id'		=> (int)$event_id,
							'order_id'		=> (int)$order['order_id'],
							'book_id'		=> (int)$order['product_id'],
						])->row_array())) {
							/*$this->db->insert('event_order', [
								'event_id'		=> (int)$event_id,
								'order_id'		=> (int)$order['order_id'],
								'book_id'		=> (int)$order['product_id'],
								'quantity'		=> (int)$order['quantity'],
								'date_added'	=> $order_info['date_added'],
								'date_modified'	=> date('Y-m-d 15:40:00'),
							]);*/
						}
					}
				}

				if(!empty($orders[0]['order_id'])) {
					// pr($orders, 1);

					/*$this->cron_model->add([
						'code'		  => 'createCertificateNyafIn_' . $orders[0]['order_id'],
						'action'		=> 'alert_model->createCertificateNyafIn',
						'data'		  => [$orders[0]['order_id']],
						'site_id'	   => '1',
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+5 minutes')),
					]);

					$this->cron_model->add([
						'code'		  => 'createAwardsOnBookSoldNyafIn_' . $orders[0]['order_id'],
						'action'		=> 'alert_model->createAwardsOnBookSoldNyafIn',
						'data'		  => [$orders[0]['order_id']],
						'site_id'	   => '1',
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+10 minutes')),
					]);

					$this->cron_model->add([
						'code'		  => 'createMedallionOnBookSoldNyafIn_' . $orders[0]['order_id'],
						'action'		=> 'alert_model->createMedallionOnBookSoldNyafIn',
						'data'		  => [$orders[0]['order_id']],
						'site_id'	   => '1',
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+15 minutes')),
					]);*/
				}
			}

			pr([
				'event_id'		=> (int)$event_id,
				'book_id'		=> (int)$item['id'],
				'date_added'	=> $item['date_published'],
				'date_modified'	=> $item['date_published'],
			]);

			if (!($this->db->get_where('event_user', [
				'user_id' 	=> (int)$item['user_id'],
				'event_id' 	=> (int)$event_id,
			])->row_array())) {
				/*$this->db->insert('event_user', [
					'event_id'		=> (int)$event_id,
					'user_id'		=> (int)$item['user_id'],
					'date_added'	=> $item['date_published'],
					'date_modified'	=> date('Y-m-d 15:40:00'),
				]);*/
			}

			if (!($this->db->get_where('event_book', [
				'book_id' 	=> (int)$item['id'],
				'event_id' 	=> (int)$event_id,
			])->row_array())) {
				/*$this->db->insert('event_book', [
					'event_id'		=> (int)$event_id,
					'book_id'		=> (int)$item['id'],
					'date_added'	=> $item['date_published'],
					'date_modified'	=> date('Y-m-d 15:40:00'),
				]);*/
			}
		}
	}

	public function missingEventBookEnrolment() {
		return;
		$event_id = 10;
		$event_date = '2023-08-01';

		$results = $this->db->select('book.*')
			->from('book')
			->where('status', 1)
			->where('date_added > ', $event_date)
			->where(sprintf('user_id in (select user_id from event_user where event_id = %s)', $event_id))
			->where(sprintf('id not in (select book_id from event_book where event_id = %s)', $event_id))
			->get()
			->result_array();

		pr($results, 1);

		foreach ($results as $item) {
			$orders = $this->db->get_where('order_product', [
				'product_id'	=> (int)$item['id']
			])->result_array();

			pr($orders);

			foreach ($orders as $order) {
				$order_info = $this->db->get_where('order', [
					'id'	=> (int)$order['order_id']
				])->row_array();

				pr([
					'event_id'		=> (int)$event_id,
					'order_id'		=> (int)$order['order_id'],
					'book_id'		=> (int)$order['product_id'],
					'quantity'		=> (int)$order['quantity'],
					'date_added'	=> $order_info['date_added'],
					'date_modified'	=> $order_info['date_added'],
				]);

				/*$this->db->insert('event_order', [
					'event_id'		=> (int)$event_id,
					'order_id'		=> (int)$order['order_id'],
					'book_id'		=> (int)$order['product_id'],
					'quantity'		=> (int)$order['quantity'],
					'date_added'	=> $order_info['date_added'],
					'date_modified'	=> $order_info['date_added'],
				]);*/
			}

			pr([
				'event_id'		=> (int)$event_id,
				'book_id'		=> (int)$item['id'],
				'date_added'	=> $item['date_published'],
				'date_modified'	=> $item['date_published'],
			]);

			if (!($this->db->get_where('event_book', [
				'book_id' 	=> (int)$item['id'],
				'event_id' 	=> (int)$event_id,
			])->row_array())) {
				/*$this->db->insert('event_book', [
					'event_id'		=> (int)$event_id,
					'book_id'		=> (int)$item['id'],
					'date_added'	=> $item['date_published'],
					'date_modified'	=> $item['date_published'],
				]);*/
			}
		}
	}

	public function testAsync() {
		return;
		$this->load->model('common/AsyncTask_model', 'async_task_model');
		$this->async_task_model->add([
			'action'	=> 'Alert_model->validationOtp',
			'data' 		=> [
				'abhishek@youbooks.co',
				_li('Your login Verification code for ') . get_settings('system_name'),
				333333,
			]
		]);
	}

	public function fixcsvdata() {
		return;
		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/schools/clean_data.csv');
		$rows = $this->parsecsv->data;

		// pr($rows, 1);

		$clean_rows = [];

		foreach ($rows as $row) {
			$data = $row;

			if ($row['nsite_id'] === 'n') {
				$filtered = array_filter($rows, function($item) use($row) {
					return $item['cuid'] === $row['uid'];
				});

				if ($filtered) {
					$filtered = array_values($filtered);
					$data['site_id'] = $filtered[0]['site_id'];
				}

				// pr($filtered);
			}

			unset($data['nsite_id'], $data['cuid'], $data['c_site_id']);

			$clean_rows[] = $data;
		}

		// pr($clean_rows, 1);

		self::_downloadCsv($clean_rows, 'cleaned_live');
	}

	private function _downloadCsv($results = [], $filename = 'download') {
		$filename = $filename . date('Y_m_d_h_i_s') . '.csv';

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

		$headers = isset($results[0]) ? array_keys($results[0]) : [];

		if (!$headers) {
			exit(_l('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		self::_writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	public function buildRank() {
		return;

		$this->load->library('Ranking_lib', 'ranking_lib');
		// $this->ranking_lib->buildRankByType([
		// 	'type'		=> 'school',
		// 	'event_id'	=> 10,
		// 	'school_id'	=> 71588,
		// ]);

		$this->ranking_lib->buildRankByType([
			'type'		=> 'school',
			'event_id'	=> 10,
			'school_id'	=> 386,
		]);

		// $this->ranking_lib->buildRankByType([
		// 	'type'		=> 'country',
		// 	'event_id'	=> 10,
		// ]);
		// $this->ranking_lib->buildRanks(12, 'country');
		// $this->ranking_lib->buildRanks(12, 'school');
	}

	public function buildSchoolRank() {
		// return;

		$this->load->library('SchoolRanking_lib', 'schoolranking_lib');

		// $this->schoolranking_lib->buildRanks(21, 'country');
		// $this->schoolranking_lib->buildRanks(21, 'state');
		$this->schoolranking_lib->buildRanks(21, 'city');
	}

	public function pushCountryUpdateRank() {
		return;

		$this->load->library('Ranking_lib', 'ranking_lib');
		$this->ranking_lib->pushCountryUpdateRank(2699);
	}

	public function testEvents() {
		CI_Events::trigger('user_login', [
			'uid'	=> 3
		]);
	}

	public function genNationalRank($event_id = false, $event_challenge_country_id = false) {
		return;

		if(!$event_id || !$event_challenge_country_id)
			return;

		$this->load->library('Ranking_lib', 'ranking_lib');
		$this->ranking_lib->buildRanks($event_id, $event_challenge_country_id);
	}

	private function _getClientV2() {
		return;
		try {
			$code = '';

			$client = new Google_Client();
			$client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);
			$client->setAccessType('offline');
			$client->setAuthConfig(FCPATH . 'assets/csv/bb_fgfhgfg_65465465_786576576youbooksi-firebase-adminsdk-h347h-287df064cd.json');
			// $client->setPrompt('select_account consent');
			$redirect_uri = base_url('home/fetchGoogleCred');
			$client->setRedirectUri($redirect_uri);

			$token_path = FCPATH . 'uploads/gg_bb_sjhgdjfhgjhsdgf_345752364_765765766_token.json';

			if (is_file($token_path)) {
				$access_token = json_decode(file_get_contents($token_path), true);
				!empty($access_token) && $client->setAccessToken($access_token);
			}

			if ($client->isAccessTokenExpired() || empty($access_token)) {
				if ($client->getRefreshToken()) {
					$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
				} else {
					$auth_url = $client->createAuthUrl();

					if ($auth_code) {
						$auth_code = trim($code);

						$access_token = $client->fetchAccessTokenWithAuthCode($auth_code);
						$client->setAccessToken($access_token);
					} else {
						printf("Open the following link in your browser:\n%s\n", $auth_url);
					}

					if (array_key_exists('error', $access_token)) {
						throw new Exception(join(', ', $access_token));
					}
				}

				file_put_contents($token_path, json_encode($client->getAccessToken()));
			}

			return $client;
		} catch (\Exception $e) {
			pr('Error generating firebase access token: ' . $e->getMessage(), 1);
			return null;
		}
	}

	public function fetchGoogleCred() {
		$client 	= self::_getClientV2();

		// pr($client, 1);
	}

	private function _getClient() {
		$code = '4/0Adeu5BVyT375ZJEa0RjSotRcIqk91AWwGUcE_CQYOu_htISQd69azhiRzHhWrrHnTHRQMg';

		$client = new Google_Client();
		$client->setApplicationName('Google Sheets with ', get_settings('system_name'));
		$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
		$client->setAccessType('offline');
		$client->setAuthConfig(FCPATH . 'assets/csv/bb_fsjdhgfhj_kjahkjdfhkj_client_secret_752448992196-m67fvv98n9q6923fh0nve58iehkfn1ud.apps.googleusercontent.com.json');
		$client->setPrompt('select_account consent');

		$token_path = FCPATH . 'uploads/gg_bb_sjhgdjfhgjhsdgf_345752364_token.json';

		if (is_file($token_path)) {
			$access_token = json_decode(file_get_contents($token_path), true);
			$client->setAccessToken($access_token);
		}

		if ($client->isAccessTokenExpired() || empty($access_token)) {
			if ($client->getRefreshToken()) {
				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			} else {
				$auth_url = $client->createAuthUrl();
				// printf("Open the following link in your browser:\n%s\n", $auth_url);
				$auth_code = trim($code);

				$access_token = $client->fetchAccessTokenWithAuthCode($auth_code);
				$client->setAccessToken($access_token);

				if (array_key_exists('error', $access_token)) {
					throw new Exception(join(', ', $access_token));
				}
			}

			file_put_contents($token_path, json_encode($client->getAccessToken()));
		}

		return $client;
	}

	public function fetchGoogleCsv() {
		$client 	= self::_getClient();
		$service 	= new Google_Service_Sheets($client);

		$spreadsheet_id = '1ND1MoaWXAFmbAzvLc2ZG2OgcP_-mKH74IY0irIUmJ5o';
		$range 		= 'Sheet1';
		$response 	= $service->spreadsheets_values->get($spreadsheet_id, $range);
		$rows 		= $response->getValues();
		$headers 	= array_shift($rows);
		$results 	= [];

		foreach ($rows as $row) {
			$results[] = array_combine($headers, $row);
		}

		pr($results, 1);
	}

	public function encryptBankData() {
		return;
		$this->load->library('Encrypt_lib', 'encrypt_lib');
		$this->load->model('user/Bank_model', 'bank_model');

		$ge = 300000;

		$results = $this->bank_model->get_all()['rows'] ?? [];

		foreach ($results as $key => $item) {
			if ($item['id'] < $ge) {
				$item['account_number'] = $this->encrypt_lib->decrypt($item['account_number']);

				if (preg_match('/[^\d]/', $item['account_number'])) {
					$item['account_number'] = $this->encrypt_lib->decrypt($item['account_number']);
					pr($item);

					// $this->bank_model->edit($item['id'], [
					// 	'account_number'	=> $this->encrypt_lib->encrypt($item['account_number']),
					// ]);
				}
			}
		}
	}

	public function genCredit() {
		return;
		$this->load->model('user/AuthorEarning_model', 'author_earning_model');

		$results = $this->author_earning_model->get_all([
			'status'	=> 0,
			'currency_code'	=> 'inr'
		])['rows'] ?? [];

		// $includes = $this->db->get('israel_order')->result_array();
		// $includes = array_column($this->db->get('israel_order')->result_array(), 'order_id');

		// pr($includes, 1);

		$count = 0;

		foreach ($results as $key => $item) {
			// if (!in_array($item['order_id'], $includes)) continue;

			$order_info = $this->order_model->get($item['order_id']);

			if ($item['amount'] > 0 && $order_info['status'] == 4) {
				$count++;
				pr(['count' =>  $count, $item]);
				// self::_genCredit($item);
			}
		}
	}

	private function _genCredit($info = []) {
		$this->load->model('user/AuthorEarning_model', 'author_earning_model');
		$this->load->model('user/UserCredit_model', 'user_credit_model');
		$this->load->model('user/UserCreditHistory_model', 'user_credit_history_model');

		/*$this->author_earning_model->edit($info['id'], [
			'status' 			=> 1,
			'processing_by' 	=> -1,
			'processed_by' 		=> -1,
			'date_processing'	=> date('Y-m-d H:i:s'),
			'date_processed'	=> date('Y-m-d H:i:s'),
		]);*/

		$author_currency_code = get_author_currency_code($info['author_id']);

		if (empty($author_currency_code)) return;

		$info['amount'] = convert_to_local_currency($info['amount'], $info['author_id'], $info['currency_code']);

		$credit_info = $this->user_credit_model->getByUserId($info['author_id']);

		if (!empty($credit_info)) {
			// $this->user_credit_model->edit($credit_info['id'], [
			// 	'credit'	=> (double)($credit_info['credit'] + $info['amount']),
			// ]);
		} else {
			pr([
				'currency_code'	=> $author_currency_code,
				'user_id'		=> (int)$info['author_id'],
				'credit'		=> (double)$info['amount'],
			]);
			// $this->user_credit_model->add([
			// 	'currency_code'	=> $author_currency_code,
			// 	'user_id'		=> (int)$info['author_id'],
			// 	'credit'		=> (double)$info['amount'],
			// ]);
		}

		/*$this->user_credit_history_model->add([
			'type'					=> 1,
			'currency_code'			=> $author_currency_code,
			'user_id'				=> (int)$info['author_id'],
			'credit'				=> (double)$info['amount'],
			'order_id'				=> (int)$info['order_id'],
			'note'					=> (int)$info['id'],
		]);*/
	}

	public function fixOldOrdersToAfs() {
		return;
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('book/BookStock_model', 'book_stock_model');
		$this->load->model('book/BookStockHistory_model', 'book_stock_history_model');

		$orders = $this->db
			->where_in('status', [1,2,8])
			->where('_deleted', 0)
			->where('date_added > ', '2023-04-01')
			->where('id not in (select order_id from book_stock_history)')
			->get('order')
			->result_array();

		// pr($orders); die;

		foreach ($orders as $order) {
			self::_fixAfsOrders($order['id']);
		}
	}

	/*
	Patch for fix BookStock & BookStockHistory when invoiceOrder not created
	Date: 28-12-2023
	Himanshu Batra
	*/

	public function fixOrdersNotInBookStockHistory() {
		return;

		$orders = $this->db->query("
			SELECT `order`.id
			FROM `order`
			WHERE status NOT IN (0)
			AND date_added between '2023-12-21' and '2023-12-27'
			AND order_type != 3 AND user_id != 383799
			AND id NOT IN (SELECT order_id FROM book_stock_history)
		")->result_array();

		pr(count($orders));

		pr($orders, 1);

		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->library('Stock_lib', 'stock_lib');

		foreach ($orders as $order) {
			$order_info = $this->order_model->get($order['id']);
			// pr($order_info, 1);

			if (0 && empty($this->cron_model->getByCode('invoiceOrderCron_' . $order['id']))) {
				$this->cron_model->add([
					'code'		  => 'invoiceOrderCron_' . $order['id'],
					'action'		=> 'alert_model->invoiceOrderCron',
					'data'		  => [$order['id']],
					'site_id'	   => $order_info['site_id'] ?? '1',
					'status'	   	=> '1',
					'alert_date'	=> date('Y-m-d 13:50:30'),
				]);
			}

			// $this->stock_lib->orderFulfill($order['id'], true);

			if(in_array($order_info['status'], [92])) {
				// $this->stock_lib->refund($order['id']);
			}

			// pr($order, 1);
		}
	}

	private function _fixAfsOrders($order_id = 0) {
		$order_info = $this->order_model->get($order_id);
		pr($order_info, 1);

		if ($this->book_stock_history_model->get_all([
			'order_id'	=> (int)$order_id,
		])['total'] !== 0) {
			return;
		}

		// if all order quantity of all books are meet then move to afs
		$products = $this->order_model->getProducts($order_id);

		foreach ($products as $product) {
			$option = json_decode($product['option'], 1);

			$stock_info = $this->book_stock_model->get_all([
				'book_id'	=> (int)$product['product_id'],
				'version'	=> (int)$product['version'],
				'option'	=> $option['name'],
			])['rows'][0] ?? [];

			// If stock not found then create empty stock
			if (empty($stock_info)) {
				log_kb(['Stock Not found:: ' => [
					'book_id'	=> (int)$product['product_id'],
					'version'	=> (int)$product['version'],
					'option'	=> $option['name'],
				]]);

				$stock_id = $this->book_stock_model->add([
					'book_id'	=> (int)$product['product_id'],
					'version'	=> (int)$product['version'],
					'option'	=> $option['name'],
					'quantity'	=> 0,
				]);

				$stock_info = $this->book_stock_model->get($stock_id);
			}

			$stock_quantity = $stock_info['quantity'] ?? 0;

			$update_data = [
				'order_id'		=> (int)$order_id,
				'book_id'		=> (int)$product['product_id'],
				'version'		=> (int)$product['version'],
				'option'		=> $option['name'],
				'quantity'		=> (int)$stock_quantity,
				'quantity_order'=> (int)$product['quantity'],
				'quantity_hold'	=> $stock_quantity > 0
					? (int)($stock_quantity >= $product['quantity'] ? $product['quantity'] : $stock_quantity)
					: 0,
				'hold_date'		=> date('Y-m-d h:i:s'),
				'status'		=> $stock_quantity >= $product['quantity'] ? 1 : 0,
			];

			if ($stock_quantity >= $product['quantity']) {
				$update_data['release_date'] = date('Y-m-d h:i:s');
				$update_data['quantity_fulfill'] = $product['quantity'];
			}

			log_kb(['Stock History Add:: ' => $update_data]);

			$this->book_stock_history_model->add($update_data);

			$this->book_stock_model->edit($stock_info['id'], [
				'quantity'	=> (int)($stock_quantity - $product['quantity'])
			]);
		}

		if ($this->book_stock_history_model->get_all([
			'order_id'			=> (int)$order_id,
			'ne_status'			=> 1,
		])['total'] === 0) {
			log_kb(['Order orderFulfill Moving To Afs:: ' => $order_info]);

			$this->order_model->edit($order_id, [
				'status'	=> 21,
			]);
		}
	}

	public function testCache() {
		return;
		$this->load->driver('cache', [
			'adapter' 		=> 'redis',
			'backup' 		=> 'file',
			'key_prefix' 	=> 'bb_',
		]);

		$filter_data = [
			'key1' => 'value1',
			'key2' => 'value2',
			'key3' => 'value3',
			'key4' => 'value4',
		];

		$key = implode('_' . array_keys($filter_data)) . '_' . implode('_' . array_values($filter_data));

		if ($cache_data = $this->cache->get($key)) {
			$result = json_decode($cache_data, true);

			pr(['cache' => $result]);
		} else {
			$result = [
				'key1' => 'value1',
				'key2' => 'value2',
				'key3' => 'value3',
				'key4' => 'value4',
			];
			$this->cache->save($key, json_encode($result), 60);
		}

		pr($result);
	}

	public function testBulkDownload() {
		// return;
		$this->load->model('PrinterZipDownload_model', 'printer_zip_download_model');
		$this->printer_zip_download_model->downloadZipCron(35);
		// $this->load->library('S3_lib');
		// $obj = $this->s3_lib->get(sprintf('cover-%s.pdf', 'download_2024_07_24_23_51_03'), false);
		// pr($obj,1);
	}

	public function approvePendingBooks() {
		return;
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('book/Page_model', 'page_model');
		$this->load->model('Alert_model', 'alert_model');

		$results = $this->book_model->get_all([
			'status'	=> 2,
		])['rows'] ?? [];

		// pr($results);

		foreach ($results as $item) {
			self::_approvePendingBook($item['id']);
			if (ENVIRONMENT !== 'production') break;
		}
	}

	private function _approvePendingBook($book_id = 0) {
		$book_info = $this->book_model->get($book_id);
		$user_info = $this->student_model->get($book_info['user_id']);

		$total_pages = $this->page_model->get_all([
			'book_id' => $book_id
		])['total'] ?? 0;

		$total_order_count = $this->order_model->getAuthorProducts([
			'product_id'	=> $book_id,
			'version'		=> $book_info['version'],
		]);

		// pr([
		// 	'total_pages'	=> $total_pages,
		// 	'total_order_count'	=> $total_order_count,
		// 	'book'	=> $book_info,
		// ]);

		// Reject book
		if ($total_pages < 10 && empty($total_order_count)) {
			$this->book_model->edit($book_id, [
				'status'				=> 0,
				'editing'				=> 1,
			]);
			$subject = $book_info['author_name'] . ' your book ' . $book_info['name'] . ' is rejected.';
			$this->alert_model->bookApproved($book_info['id'], $subject);

			echo sprintf('Book rejected ID::%s -- %s -- pages:: %s orders:: %s<br>', $book_info['id'], $book_info['name'], $total_pages, $total_order_count);
		} else {
			// approve books
			$this->book_model->edit($book_id, [
				'status'				=> 2,
				'editing'				=> 0,
				'preview_token'			=> sha1(md5(time())),
				'slug'					=> get_book_slug($book_info['name'], $book_info['id']),
				'subscription_plan_id'	=> (int)$user_info['subscription_plan_id'],
				'can_be_published'		=> (int)$subscription_plan_info['can_be_published'],
				'date_published'		=> date('Y-m-d H:i:s'),
			]);

			$this->load->library('AutoApproval_lib', 'autoapproval_lib');
			$this->autoapproval_lib->approveBook($book_info['id']);

			$this->load->library('Version_lib', 'version_lib');
			$this->version_lib->apply($book_info['id']);

			$subject = (strpos($user_info['source'], 'NYAFIND') !== false ? _li($book_info['author_name'] . ' your book ' . $book_info['name'] . ' is approved & published') : $book_info['author_name'] . ' your book ' . $book_info['name'] . ' is approved.');
			$this->alert_model->bookApproved($book_info['id'], $subject);

			echo sprintf('Book approved ID:: %s -- %s -- pages:: %s orders:: %s<br>', $book_info['id'], $book_info['name'], $total_pages, $total_order_count);
		}
	}

	public function importCities()
	{
		return;
		$results = json_decode(file_get_contents(FCPATH . '/assets/csv/cities.bin'), true);
		ksort($results);
		// pr($results);

		$this->load->model('localisation/City_model', 'city_model');

		$i = 0;

		foreach ($results as $state => $cities) {
			if ($state_info = $this->db->get_where('state', ['name' => $state])->row_array()) {
				$state_id = $state_info['id'];
			} else {
				echo 'State Not in the list ' . $state . ' <br>';
				continue;
			}

			foreach ($cities as $key => $city) {
				if ($city_info = $this->db->get_where('city', [
					'name' 		=> $city,
					'state_id'	=> $state_id,
				])->row_array()) {
					$city_id = $city_info['id'];
				} else {
					$i++;

					echo sprintf('City Not in the list %s ---- %s %s <br>', $state, $city, $i);
					$city_id = $this->city_model->add([
						'name'		=> $city,
						'state_id'	=> $state_id,
					]);
				}
			}
		}
	}

	// Test Part
	public function testGradeAndSectionAssign()
	{
		$this->load->model('common/Grade_model', 'grade_model');
		$this->load->model('common/SiteSection_model', 'section_model');
		$this->load->model('event/EventSite_model', 'event_site_model');

		$sites = $this->event_site_model->get_all(['event_id' => 10])['rows'] ?? [];

		$sections = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

		foreach ($sites as $site) {
			for ($i = 1; $i <= 12; $i++) {
				$site_id = $site['site_id'];

				$grade_info = $this->grade_model->get_all([
					'site_id' => $site_id,
					'name' => $i,
				]);

				if (empty($grade_info['rows'])) {

					$grade_id = $this->grade_model->add([
						'site_id'	=> (int)$site_id,
						'name'		=> $i,
					]);
					for ($j = 0; $j < 26; $j++) {
						$this->section_model->add([
							'grade_id'	=> (int)$grade_id,
							'name'		=> $sections[$j],
						]);
					}
				}



			}
		}
	}

	public function testImportCountries()
	{
		// require FCPATH . 'countries.php';
		// pr($countries);

		// $this->load->model('localisation/Country_model', 'country_model');
		//
		// foreach ($countries as $code => $country) {
		// 	$_POST['name'] = $country['country'];
		// 	$_POST['tel_code'] = $country['tel_code'];
		// 	$_POST['code'] = mb_strtoupper($code);
		//
		// 	$this->country_model->add();
		// }
	}

	public function testWhatsapp()
	{
		return;
		// Case 1. payment reminder 24hours
		// self::_sendWhatsappImage(
		// 	'+917042467407',
		// 	[
		// 		'template'		=> '559219615698367',
		// 		'parameters'	=> [
		// 			'Author Name'
		// 		],
		// 		'document'	=> [
		// 			'name'	=> 'payment reminder',
		// 			'link'	=> base_url('assets/marketing/whatsapp.jpeg')
		// 		]
		// 	]
		// );

		// Case 2. payment reminder 48hours
		// self::_sendWhatsappImage(
		// 	'+917042467407',
		// 	[
		// 		'template'		=> '408256681263723',
		// 		'parameters'	=> [
		// 			'Author Name',
		// 			'Book Name',
		// 			'80%',
		// 		],
		// 		'document'	=> [
		// 			'name'	=> 'payment reminder',
		// 			'link'	=> base_url('assets/marketing/whatsapp.jpeg')
		// 		]
		// 	]
		// );

		// Case 3. competition payment
		self::_sendWhatsappImage(
			'+917042467407',
			[
				'template'		=> '3366669950272818',
				'parameters'	=> [
					'yash',
					'14',
					'Tanish',
				],
				'document'	=> [
					'name'	=> 'payment reminder',
					'link'	=> base_url('assets/marketing/user_1.jpeg')
				]
			]
		);

		// Case 4. signup but not wrote the book
		// self::_sendWhatsappImage(
		// 	'+917042467407',
		// 	[
		// 		'template'		=> '836768587309929',
		// 		'parameters'	=> [
		// 			'Published User name',
		// 			'92000',
		// 		],
		// 		'document'	=> [
		// 			'name'	=> 'payment reminder',
		// 			'link'	=> base_url('assets/marketing/user_1.jpeg')
		// 		]
		// 	]
		// );
	}

	public function testEventMail()
	{
		// $this->alert_model->eventFinalCron();
	}

	public function testEventSignupMail()
	{
		// $this->alert_model->eventSignupCron();
	}

	public function registerEventUser()
	{
		return;
		$user_id = 2400;
		$row = $this->user_model->get($user_id);

		self::eventRegister([
			'first_name'	=> $row->first_name,
			'last_name'		=> $row->last_name ? $row->last_name : $row->first_name,
			'email'			=> $row->email,
			'mobile'		=> $row->mobile,
			'grade'			=> (int)$row->grade,
			'password'		=> $row->password,
		]);
	}

	public function testDb()
	{
		$_bucket = 'youbooks-storage-5fd6173683748-webdev';
		$credentials = new Aws\Credentials\Credentials('', '');

		$this->_s3 = new Aws\S3\S3Client([
			'version'	 	=> 'latest',
			'region'	  	=> 'us-east-1',
			'credentials' 	=> $credentials,
		]);

		$directory = $this->config->item('s3_user_gallery') . 'site_images/nyaf2024/';

		$result = $this->_s3->listObjectsV2([
			'Bucket' 		=> $_bucket,
			'Prefix' 		=> $directory,
			'Delimiter' 	=> '/',
		]);

		pr($result);

		foreach ($result['CommonPrefixes'] ?? [] as $key => $item) {
			if(!empty($item['Prefix'])) {
				$images = [];

				$response = $this->_s3->listObjectsV2([
					'Bucket' 		=> $_bucket,
					'Prefix' 		=> $item['Prefix'],
					'Delimiter' 	=> '/',
				]);

				// foreach ($response['Contents'] ?? [] as $key1 => $item1) {
				// 	if(!empty($item1['Key'])) {
				// 		if ($key1 > 0) {
				// 			$extension = explode('.', strtolower($item1['Key']));
				// 			if(in_array(end($extension), ['jpeg','jpg','png'])) {
				// 				$images[] = $this->config->item('cloudfront_url') . $item['Prefix'] . basename($item1['Key']);
				// 			}
				// 		}
				// 	}
				// }

				echo "lhewbdchvdjcbdwjc";
				pr($response, 1);

				$data[basename($item['Prefix'])] = $images;
			} else {
				echo 'mc';die;
			}
		}

		echo 'NOOO';
	}

	public function testEvent()
	{
		return;
		// $this->edb = $this->load->database('eventdb', TRUE);
		// pr($this->edb->get('adt_user')->result_array());

		// $result = $this->icode_lib->setEndpoint($this->config->item('event_api_icode'))->setHeader([
		// 	'Content-Type'	=> 'application/x-www-form-urlencoded',
		// ])->insert('user/userRegister', [
		// 	'firstName'		=> 'Test2',
		// 	'lastName'		=> 'Test2',
		// 	'email'			=> 'test2@icode.org',
		// 	'mobile'		=> 9876543220,
		// 	'grade'			=> 4,
		// 	'schoolName'	=> 'Test2 School',
		// 	'password'		=> md5('123456'),
		// 	'countryCode'	=> 91,
		// 	'gameId'		=> 202102,
		// ])->rows();

		$result = $this->icode_lib->setEndpoint($this->config->item('event_api_icode'))->setHeader([
			'Content-Type'	=> 'application/x-www-form-urlencoded',
		])->insert('user/channel/userLogin', [
			'eventCode'		=> '2021INT',
			'email'			=> 'test2@icode.org',
			'password'		=> md5('123456'),
		]);

		$cookie1 = http_parse_cookie($result->resHeaders()['Set-Cookie'][0] ?? '');
		$cookie2 = http_parse_cookie($result->resHeaders()['Set-Cookie'][1] ?? '');

		// pr($result->rows());
		// pr($result->resHeaders());

		// pr([
		// 	$cookie1['USERID'],
		// 	$cookie2['SESSION'],
		// ]);

		$url = sprintf('http://crm.icode.education:5000/userIndex?token=%s&uid=%s', $cookie2['SESSION'], $cookie1['USERID']);
		redirect($url);
	}

	public function testGlobalCert()
	{
		return;
		$file = $this->tool_model->createGlobalCertificate([
			'date'					=> date('d/m/Y'),
			'author_name'			=> 'Abhishek Kumar',
			'book_name'				=> 'Book Wirtten on the BriBooks',
			'isbn'					=> '9789394848XXX',
			'code'					=> 2,
			'qrdata'				=> USER_URL . 'bookstore/ujhgjghj',
		]);

		echo sprintf('<img src="%s" />', base_url('uploads/global_certificate/' . $file));
		echo '<div style="background:grey;width:100px;height:100px;"></div>';
	}

	public function downloadGlobalCert($site_id = 0)
	{
		return;
		$results = $this->order_model->getTopSoldBooks([
			'site_id'	=> $site_id,
		]);

		foreach ($results as $key => $item) {
			// if (!empty($item['quantity'])) continue;

			if (ENVIRONMENT !== 'production' && $key > 2) {
				break;
			}

			$book_info = $this->book_model->get($item['id']);

			$file = $this->tool_model->createGlobalCertificate([
				'date'					=> date('d/m/Y', strtotime('2022/11/04')),
				'author_name'			=> $book_info['author_name'],
				'book_name'				=> $book_info['name'],
				'isbn'					=> !empty($book_info['isbn']) ? $book_info['isbn'] : '9789394848XXX',
				'code'					=> $book_info['id'],
				'qrdata'				=> !empty($book_info['isbn'])
					? 'https://isbnnew.inflibnet.ac.in/Recently_Published_Books.aspx'
					: USER_URL . 'bookstore/' . $book_info['slug'],
			], 'stthomas');
		}

		self::_downloadZipCertificate('stthomas');
	}

	public function testEmail()
	{

		pr($this->alert_model->email(
			'abhishek@youbooks.co',
						'Welcome to the BriBooks',
						'<p>Dear {name},</p>
			<p>Welcome to the BriBooks Family. Now you can avail on Select Books on our platform using coupon 50OFF. </p>
			<p>We will send you more information soon.</p>
			<p>Regards,</p>
			<p>Team BriBooks</p>',
			[]
		));
		//
		// pr($this->alert_model->email(
		// 	'developer.1@leaplearner.in',
		// 	'This is the Test Subject',
		// 	'This is test message',
		// 	[]
		// ));

// 		pr($this->alert_model->email(
// 			'abhishek@youbooks.co',
// 			'Welcome to the BriBooks',
// 			'<p>Dear {name},</p>
// <p>Welcome to the BriBooks Family. Now you can avail on Select Books on our platform using coupon 50OFF. </p>
// <p>We will send you more information soon.</p>
// <p>Regards,</p>
// <p>Team BriBooks</p>',
// 			[],
// 			[],
// 			null,
// 			'ami@bribooks.us',
// 			'BriBooks',
// 			'support@bribooks.us',
// 			[
// 				'X-BBCampaign' => 1234567,
// 			]
// 		));
	}

	public function testGmailApi()
	{
	}

	public function testSms()
	{
		return;

		// $sms = "Dear Ronav Mookerjee,%nUrgent Update: We have important information about Summer Camp that requires your attention. Please visit https://www.bribooks.com/notification for more details.%nRegards,%nTeam BriBooks";
		$sms = "Hey Himanshu Batra, Your book ain't published yet! Deadline ends in 30 days. Check https://www.bribooks.com/notification for details.";
		pr($sms);
		// pr($this->alert_model->sms('918840506668', $sms, 'textlocal', 'NzQ0YTZmNGM3MjMzNTU2YTUwNTg1MTc0NjE2Yjc5NjE='));

		//pr($this->alert_model->sms('9818651520', "Dear test parent\n\nA Demo for python has been requested for student name.\n\nOur Enrolment team will call you with confirmation soon.\n\nGratitude!\nEnrolment Team\nLeapLearner."));
		//pr($this->alert_model->sms('9818651520', "Dear parent_name\n\ncourse class for student name is schedule for 16:00 LeapLearner Sector 63 on 2020-02-24 \n\nGratitude!\nTeam LeapLearner"));
		//pr($this->alert_model->sms('9625416974', "Dear parent_name,\n\nA Demo for course has been requested for name.\nWe are running a Full Schedule and the Demo Slot requested by you will be confirmed based on availability.\nOur Enrolment team will call you soon with a Confirmed slot.\n\nGratitude!\nTeam LeapLearner"));
		//pr($this->alert_model->sms('9625416974', "Dear test\n\nWe have received a Demo request for python for your child abhi.\n\nWe have tried reaching you to confirm the Demo Schedule but haven't been able to. Kindly call us back on 9818651520.\n\nGratitude!\nTeam LeapLearner"));
	}

	public function testZoom()
	{
		return;
		$student_info = $this->student_model->get($this->session->user_id)->row_array();

		$data['debug']				= true;
		$data['class_id']			= 0;
		$data['name']				= $this->session->name;
		$data['email']				= !empty($student_info['email']) ? $student_info['email'] : uniqid() . '@leaplearner.co';
		$data['meeting_id']			= '';
		$data['meeting_password']	= '';

		$data['action']				= site_url('home/testZoom') . '?class_id=' . (int)$this->input->get('class_id');

		// pr($data);

		$this->load->view('frontend/' . get_frontend_settings('theme') . '/zoom', $data);
	}

	public function schoolpdftemplate($site_id = '')
	{
		return;
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('common/Grade_model', 'grade_model');
		$this->load->model('common/Section_model', 'section_model');

		$data['site_info'] = $this->site_model->get($site_id);

		$data['total_registered'] = $this->student_model->get_all([
			'site_id' => $site_id
		])['total'] ?? 0;

		$grades = $this->grade_model->get_all([
			'site_id' 	=> $site_id,
			'sort'		=> 'grade.name',
			'order'		=> 'ASC',
		])['rows'] ?? [];

		$data['grades']	= [];

		foreach ($grades as $grade) {
			$sections = $this->section_model->get_all([
				'grade_id' 	=> $grade['id'],
				'sort'		=> 'section.name',
				'order'		=> 'ASC',
			])['rows'] ?? [];

			$section_data = [];

			foreach ($sections as $section) {
				$reg_students = $this->student_model->get_all([
					'section_id' 	=> $section['id'],
					'grade_id' 		=> $grade['id'],
				])['total'];

				$book_written = $this->book_model->get_all([
					'section_id' 	=> $section['id'],
					'grade_id' 		=> $grade['id'],
				])['total'];

				$book_published = $this->book_model->get_all([
					'section_id' 	=> $section['id'],
					'grade_id' 		=> $grade['id'],
					'ne_status' 	=> 0,
				])['total'];

				$section_data[] = [
					'name'			=> $section['name'],
					'reg_students'	=> $reg_students,
					'book_written'	=> $book_written,
					'book_published' => $book_published,
				];
			}

			$data['grades'][] = [
				'name'		=> $grade['name'],
				'sections'	=> $section_data,
			];
		}
		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/school_pdf_template', $data, true);
		$dompdf = new Dompdf();
		// // // Load HTML content
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		// $dompdf->stream();
		$file = 'uploads/pdfs/'.date('Y-m-d').'-'.$site_id.'.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH.$file, $output);
		return "https://cms.bribooks.com/".$file;
		// $this->load->view('frontend/' . get_frontend_settings('theme') . '/school_pdf_template', $data);
	}

	public function testHindiEmojiOld() {
		return;
		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/test_emoji', $data, true);
		$dompdf = new Dompdf();
		// // // Load HTML content
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('dpi', 300);
		$dompdf->set_option('isHtml5ParserEnabled', true);

		// $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$dompdf->stream();
	}

	public function testHindiEmoji() {
		// return;
		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/test_emoji', $data, true);
		$this->load->library('Emoji_lib', 'emoji_lib');
		$this->emoji_lib->img_size = '20x20';
		$html = 'हिंदी कहानियाँ  एक ऐसी विधा जो जीवन को, परिस्थितियों को अपने में लेकर उलझी हुई समझ को, सुलझा देती हैं. हिंदी कहानी हमारे व्यक्तित्व को एक दर्पण की भांति हमारे सामने प्रेषित करती हैं जिनसे हमें अपने कर्मो का बोध होता हैं. माना कि कहानियाँ काल्पनिक होती हैं पर कल्पना परिस्थिती के द्वारा ही निर्मित होती हैं. पाठको को लुभाने एवं बांधे रखने के लिए कई बार भावों की अतिश्योक्ति की जाती हैं लेकिन अंत सदैव व्यवहारिक होता हैं, यथार्थता से परिपूर्ण होता हैं.

	हिंदी कहानी गद्य का रूप हैं. इसे उपन्यास का सूक्ष्मतम रूप कहा जा सकता हैं जिनमे पात्र हैं, संवाद हैं, लेकिन उपन्यास की तरह पल- पल का विस्तार नहीं. हिंदी कहानी एक रूप में उपन्यास का सारांश हैं.

	कहानियों के कई रूप हैं – प्रेम, नफ़रत, देश भक्ति, शौर्य, दुःख, ख़ुशी, भुत पिशाच, जासूसी आदि ऐसे कई भाव. आमतौर पर शिक्षाप्रद छोटी- छोटी कहानियाँ, प्रेरणादायक कहानियाँ, जासूसी कहानियाँ पाठको को लुभाती हैं. यह एक दर्पण की तरह उनका मार्गदर्शन करती हैं और वही कहानियाँ छोटे- छोटे बच्चों को सही गलत की पहचान कराती हैं.कहानियों के जरिये उन्हें नीति का ज्ञान होता हैं.उनमें संस्कारों की वृद्धि होती हैं. कहानियाँ इसलिए इतनी प्रभावशाली होती हैं क्यूंकि उनमें पात्र होते हैं,संवाद होते हैं, जो दिल और दिमाग में जगह बना लेते हैं जिन्हें व्यक्ति आसानी से स्वीकार कर लेता हैं, याद रख पता हैं. यही कारण हैं कि कहानियों को सर्वांगिक विकास के लिए सबसे सुन्दर विधा समझा जाता हैं.दोस्तो दोस्तो hello 😩😩😩';
		echo $html; die;
	}

	private function _getQrCode($book_info = [], $size = 60) {
		// $file = 'uploads/pdfs/qrcode_' . $book_info['slug'] . '.png';

		// $logo = imagecreatefrompng(FCPATH . 'assets/images/logo.png');
		// $logo_width = imagesx($logo);
		// $logo_height = imagesy($logo);
		// $qr_img = '';
		// $qr_img = imagecreatefrompng(vsprintf('https://chart.googleapis.com/chart?cht=qr&chld=H|0&chs=512x512&chl=%s', [
		// 	urlencode(USER_URL . 'bookstore/' . $book_info['slug']),
		// ]));

		// $qr_img_width = imagesx($qr_img);
		// $qr_img_height = imagesy($qr_img);

		// imagecopyresampled(
		// 	$qr_img,
		// 	$logo,
		// 	($qr_img_width / 2 - 150),
		// 	($qr_img_height / 2 - 150),
		// 	0,
		// 	0,
		// 	300,
		// 	300,
		// 	$logo_width,
		// 	$logo_height
		// );

		// imagepng($qr_img, $file);

		// return $file;
	}

	private function _getBarcode($isbn = 0) {
		$file = 'uploads/pdfs/' . $isbn . '.png';
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj(
			'C128',
			$isbn,
			160,
			40,
			'black',
			array(5, 5, 0, 5)
		)->setBackgroundColor('white');

		return $bobj->getHtmlDiv();

		file_put_contents(FCPATH . $file, $bobj->getPngData());
		return $file;
	}

	private function _getSpineSize($total_pages = 0) {
		return ($total_pages * 130 * 1.35 / 2000 + 2) * 2.83465;
	}

	public function testPrintCover($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		return;
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('book/Page_model', 'page_model');
		$this->load->model('book/PageVersion_model', 'page_version_model');
		$this->load->model('design/Category_model', 'category_model');
		$this->load->model('design/Theme_model', 'theme_model');
		$this->load->model('design/Cover_model', 'cover_model');

		$this->load->library('Emoji_lib', 'emoji_lib');

		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$book_original_info 	= $this->book_model->get($book_id);

			$this->load->library('Emoji_lib', 'emoji_lib');
			$this->emoji_lib->img_size = '20x20';

			$data['author_copy'] 	= $author_copy;

			$data['spine_size'] 	= self::_getSpineSize($this->page_version_model->get_all([
				'version'	=> $book_info['version'],
				'book_id'	=> $book_info['book_id'],
			])['total']);

			// $data['multiplier'] = $multiplier = 432 / 285;
			$data['multiplier'] = 432 / 285;
			$data['bleed'] 		= 30;
			$data['fc_bleed'] 	= 30;

			$multiplier 		= 468 / 285;
			$cover_info 		= !empty($book_info['cover_id'])
				? $this->cover_model->get($book_info['cover_id'])
				: [];
			$heading_style 		= !empty($cover_info['heading_style'])
				? json_decode($cover_info['heading_style'], true)
				: [];
			$data['cover_info'] 	= $cover_info;
			$data['heading_style'] 	= !empty($heading_style['style'])
				? $heading_style['style']
				: [];

			$data['book'] 				= $book_info;
			$data['book_code'] 			= _o_b_code($book_info['book_id'], $book_info['version'], 'paperback');
			$data['book_original_info'] = $book_original_info;

			$data['width'] 	= 285 * $multiplier * 2 + $data['spine_size'];
			$data['height'] = 427.5 * $multiplier;

			$data['qrcode'] 	= base_url(self::_getQrCode($book_info));
			$data['barcode'] 	= !empty($book_original_info['isbn'])
				// ? base_url(self::_getBarcode($book_original_info['isbn']))
				? self::_getBarcode($book_original_info['isbn'])
				: $data['qrcode'];


			$html = $this->load->view('backend/admin/books/book_cover', $data, true);
			// echo $html;
			// die;

			$dompdf = new Dompdf([
				// 'debugLayout' 	=> true,
				// 'debugCss'		=> true,
				// 'debugPng'		=> true,
			]);

			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('dpi', 300);
			$dompdf->set_option('isHtml5ParserEnabled', true);

			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper(
				[
					0,
					0,
					$data['width'] + 10,
					$data['height'] + 10
				],
				'portrait'
			);

			// Render the HTML as PDF
			$dompdf->render();

			// Output the generated PDF to Browser
			$dompdf->stream(str_replace('-', '_', $book_info['slug'] . '_by_' . $book_info['author_name']) . '_v' . $book_info['version'] . '.pdf');
		}
	}

	public function testPrintPages($book_id = 0, $version = 0, $author_copy = false, $download = true) {
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('book/Page_model', 'page_model');
		$this->load->model('book/PageVersion_model', 'page_version_model');
		$this->load->model('design/Category_model', 'category_model');
		$this->load->model('design/Theme_model', 'theme_model');
		$this->load->model('design/Cover_model', 'cover_model');

		$this->load->library('Emoji_lib', 'emoji_lib');

		if ($book_info = $this->book_version_model->getByVersion($book_id, $version)) {
			$book_original_info = $this->book_model->get($book_id);
			$cover_info 		= !empty($book_info['cover_id'])
				? $this->cover_model->get($book_info['cover_id'])
				: [];
			$heading_style 		= !empty($cover_info['heading_style'])
				? json_decode($cover_info['heading_style'], true)
				: [];

			$data['author_copy'] = $author_copy;

			$trim_size 			= 9; // .125 inch
			$bleed_size 		= 9; // .125 inch

			$original_width 	= 285; // 380 pixel eBook width
			$original_height 	= 427.5; // 570 pixel eBook height

			$page_width			= 432; // 6 inch
			$page_height		= 648; // 9 inch

			$data['multiplier'] = $page_width / $original_width; // adjust size according to eBook
			$data['bleed'] 		= 0;
			$data['fc_bleed'] 	= 0;
			$data['trim_size'] 	= $trim_size;
			$data['bleed_size'] = $bleed_size;
			$data['page_width'] = $page_width;
			$data['page_height']= $page_height;

			$data['cover_info'] 	= $cover_info;
			$data['heading_style'] 	= !empty($heading_style['style'])
				? $heading_style['style']
				: [];

			$data['pages'] = $this->page_version_model->get_all([
				'version'	=> $book_info['version'],
				'book_id'	=> $book_info['book_id'],
				'sort'		=> 'page_version.sort_order',
				'order'		=> 'ASC',
			])['rows'];

			$data['book'] 				= $book_info;
			$data['book_code'] 			= _o_b_code($book_info['book_id'], $book_info['version'], 'paperback');
			$data['book_original_info'] = $book_original_info;

			// two page size and 2x trim size + 2x bleed size and spine size
			$data['width'] 	= $page_width + $bleed_size;
			$data['height'] = $data['width'] * 1.5;

			$html = $this->load->view('backend/admin/book_print/page', $data, true);
			echo $html;
			die;
		}
	}

	public function updateSchools() {
		return;
		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/schools/updated_schools.csv');
		$rows = $this->parsecsv->data;
		// pr($rows, 1);

		foreach ($rows as $key => $row) {
			$this->db->update('site', [
				'name'			=> $row['school_name'],
				'owner_name'	=> $row['owner_name'],
				'owner_email'	=> $row['owner_email'],
				'owner_mobile'	=> $row['owner_mobile'],
				'date_modified'	=> date('Y-m-d H:i:s'),
			], [
				'id'			=> (int)$row['site_id']
			]);
		}
	}

	public function importSchools()
	{
		return;

		// $this->db->where('state_id', '35');
		// $this->db->delete('city');
		//
		// $this->db->where('state_id', '35');
		// $this->db->delete('schools_input');

		$country_info = $this->db->get_where('country', ['name' => 'India'])->row_array();

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/schools/summercamp_schools.csv');
		$rows = $this->parsecsv->data;

		// pr($rows);

		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		foreach ($rows as $key => $row) {
			$row['State'] = trim($row['State']);
			$row['City'] = trim($row['City']);
			$row['School Name'] = trim($row['School Name']);

			if (empty($row['State']) || empty($row['City']) || empty($row['School Name'])) continue;

			if ($state_info = $this->db->get_where('state', ['name' => $row['State']])->row_array()) {
				$state_id = $state_info['id'];
			} else {
				$state_id = $this->state_model->add([
					'name'			=> $row['State'],
					'country_id'	=> (int)$country_info['id'],
				]);
			}

			if ($city_info = $this->db->get_where('city', [
				'name' 		=> $row['City'],
				'state_id'	=> $state_id,
			])->row_array()) {
				$city_id = $city_info['id'];
			} else {
				$city_id = $this->city_model->add([
					'name'		=> $row['City'],
					'state_id'	=> $state_id,
				]);
			}

			echo sprintf('City %s State %s', $city_id, $state_id);

			$this->db->insert('schools_input', [
				'name'			=> $row['School Name'],
				'state_id'		=> $state_id,
				'city_id'		=> $city_id,
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
			]);
		}
	}

	public function letterHead()
	{
		return;
		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/letter_head.csv');
		$rows = $this->parsecsv->data;

		pr($rows, 1);

		// print_r($html);
		$this->load->library('zip');

		for ($i = 2500; $i < 2887; $i++) {
			pr($rows[$i], 1);

			$html = $this->load->view('common/letter_heads/summercamp24', [
				'data' => $rows[$i]
			], true);
			$dompdf = new Dompdf();

			// Load HTML content
			$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
			$dompdf->set_option('isJavascriptEnabled', true);
			$dompdf->set_option('isRemoteEnabled', true);
			$dompdf->set_option('isHtml5ParserEnabled', true);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();

			$this->zip->add_data('letter' . $i . '.pdf', $dompdf->output());

			if ($i > 3 && ENVIRONMENT !== 'production') break;
		}

		$this->zip->download('letter.zip');
	}

	public function mailtest()
	{
		// $this->load->model('school/School_model', 'school_model');
		// $this->load->model('Alert_model', 'alert_model');
		// print_r("Sahil");
		// $this->alert_model->schoolLeadShare(131);
	}

	public function school_mail()
	{
		// $this->alert_model->campaignSchoolAlertCron([]);
	}

	public function school_input()
	{
		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/bbbkp/cschool.csv');
		$rows = $this->parsecsv->data;


		foreach ($rows as $key => $row) {
			if ($this->db->get_where('campaign_school_new', [
				'email' => $row['Email']
			])->num_rows() < 1) {
				print_r($this->db->insert('campaign_school_new', [
					'school_name'			=> explode(',', $row['School Names'])[0],
					'email'			=> $row['Email'],
					'principle_name' => $row['Principal Name'],
				]));
			}
		}
	}

	public function autoApproved($param = '')
	{
		$this->load->model('book/Page_model', 'page_model');
		// $this->load->library('AutoApproval_lib','autoapproval_lib');
		// $approvedBook = $this->autoapproval_lib->autoApprovedBook($param);
		// // 985
		// print_r($approvedBook);
		$pages = $this->page_model->get_all(['book_id' => $param]);
		print_r($pages['total']);
	}

	public function sitesStudentInfo($site_id = '')
	{
		return;
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('common/Grade_model', 'grade_model');
		$this->load->model('common/Section_model', 'section_model');
		$data['site_info'] = $this->site_model->get($site_id);

		$students = $this->student_model->get_all([
			'site_id' => $site_id
		])['rows'] ?? [];

		$data['total_registered'] = count($students);

		$data['students'] = [];

		$grade_sort_order = [];
		$section_sort_order = [];

		foreach ($students as $item) {
			$grade_info = $this->grade_model->get($item['grade_id']);
			$section_info = $this->section_model->get($item['section_id']);

			$book_written = $this->book_model->get_all([
				'user_id'	   	=> $item['id'],
				'grade_id'	  	=> $grade_info['id'],
				'section_id'	=> $section_info['id'],
			])['total'];
			$book_published = $this->book_model->get_all([
				'user_id'	   	=> $item['id'],
				'grade_id'	  	=> $grade_info['id'],
				'section_id'	=> $section_info['id'],
				'ne_status'	 	=> 0,
			])['total'];

			$data['students'][] = [
				'name'			=> $item['first_name'] . ' ' . $item['last_name'],
				'grade'			=> $grade_info['name'],
				'section'		=> $section_info['name'],
				'book_written'	=> $book_written,
				'book_published'=> $book_published,
			];

			$grade_sort_order[] = $grade_info['name'];
			$section_sort_order[] = $section_info['name'];
		}

		array_multisort($grade_sort_order, $section_sort_order, $data['students']);

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/StudentPDF', $data, true);
		// echo $html; exit;
		$dompdf = new Dompdf();
		// Load HTML content
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		// $dompdf->stream(); exit;
		$file = 'uploads/pdfs/daily_report_' . $site_id . '.pdf';
		$output = $dompdf->output();
		file_put_contents(FCPATH . $file, $output);
		return $file;
	}

	public function testS3() {
		return;
		$this->load->library('S3_lib', 's3_lib');
		// $this->s3_lib->listBuckets();
		// pr($this->s3_lib->put('assets/images/jury_cert.png', 'testlogo'));
		$file = $this->s3_lib->get('assets/images/jury_cert.png', 'testlogo');
	}

	public function testIncompleteOrdersData() {
		return;
		$filter_data = [];
		$filter_data['ne_status'] = 4;
		$filter_data['startdate'] = date('2022-06-01');
		$filter_data['enddate'] = date('Y-m-d', strtotime('-30 days'));

		$results = $this->order_model->searchProductName($filter_data)['rows'] ?? [];

		// pr($filter_data);
		// pr(count($results));
		// pr($results);
	}

	public function testOrderRefund($order_id = '') {
		return;

		if(empty($order_id))
			return;

		$order_info = $this->order_model->get($order_id);
		if (empty($order_info)) return;

		$result = '';
		if(strtolower($order_info['provider']) == 'razorpay') {
			$result = $this->order_model->refundRazorpayOrder($order_id);
		} else if(strtolower($order_info['provider']) == 'stripe') {
			$result = $this->order_model->refundStripeOrder($order_id);
		}

		pr($result, 1);
	}

	public function fetchAwbDetails($order_id = '') {
		if(empty($order_id))
			return;

		$this->load->library('couriers/shiprocket_lib');
		$response = $this->shiprocket_lib->fetchAWB($order_id);
		pr($response, 1);
	}

	public function fetchShipmentDetails($shipment_id = '') {
		if(empty($shipment_id))
			return;

		$this->load->library('couriers/shiprocket_lib');
		$response = $this->shiprocket_lib->fetchShipment($shipment_id);
		pr($response, 1);
	}

	public function getDonationCertificate($user_credit_request_id = '') {
		return;

		if(empty($user_credit_request_id))
			return;

		self::donationRequestCron($user_credit_request_id);
	}

	public function user_royalty() {
		return;

		$this->load->model('user/UserCredit_model', 'user_credit_model');
		$this->load->model('user/Bank_model', 'bank_model');

		$filter_data = [];
		$filter_data['currency_code'] = 'INR';
		$filter_data['credit_ge'] = 100;

		$results = $this->user_credit_model->get_all($filter_data)['rows'];

		foreach ($results as $key => $result) {
			$bank_info = $this->bank_model->getByUid($result['user_id']);

			if(empty($bank_info['pan_number'])) {
				$user_info = $this->user_model->get($result['user_id']);

				$full_name = ucfirst(trim($user_info['first_name'] . ' ' . $user_info['last_name']));
				$author_earning_url = USER_URL . 'account/myearnings';

				$mobile = $user_info['mobile'];
				$email 	= $user_info['email'];

				self::_sendWhatsappText(
					$mobile,
					[
						'template'		=> '1510783599729683',
						'parameters'	=> [
							$full_name,
							$author_earning_url
						]
					]
				);

				$subject = $full_name . ', Verify Your Account Details for Continued Royalty Payments!';

				$content = '<p>Dear <strong>'.$full_name.'</strong>,</p>
<p>Congratulations on your recent author royalty payment and earnings!</p>
<p>To ensure that you continue receiving future royalties, we kindly request you to verify your account details by <strong>30th June 2023</strong>. Please note that we will not be able to proceed with the author royalties until you update your account details. It is crucial to ensure that you meet the deadline, as failure to do so may result in a delay or inability to receive the amount.</p>
<p>To verify your account, simply follow the link <strong>'.$author_earning_url.'</strong></p>
<p>Your privacy and security are of utmost importance to us, and we assure you that your information will be handled with care.</p>
<p>Thank you for your prompt attention to this matter. We look forward to continuing our partnership with you and supporting your writing journey on BriBooks!</p><br />
<p>Best regards,</p>
<p>Team BriBooks</p>';

				$this->alert_model->email(
					$email,
					$subject,
					$content,
					[],
					[]
				);
			}
		}

		pr(count($results), 1);
	}

	public function author_royalty_transfer_update() {
		return;

		$this->load->model('user/UserCredit_model', 'user_credit_model');
		$this->load->model('user/Bank_model', 'bank_model');

		$filter_data = [];
		$filter_data['currency_code'] = 'INR';
		$filter_data['credit_ge'] = 20;

		$results = $this->user_credit_model->get_all($filter_data)['rows'];

		pr($results, 1);

		foreach ($results as $key => $result) {
			$user_info = $this->user_model->get($result['user_id']);

			$full_name = ucfirst(trim($user_info['first_name'] . ' ' . $user_info['last_name']));

			if(0 && ENVIRONMENT == 'production') {
				$mobile = $user_info['mobile'];
				$email = $user_info['email'];
			}

			/*self::_sendWhatsappText(
				$mobile,
				[
					'template'		=> '972435270843545',
					'parameters'	=> [
						$full_name
					]
				]
			);

			$subject = 'BriBooks: Author Royalty transfer update!';

			$content = '<p>Dear <strong>'.$full_name.'</strong>,</p>
<p>We are glad to inform you that the minimum amount clause for self royalty transfer has been removed. Now, you can transfer any amount to your bank account from your Author Royalty balance.</p><br />
<p>Regards,</p>
<p>Team BriBooks</p>';

			$this->alert_model->email(
				$email,
				$subject,
				$content,
				[],
				[]
			);*/
		}

		pr(count($results), 1);
	}

	public function getS3Data() {
		$this->_bucket = 'youbooks-storage-5fd6173683748-webdev';
		$credentials = new Aws\Credentials\Credentials('', '');

		$this->_s3 = new Aws\S3\S3Client([
			'version'	 	=> 'latest',
			'region'	  	=> 'us-east-1',
			'credentials' 	=> $credentials,
		]);

		try {
			$directory = $this->config->item('s3_user_gallery') . 'NYAF_2023/UserGallery/nyaf_2022/';

			$result = $this->_s3->listObjectsV2([
				'Bucket' 		=> $this->_bucket,
				'Prefix' 		=> $directory,
				'Delimiter' 	=> '/',
			]);

			foreach ($result['CommonPrefixes'] ?? [] as $key => $item) {
				if(!empty($item['Prefix'])) {
					$images = [];

					$response = $this->_s3->listObjectsV2([
						'Bucket' 		=> $this->_bucket,
						'Prefix' 		=> $item['Prefix'],
						'Delimiter' 	=> '/',
					]);

					foreach ($response['Contents'] ?? [] as $key1 => $item1) {
						if(!empty($item1['Key'])) {
							if ($key1 > 0) {
								$extension = explode('.', strtolower($item1['Key']));
								if(in_array(end($extension), ['jpeg','jpg','png'])) {
									$images[] = $this->config->item('cloudfront_url') . $item['Prefix'] . basename($item1['Key']);
								}
							}
						}
					}

					$return[basename($item['Prefix'])]['title'] = _l(basename($item['Prefix']));
					$return[basename($item['Prefix'])]['data'] = $images;
				}
			}

			pr($return, 1);
		} catch (Exception $e) {
			self::_log($e->getMessage());
		}
	}

	public function updatedUSASchoolData() {
		return;

		$other_cities = $this->db->select('GROUP_CONCAT(id) as city_ids')
			->from('city')
			->where('name', 'other')
			->get()->row()->city_ids ?? '';

		// pr($other_cities);
		$other_cities = explode(',', $other_cities);

		// pr($other_cities, 1);

		$school_data = $this->db->select('*')
			->from('updated_school_input')
			->get()->result_array();

		// pr($school_data);

		foreach($school_data ?? [] as $data) {
			$country_info = $this->db->get_where('country', ['name' => trim($data['country'])])->row_array();
			if(empty($country_info))
				continue;

			$country_id = $country_info['id'];

			$state_info = $this->db->get_where('state', [
				'country_id'	=> $country_id,
				'name'			=> trim($data['state'])
			])->row_array();

			if(empty($state_info))
				continue;

			$site_results = $this->site_model->get_all([
				'name'		=> trim($data['name']),
				'state_id'	=> $state_info['id']
			]);

			// pr($site_results);

			if(empty($site_results))
				continue;

			$school_results = $this->schoolinput_model->get_all([
				'name'		=> trim($data['name']),
				'state_id'	=> $state_info['id']
			])['rows'] ?? [];

			// pr($school_results);

			if(empty($school_results))
				continue;

			if ($city_info = $this->db->get_where('city', [
				'state_id'	=> $state_info['id'],
				'name' 		=> trim($data['city'])
			])->row_array()) {
				$city_id = $city_info['id'];
			} else {
				$city_id = $this->city_model->add([
					'name'		=> trim($data['city']),
					'state_id'	=> $state_info['id'],
				]);
			}

			$update_data = [];
			$update_data['state_id'] = $state_info['id'];
			$update_data['city_id'] = $city_id;

			foreach ($site_results as $site_info) {
				if(in_array($site_info['city_id'], $other_cities))
					$update_data['site_id'] = $site_info['id'];
			}

			foreach ($school_results as $school_info) {
				if(in_array($school_info['city_id'], $other_cities))
					$update_data['school_id'] = $school_info['id'];
			}

			// pr($site_results);
			// pr($school_results);
			// pr($update_data, 1);

			/*$this->db->update('updated_school_input', $update_data, [
				'id' => (int)$data['id']
			]);*/
		}

		echo "Schools updated successfully!";
	}

	public function updatedUSASchoolStateCity() {
		return;

		$school_data = $this->db->select('*')
			->from('updated_school_input')
			/*->limit(4)*/
			->get()
			->result_array();

		// pr($school_data);

		/*foreach($school_data as $data) {
			$this->site_model->editById($data['site_id'], [
				'city_id' => $data['city_id']
			]);

			$this->schoolinput_model->edit($data['school_id'], [
				'city_id' => $data['city_id']
			]);
		}*/

		echo "Schools updated successfully!";
	}


	public function mergeImage() {
		$img1 = "test.jpeg";
		$img2 = "1.png";

		if (empty($img1) || empty($img2))
			return;

		$file = 'uploads/school_letterhead23/new_img.png';

		$d_img = imagecreatefromjpeg(FCPATH . 'uploads/school_letterhead23/' . $img1);
		$s_img = imagecreatefrompng(FCPATH . 'uploads/school_letterhead23/qrcodes/qrcode_' . $img2);

		$dw = imagesx($d_img);
		$dh = imagesy($d_img);

		$sw = imagesx($s_img);
		$sh = imagesy($s_img);

		imagecopyresampled(
			$d_img,
			$s_img,
			// ($dw - $sw / 3.5 - 220),
			// ($dh - $sh / 3.5 - 260),
			1845,
			1500,
			0,
			0,
			// $sw / 3.5,
			// $sh / 3.5,
			$sw + 75,
			$sh + 80,
			$sw,
			$sh
		);

		imagejpeg($d_img, $file);

		echo sprintf('<img src="%s?v=%s" />', base_url($file), time());
	}

	public function brochureInd23() {

		$font_path_regular = FCPATH . 'assets/global/fonts/Poppins-Bold.ttf';

		$dir = FCPATH . 'uploads/in-nyafbro/brochure/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$schoolDetails = $this->db->where('id','3916')->get('brochure_2023')->result_array();
		// pr($schoolDetails, 1);

		foreach($schoolDetails as $data) {
			if(empty($data['school_name']))
				continue;

			$img_brochure_1 = imagecreatefromjpeg(FCPATH . 'assets/images/bro-india23/brochure_1.jpg');
			$img_brochure_4 = imagecreatefromjpeg(FCPATH . 'assets/images/bro-india23/brochure_4.jpg');

			$schoolLength = strlen($data['school_name']);

			if($schoolLength > 75) {
				$position_1 = (int)((mb_strlen($data['school_name'], 'utf-8') * (14))/2) ?? 600;
				$position_2 = (int)((mb_strlen($data['school_name'], 'utf-8') * (13))/2) ?? 600;
			} else if($schoolLength > 60) {
				$position_1 = (int)((mb_strlen($data['school_name'], 'utf-8') * (16))/2) ?? 600;
				$position_2 = (int)((mb_strlen($data['school_name'], 'utf-8') * (15))/2) ?? 600;
			} else if($schoolLength > 30) {
				$position_1 = (int)((mb_strlen($data['school_name'], 'utf-8') * (17))/2) ?? 600;
				$position_2 = (int)((mb_strlen($data['school_name'], 'utf-8') * (16))/2) ?? 600;
			} else if($schoolLength > 20) {
				$position_1 = (int)((mb_strlen($data['school_name'], 'utf-8') * (20))/2) ?? 600;
				$position_2 = (int)((mb_strlen($data['school_name'], 'utf-8') * (18))/2) ?? 600;
			} else {
				$position_1 = (int)((mb_strlen($data['school_name'], 'utf-8') * (24))/2) ?? 600;
				$position_2 = (int)((mb_strlen($data['school_name'], 'utf-8') * (22))/2) ?? 600;
			}

			$darkgrey 	= imagecolorallocate($img_brochure_1, 0, 0, 0);
			$darkgrey2 	= imagecolorallocate($img_brochure_4, 0, 0, 0);

			imagettftext($img_brochure_1, 22, 0, 640 - $position_1, 610, $darkgrey, $font_path_regular, $data['school_name']);
			imagettftext($img_brochure_4, 20, 0, 640 - $position_2, 1590, $darkgrey2, $font_path_regular, $data['school_name']);

			imagejpeg($img_brochure_1, $dir . "/brochure_".$data['id']."_1.jpeg");
			imagedestroy($img_brochure_1);

			imagejpeg($img_brochure_4, $dir . "/brochure_".$data['id']."_4.jpeg");
			imagedestroy($img_brochure_4);
		}
	}

	public function generateBrochurePDFIndia() {
		return;

		// return;

		$schoolDetails = $this->db->where('status','0')->get('brochure_2023')->result_array();

		$dir = FCPATH . 'uploads/in-nyafbro/school_pdf/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		foreach($schoolDetails as $key => $data) {
			if(empty($data['school_name']))
				continue;

			$file1 = 'uploads/in-nyafbro/brochure/brochure_'.$data['id'].'_1.jpeg';
			$file2 = 'assets/images/bro-india23/brochure_2.jpg';
			$file3 = 'assets/images/bro-india23/brochure_3.jpg';
			$file4 = 'uploads/in-nyafbro/brochure/brochure_'.$data['id'].'_4.jpeg';

			$pdf = new TCPDF();

			pr($pdf, 1);

			$pdf->setTitle('Brochure');
			$pdf->SetMargins(0,0,0,0);
			$pdf->SetAutoPageBreak(true);
			$pdf->SetPrintHeader(false);
			$pdf->setPrintFooter(false);

			$pdf->AddPage('p', 'A4');
			$pdf->Image($file1,0,0,0,0,'','','',true,700,'',false,false,false,false);
			$pdf->AddPage('p', 'A4');
			$pdf->Image($file2,0,0,0,0,'','','',true,700,'',false,false,false,false);
			$pdf->AddPage('p', 'A4');
			$pdf->Image($file3,0,0,0,0,'','','',true,700,'',false,false,false,false);
			$pdf->AddPage('p', 'A4');
			$pdf->Image($file4,0,0,0,0,'','','',true,700,'',false,false,false,false);

			$fname = 'school' . sprintf('%04d', $data['id']);

			pr($fname, 1);

			$pdf_string = $pdf->Output('pseudo.pdf', 'S');
			file_put_contents($dir.$fname.'.pdf', $pdf_string);

			// $this->db->update('brochure_2023', [
			// 	'status'		=> '1',
			// 	'date_added'	=> date('Y-m-d H:i:s'),
			// ], [
			// 	'id'			=> (int)$data['id']
			// ]);

			// unlink(FCPATH . 'uploads/in-nyafbro/brochure/brochure_'.$data['id'].'_1.jpeg');
			// unlink(FCPATH . 'uploads/in-nyafbro/brochure/brochure_'.$data['id'].'_4.jpeg');
		}
	}

	public function generateLetterHeadIndia() {
		$dir = FCPATH . 'uploads/school_letterhead23/pdf/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$dir = FCPATH . 'uploads/school_letterhead23/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		if (!empty($results = $this->db->get('brochure_2023')->result_array())) {
			list($image_width, $image_height) = getimagesize(FCPATH . 'assets/images/NYAF_IN_LetterHead.jpg');
			$font_path = FCPATH . 'assets/global/fonts/Poppins-Bold.ttf';
			$font_path_regular = FCPATH . 'assets/global/fonts/Poppins-Regular.ttf';
			$font_path_light = FCPATH . 'assets/global/fonts/Poppins-Light.ttf';

			foreach ($results as $key => $result) {
				$str1 = $str2 = $str3 = '';

				$nomination_code = 'School' . sprintf('%04d', $result['id']);

				// $image_name = 'test.jpeg';
				$image_name = $nomination_code . '.jpeg';

				// $p = 'Thank you for applying to the National Young Authors’ Fair (NYAF). It is our pleasure to announce that ' . $result['school_name']. ' has been officially selected to participate in this event.';
				$p = 'We are happy to invite, '.$result['school_name'].', in the 2023 edition of National Young Authors’ Fair™ - the world’s largest book writing competition for school students.';
				// $p = 'We are happy to invite, '.$result['school_name'].', in the 2023 edition of National Young Authors’ FairTM - the world’s largest book writing competition for school students.';

				$school_arr = explode(" ", $p);
				foreach ($school_arr as $school) {
					if(empty(strlen($str2)) && empty(strlen($str3)) && (strlen($str1) + strlen($school)) < 100) {
						$str1 .= ' ' . $school;
					} else if(empty(strlen($str3)) && (strlen($str2) + strlen($school)) < 100) {
						$str2 .= ' ' . $school;
					} else if((strlen($str3) + strlen($school)) < 100) {
						$str3 .= ' ' . $school;
					}
				}

				$sn_length = strlen($result['school_name']);
				$p_length = strlen($p);

				$image 		= imagecreatefromjpeg(FCPATH . 'assets/images/NYAF_IN_LetterHead.jpg');
				$darkgrey 	= imagecolorallocate($image, 16, 40, 75);
				$dark 		= imagecolorallocate($image, 0, 0, 0);
				$grey 		= imagecolorallocate($image, 110, 110, 110);
				$white 		= imagecolorallocate($image, 255, 255, 255);

				if($str3) {
					imagettftext($image, 80, 0, 210, 820, $dark, $font_path_regular, 'Dear School Leader :');

					imagettftext($image, 65, 0, 200, 1080, $darkgrey, $font_path_regular, $str1);
					imagettftext($image, 65, 0, 200, 1210, $darkgrey, $font_path_regular, $str2);

					imagettftext($image, 65, 0, 200, 1340, $darkgrey, $font_path_regular, $str3);
					$updatedY = 1340;
				} else {
					imagettftext($image, 80, 0, 210, 980, $dark, $font_path_regular, 'Dear School Leader :');

					imagettftext($image, 65, 0, 200, 1240, $darkgrey, $font_path_regular, $str1);
					imagettftext($image, 65, 0, 200, 1380, $darkgrey, $font_path_regular, $str2);

					$updatedY = 1390;
				}

				$str1 = $str2 = $str3 = '';

				$p = 'We would like to see , '.$result['school_name'].', progressively ace the following goals for this year’s event and shine both in the national as well global media platforms like Times of India-NIE, Education World and Disney International HD.';

				$school_arr = explode(" ", $p);
				foreach ($school_arr as $school) {
					if(empty(strlen($str2)) && empty(strlen($str3)) && (strlen($str1) + strlen($school)) < 100) {
						$str1 .= ' ' . $school;
					} else if(empty(strlen($str3)) && (strlen($str2) + strlen($school)) < 100) {
						$str2 .= ' ' . $school;
					} else if((strlen($str3) + strlen($school)) < 100) {
						$str3 .= ' ' . $school;
					}
				}

				// imagettftext($image, 65, 0, 200, 2020, $darkgrey, $font_path_regular, $str1);
				// imagettftext($image, 65, 0, 200, 2150, $darkgrey, $font_path_regular, $str2);

				imagettftext($image, 65, 0, 200, ($updatedY + 660), $darkgrey, $font_path_regular, $str1);
				imagettftext($image, 65, 0, 200, ($updatedY + 790), $darkgrey, $font_path_regular, $str2);
				$updatedYA = ($updatedY + 810);

				if($str3) {
					// imagettftext($image, 65, 0, 200, 2270, $darkgrey, $font_path_regular, $str3);
					imagettftext($image, 65, 0, 200, ($updatedY + 910), $darkgrey, $font_path_regular, $str3);
					$updatedYA = ($updatedY + 930);
				}

				$str1 = $str2 = $str3 = '';

				$p = 'Following are the tiered goals for '.$result['school_name'].' in The National Young Authors’ Fair™ :';

				$school_arr = explode(" ", $p);
				foreach ($school_arr as $school) {
					if(empty(strlen($str2)) && empty(strlen($str3)) && (strlen($str1) + strlen($school)) < 100) {
						$str1 .= ' ' . $school;
					} else if(empty(strlen($str3)) && (strlen($str2) + strlen($school)) < 100) {
						$str2 .= ' ' . $school;
					} else if((strlen($str3) + strlen($school)) < 100) {
						$str3 .= ' ' . $school;
					}
				}

				// imagettftext($image, 65, 0, 200, 2440, $darkgrey, $font_path_regular, $str1);
				// imagettftext($image, 65, 0, 200, 2560, $darkgrey, $font_path_regular, $str2);

				imagettftext($image, 65, 0, 200, ($updatedYA + 170), $darkgrey, $font_path_regular, $str1);
				imagettftext($image, 65, 0, 200, ($updatedYA + 290), $darkgrey, $font_path_regular, $str2);


				imagettftext($image, 65, 0, 1455, 2829, $darkgrey, $font_path_regular, (!empty($result['city']) ? $result['city'] : "Your City"));
				imagettftext($image, 65, 0, 1651, 3029, $darkgrey, $font_path_regular, (!empty($result['state']) ? $result['state'] : "Your State"));

				imagejpeg($image, $dir . '/' . $image_name);
				imagedestroy($image);

				// echo sprintf('<img src="%s?v=%s" />', base_url('uploads/school_letterhead/test.jpeg'), time());

				$html = '<style>@page{margin:0;padding:0;}</style><img
					src="' . site_url('uploads/school_letterhead23/') . $image_name . '"
					style="width:100%;max-height:100%;"
				/>';

				$dompdf = new Dompdf();
				$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
				$dompdf->set_option('isJavascriptEnabled', true);
				$dompdf->set_option('isRemoteEnabled', true);
				$dompdf->set_option('isHtml5ParserEnabled', true);

				// (Optional) Setup the paper size and orientation
				$dompdf->setPaper('A4', 'potrait');

				// Render the HTML as PDF
				$dompdf->render();

				$path_info = pathinfo($image_name);

				$dirpdf = FCPATH . 'uploads/school_letterhead23/pdf/';

				file_put_contents(
					$dirpdf . $path_info['filename'] . '.pdf',
					$dompdf->output()
				);
			}
		}
	}

	public function generateLetterHeadIndia23() {



		// $letterHeads = $this->db->get('letterhead_site')->result_array();
		// pr($letterHeads,1);

		$dir = FCPATH . 'uploads/school_letterhead23/pdf/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$dir = FCPATH . 'uploads/school_letterhead23/';
		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		// $results = $this->db->where('status', 0)->get('new_letterhead')->result_array();
		// pr($results,1);

		if (!empty($results = $this->db->where('status', 0)->get('letterhead_new_site')->result_array())) {
			list($image_width, $image_height) = getimagesize(FCPATH . 'assets/images/NYAF_IN_LetterHead.jpg');
			$font_path_regular = FCPATH . 'assets/global/fonts/Poppins-Regular.ttf';
			$font_path_bold = FCPATH . 'assets/global/fonts/Poppins-Bold.ttf';
			$font_path_semibold = FCPATH . 'assets/global/fonts/Poppins-SemiBold.otf';

			foreach ($results as $key => $result) {
				$str1 = $str2 = $str3 = '';

				$nomination_code = 'School' . sprintf('%05d', $result['site_id']);

				// $image_name = 'test.jpeg';
				$image_name = $nomination_code . '.jpeg';

				$p = 'We are delighted to invite '.$result['school_name'].' to join the ranks of India’s premier schools participating in the 2023 edition of the National Young Authors’ Fair™ - the world\'s largest book writing competition for school students.';
				$school_arr = explode(" ", $p);
				foreach ($school_arr as $school) {
					if(empty(strlen($str2)) && empty(strlen($str3)) && (strlen($str1) + strlen($school)) < 100) {
						$str1 .= ' ' . $school;
					} else if(empty(strlen($str3)) && (strlen($str2) + strlen($school)) < 100) {
						$str2 .= ' ' . $school;
					} else if((strlen($str3) + strlen($school)) < 100) {
						$str3 .= ' ' . $school;
					}
				}

				$sn_length = strlen($result['school_name']);
				$p_length = strlen($p);

				$image 		= imagecreatefromjpeg(FCPATH . 'assets/images/NYAF_IN_LetterHead.jpg');
				$darkgrey 	= imagecolorallocate($image, 16, 40, 75);
				$dark 		= imagecolorallocate($image, 0, 0, 0);
				$grey 		= imagecolorallocate($image, 110, 110, 110);
				$white 		= imagecolorallocate($image, 255, 255, 255);

				// imagettftext($image, 35, 0, 110, 460, $dark, $font_path_regular, 'Dear School Leader,');
				imagettftext($image, 35, 0, 110, 460, $dark, $font_path_regular, 'Dear '.$result['principal_name'].',');
				imagettftext($image, 35, 0, 2040, 460, $dark, $font_path_regular, 'NYAF'.sprintf('%05d', $result['site_id']));

				imagettftext($image, 35, 0, 106, 770, $darkgrey, $font_path_regular, $str1);
				imagettftext($image, 35, 0, 106, 842, $darkgrey, $font_path_regular, $str2);

				imagettftext($image, 35, 0, 106, 920, $darkgrey, $font_path_regular, $str3);


				$str1 = $str2 = $str3 = '';

				$p = 'Please scan the QR Code or visit the link below to complete your school\'s registration www.yaf.bribooks.com/india/school/'.$result['site_id'];
				$school_arr = explode(" ", $p);
				foreach ($school_arr as $school) {
					if(empty(strlen($str2)) && empty(strlen($str3)) && (strlen($str1) + strlen($school)) < 55) {
						$str1 .= ' ' . $school;
					} else if(empty(strlen($str3)) && (strlen($str2) + strlen($school)) < 55) {
						$str2 .= ' ' . $school;
					} else if((strlen($str3) + strlen($school)) < 55) {
						$str3 .= ' ' . $school;
					}
				}


				// imagettftext($image, 35, 0, 290, 1575, $darkgrey, $font_path_regular, $str1);
				// imagettftext($image, 35, 0, 290, 1645, $darkgrey, $font_path_regular, $str2);
				// imagettftext($image, 35, 0, 290, 1713, $darkgrey, $font_path_regular, $str3);

				imagettftext($image, 35, 0, 290, 1627, $darkgrey, $font_path_regular, $str1);
				imagettftext($image, 35, 0, 290, 1697, $darkgrey, $font_path_regular, $str2);
				imagettftext($image, 35, 0, 290, 1765, $darkgrey, $font_path_regular, $str3);

				imagejpeg($image, $dir . '/' . $image_name);
				imagedestroy($image);

				// echo sprintf('<img src="%s?v=%s" />', base_url('uploads/school_letterhead/test.jpeg'), time());

				self::_letterheadQrCodeSchool23($result['site_id']);

				self::mergeInImage23($image_name, $result['site_id'].'.png');


				$html = '<style>@page{margin:0;padding:0;}</style><img
					src="' . site_url('uploads/school_letterhead23/') . $image_name . '"
					style="width:100%;max-height:100%;"
				/>';

				$dompdf = new Dompdf();
				$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
				$dompdf->set_option('isJavascriptEnabled', true);
				$dompdf->set_option('isRemoteEnabled', true);
				$dompdf->set_option('isHtml5ParserEnabled', true);

				// (Optional) Setup the paper size and orientation
				$dompdf->setPaper('A4', 'potrait');

				// Render the HTML as PDF
				$dompdf->render();

				$path_info = pathinfo($image_name);

				$dirpdf = FCPATH . 'uploads/school_letterhead23/pdf/';

				file_put_contents(
					$dirpdf . $path_info['filename'] . '.pdf',
					$dompdf->output()
				);

				// $file1 = 'uploads/school_letterhead23/'.$image_name;

				// $pdf = new TCPDF();

				// $pdf->setTitle('Letterhead');
				// $pdf->SetMargins(0,0,0,0);
				// $pdf->SetAutoPageBreak(true);
				// $pdf->SetPrintHeader(false);
				// $pdf->setPrintFooter(false);

				// $pdf->AddPage('p', 'A4');
				// $pdf->Image($file1,0,0,0,0,'','','',false,700,'',false,false,false,false);

				// $fname = 'school' . sprintf('%05d', $result['site_id']);

				// $pdf_string = $pdf->Output('pseudo.pdf', 'S');
				// file_put_contents($dirpdf.$fname.'.pdf', $pdf_string);

				// $this->db->update('new_letterhead', [
				// 	'status'			=> 1
				// ], [
				// 	'id'			=> $result['id']
				// ]);

				// echo sprintf('<img src="%s?v=%s" />', base_url('uploads/school_letterhead23/test.jpeg'), time());

				// if($key >= 1){
				// 	break;die;
				// }
			}
		}
	}

	private function _letterheadQrCodeSchool23($code = '') {
		if (file_exists('uploads/school_letterhead23/qrcodes/qrcode_'.$code.'.png'))
			return base_url().'uploads/school_letterhead23/qrcodes/qrcode_'.$code.'.png';

		$dir = FCPATH . 'uploads/school_letterhead23/qrcodes';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$file = 'uploads/school_letterhead23/qrcodes/qrcode_' . $code . '.png';

		$logo = imagecreatefrompng(FCPATH . 'assets/images/logo.png');
		$logo_width = imagesx($logo);
		$logo_height = imagesy($logo);

		$qr_img = imagecreatefrompng(vsprintf('https://chart.googleapis.com/chart?cht=qr&chld=H|0&chs=512x512&chl=%s', [
			urlencode('https://www.yaf.bribooks.com/india/school/'. $code),
		]));

		$qr_img_width = imagesx($qr_img);
		$qr_img_height = imagesy($qr_img);

		imagecopyresampled(
			$qr_img,
			$logo,
			($qr_img_width / 2 - 150),
			($qr_img_height / 2 - 150),
			0,
			0,
			300,
			300,
			$logo_width,
			$logo_height
		);

		imagepng($qr_img, $file);

		return base_url($file);
	}

	public function generateLetterHeadSCIndia24($start = 0, $end = 10) {
		$dir 			= FCPATH . 'uploads/school_letterheadsc24/';
		$design_file 	= FCPATH . 'assets/images/letterhead_sc_24.jpg';

		$dir_pdf 		= $dir . 'pdf/';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		if (!is_dir($dir_pdf)) {
			mkdir($dir_pdf, 0777, TRUE);
			chmod($dir_pdf, 0777);
			@touch($dir_pdf . '/' . 'index.html');
		}

		$this->load->library('parsecsv');
		$this->parsecsv->auto('assets/csv/letter_head.csv');
		$rows = $this->parsecsv->data;

		// pr($rows, 1);

		$this->load->library('zip');

		if (!empty($rows)) {
			list($image_width, $image_height) = getimagesize($design_file);

			$font_path_regular 	= FCPATH . 'assets/global/fonts/Poppins-Medium.ttf';
			$font_path_bold 	= FCPATH . 'assets/global/fonts/Poppins-Bold.ttf';
			$font_path_semibold = FCPATH . 'assets/global/fonts/Poppins-SemiBold.otf';

			$limit_per_line 	= 98;

			foreach ($rows as $key => $result) {
				if ($key < $start) continue;
				if ($key > $end) break;

				$str1 = $str2 = $str3 = $str4 = '';

				$nomination_code = 'School' . sprintf('%05d', $result['id']);

				$image_name = $nomination_code . '.jpeg';

				$p = sprintf('We are pleased to inform you that, based on the inputs received from Education World Magazine, your school, %s, has been selected to participate in the 2024 edition of the Summer Book Writing Festival (SBWF).', $result['name']);
				$p .= ' This INVITE-ONLY event is COMPLETELY FREE for both the school and students and is exclusively meant only for the top schools of India.';

				$explodes 	= explode(' ', $p);
				$implodes 	= [];
				$line 		= 0;

				foreach ($explodes as $str) {
					if (empty($implodes[$line])) {
						$implodes[$line] = '';
					}

					if ((strlen($implodes[$line]) + strlen($str)) < $limit_per_line) {
						$implodes[$line] .= empty($implodes[$line]) ? $str : (' ' . $str);
					} else {
						$line++;
						$implodes[$line] .= $str;
					}
				}

				$image 		= imagecreatefromjpeg($design_file);
				$darkgrey 	= imagecolorallocate($image, 16, 40, 75);
				$dark 		= imagecolorallocate($image, 0, 0, 0);
				$grey 		= imagecolorallocate($image, 110, 110, 110);
				$blue 		= imagecolorallocate($image, 78, 99, 210);
				$white 		= imagecolorallocate($image, 255, 255, 255);
				$font_size	= 36;

				$start_y 	= count($implodes) > 4 ? 610 : 640;

				foreach ($implodes as $index => $str) {
					imagettftext($image, $font_size, 0, 80, $start_y + ($index * 70), $darkgrey, $font_path_regular, $str);
				}

				$event_site_code = sprintf('SBWF-%s', $result['id']);

				imagettftext($image, $font_size, 0, $image_width - 400 - strlen($event_site_code), 520, $dark, $font_path_regular, $event_site_code);

				$reg_url = 'www.camp.bribooks.com/india/2024/school/' . $result['id'];
				imagettftext($image, $font_size, 0, 80, 1840, $blue, $font_path_regular, $reg_url);

				$reg_url = sprintf('A Customised Letter for the teachers and students of %s.', $result['name']);
				$reg_url_2 = '';

				if (strlen($reg_url) > ($limit_per_line - 6)) {
					$temp_reg_url = $reg_url;
					$reg_url = substr($reg_url, 0, ($limit_per_line - 6));
					$index = strrpos($reg_url, ' ');
					$reg_url = substr($reg_url, 0, $index);
					$reg_url_2 = substr($temp_reg_url, $index);
				}

				if (!empty($reg_url_2)) {
					imagettftext($image, $font_size, 0, 210, 2330, $darkgrey, $font_path_regular, $reg_url);
					imagettftext($image, $font_size, 0, 210, 2390, $darkgrey, $font_path_regular, $reg_url_2);
				} else {
					imagettftext($image, $font_size, 0, 210, 2350, $darkgrey, $font_path_regular, $reg_url);
				}

				imagejpeg($image, $dir . '/' . $image_name);
				imagedestroy($image);

				self::_letterheadQrCodeSchoolSC24($result['id'], $dir);

				self::mergeInImageSC24($image_name, $result['id'] . '.png');

				$html = '<style>@page{margin:0;padding:0;}</style><img
					src="' . site_url('uploads/school_letterheadsc24/') . $image_name . '"
					style="width:100%;max-height:100%;"
				/>';

				// echo sprintf('<img src="%s?v=%s" />', base_url('uploads/school_letterheadsc24/' . $image_name), time()); die;

				$dompdf = new Dompdf();
				$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
				$dompdf->set_option('isJavascriptEnabled', true);
				$dompdf->set_option('isRemoteEnabled', true);
				$dompdf->set_option('isHtml5ParserEnabled', true);

				// (Optional) Setup the paper size and orientation
				$dompdf->setPaper('A4', 'potrait');

				// Render the HTML as PDF
				$dompdf->render();

				$path_info = pathinfo($image_name);

				// file_put_contents(
				// 	$dir_pdf . $path_info['filename'] . '.pdf',
				// 	$dompdf->output()
				// );

				$this->zip->add_data('letter_' . $path_info['filename'] . '.pdf', $dompdf->output());
			}

			$this->zip->download('letters.zip');
		}
	}

	private function _letterheadQrCodeSchoolSC24($code = '', $dir = '') {
		if (file_exists($dir . 'qrcodes/qrcode_' . $code . '.png')) {
			return base_url(str_replace(FCPATH, '', $dir) . 'qrcodes/qrcode_'. $code . '.png');
		}

		$dir = $dir . 'qrcodes';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$file = str_replace(FCPATH, '', $dir) . '/qrcode_' . $code . '.png';

		$file = generateQrCode(sprintf('https://www.camp.bribooks.com/india/2024/school/%s?utm_source=lf', $code), 20, 2, $file);

		return base_url($file);
	}

	private function mergeInImageSC24($img1, $img2) {
		if (empty($img1) || empty($img2))
			return;

		$file = 'uploads/school_letterheadsc24/'. $img1;

		$image = imagecreatefromjpeg(FCPATH . 'uploads/school_letterheadsc24/' . $img1);
		$qr_image = imagecreatefrompng(FCPATH . 'uploads/school_letterheadsc24/qrcodes/qrcode_' . $img2);

		$image_width = imagesx($image);
		$image_height = imagesy($image);

		$qr_image_width = imagesx($qr_image);
		$qr_image_height = imagesy($qr_image);

		$zoom = 3;

		imagecopyresampled(
			$image,
			$qr_image,
			($image_width - $qr_image_width / $zoom - 80),
			($image_height - $qr_image_height / $zoom - 1620),
			0,
			0,
			$qr_image_width / $zoom,
			$qr_image_height / $zoom,
			$qr_image_width,
			$qr_image_height
		);

		imagejpeg($image, $file);

		// echo sprintf('<img src="%s?v=%s" />', base_url($file), time());
	}

	private function mergeInImage23($img1, $img2) {
		// $img1 = "test.jpeg";
		// $img2 = "1.png";

		if (empty($img1) || empty($img2))
			return;

		$file = 'uploads/school_letterhead23/'. $img1;

		$d_img = imagecreatefromjpeg(FCPATH . 'uploads/school_letterhead23/' . $img1);
		$s_img = imagecreatefrompng(FCPATH . 'uploads/school_letterhead23/qrcodes/qrcode_' . $img2);

		$dw = imagesx($d_img);
		$dh = imagesy($d_img);

		$sw = imagesx($s_img);
		$sh = imagesy($s_img);

		imagecopyresampled(
			$d_img,
			$s_img,
			// ($dw - $sw / 3.5 - 220),
			// ($dh - $sh / 3.5 - 260),
			1845,
			1500,
			0,
			0,
			// $sw / 3.5,
			// $sh / 3.5,
			$sw + 75,
			$sh + 80,
			$sw,
			$sh
		);

		imagejpeg($d_img, $file);

		// echo sprintf('<img src="%s?v=%s" />', base_url($file), time());
	}

	public function sanitizeUSerSchool() {
		return;

		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('user/Student_model', 'student_model');

		$results = $this->db->query("
			SELECT `site`.`id`, `site`.`parent_id`, `site`.`name`, `site`.`site_code`, `site`.`state_id`, `site`.`city_id`
			FROM `site`
			WHERE `parent_id` = 2273
			GROUP BY `site_code`
			HAVING COUNT(`site_code`) > 1"
		)->result_array();

		pr($results, 1);
		foreach($results as $key => $result) {
			// pr($result);

			$siteData = $this->db->where('site_code' , $result['site_code'])->get('site')->result_array();
			// $student_site_info = $this->student_model->get_all(['source' => $result['site_code']])['rows'] ?? [];

			// pr($siteData);
			// pr($student_site_info);

			if(!empty($siteData) && count($siteData) == 2) {
				pr($siteData[0]);

				/*$site_code_0 = $result['site_code'] . "-s" . $siteData[0]['state_id'] . "-c" . $siteData[0]['city_id'];
				$site_code_1 = $result['site_code'] . "-s" . $siteData[1]['state_id'] . "-c" . $siteData[1]['city_id'];

				$this->db->update('site', [
					'site_code'	 => $site_code_0
				], [
					'id'	   		=> $siteData[0]['id']
				]);

				$this->db->update('users', [
				 	'site_id'	   => $siteData[0]['id'],
					'source'	 	=> $site_code_0
				], [
					'role_id'	   => 2,
					'source'	   	=> $result['site_code'],
				 	'state_id'	  => $siteData[0]['state_id'],
				 	'city_id'	   => $siteData[0]['city_id']
				]);

				if (!empty($users = $this->db->get_where('users', [
					'role_id'	   => 2,
				 	'site_id'	   => $siteData[0]['id'],
					'source'	   	=> $result['site_code'],
				 	'state_id'	  => $siteData[1]['state_id'],
				 	'city_id'	   => $siteData[1]['city_id']
				])->result_array())) {
					pr($users);

					$this->db->update('users', [
					 	'site_id'	   => $siteData[1]['id'],
						'source'	 	=> $site_code_1
					], [
						'role_id'	   => 2,
					 	'site_id'	   => $siteData[0]['id'],
						'source'	   	=> $result['site_code'],
					 	'state_id'	  => $siteData[1]['state_id'],
					 	'city_id'	   => $siteData[1]['city_id']
					]);
				}

				$this->db->update('lead', [
				 	'site_id'	   => $siteData[0]['id'],
					'source'	 	=> $site_code_0
				], [
					'source'	   	=> $result['site_code'],
				 	'state_id'	  => $siteData[0]['state_id'],
				 	'city_id'	   => $siteData[0]['city_id']
				]);

				if (!empty($leads = $this->db->get_where('lead', [
				 	'site_id'	   => $siteData[0]['id'],
					'source'	   	=> $result['site_code'],
				 	'state_id'	  => $siteData[1]['state_id'],
				 	'city_id'	   => $siteData[1]['city_id']
				])->result_array())) {
					pr($leads);

					$this->db->update('lead', [
					 	'site_id'	   => $siteData[1]['id'],
						'source'	 	=> $site_code_1
					], [
					 	'site_id'	   => $siteData[0]['id'],
						'source'	   	=> $result['site_code'],
					 	'state_id'	  => $siteData[1]['state_id'],
					 	'city_id'	   => $siteData[1]['city_id']
					]);
				}

				pr($siteData[1]);

				$this->db->update('site', [
					'site_code'	 => $site_code_1
				], [
					'id'	   		=> $siteData[1]['id']
				]);

				$this->db->update('users', [
				 	'site_id'	   => $siteData[1]['id'],
					'source'	 	=> $site_code_1
				], [
					'role_id'	   => 2,
					'source'	   	=> $result['site_code'],
				 	'state_id'	  => $siteData[1]['state_id'],
				 	'city_id'	   => $siteData[1]['city_id']
				]);

				if (!empty($users = $this->db->get_where('users', [
					'role_id'	   => 2,
				 	'site_id'	   => $siteData[1]['id'],
					'source'	   	=> $result['site_code'],
				 	'state_id'	  => $siteData[0]['state_id'],
				 	'city_id'	   => $siteData[0]['city_id']
				])->result_array())) {
					pr($users);

					$this->db->update('users', [
					 	'site_id'	   => $siteData[0]['id'],
						'source'	 	=> $site_code_0
					], [
						'role_id'	   => 2,
					 	'site_id'	   => $siteData[1]['id'],
						'source'	   	=> $result['site_code'],
					 	'state_id'	  => $siteData[0]['state_id'],
					 	'city_id'	   => $siteData[0]['city_id']
					]);
				}

				$this->db->update('lead', [
				 	'site_id'	   => $siteData[1]['id'],
					'source'	 	=> $site_code_1
				], [
					'source'	   	=> $result['site_code'],
				 	'state_id'	  => $siteData[1]['state_id'],
				 	'city_id'	   => $siteData[1]['city_id']
				]);

				if (!empty($leads = $this->db->get_where('lead', [
				 	'site_id'	   => $siteData[1]['id'],
					'source'	   	=> $result['site_code'],
				 	'state_id'	  => $siteData[0]['state_id'],
				 	'city_id'	   => $siteData[0]['city_id']
				])->result_array())) {
					pr($leads);

					$this->db->update('lead', [
					 	'site_id'	   => $siteData[0]['id'],
						'source'	 	=> $site_code_0
					], [
					 	'site_id'	   => $siteData[1]['id'],
						'source'	   	=> $result['site_code'],
					 	'state_id'	  => $siteData[0]['state_id'],
					 	'city_id'	   => $siteData[0]['city_id']
					]);
				}*/
			}

			die;
		}
	}

	public function checkAuthorCertification($user_id = '') {
		if(!$user_id)
			return;

		$this->load->model('certificate/Certificate_model', 'certificate_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');

		if (!empty($this->event_user_model->getEventUserByUserId(NYAF_IN_EVENT_ID, $user_id)) && (date('YmdHis') <= NYAF_IN_END_DATE)) {
			$order_product_info_nyaf = $this->order_product_model->getOrderProductQuantityByEventId(NYAF_IN_EVENT_ID, $user_id);

			foreach ($order_product_info_nyaf as $order_product) {
				self::checkCreateAllCertificateNyafIn($order_product);
			}
		} else {
			echo "user not in event OR event not in date";die;
		}
	}

	private function checkCreateAllCertificateNyafIn($data = []) {
		log_kb(['createAllCertificateNyafIn:: ' => $data]);

		if(empty($book_id = $data['book_id']) || empty($quantity = $data['quantity']))
			echo "checkCreateAllCertificateNyafIn:: book or quantity empty";die;

		$this->load->model('event/Event_model', 'event_model');

		$event_info = $this->event_model->get(NYAF_IN_EVENT_ID);

		$filter_data = [
			'book_id'	=> $data['book_id'],
			'event_id'	=> NYAF_IN_EVENT_ID,
			'enddate'	=> date('Y-m-d', strtotime($event_info['end_date'])),
			'status'	=> 1,
			'start'		=> 0,
			'limit'		=> 1
		];

		$books = $this->book_model->get_all($filter_data);
		if(empty($books['rows'][0]))
			echo "checkCreateAllCertificateNyafIn:: book is empty";die;

		$book_info = $books['rows'][0];

		if(empty($user_id = $book_info['user_id']))
			echo "checkCreateAllCertificateNyafIn:: book user is empty";die;

		$lead_info = $this->user_model->get($user_id);
		if(empty($lead_info))
			echo "checkCreateAllCertificateNyafIn:: user lead info";die;

		$book_info['date'] = date('d/m/Y');
		$book_info['book_name'] = $book_info['name'];

		$book_info['site_id'] = $lead_info['site_id'];

		$book_info['quantity'] = $data['quantity'];

		$book_info['order_id'] = $data['order_id'];

		foreach (NYAF_IN_CERTIFICATE as $certificate_type => $certificate_eligiblity) {
			if(!empty($book_info['quantity']) && !empty(NYAF_IN_ALL_CERTIFICATE[$certificate_type]) && ($book_info['quantity'] >= NYAF_IN_ALL_CERTIFICATE[$certificate_type])) {
				$image_name = self::checkCreateAuthorCertificate_NyafIn($book_info, $certificate_type);
			} else {
				echo "checkCreateAuthorCertificate_NyafIn :: quantity of book is empty for certificate type";
			}
		}

		foreach (NYAF_IN_CERTIFICATE_ISBN as $certificate_type => $certificate_eligiblity) {
			if(!empty($book_info['quantity']) && !empty(NYAF_IN_ALL_CERTIFICATE[$certificate_type]) && ($book_info['quantity'] >= NYAF_IN_ALL_CERTIFICATE[$certificate_type])) {
				$image_name = self::checkCreateAuthorCertificateISBN_NyafIn($book_info, $certificate_type);
			} else {
				echo "checkCreateAuthorCertificateISBN_NyafIn :: quantity of book is empty for certificate type ";
			}

		}
	}

	private function checkCreateAuthorCertificate_NyafIn($data = [], $type = '') {
		if(!empty($data['quantity']) && !empty(NYAF_IN_ALL_CERTIFICATE[$type]) && ($data['quantity'] >= NYAF_IN_ALL_CERTIFICATE[$type])) {
			$image_name = $type . '_user_' . $data['user_id'] . '_' . $data['id'] . '.jpeg';
			$certificate_info_name = self::getByName($image_name, NYAF_IN_EVENT_ID);

			if($certificate_info_name) {
				echo "certificate_info_name already exist ".$certificate_info_name;
			}

		} else {
			echo "quantity of book is empty for certificate type in creation type ".$type." quantity ".$data['quantity'];
		}
	}

	private function checkCreateAuthorCertificateISBN_NyafIn($data = [], $type = '') {
		if(!empty($data['quantity']) && !empty(NYAF_IN_ALL_CERTIFICATE[$type]) && ($data['quantity'] >= NYAF_IN_ALL_CERTIFICATE[$type])) {
			$image_name = $type . '_user_' . $data['user_id'] . '_' . $data['id'] . '.jpeg';
			$certificate_info_name = self::getByName($image_name, NYAF_IN_EVENT_ID);
			if($certificate_info_name) {
				echo "certificate_info_name already exist ISBN".$certificate_info_name;die;
			}

			if(empty($data['isbn'])) {
				"ISBN is empty";die;
			}
		} else {
			echo "ISBN quantity of book is empty for certificate type in creation type ".$type." quantity ".$data['quantity'];
		}
	}

	public function getStateFromJsonFile($country = '') {
		if(empty($country))
			return;

		pr(_get_country_state($country), 1);
	}

	public function fixUsersCityState() {
		return;

		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('user/Student_model', 'student_model');

		$users = $this->db->query("
			SELECT `event_book`.`event_id`, `event_book`.`book_id`, book.id as b_id, users.id as uid , users.site_id, users.state_id, users.city_id
			FROM `event_book`
			join book on book.id = event_book.book_id
			join users on users.id = book.user_id
			WHERE `event_id` = '10'
			AND users.state_id = 0
			AND users.site_id != 1
			AND users.site_id != 71588
			AND users.site_id != 0
			group by users.id"
		)->result_array();

		// $users = $this->db->query("
		// 	SELECT `event_book`.`event_id`, `event_book`.`book_id`, book.id as b_id, users.id as uid , users.site_id, users.state_id, users.city_id
		// 	FROM `event_book`
		// 	join book on book.id = event_book.book_id
		// 	join users on users.id = book.user_id
		// 	WHERE `event_id` = '10'
		// 	AND users.city_id = 0
		// 	AND users.site_id != 1
		// 	group by users.id"
		// )->result_array();

		// pr($users,1);

		foreach ($users as $user) {

			if(!empty($user['uid'])) {
				$user_info = $this->student_model->get($user['uid']);
				if ($user_info) {
					$site_info = $this->site_model->get($user_info['site_id']);

					if (!empty($site_info) && !empty($site_info['state_id']) && !empty($site_info['city_id'])) {
						$this->student_model->edit($user_info['id'], [
							'state_id' 	=> $site_info['state_id'],
							'city_id' 	=> $site_info['city_id'],
						]);
					}
				}

			}
		}

		echo "user-state-updated";
	}

	public function missingEventOrderEnrolment() {
		return;

		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');

		$event_id = 10;

		/*$results = $this->db->query("
			SELECT product_id, SUM(quantity) as total
			FROM order_product
			JOIN book on book.id = order_product.product_id
			JOIN event_user on event_user.user_id = book.user_id AND event_user.event_id = '$event_id'
			WHERE
			product_id IN (SELECT book_id FROM event_book WHERE event_id = '$event_id' AND date_added IS NULL and _deleted = 0)
			AND order_id not in (SELECT order_id FROM event_order WHERE event_id = '$event_id')
			GROUP BY product_id
			order BY total DESC
		")->result_array();

		$results = $this->db->query("
			SELECT `order_product`.product_id,
			COALESCE(SUM(`order_product`.quantity), 0) as order_product_book_sold,
			(SELECT COALESCE(SUM(quantity), 0) FROM `event_order` WHERE `book_id`=`order_product`.product_id AND `event_order`.`event_id` = '$event_id') as event_order_book_sold
			FROM `order_product`
			JOIN `event_book` on `event_book`.book_id=`order_product`.product_id
			WHERE `event_book`.event_id='$event_id'
			GROUP BY `order_product`.product_id
			HAVING (order_product_book_sold!=event_order_book_sold)
			ORDER BY order_product_book_sold DESC
		")->result_array();*/

		pr(count($results));

		$missing_orders = [];

		foreach ($results as $result) {
			if(0 && !empty($order_product_results = $this->order_product_model->get_all([
				'book_id'	=> $result['product_id']
			])['rows'] ?? [])) {
				foreach ($order_product_results as $order_product_result) {
					$order_info = $this->db->get_where('order', [
						'id'	=> (int)$order_product_result['order_id']
					])->row_array();
					if(empty($event_order_results = $this->event_order_model->get_all([
						'event_id'		=> $event_id,
						'order_id'		=> $order_product_result['order_id'],
						'book_id'		=> $order_product_result['product_id']
					])['rows'] ?? [])) {
						$missing_orders[] = [
							'event_id'		=> (int)$event_id,
							'order_id'		=> (int)$order_product_result['order_id'],
							'book_id'		=> (int)$order_product_result['product_id'],
							'quantity'		=> (int)$order_product_result['quantity'],
							'date_added'	=> $order_info['date_added'],
							'date_modified'	=> date('Y-m-d 15:40:00'),
						];

						if (0 && empty($this->cron_model->getByCode('createCertificateNyafIn_' . $order_product_result['order_id']))) {
							$this->cron_model->add([
								'code'		  => 'createCertificateNyafIn_' . $order_product_result['order_id'],
								'action'		=> 'alert_model->createCertificateNyafIn',
								'data'		  => [$order_product_result['order_id']],
								'site_id'	   => '1',
								'alert_date'	=> date('Y-m-d H:i:00', strtotime('+5 minutes')),
							]);
						}

						if (0 && empty($this->cron_model->getByCode('createMedallionOnBookSoldNyafIn_' . $order_product_result['order_id']))) {
							$this->cron_model->add([
								'code'		  => 'createMedallionOnBookSoldNyafIn_' . $order_product_result['order_id'],
								'action'		=> 'alert_model->createMedallionOnBookSoldNyafIn',
								'data'		  => [$order_product_result['order_id']],
								'site_id'	   => '1',
								'alert_date'	=> date('Y-m-d H:i:00', strtotime('+10 minutes')),
							]);
						}
					}
				}
			}
		}

		if(0 && !empty($missing_orders)) {
			// array_multisort(array_column($missing_orders, 'order_id'), SORT_ASC, $missing_orders);

			// pr($missing_orders, 1);
			// $this->db->insert_batch('event_order', $missing_orders);
		}
	}

	public function missingEventData_18Jan() {
		return;

		$this->load->library('Ranking_lib', 'ranking_lib');

		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('common/Cron_model', 'cron_model');

		$event_id = 10;

		$results = $this->db->query("
			select
			(select sum(quantity) from order_product join `order` on `order`.id = order_product.order_id where `order`.date_added > '2023-08-01' and order_product.product_id = book.id) as total,
			(select event_id from event_book where book_id = book.id) as book_event_id,
			(select group_concat(event_id) from event_user where user_id = book.user_id) as user_event_id,
			(select users.location from users where users.id = book.user_id) as user_location,
			book.*
			from book where
			user_id in (select users.id from users WHERE `site_id` = '1' and users.id not in (select user_id from event_user))
			and book.id not in (select book_id from event_book)
			and book.status = 1
			and book.archived = 0
			and book._deleted = 0
			and book.date_published > '2023-08-01'
			and book.date_added > '2023-08-01'
			and book.date_added < '2024-01-15'
			and book.date_published < '2024-01-15'
			order by total desc;
		")->result_array();

		pr($results, 1);

		foreach ($results as $item) {
			// pr($item);

			/*pr([
				'event_id'		=> (int)$event_id,
				'book_id'		=> (int)$item['id'],
				'date_added'	=> $item['date_published'],
				'date_modified'	=> $item['date_published'],
			]);*/

			/*if (!($this->db->get_where('event_user', [
				'user_id' 	=> (int)$item['user_id'],
				'event_id' 	=> (int)$event_id,
			])->row_array())) {
				$this->db->insert('event_user', [
					'event_id'		=> (int)$event_id,
					'user_id'		=> (int)$item['user_id'],
					'date_added'	=> $item['date_published'],
					'date_modified'	=> date('Y-m-d 15:40:00'),
				]);
			}

			if (!($this->db->get_where('event_book', [
				'book_id' 	=> (int)$item['id'],
				'event_id' 	=> (int)$event_id,
			])->row_array())) {
				$this->db->insert('event_book', [
					'event_id'		=> (int)$event_id,
					'book_id'		=> (int)$item['id'],
					'date_added'	=> $item['date_published'],
					'date_modified'	=> date('Y-m-d 15:40:00'),
				]);
			}*/

			$orders = $this->db->get_where('order_product', [
				'product_id'	=> (int)$item['id']
			])->result_array();

			// pr($orders, 1);

			if(!empty($orders)) {
				foreach ($orders as $order) {
					// pr($order);

					$events = $this->event_book_model->get_all([
						'book_id'	=> (int)$order['product_id']
					])['rows'] ?? [];

					if(empty($events))
						continue;

					$order_info = $this->db->get_where('order', [
						'id'	=> (int)$order['order_id']
					])->row_array();

					// pr($order_info, 1);

					if(!empty($order)) {
						/*pr([
							'event_id'		=> (int)$event_id,
							'order_id'		=> (int)$order['order_id'],
							'book_id'		=> (int)$order['product_id'],
							'quantity'		=> (int)$order['quantity'],
							'date_added'	=> $order_info['date_added'],
							'date_modified'	=> $order_info['date_added'],
						]);*/

						// pr($order_info, 1);

						if (!($this->db->get_where('event_order', [
							'event_id'		=> (int)$event_id,
							'order_id'		=> (int)$order['order_id'],
							'book_id'		=> (int)$order['product_id'],
						])->row_array())) {
							/*$this->db->insert('event_order', [
								'event_id'		=> (int)$event_id,
								'order_id'		=> (int)$order['order_id'],
								'book_id'		=> (int)$order['product_id'],
								'quantity'		=> (int)$order['quantity'],
								'date_added'	=> $order_info['date_added'],
								'date_modified'	=> date('Y-m-d 15:40:00'),
							]);*/
						}
					}
				}

				if(!empty($order_id = end($orders)['order_id'])) {
					pr($order_id);

					/*$this->ranking_lib->updateRank($order_id);

					$this->cron_model->add([
						'code'		  => 'createCertificateNyafIn_' . $order_id,
						'action'		=> 'alert_model->createCertificateNyafIn',
						'data'		  => [$order_id],
						'site_id'	   => '1',
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+2 minutes')),
					]);

					$this->cron_model->add([
						'code'		  => 'createAwardsOnBookSoldNyafIn_' . $order_id,
						'action'		=> 'alert_model->createAwardsOnBookSoldNyafIn',
						'data'		  => [$order_id],
						'site_id'	   => '1',
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+3 minutes')),
					]);

					$this->cron_model->add([
						'code'		  => 'createMedallionOnBookSoldNyafIn_' . $order_id,
						'action'		=> 'alert_model->createMedallionOnBookSoldNyafIn',
						'data'		  => [$order_id],
						'site_id'	   => '1',
						'alert_date'	=> date('Y-m-d H:i:00', strtotime('+3 minutes')),
					]);*/
				}
			}

			// pr($item, 1);
		}
	}

	public function testBookstore($page = 1) {
		$filter_data = [
			'status'	=> 1,
			'start'		=> $page > 0 ? ($page - 1) * 16
				: 1,
			'limit'		=> 16,
			'sort'		=> 'sold',
			'order'		=> 'DESC'
		];

		$this->load->model('book/Bookstore_model', 'bookstore_model');
		$result = $this->bookstore_model->get_all($filter_data);

		pr($result, 1);
	}

	public  function testBinod($order_id=7419)
	{
		$this->load->library('Dropshipper_lib');
		$this->dropshipper_lib->assignDropshipper($order_id);
	}
}
