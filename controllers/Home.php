<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

load_trait('home');
load_trait('frontend');

class Home extends CI_Controller {

	public function __construct() {
		parent::__construct();

		if (ENVIRONMENT === 'production' && $this->session->userdata('admin_login') == false) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->model('common/Slot_model', 'slot_model');
		$this->load->model('common/Course_model', 'course_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('common/Class_model', 'class_model');
		$this->load->model('common/Schedule_model', 'schedule_model');
		$this->load->model('user/Lead_model', 'lead_model');
		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('common/Enrol_model', 'enrol_model');
		$this->load->model('localisation/Center_model', 'center_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('common/Report_model', 'report_model');
		$this->load->model('localisation/Country_model', 'country_model');
		$this->load->model('localisation/Currency_model', 'currency_model');
		$this->load->model('Crud_model', 'crud_model');
		$this->load->model('school/SchoolDetail_model', 'schooldetail_model');
		$this->load->model('school/SchoolInput_model', 'schoolinput_model');
		$this->load->model('school/SchoolLead_model', 'school_lead_model');
		$this->load->model('school/School_model', 'school_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('event/Event_model', 'event_model');

		// $this->load->library('stripe');

		$this->load->library('form_validation');
		$this->load->library('Cart_lib');

		if (!$this->session->userdata('cart_items')) {
			$this->session->set_userdata('cart_items', array());
		}
	}

	use
		Enrol,
		Cart,
		Zoom,
		Certificate,
		Old,
		Test,
		Test2024,
		CommonLogin,
		EventLogin,
		ExportRank,
		ExportCertificate,
		Logistic,
		WebinarSchedule,
		Script,
		NyafInd2022,
		Nfwis2023,
		SummerCamp2023,
		Shipping,
		Uae2023,
		NyafUs2023,
		NyafInd2023,
		EventPrep,
		PrintAuthorWall
	;

	public function index() {
		redirect('home/login', 'refresh');

		$this->home();
	}

	public function home() {
		$data['page_name'] 	= 'home';
		$data['page_title'] = _l('home');

		$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
	}

	public function nyafbbllkkfg56() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url(), 'refresh');
		}

		$data['page_name'] 	= 'NYAF';
		$data['page_title'] = _l('NYAF');

		$data['data'] = [
			'school_register' 		=> $this->crud_model->sum_school_register(false, 'NYAFIND2022'),
			'new_school_register' 	=> $this->crud_model->sum_school_register(true, 'NYAFIND2022'),
			'users' 				=> $this->crud_model->sum_school_students(false, 'NYAFIND'),
			'new_users' 			=> $this->crud_model->sum_school_students(true, 'NYAFIND'),
			'books' 				=> $this->crud_model->sum_school_books(false, 'NYAFIND'),
			'new_books' 			=> $this->crud_model->sum_school_books(true, 'NYAFIND'),
			'publish_book' 			=> $this->crud_model->sum_school_books_published(false, 'NYAFIND'),
			'new_publish_book' 		=> $this->crud_model->sum_school_books_published(true, 'NYAFIND'),
			'ordered_books' 		=> $this->crud_model->sum_nyaf_ordered_book(false, 'NYAFIND'),
			'new_ordered_books' 	=> $this->crud_model->sum_nyaf_ordered_book(true, 'NYAFIND'),
			'orders' 				=> $this->crud_model->sum_nyaf_orderes(false, 'NYAFIND'),
			'new_orders' 			=> $this->crud_model->sum_nyaf_orderes(true, 'NYAFIND'),
		];

		$data['school_url'] 	= site_url('admin/sites?site_code=NYAFIND2022');
		$data['order_url'] 	= site_url('admin/all_orders?site_code=NYAFIND2022');

		$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
	}

