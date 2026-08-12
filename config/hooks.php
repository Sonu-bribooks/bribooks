<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['post_controller_constructor'][] = [
	'class'		=> 'Config_setting',
	'function' 	=> 'init',
	'filename' 	=> 'Config_setting.php',
	'filepath' 	=> 'hooks',
	'params'   	=> []
];

$hook['post_controller_constructor'][] = [
	'class'    => 'Authentication',
	'function' => 'init',
	'filename' => 'Authentication.php',
	'filepath' => 'hooks',
	'params'   => []
];

$hook['post_system'][] = [
	'class'		=> 'Config_setting',
	'function' 	=> 'reset',
	'filename' 	=> 'Config_setting.php',
	'filepath' 	=> 'hooks',
	'params'   	=> []
];

/*$hook['pre_controller'][] = [
	'class'	   => 'Benchmark_log',
	'function' => 'start',
	'filename' => 'Benchmark_log.php',
	'filepath' => 'hooks',
	'params'   => []
];

$hook['post_controller'][] = [
	'class'	   => 'Benchmark_log',
	'function' => 'stop',
	'filename' => 'Benchmark_log.php',
	'filepath' => 'hooks',
	'params'   => []
];*/

$hook['post_controller'][] = [
	'class'    => 'Db_log',
	'function' => 'logQueries',
	'filename' => 'Db_log.php',
	'filepath' => 'hooks'
];

$hook['post_controller'][] = [
	'class'    => 'Output',
	'function' => 'setOutput',
	'filename' => 'Output.php',
	'filepath' => 'hooks',
	'params'   => []
];
