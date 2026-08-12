<?php defined('BASEPATH') OR exit('No direct script access allowed');

function adminer_object() {
	class AdminerCI extends Adminer\Adminer {
		private $_config;
		private $_width = 200, $_height = 200, $_url = false;

		function __construct() {
			$this->CI =& get_instance();
			$this->db = $this->CI->db;
			$this->session = $this->CI->session;
			$this->load = $this->CI->load;
			$this->config = $this->CI->config;
			$this->input = $this->CI->input;

			$this->rdb = $this->load->database('replica', TRUE);

			$this->_config = [
				'hostname'	=> $this->rdb->hostname,
				'username'	=> $this->rdb->username,
				'password'	=> $this->rdb->password,
				'database'	=> $this->rdb->database,
			];
		}

		function name() {
			return 'BriBooks';
		}

		function credentials() {
			return [
				$this->_config['hostname'],
				$this->_config['username'],
				$this->_config['password']
			];
		}

		function login($login, $password) {
			return true;
		}

		function database() {
			return $this->_config['database'];
		}

		function loginForm() {
			if (empty($this->input->get('server'))) {
				$data['server'][$this->_config['hostname']][$this->_config['username']] = ($this->input->cookie('adminer_key') && is_string($this->_config['password'])
					? array(Adminer\encrypt_string($this->_config['password'], $this->input->cookie('adminer_key')))
					: $this->_config['password']
				);

				$this->session->set_userdata('pwds', $data);

				redirect(vsprintf('bbdatabase/?server=%s&username=%s&db=%s', [
					$this->_config['hostname'],
					$this->_config['username'],
					$this->_config['database']
				]), 'refresh');
			}

			parent::loginForm();
		}

		function head($dark = null) {
			parent::head($dark);
			self::_loadImage();

			?>
				<script <?php echo Adminer\nonce(); ?>>
					let adminerDark;

					function adminerDarkSwitch() {
						adminerDark = !adminerDark;
						adminerDarkSet(true);
					}

					function adminerDarkSet(force) {
						qsa('link[href*="dark.css"]').forEach(link => {
							if (!adminerDark || force) {
								link.media = (adminerDark ? '' : 'never')
							}
						});
						qs('meta[name="color-scheme"]').content = (adminerDark ? 'dark' : 'light');
						cookie('adminer_dark=' + (adminerDark ? 1 : 0), 30);
					}

					const saved = document.cookie.match(/adminer_dark=(\d)/);
					if (saved) {
						adminerDark = +saved[1];
						adminerDarkSet();
					} else {
						adminerDark = +matchMedia('(prefers-color-scheme: dark)').matches;
					}
				</script>
			<?php
		}

		function navigation($missing) {
			parent::navigation($missing);

			echo "<big style='position: fixed; bottom: .5em; right: .5em; cursor: pointer;'>☀</big>"
				. Adminer\script("adminerDarkSet(); qsl('big').onclick = adminerDarkSwitch;") . "\n"
			;
		}

		private function _loadImage() {
			echo sprintf('<script %s>', Adminer\nonce());
			echo sprintf("
				(function () {
					document.addEventListener('DOMContentLoaded', function (e) {
						const links = document.querySelectorAll('.scrollable table tbody a');
						const re = /^.*\.(jpg|jpeg|png|gif)(\?.*)?$/

						for (let i = 0; i < links.length; i++) {
							if (re.test(links[i].href)) {
								const img = document.createElement('img');
								img.src = links[i].href;
								img.alt = links[i].href;
								img.style.display = 'block';
								img.style.maxWidth = '%spx';
								img.style.maxHeight = '%spx';

								img.addEventListener('load', function (image) {
									%s
									links[this].insertBefore(image, links[this].firstChild);
								}.bind(i, img));
							}
						}
					});
				})();
			", $this->_width, $this->_height, $this->_url ? '' : "links[this].innerHTML = '';");
			echo '</script>';
		}
	}

	return new AdminerCI();
}

include __DIR__ . '/adminer.php';
