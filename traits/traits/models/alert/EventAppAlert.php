<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventAppAlert {
	public function verifiedEntryAlert($data = []) {
		if (empty($data['mobile'])) return;
		if (empty($data['event_id'])) return;
		if (empty($data['type'])) return;

		$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');

		$info = json_decode($this->event_communication_kit_model->get_all([
			'event_id'	=> $data['event_id'],
			'start'		=> 0,
			'limit'		=> 1,
		])['rows'][0]['event_exhibition'] ?? '', true);

		log_kb(['info' => [$info, $data]]);

		$info = array_values(array_filter($info, fn($item) => $item['type'] == $data['type']));
		$info = $info[0]['whatsapp'] ?? [];

		if (!empty($info['template_id'])) {
			$whatsapp_data = [
				'template_id' => $info['template_id'] ?? '',
			];

			if (!empty($info['message'])) {
				$whatsapp_data['parameters'] = format_whatsapp_sms_message($info['message'], $data['data']);
			}

			if (!empty($info['attachment_file'])) {
				$whatsapp_data['media'] = [
					'type' 		=> 'DOC',
					'url'		=> sprintf('%s%s%s', $this->config->item('cloudfront_url'), $this->config->item('s3_user_gallery'), $info['attachment_file']),
					'fileName'	=> basename($info['attachment_file']),
				];
			}

			self::_sendOnextelWhatsapp($data['mobile'], $whatsapp_data);
		}
	}
}
