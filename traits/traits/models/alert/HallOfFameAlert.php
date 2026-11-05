<?php defined('BASEPATH') or exit('No direct script access allowed');

trait HallOfFameAlert {
    public function enrolBookToHallOfFameAlert($id = 0, $type = 'global') {
        $code = sprintf('enrol%sBookToHallOfFameCron_%s', ucwords($type), (int)$id);

        $update_data = [
            'code'      => $code,
            'site_id'   => 1,
            'action'    => 'alert_model->enrolBookToHallOfFameCron',
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

    public function enrolBookToHallOfFameCron($id = 0) {
		$this->load->model('halloffame/HallOfFameCountry_model', 'hall_of_fame_country_model');
        $this->load->model('event/EventOrder_model', 'event_order_model');
        $this->load->model('order/OrderProduct_model', 'order_product_model');
        $this->load->model('localisation/Country_model', 'country_model');
        $this->load->library('HallOfFame_lib');

        $hall_fame_info = $this->hall_of_fame_country_model->get((int)$id);

        if (empty($hall_fame_info)) {
            log_kb([
                'enrolBookToHallOfFameCron' => 'Invalid configuration ID: ' . $id
            ]);
            return;
        }

        $country_code   = $hall_fame_info['country_code'];
        $event_id       = (int)($hall_fame_info['event_id'] ?? 0);
        $min_book_sold  = (int)($hall_fame_info['book_sold'] ?? 0);
        $type           = $hall_fame_info['type'] ?? 'global';
        $country_info   = $this->country_model->getByCode($country_code);

        if ($event_id > 0) {
            $results = $this->event_order_model->getSoldByBook([
                    'event_id'     => $event_id,
                    'quantity_ge'  => $min_book_sold
                ])['rows'] ?? [];

            log_kb(['eventBooksResult::' => $results]);
        } else {
            $result = $this->order_product_model->getOrderProductQuantityByCountry([
                'country_name'              => $country_info['name'],
                'country_code'              => $country_info['code'],
                'type'                      => $type,
                'quantity_ge'               => $min_book_sold,
                'exclude_hfc_event_books'   => true
            ]);

            log_kb(['countryBooksResult::' => $results]);
        }

        $eligible_book_ids = array_column($results, 'book_id');
        self::_cleanupHallOfFameBooks($country_code, $event_id, $eligible_book_ids, $type);

        $processed = 0;

        foreach ($results as $result) {
           $this->halloffame_lib->addBookToHallOfFame((int) $result['book_id'], $type);
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

    private function _cleanupHallOfFameBooks($country_code, $event_id, $eligible_book_ids = [], $type = 'global') {
        $existing_entries = $this->hall_of_fame_model->getExistingHallOfFameBooks([
                'country_code' => $country_code,
                'event_id'     => $event_id,
                'type'         => $type
            ]);

        $existing_book_ids = array_column($existing_entries, 'book_id');

        $delete_book_ids = array_diff(
            array_map('intval', $existing_book_ids),
            array_map('intval', $eligible_book_ids)
        );

        if (!empty($delete_book_ids)) {
            $this->hall_of_fame_model->BulkdeleteByBookId($delete_book_ids, $type);
        }
    }
}