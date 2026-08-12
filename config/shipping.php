<?php defined('BASEPATH') or exit('No direct script access allowed');

$config['shipping_config'] = true;

define('BOOK_WEIGHT', [
	'cover'		=> [
		'hard_cover'	=> 150,
		'paperback'		=> 75,
		'black_white'	=> 75,
		'audio_book'	=> 0,
	],
	'printed'		=> 1.4,
	'black_white'	=> 0.75,
	'audio_book'	=> 0,
	'page'			=> 1.4,
	'packing'		=> 7,
]);

define('SHIPROCKET', [
	'email'		=> 'abhishek@youbooks.co',
	'password'	=> '',
]);

define('HANDLING_COST', [
	'GE' 	=> 50,
	'IN' 	=> 20
]);

define('SHIPPING_FLAT_PRICE', [
	'IL'	=> 6
]);

define('FREE_SHIPPING_QUANTITY', [
	'IL'	=> 10,
]);

define('FREE_SHIPPING_COUNTRY', [
	'MY'	=> 0,
	'SG'	=> 0,
	'AE'	=> 0,
	'US'	=> 0,
]);

define('USD_AMOUNT', 85);

define('GLOBAL_SHIPPING_FLAT_PRICE', 6);

define('GLOBAL_FREE_SHIPPING_QUANTITY', 10);

define('SHIPPING_VENDORS', [
	'aramex'	=> ['GB']
]);

//bluedart api keys
$bluedart = [];
$bluedart['bluedart_area_code'] = 'GGN';
$bluedart['bluedart_api_version'] = '1.10';

$bluedart['bluedart_tracking_api_verno'] = '1.3';
$bluedart['bluedart_tracking_api_licence_key'] = '';

if (ENVIRONMENT == 'production') {
	$bluedart['bluedart_licence_key'] = '';
	$bluedart['bluedart_login_id'] = '';
	$bluedart['bluedart_customer_code'] = '';
	$bluedart['bluedart_api_mode'] = '';
} else {
	$bluedart['bluedart_licence_key'] = 'kh7mnhqkmgegoksipxr0urmqesesseup';
	$bluedart['bluedart_login_id'] = 'GG940111';
	$bluedart['bluedart_customer_code'] = '940111';
	$bluedart['bluedart_api_mode'] = 'demo';
}

//pickup address
define('PICKUP_ADDRESS', []);

define('BLUEDART_API', $bluedart);

define('SHIPMENT_TOTAL_VALUE', [
	'shiprocket'	=> [
		'canada'		=> '20',
		'oman'			=> '10',
		'portugal'		=> '174',
		'thailand'		=> '47',
		'qatar'			=> '1'
	]
]);

define('SHIPMENT_ZONES', [
	'shiprocket'	=> [
		'z_a'	=> 'z1',
		'z_b'	=> 'z2',
		'z_c'	=> 'z3',
		'z_d'	=> 'z4',
		'z_e'	=> 'z5'
	]
]);

define('SHIPMENT_GST_PERCENT', 18);

define('BLOCKED_COURIERS', [
	'Smartr Logistics Air',
]);

define('DTDC_COURIERS', [
	'Dtdc Priority Express Air',
	'Dtdc Smart Express Surface'
]);

define('BLUEDART_COURIERS', [
	'Etail Air (Air Product)',
	'Dart Plus (Surface Product)'
]);

define('BANNED_DELIVERY', [
	'QA',
	'EC',
	'PK',
	'JO',
	'OM',
	'NG',
]);

$config['default_pickup_location_id'] = 1;
$config['pickup_zipcode']  = '122004';

define('ORDER_STATUS', [
	'in_complete'				=> 0,
	'new'						=> 1,
	'in_print'					=> 2,
	'printed'					=> 8,
	'available_for_shipping'	=> 21,
	'ready_to_ship'				=> 9,
	'shipped'					=> 3,
	'out_for_delivery'			=> 19,
	'delivered'					=> 4,
	'undelivered'				=> 20,
	'returned'					=> 15,
	'reprint'					=> 10,
	'cancelled'					=> 91,
	'refunded'					=> 92,
	'escalated'					=> 93,
	'clone'						=> 94,
]);

define('ORDER_STATUS_COLOR', [
	0 => 'dark',
	1 => 'warning',
	2 => 'info',
	3 => 'primary',
	4 => 'success',
	8 => 'dark',
	9 => 'secondary',
	10 => 'warning',
	15 => 'dark',
	19 => 'warning',
	20 => 'danger',
	21 => 'secondary',
	91 => 'danger',
	92 => 'dark',
	93 => 'primary',
	94 => 'dark',
]);
