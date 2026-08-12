<?php defined('BASEPATH') or exit('No direct script access allowed');

trait TeacherSignupAlert {
	public function signupTeacher($id, $type = 'signup', $event_id = 0) {
		self::cron($id, 'signupTeacherCron');
	}

	public function signupTeacherCron($teacher_id = 0, $event_id = 0) {
		$this->load->model('school/SchoolTeacherTemplate_model', 'school_teacher_template_model');
		$this->load->model('user/teacher/Teacher_model', 'teacher_model');

        if (!empty($info = $this->teacher_model->get($teacher_id))) {

            $template_filter = [
                'event_id'      => $event_id ?? '0',
                'template_type' => 'teacher_signup_email',
            ];

            $school_template_info   = $this->school_teacher_template_model->get_all($template_filter)['rows'][0] ?? '';
            $event_info             = $this->event_model->get($event_id);

            if (!empty($school_template_info)) {
                $this->load->model('localisation/State_model', 'state_model');
                $this->load->model('localisation/City_model', 'city_model');

                $site_info 		= $this->site_model->get($info['site_id']);
                $state_info  	= $this->state_model->get($info['state_id']);
                $city_info  	= $this->city_model->get($info['city_id']);

                $teacher_dashboard_url  = 'https://schools.bribooks.com/teacher/login';

				$password		 	= uniqid();
				$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
				$verification_code 	= sha1(md5($info['username'] . $password . $this->config->item('password_salt')));

				$this->teacher_model->edit($teacher_id, [
					'password'			=> $encoded_password,
					'verification_code'	=> $verification_code
				]);

                $variables = [
					'name'	  	        	=> ucwords($info['first_name'] . ' ' . $info['last_name']),
					'first_name'	  	    => ucwords($info['first_name']),
					'site_id'	  	        => $site_info['id'],
					'event_id'	  	        => $event_id,
					'authorized_person'	  	=> $site_info['authorized_person'],
					'school_name'	  	    => $site_info['name'],
					'owner_name'	  	    => $site_info['owner_name'],
					'email'	  	            => $info['email'],
					'mobile'	  	        => $info['mobile'],
                    'state' 				=> $state_info['name'],
                    'city' 					=> $city_info['name'],
                    'username' 				=> $info['username'],
                    'password' 				=> $password,
                    'teacher_dashboard_url' => $teacher_dashboard_url
				];

                $subject = self::formatCommonEmailSubject($school_template_info['subject'], $variables) ?? '';

                $content = self::formatCommonEmailContent($school_template_info['body'], $variables) ?? '';

                $data['title']		  	= $subject;
                $data['heading']		= '';
                $data['subheading']	 	= '';
                $data['subheading']		= '';
                $data['content']		= $content;
                $data['link']		   	= '';
                $data['link_text']	  	= '';
                $message				= $this->load->view('common/mail/templates/site/general', $data, true);

                $email  = $info['email'];
                $mobile = $info['mobile'];

                if (!empty($subject) && !empty($content)) {

                    $attachment = [];

                    self::email(
                        $email,
                        $subject,
                        $message,
                        [],
                        (ENVIRONMENT === 'production') ? ['communication@bribooks.com'] : [],
					    $attachment
                    );
                }

                if (!empty($school_template_info['whatsapp_template_id'])) {
                    !empty($mobile) && self::_sendWhatsappText(
                        $mobile,
                        [
                            'template'		=> $school_template_info['whatsapp_template_id'],
                            'parameters'	=> self::_formatMarketingWhatsappMessage($school_template_info['whatsapp_message'], $variables),
                        ]
                    );
                }
            }
        }
    }

	public function inviteTeacher($id) {
		self::cron($id, 'inviteTeacherCron');
	}

	public function inviteTeacherCron($id = 0, $event_id = 0) {
		$this->load->model('school/SchoolTeacherTemplate_model', 'school_teacher_template_model');
		$this->load->model('user/teacher/Teacher_model', 'teacher_model');

		if ($info = $this->teacher_model->get($id)) {
			$template_filter = [
				'event_id'	  	=> $event_id,
				'template_type' => 'email_teacher_invite',
			];

			$school_template_info   = $this->school_teacher_template_model->get_all($template_filter)['rows'][0] ?? '';
			$event_info			 	= $this->event_model->get($event_id);

			$site_id = $info['site_id'];

			$site_info = $this->site_model->get($site_id);

			if (!empty($school_template_info)) {
				$this->load->model('localisation/State_model', 'state_model');
				$this->load->model('localisation/City_model', 'city_model');

				$state_info = $this->state_model->get($info['state_id']);
				$city_info  = $this->city_model->get($info['city_id']);

				$password		 	= uniqid();
				$encoded_password 	= sha1(md5($password . $this->config->item('password_salt')));
				$verification_code 	= sha1(md5($info['username'] . $password . $this->config->item('password_salt')));

				$this->teacher_model->edit($id, [
					'password'			=> $encoded_password,
					'verification_code'	=> $verification_code
				]);

				if ($site_info['site_type'] == 5) {
					$url			= sprintf('%s/v2/teacher/signup/%s?uid=%s&code=%s&utm_source=nyaf2024_%s', $event_info['url'], $site_info['id'], $info['id'], $verification_code, $id);
				} else {
					$url			= sprintf('%s/teacher/signup/%s?uid=%s&code=%s&utm_source=nyaf2024_%s', $event_info['url'], $site_info['id'], $info['id'], $verification_code, $id);
				}

				$variables = [
					'name'					=> $info['first_name'] . ' ' . $info['last_name'],
					'site_id'	  			=> $site_info['id'] ?? 0,
					'event_id'	  			=> $event_id,
					'authorized_person'	  	=> $site_info['authorized_person'] ?? '',
					'school_name'			=> $site_info['name'] ?? '',
					'owner_name'	  		=> $site_info['owner_name'] ?? '',
					'email'	  				=> $info['email'] ?? '',
					'mobile'	  			=> $info['mobile'] ?? '',
					'state' 				=> $state_info['name'] ?? '',
					'city' 					=> $city_info['name'] ?? '',
					'url'					=> $url,
				];

				$subject = self::formatCommonEmailSubject($school_template_info['subject'], $variables) ?? '';

				$content = self::formatCommonEmailContent($school_template_info['body'], $variables) ?? '';

				$data['title']		  	= $subject;
				$data['heading']		= '';
				$data['subheading']	 	= '';
				$data['subheading']		= '';
				$data['content']		= $content;
				$data['site_id']		= $site_id;
				$data['site_code']		= $site_info['site_code'];
				$data['link']			= '';
				$data['link_text']		= '';
				$data['unsubscribe_url']= gen_unsubscribe_url($info['email']);

				$message				= $this->load->view('common/mail/templates/site/general', $data, true);

				$attachment = [];

				$email  = $info['email'];
				$mobile = $info['mobile'];

				log_kb(['Teacher::invite::' => [
					$info,
					$info['email'],
					$data['title'],
					$message,
					[],
					(ENVIRONMENT === 'production') ? [] : [],
					$attachment
				]]);

				if (!empty($subject) && !empty($content)) {
					self::email(
						$email,
						$subject,
						$message,
						[],
						ENVIRONMENT === 'production'
							? ['communication@bribooks.com']
							: [],
						$attachment
					);
				}

			}
		}
	}
}
