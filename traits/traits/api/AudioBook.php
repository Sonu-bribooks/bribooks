<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait AudioBook {
	public function generateAudioBooks() {
		try {
			$this->form_validation->set_rules('slug_name', _l('slug_name'), 'required');
			$this->form_validation->set_rules('text', _l('text'), 'required');
			$this->form_validation->set_rules('book_id', _l('book_id'), 'required|numeric');

			self::_runFormValidation();

			if (!$this->json) {
				if (!$this->session->userdata('user_id')) {
					return $this->json['error']	= _l('unauthorized_user');
				}

				if (!empty($book_info = $this->book_model->get($this->input->post('book_id')))) {
					$audio_book_info =  $this->audio_book_model->get_all([
						'slug_name'	 	=> $this->input->post('slug_name'),
						'book_id'		=> (int)$book_info['id']
					])['rows'][0] ?? [];

					$this->load->library('S3_lib', 's3_lib');
					$this->s3_lib->setBucket('bbaudiobooks');

					$s3_dirname = 'audioBookMP3/';

					if (!empty($audio_book_info)) {
						$audio_book_info['file_url'] =  $this->s3_lib->getUrl(
							$audio_book_info['file'],
							(ENVIRONMENT === 'production') ? $s3_dirname : $s3_dirname . 'test',
							false
						);

						$this->json['success']		= _l('success');
						$this->json['data']		  	=  $audio_book_info;
					} else {
						$file_name  = $this->input->post('slug_name') . '_' . time() . '.mp3';

						$audio_file = self::_generateMp3File([
							'text'			=> $this->input->post('text'),
							'file_name'	  	=> $file_name
						]);

						$audio_book_id = $this->audio_book_model->add([
							'book_id'		=> (int)$book_info['id'],
							'file'			=> $file_name,
							'slug_name'	  	=> $this->input->post('slug_name'),
						]);

						if ($audio_book_id) {
							$data = [
								'id'				=> $audio_book_id,
								'slug_name'		  	=> $this->input->post('slug_name'),
								'book_id'			=> $book_info['id'],
								'file'				=> $file_name,
								'file_url'			=> $audio_file
							];

							$this->json['success']	 = _l('success');
							$this->json['data']		= $data;
						} else {
							$this->json['error']	= _l('not_saved');
						}
					}
				} else {
					$this->json['error']	= _l('book_id_does_not_match');
				}
			}
		} catch (Exception $e) {
			$this->json['error']	= $e->getMessage();
		}
	}

	private function _generateMp3File($data = []) {
		$ch = curl_init();

		curl_setopt_array($ch, array(
			CURLOPT_URL 			=> 'https://ttsmp3.com/makemp3_new.php',
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_ENCODING 		=> '',
			CURLOPT_MAXREDIRS 		=> 10,
			CURLOPT_TIMEOUT 		=> 0,
			CURLOPT_FOLLOWLOCATION 	=> true,
			CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST 	=> 'POST',
			CURLOPT_POSTFIELDS 		=> 'msg=' . urlencode($data['text']) . '&lang=Ivy&source=ttsmp3',
			CURLOPT_HTTPHEADER 		=> [
				'Content-Type: application/x-www-form-urlencoded'
			],
		));

		$response = curl_exec($ch);
		curl_close($ch);

		$response = json_decode($response, true);

		if (empty($response['URL'])) return false;

		$mp3_url 		= $response['URL'];
		$mp3_content 	= file_get_contents($mp3_url);

		if (!$mp3_content) return false;

		$audio_file = FCPATH . 'uploads/audio_book/' . $data['file_name'];
		$dir 		= FCPATH . 'uploads/audio_book';

		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
			chmod($dir, 0777);
		}

		file_put_contents($audio_file, $mp3_content);

		return self::_uploadBookMp3FileS3($data['file_name']);
	}

	private function _uploadBookMp3FileS3($file = '') {
		if (empty($file)) return;

		$path_info = pathinfo($file);

		$this->load->library('S3_lib', 's3_lib');
		$this->s3_lib->setBucket('bbaudiobooks');

		$s3_dirname	 = 'audioBookMP3/';
		$s3_dirname	 = (ENVIRONMENT === 'production') ? $s3_dirname : $s3_dirname . 'test';

		$this->s3_lib->put(FCPATH . 'uploads/audio_book/' . $path_info['filename'] . '.mp3', $s3_dirname, false);

		unlink(FCPATH . 'uploads/audio_book/' . $path_info['filename'] . '.mp3');

		return $this->s3_lib->getUrl($file, $s3_dirname, false);
	}

	public function audioBookList() {
		try {
			if (!$this->session->userdata('user_id')) {
				$this->json['error'] = _l('unauthorized_user');
			} else {
				$this->load->library('S3_lib', 's3_lib');
				$this->s3_lib->setBucket('bbaudiobooks');

				$s3_dirname = 'audioBookMP3/';
				$result 	= $this->audio_book_model->get_all([
					'book_id'	=> (int)$this->input->get('book_id')
				]);

				if (!empty($result['rows'] ?? [])) {
					$books = [];

					foreach ($result['rows'] as $value) {
						$value['file_url'] 	=  $this->s3_lib->getUrl($value['file'], (ENVIRONMENT === 'production') ? $s3_dirname : $s3_dirname . 'test', false);
						$books[]			=  $value;
					}

					$this->json['success']	= _l('success');
					$this->json['total']	=  $result['total'];
					$this->json['data']		=  $books ;
				} else {
					$this->json['error']	= _l('book_id_does_not_match');
				}
			}
		} catch (Exception $e) {
			$this->json['error']	= $e->getMessage();
		}
	}
}
