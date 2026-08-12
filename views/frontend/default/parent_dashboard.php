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

// echo "<pre>"; print_r($enrols); die;

$python 	= false;
$blockly 	= false;

foreach ($enrols as &$enrol) {
	$center_info = $this->enrol_model->getCenter($enrol['id']);

	$enrol['center'] = $center_info['center'] ?? '-';

	if (strtotime($enrol['renewal_date'] . ' -1 week') < time()) {
		$enrol['renewal'] 		= true;
		$enrol['amount'] 		= $this->enrol_model->getRenewalAmount($enrol['id']);
	}

	if (strtotime($enrol['renewal_date']) < time()) {
		$enrol['expired'] 		= true;
	}

	if (strpos(mb_strtolower($enrol['course']), 'python') !== false) {
		$python = $enrol;
	}

	if (strpos(mb_strtolower($enrol['course']), 'blockly') !== false) {
		$blockly = $enrol;
	}
}

/*echo "<pre>"; print_r($python);
echo "<pre>"; print_r($blockly);
die;*/

// hc($schedules);
?>
<style>
	.card {
		border-radius: 0px;
		padding: 5px 5px 5px 5px;
		min-height: 84px;
	}
	.card-green, .card.active {
		border: 1px solid #4ca95a;
		background-color: #4ca95a;
		color: #ffffff;
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
	#timer-wrap {
		position: fixed;
		top: 0px;
		left: 50%;
		transform: translateX(-50%);
		z-index: 9999;
		background-color: rgba(255,255,255,0.7);
		padding: 0 20px;
	}
</style>
<section class="page-header-area my-course-area">
	<div class="container">
		<div class="row">
			<div class="col">
				<h1 class="page-title">
					<?php echo _l('welcome'); ?> <?php echo $this->session->name; ?>
					<span style="font-size: 14px;">(<?php _el('School:'); ?> <?php echo $this->config->item('site_name'); ?>)</span>
				</h1>
				<small><?php echo $this->config->item('site_country'); ?></small>
				<br />
			</div>
		</div>
	</div>
</section>

<section class="my-dashboard-area">
	<div class="container">
		<div class="row">
			<div class="col-sm-2 col-lg-2">
				<div class="card card-green">
					<p>World’s#1 &amp; Largest Coding Education Company</p>
				</div>
			</div>
			<div class="col-sm-5 col-lg-5">
				<div class="card card-yellow">
					<p>
						<?php echo $schedule ? vsprintf(_li('Your %sWebinar is Scheduled at %s Please call %s(%s) if it does not start within 5 mins of Scheduled time.'), [
							(($schedule['is_demo'] ?? '') ? 'Demo ' : ''),
							date('h:iA, j F Y', strtotime($schedule['schedule'] ?? time())),
							$schedule['mobile'] ?? '',
							$schedule['name'] ?? '',
						]) : _li('no_webinar'); ?>
					</p>
				</div>
			</div>
			<div class="col-sm-2 col-lg-2">
				<div class="card card-light-yellow">
					<p><?php echo $this->session->name; ?></p>
				</div>
			</div>
			<div class="col-sm-3 col-lg-3">
				<div class="card card-blue">
					<p>
						<?php echo vsprintf(_li('You are student #%s Of the Icode Cohort'), [
							number_format(1559999 + $this->session->user_id)
						]); ?>
					</p>
				</div>
			</div>
		</div>
	</div>
</section>

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
								<?php if (0) { ?>
								<a
									id="downloadCertificate<?php echo strpos(mb_strtolower(trim($enrol_i['course'])), 'python') !== false ? 'Python' : 'Blockly'; ?>"
									href="<?php echo site_url('home/downloadCertificate/'. (strpos(mb_strtolower(trim($enrol_i['course'])), 'python') !== false ? 1 : 0) . '/' . $enrol_i['course_id']); ?>"
									class="btn btn-receipt"
									target="_blank"
									style="display: none;"
								><?php _el('Download Certificate'); ?></a>
								<?php } ?>
								<a
									href="#"
									style="color:black;font-size:12px;"
								><?php echo sprintf(_li('Certificate after <br>Hackathon %s'), date('Y')); ?></a>

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
								<div id="gameList<?php echo strpos(mb_strtolower(trim($enrol_i['course'])), 'python') !== false ? 'Python' : 'Blockly'; ?>"></div>
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

