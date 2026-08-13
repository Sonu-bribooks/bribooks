<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('format_date')) {
	function format_date($date_string = '', $timezone = '', $format = 'M j, Y h:i A') {
		if (empty($date_string)) return;

		if (!empty($timezone) && $timezone != -330) {
			$timezone_offset = 0;
			$timezone_offset -= 330;
			$timezone_offset -= $timezone;

			return date($format, strtotime(sprintf('%s minutes', $timezone_offset), strtotime($date_string)));
		}

		return date($format, strtotime($date_string));
	}
}

if (!function_exists('log_kb')) {
	function log_kb($data = NULL) {
		$CI	=&	get_instance();

		$class 	= get_class($CI);
		$method = $CI->router->fetch_method();

		$print_data[$class . '::' . $method] = $data;

		// return log_message('KB', ENV_API ? json_encode($print_data) : print_r($print_data, 1));
		return log_message('KB', json_encode($print_data));

	}
}

if (!function_exists('log_kb_imp')) {
	function log_kb_imp($data = NULL) {
		return log_message('error', json_encode($data));
	}
}

if (!function_exists('get_bb_user_id')) {
	function get_bb_user_id($data = NULL) {
		$CI	=&	get_instance();
		return $CI->input->cookie('bb_user_id', TRUE);
	}
}

if (!function_exists('formatDate')) {
	function formatDate($date_string, $timezone = '', $format = 'M j, Y h:i A') {
		return format_date($date_string, $timezone, $format);
	}
}

if (!function_exists('removeIsdCode')) {
	function removeIsdCode($mobile = '', $country = '') {
		$results = json_decode(file_get_contents(FCPATH . 'assets/csv/countries.json'), true);

		foreach ($results as $key => $item) {
			if (strtolower($item[0]) == strtolower($country) && strpos($mobile, $item[3]) === 0) {
				$mobile = substr($mobile, strlen($item[3]));
				break;
			}
		}

		return $mobile;
	}
}

if (!function_exists('get_user_slug')) {
	function get_user_slug($name, $user_id = 0) {
		$slug = preg_replace(['/[^\w\s]/', '/\s+/'], [' ', '-'], mb_strtolower($name));

		$CI	=&	get_instance();

		if ($result = $CI->db->get_where('users', [
			'slug'	=> $slug
		])->row_array()) {
			if ($result['id'] != $user_id) {
				$slug .= '-' . uniqid();
			}
		}

		return $slug;
	}
}

if (!function_exists('gen_slug')) {
	function gen_slug($name) {
		$slug = preg_replace(['/[^\w\s]/', '/\s+/'], [' ', '-'], mb_strtolower($name));

		return $slug;
	}
}

if (!function_exists('get_blog_slug')) {
	function get_blog_slug($name, $blog_id = 0) {
		$slug = preg_replace(['/[^\w\s]/', '/\s+/'], [' ', '-'], mb_strtolower($name));

		$CI	=&	get_instance();

		if ($result = $CI->db->get_where('blog', [
			'slug'	=> $slug
		])->row_array()) {
			if ($result['id'] != $blog_id) {
				$slug .= '-' . uniqid();
			}
		}

		return $slug;
	}
}

if (!function_exists('random_code')) {
	function random_code($length = 6) {
		$random_string = '';

		for ($i = 0; $i < $length; $i++) {
			$number = random_int(0, 36);
			$character = base_convert($number, 10, 36);
			$random_string .= $character;
		}

		return $random_string;
	}
}

if (!function_exists('_get_country_code_by_name')) {
	function _get_country_code_by_name($name = '') {
		$CI	=&	get_instance();

		if ($result = $CI->db->get_where('country', [
			'name'	=> $name
		])->row_array()) {
			return $result['code'];
		}

		return '';
	}
}

if (!function_exists('http_parse_headers')) {
	function http_parse_cookie($cookie = '') {
		$csplit 	= explode(';', $cookie);
		$cookies 	= [];

		foreach( $csplit as $data ) {
			$cinfo = explode('=', $data);
			$cinfo[0] = trim( $cinfo[0] );

			if (mb_strtolower($cinfo[0]) == 'expires') $cinfo[1] = strtotime($cinfo[1]);
			if (mb_strtolower($cinfo[0]) == 'secure') $cinfo[1] = "true";

			if (in_array($cinfo[0], ['domain', 'expires', 'path', 'secure', 'comment'])) {
				$cookies[$cinfo[0]] = $cinfo[1] ?? '';
			} else {
				$cookies[$cinfo[0]] = $cinfo[1] ?? '';
			}
		}

		return $cookies;
	}
}
if (!function_exists('http_parse_headers')) {
	function http_parse_headers($raw_headers) {
		$headers = array();
		$key = '';

		foreach (explode("\n", $raw_headers) as $i => $h) {
			$h = explode(':', $h, 2);

			if (isset($h[1])) {
				if (!isset($headers[$h[0]])) {
					$headers[$h[0]] = trim($h[1]);
				} elseif (is_array($headers[$h[0]])) {
					$headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
				} else {
					$headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
				}

				$key = $h[0];
			} else {
				if (substr($h[0], 0, 1) == "\t") {
					$headers[$key] .= "\r\n\t".trim($h[0]);
				} elseif (!$key) {
					$headers[0] = trim($h[0]);
				}
			}
		}

		return $headers;
	}
}

