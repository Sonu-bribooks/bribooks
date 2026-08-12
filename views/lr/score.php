<div>
	<h2><?php _el(''); ?></h2>
</div>

<div class="summary score-summary">
	<div class="krow">
		<div class="kcol accuracy">
			<p class="heading"><?php echo $accuracy; ?></p>
			<p class="text"><?php _el('accuracy'); ?></p>
		</div>
		<div class="kcol speed">
			<p class="heading"><?php echo $speed; ?></p>
			<p class="text"><?php _el('speed'); ?></p>
		</div>
		<div class="kcol ranking">
			<p class="heading"><?php echo $ranking; ?></p>
			<p class="text"><?php _el('peer_ranking'); ?></p>
		</div>
	</div>
</div>

<div class="greet">
	<p><?php echo sprintf(_li('Dear %s'), $name); ?></p>
	<p>
		<?php if ((int)$accuracy > 90) { ?>
		<?php echo _li('Congratulation!'); ?>
	<?php } elseif ((int)$accuracy > 75) { ?>
		<?php echo _li('Well Done!'); ?>
		<?php } else { ?>
		<?php echo _li('Well Tried!'); ?>
		<?php } ?>
	</p>
	<p><?php echo _li('We look forward to seeing you grow and improve your'); ?></p>
	<p><?php echo _li('Team Icode'); ?></p>
</div>
