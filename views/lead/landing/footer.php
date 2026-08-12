<?php if (0) { ?>
<script type="text/javascript">
AppleID.auth.init({
	clientId : '[CLIENT_ID]',
	scope : '[SCOPES]',
	redirectURI : '[REDIRECT_URI]',
	state : '[STATE]',
	nonce : '[NONCE]',
	usePopup : true
});

//Listen for authorization success
document.addEventListener('AppleIDSignInOnSuccess', (data) => {
	//handle successful response
	console.log(data)
});
//Listen for authorization failures
document.addEventListener('AppleIDSignInOnFailure', (error) => {
	//handle error.
	console.log(error)
});
</script>
<?php } ?>

<script>
function onSignIn(googleUser) {
	const profile = googleUser.getBasicProfile();
	$('#student_name, #parent_name').val(profile.getName());
	$('#email').val(profile.getEmail());
}
</script>

<script src="<?php echo base_url().'assets/frontend/default/js/bootstrap.min.js'; ?>"></script>

<script>
$('#carousel-form').carousel({
	interval: false,
});
</script>

<script>
$.getJSON('<?php echo $base_url; ?>/api/getCountry', function(json) {
	$('#tel_code option[data-country-code="' + json.country_code + '"]').prop('selected', true)
	// getCountryCode(json.country);
});
</script>

<script type="text/javascript">
$(document).ready(function () {
	$('#student_name, #parent_name').on('keypress', function(e) {
		var regex = new RegExp("^[a-zA-Z. ]*$");
		var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
		if (regex.test(str)) {
			return true;
		}
		e.preventDefault();
		return false;
	});

});

function nextStep() {
	var student_name = $('#student_name').val();
	var student_grade = $('#student_grade').val();
	var parent_name = $('#parent_name').val();
	var email = $('#email').val();
	var parent_mobile = $('#parent_mobile').val();
	var tel_code = $('#tel_code').val();
	var utm_source = $('#utm_source').val();
	var utm_medium = $('#utm_medium').val();
	var utm_campaign = $('#utm_campaign').val();

	var error = false;
	$('.error').html("");

	if (student_name == '') {
		error = true;
		$('.student-name-error').html('<?php _eli('Enter Student Name'); ?>')
	}

	if (student_grade == '') {
		error = true;
		$('.student-grade-error').html('<?php _eli('Enter Student Grade'); ?>')
	}

	if (parent_name == '') {
		error = true;
		$('.parent-name-error').html('<?php _eli('Enter Parent Name'); ?>')
	}

	if (email == '') {
		error = true;
		$('.email-error').html('<?php _eli('Enter Email ID'); ?>')
	}

	if (parent_mobile == '') {
		error = true;
		$('.parent-mobile-error').html('<?php _eli('Enter Parent Mobile'); ?>')
	}

	if (error) {
		// $('.error').fadeOut(2000);
		return false;
	} else {
		$.ajax({
			type: 'POST',
			data: {
				'student_name'	: student_name,
				'student_grade'	: student_grade,
				'parent_name'	: parent_name,
				'email'			: email,
				'mobile'		: tel_code + parent_mobile,
				'utm_source'	: utm_source,
				'utm_medium'	: utm_medium,
				'utm_campaign'	: utm_campaign,
				'api_site_id'	: $('#api_site_id').val(),
			},
			url: '<?php echo $base_url;?>/api/sendOtp',
			dataType: 'json',
			beforeSend: function() {
				$('.error-form').addClass('hide');
				$('#button-next').prop('disabled', true);
			},
			complete: function() {
				$('#button-next').prop('disabled', false);
			},
			success: function (res) {
				if (res.default_otp) {
					$('#lead_id').val(res.lead_id);
					$('#otp').val(res.default_otp);
					verifyOtp();
				} else {
					if (!res.error) {
						$('#carousel-form').carousel('next');
						$('#lead_id').val(res.lead_id);
						$('.otp-error').html('<span style="color: <?php echo $theme_color; ?>; font-weight: 400;">'+ res.success +'</span>');
					} else {
						$('.error-form').html(res.error);
						$('.error-form').removeClass('hide');
					}
				}
			}
		});
	}
}

