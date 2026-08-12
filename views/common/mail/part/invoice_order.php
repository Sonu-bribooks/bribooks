<?php
$location = !empty($user['location']) ? strtolower($user['location']) : 'india';
?>
<h2>Hi <?=$user['name']?>,</h2>
<p>
	Thank you for purchasing at BriBooks
</p>
<hr style="width: 80%" />
<p>Here are the details of your order:</p>
<table style="margin: auto; border-spacing: 30px;">
	<tr>
		<td style="width: 40%; text-align: left;margin: 20px;margin-right: 20px;border-radius: 15px;background-color: #F4F7FF;">
			<?php $total_copies = 0; ?>
			<table>
				<tr>
					<?php foreach ($products as $index => $item) { ?>
					<td>
						<img
							src="<?php echo $this->config->item('s3_base_url') . 'public/' . $item['cover_image']; ?>"
							width="100"
							height="140"
							style="margin-left: 30px"
						/>
						<p style="margin-left: 30px; margin-top: 0px;margin-bottom: 0;font-size:12px;"><?=$item['name']?> <br />Version <?=$item['version']?></p>
						<p style="margin-left: 30px; margin-top: 0px;margin-bottom: 0;font-size:12px;"><?=$item['quantity']?> copies</p>
						<?php $total_copies += $item['quantity']; ?>
						<?php $option = json_decode($item['option'], true); ?>
						<p
							class="orange"
							style="
								color: #f99232;
								margin-top: 0px;
								font-size: 16px;
								margin-left: 53px;
							"
						>
							<?=$option['name']?>
						</p>
					</td>
					<?php if (count($products) > 0 && $index < count($products) - 1) { ?>
					<td>
						<p
							style="
								font-size: 50px;
								margin-left: 10px;
								margin-top: -20px;

							"
						>
							+
						</p>
					</td>
					<?php } ?>
					<?php } ?>
				</tr>
			</table>


			<div style="font-size: 35px; font-weight: bold;color: #148108;margin-left: 30px;">
				<!-- <?php if ($order['subtotal'] != $order['total']) { ?>
				<h5
					style="
						text-decoration: line-through;
						color:#3B3B3B;
						display: inline;
					"
				>
					<?=$order['currency_code']?> <?=$order['subtotal']?>
				</h5>
				<?php } ?> -->

				<?=$order['currency_code']?> <?=$order['total']?>
				<?php if ($order['credit_discount'] > 0) { ?>
				<p style="margin-top: -5px">Free book bundle applied</p>
				<?php } ?>
			</div>

			<?php if ($has_printed_copies) { ?>
			<div>
				<a
					href="<?= USER_URL ?>trackdelivery/<?= $order['order_code'] ?>"
					style="margin-left:90px;color: #148108;"
				>Track Delivery</a>
			</div>
			<?php } ?>
		</td>

		<td style=" text-align: left; width: 50%; border-radius: 15px; color: #10284B;background-color: #F4F7FF;">
			<div style="margin-left: 30px;margin-right: 10px;margin-top: -10px;">
				<p class="rghtbox"><b>Total Books:</b> <?=count($products)?></p>
				<p class="rghtbox"><b>Total Copies:</b> <?=$total_copies?></p>
				<p class="rghtbox"><b>Total Price:</b> <?=$order['currency_code']?> <?=$order['total']?></p>
				<p class="rghtbox"><b>Shipping:</b> <?=$order['currency_code']?> <?=$order['shipping_cost']?></p>
				<p class="rghtbox"><b>Taxes:</b> <?=$order['currency_code']?> <?=$order['tax']?></p>

				<?php if ($has_printed_copies) { ?>
				<hr style="width: 90%" />
				<p class="rghtbox">
					<b>Address:</b><br />
					<?=$address['address']?>,<?=$address['landmark']?><br>
					<?=$address['city']?>,<?=$address['state']?>,<br>
					<?=$address['country']?>-<?=$address['zipcode']?>
				</p>
				<?php } ?>
			</div>
		</td>
	</tr>
</table>
<?php if ($has_printed_copies) { ?>
<p>
	We will be delivering your order in the next 21 business Days/30 calendar days
</p>
<?php } ?>
<?php if (!empty($has_my_order)) { ?>
<p><b>Important notes from BriBooks:</b></p>
<p style="text-align: left;">
	<ol>
		<li style="text-align: left;">We will allocate a legitimate ISBN number which can be verified on the government ISBN portal once <?= ($location == 'india') ? ISBN_LIMIT : GLOBAL_ISBN_LIMIT; ?> copies of this book are sold on BriBooks.</li>
		<li style="text-align: left;">This book will be available on Amazon within 30 to 60 days post <?= ($location == 'india') ? AMAZON_LIMIT : GLOBAL_AMAZON_LIMIT; ?> copies of this book are sold on BriBooks.</li>
	</ol>
</p>
<?php } ?>
<p>
	Additional order details are available in your <b>account settings</b>
</p>
<p>We hope you enjoy the BriBooks experience.</p>
<br />
