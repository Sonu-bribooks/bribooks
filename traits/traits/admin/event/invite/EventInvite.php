<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;
trait EventInvite {
	private $_event_user_invite_filters = [];
	private $_event_school_invite_filters = [];

	private function _initFilters(&$data = [], $type = 'user') {
		$data['filters'][] 		= [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
				'label' => $event_name ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
		];

		$data['filters'][]		= [
			'type'		=> 'select',
			'key'		=> 'status',
			'label'		=> _l('select_status'),
			'required'	=> false,
			'value'		=> 1,
			'options'	=> [
				[
					'value' => 0,
					'label' => _l('pending'),
				],
				[
					'value' => 1,
					'label' => _l('accepted'),
				],
				[
					'value' => 2,
					'label' => _l('rejected'),
				],
			],
		];

		$data['filters'][]		= [
			'type'		=> 'select',
			'key'		=> 'verified',
			'label'		=> _l('select_verified'),
			'required'	=> false,
			'value'		=> 0,
			'options'	=> [
				[
					'value' => 0,
					'label' => _l('no'),
				],
				[
					'value' => 1,
					'label' => _l('yes'),
				],
			],
		];

		$data['filters'][]		= [
			'type'		=> 'select',
			'key'		=> 'user_type',
			'label'		=> _l('select_user_type'),
			'required'	=> false,
			'value'		=> 'student',
			'options'	=> [
				[
					'value' => 'user',
					'label' => _l('user'),
				],
				[
					'value' => 'teacher',
					'label' => _l('teacher'),
				],
				[
					'value' => 'school',
					'label' => _l('school'),
				],
			],
		];

		// $this->_event_user_invite_filters = $data['filters'];

		$this->{sprintf('_event_%s_invite_filters', $type)} = $data['filters'];
	}

	public function event_user_invite($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'event_id',
			'event',
			'type',
			'name',
			'rank',
			'invite_status',
			'guest_count',
			'verified',
			'date_modified',
			'download',
			'actions',
		];

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_user_invite');
		$data['action_add'] 	= '';
		// $data['is_auto_reload'] = 15000;
		$data['action_ajax'] 	= base_url('admin/ajax_event_invite/user');
		$data['action_export'] 	= base_url('admin/ajax_event_invite_export/user');
		$data['actions'] 		= [];

		self::_initFilters($data);

