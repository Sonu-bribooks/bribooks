<?php defined('BASEPATH') or exit('No direct script access allowed');


class Login extends CI_Controller {
	public function __construct() {
		parent::__construct();

		$this->load->model('user/User_model', 'user_model');
		$this->load->model('Alert_model', 'alert_model');
		$this->load->model('common/Validate_model', 'validate_model');
		$this->load->model('user/Role_model', 'role_model');

		$this->load->library('form_validation');
	}

	public function index() {
		
		self::session_destroy();

		$page_data['page_name'] 	= 'backend_login';
		$page_data['page_title'] 	= _l('login');
		$page_data['action_gmail'] 	= base_url('login/validate_gmail');

		if (!empty($this->input->get('back'))) {
			if (!empty($back = self::_getRedirectUrl($this->input->get('back')))) {
				$page_data['action_gmail'] .= '?back=' . $back;
			}
		}
		
		$this->load->view('frontend/' . get_frontend_settings('theme') . '/index', $page_data);
	}

	public function switchAccount($role_id) {
		$current_role_id = $this->session->userdata('role_id');

		$this->session->set_userdata('role_id', $this->session->userdata('additional_role_id'));
		$this->session->set_userdata('role', get_user_role_by_id($this->session->userdata('additional_role_id')));
		$this->session->set_userdata('additional_role_id', $current_role_id);

		$role_info = $this->role_model->get($this->session->userdata('role_id'));

		$this->session->set_userdata('user_role_type', $role_info['type']);

		if ($role_info['type'] === 'admin') {
			$this->session->set_userdata('admin_login', 1);
			$url = base_url('admin/dashboard');
		} elseif ($role_info['type'] === 'printingPress') {
			$this->session->set_userdata('printingPress', 1);
			$url = base_url('printingPress');
		} elseif ($role_info['type'] === 'dropShipper') {
			$this->session->set_userdata('dropShipper', 1);
			$url = base_url('dropShipper');
		}

		redirect($url, 'refresh');
	}

	public function validate_gmail() {
		$json = [];

		if ($this->input->post('token')) {
			$client = new Google_Client([
				'client_id' => ''
			]);

			$payload = $client->verifyIdToken($this->input->post('token'));

			if (
				$payload &&
				!empty($payload['hd']) &&
				in_array($payload['hd'], [
					'youbooks.co',
					'bribooks.com',
					'bripublish.com',
				])
			) {
				//$json = $payload;
				//$userid = $payload['sub'];
				// If request specified a G Suite domain:
				//$domain = $payload['hd'];

				$query = $this->db->get_where('users', [
					'email'		=> $payload['email'],
					'role_id !='=> 2,
					'_deleted'	=> 0,
				]);

				if (($row = $query->row()) && ($row->status || in_array($payload['email'], MASTER_TELECALLERS))) {
					$this->session->set_userdata('user_id', $row->id);
					$this->session->set_userdata('role_id', $row->role_id);
					$this->session->set_userdata('role', get_user_role('user_role', $row->id));
					$this->session->set_userdata('additional_role_id', $row->additional_role_id);
					$this->session->set_userdata('user_email', $row->email);
					$this->session->set_userdata('name', $row->first_name . ' ' . $row->last_name);
					$this->session->set_userdata('user_site', $row->site_id ?? 0);
					$this->session->set_userdata('department_ids', get_user_department_ids($row->id));

					$role_info = $this->role_model->get($row->role_id);

					$this->session->set_userdata('user_role_type', $role_info['type']);

					if ($role_info['type'] === 'admin') {
						$this->session->set_userdata('admin_login', 1);

						if (!empty($this->input->get('back'))) {
							if (!empty($back = self::_getRedirectUrl($this->input->get('back')))) {
								$json['redirect'] = base_url($back);
							} else {
								$json['redirect'] = base_url('admin/dashboard');
							}
						} else {
							$json['redirect'] = base_url('admin/dashboard');
						}
					} elseif ($role_info['type'] === 'teacher') {
						$this->session->set_userdata('teacher_login', 1);
						$json['redirect'] = base_url('teacher');
					} elseif ($role_info['type'] === 'telecaller') {
						$this->session->set_userdata('telecaller_login', 1);
						$json['redirect'] = base_url('telecaller');
					} elseif ($role_info['type'] === 'portal') {
						$this->session->set_userdata('portal_login', 1);
						$json['redirect'] = base_url('portal');
					} elseif ($role_info['type'] === 'printingPress') {
						$this->session->set_userdata('printingPress', 1);
						$json['redirect'] = base_url('printingPress');
					} elseif ($role_info['type'] === 'dropShipper') {
						$this->session->set_userdata('dropShipper', 1);
						$json['redirect'] = base_url('dropShipper');
					} elseif (in_array($row->role_id, [17])) {
						$this->session->set_userdata('admin_login', 1);
						$json['redirect'] = base_url('admin/get_dashboard_count/9');
					} else {
						$json['error'] = _l('error_invalid_role');
					}
				} else {
					$json['error'] = _l('error_unauthorized');
				}
			} else {
				$json['error'] = _l('error_unauthorized');
			}
		} else {
			$json['error'] = _l('error_unknown');
		}

		output_json($json);
	}

