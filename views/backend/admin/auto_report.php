<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('auto_report'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo _l('auto_report');?></h4>

					<form class="required-form" action="<?php echo site_url('admin/auto_report/update'); ?>" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label for="report_range"><?php _el('report_range'); ?></label>
							<select
								class="form-control select2"
								data-toggle="select2"
								name="report_range"
								id="report_range"
							>
								<?php foreach ($ranges as $range) { ?>
								<option value="<?php echo $range; ?>"<?php echo $range == get_settings('auto_report_range') ? ' selected' : ''; ?>><?php echo $range; ?></option>
								<?php } ?>
							</select>
						</div>

						<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l('save'); ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
$('.btn-test').on('click', function() {
	$el = $('#form-sms-test');

	$('#input-message').val($(this).parent().find('textarea').val());

	submitForm($el.attr('action'), new FormData($el[0]), json => {
		json.status === 'success' && success_notify('<?php _el('success'); ?>')
		json.status === 'failure' && error_notify('<?php _el('failure'); ?>')
	});
});
</script>
