<?php defined('BASEPATH') or exit('No direct script access allowed');

trait OrderProcessAlert {
	public function outForDeliverOrderCron($id = '') {
		$order_info = $this->order_model->get($id);

		if (empty($order_info)) return;

		$user_info = $this->student_model->get($order_info['user_id']);

		!empty($user_info['mobile']) && self::sendOnextelWhatsappMessage(
			$user_info['mobile'],
			[
				'template_id'	=> '01kerfw4jvbd0shz4df9xxtb2m',
				'parameters'	=> [
					trim($user_info['first_name'] . ' ' . $user_info['last_name']),
				]
			],
		);
	}

	public function orderProcessingAlertCron($id = 0) {
		$order_info = $this->order_model->get($id);
		$user_info = $this->student_model->get($order_info['user_id']);

		if (empty($user_info['mobile'])) return;

		$products = $this->order_model->getProducts($order_info['id']);

		// self::sms($user_info['mobile'], self::formatMessage('sms_order', [
		// 	'name'				=> $user_info['first_name'],
		// 	'book_name'			=> $products[0]['name'] . count($products) > 1
		// 		? ('+' . (count($products) - 1))
		// 		: '',
		// 	'order_id'			=> $order_info['order_code'],
		// ]));

		// $this->load->model('order/OrderHistory_model', 'order_history_model');
		// $this->order_history_model->add([
		// 	'order_id' 		=> $order_info['id'],
		// 	'description' 	=> _li('Your order has been received'),
		// 	'status' 		=> $order_info['status'],
		// ]);

		// printed pages alert after 60m
		$this->cron_model->add([
			'code'			=> 'orderPagesPrintedAlertCron_' . $id,
			'action'		=> 'alert_model->orderPagesPrintedAlertCron',
			'data'			=> [$id],
			'alert_date'	=> date('Y-m-d H:i:s', strtotime(sprintf('+%s minutes',
				ENVIRONMENT === 'production' ? 60 : 2
			))),
		]);
	}

	public function orderPagesPrintedAlertCron($id = 0) {
		$order_info = $this->order_model->get($id);
		$user_info = $this->student_model->get($order_info['user_id']);

		if (!in_array($order_info['status'], [1, 2, 8])) return;

		if (empty($user_info['mobile'])) return;

		$products = $this->order_model->getProducts($order_info['id']);

		// self::sms($user_info['mobile'], self::formatMessage('sms_pages_printed', [
		// 	'name'				=> $user_info['first_name'],
		// 	'order_id'			=> $order_info['order_code'],
		// ]));

		$this->load->model('order/OrderHistory_model', 'order_history_model');
		$this->order_history_model->add([
			'order_id' 		=> $order_info['id'],
			'description' 	=> _li('The book pages have been sent to print.'),
			'status' 		=> $order_info['status'],
		]);

		$this->cron_model->add([
			'code'			=> 'orderBookInFoldingAlertCron_' . $id,
			'action'		=> 'alert_model->orderBookInFoldingAlertCron',
			'data'			=> [$id],
			'alert_date'	=> date('Y-m-d H:i:s', strtotime(sprintf('+%s minutes',
				ENVIRONMENT === 'production' ? (96 * 60) : 2
			))),
		]);
	}

	public function orderBookInFoldingAlertCron($id = 0) {
		$order_info = $this->order_model->get($id);
		$user_info = $this->student_model->get($order_info['user_id']);

		if (!in_array($order_info['status'], [1, 2, 8])) return;

		if (empty($user_info['mobile'])) return;

		$products = $this->order_model->getProducts($order_info['id']);

		// self::sms($user_info['mobile'], self::formatMessage('sms_book_in_folding', [
		// 	'name'				=> $user_info['first_name'],
		// 	'order_id'			=> $order_info['order_code'],
		// ]));

		$this->load->model('order/OrderHistory_model', 'order_history_model');
		$this->order_history_model->add([
			'order_id' 		=> $order_info['id'],
			'description' 	=> _li('The book pages have been sorted and folded.'),
			'status' 		=> $order_info['status'],
		]);

		$this->cron_model->add([
			'code'			=> 'orderBookInBindingAlertCron_' . $id,
			'action'		=> 'alert_model->orderBookInBindingAlertCron',
			'data'			=> [$id],
			'alert_date'	=> date('Y-m-d H:i:s', strtotime(sprintf('+%s minutes',
				ENVIRONMENT === 'production' ? (96 * 60) : 2
			))),
		]);
	}

