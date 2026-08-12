<?php defined('BASEPATH') or exit('No direct script access allowed');

trait Script
{
    public function nyaf_not_started_writing()
    {
        // echo "<pre>";
        $this->load->model("Student_model", "student_model");
        $users = $this->student_model->nyaf_users_data([
            'source' => 'NYAFIND2022'
        ]);

        foreach ($users as $key => $value) {
            $data[] = [
                'Author Name' => $value['name'],
                'Email' => $value['email'],
                'Phone number' => $value['mobile'],
            ];
        }

        $filename = 'not_writing_authors_' . date('Y_m_d_h_i_s') . '.csv';
        self::array_to_csv_download($data, $filename);
    }

    public function nyaf_writing_but_not_published()
    {
        $this->load->model("Student_model", "student_model");
        $users = $this->student_model->nyaf_users_data([
            'source' => 'NYAFIND2022'
        ], true);

        foreach ($users as $key => $value) {
            $data[] = [
                'Author Name' => $value['name'],
                'Email' => $value['email'],
                'Phone number' => $value['mobile'],
            ];
        }


        $filename = 'not_published_author' . date('Y_m_d_h_i_s') . '.csv';
        self::array_to_csv_download($data, $filename);
    }

    public function nyaf_published_but_not_sold()
    {
        $this->load->model("Student_model", "student_model");
        $users = $this->student_model->nyaf_book_not_sold_data([
            'source' => 'NYAFIND2022'
        ]);

        foreach ($users as $key => $value) {
            $data[] = [
                'Author Name' => $value['name'],
                'Email' => $value['email'],
                'Phone number' => $value['mobile'],
                'Book Name' => $value['book_name'],
                'Book Url' => $value['url'],
            ];
        }

        $filename = 'nyaf_published_but_not_sold' . date('Y_m_d_h_i_s') . '.csv';
        self::array_to_csv_download($data, $filename);
    }

    function array_to_csv_download($array, $filename = "", $delimiter = ",")
    {
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        $f = fopen('php://output', 'w');
        fputcsv($f, array_keys($array[0]));
        foreach ($array as $line) {
            fputcsv($f, $line, $delimiter);
        }
    }

    public function siteUpgrade()
    {
        return;
        $this->load->model("Site_model", "site_model");
        $this->load->model('common/Grade_model', 'grade_model');
        $this->load->model('common/SiteSection_model', 'sitesection_model');

        $sites = $this->site_model->get_all([
            'parent_id' => 2273,
            'status'    => 1
        ])['rows'] ?? [];

        // pr($sites, 1);

        $section_input = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
        $grade_input = "1,2,3,4,5,6,7,8,9,10,11,12";

        foreach ($sites as $key => $value) {
            $section = explode(",", $section_input);
            $grade = explode(",", $grade_input);

            foreach ($grade as $key => $gradeVal) {
                $grade_id = $this->grade_model->add([
                    'site_id'   => $value['id'],
                    'name'      => $gradeVal
                ]);
                for ($i = 0; $i < count($section); $i++) {
                    $this->sitesection_model->add([
                        'grade_id'  => $grade_id,
                        'name'      => strtoupper($section[$i]),
                    ]);
                }
            }
        }

        pr(count($sites), 1);
    }

    public function reassign()
    {
		return;
        $this->load->model('printer/PrinterAssignLog_model', 'printer_assign_log_model');
        $this->load->model('printer/PrinterStats_model', 'printer_stats_model');
        $this->load->model('order/OrderHistory_model', 'order_history_model');
        $lists  = $this->printer_stats_model->orderAll([
            "assign_printer_id" => '0'
        ]);
        foreach ($lists['rows'] as $key => $value) {
            // $this->order_model->edit($value['id'], [
            //     'assign_printer_id' => $value['assign_printer_id'],
            //     'status' => 2,
            // ]);
            $products = $this->order_model->getProducts($value["id"]);
            foreach ($products as $product) {
                $this->printer_assign_log_model->add([
                    'order_id'         => $value['id'],
                    'version'         => $product['version'],
                    'product_id'     => $product['product_id'],
                    'option'         => $product['option'],
                    'quantity'         => $product['quantity'],
                    'printer_id'    => $value['assign_printer_id'],
                    'manager_id'     => $this->session->userdata('user_id')
                ]);
            }
            $this->order_history_model->add([
                'order_id' => $value['id'],
                'description' => _order_status(0),
                'status' => 2
            ]);
        }
    }

