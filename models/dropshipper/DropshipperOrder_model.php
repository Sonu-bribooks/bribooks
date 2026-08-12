<?php defined('BASEPATH') or exit('No direct script access allowed');

class DropshipperOrder_model extends CI_Model {
	public function printerAssignData($data = []) {
		$this->db->select("
			product.product_id,
			SUM(product.quantity) as quantity,
			book.name,
			product.option,
			book.id,
			order.order_code,
			order.currency_code,
			order.currency_symbol,
			product.version,
			DATE(product.date_added) as date_added,
			GROUP_CONCAT(product.order_id) as order_ids,
			GROUP_CONCAT(product.id) as ids,
			GROUP_CONCAT(distinct dropshipper_assignment.code) as assignment_code,
		");

		if (!empty($data['option_type'])) {
			$this->db->where_in('dropshipper_assignment.option_type', $data['option_type']);
		}

		if (isset($data['assign_printer_id'])) {
			$this->db->where('dropshipper_assignment.printer_id', $data['assign_printer_id']);
		}

		if (isset($data['assignment_id'])) {
			$this->db->where('product.assignment_id', $data['assignment_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('product.status', (int)$data['status']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('product.product_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('product.version', (int)$data['version']);
		}

		if (isset($data['option'])) {
			$this->db->like('product.option', $data['option'], 'both');
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(product.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (isset($data['startdate']) || isset($data['enddate'])) {
			$this->db->where('dropshipper_assignment.date_added BETWEEN "' . date('Y-m-d H:i:s', strtotime($data['startdate'])) . '" and "' . date('Y-m-d 23:59:59', strtotime($data['enddate'])) . '"');
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.name', $data['search'], 'after');
			$this->db->or_like('book.author_name', $data['search'], 'after');
			$this->db->or_like('book.id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('order._deleted', 0);
		$this->db->where('product._deleted', 0);
		$this->db->from('order');
		$this->db->join('dropshipper_assign_logs as product', 'order.id=product.order_id', 'left');
		$this->db->join('book', 'product.product_id = book.id', 'left');
		$this->db->join('dropshipper_assignment', 'dropshipper_assignment.id = product.assignment_id', 'left');
		$this->db->group_by('product.product_id');
		$this->db->group_by('product.option');
		$this->db->group_by('product.version');
		$this->db->group_by('DATE(product.date_added)');

		$total = $this->db->count_all_results('', FALSE);

		$this->db->order_by('product.id', 'ASC');

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$row = $this->db->get()->result_array();

		return ['rows' => $row, 'total' => $total];
	}

	public function printerBookStatus($data = []) {
		$this->db->select('
			dropshipper_assign_logs.order_id as order_id,
			dropshipper_assign_logs.product_id,
			book.name,
			dropshipper_assign_logs.option,
			book.book_id,
			book.author_name,
			dropshipper_assign_logs.quantity,
			dropshipper_assign_logs.version,
			dropshipper_assign_logs.date_added
		');

		if (isset($data['assign_printer_id'])) {
			$this->db->where('dropshipper_assign_logs.printer_id', (int)$data['assign_printer_id']);
		}

		$this->db->where('dropshipper_assign_logs.status', (int)$data['status']);
		$this->db->where('dropshipper_assign_logs.version', (int)$data['version']);
		$this->db->where('dropshipper_assign_logs.product_id', (int)$data['book_id']);
		$this->db->like('dropshipper_assign_logs.option', $data['type']);
		$this->db->from('dropshipper_assign_logs');
		$this->db->join('book_version as book', 'book.book_id = dropshipper_assign_logs.product_id AND book.version = dropshipper_assign_logs.version', 'left');

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$total = $this->db->count_all_results('', FALSE);
		$row = $this->db->get()->result_array();

		return ['rows' => $row, 'total' => $total];
	}

	public function quantityCount($data = []) {
		$this->db->select_sum('product.quantity');

		if (!empty($data['option_type'])) {
			$this->db->where('product.option_type', $data['option_type']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('product.product_id', $data['book_id']);
		}

		if (isset($data['type'])) {
			$this->db->like('product.option', $data['type']);
		}

		if (isset($data['option'])) {
			$this->db->like('product.option', $data['option']);
		}

		if (isset($data['version'])) {
			$this->db->where('product.version', (int)$data['version']);
		}

		if (isset($data['status'])) {
			$this->db->where('order.printing_status', (int)$data['status']);
		}

		if (isset($data['order_status'])) {
			$this->db->where('order.status', (int)$data['order_status']);
		}

		if (isset($data['order_status_ne'])) {
			$this->db->where('order.status !=', (int)$data['order_status_ne']);
		}

		if (isset($data['order_status_ge'])) {
			$this->db->where('order.status > ', (int)$data['order_status_ge']);
		}

		$this->db->where('order._deleted', 0);
		$this->db->where('order.pickup_location_id != 1');
		$this->db->where('product._deleted', 0);
		$this->db->where('order.order_type != 3');
		$this->db->from('order');
		$this->db->join('order_product as product', 'order.id = product.order_id', 'left');

		return $this->db->get()->row()->quantity;
	}

	public function printerStats($data = []) {
		$this->db->select_sum('dropshipper_assign_logs.quantity');

		if (isset($data['assignment_id'])) {
			$this->db->where('dropshipper_assign_logs.assignment_id', (int)$data['assignment_id']);
		}

		if (isset($data['assign_printer_id'])) {
			$this->db->where('dropshipper_assign_logs.printer_id', (int)$data['assign_printer_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('dropshipper_assign_logs.product_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('dropshipper_assign_logs.version', (int)$data['version']);
		}

		if (isset($data['type'])) {
			$this->db->like('dropshipper_assign_logs.option', $data['type']);
		}

		if (isset($data['option'])) {
			$this->db->like('dropshipper_assign_logs.option', $data['option']);
		}

		if (isset($data['status'])) {
			$this->db->where('order.status', (int)$data['status']);
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(dropshipper_assign_logs.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}
		$this->db->where('dropshipper_assign_logs._deleted', 0);

		$this->db->from('dropshipper_assign_logs');
		$this->db->join('order', 'dropshipper_assign_logs.order_id = order.id', 'left');

		return $this->db->get()->row()->quantity;
	}

	public function todayData($data = []) {
		$this->db->select_sum('dropshipper_assign_logs.quantity');

		if (isset($data['assign_printer_id'])) {
			$this->db->where('dropshipper_assign_logs.printer_id', $data['assign_printer_id']);
		}

		if (isset($data['type'])) {
			$this->db->like('dropshipper_assign_logs.option', $data['type']);
		}

		if (isset($data['status'])) {
			$this->db->where('dropshipper_assign_logs.status', (int)$data['status']);
		}

		$this->db->where('DATE(dropshipper_assign_logs.date_added)', date('Y-m-d'));

		$this->db->where('dropshipper_assign_logs._deleted', 0);

		$this->db->from('dropshipper_assign_logs');

		return $this->db->get()->row()->quantity ?? 0;
	}

	public function getQaQcAssignCount($data = []) {
		$this->db->select('sum(qa_qc_lots_details.accepted_quantity) AS accepted_quantity, sum(qa_qc_lots_details.accepted_short_quantity) AS accepted_short_quantity, sum(qa_qc_lots_details.rejected_quantity) AS rejected_quantity');

		$this->db->where('qa_qc_lots_details.assignment_id', (int)$data['assignment_id']);

		$this->db->from('qa_qc_lots_details');
		$this->db->group_by('qa_qc_lots_details.assignment_id');

		return $this->db->get()->row_array();
	}

	public function getQaQcCount($data = []) {
		$this->db->select('qa_qc_lots_details.*');

		if (isset($data['assignment_id'])) {
			$this->db->where('qa_qc_lots_details.assignment_id', (int)$data['assignment_id']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('qa_qc_lots_details.book_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('qa_qc_lots_details.version', (int)$data['version']);
		}

		if (isset($data['option'])) {
			$this->db->where('qa_qc_lots_details.option', $data['option']);
		}

		$this->db->from('qa_qc_lots_details');

		return $this->db->get()->row_array();
	}

	public function printerAssignDataSortByBalanced($data = []) {
		$this->db->select("
			product.product_id,
			SUM(product.quantity) as quantity,
			COALESCE(qa.qa_qc_quantity, 0) as qa_qc_quantity,
			COALESCE(ABS(SUM(product.quantity) - COALESCE(qa.qa_qc_quantity, 0)), 0) as diff_quantity,
			book.name,
			product.option,
			book.id,
			product.version,
			DATE(product.date_added) as date_added,
			GROUP_CONCAT(product.order_id) as order_ids,
			GROUP_CONCAT(product.id) as ids,
			GROUP_CONCAT(distinct dropshipper_assignment.code) as assignment_code,
		");

		if (isset($data['assign_printer_id'])) {
			$this->db->where('dropshipper_assignment.printer_id', $data['assign_printer_id']);
		}

		if (isset($data['assignment_id'])) {
			$this->db->where('product.assignment_id', $data['assignment_id']);
		}

		if (isset($data['status'])) {
			$this->db->where('product.status', (int)$data['status']);
		}

		if (isset($data['book_id'])) {
			$this->db->where('product.product_id', (int)$data['book_id']);
		}

		if (isset($data['version'])) {
			$this->db->where('product.version', (int)$data['version']);
		}

		if (isset($data['option'])) {
			$this->db->like('product.option', $data['option'], 'both');
		}

		if (isset($data['date_added'])) {
			$this->db->where('DATE(product.date_added)', date('Y-m-d', strtotime($data['date_added'])));
		}

		if (!empty($data['search'])) {
			$this->db->group_start();
			$this->db->like('book.name', $data['search'], 'after');
			$this->db->or_like('book.author_name', $data['search'], 'after');
			$this->db->or_like('book.id', $data['search'], 'after');
			$this->db->group_end();
		}

		$this->db->where('order._deleted', 0);
		$this->db->where('product._deleted', 0);
		$this->db->from('order');
		$this->db->join('dropshipper_assign_logs as product', 'order.id = product.order_id', 'left');
		$this->db->join('book', 'product.product_id = book.id', 'left');
		$this->db->join('dropshipper_assignment', 'dropshipper_assignment.id = product.assignment_id', 'left');

		$this->db->join('(SELECT assignment_id, book_id, version, COALESCE(ABS(accepted_quantity) + ABS(accepted_short_quantity), 0) as qa_qc_quantity FROM qa_qc_lots_details) AS qa', 'qa.assignment_id = product.assignment_id AND qa.book_id = product.product_id AND qa.version = product.version', 'left outer');

		$this->db->group_by('product.product_id');
		$this->db->group_by('product.option');
		$this->db->group_by('product.version');
		$this->db->group_by('DATE(product.date_added)');

		$total = $this->db->count_all_results('', FALSE);

		$this->db->order_by('diff_quantity', 'DESC');

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$this->db->limit($data['limit'], $data['start']);
		}

		$row = $this->db->get()->result_array();

		return ['rows' => $row, 'total' => $total];
	}
}
