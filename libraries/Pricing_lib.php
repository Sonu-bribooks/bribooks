<?php defined('BASEPATH') or exit('No direct script access allowed');

final class Pricing_lib {
	protected $plan = false;
	protected $plan_type = false;
	protected $courier_id = false;
	protected $shipment_id = false;
	protected $courier = false;
	protected $origin = false;
	protected $destination = false;
	protected $type = false;
	protected $cod_amount = false;
	protected $weight = 500;
	protected $length = 10;
	protected $height = 10;
	protected $breadth = 10;
	protected $base_freight = false;
	protected $base_rto_freight = false;
	protected $base_add_weight_freight = false;
	protected $courier_type = false;
	protected $courier_weight = false;
	protected $courier_additional_weight = false;
	protected $courier_volumetric_divisor = false;
	protected $zone = false;

	public function __construct($params = false) {
		$this->CI = &get_instance();
		$this->load = $this->CI->load;

		$this->load->model('shipping/Courier_model');
		$this->load->model('shipping/Plans_model');

		$this->courier_model 	= $this->CI->Courier_model;
		$this->plans_model 		= $this->CI->Plans_model;

		if (!empty($params)) {
			foreach ($params as $key => $param) {
				$this->{$key} = $param;
			}
		}
	}

	public function setPlan($value = false) {
		$plan = $this->plans_model->getPlanByName($value);

		if (empty($plan)) return false;

		$this->plan = $plan->id;
		$this->plan_type = $plan->plan_type;
	}

	public function setCourier($value = false) {
		if (!empty($value)) {
			$this->courier_id = $value;
			$this->courier = json_decode(json_encode($this->courier_model->get($value)));
		}
	}

	public function setShipment($value = false) {
		if (!empty($value)) {
			$this->shipment_id = $value;
		}
	}

	public function setOrigin($value = false) {
		$this->origin = trim($value);
	}

	public function setDestination($value = false) {
		$this->destination = trim($value);
	}

	public function setType($value = false) {
		$this->type = $value;
	}

	public function setAmount($value = false) {
		$this->cod_amount = $value;
	}

	public function setWeight($value = false) {
		if (!empty($value)) {
			$this->weight = $value;
		}
	}

	public function setLength($value = false) {
		if (!empty($value))
			$this->length = $value;
	}

	public function setHeight($value = false) {
		if (!empty($value)) {
			$this->height = $value;
		}
	}

	public function setBreadth($value = false) {
		if (!empty($value)) {
			$this->breadth = $value;
		}
	}

	public function setBaseFreight($value = false) {
		if (!empty($value)) {
			$this->base_freight = $value;
		}
	}

	public function setRtoFreight($value = false) {
		if (!empty($value)) {
			$this->base_rto_freight = $value;
		}
	}

	public function setExtraFreight($value = false) {
		if (!empty($value)) {
			$this->base_add_weight_freight = $value;
		}
	}

	public function setCourierType($value = false) {
		if (!empty($value)) {
			$this->courier_type = $value;
		}
	}

	public function setCourierWeight($value = false) {
		if (!empty($value)) {
			$this->courier_weight = $value;
		}
	}

	public function setCourierAdditionalWeight($value = false) {
		if (!empty($value)) {
			$this->courier_additional_weight = (!empty($value)) ? $value : $this->courier_weight;
		}
	}

	public function setCourierVolumetricDivisor($value = false) {
		if (!empty($value)) {
			$this->courier_volumetric_divisor = (!empty($value)) ? $value : 5000;
		}
	}

	public function setZone($value = false) {
		if (!empty($value)) {
			$this->zone = $value;
		}
	}

	public function getZone() {
		return $this->zone;
	}

	public function calculateZone() {
		$origin 		= $this->origin;
		$destination 	= $this->destination;

		if (!$origin || !$destination) return false;
		if (!is_numeric($origin) || strlen($origin) != 6) return false;
		if (!is_numeric($destination) || strlen($destination) != 6) return false;

		$this->CI->load->config('pincodes');

		$pincode_states 	= $this->CI->config->item('pincode_states');
		$zone5_pincodes 	= $this->CI->config->item('zone_5_pincodes');
		$delhi_ncr_picodes 	= $this->CI->config->item('delhi_ncr_pincodes');
		$metro_pincodes 	= $this->CI->config->item('metro_cities_pincodes');

		$origin_two_digit 	= substr($origin, 0, 2);
		$origin_three_digit = substr($origin, 0, 3);

		$destination_two_digit 		= substr($destination, 0, 2);
		$destination_three_digit	= substr($destination, 0, 3);

		$origin_state 		= isset($pincode_states[$origin_two_digit]) ? $pincode_states[$origin_two_digit] : false;
		$destination_state 	= isset($pincode_states[$destination_two_digit]) ? $pincode_states[$destination_two_digit] : false;

		if (!$origin_state || !$destination_state) return false;

		//check if any pin code is zone 5. Origin or Desitnation is in zone 5
		if (in_array($origin_two_digit, $zone5_pincodes) || in_array($destination_two_digit, $zone5_pincodes))
			return 'z5'; //north east or j&K
		//check if both codes are in delhi ncr
		if (in_array($origin_three_digit, $delhi_ncr_picodes) && in_array($destination_three_digit, $delhi_ncr_picodes))
			return 'z1'; //within same city
		// check if courier is within city
		if ($origin_three_digit == $destination_three_digit)
			return 'z1'; //within city
		// check if courier is within state
		if ($origin_state == $destination_state)
			return 'z2'; //within state
		//check if both pincodes are metro to metro
		if (in_array($origin, $metro_pincodes) && in_array($destination, $metro_pincodes))
			return 'z3'; //metro to metro

		return 'z4'; //rest of india
	}

