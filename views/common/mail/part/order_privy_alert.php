<p>Dear Team</p>
<p>A new order is received with order id: <?=$order_code ?> and order value of <?= $currency_code ?> <?=$total?>.</p>
<table style="border:1px solid black; margin-top:10px;">
  <tr>
    <th style="border:2px solid black;">Book Name</th>
    <th style="border:2px solid black;">Quantity</th>
    <th style="border:2px solid black;">Event Name</th>
  </tr>
  <?php foreach ($product_info as $value): ?>
  <tr>
    <td style="border: 1px solid black;"><?php echo $value['book_name']?></td>
    <td style="border: 1px solid black;"><?php echo $value['book_quantity']?></td>
    <td style="border: 1px solid black;"><?php echo $value['event_name']?></td>
  </tr>
  <?php endforeach; ?>
</table>
<p>Kindly get the order confirmed.</p>
<p>Warm Regards,<br>
Tech Team 📚</p>