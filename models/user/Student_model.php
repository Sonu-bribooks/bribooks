<?php defined('BASEPATH') or exit('No direct script access allowed');

class Student_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($student_id = 0) {
		$this->db->select('users.*, site.country_code, site.name AS site, site.currency_code, state.name as state, city.name as city, country.name as country');
		$this->db->where('users.id', (int)$student_id);

		$this->db->where('users.role_id', 2);
		$this->db->where('users._deleted', 0);

		$this->db->join('site', 'site.id = users.site_id', 'left');
		$this->db->join('country', 'country.id = users.country_id', 'left');
		$this->db->join('state', 'state.id = users.state_id', 'left');
		$this->db->join('city', 'city.id = users.city_id', 'left');

		return $this->db->get('users')->row_array();
	}

	public function get_by_role_id($role_id = 0) {
		$this->db->select('users.*');
		$this->db->where('role_id', (int)$role_id);
		$this->db->where('users._deleted', 0);
		return $this->db->get('users')->result_array();
	}

	public function get_by_role_id_in($role_id = []) {
		$this->db->select('users.*');
		$this->db->where_in('role_id', $role_id);
		$this->db->where('users._deleted', 0);
		return $this->db->get('users')->result_array();
	}

	public function get_all($data = []) {
		$this->db->select('users.*');

		if (isset($data['user_id'])) {
			$this->db->where('id', (int)$data['user_id']);
		}

		if (!empty($data['user_ids'])) {
			$this->db->where_in('id', $data['user_ids']);
		}

		if (isset($data['site_id'])) {
			$this->db->where('site_id', (int)$data['site_id']);
		}

		if (isset($data['location'])) {
			$this->db->where('location', $data['location']);
		}

		if (isset($data['verification_code'])) {
			$this->db->where('users.verification_code', $data['verification_code']);
		}

		if (isset($data['exported'])) {
			$this->db->where('exported', (int)$data['exported']);
		}

		if (isset($data['mobile'])) {
			$this->db->like('mobile', $data['mobile'], 'after');
		}

		if (isset($data['name'])) {
			$this->db->like('first_name', $data['name'], 'after');
		}

		if (isset($data['source'])) {
			$this->db->like('source', $data['source'], 'after');
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

		if (isset($data['student_verified'])) {
			$this->db->group_start();
			$this->db->where('mobile_verified', (int)$data['student_verified']);
			$this->db->or_where('email_verified', (int)$data['student_verified']);
			$this->db->group_end();
		}

		if (isset($data['section_id'])) {
			$this->db->where('section_id', (int)$data['section_id']);
		}

		if (isset($data['grade_id'])) {
			$this->db->where('grade_id', (int)$data['grade_id']);
		}

		if (isset($data['grade'])) {
			$this->db->where('grade', (int)$data['grade']);
		}

		if (isset($data['section'])) {
			$this->db->where('section', $data['section']);
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('users.date_added BETWEEN "'. date('Y-m-d H:i:s', strtotime($data['startdate'].' 00:00:00')). '" and "'. date('Y-m-d H:i:s', strtotime($data['enddate'].' 23:59:59')).'"');
		}

		if (isset($data['event_id'])) {
			$this->db->where(sprintf('users.id in (select user_id from event_user where event_id = %s)', (int)$data['event_id']));
		}

		$this->db->where('role_id', 2);
		$this->db->where('users._deleted', 0);

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('CONCAT(users.first_name, " ", users.last_name)', $data['search'], 'after');
			$this->db->or_like('users.id', $data['search'], 'after');
			$this->db->or_like('users.email', $data['search'], 'after');
			$this->db->or_like('users.mobile', $data['search'], 'after');
			$this->db->or_like('users.source', $data['search'], 'after');
			$this->db->group_end();
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
			'users.first_name',
			'users.date_added',
			'users.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'users.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('users', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'status'		=> 1,
			'site_id'		=> $data['site_id'] ?? (int)$this->config->item('site_id'),
		]);

		$student_id = $this->db->insert_id();

		return $student_id;
	}

	public function edit($id = 0, $data = []) {
		$this->db->update('users', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		], [
			'id'			=> (int)$id
		]);
	}

	public function check_duplication($action = '', $email = '', $mobile = '', $student_id = 0) {
		if (!$email) return true;

		$duplicate_email_check = $this->db->get_where('users', [
			'email' 	=> $email,
			'mobile' 	=> $mobile,
		]);

		if ($action == 'on_create') {
			if ($duplicate_email_check->num_rows() > 0) {
				return false;
			} else {
				return true;
			}
		} elseif ($action == 'on_update') {
			if ($duplicate_email_check->num_rows() > 0) {
				if ($duplicate_email_check->row()->id == $student_id) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
	}

	public function delete($student_id = 0) {
		$this->db->where('id', (int)$student_id);
		$this->db->update('users',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', get_phrase('student_deleted_successfully'));
	}

	public function get_image_url($student_id) {
		if (file_exists('uploads/student_image/' . $student_id . '.jpg'))
			return base_url() . 'uploads/student_image/' . $student_id . '.jpg';
		else
			return base_url() . 'uploads/student_image/placeholder.png';
	}

	public function update_email($data = []) {
		if ($this->get($data['student_id'])->row()) {
			$this->db->update('users', [
				'email'		=> $data['email'],
			], [
				'id'		=> (int)$data['student_id'],
			]);

			$this->session->set_flashdata('flash_message', _l('email_updated_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('student_not_found'));
		}
	}

	public function enableDisable($student_id) {
		if ($row = self::get($student_id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $student_id);
			$this->db->where('role_id', 2);
			$this->db->update('users', [
				'status'	=> (int)$status
			]);
		}

		$this->session->set_flashdata('flash_message', _l('updated_successfully'));
	}

	public function getLmsLink($data = []) {
	}

	public function updateSubscriptionPlan($user_id = 0, $subscription_plan_id = 0, $hard_copy = 0) {
		$this->db->update('users', [
			'subscription_plan_id'		=> (int)$subscription_plan_id,
			'hard_copy'					=> (int)$hard_copy,
			'date_modified'				=> date('Y-m-d H:i:s'),
		], [
			'id'						=> (int)$user_id,
		]);
	}

	public function getBySlug($slug = '') {
		$this->db->select('users.*');

		$this->db->where('users.slug', $slug);
		$this->db->where('users.status', 1);
		$this->db->where('users._deleted', 0);

		return $this->db->get('users')->row_array();
	}

	public function updateAddress($user_id = 0, $address_id = 0) {
		$this->db->update('users', [
			'address_id'		=> (int)$address_id,
			'date_modified'		=> date('Y-m-d H:i:s'),
		], [
			'id'				=> (int)$user_id,
		]);
	}

	public function totalBooksBySite($data = []) {
		$this->db->select('count(book.id) as total');
		$this->db->where('users.role_id', 2);
		$this->db->where('users.site_id', (int)$data['site_id']);
		$this->db->where('users._deleted', 0);
		$this->db->where('users.status', 1);
		$this->db->from('book');
		$this->db->join('users', 'users.id=book.user_id', 'left');

		$total = $this->db->get()->row()->total;

		return $total;
	}

	public function updateHardCopy($user_id = 0, $used_copy = 0) {
		if ($user_info = self::get($user_id)) {
			$this->db->update('users', [
				'hard_copy'			=> (int)($user_info['hard_copy'] - $used_copy),
				'date_modified'		=> date('Y-m-d H:i:s'),
			], [
				'id'				=> (int)$user_id,
			]);
		}
	}

	public function nyaf_users_data($data = [], $published = false) {
	}

	public function nyaf_book_not_sold_data($data = []) {
	}

	public function getClassByStudentId($student_id = 0) {
	}

	public function getReferralUser($user_id = 0) {
		$this->db->select('users.*, site.country_code, site.name AS site, site.currency_code');

		$this->db->where('users.parent_referral_id', (int)$user_id);
		$this->db->where('users.role_id', 2);
		$this->db->join('site', 'site.id = users.site_id', 'left');
		$this->db->from('users');

		$total = $this->db->count_all_results('', FALSE);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}
}
