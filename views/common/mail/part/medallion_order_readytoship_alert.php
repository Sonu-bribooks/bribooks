Dear <?=$user_info['first_name']?>,<br /><br />

Congratulations on earning the prestigious
<ul>
<?php foreach ($products as $key => $product) { ?>
<li><?=$product['medallion_name'] . ' for your amazing book "' . $product['book_name'] . '"'?></li>
<?php } ?>
</ul>
It's time to celebrate your achievement.<br><br>

Important Update: Your medallion has been shipped to your registered address and will reach you within the next 7 days.<br><br>

We are truly delighted for you and we celebrate your literary excellence.<br><br>

Share your success with the world. Create a short video and post it on social media.<br><br>

<?php $shipping_tracking_info = !empty($order_info['shipping_tracking_info']) ? json_decode($order_info['shipping_tracking_info'], true) : ''; ?>

You can track your shipment here <a href="https://shiprocket.co/tracking/<?=$shipping_tracking_info['awb_code']?>">Track Shipment</a><br><br>

<b>Please Note:</b> This award is entirely complimentary, presented by BriBooks as a token of appreciation for your remarkable achievement. No payment is required at the time of delivery.<br><br>

Best of luck!<br>
Team BriBooks
