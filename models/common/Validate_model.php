<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Validate_model extends CI_Model {
	public function __construct() {
		parent::__construct();

		$this->load->model('common/Slot_model', 'slot_model');
		$this->load->model('common/Site_model', 'site_model');

		$this->load->model('school/School_model', 'school_model');
		$this->load->model('school/SchoolLead_model', 'school_lead_model');

		$this->load->model('localisation/Country_model', 'country_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('localisation/Center_model', 'center_model');
		$this->load->model('localisation/State_model', 'state_model');

		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('user/Telecaller_model', 'telecaller_model');
		$this->load->model('user/Lead_model', 'lead_model');
		$this->load->model('user/UserCover_model', 'user_cover_model');
		$this->load->model('user/teacher/TeacherLead_model', 'teacher_lead_model');
		$this->load->model('user/teacher/Teacher_model', 'teacher_model');

		$this->load->model('design/Genre_model', 'genre_model');
		$this->load->model('design/Category_model', 'category_model');
		$this->load->model('design/Theme_model', 'theme_model');
		$this->load->model('design/Cover_model', 'cover_model');

		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/Page_model', 'page_model');
		$this->load->model('book/CustomTheme_model', 'custom_theme_model');
		$this->load->model('book/CustomCover_model', 'custom_cover_model');

		$this->load->model('address/Address_model', 'address_model');
		$this->load->model('order/Order_model', 'order_model');

		$this->load->model('subscription/SubscriptionPlan_model', 'subscription_plan_model');
		$this->load->model('subscription/SubscriptionOrder_model', 'subscription_order_model');

		$this->load->model('competition/Competition_model', 'competition_model');
		$this->load->model('competition/CompetitionOrder_model', 'competition_order_model');
		$this->load->model('competition/CompetitionPayment_model', 'competition_payment_model');

		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventChallenge_model', 'event_challenge_model');
		$this->load->model('event/EventChallengeWeekly_model', 'event_challenge_weekly_model');
		$this->load->model('event/EventChallengeDaily_model', 'event_challenge_daily_model');
		$this->load->model('event/EventChallengeCountry_model', 'event_challenge_country_model');
		$this->load->model('event/EventChallengeState_model', 'event_challenge_state_model');
		$this->load->model('event/EventChallengeCity_model', 'event_challenge_city_model');
		$this->load->model('event/EventChallengeSchool_model', 'event_challenge_school_model');
		$this->load->model('event/EventChallengeGenre_model', 'event_challenge_genre_model');
		$this->load->model('event/EventChallengeGeneral_model', 'event_challenge_general_model');
		$this->load->model('event/EventChallengeGroup_model', 'event_challenge_group_model');
		$this->load->model('event/EventChallengeVote_model', 'event_challenge_vote_model');
		$this->load->model('event/EventLeagueGroup_model', 'event_league_group_model');

		$this->load->model('common/Notification_model', 'notification_model');

		$this->load->library('form_validation');
	}

	public function studentAge($str) {
		if (!preg_match('/(\d{1,2}\-\d{1,2}|\d{1,2})/', $str)) {
			$this->form_validation->set_message('student_age', _li('The {field} format eg.5-7'));
			return false;
		}

		return true;
	}

	public function demoDate($str) {
		if (strtotime($str) < time()) {
			$this->form_validation->set_message('demo_date', _li('The {field} can`t less than current date'));
			return false;
		}

		return true;
	}

	public function demoTime($str) {
		return true;
	}

	public function center($str) {
		if ($this->input->post('learning_mode') == 'offline' && !$this->center_model->get($str)->row_array()) {
			$this->form_validation->set_message('center', _li('The {field} is required'));
			return false;
		}

		return true;
	}

	public function city($str) {
		if (!$this->city_model->get($str)) {
			$this->form_validation->set_message('city', _li('The {field} is required'));
			return false;
		}

		return true;
	}

	public function state($str) {
		if (!$this->state_model->get($str)) {
			$this->form_validation->set_message('state', _li('The {field} is required'));
			return false;
		}

		return true;
	}

	public function country($str) {
		if (!$this->country_model->get($str)) {
			$this->form_validation->set_message('country', _li('The {field} is required'));
			return false;
		}

		return true;
	}

	public function lead($str) {
		if (!$this->lead_model->get($str)) {
			$this->form_validation->set_message('lead', _li('The {field} is required'));
			return false;
		}

		return true;
	}

	public function userLead($str) {
		if (!$this->lead_model->get($str)) {
			$this->form_validation->set_message('lead', _li('The {field} is required'));
			return false;
		}

		return true;
	}

	public function schoolLead($str) {
		if (!$this->school_lead_model->get($str)) {
			$this->form_validation->set_message('lead', _li('The {field} is required'));
			return false;
		}

		return true;
	}

	public function teacherLead($str) {
		if (!$this->teacher_lead_model->get($str)) {
			$this->form_validation->set_message('lead', _li('The {field} is not valid'));
			return false;
		}

		return true;
	}

	public function teacher($str) {
		if (!$this->teacher_model->get($str)) {
			$this->form_validation->set_message('teacher', _li('The {field} is not valid'));
			return false;
		}

		return true;
	}

	public function student($str) {
		if (!$this->student_model->get($str)) {
			$this->form_validation->set_message('student', _li('The {field} is not valid'));
			return false;
		}

		return true;
	}

	public function user($str) {
		if (!$this->user_model->get($str)) {
			$this->form_validation->set_message('user', _li('The {field} is not valid'));
			return false;
		}

		return true;
	}

	public function notification($str) {
		if (!$this->notification_model->getById($str)) {
			$this->form_validation->set_message('notification', _li('The {field} is not valid'));
			return false;
		}

		return true;
	}

	public function portalCode($str) {
		if (!$this->site_model->getByCode($str)) {
			$this->form_validation->set_message('portal_code', _li('The {field} is not valid'));
			return false;
		}

		return true;
	}

	public function site($str) {
		if (!$this->site_model->get($str)) {
			$this->form_validation->set_message('site', _li('The {field} is not valid'));
			return false;
		}

		return true;
	}

	public function school($str) {
		if (!$this->school_model->get($str)) {
			$this->form_validation->set_message('school', _li('The {field} is not valid'));
			return false;
		}

		return true;
	}

	public function genre($str) {
		if (!$this->genre_model->get($str)) {
			$this->form_validation->set_message('genre', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function category($str) {
		if (!$this->category_model->get($str)) {
			$this->form_validation->set_message('category', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function book($str) {
		if (!$this->book_model->get($str)) {
			$this->form_validation->set_message('book', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function page($str) {
		if (!$this->page_model->get($str)) {
			$this->form_validation->set_message('page', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function theme($str) {
		if (!$this->theme_model->get($str)) {
			$this->form_validation->set_message('theme', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function custom_theme($str) {
		if (!$this->custom_theme_model->get($str)) {
			$this->form_validation->set_message('custom_theme', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function cover($str) {
		if (!$this->cover_model->get($str)) {
			$this->form_validation->set_message('cover', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function user_cover($str) {
		if (!$this->user_cover_model->get($str)) {
			$this->form_validation->set_message('user_cover', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function custom_cover($str) {
		if (!$this->custom_cover_model->get($str)) {
			$this->form_validation->set_message('custom_cover', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function subscription_plan($str) {
		if (!$this->subscription_plan_model->get($str)) {
			$this->form_validation->set_message('subscription_plan', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function subscription_order($str) {
		if (!$this->subscription_order_model->get($str)) {
			$this->form_validation->set_message('subscription_order', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function address($str) {
		if (!$this->address_model->get($str)) {
			$this->form_validation->set_message('address', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function order($str) {
		if (!$this->order_model->get($str)) {
			$this->form_validation->set_message('order', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function spam($str) {
		$str = preg_replace(['/[^\w\s]/', '/\s+/'], [' ', ' '], trim($str));

		if (empty(trim($str))) return true;

		if (strlen($str) > 700) {
			$this->form_validation->set_message('spam', _li('{field} is exceeding characters limit'));
			return false;
		}

		if ($result = $this->page_model->checkSpamWords($str)) {
			$spam_words = implode(', ', array_column($result, 'word'));
			$this->form_validation->set_message('spam', _li('{field} contains spam word - ' . $spam_words));
			return false;
		}

		return true;
	}

	public function courier($str) {
		$results = array_filter($this->session->userdata('couriers')['data'] ?? [], function($item) use($str) {
			return $str == ($item['id'] ?? '');
		});

		if (count($results) === 0) {
			$this->form_validation->set_message('courier', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function competition($str) {
		if (!$this->competition_model->get($str)) {
			$this->form_validation->set_message('competition', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function grade($str) {
		$grades = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 101, 102, 103, 104, 105];

		if (!in_array($str, $grades)) {
			$this->form_validation->set_message('grade', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function grade_type($str) {
		if (!in_array($str, ['general', 'primary'])) {
			$this->form_validation->set_message('grade_type', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function section($str) {
		$sections = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');

		if (!in_array(mb_strtoupper($str), $sections)) {
			$this->form_validation->set_message('section', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event($str) {
		if (!$this->event_model->get($str)) {
			$this->form_validation->set_message('event', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function designation($str) {
		if (!in_array($str, ['Principal', 'Director', 'Coordinator', 'Librarian', 'English HOD', 'Others'])) {
			$this->form_validation->set_message('designation', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function authorized_person($str) {
		return true;
	}

	public function school_head($str) {
		return true;
	}

	public function validation($str) {
		if (!in_array($str, ['email', 'mobile', 'email_link'])) {
			$this->form_validation->set_message('validation', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function first_name($str) {
		return true;
	}

	public function last_name($str) {
		return true;
	}

	public function parent_name($str) {
		return true;
	}

	public function parent_email($str) {
		return true;
	}

	public function dob($str) {
		return true;
	}

	public function name($str) {
		return true;
	}

	public function email($str) {
		if (!empty($str) && !empty($this->user_model->get_all(['email' => $str])['total'])) {
			$this->form_validation->set_message('email', _li('This {field} is already registered with BriBooks'));
			return false;
		}

		return true;
	}

	public function existing_email($str) {
		if (
			!empty($str) &&
			!empty($user_info = $this->user_model->get_all(['email' => $str])['rows'][0] ?? []) &&
			$user_info['id'] != $this->input->post('id')
		) {
			$this->form_validation->set_message('email', _li('This {field} is already registered with BriBooks'));
			return false;
		}

		return true;
	}

	public function mobile($str) {
		if (!empty($str) && !empty($this->user_model->get_all(['mobile' => $str])['total'])) {
			$this->form_validation->set_message('mobile', _li('This {field} number is already registered with BriBooks'));
			return false;
		}

		return true;
	}

	public function existing_mobile($str) {
		if (
			!empty($str) &&
			!empty($user_info = $this->user_model->get_all(['mobile' => $str])['rows'][0] ?? []) &&
			$user_info['id'] != $this->input->post('id')
		) {
			$this->form_validation->set_message('mobile', _li('This {field} is already registered with BriBooks'));
			return false;
		}

		return true;
	}

	public function event_challenge($str) {
		if (!$this->event_challenge_model->get($str)) {
			$this->form_validation->set_message('event_challenge', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_weekly($str) {
		if (!$this->event_challenge_weekly_model->get($str)) {
			$this->form_validation->set_message('event_challenge_weekly', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_daily($str) {
		if (!$this->event_challenge_daily_model->get($str)) {
			$this->form_validation->set_message('event_challenge_daily', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_country($str) {
		if (!$this->event_challenge_country_model->get($str)) {
			$this->form_validation->set_message('event_challenge_country', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_state($str) {
		if (!$this->event_challenge_state_model->get($str)) {
			$this->form_validation->set_message('event_challenge_state', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_city($str) {
		if (!$this->event_challenge_city_model->get($str)) {
			$this->form_validation->set_message('event_challenge_city', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_school($str) {
		if (!$this->event_challenge_school_model->get($str)) {
			$this->form_validation->set_message('event_challenge_school', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_weekly_slug($str) {
		if (!$this->event_challenge_weekly_model->getBySlug($str)) {
			$this->form_validation->set_message('slug', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_general($str) {
		if (!$this->event_challenge_general_model->get($str)) {
			$this->form_validation->set_message('event_challenge_general', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_general_slug($str) {
		if (!$this->event_challenge_general_model->getBySlug($str)) {
			$this->form_validation->set_message('slug', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_league_group($str) {
		if (!$this->event_league_group_model->get($str)) {
			$this->form_validation->set_message('event_league_group', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_group($str) {
		if (!$this->event_challenge_group_model->get($str)) {
			$this->form_validation->set_message('event_challenge_group', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_group_slug($str) {
		if (!$this->event_challenge_group_model->getBySlug($str)) {
			$this->form_validation->set_message('slug', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_genre_slug($str) {
		if (!$this->event_challenge_genre_model->getBySlug($str)) {
			$this->form_validation->set_message('slug', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_school_slug($str) {
		if (!$this->event_challenge_school_model->getBySlug($str)) {
			$this->form_validation->set_message('slug', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_city_slug($str) {
		if (!$this->event_challenge_city_model->getBySlug($str)) {
			$this->form_validation->set_message('slug', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_state_slug($str) {
		if (!$this->event_challenge_state_model->getBySlug($str)) {
			$this->form_validation->set_message('slug', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_country_slug($str) {
		if (!$this->event_challenge_country_model->getBySlug($str)) {
			$this->form_validation->set_message('slug', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_vote($str) {
		if (!$this->event_challenge_vote_model->get($str)) {
			$this->form_validation->set_message('event_challenge_vote', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function event_challenge_vote_slug($str) {
		if (!$this->event_challenge_vote_model->getBySlug($str)) {
			$this->form_validation->set_message('slug', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function competition_order($str) {
		if (!$this->competition_order_model->get($str)) {
			$this->form_validation->set_message('competition_order', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function ifsc_code($str) {
		$response = @json_decode(file_get_contents('https://ifsc.razorpay.com/' . $str), true);

		if (empty($response['BRANCH'])) {
			$this->form_validation->set_message('ifsc_code', _li('The {field} is inavlid'));
			return false;
		}

		return true;
	}

	public function printer($str) {
		if (!$this->db->get_where('users', [
			'id'		=> (int)$str,
			'role_id'	=> 12
		])->row_array()) {
			$this->form_validation->set_message('printer', _li('The {field} is not found'));
			return false;
		}

		return true;
	}

	public function assignment($str) {
		if (!$this->db->get_where('printer_assignment', [
			'id'		=> (int)$str,
		])->row_array()) {
			$this->form_validation->set_message('assignment', _li('The {field} is not found'));
			return false;
		}

		return true;
	}
}
