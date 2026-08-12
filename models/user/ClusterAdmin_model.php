<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ClusterAdmin_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($cluster_admin_id = 0) {
		if ($cluster_admin_id > 0) {
			$this->db->where('id', $cluster_admin_id);
		}

		$this->db->where('role_id', 6);

		return $this->db->get('users');
	}

	public function get_all($cluster_admin_id = 0) {
		if ($cluster_admin_id > 0) {
			$this->db->where('id', $cluster_admin_id);
		}

		$this->db->where('role_id', 6);

		return $this->db->get('users');
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
			$data['role_id'] = 6;
			$data['mode'] = $this->input->post('mode');
			$data['date_added'] = date('Y-m-d H:i:s');
			$data['wishlist'] = json_encode(array());
			$data['watch_history'] = json_encode(array());
			$data['status'] = 1;

			$this->db->insert('users', $data);
			$cluster_admin_id = $this->db->insert_id();

			$this->upload_image($cluster_admin_id);
			$this->session->set_flashdata('flash_message', _l('cluster_admin_added_successfully'));
		}
	}

	public function check_duplication($action = "", $email = "", $cluster_admin_id = "") {
		$duplicate_email_check = $this->db->get_where('users', array('email' => $email));

		if ($action == 'on_create') {
			if ($duplicate_email_check->num_rows() > 0) {
				return false;
			} else {
				return true;
			}
		} elseif ($action == 'on_update') {
			if ($duplicate_email_check->num_rows() > 0) {
				if ($duplicate_email_check->row()->id == $cluster_admin_id) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
	}

	public function edit($cluster_admin_id = "") { // Admin does this editing
		$validity = $this->check_duplication('on_update', $this->input->post('email'), $cluster_admin_id);
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

			$this->db->where('id', $cluster_admin_id);
			$this->db->update('users', $data);

			$this->upload_image($cluster_admin_id);
			$this->session->set_flashdata('flash_message', _l('cluster_admin_update_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('email_duplication'));
		}

		$this->upload_image($cluster_admin_id);
	}

	public function delete($cluster_admin_id = "") {
		$this->db->where('id', $cluster_admin_id);
		$this->db->delete('users');
		$this->session->set_flashdata('flash_message', _l('cluster_admin_deleted_successfully'));
	}

	public function unlock_screen_by_password($password = "") {
		$password = sha1($password);
		return $this->db->get_where('users', array('id' => $this->session->cluster_admindata('cluster_admin_id'), 'password' => $password))->num_rows();
	}

	public function register($data) {
		$this->db->insert('users', $data);
		return $this->db->insert_id();
	}

	public function my_courses() {
		return $this->db->get_where('enrol', array('cluster_admin_id' => $this->session->cluster_admindata('cluster_admin_id')));
	}

	public function upload_image($cluster_admin_id) {
		$this->load->model('Tool_model');

		$res = $this->Tool_model->upload(
			'cluster_admin_image',
			$cluster_admin_id . '.jpg',
			'uploads/user_image/'
		);

		if (!empty($res['error'])) {
			$this->session->set_flashdata('error_message', $res['error']);
		} else {
			$this->session->set_flashdata('flash_message', _l('update_successfully'));
		}
	}

	public function update_account_settings($cluster_admin_id) {
		$validity = $this->check_duplication('on_update', $this->input->post('email'), $cluster_admin_id);
		if ($validity) {
			if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
				$cluster_admin_details = $this->get($cluster_admin_id)->row_array();
				$current_password = $this->input->post('current_password');
				$new_password = $this->input->post('new_password');
				$confirm_password = $this->input->post('confirm_password');
				if ($cluster_admin_details['password'] == sha1($current_password) && $new_password == $confirm_password) {
					$data['password'] = sha1($new_password);
				} else {
					$this->session->set_flashdata('error_message', _l('mismatch_password'));
					return;
				}
			}
			$data['email'] = html_escape($this->input->post('email'));
			$this->db->where('id', $cluster_admin_id);
			$this->db->update('users', $data);
			$this->session->set_flashdata('flash_message', _l('updated_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('email_duplication'));
		}
	}

	public function change_password($cluster_admin_id) {
		$data = array();
		if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
			$cluster_admin_details = $this->get_all($cluster_admin_id)->row_array();
			$current_password = $this->input->post('current_password');
			$new_password = $this->input->post('new_password');
			$confirm_password = $this->input->post('confirm_password');

			if ($cluster_admin_details['password'] == sha1($current_password) && $new_password == $confirm_password) {
				$data['password'] = sha1($new_password);
			} else {
				$this->session->set_flashdata('error_message', _l('mismatch_password'));
				return;
			}
		}

		$this->db->where('id', $cluster_admin_id);
		$this->db->update('users', $data);
		$this->session->set_flashdata('flash_message', _l('password_updated'));
	}

	public function get_image_url($cluster_admin_id) {
		if (file_exists('uploads/user_image/'.$cluster_admin_id.'.jpg'))
			return base_url().'uploads/user_image/'.$cluster_admin_id.'.jpg';
		else
			return base_url().'uploads/user_image/placeholder.png';
	}
}
