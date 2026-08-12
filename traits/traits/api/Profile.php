<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Profile {

	public function updateUserProfile() {
		if (!$this->json) {
            $user_id = $this->session->userdata('user_id');
		
            if (!$user_id ||
                empty($user_info = $this->student_model->get($user_id))
            ) {
                $this->json['login'] 	= true;
                $this->json['success'] 	= _l('login_to_publish');
                return;
            }

            $data = $this->input->post();

            if ($data['status'] == 2) {
                CI_Events::trigger('access_log', [
					'module'	=> sprintf('force_enrol_skip_%s_%s', $user_info['id'], $user_info['location'])
				]);

			    $this->json['success'] = _l('profile_successfully_saved');
                return;

            }

            // if (!self::_verifyCaptcha()) {
			// 	$this->json['error'] = _li('Invalid Captcha. Please try again.');
			// 	return;
			// }

            $profile_data = [];

            if (!empty($data['name'])) {
                $explode 	= explode(' ', ($data['name'] ?? ''), 2);
                $first_name = array_shift($explode);
                $last_name 	= array_shift($explode);

                $profile_data['first_name'] = $first_name;
                $profile_data['last_name']  = $last_name;
			}

            if (!empty($data['site_id'])) {
                if (!empty($site_info  = $this->site_model->get($data['site_id'])) && ($site_info['site_type'] != 7)) {
                    $profile_data['site_id'] = $data['site_id'];
                }
            }

            if (!empty($data['site_id'])) {
                $profile_data['site_id'] = $data['site_id'];
            }

            if (!empty($data['state_id'])) {
                $profile_data['state_id'] = $data['state_id'];
            }

            if (!empty($data['city_id'])) {
                $profile_data['city_id'] = $data['city_id'];
            }

            if (!empty($data['grade_id'])) {
                $profile_data['grade']      = $data['grade_id'];
                $profile_data['grade_id']   = $data['grade_id'];
            }

            if (!empty($data['section_id'])) {
                $profile_data['section']    = $data['section_id'];
                $profile_data['section_id'] = $data['section_id'];
            }

            if (!empty($profile_data)) {
                $this->student_model->edit($user_info['id'], $profile_data);
                self::_forceUserEnrol($user_info['id']);
            }

            CI_Events::trigger('access_log', [
			    'module'	=> sprintf('force_enrol_%s_%s', $user_info['id'], $user_info['location'])
			]);

			$this->json['success'] = _l('profile_successfully_saved');

        }
	}

    private function _forceUserEnrol($user_id = 0) {
		if ($user_id &&
			!empty($user_info = $this->student_model->get($user_id)) &&
			!empty($site_info = $this->site_model->get($user_info['site_id'] ?? 0))
		) {
            $event_info = $this->event_model->get_all([
                'country_code'    => strtoupper($site_info['country_code']),
                'is_active_event' => 1,
                'force_enrol_in'  => [2,3],
                'start'           => 0,
                'limit'           => 1,
            ])['rows'][0] ?? null;
    
            if (!empty($event_info) && empty($this->event_user_model->getEventUserByUserId($event_info['id'], $user_info['id']))) {
                $this->event_user_model->add([
                    'event_id'	=> (int)$event_info['id'],
                    'user_id'	=> (int)$user_info['id']
                ]);
            }
		}
	}
}