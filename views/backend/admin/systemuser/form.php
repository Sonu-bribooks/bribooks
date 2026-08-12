<?php
	$social_links = json_decode($details['social_links'] ?? '', true);
?>
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

				<form class="required-form" action="<?php echo $action; ?>" enctype="multipart/form-data" method="post">
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
								<a href="#social_information" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
									<i class="mdi mdi-wifi mr-1"></i>
									<span class="d-none d-sm-inline"><?php echo _l('social_information'); ?></span>
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
											<label class="col-md-3 col-form-label" for="role_id"><?php echo _l('role_id'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<select class="form-control select2" data-toggle="select2" name="role_id" id="role_id">
													<?php foreach ($roles as $key => $role) { ?>
														<option
															value="<?= $role['id'] ?>"
															<?= ($details['role_id'] ?? 5)  == $role['id'] ? ' selected' : '' ?>
														><?= $role['name'] ?></option>
													<?php } ?>
												</select>
											</div>
										</div>

										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="first_name"><?php echo _l('first_name'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $details['first_name'] ?? ''; ?>" required>
											</div>
										</div>

										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="last_name"><?php echo _l('last_name'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $details['last_name'] ?? ''; ?>" required>
											</div>
										</div>

										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="linkedin_link"><?php echo _l('biography'); ?></label>
											<div class="col-md-9">
												<textarea name="biography" id = "summernote-basic" class="form-control"><?php echo $details['biography'] ?? ''; ?></textarea>
											</div>
										</div>

										<div class="form-group row mb-3 state-list <?= ($details['state_id'] ?? 'd-none') ?>">
											<label class="col-md-3 col-form-label" for="state"><?php echo _l('state'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<input type="hidden" name="country_id" value="1">
												<select class="form-control select2" data-toggle="select2" name="state_id" id="state_id">
													<option value="0">Select States</option>
													<?php foreach ($states as $key => $state) { ?>
														<option
															value="<?= $state['id'] ?>"
															<?= ($details['state_id'] ?? 0)  == $state['id'] ? ' selected' : '' ?>
														><?= $state['name'] ?></option>
													<?php } ?>
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
												<input type="email" id="email" name="email" class="form-control" value="<?php echo $details['email'] ?? ''; ?>" required>
											</div>
										</div>
									</div>

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

							<div class="tab-pane" id="social_information">
								<div class="row">
									<div class="col-12">
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="facebook_link"> <?php echo _l('facebook'); ?></label>
											<div class="col-md-9">
												<input type="text" id="facebook_link" name="social_links[facebook]" class="form-control" value="<?php echo $social_links['facebook'] ?? ''; ?>">
											</div>
										</div>
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="twitter_link"><?php echo _l('twitter'); ?></label>
											<div class="col-md-9">
												<input type="text" id="twitter_link" name="social_links[twitter]" class="form-control" value="<?php echo $social_links['twitter'] ?? ''; ?>">
											</div>
										</div>
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="linkedin_link"><?php echo _l('linkedin'); ?></label>
											<div class="col-md-9">
												<input type="text" id="linkedin_link" name="social_links[linkedin]" class="form-control" value="<?php echo $social_links['linkedin'] ?? ''; ?>">
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
	$(function() {
		$('#role_id').on('select2:select', function (e) {
			if (e.params.data.id == 19) {
				$(".state-list").removeClass('d-none');
			}
			else {
				$(".state-list").addClass('d-none');
			}
		});
	});
</script>