if (!function_exists('readable_format')) {
	function readable_format($num) {
		if ($num > 1000) {
			$x = round($num);
			$x_number_format = number_format($x);
			$x_array = explode(',', $x_number_format);
			$x_parts = array('k', 'm', 'b', 't');
			$x_count_parts = count($x_array) - 1;
			$x_display = $x;
			$x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
			$x_display .= $x_parts[$x_count_parts - 1];

			return $x_display;
		}
		return $num;
	}
}

if (!function_exists('pr')) {
	function pr($data, $status = false) {
		echo '</pre>';
		print_r($data);

		if ($status)
			exit();
	}
}

if (!function_exists('get_settings')) {
	function get_settings($key = '') {
		$CI	=&	get_instance();

		$CI->db->where('key', $key);

		$result = $CI->db->get('settings')->row();
		return (!empty($result))?$result->value:'';
	}
}

if (!function_exists('get_site')) {
	function get_site($id = 0, $key = '') {
		$CI	=&	get_instance();

		$CI->db->where('id', $id);
		return $key ? $CI->db->get('site')->row()->{$key} ?? '' : $CI->db->get('site')->row();
	}
}

if (!function_exists('timezonechoice')) {
	function timezonechoice($selectedzone = '') {
		$all = timezone_identifiers_list();

		$i = 0;

		foreach ($all AS $zone) {
			$zone = explode('/',$zone);
			$zonen[$i]['continent'] = isset($zone[0]) ? $zone[0] : '';
			$zonen[$i]['city'] = isset($zone[1]) ? $zone[1] : '';
			$zonen[$i]['subcity'] = isset($zone[2]) ? $zone[2] : '';
			$i++;
		}

		asort($zonen);
		$structure = '';

		foreach ($zonen AS $zone) {
			extract($zone);

			if ($continent == 'Africa' || $continent == 'America' || $continent == 'Antarctica' || $continent == 'Arctic' || $continent == 'Asia' || $continent == 'Atlantic' || $continent == 'Australia' || $continent == 'Europe' || $continent == 'Indian' || $continent == 'Pacific') {
				if (!isset($selectcontinent)) {
					$structure .= '<optgroup label="'.$continent.'">'; // continent
				} elseif ($selectcontinent != $continent) {
					$structure .= '</optgroup><optgroup label="'.$continent.'">'; // continent
				}

				if (isset($city) != '') {
					if (!empty($subcity) != '') {
						$city = $city . '/'. $subcity;
					}

					$structure .= "<option ".((($continent.'/'.$city)==$selectedzone)?'selected="selected "':'')." value=\"".($continent.'/'.$city)."\">".str_replace('_',' ',$city)."</option>"; //Timezone
				} else {
					if (!empty($subcity) != '') {
						$city = $city . '/'. $subcity;
					}

					$structure .= "<option ".(($continent==$selectedzone)?'selected="selected "':'')." value=\"".$continent."\">".$continent."</option>"; //Timezone
				}

				$selectcontinent = $continent;
			}
		}

		$structure .= '</optgroup>';

		return $structure;
	}
}

if (!function_exists('get_frontend_settings')) {
	function get_frontend_settings($key = '') {
		$CI	=&	get_instance();

		$CI->db->where('key', $key);
		$result = $CI->db->get('frontend_settings')->row()->value;
		return $result;
	}
}

if (!function_exists('slugify')) {
	function slugify($text) {
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text);
		$text = trim($text, '-');
		$text = strtolower($text);
		$text = preg_replace('~[^-\w]+~', '', $text);
		if (empty($text))
				return 'n-a';
		return $text;
	}
}

if (!function_exists('get_video_extension')) {
	// Checks if a video is youtube, vimeo or any other
	function get_video_extension($url) {
		if (strpos($url, '.mp4') > 0) {
			return 'mp4';
		} elseif (strpos($url, '.webm') > 0) {
			return 'webm';
		} else {
			return 'unknown';
		}
	}
}

