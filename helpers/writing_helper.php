<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (! function_exists('parse_textboxes')) {
	function parse_textboxes(&$data = []) {
		foreach ($data as $key => &$value) {
			$value['p'] = [
				'h' => (int)$value['p']['h'],
				'l' => (int)($value['p']['l'] ?? 0),
				'w' => (int)$value['p']['w'],
				'x' => (int)$value['p']['x'],
				'y' => (int)$value['p']['y'],
			];
		}
	}
}

if (! function_exists('parse_cover_style')) {
	function parse_cover_style($data = []) {
		return [
			'limit'	=> (int)($data['limit'] ?? 400),
			'style'	=> [
				'color'		=> $data['style']['color'] ?? '#000000',
				'fontFamily'=> $data['style']['fontFamily'] ?? 'Signika',
				'fontSize'	=> (int)($data['style']['fontSize'] ?? 14),
				'left'		=> (int)($data['style']['left'] ?? 0),
				'right'		=> (int)($data['style']['right'] ?? 0),
				'textAlign'	=> $data['style']['textAlign'] ?? 'center',
				'top'		=> (int)($data['style']['top'] ?? 0),
			],
		];
	}
}

if (! function_exists('_has_not_allowed_chars')) {
	function _has_not_allowed_chars($texts = []) {
		foreach ($texts as $text) {
			if (_has_not_allowed_chars_single($text)) {
				return true;
				break;
			}
		}

		return false;
	}
}

if (! function_exists('_has_not_allowed_chars_single')) {
	function _has_not_allowed_chars_single($text = '') {
		$CI	=&	get_instance();

		$regex = [];

		$text = htmlentities($text, ENT_QUOTES, 'UTF-8');

		if ($CI->input->cookie('user_language') === 'es') {
			$regex[] = 'áÁéÉíÍñÑóÓúÚüÜ¿¡«»&aacute;&Aacute;&eacute;&Eacute;&iacute;&Iacute;&ntilde;&Ntilde;&oacute;&Oacute;&uacute;&Uacute;&uuml;&Uuml;&iquest;&iexcl;&laquo;&raquo;';
		}

		// $text = nl2br($text);
		if ($CI->input->cookie('user_language') === 'en' || empty($CI->input->cookie('user_language'))) {
			$text = filter_var($text, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);
		}

		$text = str_replace('<p></p>', '<p> </p>', $text);
		return preg_match('@[^\w\s\r\n' . preg_quote('+=~!@#$%^&*()_`-[]\{}|;:\'",./<>?€£”„‘’“', '@') . preg_quote(implode('', $regex), '@') . ']@ims', $text, $output);
	}
}

if (! function_exists('_clean_text')) {
	function _clean_text($texts = []) {
		foreach ($texts as &$text) {
			$text = _clean_text_single($text);
		}

		return $texts;
	}
}

if (! function_exists('_clean_emoji')) {
	function _clean_emoji($text = '') {
		// Emoji Unicode ranges
		$text = preg_replace('/\x{00A0}/u', ' ', $text);
		$emoji_regex = '/[\x{1F600}-\x{1F64F}' .  // Emoticons
						'\x{1F300}-\x{1F5FF}' .  // Misc Symbols and Pictographs
						'\x{1F680}-\x{1F6FF}' .  // Transport and Map
						'\x{1F700}-\x{1F77F}' .  // Alchemical Symbols
						'\x{1F780}-\x{1F7FF}' .  // Geometric Shapes Extended
						'\x{1F800}-\x{1F8FF}' .  // Supplemental Arrows-C
						'\x{1F900}-\x{1F9FF}' .  // Supplemental Symbols and Pictographs
						'\x{1FA00}-\x{1FA6F}' .  // Chess Symbols, etc.
						'\x{1FA70}-\x{1FAFF}' .  // Symbols and Pictographs Extended-A
						'\x{2600}-\x{26FF}' .	// Misc symbols (☀, ☂)
						'\x{2700}-\x{27BF}' .	// Dingbats
						'\x{FE00}-\x{FE0F}' .	// Variation Selectors
						'\x{1F1E6}-\x{1F1FF}' .  // Regional indicators
						'\x{1F191}-\x{1F251}' .  // Enclosed characters
						'\x{E0020}-\x{E007F}' .  // Tags
						'\x{24C2}' .			 // Enclosed (Ⓜ️)
						'\x{200D}' .			 // Zero-width joiner 👨‍👩‍👧‍👦
						']/u';

		return preg_replace($emoji_regex, '', $text);
	}
}

