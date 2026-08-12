<?php
$start_time = 0 * 60;
// $end_time = $start_time + 160;
$end_time = $start_time + 640;

$schedules = $this->schedule_model->get_all([
	'range_start'	=> date('Y-m-d H:i', strtotime("+{$start_time} minutes")),
	'range_end'		=> date('Y-m-d H:i', strtotime("+{$end_time} minutes")),
	'student_id'	=> $this->session->user_id,
])->result_array();

$schedule = end($schedules);

if (!$schedule) {
	$schedules = $this->schedule_model->get_all([
		'range_start'	=> date('Y-m-d H:i', strtotime("+{$start_time} minutes")),
		'range_end'		=> date('Y-m-d H:i', strtotime("+{$end_time} minutes")),
		'student_id'	=> $this->session->user_id,
		'is_demo'		=> true,
	])->result_array();

	$schedule = end($schedules);
}

$enrols = $this->enrol_model->getAll([
	'user_id'	=> $this->session->userdata('user_id'),
	'site_id'	=> $this->config->item('site_id'),
]);

$python 	= false;
$blockly 	= false;
$enrol_info = [];
$course_info = [];

foreach ($enrols as $key => $enrol) {
	if (!$enrol['status'] || $enrol['emi_type'] === 'free') {
		unset($enrols[$key]);
		continue;
	}

	if (strpos(mb_strtolower($enrol['course']), 'python') !== false) {
		$python = $enrol;
	}

	if (strpos(mb_strtolower($enrol['course']), 'blockly') !== false) {
		$blockly = $enrol;
	}

	$enrol_info = $enrol;
}

$course_info = $enrol_info ? $this->course_model->get($enrol_info['course_id'])->row_array() : [];

