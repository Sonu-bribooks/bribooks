<?php defined('BASEPATH') or exit('No direct script access allowed');

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;

trait DeprecatedData {
	public function nyaf_images() {
		if (!$this->json) {
			$literary_leadership_award_winners = $jury_award_winners = $popular_author_award_images = $author_interviews_with_ndtv = [];

			$accepted_ext = '*.{jpg,jpeg,png,JPG,JPEG,PNG}';

			$folder = 'uploads/nyaf_2022/literary_leadership_award_winners/';
			$images = glob(FCPATH . $folder . $accepted_ext, GLOB_BRACE);
			foreach($images as $image) {
				$literary_leadership_award_winners[] = base_url($folder.str_replace(FCPATH . $folder, '', $image));
			}

			$folder = 'uploads/nyaf_2022/jury_award_winners/';
			$images = glob(FCPATH . $folder . $accepted_ext, GLOB_BRACE);
			foreach($images as $image) {
				$jury_award_winners[] = base_url($folder.str_replace(FCPATH . $folder, '', $image));
			}

			$folder = 'uploads/nyaf_2022/popular_author_award_images/';
			$images = glob(FCPATH . $folder . $accepted_ext, GLOB_BRACE);
			$rand_images = array_rand($images, 100);
			foreach($rand_images as $image) {
				$popular_author_award_images[] = base_url($folder.str_replace(FCPATH . $folder, '', $images[$image]));
			}

			$folder = 'uploads/nyaf_2022/author_interviews_with_ndtv/';
			$images = glob(FCPATH . $folder . $accepted_ext, GLOB_BRACE);
			foreach($images as $image) {
				$author_interviews_with_ndtv[] = base_url($folder.str_replace(FCPATH . $folder, '', $image));
			}

			$return = [
				'literary_leadership_award_winners'	=> [
					'title'	=>	'Literary Leadership Award Winners',
					'data'	=>	$literary_leadership_award_winners
				],
				'jury_award_winners'	=> [
					'title'	=>	'Jury Award Winners',
					'data'	=>	$jury_award_winners
				],
				'popular_author_award_images'	=> [
					'title'	=>	'Popular Author Award Winners',
					'data'	=>	$popular_author_award_images
				],
				'author_interviews_with_ndtv'	=> [
					'title'	=>	'Author Interviews with NDTV',
					'data'	=>	$author_interviews_with_ndtv
				]
			];

			$this->json['nyaf_images'] = $return;
		}
	}

	public function nyaf_images_cloud() {
		if (!$this->json) {
			$this->_bucket = 'youbooks-storage-5fd6173683748-webdev';
			$credentials = new Aws\Credentials\Credentials('', '');

			$this->_s3 = new Aws\S3\S3Client([
				'version'     	=> 'latest',
				'region'      	=> 'us-east-1',
				'credentials' 	=> $credentials,
			]);

			try {
				$directory = $this->config->item('s3_user_gallery') . 'NYAF_2023/UserGallery/nyaf_2023/';

				$result = $this->_s3->listObjectsV2([
					'Bucket' 		=> $this->_bucket,
					'Prefix' 		=> $directory,
					'Delimiter' 	=> '/',
				]);

				foreach ($result['CommonPrefixes'] ?? [] as $key => $item) {
					if(!empty($item['Prefix'])) {
						$images = [];

						$response = $this->_s3->listObjectsV2([
							'Bucket' 		=> $this->_bucket,
							'Prefix' 		=> $item['Prefix'],
							'Delimiter' 	=> '/',
						]);

						foreach ($response['Contents'] ?? [] as $key1 => $item1) {
							if(!empty($item1['Key'])) {
								if ($key1 > 0) {
									$extension = explode('.', strtolower($item1['Key']));
									if(in_array(end($extension), ['jpeg','jpg','png'])) {
										$images[] = $this->config->item('cloudfront_url') . $item['Prefix'] . basename($item1['Key']);
									}
								}
							}
						}

						$data[basename($item['Prefix'])] = $images;
					}
				}
			} catch (Exception $e) {
			}

			$return = [
				'literary_leadership_award_winners'	=> [
					'title'	=>	'Literary Leadership Award Winners',
					'path'	=>	$this->config->item('cloudfront_url') . $directory . 'literary_leadership_award_winners/',
					'data'	=>	$data['literary_leadership_award_winners'] ?? []
				],
				'jury_award_winners'	=> [
					'title'	=>	'Golden Pen Award Winners',
					'path'	=>	$this->config->item('cloudfront_url') . $directory . 'jury_award_winners/',
					'data'	=>	$data['jury_award_winners'] ?? []
				],
				'popular_author_award_images'	=> [
					'title'	=>	'Best Seller Award Winners',
					'path'	=>	$this->config->item('cloudfront_url') . $directory . 'popular_author_award_images/',
					'data'	=>	$data['popular_author_award_images'] ?? []
				],
				'author_interviews_with_ndtv'	=> [
					'title'	=>	'Author Interviews with NDTV',
					'path'	=>	$this->config->item('cloudfront_url') . $directory . 'author_interviews_with_ndtv/',
					'data'	=>	$data['author_interviews_with_ndtv'] ?? []
				]
			];

			$this->json['nyaf_images'] = $return;
		}
	}

