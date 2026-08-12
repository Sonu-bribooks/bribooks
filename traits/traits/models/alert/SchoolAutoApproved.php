<?php defined('BASEPATH') or exit('No direct script access allowed');

trait SchoolAutoApproved {
	public function approvedLead($id = 0) {
		$this->load->model('school/SchoolLead_model', 'school_lead_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('school/SchoolInput_model', 'schoolinput_model');

		$lead_info = $this->school_lead_model->get($id);
		$event_info = $this->event_model->get($lead_info['event_id']);
		$parent_site_info = $this->site_model->get($event_info['parent_site_id']);

		$site_code = !empty($parent_site_info['site_code']) ? $parent_site_info['site_code'] : '';
		$site_code = $site_code . $lead_info['id'];

		if (
			$lead_info &&
			empty($lead_info['site_id']) &&
			!($site_info = $this->site_model->getByCode($site_code))
		) {
			$this->schoolinput_model->add([
				'name' 			=> $lead_info['name'],
				'state_id' 		=> $lead_info['state_id'],
				'city_id' 		=> $lead_info['city_id'],
				'date_added' 	=> $lead_info['date_added'],
				'date_modified' => $lead_info['date_modified'],
			]);

			self::_addSite($lead_info);

			// $this->alert_model->otherSchoolAutoApproval($lead_info['id']);
			$this->alert_model->schoolLeadRegistration($lead_info['id']);
			ENVIRONMENT === 'production' && $this->alert_model->schoolLeadShare($lead_info['id']);
		}
	}

	public function approvedLeadExisting($id = 0) {
		$this->load->model('school/SchoolLead_model', 'school_lead_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('school/SchoolInput_model', 'schoolinput_model');

		$lead_info = $this->school_lead_model->get($id);

		$event_info = $this->event_model->get($lead_info['event_id']);
		$parent_site_info = $this->site_model->get($event_info['parent_site_id']);

		$site_code = !empty($parent_site_info['site_code']) ? $parent_site_info['site_code'] : '';
		$site_code = $site_code . $lead_info['id'];

		if (
			$lead_info &&
			empty($lead_info['site_id']) &&
			!($site_info = $this->site_model->getByCode($site_code))
		) {
			self::_addSite($lead_info);

			ENVIRONMENT === 'production' && $this->alert_model->schoolLeadShare($lead_info['id']);
		}
	}

	private function _addSite($lead_info = []) {
		$this->load->model('common/Site_model', 'site_model');
		$this->load->model('event/Event_model', 'event_model');
		$this->load->model('event/EventSite_model', 'event_site_model');

		$event_info = $this->event_model->get($lead_info['event_id']);
		$parent_site_info = $this->site_model->get($event_info['parent_site_id']);

		$site_code = !empty($parent_site_info['site_code']) ? $parent_site_info['site_code'] : '';
		$site_code = $site_code . $lead_info['id'];

		if (!($site_info = $this->site_model->getByCode($site_code))) {
			$site_id = $this->site_model->add('', [
				'license_total'		=> 500,
				'name'				=> $lead_info['name'],
				'image' 			=> '',
				'parent_id'			=> $parent_site_info['id'] ?? 0,
				'payment_gateway'	=> $parent_site_info['payment_gateway'] ?? 'razorpay',
				'sms_gateway'		=> $parent_site_info['sms_gateway'] ?? 'textlocal',
				'email_alert'		=> '',
				'address'			=> '',
				'mobile_length'		=> $parent_site_info['mobile_length'] ?? 10,
				'country_code'		=> $parent_site_info['country_code'] ?? 'IN',
				'state_id'			=> $lead_info['state_id'],
				'city_id'			=> $lead_info['city_id'],
				'site_code'			=> !empty($parent_site_info['id']) ? $lead_info['id'] : ($site_code . $lead_info['id']),
				'discount_code'		=> $parent_site_info['discount_code'] ?? '',
				'discount_percentage' => $parent_site_info['discount_percentage'] ?? '',
				'currency_code'		=> $parent_site_info['currency_code'] ?? 'INR',
				'base_price'		=> $parent_site_info['base_price'] ?? 399.00,
				'ebook_price'		=> $parent_site_info['ebook_price'] ?? 299.00,
				'price_per_page'	=> $parent_site_info['price_per_page'] ?? 8.00,
				'free_page_limit'	=> $parent_site_info['free_page_limit'] ?? 80,
				'hard_cover_price'	=> $parent_site_info['hard_cover_price'] ?? 50.00,
				'tax'				=> $parent_site_info['tax'] ?? 0,
				'tax_text'			=> $parent_site_info['tax_text'] ?? 'GST',
				'timezone'			=> $parent_site_info['timezone'] ?? 'Asia/Kolkata',
				'owner_name'		=> $lead_info['school_head'],
				'authorized_person'	=> $lead_info['authorized_person'],
				'owner_email'		=> $lead_info['email'],
				'owner_mobile'		=> $lead_info['mobile'],
				'owner_password'	=> $lead_info['email'],
				'can_add_site'		=> 0,
				'status'			=> 1,
				'date_added'		=> date('Y-m-d H:i:s'),
				'date_modified'		=> date('Y-m-d H:i:s'),
			]);

			$this->school_lead_model->edit($lead_info['id'], [
				'site_id' => $site_id
			]);

			// add to event
			if (!empty($lead_info['event_id'])) {
				$this->event_site_model->add([
					'event_id'	=> (int)$lead_info['event_id'],
					'site_id'	=> (int)$site_id,
				]);
			}
		}
	}
}
