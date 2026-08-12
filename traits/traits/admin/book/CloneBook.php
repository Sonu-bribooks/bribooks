<?php defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

trait CloneBook {
	public function clone_books($param1 = NULL, $param2 = NULL) {
		if ($this->session->userdata('admin_login') != true) {
			redirect(base_url('login'), 'refresh');
		}

		if ($param1 == 'delete') {
			
			$bookstore_info 	= $this->bookstore_model->getByBookId($param2);
			$bookclone_info 	= $this->book_clone_model->get_all([
                'book_id' => $param2
            ])['rows'][0] ?? '';
			$bookversion_info 	= $this->book_version_model->get_all([
				'book_id' => $param2
				])['rows'] ?? [];
				
			$this->book_model->delete($param2);

            if (!empty($bookclone_info)) {
                $this->book_clone_model->delete($bookclone_info['id']);
            }

			$this->bookstore_model->delete($bookstore_info['id'] ?? 0);

			if (!empty($bookversion_info)) {
				$this->db->where_in('id', array_column($bookversion_info, 'id'));
				$this->db->update('book_version',  [
					'_deleted'		=> 1,
					'date_deleted'	=> date('Y-m-d H:i:s'),
				]);
			}
			redirect(base_url('admin/clone_books'), 'refresh');
		}

		$data['status'] 		= 0;
		$data['page_name'] 		= 'books/clone_books';
		$data['page_title'] 	= _l('all clone books');
		$data['action_ajax'] 	= base_url('admin/ajax_clone_books');

		$this->load->view('backend/index', $data);
	}

	public function ajax_clone_books($status = 0) {
		$json['data'] = [];

		$columns = $this->input->get('columns');

		$filter_data = [
			'start'				=> (int)$this->input->get('start'),
			'limit'				=> (int)$this->input->get('length'),
			'search'			=> $this->input->get('search[value]'),
			'sort'				=> $columns[$this->input->get('order[0][column]')]['data'] ?? '',
			'order'				=> mb_strtoupper($this->input->get('order[0][dir]'))
		];

		$results = $this->book_clone_model->get_all($filter_data);

		$json['recordsTotal'] 		= $results['total'];
		$json['recordsFiltered'] 	= $results['total'];

		$this->load->model('book/BookTitleVerso_model', 'book_title_verso_model');

		foreach ($results['rows'] ?? [] as $key => $result) {
            $book_info = $this->book_model->get($result['new_book_id']);

            if (!empty($book_info)) {
                $event_book_info = $this->event_book_model->get_all(['book_id' => $book_info['id']]);
    
                $event_id = (!empty($event_book_info['rows'][0])) ? $event_book_info['rows'][0]['event_id'] : '';
    
                $amazon_book_info = $this->amazon_book_model->get_all(['book_id' => $book_info['id']]);
    
                $amazon_book_id = (!empty($amazon_book_info['rows'][0])) ? $amazon_book_info['rows'][0]['id'] : '';
    
                $hall_of_fame_info = $this->hall_of_fame_model->get_all(['book_id'	=> $book_info['id']]);
    
                $hall_of_fame_id = (!empty($hall_of_fame_info['rows'][0])) ? $hall_of_fame_info['rows'][0]['id'] : '';
    
                $category_info = $this->category_model->get($book_info['category_id']);
                $user_info = $this->user_model->get($book_info['user_id']);
                $pages = $this->page_model->get_all([
                    'book_id' => $book_info['id'],
                ])['total'];
                $review_logs = $this->reviewlog_model->get_all([
                    'book_id' => $book_info['id'],
                    'order'	=> 'DESC'
                ]);
                $reviewr_name = $this->user_model->get($book_info['reviewer_id']);
    
                $book_title_verso_info = $this->book_title_verso_model->get_all([
                    'book_id'	=> $book_info['id']
                ])['rows'][0] ?? [];
    
                $custom_theme_book  = $this->page_version_model->get_all([
                    'book_id'		=> $book_info['id'],
                    'version'		=> $book_info['version'],
                    'is_custom_id'	=> 1,
                ])['rows'][0] ?? [];
    
                $actions = '<div class="dropright dropright">
                <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-dots-vertical"></i></button>
                <ul class="dropdown-menu">
                <li>
                <a class="dropdown-item" href="'.base_url('admin/books/delete/' . $book_info['id']).'" >Remove Book</a>
                </li>
                </ul></div>';
    
                $json['data'][] = [
                    'sn'				=> '<input type="checkbox" class="select-me" value="' . $book_info['id'] . '">', //$filter_data['start'] + 1 + $key,
                    'id'				=> $book_info['id'],
                    'event_id'			=> $event_id,
                    'amazon_book_id'	=> $amazon_book_id,
                    'theme'				=> $category_info['name'],
                    'custom_theme'		=> !empty($custom_theme_book) ? 'YES' : 'NO',
                    'user'				=> ($user_info['first_name'] ?? '') . ' ' . ($user_info['last_name'] ?? ''),
                    'country'			=> $user_info['location'] ?? '',
                    'name'				=> sprintf('%s ISBN: %s', $book_info['name'], $book_info['isbn']) . ($book_info['status'] == 1 ? (vsprintf('<br><a href="%s" class="btn btn-sm btn-danger">%s</a>&nbsp;<a href="%s" class="btn btn-sm btn-info">%s</a>&nbsp;<a href="%s" class="btn btn-sm btn-dark">%s</a>', [
                        base_url('admin/printBook/' . $book_info['id'] . '/' . $book_info['version']),
                        _li('PDF'),
                        base_url('admin/printKdpBook/' . $book_info['id'] . '/' . $book_info['version']),
                        _li('KDP'),
                        base_url('admin/printGreyBook/' . $book_info['id'] . '/' . $book_info['version']),
                        _li('BW'),
                    ])) : ''),
                    'isbn'				=> $book_info['isbn'],
                    'author_name'		=> $book_info['author_name'],
                    'reviewer'  		=> (isset($reviewr_name['first_name'])) ? $reviewr_name['first_name'] . ' ' . $reviewr_name['last_name'] : 'N/A',
                    'commented'  		=> $review_logs,
                    'status'			=> (($book_info['status'] == '2') ? (($review_logs['total'] > 0) ? '<i class="mdi mdi-circle" style="color: #4287f5; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="' . strip_tags($review_logs['rows'][0]['comment']) . '" data-original-title="%s"></i>' : _sd($result['status'])) : _sd($result['status'])) . (!empty($amazon_book_id) ? '<i class="mdi mdi-circle" style="color: #FFC107; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="Amazon Book" data-original-title="Amazon Book"></i>' : '') . (!empty($hall_of_fame_id) ? '<i class="mdi mdi-circle" style="color: #39AFD1; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="Hall Of Fame Book" data-original-title="Hall Of Fame Book"></i>' : ''),
                    'date_added'		=> formatDate($book_info['date_added']),
                    'date_published'	=> formatDate($book_info['date_published']),
                    'date_approved'		=> formatDate($book_info['date_approved']),
                    'date_title_verso'	=> formatDate($book_title_verso_info['date_added'] ?? ''),
                    'featured'			=> $book_info['featured'] == 0 ? _l('no') : _l('yes'),
                    'page_count' 		=> $pages,
                    'sold_book' 		=> $book_info['sold'] ?? 0,
                    'actions'           => $actions
                ];
            }
		}

		output_json($json);
	}

    