	public function orderBookInBindingAlertCron($id = 0) {
		$order_info = $this->order_model->get($id);
		$user_info = $this->student_model->get($order_info['user_id']);

		if (!in_array($order_info['status'], [1, 2, 8])) return;

		if (empty($user_info['mobile'])) return;

		$products = $this->order_model->getProducts($order_info['id']);

		// self::sms($user_info['mobile'], self::formatMessage('sms_book_in_binding', [
		// 	'name'				=> $user_info['first_name'],
		// 	'order_id'			=> $order_info['order_code'],
		// ]));

		$this->load->model('order/OrderHistory_model', 'order_history_model');
		$this->order_history_model->add([
			'order_id' 		=> $order_info['id'],
			'description' 	=> _li('The binding process of the book is completed'),
			'status' 		=> $order_info['status'],
		]);

		$this->cron_model->add([
			'code'			=> 'orderBookInQAAlertCron_' . $id,
			'action'		=> 'alert_model->orderBookInQAAlertCron',
			'data'			=> [$id],
			'alert_date'	=> date('Y-m-d H:i:s', strtotime(sprintf('+%s minutes',
				ENVIRONMENT === 'production' ? (120 * 60) : 3
			))),
		]);
	}

	public function orderBookInQAAlertCron($id = 0) {
		$order_info = $this->order_model->get($id);
		$user_info = $this->student_model->get($order_info['user_id']);

		if (!in_array($order_info['status'], [1, 2, 8])) return;

		if (empty($user_info['mobile'])) return;

		$products = $this->order_model->getProducts($order_info['id']);

		// self::sms($user_info['mobile'], self::formatMessage('sms_book_in_qa', [
		// 	'name'				=> $user_info['first_name'],
		// 	'order_id'			=> $order_info['order_code'],
		// ]));

		$this->load->model('order/OrderHistory_model', 'order_history_model');
		$this->order_history_model->add([
			'order_id' 		=> $order_info['id'],
			'description' 	=> _li('The final quality check process of the book is completed'),
			'status' 		=> $order_info['status'],
		]);
	}

	public function orderBookReadyToShipCron($id = 0) {
		$order_info = $this->order_model->get($id);
		$user_info = $this->student_model->get($order_info['user_id']);

		if (empty($user_info['mobile'])) return;

		$products = $this->order_model->getProducts($order_info['id']);

		// self::sms($user_info['mobile'], self::formatMessage('sms_book_ready_to_ship', [
		// 	'name'				=> $user_info['first_name'],
		// 	'order_id'			=> $order_info['order_code'],
		// ]));
	}

	public function deliveredOrderCron($id = '') {
		$order_info = $this->order_model->get($id);
		if (empty($order_info)) return;

		$user_info = $this->student_model->get($order_info['user_id']);
		if (empty($user_info['email'])) return;

		$message = 'Dear '.$user_info['first_name'].',<br /><br />
Congratulations! Your order ('.$order_info['order_code'].') has been successfully delivered to your registered address. We sincerely hope that you enjoy reading them.<br /><br />
In case you face any issues with the quality of the books, please feel free to report them to us within 24 hours of delivery at support@bribooks.com. Our team will be more than happy to assist you and resolve the issue at the earliest.<br /><br />
Please note that any issue raised after 24 hours of delivery will not be liable to BriBooks.<br /><br />
Thank you for choosing BriBooks. We look forward to serving you again.<br /><br />
Best regards,<br />
BriBooks Team';

		self::email(
			$user_info['email'],
			'BriBooks: Your order has been successfully delivered to your Registered Address',
			$message,
			[],
			[]
		);
	}

