<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Dompdf\Dompdf;

trait PodcastInvite {
	public function getPodcastSlot(){
        $this->form_validation->set_rules('slug', _l('slug'), 'trim|required');

		self::_runFormValidation();

		if (!$this->json) {
            $this->load->model('api/PodcastTimeSlots_model', 'podcast_time_slots_model');
            $this->load->model('api/BookPodcast_model', 'book_podcast_model');

            if (!empty($book_info = $this->book_model->getBySlug($this->input->post('slug')))) {
                if (!empty($this->book_podcast_model->get_all([
                    'book_id' => $book_info['id']
                ])['rows'][0] ?? '')) {
                    return $this->json['error'] 	= _l('you_are_already_added_your_slot');
                }

                $slots = $this->podcast_time_slots_model->get_all([
                    'order'     => 'ASC',
                    'occupied'  => 1
                ])['rows'] ?? [];

                $slots_data = [];

                foreach ($slots as $slot) {
                    $slots_data[$slot['date']][] = [
                        'slot_id'   => $slot['id'],
                        'slot'      => $slot['slot']
                    ];
                }

                $this->json['success'] 	= _l('success');
			    $this->json['book'] 	= $book_info;
			    $this->json['slots'] 	= $slots_data;
            } else {
			    $this->json['error'] 	= _l('invalid_url');
            }
        }
	}

	public function addPodcastSlot() {
		$this->form_validation->set_rules('book_id', _l('Book Id'), 'trim|required|numeric');
		$this->form_validation->set_rules('slot_id', _l('Slot'), 'trim|required|numeric');

		self::_runFormValidation();

		if (!$this->json) {
            $this->load->model('api/BookPodcast_model', 'book_podcast_model');
            $this->load->model('api/PodcastTimeSlots_model', 'podcast_time_slots_model');

            $slot_info = $this->podcast_time_slots_model->get($this->input->post('slot_id'));

            $this->book_podcast_model->add([
                'event_id' => $slot_info['event_id'] ?? 0,
                'book_id'  => $this->input->post('book_id'),
                'slot_id'  => $this->input->post('slot_id'),
            ]);

			$this->json['success'] = _li('Slot added successfully');
		}
	}
}