	public function getAmazonKdpBooks() {
		if (!$this->json) {
			$key = 'amazon_kdp_books' . (ENVIRONMENT === 'production' ? '_live' : '_test');

			$cache_data = json_decode($this->cache->get($key), true);

			if (!empty($cache_data)) {
				$this->json['top_rankers'] = $cache_data;
			} else {
				$this->load->model('book/AmazonBook_model', 'amazon_book_model');

				$top_rankers = array_map(function ($item) {
					$event_book_info = $this->event_book_model->getEventBookByBookId(9, $item['book_id']);
					if(!empty($event_book_info)) {
						$book_info = $this->book_model->get($item['book_id']);

						$student_info = $this->student_model->get($book_info['user_id']);

						$site_info = $this->site_model->get($student_info['site_id']);

						$author_image = empty($book_info['author_image']) ? base_url('uploads/user_image/placeholder.png') : $this->config->item('cloudfront_url') . 'public/' . $book_info['author_image'];

						return [
							'id'			=> $item['id'],
							'book_id'		=> $book_info['id'],
							'user_id'		=> $book_info['user_id'],
							'book_name'		=> $book_info['name'],
							'author_name'	=> $book_info['author_name'],
							'site_id'		=> $site_info['id'],
							'school_name'	=> $site_info['name'],
							'book_image'	=> $this->config->item('cloudfront_url') . 'public/' . $book_info['cover_image'],
							'author_image'	=> $author_image,
							'book_url'		=> !empty($book_info['amazon_url']) ? $book_info['amazon_url'] : (USER_URL . 'bookstore/' . $book_info['slug'])
						];
					}
				}, $this->amazon_book_model->get_all()['rows'] ?? []);

				$this->json['top_rankers'] = array_values(array_filter($top_rankers));

				$this->cache->save($key, json_encode($this->json['top_rankers']), 7200);
			}
		}
	}

	public function getReferralUsers() {
		if (!$this->json) {
			if (!empty($this->session->userdata('user_id'))) {
				$user_info = $this->user_referral_list_model->get_all(['referral_id' => $this->session->userdata('user_id')]);

				$this->json['users']['user_details'] = array_map(function ($item) {
					return [
						'id'	=> $item['id'],
						'name'	=> $item['first_name'].' '.$item['last_name'],
					];
				}, $user_info['rows'] ?? []);

				$this->json['users']['total'] 	= USER_REFERRAL_LIMIT;
				$this->json['users']['pending'] = USER_REFERRAL_LIMIT - $user_info['total'];
			} else {
				$this->json['error'] = _li('Invalid user!');
			}
		}
	}

