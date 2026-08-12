Dear <?=$user_info['first_name']?>,<br><br>

Congratulations! Your hard-earned
<ul>
<?php foreach ($products as $key => $product) { ?>
<li><?=$product['medallion_name'] . ' for your amazing book "' . $product['book_name'] . '"'?></li>
<?php } ?>
</ul>
has been delivered to your registered address successfully on <?=format_date($order_info['date_completed'])?>.<br><br>

But your journey has just begun. Share your success with the world. Create a short video and post it on social media.<br><br>

Best of luck!<br>
Team BriBooks