if (!function_exists('ellipsis')) {
	// Checks if a video is youtube, vimeo or any other
	function ellipsis($long_string, $max_character = 30) {
		$short_string = strlen($long_string) > $max_character ? substr($long_string, 0, $max_character)."..." : $long_string;
		return $short_string;
	}
}

if (!function_exists('_ls')) {
	function _ls($index) {
		$statuses = [_l('lead'), _l('demo_scheduled'), _l('demo_completed'), _l('demo_not_completed'), _l('enrolled'),_l('rejected')];
		$badges = ['dark', 'info', 'warning', 'danger', 'success','danger'];

		return sprintf('<span class="badge badge-%s">%s</span>', ($badges[$index] ?? ''), ($statuses[$index] ?? ''));
	}
}

if (!function_exists('_mv')) {
	function _mv($index, $type = '') {
		$statuses = [_l('not_verified'), _l('verified')];
		$badges = ['danger', 'success'];

		return sprintf('<span class="badge badge-%s">%s</span>', ($badges[$index] ?? ''), ($type . ' ' . ($statuses[$index] ?? '')));
	}
}

if (!function_exists('_cs')) {
	function _cs($index) {
		$statuses = [_l('not_active'), _l('active')];
		$badges = ['danger', 'success'];

		return sprintf('<span class="badge badge-%s">%s</span>', ($badges[$index] ?? ''), ($statuses[$index] ?? ''));
	}
}

if (!function_exists('_es')) {
	function _es($index) {
		$statuses = [_l('not_active'), _l('active')];
		$badges = ['danger', 'success'];

		return sprintf('<span class="badge badge-%s">%s</span>', ($badges[$index] ?? ''), ($statuses[$index] ?? ''));
	}
}

if (!function_exists('_lv')) {
	function _lv($index) {
		$statuses = [_l('not_verified'), _l('verified')];
		$badges = ['dark', 'success'];

		return sprintf('<span class="badge badge-%s">%s</span>', ($badges[$index] ?? ''), ($statuses[$index] ?? ''));
	}
}

if (!function_exists('_earning_status')) {
	function _earning_status($index) {
		$statuses = [
			0 => _l('pending_delivery'),
			1 => _l('available_for_transfer'),
			2 => _l('processing'),
			3 => _l('order_cancelled'),
		];

		return $statuses[$index];
	}
}

if (!function_exists('_redeem_status')) {
	function _redeem_status($index) {
		$statuses = [
			0 => _l('pending'),
			1 => _l('paid'),
			2 => _l('processing'),
			3 => _l('order_cancelled'),
		];

		return $statuses[$index];
	}
}

if (!function_exists('_request_status')) {
	function _request_status($index) {
		$statuses = [
			0 => _l('requested'),
			1 => _l('paid'),
			2 => _l('processing'),
			3 => _l('order_cancelled'),
		];

		return $statuses[$index];
	}
}

if (!function_exists('_transfer_type')) {
	function _transfer_type($index, $type = 0) {

		if (!empty($type) && $type == 3) {
			$statuses = [
				0 => _li('Amazon_Voucher_Added'),
				1 => _li('Donated_to_Edesia'),
				2 => _li('Donated_to_Sunshine_Foundation'),
			];
		} else {
			$statuses = [
				0 => _li('Transfer_to_Self'),
				1 => _li('Donated_to_Edesia'),
				2 => _li('Donated_to_Sunshine_Foundation'),
			];
		}

		return $statuses[$index];
	}
}

if (!function_exists('_aes')) {
	function _aes($index) {
		$statuses = [
			0 => _l('pending'),
			1 => _l('paid'),
			2 => _l('processing'),
			3 => _l('cancelled'),
		];

		$badges = ['info', 'success', 'warning', 'danger'];

		return sprintf('<span class="badge badge-%s">%s</span>', ($badges[$index] ?? ''), ($statuses[$index] ?? ''));
	}
}

if (!function_exists('_et')) {
	function _et($index) {
		$index = strtolower($index);
		$badges = ['free' => 'danger', 'base' => 'info', 'premium' => 'success'];

		return sprintf('<span class="badge badge-%s">%s</span>', ($badges[$index] ?? ''), $index);
	}
}

if (!function_exists('_sd')) {
	function _sd($status) {
		if ($status == 1) {
			return sprintf('<i class="mdi mdi-circle" style="color: #4CAF50; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="%s"></i>', _l('enabled'));
		} elseif ($status == 2) {
			return sprintf('<i class="mdi mdi-circle" style="color: #a318bd; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="%s"></i>', _l('disabled'));
		} else {
			return sprintf('<i class="mdi mdi-circle" style="color: #FFC107; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="%s"></i>', _l('disabled'));
		}
	}
}

