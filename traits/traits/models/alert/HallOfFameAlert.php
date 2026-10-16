<?php defined('BASEPATH') or exit('No direct script access allowed');

trait HallOfFameAlert {
    public function enrolBookToHallOfFameAlert($id = 0, $type = 'global') {
        $code = sprintf('enrol%sBookToHallOfFameCron_%s', ucwords($type), (int)$id);

        $update_data = [
            'code'      => $code,
            'site_id'   => 1,
            'action'    => 'alert_model->enrol'.ucwords($type).'BookToHallOfFameCron',
            'data'      => [$id],
            'status'     => 0,
            'alert_date' => date('Y-m-d H:i:00', strtotime('+1 minutes')),
        ];

        if (!empty($cron_info = $this->cron_model->getByCode($code))) {
            $this->cron_model->edit($cron_info['id'], $update_data);
        } else {
            $this->cron_model->add($update_data);
        }
	}

    public function enrolGlobalBookToHallOfFameCron($id = 0) {
		$this->load->model('halloffame/HallOfFameCountry_model', 'hall_of_fame_country_model');
        $this->load->model('localisation/Country_model', 'country_model');
        $this->load->library('HallOfFame_lib');

        $global_fame_info = $this->hall_of_fame_country_model->get((int)$id);

        if (empty($global_fame_info)) {
            log_kb([
                'enrolGlobalBookToHallOfFameCron' => 'Invalid configuration ID: ' . $id
            ]);
            return;
        }

        $country_code   = $global_fame_info['country_code'];
        $event_id       = (int) ($global_fame_info['event_id'] ?? 0);
        $min_book_sold  = (int) ($global_fame_info['book_sold'] ?? 0);
        $country_info   = $this->country_model->getByCode($country_code);

        // Event configuration: event_order se book IDs.
        if ($event_id > 0) {
            $results = self::_getSoldEventBooks($event_id, $min_book_sold);
            log_kb(['eventBooksResult::' => $results]);
        } else {
            $results = self::_getSoldCountryBook($min_book_sold, $country_info, 'global');
            log_kb(['countryBooksResult::' => $results]);
        }

        $eligible_book_ids = array_column($results, 'book_id');
        self::_cleanupHallOfFameBooks($country_code, $event_id, $eligible_book_ids, 'global');

        $processed = 0;

        foreach ($results as $result) {
           $this->halloffame_lib->addBookToHallOfFame((int) $result['book_id'], 'global');
            $processed++;
        }

        log_kb([
            'enrolGlobalBookToHallOfFameCron' => [
                'config_id' => $id,
                'country_code' => $country_code,
                'event_id' => $event_id,
                'books_found' => count($results),
                'books_processed' => $processed
            ]
        ]);
    }

    public function enrolAsiaBookToHallOfFameCron($id = 0) {
		$this->load->model('halloffame/HallOfFameAsiaCountry_model', 'hall_of_fame_asia_country_model');
        $this->load->model('localisation/Country_model', 'country_model');
        $this->load->library('HallOfFame_lib');

        $asia_fame_info = $this->hall_of_fame_asia_country_model->get((int)$id);

        if (empty($asia_fame_info)) {
            log_kb([
                'enrolAsiaBookToHallOfFameCron' => 'Invalid configuration ID: ' . $id
            ]);
            return;
        }

        $country_code   = $asia_fame_info['country_code'];
        $event_id       = (int) ($asia_fame_info['event_id'] ?? 0);
        $min_book_sold  = (int) ($asia_fame_info['book_sold'] ?? 0);
        $country_info   = $this->country_model->getByCode($country_code);

        // Event configuration: event_order se book IDs.
        if ($event_id > 0) {
            $results = self::_getSoldEventBooks($event_id, $min_book_sold);
            log_kb(['asiaEventBooksResult::' => $results]);
        } else {
            $results = self::_getSoldCountryBook($min_book_sold, $country_info, 'asia');
            log_kb(['asiaCountryBooksResult::' => $results]);
        }

        $eligible_book_ids = array_column($results, 'book_id');
        self::_cleanupHallOfFameBooks($country_code, $event_id, $eligible_book_ids, 'asia');

        $processed = 0;

        foreach ($results as $result) {
           $this->halloffame_lib->addBookToHallOfFame((int) $result['book_id'], 'asia');
            $processed++;
        }

        log_kb([
            'enrolAsiaBookToHallOfFameCron' => [
                'config_id' => $id,
                'country_code' => $country_code,
                'event_id' => $event_id,
                'books_found' => count($results),
                'books_processed' => $processed
            ]
        ]);
    }

