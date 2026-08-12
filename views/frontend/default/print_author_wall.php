<?php
$cover_img_url = $this->config->item('cloudfront_url') . 'public/';
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

		html {
			margin: 0cm 0cm;
			padding: 0cm 0cm;
			overflow: hidden;
		}
		body {
			font-family: 'Signika', sans-serif;
			margin: 0cm 0cm;
			padding: 0cm 0cm;
			width: <?= $width ?>pt;
			height: <?= $height ?>pt;
			overflow: hidden;
		}
		.clearfix {
			content: '';
			display: table;
			clear: both;
		}
		.float-left {
			float: left;
		}
		.float-right {
			float: right;
		}
		.relative {
			position: relative;
		}
		.absolute {
			position: absolute;
		}
		.text-center {
			text-align: center;
		}
		.text-light {
			color: #fefefe !important
		}
		.rounded {
			border-radius: 50%;
		}
		.top-0 {
			top: 0;
		}
		.left-0 {
			left: 0;
		}
		.right-0 {
			right: 0;
		}
		.bottom-0 {
			bottom: 0;
		}
		.w-100 {
			width: 100%;
		}
		.full-page {
			width: <?= $width ?>pt;
			height: <?= $height ?>pt;
		}

		.book-cover {
			position: relative;
			background-color: white;
		}
		.book-page {
			width: <?= $image_size['cover_image_w'] / 2 ?>pt;
			height: <?= $image_size['cover_image_h'] ?>pt;
		}

		.book-page .page {
			width: <?= $image_size['cover_image_w'] / 2 ?>pt;
			height: <?= $image_size['cover_image_h'] ?>pt;
			position: relative;
		}
		.book-sku {
			color:#fff;
			font-size: 24pt;
			position: absolute;
			right: 0;
			margin-top: 30pt;
		}
		.backcover {
			padding: <?= (4.689 * 16 * $multiplier + $bleed) * 0.75 ?>pt <?= (6.564 * 16 * $multiplier + $bleed) * 0.75 ?>pt;
		}
		.backcover p {
			font-size: <?= $backcover['author_bio'] ?>pt;
			margin: 0;
			font-weight: 300;
		}
		.backcover .author-img img {
			border-radius: <?= 3 * 16 * 0.75 * $multiplier ?>pt
		}

		.backcover .author-info,
		.backcover .title-info {
			padding-bottom: <?= 3.75 * 16 * 0.75 * $multiplier ?>pt;
		}
		.backcover .author-info-wrapper {
			position: relative;
		}
		.backcover .author-name {
			font-size: <?= 2.625 * 16 * 0.75 * $multiplier ?>pt;
			position: relative;
		}
		.backcover .title-info {
			border-bottom: <?= 3 * 0.75 ?>pt solid rgba(255, 255, 255, 0.2);
		}
		.backcover .title-info .book-title {
			word-break: break-word;
			text-transform: uppercase;
			font-family: Signika;
			margin: 0;
			margin-bottom: <?= .3 * 16 * 0.75 * $multiplier ?>pt;
			font-weight: 700;
		}
		.backcover .author-info,
		.backcover .publisher-info {
			padding-top: <?= 3.75 * 16 * 0.75 * $multiplier ?>pt
		}
		.backcover .author-img {
			margin-right: <?= 3 * 16 * 0.75 * $multiplier ?>pt;
			text-align: center;
			width: 27%;
			display: inline-block;
			vertical-align: top;
		}
		.backcover .author-info {
			padding-bottom: 0;
		}
		.backcover .author-img img {
			width: <?= 15 * 16 * 0.75 * $multiplier ?>pt;
			height: <?= 15 * 16 * 0.75 * $multiplier ?>pt;
			/* margin-top: 0.5pt; */
		}
		.backcover .author-img p {
			font-size: <?= 2.25 * 16 * 0.75 * $multiplier ?>pt;
		}
		.backcover .author-bio {
			padding-top: 60pt;
			width: 65%;
			display: inline-block;
			/* position: absolute;
			top: 10pt; */
			word-break: break-word;
		}
		.backcover .author-bio p {
		}
		.backcover .back-footer {
			padding-top: <?= 1.5 * 16 * 0.75 * $multiplier ?>pt;
		}

		.backcover .isbn-info,
		.backcover .publisher-info {
			border-top: <?= 3 * 0.75 * $multiplier ?>pt solid rgba(255, 255, 255, 0.2);
			padding-bottom: <?= 3.75 * 16 * 0.75 * $multiplier ?>pt
		}

		.backcover .isbn-info,
		.backcover .publisher-info h6 {
			font-family: Signika;
			font-size: <?= 3 * 16 * 0.75 * $multiplier ?>pt;
			margin: 0;
			margin-bottom: <?= 1.5 * 16 * 0.75 * $multiplier ?>pt;
			font-weight: 600;
		}

		.backcover .isbn-logo {
			width: 40%;
			display: inline-block;
		}

		.backcover .isbn-logo img {
			height: <?= 4.125 * 16 * 0.75 * $multiplier ?>pt;
		}

		.backcover .isbn-code, .backcover .qr-code {
			width: 60%;
			display: inline-block;
			text-align: center;
			position: absolute;
			right: 0;
			text-align: right;
			background-color: white;
			height: 170pt;
		}

		.backcover .isbn-code .isbn-data {
			background-color: white;
			padding: 9pt;
			padding-bottom: 6px;
			width: 420pt;
		}

		.backcover .isbn-code .isbn-data img {
			width: 100%;
		}

		.backcover .isbn-code .isbn-data p {
			width: 100%;
			background-color: white;
			font-size: <?= 1.2 * 16 * 0.75 * $multiplier ?>pt;
		}

		.isbn-right, .isbn-left {
			position: absolute;
			top: 0;
			background-color: #fff;
		}
		.isbn-left {
			right: 150pt;
			left: 0;
		}
		.isbn-right {
			right: 0;
			padding-top: 30pt;
			padding-bottom: 30pt;
			padding-right: 15pt;
		}
		.isbn-right img {
			width: 135pt;
		}

		.backcover .qr-code .qr-data {
			background-color: white;
			display: block;
			padding: 18pt;
			/* margin-bottom: 5pt; */
			float: right;
		}
		.backcover .qr-code .qr-data img {
			width: 240pt;
		}

		.backcover .isbn-info {
			padding-top: <?= 3 * 16 * 0.75 * $multiplier ?>pt;
			padding-bottom: 0;
			position: relative;
		}
		.backcover .isbn-info img {
			border-radius: 0
		}
		.backcover .version {
			font-size: <?= 1.5 * 16 * 0.75 * $multiplier ?>pt;
			line-height: <?= 1.5 * 16 * 0.75 * $multiplier ?>pt;
		}
		#book-front-cover {
			position: absolute;
			left: <?= $image_size['cover_image_w'] / 2 + $gap['cover_image']['h'] ?>pt;
		}
		#book-back-cover {
			position: absolute;
			left: <?= $gap['cover_image']['h'] ?>pt;
		}
	</style>
