<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('email_template'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo _l('email_template'); ?></h4>

					<div class="alert alert-info">
						<?php _el('available_variables'); ?>
						<?php foreach ($variables as $variable) { ?>
							{<?php echo $variable; ?>}
						<?php } ?>

						<br>
						<br>

						<!-- <form class="required-form" action="<?php echo site_url('admin/email_test'); ?>" method="post" id="form-email-test" enctype="multipart/form-data">
							<input type="hidden" name="message" value="" id="input-message">
							<div class="form-group">
								<label>Test Email</label>
								<input type="text" class="form-control" placeholder="Test Email" name="email" />
							</div>

							<div class="row">
								<?php foreach ($variables as $variable) { ?>
									<div class="form-group col-sm-6">
										<label><?php echo _l($variable); ?></label>
										<input type="text" value="<?php echo $values[$variable]; ?>" class="form-control" placeholder="{<?php echo $variable; ?>}" name="email_data[<?php echo $variable; ?>]" />
									</div>
								<?php } ?>
							</div>
						</form> -->
					</div>

					<form class="required-form" action="<?php echo site_url('admin/schedule_email_template/update'); ?>" method="post" enctype="multipart/form-data">
						<?php foreach ($types as $type) { ?>
							<div class="form-group">
								<label><?php echo _l($type); ?><span class="required">*</span></label>
								<?php
									if (!in_array($type, array('email_signup_user_24hrs','email_signup_user_48hrs','email_signup_user_72hrs'))) { ?>
								<button type="button" data-id="<?php echo $type; ?>" class="btn btn-sm btn-info btn-test"><?php _el('test'); ?></button>
								<?php } ?>
                                <label for=""><?php
                                foreach($lables[$type] ?? [] as $lable){
                                    echo '{'.$lable.'} ';
                                }
                                    ?></label>
								<?php
									if (in_array($type, array('subject_for_24hrs','subject_for_48hrs','subject_for_72hrs'))) { ?>
									<a href="/admin/send_signup_hour_mail/<?= (int)filter_var($type, FILTER_SANITIZE_NUMBER_INT);?>" class="btn btn-sm btn-info btn-test">Send Now</a>
								<?php } ?>

								<?php if(strpos($type, 'subject') !== false){ ?>
								<input type="text" class="form-control" name="email_subject[<?php echo $type; ?>]" value="<?php echo get_settings($type); ?>" placeholder="<?php echo _l($type); ?>">
								<br />
								<?php }else{  ?>
								<textarea class="form-control" name="email_template[<?php echo $type; ?>]" rows="5"><?php echo get_settings($type); ?></textarea>
								<?php } ?>
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
		$el = $('#form-email-test');

		$('#input-message').val($(this).parent().find('textarea').val());

		submitForm($el.attr('action'), new FormData($el[0]), json => {
			json.status === 'success' && success_notify('<?php _el('success'); ?>')
			json.status === 'failure' && error_notify('<?php _el('failure'); ?>')
		});
	});
</script>
<script src=<?= base_url('assets/backend/js/tinymce/tinymce.min.js') ?>></script>

<script type="text/javascript">
	$(document).ready(function() {
		tinymce.init({
			selector: 'textarea',
			branding: false,
			force_br_newlines: true,
			force_p_newlines: false,
			forced_root_block: '',
			plugins: 'lists code emoticons',
			toolbar: 'undo redo | styleselect | bold italic | ' +
				'alignleft aligncenter alignright alignjustify | ' +
				'outdent indent | numlist bullist | emoticons',
		});
	});
</script>
