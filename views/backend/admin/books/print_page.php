<?php
$page_img_url = $this->config->item('s3_base_url') . $this->config->item('s3_themes');
$cover_img_url = $this->config->item('s3_base_url') . 'public/';

?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<title><?= $book['name'] ?></title>
	<!-- all the meta tags -->
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta content="" name="description" />
	<meta content="" name="author" />
	<link href="https://fonts.googleapis.com/css2?family=Signika:wght@300;400;500;600;700" rel="stylesheet" />
	<!-- <link href="<?= base_url('assets/global/fonts/Impact/impact.ttf') ?>" rel="stylesheet" /> -->
	<!-- <link href="<?= base_url('assets/global/fonts/Chalkboard/chalkboar_font.otf') ?>" rel="stylesheet" /> -->
	<style>
		@font-face {
			font-family: 'Impact';
			font-weight: bold;
			src: url('<?= base_url('assets/global/fonts/Impact/impact.ttf') ?>') format("truetype");
		}

		@font-face {
			font-family: 'Chalkboard SE';
			font-weight: normal bold;
			src: url('<?= base_url('assets/global/fonts/Chalkboard/chalkboar_font.otf') ?>') format("OpenType");
		}

		.text-center {
			text-align: center;
		}

		.text-light {
			color: #fefefe !important
		}

		html {
			margin: 0cm 0cm;
			padding: 0cm 0cm;
		}

		body {
			font-family: 'Signika', sans-serif;
			margin: 0cm 0cm;
			padding: 0cm 0cm;
			width: 100%;
			height: 100%;
		}

		@page bpage {
			size: <?= $width ?>pt <?= $height ?>pt;
			margin: 0cm 0cm;
			padding: 0cm 0cm;
		}

		.pagebreak {
			page-break-after: always;
			/* page-break-inside: avoid; */
		}

		.book-page {
			page: bpage;
			width: <?= $width ?>pt;
			height: <?= $height ?>pt;
			margin: auto;
		}

		.book-page .page {
			width: <?= $width ?>pt;
			height: <?= $height ?>pt;
			position: relative;
			overflow: hidden;
		}
		.page-num {
			position: absolute;
			bottom: 0;
			left: 0;
			right: 0;
			display: block;
			padding-bottom: 40pt;
			text-align: center;
		}

		.background-image,
		.background-cover,
		.background-cover img {
			width: <?= $width ?>pt;
			height: <?= $height ?>pt;
		}

		.background-image img {
			width: <?= $width * 2 ?>pt;
			height: <?= $height ?>pt;
		}

		.background-image.right img {
			transform: translateX(-<?= $width ?>pt);
		}

		.page-text {
			position: absolute;
			z-index: 9;
		}

		.page-text>p {
			margin: 0;
			line-height: <?= 1.2 * 16 * 0.75 * $multiplier ?>pt;
			word-break: break-word;
		}

		.book-name {
			text-transform: uppercase;
			padding: 0;
			margin: 0;
			word-break: break-word;
			line-height: 0.8;
		}

		.book-info {
			background-color: rgba(255, 255, 255, 0.4);
			padding: <?= 0.313 * 16 * 0.75 * $multiplier ?>pt <?= 0.625 * 16 * 0.75 * $multiplier ?>pt;
			padding-bottom: <?= (0.313 * 16 * $multiplier + $fc_bleed + 24) * 0.75 ?>pt;
			padding-right: <?= (0.625 * 16 * $multiplier + $fc_bleed + 24) * 0.75 ?>pt;
			font-family: Signika;
			font-weight: 300;
			position: absolute;
			bottom: 0;
			left: 0;
			right: 0;
		}

		.book-info .author-name {
			font-size: <?= 0.875 * 16 * 0.75 * $multiplier ?>pt;
			color: rgb(16, 40, 75);
			text-align: right;
			margin: 0;
		}

		.printlinecover {
			padding: <?= (1.563 * 16 * $multiplier) * 0.75 ?>pt <?= (2.188 * 16 * $multiplier) * 0.75 ?>pt;
			position: absolute;
			top: 50%;
			left: 0;
			right: 0;
			transform: translateY(-50%);
		}

		.printlinecover .book-title {
			font-family: Impact, cursive;
			font-size: <?= 2.5 * 16 * 0.75 * $multiplier ?>pt;
			text-align: center;
			text-transform: uppercase;
			color: #10284b;
			word-break: break-word
		}

		.printlinecover .author-name {
			text-align: center;
			font-weight: 400;
			font-size: <?= 1.2 * 16 * 0.75 * $multiplier ?>pt;
			padding-bottom: <?= 1 * 16 * 0.75 * $multiplier ?>pt;
			border-bottom: 1pt solid #dddddd;
		}

		.printlinecover .publisher-info {
			font-size: <?= .8 * 16 * 0.75 * $multiplier ?>pt;
			font-weight: 300;
			line-height: <?= 1.2 * 16 * 0.75 ?>pt;
		}

		.backcover {
			padding: <?= (1.563 * 16 * $multiplier + $bleed) * 0.75 ?>pt <?= (2.188 * 16 * $multiplier + $bleed) * 0.75 ?>pt;
		}

		.backcover p {
			font-size: <?= 0.625 * 16 * 0.75 * $multiplier ?>pt;
			margin: 0;
			font-weight: 300;
		}

		.backcover img {
			border-radius: <?= 1 * 16 * 0.75 * $multiplier ?>pt
		}

		.backcover .author-info,
		.backcover .title-info {
			padding-bottom: <?= 1.25 * 16 * 0.75 * $multiplier ?>pt
		}

		.backcover .author-info-wrapper {
			position: relative;
		}

		.backcover .author-info-wrapper .author-info {}

		.backcover .author-name {
			font-size: <?= 0.875 * 16 * 0.75 * $multiplier ?>pt
		}

		.backcover .title-info {
			border-bottom: <?= 1 * 0.75 ?>pt solid rgba(255, 255, 255, 0.2);
		}

		.backcover .title-info .book-title {
			word-break: break-word;
			text-transform: uppercase;
			font-family: Signika;
			margin: 0;
			margin-bottom: <?= .5 * 16 * 0.75 * $multiplier ?>pt;
			font-weight: 700;
		}

		.backcover .author-info,
		.backcover .publisher-info {
			padding-top: <?= 1.25 * 16 * 0.75 * $multiplier ?>pt
		}

		.backcover .author-img {
			margin-right: <?= 1 * 16 * 0.75 * $multiplier ?>pt;
			text-align: center;
			width: 27%;
			display: inline-block;
			vertical-align: top;
		}

		.backcover .author-img img {
			width: <?= 5 * 16 * 0.75 * $multiplier ?>pt;
			height: <?= 5 * 16 * 0.75 * $multiplier ?>pt;
			/* margin-top: 0.5pt; */
		}

		.backcover .author-img p {
			font-size: <?= 0.75 * 16 * 0.75 * $multiplier ?>pt;
		}

		.backcover .author-bio {
			width: 65%;
			display: inline-block;
			position: absolute;
			top: 0;
		}

		.backcover .author-bio p {
			/* position: absolute;
	top: 0;
	left: 0;
	right: 0; */
		}

		.backcover-logo {
			filter: brightness(0) invert(1)
		}

		.backcover .back-footer {
			/* padding-top: 1pt; */
			/* position: absolute;
	bottom: 2.188pt;
	left: 1.563pt;
	right: 1.563pt; */
			padding-top: <?= 0.5 * 16 * 0.75 * $multiplier ?>pt;
		}

		.backcover .isbn-info,
		.backcover .publisher-info {
			border-top: <?= 1 * 0.75 * $multiplier ?>pt solid rgba(255, 255, 255, 0.2);
			padding-bottom: <?= 1.25 * 16 * 0.75 * $multiplier ?>pt
		}

		.backcover .isbn-info,
		.backcover .publisher-info h6 {
			font-family: Signika;
			font-size: <?= 1 * 16 * 0.75 * $multiplier ?>pt;
			margin: 0;
			margin-bottom: <?= .5 * 16 * 0.75 * $multiplier ?>pt;
			font-weight: 600;
		}

		.backcover .isbn-logo {
			width: 50%;
			display: inline-block;
		}

		.backcover .isbn-logo img {
			height: <?= 1.675 * 16 * 0.75 * $multiplier ?>pt;
		}

		.backcover .isbn-code, .backcover .qr-code {
			width: 40%;
			display: inline-block;
			text-align: center;
			position: absolute;
			right: 0;
		}

		.backcover .isbn-code .isbn-data {
			background-color: white;
			padding-bottom: 2px;
			margin-left: 10px;
		}

		.backcover .isbn-code .isbn-data img {
			width: 100%;
		}

		.backcover .isbn-code .isbn-data p {
			width: 100%;
			background-color: white;
			font-size: <?= 0.5 * 16 * 0.75 * $multiplier ?>pt;
		}

		.backcover .qr-code .qr-data {
			background-color: white;
			display: block;
			padding: 6pt;
			margin-bottom: 5pt;
			float: right;
		}
		.backcover .qr-code .qr-data img {
			width: 90pt;
		}

		.backcover .isbn-info {
			padding-top: <?= 1 * 16 * 0.75 * $multiplier ?>pt;
			padding-bottom: 0;
			position: relative;
		}

		.backcover .isbn-info img {
			border-radius: 0
		}

		.backcover .isbn-info .small {
			font-size: <?= 0.5 * 16 * 0.75 * $multiplier ?>pt
		}

		.backcover .isbn-info .cover-logo {
			background-color: #fff;
			padding: 0 <?= 0.625 * 16 * 0.75 * $multiplier ?>pt;
		}
		.backcover .version {
			clear: both;
			text-align: right;
			display: block;
			font-size: <?= 0.5 * 16 * 0.75 * $multiplier ?>pt;
			margin-right: 30pt;
		}
	</style>
