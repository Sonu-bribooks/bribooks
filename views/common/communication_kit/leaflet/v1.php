<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>
<body>
	<div style="overflow: hidden; position: relative">
		<img src="<?= $base_url . $leaflet ?>" style="width: 100%;" alt="<?= $base_url . $leaflet ?>" />
		<div style="
			position: absolute;
			left:29%;
			z-index: 1;
			color: #c1c1c7;
			font-weight: bold;
			font-size: 35px;
			top: 65%;
			transform: translate(-50%, 0%);">
			<a href="<?= $student_url ?>" target="_blank" style="text-decoration: none;">
				<?= $student_url ?>
			</a>
		</div>
		<div style="
			position: absolute;
			right:0%;
			z-index: 1;
			color: #000;
			font-weight: bold;
			font-size: 30px;
			top: 53.5%;
			transform: translate(52%, 0%);">
			<img src="<?= $qrcode_url ?>" style="width: 35%;" alt="<?= $qrcode_url ?>"/>
		</div>
		<?php 
			$school_font_size = (strlen($school_name) > 45) ? '40px' : '50px';
		?>
		<div style="
			position: absolute;
			left:45%;
			z-index: 1;
			color: #000;
			font-weight: bold;
			font-size: <?= $school_font_size ?>;
			top: 90.7%;
			transform: translate(-50%, 0%);">
				<?= $school_name ?>
			</a>
		</div>
	</div>
</body>
</html>
