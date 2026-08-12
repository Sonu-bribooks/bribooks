<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Author Calendar</title>
	<style>
	@font-face {
		font-family: 'Poppins-Regular';
		font-weight: 400;
		src: url('<?= base_url('assets/global/fonts/Poppins-Regular.ttf') ?>') format("truetype");
	}
	@font-face {
		font-family: 'Poppins-Bold';
		font-weight: 800;
		src: url('<?= base_url('assets/global/fonts/Poppins-Bold.ttf') ?>') format("truetype");
	}
	html {
		margin: 0cm 0cm;
		padding: 0cm 0cm;
	}
	body {
		margin: 0cm 0cm;
		padding: 0cm 0cm;
		width: 100%;
		height: 100%;
		font-family: 'Poppins-Regular';
		background: rgb(83 81 237);
	}
	.front-page, .page {
		width: <?= $width ?>pt;
		height: <?= $height ?>pt;
		position: relative;
		padding: 0mm 0mm;
		margin: 0mm;
	}
	.front-page .info {
		position: absolute;
		top: 20%;
		left: 20pt;
		right: 20pt;
		text-align: center;
	}
	.front-page .cover, .page .cover {
		position: absolute;
		bottom: 15.2%;
		left: 50%;
		transform: translateX(-50%);
		text-align: center;
		width: 53%;
	}
	.front-page .cover .cover-image, .page .cover .cover-image {
		border: 2.25pt solid #F8DA5B;
		border-radius: 7.5pt;
		background: #F8DA5B;
	}
	.front-page .cover .cover-image img, .page .cover .cover-image img  {
		width: 100%;
		border-radius: 7.5pt;
		display: block;
	}
	.front-page .cover .author-image, .page .cover .author-image {
		position: absolute;
		left: 50%;
		transform: translate(-50%, -50%);
		border: 2.25pt solid #F8DA5B;
		border-radius: 50%;
		width: 90pt;
		height: 90pt;
		background: #F8DA5B;
	}
	.front-page .cover .author-image img, .page .cover .author-image img {
		width: 100%;
		border-radius: 50%;
		display: block;
	}
	.front-page .author-name, .front-page .book-name {
		font-size: 18pt;
		font-weight: 800;
		font-family: 'Poppins-Bold';
		margin: 0;
		padding: 0;
		display: block;
		line-height: 10pt;
	}
	.front-page .author-of {
		font-size: 12pt;
		font-weight: 400;
		font-family: 'Poppins-Regular';
		margin: 12pt 0;
		display: block;
		line-height: 6pt;
	}
	.pagebreak {
		page-break-after: always;
		/* page-break-inside: avoid; */
	}
	.background-image img {
		width: <?= $width ?>pt;
		height: <?= $height ?>pt;
	}
	.page .cover {
		width: 28%;
		left: 23%;
		transform: translateX(-50%);
		top: 18.1%;
		bottom: auto;
	}
	.page .cover .author-image {
		width: 70pt;
		height: 70pt;
	}
	.front-page .book-sku {
		color: #fff;
		position: absolute;
		bottom: 30pt;
		left: 30pt;
		font-size: 5pt;
	}
	.page .book-sku {
		color: #fff;
		position: absolute;
		bottom: 30pt;
		right: 30pt;
		font-size: 5pt;
	}
	.page .info {
		position: absolute;
		color: #000;
		width: 100%;
		font-size: 8pt;
		text-align: center;
		transform: translateY(35pt);
	}
	b {
		font-family: 'Poppins-Bold';
		font-weight: 800;
	}
	.bleed {
		position: absolute;
		top: <?= 2 * 14.1732 ?>pt;
		left: <?= 2 * 14.1732 ?>pt;
		right: <?= 2 * 14.1732 ?>pt;
		bottom: <?= 2 * 14.1732 ?>pt;
		border: 0px solid #000;
	}
	</style>
</head>
<body>
	<!-- front page -->
	<div class="front-page">
		<div class="background-image">
			<img src="<?= $front_page ?>" alt="..." />
		</div>
		<div class="info">
			<p class="author-name"><?=ucwords($author_name)?></p>
			<p class="author-of"><?=_li('Author_of')?></p>
			<p class="book-name"><?=ucwords($book_name)?></p>
		</div>
		<div class="cover">
			<div class="cover-image">
				<img src="<?= $cover_image ?>" alt="..." />
			</div>
			<div class="author-image">
				<img src="<?= $author_image ?>" alt="..." />
			</div>
		</div>
		<div class="book-sku"><?=$book_id?></div>
		<div class="bleed"></div>
	</div>
	<div class="pagebreak"></div>
	<!-- page -->
	<?php for ($i = 1; $i < 7; $i++) { ?>
	<div class="page">
		<div class="background-image">
			<img src="<?= ${'page_' . $i} ?>" alt="..." />
		</div>
		<div class="cover">
			<div class="cover-image">
				<img src="<?= $cover_image ?>" alt="..." />
			</div>
			<div class="author-image">
				<img src="<?= $author_image ?>" alt="..." />
			</div>
			<div class="info">
				<?=ucwords($author_name)?>
			</div>
		</div>
		<div class="book-sku"><?=$book_id?></div>
		<div class="bleed"></div>
	</div>
	<?php if ($i !== 6) { ?>
		<div class="pagebreak"></div>
	<?php } ?>
	<?php } ?>
</body>
</html>