// hc($schedules);
?>
<style>
	.card {
		border-radius: 0px;
		padding: 5px 5px 5px 5px;
		min-height: 84px;
	}
	.course-card {
		padding: 10px 15px;
		border-radius: 10px;
		box-shadow: 3px 5px 10px -5px #000;
		margin-bottom: 30px;
	}
	.dev-icon {
		height: 20px;
		content: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABcAAAAdCAYAAABBsffGAAAAAXNSR0IB2cksfwAAAAlwSFlzAAAuIwAALiMBeKU/dgAABcxJREFUeJztlmtsU2UcxtmmxktI/GDUT8bIN/WLF6KIUVEEiUZJUMQYQeV+UbxgBBaZIIEJA2QIjI2tk67r2vW2rV27raztLl3Xy2i3sY2OrVvZ2KV0MHo57zk95zy+XQUvDIWY+MknefKe5Jz+/s//n7fvOTNm/K9/K4Zh06JRJsN3pj/DZPBklEjq0m4LsHfHiXuOHq5erNd68s/7R9wUOBRnuEtMnA3T6zCFj7d7+x11pjPfqRUNj1cYOu9I/u6rl1enrXnls/RpoQFLXnpv/ckX6mubLReGQpwoigIv8GKcsIgyHKJxDgzhIPAcRAigt8VA38Xx6prOrMrDRfdvXbDmkY3z1j++eeGme96bt+P3jgKNBemD9l/mTY74RwQqnk+AEAKGSZlQx+LUdA2OEXQHWTBsAhAF8KPdorPweNue99YFtr++Ivzlwg2lH7267eHf4W7tQ8GBvi6SEEVCkzIMcx18Dc6xBJ2DBOY2Als7i4GQAC50AcScD9eeHdB89wUKl6/C3kXLhA3zN0umwF0uW3qXu3FTYIwR4kwC7B+gf4QnOAJnD4Gxjcfg8FXsz6mHJOsArNu+geT9L1G9cTNUn6xH8TvzsWPRiqEpuO9sIJ3a1DsiIHSFnQKxZHp44CKBqx9ocgzgsVlZyFx/CIbtmSh8dz3kS9dAsexDFC9+Q9iycJ0nBe8eSaP2ewNAYITF6ASLSCxVIGnym5PXPC1wcYKH3XcJ2Qct2LnPBlVOPtxZm2De+LFYtuR1ZucbywNLX/n+zesz93Ze6DW6RXj7CPpGWQyGWIyEWYSvEFyJEFy+mvJklCB0meDsBRGNvhi+3evB7oPtaDXZ4dZUje9fsnLl6hfXzvrTNvT3dFkr7LxocLBw+wl6afvnhtmpQgNj1OO/rdR9tLvOQRZ6B4c8nYgjag7lLUBZfdRJfeM+7/fWbFU1MEKJHSh3AnraRYOPoI120jHAoiuYAp45z8J1joXVS1BhJ5CbGZRbCX1eQJk5YqTOuAHut5c9a7b1R40aN2q1rVCfnkCykKaZR0UzuW4dtcFBUNlC11aCameqiKYpAakpki81TgMPtMhmjhn2tcf3PieSnOfhL9gKq1SHclscStqyppFct9FFTaF6CtfTQtpmDkoLgVTZnikt894IH2iRpYUVn0rjP83h2VMvQcx/CuKBWThXtBNVtUEomkSobCxNmEqchNd6UnAVTa2yRmE1VH3QoFfdCE/qkmbtV0zuXC5xag76K7ahrSIPzNHZOHdoFWSGEOQ2AYrTzFT65DiqXakxqSlcbbsatdWYn2k26qY/uIblm+aHDi2KRYrfQqu1CYqWBE4bzJg88jTOHMtEmZVBqZmmb2Cm4JUUrLHzKLeLMNmC3TaL64FpwUnp86SPegqzL0Y0W8TwpQmMx4CG9hgG1V8jcux51MlNkDp4qFtFaJ0iyl0CHVUEdbXdaLG0qlssjrtvCjcX5czslnzuC+t2CeTKaPJIxfikiCaLA/Hiueg5ug4GtRXGKg9qdA5YtEZ41afQoZGiq650S9fp0rtuCq+XZKf1qDJzehT7WWagDQmexwQDeFxdiMkXYFLyNobK92PIXIrzBik66KhaS0tRqLsck8nds5Ul5unnfU3+yswHbSd2Ffhb28P0TBfpPx2dbh/YA08ioNwDcweDxg4eJk8CBdUcdkk4HC1qrzkh8cz8W3BSvfrMNGvezjuDPd2l9I3BhWhyZ10zRnKXwqR1IlfLIVdBcEAaw+7CCH48OcqrFbXvGlXqO/8Rfk2xYfcxUeRJkL4QlJVBVBrO47CKQ3ZxDNmSOLIKaOrCCC9TeHKV5S333TI4qYlA89L+ocjVhrYJ9pBseCyzUBC+OcJj2zEBPxQx4s+y4VhVtfcHU43z9sBJDfm091bK9YerVKbVSq3jieOygRN5Mr+/RNfntzR0yH2NmtfONpbc+ij+Kl1JZUaV0jC1Ayj89r5P/gv9Ci9vFLeCDkHlAAAAAElFTkSuQmCC');
	}
	.course-card .page-title {
		font-size: 180% !important;
		font-weight: bold !important;
	}
	.event-items {
		border-left: 5px solid #ddd;
	}
	.event-item {
		margin-bottom: 15px;
	}
	.sprite-img {
		background-image: url('<?php echo base_url('uploads/icode-sprite.png'); ?>');
		width: 150px;
		height: 100px;
		background-repeat: no-repeat;
		margin: auto;
	}
	.sprite-hackathon {
		background-position: 0 -270px;
	}
	.sprite-certificate {
		background-position: 0 -135px;
	}
	.card-green, .card.active {
		border: 1px solid #4ca95a;
		background-color: #4ca95a;
		color: #ffffff;
	}
	.btn-kb, .btn-kb:hover, .btn-kb:focus, .btn-kb:active {
		background-color: #fd7f25;
		box-shadow: 0 4px 0 0 #b14b03;
		border-radius: 7px;
		color: #fff !important;
		padding: 8px 28px;
		border: unset;
	}
	.btn-kb:hover, .btn-kb:focus, .btn-kb:active {
		background-color: #f58c40;
	}
	.btn-kb.disabled, .btn-kb:disabled {
		background-color: #cccc;
		color: #000 !important;
		box-shadow: 0 4px 0 0 #999;
		pointer-events: unset !important;
		cursor: not-allowed;
	}

	.card-yellow {
		border: 1px solid #e7953c;
		background-color: #e7953c;
	}

	.card-light-yellow {
		border: 1px solid #c3d444;
		background-color: #c3d444;
	}
	.card-blue {
		border: 1px solid #3a99ca;
		background-color: #3a99ca;
	}
	section.my-dashboard-area {
		padding: 40px 0;
	}
	.my-dashboard-area .card p {
		color: #ffffff;
	}
	.course h5, .quick h5 {
		text-transform: uppercase;
		background-color: #e66f38;
		border: 1px solid #e66f38;
		border-radius: 20px;
		width: fit-content;
		padding: 0 10px;
		color: #ffffff;
		font-size: 120%;
		font-weight: 600;
	}
	.course h3 {
		margin-top: 50px;
		text-align: center;
		background-color: #e7953c;
		border: 1px solid #e7953c;
		border-radius: 0px;
		width: fit-content;
		padding: 0 10px;
		color: #ffffff;
		font-weight: 600;
		margin-left: auto;
		margin-right: auto;
	}

	.quick h2 {
		margin-top: 50px;
		text-align: center;
		color: #e7953c;
		width: fit-content;
		font-weight: 600;
		font-size: 50px;
		margin-left: auto;
		margin-right: auto;
	}
	.course p {
		font-weight: 600;
	}
	.stage {
		background-image: url("<?=base_url().'assets/frontend/default/img/footer-top.png'; ?>");
		background-size: cover;
		background-position: bottom;
		background-repeat: no-repeat;
		padding: 147px 0 !important;
		margin-top: -163px;
	}

	.cards {
		background-image: url("<?=base_url().'assets/frontend/default/img/card-active.png'; ?>");
		background-size: contain;
		background-repeat: no-repeat;
	}

	.zoom-video img {
		width: 100%;
	}
	.stage .card {
		min-height: 1px;
		float: left;
		margin-left: 20px;
		margin-top: 10px;
	}

	.stage .card p {
		color: #000000;
	}
	.stage .active p {
		color: #ffffff;
	}

	.countdownHolder{
		margin:0 auto;
		font: 40px/1.5 'Open Sans Condensed',sans-serif;
		text-align:center;
		letter-spacing:-3px;
	}

	.position{
		display: inline-block;
		height: 1.6em;
		overflow: hidden;
		position: relative;
		width: 1.05em;
	}

	.digit{
		position:absolute;
		display:block;
		width:1em;
		background-color:#e7953c;
		border-radius:0.2em;
		text-align:center;
		color:#fff;
		letter-spacing:-1px;
	}

	.digit.static{
		box-shadow:1px 1px 1px rgba(4, 4, 4, 0.35);

		background-image: linear-gradient(bottom, #df8423 50%, #e7953c 50%);
		background-image: -o-linear-gradient(bottom, #df8423 50%, #e7953c 50%);
		background-image: -moz-linear-gradient(bottom, #df8423 50%, #e7953c 50%);
		background-image: -webkit-linear-gradient(bottom, #df8423 50%, #e7953c 50%);
		background-image: -ms-linear-gradient(bottom, #df8423 50%, #e7953c 50%);

		background-image: -webkit-gradient(
			linear,
			left bottom,
			left top,
			color-stop(0.5, #df8423),
			color-stop(0.5, #e7953c)
		);
	}

	/**
	* You can use these classes to hide parts
	* of the countdown that you don't need.
	*/

	.countDays{ /* display:none !important;*/ }
	.countDiv0{ /* display:none !important;*/ }
	.countHours{}
	.countDiv1{}
	.countMinutes{}
	.countDiv2{}
	.countSeconds{}


	.countDiv{
		display:inline-block;
		width:16px;
		height:1.6em;
		position:relative;
	}

	.countDiv:before,
	.countDiv:after{
		position:absolute;
		width:5px;
		height:5px;
		background-color:#df8423;
		border-radius:50%;
		left:50%;
		margin-left:-3px;
		top:0.5em;
		box-shadow:1px 1px 1px rgba(4, 4, 4, 0.5);
		content:'';
	}

	.countDiv:after{
		top:0.9em;
	}

	.countDays, .countDays+.countDiv {
		display: none;
	}

	.progressbar{
		counter-reset: step;
		list-style: none;
	}
	.progressbar li{
		float: left;
		width: 20%;
		position: relative;
		text-align: center;
	}
	.progressbar li:before{
		content:counter(step);
		counter-increment: step;
		width: 50px;
		height: 50px;
		border: 2px solid #bebebe;
		display: block;
		margin: 0 auto 10px auto;
		border-radius: 50%;
		line-height: 47px;
		background: white;
		color: #bebebe;
		text-align: center;
		font-weight: bold;
	}
	.progressbar li:after{
		content: '';
		position: absolute;
		width:100%;
		height: 3px;
		background: #979797;
		top: 25px;
		left: -50%;
		z-index: -1;
	}
	.progressbar li.active:after{
		background: #df8423;
	}
	.progressbar li.active:before{
		border-color: #df8423;
		background: #df8423;
		color: white
	}
	.progressbar li:first-child:after{
		content: none;
	}
	.mt-40 {
		margin-top: 40px;
	}
</style>
<section class="page-header-area my-course-area">
	<div class="container">
		<div class="row">
			<div class="col-sm-4">
				<div class="card course-card">
					<h1 class="page-title">
						<?php echo $course_info['title'] ?? ''; ?>
					</h1>
					<p><?php echo $course_info['description'] ?? ''; ?></p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-4 mb-4 text-center">
				<?php if ($enrol_info || $this->config->item('site_country_code') != 'IN') { ?>
				<div class="gameList<?php echo strpos(mb_strtolower(trim($enrol_info['course'])), 'python') !== false ? 'Python' : 'Blockly'; ?>"></div>
				<?php } ?>
			</div>
		</div>
	</div>
</section>

<?php if (0) { ?>
<?php if (time() < strtotime(EVENT_START_DATE) && time() < strtotime(EVENT_END_DATE)) { ?>
<div class="alert alert-primary text-center" role="alert">
	<strong><?php echo EVENT_TITLE . ' ' . _li('will_start_at'); ?> <span class="text-danger"><?php echo format_date(EVENT_START_DATE); ?> IST</span></strong>
</div>
<?php } ?>

<?php if (time() > strtotime(EVENT_START_DATE) && time() < strtotime(EVENT_END_DATE)) { ?>
<div class="alert alert-success text-center" role="alert">
	<strong><?php echo EVENT_TITLE . ' ' ._li('is_live_now!'); ?> <?php echo _li('Click_Join_Hackathon_to_continue'); ?></strong>
</div>
<?php } ?>

<?php if (time() > strtotime(EVENT_END_DATE)) { ?>
<div class="alert alert-success text-center" role="alert">
	<strong><?php echo _li('Certificate_generated!'); ?> <?php echo _li('Click_to_Download_Certificate'); ?></strong>
</div>
<?php } ?>
<?php } ?>

<section class="my-dashboard-area">
	<div class="container">
		<div class="row">
			<div class="col-sm-8">
				<?php if (time() < strtotime(EVENT_END_DATE) && 0) { ?>
				<div id="timer-wrap">
					<p class="text-center" id="time-note"><?php echo EVENT_TITLE . ' ' . _li('will_start_in'); ?></p>
					<div id="time-counter"></div>
				</div>
				<?php } ?>

				<p>Hey <?php echo $this->session->name; ?> ! I am Dev <span class="dev-icon"></span><br /><br />

Welcome to iCode, your learning partner for coding education.
You and I will together explore the Space and you will help me
navigate by writing different codes. As you learn and write
codes, we will be able to move to new stages of our journey.
As we move ahead, you will be able to	collect points and
energy coins.<br /><br />

What’s more, you will be able to collaborate and compete with
friends from around the world with Global Hackathons and win
cool prizes, medals and certificates.</p>
			</div>
			<div class="col-sm-4 text-center event-items">
				<div class="event-item">
					<div class="sprite-img sprite-webinar"></div>
					<a class="btn btn-success btn-kb" href="<?php echo base_url('home/webinar'); ?>"><?php _el('click_to_join'); ?></a>
				</div>

				<div class="event-item">
					<div class="sprite-img sprite-hackathon"></div>
					<a
						class="btn btn-success btn-kb disabled event-button"
						href="#"
					><?php _el('join_hackathon'); ?></a>
					<p class="text-danger d-none">*<small><?php echo _li('hackathon_not_started_yet'); ?></small></p>
				</div>

				<div class="event-item">
					<div class="sprite-img sprite-certificate"></div>
					<a
						class="btn btn-success btn-kb disabled cert-button"
						href="#"
					><?php _el('download_certificate'); ?></a>
					<p class="text-danger d-none">*<small><?php echo _li('hackathon_not_finished_yet'); ?></small></p>
				</div>
			</div>
		</div>
	</div>
</section>

<?php if (count($enrols) > 0 && in_array($this->session->userdata('user_email'), TESTING_EMAILS)) { ?>
<section class="my-dashboard-area">
	<div class="container">
		<ul class="purchase-history-list">
			<li class="purchase-history-list-header">
				<div class="row">
					<div class="col-sm-2"><h4 class="purchase-history-list-title"> <?php echo _l('courses'); ?> </h4></div>
					<div class="col-sm-10 hidden-xxs hidden-xs">
						<div class="row">
							<div class="col-sm-1 text-center"><b><?php echo _l('total_points'); ?></b></div>
							<div class="col-sm-2 text-center"><b><?php echo _l('national_ranking'); ?></b></div>
							<div class="col-sm-2 text-center"><b><?php echo _l('current_level'); ?></b></div>
							<div class="col-sm-2 text-center"><b><?php echo _l('subscription'); ?></b></div>
							<div class="col-sm-2"><b></b></div>
							<div class="col-sm-3"><b></b></div>
						</div>
					</div>
				</div>
			</li>

			<?php foreach ($enrols as $enrol_i) {
				$user_info = $this->user_model->get($enrol_i['user_id']);

				$lead_info = $this->lead_model->get($user_info['lead_id']);

				$site_info = $this->site_model->get($enrol_i['site_id']);

			 	$payment_info = $this->enrol_model->getPaymentByEnrolId($enrol_i['id']);

				$upgrade_locked = 0;
				$upgrade_plan = '';
				$upgrade_amount = 0;

				if ($enrol_i['emi_type'] === 'base') {
					$upgrade_locked = 1;
					$upgrade_plan 	= 'premium';
					$upgrade_amount = $site_info['premium_plan'];
				} elseif ($enrol_i['emi_type'] === 'free') {
					$upgrade_locked = 0;
					$upgrade_plan 	= 'base';
					$upgrade_amount = $site_info['base_plan'];
				} else {
					$upgrade_locked = 1;
					$upgrade_plan 	= 'premium';
					$upgrade_amount = $site_info['premium_plan'];
				}
			?>

			<li class="purchase-history-items mb-2">
				<div class="row">
					<div class="col-sm-2">
						<a class="purchase-history-course-title" href="#">
							<?php echo $enrol_i['course']; ?>
						</a>
						<?php if (!$enrol_i['emi_type'] === 'premium') { ?>
						<?php if (!empty($enrol_i['expired'])) { ?>
						<span class="badge badge-danger"><?php _el('expired'); ?></span>
						<?php } else if (!empty($enrol_i['renewal'])) { ?>
						<span class="badge badge-warning"><?php _el('expiring_soon'); ?></span>
						<?php } else { ?>
						<?php echo _cs($enrol_i['status']); ?>
						<?php } ?>
						<?php } ?>
					</div>

					<div class="col-sm-10 purchase-history-detail">
						<div class="row">
							<div class="col-sm-1 date text-center" id="totalPoints<?php echo $enrol_i['course']; ?>">
								0
							</div>
							<div class="col-sm-2 date text-center" id="nationalRanking<?php echo $enrol_i['course']; ?>">
								0
							</div>
							<div class="col-sm-2 date text-center" id="currentLevel<?php echo $enrol_i['course']; ?>">
								0
							</div>

							<div class="col-sm-2 price text-center">
								<?php echo _et($enrol_i['emi_type']); ?>
							</div>

							<div class="col-sm-2 text-center">
								<?php if ($enrol_i['emi_type'] !== 'free' && $enrol_i['status']) { ?>

								<a
									id="downloadCertificate<?php echo strpos(mb_strtolower(trim($enrol_i['course'])), 'python') !== false ? 'Python' : 'Blockly'; ?>"
									href="<?php echo site_url('home/downloadCertificate/'. (strpos(mb_strtolower(trim($enrol_i['course'])), 'python') !== false ? 1 : 0) . '/' . $enrol_i['course_id']); ?>"
									class="btn btn-receipt"
								><?php _el('Download Certificate'); ?></a>

								<?php if (@$payment_info['order_id']) { ?>
								<a
									href="<?php echo site_url('home/invoice/' . $payment_info['order_id']); ?>"
									class="btn btn-receipt mt-1"
									target="_blank"
								><?php _el('Download Invoice'); ?></a>
								<?php } ?>
								<?php } ?>

								<?php if ($enrol_i['emi_type'] !== 'premium' || !$enrol_i['status']) { ?>
								<a
									href="<?php echo site_url('home/renewal/' . $this->enrol_model->generatePaymentLink($enrol_i['id'], $upgrade_amount, $upgrade_plan, $upgrade_locked)); ?>"
									class="btn btn-receipt mt-1"
									target="_blank"
								><?php _el('Upgrade'); ?></a>
								<?php } ?>
							</div>

							<div class="col-sm-3 text-center">
								<?php if (($enrol_i['emi_type'] !== 'free' && $enrol_i['status']) || $this->config->item('site_country_code') != 'IN') { ?>
								<div class="gameList<?php echo strpos(mb_strtolower(trim($enrol_i['course'])), 'python') !== false ? 'Python' : 'Blockly'; ?>"></div>
								<?php } ?>
							</div>
						</div>
						<?php if (($enrol_i['emi_type'] !== 'free' && $enrol_i['status']) || $this->config->item('site_country_code') != 'IN') { ?>
						<div class="row">
							<div class="col-sm-12 text-center">
								<?php if (in_array($this->session->userdata('user_email'), TESTING_EMAILS)) { ?>
								<a class="btn btn-success" style="min-width:40%;background-color:#17a2b8;border-color:#17a2b8;margin-top:10px;" href="<?php echo base_url('home/joinRace/' . $enrol_i['course_id']); ?>"><?php _el('join_race'); ?></a>
								<?php } ?>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</li>
			<?php } ?>
		</ul>
	</div>
</section>
<?php } ?>

<script>
function getCookie(cname) {
	let name = cname + "=";
	let decodedCookie = decodeURIComponent(document.cookie);
	let ca = decodedCookie.split(';');
	for(let i = 0; i <ca.length; i++) {
		let c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

$('#totalPointsPython').val(1);

const formatHtml = data => {
	var html = '';

	$.each(data, function(key, value) {
		$.ajax({
			url: '<?php echo site_url('api/queryUserGameInfo'); ?>',
			type: 'POST',
			data: {
				mode: value.gameMode,
				level: value.gameLevel,
				gameId: value.gameId,
			},
			success: function(json) {
				if (json.code == 0) {
					$('#totalPoints' + value.gameName).text(value.gameLevel);
					$('#nationalRanking' + value.gameName).text(value.gameLevel);
					$('#currentLevel' + value.gameName).text(json.data.levelId);
					$('#downloadCertificate' + value.gameName).css('display', 'inline-block');

					if (json.data.levelId == 0) {
						// $('#downloadCertificate' + value.gameName).remove();
						// $('#downloadCertificate' + value.gameName).css('display', 'none');
					}
				} else {
					// $('#downloadCertificate' + value.gameName).remove();
				}
			},
			error: function(xhr) {
				console.warn(xhr);
			}
		});

		html += '<a class="btn btn-success btn-kb" onclick="enterGame(' + value.gameMode + ',' + value.gameId + ')"><?php _eli('Start Learning'); ?></a>'
	});

	return html;
};

<?php if ($blockly) { ?>
$.ajax({
	url: '<?php echo site_url('api/getGameList'); ?>',
	type: 'POST',
	data: {
		mode: 0,
		level: 0,
		course_id: '<?php echo $enrol_info['course_id'] ?? 0; ?>'
	},
	success: function(json) {
		console.log(json);
		if(json.code == 0) {
			let html = formatHtml(json.data);
			$(".gameListBlockly").html(html);
		}
	},
	error: function(xhr) {
		console.warn(xhr);
	}
});
<?php } ?>

<?php if ($python) { ?>
$.ajax({
	url: '<?php echo site_url('api/getGameList'); ?>',
	type: 'POST',
	data: {
		mode: 1,
		level: 0,
		course_id: '<?php echo $enrol_info['course_id'] ?? 0; ?>'
	},
	success: function(json) {
		console.log(json);
		if(json.code == 0) {
			let html = formatHtml(json.data);
			$(".gameListPython").html(html);
		}
	},
	error: function(xhr) {
		console.warn(xhr);
	}
});
<?php } ?>

function enterGame(mode, gameId) {
	$.ajax({
		url: '<?php echo site_url('api/enterGame'); ?>',
		type: 'POST',
		data: {
			mode: mode,
			gameId: gameId,
			course_id: '<?php echo $enrol_info['course_id'] ?? 0; ?>'
		},
		success: function(json) {
			console.log(json);
			if(json.code == 0) {
				window.location.href = "<?=GAME_URL?>?highLevel=1&mode=" + mode + "&game=" + gameId + '&token=' + json.token + '&uid=' + json.uid;
				// window.location.href = "<?=GAME_URL?>?highLevel=1&mode=" + mode + "&game=" + gameId;
			}
		},
		error: function(xhr) {
			console.warn(xhr);
		}
	});
}
</script>

<?php if ($enrol_info) { ?>
<script>
setInterval(() => {
	const fd = new FormData();
	fd.append('course_id', '<?php echo $enrol_info['course_id']; ?>');

	submitForm('<?php echo base_url('home/getEventUrl'); ?>', fd, json => {
		if (json.event_url) {
			$('.event-button')
			.attr('href', json.event_url)
			.removeClass('disabled')
			.siblings('.text-danger')
			.addClass('d-none');
		} else {
			$('.event-button')
			.attr('href', '#')
			.addClass('disabled')
			.siblings('.text-danger')
			.removeClass('d-none');
		}

		if (json.certificate_url) {
			$('.cert-button')
			.attr('href', json.certificate_url)
			.removeClass('disabled')
			.siblings('.text-danger')
			.addClass('d-none');
		} else {
			$('.cert-button')
			.attr('href', '#')
			.addClass('disabled')
			.siblings('.text-danger')
			.removeClass('d-none');
		}
	});
}, 2000);
</script>
<?php } ?>

<?php if (time() < strtotime(EVENT_END_DATE)) { ?>
<script src="<?php echo site_url('assets/global/jquery.countdown.js');?>"></script>

<script>
loaded = false;
$('#time-counter').countdown({
	timestamp: <?php echo strtotime(EVENT_START_DATE) * 1000; ?>,
	callback: function(days, hours, minutes, second) {
		if (!loaded && days === 0 && hours === 0 && minutes === 0 && second === 0) {
			$('#time-counter').hide();
			$('#time-note').html('<?php echo EVENT_TITLE . ' ' ._li('is_live_now!'); ?> <?php echo _li('Click_Join_Hackathon_to_continue'); ?>');
			loaded = true;
		}
	}
})
</script>
<?php } ?>
