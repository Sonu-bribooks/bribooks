<?php

trait OrderHistory
{
    public function order_history()
    {
        $description = $this->input->post('description');
        $order_id = $this->input->post('orderid');
        $status = $this->input->post('status');


        $this->order_history_model->add([
            'order_id' => $order_id,
            'description' => $description,
            'status' => $status
        ]);
        $this->order_model->edit($order_id, [
            'status' => (int)$status 
        ]);

        // 0=incomplete,1=processing,2=inprint,3=shipped,4=order_complete,
        // if ($description == 'Book Is Sent For Printing') {
        //     $this->order_model->edit($order_id, [
        //         'status' => (int)2
        //     ]);
        // } else if ($description == "Your Order Is Shipped") {
        //     $this->order_model->edit($order_id, [
        //         'status' => (int)3
        //     ]);
        // }
        // else if ($description == "Order Completed") {
        //     $this->order_model->edit($order_id, [
        //         'status' => (int) 4
        //     ]);
        // }

        // else if ($description == "In-Transit") {
        //     $this->order_model->edit($order_id,[
        //         'status' => 4
        //     ]);
        // }
        // else if ($description == "Delivered") {
        //     $this->order_model->edit($order_id,[
        //         'status' => 5
        //     ]);
        // }

    }
}