if (! function_exists('_clean_text_single')) {
	function _clean_text_single($text = '') {
		$CI	=&	get_instance();

		$text = htmlentities($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

		$regex = [];

		if ($CI->input->cookie('user_language') === 'es') {
			$regex[] = 'áÁéÉíÍñÑóÓúÚüÜ¿¡«»&aacute;&Aacute;&eacute;&Eacute;&iacute;&Iacute;&ntilde;&Ntilde;&oacute;&Oacute;&uacute;&Uacute;&uuml;&Uuml;&iquest;&iexcl;&laquo;&raquo;';
		}

		if (!_is_html($text) && _contains_markdown($text)) {
			$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
			$text = strip_tags($text);
			$text = _clean_emoji($text);
			$text = preg_replace('@[^\w\s\r\n' . preg_quote('+=~!@#$%^&*()_`-[]\{}|;:\'",./<>?€£”„‘’“', '@') . preg_quote(implode('', $regex), '@') . ']@ims', '', $text);

			return $text;
		}

		// $text = nl2br($text);
		if ($CI->input->cookie('user_language') === 'en' || empty($CI->input->cookie('user_language'))) {
			$text = filter_var($text, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);
		}

		$text = preg_replace('@[^\w\s\r\n' . preg_quote('+=~!@#$%^&*()_`-[]\{}|;:\'",./<>?€£”„‘’“', '@') . preg_quote(implode('', $regex), '@') . ']@ims', '', $text);

		$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

		$text = _clean_emoji($text);

		if (_is_html($text)) {
			$text = preg_replace_callback('/ {2,}/', function($matches) {
				return str_repeat('&nbsp;', strlen($matches[0]));
			}, $text);
		}

		$text = str_replace('<p></p>', '<p>&nbsp;</p>', $text);

		$text = preg_replace('/<p>(\s|&nbsp;)*<\/p>/', '<p><br></p>', $text);

		return $text;
	}
}

if (!function_exists('_is_html')) {
	function _is_html($text) {
		$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
		return $text !== strip_tags($text);
	}
}

if (!function_exists('_contains_markdown')) {
	function _contains_markdown($string) {
		$patterns = [
			'/^#{1,3}\s/',		  	// Headings: #, ##, ..., ###
			'/\*\*(.+?)\*\*/',		// Bold: **bold**
			'/\*(.+?)\*/',			// Italic: *italic*
			'/^\s*[\*\-\+]\s+/m',	// Unordered list: *, -, +
			'/^\s*\d+\.\s+/m',		// Ordered list: 1., 2., 3.
		];

		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $string)) {
				return true;
			}
		}

		return false;
	}
}

if (!function_exists('_parse_md')) {
	function _parse_md($text) {
		$text = str_replace(
			['\\\\', '\\*', '\\_', '\\#', '\\-'],
			['[[ESCBACKSLASH]]', '[[ESCSTAR]]', '[[ESCUNDERSCORE]]', '[[ESCHASH]]', '[[ESCDASH]]'],
			$text
		);

		$parseInlineMD = function($input) {
			$input = preg_replace('/\*\*\*([\s\S]+?)\*\*\*/', '<b><i>$1</i></b>', $input);
			$input = preg_replace('/\*\*([\s\S]+?)\*\*/', '<b>$1</b>', $input);
			$input = preg_replace('/(?<!\*)\*(?!\*)([\s\S]+?)\*/', '<i>$1</i>', $input);
			$input = preg_replace('/_([\s\S]+?)_/', '<u>$1</u>', $input);
			return $input;
		};

		$text = $parseInlineMD($text);

		$lines = explode("\n", $text);
		$html = [];
		$list_items = [];
		$list_type = '';

		$flushList = function() use (&$html, &$list_items, &$list_type) {
			if (!empty($list_items)) {
				$html[] = "<$list_type>" . implode('', array_map(function($item) {
					return "<li>$item</li>";
				}, $list_items)) . "</$list_type>";
				$list_items = [];
				$list_type = '';
			}
		};

		foreach ($lines as $line) {
			$line = trim($line);

			if (preg_match('/^### (.+)/', $line, $matches)) {
				$flushList();
				$html[] = '<h3>' . $parseInlineMD($matches[1]) . '</h3>';
			} elseif (preg_match('/^## (.+)/', $line, $matches)) {
				$flushList();
				$html[] = '<h2>' . $parseInlineMD($matches[1]) . '</h2>';
			} elseif (preg_match('/^# (.+)/', $line, $matches)) {
				$flushList();
				$html[] = '<h1>' . $parseInlineMD($matches[1]) . '</h1>';
			} elseif (preg_match('/^- (.+)/', $line, $matches)) {
				$item = $parseInlineMD($matches[1]);
				if ($list_type !== 'ul') $flushList();
				$list_type = 'ul';
				$list_items[] = $item;
			} elseif (preg_match('/^\d+\. (.+)/', $line, $matches)) {
				$item = $parseInlineMD($matches[1]);
				if ($list_type !== 'ol') $flushList();
				$list_type = 'ol';
				$list_items[] = $item;
			} elseif ($line === '') {
				$flushList();
			} else {
				$flushList();
				$html[] = '<p>' . $parseInlineMD($line) . '</p>';
			}
		}

		$flushList();

		$result = implode('', $html);

		$result = str_replace(
			['[[ESCSTAR]]', '[[ESCUNDERSCORE]]', '[[ESCHASH]]', '[[ESCDASH]]', '[[ESCBACKSLASH]]'],
			['*', '_', '#', '-', '\\'],
			$result
		);

		return $result;
	}
}
