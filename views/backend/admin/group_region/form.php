
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<a href="<?php echo base_url('admin/group_region'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-hand-pointing-left"></i> <?= _l('back'); ?></a>
				</h4>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?= $page_title ?></h4>

					<form class="required-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="country"><?php echo _l('country'); ?></label>
							<select
									class="form-control select2 filter_select_country"
									data-toggle="select2"
									name="country_id"
									id="country_id"
									required
									data-ajax-url=<?= base_url('admin/ajax_search_country') ?>
								>

									<?php if (!empty($details['country_id'])) { ?>
										<option
											value="<?= $details['country_id'] ?>"
											selected="selected"
										><?= $details['country'] ?></option>
									<?php } ?>
								</select>
						</div>

						<div class="form-group">
							<label for="state"><?php echo _l('state'); ?></label>
							<select
									class="form-control select2 filter_select_state"
									data-toggle="select2"
									name="state_id[]"
									id="state_id"
									required
									data-ajax-url=<?= base_url('admin/ajax_search_state') ?>
									multiple
								>

								<?php if (!empty($details['state']) && !empty($details['state_name'])) {
									$state_ids = explode(',', $details['state']);
									$state_names = explode(',', $details['state_name']);

									foreach ($state_ids as $key => $id) {
										$state_name = isset($state_names[$key]) ? trim($state_names[$key]) : '';
										?>
										<option value="<?= trim($id) ?>" selected="selected"><?= $state_name ?></option>
										<?php
									}
								} ?>
							</select>
						</div>

						<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
$(function() {
	$('.datetimepicker').datetimepicker({

	});

	$('.filter_select_country').select2({
		ajax: {
			url: $(this).data('ajax-url'),
			dataType: "json",
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
		placeholder: "Select",
		minimumInputLength: 3,
	});

	$('.filter_select_state').select2({
		ajax: {
			url: $(this).data('ajax-url'),
			dataType: "json",
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
		placeholder: "Select",
		minimumInputLength: 3,
		multiple: true,

	});
})
</script>