if (!function_exists('_ad')) {
	function _ad($status, $color = '#FFC107') {
		if ($status == 1) {
			return sprintf('<i class="mdi mdi-circle" style="color: #4CAF50; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="%s"></i>', _l('approved'));
		} elseif ($status == 2) {
			return sprintf('<i class="mdi mdi-circle" style="color: red; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="%s"></i>', _l('rejected'));
		} else {
			return sprintf('<i class="mdi mdi-circle" style="color: %s; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="%s"></i>', $color, _l('pending'));
		}
	}
}

if (!function_exists('_ds')) {
	function _ds($status) {
		return $status ? '<span class="badge badge-info">' . _l('demo') . '</span>' : '';
	}
}

if (!function_exists('_gc')) {
	function _gc($status) {
		return $status ? '<span class="badge badge-danger">' . _l('group_class') . '</span>' : '';
	}
}

if (!function_exists('_ec')) {
	function _ec($courses) {
		$enrolled = [];

		foreach ($courses as $course) {
			$enrolled[] = sprintf('<li>%s</li>', $course['course']);
		}

		return sprintf('<ul>%s</ul>', implode("\n", $enrolled));
	}
}

if (!function_exists('_ui')) {
	function _ui($image) {
		return sprintf('<img src="%s" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail" style="max-height:50px">', $image);
	}
}

if (!function_exists('_render_column')) {
	function _render_column($data) {
		$columns = [];

		foreach ($data as $k => $v) {
			if ($k === 'actions') {
				$actions = [];

				foreach ($v as $action) {
					if (
						!empty($action['type'])
						&& $action['type'] === 'confirm'
					) {
						$actions[] = sprintf('
						<li>
							<a
								class="dropdown-item"
								data-action="%s"
								href="#"
								onclick="confirm_modal(\'%s${data.id}\');"
							>%s</a>
						</li>
						', $action['key'], base_url($action['url']), _l($action['key']));
					} elseif (
						!empty($action['type'])
						&& $action['type'] === 'confirm_ajax'
					) {
						$actions[] = sprintf('
						<li>
							<a
								class="dropdown-item"
								data-action="%s"
								href="#"
								onclick="confirm_modal(\'%s${data.id}\', true);"
							>%s</a>
						</li>
						', $action['key'], base_url($action['url']), _l($action['key']));
					} elseif (
						!empty($action['type'])
						&& $action['type'] === 'comment_box'
					) {
						$actions[] = sprintf('
							<li>
								<a
									class="dropdown-item"
									data-action="%s"
									href="#"
									onclick="comment_modal({
										title: \'%s\',
										id: ${data.id},
										url: \'%s${data.id}\'
									});"
								>%s</a>
							</li>
							',
							$action['key'],
							$action['title'],
							base_url($action['url']),
							_l($action['key']));
					} elseif (
						!empty($action['type'])
						&& $action['type'] === 'status'
					) {
						$actions[] = sprintf('
						<li>
							<a
								class="dropdown-item"
								data-action="%s"
								href="#"
								onclick="confirm_modal(\'%s${data.id}\');"
							>${status}</a>
						</li>
						', $action['key'], base_url($action['url']));
					} elseif (
						!empty($action['type'])
						&& ($action['type'] === 'modal' || $action['type'] === 'callback')
					) {
						$actions[] = sprintf('
						<li>
							<a
								class="dropdown-item"
								data-action="%s"
								data-toggle="modal"
								data-target="#%s"
								href="#"
								onclick="%s(${data.id})"
							>%s</a>
						</li>
						', $action['key'], $action['url'], $action['callback'], _l($action['key']));
					} else {
						$actions[] = sprintf('
						<li>
							<a
								class="dropdown-item"
								href="%s${data.id}"
							>%s</a>
						</li>', base_url($action['url']), _l($action['key']));
					}
				}

				$columns[] = [
					'data'		=> 'actions',
					'render'	=> sprintf('(function(data, type) {
						if (type === \'display\') {
							let status = parseInt(data.status) === 1 ? "%s" : "%s"
							return `
							<div class="dropright dropright">
								<button
									type="button"
									class="btn btn-sm btn-outline-primary btn-rounded btn-icon"
									data-toggle="dropdown"
									aria-haspopup="true"
									aria-expanded="false"
								>
									<i class="mdi mdi-dots-vertical"></i>
								</button>

								<ul class="dropdown-menu">
									%s
								</ul>
							</div>`
						}

						return data;
					})', _l('mark_inactive'), _l('mark_active'), implode("\n", $actions))
				];
			} else {
				foreach ($v as $c) {
					if ($c == 'thumb') {
						$columns[] = [
							'data'		=> $c,
							'render'	=> '(function(data, type) {
								if (type === \'display\') {
									return `<img src="${data}" class="img-thumbnail" />`;
								}

								return data;
							})',
						];
					} else {
						$columns[] = [
							'data'	=> $c
						];
					}
				}
			}
		}

		return base64_encode(json_encode($columns));
	}
}

