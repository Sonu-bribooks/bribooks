
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $title; ?></title>
	<link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,300,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<meta charset="utf-8">
	<style>
		html, body, div, span, applet, object, iframe,
		h1, h2, h3, h4, h5, h6, p, blockquote, pre,
		a, abbr, acronym, address, big, cite, code,
		del, dfn, em, img, ins, kbd, q, s, samp,
		small, strike, strong, sub, sup, tt, var,
		b, u, i, center,
		dl, dt, dd, ol, ul, li,
		fieldset, form, label, legend,
		table, caption, tbody, tfoot, thead, tr, th, td,
		article, aside, canvas, details, embed,
		figure, figcaption, footer, header, hgroup,
		menu, nav, output, ruby, section, summary,
		time, mark, audio, video {
			margin: 0;
			padding: 0;
			border: 0;
			font: inherit;
			font-size: 100%;
			vertical-align: baseline;
		}

		html {
			line-height: 1;
		}

		ol, ul {
			list-style: none;
		}

		table {
			border-collapse: collapse;
			border-spacing: 0;
		}

		caption, th, td {
			text-align: left;
			font-weight: normal;
			vertical-align: middle;
		}

		q, blockquote {
			quotes: none;
		}
		q:before, q:after, blockquote:before, blockquote:after {
			content: "";
			content: none;
		}

		a img {
			border: none;
		}

		article, aside, details, figcaption, figure, footer, header, hgroup, main, menu, nav, section, summary {
			display: block;
		}

		body {
			font-family: 'Source Sans Pro', sans-serif;
			font-weight: 300;
			font-size: 12px;
			margin: 0;
			padding: 0;
			color: #777777;
		}
		body a {
			text-decoration: none;
			color: inherit;
		}
		body a:hover {
			color: inherit;
			opacity: 0.7;
		}
		body .container {
			min-width: 500px;
			margin: 0 auto;
			padding: 0 30px;
		}
		body .clearfix:after {
			content: "";
			display: table;
			clear: both;
		}
		body .left {
			float: left;
		}
		body .right {
			float: right;
		}
		body .helper {
			height: 100%;
		}

		header {
			height: 40px;
			margin-top: 20px;
			margin-bottom: 40px;
			padding: 0px 5px 0;
		}
		header figure {
			float: left;
			width: auto;
			margin-right: 10px;
		}
		header figure img {
			height: 40px;
		}
		header .company-info {
			color: #BDB9B9;
		}
		header .company-info .title {
			margin-bottom: 5px;
			color: #ff9900;
			font-weight: 600;
			font-size: 2em;
		}
		header .company-info .line {
			display: inline-block;
			height: 9px;
			margin: 0 4px;
			border-left: 1px solid #ff9900;
		}

		section .details {
			min-width: 500px;
			margin-bottom: 40px;
			padding: 10px 35px;
			background-color: #ff9900;
			color: #ffffff;
		}
		section .details .client {
			width: 50%;
			line-height: 16px;
		}
		section .details .client .name {
			font-weight: 600;
		}
		section .details .data {
			width: 50%;
			text-align: right;
		}
		section .details .title {
			margin-bottom: 15px;
			font-size: 3em;
			font-weight: 400;
			text-transform: uppercase;
		}
		section .table-wrapper {
			position: relative;
			overflow: hidden;
		}
		section .table-wrapper:before {
			content: "";
			display: block;
			position: absolute;
			top: 33px;
			left: 30px;
			width: 90%;
			height: 100%;
			border-top: 2px solid #BDB9B9;
			border-left: 2px solid #BDB9B9;
			z-index: -1;
		}
		section .no-break {
			page-break-inside: avoid;
		}
		section table {
			width: 100%;
			margin-bottom: -20px;
			table-layout: fixed;
			border-collapse: separate;
			border-spacing: 5px 20px;
		}
		section table .no {
			width: 50px;
		}
		section table .desc {
			width: 55%;
		}
		section table .qty, section table .unit, section table .total {
			width: 15%;
		}
		section table tbody.head {
			vertical-align: middle;
			border-color: inherit;
		}
		section table tbody.head th {
			text-align: center;
			color: white;
			font-weight: 600;
			text-transform: uppercase;
		}
		section table tbody.head th div {
			display: inline-block;
			padding: 7px 0;
			width: 100%;
			background: #BDB9B9;
		}
		section table tbody.head th.desc div {
			width: 115px;
			margin-left: -110px;
		}
		section table tbody.body td {
			padding: 10px 5px;
			background: #F3F3F3;
			text-align: center;
		}
		section table tbody.body h3 {
			margin-bottom: 5px;
			color: #ff9900;
			font-weight: 600;
		}
		section table tbody.body .no {
			padding: 0px;
			background-color: #ff9900;
			color: #ffffff;
			font-size: 1.66666666666667em;
			font-weight: 600;
			line-height: 50px;
		}
		section table tbody.body .desc {
			padding-top: 0;
			padding-bottom: 0;
			background-color: transparent;
			color: #777787;
			text-align: left;
		}
		section table tbody.body .total {
			color: #ff9900;
			font-weight: 600;
		}
		section table tbody.body tr.total td {
			padding: 5px 10px;
			background-color: transparent;
			border: none;
			color: #777777;
			text-align: right;
		}
		section table tbody.body tr.total .empty {
			background: white;
		}
		section table tbody.body tr.total .total {
			font-size: 1.18181818181818em;
			font-weight: 600;
			color: #ff9900;
		}
		section table.grand-total {
			margin-top: 40px;
			margin-bottom: 0;
			border-collapse: collapse;
			border-spacing: 0px 0px;
			margin-bottom: 40px;
		}
		section table.grand-total tbody td {
			padding: 0 10px 10px;
			background-color: #ff9900;
			color: #ffffff;
			font-weight: 400;
			text-align: right;
		}
		section table.grand-total tbody td.no, section table.grand-total tbody td.desc, section table.grand-total tbody td.qty {
			background-color: transparent;
		}
		section table.grand-total tbody td.total, section table.grand-total tbody td.grand-total {
			border-right: 5px solid #ffffff;
		}
		section table.grand-total tbody td.grand-total {
			padding: 0;
			font-size: 1.16666666666667em;
			font-weight: 600;
			background-color: transparent;
		}
		section table.grand-total tbody td.grand-total div {
			float: right;
			padding: 10px 5px;
			background-color: #e07e1b;
		}
		section table.grand-total tbody td.grand-total div span {
			margin-right: 5px;
		}
		section table.grand-total tbody tr:first-child td {
			padding-top: 10px;
		}

		footer {
			margin-bottom: 20px;
			padding: 0 5px;
		}
		footer .thanks {
			margin-bottom: 40px;
			color: #ff9900;
			font-size: 1.16666666666667em;
			font-weight: 600;
		}
		footer .notice {
			margin-bottom: 25px;
		}
		footer .end {
			padding-top: 5px;
			border-top: 2px solid #ff9900;
			text-align: center;
		}

		.print-button {
			background: #ff9900;
			color: #fff;
			padding: 10px 15px;
			outline: none;
			border: unset;
			margin: auto;
			display: block;
			position: fixed;
			bottom: 90px;
			left: 50%;
			transform: translateX(-50%);
			cursor: pointer;
			text-transform: uppercase;
		}

		.tc {
			font-weight: bold;
		}

		@media print {
			@page { margin: 0; }
			body  { margin: 1.6cm; }
			body {-webkit-print-color-adjust: exact;}
			.print-button {display: none;}
		}

	</style>
