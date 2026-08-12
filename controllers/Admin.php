<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('admin');
load_trait('common');

class Admin extends CI_Controller {
	private $_generic_filters = [];

	public function __construct() {
		parent::__construct();
		// echo '<pre>';
		// echo session_id();
		// print_r($_SESSION);
		// print_r($this->session->userdata());
		// exit('constructor');
		check_has_permission();

		$this->load->model('user/Department_model', 'department_model');
		$this->load->model('user/Role_model', 'role_model');
		$this->load->model('user/Admin_model', 'admin_model');
		$this->load->model('user/SystemUser_model', 'system_user_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('user/Telecaller_model', 'telecaller_model');
		$this->load->model('user/ProgramAdmin_model', 'program_admin_model');
		$this->load->model('user/ClusterAdmin_model', 'cluster_admin_model');
		$this->load->model('user/User_model', 'user_model');
		$this->load->model('user/Lead_model', 'lead_model');
		$this->load->model('user/teacher/Teacher_model', 'teacher_model');
		$this->load->model('user/teacher/TeacherLead_model', 'teacher_lead_model');
		$this->load->model('user/Bank_model', 'bank_model');
		$this->load->model('user/AuthorEarning_model', 'author_earning_model');
		$this->load->model('user/Unsubscribed_model', 'unsubscribed_model');
		$this->load->model('user/UserCredit_model', 'user_credit_model');
		$this->load->model('user/UserCreditRequest_model', 'user_credit_request_model');
		$this->load->model('user/UserCreditHistory_model', 'user_credit_history_model');
		$this->load->model('user/UserCreditRedeem_model', 'user_credit_redeem_model');
		$this->load->model('user/UserCreditRedeemJob_model', 'user_credit_redeem_job_model');
		$this->load->model('user/UserReferral_model', 'user_referral_model');
		$this->load->model('user/UserReferralList_model', 'user_referral_list_model');
		$this->load->model('user/UserAnnouncements_model', 'user_announcements_model');
		$this->load->model('user/UserAwardAddress_model', 'user_award_address_model');
		$this->load->model('user/UserCover_model', 'user_cover_model');
		$this->load->model('user/AuthorEarning_model', 'authorearning_model');
		$this->load->model('user/UserTag_model', 'user_tag_model');

		$this->load->model('common/Slot_model', 'slot_model');
		$this->load->model('common/Course_model', 'course_model');
		$this->load->model('common/Class_model', 'class_model');
		$this->load->model('common/Schedule_model', 'schedule_model');
		$this->load->model('common/Enrol_model', 'enrol_model');
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('common/SiteType_model', 'site_type_model');
		$this->load->model('common/Validate_model', 'validate_model');
		$this->load->model('common/Marketing_model', 'marketing_model');
		$this->load->model('common/Image_model', 'image_model');
		$this->load->model('common/Grade_model', 'grade_model');
		$this->load->model('common/SiteSection_model', 'sitesection_model');
		$this->load->model('common/Blog_model', 'blog_model');
		$this->load->model('common/AddTemplate_model', 'addtemplate_model');
		$this->load->model('common/ShareTemplate_model', 'share_template_model');
		$this->load->model('common/InviteSlot_model', 'invite_slot_model');
		$this->load->model('common/BlockedIp_model', 'blocked_ip_model');
		$this->load->model('common/CampaignLog_model', 'campaign_log_model');
		$this->load->model('common/UtmSource_model', 'utm_source_model');
		$this->load->model('common/MarketingDataset_model', 'marketing_dataset_model');
		$this->load->model('common/Lesson_model', 'lesson_model');
		$this->load->model('common/ImportJob_model', 'import_job_model');
		$this->load->model('common/Online_model', 'online_model');
		$this->load->model('common/Notification_model', 'notification_model');
		$this->load->model('common/WebPushSubscriber_model', 'web_push_subscriber_model');
		$this->load->model('common/MessageTemplateType_model', 'message_template_type_model');
		$this->load->model('common/MessageTemplate_model', 'message_template_model');
		$this->load->model('common/LeagueTemplate_model', 'league_template_model');

		// localisation
		$this->load->model('localisation/Country_model', 'country_model');
		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');
		$this->load->model('localisation/Currency_model', 'currency_model');
		$this->load->model('localisation/Center_model', 'center_model');
		$this->load->model('localisation/Language_model', 'language_model');
		$this->load->model('localisation/Translation_model', 'translation_model');
		$this->load->model('localisation/GroupRegion_model', 'group_region_model');

		$this->load->model('Alert_model', 'alert_model');

		// Design
		$this->load->model('design/Genre_model', 'genre_model');
		$this->load->model('design/GenreLocale_model', 'genre_locale_model');
		$this->load->model('design/Category_model', 'category_model');
		$this->load->model('design/Theme_model', 'theme_model');
		$this->load->model('design/Cover_model', 'cover_model');
		$this->load->model('design/ThemeLocale_model', 'theme_locale_model');
		$this->load->model('design/CoverLocale_model', 'cover_locale_model');
		$this->load->model('design/CategoryLocale_model', 'category_locale_model');
		$this->load->model('design/Font_model', 'font_model');

		// Book
		$this->load->model('book/SpamWord_model', 'spam_word_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('book/BookArchiveLog_model', 'book_archive_log_model');
		$this->load->model('admin/Bookstore_model', 'bookstore_model');
		$this->load->model('book/BookVersion_model', 'book_version_model');
		$this->load->model('book/BookClone_model', 'book_clone_model');
		$this->load->model('book/Page_model', 'page_model');
		$this->load->model('book/PageVersion_model', 'page_version_model');
		$this->load->model('book/Review_model', 'review_model');
		$this->load->model('book/ReviewLog_model', 'reviewlog_model');
		$this->load->model('book/ReviewEditLogs_model', 'revieweditlogs_model');
		$this->load->model('book/BookAssignLog_model', 'bookassignlog_model');
		$this->load->model('book/BookStock_model', 'book_stock_model');
		$this->load->model('book/BookStockHistory_model', 'book_stock_history_model');
		$this->load->model('book/BookStockLog_model', 'book_stock_log_model');
		$this->load->model('book/RejectedBook_model', 'rejected_book_model');
		$this->load->model('book/RejectedBookLog_model', 'rejected_book_log_model');
		$this->load->model('book/CrosswordStore_model', 'cross_word_store_model');
		$this->load->model('book/CrosswordBook_model', 'cross_word_book_model');
		$this->load->model('book/BookExhibition_model', 'book_exhibition_model');
		$this->load->model('book/CustomCover_model', 'custom_cover_model');
		$this->load->model('book/CustomCoverLog_model', 'custom_cover_log_model');
		$this->load->model('book/AmazonBook_model', 'amazon_book_model');
		$this->load->model('book/SpamWord_model', 'spam_word_model');

		$this->load->model('event/EventBookIsbnLimit_model', 'event_book_isbn_limit_model');

		$this->load->model('Email_model', 'email_model');

		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');
		$this->load->model('order/OrderHistory_model', 'order_history_model');
		$this->load->model('order/OrderComment_model', 'order_comment_model');
		$this->load->model('order/OrderPackingLog_model', 'order_packing_log_model');
		$this->load->model('order/Payment_model', 'payment_model');
		$this->load->model('order/OrderClone_model', 'order_clone_model');
		$this->load->model('order/Coupon_model', 'coupon_model');
		$this->load->model('order/OrderPrivy_model', 'order_privy_model');
		$this->load->model('order/AmazonKdpOrder_model', 'amazon_kdp_order_model');
		$this->load->model('order/AutoEscalatedOrder_model', 'auto_escalated_order_model');

		$this->load->model('competition/Competition_model', 'competition_model');
		$this->load->model('competition/CompetitionOrder_model', 'competition_order_model');
		$this->load->model('competition/CompetitionPayment_model', 'competition_payment_model');

		$this->load->model('shipping/Indiapost_model', 'indiapost_model');
		$this->load->model('shipping/PickupLocation_model', 'pickup_location_model');
		$this->load->model('shipping/DirectShipments_model', 'direct_shipments_model');
		$this->load->model('shipping/DeliveryCountry_model', 'delivery_country_model');

		$this->load->model('printer/PrinterAssignment_model', 'printer_assignment_model');
		$this->load->model('printer/PrinterStats_model', 'printer_stats_model');
		$this->load->model('printer/Printer_model', 'printer_model');
		$this->load->model('printer/PrinterAssignLog_model', 'printer_assign_log_model');
		$this->load->model('printer/PrinterExtraDetails_model', 'printer_extra_details_model');

		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventUser_model', 'event_user_model');
		$this->load->model('event/EventSite_model', 'event_site_model');
		$this->load->model('event/EventContent_model', 'event_content_model');
		$this->load->model('event/EventBook_model', 'event_book_model');
		$this->load->model('event/EventOrder_model', 'event_order_model');
		$this->load->model('event/EventType_model', 'event_type_model');
		$this->load->model('event/EventTemplate_model', 'event_template_model');
		$this->load->model('event/EventDetail_model', 'event_detail_model');
		$this->load->model('event/EventBrochure_model', 'event_brochure_model');
		$this->load->model('event/EventSignupForm_model', 'event_signup_form_model');
		$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');
		$this->load->model('event/EventLandingPage_model', 'event_landing_page_model');
		$this->load->model('event/EventPartner_model', 'event_partner_model');
		$this->load->model('event/EventConfig_model', 'event_config_model');
		$this->load->model('event/EventInviteTemplate_model', 'event_invite_template_model');
		$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');
		$this->load->model('event/EventSchoolInvite_model', 'event_school_invite_model');
		$this->load->model('event/AuthorWall_model', 'author_wall_model');
		$this->load->model('event/EventExhibition_model', 'event_exhibition_model');

		// event
		$this->load->model('event/EventChallengeDaily_model', 'event_challenge_daily_model');
		$this->load->model('event/EventChallengeGeneral_model', 'event_challenge_general_model');
		$this->load->model('event/EventChallengeGenre_model', 'event_challenge_genre_model');
		$this->load->model('event/EventChallengeSchool_model', 'event_challenge_school_model');
		$this->load->model('event/EventChallengeCity_model', 'event_challenge_city_model');
		$this->load->model('event/EventChallengeState_model', 'event_challenge_state_model');
		$this->load->model('event/EventChallengeCountry_model', 'event_challenge_country_model');
		$this->load->model('event/EventChallengeGroup_model', 'event_challenge_group_model');
		$this->load->model('event/EventChallengeLiteraryLeader_model', 'event_challenge_literary_leader_model');

		$this->load->model('event/EventLeagueGroup_model', 'event_league_group_model');
		$this->load->model('event/EventAward_model', 'event_award_model');
		$this->load->model('event/EventAwardGroup_model', 'event_award_group_model');
		$this->load->model('event/EventChallengeJury_model', 'event_challenge_jury_model');
		$this->load->model('event/EventPdf_model', 'event_pdf_model');
		$this->load->model('event/MasterClass_model', 'master_class_model');

		//subscription
		$this->load->model('subscription/SubscriptionPlan_model', 'subscription_plan_model');
		$this->load->model('subscription/SubscriptionOrder_model', 'subscription_order_model');
		$this->load->model('subscription/SubscriptionPayment_model', 'subscription_payment_model');
		$this->load->model('subscription/UserSubscription_model', 'user_subscription_model');

		// Medallion
		$this->load->model('medallion/Medallion_model', 'medallion_model');
		$this->load->model('medallion/MedallionStockLog_model', 'medallion_stock_log_model');
		$this->load->model('medallion/MedallionOrder_model', 'medallion_order_model');
		$this->load->model('medallion/MedallionAddress_model', 'medallion_address_model');
		$this->load->model('medallion/SchoolMedallionAddress_model', 'school_medallion_address_model');
		$this->load->model('medallion/MedallionOrderComment_model', 'medallion_order_comment_model');
		$this->load->model('medallion/MedallionOrderHistory_model', 'medallion_order_history_model');
		$this->load->model('medallion/MedallionOrderPackingLog_model', 'medallion_order_packing_log_model');
		$this->load->model('medallion/MedallionFeedback_model', 'medallion_feedback_model');

		// School
		$this->load->model('school/SchoolOrder_model', 'school_order_model');
		$this->load->model('school/SchoolOrderComment_model', 'school_order_comment_model');
		$this->load->model('school/SchoolOrderHistory_model', 'school_order_history_model');
		$this->load->model('school/SchoolOrderPackingLog_model', 'school_order_packing_log_model');
		$this->load->model('school/School_model', 'school_model');
		$this->load->model('school/SchoolLead_model', 'school_lead_model');
		$this->load->model('school/SchoolUser_model', 'school_user_model');
		$this->load->model('school/SchoolAwardAddress_model', 'school_award_address_model');
		$this->load->model('school/SchoolTag_model', 'school_tag_model');
		$this->load->model('school/TelecallerSchool_model', 'telecaller_school_model');

		// Dropshipper
		$this->load->model('dropshipper/Dropshipper_model', 'dropshipper_model');
		$this->load->model('dropshipper/DropshipperOrder_model', 'dropshipper_order_model');
		$this->load->model('dropshipper/DropshipperAssignLog_model', 'dropshipper_assignlog_model');
		$this->load->model('dropshipper/DropshipperAssignRollback_model', 'dropshipper_assign_rollback_model');
		$this->load->model('dropshipper/DropshipperAssignment_model', 'dropshipper_assignment_model');

		// certificate
		$this->load->model('certificate/CertificateMessageTemplate_model', 'certificate_message_template_model');
		$this->load->model('certificate/CertificateTemplate_model', 'certificate_template_model');
		$this->load->model('certificate/CertificateType_model', 'certificate_type_model');

		$this->load->model('broadcast/BroadcastPartner_model', 'broadcast_partner_model');
		$this->load->model('broadcast/BroadcastPartnerSlot_model', 'broadcast_partner_slot_model');

		$this->load->model('halloffame/HallOfFame_model', 'hall_of_fame_model');

		$this->load->model('address/Address_model', 'address_model');

		$this->load->model('ranking/RankingCountry_model', 'user_rank_country_model');
		$this->load->model('ranking/LeagueBreakPointMessage_model', 'league_breakpoint_message_model');

		$this->load->model('review/ReviewFlag_model', 'review_flag_model');
		$this->load->model('review/ReviewFlagType_model', 'review_flag_type_model');
		$this->load->model('review/BookAiReview_model', 'book_ai_review_model');

		$this->load->model('admin/ticket/Ticket_model', 'ticket_model');
		$this->load->model('admin/ticket/TicketCategory_model', 'ticket_category_model');
		$this->load->model('admin/ticket/TicketPriority_model', 'ticket_priority_model');
		$this->load->model('admin/ticket/TicketStatus_model', 'ticket_status_model');
		$this->load->model('admin/ticket/TicketHistory_model', 'ticket_history_model');
		$this->load->model('admin/AdminValidate_model', 'admin_validate_model');
		$this->load->model('admin/TelecallerDashboard_model', 'telecaller_dashboard_model');

		// BriSharks
		$this->load->model('brisharks/common/BSMessageTemplate_model', 'bs_message_template_model');
		$this->load->model('brisharks/common/BSCron_model', 'bs_cron_model');
		$this->load->model('brisharks/subscription/BSSubscriptionPayment_model', 'bs_subscription_payment_model');
		$this->load->model('brisharks/user/BSUserInviteCode_model', 'bs_user_invite_code_model');
		$this->load->model('brisharks/user/BSUser_model', 'bs_user_model');
		$this->load->model('brisharks/startup/BSStartup_model', 'bs_startup_model');
		$this->load->model('brisharks/event/BSEvent_model', 'bs_event_model');
		$this->load->model('brisharks/event/BSEventChallenge_model', 'bs_event_challenge_model');
		$this->load->model('brisharks/event/BSEventInvite_model', 'bs_event_invite_model');
		$this->load->model('brisharks/ranking/BSUserRankCountry_model', 'bs_user_ranking_country_model');

		$this->load->library('Stock_lib');
		$this->load->library('form_validation');
		$this->load->library('s3');

		if (!$this->session->userdata('cart_items')) {
			$this->session->set_userdata('cart_items', []);
		}

		if ($this->session->userdata('admin_login') == false) {
			$this->session->set_userdata('page', base_url($_SERVER['REDIRECT_QUERY_STRING']));
			redirect(base_url('login'), 'refresh');
		}

		if (empty($this->session->userdata('user_role_type'))) {
			redirect(base_url('login'), 'refresh');
		}

		$this->load->driver('cache', [
			'adapter' 		=> (ENVIRONMENT === 'production' ? 'redis' : 'file'),
			'backup' 		=> 'file',
			'key_prefix' 	=> (ENVIRONMENT === 'production' ? 'live_api_' : 'test_api_'),
		]);
	}

	use Dashboard,
		TelecallerDashboard,
		Lead,
		SchoolLead,
		Users,
		Setting,
		Marketing,
		Books,
		BookRating,
		BookExtra,
		Themes,
		Covers,
		ImportBkp,
		Subscribers,
		Payment,
		Orders,
		GlobalOrders,
		OrderShipping,
		OrderExtra,
		Genre,
		Category,
		Font,
		Competition,
		Coupon,
		Blog,
		OrderHistory,
		AuthorRoyalty,
		UserCreditRequest,
		School,
		EmailTemplates,
		BookPrintCustom,
		BookPrintKdp,
		BookPrintBW,
		BookPrintGrey,
		BookPrintUnPublished,
		Printer,
		BookStock,
		Unsubscribed,
		Notification,
		Localisation,
		PrinterAssignment,
		RejectedBook,
		NyafExport,
		EventBookPoster,
		Nyaf2022,
		EbookOrders,
		QaQc,
		Site,
		SiteType,
		DirectShipments,
		AmazonKdpOrder,
		ShipOrder,
		ShareTemplateMessage,
		CrosswordStore,
		OrderClone,
		ReferralUser,
		OrderPrivy,
		HallOfFame,
		BlackWhiteOrders,
		Event,
		EventSchool,
		EventUser,
		EventBook,
		EventTemplate,
		EventDetail,
		MasterClass,
		Medallion,
		MedallionAddress,
		MedallionOrder,
		MedallionFeedback,
		BookExhibition,
		UserAwardsAddress,
		DataCleaning,
		AmazonVoucher,
		BlockedIpAdmin,
		CustomizedBook,
		SchoolAwardsAddress,
		CertificateMessageTemplates,
		CertificateTemplates,
		CloneBook,
		AutoEscalateOrder,
		Address,
		PickupLocations,
		DropshipperAssignment,
		Dropshipper,
		SchoolOrder,
		CustomizedCover,
		CampaignLog,
		BroadcastPartner,
		BroadcastPartnerSlot,
		UtmSource,
		Schools,
		BookIsbn,
		DeliveryCountry,
		MarketingDataset,
		Lesson,
		GroupRegion,
		IsbnAmazonLimit,
		ReviewFlag,
		Announcement,
		BookAiReview,
		Import,
		OnlineStats,
		Ticket,
		WebPush,
		CacheStats,
		SpamWord,
		PincodeZone,
		EventDataCleaning,
		EventChallengeJury,
		SchoolMedallionAddress,
		EventPdf,
		MessageTemplateType,
		MessageTemplate,
		SubscriptionPlan,
		BriSharks,
		DeactivateUser,
		ThirdPartyService,
		EventChallengeLiteraryLeader
	;

	public function index() {
		// print_r($this->session->userdata('admin_login'));
		// exit('admin dashboard');
		if ($this->session->userdata('admin_login') == true) {
			$this->dashboard();
		} else {
			redirect(base_url('login'), 'refresh');
		}
	}

	public function dashboard() {
		// print_r($this->session->userdata('admin_login'));
		// exit('admin dashboard');
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($this->session->userdata('role_id') == 17) {
			redirect(base_url('admin/get_dashboard_count/9'), 'refresh');
		}

		$data['page_name'] 	= 'dashboard';
		$data['page_title'] = _l('dashboard');

		$this->load->view('backend/index.php', $data);
	}

	private function _formatFilters(&$filter_data) {
		foreach ($this->_generic_filters as $key => $item) {
			$value = $this->input->get($item['key']);

			if ($value !== null && $value !== '') {
				if (is_array($value)) {
					$value = array_filter($value, fn($item) => $item !== null && $item !== '');
					$value = array_map(fn($item) => is_numeric($item) ? (int)$item : $item, $value);

					if (empty($value)) continue;
				}

				$filter_data[$item['key']] = is_numeric($value)
					? (int)$value
					: $value;
			}
		}
	}

	public function lib_check() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] = 'libcheck';
		$data['country'] = $this->indiapost_model->countryList();
		$data['page_title'] = _l('libcheck');
		$this->load->view('backend/index.php', $data);
	}

