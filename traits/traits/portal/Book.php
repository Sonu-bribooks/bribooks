<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Book {
	public function book() {
		$data['page_name']		= 'book';
		$data['page_title']		= _l('books');

		self::filterSite($data, 'book/');

		$data['action_ajax'] = site_url('portal/ajaxBook/?site_id=' . (int)$data['site_id']);

		$this->load->view('backend/index', $data);
	}

	public function ajaxBook() {
		$data = $json['data'] = [];

		self::filterSite($data, 'book');

		$columns = $this->input->get('columns');

		$filter_data = [
			'archived'			=> $archived,
			'site_id'			=> $data['site_id'],
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]')),
		];

		$result = $this->book_model->get_all($filter_data);

		$json['recordsTotal'] 		= $result['total'];
		$json['recordsFiltered'] 	= count($result['rows'] ?? []);

		foreach ($result['rows'] ?? [] as $key => $result) {
			$category_info = $this->category_model->get($result['category_id']);
			$user_info = $this->student_model->get($result['user_id']);
			$pages = $this->page_model->get_all([
				'book_id' => $result['id'],
			])['total'];
			$total = $this->db->get_where('order_product',[
				'product_id' => $result['id'],
			])->num_rows() ;

			$json['data'][] = [
				'sn'				=> $key + 1,
				'id'				=> $result['id'],
				'theme'				=> $category_info['name'],
				'user'				=> $user_info['first_name'] . ' ' . $user_info['last_name'],
				'name'				=> $result['name'],
				'isbn'				=> $result['isbn'],
				'author_name'		=> $result['author_name'],
				'status'			=> ($result['status'] == '2') ? (($review_logs['total'] > 0) ? '<i class="mdi mdi-circle" style="color: #4287f5; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="' . strip_tags($review_logs['rows'][0]['comment']) . '" data-original-title="%s"></i>' : _sd($result['status'])) : _sd($result['status']),
				'date_added'		=> formatDate($result['date_added']),
				'date_published'	=> formatDate($result['date_published']),
				'date_approved'		=> formatDate($result['date_approved']),
				'page_count' 		=> $pages,
				'actions'			=> ['id' => $result['id'], 'status' => $result['status'] ?? 0],
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($json));
	}
}
