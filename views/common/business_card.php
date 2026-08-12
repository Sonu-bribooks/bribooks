<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Signika:wght@300..700&display=swap" rel="stylesheet">
</head>
<style>
html {
	margin: 0cm 0cm;
	padding: 0cm 0cm;
}
body {
	margin: 0cm 0cm;
	padding: 0cm 0cm;
	width: 100%;
	height: 100%;
	font-family: 'Poppins';
	line-height: 0.7;
}
.bpage {
	width: <?= $width ?>pt;
	height: <?= $height ?>pt;
	background-repeat: no-repeat;
	background-size: 100%;
}

.bpage .page {
	width: <?= $width ?>pt;
	height: <?= $height ?>pt;
	position: relative;
	overflow: hidden;
}
</style>
<body>
	<!-- card front side -->
	<div class="bpage" style="position: relative; background-image: url(<?= $front_image ?>);">
		<div class="page" style="position: absolute; height: 100%; width: 100%; top: 0; left: 0;">
			<div style="position: absolute; width: 50%; bottom: 0; left:0; top:0; text-align: center;padding-top: 16pt;" >
				<div style="font-size: 6pt; margin-bottom: 3pt;">
					<!-- variable above qr code -->
					<?= 'SKU' . $sku ?>
				</div>
				<!-- qrcode -->
				<img src="<?= $qr_code ?>" alt="<?= $qr_code ?>" style="max-width: 62%; height: auto;"/>
			</div>
			<div
				style="
					width: 50%;
					text-align: center;
					top: 63%;
					right: 0;
					position: absolute;
				"
			>
				<div
					style="
						font-size: <?= strlen($author_name) > 20 ? '60%' : '90%'?>;
						padding: 0 5pt;
						word-wrap: break-word;
						font-weight: 600;
					"
				>
					<!-- variable name -->
					<?= $author_name ?>
				</div>
			</div>
		</div>
	</div>

	<!-- card back side -->
	<div class="bpage" style="position: relative; background-image: url(<?= $inside_image ?>);">
		<div class="page" style="position: absolute; height: 100%; width: 100%; top: 0; left: 0;">
			<div style="position: absolute; width: 50%; text-align: center; padding-top: 4%; left: 0;" >
				<!-- book front cover -->
				<img src="<?= $cover_image ?>" alt="<?= $cover_image ?>" style="max-width: 65%; height: auto;"/>
			</div>
			<div
				style="
					position: absolute;
					right: 0;
					width: 50%;
					text-align: center;
					top: 10%;
					font-weight: 600;
					height: 16%;
				"
			>
				<div
					style="
						font-size: <?= strlen($author_name) >= 20 ? '60%' : '75%'?>;
						padding: 0 5pt;
					"
				>
					<!-- variable name -->
					<?= $author_name ?>
				</div>
			</div>
			<div
				style="
					position: absolute;
					right: 0;
					width: 50%;
					text-align: center;
					top: 25%;
					font-weight: 600;
					height: 10%;
				"
			>
				<div
					style="
						font-size: 70%;
						padding: 0 5pt;
						font-weight: 300;
					"
				>
					<?=_li('Young_Published_Author') ?>
				</div>
				<div
					style="
						font-size: 70%;
						padding: 0 5pt;
						font-weight: 300;
						margin-top: 4pt;
					"
				>
					<?=_li('of') ?>
				</div>
			</div>
			<div
				style="
					position: absolute;
					width: 50%;
					right: 0;
					box-sizing: border-box;
					text-align: center;
					top: 40%;
					font-weight: 600;
					color: #003f63;
					height: 16%;
				"
			>
				<div style="
					font-size: <?= strlen($book_name) >= 20 ? '70%' : '80%'?>;
					padding: 0 5pt;
				">
					<!-- variable book name -->
					“<?= $book_name ?>”
				</div>
			</div>
			<div
				style="
					position: absolute;
					right: 0;
					width: 50%;
					text-align: center;
					top: 54%;
					font-weight: 600;
					height: 16%;
				"
			>
				<div
					style="
						font-size: 60%;
						font-weight: 300;
					"
				>
					<?=_li('Official_Participant_of') ?>
				</div>
			</div>
			<div
				style="
					position: absolute;
					right: 0;
					box-sizing: border-box;
					text-align: center;
					bottom: 8%;
					height: 25%;
					width: 50%;
				"
			>
				<img
					src="<?= $logo ?>"
					style="
						height: 100%;
						max-width: 100%;
					"
				/>
			</div>
		</div>
	</div>
</body>
</html>
