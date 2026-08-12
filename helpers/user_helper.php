<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('get_user_department_ids')) {
	function get_user_department_ids($user_id = '') {
		$CI	=&	get_instance();

		return array_map(
			fn($item) => $item['department_id'],
			$CI->db->get_where('department_user', [
				'user_id' 	=> $user_id,
				'_deleted' 	=> 0,
			])->result_array()
		);
	}
}

if (!function_exists('get_user_role')) {
	function get_user_role($type = '', $user_id = '') {
		$CI	=&	get_instance();

		$role_id	=	$CI->db->get_where('users' , array('id' => $user_id))->row()->role_id;
		$user_role	=	$CI->db->get_where('role' , array('id' => $role_id))->row()->name;

		if ($type == 'user_role') {
			return $user_role;
		} else {
			return $role_id;
		}
	}
}

if (!function_exists('get_user_role_by_id')) {
	function get_user_role_by_id($role_id = '') {
		$CI	=&	get_instance();

		return $CI->db->get_where('role' , array('id' => $role_id))->row()->name;
	}
}

if (!function_exists('is_purchased')) {
	function is_purchased($course_id = '') {
		$CI	=&	get_instance();

		if ($CI->session->userdata('user_login')) {
			$enrolled_history = $CI->db->get_where('enrol' , array('user_id' => $CI->session->userdata('user_id'), 'course_id' => $course_id))->num_rows();

			if ($enrolled_history > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

if (!function_exists('load_controller')) {
	function load_controller($controller, $method = null) {
		require_once(APPPATH . 'controllers/' . $controller . '.php');

		if (empty($method)) return;

		$controller = new $controller();
		return $controller->$method();
	}
}