    public function missed_order_case_1_all_0_no_change()
    {
        return;

        $results = $this->db->query('SELECT order_id, book_id, version, count(id) as total, sum(status) as total_status
FROM `book_stock_history`
where order_id in (select id from `order` where status in (1,2,8)) and _deleted = 0
group by order_id, book_id, version
having count(id) > 1 and total_status = 0
order by total desc')->result_array();

        pr($results, 1);

        $this->load->model('book/BookStock_model', 'book_stock_model');
        $this->load->model('book/BookStockHistory_model', 'book_stock_history_model');

        foreach ($results as $result) {
            $order_id = $result['order_id'];
            $book_id = $result['book_id'];
            $version = $result['version'];
            $option = $result['option'];

            $book_stock_history_results = $this->db->query("SELECT * FROM `book_stock_history`
            where order_id=$order_id AND book_id=$book_id AND version=$version AND _deleted = 0")->result_array();

            foreach ($book_stock_history_results as $bs_key => $book_stock_history_result) {
                if(!empty($bs_key)) {
                    // pr($book_stock_history_result);

                    // $this->book_stock_history_model->delete($book_stock_history_result['id']);

                    if ($stock_info = $this->book_stock_model->get_all([
                        'book_id'   => $book_id,
                        'version'   => $version,
                        'option'    => $option
                    ])['rows'][0] ?? []) {
                        // pr($stock_info);

                        $qty = (int)$stock_info['quantity'] + (int)$book_stock_history_result['quantity_order'];
                        // pr($qty);

                        /*$this->book_stock_model->edit($stock_info['id'], [
                            'quantity'  => $qty ?? 0,
                        ]);*/
                    }
                }
            }

            // pr($book_stock_history_results, 1);
        }

        pr(count($results), 1);
    }

    public function missed_order_case_2_single_1_move_to_afs()
    {
        return;

        $results = $this->db->query('SELECT order_id, book_id, version, count(id) as total, sum(status) as total_status
FROM `book_stock_history`
where order_id in (select id from `order` where status in (1,2,8)) and _deleted = 0
group by order_id, book_id, version
having count(id) > 1 and total_status = 1
order by total desc')->result_array();

        pr($results, 1);

        $this->load->model('order/Order_model', 'order_model');
        $this->load->model('book/BookStock_model', 'book_stock_model');
        $this->load->model('book/BookStockHistory_model', 'book_stock_history_model');

        foreach ($results as $result) {
            $order_id = $result['order_id'];
            $book_id = $result['book_id'];
            $version = $result['version'];
            $option = $result['option'];

            $book_stock_history_results = $this->db->query("SELECT * FROM `book_stock_history`
            where order_id=$order_id AND book_id=$book_id AND version=$version AND _deleted = 0")->result_array();

            foreach ($book_stock_history_results as $bs_key => $book_stock_history_result) {
                if(empty($book_stock_history_result['status'])) {
                    // pr($book_stock_history_result);

                    // $this->book_stock_history_model->delete($book_stock_history_result['id']);

                    if ($stock_info = $this->book_stock_model->get_all([
                        'book_id'   => $book_id,
                        'version'   => $version,
                        'option'    => $option
                    ])['rows'][0] ?? []) {
                        // pr($stock_info);

                        $qty = (int)$stock_info['quantity'] + (int)$book_stock_history_result['quantity_order'];
                        // pr($qty);

                        /*$this->book_stock_model->edit($stock_info['id'], [
                            'quantity'  => $qty ?? 0,
                        ]);*/
                    }
                }
            }

            if ($order_info = $this->order_model->get($order_id)) {
                // pr($order_info);

                /*if(in_array($order_info['status'], [1,2,8])) {
                    $this->order_model->edit($order_id, [
                        'status'  => 21
                    ]);
                }*/
            }

            // pr($book_stock_history_results, 1);
        }

        pr(count($results), 1);
    }

    public function missed_order_case_3_partial_fulfill_no_change()
    {
        return;

        $results = $this->db->query('SELECT * from book_stock_history where order_id in (select c.order_id from (SELECT order_id, book_id, version, count(id) as total, sum(status) as total_status
FROM `book_stock_history`
where order_id in (select id from `order` where status in (1,2,8)) and _deleted = 0
group by order_id, book_id, version
having count(id) > 1 and total_status = 2
order by total desc) as c)
and status = 2')->result_array();

        pr($results, 1);

        $this->load->model('order/Order_model', 'order_model');
        $this->load->model('book/BookStock_model', 'book_stock_model');
        $this->load->model('book/BookStockHistory_model', 'book_stock_history_model');

        foreach ($results as $result) {
            $order_id = $result['order_id'];
            $book_id = $result['book_id'];
            $version = $result['version'];
            $option = $result['option'];

            $book_stock_history_results = $this->db->query("SELECT * FROM `book_stock_history`
            where order_id=$order_id AND book_id=$book_id AND version=$version AND _deleted = 0")->result_array();

            foreach ($book_stock_history_results as $bs_key => $book_stock_history_result) {
                if(empty($book_stock_history_result['status'])) {
                    pr($bs_key);
                    pr($book_stock_history_result);

                    // $this->book_stock_history_model->delete($book_stock_history_result['id']);

                    if ($stock_info = $this->book_stock_model->get_all([
                        'book_id'   => $book_id,
                        'version'   => $version,
                        'option'    => $option
                    ])['rows'][0] ?? []) {
                        pr($stock_info);

                        $qty = (int)$stock_info['quantity'] + (int)$book_stock_history_result['quantity_order'];
                        pr($qty);

                        /*$this->book_stock_model->edit($stock_info['id'], [
                            'quantity'  => $qty ?? 0,
                        ]);*/
                    }
                }
            }

            // pr($book_stock_history_results, 1);
        }

        pr(count($results), 1);
    }

    public function missed_order_case_4_most_criticals()
    {
        return;

        $results = $this->db->query('SELECT order_id, book_id, version, count(id) as total, sum(status) as total_status
FROM `book_stock_history`
where order_id in (select id from `order` where status in (1,2,8)) and _deleted = 0
group by order_id, book_id, version
having count(id) > 1 and total_status > 2
order by total desc')->result_array();

        // pr($results, 1);

        $this->load->model('order/Order_model', 'order_model');
        $this->load->model('book/BookStock_model', 'book_stock_model');
        $this->load->model('book/BookStockHistory_model', 'book_stock_history_model');

        foreach ($results as $result) {
            $order_id = $result['order_id'];
            $book_id = $result['book_id'];
            $version = $result['version'];
            $option = $result['option'];

            $book_stock_history_results = $this->db->query("SELECT * FROM `book_stock_history`
            where order_id=$order_id AND book_id=$book_id AND version=$version AND _deleted = 0")->result_array();

            pr($book_stock_history_results);
        }

        pr(count($results), 1);
    }

    public function missed_order_case_4_sum_2()
    {
        return;

        $results = $this->db->query('SELECT order_id, book_id, version, count(id) as total, sum(status) as total_status
FROM `book_stock_history`
where order_id in (select id from `order` where status in (3,4,9)) and _deleted = 0
group by order_id, book_id, version
having count(id) > 1 and total_status = 2
order by total desc')->result_array();

        pr($results, 1);

        $this->load->model('book/BookStock_model', 'book_stock_model');
        $this->load->model('book/BookStockHistory_model', 'book_stock_history_model');

        foreach ($results as $result) {
            $order_id = $result['order_id'];
            $book_id = $result['book_id'];
            $version = $result['version'];
            $option = $result['option'];

            $book_stock_history_results = $this->db->query("SELECT * FROM `book_stock_history`
            where order_id=$order_id AND book_id=$book_id AND version=$version AND _deleted = 0")->result_array();

            foreach ($book_stock_history_results as $bs_key => $book_stock_history_result) {
                if(!empty($bs_key)) {
                    // pr($book_stock_history_result);

                    // $this->book_stock_history_model->delete($book_stock_history_result['id']);

                    if ($stock_info = $this->book_stock_model->get_all([
                        'book_id'   => $book_id,
                        'version'   => $version,
                        'option'    => $option
                    ])['rows'][0] ?? []) {
                        pr($stock_info);

                        // $qty = (int)$stock_info['quantity'] + (int)$book_stock_history_result['quantity_order'];
                        // pr($qty);

                        /*$this->book_stock_model->edit($stock_info['id'], [
                            'quantity'  => $qty ?? 0,
                        ]);*/
                    }
                }
            }

            // pr($book_stock_history_results, 1);
        }

        // pr(count($results), 1);
    }

    public function updateCloneOrder() {
        return;

        $results = $this->db->query("
            SELECT *
            FROM `order`
            WHERE parent_order_id != 0
            AND total > 0
        ")->result_array();

        pr(count($results));
        pr($results, 1);

        $this->load->model('order/OrderClone_model', 'order_clone_model');
        $this->load->model('order/OrderHistory_model', 'order_history_model');
        $this->load->model('order/OrderProduct_model', 'order_product_model');

        foreach ($results as $result) {
            // pr($result);

            $order_info = $this->order_model->get($result['parent_order_id']);
            // pr($order_info);

            $order_clone_info = $this->order_clone_model->getByIds([
                'clone_order_id'    => $result['id'],
                'parent_order_id'   => $result['parent_order_id']
            ])[0] ?? [];

            $order_history_info = $this->order_history_model->get_all([
                'order_id'      => $result['parent_order_id'],
                'description'   => 'Clone Order Created',
                'order'         => 'ASC'
            ])['rows'][0] ?? [];

            // pr($order_history_info);

            $changes_required = false;
            $shipping_cost  = $order_info['shipping_cost'];
            $subtotal       = $result['subtotal'];
            $total          = $result['total'];

            if($order_info['weight'] == $result['weight']) {
                $changes_required = true;
            } else {
                $parent_order_product_results = $this->order_product_model->getOrderProductByOrderId($result['parent_order_id']);
                $parent_quantity = array_sum(array_column($parent_order_product_results, 'quantity'));

                $clone_order_product_results = $this->order_product_model->getOrderProductByOrderId($result['id']);
                $clone_quantity = array_sum(array_column($clone_order_product_results, 'quantity'));

                if($parent_quantity == $clone_quantity) {
                    $changes_required = true;
                } else if($shipping_cost) {
                    $shipping_cost_per_unit = round(($shipping_cost / $parent_quantity), 2);
                    $shipping_cost_clone_order = (double)($shipping_cost_per_unit * $clone_quantity);

                    if(empty($result['shipping_cost']) || ($result['shipping_cost'] == '0.00')) {
                        $subtotal = $subtotal + $shipping_cost_clone_order;
                        $total = $total + $shipping_cost_clone_order;
                        $shipping_cost = $shipping_cost_clone_order;
                    } else {
                        $subtotal = $subtotal - $shipping_cost + $shipping_cost_clone_order;
                        $total = $total - $shipping_cost + $shipping_cost_clone_order;
                        $shipping_cost = $shipping_cost_clone_order;
                    }

                    $changes_required = true;
                } else if(empty($shipping_cost) || ($shipping_cost == '0.00')) {
                    $changes_required = true;
                }
            }

            if($changes_required) {
                pr([
                    'currency_code'     => $order_info['currency_code'],
                    'currency_symbol'   => $order_info['currency_symbol'],
                    'shipping_cost'     => $shipping_cost,
                    'subtotal'          => $subtotal,
                    'total'             => $total,
                    'weight'            => $order_info['weight'],
                    'shipment_type'     => in_array($order_history_info['status'], [1,2,8,9,21]) ? '1' : '2',
                    'order_status'      => $order_history_info['status'],
                ], 1);

                /*$this->order_clone_model->edit($order_clone_info['id'], [
                    'currency_code'     => $order_info['currency_code'],
                    'currency_symbol'   => $order_info['currency_symbol'],
                    'shipping_cost'     => $shipping_cost,
                    'subtotal'          => $subtotal,
                    'total'             => $total,
                    'weight'            => $order_info['weight'],
                    'shipment_type'     => in_array($order_history_info['status'], [1,2,8,9,21]) ? '1' : '2',
                    'order_status'      => $order_history_info['status'],
                ]);

                $this->order_model->editById($result['id'], [
                    'shipping_cost'         => '0.00',
                    'subtotal'              => '0.00',
                    'total'                 => '0.00',
                ]);*/
            }
        }
    }

    public function getSchoolDetails($event_id = '', $site_id = '') {
        if(empty($event_id) || empty($site_id))
            return;

        $this->load->model("Site_model", "site_model");
        $this->load->model('event/Event_model', 'event_model');
        $this->load->model('event/EventUser_model', 'event_user_model');
        $this->load->model('event/EventBook_model', 'event_book_model');

        $event_info = $this->event_model->get($event_id);

        if(empty($event_info = $this->event_model->get($event_id)))
            return;

        if(empty($site_info = $this->site_model->get($site_id)))
            return;

        pr('Event Name: ' . $event_info['name']);
        pr('Site Name: ' . $site_info['name']);
        pr('Site Id: ' . $site_info['id']);

        if(empty($event_students = $this->event_user_model->get_all([
            'event_id'  => $event_id,
            'site_id'   => $site_id,
        ]))) {
            return;
        }

        pr('Total Registered: ' . $event_students['total']);

        if(empty($event_books = $this->event_book_model->get_all([
            'event_id'  => $event_id,
            'site_id'   => $site_id,
        ]))) {
            return;
        }

        $event_book_ids = array_column($event_books['rows'], 'book_id');
        $book_ids = (!empty($event_book_ids)) ? implode(',', $event_book_ids) : [];

        $total_authors = $this->db->query("SELECT *
        FROM `book`
        WHERE `id` IN ($book_ids)
        GROUP BY `book`.user_id")->result_array();

        pr('Total Authors: ' . count($total_authors));

        pr('Total Book Published: ' . $event_books['total']);

        $book_sold_data = $this->db->query("SELECT `book`.id as book_id, `book`.name as book_name, `book`.user_id, `book`.author_name, SUM(event_order.quantity) as book_sold
        FROM `book`
        JOIN `event_order` on `event_order`.book_id=`book`.`id` AND `event_order`._deleted=0
        WHERE `book`.`id` IN ($book_ids)
        GROUP BY `book`.`id`
        ORDER BY book_sold DESC")->result_array();

        pr('Highest Book Sold: ' . ($book_sold_data[0]['book_sold'] ?? 0));
        pr('Book Name: ' . ($book_sold_data[0]['book_name'] ?? ''));
        pr('Author Name: ' . ($book_sold_data[0]['author_name'] ?? ''));
    }
}
