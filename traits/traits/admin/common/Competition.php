<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Competition
{
	public function competition($param1 = NULL, $param2 = NULL)
	{
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}


		$data['page_name'] 		= 'competition/index';
		$data['page_title'] 	= _l('Competition');
		$data['action_ajax'] 	= site_url('admin/ajax_competition');

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 50
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_competition()
	{
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (!empty($this->input->get('start'))) ? (int)$this->input->get('start') : 0,
			'limit'				=> (!empty($this->input->get('length'))) ? (int)$this->input->get('length') : 20,
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->competition_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'		=> $filter_data['start'] + 1 + $key,
				'name'		=> $result['name'],
				'limit'		=> $result['limit'],
				'price'		=> $result['price'],
				'start_date' => formatDate($result['start_date']),
				'end_date'	=> formatDate($result['end_date']),
				'status'	=> _sd($result['status']),
				'date_added' => formatDate($result['date_added']),
				'actions'	=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function add_competition($id = false)
	{
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		$competition = array();

		if ($id) {
			$competition = $this->competition_model->get($id);
			if (empty($competition)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/competition'), 'refresh');
			}
		}

		$data['page_name'] 		= 'competition/add';
		$data['page_title'] 	= _l('Add Competition');
		$data['subscriptions']	= $this->subscription_plan_model->get_all();

		$data['competitionInfo'] = $competition;

		$this->load->view('backend/index', $data);
	}

	function delete_competition($id = false)
	{
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if (!$id) {
			$this->session->set_flashdata('error_message', 'Invalid data ID.');
			redirect(site_url('admin/competition'), 'refresh');
		}

		if ($id) {
			$competition = $this->competition_model->get($id);
			if (empty($competition)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/competition'), 'refresh');
			}
		}

		$this->competition_model->delete($id);
		$this->session->set_flashdata('success_message', 'Data saved successfully');
		redirect(site_url('admin/competition'), 'refresh');
	}

	function save_competition($id = false)
	{
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($id) {
			$competition = $this->competition_model->get($id);
			if (empty($competition)) {
				$this->session->set_flashdata('error_message', 'Invalid data ID.');
				redirect(site_url('admin/competition'), 'refresh');
			}
		}

		$this->form_validation->set_message('alpha_numeric_spaces', 'Only Characters, Numbers & Spaces are  allowed in %s');

		$config = array(
			array(
				'field' => 'name',
				'label' => 'Competition Name',
				'rules' => 'trim|required',
			),
			array(
				'field' => 'user_limit',
				'label' => 'User Limit',
				'rules' => 'trim|required',
			),
			array(
				'field' => 'site',
				'label' => 'Competition Site',
				'rules' => 'trim|required|numeric',
			),
			array(
				'field' => 'subscriptions',
				'label' => 'Competition Subscriptions',
				'rules' => 'trim|required|numeric',
			),
			array(
				'field' => 'price',
				'label' => 'Competition Price',
				'rules' => 'trim|required',
			),
			array(
				'field' => 'start_date',
				'label' => 'Start Date',
				'rules' => 'trim|required',
			),
			array(
				'field' => 'end_date',
				'label' => 'End Date',
				'rules' => 'trim|required',
			),
			array(
				'field' => 'status',
				'label' => 'status',
				'rules' => 'trim|required|in_list[1,0]',
			)
		);

		$this->form_validation->set_rules($config);
		if ($this->form_validation->run()) {

			$save =  array(
				'site_id' 		=> trim($this->input->post('site')),
				'subscription_plan_id' 	=> trim($this->input->post('subscriptions')),
				'name' 		=> trim($this->input->post('name')),
				'limit' 	=> trim($this->input->post('user_limit')),
				'price' 	=> trim($this->input->post('price')),
				'start_date' => trim($this->input->post('start_date')),
				'end_date' 	=> trim($this->input->post('end_date')),
				'status' 	=> trim($this->input->post('status'))
			);

			if (!$id) {
				$this->competition_model->add($save);
			} else {
				$this->competition_model->edit($id, $save);
			}

			$this->session->set_flashdata('success_message', 'Data saved successfully');
			redirect('/admin/competition/', 'refresh');
		} else {
			$this->data['error'] = validation_errors();
			$this->session->set_flashdata('error_message', validation_errors());
			redirect(base_url('admin/add_competition/' . $id), 'refresh');
		}
	}

	public function competition_user($param1 = NULL, $param2 = NULL)
	{
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}


		$data['page_name'] 		= 'competition/user';
		$data['page_title'] 	= _l('Competition user');
		$data['action_ajax'] 	= site_url('admin/ajax_competition_user');

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 50
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_competition_user()
	{
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (!empty($this->input->get('start'))) ? (int)$this->input->get('start') : 0,
			'limit'				=> (!empty($this->input->get('length'))) ? (int)$this->input->get('length') : 20,
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->competition_order_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'		=> $filter_data['start'] + 1 + $key,
				'name'		=> $result['competition'],
				'price'		=> $result['amount'],
				'provider' => $result['provider'],
				'status'	=> _sd($result['status']),
				'date_added' => formatDate($result['date_added']),
				'actions'	=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function competition_order($param1 = NULL, $param2 = NULL)
	{
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}


		$data['page_name'] 		= 'competition/order';
		$data['page_title'] 	= _l('Competition Order');
		$data['action_ajax'] 	= site_url('admin/ajax_competition_order');

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 50
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_competition_order()
	{
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (!empty($this->input->get('start'))) ? (int)$this->input->get('start') : 0,
			'limit'				=> (!empty($this->input->get('length'))) ? (int)$this->input->get('length') : 20,
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->competition_order_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'		=> $filter_data['start'] + 1 + $key,
				'name'		=> $result['first_name'].' '.$result['last_name'],
				'email'		=> $result['email'],
				'mobile'	=> $result['mobile'],
				'competition'=> $result['competition'],
				'price'		=> $result['currency_symbol'].' '.$result['amount'],
				'provider'  => $result['provider'],
				'status'	=> _sd($result['status']),
				'date_added'=> formatDate($result['date_added']),
				'actions'	=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}

	public function competition_payment($param1 = NULL, $param2 = NULL)
	{
		if ($this->session->userdata('admin_login') != true) {
			redirect(site_url('login'), 'refresh');
		}


		$data['page_name'] 		= 'competition/payment';
		$data['page_title'] 	= _l('Competition Payment');
		$data['action_ajax'] 	= site_url('admin/ajax_competition_payment');

		$filter_data = [
			'start'				=> 0,
			'limit'				=> 50
		];

		$this->load->view('backend/index', $data);
	}

	public function ajax_competition_payment()
	{
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (!empty($this->input->get('start'))) ? (int)$this->input->get('start') : 0,
			'limit'				=> (!empty($this->input->get('length'))) ? (int)$this->input->get('length') : 20,
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$results = $this->competition_payment_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {
			$json['data'][] = [
				'sn'		=> $filter_data['start'] + 1 + $key,
				'name'		=> $result['first_name'].' '.$result['last_name'],
				'email'		=> $result['email'],
				'mobile'	=> $result['mobile'],
				'competition'=> $result['competition'],
				'price'		=> $result['currency_symbol'].' '.$result['amount'],
				'provider'  => $result['provider'],
				'status'	=> _sd($result['status']),
				'date_added'=> formatDate($result['date_added']),
				'actions'	=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
