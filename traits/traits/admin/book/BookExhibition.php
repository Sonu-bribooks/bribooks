<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait BookExhibition {
	public function exhibition_authors() {
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$data['events'] 		= $this->event_model->get_all()['rows'] ?? [];
		$data['slots'] 			= $this->invite_slot_model->get_all()['rows'] ?? [];

		$data['page_name'] 		= 'students/exhibition_authors_invite';
		$data['page_title'] 	= _l('exhibition_authors_invite');
		$data['action_csv'] 	= site_url('admin/download_exhibition_author_csv');
		$data['action_zip'] 	= site_url('admin/download_exhibition_author_pdfs_zip');
		$data['action_ajax'] 	= site_url('admin/ajax_exhibition_authors');

		$this->load->view('backend/index', $data);
	}

	public function ajax_exhibition_authors() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'event_id'			=> $this->input->get('event_id') < 0 || is_null($this->input->get('event_id')) ? null : (int)$this->input->get('event_id'),
			'verified'			=> $this->input->get('verified') < 0 || is_null($this->input->get('verified')) ? null : (int)$this->input->get('verified'),
			'slot_id'			=> $this->input->get('slot_id') < 0 || is_null($this->input->get('slot_id')) ? null : (int)$this->input->get('slot_id'),
			'status'			=> $this->input->get('status') < 0 || is_null($this->input->get('status')) ? null : (int)$this->input->get('status'),
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> 'book_exhibition_exhibition_invites.' . $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->book_exhibition_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$invite_status = '';
			switch ($result['status']) {
				case 1:
					$invite_status = '<span class="text-success">Accepted</span>';
					break;

				case 2:
					$invite_status = '<span class="text-danger">Rejected</span>';
					break;

				default:
					$invite_status = '<span class="text-warning">Pending</span>';
					break;
			}

			$user_info 	= $this->user_model->get($result['user_id']);
			$event_info = $this->event_model->get($result['event_id']);

			$url = !empty($user_info['id']) ? vsprintf(USER_URL . 'exhibitioninvite?ui=%s&code=%s&eid=%s', [
				$user_info['id'],
				$user_info['verification_code'],
				$result['event_id'],
			]) : '#';

			$slot_info = $this->db->get_where('invite_slot', ['id' => $result['slot_id']])->row_array();

			$json['data'][] = [
				'sn'				=> $filter_data['start'] + 1 + $key,
				'id'				=> $result['id'],
				'event'				=> $event_info['name'] ?? '',
				'name'				=> vsprintf(_l('%s <br>%s <br> %s'), [
					$result['name'],
					$result['email'],
					$result['mobile'],
				]),
				'slot'				=> sprintf('%s - %s', $slot_info['slot_start'], $slot_info['slot_end']),
				'invite_status'		=> $invite_status,
				'guest_count'		=> $result['no_of_guest'],
				'location'			=> $user_info['location'],
				'source'			=> $user_info['source'],
				'verified'			=> sprintf('%s <br> %d %s', _sd($result['verified']), $result['verified_count'], _l('guests')),
				'date_added'		=> formatDate($result['date_added']),
				'actions'			=> !empty($result['status'])
					? '<a target="_blank" title="Form Link" href="' . $url . '"><i class="fa fa-link"></i></a> <a target="_blank" title="Entry Pass" href="' . base_url('uploads/exhibitionpass/pdfs/entry_pass_' . $result['code']) . '.pdf' . '"><i class="fa fa-download"></i></a>'
					: '<a target="_blank" title="Form Link" href="' . $url . '"><i class="fa fa-link"></i></a>',
			];
		}

		output_json($json);
	}

	public function download_exhibition_author_images_zip() {
		$this->load->library('zip');

		$results = $this->book_exhibition_model->get_all()['rows'] ?? [];

		if (empty($results)) {
			$this->session->set_flashdata('error_message', _l('no_record_found'));
			redirect(site_url('admin/exhibition_authors'), 'refresh');
		}

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'exhibition_authors_images.zip';

		foreach ($results as $result) {
			$user_image = 'user_' . (int)$result['user_id'] . '.png';

			$this->zip->add_data($user_image, @file_get_contents($this->config->item('s3_base_url') . $this->config->item('s3_exhibition_images') . (ENVIRONMENT === 'production' ? '' : 'test/') . $user_image));
		}

		$this->zip->download($filename);

		is_file($filename) && unlink($filename);
	}

	public function export_exhibition_invites() {
		$json = [];

		$filter_data = [
			'event_id'			=> $this->input->get('event_id') < 0 || is_null($this->input->get('event_id')) ? null : (int)$this->input->get('event_id'),
			'slot_id'			=> $this->input->get('slot_id') < 0 || is_null($this->input->get('slot_id')) ? null : (int)$this->input->get('slot_id'),
			'verified'			=> $this->input->get('verified') < 0 || is_null($this->input->get('verified')) ? null : (int)$this->input->get('verified'),
			'status'			=> $this->input->get('status') < 0 ? null : (int)$this->input->get('status'),
		];

		$results = $this->book_exhibition_model->get_all($filter_data);

		foreach ($results['rows'] ?? [] as $key => $result) {
			$invite_status = '';

			switch ($result['status']) {
				case 1:
					$invite_status = _l('accepted');
					break;

				case 2:
					$invite_status = _l('rejected');
					break;

				default:
					$invite_status = _l('pending');
					break;
			}

			$user_info 	= $this->user_model->get($result['user_id']);
			$event_info = $this->event_model->get($result['event_id']);

			$url = !empty($user_info['id']) ? vsprintf(USER_URL . 'exhibitioninvite?ui=%s&code=%s&eid=%s', [
				$user_info['id'],
				$user_info['verification_code'],
				$result['event_id'],
			]) : '#';

			$slot_info = $this->db->get_where('invite_slot', ['id' => $result['slot_id']])->row_array();

			$invites[] = [
				'sn'				=> 1 + $key,
				'id'				=> $result['id'],
				'event'				=> $event_info['name'] ?? '',
				'author_name'		=> $result['name'],
				'email'				=> $result['email'],
				'mobile'			=> $result['mobile'],
				'slot'				=> sprintf('%s - %s', $slot_info['slot_start'], $slot_info['slot_end']),
				'invite_status'		=> $invite_status,
				'guest_count'		=> $result['guest_count'],
				'verified'			=> $result['verified_count'],
				'location'			=> $user_info['location'],
				'source'			=> $user_info['source'],
				'date_added'		=> formatDate($result['date_added']),
				'invite_urls'		=> !empty($result['verified_count'])
					? $url . "\r\n" . base_url('uploads/exhibitionpass/pdfs/entry_pass_' . $result['code']) . '.pdf'
					: $url,
			];
		}

		self::_downloadCsv($invites, 'exhibition_author_invites_');

		output_json($json);
	}

	public function download_exhibition_author_pdfs_zip() {
		$this->load->library('zip');

		$filter_data = [
			'event_id'			=> $this->input->get('event_id') < 0 || is_null($this->input->get('event_id')) ? null : (int)$this->input->get('event_id'),
			'slot_id'			=> $this->input->get('slot_id') < 0 || is_null($this->input->get('slot_id')) ? null : (int)$this->input->get('slot_id'),
			'verified'			=> $this->input->get('verified') < 0 || is_null($this->input->get('verified')) ? null : (int)$this->input->get('verified'),
			'status'			=> $this->input->get('status') < 0 ? null : (int)$this->input->get('status'),
		];

		$results = $this->book_exhibition_model->get_all($filter_data)['rows'] ?? [];

		if (empty($results)) {
			$this->session->set_flashdata('error_message', _l('no_record_found'));
			redirect(site_url('admin/exhibition_authors'), 'refresh');
		}

		$filename = (ENVIRONMENT === 'production' ? '' : 'test_') . 'exhibition_authors_guest_pdfs.zip';

		foreach ($results as $result) {
			$this->zip->add_data(
				'entry_pass_' . $result['code'] . '.pdf',
				@file_get_contents(FCPATH . 'uploads/exhibitionpass/pdfs/entry_pass_' . $result['code'] . '.pdf')
			);
		}

		$this->zip->download($filename);

		is_file($filename) && unlink($filename);
	}

	public function download_exhibition_author_csv() {
		$filter_data = [
			'event_id'			=> $this->input->get('event_id') < 0 || is_null($this->input->get('event_id')) ? null : (int)$this->input->get('event_id'),
			'slot_id'			=> $this->input->get('slot_id') < 0 || is_null($this->input->get('slot_id')) ? null : (int)$this->input->get('slot_id'),
			'verified'			=> $this->input->get('verified') < 0 || is_null($this->input->get('verified')) ? null : (int)$this->input->get('verified'),
			'status'			=> $this->input->get('status') < 0 ? null : (int)$this->input->get('status'),
		];

		$results = $this->book_exhibition_model->get_all($filter_data)['rows'] ?? [];

		if (empty($results)) {
			$this->session->set_flashdata('error_message', _l('no_record_found'));
			redirect(site_url('admin/exhibition_authors'), 'refresh');
		}

		$author_invites = [];

		$filename = 'exhibition_author_invites_' . date('Y_m_d_H_i_s') . '.csv';

		$this->load->model('localisation/State_model', 'state_model');
		$this->load->model('localisation/City_model', 'city_model');

		$i = 0;

		foreach ($results as $key => $result) {
			$invite_status = '';

			switch ($result['status']) {
				case 1:
					$invite_status = 'Accepted';
					break;

				case 2:
					$invite_status = 'Rejected';
					break;

				default:
					$invite_status = 'Pending';
					break;
			}

			$user_info 	= $this->user_model->get($result['user_id']);
			$state_info = $this->state_model->get($result['state_id']);
			$city_info 	= $this->city_model->get($result['city_id']);
			$event_info = $this->event_model->get($result['event_id']);

			$author_invites[] = [
				'id'				=> $i + 1,
				'event'				=> $event_info['name'] ?? '',
				'user_id'			=> $result['user_id'],
				'author_name'		=> $result['name'],
				'author_email'		=> $result['email'],
				'author_mobile'		=> $result['mobile'],
				'invite_status'		=> $invite_status,
				'no_of_guest'		=> $result['guest_count'],
				'verified'			=> $result['verified_count'],
				'state'				=> $state_info['name'] ?? '',
				'city'				=> $city_info['name'] ?? '',
				'location'			=> $user_info['location'],
				'verified'			=> $result['verified'],
				'site_code'			=> $user_info['source'],
				'date_added'		=> formatDate($result['date_added'])
			];

			$i++;
		}

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

		$headers = isset($author_invites[0]) ? array_keys($author_invites[0]) : [];

		if (!$headers) {
			exit(_l('error_empty'));
		}

		$fp = fopen('php://output', 'w');

		self::_writeRowToCsv($author_invites, $fp, $headers);

		fclose($fp);

		exit();
	}
}
