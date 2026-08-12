<?php defined('BASEPATH') or exit('No direct script access allowed');

class SiteSection_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($site_grade_id = 0)
    {
        $this->db->select('site_section.*');

        $this->db->where('site_section.grade_id', (int)$site_grade_id);
        $this->db->where('site_section._deleted', 0);

        // $this->db->join('site', 'site.id = site_grade.site_id', 'left');

        return $this->db->get('site_section')->row_array();
    }

    public function get_all($data = [])
    {
        $this->db->select('site_section.*');

        if (isset($data['grade_id'])) {
            $this->db->where('site_section.grade_id', (int)$data['grade_id']);
        }

        if (isset($data['name'])) {
            $this->db->where('site_section.name', (int)$data['name']);
        }

        if (isset($data['site_code'])) {
            $this->db->where('site_section.site_code', $data['site_code']);
        }

        if (isset($data['status'])) {
            $this->db->where('site_section.status', (int)$data['status']);
        }

        if (!empty($data['search'])) {
            $this->db->group_start();
            $this->db->like('site_section.name', $data['search'], 'after');
            $this->db->group_end();
        }

        $this->db->where('site_section._deleted', 0);

        // $this->db->join('site', 'site.id = site_section.site_id', 'left');
        $this->db->from('site_section');

        $total = $this->db->count_all_results('', FALSE);

        if (isset($data['start']) && isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 10;
            }

            $this->db->limit($data['limit'], $data['start']);
        }

        $sort_data = [
            'site_section.name',
            'site_section.sort_order',
            'site_section.status',
            'site_section.date_added',
            'site_section.date_modified',
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sort = $data['sort'];
        } else {
            $sort = 'site_section.date_added';
        }

        if (isset($data['order']) && ($data['order'] == 'ASC')) {
            $order = 'ASC';
        } else {
            $order = 'DESC';
        }

        $this->db->order_by($sort, $order);

        return ['rows' => $this->db->get()->result_array(), 'total' => $total];
    }
    
    public function add($data = [])
    {
        $this->db->where("name", $data['name']);
        $this->db->where("grade_id", $data['grade_id']);
        $this->db->where('_deleted', 0);
        $result = $this->db->get('site_section');

        if ($result->num_rows() > 0) {
            $result = $result->result_array();
            return $result[0]['id'];
        } else {
            $this->db->insert('site_section',  [
                'grade_id'            => $data['grade_id'],
                'name'                => $data['name'],
                'status'              => 1,
                'date_added'          => date('Y-m-d H:i:s'),
                'date_modified'          => date('Y-m-d H:i:s'),
            ]);

            $site_grade_id = $this->db->insert_id();
            return $site_grade_id;
        }
    }

    public function edit($site_grade_id = 0, $data = [])
    {
        $this->db->where('id', (int)$site_grade_id);
        $this->db->update('site_section', $data + [
            'date_modified'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function delete($site_grade_id = 0)
    {
        $this->db->where('id', (int)$site_grade_id);
        $this->db->update('site_section',  [
            'status'        => 0,
            '_deleted'        => 1,
            'date_deleted'    => date('Y-m-d H:i:s'),
        ]);
    }
}
