<?php
$page_img_url = $this->config->item('s3_base_url') . $this->config->item('s3_themes');
$cover_img_url = $this->config->item('s3_base_url') . 'public/';
$custom_theme_url = $this->config->item('s3_base_url') . $this->config->item('s3_custom_themes');
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
			background-color: white;
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
			filter: grayscale(1);
			/* break-after: page */
		}

		.book-page .page {
			width: <?= $page_width ?>pt;
			height: <?= $height ?>pt;
			position: relative;
			overflow: hidden;
		}
		.page-num {
			position: absolute;
			bottom: 40pt;
			left: 0;
			right: 0;
			display: block;
			text-align: center;
		}

		.background-image,
		.background-cover,
		.background-cover img {
			width: <?= $page_width ?>pt;
			height: <?= $height ?>pt;
		}

		.background-image img {
			width: <?= $page_width * 2 ?>pt;
			height: <?= $height ?>pt;
		}
		.custom-theme img {
			position: absolute;
			top: <?= 10 + $bleed ?>pt;
			left: 10pt;
			width: <?= $page_width - $bleed - 20 ?>pt;
			height: <?= $height - 2 * $bleed - 20?>pt;
		}

		.background-image.right img {
			transform: translateX(-<?= $page_width ?>pt);
		}

		.page-text {
			position: absolute;
			z-index: 9;
		}

		.page-text>p {
			margin: 0;
			line-height: 1.5;
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
			padding: <?= 25 * $multiplier * 0.75 ?>pt <?= 35 * $multiplier * 0.75 ?>pt;
			justify-content: center;
			flex-direction: column;
			display: flex;
			height: 100%;
			background-color: white;
		}

		.printlinecover .book-title {
			font-family: Impact, cursive;
			font-size: <?= 2.5 * 16 * 0.75 * $multiplier ?>pt;
			text-align: center;
			text-transform: uppercase;
			color: #10284b;
			word-break: break-word;
			line-height: 1.2;
			margin: 0;
			margin-bottom: <?= 0.5 * 16 * 0.75 * $multiplier ?>pt;
		}

		.printlinecover .author-name {
			text-align: center;
			font-weight: 400;
			font-size: <?= 1.2 * 16 * 0.75 * $multiplier ?>pt;
			border-bottom: 1pt solid #dddddd;
			padding-bottom: <?= 1 * 16 * 0.75 * $multiplier ?>pt;
			margin-top: <?= 1 * 16 * 0.75 * $multiplier ?>pt;
			line-height: 1;
		}

		.printlinecover .publisher-info {
			font-size: <?= .8 * 16 * 0.75 * $multiplier ?>pt;
			font-weight: 300;
			line-height: 1.5;
			margin: 0;
			margin-top: 10pt;
		}
	</style>
</head>

<body>
	<!-- printline cover -->
	<div class="book-page" style="position: relative;">
		<div class="printlinecover">
			<div class="title-info">
				<h3 class="book-title" style="font-size: <?= (strlen($book['name']) > 20 ? 3.3 : 3.5) * 16 * $multiplier * 0.75 ?>pt;">
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
		<span class="page-num" style="font-family: Signika;color: black;">1</span>
	</div>

	<?php foreach ($pages as $key => $page) { ?>
		<?php
		$texts = json_decode(trim($page['texts']), true);
		$text = $texts[0] ?? '';
		$text_2 = $texts[1] ?? '';
		$text_boxes = json_decode($page['text_boxes'], true);
		$design = $text_boxes[0];
		$design_2 = $text_boxes[1] ?? [];
		$bg_color = '#' . (_color_palette($page_img_url . $page['image'], 1, 5)[0] ?? 'fff');
		?>

		<div class="book-page" style="background-color: <?=$bg_color?>">
			<div class="page" style="position:relative;">
				<div class="background-image">
					<img src="<?= $page_img_url . $page['image'] ?>" alt="..." />
				</div>

				<div class="page-text" style="position: absolute;width: <?= ($design['p']['w'] + 22) * $multiplier * 0.75 ?>pt; left: <?= ($design['p']['x'] * $multiplier + $text_bleed) * 0.75 ?>pt; top: <?= ($design['p']['y'] * $multiplier + $text_bleed) * 0.75 ?>pt; font-family: <?= $page['font_family'] ?>; font-size: <?= $page['font_size'] * $multiplier * 0.75 ?>pt; font-weight: <?= $page['font_weight'] ?>; color: <?= $page['font_color'] ?>;">
					<?php echo html_entity_decode($this->emoji_lib->parse($text, '15x15')); ?>
				</div>

				<span class="page-num" style="font-family: <?= $page['font_family'] ?>;color: <?= $page['font_color'] ?>;">
					<?=2 * $key + 2?>
				</span>
			</div>
		</div>

		<div class="book-page" style="background-color: <?=$bg_color?>">
			<div class="page" style="position:relative;margin-left:<?=$bleed?>pt">
				<div class="background-image right">
					<img src="<?= $page_img_url . $page['image'] ?>" alt="..." />
				</div>

				<?php if (!empty($text_2) && !empty($design_2)) { ?>
				<div class="page-text" style="position: absolute;width: <?= ($design_2['p']['w'] + 22) * $multiplier * 0.75 ?>pt; right: <?= ($design_2['p']['x'] * $multiplier + $text_bleed) * 0.75 ?>pt; top: <?= ($design_2['p']['y'] * $multiplier + $text_bleed) * 0.75 ?>pt; font-family: <?= $page['font_family'] ?>; font-size: <?= $page['font_size'] * $multiplier * 0.75 ?>pt; font-weight: <?= $page['font_weight'] ?>; color: <?= $page['font_color'] ?>;">
					<?php echo html_entity_decode($this->emoji_lib->parse($text_2, '15x15')); ?>
				</div>
				<?php } ?>

				<?php $custom_theme_info = !empty($page['custom_theme_id'])
					? $this->custom_theme_model->get($page['custom_theme_id'])
					: [];
				?>

				<?php if (!empty($custom_theme_info['image'])) { ?>
				<div class="custom-theme">
					<img src="<?= $custom_theme_url . $custom_theme_info['image'] ?>" alt="..." />
				</div>
				<?php } ?>

				<span class="page-num" style="font-family: <?= $page['font_family'] ?>;color: <?= $page['font_color'] ?>;">
					<?=2 * $key + 3?>
				</span>
			</div>
		</div>
	<?php } ?>
</body>

</html>
