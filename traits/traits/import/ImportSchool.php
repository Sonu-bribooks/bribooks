<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
use Spatie\Browsershot\Browsershot;

trait ImportSchool {
	private function _importSchoolLetterHead($rows = [], $map = [], $job_id = 0) {
		$this->load->library('zip');
		$this->load->library('S3_lib', 's3_lib');

		$skipped = $uploaded = 0;
		$headers = [];
		$template= $header = $footer = $subheader = '';
		$start	 = $end = null;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			if (empty($headers)) {
				$headers = array_map('trim', array_keys($row ?? []));
			}

			if (!isset($start)) {
				$start = $index;
			}

			$end = $index;

			if (!empty($data['template'])) {
				$template = $data['template'];
			} else {
				if (!empty($template)) {
					$data['template'] = $template;
				}
			}

			if (!empty($data['header'])) {
				$header = $data['header'];
			} else {
				if (!empty($header)) {
					$data['header'] = $header;
				}
			}

			if (!empty($data['subheader'])) {
				$subheader = $data['subheader'];
			} else {
				if (!empty($subheader)) {
					$data['subheader'] = $subheader;
				}
			}

			if (!empty($data['footer'])) {
				$footer = $data['footer'];
			} else {
				if (!empty($footer)) {
					$data['footer'] = $footer;
				}
			}

			self::_updateCounter($job_id);

			if (empty($data['id'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($school_info = $this->school_model->get($data['id']))) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['template'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['header'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['footer'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			self::_generateLetterHead(array_merge($row, $data), $headers, $school_info, $index);

			sleep(0.1);

			$uploaded++;

			// break;
		}

		$this->s3_lib->setBucket('bbpdfenginefiles');
		$zip_data = $this->zip->get_zip();

		if (!empty($zip_data)) {
			$s3_filename = $this->s3_lib->putData(
				sprintf('schoolletterhead_%s_%s.zip', $start, $end),
				sprintf('%sschoolletterhead_%s/%s', (ENVIRONMENT === 'production' ? '' : 'test'), date('Y'), $job_id),
				$zip_data,
				false
			);
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	public function _generateLetterHead($data = [], $headers = [], $school_info = [], $index = 0) {
		if (!empty($data['qrcode'])) {
			$data['qrcode'] = sprintf('<div style="width: 100%%; text-align: center;"><img src="%s" alt="QR Code" style="height: 130px;"></div>', base_url(generateQrCode($data['qrcode'], 20, 2, sprintf('uploads/test/import_testqr_%s.png', $school_info['id']))));
		}

		$find 			= array_map(fn($item) => sprintf('{%s}', $item), $headers);
		$data['content']= str_replace($find, $data, $data['template']);
		$html 			= $this->load->view(sprintf('common/letter_heads/generic'), $data, true);

		log_kb(compact('index', 'data', 'headers', 'school_info'));

		// echo $html; die;

		$pdf_data = Browsershot::html($html)
			->setNodeBinary('/usr/bin/node')
			->setNpmBinary('/usr/bin/npm')
			->newHeadless()
			// ->setChromePath('/home/ubuntu/.cache/puppeteer/chrome/linux-146.0.7680.153/chrome-linux64/chrome')
			->showBackground()
			->hideHeader()
			->hideFooter()
			->timeout(60)
			->waitUntilNetworkIdle()
			->delay(300)
			->scale(1)
			->setOption('args', [
				'--disable-web-security',
				'--no-sandbox',
				'--disable-setuid-sandbox',
				'--font-render-hinting=none',
			])
			->margins(0, 0, 0, 0)
			// ->paperSize($data['width'] * 0.0138889, $data['height'] * 0.0138889, 'in')
			->format('A4')
			->pdf();

		// $dompdf = new Dompdf([
		// 	// 'debugLayout' 	=> true,
		// ]);
		// $dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
		// $dompdf->set_option('isJavascriptEnabled', true);
		// $dompdf->set_option('isRemoteEnabled', true);
		// $dompdf->set_option('isHtml5ParserEnabled', true);
		// $dompdf->setPaper('A4', 'potrait');
		//
		// $dompdf->render();
		// $pdf_data = $dompdf->output();

		$filename = vsprintf('school_letter_head_%s_%s.pdf', [
			date('Y'),
			$school_info['id'],
		]);

		$this->zip->add_data($filename, $pdf_data);
	}

	private function _importSite($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['school_name'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			$country = 'India';

			if (!empty($data['country_id'])) {
				if ($country_info = $this->db->get_where('country', [
					'id' => $data['country_id']
				])->row_array()) {
					$country = $country_info['name'];
				}
			}

			$row['country'] = $country;

			if (!empty($data['email']) && !empty($this->site_model->get_all([
				'owner_email' 			=> trim($data['email']),
			])['rows'] ?? [])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			} elseif (!empty($data['mobile']) && !empty($this->site_model->get_all([
				'owner_mobile' => trim($data['mobile']),
			])['rows'] ?? [])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			self::_saveSiteData($row);

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _saveSiteData($data = []) {
		if (!empty($data) && !empty($data['school_name'])) {

			$site_info = [];

			if (empty($country_site_info = $this->site_model->getSiteByName($data['country']))) {
				$country_site_info = $this->site_model->getSiteByName('India');
			}

			if (!empty($country_site_info)) {
				$site_info = $this->site_model->get($country_site_info['id']);
			}

			if (!empty($site_info)) {
				$insert_site_data = [
					'parent_id' 		  			=> $data['parent_id'] ?? $site_info['id'],
					'can_add_site' 		  			=> 0,
					'name' 				  			=> trim($data['school_name']),
					'site_code' 		  			=> $site_info['site_code'] . '-import-' . uniqid(),
					'site_type' 		  			=> $data['site_type'] ?? 1,
					'discount_code' 	  			=> $site_info['discount_code'],
					'discount_percentage' 			=> $site_info['discount_percentage'],
					'timezone' 			  			=> $site_info['timezone'],
					'payment_gateway' 	  			=> $site_info['payment_gateway'],
					'sms_gateway' 		  			=> $site_info['sms_gateway'],
					'email_alert' 		  			=> $site_info['email_alert'],
					'address' 			  			=> $data['address'] ?? '',
					'landmark' 			  			=> $data['landmark'] ?? '',
					'pincode' 			  			=> $data['zipcode'] ?? '',
					'mobile_length' 	  			=> $site_info['mobile_length'],
					'country_code' 		  			=> $site_info['country_code'],
					'currency_code' 	  			=> $site_info['currency_code'],
					'state_id' 			  			=> $data['state_id'],
					'city_id' 			  			=> $data['city_id'],
					'base_price' 		  			=> $site_info['base_price'],
					'black_white_price'   			=> $site_info['black_white_price'],
					'ebook_price' 		  			=> $site_info['ebook_price'],
					'price_per_page' 	  			=> $site_info['price_per_page'],
					'black_white_price_per_page'   	=> $site_info['black_white_price_per_page'],
					'free_page_limit' 	  			=> $site_info['free_page_limit'],
					'hard_cover_price' 	  			=> $site_info['hard_cover_price'],
					'paperback_price' 	  			=> $site_info['paperback_price'],
					'tax' 				  			=> $site_info['tax'],
					'tax_text' 			  			=> $site_info['tax_text'],
					'owner_name' 	      			=> !empty($data['owner_name']) ? trim($data['owner_name']) : '',
					'authorized_person'   			=> !empty($data['authorized_person']) ? trim($data['authorized_person']) : '',
					'owner_email' 		  			=> $data['email'],
					'owner_mobile' 	      			=> $data['mobile'],
					'status' 			  			=> 1,
					'license_total' 	  			=> 1000,
					'license_used' 		  			=> 0,
				];

				$site_id = $this->site_model->addSite($insert_site_data);

				if (!empty($site_id)) {
					$this->site_model->editById($site_id, [
						'site_code' => get_site_code_slug(trim($data['school_name'])) . '-' . $site_id
					]);
				}

				if (!empty($data['event_id']) && !empty($site_id)) {
					self::_saveSiteEventData($data['event_id'], $site_id);
				}

				if (!empty($data['is_school_lead']) && empty($school_lead_info = $this->db->get_where('school_lead', [
					'event_id'		=> $event_info['id'],
					'site_id'		=> $site_id,
					'name' 			=> trim($data['school_name']),
					'state_id'		=> $data['state_id'] ?? 0,
					'city_id'		=> $data['city_id'] ?? 0,
					'site_type' 	=> $data['site_type'] ?? 1
				])->row_array())) {
					$this->db->insert('school_lead', [
						'event_id'			=> $event_info['id'],
						'site_id'			=> $site_id,
						'name'				=> trim($data['school_name']),
						'country'			=> $data['country'] ?? '',
						'state_id'			=> $data['state_id'] ?? 0,
						'city_id'			=> $data['city_id'] ?? 0,
						'site_type' 		=> $data['site_type'] ?? 1,
						'school_head'		=> !empty($data['owner_name']) ? trim($data['owner_name']) : '',
						'authorized_person'	=> !empty($data['authorized_person']) ? trim($data['authorized_person']) : '',
						'designation'		=> !empty($data['designation']) ? trim($data['designation']) : '',
						'email'				=> $data['email'],
						'mobile'			=> $data['mobile'],
						'mobile_verified'	=> ($data['type'] ?? '') == 'mobile',
						'email_verified'	=> ($data['type'] ?? '') == 'email',
						'status'			=> 1,
						'date_added'		=> date('Y-m-d H:i:s'),
						'date_modified'		=> date('Y-m-d H:i:s')
					]);

					$school_lead_id = $this->db->insert_id();

					$school_lead_id && $this->db->update('site', [
						'site_code' 		=> $site_info['site_code'],
						'date_modified'		=> date('Y-m-d H:i:s')
					], [
						'id'				=> $site_id
					]);
				} else {
					$school_lead_id = $school_lead_info['id'];
				}
			}
		}
	}

	private function _importSchool($rows = [], $map = [], $job_id = 0) {
		$skipped = $uploaded = 0;

		log_kb([
			'_importSchool::rows' => $rows
		]);

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['school_name'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if (empty($data['id']) &&
				!empty($data['site_id']) &&
				!empty($school_info = $this->school_model->getBySiteID($data['site_id']))
			) {
				$data['id'] = $school_info['id'];
			}

			if (!empty($data['id']) && !empty($this->school_model->get($data['id']))) {
				log_kb([
					'_importSchool::id_exist' => $data['id']
				]);
				self::_saveSchoolData($data, true);
			} else {
				// if (!empty($school_info = $this->db->get_where('schools', [
				// 	'name' 			=> trim($data['school_name']),
				// 	'state_id'		=> $data['state_id'],
				// 	'city_id'		=> $data['city_id'],
				// ])->row_array())) {
				// 	self::_updateCounter($job_id, true);
				// 	$skipped++;
				// 	continue;
				// }

				log_kb([
					'_importSchool::single_row' => $data
				]);

				// if (!empty($data['email']) && !empty($this->school_model->get_all([
				// 	'owner_email' 			=> trim($data['email']),
				// ])['rows'] ?? [])) {
				// 	log_kb([
				// 		'_importSchool::owner_email' => $data['email']
				// 	]);
				// 	self::_updateCounter($job_id, true);
				// 	$skipped++;
				// 	continue;
				// } elseif (!empty($data['email']) && !empty($this->school_model->get_all([
				// 	'alternate_owner_email' => trim($data['email']),
				// ])['rows'] ?? [])) {
				// 	log_kb([
				// 		'_importSchool::alternate_owner_email' => $data['email']
				// 	]);
				// 	self::_updateCounter($job_id, true);
				// 	$skipped++;
				// 	continue;
				// } elseif (!empty($data['mobile']) && !empty($this->school_model->get_all([
				// 	'owner_mobile' => trim($data['mobile']),
				// ])['rows'] ?? [])) {
				// 	log_kb([
				// 		'_importSchool::owner_mobile' => $data['mobile']
				// 	]);
				// 	self::_updateCounter($job_id, true);
				// 	$skipped++;
				// 	continue;
				// } elseif (!empty($data['mobile']) && !empty($this->school_model->get_all([
				// 	'alternate_owner_mobile' => trim($data['mobile']),
				// ])['rows'] ?? [])) {
				// 	log_kb([
				// 		'_importSchool::alternate_owner_mobile' => $data['mobile']
				// 	]);
				// 	self::_updateCounter($job_id, true);
				// 	$skipped++;
				// 	continue;
				// } elseif (!empty($data['alternate_email']) && !empty($this->school_model->get_all([
				// 	'owner_email' 			=> trim($data['alternate_email']),
				// ])['rows'] ?? [])) {
				// 	log_kb([
				// 		'_importSchool::alternate_email-owner' => $data['alternate_email']
				// 	]);
				// 	self::_updateCounter($job_id, true);
				// 	$skipped++;
				// 	continue;
				// } elseif (!empty($data['alternate_email']) && !empty($this->school_model->get_all([
				// 	'alternate_owner_email' => trim($data['alternate_email']),
				// ])['rows'] ?? [])) {
				// 	log_kb([
				// 		'_importSchool::alternate_email' => $data['alternate_email']
				// 	]);
				// 	self::_updateCounter($job_id, true);
				// 	$skipped++;
				// 	continue;
				// } elseif (!empty($data['alternate_mobile']) && !empty($this->school_model->get_all([
				// 	'owner_mobile' => trim($data['alternate_mobile']),
				// ])['rows'] ?? [])) {
				// 	log_kb([
				// 		'_importSchool::alternate_mobile-owner' => $data['alternate_mobile']
				// 	]);
				// 	self::_updateCounter($job_id, true);
				// 	$skipped++;
				// 	continue;
				// } elseif (!empty($data['alternate_mobile']) && !empty($this->school_model->get_all([
				// 	'alternate_owner_mobile' => trim($data['alternate_mobile']),
				// ])['rows'] ?? [])) {
				// 	log_kb([
				// 		'_importSchool::alternate_mobile' => $data['alternate_mobile']
				// 	]);
				// 	self::_updateCounter($job_id, true);
				// 	$skipped++;
				// 	continue;
				// }

				if (!empty($row['email']) && !empty($site_info = $this->site_model->get_all([
                    'owner_email' 			=> trim($data['email']),
                ])['rows'] ?? [])) {
					$data['site_id'] = $site_info['id'];
                    self::saveGarbageSchoolImport($job_id, $data);
					self::_updateCounter($job_id, true);
					$skipped++;
                    continue;
                } elseif (!empty($row['mobile']) && !empty($site_info = $this->site_model->get_all([
                    'owner_mobile' => trim($data['mobile']),
                ])['rows'] ?? [])) {
					$data['site_id'] = $site_info['id'];
                    self::saveGarbageSchoolImport($job_id, $data);
					self::_updateCounter($job_id, true);
					$skipped++;
                    continue;
                } elseif (!empty($row['alternate_email']) && !empty($site_info = $this->site_model->get_all([
                    'owner_email' => trim($data['alternate_email']),
                ])['rows'] ?? [])) {
					$data['site_id'] = $site_info['id'];
                    self::saveGarbageSchoolImport($job_id, $data);
					self::_updateCounter($job_id, true);
					$skipped++;
                    continue;
                } elseif (!empty($row['alternate_mobile']) && !empty($site_info = $this->site_model->get_all([
                    'owner_mobile' => trim($data['alternate_mobile']),
                ])['rows'] ?? [])) {
					$data['site_id'] = $site_info['id'];
                    self::saveGarbageSchoolImport($job_id, $data);
					self::_updateCounter($job_id, true);
					$skipped++;
                    continue;
                }

				self::_saveSchoolData($data);
			}

			$uploaded++;
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function _saveSchoolData($data = [], $update = false) {
		log_kb([
			'_importSchool::_saveSchoolData' => $data,
			'_importSchool::_saveSchoolData::flag' => $update
		]);
		if (!empty($data) && !empty($data['school_name'])) {
			$site_info 		= [];
			$country_info 	= $this->country_model->get($data['country_id']);

			if (
				!empty($country_info['name']) &&
				empty($country_site_info = $this->site_model->getSiteByName($country_info['name']))
			) {
				$country_site_info = $this->site_model->getSiteByName('India');
			}

			if (!empty($country_site_info)) {
				$site_info = $this->site_model->get($country_site_info['id']);
			}

			log_kb([
				'_importSchool::site_info' => $site_info,
			]);

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
				'status' 			  			=> 1,
				'license_total' 	  			=> 1000,
				'license_used' 		  			=> 0,
				'tag' 		  					=> $data['tag'] ?? '',
			];

			log_kb([
				'_importSchool::insert_school_data' => $insert_school_data,
			]);

			if ($update) {
				unset($insert_school_data['site_code']);
			}

			if ($update && !empty($data['id'])) {
				$this->school_model->edit($data['id'], $insert_school_data);
				$school_id = $data['id'];
				log_kb([
					'_importSchool::update_school_data_id' => $data['id'],
					'_importSchool::update_school_data' => $insert_school_data,
				]);
			} else {
				$school_id = $this->school_model->add($insert_school_data);
				log_kb([
					'_importSchool::insert_school_id' => $school_id,
				]);

				if (!empty($school_id)) {
					$this->school_model->edit($school_id, [
						'site_code' => get_site_code_slug(trim($data['school_name'])) . '-' . $school_id
					]);
				}
			}
		}
	}

	private function _importSchoolAwardAddress($rows = [], $map = [], $job_id = 0) {
		$this->load->model('school/SchoolAwardAddress_model', 'school_award_address_model');

		$skipped = $uploaded = 0;

		foreach ($rows as $index => $row) {
			$data = array_combine(array_keys($map), array_map(function($i) use($row) {
				return @$row[$i];
			}, array_values($map)));

			self::_updateCounter($job_id);

			if (empty($data['event_id']) || empty($data['school_id'])) {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}

			if ($school_info = $this->school_model->get($data['school_id'])) {
				if (empty($info = $this->school_award_address_model->get_all([
					'event_id' 	=> $data['event_id'],
					'school_id' => $data['school_id'],
				])['rows'] ?? [])) {
					$this->school_award_address_model->add([
						'event_id'	=> $data['event_id'],
						'school_id'	=> $data['school_id'] ?? 0,
						'site_id'	=> $data['site_id'] ?? 0,
						'status' 	=> 0,

					]);
				}
			} else {
				self::_updateCounter($job_id, true);
				$skipped++;
				continue;
			}
		}

		self::_updateCompleted($job_id);

		return [
			'skipped' 	=> $skipped,
			'uploaded' 	=> $uploaded,
		];
	}

	private function saveGarbageSchoolImport($job_id = 0, $data = []) {

        $this->db->insert('garbage_school', [
            'job_id'		                => $job_id,
            'site_id'                       => $data['site_id'] ?? 0,
			'parent_id'                     => $data['parent_id'] ?? 0,
			'country_id'                    => $data['country_id'] ?? 0,
			'state_id'                      => $data['state_id'] ?? 0,
			'city_id'                       => $data['city_id'] ?? 0,

			'school_name'                   => $data['school_name'] ?? '',
			'email'                         => $data['email'] ?? '',
			'mobile'                        => $data['mobile'] ?? '',

			'authorized_person'             => !empty($data['authorized_person']) ? trim($data['authorized_person']) : '',

			'alternate_email'               => $data['alternate_email'] ?? '',
			'alternate_mobile'              => $data['alternate_mobile'] ?? '',
			'alternate_authorized_person'   => $data['alternate_authorized_person'] ?? '',

			'owner_name'                    => $data['owner_name'] ?? '',
			'address'                       => $data['address'] ?? '',
			'landmark'                      => $data['landmark'] ?? '',
			'zipcode'                       => $data['zipcode'] ?? '',

			'site_type'                     => $data['site_type'] ?? 0,
			'tag'                           => $data['tag'] ?? '',

			'date_added'                    => date('Y-m-d H:i:s'),
			'date_modified'                 => date('Y-m-d H:i:s')
        ]);
	}
}
