<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait AcknowledgeAlert {
	public function eventParentAcknowledgeSignup($data = []) {
		if(empty($user_info = $this->user_model->get($data['student_id']))) return;

		$event_info	= $this->event_model->get($data['event_id']);

		$this->load->model('event/EventCommunicationKit_model', 'event_communication_kit_model');
		$this->load->model('event/EventBrochure_model', 'event_brochure_model');

		$communication_kit_info = $this->event_communication_kit_model->get_all([
			'event_id' => $data['event_id']
		])['rows'][0]['parent_acknowledge'] ?? '';

		if (empty($communication_kit_info)) return;

		$communication_kit_info = json_decode($communication_kit_info, true);

		$age = calculate_age($user_info['dob']);

		$format_message = array_values(array_filter($communication_kit_info, function($item) use ($age) {
			return (!empty($item['email']['age']) && ($age <= $item['email']['age']));
		}));

		if (!empty($format_message[0])) {
			$kit_info = $format_message[0];
		} else {
			$kit_info = $communication_kit_info[0] ?? [];
		}

		if (empty($kit_info)) return;
		if (empty($kit_info['email']['subject'] ?? '')) return;
		if (empty($kit_info['email']['message'] ?? '')) return;

		$approval_link 			= vsprintf('%sevents/parent-approval/%s?lid=%d', [
			$event_info['url'],
			$event_info['slug'],
			$data['lead_id']
		]);

		$variable = [
			'site_id'	  			=> $user_info['site_id'],
			'user_id'	  			=> $user_info['id'] ?? 0,
			'event_id'	  			=> $data['event_id'],
			'author_name'	  		=> ucwords($user_info['first_name'] . ' ' . $user_info['last_name']),
			'approval_link'	  		=> $approval_link,
		];

		$subject 		= format_message_with_data($kit_info['email']['subject'], $variable) ?? '';
		$message 		= format_message_with_data($kit_info['email']['message'], $variable) ?? '';

		if (!empty($user_info['parent_email'])) {
			self::email(
				$user_info['parent_email'],
				$subject,
				$message,
				[],
				ENVIRONMENT === 'production'
					? ['communication@bribooks.com']
					: [],
				[]
			);

			$this->cron_model->add([
				'code'			=> sprintf('deactivateEventUser_%s_%s', $data['event_id'], $data['student_id'] ?? 0),
				'action'		=> 'alert_model->deactivateEventUser',
				'data'			=> [['event_id' => $data['event_id'], 'student_id' => $data['student_id']]],
				'site_id'		=>  1,
				'alert_date'	=> date('Y-m-d H:i:00', strtotime('+7 days')),
			]);
		}
	}

	public function deactivateEventUser($data = []) {
		$this->load->model('user/DeactivateUser_model', 'deactivate_user_model');

		if (empty($user_info = $this->user_model->get($data['student_id']))) return;

		$this->user_model->delete($user_info['id']);

		$this->db->update('event_user', [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		],[
			'event_id' 	=> $data['event_id'],
			'user_id' 	=> $user_info['id'],
		]);

		$this->deactivate_user_model->add([
			'event_id' 	=> $data['event_id'],
			'user_id' 	=> $user_info['id'],
		]);
	}
}
