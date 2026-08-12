<?php defined('BASEPATH') OR exit('No direct script access allowed');

class BookAiReview_model extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	public function get($book_ai_review_id = 0) {
		$this->db->select('book_ai_review.*');

		$this->db->where('book_ai_review.id', (int)$book_ai_review_id);
		$this->db->where('book_ai_review._deleted', 0);

		return $this->db->get('book_ai_review')->row_array();
	}

	public function get_all($data = []) {
		$this->db->select('book_ai_review.*');

		if (isset($data['event_id'])) {
			$this->db->where('book_ai_review.event_id', (int)$data['event_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('book_ai_review.book_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('book_ai_review.version', (int)$data['version']);
		}

		if (isset($data['name'])) {
			$this->db->where('book_ai_review.name', $data['name']);
		}

		if (isset($data['author'])) {
			$this->db->where('book_ai_review.author', $data['author']);
		}

		if (isset($data['slug'])) {
			$this->db->where('book_ai_review.slug', $data['slug']);
		}

		if (isset($data['score'])) {
			$this->db->where('book_ai_review.score', (double)$data['score']);
		}

		if (!empty($data['score_le'])) {
			$this->db->having('score <= ', (int)$data['score_le']);
		}

		if (!empty($data['score_ge'])) {
			$this->db->having('score >= ', (int)$data['score_ge']);
		}

		if (isset($data['creativity_originality'])) {
			$this->db->where('book_ai_review.creativity_originality', (int)$data['creativity_originality']);
		}

		if (isset($data['character_development_depth'])) {
			$this->db->where('book_ai_review.character_development_depth', (int)$data['character_development_depth']);
		}

		if (isset($data['plot_storytelling'])) {
			$this->db->where('book_ai_review.plot_storytelling', (int)$data['plot_storytelling']);
		}

		if (isset($data['grammatical_errors'])) {
			$this->db->where('book_ai_review.grammatical_errors', (int)$data['grammatical_errors']);
		}

		if (isset($data['imaginative_use_of_language'])) {
			$this->db->where('book_ai_review.imaginative_use_of_language', (int)$data['imaginative_use_of_language']);
		}

		if (isset($data['theme_message'])) {
			$this->db->where('book_ai_review.theme_message', (int)$data['theme_message']);
		}

		if (isset($data['overall_impact'])) {
			$this->db->where('book_ai_review.overall_impact', (int)$data['overall_impact']);
		}

		if (!empty($data['challenge_id']) && !empty($data['challenge_type'])) {
			$this->db->where(sprintf(
				'book_ai_review.book_id IN (select book_id from event_challenge_%s WHERE _deleted=0 AND id = %s)',
				$data['challenge_type'],
				$data['challenge_id']
			));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book_ai_review.name', $data['search'], 'after');
			$this->db->or_like('book_ai_review.author', $data['search'], 'after');
			$this->db->or_like('book_ai_review.slug', $data['search'], 'after');
			$this->db->or_like('book_ai_review.book_id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('book_ai_review._deleted', 0);

		$this->db->from('book_ai_review');

		$total = $this->db->count_all_results('', FALSE);

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		} else {
			$this->db->limit(10, 0);
		}

		$sort_data = [
			'book_ai_review.id',
			'book_ai_review.name',
			'book_ai_review.author',
			'book_ai_review.date_added',
			'book_ai_review.date_modified',
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'book_ai_review.id';
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$this->db->order_by($sort, $order);

		$results = $this->db->get()->result_array();

		return ['rows' => $results, 'total' => $total];
	}

	public function add($data = []) {
		$this->db->insert('book_ai_review', $data + [
			'date_added'	=> date('Y-m-d H:i:s'),
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$book_ai_review_id = $this->db->insert_id();

		$this->session->set_flashdata('flash_message', _l('event_added_successfully'));

		return $book_ai_review_id;
	}

	public function edit($book_ai_review_id = 0, $data = []) {
		$this->db->where('id', (int)$book_ai_review_id);
		$this->db->update('book_ai_review', $data + [
			'date_modified'	=> date('Y-m-d H:i:s'),
		]);

		$this->session->set_flashdata('flash_message', _l('event_update_successfully'));
	}

	public function delete($book_ai_review_id = 0) {
		$this->db->where('id', (int)$book_ai_review_id);
		$this->db->update('book_ai_review',  [
			'_deleted'		=> 1,
			'date_deleted'	=> date('Y-m-d H:i:s'),
		]);
	}
}
