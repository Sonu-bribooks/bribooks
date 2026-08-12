<!-- start page title -->
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?= $page_title ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
			  <div class="col-lg-12">
				<h4 class="mb-3 header-title"><?= $page_title ?></h4>
				<form class="required-form" action="<?= $action  ?>" id="form" method="post" enctype="multipart/form-data">
					<?php foreach ($fields as $field) { ?>
						<?php if ($field['type'] == 'text') { ?>
							<div class="form-group">
								<label for="<?= $field['key'] ?>">
									<?= $field['label'] ?>
									<?php if (!empty($field['required'])) { ?>
										<span class="required">*</span>
									<?php } ?>
								</label>
								<input
									type="<?= $field['type'] ?>"
									class="form-control"
									id="<?= $field['key'] ?>"
									name="<?= $field['key'] ?>"
									value="<?= $field['value'] ?>"
									<?= $field['required'] ? ' required' : '' ?>
								>
							</div>
						<?php } ?>

						<?php if ($field['type'] == 'date_time') { ?>
							<div class="form-group">
								<label for="<?= $field['key'] ?>">
									<?= $field['label'] ?>
									<?php if (!empty($field['required'])) { ?>
										<span class="required">*</span>
									<?php } ?>
								</label>
								<input
									type="<?= $field['type'] ?>"
									class="form-control datetimepicker"
									id="<?= $field['key'] ?>"
									name="<?= $field['key'] ?>"
									value="<?= $field['value'] ?>"
									<?= $field['required'] ? ' required' : '' ?>
								>
							</div>
						<?php } ?>

						<?php if ($field['type'] == 'number' ) { ?>
							<div class="form-group">
								<label for="<?= $field['key'] ?>">
									<?= $field['label'] ?>
									<?php if (!empty($field['required'])) { ?>
										<span class="required">*</span>
									<?php } ?>
								</label>
								<input
									type="<?= $field['type'] ?>"
									class="form-control"
									id="<?= $field['key'] ?>"
									name="<?= $field['key'] ?>"
									value="<?= $field['value'] ?>"
									min="<?= $field['min'] ?>"
									max="<?= $field['max'] ?>"
									step="<?= $field['step'] ?>"
									<?= $field['required'] ? ' required' : '' ?>
								>
							</div>
						<?php } ?>

						<?php if ($field['type'] == 'select2') { ?>
							<div class="form-group">
								<label for="<?= $field['key'] ?>">
									<?= $field['label'] ?>
									<?php if (!empty($field['required'])) { ?>
										<span class="required">*</span>
									<?php } ?>
								</label>
								<select
									class="form-control select2 filter_select"
									data-toggle="select2"
									name="<?= $field['key'] ?>"
									id="<?= $field['key'] ?>"
									<?= $field['required'] ? ' required' : '' ?>
									data-ajax-url=<?= $field['ajax_url'] ?>
								>
									<option value=""><?= $field['label'] ?></option>
									<?php if (!empty($field['value'])) { ?>
										<option
											value="<?= $field['value']['value'] ?>"
											selected="selected"
										><?= $field['value']['label'] ?></option>
									<?php } ?>
								</select>
							</div>
						<?php } ?>

						<?php if ($field['type'] == 'select') { ?>
							<div class="form-group">
								<label for="<?= $field['key'] ?>">
									<?= $field['label'] ?>
									<?php if (!empty($field['required'])) { ?>
										<span class="required">*</span>
									<?php } ?>
								</label>
								<select
									class="form-control select2"
									data-toggle="select2"
									name="<?= $field['key'] ?>"
									id="<?= $field['key'] ?>"
									<?= $field['required'] ? ' required' : '' ?>
								>
									<option value=""><?= $field['label'] ?></option>

									<?php foreach ($field['options'] ?? [] as $option) { ?>
										<option
											value="<?= $option['value'] ?>"
											<?= $option['value'] == $field['value'] ? ' selected' : '' ?>
										><?= $option['label'] ?></option>
									<?php } ?>
								</select>
							</div>
						<?php } ?>
					<?php } ?>

					<button
						type="submit"
						class="btn btn-primary"
					><?= _l('submit') ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<script>
$(function() {
	$('.filter_select').select2({
		ajax: {
			url: $(this).data('ajax-url'),
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					search: params.term,
				};
			},
			processResults: function (data) {
				return {
					results: data
				};
			},
			cache: true
		},
		placeholder: 'Select',
		minimumInputLength: 3
	});

	$('.datetimepicker').datetimepicker({
		format: 'YYYY/MM/DD H:mm'
	});
})
</script>