    private function _getSoldEventBooks($event_id, $min_book_sold = 1) {
        return $this->db
        ->select('
            event_order.event_id,
            event_order.book_id,
            SUM(event_order.quantity) AS sold_count
        ')
        ->from('event_order')
        ->join('order', 'order.id = event_order.order_id')
        ->join('book', 'book.id = event_order.book_id')
        ->join('users', 'users.id = book.user_id')
        ->where('event_order._deleted', 0)
        ->where('event_order.event_id', (int) $event_id)
        ->where('order._deleted', 0)
        ->where_not_in('order.status', [0, 91, 92])
        ->where('book._deleted', 0)
        ->where('book.archived', 0)
        ->where('book.status', 1)
        ->where('users._deleted', 0)
        ->group_by('event_order.book_id')
        ->having('SUM(event_order.quantity) >=', (int) $min_book_sold)
        ->get()
        ->result_array();
    }

    private function _getSoldCountryBook($min_book_sold, $country_info = [], $type = 'global') {

        if (empty($country_info['name'])) {
            return [];
        }

        $hof_table = ($type === 'global') ? 'hall_of_fame_country' : 'hall_of_fame_asia_country';

        return $this->db
        ->select('
            order_product.product_id AS book_id,
            SUM(order_product.quantity) AS sold_count
        ')
        ->from('order_product')
        ->join('order', 'order.id = order_product.order_id')
        ->join('book', 'book.id = order_product.product_id')
        ->join('users', 'users.id = book.user_id')
        ->where('order_product._deleted', 0)
        ->where('order._deleted', 0)
        ->where_not_in('order.status', [0, 91, 92])
        ->where('book._deleted', 0)
        ->where('book.archived', 0)
        ->where('book.status', 1)
        ->where('users._deleted', 0)
        ->where('users.location', $country_info['name'])
        ->where("
            NOT EXISTS (
                SELECT 1
                FROM event_book eb
                JOIN {$hof_table} hfc
                    ON hfc.event_id = eb.event_id
                WHERE hfc.country_code = " . $this->db->escape($country_info['code']) . "
                AND hfc.event_id > 0
                AND hfc._deleted = 0
                AND eb._deleted = 0
                AND eb.book_id = order_product.product_id
            )
        ", null, false)
        ->group_by('order_product.product_id')
        ->having('SUM(order_product.quantity) >=', (int) $min_book_sold)
        ->get()
        ->result_array();
    }

    private function _cleanupHallOfFameBooks($country_code, $event_id, $eligible_book_ids = [], $type = 'global') {
        $eligible_book_ids = array_map('intval', $eligible_book_ids);

        if ($type === 'global') {
            $hof_books_table   = 'hall_of_fame_books';
            $hof_country_table = 'hall_of_fame_country';
        } else {
            $hof_books_table   = 'hall_of_fame_asia_books';
            $hof_country_table = 'hall_of_fame_asia_country';
        }
        
        $this->db
            ->select('hf.id, hf.book_id')
            ->from("{$hof_books_table} hf")
            ->where('hf.country_code', $country_code)
            ->where('hf._deleted', 0);

        if ((int) $event_id > 0) {
            // Event fame: sirf current event ki entries.
            $this->db->where('hf.event_id', (int) $event_id);
        } else {
            // Country fame: separately configured events ko exclude karein.
            $country_code_escaped = $this->db->escape($country_code);

            $this->db->where("
                NOT EXISTS (
                    SELECT 1
                    FROM event_book eb
                    JOIN {$hof_country_table} hfc
                        ON hfc.event_id = eb.event_id
                    WHERE hfc.country_code = {$country_code_escaped}
                        AND hfc.event_id > 0
                        AND hfc._deleted = 0
                        AND eb._deleted = 0
                        AND eb.book_id = hf.book_id
                )
            ", null, false);
        }

        $existing_entries = $this->db->get()->result_array();

        foreach ($existing_entries as $entry) {
            if (!in_array((int) $entry['book_id'], $eligible_book_ids, true)) {
                $this->db
                    ->where('id', (int) $entry['id'])
                    ->where('_deleted', 0)
                    ->update($hof_books_table, [
                        '_deleted'              => 1,
                        'date_deleted'		    => date('Y-m-d H:i:s'),
			            'deleted_manager_id'    => $this->session->userdata('user_id') ?? 0
                    ]);
            }
        }
    }
}