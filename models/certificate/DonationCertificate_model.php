<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DonationCertificate_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($donation_certificate_id = 0) {
		$this->db->select('donation_certificates.*');

		$this->db->where('donation_certificates.id', (int)$donation_certificate_id);
		$this->db->where('donation_certificates._deleted', 0);

		return $this->db->get('donation_certificates')->row_array();
	}

	public function getByUserId($user_id = 0, $book_id = 0) {
		$this->db->select('donation_certificates.*');

		$this->db->where('donation_certificates.user_id', (int)$user_id);

		$this->db->where('donation_certificates.status', 1);
		$this->db->where('donation_certificates._deleted', 0);

		return $this->db->get('donation_certificates')->result_array();
	}

	public function get_all($data = []) {
		$this->db->select('donation_certificates.*');

		if (isset($data['user_id'])) {
			$this->db->where('donation_certificates.user_id', (int)$data['user_id']);
		}

		if (isset($data['user_credit_request_id'])) {
			$this->db->where('donation_certificates.user_credit_request_id', $data['user_credit_request_id']);
		}

		if (isset($data['donation_type'])) {
			$this->db->where('donation_certificates.donation_type', $data['donation_type']);
		}

		if (isset($data['status'])) {
			$this->db->where('donation_certificates.status', (int)$data['status']);
		}

		$this->db->where('donation_certificates._deleted', 0);

		$this->db->from('donation_certificates');

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
			'donation_certificates.name',
			'donation_certificates.status',
			'donation_certificates.date_added',
			'donation_certificates.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'donation_certificates.date_added';
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
		$this->db->insert('donation_certificates', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$donation_certificate_id = $this->db->insert_id();

		return $donation_certificate_id;
	}

	public function edit($donation_certificate_id = 0, $data = []) {
		$this->db->where('id', (int)$donation_certificate_id);
		$this->db->update('donation_certificates', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function delete($donation_certificate_id = 0) {
		$this->db->where('id', (int)$donation_certificate_id);
		$this->db->update('donation_certificates',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}

	public function getByName($name = '') {
		$this->db->select('donation_certificates.*');
		$this->db->where('donation_certificates.name', $name);
		return $this->db->get('donation_certificates')->row_array();
	}
}
