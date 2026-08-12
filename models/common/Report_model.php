<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Report_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($lead_id = 0) {
		$this->db->select('lead.id, lead.name, lead.age, lead.parent_name, lead.mobile, lead.email, lead.course_id, lead.mode, lead.schedule, lead.confirmed_schedule, lead.status, lead.utm_source, lead.utm_medium, lead.utm_campaign, lead.date_added');

		if ($lead_id > 0) {
			$this->db->where('id', $lead_id);
		}

		return $this->db->get('lead');
	}

	public function get_all($data = []) {
		$this->db->select('lead.id, lead.name, lead.age, lead.parent_name, lead.mobile, lead.email, lead.course_id, course.title, course.short_description, lead.mode, lead.schedule, lead.confirmed_schedule, lead.is_converted, lead.status, lead.utm_source, lead.utm_medium, lead.utm_campaign, lead.date_added');

		if (isset($data['status'])) {
			$this->db->where('lead.status', (int)$data['status']);
		}

		$this->db->join('course', 'course.id = lead.course_id', 'left');

		if (!empty($data['order']) && !empty($data['sort']) && in_array($data['order'], ['ASC', 'DESC'])) {
			$this->db->order_by($data['sort'], $data['order']);
		} else {
			$this->db->order_by('lead.date_added', 'DESC');
		}

		if (!empty($data['limit']) && empty($data['offset'])) {
			$this->db->limit((int)$data['limit']);
		}

		if (!empty($data['limit']) && !empty($data['offset']) && $data['offset'] > 0) {
			$this->db->limit((int)$data['offset'], (int)$data['limit']);
		}

		return $this->db->get('lead');
	}

	public function get_count($type = '') {
		$date_from = date('Y-m-d 00:00:00');
		$date_to = date('Y-m-d 23:59:59');

		$subject = date('d-M-Y');

		switch ($type) {
			case 'monthly':
				$date_from = date('Y-m-d 00:00:00', strtotime('-1 month'));
				$date_to = date('Y-m-d 23:59:59');

				$subject = date('d-M-Y', strtotime('-1 month')) . ' - ' . date('d-M-Y');
				break;

			case 'weekly':
				$date_from = date('Y-m-d 00:00:00', strtotime('-7 days'));
				$date_to = date('Y-m-d 23:59:59');

				$subject = date('d-M-Y', strtotime('-7 days')) . ' - ' . date('d-M-Y');
				break;

			default:
				break;
		}

		$return = array();
		$return['verified_leads'] = 0;
		$return['unverified_leads'] = 0;
		$return['demo_scheduled'] = 0;
		$return['revenue_collected'] = 0;
		$return['subject'] = $subject;

		/* verified_leads */
		$this->db->select('COUNT(id) AS count');
		$this->db->where("date_added BETWEEN '$date_from' AND '$date_to'");
		$this->db->where('mobile_verified=1');
		$res = $this->db->get('lead');
		if ($res->num_rows() == 1) {
			$return['verified_leads'] = $res->row(0)->count;
		}

		/* unverified_leads */
		$this->db->select('COUNT(id) AS count');
		$this->db->where("date_added BETWEEN '$date_from' AND '$date_to'");
		$this->db->where('mobile_verified=0');
		$res = $this->db->get('lead');
		if ($res->num_rows() == 1) {
			$return['unverified_leads'] = $res->row(0)->count;
		}

		/* demo_scheduled */
		$this->db->select('COUNT(id) AS count');
		$this->db->where("date_added BETWEEN '$date_from' AND '$date_to'");
		$this->db->where('schedule_id>0');
		$res = $this->db->get('lead');
		if ($res->num_rows() == 1) {
			$return['demo_scheduled'] = $res->row(0)->count;
		}

		/* revenue_collected */
		$date_from = strtotime($date_from);
		$date_to = strtotime($date_to);

		$this->db->select('SUM(amount) AS amount');
		$this->db->where("date_added BETWEEN '$date_from' AND '$date_to'");
		$res = $this->db->get('payment');
		if ($res->num_rows() == 1) {
			$return['revenue_collected'] = $res->row(0)->amount;
		}

		return $return;
	}

	public function get_details($type = '', $telecaller_id = '') {
		$date_from = date('Y-m-d 00:00:00');
		$date_to = date('Y-m-d 23:59:59');

		$subject = date('d-M-Y');

		switch ($type) {
			case 'monthly':
				$date_from = date('Y-m-d 00:00:00', strtotime('-1 month'));
				$date_to = date('Y-m-d 23:59:59');

				$subject = date('d-M-Y', strtotime('-1 month')) . ' - ' . date('d-M-Y');
				break;

			case 'weekly':
				$date_from = date('Y-m-d 00:00:00', strtotime('-7 days'));
				$date_to = date('Y-m-d 23:59:59');

				$subject = date('d-M-Y', strtotime('-7 days')) . ' - ' . date('d-M-Y');
				break;

			default:
				break;
		}

		$lead_ids = '';
		$leadArr = array();
		$return = array();
		$return['leads_assigned'] = 0;
		$return['demo_scheduled'] = 0;
		$return['dnp_reported'] = 0;
		$return['revenue_generated'] = 0;
		$return['subject'] = $subject;

		/* lead_ids */
		$this->db->simple_query('SET SESSION group_concat_max_len=10000');

		$this->db->select('GROUP_CONCAT(lead.id SEPARATOR ",") AS lead_ids');
        $this->db->join('users', 'lead.telecaller_id=users.id AND users.role_id=4 AND users.status=1');
		$this->db->where("lead.date_added BETWEEN '$date_from' AND '$date_to'");
		if($telecaller_id) { $this->db->where('lead.telecaller_id', $telecaller_id); }
		else { $this->db->where('lead.telecaller_id>0'); }
		$res = $this->db->get('lead');
		if ($res->num_rows() == 1) {
			$lead_ids = $res->row(0)->lead_ids;
			if($lead_ids) {
				$leadArr = explode(",", $lead_ids);
			}
		}

		/* leads_assigned */
		$this->db->select('COUNT(lead.id) AS count');
        $this->db->join('users', 'lead.telecaller_id=users.id AND users.role_id=4 AND users.status=1');
		$this->db->where("lead.date_added BETWEEN '$date_from' AND '$date_to'");
		if($telecaller_id) { $this->db->where('lead.telecaller_id', $telecaller_id); }
		else { $this->db->where('lead.telecaller_id>0'); }
		$res = $this->db->get('lead');
		if ($res->num_rows() == 1) {
			$return['leads_assigned'] = (@$res->row(0)->count) ? $res->row(0)->count : 0;
		}

		/* demo_scheduled */
		$this->db->select('COUNT(lead.id) AS count');
        $this->db->join('users', 'lead.telecaller_id=users.id AND users.role_id=4 AND users.status=1');
		$this->db->where("lead.date_added BETWEEN '$date_from' AND '$date_to'");
		$this->db->where('lead.schedule_id>0');
		if($telecaller_id) { $this->db->where('lead.telecaller_id', $telecaller_id); }
		$res = $this->db->get('lead');
		if ($res->num_rows() == 1) {
			$return['demo_scheduled'] = (@$res->row(0)->count) ? $res->row(0)->count : 0;
		}

		/* dnp_reported */
		$count_dnp = 0;
		foreach ($leadArr as $lead_id) {
			$this->db->select('id, status');
			$this->db->where('lead_id', $lead_id);
            $this->db->order_by('id', 'desc');
            $this->db->limit(1);
			$res = $this->db->get('lead_status');
			if ($res->num_rows() == 1 && $res->row(0)->status == 'not_responding') {
				$count_dnp++;
				$return['dnp_reported'] = $count_dnp;
			}
		}

		/* revenue_generated */
		foreach ($leadArr as $lead_id) {
			$this->db->select('payment.id, payment.order_id, payment.amount');
        	$this->db->join('payment', 'order.id=payment.order_id');
			$this->db->where('order.lead_id', $lead_id);
			$this->db->where('order.status=1');
			$res = $this->db->get('order');
			// pr($this->db->last_query());
			if ($res->num_rows() == 1) {
				$return['revenue_generated'] += $res->row(0)->amount;
			}
		}

		return $return;
	}
}
