<style>
.menu-area {
	display: none;
}
body.gray-bg {
	background: #fff;
}
.header-bg {
	position: relative;
	margin-top: -120px;
}
.header-bg img {
	width: 100%;
}
.event-item img {
    width: 100px;
    margin: auto;
}
.event-item p {
    color: #000;
    margin-top: 15px;
    text-align: center;
    height: 30px;
}

element.style {
}
.heading {
    margin-top: 20px;
    color: #263787;
    font-weight: bolder;
    font-size: 300%;
}
.subheading {
    font-weight: normal;
    margin-top: 10px;
    font-size: 80%;
}
.user-dashboard-box {
	box-shadow: unset;
}
.user-dashboard-content .content-title-box {
	border: unset;
}
.btn, .user-dashboard-content .content-update-box button {
	background-color: #273787;
	border: unset;
}
.user-dashboard-content .content-box .form-group .form-control {
	border: unset;
    border-radius: 5px;
    background: #f2f2f2;
    outline: unset;
    box-shadow: unset;
    height: 50px !important;
}
@media (max-width: 767px) {
	.header-bg {
		margin-top: 0;
	}
}
</style>
<div class="container-full header-bg">
	<picture>
		<source
			srcset="<?php echo base_url('assets/frontend/default/img/' . date('Y') . '/header-mobile.webp'); ?>"
			media="(max-width: 767px)"
		/>
		<img
			src="<?php echo base_url('assets/frontend/default/img/' . date('Y') . '/header.webp'); ?>"
			class="img-fluid"
			alt="Icode Global Hackathon 2022"
			loading=lazy
		>
	</picture>
</div>
<div class="container text-center">
	<img
		src="<?php echo base_url('assets/frontend/default/img/' . date('Y') . '/highlight.png'); ?>"
		class="img-fluid"
		style="margin: auto;max-height: 200px;"
		alt="Icode Global Hackathon 2022"
	>
</div>
<div class="container">
	<div class="row justify-content-center">
		<div class="col-sm-10">
			<div class="row event-items">
				<div class="col-sm-3 col-6 event-item text-center">
					<img
						src="<?php echo base_url('assets/frontend/default/img/' . date('Y') . '/icons/ranking.png'); ?>"
						class="img-fluid"
					/>
					<p><?php echo _li('Global Ranking & Certificates'); ?></p>
				</div>
				<div class="col-sm-3 col-6 event-item text-center">
					<img
						src="<?php echo base_url('assets/frontend/default/img/' . date('Y') . '/icons/account.png'); ?>"
						class="img-fluid"
					/>
					<p><?php echo _li('Personalized Account'); ?></p>
				</div>
				<div class="col-sm-3 col-6 event-item text-center">
					<img
						src="<?php echo base_url('assets/frontend/default/img/' . date('Y') . '/icons/reward.png'); ?>"
						class="img-fluid"
					/>
					<p><?php echo _li('Prizes Worth $ 150,000'); ?></p>
				</div>
				<div class="col-sm-3 col-6  event-item text-center">
					<img
						src="<?php echo base_url('assets/frontend/default/img/' . date('Y') . '/icons/webinar.png'); ?>"
						class="img-fluid"
					/>
					<p><?php echo _li('Preparatory Webinar & Mock  Hackathons'); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