	public function ajax_lib_check() {
		$this->load->library('BriBooksShipping_lib');
		print_r($this->bribooksshipping_lib->getRate($this->input->post('country'), round($this->input->post('weight'), 2)));
	}

	public function telecaller_reassign() {
		$data['page_name']		= 'telecaller_reassign';
		$data['page_title']		= _l('telecaller_reassign');
		$data['reassigns'] 		= [];

		$results = $this->lead_model->get_all_reassign();

		foreach ($results as $result) {
			$telecaller_info 			= $this->telecaller_model->get($result['telecaller_id'])->row_array();
			$telecaller_original_info 	= $this->telecaller_model->get($result['original_telecaller_id'])->row_array();

			$data['reassigns'][] = [
				'id'					=> $result['id'],
				'lead_id'				=> $result['lead_id'],
				'name'					=> $result['name'],
				'mobile'				=> $result['mobile'],
				'original_telecaller'	=> $telecaller_original_info['first_name'] ?? '',
				'telecaller'			=> $telecaller_info['first_name'] ?? '',
				'comment'				=> $result['comment'],
				'date_added'			=> $result['date_added'],
			];
		}

		$this->load->view('backend/index', $data);
	}

	public function reassign() {
		$data['page_name']		= 'reassign';
		$data['page_title']		= _l('request_reassign');
		$data['requests'] 		= [];

		$results = $this->schedule_model->get_all_reassign();

		foreach ($results as $result) {
			$class_info = $this->class_model->get($result['class_id'])->row_array();

			if ($class_info) {
				$data['requests'][] = [
					'id'				=> $result['id'],
					'teacher_id'		=> $result['user_id'],
					'name'				=> $result['name'],
					'course_id'			=> $class_info['course_id'] ?? '',
					'course'			=> $class_info['course'] ?? '',
					'schedule_id'		=> $result['schedule_id'],
					'schedule'			=> $result['schedule'],
					'comment'			=> $result['comment'],
					'date_added'		=> $result['date_added'],
				];
			}
		}

		$this->load->view('backend/index', $data);
	}

