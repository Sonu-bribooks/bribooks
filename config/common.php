<?php defined('BASEPATH') or exit('No direct script access allowed');

$config['common_config'] = true;

define('LOGIN_SESSION_ACTIVE_DAYS', ENVIRONMENT == 'production' ? 90 : 10);

define('ENV_API', false);

define('TESTING_PRICE', 1);
define('LOG_API', true);

define('COMPETITION', [
	1 	=> 1,
	2 	=> 2,
]);

$config['default_site_id'] = 2;
$config['default_site_currency_code'] = 'USD';
$config['default_isbn'] = '9789394848XXX';
$config['api'] = '';
$config['api_icode'] = '';
$config['event_api_icode'] = '';
$config['api_icode_new'] = '';
$config['event_api_icode_new'] = '';
$config['online_expire'] = 15;

$config['google_client_id'] = '';

$config['password_salt']  = '';

$config['bb_secret_jwt_token']  = ENVIRONMENT === 'production'
	? ''
	: '';

// no of days to keep log files
define('LOG_TIME', 7);

define('SMTP_PASS', '');

define('LIMIT_ONLINE_SCHEDULE', 5);

define('LOGIN_CODE_VALIDITY', 5);

define('DEFAULT_OTP', 878787);

define('DEMO_DATES', [
	'Weekday(Monday-Friday)',
	'Weekend(Saturday-Sunday)',
]);

define('BASE_LEVEL', 10);
define('FREE_LEVEL', 0);

define('DEMO_DATES_IS', [
	'Weekday(Sunday-Thrusday)',
	'Weekend(Friday-Saturday)',
]);

define('TESTING_MOBILES', [
	'919818651520',
	'919058404695',
	'918112237758',
	'917303234240',
	'919534086239',
]);

define('TESTING_EMAILS', [
	'abhishek@youbooks.co',
	'deepak789456123@gmail.com',
	'publishing@bribooks.com'
]);

define('REVIEWER_EMAILS', [
	'abhishek@bribooks.com',
	'etvinita@gmail.com',
	'amidror@gmail.com',
	'review@bribooks.com',
]);

define('TELECALLER_CAN_ASSIGN', [
	'abhishek@youbooks.co',
]);

define('MASTER_TELECALLERS', [
	'dm@bribooks.com',
]);

define('LEAD_STATUSES', [
	'not_responding' 		=> 'not_responding',
	'not_interested'		=> 'not_interested',
	'course_fee_details' 	=> 'course_fee_and_details',
	'followup'				=> 'followup',
	'call_back_requested' 	=> 'call_back_requested',
	'disconnecting_calls' 	=> 'disconnecting_calls',
	'needed_help' 			=> 'needed_help',
	'no_query' 				=> 'no_query',
	'no_response' 			=> 'no_response',
	'out_of_service' 		=> 'out_of_service',
	'resolved' 				=> 'resolved',
	'switched_off' 			=> 'switched_off',
]);

define('TELEPHONE_CODES', [
	'IN'		=> 91,
]);

define('ZOOM_API_KEY', '');
define('ZOOM_API_SECRET', '');

define('ZOOM_APIS', []);

if (ENVIRONMENT == 'production') {
	define('DEBUG_API', false);
	define('DEBUG_API_DEEP', false);
} elseif (ENVIRONMENT == 'testing') {
	define('DEBUG_API', false);
	define('DEBUG_API_DEEP', false);
} else {
	define('DEBUG_API', true);
	define('DEBUG_API_DEEP', false);
}

define('DEMO_DISCOUNT_CODES', []);

if (ENVIRONMENT == 'production') {
	define('USER_URL', 'https://www.bribooks.com/');
	define('USER_INVOICE_URL', 'https://cms.bribooks.com/');
	define('SC_USER_ADDRESS_URL', 'https://www.camp.bribooks.com/');
	define('USER_YAF_URL', 'https://www.yaf.bribooks.com/');
	define('USER_SCHOOL_URL', 'https://www.schools.bribooks.com/');
	define('UNSUBSCRIBE_URL', 'https://www.bribooks.com/unsubscribe/');
} else {
	define('USER_URL', 'https://www.uat.bribooks.com/');
	define('USER_INVOICE_URL', 'https://crm.dev.bribooks.com/');
	define('SC_USER_ADDRESS_URL', 'https://www.uat.camp.bribooks.com/');
	define('USER_YAF_URL', 'https://uat.yaf.bribooks.com/');
	define('USER_SCHOOL_URL', 'https://uat.schools.bribooks.com/');
	define('UNSUBSCRIBE_URL', 'https://uat.bribooks.com/unsubscribe/');
}

define('FREE_BOOKS', [
	101,
	446,
	213,
]);

