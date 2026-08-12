<meta name="google-signin-client_id" content="752448992196-m67fvv98n9q6923fh0nve58iehkfn1ud.apps.googleusercontent.com">
<script src="https://apis.google.com/js/platform.js" async defer></script>

<section class="category-header-area">
	<div class="container-lg">
		<div class="row">
			<div class="col">

				<h1 class="category-name text-center">
					<?php echo _li('secured_login'); ?>
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
							<div class="title"><?php echo _li('login'); ?></div>
							<div class="subtitle"><?php echo _li('Use Gsuite email id for login'); ?>.</div>
						</div>
						<div class="content-update-box">
							<div class="text-center"><div class="g-signin2" data-width="320" data-height="80" data-longtitle="true" data-theme="dark" data-onsuccess="onSignIn"></div></div>
							<?php if ($this->input->get('kb')) { ?>
							<form action="<?php echo site_url('login/validate_login/user'); ?>" method="post">
								<div class="content-box">
									<div class="basic-group">
										<div class="form-group">
											<input type="email" class="form-control" name = "email" id="login-email" placeholder="<?php echo _l('email'); ?>" value="" required>
										</div>
										<div class="form-group">
											<input type="password" class="form-control" name = "password" placeholder="<?php echo _l('password'); ?>" value="" required>
										</div>
									</div>
								</div>
								<div class="content-update-box">
									<button type="submit" class="btn btn-block"><?php echo _l('login'); ?></button>
								</div>
							</form>
	  					  <?php } ?>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</section>

<script>
function onSignIn(googleUser) {
	$.post('<?= $action_gmail ?>', {token: googleUser.getAuthResponse().id_token}, function(json) {
		if (json.redirect) {
			window.location = json.redirect;
		} else {
			error_notify(json.error);
		}
	});
}

/*$(window).on('load', function() {
	console.log(window.gapi);
	gapi.load('auth2', function() {
		var auth2 = gapi.auth2.getAuthInstance();
		auth2.signOut().then(function () {
			console.log('User signed out.');
		});
	});
});*/
</script>
