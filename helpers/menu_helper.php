<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('get_navigation_menus')) {
	function get_navigation_menus() {
		$CI =& get_instance();
		$user_role_id 	= $CI->session->userdata('role_id');
		$user_email 	= $CI->session->userdata('user_email');

		$menus 		= [];
		$menus[]	= [
			'key'	=> 'dashboard',
			'icon'	=> 'fas fa-tachometer-alt',
			'name'	=> _l('dashboard'),
			'url'	=> 'admin',
			'roles'	=> [],
			'childs'=> [],
		];
		$menus[]	= [
			'key'	=> 'bripublish_panel',
			'icon'	=> 'fas fa-external-link-square-alt',
			'name'	=> _l('bripublish_panel'),
			'url'	=> 'https://cms.bripublish.com/',
			'roles'	=> [12],
			'childs'=> [],
		];
		$menus[]	= [
			'key'	=> 'competition',
			'icon'	=> 'fas fa-trophy',
			'name'	=> _l('competition'),
			'url'	=> '#',
			'roles'	=> [1, 5],
			'childs'=> [
				[
					'key'	=> 'competition',
					'name'	=> _l('competition'),
					'url'	=> 'admin/competition',
				],
				[
					'key'	=> 'competition_order',
					'name'	=> _l('competition_order'),
					'url'	=> 'admin/competition_order',
				],
				[
					'key'	=> 'competition_payment',
					'name'	=> _l('competition_payment'),
					'url'	=> 'admin/competition_payment',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'coupon',
			'icon'	=> 'fas fa-money-check',
			'name'	=> _l('coupon'),
			'url'	=> '#',
			'roles'	=> [1, 5],
			'childs'=> [
				[
					'key'	=> 'coupon',
					'name'	=> _l('coupon'),
					'url'	=> 'admin/coupon',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'orders',
			'icon'	=> 'fas fa-shopping-basket',
			'name'	=> _l('orders'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'all_orders',
					'name'	=> _l('all_orders'),
					'url'	=> 'admin/all_orders',
					'regex'	=> 'admin/(ge_|bw_|all_|order_clone/)?(orders|new_order|in_print_order|printed_order|afs|ready_to_ship|shipped_orders|delivered_order|return_order|reprint_order|cancel_order|refunded_order|escalated_order|cloned_order|\d)'
				],
				[
					'key'	=> 'bw_orders',
					'name'	=> _l('b_&_w_orders'),
					'url'	=> 'admin/bw_orders',
				],
				[
					'key'	=> 'ebook_orders',
					'name'	=> _l('eBook_orders'),
					'url'	=> 'admin/ebook_orders/0',
					'regex'	=> 'admin/ebook_orders/*',
				],
				[
					'key'	=> 'rejected_book',
					'name'	=> _l('rejected_book'),
					'url'	=> 'admin/rejected_book',
				],
				[
					'key'	=> 'order_privy',
					'name'	=> _l('confirm_orders'),
					'url'	=> 'admin/order_privy',
					'regex'	=> 'admin/order_privy/*',
				],
				[
					'key'	=> 'book_stocks',
					'name'	=> _l('book_stocks'),
					'url'	=> 'admin/book_stocks',
				],
				[
					'key'	=> 'address',
					'name'	=> _l('address'),
					'url'	=> 'admin/address',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'school_orders',
			'icon'	=> 'fas fa-school',
			'name'	=> _l('school_orders'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'school_orders',
					'name'	=> _l('school_orders'),
					'url'	=> 'admin/school_orders',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'medallion',
			'icon'	=> 'fas fa-medal',
			'name'	=> _l('medallion'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'medallions',
					'name'	=> _l('medallions'),
					'url'	=> 'admin/medallion',
				],
				[
					'key'	=> 'medallion_orders',
					'name'	=> _l('medallion_orders'),
					'url'	=> 'admin/medallion_orders',
					'regex'	=> 'admin/medallion_orders/*'
				],
				[
					'key'	=> 'school_medallion_orders',
					'name'	=> _l('school_medallion_orders'),
					'url'	=> 'admin/school_medallion_orders',
					'regex'	=> 'admin/medallion_orders/*'
				],
				[
					'key'	=> 'medallion_address',
					'name'	=> _l('medallion_address'),
					'url'	=> 'admin/medallion_address',
				],
				[
					'key'	=> 'medallion_feedback',
					'name'	=> _l('medallion_feedback'),
					'url'	=> 'admin/medallion_feedback',
				],
				[
					'key'	=> 'school_medallion_address',
					'name'	=> _l('school_medallion_address'),
					'url'	=> 'admin/school_medallion_address',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'shipments',
			'icon'	=> 'fas fa-truck',
			'name'	=> _l('shipments'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'amazon_kdp_order',
					'name'	=> _l('amazon_kdp_order'),
					'url'	=> 'admin/amazon_kdp_order',
				],
				[
					'key'	=> 'delivery_country',
					'name'	=> _l('delivery_country'),
					'url'	=> 'admin/delivery_country',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'design',
			'icon'	=> 'fas fa-palette',
			'name'	=> _l('design'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'genre',
					'name'	=> _l('genres'),
					'url'	=> 'admin/genre',
				],
				[
					'key'	=> 'category',
					'name'	=> _l('categories'),
					'url'	=> 'admin/category',
				],
				[
					'key'	=> 'themes',
					'name'	=> _l('themes'),
					'url'	=> 'admin/themes',
				],
				[
					'key'	=> 'covers',
					'name'	=> _l('book_covers'),
					'url'	=> 'admin/covers',
				],
				[
					'key'	=> 'fonts',
					'name'	=> _l('fonts'),
					'url'	=> 'admin/font',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'book',
			'icon'	=> 'fas fa-book',
			'name'	=> _l('book'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'books',
					'name'	=> _l('books'),
					'url'	=> 'admin/approved_books',
					'regex'	=> 'admin/(approved_books|in_review_books|assign_books|ordered_books_in_review)',
				],
				[
					'key'	=> 'customized_book',
					'name'	=> _l('customized_book'),
					'url'	=> 'admin/customized_book',
				],
				[
					'key'	=> 'clone_book',
					'name'	=> _l('clone_book'),
					'url'	=> 'admin/clone_books',
				],
				[
					'key'	=> 'customized_cover',
					'name'	=> _l('customized_cover'),
					'url'	=> 'admin/customized_cover',
				],
			],
		];

		$menus[]	= [
			'key'	=> 'review',
			'icon'	=> 'fas fa-user-edit',
			'name'	=> _l('review'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'book_rating',
					'name'	=> _l('book_reviews'),
					'url'	=> 'admin/book_rating',
				],
				[
					'key'	=> 'review_flag',
					'name'	=> _l('review_flag'),
					'url'	=> 'admin/review_flag',
				],
				[
					'key'	=> 'ai_review',
					'name'	=> _l('ai_reviews'),
					'url'	=> 'admin/book_ai_review',
				],
			],
		];

		$menus[]	= [
			'key'	=> 'isbn',
			'icon'	=> 'fas fa-book',
			'name'	=> _l('isbn'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'isbn_amazon_limit',
					'name'	=> _l('isbn_amazon_limit'),
					'url'	=> 'admin/isbn_amazon_limit',
				],
				[
					'key'	=> 'book_isbn',
					'name'	=> _l('book_isbn_assign'),
					'url'	=> 'admin/book_isbn',
				],
				[
					'key'	=> 'book_amazon_assign',
					'name'	=> _l('book_amazon_assign'),
					'url'	=> 'admin/book_isbn/amazon',
				]
			],
		];
		$menus[]	= [
			'key'	=> 'printer',
			'icon'	=> 'fas fa-print',
			'name'	=> _l('printers'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'printer',
					'name'	=> _l('printers'),
					'url'	=> 'admin/printer',
					'roles'	=> [1],
				],
				[
					'key'	=> 'printer_assignment',
					'name'	=> _l('printer_assignment'),
					'url'	=> 'admin/printer_assignment',
					'regex'	=> 'book_titles/\d'
				],
			],
		];
		$menus[]	= [
			'key'	=> 'dropshipper',
			'icon'	=> 'fas fa-print',
			'name'	=> _l('dropshippers'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'pickup_locations',
					'name'	=> _l('pickup_locations'),
					'url'	=> 'admin/pickup_locations',
				],
				[
					'key'	=> 'dropshipper',
					'name'	=> _l('stats'),
					'url'	=> 'admin/dropshipper_stats',
					'roles'	=> [1],
				],
				[
					'key'	=> 'dropshippers',
					'name'	=> _l('dropshippers'),
					'url'	=> 'admin/dropshippers',
				],
				[
					'key'	=> 'colored_orders',
					'name'	=> _l('colored_orders'),
					'url'	=> 'admin/dropshipper_all_orders',
				],
				[
					'key'	=> 'bw_orders',
					'name'	=> 'BW Orders',
					'url'	=> 'admin/dropshipper_all_bw_orders',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'reports',
			'icon'	=> 'fas fa-chart-bar',
			'name'	=> _l('reports'),
			'url'	=> '#',
			'roles'	=> [1, 5],
			'childs'=> [
				[
					'key'	=> 'subscribers',
					'name'	=> _l('subscriber_payments'),
					'url'	=> 'admin/subscribers',
				],
				[
					'key'	=> 'payment',
					'name'	=> _l('order_payments'),
					'url'	=> 'admin/payment',
				],
				[
					'key'	=> 'online_stats',
					'name'	=> _l('online_stats'),
					'url'	=> 'admin/online_stats',
				],
				[
					'key'	=> 'thirdparty_service',
					'name'	=> _l('thirdparty_service'),
					'url'	=> 'admin/thirdparty_service',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'author_earning',
			'icon'	=> 'fas fa-database',
			'name'	=> _l('author_earnings'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'amazon_voucher',
					'name'	=> _l('amazon_voucher'),
					'url'	=> 'admin/amazon_voucher',
				],
				[
					'key'	=> 'author_royalty',
					'name'	=> _l('author_royalty'),
					'url'	=> 'admin/author_royalty',
				],
				[
					'key'	=> 'user_credit_request',
					'name'	=> _l('user_redeem_request'),
					'url'	=> 'admin/user_credit_request',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'broadcast_partner',
			'icon'	=> 'fas fa-rss',
			'name'	=> _l('broadcast_partners'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'broadcast_partner',
					'name'	=> _l('broadcast_partner'),
					'url'	=> 'admin/broadcast_partner',
				],
				[
					'key'	=> 'broadcast_partner_slot',
					'name'	=> _l('broadcast_partner_slot'),
					'url'	=> 'admin/broadcast_partner_slot',
				]
			],
		];
		$menus[]	= [
			'key'	=> 'leads',
			'icon'	=> 'fas fa-headphones',
			'name'	=> _l('leads'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'leads',
					'name'	=> _l('leads'),
					'url'	=> 'admin/lead',
				],
				[
					'key'	=> 'school_lead',
					'name'	=> _l('school_lead'),
					'url'	=> in_array($user_role_id, [1, 14]) || in_array($user_email, TELECALLER_CAN_ASSIGN)
						? 'admin/school_lead'
						: 'admin/assign_leads',
				],
				[
					'key'	=> 'download_leads',
					'name'	=> _l('download_leads'),
					'url'	=> 'admin/download_lead',
					'roles'	=> [1],
				],
			],
		];
		$menus[]	= [
			'key'	=> 'users',
			'icon'	=> 'fas fa-users',
			'name'	=> _l('users'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'department',
					'name'	=> _l('departments'),
					'url'	=> 'admin/department',
					'roles'	=> [1],
				],
				[
					'key'	=> 'roles',
					'name'	=> _l('roles'),
					'url'	=> 'admin/roles',
					'roles'	=> [1],
				],
				[
					'key'	=> 'admins',
					'name'	=> _l('admins'),
					'url'	=> 'admin/admins',
					'roles'	=> [1],
				],
				[
					'key'	=> 'system_users',
					'name'	=> _l('system_users'),
					'url'	=> 'admin/system_users',
				],
				[
					'key'	=> 'teachers',
					'name'	=> _l('teachers'),
					'url'	=> 'admin/teachers',
					'roles'	=> [1, 5],
				],
				[
					'key'	=> 'telecallers',
					'name'	=> _l('telecallers'),
					'url'	=> 'admin/telecallers',
				],
				[
					'key'	=> 'students',
					'name'	=> _l('students'),
					'url'	=> 'admin/students',
				],
				[
					'key'	=> 'printers',
					'name'	=> _l('printers'),
					'url'	=> 'admin/printers',
					'roles'	=> [1, 5],
				],
				[
					'key'	=> 'unsubscribed',
					'name'	=> _l('unsubscribed'),
					'url'	=> 'admin/unsubscribed',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'events',
			'icon'	=> 'fas fa-layer-group',
			'name'	=> _l('events'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'event_type',
					'name'	=> _l('event_types'),
					'url'	=> 'admin/event_type',
				],
				[
					'key'	=> 'event_partner',
					'name'	=> _l('event_partners'),
					'url'	=> 'admin/event_partner',
				],
				[
					'key'	=> 'event',
					'name'	=> _l('events'),
					'url'	=> 'admin/event',
				],
				[
					'key'	=> 'event_dashboard',
					'name'	=> _l('event_dashboard'),
					'url'	=> 'admin/get_dashboard_count',
				],
				[
					'key'	=> 'telecaller_dashboard',
					'name'	=> _l('telecaller_dashboard'),
					'url'	=> 'admin/telecaller_dashboard',
				],
				[
					'key'	=> 'event_award',
					'name'	=> _l('event_award'),
					'url'	=> 'admin/event_award',
				],
				[
					'key'	=> 'event_award_group',
					'name'	=> _l('event_award_group'),
					'url'	=> 'admin/event_award_group',
				],
				[
					'key'	=> 'data_charts',
					'name'	=> _l('data_charts'),
					'url'	=> 'admin/data_chart',
				],
				[
					'key'	=> 'nyaf_authors_invite',
					'name'	=> _l('invited_authors'),
					'url'	=> 'admin/nyaf_authors_invite',
				],
				[
					'key'	=> 'book_exhibition',
					'name'	=> _l('book_exhibition'),
					'url'	=> 'admin/exhibition_authors',
				],
				[
					'key'	=> 'nyaf_schools_invite',
					'name'	=> _l('invited_schools'),
					'url'	=> 'admin/nyaf_schools_invite',
				],
				[
					'key'	=> 'referral_user',
					'name'	=> _l('referral_user'),
					'url'	=> 'admin/referral_user',
				],
				[
					'key'	=> 'award_address',
					'name'	=> _l('award_address'),
					'url'	=> 'admin/award_address',
				],
				[
					'key'	=> 'school_award_address',
					'name'	=> _l('school_award_address'),
					'url'	=> 'admin/school_award_address',
				],
				[
					'key'	=> 'master_classes',
					'name'	=> _l('master_classes'),
					'url'	=> 'admin/master_class',
				],
				[
					'key'	=> 'event_data_cleaning',
					'name'	=> _l('event_data_cleaning'),
					'url'	=> 'admin/clean_event_data_form',
				],
				[
					'key'	=> 'event_pdf_content',
					'name'	=> _l('event_pdf_content'),
					'url'	=> 'admin/event_pdf',
				],
				[
					'key'	=> 'deactivate_user',
					'name'	=> _l('deactivate_user'),
					'url'	=> 'admin/deactivate_user',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'event_exhibition',
			'icon'	=> 'fas fa-calendar',
			'name'	=> _l('event_exhibition'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'event_invite_template',
					'name'	=> _l('event_invite_template'),
					'url'	=> 'admin/event_invite_template',
				],
				[
					'key'	=> 'event_user_invite',
					'name'	=> _l('event_user_invite'),
					'url'	=> 'admin/event_user_invite',
				],
				[
					'key'	=> 'event_school_invite',
					'name'	=> _l('event_school_invite'),
					'url'	=> 'admin/event_school_invite',
				],
				[
					'key'	=> 'author_wall',
					'name'	=> _l('author_wall'),
					'url'	=> 'admin/author_wall',
				],
				[
					'key'	=> 'event_exhibition',
					'name'	=> _l('event_exhibition'),
					'url'	=> 'admin/event_exhibition',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'certificates',
			'icon'	=> 'fas fa-award',
			'name'	=> _l('certificates'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'certificate_types',
					'name'	=> _l('certificate_types'),
					'url'	=> 'admin/certificate_types',
				],
				[
					'key'	=> 'certificate_message_templates',
					'name'	=> _l('message_templates'),
					'url'	=> 'admin/certificate_message_templates',
				],
				[
					'key'	=> 'certificate_templates',
					'name'	=> _l('templates'),
					'url'	=> 'admin/certificate_templates',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'event_challenges',
			'icon'	=> 'fas fa-calendar-check',
			'name'	=> _l('event_challenges'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'event_challenges_weekly',
					'name'	=> _l('event_challenges_weekly'),
					'url'	=> 'admin/event_challenges_weekly',
				],
				[
					'key'	=> 'event_challenges_general',
					'name'	=> _l('event_challenges_general'),
					'url'	=> 'admin/event_challenges_general',
				],
				[
					'key'	=> 'event_challenges_genre',
					'name'	=> _l('event_challenges_genre'),
					'url'	=> 'admin/event_challenges_genre',
				],
				[
					'key'	=> 'event_challenges_school',
					'name'	=> _l('event_challenges_school'),
					'url'	=> 'admin/event_challenges_school',
				],
				[
					'key'	=> 'event_challenges_city',
					'name'	=> _l('event_challenges_city'),
					'url'	=> 'admin/event_challenges_city',
				],
				[
					'key'	=> 'event_challenges_state',
					'name'	=> _l('event_challenges_state'),
					'url'	=> 'admin/event_challenges_state',
				],
				[
					'key'	=> 'event_challenges_country',
					'name'	=> _l('event_challenges_country'),
					'url'	=> 'admin/event_challenges_country',
				],
				[
					'key'	=> 'event_challenges_group',
					'name'	=> _l('event_challenges_group'),
					'url'	=> 'admin/event_challenges_group',
				],
				[
					'key'	=> 'event_challenge_vote',
					'name'	=> _l('event_challenge_vote'),
					'url'	=> 'admin/event_challenge_vote',
				],
				[
					'key'	=> 'event_challenge_jury',
					'name'	=> _l('event_challenge_jury'),
					'url'	=> 'admin/event_challenge_jury',
				],
				[
					'key'	=> 'event_league_group',
					'name'	=> _l('event_league_group'),
					'url'	=> 'admin/event_league_group',
				],
				[
					'key'	=> 'league_message_template',
					'name'	=> _l('league_message_template'),
					'url'	=> 'admin/league_template',
				],
				[
					'key'	=> 'league_breakpoint_message',
					'name'	=> _l('league_breakpoint_message'),
					'url'	=> 'admin/league_breakpoint_message',
				],
				[
					'key'	=> 'event_challenge_literary_leader',
					'name'	=> _l('event_challenge_literary_leader'),
					'url'	=> 'admin/event_challenge_literary_leader',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'logistics',
			'icon'	=> 'fas fa-warehouse',
			'name'	=> _l('logistics'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'dashboard',
					'name'	=> _l('dashboard'),
					'url'	=> 'admin/get_logistic_dashboard_int',
				],
				[
					'key'	=> 'qa_qc',
					'name'	=> _l('QA_QC'),
					'url'	=> 'admin/get_logistic_qaqc_int',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'marketing',
			'icon'	=> 'fas fa-poll',
			'name'	=> _l('marketing'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'marketing',
					'name'	=> _l('marketing'),
					'url'	=> 'admin/marketing',
				],
				[
					'key'	=> 'share_template',
					'name'	=> _l('share_template'),
					'url'	=> 'admin/share_template',
				],
				[
					'key'	=> 'utm_source',
					'name'	=> _l('utm_source'),
					'url'	=> 'admin/utm_source',
				],
				[
					'key'	=> 'marketing_dataset',
					'name'	=> _l('marketing_dataset'),
					'url'	=> 'admin/marketing_dataset',
				],
				[
					'key'	=> 'marketing_settings',
					'name'	=> _l('marketing_settings'),
					'url'	=> 'admin/marketing_settings',
				],
				[
					'key'	=> 'announcement',
					'name'	=> _l('announcement'),
					'url'	=> 'admin/announcement',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'blog',
			'icon'	=> 'fas fa-rss',
			'name'	=> _l('blog'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'blog',
					'name'	=> _l('blogs'),
					'url'	=> 'admin/blog',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'email_templates',
			'icon'	=> 'fas fa-envelope',
			'name'	=> _l('email_templates'),
			'url'	=> '#',
			'roles'	=> [1, 5],
			'childs'=> [
				[
					'key'	=> 'user',
					'name'	=> _l('user_templates'),
					'url'	=> 'admin/templates/user',
				],
				[
					'key'	=> 'book',
					'name'	=> _l('book_templates'),
					'url'	=> 'admin/templates/book',
				],
				[
					'key'	=> 'school',
					'name'	=> _l('school_templates'),
					'url'	=> 'admin/templates/school',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'localisation',
			'icon'	=> 'fas fa-map-marker',
			'name'	=> _l('localisation'),
			'url'	=> '#',
			'roles'	=> [1, 5],
			'childs'=> [
				[
					'key'	=> 'countries',
					'name'	=> _l('countries'),
					'url'	=> 'admin/countries',
					'regex'	=> 'admin/country_*',
				],
				[
					'key'	=> 'states',
					'name'	=> _l('states'),
					'url'	=> 'admin/states',
					'regex'	=> 'admin/state_*',
				],
				[
					'key'	=> 'cities',
					'name'	=> _l('cities'),
					'url'	=> 'admin/cities',
					'regex'	=> 'admin/city_*',
				],
				[
					'key'	=> 'pincode_zone',
					'name'	=> _l('pincode_zone'),
					'url'	=> 'admin/pincode_zone',
				],
				[
					'key'	=> 'currencies',
					'name'	=> _l('currencies'),
					'url'	=> 'admin/currencies',
					'regex'	=> 'admin/currency_*',
				],
				[
					'key'	=> 'payment_settings',
					'name'	=> _l('system_currency_setting'),
					'url'	=> 'admin/payment_settings',
				],
				[
					'key'	=> 'blocked_ip',
					'name'	=> _l('blocked_ip'),
					'url'	=> 'admin/blocked_ip',
				],
				[
					'key'	=> 'language',
					'name'	=> _l('language'),
					'url'	=> 'admin/language',
				],
				[
					'key'	=> 'translation',
					'name'	=> _l('translation'),
					'url'	=> 'admin/translation',
				],
				[
					'key'	=> 'group_region',
					'name'	=> _l('group_region'),
					'url'	=> 'admin/group_region',
				],
				[
					'key'	=> 'spam_word',
					'name'	=> _l('spam_word'),
					'url'	=> 'admin/spam_word',
				],
			],
		];
		$menus[]	= [
			'key'	=> 'cross_word',
			'icon'	=> 'fas fa-store',
			'name'	=> _l('cross_word'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'crossword_store',
					'name'	=> _l('crossword_store'),
					'url'	=> 'admin/crossword_store',
				],
			],
		];

		$menus[]	= [
			'key'	=> 'ticket',
			'icon'	=> 'fas fa-ticket-alt',
			'name'	=> _l('tickets'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'ticket_category',
					'name'	=> _l('categories'),
					'url'	=> 'admin/ticket_category',
				],
				[
					'key'	=> 'ticket_status',
					'name'	=> _l('status'),
					'url'	=> 'admin/ticket_status',
				],
				[
					'key'	=> 'ticket_priority',
					'name'	=> _l('priority'),
					'url'	=> 'admin/ticket_priority',
				],
				[
					'key'	=> 'ticket',
					'name'	=> _l('tickets'),
					'url'	=> 'admin/ticket',
				],
			],
		];

		$menus[]	= [
			'key'	=> 'schools',
			'icon'	=> 'fas fa-school',
			'name'	=> _l('schools/sites'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'sites',
					'name'	=> _l('sites'),
					'url'	=> 'admin/sites',
				],
				[
					'key'	=> 'site_types',
					'name'	=> _l('site_types'),
					'url'	=> 'admin/site_types',
				],
				[
					'key'	=> 'schools',
					'name'	=> _l('schools'),
					'url'	=> 'admin/schools',
				],
				[
					'key'	=> 'school_tags',
					'name'	=> _l('school_tags'),
					'url'	=> 'admin/school_tags',
				],
			],
		];

		$menus[]	= [
			'key'	=> 'data_cleaning',
			'icon'	=> 'fas fa-school',
			'name'	=> _l('data_clean_&_merge'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'event_data_cleaning',
					'name'	=> _l('event_data_cleaning'),
					'url'	=> 'admin/clean_event_data_form',
				],
				[
					'key'	=> 'merge_site_data',
					'name'	=> _l('merge_site_only'),
					'url'	=> 'admin/merge_site_data_form',
				],
			],
		];

		$menus[]	= [
			'key'	=> 'message_template',
			'icon'	=> 'fas fa-envelope',
			'name'	=> _l('message_template'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'template_type',
					'name'	=> _l('template_type'),
					'url'	=> 'admin/message_template_type',
				],
			],
		];

		$menus[]	= [
			'key'	=> 'subscription',
			'icon'	=> 'fas fa-envelope',
			'name'	=> _l('subscription'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'subscription_plan',
					'name'	=> _l('subscription_plan'),
					'url'	=> 'admin/subscription_plan',
				],
				// [
				// 	'key'	=> 'countries',
				// 	'name'	=> _l('enable_country_publish_limit'),
				// 	'url'	=> 'admin/countries',
				// ],
			],
		];

		$menus[]	= [
			'key'	=> 'notification',
			'icon'	=> 'fas fa-envelope',
			'name'	=> _l('notification'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'notification',
					'name'	=> _l('notification'),
					'url'	=> 'admin/notification',
				],
			],
		];

		$menus[]	= [
			'key'	=> 'brisharks',
			'icon'	=> 'fas fa-fish',
			'name'	=> _l('brisharks'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'bs_event',
					'name'	=> _l('event'),
					'url'	=> 'admin/bs_event',
				],
				[
					'key'	=> 'bs_event_challenge',
					'name'	=> _l('event_challenge'),
					'url'	=> 'admin/bs_event_challenge',
				],
				[
					'key'	=> 'bs_event_invite',
					'name'	=> _l('event_invite'),
					'url'	=> 'admin/bs_event_invite',
				],
				[
					'key'	=> 'bs_message_template',
					'name'	=> _l('message_template'),
					'url'	=> 'admin/bs_message_template',
				],
				[
					'key'	=> 'bs_payment',
					'name'	=> _l('payment'),
					'url'	=> 'admin/bs_payment',
				],
				[
					'key'	=> 'bs_user_invite_code',
					'name'	=> _l('invite_code'),
					'url'	=> 'admin/bs_user_invite_code',
				],
				[
					'key'	=> 'bs_user',
					'name'	=> _l('user'),
					'url'	=> 'admin/bs_user',
				],
				[
					'key'	=> 'bs_startup',
					'name'	=> _l('startup'),
					'url'	=> 'admin/bs_startup',
				],
			],
		];

		$menus[]	= [
			'key'	=> 'external_crm',
			'icon'	=> 'fas fa-link',
			'name'	=> _l('external_crm'),
			'url'	=> '#',
			'roles'	=> [],
			'childs'=> [
				[
					'key'	=> 'brisharks',
					'name'	=> _l('brisharks'),
					'url'	=> 'admin/external_crm/brisharks',
				],
				[
					'key'	=> 'briminds',
					'name'	=> _l('briminds'),
					'url'	=> 'admin/external_crm/briminds',
				],
			]
		];

		$menus[]	= [
			'key'	=> 'settings',
			'icon'	=> 'fas fa-cogs',
			'name'	=> _l('settings'),
			'url'	=> '#',
			'roles'	=> [1, 5],
			'childs'=> [
				[
					'key'	=> 'system_settings',
					'name'	=> _l('system_settings'),
					'url'	=> 'admin/system_settings',
					'roles'	=> [1],
				],
				[
					'key'	=> 'frontend_settings',
					'name'	=> _l('basic_settings'),
					'url'	=> 'admin/frontend_settings',
					'roles'	=> [1],
				],
				[
					'key'	=> 'smtp_settings',
					'name'	=> _l('smtp_settings'),
					'url'	=> 'admin/smtp_settings',
					'roles'	=> [1],
				],
				[
					'key'	=> 'sms_template',
					'name'	=> _l('sms_template'),
					'url'	=> 'admin/sms_template',
				],
				[
					'key'	=> 'auto_report',
					'name'	=> _l('auto_report'),
					'url'	=> 'admin/auto_report',
				],
				[
					'key'	=> 'order_privy_setting',
					'name'	=> _l('order_privy_setting'),
					'url'	=> 'admin/order_privy_setting',
				],
				[
					'key'	=> 'export',
					'name'	=> _l('export_data'),
					'url'	=> 'export',
				],
				[
					'key'	=> 'import',
					'name'	=> _l('import_data'),
					'url'	=> 'import',
				],
				[
					'key'	=> 'import_job',
					'name'	=> _l('import_job'),
					'url'	=> 'admin/import_job',
				],
				[
					'key'	=> 'manage_language',
					'name'	=> _l('manage_language'),
					'url'	=> 'admin/manage_language',
				],
				[
					'key'	=> 'data_cleaning',
					'name'	=> _l('data_cleaning'),
					'url'	=> 'admin/dataCleaning',
					'roles'	=> [1],
				],
				[
					'key'	=> 'log',
					'name'	=> _l('log'),
					'url'	=> 'log',
					'roles'	=> [1],
				],
				[
					'key'	=> 'stripe_payment',
					'name'	=> _l('stripe_payment'),
					'url'	=> 'admin/stripe_payment',
					'roles'	=> [1],
				],
				[
					'key'	=> 'cache_stats',
					'name'	=> _l('cache_stats'),
					'url'	=> 'admin/cache_stats',
					'roles'	=> [1],
				],
			],
		];

		return $menus;
	}
}

if (!function_exists('check_route_access')) {
	function check_route_access($item = []) {
		$route = $item['url'];
		$roles = $item['roles'] ?? [];

		$CI =& get_instance();
		$user_role_id = $CI->session->userdata('role_id');

		if (
			in_array(get_class($CI), ['Import'])
			&& in_array($user_role_id, [1, 5, 10])
		) {
			return true;
		}

		if ($user_role_id == 1 && in_array(get_class($CI), ['Log', 'Import', 'Export', 'DataCleaning'])) {
			return true;
		}

		if (substr($route, 0, 6) === 'admin/') {
			$CI->load->model('user/Role_model');
			$role_info = $CI->Role_model->get($user_role_id);

			$routes = explode('/', $route);
			$method = $routes[1] ?? '';
			$module	= basename((new ReflectionClass('Admin'))->getMethod($method)->getFileName(), '.php');

			return in_array(
				$method,
				$role_info['permissions']['admin'][$module] ?? []
			);
		}

		return $user_role_id == 1 || in_array($user_role_id, $roles) || empty($roles);
	}
}

if (!function_exists('check_has_permission')) {
	function check_has_permission() {
		$CI =& get_instance();
		
		$user_id 		= $CI->session->userdata('user_id');
		$user_role_id 	= $CI->session->userdata('role_id');
		$user_email 	= $CI->session->userdata('user_email');

		$is_ajax 		= $CI->input->is_ajax_request();
		$method			= $CI->router->fetch_method();

		if (empty($user_role_id) || empty($user_email)) {
			redirect(base_url('login'), 'refresh');
		}

		if (in_array(get_class($CI), ['Admin', 'Log', 'Import', 'Export', 'DataCleaning'])) {
			if (
				in_array(get_class($CI), ['Import'])
				&& in_array($user_role_id, [1, 5, 10])
			) {
				return;
			}

			if (in_array(get_class($CI), ['Log', 'Import', 'Export', 'DataCleaning']) && $user_role_id != 1) {
				if ($is_ajax) {
					exit(json_encode(['data' => []]));
				}

				// $CI->session->set_flashdata('error_message', _l('not_authorized'));
				redirect(base_url('admin'), 'refresh');
			}

			$CI->load->model('user/Role_model');
			$role_info = $CI->Role_model->get($user_role_id);
			// echo "<pre>";
			// print_r($role_info);
			// exit('check permission');
			if (in_array(get_class($CI), ['Admin']) &&
				$method !== 'index' &&
				!in_array(
					$method,
					$role_info['permissions'][mb_strtolower(get_class($CI))][basename((new ReflectionClass('Admin'))->getMethod($method)->getFileName(), '.php')] ?? []
				)
			) {
				if ($user_id != 1) {
					if ($is_ajax) {
						exit(json_encode(['data' => []]));
					}

					// $CI->session->set_flashdata('error_message', _l('not_authorized'));
					redirect(base_url('admin'), 'refresh');
				}
			}

			log_system_access();
		}
	}
}

if (!function_exists('log_system_access')) {
	function log_system_access() {
		$CI =& get_instance();

		$CI->load->library('user_agent');

		$user_id 	= $CI->session->userdata('user_id');
		$role_id 	= $CI->session->userdata('role_id');
		$is_ajax 	= $CI->input->is_ajax_request();
		$method		= get_class($CI) . '::' . $CI->router->fetch_method();
		$data 		= $CI->security->xss_clean($CI->input->post());
		$is_ajax 	= $CI->input->is_ajax_request();

		if (!empty($data) || strpos(mb_strtolower($CI->uri->uri_string()), 'delete') !== false) {
			if (!$is_ajax) {
				$data['uri'] = str_replace('/' , '_', $CI->uri->uri_string());
			}

			$CI->db->insert('system_access_log', [
				'user_id'		=> (int)$user_id,
				'role_id'		=> (int)$role_id,
				'method'		=> $method,
				'data'			=> json_encode($data),
				'browser'		=> $CI->agent->browser(),
				'platform'		=> $CI->agent->platform(),
				'ip'			=> $CI->input->ip_address(),
				'date_added'	=> date('Y-m-d H:i:s'),
			]);
		}
	}
}
