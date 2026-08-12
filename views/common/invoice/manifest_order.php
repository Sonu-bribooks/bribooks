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
</head>
<style>
	body {
		font-family: 'Signika', Arial, sans-serif;
		/* border:2px solid black; */
		margin: auto;
		width: 100%;
		height: 100%;
		font-weight: 300;
		margin: 0cm 0cm;
		padding: 0cm 0cm;
		font-size: 10px;
		line-height: 11px;
	}
	@page {
		size: 288pt 432pt;
		margin: 0cm 0cm;
		padding: 0cm 0cm;
	}
	.tab {
		border: 1px solid black;
		border-collapse: collapse;
	}
	.currency{
		font-family: DejaVu Sans !important;
		font-weight: normal;
	}
</style>
<body>
	<div style="border: 2px solid black;margin: 5pt;">
		<table style="border: none;width:100%;">
			<tr style="text-align: left">
				<td>
					<b>Ship To</b><br>
					<?=$address['name']?><br>
					<?=$address['address']?>,<?=$address['landmark']?>
					<?=$address['city']?>,<?=$address['state']?>,
					<?=$address['country']?><br><?=$address['zipcode']?><br>
					Phone No.: <b>+<?=$address['mobile']?>
				</td>
				<td align="right">
					<img
						src="<?=base_url('assets/images/logo-outline-black.png')?>"
						height="30"
					/>
				</td>
			</tr>
		</table>

		<hr />
		<table style="border: none;width:100%;">
			<tr>
				<td>Dimensions: 10.00x10.00x10.00</td>
				<td rowspan="5" align="right" style="position:relative;">
					<div style="position:absolute;right: 0;">
						<?=$barcode?>
					</div>
					<p style="margin:0;clear:both;margin-top:10px;margin-right:10px;">Order #: <?=$order['order_code']?></p>
				</td>
			</tr>
			<tr>
				<td>Payment: <b>PREPAID</b></td>
			</tr>
			<tr>
				<td>Order Total: <?=$order['total']?> <?=mb_strtoupper($order['currency_code'])?></td>
			</tr>
			<tr>
				<td>Weight: <?=$order['weight']?>GM</td>
			</tr>
			<tr>
				<td>eWaybill No.:N/A</td>
			</tr>
		</table>
		<hr />
		<table style="border: none;">
			<tr>
				<th style="text-align: left;">Shipped By</th>
			</tr>
			<tr>
				<td>BriBooks</td>
			</tr>
			<tr>
				<td>
					YouBooks EdTech India Pvt. Ltd.<br />
					Tower 1, Unit # 2101,Dlf Corporate Greens Sector 74a Gurgaon -
					122004
				</td>
			</tr>
			<tr>
				<td>GSTIN: 06AABCY5072A1ZN</td>
			</tr>
			<tr>
				<td>Phone No.: 8800268128</td>
			</tr>
		</table>

		<table style="margin-top: 10px;width:100%;" class="tab">
			<tbody style="border:2px solid black;">
				<tr>
					<th class="tab">Product Name & SKU</th>
					<th class="tab">HSN</th>
					<th class="tab">Qty</th>
					<th class="tab">Unit Price</th>
					<th class="tab">Taxable Value</th>
					<th class="tab">IGST</th>
					<th class="tab">Total</th>
				</tr>
				<?php foreach ($products as $index => $item) { ?>
				<tr style="border:2px solid black;">
					<td class="tab"><?=$item['name']?></td>
					<td class="tab"></td>
					<td class="tab"><?=$item['quantity']?></td>
					<td class="tab"><?=$order['currency_code'] !== 'INR' ? '' : $item['total']?></td>
					<td class="tab"><?=$order['currency_code'] !== 'INR' ? '' : $item['total']?></td>
					<td class="tab">0.00</td>
					<td class="tab"><?=$order['currency_code'] !== 'INR' ? '' : $item['total']?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>

		<p style="border-top: 2px solid #000000; margin-top: 5px;padding:2px;">
			All disputes are subject to Haryana jurisdiction only.Goods once sold will only be taken back or exchanged as per the store's exchange/return policy.
		</p>
		<p style="text-transform: capitalize;border-top: 1px solid #000000;padding:2px;">
			This is an Auto-genrated and does not need signature
		</p>
	</div>
</body>
</html>