</head>

<body>
	<header class="clearfix">
		<div class="container">
			<figure>
				<img class="logo" src="<?php echo site_url('uploads/system/logo-dark.png'); ?>" alt="leaplearner">
			</figure>
			<div class="company-info">
				<h2 class="title">ICODE</h2>
				<span>&copy; <?php echo date('Y'); ?> ICODE</span>
				<span class="line"></span>
				<span><?php echo $this->config->item('site_address'); ?></span>
				<a class="phone" href="tel:<?php echo $this->config->item('site_mobile'); ?>"><?php echo $this->config->item('site_mobile'); ?></a>
				<span class="line"></span>
				<a class="email" href="mailto:<?php echo $this->config->item('site_email'); ?>"><?php echo $this->config->item('site_email'); ?></a>
			</div>
		</div>
	</header>

	<section>
		<div class="details clearfix">
			<div class="container">
				<div class="client left">
					<p>INVOICE TO:</p>
					<p class="name"><?php echo $name; ?></p>
					<a class="phone" href="tel:<?php echo $mobile; ?>"><?php echo $mobile; ?></a>
				</div>
				<div class="data right">
					<div class="title"><?php echo $invoice_number; ?></div>
					<div class="date">
						Due Date: <?php echo $invoice_date; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="container">
			<div class="table-wrapper">
				<table>
					<tbody class="head">
						<tr>
							<th class="no"></th>
							<th class="desc"><div>Description</div></th>
							<th class="qty"><div>Course Level</div></th>
							<th class="unit"><div>Course Duration</div></th>
							<th class="unit"><div>Course Mode</div></th>
							<th class="total"><div>Total</div></th>
						</tr>
					</tbody>
					<tbody class="body">
						<?php foreach ($items as $key => $item) { ?>
						<?php
							$enrol_info 		= $this->enrol_model->get($item['enrol_id']);
							$renewal_period 	= EMI_TYPES[$enrol_info['emi_type']];

							$enrolment_period	= date('M Y', strtotime("-{$renewal_period} month", strtotime($enrol_info['renewal_date']))) . '-' . date('M Y', strtotime($enrol_info['renewal_date']));
						?>

						<tr>
							<td class="no"><?php echo $key; ?></td>
							<td class="desc"><?php echo $item['course']; ?></td>
							<td class="qty"><?php echo _l($matches['level'] ?? '-'); ?></td>
							<td class="unit"><?php echo _l($enrolment_period); ?></td>
							<td class="unit"><?php echo _l($enrol_info['mode'] ?? '-'); ?></td>
							<td class="total"><?php echo currency($item['amount'], $this->config->item('site_tax')); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<div class="no-break">
				<table class="grand-total">
					<tbody>
						<tr>
							<td class="no"></td>
							<td class="desc"></td>
							<td class="qty"></td>
							<td class="qty"></td>
							<td class="unit">SUBTOTAL:</td>
							<td class="total"><?php echo currency($total, $this->config->item('site_tax')); ?></td>
						</tr>
						<tr>
							<td class="no"></td>
							<td class="desc"></td>
							<td class="qty"></td>
							<td class="qty"></td>
							<td class="unit"><?php echo $this->config->item('site_tax_text'); ?>:</td>
							<td class="total"><?php echo currency($total * ($this->config->item('site_tax') / (100 + $this->config->item('site_tax')))); ?></td>
						</tr>
						<tr>
							<td class="grand-total" colspan="6"><div><span>GRAND TOTAL:</span><?php echo currency($total); ?></div></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</section>

	<footer>
		<div class="container">
			<div class="thanks">Thank you!</div>
			<div class="notice">
				<div class="tc">Terms & Conditions:</div>
				<div>
					<ol>
						<li>1. The Fee paid is Non-Refundable and Cannot be charged back if paid through a Credit Card/Debit Card or any such instrument.</li>
						<li>2. The course for which the fees is paid is Non-Transferable and cannot be used by any other user.</li>
						<li>3. The Course for which is fees is paid for cannot be exchanged for any other course.</li>
					</ol>
				</div>
			</div>
			<div class="end">Invoice was created on a computer and is valid without the signature and seal.</div>
		</div>
	</footer>

	<button class="print-button" onclick="window.print();">Print</button>

</body>
<script>setTimeout(() => (window.print()), 200);</script>
</html>
