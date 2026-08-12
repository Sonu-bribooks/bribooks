<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/pricing/BasePrice.php';

final class Landing_lib extends BasePrice {
	protected $courier;
	protected $mode = 'fwd';

	public function __construct($params = []) {
		$this->CI = &get_instance();
		$this->load = $this->CI->load;

		$this->load->model('shipping/Plans_model');

		$this->plans_model = $this->CI->Plans_model;

		$this->courier 	= $params['courier_id'] ?? '';
		$this->mode 	= $params['mode'] ?? 'fwd';

		$this->loadPricing();
	}

	public function loadPricing() {
		$landing = $this->plans_model->getLandingByCourierAndType($this->courier, $this->mode);

		if (empty($landing)) return false;

		$this->setZone1Price($landing->zone1);
		$this->setZone2Price($landing->zone2);
		$this->setZone3Price($landing->zone3);
		$this->setZone4Price($landing->zone4);
		$this->setZone5Price($landing->zone5);
		$this->setMinCod($landing->min_cod);
		$this->setCodPercent($landing->cod_percent);
	}
}
