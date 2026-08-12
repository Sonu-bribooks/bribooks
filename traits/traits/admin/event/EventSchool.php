<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventSchool {

	private $error = array();
	private $columns = [
		"school" => [
			'country',
			'state',
			'city',
			'name',
			'email',
			'mobile',
			'address',
			'zipcode',
			'authorized_person',
			'event_id',
			'owner_name',
			'designation',
			'is_verified'
		],
	];
	private $types = [
		'school'
	];

	public function event_school($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
            $this->event_site_model->add([
				'event_id'  => $this->input->post('event_id'),
				'site_id'   => $this->input->post('site_id')
			]);
			redirect(site_url('admin/event_school'), 'refresh');
		} elseif ($param1 == 'edit') {
			$this->event_site_model->edit($param2,[
				'name' => trim(ucwords($this->input->post('name')))
			]);
			redirect(site_url('admin/event_school'), 'refresh');
		}

		$data['page_name'] 			= 'events/school/index';
		$data['page_title'] 		= _l('event_school');
		$data['action_add'] 		= site_url('admin/event_school_form/add');
		$data['action_ajax'] 		= site_url('admin/ajax_event_school');
        $data['events'] 			= $this->event_model->get_all()['rows'];

		$this->load->view('backend/index', $data);
	}

	public function event_school_form($param1 = NULL, $param2 = NULL) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'events/school/form';
			$data['page_title'] 					= _l('event_school_add');
			$data['action'] 						= site_url('admin/event_school/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'events/school/form';
			$data['page_title'] 					= _l('event_school_edit');
			$data['action'] 						= site_url('admin/event_school/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$data['details'] 						= $this->event_site_model->get($param2);
		}

		$this->load->view('backend/index', $data);
	}

	public function ajax_event_school() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

        if(!empty($this->input->get('event_id'))) {
			$filter_data['event_id'] = $this->input->get('event_id');
		}

		$results = $this->event_site_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

            $json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
                'event_name'			=> $result['event_name'],
				'site_name'			    => $result['site_name'],
				'date_added'			=> formatDate($result['date_added']),
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

    public function check_school_in_event() {
        $data = $this->event_site_model->getEventIdBySiteId($this->input->post('event_id'), $this->input->post('site_id'));
        $json['status'] = false;
		if(!empty($data)) {
            $json['status'] = true;
        }
		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function import_school() {

		$data['action_file'] 		= site_url('admin/upload_school');
		$data['action_type'] 		= site_url('admin/school_type');
		$data['action_save'] 		= site_url('admin/save_school');
		$data['action_download'] 	= site_url('admin/download_school');
		$data['action_add'] 		= site_url('import_school_form');


		$data['page_name'] 			= 'events/school/import';
		$data['page_title'] 		= _l('import_school');

		$data['types'] 				= $this->types;

		$this->load->view('backend/index', $data);
	}

	public function school_type() {
		$data = [];

		if ($this->input->method() == 'post' && $this->input->post('type') && in_array($this->input->post('type'), $this->types)) {
			$this->session->set_userdata('type', $this->input->post('type'));

			$data['success']			= _l('successfully updated type');
			$data['next']				= _l('successfully updated type');
		} else {
			$data['error'] 				= _l('invalid_type');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	public function upload_school() {

		ini_set('upload_max_filesize', '20M');
		ini_set('post_max_size', '20M');
		set_time_limit(0);

		$data['headers'] = [];

		if (!is_dir('uploads/csv/school')) {
			mkdir('uploads/csv/school', 0777, TRUE);
		}

		$config = [
			'upload_path' 	=> "uploads/csv/school",
			'allowed_types' => "text/plain|text/csv|csv",
			'remove_spaces' => TRUE,
			'max_size' 		=> "50000",
			'file_name' 	=> "data_" . date('d-m-Y_H_i_s')
		];

		$this->load->library('upload', $config);

		if ($this->upload->do_upload('file')) {
			$upload = $this->upload->data();

			$this->load->library('parsecsv');

			$this->parsecsv->auto($config['upload_path'] . '/' . $upload['file_name']);

			$rows = $this->parsecsv->data;

			$data['csv_file'] 	= $upload['file_name'];
			$data['type'] 		= $this->session->userdata('type');

			if ($rows) {
				$data['headers'] 		= array_map('trim', array_keys($rows[0]));
				$data['columns'] 		= $this->columns[$this->session->userdata('type')];
				$data['success'] 		= _l('upload_success');
			} else {
				$data['error'] 			= _l('error_unknown');
			}
		} else {
			$data['error'] 				= $this->upload->display_errors();
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

    public function download_school($type = 'school') {
		$filename 	= 'sample_' . preg_replace(['/[^\w\s]/', '/\s+/'], [' ', ' '], $type) . '_' . date('Y_m_d_H_i_s') . '.csv';

		$fields 	= $this->columns[$type] ?? [];

		$default_values['school'] = [
			'India',
			'Delhi',
			'New Delhi',
			'Test School',
			'email_id',
			'phone_number',
			'Delhi',
			'110001',
			'authorized_person',
			'1',
			'owner_name',
			'Coordinator/Director/Principal/Others',
			'0 => no, 1 => yes'
		];

		$results[] 	= array_combine($fields, $default_values[$type]);

		if (!headers_sent()) {
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' .  $filename . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			if (ob_get_level()) {
				ob_end_clean();
			}
		} else {
			exit('Error: Headers already sent out!');
		}

		$headers = isset($results[0]) ? array_keys($results[0]) : [];

		if (!$headers) {
			exit($this->lang->line('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		$this->writeRowToCsv($results, $fp, $headers);

		fclose($fp);

		exit();
	}

	public function save_school() {
		ini_set('memory_limit', -1);
		ini_set('max_execution_time', 0);
		set_time_limit(0);

		$data = [];

		if ($this->input->method() == 'post' && $this->input->post('mapping') && $this->input->post('csv_file') && $this->input->post('type')) {
			$this->load->library('parsecsv');
			$this->parsecsv->auto('uploads/csv/school/' . $this->input->post('csv_file'));

			$rows = $this->parsecsv->data;
			$map = $this->input->post('mapping');

			switch ($this->input->post('type')) {
				case 'school':
					$data['status'] = self::importSchools($rows, $map);
					break;
				default:
					$data['error'] 	= _l('nothing_to_import');
			}

			$data['finish'] 	= true;
			$data['success'] 	= _l('text_save_success');

			if (!empty($data['status']['skipped'])) {
				$data['error'] = sprintf(_l('skipped %s %s '), $data['status']['skipped'], $this->input->post('type'));
			}

			if (!empty($data['status']['uploaded'])) {
				$data['success'] = sprintf(_l('cleaned %s %s successfully'), $data['status']['uploaded'], $this->input->post('type'));
			}
		} else {
			$data['error'] 		= _l('error_unknown');
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($data));
	}

	private function importSchools($rows = [], $map = []) {
		$skipped = $uploaded = 0;

		$this->load->model('common/Cron_model', 'cron_model');
		$this->load->model('localisation/State_model', 'state_model');

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if(!empty($data) && !empty($data['name']) && !empty($data['mobile']) && !empty($data['email']) && !empty($data['event_id']) && !empty($data['country'])) {
				$skipped++;
				continue;
			}


			// if (empty($data['school'])) {
			// 	$skipped++;
			// 	continue;
			// }


			if (!(self::checkDuplicateSchool($row))) {
				$skipped++;
				continue;
			}

			$country_id = $state_id = $city_id = 0;

			if(!empty($data['country'])) {
				if ($country_info = $this->db->get_where('country', [
					'name' => $data['country']
				])->row_array()) {
					$country_id = $country_info['id'];
				} else {
					$country_id = $this->country_model->add([
						'name'			=> $data['country'],
					]);
				}
			}

			if(!empty($data['state'])) {
				if ($state_info = $this->db->get_where('state', [
					'country_id'	=> $country_id,
					'name' 			=> $data['state']
				])->row_array()) {
					$state_id = $state_info['id'];
				} else {
					$state_id = $this->state_model->add([
						'name'			=> $data['state'],
						'country_id'	=> (int)$country_id,
					]);
				}
			}

			if(!empty($data['city'])) {
				if ($city_info = $this->db->get_where('city', [
					'name' 		=> $data['city'],
					'state_id'	=> $state_id,
				])->row_array()) {
					$city_id = $city_info['id'];
				} else {
					$city_id = $this->city_model->add([
						'name'		=> $data['city'],
						'state_id'	=> $state_id,
					]);
				}
			}

			$row['country_id'] = $country_id;
			$row['state_id'] = $state_id;
			$row['city_id'] = $city_id;

			self::saveSiteData($row);

			$uploaded++;
		}

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function checkDuplicateSchool ($data = []) {

		if (!empty($this->site_model->getSiteByWhere([
			'owner_email'  => $data['email']
		]))) {
			return false;
		}

		if (!empty($this->site_model->getSiteByWhere([
			'owner_mobile'  => $data['email']
		]))) {
			return false;
		}

		if (!empty($this->student_model->getUserByWhere([
			'email'  => $data['email']
		]))) {
			return false;
		}

		if (!empty($this->student_model->getUserByWhere([
			'owner_mobile'  => $data['mobile']
		]))) {
			return false;
		}

		return true;
	}

	private function saveSiteData($data = []) {
		if(!empty($data) && !empty($data['name']) && !empty($data['mobile']) && !empty($data['email']) && !empty($data['event_id']) && !empty($data['country'])) {
			if(
				($event_info = $this->event_model->get($data['event_id']))
			) {
				$site_info_detail = $this->site_model->getSiteByWhere([
					'name' 			=> $data['name'],
					'state_id' 		=> $data['state_id'],
					'city_id' 		=> $data['city_id'],
					'owner_email' 	=> $data['email']
				]);

				if (strtolower($data['country']) == 'india') {
					$site_info = $this->site_model->get(1);
				} else {
					$site_info = $this->site_model->get(2);
				}

				$site_id = 0;

				if(!empty($site_info_detail)) {
					$site_id = $site_info_detail['id'];
				} else {
					$address = !empty($data['address']) ? trim($data['address']) . ', ' : '';
					$address .= !empty($data['city']) ? trim($data['city']) . ', ' : '';
					$address .= !empty($data['state_code']) ? trim($data['state_code']) . ', ' : '';
					$address .= !empty($data['state']) ? trim($data['state']) . ', ' : '';
					$address .= !empty($data['zipcode']) ? trim($data['zipcode']) : '';

					$address = !empty($address) ? rtrim(trim($address), ',') : '';

					$site_code_count = (int)($this->db->select('COUNT(id) as count')
					->from('site')
					->where('parent_id', (int)$data['parent_id'])
					->get()
					->row_array()['count']) + 1;

					$insert_site_data = [
						'parent_id' 		  => $site_info['id'],
						'can_add_site' 		  => 0,
						'name' 				  => trim($data['name']),

						'site_code' 		  => $site_info['site_code'] . "-import-" . $site_code_count,
						'site_type' 		  => $data['site_type'] ?? 1,
						'discount_code' 	  => $site_info['discount_code'],
						'discount_percentage' => $site_info['discount_percentage'],
						'timezone' 			  => $site_info['timezone'],
						'payment_gateway' 	  => $site_info['payment_gateway'],
						'sms_gateway' 		  => $site_info['sms_gateway'],
						'email_alert' 		  => $site_info['email_alert'],

						'address' 			  => $address,

						'mobile_length' 	  => $site_info['mobile_length'],
						'country_code' 		  => $site_info['country_code'],
						'currency_code' 	  => $site_info['currency_code'],

						'state_id' 			  => $data['state_id'],
						'city_id' 			  => $data['city_id'],

						'base_price' 		  => $site_info['base_price'],
						'ebook_price' 		  => $site_info['ebook_price'],
						'price_per_page' 	  => $site_info['price_per_page'],
						'free_page_limit' 	  => $site_info['free_page_limit'],
						'hard_cover_price' 	  => $site_info['hard_cover_price'],
						'paperback_price' 	  => $site_info['paperback_price'],
						'tax' 				  => $site_info['tax'],
						'tax_text' 			  => $site_info['tax_text'],
						'owner_name' 	      => !empty($data['owner_name']) ? trim($data['owner_name']) : '',
						'authorized_person'   => !empty($data['authorized_person']) ? trim($data['authorized_person']) : '',

						'owner_email' 		  => $data['email'],
						'owner_mobile' 	      => $data['mobile'],
						'verified' 	      	  => $data['is_verified'] ?? 0,

						'status' 			  => 1,
						'license_total' 	  => 1000,
						'license_used' 		  => 0,
					];

					$site_id = $this->site_model->addSite($insert_site_data);

					if($site_id) {
						$this->db->insert('users', [
							'site_id'			=> $site_id,
							'role_id'			=> 9,
							'first_name'		=> trim($data['name']),
							'email'				=> $data['email'],
							'mobile'			=> $data['mobile'],
							'country_id'		=> $data['country_id'] ?? 0,
							'state_id'			=> $data['state_id'] ?? 0,
							'city_id'			=> $data['city_id'] ?? 0,
							'location'			=> $data['country'] ?? '',
							'mobile_verified'	=> 1,
							'email_verified'	=> 1,
							'status'			=> 1,
							'date_added'		=> date('Y-m-d H:i:s'),
							'date_modified'		=> date('Y-m-d H:i:s')
						]);

						$this->cron_model->add([
							'code'			=> 'eventSchoolRegistrationCron_' . $site_id,
							'action'		=> 'alert_model->eventSchoolRegistrationCron',
							'data'			=> [$site_id, $data['event_id']],
							'site_id'		=> $site_id,
							'alert_date'	=> date('Y-m-d H:i:00', '+1 minutes')
						]);
					}
				}

				if(!empty($data['event_id']) && !empty($site_id)) {
					self::saveSiteEventData($data['event_id'], $site_id);
				}
			}
		}
	}

	private function saveSiteEventData($event_id = false, $site_id = false) {
		if(!empty($event_id) && !empty($site_id) && empty($this->event_site_model->getEventIdBySiteId($event_id, $site_id))){
			return $this->event_site_model->add([
				'event_id'=> $event_id,
				'site_id'=> $site_id
			]);
		}
		return false;
	}

}
