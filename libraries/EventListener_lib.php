<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

final class EventListener_lib {
	public static function userLogin(...$params) {
		list($data) = $params;

		log_kb([
			'Event::userLogin' => [$params, $data],
		]);
	}

	public static function userRegister(...$params) {
		list($data) = $params;

		log_kb([
			'Event::userRegister' => [$params, $data]
		]);
	}

	public static function bookCreated(...$params) {
		list($data) = $params;

		$CI =& get_instance();

		$CI->load->model('book/Book_model');
		$CI->load->model('user/Student_model');
		$CI->load->model('event/Event_model');
		$CI->load->model('event/EventUser_model');
		$CI->load->model('common/Cron_model');

		$book_info = $CI->Book_model->get($data['book_id']);

		$user_info = $CI->Student_model->get($book_info['user_id']);

		if (empty($user_info)) return;

		if (!in_array(strtolower($user_info['location']), ['nigeria', 'czechia'])) return;

		$country_code = [
			'india' 	=> 'IN',
			'nigeria' 	=> 'NG',
			'czechia' 	=> 'NG',
		];

		if (empty($active_events = $CI->Event_model->get_all([
			'country_code'		=> $country_code[strtolower($user_info['location'])],
			'event_type_id'		=> 0,
			'is_active_event'	=> 1,
		])['rows'] ?? [])) return;

		foreach ($active_events as $event_info) {
			if (
				!empty($event_info) &&
				$book_info['date_added'] >= $event_info['start_date'] &&
				$event_info['book_writing_end_date'] >= date('Y-m-d H:i:s') &&
				empty($CI->EventUser_model->getEventUserByUserId($event_info['id'], $user_info['id']))
			) {
				$CI->EventUser_model->add([
					'event_id'	=> (int)$event_info['id'],
					'user_id'	=> (int)$user_info['id'],
				]);

				$CI->Cron_model->add([
					'code'			=> 'eventAuthorSignupTnc_' . $user_info['id'],
					'action'		=> 'alert_model->eventAuthorSignupTnc',
					'data'			=> [$user_info['id'], '', $event_info['id']],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime('+2 minutes', strtotime(date('Y-m-d H:i:s')))),
			    ]);
			}
		}
	}

	public static function bookUpdated(...$params) {
		list($data) = $params;

		log_kb([
			'Event::bookCreated' => [$params, $data]
		]);
	}

	public static function bookPublished(...$params) {
		list($data) = $params;

		log_kb([
			'Event::bookPublished' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->library('AutoApproval_lib');
		$CI->load->library('Version_lib');
		$CI->load->library('Ranking_lib');
		$CI->load->library('Vote_lib');
		$CI->load->library('Event_lib');
		$CI->load->library('Bookstore_lib');
		$CI->load->library('SchoolRanking_lib');
		$CI->load->library('TeacherRanking_lib');
		$CI->load->library('Medallion_lib');
		$CI->load->model('common/AsyncTask_model', 'async_task_model');

		$CI->load->model('Alert_model');

		$CI->autoapproval_lib->approveBook($data['book_id']);
		$CI->version_lib->apply($data['book_id']);

		$CI->event_lib->enrolBook($data['book_id']);

		$CI->alert_model->publishBook($data['book_id']);

		$CI->bookstore_lib->enrolBookstore($data['book_id']);

		$CI->ranking_lib->updateBookInfo($data['book_id']);
		$CI->vote_lib->updateBookInfo($data['book_id']);
		$CI->schoolranking_lib->updateRank($data['book_id']);
		$CI->teacherranking_lib->updateRank($data['book_id']);
		$CI->medallion_lib->createMedallion($data['book_id']);

		$CI->async_task_model->add([
			'action'	=> 'EventAsyncJob_model->bookPublished',
			'data' 		=> [$data['book_id']]
		]);
	}

	public static function bookReviewed(...$params) {
		list($data) = $params;

		log_kb([
			'Event::bookReviewed' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('Alert_model');

		$CI->alert_model->bookReviewed($data['review_id'] ?? 0);
	}

	public static function cartCreated(...$params) {
		list($data) = $params;

		log_kb([
			'Event::cartCreated' => [$params, $data]
		]);

		$CI =& get_instance();
		$CI->load->model('Alert_model');
		$CI->alert_model->abandonCart($data['cart_id']);

		$CI->load->model('EventAsyncJob_model', 'event_async_job_model');
		$CI->event_async_job_model->createBookCoupon($data['book_id'], $data['cart_id']);
	}

	public static function cartUpdated(...$params) {
		list($data) = $params;

		log_kb([
			'Event::cartUpdated' => [$params, $data]
		]);

		if (empty($data['cart_id']) || empty($data['quantity'])) return;

		$CI =& get_instance();
		$CI->load->model('Alert_model', 'alert_model');
		$CI->alert_model->abandonCart($data['cart_id']);
	}

	public static function orderCreated(...$params) {
		list($data) = $params;

		log_kb([
			'Event::orderCreated' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('order/Order_model', 'order_model');
		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('address/Address_model', 'address_model');

		$CI->load->model('Alert_model');

		$order_info = $CI->order_model->get($data['order_id']);

		if (empty($order_info)) return;

		$site_id 		= strtolower($order_info['currency_code']) != 'inr' ? 2 : 1;
		$user_info 		= $CI->user_model->get($order_info['user_id']);
		$address_info 	= $CI->address_model->get($order_info['address_id']);

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$data = [
			'buyer_name'	=> $address_info['name'] ?? '',
			'username'		=> sprintf('%s %s', $user_info['first_name'], $user_info['last_name']),
			'order_code'	=> $order_info['order_code']
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $order_info['id'],
			'code'				=> 'order_created',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}

	public static function orderUpdated(...$params) {
		list($data) = $params;

		log_kb([
			'Event::orderUpdated' => [$params, $data]
		]);
	}

	public static function paymentCreated(...$params) {
		list($data) = $params;

		log_kb([
			'Event::paymentCreated' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('common/AsyncTask_model', 'async_task_model');

		$CI->async_task_model->add([
			'action'	=> 'PaymentAsyncJob_model->processOrder',
			'data' 		=> [$data['order_id']]
		]);
	}

	public static function printerAssigned(...$params) {
		list($data) = $params;

		log_kb([
			'Event::printerAssigned' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('order/Order_model', 'order_model');
		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('address/Address_model', 'address_model');

		$CI->load->model('Alert_model');

		$order_info = $CI->order_model->get($data['order_id']);

		if (empty($order_info)) return;

		$site_id 		= strtolower($order_info['currency_code']) != 'inr' ? 2 : 1;
		$user_info 		= $CI->user_model->get($order_info['user_id']);
		$address_info 	= $CI->address_model->get($order_info['address_id']);

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$data = [
			'buyer_name'	=> $address_info['name'] ?? '',
			'username'		=> sprintf('%s %s', $user_info['first_name'], $user_info['last_name']),
			'order_code'	=> $order_info['order_code']
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $order_info['id'],
			'code'				=> 'printer_assigned',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}

	public static function orderMovedToAfs(...$params) {
		list($data) = $params;

		log_kb([
			'Event::orderMovedToAfs' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('order/Order_model', 'order_model');
		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('address/Address_model', 'address_model');

		$CI->load->model('Alert_model');

		$order_info = $CI->order_model->get($data['order_id']);

		if (empty($order_info)) return;

		$site_id 		= strtolower($order_info['currency_code']) != 'inr' ? 2 : 1;
		$user_info 		= $CI->user_model->get($order_info['user_id']);
		$address_info 	= $CI->address_model->get($order_info['address_id']);

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$data = [
			'buyer_name'	=> $address_info['name'] ?? '',
			'username'		=> sprintf('%s %s', $user_info['first_name'], $user_info['last_name']),
			'order_code'	=> $order_info['order_code']
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $order_info['id'],
			'code'				=> 'order_moved_to_afs',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}

	public static function orderReadyToShip(...$params) {
		list($data) = $params;

		log_kb([
			'Event::orderReadyToShip' => [$params, $data]
		]);
	}

	public static function orderShipped(...$params) {
		list($data) = $params;

		log_kb([
			'Event::orderShipped' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('order/Order_model', 'order_model');
		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('address/Address_model', 'address_model');

		$CI->load->model('Alert_model');

		$order_info = $CI->order_model->get($data['order_id']);

		if (empty($order_info)) return;

		$site_id 		= strtolower($order_info['currency_code']) != 'inr' ? 2 : 1;
		$user_info 		= $CI->user_model->get($order_info['user_id']);
		$address_info 	= $CI->address_model->get($order_info['address_id']);

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$data = [
			'buyer_name'	=> $address_info['name'] ?? '',
			'username'		=> sprintf('%s %s', $user_info['first_name'], $user_info['last_name']),
			'order_code'	=> $order_info['order_code']
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $order_info['id'],
			'code'				=> 'order_shipped',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}

	public static function orderOutForDelivery(...$params) {
		list($data) = $params;

		log_kb([
			'Event::orderOutForDelivery' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('order/Order_model', 'order_model');
		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('address/Address_model', 'address_model');

		$CI->load->model('Alert_model');

		$order_info = $CI->order_model->get($data['order_id']);

		if (empty($order_info)) return;

		$site_id 		= strtolower($order_info['currency_code']) != 'inr' ? 2 : 1;
		$user_info 		= $CI->user_model->get($order_info['user_id']);
		$address_info 	= $CI->address_model->get($order_info['address_id']);

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$data = [
			'buyer_name'	=> $address_info['name'] ?? '',
			'username'		=> sprintf('%s %s', $user_info['first_name'], $user_info['last_name']),
			'order_code'	=> $order_info['order_code']
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $order_info['id'],
			'code'				=> 'order_out_for_delivery',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}

	public static function orderUndelivered(...$params) {
		list($data) = $params;

		log_kb([
			'Event::orderUndelivered' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('order/Order_model', 'order_model');
		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('address/Address_model', 'address_model');

		$CI->load->model('Alert_model');

		$order_info = $CI->order_model->get($data['order_id']);

		if (empty($order_info)) return;

		$site_id 		= strtolower($order_info['currency_code']) != 'inr' ? 2 : 1;
		$user_info 		= $CI->user_model->get($order_info['user_id']);
		$address_info 	= $CI->address_model->get($order_info['address_id']);

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$data = [
			'buyer_name'	=> $address_info['name'] ?? '',
			'username'		=> sprintf('%s %s', $user_info['first_name'], $user_info['last_name']),
			'order_code'	=> $order_info['order_code']
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $order_info['id'],
			// 'code'				=> 'order_out_for_delivery',
			'code'				=> 'order_undelivered',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}

	public static function orderDelivered(...$params) {
		list($data) = $params;

		log_kb([
			'Event::orderDelivered' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('order/Order_model', 'order_model');
		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('address/Address_model', 'address_model');

		$CI->load->model('Alert_model');

		$order_info = $CI->order_model->get($data['order_id']);

		if (empty($order_info)) return;

		$site_id 		= strtolower($order_info['currency_code']) != 'inr' ? 2 : 1;
		$user_info 		= $CI->user_model->get($order_info['user_id']);
		$address_info 	= $CI->address_model->get($order_info['address_id']);

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$data = [
			'buyer_name'	=> $address_info['name'] ?? '',
			'username'		=> sprintf('%s %s', $user_info['first_name'], $user_info['last_name']),
			'order_code'	=> $order_info['order_code']
		];

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $order_info['id'],
			'code'				=> 'order_delivered',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}

	public static function orderReturned(...$params) {
		list($data) = $params;

		log_kb([
			'Event::orderReturned' => [$params, $data]
		]);
	}

	public static function orderCanceled(...$params) {
		list($data) = $params;

		log_kb([
			'Event::orderCanceled' => [$params, $data]
		]);
	}

	public static function subscriptionPaymentCreated(...$params) {
		list($data) = $params;

		log_kb([
			'Event::subscriptionPaymentCreated' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->library('Subscription_lib');
		$CI->subscription_lib->addShippingCredit($data['order_id']);
	}

	public static function accessLog(...$params) {
		list($data) = $params;

		log_kb([
			'Event::accessLog' => [$params, $data]
		]);

		if (empty($data['module'])) return;

		$CI =& get_instance();

		$CI->load->model('common/AccessLog_model');
		$CI->load->library('user_agent');

		if ($info = $CI->AccessLog_model->get_all([
			'module'	=> $data['module'],
			'user_id'	=> (int)$CI->session->userdata('user_id'),
			'ip'		=> $CI->input->ip_address(),
			'date_added'=> date('Y-m-d'),
		])['rows'][0] ?? []) {
			$CI->AccessLog_model->edit($info['id'], [
				'module'	=> $data['module'],
				'user_id'	=> (int)$CI->session->userdata('user_id'),
				'browser'	=> !empty($CI->input->post('app_os')) ? (!empty($CI->input->post('is_tablet')) ? 'tablet' : 'mobile') : $CI->agent->browser(),
				'platform'	=> !empty($CI->input->post('app_os')) ? $CI->input->post('app_os') : $CI->agent->platform(),
				'ip'		=> $CI->input->ip_address(),
				'agent'		=> $CI->agent->agent_string(),
			]);
		} else {
			$CI->AccessLog_model->add([
				'module'	=> $data['module'],
				'user_id'	=> (int)$CI->session->userdata('user_id'),
				'browser'	=> !empty($CI->input->post('app_os')) ? (!empty($CI->input->post('is_tablet')) ? 'tablet' : 'mobile') : $CI->agent->browser(),
				'platform'	=> !empty($CI->input->post('app_os')) ? $CI->input->post('app_os') : $CI->agent->platform(),
				'ip'		=> $CI->input->ip_address(),
				'agent'		=> $CI->agent->agent_string(),
			]);
		}
	}

	private function confirmOrder($order_id = 0) {
		if (empty($order_id)) {
			return ;
		}

		$CI =& get_instance();
		$CI->load->model('order/OrderPrivy_model');
		$CI->load->model('order/Order_model');
		$CI->load->model('common/Cron_model');

		$order_privy_value = get_settings('order_privy');

		$order_info = $CI->Order_model->get($order_id);

		if (!empty($order_info) && !empty($order_privy_value)) {
			$amount = $order_info['total'] * get_exchange_rate($order_info['currency_code']);

			log_kb([
				'Event::convert-amount' => $amount
			]);

			if ($amount >= $order_privy_value) {
				if (empty($CI->OrderPrivy_model->get_all(['order_id' => $order_id])['rows'] ?? [])) {
					$CI->OrderPrivy_model->add(['order_id' => (int)$order_id]);
				}

				$CI->Cron_model->add([
					'code'			=> 'orderPrivyAlert_' . $order_id,
					'action'		=> 'alert_model->orderPrivyAlert',
					'data'			=> [$order_id],
					'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
				]);

			}
		}
	}

	public static function systemAccessLog(...$params) {
		list($data) = $params;

		log_kb([
			'Event::systemAccessLog' => [$params, $data]
		]);

		if (empty($data['method'])) return;

		$CI =& get_instance();

		$CI->load->model('admin/SystemAccessLog_model');
		$CI->load->library('user_agent');

		$CI->SystemAccessLog_model->add([
			'user_id'	=> (int)$CI->session->userdata('user_id'),
			'role_id'	=> (int)$CI->session->userdata('role_id'),
			'method'	=> $data['method'],
			'data'		=> is_array($data) ? json_encode($data) : $data,
			'browser'	=> $CI->agent->browser(),
			'platform'	=> $CI->agent->platform(),
			'ip'		=> $CI->input->ip_address(),
		]);
	}

	public static function orderConfirmationPaperback(...$params) {
		list($data) = $params;

		log_kb([
			'Event::orderConfirmationPaperback' => [$params, $data]
		]);

		$CI =& get_instance();

		$CI->load->model('order/Order_model', 'order_model');
		$CI->load->model('user/User_model', 'user_model');
		$CI->load->model('address/Address_model', 'address_model');

		$CI->load->model('Alert_model');

		$order_info = $CI->order_model->get($data['order_id']);

		if (empty($order_info)) return;

		$site_id 		= strtolower($order_info['currency_code']) != 'inr' ? 2 : 1;
		$user_info 		= $CI->user_model->get($order_info['user_id']);
		$address_info 	= $CI->address_model->get($order_info['address_id']);
		$products 		= $CI->order_model->getProducts($order_info['id']);

		if (empty($user_info['email']) && empty($user_info['mobile'])) return;

		$has_printed_copies = array_filter($products, function($item) {
			$option = json_decode($item['option'], true);
			return (!in_array(mb_strtolower($option['name']), ['ebook', 'audio book']));
		});


		$formatData = self::_formattedInvoiceData($products,$has_printed_copies,$user_info,$order_info,$address_info);
		log_kb([
			'Event::orderConfirmationPaperback::data' => $formatData
		]);
		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $order_info['id'],
			'code'				=> 'order_confirmation_paperback',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $formatData,
		]);
	}

	private static function _formattedInvoiceData($products = [], $has_printed_copies = [], $user_info = [], $order = [], $address = []) {
		$products_html = '';
		$total_copies = 0;

		$CI =& get_instance();
		log_kb([
            'orderConfirmationPaperback::_formattedInvoiceData' => [
                'products' => $products,
                'user_info' => $user_info,
                'order_info' => $order,
				'has_printed_copies' => $has_printed_copies,
            ]
        ]);

		foreach ($products as $index => $item) {

			$option = json_decode($item['option'], true);

			$total_copies += (int) ($item['quantity'] ?? 0);

			$image = $CI->config->item('s3_base_url'). 'public/'. ($item['cover_image'] ?? '');

			$products_html .= '
				<td style="vertical-align: top; padding: 10px;">
					<img
						src="' . $image . '"
						width="100"
						height="140"
						style="margin-left: 30px;"
					/>

					<p style="
						margin-left: 30px;
						margin-top: 0;
						margin-bottom: 0;
						font-size: 12px;
					">
						' . htmlspecialchars($item['name'] ?? '') . '
						<br />
						Version ' . htmlspecialchars($item['version'] ?? '') . '
					</p>

					<p style="
						margin-left: 30px;
						margin-top: 0;
						margin-bottom: 0;
						font-size: 12px;
					">
						' . (int) ($item['quantity'] ?? 0) . ' copies
					</p>

					<p style="
						color: #f99232;
						margin-top: 0;
						font-size: 16px;
						margin-left: 53px;
					">
						' . htmlspecialchars($option['name'] ?? '') . '
					</p>
				</td>
			';

			if (count($products) > 1 && $index < count($products) - 1) {
				$products_html .= '
					<td style="vertical-align: top;">
						<p style="
							font-size: 50px;
							margin-left: 10px;
							margin-top: -20px;
						">
							+
						</p>
					</td>
				';
			}
		}

		$free_book_bundle = '';
		if (!empty($order['credit_discount']) && $order['credit_discount'] > 0) {
			$free_book_bundle = '
				<p style="margin-top: -5px;">
					Free book bundle applied
				</p>
			';
		}

		$track_delivery = '';
		if (!empty($has_printed_copies)) {
			$track_delivery = '
				<div>
					<a
						href="' . USER_URL . 'trackdelivery/' . $order['order_code'] . '"
						style="margin-left: 90px; color: #148108;"
					>
						Track Delivery
					</a>
				</div>
			';
		}

		$address_html = '';

		if (!empty($has_printed_copies)) {
			$address_html = '
				<hr style="width: 90%" />

				<p>
					<b>Address:</b><br />
					' . htmlspecialchars($address['address'] ?? '') . ',
					' . htmlspecialchars($address['landmark'] ?? '') . '<br />

					' . htmlspecialchars($address['city'] ?? '') . ',
					' . htmlspecialchars($address['state'] ?? '') . ',<br />

					' . htmlspecialchars($address['country'] ?? '') . '-
					' . htmlspecialchars($address['zipcode'] ?? '') . '
				</p>
			';
		}

		$delivery_message = '';

		if (!empty($has_printed_copies)) {
			$delivery_message = '
				<p>
					We will be delivering your order in the next
					21 business Days/30 calendar days
				</p>
			';
		}


		return [
			'username'			=> sprintf('%s %s', $user_info['first_name'], $user_info['last_name']),
			'order_code'		=> $order['order_code'],

			'products_html' 	=> $products_html,

			'total_books' 		=> count($products),

			'total_copies'	 	=> $total_copies,

			'currency' 			=> $order['currency_code'] ?? '',

			'total' 			=> $order['total'] ?? 0,

			'shipping_cost' 	=> $order['shipping_cost'] ?? 0,

			'tax' 				=> $order['tax'] ?? 0,

			'free_book_bundle' 	=> $free_book_bundle,

			'track_delivery' 	=> $track_delivery,

			'address_html' 		=> $address_html,

			'delivery_message' 	=> $delivery_message,

			'system_name'       => get_settings('system_name'),

			'current_year'      => date('Y'),
		];
	}

	public static function orderConfirmationAudiobook(...$params) {
		list($data) = $params;

		log_kb([
			'Event::orderConfirmationAudiobook' => [$params, $data]
		]);

		if (empty($data['book_name'])) return;
		if (empty($data['audio_book_url'])) return;

		$CI =& get_instance();

		$CI->load->model('order/Order_model', 'order_model');
		$CI->load->model('user/User_model', 'user_model');

		$CI->load->model('Alert_model');

		$order_info = $CI->order_model->get($data['order_id']);

		if (empty($order_info)) return;

		$site_id 		= strtolower($order_info['currency_code']) != 'inr' ? 2 : 1;
		$user_info 		= $CI->user_model->get($order_info['user_id']);

		$data = [
			'buyer_name'		=> sprintf('%s %s', $user_info['first_name'], $user_info['last_name']),
			'order_code'		=> $order_info['order_code'],
			'book_name'			=> $data['book_name'],
			'audio_book_url'	=> $data['audio_book_url']
		];

		log_kb([
			'Event::orderConfirmationAudiobook::data' => $data
		]);

		$CI->Alert_model->genericMessageTemplate([
			'id'			  	=> $order_info['id'],
			'code'				=> 'order_confirmation_audiobook',
			'site_id'			=> $site_id,
			'email'		   		=> $user_info['email'],
			'mobile'		  	=> $user_info['mobile'],
			'data'				=> $data,
		]);
	}
}