	public function updateOrderStatusCron() {
		return;
		$this->load->model('order/OrderHistory_model', 'order_history_model');

		$sort_orders = ['ASC','DESC'];

		$filter_data = [];
		$filter_data['start'] = 0;
		$filter_data['limit'] = 500;
		$filter_data['sort'] = 'order.id';
		$filter_data['order'] = $sort_orders[array_rand($sort_orders, 1)];
		$filter_data['ne_like_status'] = [4,15,91,92,93];
		$filter_data['shipping_status'] = 1;
		$filter_data['ne_like_shipping_info'] = 'BriBooks Flat Shipping';
		$filter_data['ne_like_shipping_tracking_info'] = '"awb_code":""';
		$filter_data['startdate'] = date('Y-m-d', strtotime('-180 days'));
		$filter_data['enddate'] = date('Y-m-d', strtotime('-1 days'));

		$results = $this->order_model->get_all($filter_data);

		$this->load->library('couriers/shiprocket_lib');

		foreach ($results['rows'] ?? [] as $key => $order_info) {
			$shippment_id = json_decode($order_info['shipping_tracking_info']);

			$response = $this->shiprocket_lib->getAwbCode($shippment_id->shipment_id);

			if(empty($data = $response->tracking_data->shipment_track[0]))
				continue;

			if ($response->tracking_data->shipment_status == 7) {
				$shipment_info = json_decode($order_info['shipping_tracking_info'], true);

				$shipment_info['awb_code'] = $data->awb_code;
				$shipment_info['status'] = $data->current_status ?? $shipment_info['status'];
				$shipment_info['courier_name'] = $data->courier_name ?? $shipment_info['courier_name'];

				$this->order_model->edit($order_info['id'], [
					'status' 				=> 4,
					'shipping_tracking_info'=> json_encode($shipment_info),
					'date_completed'		=> date('Y-m-d H:i:s')
				]);

				$this->order_history_model->add([
					'order_id' 		=> $order_info['id'],
					'description' 	=> _order_history(4),
					'status' 		=> 4
				]);

				$this->load->library('Royalty_lib', 'royalty_lib');
				$this->royalty_lib->generateCredit($order_info['id']);
			} else if (strtolower($data->current_status) === 'rto delivered') {
				$this->order_model->edit($order_info['id'], [
					'status' => 15,
				]);
			}
		}
	}

	public function cancelOrderCron($order_id = '') {
		/*if (empty($order_id)) return;

		$order_info = $this->order_model->get($order_id);
		if (empty($order_info)) return;

		$user_info = $this->student_model->get($order_info['user_id']);
		if (empty($user_info['email'])) return;

		$mobile = $user_info['mobile'];
		$email = $user_info['email'];

		// Whatsapp to Customer
		self::_sendWhatsappText(
			$mobile,
			[
				'template'		=> '3760787810822982',
				'parameters'	=> [
					$user_info['first_name'],
					$order_info['order_code'],
					$order_info['currency_symbol'],
					$order_info['total'],
					$order_info['order_code'],
					date('M j, Y', strtotime($order_info['date_added']))
				]
			]
		);

		// Mail to Customer
		$message = 'Dear '.$user_info['first_name'].',<br /><br />
Greetings from BriBooks!<br /><br />
We understand your concern regarding the refund of your order ('.$order_info['order_code'].') and regret the inconvenience caused to you.<br /><br />
Please be informed that the refund amount of '.$order_info['currency_symbol'].$order_info['total'].' against the order ID ('.$order_info['order_code'].') has been successfully processed to your registered account number on '.date('M j, Y', strtotime($order_info['date_added'])).', It may take 5-7 working days to reflect in your source account. If you did not receive the amount, kindly write to us at <a href="mailto:support@bribooks.com">support@bribooks.com</a> or contact your bank.<br /><br />
In case of any queries, please don’t hesitate to contact us at <a href="tel:18003099917">1800 309 9917</a>.<br />
We would be more than delighted to extend our assistance in the future.<br /><br />
Best regards,<br />
BriBooks Team';

		self::email(
			$email,
			'Notification of Successful Processing of Your Refund Amount',
			$message,
			[],
			(ENVIRONMENT == 'production') ? ['rahul@bribooks.com'] : []
		);

		// Mail to Finance
		$message = 'Dear Coordinator,<br /><br />
Please note that the Refund Amount of '.$order_info['currency_symbol'].$order_info['total'].' has been successfully transferred to the Order ID ('.$order_info['order_code'].') on '.date('M j, Y', strtotime($order_info['date_added'])).'.<br /><br />
The payment ID '.$order_info['ext_transaction_id'].' now stands canceled and the amount has been transferred to the registered account number.<br /><br />
Thanks!<br />
Team BriBooks';

		self::email(
			'accounts@bribooks.com',
			'Disbursement of Refund Amount for Order ID ('.$order_info['order_code'].'): Successfully Completed',
			$message,
			[],
			[]
		);*/
	}

