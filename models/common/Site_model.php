<?php defined('BASEPATH') or exit('No direct script access allowed');

class Site_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($id = 0) {
		$this->db->where('site.id', (int)$id);

		$row = $this->db->get('site')->row_array();

		unset($row['owner_password']);

		return $row;
	}

	public function get_all($data = []) {
		$this->db->select('
			site.*,
		');

		if (!empty($data['name'])) {
			$this->db->where('site.name', $data['name']);
		}

		if (!empty($data['country_code'])) {
			$this->db->where('site.country_code', $data['country_code']);
		}

		if (!empty($data['id'])) {
			$this->db->where_in('site.id', $data['id']);
		}

		if (!empty($data['email'])) {
			$this->db->where('site.owner_email', trim($data['email']));
		}

		if (!empty($data['owner_email'])) {
			$this->db->where('site.owner_email', trim($data['owner_email']));
		}

		if (!empty($data['owner_mobile'])) {
			$this->db->where('site.owner_mobile', trim($data['owner_mobile']));
		}

		if (!empty($data['site_ids'])) {
			$this->db->where_in('site.id', $data['site_ids']);
		}

		if (isset($data['site_id_ne'])) {
			$this->db->where('site.id !=', (int)$data['site_id_ne']);
		}

		if (!empty($data['site_type'])) {
			$this->db->where('site.site_type', $data['site_type']);
		}

		if (isset($data['parent_id'])) {
			$this->db->where('site.parent_id', (int)$data['parent_id']);
		}

		if (isset($data['country_id'])) {
			$this->db->where('site.country_id', (int)$data['country_id']);
		}

		if (isset($data['state_id'])) {
			$this->db->where('site.state_id', (int)$data['state_id']);
		}

		if (isset($data['state_id_ne'])) {
			$this->db->where('site.state_id!=', (int)$data['state_id_ne']);
		}

		if (isset($data['city_id'])) {
			$this->db->where('site.city_id', (int)$data['city_id']);
		}

		if (!empty($data['site_code'])) {
			$this->db->like('site.site_code', $data['site_code'], 'after');
		}

		if (!empty($data['site_codes'])) {
			$this->db->where_in('site.site_code', $data['site_codes']);
		}

		if (!empty($data['parent_ids'])) {
			$this->db->where_in('site.parent_id', $data['parent_ids']);
		}

		if (isset($data['verified'])) {
			$this->db->where('site.verified', (int)$data['verified']);
		}

		if (isset($data['status'])) {
			$this->db->where('site.status', (int)$data['status']);
		}

		if (isset($data['event_id'])) {
			$this->db->where(sprintf('site.id in (select site_id from event_site where event_id = %s and _deleted = 0)', (int)$data['event_id']));
		}

		if (isset($data['user_id'])) {
			$this->db->where('site.user_id', (int)$data['user_id']);
		}

		if (!empty($data['search_name'])) {
			$this->db->like('site.name', $data['search_name'], 'both');
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('site.name', $data['search'], 'both');
			$this->db->or_like('site.owner_name', $data['search'], 'both');
			$this->db->or_like('site.owner_mobile', $data['search'], 'both');
			$this->db->or_like('site.owner_email', $data['search'], 'both');
			$this->db->or_like('site.authorized_person', $data['search'], 'both');
			$this->db->or_like('site.site_code', $data['search'], 'both');
			$this->db->or_like('site.id', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->where('site._deleted', 0);

		$this->db->from('site');

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
			'id',
			'status',
			'date_added',
			'date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'site.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function addSite($data = []) {
		$this->db->insert('site', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$id = $this->db->insert_id();
		return $id;

	}

	public function add($data) {
		$validity = $this->check_duplication('on_create', $data['site_code']);

		if ($validity == false) {
			$this->session->set_flashdata('error_message', _l('site_duplication'));
		} else {
			$data = $data ? $data : $this->input->post();

			$data['parent_id'] && ($site_info = self::get($data['parent_id']));

			$this->db->insert('site', [
				'site_type'			=> $data['site_type'] ?? 1,
				'license_total'		=> $data['license_total'],
				'name'				=> $data['name'],
				'image' 			=> $data['image'],
				'parent_id'			=> (int)$data['parent_id'],
				'payment_gateway'	=> $data['payment_gateway'],
				'pincode'			=> $data['pincode'] ?? '',
				'sms_gateway'		=> $data['sms_gateway'],
				'email_alert'		=> $data['email_alert'],
				'address'			=> $data['address'],
				'mobile_length'		=> (int)$data['mobile_length'],
				'country_code'		=> $data['country_code'],
				'state_id'			=> $data['state_id'] ?? 0,
				'city_id'			=> $data['city_id'] ?? 0,
				'site_code'			=> $data['site_code'] ?? uniqid(),
				// 'site_code'			=> isset($site_info['site_code']) ? ($site_info['site_code'] . '-' . $data['site_code']) : $data['site_code'],
				'discount_code'		=> !empty($data['discount_code']) ? $data['discount_code'] : $site_info['discount_code'],
				'discount_percentage' => !empty($data['discount_percentage']) ? $data['discount_percentage'] : $site_info['discount_percentage'],
				'currency_code'		=> $data['currency_code'],
				'base_price'		=> $data['base_price'],
				'ebook_price'		=> $data['ebook_price'] ?? 0,
				'price_per_page'	=> $data['price_per_page'],
				'free_page_limit'	=> $data['free_page_limit'],
				'hard_cover_price'	=> $data['hard_cover_price'],
				'tax'				=> $data['tax'],
				'tax_text'			=> $data['tax_text'],
				'timezone'			=> $data['timezone'],
				'owner_name'		=> $data['owner_name'] ?? '',
				'authorized_person'	=> $data['authorized_person'] ?? '',
				'owner_email'		=> $data['owner_email'],
				'owner_mobile'		=> $data['owner_mobile'],
				'owner_password'	=> sha1($data['owner_password']),
				'can_add_site'		=> (int)$data['can_add_site'],
				'status'			=> (int)$data['status'],
				'verified'			=> (int)$data['verified'] ?? 1,
				'date_verified'		=> $data['date_verified'] ?? '',
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
			]);

			$id = $this->db->insert_id();

			self::addUser($id, $data);

			$this->session->set_flashdata('flash_message', _l('site_added_successfully'));

			return $id;
		}
	}

	public function edit($id = 0, $image = NULL, $data = []) {
		$validity = $this->check_duplication('on_update', $data['site_code'], $id);
		$site_data_info = self::get($id);

		if ($validity) {
			$data = $data ? $data : $this->input->post();

			if ($data['parent_id'] && ($site_info = self::get($data['parent_id']))) {
				$site_code = strpos($data['site_code'], $site_info['site_code'] . '-') === false
					? $site_info['site_code'] . '-' . $data['site_code']
					: $data['site_code'];
				$discount_code = $site_info['discount_code'];
				$discount_percentage = $site_info['discount_percentage'];
			} else {
				$site_code = $data['site_code'];
				$discount_code = $data['discount_code'];
				$discount_percentage = $data['discount_percentage'];
			}

			if (empty($discount_code)) {
				$discount_code = $data['discount_code'];
			}

			if (empty($discount_percentage)) {
				$discount_percentage = $data['discount_percentage'];
			}

			$this->db->update('site', [
				'parent_id'			=> (int)$data['parent_id'],
				'license_total'		=> $data['license_total'],
				'name'				=> $data['name'],
				'image'				=> $image,
				'payment_gateway'	=> $data['payment_gateway'],
				'sms_gateway'		=> $data['sms_gateway'],
				'email_alert'		=> $data['email_alert'],
				'address'			=> $data['address'],
				'mobile_length'		=> (int)$data['mobile_length'],
				'country_code'		=> $data['country_code'],
				'state_id'			=> $data['state_id'] ?? 0,
				'city_id'			=> $data['city_id'] ?? 0,
				'site_code'			=> $site_code,
				'discount_code'		=> $discount_code,
				'discount_percentage' => $discount_percentage,
				'currency_code'		=> $data['currency_code'],
				'base_price'		=> $data['base_price'],
				'ebook_price'		=> $data['ebook_price'] ?? 0,
				'price_per_page'	=> $data['price_per_page'],
				'free_page_limit'	=> $data['free_page_limit'],
				'hard_cover_price'	=> $data['hard_cover_price'],
				'tax'				=> $data['tax'],
				'tax_text'			=> $data['tax_text'],
				'timezone'			=> $data['timezone'],
				'owner_name'		=> $data['owner_name'] ?? '',
				'authorized_person'	=> $data['authorized_person'] ?? '',
				'owner_email'		=> $data['owner_email'],
				'owner_mobile'		=> $data['owner_mobile'],
				'can_add_site'		=> (int)$data['can_add_site'],
				'status'			=> (int)$data['status'],
				'verified'			=> isset($data['verified']) ? (int)$data['verified'] : $site_data_info['verified'],
				'date_modified'		=> date('Y-m-d H:i:s'),
			], [
				'id'				=> (int)$id
			]);

			if (!empty($data['owner_password'])) {
				$this->db->update('site', [
					'owner_password'	=> sha1($data['owner_password']),
					'date_modified'		=> date('Y-m-d H:i:s'),
				], [
					'id'				=> (int)$id
				]);
			}

			self::addUser($id, $data);

			$this->session->set_flashdata('flash_message', _l('site_update_successfully'));
		} else {
			$this->session->set_flashdata('error_message', _l('site_duplication'));
		}
	}

	public function editById($site_id = 0, $data = []) {
		$this->db->where('id', $site_id);
		$this->db->update('site', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('site_edited_successfully'));
	}

	private function addUser($site_id, $data = []) {
		$user_id = 0;

		if ($row = $this->db->get_where('users', [
			'site_id'		=> $site_id,
			'role_id'		=> 9
		])->row_array()) {
			$update_data = [
				'first_name'	=> $data['name'],
				'email'			=> $data['owner_email'],
				'mobile'		=> $data['owner_mobile'],
				'role_id'		=> 9,
				'site_id'		=> (int)$site_id,
				'date_modified'	=> date('Y-m-d H:i:s'),
				'status'		=> (int)$data['status'],
			];

			if (!empty($data['owner_password'])) {
				$update_data['password'] = sha1($data['owner_password']);
			}

			$this->db->update('users', $update_data, [
				'id' => (int)$row['id']
			]);

			$user_id = $row['id'];
		} else {
			$this->db->insert('users', [
				'first_name'	=> $data['name'],
				'password'		=> sha1($data['owner_password']),
				'email'			=> $data['owner_email'],
				'mobile'		=> $data['owner_mobile'],
				'role_id'		=> 9,
				'site_id'		=> (int)$site_id,
				'date_added'	=> date('Y-m-d H:i:s'),
				'date_modified'	=> date('Y-m-d H:i:s'),
				'status'		=> (int)$data['status'],
				'state_id'		=> $data['state_id'] ?? 0,
				'city_id'		=> $data['city_id'] ?? 0
			]);

			$user_id = $this->db->insert_id();
		}

		// self::addSitesByTable('users', [
		// 	'column'	=> 'user_id',
		// 	'id'		=> $user_id,
		// 	'sites'		=> [$site_id]
		// ]);
	}


	public function addSchoolUser($site_id, $data = []) {

		$insert_data = [
			'first_name'	=> $data['name'],
			'password'		=> sha1($data['owner_password']),
			'email'			=> $data['owner_email'],
			'mobile'		=> $data['owner_mobile'],
			'role_id'		=> 9,
			'site_id'		=> (int)$site_id,
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
			'status'		=> (int)$data['status'],
			'state_id'		=> $data['state_id'] ?? 0,
			'city_id'		=> $data['city_id'] ?? 0,
		];

		if(!empty($data['mobile_verified'])){
			$insert_data['mobile_verified'] = 1;
		}

		if(!empty($data['email_verified'])){
			$insert_data['email_verified'] = 1;
		}

		$this->db->insert('users', $insert_data);

		$user_id = $this->db->insert_id();
	}

	public function check_duplication($action = null, $site_code = '', $id = 0) {
		$duplicate_check = $this->db->get_where('site', [
			'site_code'	=> $site_code,
		]);

		if ($action == 'on_create') {
			if ($duplicate_check->num_rows() > 0) {
				return false;
			} else {
				return true;
			}
		} elseif ($action == 'on_update') {
			if ($duplicate_check->num_rows() > 0) {
				if ($duplicate_check->row()->id == $id) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $id);
			$this->db->update('site', [
				'status'	=> (int)$status
			]);
		}

		$this->session->set_flashdata('flash_message', _l('updated_successfully'));
	}

	public function getSitesByTable($table, $data) {
		if ($rows = $this->db->get_where($table . '_to_site', $data)->result_array()) {
			return array_map(function ($item) {
				return $item['site_id'];
			}, $rows);
		}

		return [];
	}

	public function addSitesByTable($table, $data) {
		if (isset($data['column'])) {
			$this->db->delete($table . '_to_site', [$data['column'] => (int)$data['id']]);

			foreach ($data['sites'] ?? [] as $site_id) {
				$this->db->insert($table . '_to_site', [
					$data['column'] => (int)$data['id'],
					'site_id'		=> (int)$site_id
				]);
			}
		}
	}

	public function delete($id = 0) {
		$this->db->where('id', (int)$id);
		$this->db->update('site',  [
			'_deleted'				=> 1,
			'date_deleted'			=> date('Y-m-d H:i:s'),
			'manager_id_deleted'	=> $this->session->userdata('user_id') ?? 0,
		]);

		$this->session->set_flashdata('flash_message', _l('deleted_successfully'));
	}

	public function getByCode($site_code = '', $site_type = '', $state_id = '') {
		$this->db->where('site.site_code', $site_code);

		if (!empty($site_type)) {
			$this->db->where('site.site_type', $site_type);
		}

		if (!empty($state_id)) {
			$this->db->where('site.state_id', $state_id);
		}

		return $this->db->get('site')->row_array();
	}

	public function getByCountryCode($country_code = '', $site_type = 0) {
		$filter_data = [
			'country_code'	=> $country_code
		];

		if (!empty($site_type)) {
			$filter_data['site_type'] = (int)$site_type;
		}

		return $this->db->get_where('site', $filter_data)->row_array();
	}

	public function initConfig($site_id = 0) {
		if ($config = self::get($site_id)) {
			$this->load->model('localisation/Currency_model', 'currency_model');
			$this->load->model('localisation/Country_model', 'country_model');
			$this->load->model('localisation/City_model', 'city_model');

			$currency_info = $this->currency_model->getByCode($config['currency_code']);
			$country_info = $this->country_model->getByCode($config['country_code']);
			$city_info = $this->city_model->get($config['city_id']);

			$this->config->set_item('site_id', $config['id']);
			$this->config->set_item('site_code', $config['site_code']);
			$this->config->set_item('site_type', $config['site_type']);
			$this->config->set_item('site_discount_code', $config['discount_code']);
			$this->config->set_item('site_parent_id', $config['parent_id']);
			$this->config->set_item('site_name', $config['name']);
			$this->config->set_item('site_image', $config['image']);
			$this->config->set_item('site_sms_gateway', $config['sms_gateway']);
			$this->config->set_item('site_payment_gateway', $config['payment_gateway']);
			$this->config->set_item('site_mobile_length', $config['mobile_length']);
			$this->config->set_item('site_country_code', $config['country_code']);
			$this->config->set_item('site_country', $country_info['name'] ?? '');
			$this->config->set_item('site_country_id', $country_info['id'] ?? '');
			$this->config->set_item('site_state', $city_info['state'] ?? '');
			$this->config->set_item('site_city', $city_info['name'] ?? '');
			$this->config->set_item('site_tel_code', $country_info['tel_code'] ?? '');
			$this->config->set_item('site_email', $config['owner_email']);
			$this->config->set_item('site_mobile', $config['owner_mobile']);
			$this->config->set_item('site_currency_id', $currency_info['id'] ?? '');
			$this->config->set_item('site_currency_code', $config['currency_code']);
			$this->config->set_item('site_currency_symbol', $currency_info['symbol'] ?? '');
			$this->config->set_item('site_timezone', $config['timezone']);
			$this->config->set_item('site_address', $config['address']);
			$this->config->set_item('site_email_alert', $config['email_alert']);
			$this->config->set_item('site_tax', $config['tax']);
			$this->config->set_item('site_license_total', $config['license_total']);
			$this->config->set_item('site_tax_text', $config['tax_text']);
			$this->config->set_item('site_can_add_site', $config['can_add_site']);
			$this->config->set_item('site_base_price', $config['base_price']);
			$this->config->set_item('site_ebook_price', $config['ebook_price']);
			$this->config->set_item('site_audio_book_price', $config['audio_book_price']);
			$this->config->set_item('site_black_white_price', $config['black_white_price']);
			$this->config->set_item('site_free_page_limit', $config['free_page_limit']);
			$this->config->set_item('site_price_per_page', $config['price_per_page']);
			$this->config->set_item('site_black_white_price_per_page', $config['black_white_price_per_page']);
			$this->config->set_item('site_hard_cover_price', $config['hard_cover_price']);
			$this->config->set_item('site_paperback_price', $config['paperback_price']);
			$this->config->set_item('site_publishing_limit', $config['publishing_limit']);
		}
	}

	public function getSiteByName($site_name = '', $site_type = '') {
		$this->db->select('
			currency_code,
			base_price, id,
			price_per_page,
			free_page_limit,
			hard_cover_price,
			site.country_code
		');

		$this->db->where('site.name', $site_name);
		$this->db->where('site.parent_id', 0);

		if (!empty($site_type)) {
			$this->db->where('site.site_type', $site_type);
		}

		$row = $this->db->get('site')->row_array();

		unset($row['owner_password']);

		return $row;
	}

	public function getSiteByDiscountCode($discount_code = '') {
		return $this->db->get_where('site', [
			'discount_code'	=> $discount_code
		])->row_array();
	}

	public function getTotalLicenseUsedBySite($parent_id = 0) {
		return ($this->db->select_sum('license_total')
			->where('parent_id', (int)$parent_id)
			->get('site')->row_array()['license_total'] ?? 0) +
			$this->enrol_model->getEnrolCountBySiteId($this->config->item('site_id'));
	}

	public function getSchoolBySiteId($site_id = 0, $site_type = 0) {
		$this->db->select('site.id,
			site.parent_id, site.name,
			site.owner_name,
			site.owner_email as email,
			site.owner_mobile as mobile,
			site.site_code, site.site_type,
			site.state_id,
			site.city_id,
			state.name as state,
			city.name as city,
			site.verified,
			site.authorized_person,
			site.image
		');

		if (!empty($site_type)) {
			$this->db->where('site.site_type', $site_type);
		}

		$this->db->where('site.id', (int)$site_id);
		$this->db->where('site._deleted', 0);

		$this->db->join('state', 'state.id = site.state_id', 'left');
		$this->db->join('city', 'city.id = site.city_id', 'left');

		return $this->db->get('site')->row_array();
	}

	public function getSiteByWhere($where) {
		$this->db->select('*');
		$this->db->where($where);
		return $this->db->get('site')->row_array();
	}
}
