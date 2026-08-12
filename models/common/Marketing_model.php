<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Marketing_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($marketing_id = 0) {
		$this->db->select('marketing.*');

		$this->db->where('marketing.id', (int)$marketing_id);

		$row = $this->db->get('marketing')->row_array();

		if (!empty($row['filters']) && is_string($row['filters'])) {
			$row['filters'] = json_decode($row['filters'], true);
		} else {
			$row['filters'] = [];
		}

		return $row;
	}

	public function get_all($data = []) {
		$this->db->select('
			marketing.*,
		');

		if (isset($data['type'])) {
			$this->db->where('marketing.type', (int)$data['type']);
		}

		if (isset($data['status'])) {
			$this->db->where('marketing.status', (int)$data['status']);
		}

		if (isset($data['attachment_type'])) {
			$this->db->where('marketing.attachment_type', (int)$data['attachment_type']);
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('marketing.to', $data['search'], 'both');
			$this->db->or_like('marketing.template_id', $data['search'], 'both');
			$this->db->or_like('marketing.subject', $data['search'], 'both');
			$this->db->or_like('marketing.name', $data['search'], 'both');
			$this->db->or_like('marketing.message', $data['search'], 'both');
			$this->db->or_like('marketing.type', $data['search'], 'both');
			$this->db->or_like('marketing.user_type', $data['search'], 'both');
			$this->db->group_end();
		}

		$this->db->where('marketing._deleted', 0);

		$this->db->from('marketing');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$sort_data = [
			'id',
			'status',
			'date_added',
			'date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'marketing.date_added';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = "ASC";
		} else {
			$order = "DESC";
		}

		$this->db->order_by($sort, $order);

		return ['rows' => $this->db->get()->result_array(), 'total' => $total];
	}

	public function add($data = []) {
		$data['to'] = is_array($data['to']) ? implode(',', $data['to']) : $data['to'];

		$data['alert_date'] = date('Y-m-d H:i:s', strtotime($data['alert_date']));

		if (is_array($data['filters'])) {
			$data['filters'] = json_encode($data['filters']);
		}

		$this->db->insert('marketing', $data + [
			'user_id' 			=> (int)$this->session->userdata('user_id'),
			'date_added' 		=> date('Y-m-d H:i:s'),
			'date_modified' 	=> date('Y-m-d H:i:s'),
		]);

		$marketing_id = $this->db->insert_id();

		self::updateCsv($marketing_id);
		self::updateAttachment($marketing_id, ATTACHMENT_TYPES[$data['attachment_type']]);
		self::updateEmailAttachment($marketing_id, ATTACHMENT_TYPES[$data['email_attachment_type']]);

		self::_scheduleAlert($marketing_id, 'add');

		$this->session->set_flashdata('flash_message', _l('marketing_added_successfully'));

		return $marketing_id;
	}

	public function edit($marketing_id = 0, $data = []) {
		$data['to'] = is_array($data['to'] ?? '') ? implode(',', $data['to']) : ($data['to'] ?? '');

		if (!empty($data['alert_date'])) {
			$data['alert_date'] = date('Y-m-d H:i:s', strtotime($data['alert_date']));
		}

		if (is_array($data['filters'] ?? '')) {
			$data['filters'] = json_encode($data['filters']);
		}

		$this->db->where('id', $marketing_id);
		$this->db->update('marketing', $data + [
			'date_modified' 		=> date('Y-m-d H:i:s'),
		]);

		self::updateCsv($marketing_id);
		self::updateAttachment($marketing_id, (ATTACHMENT_TYPES[$data['attachment_type'] ?? ''] ?? ''));

		self::_scheduleAlert($marketing_id, 'edit');

		$this->session->set_flashdata('flash_message', _l('marketing_edited_successfully'));
	}

	public function delete($marketing_id = 0) {
		$this->db->where('id', (int)$marketing_id);
		$this->db->update('marketing',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('marketing_deleted_successfully'));
	}

	public function copy($id) {
		if ($row = self::get($id)) {
			unset($row['id']);
			$row['name'] .= ' copy';
			$row['status'] = 0;
			$row['date_added'] = date('Y-m-d H:i:s');

			self::add($row);
		}

		$this->session->set_flashdata('flash_message', _l('marketing_updated_successfully'));
	}

	public function enableDisable($id) {
		if ($row = self::get($id)) {
			$status = (1 ^ $row['status']);
			$this->db->where('id', $id);
			$this->db->update('marketing', [
				'status'			=> (int)$status,
				'date_modified' 	=> date('Y-m-d H:i:s'),
			]);
		}

		$this->session->set_flashdata('flash_message', _l('marketing_updated_successfully'));
	}

	private function updateCsv($marketing_id = 0) {
		if (!empty($_FILES['csv_file']['size'])) {
			$file = $this->tool_model->upload(
				'csv_file',
				'',
				'uploads/marketing/csv_file/',
				['text/plain', 'text/csv', 'csv']
			);

			if (!isset($file['error'])) {
				$this->db->update('marketing', [
					'csv_file'			=> 'marketing/csv_file/' . $file['file_name'],
				], [
					'id'				=> (int)$marketing_id
				]);
			} else {
				log_kb(['csv_upload error=> ', $file['error']]);
				$this->session->set_flashdata('error_message', $file['error']);
			}
		}
	}

	private function updateAttachment($marketing_id = 0, $type = 'image') {
		if (!empty($_FILES['attachment_file']['size'])) {
			$file = $this->tool_model->upload(
				'attachment_file',
				'',
				'uploads/marketing/' . mb_strtolower($type) . '/',
			);

			if (!isset($file['error'])) {
				$this->db->update('marketing', [
					'attachment_file'	=> 'marketing/' . mb_strtolower($type) . '/' . $file['file_name'],
				], [
					'id'				=> (int)$marketing_id
				]);
			} else {
				$this->session->set_flashdata('error_message', $file['error']);
			}
		}
	}

	private function updateEmailAttachment($marketing_id = 0, $type = 'image') {
		if (!empty($_FILES['email_attachment_file']['size'])) {
			$file = $this->tool_model->upload(
				'email_attachment_file',
				'',
				'uploads/marketing/email/' . mb_strtolower($type) . '/',
			);

			if (!isset($file['error'])) {
				$this->db->update('marketing', [
					'email_attachment_file'	=> 'marketing/email/' . mb_strtolower($type) . '/' . $file['file_name'],
				], [
					'id'				=> (int)$marketing_id
				]);
			} else {
				$this->session->set_flashdata('error_message', $file['error']);
			}
		}
	}

	private function _scheduleAlert($marketing_id = 0, $type = 'add') {
		if ($info = self::get($marketing_id)) {
			if ($type == 'add') {
				$this->cron_model->add([
					'code'			=> 'marketingAlertCron_' . $marketing_id,
					'action'		=> 'alert_model->marketingAlertCron',
					'data'			=> [$marketing_id],
					'site_id'		=> 1,
					'status'		=> 1 ^ $info['status'],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime($info['alert_date'])),
				]);
			} else {
				$this->cron_model->editByCode('marketingAlertCron_' . $marketing_id, [
					'status'		=> 1 ^ $info['status'],
					'alert_date'	=> date('Y-m-d H:i:s', strtotime($info['alert_date'])),
				]);
			}
		}
	}

	private function _sendWhatsapp($marketing_id = 0) {
		if ($data = self::get($marketing_id)) {
			preg_match_all('/{(\w+?)}/im', $data['message'], $matches);

			$variables = $matches[1];


			$type = ATTACHMENT_TYPES[$data['attachment_type']] ?? 'text';

			$type = ucwords($type === 'none' ? 'text' : $type);

			$document = [];

			if ($type !== 'Text') {
				$path_parts = pathinfo('uploads/gallery/' . $data['attachment_file']);
				$document = [
					'link'	=> base_url('uploads/gallery/' . $data['attachment_file']),
					'name'	=> $path_parts['filename'] ?? '',
				];
			}

			if (in_array('user', $variables)) {
				foreach ($variables as &$variable) {
					if ($variable === 'user') {
						$mobiles = explode(',', $data['to']);

						foreach ($mobiles as $mobile) {
							$variable = $this->db->get_where('lead', [
								'status !='				=> 4,
								'mobile_verified'		=> 1,
								'mobile'				=> $mobile,
							])->row_array()['parent_name'] ?? '';

							self::{'_sendWhatsapp' . $type}($mobile, $variables, $document);
						}

						break;
					}
				}
			} else {
				self::{'_sendWhatsapp' . $type}($data['to'], $variables, $document);
			}
		}
	}

	private function _sendSingleWhatsapp() {

	}

	private function _sendWhatsappText($to, $parameters = [], $document = []) {
		$destination = [[
			'waid'	=> explode(',', $to)
		]];

		$message = [
			'template' 		=> '1272404983119428',
			'parameters' 	=> [
				'variable1' => $parameters[0],
				'variable2' => $parameters[1],
			]
		];

		$this->tool_model->whatsapp($destination, $message);
	}

	private function _sendWhatsappDocument($to, $parameters = [], $document = []) {
		$destination = [[
			'waid'	=> explode(',', $to)
		]];

		$message = [
			'template' 		=> '3969893143040412',
			'parameters' 	=> [
				'variable1' => $parameters[0],
				'document'	=> [
					'link'		=> $document['link'] ?? '',
					'filename'	=> $document['name'] ?? '',
				]
			]
		];

		$this->tool_model->whatsapp($destination, $message);
	}

	private function _sendWhatsappImage($to, $parameters = [], $document = []) {
		$destination = [[
			'waid'	=> explode(',', $to)
		]];

		$message = [
			'template' 		=> '666120351378693',
			'parameters' 	=> [
				'variable1' => $parameters[0],
				'variable2' => $parameters[1],
				'image'		=> [
					'link'		=> $document['link'] ?? '',
					'filename'	=> $document['name'] ?? '',
				]
			]
		];

		$this->tool_model->whatsapp($destination, $message);
	}

	private function _sendWhatsappVideo($to, $parameters = [], $document = []) {
		$destination = [[
			'waid'	=> explode(',', $to)
		]];

		$message = [
			'template' 		=> '313483239706552',
			'parameters' 	=> [
				'variable1' => $parameters[0],
				'video'		=> [
					'link'		=> $document['link'] ?? '',
					'filename'	=> $document['name'] ?? '',
				]
			]
		];

		$this->tool_model->whatsapp($destination, $message);
	}
}