if (!function_exists('_ce')) {
	function _ce($schedule) {
		if (empty($schedule['schedule'])) return;

		if ($schedule['schedule'] < date('Y-m-d H:i:s')) {
			return '<span class="badge badge-danger">' . _l('expired on ') . formatDate($schedule['schedule']) . '</span>';
		} elseif (strtotime($schedule['schedule'] . ' -1 week') < time()) {
			return '<span class="badge badge-info">' . _l('expiring_soon ') . formatDate($schedule['schedule']) . '</span>';
		}
	}
}

if (!function_exists('_qt')) {
	function _qt($name, $image) {
		if (is_file($image)) {
			return sprintf('%s<br><i class="mdi mdi-file-image" style="font-size:30px;"></i>', $name);
		}
		return $name;
	}
}

if (!function_exists('_unr')) {
	function _unr($index) {
		$statuses = [2 => _l('student'), 3 => _l('teacher')];
		$badges = [2 => 'info', 3 => 'warning'];

		return sprintf('<br><span class="badge badge-%s">%s</span>', ($badges[$index] ?? ''), ($statuses[$index] ?? ''));
	}
}

if (!function_exists('_marketing_type')) {
	function _marketing_type($index) {
		$types = [1 => _l('whatsapp'), 2 => _l('email')];
		$badges = [1 => 'info', 2 => 'primary'];

		return sprintf('<br><span class="badge badge-%s">%s</span>', ($badges[$index] ?? ''), ($types[$index] ?? ''));
	}
}

if (!function_exists('_attachment_type')) {
	function _attachment_type($index) {
		$types = [0 => _l('none'), 1 => _l('image'), 2 => _l('document'), 3 => _l('video')];
		$badges = [0 => 'dark', 1 => 'warning', 2 => 'danger', 3	=> 'info'];

		return sprintf('<br><span class="badge badge-%s">%s</span>', ($badges[$index] ?? ''), ($types[$index] ?? ''));
	}
}

if (!function_exists('output_json')) {
	function output_json($json = []) {
		$CI	=&	get_instance();

		$CI->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}

if (!function_exists('_color_palette')) {
	function _color_palette($image_file, $num_colors, $granularity = 5)  {
		$granularity = max(1, abs((int)$granularity));
		$colors = array();
		$size = @getimagesize($image_file);

		if ($size === false) {
			return false;
		}

		$img = @imagecreatefromstring(file_get_contents($image_file));

		if (!$img) {
			return false;
		}

		for ($x = 0; $x < $size[0]; $x += $granularity) {
			for ($y = 0; $y < $size[1]; $y += $granularity) {
				$thisColor = imagecolorat($img, $x, $y);
				$rgb = imagecolorsforindex($img, $thisColor);
				$red = round(round(($rgb['red'] / 0x33)) * 0x33);
				$green = round(round(($rgb['green'] / 0x33)) * 0x33);
				$blue = round(round(($rgb['blue'] / 0x33)) * 0x33);
				$thisRGB = sprintf('%02X%02X%02X', $red, $green, $blue);

				if (array_key_exists($thisRGB, $colors)) {
					$colors[$thisRGB]++;
				} else {
					$colors[$thisRGB] = 1;
				}
			}
		}

		arsort($colors);

		return array_slice(array_keys($colors), 0, $num_colors);
	}
}

if (!function_exists('_remove_special_charcater')) {
	function _remove_special_charcater($str) {
		if (empty($str)) return false;

		return str_ireplace(array('\'','"',',',';','<','>','$','&','.','-','*','(', ')','#','%','!','@','×','’','–','·','—','‘','“','”'), '', $str);
	}
}

if (!function_exists('_curl')) {
	function _curl($url, $payload, $method = 'POST', $headers = [], $type = 'json', $timeout = 30) {
		log_kb([
			'Curl::' => [
				'url'		=> $url,
				// 'payload'	=> $payload,
				'method'	=> $method,
				'headers'	=> $headers,
				'type'		=> $type,
			]
		]);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

		if ($method === 'POST') {
			if ($type === 'json') {
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
			} else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
				curl_setopt($ch, CURLOPT_POST, true);
			}
		}

		if ($type == 'json') {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['Content-Type:application/json'], $headers));
		} else {
			!empty($headers) && curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);

		return json_decode($result, true);
	}
}

