<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/pricing/BasePrice.php';

final class PlanPrice_lib extends BasePrice {
	protected $plan;
	protected $plan_id;
	protected $courier;
	protected $base;
	protected $mode = 'fwd';

	public function __construct($params = []) {
		$this->CI = &get_instance();
		$this->load = $this->CI->load;

		$this->load->model('shipping/Plans_model');

		$this->plans_model = $this->CI->Plans_model;

		$this->plan_id 	= $params['plan_id'] ?? '';
		$this->courier 	= $params['courier_id'] ?? '';
		$this->mode 	= $params['mode'] ?? 'fwd';

		$this->load->library('pricing/Landing_lib', [
			'courier_id'	=> $this->courier,
			'mode'			=> $this->mode,
		], 'landing_price');

		$this->base 	= $this->CI->landing_price;

		self::_setPlan();
		$this->getPlanPrice();
	}

	private function _setPlan() {
		if (!$this->plan_id)
			return false;

		$plan = $this->plans_model->getByID($this->plan_id);

		if (empty($plan))
			return false;

		$this->plan = $plan;
	}

	public function getPlanPrice() {
		if (!$this->plan_id) return false;

		$price = $this->plans_model->getPlanDetailsByCourierAndType($this->plan_id, $this->courier, $this->mode);

		if (empty($price)) return false;

		$this->setZone1Price($price->zone1);
		$this->setZone2Price($price->zone2);
		$this->setZone3Price($price->zone3);
		$this->setZone4Price($price->zone4);
		$this->setZone5Price($price->zone5);
		$this->setMinCod($price->min_cod);
		$this->setCodPercent($price->cod_percent);
	}

	public function getZone1Margin() {
		return $this->zone1_price;
	}

	public function getZone2Margin() {
		return $this->zone2_price;
	}

	public function getZone3Margin() {
		return $this->zone3_price;
	}

	public function getZone4Margin() {
		return $this->zone4_price;
	}

	public function getZone5Margin() {
		return $this->zone5_price;
	}

	public function getCodMargin() {
		return $this->min_cod;
	}

	public function getCodPercentMargin() {
		return $this->cod_percent;
	}

	public function getZone1Price() {
		return round($this->base->getZone1Price() + $this->zone1_price, 2);
	}

	public function getZone2Price() {
		return round($this->base->getZone2Price() + $this->zone2_price, 2);
	}

	public function getZone3Price() {
		return round($this->base->getZone3Price() + $this->zone3_price, 2);
	}

	public function getZone4Price() {
		return round($this->base->getZone4Price() + $this->zone4_price, 2);
	}

	public function getZone5Price() {
		return round($this->base->getZone5Price() + $this->zone5_price, 2);
	}

	public function getMinCod() {
		return round($this->base->getMinCod() + $this->min_cod, 2);
	}

	public function getCodPercent() {
		return round($this->base->getCodPercent() + $this->cod_percent, 2);
	}
}
