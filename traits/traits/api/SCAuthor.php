<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait SCAuthor {
	public function updateAddressSC() {
		$this->form_validation->set_rules('uid', _l('user_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('code', _l('verification_code'), 'trim|required|min_length[8]|max_length[255]');
		$this->form_validation->set_rules('bid', _l('book_id'), 'trim|required|numeric');
		$this->form_validation->set_rules('fullName', _l('Author Name'), 'trim|required');
		$this->form_validation->set_rules('phoneNumber', _l('Phone Number'), 'trim|required|numeric');
		$this->form_validation->set_rules('email', _l('Email Id'), 'trim|required');
		// $this->form_validation->set_rules('address', _l('Delivery Address'), 'trim');
		// $this->form_validation->set_rules('landmark', _l('Delivery Landmark'), 'trim');
		// $this->form_validation->set_rules('pincode', _l('Delivery Pincode'), 'trim|numeric');

		self::_runFormValidation();

		if (!$this->json) {
			if ($this->db->get_where('users', ['id'	=> $this->input->post('uid'), 'verification_code' => $this->input->post('code')])->row_array()) {
				$this->load->model('address/SCMedallionAddress_model', 'sc_user_medallion_address');

				$save = [
					'full_name'		=> $this->input->post('fullName'),
					'mobile'		=> $this->input->post('phoneNumber'),
					'email'			=> $this->input->post('email')
				];

				if(!empty($this->input->post('address')) && !empty($this->input->post('pincode'))) {
					$address = json_encode([
						'full_name'		=> $this->input->post('fullName'),
						'mobile'		=> $this->input->post('phoneNumber'),
						'email'			=> $this->input->post('email'),
						'address'		=> $this->input->post('address'),
						'landmark'		=> $this->input->post('landmark'),
						'pincode'		=> $this->input->post('pincode')
					]);

					$save['address'] = $address;
					$save['landmark'] = $this->input->post('landmark');
					$save['pincode'] = $this->input->post('pincode');
				}

				$this->sc_user_medallion_address->editByIds($this->input->post('uid'), $this->input->post('bid'), $save);

				/*if ($user_certificate_address_info = $this->sc_user_medallion_address->getByIds($this->input->post('uid'), $this->input->post('bid'))) {
					$this->sc_user_medallion_address->edit($user_certificate_address_info['id'], [
						'address'	=> $address
					]);
				} else {
					$this->sc_user_medallion_address->add([
						'user_id'		=> $this->input->post('uid'),
						'book_id'		=> $this->input->post('bid'),
						'address'		=> $address,
					]);
				}*/

				$this->json['success'] = _l('address_successfully_saved');
			} else {
				$this->json['error'] = _li('Invalid request');
			}
		} else {
			$this->json['error'] = _li('Invalid request');
		}
	}
}
