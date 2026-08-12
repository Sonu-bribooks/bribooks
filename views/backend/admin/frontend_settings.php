<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('website_settings'); ?></h4>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-center">
	<div class="col-lg-7">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo _l('website_settings');?></h4>

				<form class="required-form" action="<?php echo site_url('admin/frontend_settings/frontend_update'); ?>" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<label for="about_us"><?php echo _l('about_us'); ?></label>
						<textarea name="about_us" id = "about_us" class="form-control" rows="5"><?php echo get_frontend_settings('about_us'); ?></textarea>
					</div>
					<div class="form-group">
						<label for="terms_and_condition"><?php echo _l('terms_and_condition'); ?></label>
						<textarea name="terms_and_condition" id ="terms_and_condition" class="form-control" rows="5"><?php echo get_frontend_settings('terms_and_condition'); ?></textarea>
					</div>
					<div class="form-group">
						<label for="privacy_policy"><?php echo _l('privacy_policy'); ?></label>
						<textarea name="privacy_policy" id = "privacy_policy" class="form-control" rows="5"><?php echo get_frontend_settings('privacy_policy'); ?></textarea>
					</div>

					<div class="row justify-content-center">
						<div class="col-md-4">
							<button type="button" class="btn btn-primary btn-block" onclick="checkRequiredFields()"><?php echo _l('update_settings'); ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="col-lg-5">
		<div class="card">
			<div class="card-body">
				<div class="col-xl-12">
					<h4 class="mb-3 header-title"><?php echo _l('update_banner_image');?></h4>
					<div class="row justify-content-center">
						<form action="<?php echo site_url('admin/frontend_settings/banner_image_update'); ?>" method="post" enctype="multipart/form-data" style="text-align: center;">
							<div class="form-group mb-2">
								<div class="wrapper-image-preview">
									<div class="box" style="width: 250px;">
										<div class="js--image-preview" style="background-image: url(<?php echo base_url('uploads/system/home-banner.jpg');?>); background-color: #F5F5F5;"></div>
										<div class="upload-options">
											<label for="banner_image" class="btn"> <i class="mdi mdi-camera"></i> <?php echo _l('upload_banner_image'); ?> <br> <small>(2000 X 1335)</small> </label>
											<input id="banner_image" style="visibility:hidden;" type="file" class="image-upload" name="banner_image" accept="image/*">
										</div>
									</div>
								</div>
							</div>
							<button type="submit" class="btn btn-primary btn-block"><?php echo _l('upload_banner_image'); ?></button>
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<div class="col-xl-12">
					<h4 class="mb-3 header-title"><?php echo _l('update_light_logo');?></h4>
					<div class="row justify-content-center">
						<form action="<?php echo site_url('admin/frontend_settings/light_logo'); ?>" method="post" enctype="multipart/form-data" style="text-align: center;">
							<div class="form-group mb-2">
								<div class="wrapper-image-preview">
									<div class="box" style="width: 250px;">
										<div class="js--image-preview" style="background-image: url(<?php echo base_url('uploads/system/logo-light.png'); ?>); background-color: #F5F5F5;"></div>
										<div class="upload-options">
											<label for="light_logo" class="btn"> <i class="mdi mdi-camera"></i> <?php echo _l('upload_light_logo'); ?> <br> <small>(330 X 70)</small> </label>
											<input id="light_logo" style="visibility:hidden;" type="file" class="image-upload" name="light_logo" accept="image/*">
										</div>
									</div>
								</div>
							</div>
							<button type="submit" class="btn btn-primary btn-block"><?php echo _l('upload_light_logo'); ?></button>
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo _l('update_dark_logo');?></h4>
					<div class="row justify-content-center">
						<form action="<?php echo site_url('admin/frontend_settings/dark_logo'); ?>" method="post" enctype="multipart/form-data" style="text-align: center;">
							<div class="form-group mb-2">
								<div class="wrapper-image-preview">
									<div class="box" style="width: 250px;">
										<div class="js--image-preview" style="background-image: url(<?php echo base_url('uploads/system/logo-dark.png'); ?>); background-color: #F5F5F5;"></div>
										<div class="upload-options">
											<label for="dark_logo" class="btn"> <i class="mdi mdi-camera"></i> <?php echo _l('upload_dark_logo'); ?> <br> <small>(330 X 70)</small> </label>
											<input id="dark_logo" style="visibility:hidden;" type="file" class="image-upload" name="dark_logo" accept="image/*">
										</div>
									</div>
								</div>
							</div>
							<button type="submit" class="btn btn-primary btn-block"><?php echo _l('upload_light_logo'); ?></button>
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo _l('update_small_logo');?></h4>
					<div class="row justify-content-center">
						<form action="<?php echo site_url('admin/frontend_settings/small_logo'); ?>" method="post" enctype="multipart/form-data" style="text-align: center;">
							<div class="form-group mb-2">
								<div class="wrapper-image-preview">
									<div class="box" style="width: 250px;">
										<div class="js--image-preview" style="background-image: url(<?php echo base_url('uploads/system/logo-light-sm.png'); ?>); background-color: #F5F5F5;"></div>
										<div class="upload-options">
											<label for="small_logo" class="btn"> <i class="mdi mdi-camera"></i> <?php echo _l('upload_small_logo'); ?> <br> <small>(49 X 58)</small> </label>
											<input id="small_logo" style="visibility:hidden;" type="file" class="image-upload" name="small_logo" accept="image/*">
										</div>
									</div>
								</div>
							</div>
							<button type="submit" class="btn btn-primary btn-block"><?php echo _l('upload_small_logo'); ?></button>
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo _l('update_favicon');?></h4>
					<div class="row justify-content-center">
						<form action="<?php echo site_url('admin/frontend_settings/favicon'); ?>" method="post" enctype="multipart/form-data" style="text-align: center;">
							<div class="form-group mb-2">
								<div class="wrapper-image-preview">
									<div class="box" style="width: 250px;">
										<div class="js--image-preview" style="background-image: url(<?php echo base_url('uploads/system/favicon.png'); ?>); background-color: #F5F5F5;"></div>
										<div class="upload-options">
											<label for="favicon" class="btn"> <i class="mdi mdi-camera"></i> <?php echo _l('upload_favicon'); ?> <br> <small>(90 X 90)</small> </label>
											<input id="favicon" style="visibility:hidden;" type="file" class="image-upload" name="favicon" accept="image/*">
										</div>
									</div>
								</div>
							</div>
							<button type="submit" class="btn btn-primary btn-block"><?php echo _l('upload_favicon'); ?></button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-center">

</div>

<script type="text/javascript">
  $(document).ready(function () {
	initSummerNote(['#about_us', '#terms_and_condition', '#privacy_policy']);
  });
</script>
