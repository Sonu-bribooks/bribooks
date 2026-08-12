<?php defined('BASEPATH') or exit('No direct script access allowed');

trait EventDataCleaning {
	public function clean_event_data() {
		if (!empty($this->input->post() && !empty($this->input->post('type')))) {
			$method 			= sprintf('_update%sData', ucwords($this->input->post('type')));

			if (method_exists($this, $method)) {
				self::{$method}($this->input->post());
			}
		}

		redirect(base_url('admin/clean_event_data_form'), 'refresh');
	}

	public function clean_event_data_form() {
		$data['page_name'] 						= 'generic/form';
		$data['page_title'] 					= _l('clean_event_data');
		$data['action'] 						= base_url('admin/clean_event_data');

        $data['fields'][] = [
			'type'		=> 'select',
			'key'		=> 'type',
			'label'		=> _l('select_type'),
			'required'	=> true,
			'value'		=> '',
			'options'	=> [
				[
					'value' => 'site',
					'label' => _l('site'),
				],
				[
					'value' => 'user',
					'label' => _l('user'),
				],
				[
					'value' => 'book',
					'label' => _l('book'),
				],
			],
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'ids',
			'label'		=> _l('ids'),
			'required'	=> true,
			'value'		=> '',
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'event_id',
			'label'		=> _l('event_id'),
			'required'	=> false,
			'value'		=> '',
		];

		$this->load->view('backend/index', $data);
	}

	private function _updateSiteData($data = []) {
		if (!empty($data['ids'])) {
			$ids = explode(',', $data['ids']);

			foreach($ids as $id) {
				if (!empty($info = $this->site_model->get($id))) {
					$this->site_model->delete($info['id']);
					
					$this->db->update('schools', [
						'_deleted'			=> 1,
						'date_deleted'		=> date('Y-m-d H:i:s'),
					], [
						'site_id'			=> (int)$info['id'],
					]);

					$this->db->update('users', [
						'_deleted'			=> 1,
						'date_deleted'		=> date('Y-m-d H:i:s'),
					], [
						'site_id'			=> (int)$info['id'],
						'role_id'			=> 9
					]);
					
					if (!empty($data['event_id']) && !empty($event_info = $this->event_site_model->getEventIdBySiteId($data['event_id'], $id))) {
						$this->event_site_model->delete($event_info['id']);
					}
				}
			}
		}
	}

	private function _updateUserData($data = []) {
		if (!empty($data['ids'])) {
			$ids = explode(',', $data['ids']);

			foreach($ids as $id) {
				if (!empty($info = $this->user_model->get($id))) {
					$this->user_model->delete($info['id']);
					
					if (!empty($data['event_id']) && !empty($event_info = $this->event_user_model->getEventUserByUserId($data['event_id'], $id))) {
						$this->event_user_model->delete($event_info['id']);
					}
				}
			}
		}
	}

	private function _updateBookData($data = []) {
		if (!empty($data['ids'])) {
			$ids = explode(',', $data['ids']);

			foreach($ids as $id) {
				if (!empty($info = $this->book_model->get($id))) {
					$this->book_model->delete($info['id']);

					$this->db->update('book_version', [
						'_deleted'			=> 1,
						'date_deleted'		=> date('Y-m-d H:i:s'),
					], [
						'book_id'			=> (int)$info['id']
					]);

					$this->db->update('bookstore', [
						'_deleted'			=> 1,
						'date_deleted'		=> date('Y-m-d H:i:s'),
					], [
						'book_id'			=> (int)$info['id']
					]);
					
					if (!empty($data['event_id']) && !empty($event_info = $this->event_book_model->getEventBookByBookId($data['event_id'], $id))) {
						$this->event_book_model->delete($event_info['id']);
					}
				}
			}
		}
	}

	public function merge_site_data() {
		if (!empty($this->input->post() && !empty($this->input->post('new_id'))) && !empty($this->input->post('delete_id'))) {
			if (!empty($site_info = $this->site_model->get($this->input->post('new_id'))) && !empty($del_site_info = $this->site_model->get($this->input->post('delete_id')))) {

				if (empty($school_info = $this->school_model->getBySiteID($site_info['id']))) {
					$this->school_model->editBySite($del_site_info['id'], [
						'site_id' 			=> $site_info['id'],
						'name' 				=> $site_info['name'],
						'owner_email' 		=> $site_info['owner_email'],
						'owner_mobile' 		=> $site_info['owner_mobile'],
						'owner_name' 		=> $site_info['owner_name'],
						'authorized_person' => $site_info['authorized_person'],
						'state_id' 			=> $site_info['state_id'],
						'city_id' 			=> $site_info['city_id'],
					]);
				} else {
					$this->school_model->editBySite($del_site_info['id'], [
						'_deleted' 		=> 1,
						'date_deleted'	=> date('Y-m-d H:i:s'),
					]);
				}

				$this->db->update('users', [
					'site_id' 		=> (int)$site_info['id'],
					'date_modified' => date('Y-m-d H:i:s')
				], [
					'site_id'		=> (int)$del_site_info['id'],
					'role_id'		=> 2
				]);

				if (empty($school_user_info = $this->school_user_model->get_all([
					'site_id' 		=> $site_info['id'],
					'first_name' 	=> $site_info['name'],
					'email' 		=> $site_info['owner_email'],
					'mobile' 		=> $site_info['owner_mobile'],
					'state_id' 		=> $site_info['state_id'],
					'city_id' 		=> $site_info['city_id'],
				])['rows'] ?? [])) {
					$this->db->update('users', [
						'site_id' 		=> (int)$site_info['id'],
						'date_modified' => date('Y-m-d H:i:s')
					], [
						'site_id'		=> (int)$del_site_info['id'],
						'role_id'		=> 9
					]);
				} else {
					$this->db->update('users', [
						'_deleted' 		=> 1,
						'date_deleted'	=> date('Y-m-d H:i:s')
					], [
						'site_id'		=> (int)$del_site_info['id'],
						'role_id'		=> 9
					]);
				}

				$this->db->query("
					UPDATE event_site es1
					JOIN event_site es2 
						ON es1.event_id = es2.event_id
					AND es1.site_id = {$del_site_info['id']}
					AND es2.site_id = {$site_info['id']}
					SET es1._deleted = 1
					WHERE es1._deleted = 0
					AND es2._deleted = 0
				");

				$this->db->update('event_site', [
					'site_id' 		=> (int)$site_info['id'],
				], [
					'site_id'		=> (int)$del_site_info['id'],
					'_deleted'		=> 0
				]);

				$this->site_model->delete($del_site_info['id']);
			}
		}

		redirect(base_url('admin/merge_site_data_form'), 'refresh');
	}

	public function merge_site_data_form() {
		$data['page_name'] 						= 'generic/form';
		$data['page_title'] 					= _l('merge_site_data_only');
		$data['action'] 						= base_url('admin/merge_site_data');

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'new_id',
			'label'		=> 'New Id (Will be retained)',
			'required'	=> true,
			'value'		=> '',
		];

        $data['fields'][] = [
			'type'		=> 'text',
			'key'		=> 'delete_id',
			'label'		=> 'Id (Will be deleted)',
			'required'	=> true,
			'value'		=> '',
		];

		$this->load->view('backend/index', $data);
	}
}
