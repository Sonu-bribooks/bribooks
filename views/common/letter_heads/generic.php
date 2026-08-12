<?php $color = '#8D6F64'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
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
		font-family: Arial, sans-serif;
	}
	.emoji {
		font-family:
			"Noto Color Emoji",
			"Segoe UI Emoji",
			"Apple Color Emoji",
			sans-serif;
	}
	</style>
</head>
<body>
	<div>
		<img src="<?= $header ?>" style="width: 100%;" />
	</div>

	<div style="position: relative; padding: 0mm 10mm; margin: 0mm;">
		<?php if (!empty($subheader)) { ?>
			<div style="position: absolute; top: 0mm; right: 10mm; font-size:18px;">
				<b><?= $subheader ?></b>
			</div>
		<?php } ?>

		<?= wrap_emoji($content) ?>
	</div>

	<div style="position: absolute; bottom: 0mm; left: 0mm; right: 0mm;">
		<img src="<?= $footer ?>" style="width: 100%;" />
	</div>
</body>
</html>