	public function refundOrderCron($order_id = '') {
		if (empty($order_id)) return;

		$order_info = $this->order_model->get($order_id);
		if (empty($order_info)) return;

		$result = '';
		if(strtolower($order_info['provider']) == 'razorpay') {
			$result = $this->order_model->refundRazorpayOrder($order_id);
		} else if(strtolower($order_info['provider']) == 'stripe') {
			$result = $this->order_model->refundStripeOrder($order_id);
		}

		if (empty($result)) return;

		$user_info = $this->student_model->get($order_info['user_id']);
		if (empty($user_info['email'])) return;

		$mobile 	= $user_info['mobile'];
		$email 		= $user_info['email'];

		// Whatsapp to Customer
		self::_sendWhatsappText(
			$mobile,
			[
				'template'		=> '3760787810822982',
				'parameters'	=> [
					$user_info['first_name'],
					$order_info['order_code'],
					$order_info['currency_symbol'],
					$order_info['total'],
					$order_info['order_code'],
					date('M j, Y')
				]
			]
		);

		// Mail to Customer
		$message = 'Dear '.$user_info['first_name'].',<br /><br />
Greetings from BriBooks!<br /><br />
We understand your concern regarding the refund of your order ('.$order_info['order_code'].') and regret the inconvenience caused to you.<br /><br />
Please be informed that the refund amount of '.$order_info['currency_symbol'].$order_info['total'].' against the order ID ('.$order_info['order_code'].') has been successfully processed to your registered account number on '.date('M j, Y').', It may take 5-7 working days to reflect in your source account. If you did not receive the amount, kindly write to us at <a href="mailto:support@bribooks.com">support@bribooks.com</a> or contact your bank.<br /><br />
In case of any queries, please don’t hesitate to contact us at <a href="tel:18003099917">1800 309 9917</a>.<br />
We would be more than delighted to extend our assistance in the future.<br /><br />
Best regards,<br />
BriBooks Team';

		self::email(
			$email,
			'Notification of Successful Processing of Your Refund Amount',
			$message,
			[],
			(ENVIRONMENT == 'production') ? ['adarsh@bribooks.com','rahul@bribooks.com'] : []
		);

		// Mail to Finance
		$message = 'Dear Coordinator,<br /><br />
Please note that the Refund Amount of '.$order_info['currency_symbol'].$order_info['total'].' has been successfully transferred to the Order ID ('.$order_info['order_code'].') on '.date('M j, Y').'.<br /><br />
The payment ID '.$order_info['ext_transaction_id'].' now stands canceled and the amount has been transferred to the registered account number.<br /><br />
Thanks!<br />
Team BriBooks';

		self::email(
			'accounts@bribooks.com',
			'Disbursement of Refund Amount for Order ID ('.$order_info['order_code'].'): Successfully Completed',
			$message,
			[],
			[]
		);
	}

