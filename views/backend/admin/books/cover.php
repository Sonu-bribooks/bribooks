<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
<style>
	@font-face {
		font-family: 'Impact';
		font-weight: bold;
		src: url('<?=base_url('assets/global/fonts/Impact/impact.ttf')?>') format("truetype");
	}
	@font-face {
		font-family: 'Chalkboard SE';
		font-weight: normal bold;
		src: url('<?=base_url('assets/global/fonts/Chalkboard/chalkboar_font.otf')?>') format("OpenType");
	}
	@font-face {
		font-family: 'Signika';
		font-weight: normal bold;
		src: url('<?=base_url('assets/global/fonts/Signika/Signika-VariableFont_wght.ttf')?>') format("OpenType");
	}
	.text-center {
		text-align: center;
	}
	.text-light {
		color: #fefefe!important
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
		size: <?=$width?>pt <?=$height?>pt;
		margin: 0cm 0cm;
		padding: 0cm 0cm;
	}
	.pagebreak {
		page-break-after: always;
		/* page-break-inside: avoid; */
	}
	.book-page {
		page: bpage;
		width: <?=$width?>pt;
		height: <?=$height?>pt;
		margin: auto;
	}
	.book-page .page {
		width: <?=$width?>pt;
		height: <?=$height?>pt;
		position: relative;
		overflow: hidden;
	}
	.background-image, .background-cover, .background-cover img {
		width: <?=$width?>pt;
		height: <?=$height?>pt;
	}
	.background-image img {
		width: <?=$width * 2?>pt;
		height: <?=$height?>pt;
	}
	.background-image.right img {
		transform: translateX(-<?=$width?>pt);
	}
	.page-text {
		position: absolute;
		z-index: 9;
	}
	.page-text > p {
		margin: 0;
		line-height: <?=1.2 * 16 * 0.75 * $multiplier?>pt;
	}

	.book-name {
		text-transform: uppercase;
		padding: 0;
		margin: 0;
		word-break: keep-all;
		line-height: 0.8;
	}

	.book-info {
		background-color: rgba(255, 255, 255, 0.4);
		padding: <?=0.313 * 16 * 0.75 * $multiplier?>pt <?=0.625 * 16 * 0.75 * $multiplier?>pt;
		/* padding-bottom: <?=(0.313 * 16 * $multiplier + $fc_bleed + 24) * 0.75 ?>pt; */
		/* padding-right:<?=(0.625 * 16 * $multiplier + $fc_bleed + 24) * 0.75 ?>pt; */
		font-family: Signika;
		font-weight: 300;
		position: absolute;
		bottom: 0;
		left: 0;
		right: 0;
	}

	.book-info .author-name {
		font-size: <?=0.875 * 16 * 0.75 * $multiplier?>pt;
		color: rgb(16, 40, 75);
		text-align: right;
		margin: 0;
	}
	.printlinecover {
		padding: <?=(1.563 * 16 * $multiplier) * 0.75 ?>pt <?=(2.188 * 16 * $multiplier) * 0.75 ?>pt;
		position: absolute;
		top: 50%;
		left: 0;
		right: 0;
		transform: translateY(-50%);
	}

	.printlinecover .book-title {
		font-family: Impact, cursive;
		font-size: <?=2.5 * 16 * 0.75 * $multiplier?>pt;
		text-align: center;
		text-transform: uppercase;
		color: #10284b;
		word-break: break-word
	}

	.printlinecover .author-name {
		text-align: center;
		font-weight: 400;
		font-size: <?=1.2 * 16 * 0.75 * $multiplier?>pt;
		padding-bottom: <?=1 * 16 * 0.75 * $multiplier?>pt;
		border-bottom: 1pt solid #dddddd;
	}

	.printlinecover .publisher-info {
		font-size: <?=.8 * 16 * 0.75 * $multiplier?>pt;
		font-weight: 300;
		line-height: <?=1.2 * 16 * 0.75?>pt;
	}
	.backcover {
		padding: <?=(1.563 * 16 * $multiplier + $bleed) * 0.75 ?>pt <?=(2.188 * 16 * $multiplier + $bleed) * 0.75?>pt;
	}

	.backcover p {
		font-size: <?=0.625 * 16 * 0.75 * $multiplier?>pt;
		margin: 0;
		font-weight: 300;
	}

	.backcover img {
		border-radius: <?=1 * 16 * 0.75 * $multiplier?>pt
	}

	.backcover .author-info,.backcover .title-info {
		padding-bottom: <?=1.25 * 16 * 0.75 * $multiplier?>pt
	}

	.backcover .author-info-wrapper {
		position: relative;
	}
	.backcover .author-info-wrapper .author-info {

	}

	.backcover .author-name {
		font-size: <?=0.875 * 16 * 0.75 * $multiplier?>pt
	}

	.backcover .title-info {
		border-bottom: <?=1 * 0.75?>pt solid rgba(255, 255, 255, 0.2);
	}

	.backcover .title-info .book-title {
		word-break: break-word;
		text-transform: uppercase;
		font-family: Signika;
		margin: 0;
		margin-bottom: <?=.5 * 16 * 0.75 * $multiplier?>pt;
		font-weight: 700;
	}

	.backcover .author-info,.backcover .publisher-info {
		padding-top: <?=1.25 * 16 * 0.75 * $multiplier?>pt
	}
	.backcover .author-img {
		margin-right: <?=1 * 16 * 0.75 * $multiplier?>pt;
		text-align: center;
		width: 27%;
		display: inline-block;
		vertical-align: top;
	}
	.backcover .author-img img {
		width: <?=5 * 16 * 0.75 * $multiplier?>pt;
		height: <?=5 * 16 * 0.75 * $multiplier?>pt;
		/* margin-top: 0.5pt; */
	}
	.backcover .author-img p {
		font-size: <?=0.75 * 16 * 0.75 * $multiplier?>pt;
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
		padding-top: <?=0.5 * 16 * 0.75 * $multiplier?>pt;
	}
	.backcover .isbn-info,.backcover .publisher-info {
		border-top: <?=1 * 0.75 * $multiplier?>pt solid rgba(255, 255, 255, 0.2);
		padding-bottom: <?=1.25 * 16 * 0.75 * $multiplier?>pt
	}
	.backcover .isbn-info,.backcover .publisher-info h6 {
		font-family: Signika;
		font-size: <?=1 * 16 * 0.75 * $multiplier?>pt;
		margin: 0;
		margin-bottom: <?=.5 * 16 * 0.75 * $multiplier?>pt;
		font-weight: 600;
	}
	.backcover .isbn-logo {
		width: 50%;
		display: inline-block;
	}
	.backcover .isbn-logo img {
		height: <?=1.675 * 16 * 0.75 * $multiplier?>pt;
	}
	.backcover .isbn-code {
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
		font-size: <?=0.5 * 16 * 0.75 * $multiplier?>pt;
	}
	.backcover .isbn-info {
		padding-top: <?=1 * 16 * 0.75 * $multiplier?>pt;
		padding-bottom: 0;
		position: relative;
	}

	.backcover .isbn-info img {
		border-radius: 0
	}

	.backcover .isbn-info .small {
		font-size: <?=0.5 * 16 * 0.75 * $multiplier?>pt
	}

	.backcover .isbn-info .cover-logo {
		background-color: #fff;
		padding: 0 <?=0.625 * 16 * 0.75 * $multiplier?>pt;
	}