	public function calculateCost() {
		if (!$this->origin || !$this->destination || !$this->plan)
			return false;

		$this->load->library('pricing/PlanPrice_lib', [
			'plan'			=> $this->plan,
			'courier_id'	=> $this->courier_id,
			'mode'			=> 'fwd',
		], 'fwd_price_plan');

		$this->load->library('pricing/PlanPrice_lib', [
			'plan'			=> $this->plan,
			'courier_id'	=> $this->courier_id,
			'mode'			=> 'rto',
		], 'rto_price_plan');

		$this->load->library('pricing/PlanPrice_lib', [
			'plan'			=> $this->plan,
			'courier_id'	=> $this->courier_id,
			'mode'			=> 'weight',
		], 'add_weight_price_plan');

		$fwd_price = $this->CI->fwd_price_plan;
		$rto_price = $this->CI->rto_price_plan;
		$add_weight_price = $this->CI->add_weight_price_plan;

		if (!$zone = !empty($this->zone) ? $this->zone : $this->calculateZone($this->origin, $this->destination))
			return false;

		if (!empty($this->base_freight)) {
			$base_freight = $this->base_freight;
		} else {
			$base_freight = $fwd_price->getZonePrice($zone); // charge for first weight
		}

		if (!empty($this->base_rto_freight)) {
			$base_rto_freight = $this->base_rto_freight;
		} else {
			$base_rto_freight = $rto_price->getZonePrice($zone);
		}

		if (!empty($this->base_add_weight_freight)) {
			$base_add_weight_freight = $this->base_add_weight_freight;
		} else {
			$base_add_weight_freight = $add_weight_price->getZonePrice($zone);
		}

		$weight = (!empty($this->courier->weight) && (floor($this->weight / $this->courier->weight) >= 1)) ? $this->weight : $this->courier->weight;

		$volumetric_weight = !empty($this->courier->weight) ? ((($this->length * $this->breadth * $this->height) / $this->courier->volumetric_divisor) * 1000) : $this->courier->volumetric_divisor; //weight in grams

		$weight = max($weight, $volumetric_weight);

		$charged_weight = $this->courier->weight;

		$shipping_charges = $base_freight * 1; // charge for first weight

		$total_rto_charges = $base_rto_freight; // rto charge for first weight

		if (!empty($this->courier->weight) && (ceil($weight / $this->courier->weight) > 1)) {
			//apply additional weight charges
			$additional_weight = $weight - $this->courier->weight; // in grams
			$additional_weight_charges = $base_add_weight_freight * ceil($additional_weight / $this->courier->additional_weight);
			$charged_weight = $this->courier->weight + (ceil($additional_weight / $this->courier->additional_weight) * $this->courier->additional_weight);
			$shipping_charges = $shipping_charges + $additional_weight_charges;

			// extra weight rto charge for first weight
			$total_rto_charges = $total_rto_charges + $additional_weight_charges;
		}

		$cod_charges = 0;
		$type = strtolower($this->type);

		if ($type == 'cod') {
			$min_charges = $fwd_price->getMinCod();
			$cod_percentage = $fwd_price->getCodPercent();
			$percentage_charges = round(($this->cod_amount * $cod_percentage) / 100, 2);

			$cod_charges = max($min_charges, $percentage_charges);
		}

		$gst_charges = round(($shipping_charges * SHIPMENT_GST_PERCENT) / 100, 2);

		$total_charges = $shipping_charges + $cod_charges + $gst_charges;

		return [
			'zone' 						=> $zone,
			'base_freight' 				=> $base_freight,
			'base_rto_freight' 			=> $base_rto_freight,
			'base_add_weight_freight' 	=> $base_add_weight_freight,
			'courier_charges' 			=> round($shipping_charges, 2),
			'cod_charges' 				=> round($cod_charges, 2),
			'gst_charges' 				=> round($gst_charges, 2),
			'total' 					=> round($total_charges, 2),
			'total_rto_charges' 		=> round($total_rto_charges, 2),
			'calculated_weight' 		=> $charged_weight,
			'courier_weight' 			=> $this->weight,
			'additional_weight_charges' => round($additional_weight_charges, 2),
		];
	}
}
