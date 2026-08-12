<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait MasterClass {
	public function getLessons() {
		if (!$this->json) {
			$user_info = $this->user_model->get($this->session->userdata('user_id') ?? 0);

			if (!$user_info) {
				return $this->json['erorr'] = _l('unauthorized');
			}

			$user_events = $this->event_user_model->get_all([
				'user_id' => $user_info['id'],
			])['rows'] ?? [];

			$event_ids = [];

			if (!empty($user_events)) {
				$event_ids = array_column($user_events, 'event_id');
			}

			// $this->load->library('CloudFront_lib', 'cloudfront_lib');
			$this->load->library('S3_lib', 's3_lib');
			$this->s3_lib->setBucket('bbvideolessons');

			$this->json['lessons'] = array_map(function($item) {
				return [
					'id'			=> $item['id'],
					'name'			=> $item['name'],
					'description'	=> $item['description'],
					'video_url'		=> $this->s3_lib->getUrl($item['video_url'], '', false),
					'video_thumb'	=> vsprintf('%s%s%s', [
						$this->config->item('cloudfront_url'),
						$this->config->item('s3_user_gallery'),
						$item['video_thumb'],
					]),
				];
			}, $this->master_class_model->get_all([
				'event_ids' => !empty($event_ids) ? implode(',', $event_ids) : 0,
				'grade' 	=> !empty($user_info['grade']) ? $user_info['grade'] : 1,
				'sort'		=> 'master_class.sort_order',
				'order'		=> 'ASC',
			])['rows'] ?? []);

			$this->json['success'] = _l('master_class_fetched');
		}
	}
}
