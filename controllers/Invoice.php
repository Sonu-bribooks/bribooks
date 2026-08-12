<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

load_trait('invoice');

class Invoice extends CI_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('order/Payment_model', 'payment_model');
		$this->load->model('address/Address_model', 'address_model');
		$this->load->model('user/Student_model', 'student_model');
		$this->load->model('common/Site_model', 'site_model');
		
		$this->load->model('competition/CompetitionOrder_model', 'competition_order_model');

		$this->load->model('subscription/SubscriptionPlan_model', 'subscription_plan_model');
		$this->load->model('subscription/SubscriptionOrder_model', 'subscription_order_model');
		$this->load->model('subscription/SubscriptionPayment_model', 'subscription_payment_model');
		$this->load->model('subscription/UserSubscription_model', 'user_subscription_model');

		if (!$this->session->userdata('user_id') || $this->session->userdata('user_role_id') != 2) {
			// show_404();
		}
	}

	use InvoiceDownload;

	public function download($id = 0, $type = 'order') {
		if ($type === 'subscription') {
			self::_subscriptionInvoice($id);
		} else {
			self::_orderInvoice($id);
		}
	}

	public function downloadsg($id = 0, $type = 'order') {
		self::_orderInvoicesg($id);
	}
}
