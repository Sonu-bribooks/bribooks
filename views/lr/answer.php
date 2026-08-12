<div class="question question2">
	<img src=<?php echo site_url('assets/frontend/default/lr/images/i1.png'); ?> class="stg">
	<p>
		<label></label>
		<span><?php echo $answer['question']; ?></span>
	</p>

	<?php if (!empty($answer['question_img'])) { ?>
	<img src ="<?php echo $answer['question_img']; ?>"/>
	<?php } ?>

	<?php if ($answer['correct_answer'] == $answer['user_answer']) { ?>
	<h2 style="background-color: #00D700;padding: 10px 0px;color: #fff;width: 100%;">
		<?php _el('correct_answer'); ?>
		<img src="<?php echo $answer['user_answer_icon']; ?>" />
	</h2>
	<?php } else { ?>
	<h2 style="background-color: red;padding: 10px 0px;color: #fff;width: 100%;">
		<?php _el('wrong_answer'); ?>
		<img src="<?php echo $answer['user_answer_icon']; ?>" />
	</h2>
	<?php } ?>
</div>

<div class="options2">
	<div class="wid100 right" style="width:100%">
		<span style="color:#fff"><?php _el('the_correct_answer_is'); ?></span>
		<label>
			<img src="<?php echo $answer['answer_icon']; ?>" />
			<?php echo $answer['answer']; ?>
			<?php if (!empty($answer['answer_img'])) { ?>
			<img src="<?php echo $answer['answer_img']; ?>" />
			<?php } ?>
		</label>
	</div>

	<div class="wid100 right" style="width:100%;">
		<label style="background-color: #FEF200">
			<p><?php echo $answer['explanation_heading']; ?></p>
		</label>
	</div>
	<div class="wid100 right" style="width:100%">
		<label>
			<p style="width: 100%;">
				<?php echo $answer['explanation_details']; ?>
			</p>
		</label>
	</div>
</div>

<input type="hidden" name="question_id" id="ids" value="<?php echo $answer['id']; ?>">