	public function add_clone_book() {
		$json = [];

		if ($book_info = $this->book_model->get($this->input->post('book_id'))) {
			$clone_book_info = $this->book_clone_model->get_all([
				'book_id' => $this->input->post('book_id')
			])['rows'] ?? [];

			if (empty($clone_book_info)) {

				$book_info['user_id'] 			= $this->session->userdata('user_id');
				$book_info['status'] 			= 0;
				$book_info['date_published'] 	= NULL;
				$book_info['date_approved'] 	= NULL;
				unset($book_info['category']);
				unset($book_info['id']);

				$book_id = $this->book_model->add($book_info);
		
				$pages = $this->page_version_model->get_all([
					'book_id' 	=> $this->input->post('book_id'),
					'version' 	=> $this->input->post('book_version') ?? 1,
					'sort'		=> 'page_version.sort_order',
					'order'		=> 'ASC'
				])['rows'] ?? [];

				log_kb([
					'book_id' 	=> $this->input->post('book_id'),
					'version' 	=> $this->input->post('book_version') ?? 1,
					'add_clone_book' => $pages
				]);

				if ($book_id) {
					$this->book_clone_model->add([
						'new_book_id' 	=> $book_id,
						'old_book_id' 	=> $this->input->post('book_id'),
						'user_id' 		=> $this->session->userdata('user_id'),
						'version' 		=> $this->input->post('book_version') ?? 1,
					]);

					foreach ($pages as $page) {
						$this->page_model->add([
							'book_id'			=> (int)$book_id,
							'theme_id'			=> (int)$page['theme_id'],
							'custom_theme_id'	=> (int)$page['custom_theme_id'],
							'texts'				=> $page['texts'],
							'sort_order'		=> (int)$page['sort_order'],
						]);
					}
				}

				$json['success'] 	= _l('book_cloned_successfully');
			} else {
				$json['error'] 		= _l('book_had_been_cloned_already');
			}
		} else {
			$json['error'] = _l('book_not_found');
		}

		output_json($json);
	}
}
