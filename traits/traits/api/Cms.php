<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Cms {
	public function getSitemap() {
		$total = $this->db
			->select('count(id) as total')
			->get_where('bookstore', ['status' => 1, '_deleted' => 0])->row()->total;

		$total += (16 - $total % 16);

		$index = 1;

		for ($i = 0; $i < $total; $i += 16) {
			$this->json['sitemaps'][] = [
				'sitemap' => sprintf('sitemap%s.xml', $index)
			];

			$index++;
		}
	}

	public function filterCmsOrder() {
		// if ($this->input->get_request_header('api-key') != 'pushnot55656556ffgf@37%~8*^') return;

		$this->load->model('common/Notification_model', 'notification_model');
		$data = $this->input->post();

		if (!$this->json) {
			if ($this->input->post('user_id')) {
				if (strpos($data['slug'], 'https') !== false) {
					$data['slug'] = substr($data['slug'], strrpos($data['slug'], '/') + 1);
				} else {
					$data['isbn'] = $data['slug'];
					unset($data['slug']);
				}

				log_kb(['Notification::Data:: ' => $data]);
				$this->notification_model->save((int)$this->input->post('user_id') . '_order', $data);

				$this->json['success'] = _l('book_scanned');
			} else {
				$this->json['error'] = _l('invalid_user_id');
			}
		}
	}

	public function filterCmsBook() {
		if (!$this->json) {
			if ($this->input->post('slug')) {
				if (strpos($this->input->post('slug'), 'https') !== false) {
					$filter_data['slug'] = substr($this->input->post('slug'), strrpos($this->input->post('slug'), '/') + 1);
				} else {
					$filter_data['isbn'] = $this->input->post('slug');
					unset($filter_data['slug']);
				}

				if ($book_info = $this->book_version_model->get_all($filter_data)['rows'][0] ?? []) {
					$book = $this->book_model->get($book_info['book_id']);

					if ($this->input->post('rejected')) {
						$printers = array_map(function($item) {
							return [
								'label' => $item['first_name'] . ' ' . $item['last_name'],
								'value' => $item['id'],
							];
						}, $this->student_model->get_by_role_id(12));
					} else {
						$printers = [];
					}

					$versions = [];

					for ($i = 1; $i <= $book['version']; $i++) {
						$versions[] = [
							'label' => $i,
							'value' => $i,
						];
					}

					$this->json['book'] 	= array_merge($book, [
						'versions' 	=> $versions,
						'printers' 	=> $printers,
						'options' 	=> [
							[
								'label' => _l('paperback'),
								'value' => 'paperback',
							],
							[
								'label' => _l('hardcover'),
								'value' => 'hardcover',
							],
						],
						'version'	=> $book_info['version'],
					]);
					$this->json['success'] 	= _l('book_scanned');
				} else {
					$this->json['error'] 	= _l('book_not_found');
				}
			} else {
				$this->json['error'] = _l('invalid_slug');
			}
		}
	}

	public function addCmsBookStock() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);

		$this->form_validation->set_rules('version', _l('version'), 'trim|required|numeric|greater_than_equal_to[1]|less_than_equal_to[100]');
		$this->form_validation->set_rules('quantity', _l('quantity'), 'trim|required|numeric|greater_than_equal_to[1]|less_than_equal_to[2000]');
		$this->form_validation->set_rules('option', _l('option'), 'trim|required|in_list[paperback,hardcover]');
		$this->form_validation->set_rules('action', _l('action'), 'trim|required|in_list[1,2,3]');

		self::_runFormValidation();

		if (!$this->json) {
			if ($this->input->post('user_id')) {
				$this->load->model('book/BookStock_model', 'book_stock_model');
				$this->load->model('book/BookStockLog_model', 'book_stock_log_model');

				if ($stock_info = $this->book_stock_model->get_all([
					'book_id'	=> $this->input->post('book_id'),
					'version'	=> $this->input->post('version'),
					'option'	=> $this->input->post('option'),
				])['rows'][0] ?? []) {
					$this->book_stock_model->edit($stock_info['id'], [
						'quantity'	=> (int)($stock_info['quantity'] + (int)$this->input->post('quantity')),
					]);

					$this->book_stock_log_model->add([
						'manager_id'=> (int)$this->input->post('user_id'),
						'book_id'	=> (int)$this->input->post('book_id'),
						'version'	=> (int)$this->input->post('version'),
						'option'	=> $this->input->post('option'),
						'quantity'	=> (int)$this->input->post('quantity'),
						'status'	=> 0,
					]);

					$this->json['success'] = _l('book_stock_updated');
				} else {
					$this->book_stock_model->add([
						'manager_id'=> (int)$this->input->post('user_id'),
						'book_id'	=> (int)$this->input->post('book_id'),
						'version'	=> (int)$this->input->post('version'),
						'option'	=> $this->input->post('option'),
						'quantity'	=> (int)$this->input->post('quantity'),
					]);

					$this->book_stock_log_model->add([
						'manager_id'=> (int)$this->input->post('user_id'),
						'book_id'	=> (int)$this->input->post('book_id'),
						'version'	=> (int)$this->input->post('version'),
						'option'	=> $this->input->post('option'),
						'quantity'	=> (int)$this->input->post('quantity'),
						'status'	=> 1,
					]);
					$this->json['success'] = _l('book_stock_added');
				}
			} else {
				$this->json['error'] = _l('invalid_user_id');
			}
		}
	}

	public function addCmsRejectedBook() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);
		$this->form_validation->set_rules('printer_id', _l('printer_id'), [
			'trim',
			'required',
			'numeric',
			['printer', [$this->validate_model, 'printer']]
		]);

		$this->form_validation->set_rules('version', _l('version'), 'trim|required|numeric|greater_than_equal_to[1]|less_than_equal_to[100]');
		$this->form_validation->set_rules('quantity', _l('quantity'), 'trim|required|numeric|greater_than_equal_to[1]|less_than_equal_to[2000]');
		$this->form_validation->set_rules('option', _l('option'), 'trim|required|in_list[paperback,hardcover]');
		$this->form_validation->set_rules('comment', _l('comment'), 'trim|required|min_length[3]|max_length[100]');

		self::_runFormValidation();

		if (!$this->json) {
			if ($this->input->post('user_id')) {
				$this->load->model('book/RejectedBook_model', 'rejected_book_model');
				$this->load->model('book/RejectedBookLog_model', 'rejected_book_log_model');

				if ($stock_info = $this->rejected_book_model->get_all([
					'printer_id'=> (int)$this->input->post('printer_id'),
					'book_id'	=> (int)$this->input->post('book_id'),
					'version'	=> (int)$this->input->post('version'),
					'option'	=> $this->input->post('option'),
				])['rows'][0] ?? []) {
					$this->rejected_book_model->edit($stock_info['id'], [
						'quantity'	=> (int)($stock_info['quantity'] + (int)$this->input->post('quantity')),
					]);

					$this->rejected_book_log_model->add([
						'printer_id'=> (int)$this->input->post('printer_id'),
						'manager_id'=> (int)$this->input->post('user_id'),
						'book_id'	=> (int)$this->input->post('book_id'),
						'version'	=> (int)$this->input->post('version'),
						'option'	=> $this->input->post('option'),
						'quantity'	=> (int)$this->input->post('quantity'),
						'status'	=> 0,
						'comment'	=> $this->input->post('comment'),
					]);

					$this->json['success'] = _l('rejected_book_stock_added');
				} else {
					$this->rejected_book_model->add([
						'printer_id'=> (int)$this->input->post('printer_id'),
						'manager_id'=> (int)$this->input->post('user_id'),
						'book_id'	=> (int)$this->input->post('book_id'),
						'version'	=> (int)$this->input->post('version'),
						'option'	=> $this->input->post('option'),
						'quantity'	=> (int)$this->input->post('quantity'),
					]);

					$this->rejected_book_log_model->add([
						'printer_id'=> (int)$this->input->post('printer_id'),
						'manager_id'=> (int)$this->input->post('user_id'),
						'book_id'	=> (int)$this->input->post('book_id'),
						'version'	=> (int)$this->input->post('version'),
						'option'	=> $this->input->post('option'),
						'quantity'	=> (int)$this->input->post('quantity'),
						'status'	=> 1,
						'comment'	=> $this->input->post('comment'),
					]);
					$this->json['success'] = _l('rejected_book_stock_added');
				}
			} else {
				$this->json['error'] = _l('invalid_user_id');
			}
		}
	}

	public function getRegisteredUser() {
		if (!$this->json) {
			if ($qrcode = $this->input->post('qrcode')) {
				if (strpos($qrcode, 'https') !== false) {
					$code = substr($qrcode, strrpos($qrcode, '/') + 1);
				} else {
					$code = $qrcode;
				}

				$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');
				$this->load->model('event/EventSchoolInvite_model', 'event_school_invite_model');
				$this->load->model('book/BookExhibition_model', 'book_exhibition_model');

				log_kb(['getRegisteredUser::Data:: ' => $qrcode]);

				if (strpos($qrcode, 'author_data') && ($info = $this->event_user_invite_model->getByCode($code))) {
					log_kb(['getRegisteredUser::Author:: ' => $info]);

					$book_info = $this->book_model->get($info['book_id']);

					$guest_count = 0;

					if (!empty($info['guest_1_name'])) {
						$guest_count++;
					}

					if (!empty($info['guest__2_name'])) {
						$guest_count++;
					}

					$this->json['data'] = array_merge($info, [
						'author_name'		=> $book_info['author_name'],
						'book_name'			=> $book_info['name'],
						'guest_count'		=> $guest_count,
						'verified_count'	=> $info['guest_count'],
					]);
					$this->json['success'] = _l('user_validated');
				} elseif (strpos($qrcode, 'author_data') && ($info = $this->book_exhibition_model->getByCode($code))) {
					log_kb(['BookExhibition::Author:: ' => $info]);

					$this->load->model('common/InviteSlot_model', 'invite_slot_model');

					$slot_info = $this->invite_slot_model->get($info['slot_id']);

					$this->json['data'] = array_merge($info, [
						'guest_count'		=> (int)$info['guest_count'],
						'author_name'		=> $info['name'],
						'book_name'			=> sprintf('%s - %s', $slot_info['slot_start'], $slot_info['slot_end']),
					]);
					$this->json['success'] = _l('user_validated');
				} elseif (strpos($qrcode, 'school_data') && ($school_info = $this->event_school_invite_model->getByCode($code))) {
					log_kb(['getRegisteredUser::School:: ' => $school_info]);

					$this->json['data'] = array_merge($school_info, [
						'guest_count'		=> 1,
						'verified_count'	=> 1
					]);
					$this->json['success'] = _l('school_validated');
				} elseif (strpos($qrcode, 'teacher_data') && ($teacher_info = $this->event_user_invite_model->getByCode($code))) {
					log_kb(['getRegisteredUser::Teacher:: ' => $teacher_info]);

					$this->json['data'] = array_merge($teacher_info, [
						'guest_count'		=> 1,
						'verified_count'	=> 1
					]);
					$this->json['success'] = _l('teacher_validated');
				} else {
					$this->json['error'] = _l('invalid_data');
				}
			} else {
				$this->json['error'] = _l('invalid_data');
			}
		}
	}

	public function verifyRegisteredUser() {
		if (!$this->json) {
			if (!empty($code = $this->input->post('code'))) {
				$this->load->model('book/BookExhibition_model', 'book_exhibition_model');
				$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');
				$this->load->model('event/EventSchoolInvite_model', 'event_school_invite_model');

				log_kb(['verifyRegisteredUser::Data:: ' => $code]);

				if ($info = $this->event_user_invite_model->getByCode($code)) {
					log_kb(['verifyRegisteredUser::Author:: ' => $info]);

					$save = [
						'guest_count'	=> (int)$this->input->post('guest_count'),
						'verified'		=> 1
					];

					$this->event_user_invite_model->edit($info['id'], $save);

					$user_info = $this->user_model->get($info['user_id']);
					$user_info['event_id'] = $info['event_id'];

					$this->alert_model->verifiedEntryAlert([
						'event_id'		=> $info['event_id'],
						'mobile'		=> $user_info['mobile'],
						'email'			=> $user_info['email'],
						'first_name'	=> $user_info['first_name'],
						'last_name'		=> $user_info['last_name'],
						'type'			=> 'user',
					]);

					$this->json['success'] = _l('user_validated');
				} elseif ($info = $this->book_exhibition_model->getByCode($code)) {
					log_kb(['BookExhibition::Author:: ' => $info]);

					$save = [
						'verified_count'=> (int)$this->input->post('guest_count'),
						'verified'		=> 1
					];

					$this->book_exhibition_model->edit($info['id'], $save);

					$this->json['success'] = _l('user_validated');
				} elseif ($info = $this->event_school_invite_model->getByCode($code)) {
					log_kb(['verifyRegisteredUser::School:: ' => $info]);

					$save = [
						'guest_count'	=> 1,
						'verified'		=> 1
					];

					$this->event_school_invite_model->edit($info['id'], $save);

					$site_info = $this->site_model->get($info['site_id']);

					$this->alert_model->verifiedEntryAlert([
						'event_id'		=> $info['event_id'],
						'email'			=> $site_info['owner_email'],
						'mobile'		=> $site_info['owner_mobile'],
						'first_name'	=> !empty($site_info['owner_name']) ? $site_info['owner_name'] : $site_info['authorized_person'],
						'last_name'		=> '',
						'event_id'		=> $info['event_id'],
						'type'			=> 'school',
					]);

					$this->json['success'] = _l('school_validated');
				} else {
					$this->json['error'] = _l('invalid_data');
				}
			} else {
				$this->json['error'] = _l('invalid_data');
			}
		}
	}

	public function getPrinters() {
		if (!$this->json) {
			$this->json['printers'] = array_map(function($item) {
				return [
					'label' => $item['first_name'] . ' ' . $item['last_name'],
					'value' => $item['id'],
				];
			}, $this->student_model->get_by_role_id(12));

			array_unshift($this->json['printers'], [
				'label'	=> _l('select_printer'),
				'value' => 0,
			]);
		}
	}

	public function getAssignments() {
		$this->form_validation->set_rules('printer_id', _l('printer_id'), [
			'trim',
			'required',
			'numeric',
			['printer', [$this->validate_model, 'printer']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('printer/PrinterAssignment_model', 'printer_assignment_model');
			$this->load->model('printer/PrinterStats_model', 'printer_stats_model');

			$results = $this->printer_assignment_model->get_all([
				'printer_id'	=> (int)$this->input->post('printer_id')
			]);

			foreach ($results['rows'] ?? [] as $key => $result) {
				$printer_info = $this->user_model->get($result['printer_id']);

				$total_count = $this->printer_stats_model->printerStats([
					'assignment_id'		=> $result['id']
				]) ?? 0;

				$new_orders_count = $this->printer_stats_model->printerStats([
					'assignment_id'		=> $result['id'],
					'status'			=> 1,
				]) ?? 0;

				$in_print_orders_count = $this->printer_stats_model->printerStats([
					'assignment_id'		=> $result['id'],
					'status'			=> 2,
				]) ?? 0;

				$verify_orders_count = $this->printer_stats_model->printerStats([
					'assignment_id'		=> $result['id'],
					'status'			=> 4,
				]) ?? 0;

				$printed_orders_count = $this->printer_stats_model->printerStats([
					'assignment_id'		=> $result['id'],
					'status'			=> 3,
				]) ?? 0;

				$qa_qc_lots_info = $this->printer_stats_model->getQaQcAssignCount([
					'assignment_id'		=> $result['id'],
				]);

				$accepted_count = $qa_qc_lots_info['accepted_quantity'] ?? 0;

				$accepted_count += $qa_qc_lots_info['accepted_short_quantity'] ?? 0;

				$rejected_count = $qa_qc_lots_info['rejected_quantity'] ?? 0;

				$balance_count = (int)$total_count-(int)$accepted_count;

				$stats = [
					'total' 	=> [
						'bgcolor'	=> '#6c757d',
						'color'		=> 'white',
						'name'		=> _l('total'),
						'data' 		=> $total_count,
					],
					'new'		=> [
						'bgcolor'	=> '#39afd1',
						'color'		=> 'white',
						'name'		=> _l('new'),
						'data' 		=> $new_orders_count
					],
					'in_print'	=> [
						'bgcolor'	=> '#313a46',
						'color'		=> 'white',
						'name'		=> _l('in_print'),
						'data' 		=> $in_print_orders_count
					],
					'verify'	=> [
						'bgcolor'	=> '#fa5c7c',
						'color'		=> 'white',
						'name'		=> _l('verify'),
						'data' 		=> $verify_orders_count
					],
					'printed'	=> [
						'bgcolor'	=> '#0acf97',
						'color'		=> 'white',
						'name'		=> _l('printed'),
						'data' 		=> $printed_orders_count
					],
					'accepted'	=> [
						'bgcolor'	=> 'rgba(10,207,151,.18)',
						'color'		=> 'black',
						'name'		=> _l('accepted'),
						'data' 		=> $accepted_count
					],
					'rejected'	=> [
						'bgcolor'	=> '#fa5c7c',
						'color'		=> 'white',
						'name'		=> _l('rejected'),
						'data' 		=> $rejected_count
					],
					'balance'	=> [
						'bgcolor'	=> 'rgba(250,92,124,.18)',
						'color'		=> 'black',
						'name'		=> _l('balance'),
						'data' 		=> $balance_count
					],
				];

				$this->json['assignments'][] = [
					'sn'			=> $filter_data['start'] + 1 + $key,
					'id'			=> $result['id'],
					'printer'		=> sprintf('%s %s', $printer_info['first_name'] ?? '',  $printer_info['last_name'] ?? ''),
					'assignment_code'=> $result['code'],
					'stats'			=> $stats,
					'assignment_date'=> formatDate($result['date_added']),
				];
			}
		}
	}

	public function getAssignment() {
		$this->form_validation->set_rules('assignment_id', _l('assignment_id'), [
			'trim',
			'required',
			'numeric',
			['assignment', [$this->validate_model, 'assignment']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('printer/PrinterAssignment_model', 'printer_assignment_model');
			$this->load->model('printer/PrinterStats_model', 'printer_stats_model');

			$result = $this->printer_assignment_model->get($this->input->post('assignment_id'));

			$printer_info = $this->user_model->get($result['printer_id']);

			$total_count = $this->printer_stats_model->printerStats([
				'assignment_id'		=> $result['id']
			]) ?? 0;

			$new_orders_count = $this->printer_stats_model->printerStats([
				'assignment_id'		=> $result['id'],
				'status'			=> 1,
			]) ?? 0;

			$in_print_orders_count = $this->printer_stats_model->printerStats([
				'assignment_id'		=> $result['id'],
				'status'			=> 2,
			]) ?? 0;

			$verify_orders_count = $this->printer_stats_model->printerStats([
				'assignment_id'		=> $result['id'],
				'status'			=> 4,
			]) ?? 0;

			$printed_orders_count = $this->printer_stats_model->printerStats([
				'assignment_id'		=> $result['id'],
				'status'			=> 3,
			]) ?? 0;

			$qa_qc_lots_info = $this->printer_stats_model->getQaQcAssignCount([
				'assignment_id'		=> $result['id'],
			]);

			$accepted_count = $qa_qc_lots_info['accepted_quantity'] ?? 0;

			$accepted_count += $qa_qc_lots_info['accepted_short_quantity'] ?? 0;

			$rejected_count = $qa_qc_lots_info['rejected_quantity'] ?? 0;

			$balance_count = (int)$total_count-(int)$accepted_count;

			$stats = [
				'total' 	=> [
					'bgcolor'	=> '#6c757d',
					'color'		=> 'white',
					'name'		=> _l('total'),
					'data' 		=> $total_count,
				],
				'new'		=> [
					'bgcolor'	=> '#39afd1',
					'color'		=> 'white',
					'name'		=> _l('new'),
					'data' 		=> $new_orders_count
				],
				'in_print'	=> [
					'bgcolor'	=> '#313a46',
					'color'		=> 'white',
					'name'		=> _l('in_print'),
					'data' 		=> $in_print_orders_count
				],
				'verify'	=> [
					'bgcolor'	=> '#fa5c7c',
					'color'		=> 'white',
					'name'		=> _l('verify'),
					'data' 		=> $verify_orders_count
				],
				'printed'	=> [
					'bgcolor'	=> '#0acf97',
					'color'		=> 'white',
					'name'		=> _l('printed'),
					'data' 		=> $printed_orders_count
				],
				'accepted'	=> [
					'bgcolor'	=> 'rgba(10,207,151,.18)',
					'color'		=> 'black',
					'name'		=> _l('accepted'),
					'data' 		=> $accepted_count
				],
				'rejected'	=> [
					'bgcolor'	=> '#fa5c7c',
					'color'		=> 'white',
					'name'		=> _l('rejected'),
					'data' 		=> $rejected_count
				],
				'balance'	=> [
					'bgcolor'	=> 'rgba(250,92,124,.18)',
					'color'		=> 'black',
					'name'		=> _l('balance'),
					'data' 		=> $balance_count
				],
			];

			$this->json['assignment'] = [
				'id'			=> $result['id'],
				'printer'		=> sprintf('%s %s', $printer_info['first_name'] ?? '',  $printer_info['last_name'] ?? ''),
				'assignment_code'=> $result['code'],
				'stats'			=> $stats,
				'assignment_date'=> formatDate($result['date_added']),
			];
		}
	}

	public function filterAssignmentBook() {
		$this->form_validation->set_rules('assignment_id', _l('assignment_id'), [
			'trim',
			'required',
			'numeric',
			['assignment', [$this->validate_model, 'assignment']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if ($this->input->post('slug')) {
				if (strpos($this->input->post('slug'), 'https') !== false) {
					$filter_data['slug'] = substr($this->input->post('slug'), strrpos($this->input->post('slug'), '/') + 1);
				} else {
					$filter_data['isbn'] = $this->input->post('slug');
					unset($filter_data['slug']);
				}

				if ($book_info = $this->book_version_model->get_all($filter_data)['rows'][0] ?? []) {
					$book = $this->book_model->get($book_info['book_id']);

					$this->load->model('printer/PrinterStats_model', 'printer_stats_model');

					$results = $this->printer_stats_model->printerAssignData([
						'book_id'		=> $book_info['book_id'],
						'assignment_id'	=> $this->input->post('assignment_id'),
					])['rows'] ?? [];

					$versions = $options = $total = [];

					foreach ($results as $item) {
						$option = json_decode($item['option'], true);

						$versions[] = [
							'label' => $item['version'],
							'value' => $item['version'],
						];
						$options[] = [
							'label' => $option['name'],
							'value' => $option['name'],
						];

						$total[$option['name'] . '_' . $item['version']] = $item['quantity'];
					}

					$actions = [
						[
							'label'	=> _l('select_action'),
							'value'	=> '',
						],
						[
							'label'	=> _l('accepted_with_short_quantity'),
							'value'	=> 3,
						],
						[
							'label'	=> _l('rejected'),
							'value'	=> 2,
						],
						[
							'label'	=> _l('accepted'),
							'value'	=> 1,
						],
					];

					$reasons[2] = [
						[
							'label'	=> _l('select_reason'),
							'value'	=> '',
						],
						[
							'label'	=> _l('binding'),
							'value'	=> 'binding',
						],
						[
							'label'	=> _l('content'),
							'value'	=> 'content',
						],
						[
							'label'	=> _l('sku_mismatch'),
							'value'	=> 'sku_mismatch',
						],
						[
							'label'	=> _l('torn_paper'),
							'value'	=> 'torn_paper',
						],
						[
							'label'	=> _l('version'),
							'value'	=> 'version',
						],
						[
							'label'	=> _l('other'),
							'value'	=> 'other',
						],
					];
					$reasons[3] = [
						[
							'label'	=> _l('select_reason'),
							'value'	=> '',
						],
						[
							'label'	=> _l('short_quantity'),
							'value'	=> 'short_quantity',
						],
					];

					array_unshift($versions, [
						'label'	=> _l('select_version'),
						'value'	=> '',
					]);

					array_unshift($options, [
						'label'	=> _l('select_option'),
						'value'	=> '',
					]);

					$this->json['book'] 	= array_merge($book, [
						'versions' 	=> $versions,
						'options' 	=> $options,
						'total'		=> $total,
						'version'	=> $book_info['version'],
						'actions'	=> $actions,
						'reasons'	=> $reasons,
					]);
					$this->json['success'] 	= _l('book_scanned');
				} else {
					$this->json['error'] 	= _l('book_not_found');
				}
			} else {
				$this->json['error'] = _l('invalid_slug');
			}
		}
	}

	public function cmsBookQaQc() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);
		$this->form_validation->set_rules('assignment_id', _l('assignment_id'), [
			'trim',
			'required',
			'numeric',
			['assignment', [$this->validate_model, 'assignment']]
		]);

		$this->form_validation->set_rules('version', _l('version'), 'trim|required|numeric|greater_than_equal_to[1]|less_than_equal_to[100]');
		$this->form_validation->set_rules('quantity', _l('quantity'), 'trim|required|numeric|greater_than_equal_to[1]|less_than_equal_to[2000]');
		$this->form_validation->set_rules('option', _l('option'), 'trim|required|in_list[Paperback,Hard Cover]');
		$this->form_validation->set_rules('action', _l('action'), 'trim|required|in_list[1,2,3]');

		self::_runFormValidation();

		if (!$this->json) {
			if ($this->input->post('user_id')) {
				$this->load->model('printer/QaQcLogs_model', 'qa_qc_logs_model');
				$this->load->model('printer/QaQcLots_model', 'qa_qc_lots_model');
				$this->load->model('printer/PrinterStats_model', 'printer_stats_model');
				$this->load->model('printer/PrinterAssignment_model', 'printer_assignment_model');

				$filter_data = [];
				$filter_data['assignment_id'] = $this->input->post('assignment_id');
				$filter_data['book_id'] = $this->input->post('book_id');
				$filter_data['version'] = $this->input->post('version');
				$filter_data['option'] = $this->input->post('option');

				if (empty($result = $this->printer_stats_model->printerAssignData($filter_data)['rows'][0])) {
					$this->json['error'] = _l('book_stock_not_added');
					return;
				}

				$assignment_info = $this->printer_assignment_model->get($this->input->post('assignment_id'));

				$quantity = (int)$this->input->post('quantity');
				$action = (int)$this->input->post('action');

				$accepted_quantity = $rejected_quantity = $accepted_short_quantity = 0;

				switch ((int)$action) {
					case 1:
						$accepted_quantity = $quantity;
						break;

					case 2:
						$rejected_quantity = $quantity;
						break;

					case 3:
						$accepted_short_quantity = $quantity;
						break;

					default:
						break;
				}

				$qa_qc_lots_info = $this->qa_qc_lots_model->get_all($filter_data);
				$qa_qc_lots_info = $qa_qc_lots_info['rows'][0] ?? [];

				// QA QC closed
				if ($qa_qc_lots_info && ($qa_qc_lots_info['accepted_quantity'] + $qa_qc_lots_info['accepted_short_quantity']) == $qa_qc_lots_info['book_quantity']) {
					$this->json['error'] = _l('qa_qc_is_closed_for_this_book');
					return;
				}

				if (($action != 2) && $quantity > 0 && $qa_qc_lots_info) {
					if (($qa_qc_lots_info['accepted_quantity'] + $qa_qc_lots_info['accepted_short_quantity'] + $quantity) > $qa_qc_lots_info['book_quantity']) {
						$this->json['error'] = sprintf(
							_l('only_%s_copies_qa_qc_is_pending'),
							($qa_qc_lots_info['book_quantity'] - $qa_qc_lots_info['accepted_quantity'] - $qa_qc_lots_info['accepted_short_quantity'])
						);
						return;
					}
				}

				if (!empty($qa_qc_lots_info)) {
					$this->qa_qc_lots_model->edit($qa_qc_lots_info['id'], [
						'accepted_quantity' 		=> $qa_qc_lots_info['accepted_quantity'] + $accepted_quantity,
						'rejected_quantity' 		=> $qa_qc_lots_info['rejected_quantity'] + $rejected_quantity,
						'accepted_short_quantity' 	=> $qa_qc_lots_info['accepted_short_quantity'] + $accepted_short_quantity,
					]);
				} else {
					$this->qa_qc_lots_model->add([
						'assignment_id' 			=> (int)$this->input->post('assignment_id'),
						'book_id' 					=> (int)$this->input->post('book_id'),
						'version' 					=> (int)$this->input->post('version'),
						'option' 					=> $this->input->post('option'),
						'book_quantity' 			=> $result['quantity'],
						'accepted_quantity'			=> $accepted_quantity,
						'rejected_quantity' 		=> $rejected_quantity,
						'accepted_short_quantity' 	=> $accepted_short_quantity,
					]);
				}

				$this->qa_qc_logs_model->add([
					'assignment_id' 	=> (int)$this->input->post('assignment_id'),
					'book_id' 			=> (int)$this->input->post('book_id'),
					'version' 			=> (int)$this->input->post('version'),
					'option' 			=> $this->input->post('option'),
					'quantity' 			=> $quantity,
					'reason' 			=> ($action != 1) ? $this->input->post('reason') : '',
					'comment' 			=> (($action != 1) && !empty($this->input->post('comment'))) ? json_encode($this->input->post('comment')) : '',
					'action' 			=> $action,
					'status' 			=> 1,
					'manager_id'		=> (int)$this->input->post('user_id'),
				]);

				if (($action != 2) && $quantity > 0) {
					$this->load->model('book/BookStock_model', 'book_stock_model');
					$this->load->model('book/BookStockLog_model', 'book_stock_log_model');

					if ($stock_info = $this->book_stock_model->get_all([
						'book_id'	=> $this->input->post('book_id'),
						'version'	=> $this->input->post('version'),
						'option'	=> $this->input->post('option'),
					])['rows'][0] ?? []) {
						$this->book_stock_model->edit($stock_info['id'], [
							'quantity'	=> (int)($stock_info['quantity'] + (int)$quantity),
						]);

						$this->book_stock_log_model->add([
							'manager_id'=> (int)$this->input->post('user_id'),
							'book_id'	=> (int)$this->input->post('book_id'),
							'version'	=> (int)$this->input->post('version'),
							'option'	=> $this->input->post('option'),
							'quantity'	=> (int)$quantity,
							'status'	=> 0,
						]);

						$this->json['success'] = _l('book_stock_updated');
					} else {
						$this->book_stock_model->add([
							'manager_id'=> (int)$this->input->post('user_id'),
							'book_id'	=> (int)$this->input->post('book_id'),
							'version'	=> (int)$this->input->post('version'),
							'option'	=> $this->input->post('option'),
							'quantity'	=> (int)$quantity,
						]);

						$this->book_stock_log_model->add([
							'manager_id'=> (int)$this->input->post('user_id'),
							'book_id'	=> (int)$this->input->post('book_id'),
							'version'	=> (int)$this->input->post('version'),
							'option'	=> $this->input->post('option'),
							'quantity'	=> (int)$quantity,
							'status'	=> 1,
						]);
					}

					$this->load->library('Stock_lib', 'stock_lib');
					$this->stock_lib->stockFulfill(
						$quantity,
						$this->input->post('book_id'),
						$this->input->post('version'),
						$this->input->post('option'),
						$this->input->post('assignment_id')
					);
				}

				$this->json['success'] = _l('book_stock_added');
			} else {
				$this->json['error'] = _l('invalid_user_id');
			}
		}
	}

	public function cmsQaQcComplete() {
		$this->form_validation->set_rules('assignment_id', _l('assignment_id'), [
			'trim',
			'required',
			'numeric',
			['assignment', [$this->validate_model, 'assignment']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->load->model('printer/PrinterStats_model', 'printer_stats_model');
			$this->load->model('printer/PrinterAssignment_model', 'printer_assignment_model');

			$assignment_info = $this->printer_assignment_model->get($this->input->post('assignment_id'));

			if (!empty($assignment_info['id'])) {
				if (empty($this->cron_model->getByCode('qaqcCompleteCron_' . $assignment_info['id']))) {
					$this->cron_model->add([
					'code'		  	=> 'qaqcCompleteCron_' . $assignment_info['id'],
					'action'		=> 'alert_model->qaqcCompleteCron',
					'data'		  	=> [$assignment_info['id']],
					'site_id'	   	=> 1,
					'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
					]);
				} else {
					$this->cron_model->editByCode('qaqcCompleteCron_' . $assignment_info['id'], [
						'alert_date' => date('Y-m-d H:i:00', strtotime('+1 minutes')),
						'status' => 0
					]);
				}

				$this->json['success'] = _l('assignment_qa_qc_completed');
			} else {
				$this->json['error'] = _l('assignment_not_found');
			}
		}
	}

	public function getOrderPackagingStats() {
		if (!$this->json) {
			$this->load->model('order/OrderPackingLog_model', 'order_packing_log_model');

			$this->json['stats']['total'] = $this->order_packing_log_model->get_all([
				'user_id'		=> (int)$this->input->post('user_id'),
				'type'			=> 2,
			])['total'] ?? 0;
			$this->json['stats']['month'] = $this->order_packing_log_model->get_all([
				'user_id'		=> (int)$this->input->post('user_id'),
				'month'			=> date('Y-m-d'),
				'type'			=> 2,
			])['total'] ?? 0;
			$this->json['stats']['today'] = $this->order_packing_log_model->get_all([
				'user_id'		=> (int)$this->input->post('user_id'),
				'startdate'		=> date('Y-m-d'),
				'enddate'		=> date('Y-m-d', strtotime('+1 days')),
				'type'			=> 2,
			])['total'] ?? 0;
		}
	}

	public function addOrderPackagingLog() {
		$this->form_validation->set_rules('awb', _l('awb'), 'trim|required');

		self::_runFormValidation();

		if (!$this->json) {
			if ($order_info = $this->order_model->get_all([
				'awb'	=> $this->input->post('awb')
			])['rows'][0]) {
				$this->load->model('order/OrderPackingLog_model', 'order_packing_log_model');

				if ($this->order_packing_log_model->get_all([
					'order_id'	=> $order_info['id'],
					'user_id'	=> (int)$this->input->post('user_id'),
					'type'		=> 2,
				])['total'] === 0) {
					$this->order_packing_log_model->add([
						'order_id'	=> $order_info['id'],
						'user_id'	=> (int)$this->input->post('user_id'),
						'type'		=> 2,
					]);

					$this->json['success'] = _l('order_processed');
				} else {
					$this->json['error'] = _l('awb_already_scanned');
				}
			} else {
				$this->json['error'] = _l('awb_not_found');
			}
		}
	}
}
