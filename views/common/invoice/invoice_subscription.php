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
</head>
<body>
	<div style="width:100%; margin: auto;">
		<table style="width: 100%; border-bottom: 1px solid;">
			<tbody>
				<tr>
					<td style="margin-top: 0px; width:45%; text-align: left; vertical-align: top;">
						<img
							src="<?=base_url('uploads/system/logo-dark.png')?>"
							height="35"
						/>
					</td>
					<td style="margin-top: 0px; text-align: right;">
						<h5 style="margin-top: 20px;">Tax Invoice/Bill of Supply/Cash Memo<br>
							(Original for Recipient)</h5>
					</td>
				</tr>
			</tbody>
		</table>
		<table style="width: 100%; border-bottom: 2px solid;">
			<tbody>
				<tr>
					<td style="margin-top: 0px; width:40%; text-align: left; vertical-align: top;">
						<p><b>Sold By:</b></p>
						<?php if (strtolower($plan['code']) !== 'inr') { ?>
							<p><b>BriBooks - FZCO, IFZA</b></p>
							<p>Properties, Dubai Silicon Oasis, Dubai, United Arab Emirates</p>
						<?php } else { ?>
							<p><b>YouBooks EdTech India Pvt. Ltd.</b></p>
							<p>Unit # 2117, DLF Corporate Greens, Sector 74A, Gurugram, Haryana, India (122004)</p>
						<?php } ?>
						<?php if (strtolower($plan['code']) !== 'inr') { ?>
							<p>TRN #: 104320054000001</p>
						<?php } else { ?>
							<p>PAN No: AABCY5072A</p>
							<p>GST IN: 06AABCY5072A1ZN</p>
						<?php } ?>
					</td>
					<td style="margin-top: 0px; text-align: right;  width: 45%">
						<p><b>Issued To:</b></p>
						<p><b><?=$user['name']?></b></p>
						<p>Email ID: <b><?=$user['email']?></b></p>
						<br />
					</td>
				</tr>
				<tr>
					<td>
						<p><b>Order Number:</b> BBON<?=$order['id']?></p>
						<p><b>Order Date:</b> <?=date('M d, Y', strtotime($order['date_added']))?></p>
					</td>
					<td style="text-align: right;">
						<p><b>Invoice Number:</b> BBIN<?=$order['id']?></p>
						<p><b>Invoice Date:</b> <?=date('M d, Y', strtotime($order['date_added']))?></p>
					</td>
				</tr>
			</tbody>
		</table>

		<table style="width: 100%;padding: 5px;border-collapse: collapse;">
			<thead>
				<tr>
					<th style="text-align:center;border: 1px solid black;">SKU</th>
					<th style="text-align:center;border: 1px solid black;">Item Name</th>
					<th style="text-align:center;border: 1px solid black;">Qty.</th>
					<th style="text-align:center;border: 1px solid black;">Amount</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="text-align:center;border: 1px solid black;">
						BBSP<?=$plan['id']?>
					</td>
					<td style="text-align:center;border: 1px solid black;">
						<?=$plan['name']?><br>
						HSN: 998431
					</td>
					<td style="text-align:center;border: 1px solid black;">
						1
					</td>
					<td style="text-align:center;border: 1px solid black;">
						<span class="currency"><?=$plan['symbol']?></span>
						<?=$price = round($plan['price'] * 100 / 118, 2)?>
					</td>
				</tr>
				<tr>
					<td style="text-align:center;border: 1px solid black;">
					</td>
					<td style="text-align:center;border: 1px solid black;">
						<?=strtolower($plan['code']) === 'inr' ? 'GST(18%)' : 'VAT'?>
					</td>
					<td style="text-align:center;border: 1px solid black;">
					</td>
					<td style="text-align:center;border: 1px solid black;">
						<span class="currency"><?=$plan['symbol']?></span>
						<?=round($plan['price'] - $price, 2)?>
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
						<span class="currency"><?=$plan['symbol']?></span>
						<?=$plan['price']?>
					</td>
				</tr>
				<tr>
					<td colspan="3" style="text-align:left;border: 1px solid black;">
						<b>Total</b>
					</td>
					<td style="text-align:right;border: 1px solid black;">
						<b>
							<span class="currency"><?=$plan['symbol']?></span>
							<?=$plan['price']?>
						</b>
					</td>
				</tr>
			</tbody>
		</table>
		<h6 style="display:block;text-align:center;"><?=strtolower($plan['code']) !== 'inr'
			? 'BriBooks - FZCO, IFZA'
			: 'YouBooks EdTech India Pvt. Ltd.'
		?></h6>
		<p style="display:block;text-align:center;color:#999;">This is a system generated invoice and does not need signature</p>
	</div>
</body>
</html>