	public function site_detail() {
		$json = [];

		if (empty($this->input->post('site_id'))) {
			$json['error'] = _l('error_site_id');
		}

		if (!$json) {
			$json['site']		= $this->site_model->get($this->input->post('site_id'));
			$json['success'] 	= $this->session->flashdata('flash_message');
		}

		output_json($json);
	}

	public function update_reassign() {
		$json = [];

		/*if (empty($this->input->post('request_id')) || !($request_info = $this->schedule_model->get_reassign($this->input->post('request_id')))) {
			$json['error'] = _l('error_request_id');
		}*/

		if (empty($this->input->post('teacher_id'))) {
			$json['error'] = _l('error_teacher_id');
		}

		if (empty($this->input->post('original_teacher_id'))) {
			$json['error'] = _l('error_original_teacher_id');
		}

		if (!$json) {
			if (!empty($this->input->post('request_id')) && ($request_info = $this->schedule_model->get_reassign($this->input->post('request_id')))) {
				$schedule_id = $request_info['schedule_id'];
			} else {
				$schedule_id = $this->input->post('schedule_id');
			}

			$this->schedule_model->reassignTeacher([
				'original_teacher_id'	=> $this->input->post('original_teacher_id'),
				'teacher_id'	=> $this->input->post('teacher_id'),
				'schedule_id'	=> $schedule_id,
			]);

			$json['error'] 		= $this->session->flashdata('error_message');
			$json['success'] 	= $this->session->flashdata('flash_message');
		}

		output_json($json);
	}

