<section class="category-header-area">
	<div class="container-lg">
		<div class="row">
			<div class="col">
				<nav>
					<ol class="breadcrumb">
						<li class="breadcrumb-item">
							<a href="<?php echo site_url('home'); ?>"><i class="fas fa-home"></i></a>
						</li>
						<li class="breadcrumb-item">
							<a href="#">
								<?php echo $page_title; ?>
							</a>
						</li>
					</ol>
				</nav>
				<h1 class="category-name">
					<?php echo _l('register_yourself'); ?>
				</h1>
			</div>
		</div>
	</div>
</section>

<section class="category-course-list-area">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-9">
				<div class="user-dashboard-box mt-3">
					<div class="user-dashboard-content w-100 login-form hidden">
						<div class="content-title-box">
							<div class="title"><?php echo _l('login'); ?></div>
							<div class="subtitle"><?php echo _l('provide_your_valid_login_credentials'); ?>.</div>
						</div>

						<form action="<?php echo site_url('login/validate_login/user'); ?>" method="post">
							<div class="content-box">
								<div class="basic-group">
									<div class="form-group">
										<label for="login-email">
											<span class="input-field-icon"><i class="fas fa-envelope"></i></span>
											<?php echo _l('email'); ?>:
										</label>
										<input
											type="email"
											class="form-control"
											name="email"
											id="login-email"
											placeholder="<?php echo _l('email'); ?>"
											required
										/>
									</div>
									<div class="form-group">
										<label for="login-password">
											<span class="input-field-icon"><i class="fas fa-lock"></i></span>
											<?php echo _l('password'); ?>:
										</label>
										<input
											type="password"
											class="form-control"
											name="password"
											placeholder="<?php echo _l('password'); ?>"
											required
										/>
									</div>
								</div>
							</div>

							<div class="content-update-box">
								<button type="submit" class="btn"><?php echo _l('login'); ?></button>
							</div>

							<div class="forgot-pass text-center">
								<span>or</span>
								<a
									href="javascript::"
									onclick="toggoleForm('forgot_password')"
								>
									<?php echo _l('forgot_password'); ?>
								</a>
							</div>

							<div class="account-have text-center">
								<?php echo _l('do_not_have_an_account'); ?>?
								<a href="javascript::" onclick="toggoleForm('registration')"><?php echo _l('sign_up'); ?></a>
							</div>
						</form>
					</div>

					<div class="user-dashboard-content w-100 register-form">
						<div class="content-title-box">
							<div class="title"><?php echo _l('registration_form'); ?></div>
							<div class="subtitle"><?php echo _l('sign_up_and_start_learning'); ?>.</div>
						</div>

						<form action="<?php echo site_url('login/register'); ?>" method="post" id="form-register">
							<div class="content-box">
								<div class="basic-group">
									<div class="form-group">
										<label for="first_name">
											<span class="input-field-icon"><i class="fas fa-user"></i></span>
											<?php echo _l('first_name'); ?>:
										</label>
										<input
											type="text"
											class="form-control"
											name="first_name"
											id="first_name"
											placeholder="<?php echo _l('first_name'); ?>"
											value=""
											required
										/>
									</div>

									<div class="form-group">
										<label for="last_name">
											<span class="input-field-icon"><i class="fas fa-user"></i></span>
											<?php echo _l('last_name'); ?>:
										</label>
										<input
											type="text"
											class="form-control"
											name = "last_name"
											id="last_name"
											placeholder="<?php echo _l('last_name'); ?>"
											value=""
											required
										/>
									</div>

									<div class="form-group">
										<label for="registration-email">
											<span class="input-field-icon"><i class="fas fa-envelope"></i></span>
											<?php echo _l('email'); ?>:
										</label>
										<input
											type="email"
											class="form-control"
											name="email"
											id="registration-email"
											placeholder="<?php echo _l('email'); ?>"
											value=""
											required
										/>
									</div>

									<div class="form-group">
										<label for="registration-mobile">
											<span class="input-field-icon"><i class="fas fa-mobile"></i></span>
											<?php echo _l('mobile'); ?>:
										</label>
										<input
											type="tel"
											class="form-control"
											name="mobile"
											id="registration-mobile"
											placeholder="<?php echo _l('mobile'); ?>"
											value=""
											required
										/>
									</div>

									<div class="form-group">
										<label for="registration-mode">
											<span class="input-field-icon"><i class="fas fa-building"></i></span>
											<?php echo _l('mode_of_learning'); ?>:
										</label>
										<select
											class="form-control select2"
											data-toggle="select2"
											name="mode"
											id="mode"
										>
											<option value="online">
												<?php echo _l('online'); ?>
											</option>
											<option value="home">
												<?php echo _l('home_tutor'); ?>
											</option>
											<option value="center">
												<?php echo _l('learning_center'); ?>
											</option>
										</select>
									</div>

									<div class="form-group">
										<label for="registration-password">
											<span class="input-field-icon"><i class="fas fa-lock"></i></span>
											<?php echo _l('password'); ?>:
										</label>
										<input
											type="password"
											class="form-control"
											name="password"
											id="registration-password"
											placeholder="<?php echo _l('password'); ?>"
											value=""
											required
										/>
									</div>
								</div>
							</div>
							<div class="content-update-box">
								<button type="submit" class="btn"><?php echo _l('sign_up'); ?></button>
							</div>
							<div class="account-have text-center">
								<?php echo _l('already_have_an_account'); ?>?
								<a href="javascript::" onclick="toggoleForm('login')"><?php echo _l('login'); ?></a>
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
										<label for="forgot-email">
											<span class="input-field-icon"><i class="fas fa-envelope"></i></span>
											<?php echo _l('email'); ?>:
										</label>
										<input
											type="email"
											class="form-control"
											name="email"
											id="forgot-email"
											placeholder="<?php echo _l('email'); ?>"
											value=""
											required
										/>
										<small class="form-text text-muted">
											<?php echo _l('provide_your_email_address_to_get_password'); ?>.
										</small>
									</div>
								</div>
							</div>
							<div class="content-update-box">
								<button type="submit" class="btn"><?php echo _l('reset_password'); ?></button>
							</div>
							<div class="forgot-pass text-center">
								<?php echo _l('want_to_go_back'); ?>?
								<a href="javascript::" onclick="toggoleForm('login')"><?php echo _l('login'); ?></a>
							</div>
						</form>
					</div>
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
$(function() {
	$('#form-register').on('submit', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$el = $(this);

		$.ajax({
			url: $el.attr('action'),
			type: 'post',
			dataType: 'json',
			data: new FormData($el[0]),
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function() {
			},
			complete: function() {
			},
			success: function(json) {
				console.log(json);
				if (json.success) {
					toastr.success(`json.success`);
					json.redirect && setTimeout(() => (location = json.redirect), 3000);
				}

				if (json.error) {
					toastr.error(`${json.error}`, 'Error', {timeOut: 15000});
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	})
});
</script>