	public function getHallOfFameBooks() {
		if (!$this->json) {
			$filter_data = [];

			$filter_data = [
				'status'	=> 1,
				'start'		=> $this->input->post('page') > 0 ? ($this->input->post('page') - 1) * 16 : 1,
				'limit'		=> 16,
				'sort'		=> 'priority',
				'order'		=> 'ASC'
			];

			if ($this->input->post('page') && $this->input->post('search')) {
				$filter_data['search'] = (string)$this->input->post('search');
			}

			$key = vsprintf('%s_%s_%s_%s', [
				(ENVIRONMENT === 'production' ? 'live' : 'test'),
				'hall_of_fame_books',
				implode('_', array_keys($filter_data)),
				str_replace(' ', '', implode('_', array_values($filter_data))),
			]);

			$cache_data = json_decode($this->cache->get($key), true);

			if (!empty($cache_data)) {
				$this->json['hall_of_fame'] = $cache_data;
			} else {
				$this->load->model('halloffame/HallOfFame_model', 'hall_of_fame_model');

				$hall_of_fame_books = array_map(function ($item) {
					$student_info = $this->student_model->get($item['user_id']);

					$author_image = empty($item['author_image']) ? base_url('uploads/user_image/placeholder.png') : $this->config->item('cloudfront_url') . 'public/' . $item['author_image'];

					return [
						'book_id'		=> $item['id'],
						'user_id'		=> $item['user_id'],
						'book_name'		=> $item['name'],
						'author_name'	=> $item['author_name'],
						'book_image'	=> $this->config->item('cloudfront_url') . 'public/' . $item['cover_image'],
						'author_image'	=> $author_image,
						'book_url'		=> USER_URL . 'bookstore/' . $item['slug'],
						'book_sold'		=> $item['sold'] ?? 0,
						'priority'		=> $item['priority'] ?? 0,
						'country'		=> !empty($item['location']) ? $item['location'] : '',
						'country_code'	=> !empty($item['country_code']) ? strtolower($item['country_code']) : ''
					];
				}, $this->hall_of_fame_model->get_all($filter_data)['rows'] ?? []);

				$this->json['hall_of_fame'] = array_values(array_filter($hall_of_fame_books));

				$this->cache->save($key, json_encode($this->json['hall_of_fame']), 600);
			}
		}
	}

	public function siteImages() {
		if (!$this->json) {
			$this->_bucket = 'youbooks-storage-5fd6173683748-webdev';
			$credentials = new Aws\Credentials\Credentials('', '');

			$this->_s3 = new Aws\S3\S3Client([
				'version'     	=> 'latest',
				'region'      	=> 'us-east-1',
				'credentials' 	=> $credentials,
			]);

			try {
				$directory = $this->config->item('s3_user_gallery') . 'site_images/nyaf2024/';

				$result = $this->_s3->listObjectsV2([
					'Bucket' 		=> $this->_bucket,
					'Prefix' 		=> $directory,
					'Delimiter' 	=> '/',
				]);

				foreach ($result['CommonPrefixes'] ?? [] as $key => $item) {
					if(!empty($item['Prefix'])) {
						$images = [];

						$response = $this->_s3->listObjectsV2([
							'Bucket' 		=> $this->_bucket,
							'Prefix' 		=> $item['Prefix'],
							'Delimiter' 	=> '/',
						]);

						foreach ($response['Contents'] ?? [] as $key1 => $item1) {
							if(!empty($item1['Key'])) {
								if ($key1 > 0) {
									$extension = explode('.', strtolower($item1['Key']));
									if(in_array(end($extension), ['jpeg','jpg','png'])) {
										$images[] = $this->config->item('cloudfront_url') . $item['Prefix'] . basename($item1['Key']);
									}
								}
							}
						}

						$data[basename($item['Prefix'])] = $images;
					}
				}
			} catch (Exception $e) {
			}

			$return = [
				'nyaf2022'	=> [
					'title'	=>	'Site Image',
					'path'	=>	$this->config->item('cloudfront_url') . $directory . 'legacy_school22/',
					'data'	=>	$data['legacy_school22'] ?? []
				],
				'nyaf2023'	=> [
					'title'	=>	'Site Image',
					'path'	=>	$this->config->item('cloudfront_url') . $directory . 'legacy_school23/',
					'data'	=>	$data['legacy_school23'] ?? []
				]
			];

			$this->json['site_images'] = $return;
		}
	}
}
