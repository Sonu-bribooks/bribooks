<?php defined('BASEPATH') OR exit('No direct script access allowed');

class PaymentAsyncJob_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function processOrder($order_id = 0) {
		// move to async
		$this->load->library('Event_lib');
		$this->event_lib->enrolOrder($order_id);

		$this->load->library('Ranking_lib');
		$this->ranking_lib->updateRank($order_id);

		$this->load->library('Bookstore_lib');
		$this->bookstore_lib->updateBookstoreSold($order_id);

		$this->load->library('HallOfFame_lib');
		$this->halloffame_lib->enrolToHallOfFame($order_id);

		$this->load->library('Royalty_lib');
		$this->royalty_lib->applyRoyalty($order_id);

		$this->load->library('Subscription_lib');
		$this->subscription_lib->useShippingCredit($order_id);

		$this->load->library('Dropshipper_lib');
		$this->dropshipper_lib->assignDropshipper($order_id);

		self::confirmOrder($order_id);
		// move to async

		self::_generateCertificateAndMedallion($order_id);
	}

	private function confirmOrder($order_id = 0) {
		if (empty($order_id)) {
			return ;
		}

		$this->load->model('order/OrderPrivy_model');
		$this->load->model('order/Order_model');
		$this->load->model('common/Cron_model');

		$order_privy_value = get_settings('order_privy');

		$order_info = $this->Order_model->get($order_id);

		if (!empty($order_info) && !empty($order_privy_value)) {
			$amount = $order_info['total'] * get_exchange_rate($order_info['currency_code']);

			log_kb([
				'ConfirmOrder::' => $amount
			]);

			if ($amount >= $order_privy_value) {
				if (empty($this->OrderPrivy_model->get_all([
					'order_id' => $order_id
				])['rows'] ?? [])) {
					$this->OrderPrivy_model->add([
						'order_id' => (int)$order_id
					]);
				}

				$this->Cron_model->add([
					'code'			=> 'orderPrivyAlert_' . $order_id,
					'action'		=> 'alert_model->orderPrivyAlert',
					'data'			=> [$order_id],
					'site_id'		=> 1,
					'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
				]);
			}
		}
	}

	private function _generateCertificateAndMedallion($order_id = 0) {
		log_kb([
			'generateCertificateAndMedallion' => $order_id
		]);

		$this->load->library('GenericCertificate_lib');
		$this->genericcertificate_lib->createCertificate($order_id);
	}
}
