<?php if ($total) { ?>
<b><?php _el('license_used'); ?></b>
<div class="progress" style="height: 20px;">
	<div
		class="progress-bar <?php echo ($total - $used) < 10 ? 'bg-danger' : 'bg-success'; ?>"
		role="progressbar"
		style="width: <?php echo $used / $total * 100; ?>%;"
		aria-valuenow="<?php echo $used; ?>"
		aria-valuemin="0"
		aria-valuemax="<?php echo $total; ?>"
	><?php echo $used; ?></div>
</div>
<div class="text-center"><b><?php echo $used . '/' . $total; ?></b></div>
<?php } ?>
