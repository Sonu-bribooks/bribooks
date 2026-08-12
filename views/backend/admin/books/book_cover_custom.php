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
		.clearfix {
			clear: both;
		}

		@page bpage {
			size: <?= $width ?>pt <?= $height ?>pt;
			margin: 0cm 0cm;
			padding: 0cm 0cm;
		}
		.book-cover {
			position: relative;
			background-color: white;
			padding: 5pt;
		}

		.pagebreak {
			page-break-after: always;
			/* page-break-inside: avoid; */
		}

		.book-page {
			/* page: bpage; */
			width: <?= ($width - $spine_size) / 2 ?>pt;
			height: <?= $height ?>pt;
			/* float: left; */
		}

		.book-page .page {
			width: <?= ($width - $spine_size) / 2 ?>pt;
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
			padding-bottom: 20pt;
			text-align: center;
		}

		.background-image,
		.background-cover,
		.background-cover img {
			width: <?= ($width - $spine_size) / 2 ?>pt;
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
			padding-bottom: <?= (0.01 * 16 * $multiplier + $fc_bleed + 20) * 0.75 ?>pt;
			padding-right: <?= (0.60 * 16 * $multiplier + $fc_bleed + 20) * 0.75 ?>pt;
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

		.book-spine {
			width: <?= $spine_size ?>pt;
			height: <?= $height ?>pt;
			/* float: left; */
		}

		.book-sku {
			color:#fff;
			font-size:8pt;
			position: absolute;
			right: 0;
			margin-top: 10pt;
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

		.backcover .author-img img {
			border-radius: <?= 1 * 16 * 0.75 * $multiplier ?>pt
		}

		.backcover .author-info,
		.backcover .title-info {
			padding-bottom: <?= 1.25 * 16 * 0.75 * $multiplier ?>pt;
		}

		.backcover .author-info-wrapper {
			position: relative;
		}

		.backcover .author-info-wrapper .author-info {}

		.backcover .author-name {
			font-size: <?= 0.875 * 16 * 0.75 * $multiplier ?>pt;
			position: relative;
		}

		.backcover .title-info {
			border-bottom: <?= 1 * 0.75 ?>pt solid rgba(255, 255, 255, 0.2);
		}

		.backcover .title-info .book-title {
			word-break: break-word;
			text-transform: uppercase;
			font-family: Signika;
			margin: 0;
			margin-bottom: <?= .1 * 16 * 0.75 * $multiplier ?>pt;
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
			top: 20pt;
			word-break: break-word;
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
			width: 40%;
			display: inline-block;
		}

		.backcover .isbn-logo img {
			height: <?= 1.375 * 16 * 0.75 * $multiplier ?>pt;
		}

		.backcover .isbn-code, .backcover .qr-code {
			width: 60%;
			display: inline-block;
			text-align: center;
			position: absolute;
			right: 0;
			text-align: right;
			background-color: white;
			height: 70pt;
		}

		.backcover .isbn-code .isbn-data {
			background-color: white;
			padding: 3pt;
			padding-bottom: 2px;
			margin-left: 10px;
			width: 140pt;
			/* float: right; */
		}

		.backcover .isbn-code .isbn-data img {
			width: 100%;
		}

		.backcover .isbn-code .isbn-data p {
			width: 100%;
			background-color: white;
			font-size: <?= 0.4 * 16 * 0.75 * $multiplier ?>pt;
		}

		.isbn-right, .isbn-left {
			position: absolute;
			top: 0;
			background-color: #fff;
		}
		.isbn-left {
			right: 50pt;
			left: 0;
		}
		.isbn-right {
			right: 0;
			padding-top: 10pt;
			padding-bottom: 10pt;
			padding-right: 5pt;
		}
		.isbn-right img {
			width: 45pt;
		}

		.backcover .qr-code .qr-data {
			background-color: white;
			display: block;
			padding: 6pt;
			/* margin-bottom: 5pt; */
			float: right;
		}
		.backcover .qr-code .qr-data img {
			width: 80pt;
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
			/* clear: both; */
			text-align: right;
			display: block;
			font-size: <?= 0.5 * 16 * 0.75 * $multiplier ?>pt;
			line-height: <?= 0.5 * 16 * 0.75 * $multiplier ?>pt;
			margin-right: 70pt;;
			margin-top: 0;
		}
		#book-front-cover {
			position: absolute;
			top: 5pt;
			right: 5pt;
		}
		#book-back-cover {
			position: absolute;
			top: 5pt;
			left: 5pt;
		}
		#book-spine {
			position: absolute;
			top: 5pt;
			left: <?=$book_bleed_width + 5?>pt;
		}
		.mark {
			position: absolute;
			color: black;
			background-color: black;
			width: 2pt;
			height: 2pt;
			z-index: 99;
		}
		.mv {
			width: 5pt;
			height: 1pt;
		}
		.mh {
			width: 1pt;
			height: 5pt;
		}
		.mv1 {
			top: <?=$fc_bleed + 5?>pt;
			left: 0;
		}
		.mv2 {
			top: <?=$fc_bleed + 5?>pt;
			right: 0;
		}
		.mv3 {
			bottom: <?=$fc_bleed + 5?>pt;
			right: 0;
		}
		.mv4 {
			bottom: <?=$fc_bleed + 5?>pt;
			left: 0;
		}
		.mh1 {
			top: 0;
			left: <?=$fc_bleed + 5?>pt;
		}
		.mh2 {
			top: 0;
			right: <?=$fc_bleed + 5?>pt;
		}
		.mh3 {
			bottom: 0pt;
			right: <?=$fc_bleed + 5?>pt;
		}
		.mh4 {
			bottom: 0pt;
			left: <?=$fc_bleed + 5?>pt;
		}
		.ms1 {
			top: 0;
			left: <?=$book_bleed_width + 5?>pt;
		}
		.ms2 {
			bottom: 0pt;
			left: <?=$book_bleed_width + 5?>pt;
		}
	</style>
