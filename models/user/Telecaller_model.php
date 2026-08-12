<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Telecaller_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($telecaller_id = 0) {
		if ($telecaller_id > 0) {
			$this->db->where('id', $telecaller_id);
		}

		$this->db->where('role_id', 4);

		return $this->db->get('users')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('users.*');

		// if (isset($data['site_id'])) {
		// 	$this->db->select('user_id');
		// 	$this->db->where('site_id', (int)$data['site_id']);
		// 	$this->db->from('users_to_site');
		//
		// 	$where_clause = $this->db->get_compiled_select();
		// }
		//
		// if (isset($data['site_id'])) {
		// 	$this->db->where("`users`.`id` IN ($where_clause)", NULL, FALSE);
		// }

		if (isset($data['site_id'])) {
			$this->db->where('site_id', (int)$data['site_id']);
		}

		if (isset($data['exported'])) {
			$this->db->where('exported', (int)$data['exported']);
		}

		if (isset($data['mobile'])) {
			$this->db->like('mobile', $data['mobile'], 'after');
			// $this->db->or_like('first_name', $data['mobile'], 'after');
		}

		if (isset($data['name'])) {
			$this->db->like('first_name', $data['name'], 'after');
		}

		if (isset($data['email'])) {
			$this->db->like('email', $data['email'], 'after');
		}

		if (isset($data['email_verified'])) {
			$this->db->where('email_verified', (int)$data['email_verified']);
		}

		if (isset($data['mobile_verified'])) {
			$this->db->where('mobile_verified', (int)$data['mobile_verified']);
		}
		$this->db->where('role_id', 4);
		$this->db->where('users._deleted', 0);

		if (!empty($data['search'])) {
			$this->db->like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
		}

		$this->db->from('users');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'users.amount',
			'users.status',
			'users.date_added',
			'users.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'users.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add() {
		$validity = $this->check_duplication('on_create', $this->input->post('email'));

		if ($validity == false) {
			$this->session->set_flashdata('error_message', _l('email_duplication'));
		} else {
			$data['first_name'] = html_escape($this->input->post('first_name'));
			$data['last_name'] = html_escape($this->input->post('last_name'));
			$data['email'] = html_escape($this->input->post('email'));
			$data['mobile'] = html_escape($this->input->post('mobile'));
			$data['password'] = sha1(html_escape($this->input->post('password')));
			$social_link['facebook'] = html_escape($this->input->post('facebook_link'));
			$social_link['twitter'] = html_escape($this->input->post('twitter_link'));
			$social_link['linkedin'] = html_escape($this->input->post('linkedin_link'));
			$data['social_links'] = json_encode($social_link);
			$data['biography'] = $this->input->post('biography');
			$data['role_id'] = 4;
			$data['mode'] = $this->input->post('mode');
			$data['date_added'] = date('Y-m-d H:i:s');
			$data['wishlist'] = json_encode(array());
			$data['watch_history'] = json_encode(array());
			$data['status'] = 1;
			$data['site_id'] = (int)$this->input->post('site_id');

			$this->db->insert('users', $data);
			$telecaller_id = $this->db->insert_id();

			$this->upload_image($telecaller_id);
			$this->session->set_flashdata('flash_message', _l('telecaller_added_successfully'));
		}
	}

	public function check_duplication($action = "", $email = "", $telecaller_id = "") {
		$duplicate_email_check = $this->db->get_where('users', array('email' => $email));

		if ($action == 'on_create') {
			if ($duplicate_email_check->num_rows() > 0) {
				return false;
			} else {
				return true;
			}
		} elseif ($action == 'on_update') {
			if ($duplicate_email_check->num_rows() > 0) {
				if ($duplicate_email_check->row()->id == $telecaller_id) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
	}

	public function edit($telecaller_id = "") { // Admin does this editing
		$validity = $this->check_duplication('on_update', $this->input->post('email'), $telecaller_id);
		if ($validity) {
			$data['first_name'] = html_escape($this->input->post('first_name'));
			$data['last_name'] = html_escape($this->input->post('last_name'));

			$data['mode'] = $this->input->post('mode');

			if ($this->input->post('email')) {
				$data['email'] = html_escape($this->input->post('email'));
			}

			$data['mobile'] = html_escape($this->input->post('mobile'));

			if ($this->input->post('password')) {
				$data['password'] = sha1(html_escape($this->input->post('password')));
			}

			$social_link['facebook'] = html_escape($this->input->post('facebook_link'));
			$social_link['twitter'] = html_escape($this->input->post('twitter_link'));
			$social_link['linkedin'] = html_escape($this->input->post('linkedin_link'));
			$data['social_links'] = json_encode($social_link);
			$data['biography'] = $this->input->post('biography');
			$data['title'] = html_escape($this->input->post('title'));
			$data['date_modified'] = date('Y-m-d H:i:s');
			$data['site_id'] = (int)$this->input->post('site_id');

			$this->db->where('id', $telecaller_id);
			$this->db->update('users', $data);

			$this->upload_image($telecaller_id);
			$this->session->set_flashdata('flash_message', _l('telecaller_update_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('email_duplication'));
		}

		$this->upload_image($telecaller_id);
	}

	public function delete($telecaller_id = "") {
		$this->db->where('id', $telecaller_id);
		$this->db->delete('users');
		$this->session->set_flashdata('flash_message', _l('telecaller_deleted_successfully'));
	}

	public function enableDisable($telecaller_id) {
		if ($row = self::get($telecaller_id)->row_array()) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $telecaller_id);
			$this->db->where('role_id', 4);
			$this->db->update('users', [
				'status'	=> (int)$status
			]);
		}

		$this->session->set_flashdata('flash_message', _l('telecaller_updated_successfully'));
	}

	public function unlock_screen_by_password($password = "") {
		$password = sha1($password);
		return $this->db->get_where('users', array('id' => $this->session->telecallerdata('telecaller_id'), 'password' => $password))->num_rows();
	}

	public function register($data) {
		$this->db->insert('users', $data);
		return $this->db->insert_id();
	}

	public function my_courses() {
		return $this->db->get_where('enrol', array('telecaller_id' => $this->session->telecallerdata('telecaller_id')));
	}

	public function upload_image($telecaller_id) {
		$this->load->model('Tool_model');

		$res = $this->Tool_model->upload(
			'telecaller_image',
			$telecaller_id . '.jpg',
			'uploads/user_image/'
		);

		if (!empty($res['error'])) {
			$this->session->set_flashdata('error_message', $res['error']);
		} else {
			$this->session->set_flashdata('flash_message', _l('teacher_update_successfully'));
		}
	}

	public function update_account_settings($telecaller_id) {
		$validity = $this->check_duplication('on_update', $this->input->post('email'), $telecaller_id);
		if ($validity) {
			if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
				$telecaller_details = $this->get($telecaller_id)->row_array();
				$current_password = $this->input->post('current_password');
				$new_password = $this->input->post('new_password');
				$confirm_password = $this->input->post('confirm_password');
				if ($telecaller_details['password'] == sha1($current_password) && $new_password == $confirm_password) {
					$data['password'] = sha1($new_password);
				} else {
					$this->session->set_flashdata('error_message', _l('mismatch_password'));
					return;
				}
			}
			$data['email'] = html_escape($this->input->post('email'));
			$this->db->where('id', $telecaller_id);
			$this->db->update('users', $data);
			$this->session->set_flashdata('flash_message', _l('updated_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('email_duplication'));
		}
	}

	public function change_password($telecaller_id) {
		$data = array();
		if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
			$telecaller_details = $this->get_all($telecaller_id)->row_array();
			$current_password = $this->input->post('current_password');
			$new_password = $this->input->post('new_password');
			$confirm_password = $this->input->post('confirm_password');

			if ($telecaller_details['password'] == sha1($current_password) && $new_password == $confirm_password) {
				$data['password'] = sha1($new_password);
			} else {
				$this->session->set_flashdata('error_message', _l('mismatch_password'));
				return;
			}
		}

		$this->db->where('id', $telecaller_id);
		$this->db->update('users', $data);
		$this->session->set_flashdata('flash_message', _l('password_updated'));
	}

	public function get_image_url($telecaller_id) {
		if (file_exists('uploads/user_image/'.$telecaller_id.'.jpg'))
			return base_url().'uploads/user_image/'.$telecaller_id.'.jpg';
		else
			return base_url().'uploads/user_image/placeholder.png';
	}
}
