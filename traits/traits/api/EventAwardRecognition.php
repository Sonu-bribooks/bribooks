<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait EventAwardRecognition {
	public function getSchoolAwardRecognition() {
		if (!$this->json) {
			if (empty($this->input->post('event_id')) && empty($this->input->post('site_id'))) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			$site_info = $this->site_model->get($this->input->post('site_id'));

			if (empty($site_info)) {
				$this->json['error'] = _li('Invalid school');
				return;
			}

			$type_map = [
				'jpg'  => 'image',
				'jpeg' => 'image',
				'png'  => 'image',
				'gif'  => 'image',
				'webp' => 'image',

				'mp4'  => 'video',
				'mov'  => 'video',
				'avi'  => 'video',
				'mkv'  => 'video',
			];

			$this->load->model('event/EventExhibition_model', 'event_exhibition_model');

			$recognition_info = $this->event_exhibition_model->get_all([
				'event_id'  => (int)$this->input->post('event_id'),
				'site_id'   => (int)$this->input->post('site_id'),
			])['rows'][0] ?? [];

			if (empty($recognition_info)) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			$this->load->library('S3_lib', 's3_lib');
			$this->s3_lib->setBucket('bbprivateimagesin');

			$school_awards 		= [];
			$school_interviews 	= [];
			$school_walls 		= [];

			if (!empty($recognition_info['award'])) {
				$awards = explode(',', $recognition_info['award']);
	
				foreach ($awards as $key => $item) {
					$ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
					$school_awards[] = [
						'type' 	=> $type_map[$ext] ?? 'other',
						'url' 	=> $this->s3_lib->getUrl($item, '', false, 120)
					];
				}
			}

			if (!empty($recognition_info['interview'])) {
				$interviews = explode(',', $recognition_info['interview']);
	
				foreach ($interviews as $key => $item) {
					$ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
					$school_interviews[] = [
						'type' 	=> $type_map[$ext] ?? 'other',
						'url' 	=> $this->s3_lib->getUrl($item, '', false, 120)
					];
				}
			}

			if (!empty($recognition_info['wall'])) {
				$walls = explode(',', $recognition_info['wall']);
	
				foreach ($walls as $key => $item) {
					$ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
					$school_walls[] = [
						'type' 	=> $type_map[$ext] ?? 'other',
						'url' 	=> $this->s3_lib->getUrl($item, '', false, 120)
					];
				}
			}
			

			$this->json['recognition'] = [
				'event_id' 		=> $this->input->post('event_id'),
				'site_id' 		=> $site_info['id'] ?? 0,
				'school_name' 	=> $site_info['name'] ?? '',
				'awards' 		=> $school_awards,
				'interviews' 	=> $school_interviews,
				'walls' 		=> $school_walls,
			];

			self::_getSchoolEventBooks($this->input->post());

			$this->json['success'] = _l('event_award_recognition_fetched');
		}
	}

	public function getUserAwardRecognition() {
		if (!$this->json) {
			if (empty($this->input->post('event_id')) && empty($this->input->post('book_id'))) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			$book_info = $this->book_model->get($this->input->post('book_id'));

			if (empty($book_info)) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			$type_map = [
				'jpg'  => 'image',
				'jpeg' => 'image',
				'png'  => 'image',
				'gif'  => 'image',
				'webp' => 'image',

				'mp4'  => 'video',
				'mov'  => 'video',
				'avi'  => 'video',
				'mkv'  => 'video',
			];

			$this->load->model('event/EventExhibition_model', 'event_exhibition_model');

			$recognition_info = $this->event_exhibition_model->get_all([
				'event_id'  => (int)$this->input->post('event_id'),
				'book_id'   => (int)$this->input->post('book_id'),
			])['rows'][0] ?? [];

			if (empty($recognition_info)) {
				$this->json['error'] = _li('Invalid url');
				return;
			}

			$this->load->library('S3_lib', 's3_lib');
			$this->s3_lib->setBucket('bbprivateimagesin');

			$user_awards 		= [];
			$user_interviews 	= [];
			$user_walls 		= [];

			if (!empty($recognition_info['award'])) {
				$awards = explode(',', $recognition_info['award']);
	
				foreach ($awards as $key => $item) {
					$ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
					$user_awards[] = [
						'type' 	=> $type_map[$ext] ?? 'other',
						'url' 	=> $this->s3_lib->getUrl($item, '', false, 120)
					];
				}
			}

			if (!empty($recognition_info['interview'])) {
				$interviews = explode(',', $recognition_info['interview']);
	
				foreach ($interviews as $key => $item) {
					$ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
					$user_interviews[] = [
						'type' 	=> $type_map[$ext] ?? 'other',
						'url' 	=> $this->s3_lib->getUrl($item, '', false, 120)
					];
				}
			}

			if (!empty($recognition_info['wall'])) {
				$walls = explode(',', $recognition_info['wall']);
	
				foreach ($walls as $key => $item) {
					$ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
					$user_walls[] = [
						'type' 	=> $type_map[$ext] ?? 'other',
						'url' 	=> $this->s3_lib->getUrl($item, '', false, 120)
					];
				}
			}

			$this->json['recognition'] = [
				'event_id' 		=> $this->input->post('event_id'),
				'book_id' 		=> $book_info['id'] ?? 0,
				'book_name' 	=> $book_info['name'] ?? '',
				'author_name' 	=> $book_info['author_name'] ?? '',
				'cover_image' 	=> $book_info['cover_image'] ?? '',
				'author_image' 	=> $book_info['author_image'] ?? '',
				'awards' 		=> $user_awards,
				'interviews' 	=> $user_interviews,
				'walls' 		=> $user_walls,
			];

			$this->json['success'] = _l('event_award_recognition_fetched');
		}
	}
}
