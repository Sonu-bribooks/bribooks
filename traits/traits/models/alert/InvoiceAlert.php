<?php defined('BASEPATH') OR exit('No direct script access allowed');

load_trait('invoice');
use Dompdf\Dompdf;

trait InvoiceAlert {
	use InvoiceDownload;

	public function invoiceSubscription($id) {
		self::cron($id, 'invoiceSubscriptionCron');
	}

	public function invoiceSubscriptionCron($id = 0) {
		$this->load->model('subscription/UserSubscription_model', 'user_subscription_model');
		$this->load->model('subscription/SubscriptionPlan_model', 'subscription_plan_model');
		$this->load->model('subscription/SubscriptionOrder_model', 'subscription_order_model');

		$this->load->model('competition/Competition_model', 'competition_model');
		$this->load->model('competition/CompetitionOrder_model', 'competition_order_model');

		if (
			($info = $this->user_subscription_model->get($id)) &&
			($user_info = $this->student_model->get($info['user_id'])) &&
			($subscription_info = $this->subscription_plan_model->get($info['subscription_plan_id']))
		) {
			$data['title']			= sprintf(_li('Congratulations, %s, You Are Now A BriBooks Plus Author'), $user_info['first_name'] . ' ' . $user_info['last_name']);
			$data['heading']		= sprintf(_li('BriBooks: You %s plan'), $subscription_info['name']);

			$data['content']		= $this->load->view('common/mail/part/invoice_subscription', [
				'info'			=> [
					'start_date'	=> date('M d, Y H:i:s', strtotime($info['start_date'])),
					'end_date'		=> date('M d, Y', strtotime($info['end_date'])),
				],
				'user'			=> [
					'name'		=> $user_info['first_name'] . ' ' . $user_info['last_name']
				],
				'plan'			=> [
					'name'			=> $subscription_info['name'],
					'currency'		=> $subscription_info['symbol'],
					'currency_code'	=> $subscription_info['code'],
					'price'			=> $subscription_info['price'],
					'description'	=> $subscription_info['description']
				],
			], true);

			log_kb([
				'invoiceSubscriptionCron' => $data['content']
			]);

			// BB+ PDF TEMPLATE START

			// $tc_html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/bb_plus_tc', [], true);

			// $dompdf = new Dompdf();
			// $dompdf->loadHtml(preg_replace('/>\s+</', "><", $tc_html));
			// $dompdf->set_option('isJavascriptEnabled', true);
			// $dompdf->set_option('isRemoteEnabled', true);
			// $dompdf->set_option('isHtml5ParserEnabled', true);
			// $dompdf->setPaper('A4', 'potrait');
			// $dompdf->render();
			// $tc_file = 'uploads/custom_theme_document/custom_cover_tc_'.$info['subscription_plan_id'].'.pdf';
			// $output = $dompdf->output();
			// file_put_contents(FCPATH.$tc_file, $output);

			// BB+ PDF TEMPLATE END

			$message 				= $this->load->view('common/mail/templates/3/general', $data, true);

			$attachment 			= FCPATH . 'uploads/pdfs/sub_invoice_' . $info['id'] . '.pdf';

			// $attachment 			= [
			// 	FCPATH . 'uploads/pdfs/sub_invoice_' . $info['id'] . '.pdf',
			// 	FCPATH . $tc_file
			// ];
			file_put_contents($attachment, self::_subscriptionInvoice($info['id'], true));

			self::email(
				$user_info['email'],
				$data['title'],
				$message,
				[],
				(ENVIRONMENT === 'production') ? $this->admin_emails : [],
				$attachment
			);

			$mobile = $user_info['mobile'];
			if(ENVIRONMENT != 'production') {
				$mobile = '919935343128';
			}

			// self::_sendWhatsappText(
			// 	$mobile,
			// 	[
			// 		'template'		=> '893413432936278',
			// 		'parameters'	=> [
			// 			$user_info['first_name'] . ' ' . $user_info['last_name'],
			// 			$subscription_info['price'],
			// 			date('M d, Y H:i:s', strtotime($info['start_date']))
			// 		]
			// 	]
			// );

			self::sendOnextelWhatsappMessage(
				$mobile,
				[
					'template_id'	=> '01kcrsv03y4xhpm3zncaham1wb',
					'parameters'	=> [
						$user_info['first_name'] . ' ' . $user_info['last_name'],
						$subscription_info['price'],
						date('M d, Y H:i:s', strtotime($info['start_date']))
					]
				]
			);

			unlink($attachment);
		}
	}

	public function invoiceCompetition($id) {
		self::cron($id, 'invoiceCompetitionCron');
	}