if (!function_exists('masked_email')) {
	function masked_email($email) {
		if (empty($email)) return false;

		$em   = explode('@', $email);
		$name = implode('@', array_slice($em, 0, count($em)-1));

		if (strlen($name) < 5) {
			return str_repeat('*', strlen($name)) . '@' . end($em);
		} else {
			return substr($name, 0, 2) . str_repeat('*', (strlen($name) - 4)) . substr($name, -2) . '@' . end($em);
		}
	}
}

if (!function_exists('masked_mobile')) {
	function masked_mobile($num) {
		if (empty($num)) return false;

		return substr($num, 0, 4) . str_repeat('*', (strlen($num) - 6)) . substr($num, -2);
	}
}

if (!function_exists('_generate_qr_code')) {
	function _generate_qr_code($code = '', $dir_path = '', $event_id = '') {
		if (empty($event_id))
			return;

		if (file_exists($dir_path . '/qrcode_' . $code . '.png'))
			return base_url() . $dir_path . '/qrcode_' . $code . '.png';

		$dir = FCPATH . $dir_path;

		if (!is_dir($dir)) {
			mkdir($dir, 0777, TRUE);
			chmod($dir, 0777);
			@touch($dir . '/' . 'index.html');
		}

		$file = $dir_path . '/qrcode_' . $code . '.png';

		$logo = imagecreatefrompng(FCPATH . 'assets/images/logo.png');
		$logo_width = imagesx($logo);
		$logo_height = imagesy($logo);

		$url = '';
		switch ($event_id) {
			case '9':
				$url = USER_YAF_URL . 'us/studentv2/' . $code;
				break;

			case '10':
				$url = USER_YAF_URL . 'india/student/' . $code;
				break;

			case '11':
				$url = 'https://www.bwf.bribooks.com/student/' . $code;
				break;

			default:
				break;
		}

		$qr_img = imagecreatefrompng(vsprintf('https://chart.googleapis.com/chart?cht=qr&chld=H|0&chs=512x512&chl=%s', [
			urlencode($url),
		]));

		$qr_img_width = imagesx($qr_img);
		$qr_img_height = imagesy($qr_img);

		imagecopyresampled(
			$qr_img,
			$logo,
			($qr_img_width / 2 - 150),
			($qr_img_height / 2 - 150),
			0,
			0,
			300,
			300,
			$logo_width,
			$logo_height
		);

		imagepng($qr_img, $file);

		return base_url($file);
	}
}

if (!function_exists('_get_country_state')) {
	function _get_country_state($country = '') {
		if (empty($country))
			return;

		if (!empty($country_state = file_get_contents(base_url('assets/csv/country_state.json')))) {
			$country_state = json_decode($country_state, 1);

			$key = array_search($country, array_column($country_state, 'name'));

			if ($key != '' && !empty($country_state[$key]['states'])) {
				$states = $country_state[$key]['states'];
			}
		}

		return $states ?? [];
	}
}

if (!function_exists('generateQrCode')) {
	function generateQrCode($data = null, $size = 20, $logo_size = 2, $file = 'uploads/test/testqr.png') {
		if (empty($data)) return;

		if (empty($file)) {
			$file = 'uploads/test/testqr.png';
		}

		if ($file = 'uploads/test/testqr.png') {
			$file = str_replace('.', sprintf('_%s_qrcode.', uniqid()), $file);
		}

		$qr_code_file = str_replace('.', sprintf('_%s_temp.', uniqid()), $file);

		$CI	=&	get_instance();

		$CI->load->library('ciqrcode');

		$params['data'] 	= $data;
		$params['level'] 	= 'H';
		$params['size'] 	= $size;
		$params['savename'] = FCPATH . $qr_code_file;

		$CI->ciqrcode->generate($params);

		$logo = imagecreatefrompng(FCPATH . 'assets/images/bblogo.png');
		$logo_width = imagesx($logo);
		$logo_height = imagesy($logo);

		$qr_img = imagecreatefrompng(FCPATH . $qr_code_file);
		$qr_img_width = imagesx($qr_img);
		$qr_img_height = imagesy($qr_img);

		// imagecopyresampled(
		// 	$qr_img,
		// 	$logo,
		// 	($qr_img_width / 2 - $logo_width / ($logo_size * 2)),
		// 	($qr_img_height / 2 - $logo_height / ($logo_size * 2)),
		// 	0,
		// 	0,
		// 	$logo_width / $logo_size,
		// 	$logo_height / $logo_size,
		// 	$logo_width,
		// 	$logo_height
		// );
		log_kb([
			'qr_img_width' => $qr_img_width,
			'qr_img_height' => $qr_img_height,
			'logo_width' => $logo_width,
			'logo_height' => $logo_height,
			'logo_size' => $logo_size,
			'dst_x' => $qr_img_width / 2 - $logo_width / ($logo_size * 2),
			'dst_y' => $qr_img_height / 2 - $logo_height / ($logo_size * 2),
			'dst_width' => $logo_width / $logo_size,
			'dst_height' => $logo_height / $logo_size,
		]);

		imagecopyresampled(
			$qr_img,
			$logo,
			(int)round($qr_img_width / 2 - $logo_width / ($logo_size * 2)),
			(int)round($qr_img_height / 2 - $logo_height / ($logo_size * 2)),
			0,
			0,
			(int)round($logo_width / $logo_size),
			(int)round($logo_height / $logo_size),
			(int)round($logo_width),
			(int)round($logo_height)
		);
		imagepng($qr_img, $file);

		is_file($qr_code_file) && unlink($qr_code_file);

		return $file;
	}
}