	public function get_course_teachers() {
		$json = [];

		if ($this->input->post('course_id')) {
			$json['teachers'] = $this->teacher_model->get_all([
				'course_id'		=> $this->input->post('course_id')
			])->result_array();
		} else {
			$json['error'] = _l('error_course_id');
		}

		output_json($json);
	}

	public function get_backup_teachers() {
		$json = [];

		if ($this->input->post('schedule_id') && ($schedule_info = $this->schedule_model->get($this->input->post('schedule_id')))) {
			$class_info = $this->class_model->get($schedule_info['class_id'])->row_array();

			$json['teachers'] = $class_info['backup_teacher_id'] ? array_map(function ($teacher_id) {
				return $this->teacher_model->get($teacher_id)->row_array();
			}, json_decode($class_info['backup_teacher_id'], 1)) : [];
		} else {
			$json['error'] = _l('error_schedule_id');
		}

		output_json($json);
	}

	public function get_teachers() {
		$json = [];

		if ($this->input->post('schedule_id') && ($schedule_info = $this->schedule_model->get($this->input->post('schedule_id')))) {
			$json['teachers'] = $this->teacher_model->get_all();
		} else {
			$json['error'] = _l('error_schedule_id');
		}

		output_json($json);
	}

	// Do here for scheduling
	public function get_filtered_class() {
		$json = [];

		$this->load->library('form_validation');

		$this->form_validation->set_rules('course_id', _l('course_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('mode', _l('mode'), 'trim|required|in_list[online,offline]');

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = validation_errors());

		if (!$json) {
			$json['classes'] = $this->class_model->get_all([
				'course_id'		=> $this->input->post('course_id'),
				'mode'			=> $this->input->post('mode'),
			])->result_array();
		}

		output_json($json);
	}

	public function attendance($class_id = 0) {
		$data['page_name']		= 'attendance';
		$data['page_title']		= _l('attendance');
		$data['class_id']		= (int)$class_id;
		$data['title']			= '';

		if ($class_info = $this->class_model->get($class_id)->row_array()) {
			$data['class_id']		= $class_info['id'];
			$data['title']			= $class_info['name'];
		}

		$this->load->view('backend/index', $data);
	}

	public function pending_payments() {
		$data['page_name'] 	= 'pending_payments';
		$data['action'] 	= base_url();

		$data['enrols'] 	= $this->enrol_model->pendingPayments();

		$data['page_title'] = _l('pending_payments');

		$this->load->view('backend/index', $data);
	}

	public function payment_collection() {
		if ($this->input->post('enrol_id') && ($enrol_info = $this->enrol_model->get($this->input->post('enrol_id'))) && is_numeric($this->input->post('amount'))) {
			$this->enrol_model->renewOffline($enrol_info['id'], $this->input->post('amount'));

			$json['success'] 	= _l('renewed_successfully');
			$json['redirect'] 	= base_url('admin/pending_payments');
		} else {
			$json['error'] 		= _l('invalid_enrolment');
		}

		output_json($json);
	}

	public function admin_revenue($param1 = '') {
		if ($param1 != '') {
			$date_range					= $this->input->get('date_range');
			$date_range					= explode('-', $date_range);
			$data['timestamp_start'] 	= strtotime(trim($date_range[0]));
			$data['timestamp_end']	 	= strtotime('+1 days', strtotime(trim($date_range[1])));
		} else {
			$data['timestamp_start'] 	= strtotime('-1 month', time());
			$data['timestamp_end']	 	= strtotime('+1 days', strtotime(date("m/d/Y")));
		}

		$data['page_name'] 			= 'admin_revenue';
		$data['payment_history'] 	= $this->crud_model->get_revenue_by_user_type($data['timestamp_start'], $data['timestamp_end'], 'admin_revenue');
		$data['page_title'] 		= _l('admin_revenue');

		$this->load->view('backend/index', $data);
	}

	function invoice($payment_id = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 			= 'invoice';
		$data['payment_details'] 	= $this->crud_model->get_payment_details_by_id($payment_id);
		$data['page_title'] 		= _l('invoice');

		$this->load->view('backend/index', $data);
	}

	public function payment_history_delete($param1 = "", $redirect_to = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$this->crud_model->delete_payment_history($param1);
		$this->session->set_flashdata('flash_message', _l('data_deleted_successfully'));

		redirect(base_url('admin/' . $redirect_to), 'refresh');
	}

	public function purchase_history() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 			= 'purchase_history';
		$data['purchase_history'] 	= $this->crud_model->purchase_history();
		$data['page_title'] 		= _l('purchase_history');

		$this->load->view('backend/index', $data);
	}

