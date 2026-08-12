<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Helvetica:wght@400&display=swap" rel="stylesheet">
	<title>Entry Pass</title>
	<style>
	.box {
		max-width: 480px;
		height: 684.26px;
		text-align: center;
		background-color: #F4F7FF;;
	}
	.logo {
		/* max-height: 135px; */
		max-width: 100%;
		padding-bottom: 30px;
		padding-top: 10px;
		margin-top: 20px;
	}
	.qrcode-box {
		height: 300px;
		width: 300px;
		overflow: hidden;
	}
	.qrcode {
		height: 100%;
		width: 100%;
		max-width: 768px;
		object-fit: cover;
	}
	.location {
		margin: auto;
		padding: auto;
		padding-top: 20px;
		padding-bottom: 30px;
		text-align: center;
	}
	</style>
</head>
<body style="font-family: helvetica;">
	<div>
		<div class="box">
			<img src="<?= $head_logo; ?>" class="logo" />
			<table style="margin: auto; padding: auto; text-align: center; padding-bottom: 10px;" cellspacing="0" cellpadding="0">
				<tbody>
					<tr>
						<td style="border-radius: 20px;">
							<div class="qrcode-box">
								<img src="<?= $qr_code; ?>" class="qrcode" />
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<table class="location" cellspacing="0" cellpadding="0">
				<tbody>
					<tr style="height: 4rem; padding-bottom: 10px;">
						<td width="40">
							<img src="<?= $location; ?>" style="height: 1.8rem; margin-left: 15px;">
						</td>
						<td width="480">
							<div style="font-weight: bold; font-size: 16px; text-align: left; margin-left:10px">APPAREL HOUSE, SECTOR - 44, GURUGRAM</div>
						</td>
					</tr>
				</tbody>
			</table>
			<table style="padding-bottom: 20px;" cellspacing="0" cellpadding="0">
				<tbody>
					<tr style="height: 8rem;">
						<td width="20"></td>
						<td width="260">
							<div style="text-align: left;">
								<span style="text-transform: uppercase; font-size: 16px; padding-bottom: 20px;"><?= $guest_name_1; ?>, <?= $grade; ?>-<?= $section; ?></span><br />
								<span style="text-transform: uppercase; font-size: 16px; padding-bottom: 20px;"><?= $school_name; ?></span><br />
								<span style="text-transform: uppercase; font-size: 14px;"><?= $city . ', ' . $state; ?></span>
							</div>
						</td>
						<td width="120">
							<img src="<?= $guest_1_image; ?>" style="height: 90px; width: 90px; max-width: 90px;" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</body>
</html>