</style>
</head>
<body>
<div class="book-page">
	<div class="page" style="position:relative;">
		<div class="background-cover">
			<img
				src="<?=$cover_img_url . $cover_info['image']?>"
				alt="..."
			/>
		</div>
		<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0;">
			<h3
				class="book-name"
				style="color: <?= (!empty($heading_style['color'])) ? $heading_style['color'] : '#000' ?>; position: absolute; top: <?= ((!empty($heading_style['top']) ? $heading_style['top'] : '10') * $multiplier + $fc_bleed) * 0.75 ?>pt; left: <?= ((!empty($heading_style['left']) ? $heading_style['left'] : '10') * $multiplier + $fc_bleed) * 0.75 ?>pt; right: <?= ((!empty($heading_style['right']) ? $heading_style['right'] : '10') * $multiplier + $fc_bleed) * 0.75 ?>pt; font-size: <?= ((!empty($heading_style['fontSize'])) ? $heading_style['fontSize'] : '10') * $multiplier * 0.75 ?>pt; text-align: <?= (!empty($heading_style['textAlign'])) ? $heading_style['textAlign'] : 'center' ?>; font-family: <?= (!empty($heading_style['fontFamily'])) ? $heading_style['fontFamily'] : 'Cilica' ?>;"
			><?= $this->emoji_lib->parse($book['name'], '15x15') ?></h3>
			<div class="book-info" style="position:absolute;">
				<p class="author-name">Written by <?= $this->emoji_lib->parse($book['author_name'], '10x10') ?></p>
			</div>
		</div>
	</div>
</div>
</body>
</html>
