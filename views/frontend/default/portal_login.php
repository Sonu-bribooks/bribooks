<section class="category-header-area">
	<div class="container-lg">
		<div class="row">
			<div class="col">

				<h1 class="category-name text-center">
					<?php echo _l('portal_login'); ?>
				</h1>
			</div>
		</div>
	</div>
</section>

<section class="category-course-list-area">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-6">
				<div class="user-dashboard-box mt-3">
					<div class="user-dashboard-content w-100 login-form">
						<div class="content-title-box">
							<div class="title"><?php echo _l('login'); ?></div>
							<div class="subtitle"><?php echo _l('provide_your_valid_login_credentials'); ?>.</div>
						</div>

						<div class="content-box">
							<div class="basic-group">
								<div class="form-group" id="portal-code-box">
									<label for="portal-code"><?php echo _l('portal_code'); ?>:</label>
									<input type="text" class="form-control" name="portal_code" id="portal-code" placeholder="<?php echo _l('portal_code'); ?>" value="" required>
								</div>

								<div class="form-group">
									<label for="login-email"><span class="input-field-icon"><i class="fas fa-message"></i></span> <?php echo _l('email'); ?>:</label>
									<div class="input-group">
										<?php if (0) { ?>
										<div class="input-group-prepend" style="min-width: 150px;">
											<select class="form-control select2" data-toggle="select2" name="country_code" id="country_code">
												<?php foreach ($this->country_model->get_all()['rows'] ?? [] as $country) { ?>
												<?php if ($this->config->item('site_country_code') === $country['code']) { ?>
												<option value="<?php echo $country['tel_code']; ?>" selected><?php echo $country['name']; ?>(<?php echo $country['tel_code']; ?>)</option>
												<?php } else { ?>
												<option value="<?php echo $country['tel_code']; ?>"><?php echo $country['name']; ?>(<?php echo $country['tel_code']; ?>)</option>
												<?php } ?>
												<?php } ?>
											</select>
										</div>
										<?php } ?>

										<input type="email" class="form-control" name="email" id="login-email" placeholder="<?php echo _l('email'); ?>" value="" required>

										<div class="input-group-append" id="send-otp-wrapper">
											<button
												class="btn btn-outline-secondary"
												type="button"
												onClick="sendOtp();"
												id="button-send"
											><?php _el('send_validation_code'); ?></button>
										</div>
									</div>
								</div>
								<div class="form-group d-none" id="otp-box">
									<label for="login-otp"><span class="input-field-icon"><i class="fas fa-lock"></i></span> <?php echo _l('validation_code'); ?>:</label>
									<input type="text" class="form-control" name="otp" placeholder="<?php echo _l('validation_code'); ?>" value="" id="login-otp" required>
								</div>

								<div class="content-update-box">
									<button type="button" class="btn d-none" id="button-login" onclick="validateOtp()"><?php echo _l('submit_validation_code'); ?></button>
								</div>
							</div>
						</div>

						<?php if (0) { ?>
						<form action="<?php echo site_url('login/validate_login/user'); ?>" method="post">
							<div class="content-box">
								<div class="basic-group">
									<div class="form-group">
										<label for="login-email"><span class="input-field-icon"><i class="fas fa-envelope"></i></span> <?php echo _l('email'); ?>:</label>
										<input type="email" class="form-control" name = "email" id="login-email" placeholder="<?php echo _l('email'); ?>" value="" required>
									</div>
									<div class="form-group">
										<label for="login-password"><span class="input-field-icon"><i class="fas fa-lock"></i></span> <?php echo _l('password'); ?>:</label>
										<input type="password" class="form-control" name = "password" placeholder="<?php echo _l('password'); ?>" value="" required>
									</div>
								</div>
							</div>
							<div class="content-update-box">
								<button type="submit" class="btn"><?php echo _l('login'); ?></button>
							</div>

							<div class="forgot-pass text-center">
								<span>or</span>
								<a href="javascript::" onclick="toggoleForm('forgot_password')"><?php echo _l('forgot_password'); ?></a>
							</div>
							<div class="account-have text-center">
								<?php echo _l('do_not_have_an_account'); ?>? <a href="javascript::" onclick="toggoleForm('registration')"><?php echo _l('sign_up'); ?></a>
							</div>
						</form>
						<?php } ?>
					</div>

					<?php if (0) { ?>
					<div class="user-dashboard-content w-100 register-form hidden">
						<div class="content-title-box">
							<div class="title"><?php echo _l('registration_form'); ?></div>
							<div class="subtitle"><?php echo _l('sign_up_and_start_learning'); ?>.</div>
						</div>
						<form action="<?php echo site_url('login/register'); ?>" method="post">
							<div class="content-box">
								<div class="basic-group">
									<div class="form-group">
										<label for="first_name"><span class="input-field-icon"><i class="fas fa-user"></i></span> <?php echo _l('first_name'); ?>:</label>
										<input type="text" class="form-control" name = "first_name" id="first_name" placeholder="<?php echo _l('first_name'); ?>" value="" required>
									</div>
									<div class="form-group">
										<label for="last_name"><span class="input-field-icon"><i class="fas fa-user"></i></span> <?php echo _l('last_name'); ?>:</label>
										<input type="text" class="form-control" name = "last_name" id="last_name" placeholder="<?php echo _l('last_name'); ?>" value="" required>
									</div>
									<div class="form-group">
										<label for="registration-email"><span class="input-field-icon"><i class="fas fa-envelope"></i></span> <?php echo _l('email'); ?>:</label>
										<input type="email" class="form-control" name = "email" id="registration-email" placeholder="<?php echo _l('email'); ?>" value="" required>
									</div>
									<div class="form-group">
										<label for="registration-password"><span class="input-field-icon"><i class="fas fa-lock"></i></span> <?php echo _l('password'); ?>:</label>
										<input type="password" class="form-control" name = "password" id="registration-password" placeholder="<?php echo _l('password'); ?>" value="" required>
									</div>
								</div>
							</div>
							<div class="content-update-box">
								<button type="submit" class="btn"><?php echo _l('sign_up'); ?></button>
							</div>
							<div class="account-have text-center">
								<?php echo _l('already_have_an_account'); ?>? <a href="javascript::" onclick="toggoleForm('login')"><?php echo _l('login'); ?></a>
							</div>
						</form>
					</div>

					<div class="user-dashboard-content w-100 forgot-password-form hidden">
						<div class="content-title-box">
							<div class="title"><?php echo _l('forgot_password'); ?></div>
							<div class="subtitle"><?php echo _l('provide_your_email_address_to_get_password'); ?>.</div>
						</div>
						<form action="<?php echo site_url('login/forgot_password/frontend'); ?>" method="post">
							<div class="content-box">
								<div class="basic-group">
									<div class="form-group">
										<label for="forgot-email"><span class="input-field-icon"><i class="fas fa-envelope"></i></span> <?php echo _l('email'); ?>:</label>
										<input type="email" class="form-control" name = "email" id="forgot-email" placeholder="<?php echo _l('email'); ?>" value="" required>
										<small class="form-text text-muted"><?php echo _l('provide_your_email_address_to_get_password'); ?>.</small>
									</div>
								</div>
							</div>
							<div class="content-update-box">
								<button type="submit" class="btn"><?php echo _l('reset_password'); ?></button>
							</div>
							<div class="forgot-pass text-center">
								<?php echo _l('want_to_go_back'); ?>? <a href="javascript::" onclick="toggoleForm('login')"><?php echo _l('login'); ?></a>
							</div>
						</form>
					</div>
						<?php } ?>

				</div>
			</div>
		</div>
	</div>
