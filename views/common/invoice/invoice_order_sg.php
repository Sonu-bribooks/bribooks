<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link
		rel="preconnect"
		href="https://fonts.googleapis.com"
	/>
	<link
		rel="preconnect"
		href="https://fonts.gstatic.com"
		crossOrigin="anonymous"
	/>
	<link
		href="https://fonts.googleapis.com/css2?family=Signika:wght@300;400;500;600;700&display=swap"
		rel="stylesheet"
	/>
	<style>
		body {
			font-family: 'Signika', Arial, sans-serif;
			/* border:2px solid black; */
			margin: auto;
			width: 85%;
			height: 85%;
			font-weight: 300;
		}
		table {
			font-family: Signika;
			font-size: 12px;
			font-style: normal;
			font-variant: normal;
		}
		p {
			font-size: 12px;
			font-family: Times New Roman, serif !important;
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
	</style>
</head>
<body>
	<div style="width:100%; margin: auto;">
		<table style="width: 100%; border-bottom: 1px solid;">
			<tbody>
				<tr>
					<td style="margin-top: 0px; width:100%; text-align: center; vertical-align: top;">
						<img
							src="<?=base_url('/assets/images/system/register-logo-dark.png') ?>"
							height="35"
						/>
					</td>
				</tr>
			</tbody>
		</table>
		<table style="width: 100%; border-bottom: 2px solid;">
			<tbody>
				<tr>
					<td style="margin-top: 0px; width:40%; text-align: left; vertical-align: top;">
						<p><b>Sold By:</b></p>
						<?php if (strtolower($order['currency_code']) !== 'inr') {
							   if ($order['provider'] == 'stripe_sg') { ?>
									<p><b>Youbooks Edtech Pte. Ltd.</b></p>
									<p>200 Cantonment Road, #06-01A, Southpoint, Singapore (089763)</p>
							<?php
							    } else { ?>
									<p><b>BriBooks - FZCO, IFZA</b></p>
									<p>Properties, Dubai Silicon Oasis, Dubai, United Arab Emirates</p>
							<?php
							    }
							?>

						<?php } else { ?>
							<p><b>YouBooks EdTech India Pvt. Ltd.</b></p>
							<p>Unit # 2117, DLF Corporate Greens, Sector 74A, Gurugram, Haryana, India (122004)</p>
						<?php } ?>
					</td>
					<?php if (!empty($address['mobile']) && in_array($order['order_type'], [1,2])) { ?>
					<td style="margin-top: 0px; text-align: right;  width: 60%">
						<p><b>Shipping Address</b></p>
						<p><b><?=$address['name']?></b></p>
						<p><?=$address['address']?>,<?=$address['landmark']?><br>
						<?=$address['city']?>,<?=$address['state']?>,<br>
						<?=$address['country']?>-<?=$address['zipcode']?></p>
						<p>MOBILE NO: <b>+<?=$address['mobile']?></b></p>
						</br>
					</td>
					<?php } ?>
				</tr>
				<tr>
					<td>
						<?php if (strtolower($order['currency_code']) !== 'inr') {
							
							    if ($order['provider'] == 'stripe_sg') {?>
									
									<p>UEN-202138872E</p>
								<?php
								} else { ?>
									<p>TRN #: 104320054000001</p>
								<?php
								}?>

						<?php } else { ?>
							<p>PAN No: AABCY5072A</p>
							<p>GST IN: 06AABCY5072A1ZN</p>
						<?php } ?>
					</td>
					<?php if (!empty($address['mobile']) && in_array($order['order_type'], [1,2])) { ?>
					<td style="text-align: right;">
						<p><b>Ships To:</b></p>
						<p><b><?=$address['name']?></b><</b></p>
						<p><?=$address['address']?>,<?=$address['landmark']?><br>
						<?=$address['city']?>,<?=$address['state']?>,<br>
						<?=$address['country']?>-<?=$address['zipcode']?></p>
						<p>MOBILE NO: <b>+<?=$address['mobile']?></b></p>
					</td>
					<?php } ?>
				</tr>
			</tbody>
		</table>
		<table style="width: 100%;border-bottom: 2px solid;">
			<tbody>
				<tr>
					<td style="margin-top: 0px;display: block; width: 100%">
						<p><b>Order Number: </b><?=$order['order_code']?></p>
						<p><b>Order Date:</b> <?=date('M d, Y', strtotime($order['date_added']))?></p>
					</td>
					<td style="margin-top: 20px; width:40%; text-align: center;">
						<p>
						<!-- <p>ORDERNO101</p> -->
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<table style="width: 100%;padding: 5px;padding-left: 15px; padding-right: 15px;border-bottom: 1px solid;">
			<tbody>
				<tr>
					<td style="width:40%; text-align: left; margin-left: 0pxS;">
						<!-- <p style="font-size:14px;">PAID</p> -->
						<!-- <br><br> -->
						<p>WEIGHT : <?=$order['order_type'] != 3 ? $order['weight'] : 0?> Grams</p>
					</td>
					<td style="text-align:right; width: 60%;">

						<p style="text-align:right;">AWB:543245532355665</p>
						<p>
							Dimensions (cm): 10 X 10 X 10
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<table style="width: 100%;padding: 5px;border-collapse: collapse;">
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
						<b><small>HSN-49030010</small></b>
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
				<!-- <tr>
					<td style="text-align:center;border: 1px solid black;">
					</td>
					<td style="text-align:center;border: 1px solid black;">
						Free Bundle Discount Applied
					</td>
					<td style="text-align:center;border: 1px solid black;">
					</td>
					<td style="text-align:center;border: 1px solid black;">
					</td>
					<td style="text-align:center;border: 1px solid black;">
						-<span class="currency"><?=$order['currency_symbol']?></span><?=$order['credit_discount']?>
					</td>
				</tr> -->
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
 					<img
 						src="<?=base_url('/assets/images/system/register-logo-dark.png') ?>"
 						height="35"
 					/>
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
 						<?=strtolower($order['currency_code']) !== 'inr'
 						? 'Youbooks Edtech Pte. Ltd.'
 						: 'YouBooks EdTech India Pvt. Ltd.'
 					?></h6>
 					<h6 style="text-align: right; margin-top:28px; margin-right: 3px;">Authorized Signatory</h6>
 				</td>
 			</tr>
 		</table>
		<p style="display:block;text-align:center;color:#999;">This is a system generated invoice and does not need signature</p>
	</div>
</body>
</html>