</head>

<body>
	<!-- printline cover -->
	<div class="book-page" style="position: relative;">
		<div class="printlinecover">
			<div class="title-info">
				<h3 class="book-title" style="font-size: <?= (strlen($book['name']) > 15 ? 1.75 : 2.5) * 16 * $multiplier * 0.75 ?>pt;">
					<?= $this->emoji_lib->parse($book['name']) ?>
				</h3>
				<p class="author-name">Written by <?= $this->emoji_lib->parse($book['author_name'], '15x15') ?></p>
				<p class="publisher-info text-center">
					<?php if (!empty($book['isbn'])) { ?>
					<span style="color:#000000;font-size:10pt;display:block;margin-bottom:10pt;">ISBN: <?=$book['isbn']?></span>
					<?php } ?>
					Content Copyright © <?=date('Y', strtotime($book['date_published']))?> by <?= $this->emoji_lib->parse($book['author_name'], '15x15') ?>. All rights reserved. No part of this book may be used or reproduced in any manner whatsoever without written permission except in case of brief quotations embodied in critical articles and reviews. Any plagiarism found in the content is the sole liability of the Author. The Publisher, YouBooks Edtech (BriBooks) is not fully responsible for the content in part or full and stands completely indemnified from any actions due to the same.<br>
					<span style="color:#000000;font-size:8pt">SKU: <?=$book_code?></span>
				</p>
			</div>
		</div>
		<span class="page-num" style="font-family: Signika;color: black;">
			1
		</span>
	</div>
	<div class="pagebreak"></div>

	<?php foreach ($pages as $key => $page) { ?>
		<?php
		$texts = json_decode(trim($page['texts']), true);
		$text = $texts[0] ?? '';
		$text_boxes = json_decode($page['text_boxes'], true);
		$design = $text_boxes[0];
		?>
		<div class="book-page">
			<div class="page" style="position:relative;">
				<div class="background-image">
					<img src="<?= $page_img_url . $page['image'] ?>" alt="..." />
				</div>
				<div class="page-text" style="position: absolute;width: <?= ($design['p']['w'] + 20) * $multiplier * 0.75 ?>pt; left: <?= ($design['p']['x'] * $multiplier + $bleed) * 0.75 ?>pt; top: <?= ($design['p']['y'] * $multiplier + $bleed) * 0.75 ?>pt; font-family: <?= $page['font_family'] ?>; font-size: <?= $page['font_size'] * $multiplier * 0.75 ?>pt; font-weight: <?= $page['font_weight'] ?>; color: <?= $page['font_color'] ?>;">
					<?php echo html_entity_decode($this->emoji_lib->parse($text, '15x15')); ?>
				</div>
				<span class="page-num" style="font-family: <?= $page['font_family'] ?>;color: <?= $page['font_color'] ?>;">
					<?=2 * $key + 2?>
				</span>
			</div>
		</div>
		<div class="pagebreak"></div>
		<div class="book-page">
			<div class="page" style="position:relative;">
				<div class="background-image right">
					<img src="<?= $page_img_url . $page['image'] ?>" alt="..." />
				</div>
				<span class="page-num" style="font-family: <?= $page['font_family'] ?>;color: <?= $page['font_color'] ?>;">
					<?=2 * $key + 3?>
				</span>
			</div>
		</div>
		<?php if ($key != count($pages) - 1) { ?>
		<div class="pagebreak"></div>
		<?php } ?>
	<?php } ?>
</body>

</html>
