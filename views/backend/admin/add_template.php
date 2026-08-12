<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js"></script>
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
						<?php foreach ($variables ?? [] as $variable) { ?>

						<?php } ?>

						<br>
						<br>

						<form class="required-form" action="<?php echo site_url('admin/add_template/add'); ?>" method="post" id="form-email-test" enctype="multipart/form-data">
							<div class="form-group">
								<label><?php _el('site'); ?><span class="required">*</span></label>
								<select name="site_id" class="form-control select2" data-toggle="select2" id="filter_site_id" data-site="<?=$site_id?>"></select>
							</div>

							<div class="form-group">
								<label>Enter Template name</label>
								<input type="text" required class="form-control" placeholder="Email" name="name" />
							</div>
							<div class="form-group">
								<label>Enter Subject</label>
								<input type="text" required class="form-control" placeholder="Subject" name="subject" />
							</div>
							<div class="form-group">
								<label>Enter template_id</label>
								<input type="text" required class="form-control" placeholder="template_id" name="template_id" />
							</div>
							<div class="form-group">
								<label>Enter Content</label>
								<textarea class="form-control" required name="body"> </textarea>
							</div>

							<div class="form-group">
								<input type="radio" required name="type" id="" value="0"> <label for="html">no-repeat</label><br>
								<input type="radio" required name="type" id="" value="1"> <label for="html">daily</label><br>
								<input type="radio" required name="type" id="" value="2"> <label for="html">weekly</label><br>
							</div>

							<div class="form-group">
								<input type="text" name="shedule" class="form-control date" placeholder="Pick the  dates">
							</div>

					</div>

					<div class="form-group">
						<button class="btn btn-primary float-right">Save</button>
					</div>
					</form>

				</div>

				<div>
					<?php
					foreach ($templates['rows'] as $value) {
						echo '<form class="required-form" action=" ' . site_url("admin/add_template/update/".$value['id']) . '" method="post" id="form-email-test" enctype="multipart/form-data">
							<div class="form-group">
								<label>Enter Template name</label>
								<input type="text" required class="form-control" placeholder="Email" name="name" value="' . $value['name'] . '" />
							</div>
							<div class="form-group">
								<label>Enter Subject</label>
								<input type="text" required class="form-control" placeholder="Subject" name="subject" value="' . $value['subject'] . '" />
							</div>
							<div class="form-group">
								<label>Enter template_id</label>
								<input type="text" required class="form-control" placeholder="template_id" name="template_id" value="' . $value['template_id'] . '" />
							</div>
							<div class="form-group">
								<label>Enter Content</label>
								<textarea class="form-control" required name="body"> ' . $value['body'] . ' </textarea>
							</div>

							<div class="form-group">
								<input type="radio" required name="type" value="0"> <label for="html">no-repeat</label><br>
								<input type="radio" required name="type" value="1"> <label for="html">daily</label><br>
								<input type="radio" required name="type" value="2"> <label for="html">weekly</label><br>
							</div>

							<div class="form-group">
								<input type="text" class="form-control  date" name="shedule" placeholder="Pick the date" value="'.$value['shedule'].'">
							</div>

							<div class="form-group">
								<button class="btn btn-primary float-right">Save</button>
							</div>
						</form>';
					}

					?>
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
	$('.date').datepicker({
		multidate: true,
		format: 'dd-mm-yyyy'
	});
</script>
