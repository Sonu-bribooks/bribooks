<?php defined('BASEPATH') or exit('No direct script access allowed');

$config['ic_game_config'] = true;

// Game Config
define('COURSE_GRADE', []);
define('EVENT_URL', '');
define('EVENT_CODE', 'IN');
define('EVENT_ME_START_DATE', '');
define('EVENT_ME_END_DATE', '');
define('WEBINAR_GAME', []);

if (EVENT_CODE === 'IN') {
	define('EVENT_TITLE', '');
	define('EVENT_START_DATE', '');
	define('EVENT_END_DATE', '');
	define('EVENT_2021_TOTAL', 0);
	define('EVENT_2021_CERTIFICATE', []);
	define('EVENT_2021', []);
}

define('EVENT_ME_EMAILS', []);
// Quiz
define('QUIZ_TIME', [
	2 => 30,
	3 => 60,
]);

define('GAME_COOKIE_DOMAIN', '');
define('GAME_URL', '');
define('GAME_URL_NEW', '');
define('ICODE_LEVEL', []);
define('DEMO_TIMES', []);
define('DEMO_AGES', []);
define('API_ORGCODE', '');
define('API_SECRET', '');
define('API_SCHOOL_ID', 0);
define('API_UNO_PREFIX', '');
define('ICODE_SECRET', '');
define('EMI_TYPES', []);
define('EMI_CHARGE', []);
// Game config
