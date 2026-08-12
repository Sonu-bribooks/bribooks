<div>
	<h2><?php _el('worksheet_summary'); ?></h2>
</div>

<div class="summary">
	<div class="header">
		<div class="kcol">Q#</div>
		<div class="kcol"><?php _el('question'); ?></div>
		<div class="kcol"><?php _el('status'); ?></div>
		<div class="kcol"><?php _el('action'); ?></div>
	</div>

	<div class="body">
		<?php foreach ($questions as $key => $question) { ?>
		<div class="krow">
			<div class="kcol"><?php echo $key + 1; ?></div>
			<div class="kcol"><?php echo $question['question']; ?></div>
			<div class="kcol"><?php echo $question['status']; ?></div>
			<div class="kcol">
				<?php if ($question['action']) { ?>
				<button
					type="button"
					class="btn-attempt"
					data-id="<?php echo $key + 1; ?>"
					data-qid="<?php echo $question['id']; ?>"
				><?php echo $question['action']; ?></button>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>

<button type="button" class="btn-complete" onclick="confirm('<?php _el('Are_you_sure'); ?>') ? completeQuiz() : false;"><?php _el('view_score'); ?></button>
<script>
$('.btn-attempt').on('click', function() {
	const fd = new FormData()
	fd.append('index', $(this).data('id'));
	fd.append('question_id', $(this).data('qid'));
	submitForm('<?php echo site_url('assessment/attempt'); ?>', fd, json => {
		processResponse(json)
	})
})
</script>
