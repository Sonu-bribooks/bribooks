<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Record {
	public function recordVideo() {
		// cancate video files in to a single file
		// ffmpeg -i input1.mp4 -i input2.webm \
		// -filter_complex "[0:v:0] [0:a:0] [1:v:0] [1:a:0] concat=n=2:v=1:a=1 [v] [a]" \
		// -map "[v]" -map "[a]" output.mp4

		if (!empty($_FILES['video'])) {
			// pr($_FILES);
			// $finfo = @finfo_open(FILEINFO_MIME);
			// $mime = @finfo_file($finfo, $_FILES['video']['name']);
			// pr($mime);
			$file = $this->tool_model->upload(
				'video',
				'',
				'uploads/recording/' . $this->session->userdata('quiz_uid') . '/' . $this->session->userdata('assessment_id') . '/',
				[
					'webm',
				]
			);

			if (!isset($file['error'])) {
				// $this->lrdb->update('category', [
				// 	'image'			=> 'lr/category/' . $file['file_name'],
				// ], [
				// 	'id'			=> (int)$category_id
				// ]);
			} else {
				$this->json['error'] = $file['error'];
			}
		}

		self::setOutput();
	}
}
