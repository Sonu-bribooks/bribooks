<style>
body {
	border-bottom: 20px solid #af5206;
	height: 100%;
	width: 100%;
}
#login-panel {
	min-height: 100%;
	width: 100%;
	position: relative;
	background-image: url(<?php echo base_url('assets/frontend/default/lr/bgs/login.png'); ?>);
	background-repeat: repeat;
	background-position: center;
}
#login-panel .left, #login-panel .right {
	background-image: url(<?php echo base_url('assets/frontend/default/lr/bgs/login.png'); ?>);
	background-repeat: repeat;
	width: 50%;
	position: absolute;
	height: 100%;
}
#login-panel .left {
	top:0;
	bottom: 0;
    background-color: #ff7300;
    border-top: 20px solid #af5206;
}
#login-panel .right {
	top:0;
	bottom: 0;
	right: 0;
	float: unset !important;
}
#login-panel h3, #login-panel li {
	color: #fff;
}
#login-panel h3 {
    margin-top: 40px;
    margin-bottom: 30px;
}
#login-panel li {
	font-size: 120%;
	line-height: 140%;
	margin-bottom: 10px;
}
.logo {
	height: 100px;
	margin-bottom: 1px;
	margin-top: -50px;
	width: auto;
}
.content-update-box {
    box-shadow: 0 0 5px 2px rgb(0 0 0 / 20%);
    background-color: rgba(255,255,255,0.7);
    padding: 60px 30px;
}
#code, #code:focus, #code:active {
	border: 2px solid #ff7300;
	line-height: 35px;
	outline: unset;
	box-shadow: unset;
}
#login-panel .right h4 {
	font-size: 180%;
	margin-bottom: 30px;
}
@media (max-width: 767px) {
	#login-panel .left, #login-panel .right {
		position: relative;
		width: auto;
	}
	#login-panel .right {
		margin: 0;
	}
	.logo {
		margin-top: 0;
	}
}
</style>
<div class="" id="login-panel">
	<div class="left">
		<h3 class="text-center">Welcome to ICODE.ORG Assessment Center. Please read through the instructions below before you take the test.</h3>
		<ol>
			<li>Enter the Assessment code you have received from your education Provider to start the test.</li>
			<li>Each Test has a time limit of 30 minutes. Please keep an eye on the time. You will have to retake the test if your allotted time lapses. </li>
			<li>You need to have an active webcam or Mobile Camera to be able to take the test. We may monitor you during the entire duration or take random snapshots to ensure fair practices during the test.</li>
			<li>You can generate a certificate once the test is completed.</li>
			<li>You get maximum three attempts to take the test. The certificate generated will be with your best out of three score.</li>
		</ol>
	</div>
	<div class="row right justify-content-center align-items-center align-items-center">
		<div class="col-lg-8 text-center mt-4">
			<img src="<?php echo base_url('uploads/system/logo-light.png');?>" alt="" class="logo">
			<div class="content-update-box">
				<h4>Enter your Assessment Code to continue</h4>
				<form action="<?php echo base_url('assessment/validateCode'); ?>" method="post" id="form-code">
					<div class="content-box">
						<div class="basic-group">
							<div class="form-group">
								<input
									type="password"
									class="form-control"
									name="code"
									id="code"
									placeholder="<?php echo _l('assessment_code'); ?>"
									value=""
									required
								/>
							</div>
						</div>
					</div>
					<div class="content-box">
						<button type="submit" class="btn-kb"><?php echo _l('submit'); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
$('#form-code').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	submitForm($el.attr('action'), new FormData($el[0]), json => {
		json.error && error_notify(json.error);
		json.success && success_notify(json.success);
		json.redirect && setTimeout(() => location = json.redirect, 300);
	})
});
</script>
