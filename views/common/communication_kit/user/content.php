<?php include __DIR__ . '/header.php' ?>
<?php if (!empty($header)) { ?>
	<img src="<?= $base_url . $header ?>" style="width:100%;" alt="<?= $base_url . $footer ?>" />
<?php } ?>

<?=$content?>

<?php if (!empty($footer)) { ?>
	<img src="<?= $base_url . $footer ?>" style="width:100%;" alt="<?= $base_url . $footer ?>" />
<?php } ?>

<?php include __DIR__ . '/footer.php' ?>
