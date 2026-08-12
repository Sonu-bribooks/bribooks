<?php defined('BASEPATH') or exit('No direct script access allowed');

class Plans_model extends CI_Model {
	public function __construct() {
		parent::__construct();
		$this->table = 'pricing_plans';
		$this->details_table = 'plan_details';
		$this->landing_table = 'landing_price';
		$this->actual_landing_table = 'landing_price_actual';
	}

	public function getByID($id = false) {
		if (!$id)
			return false;

		$this->db->where('id', $id);

		$this->db->limit(1);

		$q = $this->db->get($this->table);

		return $q->row();
	}

	public function getPlanByName($name = false) {
		if (!$name)
			return false;

		$this->db->where('plan_name', $name);

		$this->db->limit(1);

		$q = $this->db->get($this->table);

		return $q->row();
	}

	public function createPlan($save = array()) {
		if (empty($save))
			return false;

		$save['created'] = time();
		$save['modified'] = time();

		$this->db->insert($this->table, $save);
		return $this->db->insert_id();
	}

	public function updatePlan($id = false, $save = array()) {
		if (empty($save) || empty($id))
			return false;

		$save['modified'] = time();

		$this->db->set($save);
		$this->db->where('id', $id);
		$this->db->update($this->table);
		return $this->db->insert_id();
	}

	public function createLandingPrice($save = array()) {
		if (empty($save))
			return false;

		$save['created'] = time();
		$save['modified'] = time();

		$this->db->insert($this->landing_table, $save);
		return $this->db->insert_id();
	}

	public function updateLandingPrice($id = false, $save = array()) {
		if (empty($save) || empty($id))
			return false;

		$save['modified'] = time();

		$this->db->set($save);
		$this->db->where('id', $id);
		$this->db->update($this->landing_table);
		return $this->db->insert_id();
	}

	public function createPrice($save = array()) {
		if (empty($save))
			return false;

		$save['created'] = time();
		$save['modified'] = time();

		$this->db->insert($this->details_table, $save);
		return $this->db->insert_id();
	}

	public function updatePrice($id = false, $save = array()) {
		if (empty($save) || empty($id))
			return false;

		$save['modified'] = time();

		$this->db->set($save);
		$this->db->where('id', $id);
		$this->db->update($this->details_table);
		return $this->db->insert_id();
	}

	public function deletePlan($id = false) {
		if (!$id)
			return false;

		$this->db->where('id', $id);
		$this->db->delete($this->table);

		return true;
	}

	public function deletePrice($id = false) {
		if (!$id)
			return false;

		$this->db->where('id', $id);
		$this->db->delete($this->details_table);

		return true;
	}

	public function getAllPlans() {
		$this->db->order_by('id', 'desc');
		$q = $this->db->get($this->table);
		return $q->result();
	}

	public function getLandingByCourierAndType($courier = false, $type = false) {
		if (!$courier || !$type)
			return false;

		$this->db->where('courier_id', $courier);
		$this->db->where('type', $type);
		$this->db->limit(1);

		$q = $this->db->get($this->landing_table);
		return $q->row();
	}

	public function getAllLandingPrice() {
		$q = $this->db->get($this->landing_table);
		return $q->result();
	}

	public function getPlanDetails($plan_id = false) {
		if (!$plan_id)
			return false;

		$this->db->where('plan_id', $plan_id);

		$q = $this->db->get($this->details_table);
		return $q->result();
	}

	public function getPlanDetailsByCourierAndType($plan_id = false, $courier_id = false, $type = false) {
		if (!$plan_id || !$type)
			return false;

		$this->db->where('plan_id', $plan_id);
		$this->db->where('courier_id', $courier_id);
		$this->db->where('type', $type);

		$this->db->limit(1);

		$q = $this->db->get($this->details_table);
		return $q->row();
	}

	public function getUserCountByPlan() {
		$this->db->select('pricing_plan, count(*) as total');
		$this->db->group_by('pricing_plan');
		$this->db->order_by('total', 'desc');

		$q = $this->db->get('users');
		return $q->result();
	}

	public function getNegativePricingPlans() {
		$this->db->select('');

		$this->db->join('pricing_plans as pp', 'pp.id = pd.plan_id', 'LEFT');
		$q = $this->db->get($this->details_table . ' as pd');
		return $q->result();
	}

	public function getPlanDetailsByPlanCourierTypeAndWeight($plan_id = false, $courier_id = false, $type = false, $courier_type = false, $weight = false) {
		if (!$plan_id || !$type || !$courier_type || !$weight)
			return false;

		$this->db->where('plan_id', $plan_id);
		$this->db->where('courier_id', $courier_id);
		$this->db->where('type', $type);
		$this->db->where('courier_type', $courier_type);
		$this->db->where('weight', $weight);

		$this->db->limit(1);

		$q = $this->db->get($this->details_table);
		return $q->row();
	}

	public function getAllSmartPlans() {
		$this->db->where('plan_type', 'smart');
		$this->db->order_by('plan_name', 'asc');
		$q = $this->db->get($this->table);
		return $q->result();
	}

	public function getCustomPlanByName($name = false) {
		if (!$name)
			return false;

		$this->db->where('plan_name', $name);
		$this->db->where('plan_type', 'smart');
		$this->db->limit(1);
		$q = $this->db->get($this->table);
		return $q->row();
	}

	public function getSmartPlanById($plan_id = false, $status = false) {
		if (!$plan_id)
			return false;

		$this->db->select("CONCAT(courier_type, '_', weight, '_', additional_weight) AS courier_type_weight", FALSE);
		$this->db->where('plan_id', $plan_id);
		if($status != '') { $this->db->where('status', $status); }
		$this->db->group_by(['courier_type','weight','additional_weight']);
		$q = $this->db->get($this->details_table);
		return $q->result();
	}

	public function getSmartPlanDetails($plan_id = false) {
		if (!$plan_id)
			return false;

		$this->db->where('plan_id', $plan_id);
		$this->db->where('courier_id', '0');
		$q = $this->db->get($this->details_table);
		return $q->result();
	}

	public function deleteSmartPrice($id = false) {
		if (!$id)
			return false;

		$this->db->where('plan_id', $id);
		$this->db->where('courier_id', '0');
		$this->db->delete($this->details_table);
		return true;
	}

	public function getActualLandingByCourierAndType($courier = false, $type = false) {
		if (!$courier || !$type)
			return false;

		$this->db->where('courier_id', $courier);
		$this->db->where('type', $type);
		$this->db->limit(1);

		$q = $this->db->get($this->actual_landing_table);
		return $q->row();
	}

	public function createActualLandingPrice($save = array()) {
		if (empty($save))
			return false;

		$save['created'] = time();
		$save['modified'] = time();

		$this->db->insert($this->actual_landing_table, $save);
		return $this->db->insert_id();
	}

	public function updateActualLandingPrice($id = false, $save = array()) {
		if (empty($save) || empty($id))
			return false;

		$save['modified'] = time();

		$this->db->set($save);
		$this->db->where('id', $id);
		$this->db->update($this->actual_landing_table);
		return $this->db->insert_id();
	}

	public function getAllActualLandingPrice() {
		$q = $this->db->get($this->actual_landing_table);
		return $q->result();
	}

	public function getActualLandingByCourierId($courier_id = false) {
		if (!$courier_id)
			return false;

		$this->db->where('courier_id', $courier_id);
		$q = $this->db->get($this->actual_landing_table);
		return $q->result();
	}
}
