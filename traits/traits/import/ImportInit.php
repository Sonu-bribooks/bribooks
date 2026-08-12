<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ImportInit {
	private $error 		= [];
	private $columns 	= [];

	private function _init() {
		if ($this->session->userdata('admin_login') == false) {
			redirect(site_url('login'), 'refresh');
		}

		self::_loadModel();

		$this->types = [
			'school',
			'site',
			'state',
			'city',
			'author',
			'student_lead',
			'book_stock',
			'direct_order',
			'amazon_kdp_order',
			'crossword_store',
			'school_letter',
			'cover_tag',
			'broadcast_partner_slot',
			'user_invite_code',
			'school_invite_code',
			'jury_book',
			'user_award_address',
			'school_award_address',
			'school_letter_head',
			'pincode_zone',
			'author_calendar',
			'author_wall',
			'event_exhibition',
			'event_book_enrol',
			'deleted_user_modify',
			'event_rank_build',
			'book_ai_summary',
			'event_vote_book',
			'event_certificate',
			'event_literary_leader',
		];

		$this->columns['author'] = [
			'site_id',
			'first_name',
			'last_name',
			'mobile',
			'email',
			'grade',
			'section',
			'city_id',
			'state_id',
			'event_id',
		];

		$this->columns['state'] = [
			'country_id',
			'name',
		];

		$this->columns['city'] = [
			'state_id',
			'name',
		];

		$this->columns['teacher'] = [
			'site_id',
			'full_name',
			'mobile',
			'email',
		];

		$this->columns['site'] = [
			'parent_id',
			'country_id',
			'state_id',
			'city_id',
			'school_name',
			'email',
			'mobile',
			'address',
			'landmark',
			'zipcode',
			'site_type',
			'authorized_person',
			'owner_name',
			'event_id',
			'is_school_lead'
		];

		$this->columns['school'] = [
			'id',
			'site_id',
			'parent_id',
			'country_id',
			'state_id',
			'city_id',
			'school_name',
			'email',
			'mobile',
			'authorized_person',
			'alternate_email',
			'alternate_mobile',
			'alternate_authorized_person',
			'owner_name',
			'address',
			'landmark',
			'zipcode',
			'site_type',
			'tag',
		];

		$this->columns['book_stock'] = [
			'sku',
			'book_name',
			'quantity'
		];

		$this->columns['direct_order'] = [
			'reference_no',
			'event_name',
			'type',
			'consignee_name',
			'consignee_attention',
			'consignee_address1',
			'consignee_address2',
			'consignee_address3',
			'consignee_city',
			'consignee_state',
			'consignee_pincode',
			'consignee_telephone',
			'consignee_mobile',
			'consignee_email_id',
			'quantity',
			'actual_weight',
			'declared_value',
			'commodity_detail',
			'special_instruction',
			'length',
			'breadth',
			'height',
			'check_duplicate'
		];

		$this->columns['amazon_kdp_order'] = [
			'Royalty Date',
			'Order Date',
			'Title',
			'Author Name',
			'ISBN',
			'Marketplace',
			'Royalty Type',
			'Transaction Type',
			'Units Sold',
			'Units Refunded',
			'Net Units Sold',
			'Avg. List Price without tax',
			'Avg. Offer Price without tax',
			'Avg. Manufacturing Cost',
			'Royalty',
			'Currency',
			'ASIN'
		];

		$this->columns['student_lead'] = [
			'event_id',
			'site_id',
			'first_name',
			'last_name',
			'email',
			'mobile',
			'type'
		];

		$this->columns['crossword_store'] = [
			'store_location',
			'store_name',
			'book_isbn',
		];

		$this->columns['cover_tag'] = [
			'id',
			'tags',
		];

		$this->columns['school_letter'] = [
			'event_id',
			'school_id',
			'type',
			'quantity',
			'weight',
			'declared_value',
			'price',
			'length',
			'breadth',
			'height',
		];

		$this->columns['broadcast_partner_slot'] = [
			'event_id',
			'partner_id',
			'book_id',
			'rank',
			'start_date',
		];

		$this->columns['user_invite_code'] = [
			'event_id',
			'user_id',
			'referral_limit'
		];

		$this->columns['school_invite_code'] = [
			'event_id',
			'school_id',
			'site_id',
		];

		$this->columns['jury_book'] = [
			'type',
			'jury_challenge_id',
			'challenge_id',
			'event_id',
			'book_id',
			'user_id',
			'book_name',
			'author_name',
			'rank',
			'opening_score',
			'middle_score',
			'ending_score',
			'page_length_score',
			'total_score',
			'summary',
			'feedback',
			'url',
			'city_id',
			'state_id',
			'country_id',
		];

		$this->columns['user_award_address'] = [
			'event_id',
			'user_id',
		];

		$this->columns['school_award_address'] = [
			'event_id',
			'school_id',
			'site_id',
		];

		$this->columns['school_letter_head'] = [
			'id',
			'parent_id',
			'city_id',
			'state_id',
			'name',
			'group_name',
			'head_name',
			'head_first_name',
			'authorized_person',
			'alternate_authorized_person',
			'spoc_name',
			'book_name',
			'author_name',
			'designation',
			'reference_school',
			'top_school_1',
			'top_school_2',
			'url',
			'qrcode',
			'header',
			'subheader',
			'footer',
			'template',
		];

		$this->columns['pincode_zone'] = [
			'id',
			'pincode',
			'zone',
			'city',
			'state',
			'_deleted',
		];

		$this->columns['author_calendar'] = [
			'year',
			'user_id',
			'book_id',
			'book_name',
			'cover_image',
			'author_name',
			'author_image',
			'front_page',
			'page_1',
			'page_2',
			'page_3',
			'page_4',
			'page_5',
			'page_6',
		];

		$this->columns['author_wall'] = [
			'event_id',
			'id',
		];

		$this->columns['event_exhibition'] = [
			'event_id',
			'type',
			'user_id',
			'site_id',
			'book_id',
			'award',
			'interview',
			'wall',
		];

		$this->columns['event_book_enrol'] = [
			'event_id',
			'book_id',
			'gen_rank',
			'gen_certificate',
		];

		$this->columns['deleted_user_modify'] = [
			'user_id',
			'email',
			'mobile',
		];

		$this->columns['event_rank_build'] = [
			'event_id',
			'challenge_type',
			'challenge_id',
			'book_id',
		];

		$this->columns['book_ai_summary'] = [
			'event_id',
			'book_id',
			'version',
			'summary',
		];

		$this->columns['event_vote_book'] = [
			'event_id',
			'challenge_id',
			'book_id',
		];

		$this->columns['event_certificate'] = [
			'event_id',
			'book_id',
			'message_alert',
		];

		$this->columns['event_literary_leader'] = [
			'event_id',
			'type',
			'literary_leader_challenge_id',
			'site_id',
			'rank',
		];

		$this->default_values['student'] = [
			'0 (site_id from registration)',
			'First Name Last Name',
			'First Name Last Name',
			'9000000000',
			'abc@example.com',
			'YYYY-mm-dd HH:mm:ss',
			'1',
		];

		$this->default_values['teacher'] = [
			'0 (site_id from registration)',
			'First Name Last Name',
			'9000000000',
			'abc@example.com',
		];

		$this->default_values['school'] = [
			'id(Integer)if school is available else 0',
			'site_id(Integer)if primary site is available else 0',
			'(Integer)for group of school',
			'1',
			'state_id',
			'city_id',
			'school_name',
			'email@gmail.com',
			'987654323456',
			'authorized_person',
			'alternate_email',
			'alternate_mobile',
			'alternate_authorized_person',
			'owner_name',
			'address',
			'landmark',
			'zipcode',
			'1=School,3=School Chains,4=Community, 7=Country Site',
			'tag1,tag2',
		];

		$this->default_values['site'] = [
			'parent_id(Integer)if primary site is available else 0',
			'India = 1',
			'Delhi = 9',
			'New Delhi = 243',
			'Test School',
			'email_id',
			'phone_number',
			'full address',
			'landmark if available',
			'110001',
			'1=School,3=School chains, 4=Community',
			'authorized_person',
			'owner_name',
			'if event is available else 0',
			'1=yes,0=no'
		];

		$this->default_values['state'] = [
			'1',
			'Haryana'
		];

		$this->default_values['city'] = [
			'9',
			'Gurugram'
		];

		$this->default_values['book_stock'] = [
			'1234V1P',
			'My Book Name',
			'1',
		];

		$this->default_values['direct_order'] = [
			'TEST-123',
			'NYAF INDIA 2023',
			'Book/Certificate/Letters/Leaflet/Medallion/Trophy/Other',
			'name',
			'attention_name',
			'Unit 2101, BriBooks',
			'DLF Corporate Greens',
			'Gurugram, Haryana',
			'Gurugram',
			'Haryana',
			'110001',
			'011-123456',
			'8800199126',
			'schools@bribooks.com',
			'1',
			'0.5',
			'100',
			'BOOKS',
			'DOX',
			'10',
			'15',
			'2',
			'1/0'
		];

		$this->default_values['amazon_kdp_order'] = [
			date('Y-m-d'),
			date('Y-m-d'),
			'Testing Book',
			'tester',
			'1001000320614',
			'Amazon.com',
			'60%',
			'Standard - Paperback',
			'1',
			'0',
			'1',
			'20.00',
			'20.00',
			'3.60',
			'8.40',
			'USD',
			'320614'
		];

		$this->default_values['student_lead'] = [
			'1',
			'1',
			'student first name',
			'student last name',
			'student email',
			'student mobile',
			'email/mobile'
		];

		$this->default_values['author'] = [
			'site_id',
			'first_name',
			'last_name',
			'mobile',
			'email',
			'grade',
			'section',
			'city_id',
			'state_id',
			'event_id',
		];

		$this->default_values['crossword_store'] = [
			'Mumbai',
			'Kemps Corner Showroom (5001)',
			'ISBN13255363'
		];

		$this->default_values['cover_tag'] = [
			12,
			'Tag1,Tag2',
		];

		$this->default_values['school_letter'] = [
			'10',
			'123',
			'Letter/Cert',
			'1',
			'350',
			'100',
			'100',
			'32',
			'24',
			'2',
		];

		$this->default_values['broadcast_partner_slot'] = [
			'0',
			'1',
			'2345654',
			'1',
			'2024-09-08 09:04:01'
		];

		$this->default_values['user_invite_code'] = [
			'',
			'',
			'0'
		];

		$this->default_values['school_invite_code'] = [
			'',
			'',
			'',
		];

		$this->default_values['jury_book'] = [
			'type',
			'jury_challenge_id',
			'challenge_id',
			'event_id',
			'book_id',
			'user_id',
			'book_name',
			'author_name',
			'rank',
			'opening_score',
			'middle_score',
			'ending_score',
			'page_length_score',
			'total_score',
			'summary',
			'feedback',
			'url',
			'0',
			'0',
			'0'
		];

		$this->default_values['user_award_address'] = [
			'',
			'',
		];

		$this->default_values['school_award_address'] = [
			'',
			'',
			'',
		];

		$this->default_values['school_letter_head'] = [
			'id(required)',
			'parent_id',
			'city_id',
			'state_id',
			'name',
			'group_name',
			'head_name',
			'head_first_name',
			'authorized_person',
			'alternate_authorized_person',
			'spoc_name',
			'book_name',
			'author_name',
			'designation',
			'reference_school',
			'top_school_1',
			'top_school_2',
			'url(required)',
			'qrcode',
			'header_img(required)',
			'subheader',
			'footer_img(required)',
			'template_html(required)',
		];

		$this->default_values['pincode_zone'] = [
			'id if update else empty',
			'0000',
			'A,B,C,D,E,F',
			'',
			'',
			'0/1',
		];

		$this->default_values['author_calendar'] = [
			date('Y'),
			'user_id (Integer)',
			'book_id (Integer)',
			'book_name (String)',
			'media.bribooks url (URL)',
			'author_name (String)',
			'media.bribooks url (URL)',
			'media.bribooks url (URL)',
			'media.bribooks url (URL)',
			'media.bribooks url (URL)',
			'media.bribooks url (URL)',
			'media.bribooks url (URL)',
			'media.bribooks url (URL)',
			'media.bribooks url (URL)',
		];

		$this->default_values['author_wall'] = [
			'event_id(from event table)',
			'id(from author wall table)',
		];

		$this->default_values['event_exhibition'] = [
			'0(required)',
			'user/school',
			'0(default 0)',
			'0(required for type school)',
			'0(required for type book)',
			'awards multiple comma separated',
			'interviews multiple comma separated',
			'walls multiple comma separated',
		];

		$this->default_values['event_book_enrol'] = [
			'0, (id from event)',
			'0, (id from book)',
			'0, (1 for yes)',
			'0, (1 for yes)',
		];

		$this->default_values['deleted_user_modify'] = [
			'0, (id from user)',
			'email@email.com',
			'919876543210 (12 digit including 91)',
		];

		$this->default_values['event_rank_build'] = [
			'0 (id from event)',
			'general, weekly, city, state, country',
			'0 (id from challenge_type table)',
			'0 (id from book)',
		];

		$this->default_values['book_ai_summary'] = [
			'0(required)',
			'0(required)',
			'1(required)',
			'summary',
		];

		$this->default_values['event_vote_book'] = [
			'0(required)',
			'0(required)',
			'1(required)',
		];

		$this->default_values['event_certificate'] = [
			'0(required)',
			'0(required)',
			'0(required)',
		];
		$this->default_values['event_literary_leader'] = [
			'0(required)',
			'0(required)',
			'0(required, Id from event_literary_leader table)',
			'0(required)',
			'0(required)',
		];

		$this->debug = true;
	}

	private function _loadModel() {
		$this->load->model('order/Payment_model', 'payment_model');
		$this->load->model('design/Cover_model', 'cover_model');
		$this->load->model('user/Student_model', 'student_model');

		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('common/Course_model', 'course_model');
		$this->load->model('common/Enrol_model', 'enrol_model');
		$this->load->model('common/Grade_model', 'grade_model');
		$this->load->model('common/Section_model', 'section_model');
		$this->load->model('common/ImportJob_model', 'import_job_model');
		$this->load->model('common/Cron_model', 'cron_model');

		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventSite_model', 'event_site_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/AuthorWall_model', 'author_wall_model');
		$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');

		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('book/CrosswordStore_model', 'cross_word_store_model');
		$this->load->model('book/CrosswordBook_model', 'cross_word_book_model');

		$this->load->model('school/SchoolOrder_model', 'school_order_model');
		$this->load->model('school/School_model', 'school_model');

		$this->load->model('localisation/Currency_model', 'currency_model');
		$this->load->model('localisation/Country_model', 'country_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('localisation/PincodeZone_model', 'pincode_zone_model');
	}

	private function writeRowToCsv($results = [], $fp = null, $headers = []) {
		if (is_array($results) && $results && is_resource($fp) && is_array($headers) && $headers) {
			fputs($fp, "\xEF\xBB\xBF");
			fputcsv($fp, $headers);

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

	public function download($type = 'students') {
		$filename 	= 'sample_' . preg_replace(['/[^\w\s]/', '/\s+/'], [' ', ' '], $type) . '_' . date('Y_m_d_H_i_s') . '.csv';
		$fields 	= $this->columns[$type] ?? [];
		$results[] 	= array_combine($fields, $this->default_values[$type]);

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
			exit($this->lang->line('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		$this->writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	private function _generateImportJob($data = []) {
		$job_id = $this->import_job_model->add([
			'name'		=> $data['name'] ?? '',
			'csv'		=> $data['csv'] ?? '',
			'action'	=> $data['action'] ?? '',
			'map'		=> json_encode($data['map'] ?? []),
			'total'		=> $data['total'],
		]);

		$data['job_id'] = $job_id;

		self::generateImportChunk($data);
	}
}