	public function nwfisbbllkkfg56() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url(), 'refresh');
		}

		$data['page_name'] 	= 'NYAF';
		$data['page_title'] = _l('NWFIS');

		$data['data'] = [
			'school_register' 		=> $this->crud_model->sum_school_register(false, 'ge-NWFIS'),
			'new_school_register' 	=> $this->crud_model->sum_school_register(true, 'ge-NWFIS'),
			'users' 				=> $this->crud_model->sum_school_students(false, 'ge-NWFIS'),
			'new_users' 			=> $this->crud_model->sum_school_students(true, 'ge-NWFIS'),
			'books' 				=> $this->crud_model->sum_school_books(false, 'ge-NWFIS'),
			'new_books' 			=> $this->crud_model->sum_school_books(true, 'ge-NWFIS'),
			'publish_book' 			=> $this->crud_model->sum_school_books_published(false, 'ge-NWFIS'),
			'new_publish_book' 		=> $this->crud_model->sum_school_books_published(true, 'ge-NWFIS'),
			'ordered_books' 		=> $this->crud_model->sum_nyaf_ordered_book(false, 'ge-NWFIS'),
			'new_ordered_books' 	=> $this->crud_model->sum_nyaf_ordered_book(true, 'ge-NWFIS'),
			'orders' 				=> $this->crud_model->sum_nyaf_orderes(false, 'ge-NWFIS'),
			'new_orders' 			=> $this->crud_model->sum_nyaf_orderes(true, 'ge-NWFIS'),
		];

		$data['school_url'] 	= site_url('admin/sites?site_code=ge-NWFIS');
		$data['order_url'] 	= site_url('admin/all_orders?site_code=ge-NWFIS');

		$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
	}

	public function kidodashboard() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url(), 'refresh');
		}

		$data['page_name'] 	= 'NYAF';
		$data['page_title'] = _l('KIDO');

		$data['data'] = [
			'school_register' 		=> $this->crud_model->sum_school_register(false, 'ge-KIDO'),
			'new_school_register' 	=> $this->crud_model->sum_school_register(true, 'ge-KIDO'),
			'users' 				=> $this->crud_model->sum_school_students(false, 'ge-KIDO'),
			'new_users' 			=> $this->crud_model->sum_school_students(true, 'ge-KIDO'),
			'books' 				=> $this->crud_model->sum_school_books(false, 'ge-KIDO'),
			'new_books' 			=> $this->crud_model->sum_school_books(true, 'ge-KIDO'),
			'publish_book' 			=> $this->crud_model->sum_school_books_published(false, 'ge-KIDO'),
			'new_publish_book' 		=> $this->crud_model->sum_school_books_published(true, 'ge-KIDO'),
			'ordered_books' 		=> $this->crud_model->sum_nyaf_ordered_book(false, 'ge-KIDO'),
			'new_ordered_books' 	=> $this->crud_model->sum_nyaf_ordered_book(true, 'ge-KIDO'),
			'orders' 				=> $this->crud_model->sum_nyaf_orderes(false, 'ge-KIDO'),
			'new_orders' 			=> $this->crud_model->sum_nyaf_orderes(true, 'ge-KIDO'),
		];

		$data['school_url'] 	= site_url('admin/sites?site_code=ge-kido');
		$data['order_url'] 	= site_url('admin/all_orders?site_code=ge-kido');

		$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
	}

	public function scdashboard() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url(), 'refresh');
		}

		$data['page_name'] 	= 'NYAF';
		$data['page_title'] = _l('Summer Camp');

		$data['data'] = [
			'school_register' 		=> $this->crud_model->sum_school_register(false, 'in-sc'),
			'new_school_register' 	=> $this->crud_model->sum_school_register(true, 'in-sc'),
			'users' 				=> $this->crud_model->sum_school_students(false, 'in-sc'),
			'new_users' 			=> $this->crud_model->sum_school_students(true, 'in-sc'),
			'books' 				=> $this->crud_model->sum_school_books(false, 'in-sc'),
			'new_books' 			=> $this->crud_model->sum_school_books(true, 'in-sc'),
			'publish_book' 			=> $this->crud_model->sum_school_books_published(false, 'in-sc'),
			'new_publish_book' 		=> $this->crud_model->sum_school_books_published(true, 'in-sc'),
			'ordered_books' 		=> $this->crud_model->sum_nyaf_ordered_book(false, 'in-sc'),
			'new_ordered_books' 	=> $this->crud_model->sum_nyaf_ordered_book(true, 'in-sc'),
			'orders' 				=> $this->crud_model->sum_nyaf_orderes(false, 'in-sc'),
			'new_orders' 			=> $this->crud_model->sum_nyaf_orderes(true, 'in-sc'),
		];

		$data['school_url'] 	= site_url('admin/sites?site_code=in-sc');
		$data['order_url'] 	= site_url('admin/all_orders?site_code=in-sc');

		$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
	}

	public function login() {
		redirect('login', 'refresh');

		if ($this->session->userdata('user_login') && $this->session->userdata('user_id')) {
			redirect(site_url('home/parent_dashboard'), 'refresh');
		}

		// $data['page_name'] 	= date('Y') . '/login';
		$data['page_name'] 	= 'login';
		$data['page_title'] = _l('login');
		$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
	}

	public function parent_dashboard() {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url(), 'refresh');
		} else if (!$this->user_model->get($this->session->userdata('user_id'))) {
			redirect(site_url('login/logout/user'));
		}

		$user_info = $this->user_model->get($this->session->userdata('user_id'));

		self::icodeLogin([
			'email'		=> $user_info['email'],
			'password'	=> $user_info['password'],
		]);

		// $this->alert_model->sms('9716120257', str_replace('{otp}', '333333', get_settings('sms_otp')));

		// $data['page_name'] 		= in_array($this->session->userdata('user_email'), TESTING_EMAILS) ? 'parent_dashboard_event' : 'parent_dashboard';
		$data['page_name'] 		= date('Y') . '/parent_dashboard';
		$data['page_title'] 	= _l('dashboard');

		$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
	}

	public function webinar() {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url(), 'refresh');
		} else if (!$this->user_model->get($this->session->userdata('user_id'))) {
			redirect(site_url('login/logout/user'));
		}

		$data['page_name'] 		= date('Y') . '/webinar';
		$data['page_title'] 	= _l('webinar');

		$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $data);
	}

	public function my_courses() {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url(), 'refresh');
		}

		$data['page_name'] = "my_courses";
		$data['page_title'] = _l("my_courses");
		$this->load->view('frontend/'.get_frontend_settings('theme').'/index', $data);
	}

	public function re_schedule($slug = "", $course_id = "", $lesson_id = "") {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url('home'), 'refresh');
		}

		$data['course_id'] = $course_id;
		$data['student_id'] = 10;
		$data['page_name'] = "re_schedule";
		$data['page_title'] = _l('re_schedule');
		$this->load->view('frontend/'.get_frontend_settings('theme').'/index', $data);
	}

	public function payment() {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url('home'), 'refresh');
		}

		$total_rows = $this->crud_model->purchase_history($this->session->userdata('user_id'))->num_rows();

		$config = array();
		$config = pagintaion($total_rows, 3);
		$config['base_url']  = site_url('home/payment');

		$this->pagination->initialize($config);

		$data['per_page']   = $config['per_page'];
		$data['page_name']  = "purchase_history";
		$data['page_title'] = _l('purchase_history');

		$data['items']		= $this->cart_lib->getItems();
		$data['total']		= $this->cart_lib->getTotal();

		$this->load->view('frontend/'.get_frontend_settings('theme').'/index', $data);
	}

	public function profile($param1 = "") {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url('home'), 'refresh');
		}

		if ($param1 == 'user_profile') {
			$data['page_name'] = "user_profile";
			$data['page_title'] = _l('user_profile');
		}elseif ($param1 == 'user_credentials') {
			$data['page_name'] = "user_credentials";
			$data['page_title'] = _l('credentials');
		}elseif ($param1 == 'user_photo') {
			$data['page_name'] = "update_user_photo";
			$data['page_title'] = _l('update_user_photo');
		}
		$data['user_details'] = $this->user_model->get($this->session->userdata('user_id'));
		$this->load->view('frontend/'.get_frontend_settings('theme').'/index', $data);
	}

	public function events($course_id) {
		$schedules = $this->schedule_model->get_all([
			'course_id'	=> $course_id,
			'student_id' => $this->session->user_id
		])->result_array();

		$json = [];

		foreach ($schedules as $schedule) {
			$json[] = self::formatEvent($schedule);
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	private function formatEvent($schedule) {
		$start = date('Y-m-d H:i:s', strtotime($schedule['schedule']));
		$end = date('Y-m-d H:i:s', strtotime('+40 minutes', strtotime($schedule['schedule'])));

		$course_info = $this->course_model->get($schedule['course_id'])->row_array();

		return [
			'id'				=> $schedule['id'],
			'class_id'			=> $schedule['class_id'],
			'title'				=> $course_info ? $course_info['title'] : '',
			'start'				=> $start,
			'end'				=> $end,
			'link'				=> site_url(),
			'slot'				=> date('H:i:s', strtotime($schedule['schedule'])),
			'className'			=> 'bg-info',
			'description'		=> "{$schedule['class']}<br>{$start} {$end}",
		];
	}

	public function reschedule() {
		$json = [];

		if (($this->input->method() == 'post')) {
			$this->form_validation->set_rules('id', _l('schedule_id'), 'trim|required|numeric');
			$this->form_validation->set_rules('schedule', _l('schedule'), 'trim|required');
			$this->form_validation->set_rules('reason', _l('reason'), 'trim|required');

			$valid = $this->form_validation->run();

			!$valid && ($json['error'] = validation_errors());

			if (!$json) {
				$this->schedule_model->addReschedule([
					'schedule_id'		=> (int)$this->input->post('id'),
					'reason'			=> $this->input->post('reason'),
					'schedule'			=> date('Y-m-d H:i:s', strtotime($this->input->post('schedule'))),
					'student_id'		=> (int)$this->session->user_id,
				]);

				$json['error'] = $this->session->flashdata('error_message');
				$json['success'] = $this->session->flashdata('flash_message');
			}
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function update_profile($param1 = "") {
		if ($param1 == 'update_basics') {
			// $this->user_model->edit($this->session->userdata('user_id'));
		} elseif ($param1 == "update_credentials") {
			// $this->user_model->update_account_settings($this->session->userdata('user_id'));
		} elseif ($param1 == "update_photo") {
			// $this->user_model->upload_user_image($this->session->userdata('user_id'));
			$this->session->set_flashdata('flash_message', _l('updated_successfully'));
		}

		redirect(site_url('home/profile/user_profile'), 'refresh');
	}

	public function isLoggedIn() {
		if ($this->session->userdata('user_login') == 1)
		echo true;
		else
		echo false;
	}


	public function getAllRankings() {
		$this->load->model('competition/Competition_model', 'competition_model');
		$this->load->model('order/Order_model', 'order_model');
		if (!$this->json) {
			$type = (int)$this->input->post('type');

			$this->load->library('Royalty_lib', 'royalty_lib');

			$competition_info = $this->competition_model->get_all([
				'site_id'	=> 0,
			])['rows'][0] ?? [];

			$filter_data = [
				'site_code'	=> 'NYAFIND2022',
				// 'end_date'	=> $competition_info['end_date'] ?? '', // '2022-11-03 21:00:00',
			];

			if ($this->input->post('state_id')) {
				$filter_data['state_id'] = $this->input->post('state_id');
			}

			if ($this->input->post('city_id')) {
				$filter_data['city_id'] = $this->input->post('city_id');
			}

			if ($this->input->post('site_id')) {
				$filter_data['site_id'] = $this->input->post('site_id');
			}

			if ($this->input->post('grade_id')) {
				$filter_data['grade_id'] = $this->input->post('grade_id');
			}

			if ($this->input->post('section_id')) {
				$filter_data['section_id'] = $this->input->post('section_id');
			}

			if ($this->input->post('search')) {
				$filter_data['search'] = $this->input->post('search');
			}

			$result = $this->order_model->getTopSoldBooks($filter_data);

			$rankings = [];

			$rank = 1;
			$total = 0;

			foreach ($result ?? [] as $key => $item) {

				$order_total = $this->order_model->getTotalProductsByProductId($item['id']);

				$ranking = [
					'id'			=> $item['id'],
					'rank'			=> $rank,
					'name'			=> ucfirst($item['name']),
					'cover_image'	=> $item['cover_image'],
					'author_name'	=> $item['author_name'],
					'author_image'	=> $item['author_image'],
					'slug'			=> $item['slug'],
					'royalty'		=> currency($this->royalty_lib->getBookTotalRoyality($item['id']), 0),
					'sold'			=> readable_format(!empty($item['quantity']) ? $item['quantity'] : 0),
					'total_sold'	=> readable_format($order_total ? $order_total : 0),
				];

				$rankings[] = $ranking;
				// $rankings[] = self::_addRatingAndSold($ranking);
				$total += !empty($item['quantity']) ? $item['quantity'] : 0;
			}

			$this->json['total'] = $total;
			return $this->json;
		}
	}

	public function updateEventTopRankers() {
		return;

		$this->load->model('event/EventTopRanker_model', 'event_top_ranker_model');

		$books = array_map(function ($item) {
			/*if(empty($item['book_id']) || empty($item['user_id']) || empty($item['book_slug'])) {
				$book_results = $this->book_model->get_all([
					'book_name' 	=> $item['book_name'],
					'author_name' 	=> $item['author_name']
				])['rows'] ?? [];

				if(count($book_results)) {
					$book_info = $book_results[0];

					$item['book_id'] = $book_info['id'];
					$item['user_id'] = $book_info['user_id'];
					$item['book_slug'] 	 = $book_info['slug'];

					$book_data = [];
					$book_data['book_id'] 	= $book_info['id'];
					$book_data['user_id'] 	= $book_info['user_id'];
					$book_data['book_slug']	= $book_info['slug'];

					$this->event_top_ranker_model->edit($item['id'], $book_data);
				}
			}*/

			$book_info = $this->book_model->get($item['book_id']);

			$author_image = empty($book_info['author_image']) ? base_url('uploads/user_image/placeholder.png') : $this->config->item('s3_base_url') . 'public/' . $book_info['author_image'];

			return [
				'id'			=> $item['id'],
				'book_id'		=> $item['book_id'],
				'user_id'		=> $item['user_id'],
				'book_name'		=> $item['book_name'],
				'author_name'	=> $item['author_name'],
				'book_image'	=> $this->config->item('s3_base_url') . 'public/' . $book_info['cover_image'],
				'author_image'	=> $author_image,
				'book_url'		=> !empty($book_info['amazon_url']) ? $book_info['amazon_url'] : (USER_URL . 'bookstore/' . $item['book_slug'])
			];
		}, $this->event_top_ranker_model->get_all([
			'sort'	=> 'event_top_rankers.score',
			'order'	=> 'DESC'
		])['rows'] ?? []);

		pr($books, 1);
	}
}
