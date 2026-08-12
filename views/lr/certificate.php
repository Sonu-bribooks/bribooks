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
	min-height: 650px;
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
#button-container {
	min-height: 150px;
}
.score-info {
    background: #f5f5f5;
    border-radius: 50%;
    height: 240px;
    width: 240px;
    border: 8px solid #e2904a;
    box-shadow: 0 0 10px -3px #000;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
	margin: auto;
	margin-top: 40px;
}
.score-info h4 {
    font-size: 200%;
    font-weight: bold;
}
.score-info h2 {
    font-size: 400%;
    font-weight: bolder;
    color: #d43b36;
	margin: 0;
}
.score-info p {
    font-size: 95%;
    max-width: 70%;
    text-align: center;
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
			<h3><?php _el('your_certificate'); ?></h3>

			<div class="row justify-content-between">
				<div class="col-sm-8 student-pil">
					<img src="<?php echo $certificate; ?>" class="certificate" />
				</div>
				<div class="col-sm-4 student-pil">
					<div class="score-info">
						<h4><?php _el('your_score'); ?></h4>
						<h2><?php echo round($score / $total * 100, 2); ?>%</h2>
						<p><?php _el('By answering ' . $score . '/' . $total . ' questions correctly'); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="container mt-4 mb-4" id="button-container">
	<div class="row justify-content-end">
		<a
			href="<?php echo base_url('assessment/downloadCertificate'); ?>"
			class="btn-kb mr-3"
		><?php echo _l('download_certificate_as_pdf'); ?></a>
		<a
			href="whatsapp://send?text=<?php echo $certificate; ?>"
			class="btn-kb mr-3"
			target="_blank"
		><?php echo _l('share_on_whatsapp'); ?></a>
		<a
			href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $certificate; ?>"
			class="btn-kb mr-3"
			target="_blank"
		><?php echo _l('share_on_facebook'); ?></a>
		<a
			href="<?php echo base_url('assessment'); ?>"
			class="btn-kb"
		><?php echo _l('home'); ?></a>
	</div>
</div>
