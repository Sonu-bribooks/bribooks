<?php defined('BASEPATH') or exit('No direct script access allowed');

trait AutoEscalateOrderAlert {

	public function addAutoEscalateOrderCron() {
		$this->load->model('order/AutoEscalatedOrder_model', 'auto_escalated_order_model');

		if (ENVIRONMENT != 'production') {
			// for dev
			$domestic_afs_days 	= 3;
			$domestic_rts_days 	= 4;
			$global_afs_days 	= 2;
			$global_rts_days 	= 3;
		}else {
			// for production
			$domestic_afs_days 	= 10;
			$domestic_rts_days 	= 10;
			$global_afs_days 	= 7;
			$global_rts_days 	= 10;
		}

		// domestic order
		$new_domestic_afs_data 			= $this->auto_escalated_order_model->getForAutoEscalateOrder([
			'days' 			=> $domestic_afs_days,
			'status' 		=> [1, 2, 8, 21],
			'currency_id' 	=> 47
		]);
        $domestic_ready_shipped_data 	= $this->auto_escalated_order_model->getForAutoEscalateOrder([
			'days' 			=> $domestic_rts_days,
			'status' 		=> [3, 9],
			'currency_id' 	=> 47
		]);

		//global order
		$new_global_afs_data 			= $this->auto_escalated_order_model->getForAutoEscalateOrder([
			'days' 				=> $global_afs_days, 
			'status' 			=> [1, 2, 8, 21],
			'ne_currency_id' 	=> 47
		]);
        $ready_global_shipped_data 		= $this->auto_escalated_order_model->getForAutoEscalateOrder([
			'days' 				=> $global_rts_days,  // 21 for production
			'status' 			=> [3, 9],
			'ne_currency_id' 	=> 47
		]);

		$merge_order_data 				= array_merge($new_domestic_afs_data, $domestic_ready_shipped_data, $new_global_afs_data, $ready_global_shipped_data);
        
		if (!empty($merge_order_data)) {
			foreach($merge_order_data as $value) {
				$this->auto_escalated_order_model->add([
					'order_id' => $value['order_id']
				]);
			}
		}
	}
}
