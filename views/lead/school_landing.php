<?php require __DIR__ . '/school_landing/header.php'; ?>

<div class="container">
	<div class="col-md-12 fm">
		<div>
			<div class="alert alert-danger error-form hide" role="alert"></div>

			<form action="" id="lead_form" method="post" class="wpcf7-form m-t-20" novalidate="novalidate">
				<input type="hidden" name="api_site_id" value="<?= $site_id; ?>" id="api_site_id">
				<input type="hidden" name="lead_id" value="" id="lead_id">
				<input type="hidden" name="utm_source" value="<?= $utm_source; ?>" id="utm_source">
				<input type="hidden" name="utm_medium" value="<?= $utm_medium; ?>" id="utm_medium">
				<input type="hidden" name="utm_campaign" value="<?= $utm_campaign; ?>" id="utm_campaign">

				<div id="carousel-form" class="carousel slide" data-ride="carousel">
					<ol class="carousel-indicators">
						<li data-target="#carousel-form" class="active"></li>
						<li data-target="#carousel-form"></li>
						<li data-target="#carousel-form"></li>
					</ol>
					<div class="carousel-inner">
						<div class="carousel-item active">
							<?php require __DIR__ . '/school_landing/step_1.php'; ?>
						</div>
						<div class="carousel-item">
							<?php require __DIR__ . '/school_landing/step_2.php'; ?>
						</div>
						<div class="carousel-item">
							<?php require __DIR__ . '/school_landing/step_3.php'; ?>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<?php if (0) { ?>
<div class="container">
	<div class="col-md-12 fm">
		<div>
			<div class="alert alert-danger error-form hide" role="alert"></div>

			<form action="" id="lead_form" method="post" class="m-t-20" novalidate="novalidate">
				<input type="hidden" name="api_site_id" value="<?= $site_id; ?>" id="api_site_id">
				<input type="hidden" name="lead_id" value="" id="lead_id">
				<input type="hidden" name="utm_source" value="<?= $utm_source; ?>" id="utm_source">
				<input type="hidden" name="utm_medium" value="<?= $utm_medium; ?>" id="utm_medium">
				<input type="hidden" name="utm_campaign" value="<?= $utm_campaign; ?>" id="utm_campaign">

				<div class="first-step">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<input
									type="text"
									class="form-control"
									value=""
									id="school_name"
									name="school_name"
									placeholder="<?php _eli('School Name'); ?>"
								/>
								<span class="error school-name-error"></span>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group">
								<input
									type="text"
									class="form-control"
									value=""
									id="student_no"
									name="student_no"
									placeholder="<?php _eli('Number of Student'); ?>"
								/>
								<span class="error student-no-error"></span>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<input
									type="text"
									class="form-control"
									value=""
									id="authorized_person"
									name="authorized_person"
									placeholder="<?php _eli('Name of authorized Person'); ?>"
								/>
								<span class="error authorized-person-error"></span>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<input
									type="email"
									class="form-control"
									value=""
									id="email"
									name="email"
									placeholder="<?php _eli('Email'); ?>"
								/>
								<span class="error email-error"></span>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-prepend" style="width: 35%">
										<select
											class="form-control select2"
											data-toggle="select2"
											name="country_code"
											id="country_code"
										>
											<?php foreach ($this->country_model->get_all_join_sites() as $country) { ?>
											<?php if ($site_code === $country['code']) { ?>
											<option
												value="<?php echo $country['tel_code']; ?>"
												selected
											>
												<?php echo $country['name']; ?>(<?php echo $country['tel_code']; ?>)
											</option>
											<?php } else { ?>
											<option
												value="<?php echo $country['tel_code']; ?>"
											>
												<?php echo $country['name']; ?>(<?php echo $country['tel_code']; ?>)
											</option>
											<?php } ?>
											<?php } ?>
										</select>
									</div>

									<input
										type="tel"
										class="form-control"
										name="mobile"
										id="mobile"
										placeholder="<?php echo _l('mobile'); ?>"
										value=""
										required
									/>
								</div>

								<span class="error parent-mobile-error"></span>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6 m-t-20">
							<div class="form-group">
								<button
									type="button"
									onclick="nextStep()"
									name="button"
									class="btn btn-warning"
									style="padding: 5px 26px;"
								><?php _eli('Next'); ?> »</button>
							</div>
						</div>
					</div>
				</div>

				<div class="otp-area hide">
					<div class="row m-t-20">
						<div class="col-md-6">
							<span class="error otp-error"></span>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<div class="input-group">
									<input
										type="text"
										name="otp"
										value=""
										id="otp"
										size="10"
										class="form-control"
										placeholder="Validation Code"
									/>
									<div class="input-group-append">
										<button
											type="button"
											onclick="verifyOtp()"
											class="btn-verify btn btn-warning"
										><?php _eli('Verify Validation Code'); ?></button>
									</div>
								</div>
							</div>

							<div class="text-right">
								<a
									href="javascript:void(0);"
									class="btn-link" onclick="resendOtp()"
								><?php _eli('Resend Validation Code'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</form>

			<?php if (0) { ?>
			<p class="term"><?php _eli('By registering here, I agree to LeapLearner'); ?>  <a
					href="https://leaplearner.com/privacy-policy/"
				><?php _eli('Terms &amp; Conditions'); ?></a> and <a
					href="https://leaplearner.com/privacy-policy/"
				><?php _eli('Privacy Policy'); ?></a>
			</p>
			<?php } ?>
		</div>
	</div>
</div>
<?php } ?>

<?php require __DIR__ . '/school_landing/footer.php'; ?>