</section>

<script type="text/javascript">
	function toggoleForm(form_type) {
	if (form_type === 'login') {
		$('.login-form').show();
		$('.forgot-password-form').hide();
		$('.register-form').hide();
	}else if (form_type === 'registration') {
		$('.login-form').hide();
		$('.forgot-password-form').hide();
		$('.register-form').show();
	}else if (form_type === 'forgot_password') {
		$('.login-form').hide();
		$('.forgot-password-form').show();
		$('.register-form').hide();
	}
	}
</script>
<script>
function sendOtp() {
	$('#button-send').prop('disabled', true);

	$.post('<?php echo site_url('login/sendPortalOtp'); ?>', {
		email		: $('#login-email').val(),
		portal_code	: $('#portal-code').val(),
	}, function(json) {
		$('#button-send').prop('disabled', false);

		if (json.success) {
			$('#otp-box').removeClass('d-none');

			$('#send-otp-wrapper').addClass('d-none');
			$('#button-login').removeClass('d-none');

			setTimeout(() => {
				$('#send-otp-wrapper').removeClass('d-none');
			}, 30000);
			success_notify(json.success);
		} else {
			error_notify(json.error);
		}
	});
}

function validateOtp() {
	$.post('<?php echo site_url('login/validatePortalOtp'); ?>', {
		email		: $('#login-email').val(),
		portal_code	: $('#portal-code').val(),
		otp			: $('#login-otp').val()
	}, function(json) {
		if (json.success) {
			success_notify(json.success);
		}

		if (json.redirect) {
			window.location = json.redirect;
		} else {
			json.error && error_notify(json.error);
		}
	});
}

<?php if (0) { ?>
$('#login-otp').on('keyup', function() {
	$el = $(this);

	if ($el.val().length == 6) {

	}
});
<?php } ?>
</script>