	// Language Functions
	public function manage_language($param1 = '', $param2 = '', $param3 = '') {
		if ($param1 == 'add_language') {
			saveDefaultJSONFile($this->input->post('language'));
			$this->session->set_flashdata('flash_message', _l('language_added_successfully'));
			redirect(base_url('admin/manage_language'), 'refresh');
		}

		if ($param1 == 'add_phrase') {
			$new_phrase = _l($this->input->post('phrase'));
			$this->session->set_flashdata('flash_message', $new_phrase . ' ' . _l('has_been_added_successfully'));
			redirect(base_url('admin/manage_language'), 'refresh');
		}

		if ($param1 == 'edit_phrase') {
			$data['edit_profile'] = $param2;
		}

		if ($param1 == 'delete_language') {
			deleteJSONFile($param2);
		}

		$data['languages']				= $this->get_all_languages();
		$data['page_name']				= 'manage_language';
		$data['page_title']				= _l('multi_language_settings');

		$this->load->view('backend/index', $data);
	}

	public function update_phrase_with_ajax() {
		$current_editing_language 	= $this->input->post('currentEditingLanguage');
		$updated_value 				= $this->input->post('updatedValue');
		$key 						= $this->input->post('key');

		saveJSONFile($current_editing_language, $key, $updated_value);

		echo $current_editing_language . ' ' . $key . ' ' . $updated_value;
	}

