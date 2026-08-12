<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait ShareTemplate {
	public function getEventShareTemplate() {
		$this->form_validation->set_rules('slug', _l('slug'), 'trim|min_length[3]|max_length[255]');
		$this->form_validation->set_rules('uid', _l('uid'), 'trim|numeric');

		$this->form_validation->set_rules('event_id', _l('event_id'), [
			'trim',
			'numeric',
		]);

		self::_runFormValidation();

		if (!$this->json) {
			if (empty($this->input->post('slug')) && empty($this->input->post('uid'))) {
				return  $this->json['error'] = _l('all_field_are_required');
			}

			$event_id = $this->input->post('event_id') ?? 0;

			$filter_data = ['event_id' => $event_id];

			if (!empty($this->input->post('type'))) {
				$filter_data['type'] = (int)$this->input->post('type');
			} else {
				$filter_data['type'] = 0;
			}

			if (empty($template_info = $this->share_template_model->get_all($filter_data)['rows'][0] ?? [])) {
				$template_info = $this->share_template_model->get_all(['event_id' => 0])['rows'][0] ?? [];
			}

			if (empty($template_info)) {
				return  $this->json['error'] = _l('template_not_found');
			}

			$author_name 	= '';
			$book_name 		= '';
			$book_url 		= '';
			$state_name 	= 'State';
			$city_name 		= 'City';
			$school_name 	= 'School';
			$referral_url 	= '';
			$league_name 	= 'school';
			$league_info 	= [];
			$author_info 	= [];
			$coupon 		= '';

			if (!empty($this->input->post('slug'))) {
				if (!empty($book_info = $this->book_model->getBySlug($this->input->post('slug')))) {
					if (!empty($book_info['user_id'])) {
						$author_info = $this->user_model->get($book_info['user_id']);
					}

					$book_name 		= $book_info['name'];
					$book_url 		= USER_URL . 'bookstore/' . $book_info['slug'];
					$league_info 	= checkBookLeague($book_info['id'], $this->input->post('event_id'));
				} else {
					return $this->json['error'] = _l('book_not_found');
				}

				$coupon_info = $this->coupon_model->get_all([
					'item_id' 		=> $book_info['id'],
					'end_date_ge' 	=> $template_info['date_added'],
				])['rows'][0] ?? [];

				$coupon = $coupon_info['code'] ?? '';
			} elseif (!empty($this->input->post('uid'))) {
				$author_info = $this->user_model->get($this->input->post('uid'));

				if (empty($author_info)) {
					return  $this->json['error'] = _l('user_not_found');
				}
			}

			if (!empty($author_info)) {
				$author_name = $book_info['author_name'] ?? $author_info['first_name'];

				$referral_url = vsprintf(USER_YAF_URL . 'india/referral/signup/%s?uid=%s&code=%s', [
					$author_info['first_name'],
					$author_info['id'],
					$author_info['verification_code']
				]);

				if (!empty($author_info['site_id']) && !empty($site_info = $this->site_model->get($author_info['site_id']))) {
					$school_name= $site_info['name'] ;
				}

				if (!empty($state_info = $this->state_model->get($author_info['state_id']))) {
					$state_name	= $state_info['name'] ;
				}

				if (!empty($city_info = $this->city_model->get($author_info['city_id']))) {
					$city_name	= $city_info['name'] ;
				}
			}

			if  (!empty($league_info)) {
				switch ($league_info['type']) {
					case 'city':
						$league_name = $league_info['city'];
					break;
					case 'state':
						$league_name = $league_info['state'];
					break;
					case 'country':
						$league_name = $league_info['country'];
					break;
					default:
						$league_name = $league_info['school'];
				}
			}

			$html = str_replace(
				[
					'{author_name}',
					'{book_name}',
					'{book_url}',
					'{state_name}',
					'{city_name}',
					'{school_name}',
					'{referral_url}',
					'{league_name}',
					'{coupon}',
					'</p>',
					'<br>'
				],
				[
					$author_name ,
					$book_name ,
					$book_url ,
					$state_name ,
					$city_name ,
					$school_name ,
					$referral_url ,
					$league_name ,
					$coupon ,
					'</p>\r\n',
					''
				],
				$template_info['message']
			);

			$this->json['share_template'] = [
				'event_id'  => $template_info['event_id'],
				'type'	  	=> $template_info['type'],
				'message'   => $html,
			];

			$this->json['book'] = [
				'author_name'   => $author_name,
				'book_name'	 	=> $book_name,
				'book_url'	  	=> $book_url,
				'state_name'	=> $state_name,
				'city_name'	 	=> $city_name,
				'school_name'   => $school_name,
				'referral_url'  => $referral_url,
				'league_name'  	=> $league_name,
				'coupon'  		=> $coupon,
				'league_type'  	=> $league_info['type'] ?? '',
				'sold'  		=> $league_info['sold'] ?? 0,
			];
		}
	}
}
