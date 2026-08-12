<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;
trait InviteAlert {
	public function buildInviteUserPassCron($template_id = 0) {
        $this->load->model('event/EventInviteTemplate_model', 'event_invite_template_model');

		if (
			empty($template_id) ||
            empty($template_info = $this->event_invite_template_model->get($template_id))
		) return;

		$this->load->model('event/EventUserInvite_model', 'event_user_invite_model');
		$this->load->model('event/EventSchoolInvite_model', 'event_school_invite_model');

        $model_name = sprintf('event_%s_invite_model', $template_info['type']);

		if (!empty($invite_guests = $this->{$model_name}->get_all([
            'template_id'       => $template_info['id'],
            'status'            => 1,
            'is_pdf'            => 0
        ])['rows'] ?? [])) {
            $s3_dirname = $this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf');

            $head_logo = sprintf(
                '%spublic/EventGallery/%s',
                $this->config->item('cloudfront_url'),
                $template_info['logo']
            );

            foreach($invite_guests as $invite_guest_info) {

                $user_info	= $this->user_model->get($invite_guest_info['user_id'] ?? 0);
                $book_info  = $this->book_model->get($invite_guest_info['book_id'] ?? 0);
                $site_info  = $this->site_model->get($user_info['site_id'] ?? 0);
                $city_info  = $this->city_model->get($user_info['city_id'] ?? 0);
                $state_info	= $this->state_model->get($user_info['state_id'] ?? 0);

                $user_details_info = $this->db->get_where('user_details', ['user_id' => $user_info['id']])->row_array();

                $book_rank = sprintf('%s # %s', 
                    (!empty($invite_guest_info['is_jury']) ? 'JURY RANK' : 'RANK'), 
                    $invite_guest_info['book_rank']
                );

               $grade = $user_info['grade'] ?? '';

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

                $this->load->library('S3_lib', 's3_lib');
			    $this->s3_lib->setBucket('bbprivateimagesin');

			    $dir_name = (ENVIRONMENT === 'production' ? 'event_pass_pdf' : 'event_pass_pdf/test');

                $html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_author_single_pdf', $data, true);
                // $html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_author_pdf_template', $data, true);
                $dompdf = new Dompdf();
                $dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
                $dompdf->set_option('isJavascriptEnabled', true);
                $dompdf->set_option('isRemoteEnabled', true);
                $dompdf->set_option('isHtml5ParserEnabled', true);
                $dompdf->setPaper(array(0, 0, 360, 513.2), 'potrait');

                $dompdf->render();
                $file = sprintf('uploads/eventpass/pdfs/user_entry_pass_%s.pdf', $invite_guest_info['id']);
                $output = $dompdf->output();
                file_put_contents(FCPATH.$file, $output);

                $pdf_file = $this->s3_lib->putData(
                    sprintf('user_entry_pass_%s_%s.pdf', $invite_guest_info['id'], time()),
                    $dir_name,
                    $output,
                    false
                );

                $this->event_user_invite_model->edit($invite_guest_info['id'], [
                    'pdf' => $pdf_file
                ]);
            }
		}
	}

    public function buildInviteSchoolPassCron($template_id = 0) {
        $this->load->model('event/EventInviteTemplate_model', 'event_invite_template_model');

		if (
			empty($template_id) ||
            empty($template_info = $this->event_invite_template_model->get($template_id))
		) return;

		$this->load->model('event/EventSchoolInvite_model', 'event_school_invite_model');

		if (!empty($invite_guests = $this->event_school_invite_model->get_all([
            'template_id'       => $template_info['id'],
            'status'            => 1,
            'is_pdf'            => 0
        ])['rows'] ?? [])) {
            $s3_dirname = $this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf');

            $head_logo = sprintf(
                '%spublic/EventGallery/%s',
                $this->config->item('cloudfront_url'),
                $template_info['logo']
            );

            foreach($invite_guests as $invite_guest_info) {
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

                $this->load->library('S3_lib', 's3_lib');
			    $this->s3_lib->setBucket('bbprivateimagesin');

			    $dir_name = (ENVIRONMENT === 'production' ? 'event_pass_pdf' : 'event_pass_pdf/test');

                $html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_single_school_pdf_template', $data, true);
                // $html = $this->load->view('frontend/' . get_frontend_settings('theme') . '/nyaf_school_pdf_template', $data, true);
                $dompdf = new Dompdf();
                $dompdf->loadHtml(preg_replace('/>\s+</', "><", $html));
                $dompdf->set_option('isJavascriptEnabled', true);
                $dompdf->set_option('isRemoteEnabled', true);
                $dompdf->set_option('isHtml5ParserEnabled', true);
                $dompdf->setPaper(array(0, 0, 360, 513.2), 'potrait');

                $dompdf->render();
                $file = sprintf('uploads/eventpass/pdfs/school_entry_pass_%s.pdf', $invite_guest_info['id']);
                $output = $dompdf->output();
                file_put_contents(FCPATH.$file, $output);

                $pdf_file = $this->s3_lib->putData(
                    sprintf('school_entry_pass_%s_%s.pdf', $invite_guest_info['id'], time()),
                    $dir_name,
                    $output,
                    false
                );

                $this->event_school_invite_model->edit($invite_guest_info['id'], [
                    'pdf' => $pdf_file
                ]);
            }
		}
	}
}