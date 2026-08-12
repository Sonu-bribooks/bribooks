<style>
	@page { margin: 0 4px; }
	table {
		font-family: Arial, sans-serif;
		font-size: 10px;
		font-style: normal;
		font-variant: normal;
	}
	p {
		font-size: 10px;
		font-family: Arial, sans-serif;
		margin: 5px 0px 5px 0px;
		padding: 0px;
	}
	footer {
		position: fixed;
		bottom: 0cm;
		left: 0cm;
		right: 0cm;
		height: 2cm;
	}
	.currency{
		font-family: DejaVu Sans !important;
		font-weight: normal;
	}
</style>
<div style="width: 99%; height: 100%; padding: 0; height: 6.20in; border: 1px solid;">
	<table style="width: 100%; border-bottom: 1px solid;">
		<tbody>
			<tr>
				<td style="margin-top: 0px; width:100%; text-align: center; vertical-align: top;">
					<img src="<?=base_url('assets/images/logo-outline-black.png')?>" height="35"/>
				</td>
			</tr>
		</tbody>
	</table>
	<table style="width: 100%; border-bottom: 1px solid;">
		<tbody>
			<tr>
				<td style="font-size: 12px; margin-top: 0px; width:100%; text-align: center; vertical-align: top;">
					TAX INVOICE
				</td>
			</tr>
		</tbody>
	</table>
	<table style="width: 100%; border-bottom: 1px solid;">
		<tbody>
			<tr>
				<td style="margin-top: 0px; width:40%; text-align: left; vertical-align: top;">
					<p><b>Sold By:</b></p>
					<!-- <p><b>Youbooks Edtech Pte. Ltd.</b></p>
					<p>808 French Road,<br />#05-151, Kitchner Complex,<br />Singapore(200808)</p> -->
					<?php if (strtolower($order['currency_code']) !== 'inr') { 
						      if ($order['provider'] == 'stripe') {
								?>
									<p><b>BriBooks - FZCO, IFZA</b></p>
									<p>Properties, Dubai Silicon Oasis, Dubai, United Arab Emirates</p>
								<?php
							  } else {
								?>
									<p><b>Youbooks Edtech Pte. Ltd.</b></p>
									<p>200 Cantonment Road, #06-01A, Southpoint, Singapore (089763)</p>
								<?php
							  }
						?>

					<?php } else { ?>
						<p><b>YouBooks EdTech India Pvt. Ltd.</b></p>
						<p>Unit # 2117, DLF Corporate Greens, Sector 74A, Gurugram, Haryana, India (122004)</p>
					<?php } ?>
					<?php   if (strtolower($order['currency_code']) !== 'inr') {  
						        if ($order['provider'] == 'stripe_sg') {
									?>
									<p>TRN #: 104320054000001</p>
								       
									<?php
								    } else {
										?>
										 <p>UEN-202138872E</p>
										<?php
									}
					                 ?>
					<?php } else { ?>
						<p>PAN No: AABCY5072A</p>
						<p>GST IN: 06AABCY5072A1ZN</p>
					<?php }?>
				</td>
				<?php if (!empty($address['mobile']) && in_array($order['order_type'], [1,2])) { ?>
				<td style="margin-top: 0px; text-align: right; width: 60%">
					<p><b>Shipping Address:</b></p>
					<p><b><?=$address['name']?></b></p>
					<p><?=$address['address']?>, <?=$address['landmark']?><br />
					<?=$address['city']?>, <?=$address['state']?>,<br />
					<?=$address['country']?> - <?=$address['zipcode']?><br />
					Mobile No: <b>+<?=$address['mobile']?></b></p>
					<!-- </br> -->
				</td>
				<?php } ?>
			</tr>
		</tbody>
	</table>
	<table style="width: 100%;border-bottom: 1px solid;">
		<tbody>
			<tr>
				<td style="margin-top: 0px; display: block; width: 100%">
					<p><b>Invoice No: </b><?= 'BP'.$order['id']; ?><br />
					<b>Order Number: </b><?= $order['order_code']; ?><br />
					<b>Order Date: </b> <?= date('M d, Y', strtotime($order['date_added'])); ?></p>
				</td>
				<td style="margin-top: 0px; width:40%; text-align: center;">
					<p>AWB: <?= $awb_number; ?><br />
					Dimensions (cm): 10 X 10 X 10<br />
					WEIGHT: <?= $order['order_type'] != 3 ? $order['weight'] : 0; ?> Grams</p>
				</td>
			</tr>
		</tbody>
	</table>
	<table style="width: 100%;padding: 2px;border-collapse: collapse;">
		<thead>
			<tr>
				<th style="text-align:center;border: 1px solid black;">SN.</th>
				<th style="text-align:center;border: 1px solid black;">Item Name</th>
				<th style="text-align:center;border: 1px solid black;">Item Type</th>
				<th style="text-align:center;border: 1px solid black;">Qty.</th>
				<th style="text-align:center;border: 1px solid black;">Amount</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($products as $index => $item) {
				$option = json_decode($item['option'], true);
			?>
			<tr>
				<td style="text-align:center;border: 1px solid black;">
					<?=($index + 1)?>
				</td>
				<td style="text-align:center;border: 1px solid black;">
					<?=$item['name']?><br />
					<b><small style="font-size: 7px;">HSN-49030010</small></b>
				</td>
				<td style="text-align:center;border: 1px solid black;">
					<?=$option['name']?>
				</td>
				<td style="text-align:center;border: 1px solid black;">
					<?= $item['quantity']?>
				</td>
				<td style="text-align:center;border: 1px solid black;">
					<span class="currency"><?=$order['currency_symbol']?></span><?=$item['total']?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td style="text-align:center;border: 1px solid black;">
				</td>
				<td style="text-align:center;border: 1px solid black;">
					Shipping Charges
				</td>
				<td style="text-align:center;border: 1px solid black;">
				</td>
				<td style="text-align:center;border: 1px solid black;">
				</td>
				<td style="text-align:center;border: 1px solid black;">
					<span class="currency"><?=$order['currency_symbol']?></span><?=$order['shipping_cost']?>
				</td>
			</tr>
			<tr>
				<td style="text-align:center;border: 1px solid black;">
				</td>
				<td style="text-align:center;border: 1px solid black;">
					Taxes
				</td>
				<td style="text-align:center;border: 1px solid black;">
				</td>
				<td style="text-align:center;border: 1px solid black;">
				</td>
				<td style="text-align:center;border: 1px solid black;">
					<span class="currency"><?=$order['currency_symbol']?></span><?=$order['tax']?>
				</td>
			</tr>
			<tr>
				<td style="text-align:center;border: 1px solid black;">
				</td>
				<td style="text-align:center;border: 1px solid black;">
					Discount Applied
				</td>
				<td style="text-align:center;border: 1px solid black;">
				</td>
				<td style="text-align:center;border: 1px solid black;">
				</td>
				<td style="text-align:center;border: 1px solid black;">
					-<span class="currency"><?=$order['currency_symbol']?></span><?=$order['credit_discount']?>
				</td>
			</tr>
			<tr>
				<td style="text-align:center;border: 1px solid black;">
				</td>
				<td style="text-align:center;border: 1px solid black;">
					Order Total
				</td>
				<td style="text-align:center;border: 1px solid black;">
				</td>
				<td style="text-align:center;border: 1px solid black;">
				</td>
				<td style="text-align:center;border: 1px solid black;">
					<span class="currency"><?=$order['currency_symbol']?></span><?=$order['total']?>
				</td>
			</tr>
			<tr>
				<td colspan="4" style="text-align:left;border: 1px solid black;">
					<b>
						Total
					</b>
				</td>
				<td style="text-align:right;border: 1px solid black;">
					<b>
						<span class="currency"><?=$order['currency_symbol']?></span><?=$order['total']?>
					</b>
				</td>
			</tr>
		</tbody>
	</table>
	<table style="width:100%;">
		<tr>
			<td style="margin-top: 0px; width:50%; text-align: left;">
				<img src="<?=base_url('assets/images/logo-outline-black.png')?>" height="35"/>
			</td>
			<td style=" text-align: right; float: right; margin-top: 18px; margin-left: 142px;width: 50%;">
				<p><b>Delivery Challan</b><br>
					(Original For Recipient)
				</p>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="right" style="border: 1px solid black;">
				<h6 style="text-align: right;margin-top: 1px; margin-right: 3px;">
					Youbooks Edtech Pte. Ltd.
					<?php /* echo strtolower($order['currency_code']) !== 'inr' ? 'Youbooks Edtech Pte. Ltd.' : 'YouBooks EdTech India Pvt. Ltd.';*/ ?>
				</h6>
				<h6 style="text-align: right; margin:0; margin-right: 3px;">Authorized Signatory</h6>
			</td>
		</tr>
	</table>
</div>
