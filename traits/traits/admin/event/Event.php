<?php defined('BASEPATH') or exit('No direct script access allowed');

load_trait('admin/event/config');
load_trait('admin/event/eventchallenge');
load_trait('admin/event/league');
load_trait('admin/event/invite');

trait Event {
	use
		EventType,
		EventPartner,
		BasicInfo,
		EventConfig,
		DataImport,
		Brochure,
		LandingPage,
		Signup,
		CommunicationKit,
		Certificate,
		CertificateType,
		CertificateMessageTemplate,
		CertificateTemplate,
		Ranking,
		Finish,
		EventAward,
		EventAwardGroup,
		EventChallengeDaily,
		EventChallengeWeekly,
		EventChallengeGeneral,
		EventChallengeGenre,
		EventChallengeSchool,
		EventChallengeCity,
		EventChallengeGroup,
		EventChallengeVote,
		EventChallengeCountry,
		EventChallengeState,
		EventLeagueGroup,
		LeagueTemplate,
		LeagueBreakpointMessage,
		EventInviteTemplate,
		EventInvite,
		AuthorWall,
		EventExhibition
	;

	public function event($action = NULL, $id = NULL) {
		if ($action == 'status') {

			$this->event_model->enableDisable($id);
			redirect(site_url('admin/event'), 'refresh');
		} elseif ($action == 'delete') {
			$this->event_model->delete($id);
			redirect(site_url('admin/event'), 'refresh');
		}

		$data['fields'] = [
			'sn',
			'id',
			'name',
			'slug',
			'label',
			'country_code',
			'url',
			'start_date',
			'end_date',
			'status',
			'date_modified',
			'actions',
		];

		$data['page_name'] 		= 'generic/index';
		$data['page_title'] 	= _l('event');
		$data['action_add'] 	= base_url('admin/event_form/add');
		$data['action_ajax'] 	= base_url('admin/ajax_events');

		$data['actions'] 		= [
			[
				'key'	=> 'edit',
				'url'	=> 'admin/event_form/edit/',
			],
			[
				'key'	=> 'status',
				'type' 	=> 'status',
				'url'	=> 'admin/event/status/',
			],
			[
				'key'	=> 'delete',
				'type' 	=> 'confirm',
				'url'	=> 'admin/event/delete/',
			],
		];

		$this->load->view('backend/index', $data);
	}

	public function event_form($action = NULL, $id = 0, $stage = 'basic_info') {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($action == 'add') {
			$data['id'] 			= (int)$id;
			$data['page_title'] 	= _l('event_add');
			$data['action'] 		= base_url('admin/event_form/add/0/');
			$data['action_stage'] 	= base_url('admin/ajax_event_stage/0/');
		} elseif ($action == 'edit') {
			$data['id'] 			= (int)$id;
			$data['action'] 		= base_url('admin/event_form/edit/' . (int)$id . '/');
			$data['action_stage'] 	= base_url('admin/ajax_event_stage/' . (int)$id . '/');
			$data['page_title'] 	= _l('event_edit');
		}

		$data['current_stage'] = (string)$stage;

		$data['stages'] = [
			[
				'id'	=> 'basic_info',
				'name'	=> _l('basic_info'),
				'icon'	=> 'fa-info-circle'
			],
			[
				'id'	=> 'config',
				'name'	=> _l('config'),
				'icon'	=> 'fa-cog'
			],
			[
				'id'	=> 'landing_page',
				'name'	=> _l('landing_page'),
				'icon'	=> 'fa-file'
			],
			[
				'id'	=> 'data_import',
				'name'	=> _l('data_import'),
				'icon'	=> 'fa-database'
			],
			[
				'id'	=> 'brochure',
				'name'	=> _l('brochure'),
				'icon'	=> 'fa-book'
			],
			[
				'id'	=> 'signup',
				'name'	=> _l('signup'),
				'icon'	=> 'fa-user-plus'
			],
			[
				'id'	=> 'communication_kit',
				'name'	=> _l('communication_kit'),
				'icon'	=> 'fa-envelope'
			],
			[
				'id'	=> 'certificate',
				'name'	=> _l('certificate'),
				'icon'	=> 'fa-certificate'
			],
			[
				'id'	=> 'ranking',
				'name'	=> _l('ranking'),
				'icon'	=> 'fa-trophy'
			],
			[
				'id'	=> 'finish',
				'name'	=> _l('finish'),
				'icon'	=> 'fa-check-circle'
			],
		];

		$data['nav'] 			= $this->load->view('backend/admin/event/stage/nav', $data, true);
		$data['page_name'] 		= 'event/form';

		$this->load->view('backend/index', $data);
	}

	public function ajax_event_stage($id = 0, $stage = 'basic_info') {
		$info = $country_info = $currency_info = $event_type_info = [];

		if ($id) {
			$info 			= $this->event_model->get($id);
			$country_info 	= $this->country_model->get($info['country_id']);
			$currency_info 	= $this->currency_model->get($info['currency_id']);
			$event_type_info= $this->event_type_model->get($info['event_type_id']);
		}

		$method = sprintf('_get%s', str_replace(' ', '', ucwords(str_replace('_', ' ', $stage))));

		if (method_exists($this, $method)) {
			self::{$method}(compact('stage', 'info', 'country_info', 'currency_info', 'event_type_info'));
		} else {
			exit(_l('not_found'));
		}
	}

	public function ajax_events() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->event_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'					=> $filter_data['start'] + 1 + $key,
				'id'					=> $result['id'],
				'name'					=> $result['name'],
				'slug'					=> $result['slug'],
				'label'					=> $result['label'],
				'country_code'			=> $result['country_code'],
				'url'					=> _eventUrls($result['slug']),
				'start_date'			=> formatDate($result['start_date']),
				'end_date'				=> formatDate($result['end_date']),
				'status'				=> _sd($result['status']),
				'date_modified'			=> formatDate($result['date_modified']),
				'actions'				=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		output_json($json);
	}

	private function _validateEventForm($id = 0) {
		// $this->session->set_flashdata('error_message', _l('user_has_already_event'));
		// redirect(base_url('admin/medallion'), 'refresh');
	}

	public function ajax_search_events() {
		$json = [];

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 10,
			'search'			=> $this->input->get('search'),
		];

		$results = $this->event_model->get_all($filter_data)['rows'] ?? [];

		$json[] = [
			'id'				=> 0,
			'text'				=> _l('generic'),
		];

		foreach ($results as $key => $result) {
			$json[] = [
				'id'				=> $result['id'],
				'text'				=> $result['name'],
			];
		}

		output_json($json);
	}
}