	public function get_all_languages() {
		$language_files = [];
		$all_files = $this->get_list_of_language_files();

		foreach ($all_files as $file) {
			$info = pathinfo($file);
			if (isset($info['extension']) && strtolower($info['extension']) == 'json') {
				$file_name = explode('.json', $info['basename']);
				array_push($language_files, $file_name[0]);
			}
		}

		return $language_files;
	}

	public function get_list_of_language_files($dir = APPPATH . '/language', &$results = []) {
		$files = scandir($dir);
		foreach ($files as $key => $value) {
			$path = realpath($dir . DIRECTORY_SEPARATOR . $value);
			if (!is_dir($path)) {
				$results[] = $path;
			} elseif ($value != "." && $value != "..") {
				$this->get_list_of_directories_and_files($path, $results);
				$results[] = $path;
			}
		}
		return $results;
	}

	public function get_list_of_directories_and_files($dir = APPPATH, &$results = []) {
		$files = scandir($dir);

		foreach ($files as $key => $value) {
			$path = realpath($dir . DIRECTORY_SEPARATOR . $value);
			if (!is_dir($path)) {
				$results[] = $path;
			} elseif ($value != "." && $value != "..") {
				$this->get_list_of_directories_and_files($path, $results);
				$results[] = $path;
			}
		}

		return $results;
	}

	public function message($param1 = 'message_home', $param2 = '', $param3 = '') {
		if ($this->session->userdata('admin_login') != 1) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'send_new') {
			$message_thread_code = $this->crud_model->send_new_private_message();
			$this->session->set_flashdata('flash_message', _l('message_sent!'));
			redirect(base_url('admin/message/message_read/' . $message_thread_code), 'refresh');
		}
		if ($param1 == 'send_reply') {
			$this->crud_model->send_reply_message($param2); //$param2 = message_thread_code
			$this->session->set_flashdata('flash_message', _l('message_sent!'));
			redirect(base_url('admin/message/message_read/' . $param2), 'refresh');
		}
		if ($param1 == 'message_read') {
			$data['current_message_thread_code'] = $param2; // $param2 = message_thread_code
			$this->crud_model->mark_thread_messages_read($param2);
		}

		$data['message_inner_page_name']	= $param1;
		$data['page_name']					= 'message';
		$data['page_title']					= _l('private_messaging');

