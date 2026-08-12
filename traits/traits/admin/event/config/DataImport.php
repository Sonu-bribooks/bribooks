<?php defined('BASEPATH') or exit('No direct script access allowed');

trait DataImport {
	private $_event_import_types = [
		'school_data',
		'state_data',
		'city_data',
	];

	private $_event_import_columns = [
		'school_data' => [
			'site_id',
			'parent_id',
			'country_id',
			'state_id',
			'city_id',
			'school_name',
			'email',
			'mobile',
			'authorized_person',
			'alternate_email',
			'alternate_mobile',
			'alternate_authorized_person',
			'owner_name',
			'address',
			'landmark',
			'zipcode',
			'site_type',
			'tag',
		],
		'state_data' => [
			'country_id',
			'name',
		],
		'city_data' => [
			'state_id',
			'name',
		]
	];

	private $_event_import_dir = 'uploads/csv/eventtempimport53dddfdf';

	private function _getDataImport($data = []) {
		$stage 				= $data['stage'] ?? 'data_import';
		$info 				= $data['info'] ?? [];
		$country_info 		= $data['country_info'] ?? [];
		$event_type_info 	= $data['event_type_info'] ?? [];

		$data['types'] 		= $this->_event_import_types;

		$data['action_file'] 		= base_url('admin/ajax_event_data_import/upload');
		$data['action_type'] 		= base_url('admin/ajax_event_data_import/type');
		$data['action_save'] 		= base_url('admin/ajax_event_data_import/save');
		$data['action_download'] 	= base_url('admin/ajax_event_data_import/download');

		$this->load->view('backend/admin/event/stage/data_import', $data);
	}

	public function ajax_event_data_import($action, ...$rest) {
		$method = sprintf('_getEventImport%s', str_replace(' ', '', ucwords(str_replace('_', ' ', $action))));

		if (method_exists($this, $method)) {
			self::{$method}(...$rest);
		}
	}

	private function _getEventImportType() {
		$data = [];

		if ($this->input->method() == 'post' && $this->input->post('type') && in_array($this->input->post('type'), $this->_event_import_types)) {
			$this->session->set_userdata('type', $this->input->post('type'));

			$data['success']			= _l('successfully updated type');
			$data['next']				= _l('successfully updated type');
		} else {
			$data['error'] 				= _l('invalid_type');
		}

		output_json($data);
	}

	private function _getEventImportUpload() {
		ini_set('upload_max_filesize', '20M');
		ini_set('post_max_size', '20M');
		set_time_limit(0);

		$data['headers'] = [];

		if (!is_dir($this->_event_import_dir)) {
			mkdir($this->_event_import_dir, 0777, TRUE);
		}

		$config = [
			'upload_path' 	=> $this->_event_import_dir,
			'allowed_types' => 'text/plain|text/csv|csv',
			'remove_spaces' => TRUE,
			'max_size' 		=> 50000,
			'file_name' 	=> 'data_' . date('d-m-Y_H_i_s')
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
				$data['columns'] 		= $this->_event_import_columns[$this->session->userdata('type')];
				$data['success'] 		= _l('upload_success');
			} else {
				$data['error'] 			= _l('error_unknown');
			}
		} else {
			$data['error'] 				= $this->upload->display_errors();
		}

