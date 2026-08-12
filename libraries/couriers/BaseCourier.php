<?php defined('BASEPATH') or exit('No direct script access allowed');

abstract class BaseCourier {
	protected $api_url;
	public $error;

	abstract protected function getRates($data = [], $type = '');
	abstract protected function bookOrder($order = []);
	abstract protected function generateAWB($params = []);
	abstract protected function generatePickup($shipment_id = false);
	abstract protected function generateLabel($shipment_ids = false);
	abstract protected function generateInvoice($order_ids = false);
	abstract protected function generateManifests($order_ids = false);
	abstract protected function cancelOrder($order_ids = false);
	abstract protected function cancelShipment($awbs = false);
	abstract protected function fetchAWB($order_id = false);
	abstract protected function trackingDeatil($shipment_id = false);
	abstract protected function trackingUrl($awb_number = false);
	abstract protected function _curl($endpoint = '', $data = NULL, $method = 'POST', $token = TRUE);
}
