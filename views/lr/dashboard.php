<style>
body {
	height: 100%;
	width: 100%;
	background-image: url(<?php echo base_url('assets/frontend/default/lr/bgs/dashboard.png'); ?>);
	background-repeat: repeat;
	background-position: center;
}
.logo {
	height: 100px;
	width: auto;
}
#content {
	background-image: url(<?php echo base_url('assets/frontend/default/lr/bgs/dashboard-top.png'); ?>);
	background-repeat: no-repeat;
	background-size: 100% auto;
	min-height: 500px;
	position: relative;
}
.student-info {
	position: absolute;
	bottom: -25px;
    left: 0;
    right: 0;
}
.student-info h3 {
    background-color: #bdbdcd;
    padding: 7px 10px;
    margin-left: -15px;
    display: inline-block;
    margin-bottom: 20px;
    font-size: 250%;
    letter-spacing: 1px;
}
.student-pil {
	background-color: #fff;
	box-shadow: 0 0 5px 2px rgb(0 0 0 / 20%);
	min-height: 170px;
}
.student-pil h4 {
    margin-top: 20px;
    font-size: 140%;
    font-weight: normal;
    display: block;
    text-align: center;
}
.student-pil .text {
    background-color: #ff7300;
    color: #fff;
    border-radius: 20px;
    text-align: center;
    padding: 7px 10px;
    align-self: center;
    margin-top: 25%;
}
@media (max-width: 767px) {
	.student-info {
		position: relative;
	}
	.logo {
		height: 70px;
	}
	.btn-kb, a.btn-kb, .btn-kb:focus, a.btn-kb:focus {
		margin-top: 10px;
	}
}
</style>
<div id="content">
	<div class="container">
		<div class="row justify-content-between">
			<img
				src="<?php echo base_url('uploads/system/logo-light.png');?>"
				alt=""
				class="logo"
			/>
			<a
				href="<?php echo base_url('assessment/logout'); ?>"
				class="btn-kb"
			><?php echo _l('logout'); ?></a>
		</div>

		<div class="container student-info">
			<h3><?php _el($user_type . '_information'); ?></h3>

			<div class="row justify-content-between">
				<div class="col-sm-2 student-pil">
					<h4><?php _el($user_type . '_name'); ?></h4>
					<p class="text"><?php echo $this->session->userdata('name'); ?></p>
				</div>
				<div class="col-sm-2 student-pil">
					<h4><?php _el($user_type . '_grade'); ?></h4>
					<p class="text"><?php echo $this->session->userdata('grade'); ?></p>
				</div>
				<div class="col-sm-2 student-pil">
					<h4><?php _el('level'); ?></h4>
					<p class="text"><?php echo $this->session->userdata('quiz_level'); ?></p>
				</div>
				<div class="col-sm-2 student-pil">
					<h4><?php _el('program'); ?></h4>
					<p class="text"><?php echo $program; ?></p>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="container mt-4">
	<div class="row justify-content-end">
		<a
			href="<?php echo base_url('assessment/certificate'); ?>"
			class="btn-kb mr-3"
		><?php echo _l('download_certificate'); ?></a>
		<a
			href="<?php echo base_url('assessment/start'); ?>"
			class="btn-kb"
		><?php echo _l('start_assessment'); ?></a>
	</div>
</div>
