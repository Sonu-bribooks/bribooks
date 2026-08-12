<?php

trait OrderHistory{
    public function order_history(){
		$description = $this->input->post('status');
		$order_id = $this->input->post('orderid');

		$this->order_history_model->add([
			'order_id' => $order_id,
			'description' => $description
		]);
        // 0=incomplete,1=processing,2=inprint,3=shipped,4=intransit,5=delivered,6=order_complete,
        if ($description == "In Print") {
            $this->order_model->edit($order_id,[
                'status' => 2
            ]);
        }else if ($description == "Shipped") {
            $this->order_model->edit($order_id,[
                'status' => 3
            ]);
            $this->ship_order($order_id);
        }else if ($description == "In-Transit") {
            $this->order_model->edit($order_id,[
                'status' => 4
            ]);
        }else if ($description == "Delivered") {
            $this->order_model->edit($order_id,[
                'status' => 5
            ]);
        }else if ($description == "Order Completed") {
            $this->order_model->edit($order_id,[
                'status' => 3
            ]);
        }
	}
}
