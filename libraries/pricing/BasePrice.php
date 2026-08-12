<?php defined('BASEPATH') or exit('No direct script access allowed');

class BasePrice {
	protected $zone1_price = 0;
	protected $zone2_price = 0;
	protected $zone3_price = 0;
	protected $zone4_price = 0;
	protected $zone5_price = 0;
	protected $min_cod = 0;
	protected $cod_percent = 0;

	public function getZone1Price() {
		return $this->zone1_price;
	}

	public function setZone1Price($value = 0) {
		$this->zone1_price = $value;
		return $this;
	}

	public function getZone2Price() {
		return $this->zone2_price;
	}

	public function setZone2Price($value = 0) {
		$this->zone2_price = $value;
		return $this;
	}

	public function getZone3Price() {
		return $this->zone3_price;
	}

	public function setZone3Price($value = 0) {
		$this->zone3_price = $value;
		return $this;
	}

	public function getZone4Price() {
		return $this->zone4_price;
	}

	public function setZone4Price($value = 0) {
		$this->zone4_price = $value;
		return $this;
	}

	public function getZone5Price() {
		return $this->zone5_price;
	}

	public function setZone5Price($value = 0) {
		$this->zone5_price = $value;
		return $this;
	}

	public function getMinCod() {
		return $this->min_cod;
	}

	public function setMinCod($value = 0) {
		$this->min_cod = $value;
		return $this;
	}

	public function getCodPercent() {
		return $this->cod_percent;
	}

	public function setCodPercent($value = 0) {
		$this->cod_percent = $value;
		return $this;
	}

	public function getZonePrice($zone = 1) {
		switch ($zone) {
			case 'z1':
			case 1:
				return round($this->getZone1Price(), 2);
				break;
			case 'z2':
			case 2:
				return round($this->getZone2Price(), 2);
				break;
			case 'z3':
			case 3:
				return round($this->getZone3Price(), 2);
				break;
			case 'z4':
			case 4:
				return round($this->getZone4Price(), 2);
				break;
			case 'z5':
			case 5:
				return round($this->getZone5Price(), 2);
				break;
			default:
				return '0';
		}
	}
}