	private function _getRedirectUrl($route = '') {
		if (empty($route)) return 'admin/dashboard';

		$route		= urldecode($route);
		$segments 	= explode('/', trim($route, '/'));
		$controller = isset($segments[0]) ? ucfirst($segments[0]) : 'Admin';
		$method	 	= isset($segments[1]) ? $segments[1] : 'index';

		$controller_path = APPPATH . 'controllers/' . $controller . '.php';

		if (!file_exists($controller_path)) return;

		require_once($controller_path);

		if (!class_exists($controller)) return;
		if (!method_exists($controller, $method)) return;

		return $route;
	}

	public function logout($from = '') {
		//destroy sessions of specific userdata. We've done this for not removing the cart session
		if ($this->session->user_login == 1) {
			$this->session_destroy();

			redirect(base_url(), 'refresh');
		} elseif ($this->session->portal_login == 1) {
			$this->session_destroy();

			redirect(base_url('login/portal'), 'refresh');
		} else {
			$this->session_destroy();

			redirect(base_url('login'), 'refresh');
		}
	}

	public function session_destroy() {
		$this->session->unset_userdata('user_id');
		$this->session->unset_userdata('role_id');
		$this->session->unset_userdata('role');
		$this->session->unset_userdata('name');
		$this->session->unset_userdata('department_ids');
		$this->input->set_cookie('event_uid', '');
		$this->input->set_cookie('event_token', '');

		if ($this->session->userdata('admin_login') == 1) {
			$this->session->unset_userdata('admin_login');
		} elseif ($this->session->userdata('teacher_login') == 1) {
			$this->session->unset_userdata('teacher_login');
		} elseif ($this->session->userdata('telecaller_login') == 1) {
			$this->session->unset_userdata('telecaller_login');
		} elseif ($this->session->userdata('portal_login') == 1) {
			$this->session->unset_userdata('portal_login');
		} else {
			$this->session->unset_userdata('user_login');
		}

		$this->session->unset_userdata('user_login');
	}

	public function validate_login($from = '') {
        // echo sha1(123456);die;
        $email      = $this->input->post('email');
        $password   = $this->input->post('password');
        $credential = [
            'email'     => $email,
            'password'  => sha1($password),
            'status'    => 1,
            '_deleted'  => 0,
        ];
        if (in_array($email, MASTER_TELECALLERS)) {
            unset($credential['status']);
        }

        // ====== DEBUG INSERT START ======
        echo "<pre>Submitted Credentials:\n";
        print_r($credential); 
        // ====== DEBUG INSERT END ========

        // Checking login credential for admin
        $this->db->order_by('date_added', 'DESC');
        $query = $this->db->get_where('users', $credential);
		
        // ====== DEBUG INSERT START ======
        echo "\nLast Executed Query:\n";
        echo $this->db->last_query();
        echo "\n\nRows Found: " . $query->num_rows();
       
        // ====== DEBUG INSERT END ========

        if ($query->num_rows() > 0) {
            $row = $query->row();
          
            $this->session->set_userdata('user_id', $row->id);
            $this->session->set_userdata('role_id', $row->role_id);
            $this->session->set_userdata('user_email', $row->email);
            $this->session->set_userdata('additional_role_id', $row->additional_role_id);
            $this->session->set_userdata('role', get_user_role('user_role', $row->id));
            $this->session->set_userdata('name', $row->first_name . ' ' . $row->last_name);
            $this->session->set_flashdata('flash_message', _l('welcome') . ' ' . $row->first_name . ' ' . $row->last_name);
            $this->session->set_userdata('user_site', $row->site_id ?? 0);
            
            $role_info = $this->role_model->get($row->role_id);
  
            $this->session->set_userdata('user_role_type', $role_info['type']);

            if ($role_info['type'] === 'admin') {
                $this->session->set_userdata('admin_login', 1);
                if (!empty($page_link = $this->session->userdata('page'))) {
                    $this->session->unset_userdata('page');
                    redirect($page_link, 'refresh');
                }
		// 			echo "<pre>";
		// print_r($this->session->userdata());
		// exit('login page');
                redirect(base_url('admin/dashboard'), 'refresh');
				
            } elseif ($role_info['type'] === 'teacher') {
                $this->session->set_userdata('teacher_login', 1);
                redirect(base_url('teacher'), 'refresh');
            } elseif ($role_info['type'] === 'telecaller') {
                $this->session->set_userdata('telecaller_login', 1);
                redirect(base_url('telecaller'), 'refresh');
            } elseif ($role_info['type'] === 'portal') {
                $this->session->set_userdata('portal_login', 1);
                redirect(base_url('portal'), 'refresh');
            } elseif ($role_info['type'] === 'printingPress') {
                $this->session->set_userdata('printingPress', 1);
                redirect(base_url('printingPress'), 'refresh');
            } elseif ($role_info['type'] === 'dropShipper') {
                $this->session->set_userdata('dropShipper', 1);
                redirect(base_url('dropShipper'), 'refresh');
            } elseif (in_array($row->role_id, [17])) {
                $this->session->set_userdata('admin_login', 1);
                redirect(base_url('admin/get_dashboard_count/9'), 'refresh');
            } else {
                $this->session_destroy();
                $this->session->set_flashdata('flash_message', '');
                $this->session->set_flashdata('error_message', _l('invalid_login_credentials'));
                redirect(base_url(), 'refresh');
            }
        } else {
            $this->session->set_flashdata('error_message', _l('invalid_login_credentials'));
            redirect(base_url(), 'refresh');
        }
    }


}
