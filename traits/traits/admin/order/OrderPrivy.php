<?php defined('BASEPATH') or exit('No direct script access allowed');

trait OrderPrivy {
	public function order_privy($param1 = '', $param2 = '') {
		$data['page_name'] 		= 'order_privy/index';
		$data['page_title'] 	= _l('order_privy');
		$data['navigation'] 	= 'nav';
		$data['status'] 		= 0;
		$data['action_ajax'] 	= site_url('admin/ajax_order_privy');

		$data['timestamp_start'] 	= strtotime('-30 days', time());
		$data['timestamp_end']	 	= time();

		$this->load->view('backend/index', $data);
	}

	public function ajax_order_privy() {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
            'status'            => 0
		];

        if (!empty($this->input->get('status'))) {
            $filter_data['status'] = $this->input->get('status');
        }

		$results = $this->order_privy_model->get_all($filter_data);


		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		foreach ($results['rows'] ?? [] as $key => $result) {

			$products = $this->order_model->getProducts($result['order_id']);

            if (!empty($this->input->get('status'))) {
                $json['data'][] = [
                    'sn'			=> $filter_data['start'] + 1 + $key,
                    'order_code'	=> '<a href="/admin/order_details/'.$result['order_id'].'" target="_blank">'.$result['order_code'].'</a>',
                    'product'		=> _op_name($products, $result),
                    'customer'		=> $result['first_name'] . ' ' . $result['last_name'] . '<br /><small>' . $result['email'] . '<br />' . $result['mobile'] . '</small><br />' ,
                    'weight_amount'	=> $result['weight'] . 'gm' . '<br>' . $result['currency_symbol'] . ' ' . $result['total'],
                    'confirm_date'	=> formatDate($result['confirm_date']),
                    'status'		=> _sd($result['status'] ? 1 : 0),
                    'comment'		=> '<b>'.$result['agent_name'].'</b> : '.$result['comment'],
                ];
            } else {
                $json['data'][] = [
                    'sn'			=> $filter_data['start'] + 1 + $key,
                    'order_code'	=> '<a href="/admin/order_details/'.$result['order_id'].'" target="_blank">'.$result['order_code'].'</a>',
                    'product'		=> _op_name($products, $result),
                    'customer'		=> $result['first_name'] . ' ' . $result['last_name'] . '<br /><small>' . $result['email'] . '<br />' . $result['mobile'] . '</small><br />' ,
                    'weight_amount'	=> $result['weight'] . 'gm' . '<br>' . $result['currency_symbol'] . ' ' . $result['total'],
                    'status'		=> _sd($result['status'] ? 1 : 0),
                    'date_added'	=> formatDate($result['date_added']),
                    'comment'		=> !empty($result['comment']) ? ('<b>'.$result['agent_name'].'</b> : '.$result['comment']) : '',
                    'actions'		=> '<div class="dropright dropright">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="mdi mdi-dots-vertical"></i></button>
                    <ul class="dropdown-menu">
                    <li>
                    <a class="dropdown-item confirmOrder" href="#" order_privy_status="0" order_privy_id='.$result['id'].' >Comment</a>
                    </li>
                    <li>
                    <a class="dropdown-item confirmOrder" href="#" order_privy_status="1" order_privy_id='.$result['id'].' >Confirm Order</a>
                    </li>
                    </ul></div>',
                ];
            }


		}

		output_json($json);
	}

    public function add_order_privy_comment() {
		$json = [];

		$order_info = $this->order_privy_model->get($this->input->post('order_privy_id'));

        if (!empty( $this->input->post('order_privy_id')) || !empty($order_info)) {
            $this->order_privy_model->edit($this->input->post('order_privy_id'),[
                'agent_id' 	    => (int)$this->session->userdata('user_id'),
                'comment' 	    => $this->input->post('comment'),
                'status' 		=> $this->input->post('order_privy_status'),
                'confirm_date'  => !empty($this->input->post('order_privy_status')) ? date('Y-m-d H:i:s') : NULL
            ]);

			$this->order_comment_model->add([
                'manager_id' 	=> (int)$this->session->userdata('user_id'),
                'order_id' 		=> $order_info['order_id'],
                'description' 	=> $this->input->post('comment'),
                'status' 		=> 1
            ]);

            $json['success'] 	= _l('order_comment_added');

		} else {
			$json['error'] = _l('order_not_found');
		}

		output_json($json);
	}
}
