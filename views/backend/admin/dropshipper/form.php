<?php $state_ids = !empty($details['state_ids'])? explode(',',$details['state_ids']):[] ?>
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?> </h4>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">

				<h4 class="header-title mb-3"><?php echo $page_title; ?></h4>

				<form class="required-form" action="<?php echo $action ?? ''; ?>" enctype="multipart/form-data" method="post">
					<div id="progressbarwizard">
						<ul class="nav nav-pills nav-justified form-wizard-header mb-3">
							<li class="nav-item">
								<a href="#basic_info" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
									<i class="mdi mdi-face-profile mr-1"></i>
									<span class="d-none d-sm-inline"><?php echo _l('basic_info'); ?></span>
								</a>
							</li>
							<li class="nav-item">
								<a href="#login_credentials" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
									<i class="mdi mdi-lock mr-1"></i>
									<span class="d-none d-sm-inline"><?php echo _l('login_credentials'); ?></span>
								</a>
							</li>
							
							<li class="nav-item">
								<a href="#finish" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
									<i class="mdi mdi-checkbox-marked-circle-outline mr-1"></i>
									<span class="d-none d-sm-inline"><?php echo _l('finish'); ?></span>
								</a>
							</li>
						</ul>
						<div class="tab-content b-0 mb-0">

							<div id="bar" class="progress mb-3" style="height: 7px;">
								<div class="bar progress-bar progress-bar-striped progress-bar-animated bg-success"></div>
							</div>

							<div class="tab-pane" id="basic_info">
								<div class="row">
									<div class="col-12">
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="first_name"><?php echo _l('first_name'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $details['first_name'] ?? ''; ?>" required>
											</div>
										</div>

										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="last_name"><?php echo _l('last_name'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $details['last_name'] ?? ''; ?>" >
											</div>
										</div>

										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="linkedin_link"><?php echo _l('biography'); ?></label>
											<div class="col-md-9">
												<textarea name="biography" id = "summernote-basic" class="form-control"><?php echo $details['biography'] ?? ''; ?></textarea>
											</div>
										</div>

										<div class="form-group row mb-3">
											<label class="col-form-label col-md-3" for="all_states"><?=_l('all_states')?><span class="required">*</span></label>
											<div class="col-md-9">
												<input type="checkbox" class="form-check-input mx-1" name="all_states" id="all_states" value="all" <?= isset($details['state_ids']) && $details['state_ids'] == "all" ? 'checked' : ''?>/>
											</div>
										</div>

										<div class="form-group row mb-3 <?= isset($details['state_ids']) && $details['state_ids'] == "all" ? 'd-none' : ''?>">
											<label class="col-md-3 col-form-label" for="state_ids"><?php echo _l('state'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<select class="form-control select2" data-toggle="select2" name="state_ids[]" id="state_ids" multiple>
													<option value="0">Select States</option>
													<?php foreach ($states as $key => $state) { ?>
														<option
															value="<?= $state['id'] ?>"
															<?= in_array($state['id'], $state_ids) ? 'selected' : '' ?>
														><?= $state['name'] ?></option>
													<?php } ?>
												</select>
											</div>
										</div>

										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="global"><?php echo _l('Global'); ?></label>
											<div class="col-md-9">
											<select
												class="form-control input-filter"
												id="global"
												name="global"
												disabled="disabled"
											>
												<option value="0" selected><?=_l('no')?></option>
												<option value="1"><?=_l('yes')?></option>
											</select>
											</div>
										</div>

										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="sort_order"><?php echo _l('sort_order'); ?><span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo $details['sort_order'] ?? 0; ?>" required>
											</div>
										</div>

										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="limit"><?php echo _l('limit'); ?><span class="required">*</span></label>
											<div class="col-md-9">
												<input type="number" class="form-control" id="limit" name="limit" value="<?php echo $details['limit'] ?? 0; ?>" required>
											</div>
										</div>

										<div class="form-group row mb-3">
											<label class="col-form-label col-md-3" for="bw_printer"><?=_l('bw_printer')?><span class="required">*</span></label>
											<div class="col-md-9">
												<input type="checkbox" class="form-check-input mx-1" name="bw_printer" id="bw_printer" value="1" <?= isset($details['bw_printer']) && !empty($details['bw_printer']) ? 'checked' : ''?>/>
											</div>
										</div>

										<div class="form-group row mb-3 <?= isset($details['bw_limit']) ? '' : 'd-none' ?> ">
											<label class="col-md-3 col-form-label" for="bw_limit"><?php echo _l('bw_limit'); ?></label>
											<div class="col-md-9">
												<input type="number" class="form-control" id="bw_limit" name="bw_limit" value="<?php echo $details['bw_limit'] ?? 0; ?>">
											</div>
										</div>
										
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="colored_limit"><?php echo _l('colored_limit'); ?></label>
											<div class="col-md-9">
												<input type="number" class="form-control" id="colored_limit" name="colored_limit" value="<?php echo $details['colored_limit'] ?? 0; ?>">
											</div>
										</div>

										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="pickup_id"> <?php echo _l('pickup_location'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<select class="form-control select2" data-toggle="select2" name="pickup_id" id="pickup_id">
													<option value=""><?php _el('select_pickup_location'); ?></option>
													<?php foreach ($this->pickup_location_model->get_all()['rows'] ?? [] as $pickup_location) {
														if (($details['pickup_id'] ?? '') == $pickup_location['id']) {
													?>
													<option value="<?php echo $pickup_location['id']; ?>" selected><?php echo $pickup_location['name']; ?></option>
													<?php } else { ?>
													<option value="<?php echo $pickup_location['id']; ?>"><?php echo $pickup_location['name']; ?></option>
													<?php } } ?>
												</select>
											</div>
										</div>

									</div>
								</div>
							</div>

							<div class="tab-pane" id="login_credentials">
								<div class="row">
									<div class="col-12">
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="email"> <?php echo _l('email'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<input type="email" maxlength="50" id="email" name="email" class="form-control" value="<?php echo $details['email'] ?? ''; ?>" required>
											</div>
										</div>
									</div>

									<div class="col-12">
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="alternate_email"> <?php echo _l('alternate_email'); ?> </label>
											<div class="col-md-9">
												<input type="email" maxlength="50" id="alternate_email" name="alternate_email" class="form-control" value="<?php echo $details['alternate_email'] ?? ''; ?>">
											</div>
										</div>
									</div>

									<?php if (0) { ?>
									<div class="col-12">
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="password"><?php echo _l('password'); ?><span class="required">*</span></label>
											<div class="col-md-9">
												<input type="password" id="password" name="password" class="form-control" <?php echo $student_id ? '' : 'required'; ?> />
											</div>
										</div>
									</div>
									<?php } ?>

									<div class="col-12">
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="mobile"> <?php echo _l('mobile'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<input type="tel" id="mobile" name="mobile" class="form-control" value="<?php echo $details['mobile'] ?? ''; ?>" required>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="tab-pane" id="finish">
								<div class="row">
									<div class="col-12">
										<div class="text-center">
											<h2 class="mt-0"><i class="mdi mdi-check-all"></i></h2>
											<h3 class="mt-0"><?php echo _l('thank_you'); ?> !</h3>

											<p class="w-75 mb-2 mx-auto"><?php echo _l('you_are_just_one_click_away'); ?></p>

											<div class="mb-3">
												<button type="button" class="btn btn-primary" onclick="checkRequiredFields()" name="button"><?php echo _l('submit'); ?></button>
											</div>
										</div>
									</div>
								</div>
							</div>

							<ul class="list-inline mb-0 wizard">
								<li class="previous list-inline-item">
									<a href="javascript::" class="btn btn-info"><?php echo _l('previous'); ?></a>
								</li>
								<li class="next list-inline-item float-right">
									<a href="javascript::" class="btn btn-info"><?php echo _l('next'); ?></a>
								</li>
							</ul>
						</div>
					</div>
				</form>

			</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function() {
		$('#bw_printer').on('change', function() {
			if ($(this).is(':checked')) {
				$('#bw_limit').closest('.form-group').removeClass('d-none');
			} else {
				$('#bw_limit').val('<?= $details['bw_limit'] ?? 0 ?>');
				$('#bw_limit').closest('.form-group').addClass('d-none');
			}
		});

		$('#all_states').on('change', function() {
			if ($(this).is(':checked')) {
				$('#state_ids').closest('.form-group').addClass('d-none');
			} else {
				$('#state_ids').closest('.form-group').removeClass('d-none');
			}
		});
	});
</script>