if (!function_exists('_p_a_code')) {
	function _p_a_code($option_type = '1') {
		return vsprintf('%s', [
			($option_type == '2') ? 'B' : ''
		]);
	}
}

if (!function_exists('_getCopyText')) {
	function _getCopyText($sold = '1') {
		if ($sold > 1) {
			return _l('Copies');
		}

		return _l('Copy');
	}
}

if (!function_exists('_get_country_by_code')) {
	function _get_country_by_code($code = '') {
		$CI	=&	get_instance();

		if ($result = $CI->db->get_where('country', [
			'code'		=> trim(strtoupper($code)),
			'_deleted'	=> 0
		])->row_array()) {
			return $result;
		}

		return '';
	}
}

if (!function_exists('_allowSpecificHtmlTags')) {
	function _allowSpecificHtmlTags($str = '') {
		$allowed_tags = '<p><a><b><i><u><strong><em><img><br><hr><ol><ul><li><h1><h2><h3><h4><h5><h6><style><div>';

		return strip_tags($str, $allowed_tags);
	}
}

if (!function_exists('_getCopyTextLabel')) {
	function _getCopyTextLabel($quantity = '') {
		if ($quantity > 1) {
			return _l('Copies');
		}

		return _l('Copy');
	}
}

if (!function_exists('array_find')) {
	function array_find(array $array, callable $callback) {
		foreach ($array as $key => $value) {
			if ($callback($value, $key)) {
				return $value;
			}
		}

		return null;
	}
}

if (!function_exists('format_gallery_url')) {
	function format_gallery_url($path = '', $width = '', $height = '') {
		$CI	=&	get_instance();
		$url = $CI->config->item('cloudfront_url');

		if (strpos($path, $CI->config->item('s3_user_gallery')) === false) {
			$url .= $CI->config->item('s3_user_gallery');
		}

		$url .= $path;
		$url_parts = [];

		if (!empty($width)) {
			$url_parts[] = 'width=' . (int)$width;
		}

		if (!empty($height)) {
			$url_parts[] = 'height=' . (int)$height;
		}

		if (!empty($url_parts)) {
			$url .= '?' . implode('&', $url_parts);
		}

		return $url;
	}
}

if (!function_exists('format_message_with_data')) {
	function format_message_with_data($message = '', $data = []) {
		$find  = array_map(fn($item) => sprintf('{%s}', $item), array_keys($data));
		return str_replace($find, $data, $message);
	}
}

if (!function_exists('_saveCsv')) {
	function _saveCsv($results = [], $file_name = ''){
		$path = 'uploads/csv/reports/';
		$dir_path = rtrim(FCPATH, '/') . '/' . trim($path, '/') . '/';

		if (!is_dir($dir_path)) {
			mkdir($dir_path, 0777, true);
		}

		$file_name = !empty($file_name) ? ($file_name . '.csv') : (date('Y_m_d_H_i_s') . '.csv');

		$full_path = $dir_path . $file_name;

		$headers = array_keys($results[0]);
		$fp = fopen($full_path, 'w');

		if (!$fp) {
			log_kb([
				'Failed to open file: ' => $full_path,
			]);
			return false;
		}

		fputs($fp, "\xEF\xBB\xBF");
		fputcsv($fp, $headers);

		foreach ($results as $data) {
			fputcsv($fp, $data);
		}

		fclose($fp);

		return $full_path;
	}
}

if (!function_exists('format_whatsapp_sms_message')) {
	function format_whatsapp_sms_message($message, $data = []) {
		preg_match_all('/\{(.+?)\}/ims', $message, $output);
		$message_data = [];

		foreach ($output[1] ?? [] as $key) {
			$value = isset($data[$key]) ? $data[$key] : $key;

			$message_data[] = (string)$value;
		}

		return $message_data;
	}
}

