<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('sms_template'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo _l('sms_template');?></h4>

					<div class="alert alert-info">
						<?php _el('available_variables'); ?>
						<?php foreach ($variables as $variable) { ?>
						{<?php echo $variable; ?>}
						<?php } ?>

						<br>
						<br>

						<form class="required-form" action="<?php echo site_url('admin/sms_test'); ?>" method="post" id="form-sms-test" enctype="multipart/form-data">
							<input type="hidden" name="message" value="" id="input-message">
							<div class="form-group">
								<label><?php _el('test_mobile'); ?></label>
								<input type="text" class="form-control" placeholder="<?php _el('test_mobile'); ?>" name="mobile" />
							</div>

							<div class="row">
								<?php foreach ($variables as $variable) { ?>
								<div class="form-group col-sm-6">
									<label><?php echo _l($variable); ?></label>
									<input
										type="text"
										value="<?php echo $values[$variable]; ?>"
										class="form-control"
										placeholder="{<?php echo $variable; ?>}"
										name="sms_data[<?php echo $variable; ?>]"
									/>
								</div>
								<?php } ?>
							</div>
						</form>
					</div>

					<form class="required-form" action="<?php echo site_url('admin/sms_template/update'); ?>" method="post" enctype="multipart/form-data">
						<?php foreach ($types as $type) { ?>
						<div class="form-group">
							<label><?php echo _l($type); ?><span class="required">*</span></label>
							<button type="button" data-id="<?php echo $type; ?>" class="btn btn-sm btn-info btn-test"><?php _el('test'); ?></button>
							<textarea class="form-control" name="sms_template[<?php echo $type; ?>]" rows="5"><?php echo get_settings($type); ?></textarea>
						</div>
						<?php } ?>

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
