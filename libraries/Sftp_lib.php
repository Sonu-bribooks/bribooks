<?php defined('BASEPATH') OR exit('No direct script access allowed');

final class Sftp_lib {
	private $hostname	= '';
	private $username	= '';
	private $password	= '';
	private $port		= 22;
	private $debug		= FALSE;
	private $conn_sftp	= FALSE;
	private $login_via_key 		= FALSE;
	private $public_key_url 	= '';
	private $private_key_url 	= '';
	private $buffer_size 		= 1024;

	public function __construct($config = []) {
		if (!empty($config) && is_array($config)) {
			self::initialize($config);
		}
	}

	public function initialize($config = []) {
		foreach ($config as $key => $val) {
			if (isset($this->$key)) {
				$this->$key = $val;
			}
		}

		// Prep the hostname
		$this->hostname = preg_replace('|.+?://|', '', $this->hostname);
	}

	public function connect($config = []) {
		if (!empty($config) && is_array($config)) {
			self::initialize($config);
		}

		$this->conn = ssh2_connect($this->hostname, $this->port);

		if (!self::_login()) {
			self::_error('sftp_unable_to_login_to_ssh');

			return FALSE;
		}

		if (FALSE === ($this->conn_sftp = @ssh2_sftp($this->conn))) {
			self::_error('sftp_unable_to_open_sftp_resource');

			return FALSE;
		}

		return TRUE;
	}

	public function _login() {
		if ($this->login_via_key) {
			if (@ssh2_auth_pubkey_file($this->conn, $this->username, $this->public_key_url, $this->private_key_url, $this->password)) {
				return true;
			} else {
				self::_error('sftp_unable_to_connect_with_public_key');

				return false;
			}
		} else {
			return @ssh2_auth_password($this->conn, $this->username, $this->password);
		}
	}

	private function _is_conn() {
		if (!is_resource($this->conn_sftp)) {
			self::_error('sftp_no_connection');

			return FALSE;
		}

		return TRUE;
	}

	public function _scan_directory($dir, $recursive = FALSE) {
		$files = [];
		$handle = opendir($dir);

		// List all the files
		while (false !== ($file = readdir($handle))) {
			if (substr("$file", 0, 1) != ".") {
				if (is_dir($file) && $recursive) {
					$files[$file] = $this->_scan_directory("$dir/$file");
				} else {
					$files[] = $file;
				}
			}
		}

		closedir($handle);

		return $files;
	}

	public function mkdir($path = '') {
		if ($path == '' OR ! self::_is_conn()) {
			return FALSE;
		}

		$result = @ssh2_sftp_mkdir($this->conn_sftp, $path);

		if ($result === FALSE) {
			self::_error('sftp_unable_to_makdir');

			return FALSE;
		}

		return TRUE;
	}

	public function uploadOld($localpath, $remotepath) {
		if (!self::_is_conn()) {
			return FALSE;
		}

		if (!file_exists($localpath)) {
			self::_error('sftp_no_source_file');

			return FALSE;
		}

		$sftp = $this->conn_sftp;
		$stream = @fopen("ssh2.sftp://$sftp$remotepath", 'w');

		if ($stream === FALSE) {
			self::_error('sftp_unable_to_upload');

			return FALSE;
		}

		$data_to_send = @file_get_contents($localpath);

		if (@fwrite($stream, $data_to_send) === false) {
			self::_error('sftp_unable_to_send_data');

			return FALSE;
		}

		@fclose($stream);

		return TRUE;
	}

	public function downloadOld($remotepath, $localpath) {
		if (!self::_is_conn()) {
			return FALSE;
		}

		$sftp = $this->conn_sftp;

		$stream = @fopen("ssh2.sftp://$sftp$remotepath", 'r');

		if ($stream === FALSE) {
			self::_error('sftp_unable_to_download');

			return FALSE;
		}

		$contents = null;

		while (!feof($stream)) {
			$contents .= @fread($stream, $this->buffer_size);
		}

		$result = file_put_contents($localpath, $contents);

		@fclose($stream);

		return $result;
	}

	public function force_download($remotepath) {
		if (!self::_is_conn()) {
			return FALSE;
		}

		$sftp = $this->conn_sftp;
		$stream_path = "ssh2.sftp://$sftp$remotepath";

		$stream = @fopen($stream_path, 'r');

		if ($stream === FALSE) {
			self::_error('sftp_unable_to_download');

			return FALSE;
		}

		header('Content-Type: application/octet-stream');
		header("Content-Transfer-Encoding: Binary");
		header("Content-disposition: attachment; filename=\"".basename($stream_path)."\"");
		header('Content-Length: ' . filesize($stream_path));
		readfile($stream_path);
	}

