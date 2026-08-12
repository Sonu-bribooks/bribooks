
<script src="<?php echo base_url().'assets/frontend/default/js/bootstrap.min.js'; ?>"></script>


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
	$('#email').val(profile.getEmail());
}
</script>

<script src="<?php echo base_url().'assets/frontend/default/js/bootstrap.min.js'; ?>"></script>

<script>
$('#carousel-form').carousel({
	interval: false,
});
</script>

<script type="text/javascript">
$(document).ready(function () {
	$('#mobile').on('keypress', function (event) {
		if (event.keyCode != 8) {
			var regex = new RegExp("^[0-9]*$");
			var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
			if (!regex.test(key)) {
				event.preventDefault();
				return false;
			}
		}
	});

	$('#school_name').on('keypress', function(e) {
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
	var school_name = $('#school_name').val();
	var organization_type = $('#organization_type').val();
	var country = $('#country').val();
	var city = $('#city').val();
	var mobile = $('#tel_code').val() + $('#mobile').val();
	var email = $('#email').val();
	var utm_source = $('#utm_source').val();
	var utm_medium = $('#utm_medium').val();
	var utm_campaign = $('#utm_campaign').val();

	var error = false;
	$('.error').html("");

	if (school_name == '') {
		error = true;
		$('.name-error').html('<?php _eli('Enter School Name'); ?>')
	}

	if (organization_type == '') {
		error = true;
		$('.type-error').html('<?php _eli('Select Organization Type'); ?>');
	}

	if (country == '') {
		error = true;
		$('.country-error').html('<?php _eli('Select Country'); ?>')
	}

	if (city == '') {
		error = true;
		$('.city-error').html('<?php _eli('Enter City'); ?>')
	}

	if (mobile == '') {
		error = true;
		$('.mobile-error').html('<?php _eli('Enter Parent Mobile No'); ?>')
	}

	if (email == '') {
		error = true;
		$('.email-error').html('<?php _eli('Enter Email ID'); ?>')
	}

	if (error) {
		return false;
	} else {
		$.ajax({
			type: 'POST',
			data: {
				'mobile'    		: mobile,
				'email' 			: email,
				'name'   			: school_name,
				'type' 				: organization_type,
				'country'			: country,
				'city'				: city,
				'utm_source'    	: utm_source,
				'utm_medium'    	: utm_medium,
				'utm_campaign'  	: utm_campaign,
				'api_site_id'   	: $('#api_site_id').val(),
			},
			url: '<?php echo $base_url;?>/api/sendSchoolOtp',
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
						$('.form-error').html(res.error);
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
			'mobile'		: $('#tel_code').val() + $('#mobile').val(),
			'email'			: $('#email').val(),
			'api_site_id'	: $('#api_site_id').val(),
		},
		url: "<?php echo $base_url;?>/api/resendSchoolOtp",
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
	var mobile = $('#tel_code').val() + $('#mobile').val();
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
			type: "POST",
			data: {
				'mobile' : mobile,
				'email': email,
				'otp' : otp,
				'lead_id' : leadId,
				'api_site_id': $('#api_site_id').val(),
			},
			url: "<?php echo $base_url;?>/api/validateSchoolOtp",
			dataType: "json",
			success: function (res) {
				if (res.error) {
					$('.otp-error').html(res.error);
				} else if(res.success) {
					$('#carousel-form').carousel('next');
					<?php if (0) { ?>
						window.top.postMessage(res.success);
					<?php } ?>
				}
			}
		});
	}
}
</script>

<script type="text/javascript">
$(document).ready(function() {
	var w = $(window).width();
	if (w < 768) {
		$('.why').insertBefore($('.fm'));
	}
});
</script>

<script>
$.getJSON('<?php echo $base_url; ?>/api/getCountry', function(json) {
	$('#tel_code option[data-country-code="' + json.country_code + '"]').prop('selected', true)
	$('#country option[value="' + json.country + '"]').prop('selected', true)
	$('#city').val(json.city);
	// getCountryCode(json.country);
});
</script>
<script>
$('.btn-continue').on('click', function() {
	$el = $(this);

	window.top.postMessage(JSON.stringify({
		redirect: '<?php echo base_url(); ?>'
	}));
});
</script>


</body>
</html>