if (!function_exists('validate_user_subscription')) {
	function validate_user_subscription($user_id = 0) {
		$CI	=&	get_instance();

		if (empty($user_id)) {
			$user_id = $CI->session->userdata('user_id');
		}

		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('subscription/UserSubscription_model', 'user_subscription_model');

		if ($user_id &&
			($user_info = $CI->user_model->get($user_id)) &&
			($user_subscription_info = $CI->user_subscription_model->get_all([
				'user_id'				=> $user_info['id'],
				'subscription_plan_id'	=> $user_info['subscription_plan_id'],
				'status'				=> 1,
			])['rows'][0] ?? []) &&
			strtotime($user_subscription_info['end_date']) > time()
		) {
			return true;
		}

		return false;
	}
}

if (!function_exists('encrypt_data')) {
	function encrypt_data($data = '') {
		try {
			$CI	=&	get_instance();
			$secret_key = $CI->config->item('bb_secret_jwt_token');

			$date = new DateTime();

			$payload['data'] 	= $data;
			$payload['iat']		= $date->getTimestamp();
			$payload['exp'] 	= $date->getTimestamp() + 30 * 24 * 3600;

			if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
				return \Firebase\JWT\JWT::encode($payload, $secret_key, 'HS256');
			} else {
				return \Firebase\JWT\JWT::encode($payload, $secret_key);
			}
		} catch (Exception $e) {
			log_kb_imp($e->getMessage());
		}
	}
}

if (!function_exists('decrypt_data')) {
	function decrypt_data($data = '') {
		try {
			$CI	=&	get_instance();
			$secret_key = $CI->config->item('bb_secret_jwt_token');

			if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
				$decoded = (array)\Firebase\JWT\JWT::decode($data, new \Firebase\JWT\Key($secret_key, 'HS256'));
			} else {
				$decoded = (array)\Firebase\JWT\JWT::decode($data, $secret_key, ['HS256']);
			}

			if (!empty($decoded['exp']) && $decoded['exp'] > time() && !empty($decoded['data'])) {
				return $decoded['data'] ?? '';
			}

			return null;
		} catch (Exception $e) {
			log_kb_imp($e->getMessage());
			return null;
		}
	}
}

if (!function_exists('gen_unsubscribe_url')) {
	function gen_unsubscribe_url($email = '', $type = 'user') {
		return vsprintf(USER_URL . 'unsubscribe?code=%s', [
			encrypt_data($type . '|' . $email)
		]);
	}
}

if (!function_exists('render_url')) {
	function render_url($href = '', $name = 'download') {
		return vsprintf('<a href="%s" target="_blank">%s</a>', [
			$href,
			$name
		]);
	}
}

if (!function_exists('gen_cache_key')) {
	function gen_cache_key($key = '') {
		return vsprintf('%s_%s', [
			(ENVIRONMENT === 'production' ? 'live' : 'test'),
			$key,
		]);
	}
}

if (!function_exists('wrap_emoji')) {
	function wrap_emoji($text) {
		return preg_replace(
			'/(
				\x{00A9}|\x{00AE}|[\x{2000}-\x{3300}]|
				[\x{1F000}-\x{1FAFF}]|
				[\x{1F1E6}-\x{1F1FF}]|
				[\x{2600}-\x{27BF}]
			)/ux',
			'<span class="emoji">$0</span>',
			$text
		);
	}
}

if (!function_exists('calculate_age')) {
	function calculate_age($date) {
		if (empty($date) || $date === '0000-00-00') {
			return 0;
		}

		$dob = new DateTime(date('Y-m-d', strtotime($date)));
		$now = new DateTime(date('Y-m-d'));
		return $dob->diff($now)->y;
	}
}

if (!function_exists('error_message')) {
	function error_message($message = '') {
		$CI	=&	get_instance();
		$CI->session->set_flashdata('error_message', $message);
	}
}

if (!function_exists('success_message')) {
	function success_message($message = '') {
		$CI	=&	get_instance();
		$CI->session->set_flashdata('flash_message', $message);
	}
}

if (!function_exists('update_thirdparty_status')) {
	function update_thirdparty_status($key = '', $status = true, $message = '') {
		$CI	=&	get_instance();
		$CI->load->library('Redis_lib');

		$cache_key 	= sprintf('%s_thirdparty_service_%s_status', ENVIRONMENT === 'production' ? 'live' : 'test', $key);
		$existing 	= $CI->redis_lib->get($cache_key);

		if (!empty($existing) && $existing['status'] == $status) return;

		$data = [
			'status' => $status,
			'reason' => $message,
		];

		if (empty($status)) {
			$data['date_down'] 	= time();
		} else {
			$data['date_up'] 	= time();
		}

		$CI->redis_lib->save($cache_key, $data, 14400);
	}
}