	public function rename($old_file, $new_file, $move = FALSE) {
		if (!self::_is_conn()) {
			return FALSE;
		}

		$result = @ssh2_sftp_rename($this->conn_sftp, $old_file, $new_file);

		if ($result === FALSE) {
			self::_error('ftp_unable_to_rename');

			return FALSE;
		}

		return TRUE;
	}

	public function delete_file($filepath) {
		if (!self::_is_conn()) {
			return FALSE;
		}

		$sftp = $this->conn_sftp;
		$result = unlink("ssh2.sftp://$sftp$filepath");

		if ($result === FALSE) {
			self::_error('sftp_unable_to_delete');

			return FALSE;
		}

		return TRUE;
	}

	public function delete_dir($filepath) {
		if (!self::_is_conn()) {
			return FALSE;
		}

		$filepath = preg_replace("/(.+?)\/*$/", "\\1/",  $filepath);

		$result = @ssh2_sftp_rmdir($this->conn_id, $filepath);

		if ($result === FALSE) {
			self::_error('sftp_unable_to_delete');

			return FALSE;
		}

		return TRUE;
	}

	public function list_files($path = '.', $recursive = FALSE) {
		if (!self::_is_conn()) {
			return FALSE;
		}

		$sftp = $this->conn_sftp;
		$dir = "ssh2.sftp://$sftp$path";

		$directory = $this->_scan_directory($dir, $recursive);

		sort($directory);

		return $directory;
	}

	public function upload_from_var($data_to_send, $remotepath) {
		if (!self::_is_conn()) {
			return FALSE;
		}

		$sftp = $this->conn_sftp;

		$stream = @fopen("ssh2.sftp://$sftp$remotepath", 'w');

		if ($stream === FALSE) {
			self::_error('sftp_unable_to_upload');

			return FALSE;
		}

		if (@fwrite($stream, $data_to_send) === false) {
			self::_error('sftp_unable_to_send_data');

			return FALSE;
		}

		@fclose($stream);

		return TRUE;
	}

	public function upload($localpath, $remotepath) {
		$ch = curl_init();
		$fp = fopen($localpath, 'r');

		curl_setopt($ch, CURLOPT_URL, "sftp://{$this->hostname}/files/$remotepath");
		curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
		curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
		curl_setopt($ch, CURLOPT_INFILE, $fp);
		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localpath));
		curl_setopt($ch, CURLOPT_UPLOAD, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		curl_setopt($ch, CURLOPT_FAILONERROR,true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);

		$result = curl_exec($ch);

		$error = curl_error($ch);
		$error_no = curl_errno($ch);
		curl_close ($ch);

		log_kb([
			'$result' 	=> $result,
			'$error' 	=> $error,
			'$error_no' => $error_no,
		]);
	}

	public function download($localpath, $remotepath) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "sftp://{$this->hostname}/files/$remotepath");
		curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
		curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		curl_setopt($ch, CURLOPT_FAILONERROR,true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);

		$result = curl_exec($ch);

		$error = curl_error($ch);
		$error_no = curl_errno($ch);
		curl_close ($ch);

		file_put_contents($localpath, $result);

		log_kb([
			'$error' 	=> $error,
			'$error_no' => $error_no,
		]);
	}

	private function _error($line) {
		if ($this->debug !== TRUE) return;

		$errors['sftp_no_connection']					= 'Unable to locate a valid connection ID.  Please make sure you are connected before peforming any file routines.';
		$errors['sftp_unable_to_login_to_ssh']			= 'Unable to connect to your SSH server.  Please check your username and password.';
		$errors['sftp_unable_to_opn_sftp_resource']		= 'Unable to start SFTP resource.';
		$errors['sftp_unable_to_makdir']				= 'Unable to create the directory you have specified.';
		$errors['sftp_unable_to_changedir']				= 'Unable to change directories.';
		$errors['sftp_unable_to_upload']				= 'Unable to upload the specified file.  Please check your path.';
		$errors['sftp_no_source_file']					= 'Unable to locate the source file for upload.  Please check your path.';
		$errors['sftp_unable_to_rename']				= 'Unable to rename the file.';
		$errors['sftp_unable_to_delete']				= 'Unable to delete the file.';
		$errors['sftp_unable_to_move']					= 'Unable to move the file.  Please make sure the destination directory exists.';
		$errors['sftp_unable_to_connect_with_public_key']= 'Unable to connect with the public key you\'ve provided.';

		log_kb(['Sftp_lib::error::' => $errors[$line]]);
	}
}