<section class="my-dashboard-area">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-3 col-lg-3 course">
				<h5><?php echo $schedule['course'] ?? ''; ?></h5>

				<p><?php echo $schedule['course_description'] ?? ''; ?></p>
			</div>
			<div class="col-sm-7 col-lg-7 zoom-video">
				<?php if ($schedule) { ?>
				<div id="timer-wrap">
					<p class="text-center" id="zoom-note"><?php echo _li('Your Webinar will start in'); ?></p>
					<div id="zoom-counter"></div>
				</div>
				<?php } ?>
				<img src="<?= base_url().'assets/frontend/default/img/zoom-banner.png?v=1.0'; ?>" id="zoom-banner"/>
				<?php if ($schedule) { ?>
				<iframe src="" style="width: 100%; height: 600px;display:none;" id="zoom-iframe"></iframe>
				<hr />
				<div class="text-right"><a href="<?php echo $this->schedule_model->getScheduleLink(['schedule_id' => $schedule['id']]); ?>" class="btn btn-lg d-none" target="_blank" id="zoom-client"><?php echo _li('Open_zoom_client'); ?></a></div>
				<?php } ?>
			</div>
			<div class="col-sm-2 col-lg-2 quick">
				<h5>Quick facts</h5>
				<p></p>
			</div>
		</div>
	</div>
</section>

<script>
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
						$('#downloadCertificate' + value.gameName).remove();
						$('#downloadCertificate' + value.gameName).css('display', 'none');
					}
				} else {
					$('#downloadCertificate' + value.gameName).remove();
				}
			},
			error: function(xhr) {
				console.warn(xhr);
			}
		});

		html += '<a class="btn btn-success" onclick="enterGame(' + value.gameMode + ',' + value.gameId + ')"><?php _eli('Start Learning'); ?></a>'
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
	},
	success: function(json) {
		console.log(json);
		if(json.code == 0) {
			let html = formatHtml(json.data);
			$("#gameListBlockly").html(html);
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
	},
	success: function(json) {
		console.log(json);
		if(json.code == 0) {
			let html = formatHtml(json.data);
			$("#gameListPython").html(html);
		}
	},
	error: function(xhr) {
		console.warn(xhr);
	}
});
<?php } ?>

function enterGame(mode,gameId) {
	$.ajax({
		url: '<?php echo site_url('api/enterGame'); ?>',
		type: 'POST',
		data: {
			mode: mode,
			gameId: gameId,
		},
		success: function(json) {
			console.log(json);
			if(json.code == 0) {
				window.location.href = "<?=GAME_URL?>?highLevel=1&mode="+mode+"&game="+gameId;
			}
		},
		error: function(xhr) {
			console.warn(xhr);
		}
	});
}
</script>

<?php if ($schedule) { ?>
<script src="<?php echo site_url('assets/global/jquery.countdown.js');?>"></script>

<script>
loaded = false;
$('#zoom-counter').countdown({
	timestamp: <?php echo strtotime($schedule['schedule']) * 1000; ?>,
	callback: function(days, hours, minutes, second) {
		console.log(days, hours, minutes, second)

		if (!loaded && days === 0 && hours === 0 && minutes === 0 && second === 0) {
		// if (!loaded && minutes === 39 && second === 0) {
			$('#zoom-iframe').show().attr('src', '<?php echo site_url('home/zoom') . '?schedule_id=' . ($schedule['id'] ?? 0); ?>');
			$('#zoom-banner').hide();
			$('#zoom-counter').hide();
			$('#zoom-client').removeClass('d-none');
			$('#zoom-note').html('<?php echo _li('Your class has been started..'); ?>');
			loaded = true;
		}
	}
})
</script>
<?php } ?>
