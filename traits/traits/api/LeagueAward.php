<?php defined('BASEPATH') or exit('No direct script access allowed');

trait LeagueAward {
    public function getLeagueAward() {
        $type = !empty($this->input->post('type')) ? strtolower($this->input->post('type')) : 'general';
        $validationMethod = sprintf('event_challenge_%s_slug', $type);

        $this->form_validation->set_rules('slug', _l('slug'), [
			'trim',
			'required',
			'min_length[10]',
			'max_length[255]',
			['slug', [$this->validate_model, $validationMethod]]
		]);

        $this->form_validation->set_rules('bid', _l('book_id'), 'trim|required|numeric');

        self::_runFormValidation();

        if (!$this->json) {
            $model_name     = sprintf('event_challenge_%s_model', $type);
            $function_name  = sprintf('getUser%sRank', ucfirst($type));
			$challenge_info = $this->{$model_name}->getBySlug($this->input->post('slug'));

            if (empty($challenge_info)) {
                $this->json['error'] = _l('league_not_found');
				return;
            }

           $this->load->library('Ranking_lib', 'ranking_lib');
           $this->load->model('event/LeagueAward_model', 'league_award_model');

           $book_id   = (int)$this->input->post('bid');
           $user_id   = $this->input->post('uid') ? (int)$this->input->post('uid') : (int)$this->session->userdata('user_id');
				
        //    $user_rank = $this->ranking_lib->{$function_name}($challenge_info['event_id'], $challenge_info['id'], 0, $user_id, $book_id);

            $award_info = $this->league_award_model->get_all([
                'event_id'      => $challenge_info['event_id'],
                'challenge_id'  => $challenge_info['id'],
                'sort'          => 'league_award.display_order',
                'order'         => 'DESC'
            ])['rows'] ?? [];

            $user_rank_number = (int)($user_rank['rank'] ?? 6);
            $user_sold_count  = (int)($user_rank['score'] ?? 2);
            $award_data = [];
            foreach ($award_info as $award) {
                $earned = false;
                $message = '';
                
                if ($award['award_type'] == 'book_sold') {

                    $required_sold = (int)$award['sold_count'];

                    if ($user_sold_count >= $required_sold) {
                        $earned = true;
                        $message = _l(sprintf('Congratulations! You have earned %s', $award['rank']));
                    } else {
                        $remaining = $required_sold - $user_sold_count;
                        $message = _l(sprintf('just %d more %s to reach the %s', $remaining, $remaining == 1 ? 'copy' : 'copies', $award['rank']));
                    }

                } elseif ($award['award_type'] == 'book_rank') {

                    $min_rank   = (int)$award['min_rank'];
                    $max_rank   = (int)$award['max_rank'];

                    if ($user_rank_number > 0 && $user_rank_number <= $max_rank) {
                        $earned     = true;
                        $message    = _l(sprintf('Congratulations! You have earned %s', $award['rank']));
                    } else {
                        
                        $target_info = $this->league_award_model->getRankByPosition($challenge_info['event_id'], $challenge_info['id'], $min_rank, 'user_rank_general');
                        if (!empty($target_info)) {

                            $target_score = (int)$target_info['score'];
                            $remaining = max(0, $target_score - $user_sold_count);

                            if ($remaining > 0) {

                                $message = _l(sprintf('Just %d more %s to reach the %s', $remaining, $remaining == 1 ? 'copy' : 'copies', $award['rank']));

                            } else {
                                $message = 'Your current rank is ' . $user_rank_number;
                            }

                        } else {

                            $message = _l(sprintf('Your current rank is %d. This award requires rank between %d and %d.', $user_rank_number, $min_rank, $max_rank));
                        }
                    }
                }

                $award_data[] = [
                    'id'            => $award['id'],
                    'event_id'      => $award['event_id'],
                    'challenge_id'  => $award['challenge_id'],
                    'type'          => $award['type'],
                    'award_type'    => $award['award_type'],
                    'rank'          => $award['rank'],
                    'min_rank'      => $award['min_rank'],
                    'max_rank'      => $award['max_rank'],
                    'award'         => $award['award'],
                    'image'         => $award['image'],
                    'sold_count'    => $award['sold_count'],
                    'display_order' => $award['display_order'],
                    'status'        => $award['status'],
                    'earned'        => $earned,
                    'message'       => $message
                ];
            }

            // $this->json['user_rank'] = $user_rank;
            $this->json['award'] = $award_data;

        }
	}
}