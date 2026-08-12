<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('system_settings'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo _l('system_settings');?></h4>

					<form class="required-form" action="<?php echo site_url('admin/system_settings/system_update'); ?>" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label for="system_name"><?php echo _l('website_name'); ?><span class="required">*</span></label>
							<input type="text" name="system_name" id="system_name" class="form-control" value="<?php echo get_settings('system_name');  ?>" required>
						</div>

						<div class="form-group">
							<label for="system_title"><?php echo _l('website_title'); ?><span class="required">*</span></label>
							<input type="text" name="system_title" id="system_title" class="form-control" value="<?php echo get_settings('system_title');  ?>" required>
						</div>

						<div class="form-group">
							<label for="website_keywords"><?php echo _l('website_keywords'); ?></label>
							<input type="text" class="form-control bootstrap-tag-input" id="website_keywords" name="website_keywords" data-role="tagsinput" style="width: 100%;" value="<?php echo get_settings('website_keywords');  ?>"/>
						</div>

						<div class="form-group">
							<label for="website_description"><?php echo _l('website_description'); ?></label>
							<textarea name="website_description" id="website_description" class="form-control" rows="5"><?php echo get_settings('website_description');  ?></textarea>
						</div>

						<div class="form-group">
							<label for="author"><?php echo _l('author'); ?></label>
							<input type="text" name="author" id="author" class="form-control" value="<?php echo get_settings('author');  ?>">
						</div>

						<div class="form-group">
							<label for="slogan"><?php echo _l('slogan'); ?><span class="required">*</span></label>
							<input type="text" name="slogan" id="slogan" class="form-control" value="<?php echo get_settings('slogan');  ?>" required>
						</div>

						<div class="form-group">
							<label for="system_email"><?php echo _l('system_email'); ?><span class="required">*</span></label>
							<input type="text" name="system_email" id="system_email" class="form-control" value="<?php echo get_settings('system_email');  ?>" required>
						</div>

						<div class="form-group">
							<label for="address"><?php echo _l('address'); ?></label>
							<textarea name="address" id="address" class="form-control" rows="5"><?php echo get_settings('address');  ?></textarea>
						</div>

						<div class="form-group">
							<label for="phone"><?php echo _l('phone'); ?></label>
							<input type="text" name="phone" id="phone" class="form-control" value="<?php echo get_settings('phone');  ?>">
						</div>

						<div class="form-group">
							<label for="language"><?php echo _l('system_language'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="language" id="language">
								<?php foreach ($languages as $language): ?>
									<option value="<?php echo $language; ?>" <?php if(get_settings('language') == $language) echo 'selected'; ?>><?php echo ucfirst($language); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-group">
							<label for="language"><?php echo _l('student_email_verification'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="student_email_verification" id="student_email_verification">
								<option value="enable" <?php if(get_settings('student_email_verification') == "enable") echo 'selected'; ?>><?php echo _l('enable'); ?></option>
								<option value="disable" <?php if(get_settings('student_email_verification') == "disable") echo 'selected'; ?>><?php echo _l('disable'); ?></option>
							</select>
						</div>

						<div class="form-group">
							<label for="language"><?php echo _li('BriBooks_Shipping'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="bb_shipping" id="bb_shipping">
								<option value="1" <?php if(get_settings('bb_shipping') == 1) echo 'selected'; ?>><?php echo _l('enable'); ?></option>
								<option value="0" <?php if(get_settings('bb_shipping') == 0) echo 'selected'; ?>><?php echo _l('disable'); ?></option>
							</select>
						</div>

						<div class="form-group">
							<label for="footer_link"><?php echo _l('footer_link'); ?></label>
							<input type="text" name="footer_link" id="footer_link" class="form-control" value="<?php echo get_settings('footer_link');  ?>">
						</div>

						<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l('save'); ?></button>
					</form>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->

	<div class="col-xl-5">
		<div class="card">
			<div class="card-body">
				<?php if (0) { ?>
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo _l('update');?></h4>
				</div>
				<?php } ?>
			</div> <!-- end card body-->
		</div>
	</div>
</div>