</head>

<body>
	<div class="book-cover">
		<span class="mark mv mv1"></span>
		<span class="mark mh mh1"></span>
		<span class="mark mv mv2"></span>
		<span class="mark mh mh2"></span>
		<span class="mark mh ms1"></span>

		<!-- back cover -->
		<div class="book-page" id="book-back-cover">
			<div class="page" style="background-color: <?= $book['back_color'] ?>;">
				<div class="backcover">
					<div class="title-info">
						<h3 class="text-light book-title" style="font-size: <?= (strlen($book['name']) > 15 ? 1.15 : 1.75) * 16 * $multiplier * 0.75 ?>pt; font-weight: 600;line-height: <?= (strlen($book['name']) > 15 ? 1.25 : 1.85) * 16 * $multiplier * 0.75 ?>pt;">
							<?= $this->emoji_lib->parse($book['name'], '15x15') ?>
						</h3>
						<p class="text-light author-name" style="font-size: <?= (strlen($book['author_name']) > 10 ? 0.65 : 0.875) * 16 * $multiplier * 0.75 ?>pt;">
							Written by <?= $this->emoji_lib->parse($book['author_name'], '10x10') ?>
							<span class="book-sku">SKU: <?=$book_code?></span>
						</p>
					</div>
					<div class="author-info-wrapper">
						<div class="author-info">
							<div class="author-img">
								<?php
								$imginfo = getimagesize($cover_img_url . $book['author_image']);
								?>
								<img src="<?= $imginfo ? $cover_img_url . $book['author_image'] : base_url('assets/images/defaultAvatar.png') ?>" alt="..." />
								<p class="text-light" style="font-size: <?= (strlen($book['author_name']) > 10 ? 0.5 : 0.75) * 16 * $multiplier * 0.75 ?>pt;">
									<?= $this->emoji_lib->parse($book['author_name'], '10x10') ?>
								</p>
							</div>
							<div class="text-light author-bio">
								<p><?= $this->emoji_lib->parse($book['author_bio'], '10x10') ?></p>
							</div>
						</div>
					</div>
					<div class="back-footer">
						<div class="publisher-info">
							<h6 class="text-light">Published by BriBooks.</h6>
							<p class="text-light">
								BriBooks is the world’s leading children creative writing platform, enabling children to learn creative writing and publish their books on global outlets such as Amazon. Powered by a cutting-edge AI system, BriBooks combines the complete process of ideation, creativity, book writing, publishing, and selling on one single platform.<br><br>
								<b>© BriBooks</b>
							</p>
						</div>
						<div class="isbn-info">
							<div class="isbn-logo">
								<img src="https://www.bribooks.com/assets/images/BriBooks_white.svg" alt="..." />
								<p class="text-light" style="font-size:<?= 0.6 * $multiplier * 16 * 0.75 ?>pt;">www.bribooks.com</p>
								<?php if (empty($book['isbn'])) { ?>
								<p class="text-light" style="font-size:<?= 0.45 * $multiplier * 16 * 0.75 ?>pt;">Preview copy for limited distribution</p>
								<?php } ?>
							</div>
							<?php if ($book['isbn']) { ?>
							<div class="isbn-code">
								<div class="isbn-right">
									<img src="<?= $qrcode ?>" alt="..."/>
								</div>
								<div class="isbn-left">
									<p class="version" style="font-size: <?= 0.4 * $multiplier * 16 * 0.75 ?>pt;color:black;">
										Version <?= $book['version'] ?>
									</p>
									<div class="isbn-data">
										<?=$barcode?>
										<p class="text-center" style="font-size: <?= 0.5 * $multiplier * 16 * 0.75 ?>pt;">
											ISBN <?= $book['isbn'] ? $book['isbn'] : '' ?>
										</p>
									</div>
								</div>
							</div>
							<?php } else { ?>
							<?php if (0) { ?>
							<div class="qr-code">
								<div class="qr-data">
									<img src="<?= $barcode ?>" alt="..."/>
								</div>
								<p class="version" style="font-size: <?= 0.6 * $multiplier * 16 * 0.75 ?>pt;color:white;">
									Version <?= $book['version'] ?>
								</p>
							</div>
							<?php } ?>

							<div class="isbn-code">
								<div class="isbn-right">
									<img src="<?= $qrcode ?>" alt="..."/>
								</div>
								<div class="isbn-left">
									<p class="version" style="font-size: <?= 0.4 * $multiplier * 16 * 0.75 ?>pt;color:black;">
										Version <?= $book['version'] ?>
									</p>
									<div class="isbn-data">
										<?=$barcode?>
										<p class="text-center" style="font-size: <?= 0.5 * $multiplier * 16 * 0.75 ?>pt;">
											SN. <?= $book['unique_id'] ? $book['unique_id'] : '' ?>
										</p>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- back cover -->

		<!-- spine -->
		<div class="book-spine" style="background-color: <?= $book['back_color'] ?>;" id="book-spine">

		</div>
		<!-- spine -->

		<!-- front cover -->
		<div class="book-page" id="book-front-cover">
			<div class="page" style="position:relative;">
				<div class="background-cover">
					<img src="<?= $cover_img_url . $cover_info['image'] ?>" alt="..." />
				</div>
				<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0;">
					<h3 class="book-name" style="color: <?= (!empty($heading_style['color'])) ? $heading_style['color'] : '#000' ?>; position: absolute; top: <?= ((!empty($heading_style['top']) ? $heading_style['top'] : '10') * $multiplier + $fc_bleed) * 0.75 ?>pt; left: <?= ((!empty($heading_style['left']) ? $heading_style['left'] : '10') * $multiplier + $fc_bleed) * 0.75 ?>pt; right: <?= ((!empty($heading_style['right']) ? $heading_style['right'] : '10') * $multiplier + $fc_bleed) * 0.75 ?>pt; font-size: <?= ((!empty($heading_style['fontSize'])) ? $heading_style['fontSize'] : '10') * $multiplier * 0.75 ?>pt; text-align: <?= (!empty($heading_style['textAlign'])) ? $heading_style['textAlign'] : 'left' ?>; font-family: <?= (!empty($heading_style['fontFamily'])) ? $heading_style['fontFamily'] : 'Cilica' ?>;">
						<?= $this->emoji_lib->parse($book['name'], '40x40') ?>
					</h3>
					<div class="book-info" style="position:absolute;">
						<p class="author-name">
							Written by <?= $this->emoji_lib->parse($book['author_name'], '10x10') ?>
						</p>
					</div>
				</div>
			</div>
		</div>

		<span class="mark mv mv3"></span>
		<span class="mark mh mh3"></span>
		<span class="mark mv mv4"></span>
		<span class="mark mh mh4"></span>
		<span class="mark mh ms2"></span>
	</div>
</body>
</html>
