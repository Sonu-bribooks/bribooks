<label><?php _el('select_grade'); ?></label>
<select id="select-grade">
	<?php foreach (LEAD_GRADES as $grade) { ?>
	<option
		value="<?php echo $grade; ?>"<?php echo $this->session->userdata('quiz_grade') == $grade ? ' selected' : ''; ?>
	><?php echo $grade; ?></option>
	<?php } ?>
</select>
<script>
$('#select-grade').on('change', function() {
	window.location = '<?php echo base_url('assessment/changeGrade?grade='); ?>' + $(this).val();
});
</script>