</head>

<body>
<div
	class="relative"
	style="margin: <?= $padding ?>pt; width: <?= $width - 2 * $padding  ?>; height: <?= $height - 2 * $padding  ?>;">
	<div
		class="heading relative clearfix w-100"
	>

		<?php
			$raw_image = $book['author_front_image'];

			if ($book['full_url']) {
				$author_image_src = $raw_image;
			} else {
				$author_image_src = $cover_img_url . $raw_image . '?width=' . ($image_size['author_image'] / $multiplier);
			}
		?>
		<div
			class="rounded float-left"
			style="
				width: <?= $image_size['author_image'] ?>pt;
				height: <?= $image_size['author_image'] ?>pt;
				margin-right: <?= $gap['author_image']['h'] ?>pt;
				background-image: url(<?= $author_image_src ?>);
				background-size: cover;
				background-repeat: no-repeat;
				background-position: center;
			"
		></div>
		<div class="">
			<h3 style="margin: 0; font-size: <?= $font_size['author_name'] ?>pt; text-transform: uppercase;line-height: 0.9;"><?= $book['author_name'] ?></h3>
			<p style="margin: 0; font-size: <?= $font_size['author_of'] ?>pt;">Author of</p>
			<h3 style="margin: 0; font-size: <?= $font_size['book_name'] ?>pt; text-transform: uppercase; line-height: 0.9;"><?= $book['name'] ?></h3>
		</div>
	</div>
	<div class="clearfix"></div>
	<div style="margin-top: <?= $gap['author_image']['h'] ?>pt;">
		<h3 style="margin: 0; font-size: <?= $font_size['about_book'] ?>pt;">About The Book</h3>
		<p style="margin: 0; font-size: <?= $font_size['about_book'] ?>pt; font-weight: 300; line-height: 1;"><?= $book['book_desc'] ?></p>
	</div>

	<div class="book-cover" style="height: <?= $image_size['cover_image_h'] ?>pt; margin-top: <?= $gap['about_book']['v'] ?>pt">
		<!-- back cover -->
		<div class="book-page" id="book-back-cover">
			<div class="page" style="background-color: <?= $book['back_color'] ?>;">
				<div class="backcover">
					<div class="title-info">
						<h3
							class="text-light book-title"
							style="
								font-size: <?= $backcover['book_name'] ?>pt;
								font-weight: 600;
							"
						>
							<?= $book['name'] ?>
						</h3>
						<p
							class="text-light author-name"
							style="
								font-size: <?= $backcover['author_name'] ?>pt;
							"
						>
							Written by <?= $book['author_name'] ?>
							<span class="book-sku">SKU: <?=$book_code?></span>
						</p>
					</div>
					<div class="author-info-wrapper">
						<div class="author-info">
							<div class="author-img">
								<?php
								$imginfo = getimagesize($cover_img_url . $book['author_image']);
								?>
								<img
									src="<?= $imginfo ? $cover_img_url . $book['author_image'] : base_url('assets/images/defaultAvatar.png') ?>"
									alt="..."
								/>
								<p
									class="text-light"
									style="font-size: <?= $backcover['author_image_font'] ?>pt;"
								>
									<?= $book['author_name'] ?>
								</p>
							</div>
							<div
								class="text-light author-bio"
								style="font-size: <?= $backcover['author_bio'] ?>"
							>
								<p><?= $book['author_bio'] ?></p>
							</div>
						</div>
					</div>
					<div class="back-footer">
						<div class="publisher-info">
							<h6 class="text-light">Published by BriBooks.</h6>
							<p class="text-light">
								BriBooks is the world’s leading children creative writing platform, enabling children to learn creative writing and publish their books globally on BriBooks.com. Powered by a cutting-edge AI system, BriBooks combines the complete process of ideation, creativity, book writing, publishing, and selling on one single platform.
								<br><br>
								<b>© BriBooks</b>
							</p>
						</div>
						<div class="isbn-info">
							<div class="isbn-logo">
								<img
									src="https://www.bribooks.com/assets/images/BriBooks_white.svg"
									alt="..."
								/>
								<p class="text-light" style="font-size:<?= 1.8 * $multiplier * 16 * 0.75 ?>pt;">www.bribooks.com</p>
								<?php if (empty($book['isbn'])) { ?>
								<p class="text-light" style="font-size:<?= 1.35 * $multiplier * 16 * 0.75 ?>pt;">Preview copy for limited distribution</p>
								<?php } ?>
							</div>

							<div class="isbn-code">
								<div class="isbn-data">
									<p class="text-center" style="font-size: <?= 1.2 * $multiplier * 16 * 0.75 ?>pt;color:black;">
										Version <?= $book['version'] ?>
									</p>
									<?=$barcode?>
									<p class="text-center" style="font-size: <?= 1.5 * $multiplier * 16 * 0.75 ?>pt;">
										<?= $book['isbn'] ? 'ISBN' : 'SN.' ?> <?= $book['isbn'] ? $book['isbn'] : $book['unique_id'] ?>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- back cover -->

		<!-- front cover -->
		<div class="book-page" id="book-front-cover">
			<div class="page" style="position:relative;">
				<div class="background-cover">
					<img
						src="<?= $cover_img_url . $book['cover_image'] ?>"
						alt="..."
						style="height: <?= $image_size['cover_image_h'] ?>pt;"
					/>
				</div>
			</div>
		</div>
	</div>

	<div
		class="relative"
		style="margin-top: <?= $gap['cover_image']['v'] ?>pt; height: <?= $image_size['qr_image'] ?>pt;"
	>
		<div class="">
			<h4
				class="absolute"
				style="
					margin: 0;
					font-size: <?= $font_size['qr_code'] ?>pt;
					top: 50%;
					transform: translateY(-50%);
				"
			>
				Scan QR<br>to view<br>the book
			</h4>
		</div>
		<img
			class="absolute right-0"
			style="width: <?= $image_size['qr_image'] ?>pt;"
			src="<?= $qrcode ?>"
			alt="..."
		/>
	</div>
	<p
		class="absolute text-right"
		style="font-size: <?= $font_size['tag']?>pt; bottom: 10pt; right: 10pt;"
	>#<?= $book['rank'] ?></p>
</div>
</body>
</html>
