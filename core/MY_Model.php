<?php

defined('BASEPATH') or exit('No Direct access alloweed');

class MY_Model extends CI_Model
{
    protected $table = '';
    protected $primary_key = 'id';
  

    public function __construct()
    {
        parent::__construct();

    }

    // Change Database Connection
    protected function set_database($database = '') {
        if (!empty($database)) {
            $this->db = $this->load->database($database, TRUE);
        }
    }

    //Get function
    public function get($id = 0, $options = []) {
        $this->db->select($options['select'] ?? '*');
        
        $this->db->from($this->table);

        if (!empty($options['joins'])) {

            foreach ($options['joins'] as $join) {

                $this->db->join(
                    $join['table'],
                    $join['condition'],
                    $join['type'] ?? 'left'
                );
            }
        }

        //defaukt primary key = id if different then need to define in specific model  
        $this->db->where($this->table . '.' . $this->primary_key, (int)$id);

        $this->db->where($this->table . '._deleted',0);

        return $this->db->get()->row_array();
    }
    
    //Get All Function
    public function get_all($where = [], $options = []) {
        $this->db->from($this->table);

        //Select column conditions
        if (!empty($options['select'])) {
            $this->db->select($options['select']);
        } else {
            $this->db->select('*');
        }

        //Joins conditions
        if (!empty($options['joins'])) {

            foreach ($options['joins'] as $join) {

                $this->db->join(
                    $join['table'],
                    $join['relation'],
                    $join['type'] ?? 'left'
                );
            }
        }

        //Where conditions
        if (!empty($where)) {
            $this->db->where($where);
        }

        //filter or search
        if (!empty($options['search_keyword'])) {

            $this->db->group_start();

            foreach ($options['search_keyword'] as $column) {

                $this->db->or_like(
                    $column,
                    $options['search'],
                    'after'
                );
            }
            $this->db->group_end();
        }

        //deleted status check
        $this->db->where($this->table . '._deleted', 0);

        //Get total record count
        $clone = clone $this->db;
        $total = $clone->count_all_results();

        //Pagination
        if (isset($options['limit'])) {

            $start = max(0, (int)($options['start'] ?? 0));
            $limit = ($options['limit'] > 0) ? $options['limit'] : 10;

            $this->db->limit($limit, $start);
        }

        //Sorting Order
        if (!empty($options['sort'])) {

            if ($options['sort'] != 'sn') {
                $sort = $options['sort'];
            } else {
                $sort = $this->table . '.id';
            }

            $this->db->order_by(
                $sort,
                $options['order'] ?? 'DESC'
            );
        }

        $query = $this->db->get();

        return [
            'rows'  => $query->result_array(),
            'total' => $total
        ];
    }

    //Child model override kar sakta hai 
    protected function formatData(&$data) {}

    public function add($data = [])
    {
        $this->formatData($data);

        $data['date_added'] = date('Y-m-d H:i:s');
        $data['date_modified'] = date('Y-m-d H:i:s');

        $this->db->insert($this->table, $data);

        return $this->db->insert_id();
    }

    public function edit($id, $data = [])
    {
        $this->formatData($data);

        $data['date_modified'] = date('Y-m-d H:i:s');

        $this->db
            ->where($this->primary_key, (int)$id)
            ->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db
            ->where($this->primary_key, (int)$id)
            ->update($this->table, [
                '_deleted' => 1,
                'date_deleted' => date('Y-m-d H:i:s')
            ]);
    }

    public function enableDisable($id)
    {
        $row = $this->get($id);

        if (!$row) {
            return false;
        }

       $status = !$row['status'];

        return $this->db
            ->where($this->primary_key, (int)$id)
            ->update($this->table, [
                'status' => $status,
                'date_modified' => date('Y-m-d H:i:s')
            ]);
    }

    public function getBySlug($slug)
    {
        return $this->db
            ->where('slug', $slug)
            ->where('status', 1)
            ->where('_deleted', 0)
            ->get($this->table)
            ->row_array();
    }
}