define('BANNER_BOOKS', [
	1	=> [
		1162,
		928,
		1138,
		202,
		239,
		240,
		260,
		351,
	],
	2 	=> [
		202,
		239,
		260,
		240,
		351
	],
]);

define('DEFAULT_STATS', [
	8	=> [4.5, 26],
	81	=> [4.3, 27],
	200	=> [4.8, 32],
	202	=> [4.5, 22],
	191	=> [4.8, 33],
	233	=> [4.4, 29],
	33	=> [4.8, 36],
	150	=> [4.8, 42],
	57	=> [4.4, 35],
	92	=> [4.9, 39],
	351	=> [4.1, 47],
	260	=> [4.2, 36],
	240	=> [4.4, 39],
	213	=> [4.9, 58],
	239	=> [4.9, 38],
]);

define('DEFAULT_STATS_EX', [
	605
]);

define('DIR_IMAGE', FCPATH . 'uploads/gallery/');

define('book_edit', [771, 23, 43, 164246]);
// define('TELECALLER_CAN_ASSIGN',[23,348]);

define('ISBN_LIMIT', 50);
define('AMAZON_LIMIT', 70);

define('GLOBAL_ISBN_LIMIT', 70);
define('GLOBAL_AMAZON_LIMIT', 100);

define('PRINTING_LIMIT', [
	'paperback'		=> 16,
	'hard_cover'	=> 36,
	'black_white'	=> 16,
]);

define('PRINTER_PDF_DIR', FCPATH . 'uploads/pdfs/bookpdssjhgfjhgsdf/');
define('DIR_NOTIFICATION_STORAGE', FCPATH . 'uploads/notificationsgffhfhgfhfhg6444556411/');
define('NOTIFICATION_EMAIL', 'sahildhingra@bribooks.com');

define('STUDENT_GRADES', [
	'IL'	=> [
		1	=> 'א',
		2	=> 'ב',
		3	=> 'ג',
		4	=> 'ד',
		5	=> 'ה',
		6	=> 'ו',
		7	=> 'ז',
		8	=> 'ח',
		9	=> 'ט',
		10	=> 'י',
		11	=> 'יא',
		12	=> 'יב'
	]
]);

define('PARENT_SITE_CODES', [
	'in',
	'ge',
	'NYAFIND2022',
	'ge-NWFIS',
	'in-IGBWF',
	'ge-kido',
	'in-sc',
	'ge-UAE',
	'readers-can-write-2023',
	'parent-child-2023',
	'in-ivykids-2023',
	'ge-NYAFUS',
	'in-nyaf23',
	'ge-bwf',
	'ge-yabwf-kw-23',
	'ge-NYAFUK',
	'ge-NYAF Malaysia',
	'ge-NYAF-Singapore'
]);

if (ENVIRONMENT === 'production') {
	define('BB_UID', [
		343302,
		343299,
		343249,
		343245,
		315,
		350398,
		350056,
		350054,
		350475,
		350053,
		343242,
	]);
} else {
	define('BB_UID', []);
}

define('USER_REFERRAL_LIMIT', 5);

// App specified
define('RANKING_APP_NOTIFICATION', true);


define('WRITING_PAGE_LIMIT', 14);

define('CUSTOM_THEME_COMMENT', [
	'Blank Space',
	'Image With Watermark',
]);

if (ENVIRONMENT == 'production') {
	define('SDG_CATEGORIES', [
		82,
		83,
		84
	]);
} else {
	define('SDG_CATEGORIES', [
		53,
		54,
		55
	]);
}

define('DB_ACCESS_EMAILS', ENVIRONMENT === 'production' ? [
	'abhishek@youbooks.co',
	'rupesh@bribooks.com',
	'pratyush@bribooks.com',
	'yash@bribooks.com',
	// 'adarsh@bribooks.com',
	// 'yash@bribooks.com',
	'deepak@bribooks.com',
] : [
]);

if (ENVIRONMENT === 'production') {
	define('RECAPTCHA_SECRET', '');
} else {
	define('RECAPTCHA_SECRET', '');
}

define('EMAIL_SERVICE', 'ses');
define('EMAIL_ACCOUNTS', [
	'ses' => [
		'host'		=> 'email-smtp.us-east-1.amazonaws.com',
		'username'	=> '',
		'password'	=> '',
		'sender'	=> 'no-reply@bribooks.info',
		'webhook'	=> true,
	],
	'ses1' => [
		'host'		=> 'email-smtp.us-east-1.amazonaws.com',
		'username'	=> '',
		'password'	=> '',
		'sender'	=> 'no-reply@bribooks.shop',
		'webhook'	=> false,
	],
	'zoho'=> [
		'host'		=> 'smtp.zeptomail.in',
		'username'	=> '',
		'password'	=> '',
		'sender'	=> 'no-reply@bribooks.info',
		'webhook'	=> false,
	]
]);