		$this->load->view('backend/index', $data);
	}

	public function event_school_invite($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'event_id',
			'event',
			'type',
			'name',
			'rank',
			'invite_status',
			'guest_count',
			'verified',
			'date_modified',
			'download',
			'actions',
		];

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_school_invite');
		$data['action_add'] 	= '';
		// $data['is_auto_reload'] = 15000;
		$data['action_ajax'] 	= base_url('admin/ajax_event_invite/school');
		$data['action_export'] 	= base_url('admin/ajax_event_invite_export/school');
		$data['actions'] 		= [];

		self::_initFilters($data, 'school');

		$this->load->view('backend/index', $data);
	}

	public function ajax_event_invite($type = 'user') {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$json = self::_format_event_invite($filter_data, $type);

		output_json($json);
	}

	public function ajax_event_invite_export($filter_data, $type = 'user') {
		$json = self::_format_event_invite($filter_data, $type, true);

		self::_downloadCsv($json['data'], 'invites_');

		output_json($json);
	}

	private function _format_event_invite($filter_data = [], $type = 'user', $export = false) {
		$temp_data = [];
		self::_initFilters($temp_data, $type);

		foreach ($this->{sprintf('_event_%s_invite_filters', $type)} as $key => $item) {
			if ($this->input->get($item['key'])) {
				$filter_data[$item['key']] = is_numeric($this->input->get($item['key']))
					? (int)$this->input->get($item['key'])
					: $this->input->get($item['key']);
			}
		}

		$this->load->library('S3_lib', 's3_lib');
		$this->s3_lib->setBucket('bbprivateimagesin');

		$dir_name = (ENVIRONMENT === 'production' ? 'aadhaar_images' : 'aadhaar_images/test');

		$results = $this->{sprintf('event_%s_invite_model', $type)}->get_all($filter_data);

		$data['data'] 				= [];
		$data['recordsTotal'] 		= $results['total'];
		$data['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

			if ($type == 'school') {
				$site_info = $this->site_model->get($result['site_id'] ?? 0);
				$name 		= vsprintf(_l("%s {$br_tag}%s {$br_tag}%s {$br_tag}%s{$br_tag}"), [
					$site_info['name'],
					$site_info['authorized_person'],
					$site_info['owner_email'],
					$site_info['owner_mobile'],
				]);
				$rank = $result['school_rank'];
			} else {
				$user_info 	= $this->user_model->get($result['user_id'] ?? 0);
				$book_info 	= $this->book_model->get($result['book_id'] ?? 0);
				$name 		= vsprintf(_l("%s {$br_tag}%s {$br_tag}%s {$br_tag}%s{$br_tag}") . ($export ? '%s-%s' : '<span class="badge badge-%s">%s</span>'), [
					$book_info['name'],
					$book_info['author_name'],
					$user_info['email'],
					$user_info['mobile'],
					$result['is_jury'] ? 'warning' : 'success',
					$result['is_jury'] ? _l('jury') : _l('best_seller'),
				]);
				$rank = $result['book_rank'];
			}
			$event_info = $this->event_model->get($result['event_id'] ?? 0);

			$invite_status = '';
			switch ($result['status']) {
				case 1:
					$invite_status = $export ? _l('Accepted') : '<span class="text-success">Accepted</span>';
					break;

				case 2:
					$invite_status = $export ? _l('Rejected') : '<span class="text-danger">Rejected</span>';
					break;

				default:
					$invite_status = $export ? _l('Pending') : '<span class="text-warning">Pending</span>';
					break;
			}

			$downloads = [];

			if (!empty($result['author_image'])) {
				$downloads[] = $export ? $this->s3_lib->getUrl($result['author_image'], $dir_name, false, 120) : render_url($this->s3_lib->getUrl($result['author_image'], $dir_name, false, 30), 'author');
			}

			if (!empty($result['author_aadhaar_image'])) {
				$downloads[] = $export ? $this->s3_lib->getUrl($result['author_aadhaar_image'], $dir_name, false, 120) : render_url($this->s3_lib->getUrl($result['author_aadhaar_image'], $dir_name, false, 30), 'author_aadhaar');
			}

			if (!empty($result['guest_1_aadhaar_image'])) {
				$downloads[] = $export ? $this->s3_lib->getUrl($result['guest_1_aadhaar_image'], $dir_name, false, 120) : render_url($this->s3_lib->getUrl($result['guest_1_aadhaar_image'], $dir_name, false, 30), 'guest_1_aadhaar');
			}

			if (!empty($result['guest_2_aadhaar_image'])) {
				$downloads[] = $export ? $this->s3_lib->getUrl($result['guest_2_aadhaar_image'], $dir_name, false, 120) : render_url($this->s3_lib->getUrl($result['guest_2_aadhaar_image'], $dir_name, false, 30), 'guest_2_aadhaar');
			}

			if ($result['status'] == 1) {
				$downloads[] = render_url(base_url("admin/download_entry_pass/{$type}/{$result['id']}"), 'download_entry_pass');
			}

			$br_tag = $export ? "\n" : '<br>';

			$data['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event_id'				=> $result['event_id'],
				'event'					=> $event_info['name'] ?? '',
				'type'					=> $result['challenge_type'],
				'name'					=> $name,
				'rank'					=> $rank,
				'invite_status'			=> $invite_status,
				'guest_count'			=> $result['no_of_guest'],
				'verified'				=> $export ? $result['verified'] : sprintf("%s {$br_tag} %d %s", _sd($result['verified']), $result['guest_count'], _l('guests')),
				'date_modified'			=> $result['date_modified'],
				'download'				=> !empty($downloads)
					? implode($br_tag, $downloads)
					: '',
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		return $data;
	}

	public function download_entry_pass($type = 'user', $id = 0) {
		if (empty($id)) return;

		if (empty($invite_guest_info = $this->{sprintf('event_%s_invite_model', $type)}->get($id))) {
			return;
		}

		if (empty($template_info = $this->event_invite_template_model->get($invite_guest_info['template_id']))) {
			return;
		}

		if ($type == 'school') {
			self::_download_school_entry_pass($template_info, $invite_guest_info);
		} else {
			self::_download_user_entry_pass($template_info, $invite_guest_info);
		}
	}

	private function _download_user_entry_pass($template_info = [], $invite_guest_info = []) {
		$user_info	= $this->user_model->get($invite_guest_info['user_id'] ?? 0);
		$book_info  = $this->book_model->get($invite_guest_info['book_id'] ?? 0);
		$site_info  = $this->site_model->get($user_info['site_id'] ?? 0);
		$city_info  = $this->city_model->get($user_info['city_id'] ?? 0);
		$state_info	= $this->state_model->get($user_info['state_id'] ?? 0);

		$user_details_info = $this->db->get_where('user_details', ['user_id' => $user_info['id']])->row_array();

		$s3_dirname = $this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf');

		$book_rank = sprintf('%s # %s',
			(!empty($invite_guest_info['is_jury']) ? 'JURY RANK' : 'RANK'),
			$invite_guest_info['book_rank']
		);

		$grade = $user_info['grade'];

		$ends = array('th','st','nd','rd','th','th','th','th','th','th');
		if (($grade%100) >= 11 && ($grade%100) <= 13)
		$grade = $grade . 'th';
		else
		$grade = $grade . $ends[$grade%10];

		$author_image = empty($book_info['author_image']) ? base_url('uploads/user_image/placeholder.png') : $this->config->item('s3_base_url') . 'public/' . $book_info['author_image'];

		if (!empty($invite_guest_info['author_image'])) {
			$this->load->library('S3_lib', 's3_lib');
			$this->s3_lib->setBucket('bbprivateimagesin');

			$author_image = $this->s3_lib->getUrl($invite_guest_info['author_image'], (ENVIRONMENT === 'production' ? 'aadhaar_images' : 'aadhaar_images/test'), false, 30);
		} elseif(!empty( $user_details_info['image_nyaf'])) {
			$author_image = $s3_dirname . (ENVIRONMENT === 'production' ? '' : 'test/') . $user_details_info['image_nyaf'];
		}

		$head_logo = sprintf(
			'%spublic/EventGallery/%s',
			$this->config->item('cloudfront_url'),
			$template_info['logo']
		);

		$data = [
			'author_name'   => $book_info['author_name'],
			'school'        => $site_info['name'],
			'state'         => $state_info['name'] ?? '',
			'city'          => $city_info['name'] ?? '',
			'grade'         => $grade,
			'section'       => strtoupper($user_info['section']),
			'book_rank'     => $book_rank,
			'author_image'  => $author_image,
			'guest_1_name'  => $invite_guest_info['guest_1_name'],
			'guest_2_name'  => $invite_guest_info['guest_2_name'],

			'guest_1_image' => ($invite_guest_info['guest_1_relation'] === 'mother')
								? base_url('assets/images/woman.svg')
								: base_url('assets/images/man.svg'),

			'guest_2_image' => ($invite_guest_info['guest_2_relation'] === 'mother')
								? base_url('assets/images/woman.svg')
								: base_url('assets/images/man.svg'),

			'guest_2'       => (
				!empty($invite_guest_info['guest_2_name']) &&
				!empty($invite_guest_info['guest_2_relation']) &&
				!empty($invite_guest_info['guest_2_aadhaar'])
			),

			'qr_code'       => base_url(
				generateQrCode(
					USER_URL . 'author_data/' . $invite_guest_info['code'],
					25,
					2,
					"uploads/test/event_invite_{$invite_guest_info['code']}.png"
				)
			),

			'location_icon' => base_url('assets/images/location.svg'),
			'head_logo'     => $head_logo,
			'color_code'    => $template_info['color_code']
		];

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_author_single_pdf', $data, true);
		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper(array(0, 0, 360, 513.2), 'potrait');

		$dompdf->render();

		$dompdf->stream(str_replace(' ', '_', strtoupper($book_info['author_name'])) . '_' . $invite_guest_info['event_id'] . '.pdf');
	}

	private function _download_school_entry_pass($template_info = [], $invite_guest_info = []) {
		$site_info  = $this->site_model->get($invite_guest_info['site_id'] ?? 0);
		$city_info  = $this->city_model->get($site_info['city_id'] ?? 0);
		$state_info	= $this->state_model->get($site_info['state_id'] ?? 0);

		$head_logo = sprintf(
			'%spublic/EventGallery/%s',
			$this->config->item('cloudfront_url'),
			$template_info['logo']
		);

		$data = [
			'school'        => $site_info['name'],
			'state'         => $state_info['name'] ?? '',
			'city'          => $city_info['name'] ?? '',
			'school_rank'   => $invite_guest_info['school_rank'],
			'guest_1_name'  => $invite_guest_info['guest_1_name'],

			'qr_code'       => base_url(
				generateQrCode(
					USER_URL . 'school_data/' . $invite_guest_info['code'],
					25,
					2,
					"uploads/test/event_school_invite_{$invite_guest_info['code']}.png"
				)
			),

			'location_icon' => base_url('assets/images/location.svg'),
			'head_logo'     => $head_logo,
			'color_code'    => $template_info['color_code']
		];

		$html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_single_school_pdf_template', $data, true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		$dompdf->set_option('isJavascriptEnabled', true);
		$dompdf->set_option('isRemoteEnabled', true);
		$dompdf->set_option('isHtml5ParserEnabled', true);
		$dompdf->setPaper(array(0, 0, 360, 513.2), 'potrait');

		$dompdf->render();

		$dompdf->render();

		$dompdf->stream(str_replace(' ', '_', strtoupper($site_info['name'])) . '.pdf');
	}
}
