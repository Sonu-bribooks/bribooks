<?php if (!empty($question)) { ?>
<div class="question question<?php echo $question['layout']; ?>">
	<?php if ($question['layout'] == 2) { ?>
	<img src=<?php echo site_url('assets/frontend/default/lr/images/chotu.png'); ?> class="stg">
	<?php } ?>
	<p>
		<label></label>
		<span><?php echo $question['question']; ?></span>
	</p>
	<?php if (!empty($question['question_img'])) { ?>
	<img src ="<?php echo $question['question_img']; ?>"/>
	<?php } ?>
</div>

<?php $w_class = $question['layout'] == 1 ? 'wid50' : 'wid100'; ?>
<?php $r_class = $question['layout'] == 1 ? 'mar30' : 'right'; ?>

<div class="options options<?php echo $question['layout']; ?>">
	<div class="<?php echo $w_class; ?>">
		<label id="First">
			<b><img src=<?php echo site_url('assets/frontend/default/lr/images/24x24-ICON-A.png'); ?>></b>
			<input type="radio" name="answer" value="1" id="ossm">
			<p><?php echo $question['opt_1']; ?></p>

			<?php if (!empty($question['opt_1_img'])) { ?>
			<img src ="<?php echo $question['opt_1_img']; ?>"/>
			<?php } ?>
		</label>
	</div>

	<div class="<?php echo $w_class; ?> <?php echo $r_class; ?>">
		<label id="second">
			<b><img src=<?php echo site_url('assets/frontend/default/lr/images/24x24-ICON-B.png'); ?>></b>
			<input type="radio" name="answer" value="2">
			<p><?php echo $question['opt_2']; ?></p>

			<?php if (!empty($question['opt_2_img'])) { ?>
			<img src ="<?php echo $question['opt_2_img']; ?>"/>
			<?php } ?>
		</label>
	</div>

	<div class="<?php echo $w_class; ?>">
		<label id="third">
			<b><img src=<?php echo site_url('assets/frontend/default/lr/images/24x24-ICON-C.png'); ?>></b>
			<input type="radio" name="answer" value="3">
			<p><?php echo $question['opt_3']; ?></p>

			<?php if (!empty($question['opt_3_img'])) { ?>
			<img src ="<?php echo $question['opt_3_img']; ?>"/>
			<?php } ?>
		</label>
	</div>


	<div class="<?php echo $w_class; ?> <?php echo $r_class; ?>">
		<label id="fourth">
			<b><img src=<?php echo site_url('assets/frontend/default/lr/images/24x24-ICON-D.png'); ?>></b>
			<input type="radio" name="answer" value="4">
			<p><?php echo $question['opt_4']; ?></p>

			<?php if (!empty($question['opt_4_img'])) { ?>
			<img src ="<?php echo $question['opt_4_img']; ?>"/>
			<?php } ?>
		</label>
	</div>

</div>

<input type="hidden" name="question_id" id="question_id" value="<?php echo $question['id']; ?>">
<?php } ?>
