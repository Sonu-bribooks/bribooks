Dear <b><?=$printer?></b>,<br><br>

This is to inform, you have been assigned following on <b><?=date('M j, Y')?></b><br><br>

<?php if ($stats['hardcover']) { ?>
Hard Covers: <b><?=$stats['hardcover']?> copies </b><br><br>
<?php } ?>

<?php if ($stats['paperback']) { ?>
Paper Back: <b><?=$stats['paperback']?> copies</b><br><br>
<?php } ?>

<?php if ($stats['black_white']) { ?>
Black & White: <b><?=$stats['black_white']?> copies</b><br><br>
<?php } ?>

Assignment Code: <b><?=$assignment_code?></b><br><br>

<a href="<?=base_url()?>"><?=_l('login')?></a>
