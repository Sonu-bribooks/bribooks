<style type="text/css">
.teacher_course { display: none; }
select.form-control[multiple], select.form-control[size] { max-height: 60px; }
</style>
<?php
	$teacher_data = $this->db->get_where('users', array('id' => $teacher_id))->row_array();
	$social_links = json_decode($teacher_data['social_links'], true);
?>
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?> </h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-3"><?php echo _l('teacher_edit_form'); ?></h4>
				<form class="required-form" action="<?php echo site_url('admin/teachers/edit/'.$teacher_id); ?>" enctype="multipart/form-data" method="post">
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
								<a href="#payment_info" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2">
									<i class="mdi mdi-currency-eur mr-1"></i>
									<span class="d-none d-sm-inline"><?php echo _l('payment_info'); ?></span>
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
												<input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $teacher_data['first_name']; ?>" required>
											</div>
										</div>
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="last_name"><?php echo _l('last_name'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $teacher_data['last_name']; ?>" required>
											</div>
										</div>
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="course"><?php echo _l('course'); ?><span class="required">*</span></label>
											<div class="col-md-9">
												<select class="form-control" name="course_id" id="course">
													<?php foreach ($courses as $course) { ?>
													<option value="<?php echo $course['id']; ?>" <?php echo set_select("course_id", $course['id'], (isset($details['course_id']) && $details['course_id'] == $course['id']) ? true : false); ?>><?php echo $course['title']; ?></option>
													<?php } ?>
												</select>
											</div>
										</div>
										<?php if (0) { ?>
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="course"><?php echo _l('backup_teacher'); ?></label>
											<div class="col-md-9">
												<select class="form-control" name="backup_teacher_id[]" id="backup_teacher" multiple="multiple">
													<?php foreach ($backup_teachers as $teacher) {
														if($teacher['course_id'] && $teacher['id'] != $teacher_id) {
													?>
													<option class="teacher_course <?php echo "teacher_course_".$teacher['course_id']; ?>" value="<?php echo $teacher['id']; ?>" <?php echo set_select("backup_teacher_id", $teacher['course_id'], (!empty($details['backup_teacher_id']) && in_array($teacher['id'], explode(",", $details['backup_teacher_id']))) ? true : false); ?>><?php echo trim($teacher['first_name']." ".$teacher['last_name']); ?></option>
													<?php } } ?>
												</select>
											</div>
										</div>
										<?php } ?>
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="linkedin_link"><?php echo _l('biography'); ?></label>
											<div class="col-md-9">
												<textarea name="biography" id = "summernote-basic" class="form-control"><?php echo $teacher_data['biography']; ?></textarea>
											</div>
										</div>
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="teacher_image"><?php echo _l('teacher_image'); ?></label>
											<div class="col-md-9">
												<div class="d-flex">
												  <div class="">
													  <img class = "rounded-circle img-thumbnail" src="<?php echo $this->user_model->get_user_image_url($teacher_data['id']);?>" alt="" style="height: 50px; width: 50px;">
												  </div>
												  <div class="flex-grow-1 mt-1 pl-3">
													  <div class="input-group">
														  <div class="custom-file">
															  <input type="file" class="custom-file-input" name = "teacher_image" id="teacher_image" onchange="changeTitleOfImageUploader(this)" accept="image/*">
															  <label class="custom-file-label ellipsis" for="teacher_image"><?php echo _l('choose_teacher_image'); ?></label>
														  </div>
													  </div>
												  </div>
											  </div>
											</div>
										</div>
									</div> <!-- end col -->
								</div> <!-- end row -->
							</div>
							<div class="tab-pane" id="login_credentials">
								<div class="row">
									<div class="col-12">
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="mobile"> <?php echo _l('mobile'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<input type="mobile" id="mobile" name="mobile" class="form-control" value="<?php echo $teacher_data['mobile']; ?>" required>
											</div>
										</div>
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="email"> <?php echo _l('email'); ?> <span class="required">*</span> </label>
											<div class="col-md-9">
												<input type="email" id="email" name="email" class="form-control" value="<?php echo $teacher_data['email']; ?>" required>
											</div>
										</div>
									</div> <!-- end col -->
								</div> <!-- end row -->
							</div>
							<div class="tab-pane" id="social_information">
								<div class="row">
									<div class="col-12">
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="facebook_link"> <?php echo _l('facebook'); ?></label>
											<div class="col-md-9">
												<input type="text" id="facebook_link" name="facebook_link" class="form-control" value="<?php echo $social_links['facebook']; ?>">
											</div>
										</div>
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="twitter_link"><?php echo _l('twitter'); ?></label>
											<div class="col-md-9">
												<input type="text" id="twitter_link" name="twitter_link" class="form-control" value="<?php echo $social_links['twitter']; ?>">
											</div>
										</div>
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="linkedin_link"><?php echo _l('linkedin'); ?></label>
											<div class="col-md-9">
												<input type="text" id="linkedin_link" name="linkedin_link" class="form-control" value="<?php echo $social_links['linkedin']; ?>">
											</div>
										</div>
									</div> <!-- end col -->
								</div> <!-- end row -->
							</div>
							<?php
								$paypal_keys = json_decode($teacher_data['paypal_keys'], true);
								$stripe_keys = json_decode($teacher_data['stripe_keys'], true);
						 	?>
							<div class="tab-pane" id="payment_info">
								<div class="row">
									<div class="col-12">
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="facebook_link"> <?php echo _l('paypal_client_id'); ?></label>
											<div class="col-md-9">
												<input type="text" id="paypal_client_id" name="paypal_client_id" class="form-control" value="<?php echo $paypal_keys[0]['production_client_id']; ?>">
												<small><?php echo _l("required_for_teacher"); ?></small>
											</div>
										</div>
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="stripe_public_key"><?php echo _l('stripe_public_key'); ?></label>
											<div class="col-md-9">
												<input type="text" id="stripe_public_key" name="stripe_public_key" class="form-control" value="<?php echo $stripe_keys[0]['public_live_key']; ?>">
												<small><?php echo _l("required_for_teacher"); ?></small>
											</div>
										</div>
										<div class="form-group row mb-3">
											<label class="col-md-3 col-form-label" for="stripe_secret_key"><?php echo _l('stripe_secret_key'); ?></label>
											<div class="col-md-9">
												<input type="text" id="stripe_secret_key" name="stripe_secret_key" class="form-control" value="<?php echo $stripe_keys[0]['secret_live_key']; ?>">
												<small><?php echo _l("required_for_teacher"); ?></small>
											</div>
										</div>
									</div> <!-- end col -->
								</div> <!-- end row -->
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
									</div> <!-- end col -->
								</div> <!-- end row -->
							</div>
							<ul class="list-inline mb-0 wizard">
								<li class="previous list-inline-item">
									<a href="javascript::" class="btn btn-info">Previous</a>
								</li>
								<li class="next list-inline-item float-right">
									<a href="javascript::" class="btn btn-info">Next</a>
								</li>
							</ul>
						</div> <!-- tab-content -->
					</div> <!-- end #progressbarwizard-->
				</form>
			</div> <!-- end card-body -->
		</div> <!-- end card-->
	</div>
</div>
<script type="text/javascript">
var teacher_course_id = "<?php echo $details['course_id']; ?>";
if(teacher_course_id) {
	$(".teacher_course_"+teacher_course_id).css("display", "block");
}

$("#course").on("change", function() {
	$(".teacher_course").css("display", "none");
	$("#backup_teacher option:selected").prop("selected", false);
	var teacher_course_id = $(this).val();
	if(teacher_course_id) {
		$(".teacher_course_"+teacher_course_id).css("display", "block");
	}
});
</script>