		$this->load->view('backend/index', $data);
	}

	public function manage_profile($param1 = '', $param2 = '', $param3 = '') {
		if ($this->session->userdata('admin_login') != 1) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'update_profile_info') {
			$this->user_model->edit($param2, $this->input->post());
			redirect(base_url('admin/manage_profile'), 'refresh');
		}

		$data['page_name']	= 'manage_profile';
		$data['page_title'] = _l('manage_profile');
		$data['edit_data']	= $this->db->get_where('users', array('id' => $this->session->userdata('user_id')))->result_array();

		$this->load->view('backend/index', $data);
	}

	public function preview($course_id = '') {
		if ($this->session->userdata('admin_login') != 1) {
			redirect(base_url('login'), 'refresh');
		}

		$this->is_drafted_course($course_id);
		if ($course_id > 0) {
			$courses = $this->crud_model->get_course_by_id($course_id);
			if ($courses->num_rows() > 0) {
				$course_details = $courses->row_array();
				redirect(base_url('home/lesson/' . slugify($course_details['title']) . '/' . $course_details['id']), 'refresh');
			}
		}
		redirect(base_url('admin/courses'), 'refresh');
	}

	// Manage Quizes
	public function quizes($course_id = "", $action = "", $quiz_id = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($action == 'add') {
			$this->crud_model->add_quiz($course_id);
			$this->session->set_flashdata('flash_message', _l('quiz_has_been_added_successfully'));
		} elseif ($action == 'edit') {
			$this->crud_model->edit_quiz($quiz_id);
			$this->session->set_flashdata('flash_message', _l('quiz_has_been_updated_successfully'));
		} elseif ($action == 'delete') {
			$this->crud_model->delete_section($course_id, $quiz_id);
			$this->session->set_flashdata('flash_message', _l('quiz_has_been_deleted_successfully'));
		}
		redirect(base_url('admin/course_form/course_edit/' . $course_id));
	}

	// Manage Quize Questions
	public function quiz_questions($quiz_id = "", $action = "", $question_id = "") {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$quiz_details = $this->crud_model->get_lessons('lesson', $quiz_id)->row_array();

		if ($action == 'add') {
			$response = $this->crud_model->add_quiz_questions($quiz_id);
			echo $response;
		} elseif ($action == 'edit') {
			$response = $this->crud_model->update_quiz_questions($question_id);
			echo $response;
		} elseif ($action == 'delete') {
			$response = $this->crud_model->delete_quiz_question($question_id);
			$this->session->set_flashdata('flash_message', _l('question_has_been_deleted'));
			redirect(base_url('admin/course_form/course_edit/' . $quiz_details['course_id']));
		}
	}

	// AJAX PORTION

	// this function is responsible for managing multiple choice question
	public function manage_multiple_choices_options() {
		$data['number_of_options'] = $this->input->post('number_of_options');
		$this->load->view('backend/admin/manage_multiple_choices_options', $data);
	}

	public function ajax_get_sub_category($category_id) {
		$data['sub_categories'] = $this->crud_model->get_sub_categories($category_id);
		return $this->load->view('backend/admin/ajax_get_sub_category', $data);
	}

	public function ajax_get_section($course_id) {
		$data['sections'] = $this->crud_model->get_section('course', $course_id)->result_array();
		return $this->load->view('backend/admin/ajax_get_section', $data);
	}

	public function ajax_get_video_details() {
		$video_details = $this->video_model->getVideoDetails($_POST['video_url']);
		echo $video_details['duration'];
	}

	public function ajax_sort_section() {
		$section_json = $this->input->post('itemJSON');
		$this->crud_model->sort_section($section_json);
	}

	public function ajax_sort_lesson() {
		$lesson_json = $this->input->post('itemJSON');
		$this->crud_model->sort_lesson($lesson_json);
	}

	public function ajax_sort_question() {
		$question_json = $this->input->post('itemJSON');
		$this->crud_model->sort_question($question_json);
	}

	public function fee_collection() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		$data['page_name'] 		= 'fee_collection';
		$data['page_title'] 	= _l('fee_collection');
		$data['fee_collection'] = $this->enrol_model->getFeeCollection();

		// pr($data);
		$this->load->view('backend/index', $data);
	}

	public function approve_fee_collection() {
		$json = [];

		$this->enrol_model->approveFeeCollection($this->input->post('id'));

		$json['success'] = _l('approve_successfully');

		output_json($json);
	}

	public function get_emis() {
		$json = [];

		if ($this->input->post('enrol_id') && ($enrol_info = $this->enrol_model->get($this->input->post('enrol_id')))) {
			if ($course_info = $this->course_model->get($enrol_info['course_id'])->row_array()) {
				$json['emis'] = [];

				foreach (json_decode($course_info['emi'], 1) as $key => $amount) {
					if (!empty($enrol_info['mode'])) {
						if (strpos($key, $enrol_info['mode']) !== false) {
							$json['emis'][] = [
								'key'		=> $key,
								'amount'	=> $amount,
							];
						}
					} else {
						$json['emis'][] = [
							'key'		=> $key,
							'amount'	=> $amount,
						];
					}
				}

				$json['success']					= _l('text_success');
			} else {
				$json['error'] 						= _l('error_course');
			}
		} else if ($this->input->post('course_id') && $this->input->post('mode')) {
			if ($course_info = $this->course_model->get($this->input->post('course_id'))->row_array()) {
				$json['emis'] = [];

				foreach (json_decode($course_info['emi'], 1) as $key => $amount) {
					if (strpos($key, $this->input->post('mode')) !== false) {
						$json['emis'][] = [
							'key'		=> $key,
							'amount'	=> $amount,
						];
					}
				}

				$json['success']					= _l('text_success');
			} else {
				$json['error'] 						= _l('error_course');
			}
		} else {
			$json['error']			= _('error_enrolment');
		}

		output_json($json);
	}

	public function get_attendance_students() {
		$json = [];

		if ($this->input->method() == 'post') {
			$this->load->library('form_validation');

			$this->form_validation->set_rules('class_id', _l('class'), 'trim|required|numeric');
			$this->form_validation->set_rules('id', _l('schedule'), 'trim|required|numeric');

			$valid = $this->form_validation->run();

			!$valid && ($json['error'] = validation_errors());

			if (!($schedule_info = $this->schedule_model->get($this->input->post('id')))) {
				$json['error'] = _li('Invalid Schedule');
			}

			if (!$json) {
				$demo_students = $this->lead_model->getDemoStudents($this->input->post('id'));
				$results = $this->class_model->get_all_students($schedule_info['class_id']);

				if ($schedule_info['is_demo']) {
					if ($schedule_info['mode'] == 'online') {
						$results = $demo_students;
					} else {
						$results = array_unique(array_merge($results, $demo_students));
					}
				}

				$attendance_info = $this->class_model->get_attendance(['schedule_id' => $this->input->post('id')])->row_array();

				if ($attendance_info) {
					$attendance_info['students'] = json_decode($attendance_info['students'], true);
				} else {
					$attendance_info['students'] = [];
				}

				if ($schedule_info['mode'] == 'online') {
					$zoom_link = $this->schedule_model->getScheduleLink([
						'class_id'	=> $schedule_info['class_id']
					]);
				}

				foreach ($results as $result) {
					$student_info = $this->student_model->get($result)->row_array();

					if (!$student_info) continue;

					$json['students'][] = [
						'student_id'		=> $student_info['id'],
						'demo'				=> in_array($student_info['id'], $demo_students),
						'name'				=> $student_info['first_name'] . ' ' . $student_info['last_name'],
						'link'				=> $schedule_info['mode'] == 'online' ? $zoom_link : false,
						'present'			=> in_array($student_info['id'], $attendance_info['students']),
					];
				}

				$json['error'] = $this->session->flashdata('error_message');
				$json['success'] = $this->session->flashdata('flash_message');
			}
		}

		output_json($json);
	}

	public function get_students() {
		$json['items'] = [];

		if (!isset($json['error'])) {
			foreach ($this->student_model->get_all([
				'mobile'	=> $this->input->get('search')
			])->result_array() as $result) {
				$json['items'][] = [
					'id'		=> $result['id'],
					'text'		=> $result['first_name'] . ' ' . $result['last_name'] . ' ' . $result['mobile'],
				];
			}
		}

		output_json($json);
	}

	public function get_classes() {
		$json = [];

		$this->form_validation->set_rules('course_id', _l('course_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('mode', _l('mode'), 'trim|required');
		$this->form_validation->set_rules('center_id', _l('center_id'), 'trim');

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if (!$json) {
			$json['classes'] = [];

			foreach ($this->class_model->get_all([
				'course_id'		=> $this->input->post('course_id'),
				'mode'			=> $this->input->post('mode'),
				'center_id'		=> $this->input->post('mode') == 'online' ? 0 : $this->input->post('center_id'),
				'is_demo'		=> $this->input->post('is_demo'),
			])->result_array() as $class) {
				// if ($this->input->post('mode') == 'online' && count($this->class_model->get_all_students($class['id'])) > 0) continue;

				$json['classes'][] = [
					'id'		=> $class['id'],
					'name'		=> vsprintf('%s :: %s', [
						$class['name'],
						$class['slot'],
					]),
				];
			}
		}

		output_json($json);
	}

	public function get_enrols_by_course() {
		$json = [];

		$this->form_validation->set_rules('course_id', _l('course_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('mode', _l('mode'), 'trim|required');

		$valid = $this->form_validation->run();

		!$valid && ($json['error'] = strip_tags(validation_errors()));

		if (!$json) {
			$json['enrols'] = [];

			foreach ($this->enrol_model->get_all([
				'course_id'		=> $this->input->post('course_id'),
				'mode'			=> $this->input->post('mode'),
			])->result_array() as $enrol) {
				$json['enrols'][] = [
					'id'		=> $enrol['id'],
					'name'		=> $enrol['user'],
					'course'	=> $enrol['course'],
				];
			}
		}

		output_json($json);
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

	private function _writeRowToCsv($results = [], $fp = null, $headers = []) {
		fputs($fp, "\xEF\xBB\xBF");

		fputcsv($fp, $headers);

		if (is_array($results) && $results && is_resource($fp) && is_array($headers) && $headers) {
			foreach ($results as $result) {
				$row = [];

				foreach ($headers as $header) {
					if (!empty($result[$header]) && is_array($result[$header])) {
						//self::_writeRowToCsv($result[$header], $fp, array_keys($result[$header]));
					} else {
						$row[] = !empty($result[$header]) ? $result[$header] : '';
					}
				}

				fputcsv($fp, $row);
			}
		}
	}

	private function _getAdminBarcode($data = 0) {
		$file = 'uploads/pdfs/' . $data . '.png';
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj(
			'C128',
			$data,
			160,
			40,
			'black',
			array(5, 5, 0, 5)
		)->setBackgroundColor('white');

		return $bobj->getHtmlDiv();

		file_put_contents(FCPATH . $file, $bobj->getPngData());
		return $file;
	}

	public function get_states() {
		$json['items'] = [];

		if (!isset($json['error'])) {
			foreach ($this->state_model->get_all([
				'search'	=> $this->input->get('search')
			])['rows'] as $result) {
				$json['items'][] = [
					'id'		=> $result['id'],
					'text'		=> $result['name'] . '( ' . $result['country'] . ' )',
				];
			}
		}

		output_json($json);
	}

	public function get_cities() {
		$json['items'] = [];

		if (!isset($json['error'])) {
			foreach ($this->city_model->get_all([
				'search'	=> $this->input->get('search')
			])['rows'] as $result) {
				$json['items'][] = [
					'id'		=> $result['id'],
					'text'		=> $result['name'] . '( ' . $result['state'] . ' )',
				];
			}
		}

		output_json($json);
	}

	public function get_sites() {
		$json['items'] = [];

		if (!isset($json['error'])) {
			foreach ($this->site_model->get_all([
				'search'	=> $this->input->get('search')
			])['rows'] as $result) {
				$json['items'][] = [
					'id'		=> $result['id'],
					'text'		=> $result['name'],
				];
			}
		}

		output_json($json);
	}

	public function get_books() {
		$json['items'] = [];

		if (!isset($json['error'])) {
			foreach ($this->book_model->get_all([
				'search'	=> $this->input->get('search')
			])['rows'] as $result) {
				$json['items'][] = [
					'id'		=> $result['id'],
					'text'		=> $result['name'],
				];
			}
		}

		output_json($json);
	}

	public function get_events() {
		$json['items'] = [];

		if (!isset($json['error'])) {
			foreach ($this->event_model->get_all([
				'search'	=> $this->input->get('search')
			])['rows'] as $result) {
				$json['items'][] = [
					'id'		=> $result['id'],
					'text'		=> $result['name'],
				];
			}
		}

		output_json($json);
	}

	public function get_users() {
		$json['items'] = [];

		if (!isset($json['error'])) {
			foreach ($this->student_model->get_all([
				'search'	=> $this->input->get('search')
			])['rows'] as $result) {
				$json['items'][] = [
					'id'		=> $result['id'],
					'text'		=> $result['first_name']. ' '.$result['last_name']
				];
			}
		}

		output_json($json);
	}

	private function _pagination($data = []){
		$config['base_url'] 		= $data['base_url'];
		$config['total_rows'] 		= $data['total'];
		$config['per_page'] 		= $data['limit'];
		$config['uri_segment'] 		= 3;
		$config['use_page_numbers'] = TRUE;
		$config['full_tag_open'] 	= '<ul class="pagination justify-content-center">';
		$config['full_tag_close'] 	= '</ul>';
		$config['num_tag_open'] 	= '<li class="page-item">';
		$config['num_tag_close'] 	= '</li>';
		$config['cur_tag_open'] 	= '<li class="page-item active"><span class="page-link">';
		$config['cur_tag_close'] 	= '</span></li>';
		$config['next_link'] 		= '&raquo;';
		$config['prev_link'] 		= '&laquo;';
		$config['next_tag_open'] 	= '<li class="page-item">';
		$config['next_tag_close'] 	= '</li>';
		$config['prev_tag_open'] 	= '<li class="page-item">';
		$config['prev_tag_close'] 	= '</li>';
		$config['num_links'] 		= 3;

		return $config;
	}
}