	public function invoiceCompetitionCron($id = 0) {
		$this->load->model('subscription/UserSubscription_model', 'user_subscription_model');
		$this->load->model('subscription/SubscriptionPlan_model', 'subscription_plan_model');
		$this->load->model('subscription/SubscriptionOrder_model', 'subscription_order_model');

		$this->load->model('competition/Competition_model', 'competition_model');
		$this->load->model('competition/CompetitionOrder_model', 'competition_order_model');

		if (
			($info = $this->user_subscription_model->get($id)) &&
			($user_info = $this->student_model->get($info['user_id'])) &&
			($subscription_info = $this->subscription_plan_model->get($info['subscription_plan_id']))
		) {
			$data['title']			= sprintf(_li('BriBooks: Your %s details'), $subscription_info['name']);
			$data['heading']		= sprintf(_li('BriBooks: Your %s details'), $subscription_info['name']);

			$data['content']		= $this->load->view('common/mail/part/invoice_competition', [
				'info'			=> [
					'start_date'	=> date('M d, Y'),
				],
				'user'			=> [
					'name'		=> $user_info['first_name']
				],
				'plan'			=> [
					'name'			=> $subscription_info['name'],
					'currency'		=> $subscription_info['symbol'],
					'price'			=> $subscription_info['price'],
					'description'	=> $subscription_info['description']
				],
			], true);

			$message 				= $this->load->view('common/mail/templates/2/general', $data, true);

			$attachment 			= FCPATH . 'uploads/pdfs/com_invoice_' . $info['id'] . '.pdf';
			file_put_contents($attachment, self::_subscriptionInvoice($id, true));

			self::email(
				$user_info['email'],
				$data['title'],
				$message,
				[],
				$this->admin_emails,
				$attachment
			);

			unlink($attachment);
		}
	}

	public function invoiceOrder($id) {
		self::cron($id, 'invoiceOrderCron');
	}

	public function invoiceOrderCron($id = 0, $email = true) {
		self::_alertInternalISBNAmazon($id);
		self::cron($id, 'authorRoyaltyCron');

		$this->load->library('Stock_lib', 'stock_lib');
		$email && $this->stock_lib->orderFulfill($id);

		$this->load->model('order/Order_model', 'order_model');
		$this->load->model('address/Address_model', 'address_model');

		if (
			($info = $this->order_model->get($id)) &&
			($user_info = $this->user_model->get($info['user_id']))
		) {
			// $data['title']			= _li('Thank you for purchasing at BriBooks');
			// $data['heading']		= _li('Thank you for purchasing at BriBooks');

			$info['shipping_info'] = json_decode($info['shipping_info'], true);

			$products = $this->order_model->getProducts($id);
			log_kb([
				'invoiceordercron::Product' => [$products]
			]);
			$has_printed_copies = array_filter($products, function($item) {
				$option = json_decode($item['option'], true);
				return (!in_array(mb_strtolower($option['name']), ['ebook', 'audio book']));
			});

			$has_audiobook_copies = array_filter($products, function($item) {
				$option = json_decode($item['option'], true);
				return (in_array(mb_strtolower($option['name']), ['audio book']));
			});

			$has_printed_copies && self::cron($id, 'orderProcessingAlertCron');

			log_kb([
				'invoiceordercron::' => [$has_audiobook_copies, $has_printed_copies]
			]);

			
			// $has_my_order = array_filter($products, function($item) {
			// 	$book_id = $item['product_id'];
			// 	return $book_id == $item['user_id'] ?? 0;
			// });

			$address_info = $this->address_model->getByID($info['address_id']);

			// $data['content']		= $this->load->view('common/mail/part/invoice_order', [
			// 	'products'			=> $products,
			// 	'has_printed_copies'=> $has_printed_copies,
			// 	'has_my_order'		=> $has_my_order,
			// 	'user'				=> [
			// 		'name'			=> $user_info['first_name'],
			// 		'location'		=> $user_info['location']
			// 	],
			// 	'order'				=> $info,
			// 	'address'			=> $address_info,
			// ], true);

			//$message 				= $this->load->view('common/mail/templates/' . (strpos($user_info['source'], 'NYAFIND') !== false ? 3 : 2) . '/general', $data, true);

			// $attachment 			= FCPATH . 'uploads/pdfs/order_invoice_' . $info['id'] . '.pdf';
			// file_put_contents($attachment, self::_orderInvoice($id, true));

			// $email && self::email(
			// 	$user_info['email'],
			// 	$data['title'],
			// 	$message,
			// 	[],
			// 	$this->admin_emails,
			// 	// $attachment
			// );

			//******New code by Sonu****** */

			$email && CI_Events::trigger('order_confirmation_paperback', [
				'order_id'	=> $info['id']
			]);


			if (!empty($has_audiobook_copies)) {
				foreach ($has_audiobook_copies as $value) {
					if (!empty($book_info = $this->book_model->get($value['product_id']))) {

						// $subject 						= _li('Getting_Started_with_Your_Audiobook');

						// $message						= $this->load->view('common/mail/part/audio_purchase_mail', [
						// 	'buyer_name' 	=> ucwords($user_info['first_name']),
						// 	'book_name' 	=> $book_info['name'],
						// 	'audio_book_url' => 'https://www.bribooks.com/audiobookpreview/' . $book_info['slug'],
						// ], true);

						// $this->alert_model->email(
						// 	$user_info['email'],
						// 	$subject,
						// 	$message,
						// 	[],
						// 	[],
						// 	[]
						// );

						// !empty($user_info['mobile']) && self::sendOnextelWhatsappMessage(
						// 	$user_info['mobile'],
						// 	[
						// 		'template_id'	=> '01kevknzzwkjd5xac4n4cq10wa',
						// 		'parameters'	=> [
						// 			ucwords($user_info['first_name']),
						// 			'https://www.bribooks.com/audiobookpreview/' . $book_info['slug'],
						// 			$book_info['name'],
						// 		]
						// 	]
						// );

						//******New code by Sonu****** */
						$email && CI_Events::trigger('order_confirmation_audiobook', [
							'order_id'	=> $info['id'],
							'book_name' 	=> $book_info['name'],
							'audio_book_url' => 'https://www.bribooks.com/audiobookpreview/' . $book_info['slug'],
						]);
					}
				}
			}


			// unlink($attachment);

			return $has_printed_copies ? self::_generateShiprocketOrder(
				$info,
				$user_info,
				$address_info
			) : [];
		}
	}

