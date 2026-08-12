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
					<p><b>Youbooks Edtech Pte. Ltd.</b></p>
					<p>Unit # 2117,<br> DLF Corporate Greens, Sector 74A, <br>Gurugram, Haryana, <br>India (122004)</p>
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
					Dimensions (cm): <?= ($order['length'] ?? 10) ?> X <?= ($order['length'] ?? 10)?> X <?= ($order['length'] ?? 10)?><br />
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
			?>
			<tr>
				<td style="text-align:center;border: 1px solid black;">
					<?=($index + 1)?>
				</td>
				<td style="text-align:center;border: 1px solid black;">
					<?=$item['name']?><br />
				</td>
				<td style="text-align:center;border: 1px solid black;">
					DOC
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
