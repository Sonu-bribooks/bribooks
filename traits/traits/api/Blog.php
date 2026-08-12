<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Blog {
	public function getBlogBySlug() {
		$this->form_validation->set_rules('slug', _l('slug'), 'trim|required|min_length[3]|max_length[255]');
		self::_runFormValidation();

		if (!$this->json) {
			if ($blog_info = $this->blog_model->getBySlug($this->input->post('slug'))) {
				$blog_info['image'] = $this->config->item('s3_user_gallery') . $blog_info['image'];
				$blog_info['description'] = html_entity_decode($blog_info['description'] ?? '');
				$this->json['blog'] = $blog_info;
			} else {
				$this->json['error'] = _l('blog_not_found');
			}
		}
	}

	public function getBlogs() {
		$this->form_validation->set_rules('page', _l('page'), 'trim|numeric');
		self::_runFormValidation();

		if (!$this->json) {
			$filter_data = [];

			if ($this->input->post('page')) {
				$filter_data = [
					'status'	=> 1,
					'start'		=> $this->input->post('page') > 0
						? ($this->input->post('page') - 1) * 16
						: 0,
					'limit'		=> 16,
					'sort'		=> 'blog.site_id',
					'order'		=> 'DESC',
				];

				if ($this->input->post('search')) {
					$filter_data['search'] = $this->input->post('search');
				}
			} else {
				$filter_data = [
					'status'	=> 1,
					'sort'		=> 'blog.site_id',
					'order'		=> 'DESC',
				];
			}

			$result = $this->blog_model->get_all($filter_data);

			$sort_order = [];

			$this->json['blogs'] = array_map(function($item) use(&$sort_order) {
				$sort_order[] = $item['sort_order'];

				$item['image'] = $this->config->item('s3_user_gallery') . $item['image'];
				$item['description'] = html_entity_decode($item['description'] ?? '');

				return $item;
			}, $result['rows'] ?? []);

			array_multisort($sort_order, SORT_DESC, $this->json['blogs'], SORT_ASC);
			$this->json['total'] = $result['total'] ?? 0;
		}
	}

	public function getTotalBlogs() {
		if (!$this->json) {
			$result = $this->blog_model->get_all([
				'status'	=> 1,
			]);

			$this->json['total'] = $result['total'] ?? 0;
		}
	}
}
