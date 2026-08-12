<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
	<title>QrCodes</title>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta content="" name="description" />
	<meta content="" name="author" />
	<link href="https://fonts.googleapis.com/css2?family=Signika:wght@300;400;500;600;700" rel="stylesheet" />
	<style>
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
			padding: 15pt;
			width: 100%;
			height: 100%;
		}
		table {
			width: 100%;
			height: 100%;
		}
		.clearfix {
			clear: both;
		}

		.qrimg {
			width: 370pt;
		}
		.text {
			font-size: 18pt;
			margin-top: 10pt;
		}
		.qrimgfull {
			width: 370pt;
		}

	</style>
</head>

<body>
	<?php foreach ($qrcodes as $key => $value) { ?>
		<img src="<?=$value['image']?>" class="qrimgfull" />
		<p class="text-center text"><?=$value['book']['name']?></p>
	<?php } ?>

	<?php if (0) { ?>
	<div>
		<table>
			<?php for ($key = 0; $key < ceil(count($qrcodes) / 2); $key++) { ?>
			<tr>
				<td>
					<img src="<?=$qrcodes[2 * $key]['image']?>" class="qrimg" />
					<p class="text-center text"><?=$qrcodes[2 * $key]['book']['name']?></p>
				</td>
				<?php if (isset($qrcodes[2 * $key + 1])) { ?>
				<td>
					<img src="<?=$qrcodes[2 * $key + 1]['image']?>" class="qrimg" />
					<p class="text-center text"><?=$qrcodes[2 * $key + 1]['book']['name']?></p>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</table>
	</div>
	<?php } ?>
</body>
</html>
