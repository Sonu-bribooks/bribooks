<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Address {
	public function getAddresses() {
		if (!$this->json) {
			$this->json['addresses'] = $this->address_model->get_all([
				'user_id'	=> (int)$this->session->userdata('user_id'),
				'status'	=> 1,
			])['rows'] ?? [];
		}
	}

	public function addAddress() {
		$this->form_validation->set_rules('name', _l('name'), 'trim|required|min_length[4]|max_length[128]');
		// $this->form_validation->set_rules('tel_code', _l('tel_code'), 'trim|required|min_length[2]|max_length[10]');
		if (mb_strtolower($this->input->post('country')) === 'india') {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|exact_length[12]',[
				'exact_length' 	=> _li('Please enter a valid 10 digit mobile number')
			]);
		} else {
			$this->form_validation->set_rules('mobile', _l('mobile'), 'trim|required|min_length[10]|max_length[15]',[
				'min_length' 	=> _li('Please enter a valid 10 digit mobile number'),
				'max_length' 	=> _li('Please enter a valid 15 digit mobile number')
			]);
		}

		if (mb_strtolower($this->input->post('country')) !== 'india') {
			$this->form_validation->set_rules('zipcode', _l('zipcode'), 'trim|required|min_length[4]|max_length[10]');
		} else {
			$this->form_validation->set_rules('zipcode', _l('zipcode'), 'trim|required|numeric|exact_length[6]', [
				'exact_length' 	=> _li('The Pin Code field must be exactly 6 characters in length.')
			]);
		}

		$this->form_validation->set_rules('address', _l('address'), 'trim|required|min_length[4]|max_length[255]');
		// $this->form_validation->set_rules('lagndmark', _l('landmark'), 'trim|required|min_length[4]|max_length[255]');
		$this->form_validation->set_rules('city', _l('city'), 'trim|required|min_length[2]|max_length[128]');
		$this->form_validation->set_rules('country', _l('country'), 'trim|required|min_length[2]|max_length[255]');
		$this->form_validation->set_rules('state', _l('state'), 'trim|required|min_length[2]|max_length[255]');
		$this->form_validation->set_rules('type', _l('type'), 'trim|required|in_list[Office,Other,Home]');
		self::_runFormValidation();

		if (!$this->json) {
			if (mb_strtolower($this->input->post('country')) !== 'india') {
				// Validate serviceability country
				if (!($country_info  = $this->db->get_where('delivery_country', [
					'name'	=> $this->input->post('country')
				])->row_array())) {
					$this->json['error'] = _li('We currently do not have shipping partner for this location. Please choose another delivery address.');
				}
			} else {
				// $locality = self::_getLocality([
				// 	'postcode'	=> $this->input->post('zipcode'),
				// ]);

				// if (empty($locality['postcode_details'])) {
				// 	$this->json['error'] = _li('We currently do not have shipping partner for this location. Please choose another delivery address.');
				// }
			}
		}

		if (!$this->json) {
			$address_id = $this->address_model->add([
				'user_id'	=> (int)$this->session->userdata('user_id'),
				'name'		=> $this->input->post('name'),
				// 'tel_code'	=> $this->input->post('tel_code'),
				'mobile'	=> $this->input->post('mobile'),
				'zipcode'	=> $this->input->post('zipcode'),
				'address'	=> $this->input->post('address'),
				'landmark'	=> $this->input->post('landmark'),
				'city'		=> $this->input->post('city'),
				'country'	=> $this->input->post('country'),
				'state'		=> $this->input->post('state'),
				'type'		=> $this->input->post('type'),
			]);

			$this->json['address_id'] = $address_id;
			$this->json['success'] = _l('address_saved_successfully');
		}
	}

	public function deleteAddress() {
		$this->form_validation->set_rules('address_id', _l('address_id'), [
			'trim',
			'required',
			'numeric',
			['address', [$this->validate_model, 'address']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->address_model->delete($this->input->post('address_id'));
			$this->json['success'] = _l('address_deleted_successfully');
		}
	}

	public function validateZipcode() {
		if (strtolower($this->input->post('country')) !== 'india') {
			$this->form_validation->set_rules('zipcode', _l('zipcode'), 'trim|required|min_length[4]|max_length[10]');
		} else {
			$this->form_validation->set_rules('zipcode', _l('zipcode'), 'trim|required|numeric|exact_length[6]');
		}

		self::_runFormValidation();

		if (!$this->json) {
			if (strtolower($this->input->post('country')) !== 'india') {
				// Validate serviceability country
				$this->json['locality'] = [];
			} else {
				$locality = $this->db->get_where('pincodes', [
					'pincode'	=> $this->input->post('zipcode'),
					'status'	=> 1,
					'_deleted'	=> 0,
				])->row_array();

				if (!empty($locality)) {
					$this->json['locality'] = [
						'country'	=> 'India',
						'pincode'	=> $locality['pincode'],
						'state'		=> $locality['state'],
						'city'		=> $locality['district'],
					];
				} else {
					$this->json['error'] = _li('We currently do not have shipping partner for this location. Please choose another delivery address.');
				}
			}
		}
	}

	public function saveShippingAddress() {
		$this->form_validation->set_rules('address_id', _l('address_id'), [
			'trim',
			'required',
			'numeric',
			['address', [$this->validate_model, 'address']]
		]);

		self::_runFormValidation();

		if (!$this->json) {
			$this->json['couriers']['data'] = [];

			$this->session->unset_userdata([
				'couriers',
				'shipping_info',
			]);

			$address_info = $this->address_model->get($this->input->post('address_id'));

			$this->session->set_userdata([
				'shipping_address_id'	=> (int)$this->input->post('address_id')
			]);

			$this->student_model->updateAddress(
				$this->session->userdata('user_id'),
				$this->input->post('address_id')
			);

			$this->json['address_id'] = (int)$this->input->post('address_id');

			self::_formatCouriers($address_info['zipcode'], $address_info['country']);

			$this->cart_lib->useShippingCredit('remove');

			self::_getCart();

			$this->json['success'] = _l('shipping_address_changed');
		}
	}
}