		output_json($data);
	}

	private function _getEventImportSave() {
		ini_set('memory_limit', -1);
		ini_set('max_execution_time', 0);
		set_time_limit(0);

		$data = [];

		if ($this->input->method() == 'post' && $this->input->post('mapping') && $this->input->post('csv_file') && $this->input->post('type')) {
			$this->load->library('parsecsv');
			$this->parsecsv->auto($this->_event_import_dir . '/' . $this->input->post('csv_file'));

			$rows 	= $this->parsecsv->data;
			$map 	= $this->input->post('mapping');

			switch ($this->input->post('type')) {
				case 'school_data':
					$data['status'] = self::_eventImportSchools($rows, $map);
					break;
				case 'state_data':
					$data['status'] = self::_eventImportState($rows, $map);
					break;
				case 'city_data':
					$data['status'] = self::_eventImportCity($rows, $map);
					break;
				default:
					$data['error'] 	= _l('nothing_to_import');
			}

			$data['finish'] 	= true;
			$data['success'] 	= _l('text_save_success');

			if (!empty($data['status']['skipped'])) {
				$data['error'] = sprintf(_l('skipped %s %s due to duplicate entry'), $data['status']['skipped'], $this->input->post('type'));
			}

			if (!empty($data['status']['uploaded'])) {
				$data['success'] = sprintf(_l('uploaded %s %s successfully'), $data['status']['uploaded'], $this->input->post('type'));
			}

		} else {
			$data['error'] 		= _l('error_unknown');
		}

		output_json($data);
	}

	private function _getEventImportDownload($type = 'school_data') {
		$fields = $this->_event_import_columns[$type] ?? [];

		$default_values['school_data'] = [
			'site_id(Integer)if primary site is available else 0',
			'(Integer)for group of school',
			'1',
			'state_id',
			'city_id',
			'school_name',
			'email@gmail.com',
			'987654323456',
			'authorized_person',
			'alternate_email',
			'alternate_mobile',
			'alternate_authorized_person',
			'owner_name',
			'address',
			'landmark',
			'zipcode',
			'1=School,3=School Chains,4=Community, 7=Country Site',
			'verified, unverified',
		];

		$default_values['state_data'] = [
			'country_id',
			'name',
		];

		$default_values['city_data'] = [
			'state_id',
			'name',
		];

		$results[] = array_combine($fields, $default_values[$type]);

		self::_downloadCsv($results, $type);
	}

	private function _eventImportSchools($rows = [], $map = []) {
		$skipped = $uploaded = 0;

		$this->load->model('school/School_model', 'school_model');

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if (empty($data['school_name'])) {
				$skipped++;
				continue;
			}

			if (!empty($data['email']) && !empty($this->user_model->get_all([
				'email' 			=> trim($data['email']),
			])['rows'] ?? [])) {
				$skipped++;
				continue;
			}

			if (!empty($data['mobile']) && !empty($this->user_model->get_all([
				'mobile' => trim($data['mobile']),
			])['rows'] ?? [])) {
				$skipped++;
				continue;
			}

			self::_eventImportSaveSchoolData($data);

			$uploaded++;
		}

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _eventImportState($rows = [], $map = []) {
		$skipped = $uploaded = 0;

		$this->load->model('localisation/State_model', 'state_model');

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if (empty($data['country_id']) || empty($data['name'])) {
				$skipped++;
				continue;
			}

			if (!empty($data['name']) && !empty($this->state_model->get_all([
				'country_id' 	=> (int)$data['country_id'],
				'name' 			=> trim($data['name']),
			])['rows'] ?? [])) {
				$skipped++;
				continue;
			}

			$this->state_model->add([
				'country_id' 	=> (int)$data['country_id'],
				'name' 			=> trim($data['name']),
			]);

			$uploaded++;
		}

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _eventImportCity($rows = [], $map = []) {
		$skipped = $uploaded = 0;

		$this->load->model('localisation/City_model', 'city_model');

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if (empty($data['state_id']) || empty($data['name'])) {
				$skipped++;
				continue;
			}

			if (!empty($this->city_model->get_all([
				'state_id' 	=> (int)$data['state_id'],
				'name' 		=> trim($data['name']),
			])['rows'] ?? [])) {
				$skipped++;
				continue;
			}

			$this->city_model->add([
				'state_id' 	=> (int)$data['state_id'],
				'name' 		=> trim($data['name']),
			]);

			$uploaded++;
		}

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _eventImportSaveSchoolData($data = []) {
		if (!empty($data) && !empty($data['school_name'])) {

			$site_info = $this->site_model->get_all([
				'site_type' 	=> 7,
				'country_id' 	=> $data['country_id'] ?? 0
			])['rows'][0] ?? '';

			if (empty($site_info)) {
				$site_info = $this->site_model->get($this->config->item('default_site_id'));
			}

			$insert_school_data = [
				'parent_id' 		  			=> $data['parent_id'] ?? 0,
				'site_id' 		  				=> $data['site_id'] ?? 0,
				'name' 				  			=> trim($data['school_name']),
				'site_code' 		  			=> $site_info['site_code'] . '-import-' . uniqid(),
				'site_type' 		  			=> $data['site_type'] ?? 1,
				'discount_code' 	  			=> $site_info['discount_code'] ?? 0,
				'discount_percentage' 			=> $site_info['discount_percentage'] ?? 0,
				'timezone' 			  			=> $site_info['timezone'] ?? '',
				'payment_gateway' 	  			=> $site_info['payment_gateway'] ?? '',
				'sms_gateway' 		  			=> $site_info['sms_gateway'] ?? '',
				'email_alert' 		  			=> $site_info['email_alert'] ?? '',
				'address' 			  			=> $data['address'] ?? '',
				'landmark' 			  			=> $data['landmark'] ?? '',
				'pincode' 			  			=> $data['zipcode'] ?? '',
				'mobile_length' 	  			=> $site_info['mobile_length'] ?? '',
				'country_code' 		  			=> $site_info['country_code'] ?? '',
				'currency_code' 	  			=> $site_info['currency_code'] ?? '',
				'country_id' 			  		=> $data['country_id'] ?? 0,
				'state_id' 			  			=> $data['state_id'] ?? 0,
				'city_id' 			  			=> $data['city_id'] ?? 0,
				'base_price' 		  			=> $site_info['base_price'] ?? 0,
				'ebook_price' 		  			=> $site_info['ebook_price'] ?? 0,
				'price_per_page' 	  			=> $site_info['price_per_page']  ?? 0,
				'black_white_price' 	  		=> $site_info['black_white_price']  ?? 0,
				'black_white_price_per_page' 	=> $site_info['black_white_price_per_page']  ?? 0,
				'free_page_limit' 	  			=> $site_info['free_page_limit'] ?? 0,
				'hard_cover_price' 	  			=> $site_info['hard_cover_price'] ?? 0,
				'paperback_price' 	  			=> $site_info['paperback_price'] ?? 0,
				'tax' 				  			=> $site_info['tax'] ?? '',
				'tax_text' 			  			=> $site_info['tax_text'] ?? '',
				'owner_name' 	      			=> !empty($data['owner_name']) ? trim($data['owner_name']) : '',
				'authorized_person'   			=> !empty($data['authorized_person']) ? trim($data['authorized_person']) : '',
				'owner_email' 		  			=> $data['email'] ?? '',
				'owner_mobile' 	      			=> $data['mobile'] ?? '',
				'alternate_authorized_person'   => $data['alternate_authorized_person'] ?? '',
				'alternate_owner_email' 		=> $data['alternate_email'] ?? '',
				'alternate_owner_mobile' 	    => $data['alternate_mobile'] ?? '',
				'tag' 	    					=> $data['tag'] ?? '',
				'status' 			  			=> 1,
				'license_total' 	  			=> 1000,
				'license_used' 		  			=> 0,
			];

			$school_id = $this->school_model->add($insert_school_data);

			if (!empty($school_id)) {
				$this->school_model->edit($school_id, [
					'site_code' => get_site_code_slug(trim($data['school_name'])) . '-' . $school_id
				]);
			}
		}
	}
}