	private function _generateShiprocketOrder(
		$order_info = [],
		$user_info = [],
		$address_info = []
	) {
		if (ENVIRONMENT !== 'production') return;
		if (get_settings('bb_shipping')) return;

		$this->load->library('couriers/Shiprocket_lib');

		// Step 1. Ship Order (Create Order in shiprocket)
		$products = $this->order_model->getProducts($order_info['id']);

		$products = array_filter($products, function($item) {
			$option = json_decode($item['option'], true);
			return mb_strtolower($option['name']) != 'ebook';
		});

		if (empty($products)) return;

		$response = $this->shiprocket_lib->bookOrder(array_merge($order_info, [
			'products' 	=> $products,
			'address'	=> $address_info,
			'userData'	=> $user_info,
		]));

		log_kb([
			'_generateShiprocketOrder::' => !empty($response)
				? $response
				: ($this->shiprocket_lib->error ?? ''),
		]);

		if (!empty($response->order_id) && !empty($response->shipment_id)) {
			$this->order_model->edit($order_info['id'], [
				'shipping_status' 			=> 1,
				'shipping_tracking_info' 	=> json_encode((array)$response),
			]);
		}

		return !empty($response)
			? $response
			: ($this->shiprocket_lib->error ?? '');
	}

	private function _alertInternalISBNAmazon($order_id = 0) {
		log_kb([
			'_alertInternalISBNAmazon::order_id:: ' => $order_id
		]);

		$this->load->model('book/Bookstore_model', 'bookstore_model');
		$this->load->model('book/Book_model', 'book_model');
		$this->load->model('order/OrderProduct_model', 'order_product_model');

		$matrix = [
			'india' => [
				'isbn' 		=> 49,
				'amazon' 	=> 69,
			],
			'global' => [
				'isbn' 		=> 69,
				'amazon' 	=> 99,
			],
		];

		if (
			$order_id &&
			!empty($order_info = $this->order_model->get($order_id)) &&
			!empty($results = $this->order_product_model->getOrderProductByOrderId($order_id))
		) {
			foreach ($results as $item) {
				if (empty($book_info = $this->bookstore_model->getByBookId($item['product_id']))) continue;
				$info = $this->book_model->get($item['product_id']);

				if (!empty($info['isbn']) && $book_info['sold'] >= $matrix[$book_info['location']]['isbn'] && $book_info['sold'] < $matrix[$book_info['location']]['amazon']) return;
				if (!empty($info['amazon_url']) && $book_info['sold'] >= $matrix[$book_info['location']]['amazon']) return;

				$book_info['location'] = mb_strtolower($book_info['location']) === 'india' ? 'india' : 'global';

				if ($book_info['sold'] < $matrix[$book_info['location']]['isbn']) continue;

				$book_id 	= $book_info['book_id'];
				$type 		= 'isbn';

				if ($book_info['sold'] >= $matrix[$book_info['location']]['amazon']) {
					$type 	= 'amazon';

					if (empty($this->cron_model->getByCode(sprintf('alertInternalISBNAmazonCron_isbn_%s', $book_id)))) {
						$type 	= 'isbn_amazon';
					}
				}

				$code = sprintf('alertInternalISBNAmazonCron_%s_%s', $type, $book_id);

				if (!empty($this->cron_model->getByCode($code))) continue;

				$this->cron_model->add([
					'code'			=> $code,
					'action'		=> 'alert_model->alertInternalISBNAmazonCron',
					'data'			=> [[
						'order_id' 	=> $order_id,
						'book_id' 	=> $book_id,
						'type' 		=> $type,
						'location' 	=> $book_info['location'],
					]],
					'site_id'		=> 1,
					'alert_date'	=> date('Y-m-d H:i:00', strtotime(sprintf('+%d minutes', ENVIRONMENT === 'production' ? 1 : 1))),
				]);
			}
		}
	}

	
}