function resendOtp() {
	$.ajax({
		type: "POST",
		data: {
			'email'			: $('#email').val(),
			'api_site_id'	: $('#api_site_id').val(),
		},
		url: '<?php echo $base_url;?>/api/resendOtp',
		dataType: "json",
		beforeSend: function() {
			$('.otp-error').html('');
		},
		success: function (res) {
			if (!res.error) {
				$('.otp-error').html('<span style="color: #46f34d; font-weight: 400;">'+ res.success +'</span>');
			} else {
				$('.otp-error').html(res.error);
			}
		}
	});
}

function verifyOtp() {
	var otp = $('#otp').val();
	var email = $('#email').val();
	var leadId = $('#lead_id').val();

	var error = false;

	$('.error').html('');

	if (otp == '') {
		error = true;
		$('.otp-error').html('<?php _eli('Enter otp'); ?>')
	} else if (otp.length < 6) {
		error = true;
		$('.otp-error').html('<?php _eli('Enter valid otp'); ?>')
	}

	if (!error) {
		$.ajax({
			beforeSend: function () {
				$('.btn-verify').val('<?php _eli('Wait...'); ?>');
				$('.btn-verify').prop('disabled', true);
			},
			complete: function () {
				$('.btn-verify').val('Verify OTP');
				$('.btn-verify').prop('disabled', false);
			},
			type: 'POST',
			data: {
				'email'			: email,
				'otp' 			: otp,
				'lead_id'		: leadId,
				'api_site_id'	: $('#api_site_id').val(),
			},
			url: '<?php echo $base_url;?>/api/validateOtp',
			dataType: "json",
			success: function (json) {
				if (json.error) {
					$('.otp-error').html(json.error);
				} else if(json.success) {
					$('#carousel-form').carousel('next');
					$('#amount').html(json.amount);
					$('.btn-pay').attr('data-href', json.redirect);
					<?php if (0) { ?>
					window.top.postMessage(JSON.stringify(res));
					window.top.location.replace("<?php echo $site_url;?>/thanks?scode=" + res.success);
					<?php } ?>
				}
			}
		});
	}
}

$('.btn-pay').on('click', function() {
	$el = $(this);

	window.top.postMessage(JSON.stringify({
		redirect: $el.data('href')
	}));
});

function getCountryCode(country) {
	$.ajax({
		type: "POST",
		data: {
			'country' : $('#country').val(),
		},
		url: '<?php echo $base_url;?>/api/getCountryCode',
		dataType: "json",
		beforeSend: function() {
		},
		success: function (res) {
			if (!res.error) {
				$('#country_code').val(res.code);
			}
		}
	});
}

<?php if (!$has_discount_code) { ?>
$('#apply-discount').on('click', function() {
	$.ajax({
		type: 'POST',
		data: {
			'discount_code'	: $('#discount_code').val(),
			'api_site_id'	: $('#api_site_id').val(),
			'lead_id'		: $('#lead_id').val(),
		},
		url: '<?php echo $base_url;?>/api/applyDiscount',
		dataType: 'json',
		beforeSend: function() {
			$('.discount-success').html('');
			$('.discount-error').html('');
		},
		success: function (json) {
			if (!json.error) {
				$('.discount-success').html(json.success);
				$('#amount').html(json.amount);
				$('.btn-pay').attr('data-href', json.redirect);
			} else {
				$('.discount-error').html(json.error);
			}
		}
	});
});
<?php } ?>
</script>

<script type="text/javascript">
$(document).ready(function() {
	var w = $(window).width();
	if (w < 768) {
		$('.why').insertBefore($('.fm'));
	}
});
</script>
</body>
</html>
