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
	<div class="col-xl-8">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo _l('email_template_for_' . $site_info['name'] . ' ( ' . $site_info['site_code'] . ' )'); ?></h4>

					<div class="alert alert-info">
						<h4><?php _el('available_variables: '); ?></h4>
						<?php foreach ($variables as $variable) { ?>
							{<?php echo $variable; ?>}
						<?php } ?>
						<br>
						<br>

						<form class="required-form" action="<?php echo site_url('admin/email_test'); ?>" method="post" id="form-email-test" enctype="multipart/form-data">
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
						</form>
						<br>

						<form class="required-form" action="<?php echo site_url('admin/save_email_template/'.$site_info['id']); ?>" method="post" enctype="multipart/form-data">

							<label><?php echo _l('header_image'); ?></label>
							<div class="form-group">
								<div class="d-flex">
									<div class="flex-grow-1">
										<div class="input-group">
											<div class="custom-file">
												<input type="file" class="custom-file-input" name="header_image" id="header_image" onchange="changeTitleOfImageUploader(this)" accept="image/*">
												<label class="custom-file-label ellipsis" for="">Choose File</label>
											</div>
										</div>
									</div>
									<?php if($header_image) { ?>
									<div class="">
										<a href="<?php echo $header_image; ?>" target="_blank" class="btn text-primary"><u><?php echo get_phrase('view'); ?></u></a>
			                      	</div>
			                      	<?php } ?>
								</div>
							</div>

							<label><?php echo _l('footer_image'); ?></label>
							<div class="form-group">
								<div class="d-flex">
									<div class="flex-grow-1">
										<div class="input-group">
											<div class="custom-file">
												<input type="file" class="custom-file-input" name="footer_image" id="footer_image" onchange="changeTitleOfImageUploader(this)" accept="image/*">
												<label class="custom-file-label ellipsis" for="">Choose File</label>
											</div>
										</div>
									</div>
									<?php if($footer_image) { ?>
									<div class="">
										<a href="<?php echo $footer_image; ?>" target="_blank" class="btn text-primary"><u><?php echo get_phrase('view'); ?></u></a>
			                      	</div>
			                      	<?php } ?>
								</div>
							</div>
							<br />

							<?php foreach ($types as $key => $type) { ?>
							<div class="form-group">
								<label><?php echo $key+1 . ') ' . _l($type); ?><span class="required">*</span></label>
								<?php if (!in_array($type, array('email_signup_user_24hrs','email_signup_user_48hrs','email_signup_user_72hrs'))) { ?>
								<button type="button" data-id="<?php echo $type; ?>" class="btn btn-sm btn-info btn-test"><?php _el('test'); ?></button>
								<?php } ?>
	                            <!-- <label for="">
	                        	<?php foreach($lables[$type] ?? [] as $lable) { echo '{'.$lable.'}'; } ?>
	                            </label> -->
								<?php if (0 && in_array($type, array('subject_for_24hrs','subject_for_48hrs','subject_for_72hrs'))) { ?>
								<a href="/admin/send_signup_hour_mail/<?= (int)filter_var($type, FILTER_SANITIZE_NUMBER_INT);?>" class="btn btn-sm btn-info btn-test">Send Now</a>
								<?php } ?>
								<br />
								<input type="hidden" name="email_template[<?php echo $key; ?>][template_id]" value="<?php echo $type; ?>">
								<label><?php echo _l('name'); ?></label>
								<input type="text" class="form-control" name="email_template[<?php echo $key; ?>][name]" value="<?php echo $templates_info[$type]['name'] ?? _l($type); ?>" placeholder="<?php echo _l('name'); ?>">
								<br />
								<label><?php echo _l('subject'); ?></label>
								<input type="text" class="form-control" name="email_template[<?php echo $key; ?>][subject]" value="<?php echo $templates_info[$type]['subject'] ?? _l($type); ?>" placeholder="<?php echo _l('subject'); ?>">
								<br />
								<label><?php echo _l('body'); ?></label>
								<textarea class="form-control" name="email_template[<?php echo $key; ?>][body]" rows="5"><?php echo $templates_info[$type]['body'] ?? get_settings($type); ?></textarea>
								<br />
								<label><?php echo _l('status'); ?></label>
		                        <select class="form-control" name="email_template[<?php echo $key; ?>][status]">
		                            <option value="0" <?= (isset($templates_info[$type]['status']) && $templates_info[$type]['status'] == '0') ? 'selected="selected"' : ''; ?>>Disable</option>
		                            <option value="1" <?= (isset($templates_info[$type]['status']) && $templates_info[$type]['status'] == '1') ? 'selected="selected"' : ''; ?>>Enable</option>
		                        </select>
								<br />
							</div>
							<?php } ?>

							<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l('save'); ?></button>
						</form>
					</div>
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

<script type="text/javascript">
$(function() {
	window['FILEMANAGER'] = '<?php echo base_url('filemanager'); ?>';
});
</script>

<script src="<?php echo base_url('assets/global/filemanager.js?v=1.0.2'); ?>"></script>
