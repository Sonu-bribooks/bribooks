<style>


nav > .nav.nav-tabs{

  border: none;
    color:#fff;
    background:#272e38;
    border-radius:0;

}
nav > div a.nav-item.nav-link,
nav > div a.nav-item.nav-link.active
{
  border: none;
    padding: 18px 25px;
    color:#fff;
    background:#272e38;
    border-radius:0;
}

nav > div a.nav-item.nav-link.active:after
 {
  content: "";
  position: relative;
  bottom: -60px;
  left: -10%;
  border: 15px solid transparent;
  border-top-color: #f2903d ;
}
.tab-content{
  background: #fdfdfd;
    line-height: 25px;
    border: 1px solid #ddd;
    border-top:5px solid #f2903d;
    border-bottom:5px solid #f2903d;
    padding:30px 0px;
}

nav > div a.nav-item.nav-link:hover,
nav > div a.nav-item.nav-link:focus,
nav > div a.nav-item.nav-link.active
{
  border: none;
    background: #f2903d;
    color:#fff;
    border-radius:0;
    transition:background 0.20s linear;
}
    
</style>
<section class="category-header-area">
	<div class="container-lg">
		<div class="row">
			<div class="col">

				<h1 class="category-name text-center">
					<?php echo _l('secured_login'); ?>
				</h1>
			</div>
		</div>
	</div>
</section>

<section class="category-course-list-area">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-7">
<!--				<div class="user-dashboard-box mt-3" style="min-height: 400px;">-->
					
                    <div class="row mt-3">
                <div class="col-xs-12 ">
                  <nav>
                    <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                      <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Login with Email</a>
                      <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">Login with School Code</a>
                      
                    </div>
                  </nav>
                  <div class="tab-content py3 px3 px-sm0" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                      <div class="" style="min-height: 400px;">
					<div class="user-dashboard-content w-100">
						
						<div class="content-box">
							<div class="basic-group">
								<div class="form-group">
									<label for="login-email"><span class="input-field-icon"><i class="fas fa-message"></i></span> Email:</label>
									<div class="input-group">
										
										<input type="email" class="form-control" name="email" id="login-email" placeholder="Email" value="" required="">

										<div class="input-group-append" id="send-otp-wrapper">
											<button class="btn btn-outline-secondary" type="button" onclick="sendOtp();">Send Validation Code</button>
										</div>
									</div>
								</div>
								<div class="form-group d-none" id="otp-box">
									<label for="login-otp"><span class="input-field-icon"><i class="fas fa-lock"></i></span> Validation Code:</label>
									<input type="text" class="form-control" name="otp" placeholder="Validation Code" value="" id="login-otp" required="">
								</div>

								<div class="content-update-box">
									<button type="button" class="btn d-none" id="button-login" onclick="validateOtp()">Submit Validation Code</button>
								</div>
							</div>
						</div>

											</div>

					
				</div>
                    </div>
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                      <div class="" style="min-height: 400px;">
					<div class="user-dashboard-content w-100">
						
						<div class="content-box">
							<div class="basic-group">
								<div class="form-group">
									<label for="login-email"><span class="input-field-icon"><i class="fas fa-message"></i></span> School Code:</label>
									<div class="input-group">
										
										<input type="email" class="form-control" name="email" id="login-email" placeholder="School Code" value="" required="">

										<div class="input-group-append" id="send-otp-wrapper">
											<button class="btn btn-outline-secondary" type="button" onclick="sendSchoolOtp();">Send Validation Code</button>
										</div>
									</div>
								</div>
								<div class="form-group d-none" id="otp-box">
									<label for="login-otp"><span class="input-field-icon"><i class="fas fa-lock"></i></span> Validation Code:</label>
									<input type="text" class="form-control" name="otp" placeholder="Validation Code" value="" id="login-otp" required="">
								</div>

								<div class="content-update-box">
									<button type="button" class="btn d-none" id="button-login" onclick="validateOtp()">Submit Validation Code</button>
								</div>
							</div>
						</div>

											</div>

					
				</div>
                    </div>
                    
                  </div>
                
                </div>
              </div>
					

<!--				</div>-->
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
	$el = $('#login-email');
	$.post('<?php echo site_url('icode/sendOtp'); ?>', {email: $el.val(), schoolCode: 0}, function(json) {
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
    function sendSchoolOtp() {
	$el = $('#login-email');
	$.post('<?php echo site_url('icode/sendOtp'); ?>', {email: $el.val(),schoolCode: 1}, function(json) {
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
	$.post('<?php echo site_url('icode/validateOtp'); ?>', {email: $('#login-email').val(), otp: $('#login-otp').val()}, function(json) {
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
