<?php defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;

load_trait('api');
load_trait('api/briminds');

class Api extends CI_Controller {
	public function __construct() {
		parent::__construct();

		$this->json = [];

		$this->load->model('Alert_model', 'alert_model');

		$this->load->model('common/Otp_model', 'otp_model');
		$this->load->model('common/Validate_model', 'validate_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('common/Blog_model', 'blog_model');
		$this->load->model('common/Lesson_model', 'lesson_model');
		$this->load->model('common/ShareTemplate_model', 'share_template_model');
		$this->load->model('common/BotLogs_model', 'bot_logs_model');
		$this->load->model('common/AccessLog_model', 'access_log_model');
		$this->load->model('common/AppUserRedirect_model', 'app_user_redirect_model');
		$this->load->model('common/WebPushSubscriber_model', 'web_push_subscriber_model');
		$this->load->model('common/Online_model', 'online_model');
		$this->load->model('common/Notification_model', 'notification_model');
		$this->load->model('common/SearchLog_model', 'search_log_model');

		$this->load->model('user/User_model', 'user_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('user/UserCover_model', 'user_cover_model');
		$this->load->model('user/Bank_model', 'bank_model');
		$this->load->model('user/AuthorEarning_model', 'author_earning_model');
		$this->load->model('user/UserToken_model', 'user_token_model');
		$this->load->model('user/UserDeviceToken_model', 'user_device_token_model');
		$this->load->model('user/UserReferral_model', 'user_referral_model');
		$this->load->model('user/UserReferralList_model', 'user_referral_list_model');
		$this->load->model('user/teacher/TeacherLead_model', 'teacher_lead_model');
		$this->load->model('user/teacher/Teacher_model', 'teacher_model');
		$this->load->model('user/UserNotification_model', 'user_notification_model');
		$this->load->model('user/UserLimit_model', 'user_limit_model');
		$this->load->model('user/Unsubscribed_model', 'unsubscribed_model');

		$this->load->model('localisation/Country_model', 'country_model');
		$this->load->model('localisation/Currency_model', 'currency_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('localisation/Language_model', 'language_model');
		$this->load->model('localisation/GroupRegion_model', 'group_region_model');

		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('book/Bookstore_model', 'bookstore_model');
		$this->load->model('book/Page_model', 'page_model');
		$this->load->model('book/PageVersion_model', 'page_version_model');
		$this->load->model('book/CustomTheme_model', 'custom_theme_model');
		$this->load->model('book/CustomThemeLog_model', 'custom_theme_log_model');
		$this->load->model('book/CustomCover_model', 'custom_cover_model');
		$this->load->model('book/CustomCoverLog_model', 'custom_cover_log_model');
		$this->load->model('book/Review_model', 'review_model');
		$this->load->model('book/BookAppreciation_model', 'book_appreciation_model');
		$this->load->model('book/CrosswordStore_model', 'cross_word_store_model');
		$this->load->model('book/CrosswordBook_model', 'cross_word_book_model');
		$this->load->model('book/AudioBook_model', 'audio_book_model');

		$this->load->model('design/Genre_model', 'genre_model');
		$this->load->model('design/Category_model', 'category_model');
		$this->load->model('design/Theme_model', 'theme_model');
		$this->load->model('design/Cover_model', 'cover_model');
		$this->load->model('design/Font_model', 'font_model');

		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('order/Payment_model', 'payment_model');
		$this->load->model('order/OrderHistory_model', 'order_history_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('order/Coupon_model', 'coupon_model');

		$this->load->model('address/Address_model', 'address_model');

		$this->load->model('competition/Competition_model', 'competition_model');
		$this->load->model('competition/CompetitionOrder_model', 'competition_order_model');
		$this->load->model('competition/CompetitionPayment_model', 'competition_payment_model');

		$this->load->model('certificate/Certificate_model', 'certificate_model');

		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventSite_model', 'event_site_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('event/EventTeacher_model', 'event_teacher_model');
		$this->load->model('event/EventDetail_model', 'event_detail_model');
		$this->load->model('event/QualifiedSchool_model', 'qualified_school_model');
		$this->load->model('event/EventSignupForm_model', 'event_signup_form_model');
		$this->load->model('event/EventLandingPage_model', 'event_landing_page_model');
		$this->load->model('event/EventConfig_model', 'event_config_model');
		$this->load->model('event/EventPartner_model', 'event_partner_model');
		$this->load->model('event/EventAward_model', 'event_award_model');
		$this->load->model('event/EventAwardGroup_model', 'event_award_group_model');
		$this->load->model('event/EventBookQualificationPending_model', 'event_book_qualification_pending_model');
		$this->load->model('event/EventJuryBook_model', 'event_jury_book_model');
		$this->load->model('event/EventSchoolInviteCode_model', 'event_school_invite_code_model');
		$this->load->model('event/EventUserInviteCode_model', 'event_user_invite_code_model');
		$this->load->model('event/MasterClass_model', 'master_class_model');

		$this->load->model('school/SchoolInput_model', 'schoolinput_model');
		$this->load->model('school/SchoolLead_model', 'school_lead_model');
		$this->load->model('school/SchoolUser_model', 'school_user_model');
		$this->load->model('school/School_model', 'school_model');

		// Medallion models
		$this->load->model('medallion/Medallion_model', 'medallion_model');
		$this->load->model('medallion/MedallionStockLog_model', 'medallion_stock_log_model');
		$this->load->model('medallion/MedallionOrder_model', 'medallion_order_model');
		$this->load->model('medallion/MedallionAddress_model', 'medallion_address_model');
		$this->load->model('medallion/SchoolMedallionAddress_model', 'school_medallion_address_model');
		// Medallion models

		// subscription models
		$this->load->model('subscription/SubscriptionPlan_model', 'subscription_plan_model');
		$this->load->model('subscription/SubscriptionOrder_model', 'subscription_order_model');
		$this->load->model('subscription/SubscriptionPayment_model', 'subscription_payment_model');
		$this->load->model('subscription/UserSubscription_model', 'user_subscription_model');
		// subscription models

		$this->load->model('shipping/ShippingCredit_model', 'shipping_credit_model');
		$this->load->model('shipping/Shiprocket_model', 'shiprocket_model');
		$this->load->model('shipping/DeliveryCountry_model', 'delivery_country_model');

		$this->load->model('broadcast/BroadcastPartner_model', 'broadcast_partner_model');
		$this->load->model('broadcast/BroadcastPartnerSlot_model', 'broadcast_partner_slot_model');
		$this->load->model('event/LeadVerificationCode_model', 'lead_verification_code_model');

		$this->load->model('review/ReviewFlagType_model', 'review_flag_type_model');
		$this->load->model('review/ReviewFlag_model', 'review_flag_model');

		$this->load->model('event/EventChallengeGeneral_model', 'event_challenge_general_model');
		$this->load->model('event/EventChallengeGenre_model', 'event_challenge_genre_model');
		$this->load->model('event/EventChallengeSchool_model', 'event_challenge_school_model');
		$this->load->model('event/EventChallengeCity_model', 'event_challenge_city_model');
		$this->load->model('event/EventChallengeState_model', 'event_challenge_state_model');
		$this->load->model('event/EventChallengeCountry_model', 'event_challenge_country_model');
		$this->load->model('event/EventChallengeJury_model', 'event_challenge_jury_model');

		$this->load->library('form_validation');
		$this->load->library('Spam_lib', 'spam_lib');
		$this->load->library('s3');

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);

		$this->demo_dates = DEMO_DATES;
		$this->demo_times = DEMO_TIMES;

		$this->default_otp = NULL;

		$session_data = $this->session->userdata();

		unset($session_data['pwds'], $session_data['dbs'], $session_data['queries']);

		ENVIRONMENT !== 'production' && !ENV_API && log_kb(['Api::__construct::' => [
			'session'		=> $session_data,
			'method'		=> $this->input->method(),
			'uri_segment' 	=> $this->uri->uri_string(),
		]]);

		self::_setLocale();
		self::_updateOnline();
	}

	use Localisation,
		Login,
		Otp,
		SocialLogin,
		MobileOtp,
		EmailOtp,
		Common,
		Book,
		Page,
		Subscription,
		User,
		Cart,
		Coupon,
		Courier,
		Address,
		Order,
		Signup,
		EventSignup,
		Competition,
		Lead,
		School,
		SchoolSignup,
		SchoolSignupRequest,
		OrderHistory,
		Ranking,
		BookAppreciation,
		Cms,
		CmsOtp,
		Blog,
		Theme,
		CustomTheme,
		Certificate,
		NyafAuthor,
		SCAuthor,
		SchoolDashboard,
		Event,
		SchoolEarlyAccess,
		VerifyCertificate,
		SchoolSignupOther,
		ShareTemplate,
		Achievement,
		CrosswordStoreApi,
		Writing,
		AppUserRedirect,
		CustomCover,
		Medallion,
		BookExhibition,
		AppOtp,
		UserAwardAddress,
		GlobalEventSignup,
		GlobalSchoolSignup,
		TeacherSignup,
		TeacherDashboard,
		EventSignupData,
		Notification,
		AudioBook,
		StudentInvite,
		PrepSchool,
		SchoolLead,
		EventSchoolSignup,
		CommunicationKit,
		DeprecatedData,
		BroadcastPartnerSlot,
		GlobalEventSchoolSignup,
		GlobalEventAuthorSignup,
		GlobalTeacherSignup,
		GlobalEventTeacherSignup,
		EventQualifiedSchool,
		EventConfig,
		Pricing,
		PodcastInvite,
		EventJury,
		EventSchool,
		EventStudent,
		EventSurvey,
		MasterClass,
		Review,
		League,
		Publish,
		SchoolKit,
		EventFaq,
		EventTeacher,
		EventInvite,
		EventAwardRecognition,
		Profile,
		EventVote,
		BriMinds,
		OrderUndelivered
	;

	public function index() {
		show_404();
	}

	private function _runFormValidation($full_error = false) {
		$valid = $this->form_validation->run();

		if (!$valid) {
			if ($full_error) {
				$this->json['errors'] = $this->form_validation->error_array();
			} else {
				$this->json['error'] = strip_tags(validation_errors());
			}
		}
	}

	private function _validateNudeImage($key = 'image') {
		$base64 = base64_encode(file_get_contents($_FILES[$key]['tmp_name']));

		$result = _curl(
			'http://172.31.42.71:5002/bb44khjkhskdjfhs344w56g657686233243Ghghj',
			[
				'action'	=> 'nude_detect',
				'base64'	=> $base64
			],
		);

		log_kb(['_validateNudeImage:: ' => $result]);

		if (!empty($result['reasons']) && empty($result['success'])) {
			$this->json['error'] = _li('Upload unsuccessful: BriBooks does not accept images containing nudity. Please review our content/image guidelines and upload an appropriate image.');
		}
	}

	private function _validateFileUpload($key = 'image', $nude_detect = true, $type = 'image') {
		$filename = basename(html_entity_decode($_FILES[$key]['name'], ENT_QUOTES, 'UTF-8'));

		// Allowed file extension types
		$allowed = [
			'jpg',
			'jpeg',
			'gif',
			'png',
		];

		if ($type === 'video') {
			$allowed = [
				'mp4',
			];
		}

		if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
			$this->json['error'] = _l('invalid_filetype');
		}

		if ($_FILES[$key]['size'] > 25000000) {
			$this->json['error'] = _l('file_size_exceeded');
		}

		// Allowed file mime types
		$allowed = [
			'image/jpeg',
			'image/pjpeg',
			'image/png',
			'image/x-png',
			'image/gif',
		];

		if ($type === 'video') {
			$allowed = [
				'video/mp4',
			];
		}

		if (!in_array($_FILES[$key]['type'], $allowed)) {
			$this->json['error'] = _l('invalid_file_encoding');
		}

		if (preg_match('/<\?php/ims', file_get_contents($_FILES[$key]['tmp_name']))) {
			$this->json['error'] = _l('code_injection_detected');
		}

		if ($_FILES[$key]['error'] != UPLOAD_ERR_OK) {
			$this->json['error'] = _l('unable_to_upload');
		}

		$nude_detect && self::_validateNudeImage($key);

		return !$this->json;
	}
}