	public function updateEbookOrderStatusCron() {
		$this->load->model('order/OrderHistory_model', 'order_history_model');
		$this->load->library('Royalty_lib', 'royalty_lib');

		$results = $this->db->query("
			SELECT `order`.id
			FROM `order_product`
			JOIN `order` on `order`.id=`order_product`.order_id
			WHERE `order`.order_type=3 AND `order`.status IN (21) AND `order`._deleted=0
			GROUP BY `order`.id"
		)->result_array();

		foreach($results ?? [] as $order_info) {
			$this->order_model->edit($order_info['id'], [
				'status' 				=> 4,
				'date_completed'		=> date('Y-m-d H:i:s')
			]);
			$this->order_history_model->add([
				'order_id' 		=> $order_info['id'],
				'description' 	=> _order_history(4),
				'status' 		=> 4
			]);

			$this->royalty_lib->generateCredit($order_info['id']);
		}

		$this->cron_model->add([
			'code'			=> 'updateEbookOrderStatusCron',
			'site_id'		=> 1,
			'action'		=> 'alert_model->updateEbookOrderStatusCron',
			'data'			=> [count($results)],
			'alert_date'	=> date('Y-m-d H:i:s', strtotime(sprintf('+%s minutes', 180))),
		]);
	}

	public function updateKDPOrderStatusCron() {
		return;

		$this->load->library('Royalty_lib', 'royalty_lib');

		$results = $this->db->query("
			SELECT `order`.id
			FROM `order_product`
			JOIN `order` on `order`.id=`order_product`.order_id
			WHERE `order`.order_type=1 AND `order`.status IN (4) AND `order`._deleted=0
			AND `order`.shipping_info LIKE '%BriBooks Amazon Shipping%'
			GROUP BY `order`.id"
		)->result_array();

		foreach($results ?? [] as $order_info) {
			$this->royalty_lib->generateCredit($order_info['id']);
		}

		$this->cron_model->add([
			'code'			=> 'updateKDPOrderStatusCron',
			'site_id'		=> 1,
			'action'		=> 'alert_model->updateKDPOrderStatusCron',
			'data'			=> [count($results)],
			'alert_date'	=> date('Y-m-d H:i:s', strtotime(sprintf('+%s days', 1))),
		]);
	}

	public function deliveredSchoolOrderCron($order_id = 0) {
		$this->load->model('school/SchoolOrder_model', 'school_order_model');
		$this->load->model('event/EventTemplate_model', 'event_template_model');

		if (empty($order_info = $this->school_order_model->get($order_id) ?? []) || empty($order_info['date_completed'] || ($order_info['status'] != 4))) return;

		if (empty($school_info = $this->school_model->get($order_info['school_id']) ?? [])) return;

		if (empty($order_info['event_id']) || empty($event_info = $this->event_model->get($order_info['event_id']) ?? [])) return;

		if (empty($template_info = $this->event_template_model->getByTemplateId($order_info['event_id'], 'deliverd_school_order'))) return;

		$data['title']		  	= self::formatEventEmailSubject('deliverd_school_order', $order_info['event_id'], [
			'school_name' => $school_info['name']
		]);
		$data['heading']		= '';
		$data['subheading']	 	= '';
		$data['subheading']		= '';
		$data['content']		= self::formatEventEmailMessage('deliverd_school_order', [
			'datetime'	  			=> date('Y-m-d', strtotime($order_info['date_completed']))
		], $event_info['id']);
		$data['link']		   	= '';
		$data['link_text']	  	= '';
		$message				= $this->load->view('common/mail/templates/site/general', $data, true);

		if(empty($data['title']) || empty($data['content'])) return;

		$school_info['owner_email'] && self::email(
			$school_info['owner_email'],
			$data['title'],
			$message,
			[],
			(ENVIRONMENT == 'production') ? ['communication@bribooks.com'] : []
		);

		if (!empty($school_info['owner_mobile']) && !empty($template_info['whatsapp_template_id']) && !empty($template_info['whatsapp_message'])) {
			self::_sendWhatsappText(
				$school_info['owner_mobile'],
				[
					'template'		=> $template_info['whatsapp_template_id'],
					'parameters'	=> self::_formatMarketingWhatsappMessage($template_info['whatsapp_message'], [
						'date'	  			=> date('M j, Y', strtotime($order_info['date_completed']))
					]),
				]
			);
		}
	}
}
