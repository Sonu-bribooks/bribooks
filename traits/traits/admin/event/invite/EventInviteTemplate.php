<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait EventInviteTemplate {
	public function event_invite_template($param1 = null, $param2 = null) {
		$data['fields'] = [
			'sn',
			'id',
			'event_id',
			'challenge_id',
			'challenge_type',
			'type',
			'logo',
			'actions',
		];

		if ($param1 == 'add') {
			$data 			= $this->input->post();
			$this->event_invite_template_model->add($data);
			redirect(base_url('admin/event_invite_template'), 'refresh');
		} elseif ($param1 == 'edit') {
			$data 			= $this->input->post();
			$this->event_invite_template_model->edit($param2, $data);
			redirect(base_url('admin/event_invite_template'), 'refresh');
		} elseif ($param1 == 'delete') {
			$this->event_invite_template_model->delete($param2);
			redirect(base_url('admin/event_invite_template'), 'refresh');
		}

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event_invite_template');
		$data['action_add'] 	= base_url('admin/event_invite_template_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_event_invite_template');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/event_invite_template_form/edit/',
			],
			[
				'key'	=> 'build_pass',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_invite_template_build_pass/',
			],
			[
				'key'	=> 'download_entry_pass_zip',
				'url'	=> 'admin/download_entry_pass_zip/',
			],
			[
				'key'	=> 'download_certificate_zip',
				'url'	=> 'admin/download_certificate_zip/',
			],
			[
				'key'	=> 'download_author_wall_zip',
				'url'	=> 'admin/download_author_wall_zip/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event_invite_template/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function event_invite_template_form($param1 = null, $param2 = null) {
		if ($param1 == 'add') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('add_event_invite_template');
			$data['action'] 						= base_url('admin/event_invite_template/add');
		} elseif ($param1 == 'edit') {
			$data['page_name'] 						= 'generic/form';
			$data['page_title'] 					= _l('event_invite_template');
			$data['action'] 						= base_url('admin/event_invite_template/edit/' . (int)$param2);

			$data['id'] 							= (int)$param2;
			$info 									= $this->event_invite_template_model->get($param2);
			$event_info 							= $this->event_model->get($info['event_id']);

			$event_name							 = ($info['event_id'] == 0) ? 'Generic' : $event_info['name'];
		}

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'challenge_type',
			'label'		=> _l('select_challenge_type'),
			'required'	=> true,
			'value'		=> $info['challenge_type'] ?? '',
			'ajax_options'=> base_url('admin/ajax_search_certificate_challenge?target=challenge_id&input=select2&includes=challenge_type,event_id'),
			'options'	=> CHALLENGE_TYPES,
		];

		$data['fields'][] = [
			'type'		=> 'select2',
			'key'		=> 'event_id',
			'label'		=> _l('select_event'),
			'required'	=> true,
			'value'		=> [
				'value' => $info['event_id'] ?? '',
				'label' => $event_name ?? '',
			],
			'ajax_url'	=> base_url('admin/ajax_search_events'),
			'ajax_options'=> base_url('admin/ajax_search_certificate_challenge?target=challenge_id&input=select2&includes=challenge_type,event_id'),
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'challenge_id',
			'label'		=> _l('select_challenge'),
			'required'	=> false,
			'value'		=> $info['challenge_id'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('select_type'),
			'required'	=> true,
			'value'		=> $info['type'] ?? 'user',
			'options'	=> [
				[
					'value' => 'user',
					'label' => _l('user'),
				],
				[
					'value' => 'school',
					'label' => _l('school'),
				],
				[
					'value' => 'teacher',
					'label' => _l('teacher'),
				],
			],
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'logo',
			'label'		=> _l('logo'),
			'required'	=> true,
			'value'		=> $info['logo'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'bestseller_certificate_image',
			'label'		=> _l('bestseller_certificate_image'),
			'required'	=> false,
			'value'		=> $info['bestseller_certificate_image'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'rank_x_axis',
			'label'		=> _l('horizontal_rank_position'),
			'required'	=> false,
			'value'		=> $info['rank_x_axis'] ?? 2890,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'rank_y_axis',
			'label'		=> _l('vertical_rank_position'),
			'required'	=> false,
			'value'		=> $info['rank_y_axis'] ?? 2770,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'unique_id_x_axis',
			'label'		=> _l('horizontal_unique_id_position'),
			'required'	=> false,
			'value'		=> $info['unique_id_x_axis'] ?? 4325,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'unique_id_y_axis',
			'label'		=> _l('vertical_unique_id_position'),
			'required'	=> false,
			'value'		=> $info['unique_id_y_axis'] ?? 3070,
		];

		$data['fields'][] = [
			'type'		=> 'image',
			'key'		=> 'jury_certificate_image',
			'label'		=> _l('jury_certificate_image'),
			'required'	=> false,
			'value'		=> $info['jury_certificate_image'] ?? '',
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'jury_rank_x_axis',
			'label'		=> _l('jury_horizontal_rank_position'),
			'required'	=> false,
			'value'		=> $info['jury_rank_x_axis'] ?? 2890,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'jury_rank_y_axis',
			'label'		=> _l('jury_vertical_rank_position'),
			'required'	=> false,
			'value'		=> $info['jury_rank_y_axis'] ?? 2770,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'jury_unique_id_x_axis',
			'label'		=> _l('jury_horizontal_unique_id_position'),
			'required'	=> false,
			'value'		=> $info['jury_unique_id_x_axis'] ?? 4325,
		];

		$data['fields'][] = [
			'type'		=> 'number',
			'key'		=> 'jury_unique_id_y_axis',
			'label'		=> _l('jury_vertical_unique_id_position'),
			'required'	=> false,
			'value'		=> $info['jury_unique_id_y_axis'] ?? 3070,
		];

		$data['fields'][] = [
			'type' 		=> 'color',
			'key' 		=> 'color_code',
			'label' 	=> _l('color_code'),
			'required' 	=> false,
			'value' 	=> $info['color_code'] ?? '',
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_event_invite_template() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->event_invite_template_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$event_info = $this->event_model->get($result['event_id']);

			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'event_id'				=> $result['event_id'],
				'challenge_id'			=> $result['challenge_id'],
				'challenge_type'		=> $result['challenge_type'],
				'type'					=> $result['type'],
				'logo'					=> $result['logo'],
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	public function event_invite_template_build_pass($template_id = 0) {
		$template_info = $this->event_invite_template_model->get($template_id);

		if (empty($template_info)) {
			$this->session->set_flashdata('error_message', _li('event_invite_template_not_found'));
			redirect(base_url('admin/event_invite_template'), 'refresh');
		}

		$this->cron_model->add([
			'code'		=> sprintf('event_%s_invite_%d', $template_info['type'], (int)$template_id),
			'site_id'	=> 1,
			'action'	=> sprintf('alert_model->buildInvite%sPassCron', ucwords($template_info['type'])),
			'data'		=> [$template_info['id']],
			'alert_date'	=> date('Y-m-d H:i:00', strtotime('+1 minutes')),
		]);

		$this->session->set_flashdata('flash_message', _li('event_invite_template_build_pass_is_added'));
		redirect(base_url('admin/event_invite_template'), 'refresh');
	}

	public function download_entry_pass_zip($id = 0) {
		$template_info = $this->event_invite_template_model->get($id);

		if (empty($template_info)) {
			$this->session->set_flashdata('error_message', _li('event_invite_template_not_found'));
			redirect(base_url('admin/event_invite_template'), 'refresh');
		}

		if ($template_info['type'] == 'school') {
			self::_download_school_entry_pass_zip($template_info);
		} else {
			self::_download_user_entry_pass_zip($template_info);
		}

		$this->session->set_flashdata('flash_message', _li('zip_downloaded'));
		redirect(base_url('admin/event_invite_template'), 'refresh');
	}

	public function download_certificate_zip($id = 0, $type = '') {
		$template_info = $this->event_invite_template_model->get($id);

		if (empty($template_info)) {
			$this->session->set_flashdata('error_message', _li('event_invite_template_not_found'));
			redirect(base_url('admin/event_invite_template'), 'refresh');
		}

		if ($template_info['type'] == 'school') {
			self::_download_school_certificate_zip($template_info);
		} else {
			self::_download_user_certificate_zip($template_info);
		}

		$this->session->set_flashdata('flash_message', _li('zip_downloaded'));
		redirect(base_url('admin/event_invite_template'), 'refresh');
	}

	private function _download_user_entry_pass_zip($template_info = []) {
		if (!empty($invite_guests = $this->event_user_invite_model->get_all([
			'template_id'	   => $template_info['id'] ?? 0,
			'status'			=> 1,
			'is_pdf'			=> 1
		])['rows'] ?? [])) {
			$this->load->library('S3_lib', 's3_lib');
			$this->load->library('zip');

			$s3_dirname = (ENVIRONMENT === 'production' ? 'event_pass_pdf' : 'event_pass_pdf/test');

			foreach($invite_guests as $invite_guest_info) {
				$book_info  = $this->book_model->get($invite_guest_info['book_id'] ?? 0);

				$pdf_path = FCPATH . sprintf('uploads/eventpass/pdfs/user_entry_pass_%s.pdf', $invite_guest_info['id']);

				if (file_exists($pdf_path)) {
					$this->zip->add_data(
						vsprintf('%s/%s.pdf', [
							sprintf('event_%s', $invite_guest_info['event_id'] ?? 0),
							strtoupper(str_replace(' ', '_', $book_info['name']))
						]),
						file_get_contents($pdf_path)
					);
				}
			}

			$this->zip->download(sprintf('USER_INVITE_ZIP_EVENT_%s_%s', ($template_info['event_id'] ?? 0), date('Y')));
		}

		$this->session->set_flashdata('flash_message', _li('user_zip_downloaded'));
		redirect(base_url('admin/event_invite_template'), 'refresh');
	}

	private function _download_school_entry_pass_zip($template_info = []) {
		if (!empty($invite_guests = $this->event_school_invite_model->get_all([
			'template_id'	   => $template_info['id'] ?? 0,
			'status'			=> 1,
			'is_pdf'			=> 1
		])['rows'] ?? [])) {
			$this->load->library('S3_lib', 's3_lib');
			$this->load->library('zip');

			$s3_dirname = (ENVIRONMENT === 'production' ? 'event_pass_pdf' : 'event_pass_pdf/test');

			foreach($invite_guests as $invite_guest_info) {
				$site_info  = $this->site_model->get($invite_guest_info['site_id'] ?? 0);

				$pdf_path = FCPATH . sprintf('uploads/eventpass/pdfs/school_entry_pass_%s.pdf', $invite_guest_info['id']);

				if (file_exists($pdf_path)) {
					$this->zip->add_data(
						vsprintf('%s/%s.pdf', [
							sprintf('event_%s', $invite_guest_info['event_id'] ?? 0),
							strtoupper(str_replace(' ', '_', $site_info['name']))
						]),
						file_get_contents($pdf_path)
					);
				}
			}

			$this->zip->download(sprintf('SCHOOL_INVITE_ZIP_EVENT_%s_%s', ($template_info['event_id'] ?? 0), date('Y')));
		}

		$this->session->set_flashdata('flash_message', _li('school_zip_downloaded'));
		redirect(base_url('admin/event_invite_template'), 'refresh');
	}

	private function _download_user_certificate_zip($template_info = []) {
		if (!empty($invite_guests = $this->event_user_invite_model->get_all([
			'template_id'	   => $template_info['id'] ?? 0,
			'status'			=> 1,
		])['rows'] ?? [])) {
			$this->load->model('certificate/Certificate_model', 'certificate_model');

			$this->load->library('zip');

			foreach($invite_guests as $invite_guest_info) {
				if (empty($cert_template_info = $this->certificate_template_model->get_all([
					'event_id'	   		=> $invite_guest_info['event_id'],
					'challenge_id'		=> $invite_guest_info['challenge_id'],
					'challenge_type'	=> $invite_guest_info['challenge_type'],
					'is_jury'		   	=> $invite_guest_info['is_jury'] ?? 0,
				])['rows'][0] ?? [])) continue;

				if (empty($cert_info = $this->certificate_model->get_all([
					'certificate_template_id'  	=> $cert_template_info['id'],
					'book_id' 					=> $invite_guest_info['book_id'],
				])['rows'][0] ?? [])) continue;

				$book_info  = $this->book_model->get($invite_guest_info['book_id'] ?? 0);

				if (!empty($invite_guest_info['is_jury'])) {
					$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), $template_info['jury_certificate_image']);

					$rank_x_axis 		= $template_info['jury_rank_x_axis'] ?? 2890;
					$rank_y_axis 		= $template_info['jury_rank_y_axis'] ?? 2770;
					$unique_id_x_axis 	= $template_info['jury_unique_id_x_axis'] ?? 4325;
					$unique_id_y_axis 	= $template_info['jury_unique_id_y_axis'] ?? 3070;
				} else {
					$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), $template_info['bestseller_certificate_image']);

					$rank_x_axis 		= $template_info['rank_x_axis'] ?? 2890;
					$rank_y_axis 		= $template_info['rank_y_axis'] ?? 2770;
					$unique_id_x_axis 	= $template_info['unique_id_x_axis'] ?? 4325;
					$unique_id_y_axis 	= $template_info['unique_id_y_axis'] ?? 3070;
				}

				list($image_width, $image_height) = getimagesize($image_template);

				$image 		= imagecreatefromjpeg($image_template);

				$qr_file 	= generateQrCode('http://www.bribooks.com/verifycertificate/' . $cert_info['unique_id'], 20, 2);
				$qr_image 	= imagecreatefrompng(FCPATH . $qr_file);

				$darkgrey 	= imagecolorallocate($image, 70,  70, 70);
				$grey 		= imagecolorallocate($image, 110, 110, 110);
				$black 		= imagecolorallocate($image, 0, 0, 0);


				$image_width	= imagesx($image);
				$image_height	= imagesy($image);
				$qr_image_width		= imagesx($qr_image);
				$qr_image_height 	= imagesy($qr_image);

				$font_path 	= FCPATH . 'assets/global/fonts/Poppins-SemiBold.otf';

				$font_size = 90;

				$date   = $cert_info['date_added'];

				imagettftext($image, $font_size, 0, 370, 1820, $darkgrey, $font_path, strtoupper($book_info['author_name']));
				imagettftext($image, $font_size, 0, 370, 2420, $darkgrey, $font_path, strtoupper($book_info['name']));
				imagettftext($image, 70, 0, $rank_x_axis, $rank_y_axis, $black, $font_path, sprintf('%02d', strtoupper($cert_info['rank'])));

				imagettftext($image, 38, 0, $unique_id_x_axis, $unique_id_y_axis, $darkgrey, $font_path, $cert_info['unique_id']);
				imagettftext($image, 50, 0, 4355, 3230, $darkgrey, $font_path, date('d/m/Y', strtotime($date)));

				$zoom = 1.9;

				$qr_x_axis = 630;
				$qr_y_axis = 800;

				imagecopyresampled(
					$image,
					$qr_image,
					($image_width - $qr_image_width / $zoom - $qr_x_axis),
					($image_height - $qr_image_height / $zoom - $qr_y_axis),
					0,
					0,
					$qr_image_width / $zoom,
					$qr_image_height / $zoom,
					$qr_image_width,
					$qr_image_height
				);

				$filename = FCPATH . sprintf('uploads/test/tempcert_%s.png', uniqid());

				imagejpeg($image, $filename);
				imagedestroy($image);

				$html = sprintf('<style>@page{margin:0;padding:0;}</style><img
					src="%s"
					style="width:100%%;max-height:100%%;"
				/>', base_url(str_replace(FCPATH, '', $filename)));

				$dompdf = new Dompdf();
				$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
				$dompdf->set_option('isJavascriptEnabled', true);
				$dompdf->set_option('isRemoteEnabled', true);
				$dompdf->set_option('isHtml5ParserEnabled', true);

				// (Optional) Setup the paper size and orientation
				$dompdf->setPaper('A3', 'landscape');

				$dompdf->render();

				$pdf_output = $dompdf->output();

				$pdf_name = str_replace(' ', '_', strtoupper($book_info['name'])) . '_' . $cert_info['event_id'] . '.pdf';

				$this->zip->add_data($pdf_name, $pdf_output);
			}

			$this->zip->download(sprintf('USER_CERTIFICATE_ZIP_EVENT_%s_%s', ($template_info['event_id'] ?? 0), date('Y')));
		}

		$this->session->set_flashdata('flash_message', _li('user_zip_downloaded'));
		redirect(base_url('admin/event_invite_template'), 'refresh');
	}

	private function _download_school_certificate_zip($template_info = []) {
		if (!empty($invite_guests = $this->event_school_invite_model->get_all([
			'template_id'	   => $template_info['id'] ?? 0,
			'status'			=> 1,
		])['rows'] ?? [])) {
			$this->load->model('certificate/Certificate_model', 'certificate_model');
			$this->load->library('zip');

			$event_info  = $this->event_model->get($template_info['event_id'] ?? 0);

			foreach($invite_guests as $invite_guest_info) {
				$site_info  = $this->site_model->get($invite_guest_info['site_id'] ?? 0);

				$state_name 	= $this->state_model->get($site_info['state_id'] ?? 0)['name'] ?? 'State';
				$city_name 		= $this->city_model->get($site_info['city_id'] ?? 0)['name'] ?? 'City';

				$school_name_array  = explode(',', $site_info['name']);
				$school_name		= $school_name_array[0];

				$image_template = sprintf('%spublic/EventGallery/%s', $this->config->item('cloudfront_url'), $template_info['bestseller_certificate_image']);

				list($image_width, $image_height) = getimagesize($image_template);

				$image 		= imagecreatefromjpeg($image_template);

				$darkgrey 	= imagecolorallocate($image, 70, 70, 70);
				$grey 		= imagecolorallocate($image, 110, 110, 110);
				$black 		= imagecolorallocate($image, 0, 0, 0);

				$font_path 	= FCPATH . 'assets/global/fonts/Times-New-Roman.otf';

				$school_name_text   = strtoupper($school_name);
				$font_size  		= 70;
				$max_width  		= $image_width * 0.70; // text allowed up to 70% width
				$y_school   		= 1840;

				do {
					$bbox 					= imagettfbbox($font_size, 0, $font_path, $school_name_text);
					$school_name_text_width = abs($bbox[2] - $bbox[0]);

					if ($school_name_text_width > $max_width) {
						$font_size -= 3;
					}
				} while ($school_name_text_width > $max_width);

				$x_school = ($image_width - $school_name_text_width) / 2;

				imagettftext($image, $font_size, 0, $x_school, $y_school, $black, $font_path, $school_name_text);

				$city_text 	= ucfirst($city_name);
				$city_y 	= 2220;

				$city_line_start_x = 980;
				$city_line_end_x   = 2480;

				$city_line_width 	= $city_line_end_x - $city_line_start_x;

				$bbox 				= imagettfbbox($font_size, 0, $font_path, $city_text);
				$city_text_width 	= abs($bbox[2] - $bbox[0]);

				$city_x = $city_line_start_x + (($city_line_width - $city_text_width) / 2);

				imagettftext($image, $font_size, 0, $city_x, $city_y, $black, $font_path, strtoupper($city_text));

				$state_text = ucfirst($state_name);
				$state_y 	= 2220;

				$state_line_start_x = 2800;
				$state_line_end_x   = 4400;

				$state_line_width 	= $state_line_end_x - $state_line_start_x;

				$bbox 				= imagettfbbox($font_size, 0, $font_path, $state_text);
				$state_text_width 	= abs($bbox[2] - $bbox[0]);

				$state_x = $state_line_start_x + (($state_line_width - $state_text_width) / 2);

				imagettftext($image, $font_size, 0, $state_x, $state_y, $black, $font_path, strtoupper($state_text));

				imagettftext($image, 55, 0, 4770, 3240, $black, $font_path, date('d/m/Y', strtotime($event_info['selling_end_date'])));

				$filename = FCPATH . sprintf('uploads/test/tempcert_%s.png', uniqid());

				imagejpeg($image, $filename);
				imagedestroy($image);

				$html = sprintf('<style>@page{margin:0;padding:0;size: 5400px 3846px;}</style><img
					src="%s"
					style="width:100%%;max-height:100%%;"
				/>', base_url(str_replace(FCPATH, '', $filename)));

				$dompdf = new Dompdf();
				$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
				$dompdf->set_option('isJavascriptEnabled', true);
				$dompdf->set_option('isRemoteEnabled', true);
				$dompdf->set_option('isHtml5ParserEnabled', true);

				$dompdf->setPaper('A4', 'landscape');

				$dompdf->render();

				$pdf_output = $dompdf->output();

				$pdf_name = str_replace(' ', '_', strtoupper($school_name)) . '.pdf';

				$this->zip->add_data($pdf_name, $pdf_output);
			}

			$this->zip->download(sprintf('SCHOOL_CERTIFICATE_ZIP_EVENT_%s_%s', ($template_info['event_id'] ?? 0), date('Y')));
		}

		$this->session->set_flashdata('flash_message', _li('user_zip_downloaded'));
		redirect(base_url('admin/event_invite_template'), 'refresh');
	}

	public function download_author_wall_zip($id = 0, $type = '') {
		$template_info = $this->event_invite_template_model->get($id);

		if (empty($template_info)) {
			$this->session->set_flashdata('error_message', _li('event_invite_template_not_found'));
			redirect(base_url('admin/event_invite_template'), 'refresh');
		}

		if (!empty($invite_guests = $this->event_user_invite_model->get_all([
			'template_id'	   => $template_info['id'] ?? 0,
			'status'			=> 1,
		])['rows'] ?? [])) {
			$this->load->library('zip');
			$this->load->library('S3_lib', 's3_lib');

			$this->s3_lib->setBucket('bbprivateimagesin');

			$dir_name = (ENVIRONMENT === 'production' ? 'aadhaar_images' : 'aadhaar_images/test');

			foreach($invite_guests as $invite_guest_info) {
				$wall_info = $this->db->get_where('author_wall', [
					'book_id' => (int)$invite_guest_info['book_id'],
				])->row_array();

				$book_info = $this->book_version_model->getByVersion($wall_info['book_id'], $wall_info['version'] ?? 1);

				if (empty($book_info)) continue;

				$book_info['book_desc'] 			= $wall_info['about_the_book'];
				$book_info['event_id'] 				= $wall_info['event_id'];
				$book_info['rank'] 					= $wall_info['type'] . '-' . $wall_info['book_rank'];
				$book_info['full_url']				= false;

				if (!empty($wall_info['author_image'])) {
					$book_info['author_front_image'] 	= str_replace('https://youbooks-storage-5fd6173683748-webdev.s3.amazonaws.com/public/', '', $wall_info['author_image']);
				} else {
					$book_info['author_front_image'] = $this->s3_lib->getUrl($invite_guest_info['author_image'], $dir_name, false, 30);
					$book_info['full_url'] = true;
				}

				$multiplier = 0.75;
				// $multiplier = 0.25;

				$data['width'] 				= 3460 * $multiplier;
				$data['height'] 			= 9216 * $multiplier;
				$data['padding']			= 420 * $multiplier;
				$data['gap']				= [
					'author_image' 			=> [
						'h' => 175 * $multiplier,
						'v' => 250 * $multiplier,
					],
					'about_book' 			=> [
						'v' => 145 * $multiplier,
					],
					'cover_image' 			=> [
						'v' => 350 * $multiplier,
						'h'	=> 160 * $multiplier,
					],
				];
				$data['font_size']			= [
					'author_name'			=> 200 * $multiplier / (strlen($book_info['author_name']) > 15 ? 1.5 : 1),
					'author_of'				=> 100 * $multiplier,
					'book_name'				=> 190 * $multiplier / (strlen($book_info['name']) > 15 ? 1.3 : 1),
					'about_book'			=> 100 * $multiplier,
					'qr_code'				=> 180 * $multiplier,
					'tag'					=> 150 * $multiplier,
				];
				$data['image_size']			= [
					'author_image'	=> 680 * $multiplier,
					'cover_image_w'	=> 2280 * $multiplier,
					'cover_image_h'	=> 1680 * $multiplier,
					'qr_image'		=> 1250 * $multiplier,
				];
				$data['multiplier']			= $multiplier;
				$data['backcover']			= [
					'book_name'			=> (strlen($book_info['name']) > 15 ? 57 : 84) * $multiplier,
					'author_name'		=> (strlen($book_info['author_name']) > 10 ? 32 : 42) * $multiplier,
					'author_image_font'	=> (strlen($book_info['author_name']) > 10 ? 24 : 36) * $multiplier,
					'author_bio'		=> 30 * $multiplier,
				];

				$data['book'] 				= $book_info;
				$data['book_code'] 			= _o_b_code($book_info['book_id'], $book_info['version'], 'paperback');

				$data['qrcode'] 			= base_url(self::_getPrintAuthorWallQrCode($book_info));
				$data['barcode'] 			= self::_getPrintAuthorWallBarcode(!empty($book_info['isbn']) ? $book_info['isbn'] : $book_info['unique_id']);

				$html = $this->load->view('frontend/default/print_author_wall', $data, true);

				$dompdf = new Dompdf([
					// 'debugLayout' 	=> true,
					// 'debugCss'		=> true,
					// 'debugPng'		=> true,
				]);

				$dompdf->set_option('isJavascriptEnabled', true);
				$dompdf->set_option('isRemoteEnabled', true);
				$dompdf->set_option('dpi', 300);
				$dompdf->set_option('isHtml5ParserEnabled', true);

				$dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));

				// (Optional) Setup the paper size and orientation
				$dompdf->setPaper(
					[
						0,
						0,
						$data['width'],
						$data['height']
					],
					'portrait'
				);

				$dompdf->render();

				$pdf_output = $dompdf->output();

				$pdf_name = 'author_wall_' . str_replace('-', '_', $book_info['slug'] . '_by_' . $book_info['author_name']) . '_v' . $book_info['version'] . '.pdf';

				$this->zip->add_data($pdf_name, $pdf_output);
			}

			$this->zip->download(sprintf('AUTHOR_WALL_ZIP_EVENT_%s_%s', ($template_info['event_id'] ?? 0), date('Y')));
		}

		$this->session->set_flashdata('flash_message', _li('zip_downloaded'));
		redirect(base_url('admin/event_invite_template'), 'refresh');
	}

	private function _getPrintAuthorWallBarcode($data = 0) {
		$data = str_replace(['-', ' '], '', $data);
		$file = 'uploads/pdfs/' . $data . '.png';
		$barcode = new \Com\Tecnick\Barcode\Barcode();
		$bobj = $barcode->getBarcodeObj(
			'C128',
			$data,
			480 * 3.6,
			120 * 3.6,
			'black',
			array(15, 15, 0, 15)
		)->setBackgroundColor('white');

		return $bobj->getHtmlDiv();
	}

	private function _getPrintAuthorWallQrCode($book_info = [], $size = 60) {
		$file = 'uploads/pdfs/qrcode_' . $book_info['slug'] . '.png';

		return generateQrCode(USER_URL . 'bookstore/' . $book_info['slug'], 20, 2, $file);
	}
}
