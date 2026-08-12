<?php defined('BASEPATH') or exit('No direct script access allowed');

$config['bb_events_config'] = true;

define('EVENT_BASE_PARENT_SITE_CODES', [
	'in-sc',
	'ge-UAE',
	'readers-can-write-2023',
	'parent-child-2023',
	'in-ivykids-2023',
	'ge-NYAFUS',
	'in-nyaf23',
	'ge-bwf',
	'ge-yabwf-kw-23',
	'ge-NYAFUK'
]);

define('NYAF_SITE_CODE', 'nyafind2022');
define('ISRAEL_SITE_CODE', 'ge-nwfis');
define('KIDO_SITE_CODE', 'ge-kido');
define('SUMMER_CAMP_SITE_CODE', 'in-sc');
define('UAE_SITE_CODE', 'ge-uae');
define('RCW_SITE_CODE', 'readers-can-write-2023');
define('PC_SITE_CODE', 'parent-child-2023');
define('IVY_SITE_CODE', 'in-ivykids-2023');
define('NYAF_US_SITE_CODE', 'ge-nyafus');
define('NYAF_IN_SITE_CODE', 'in-nyaf23');
define('BWF_SITE_CODE', 'ge-bwf');
define('YABWF_SITE_CODE', 'ge-yabwf-kw-23');
define('NYAFUK_SITE_CODE', 'ge-NYAFUK');

define('NYAF_END_DATE', '20230315210000');
define('NWFIS_END_DATE', '20230620120000');
define('SC_END_DATE', '20230731235959');
define('UAE_END_DATE', '20230827210000');
define('RCW_END_DATE', '20231215210000');
define('PC_END_DATE', '20231215210000');
define('IVY_END_DATE', '20231015210000');
define('NYAF_US_END_DATE', '20231225102959');
define('NYAF_IN_END_DATE', '20240218235959');
define('BWF_END_DATE', '20240331235959');
define('YABWF_END_DATE', '20240325235959');
define('NYAFUK_END_DATE', '20240630235959');

define('NWFIS_EVENT_ID', 3);
define('SC_EVENT_ID', 14);
define('UAE_EVENT_ID', 5);
define('RCW_EVENT_ID', 6);
define('PC_EVENT_ID', 7);
define('IVY_EVENT_ID', 8);
define('NYAF_US_EVENT_ID', 9);
define('NYAF_IN_EVENT_ID', 10);
define('BWF_EVENT_ID', 11);
define('YABWF_EVENT_ID', 12);
define('NYAFUK_EVENT_ID', 15);

define('EVENT_TYPE', [
	'nyaf'			=> 'NYAF',
	'summer_camp'	=> 'Summer Camp',
	'ig'			=> 'Intergenerational',
	'stand_alone'	=> 'Stand Alone'
]);

define('EVENT_TEMPLATE', [
	'nyaf'			=> 'NYAF',
	'summer_camp'	=> 'Summer Camp',
	'ig'			=> 'Intergenerational'
]);

define('SUB_EVENT', [
	'best_seller_school'	=> 'Best Seller in the School',
	'best_seller_city'		=> 'Best Seller in the City',
	'best_seller_state'		=> 'Best Seller in the State',
	'best_seller_national'	=> 'National Best Seller',
]);

define('EVENT_GRADES_NAME', [
	'11'			=> [
		'1'		=> 'Primary Years Programme (PYP)',
		'2'		=> 'Middle Years Programme (MYP)',
		'3'		=> 'Diploma Programme (DP)',
		'4'		=> 'Career-related Programme (CP)'
	]
]);

define('CHALLENGE_TYPES', [
	[
		'value'	=> 'daily',
		'label'	=> 'daily',
	],
	[
		'value'	=> 'weekly',
		'label'	=> 'weekly',
	],
	[
		'value'	=> 'genre',
		'label'	=> 'genre',
	],
	[
		'value'	=> 'general',
		'label'	=> 'general',
	],
	[
		'value'	=> 'school',
		'label'	=> 'school',
	],
	[
		'value'	=> 'city',
		'label'	=> 'city',
	],
	[
		'value'	=> 'state',
		'label'	=> 'state',
	],
	[
		'value'	=> 'country',
		'label'	=> 'country',
	],
	[
		'value'	=> 'group',
		'label'	=> 'group',
	],
]);
