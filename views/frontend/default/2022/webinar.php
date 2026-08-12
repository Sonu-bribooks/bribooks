<?php
$start_time = 0 * 60;
// $end_time = $start_time + 160;
$end_time = $start_time + (23 * 60);

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

// hc($schedule);
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
		box-shadow: unset;
		background: unset;
		border: unset;
		margin-bottom: 20px;
		margin-top: 70px;
	}
	.dev-icon {
		height: 20px;
		content: url('data:image/png;base64,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');
	}
	.course-card .page-title {
		font-size: 300% !important;
		font-weight: bolder !important;
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
		background-position: 0 -135px;
	}
	.sprite-certificate {
		background-position: 0 -270px;
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
		background-color: #273787;
		border: 1px solid #273787;
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
	section.page-header-area {
		min-height: 550px;
		background-position: 0px -49px;
	}
	@media screen and (max-width: 420px) {
		.course-card {
			padding: 0;
			margin-top: 15px;
		}
		section.page-header-area {
			min-height: 35vh;
			background-position: 0px 0px;
		}
		section.page-header-area .page-title {
			font-size: 250% !important;
		}
		.quick {
			margin-top: 30px;
		}
	}
	@media screen and (max-height: 420px) and (orientation: landscape) {
		.col-sm-5, .col-sm-4 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 60%;
			flex: 0 0 60%;
			max-width: 60%;
		}
		section.page-header-area {
			min-height: 70vh;
			background-position: 0px 0px;
		}
		.container {
			max-width: 100%;
		}
		.course-card {
			padding: 0;
			margin-top: 15px;
		}
	}
</style>
<section class="page-header-area my-course-area">
	<div class="container">
		<div class="row">
			<div class="col-sm-4">
				<div class="card course-card">
					<h1 class="page-title">
						<?php _el('webinar'); ?>
					</h1>
					<p>The webinars are teacher led and
live and  are designed to help
students understand the iCode
learning platform and solve
problems in a scaffolded level.</p>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="my-dashboard-area">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-3 col-lg-3 course">
				<h5><?php echo $course_info['title'] ?? ''; ?></h5>

				<p><?php echo $course_info['description'] ?? ''; ?></p>


				<h3><?php _el('upcoming_webinar'); ?></h3>
				<h4
					class="text-center"
					id="upcoming-schedule"
				></h4>

				<div class="text-center">
					<button
						class="btn btn-primary btn-kb btn-sm"
						data-toggle="modal"
						data-target="#event-modal"
					>
						<?php _el('check_calendar'); ?>
					</button>
				</div>
			</div>
			<div class="col-sm-6 col-lg-6 zoom-video">
				<?php if ($schedule) { ?>
				<div id="timer-wrap">
					<p class="text-center" id="zoom-note"><?php echo _li('Your Webinar will start in'); ?></p>
					<div id="zoom-counter"></div>
				</div>
				<?php } ?>
				<img src="<?= base_url().'assets/frontend/default/img/zoom-banner.png?v=1.1'; ?>" id="zoom-banner"/>
				<?php if ($schedule) { ?>
				<iframe src="" style="width: 100%; height: 600px;display:none;" id="zoom-iframe"></iframe>
				<hr />
				<div class="text-right">
					<a
						href="<?php echo $this->schedule_model->getScheduleLink(['schedule_id' => $schedule['id']]); ?>"
						class="btn btn-lg d-none btn-kb"
						target="_blank"
						id="zoom-client"
					>
						<?php echo _li('Open_zoom_client'); ?>
					</a>
				</div>
				<?php } ?>
			</div>
			<div class="col-sm-3 col-lg-3 quick">
				<h5><?php _el('webinar_details'); ?></h5>
				<p><?php _el('host'); ?>: <?php echo $schedule['name'] ?? ''; ?></p>
				<p><?php _el('contact_mobile'); ?>: <?php echo $schedule['mobile'] ?? ''; ?></p>
				<p><?php _el('contact_email'); ?>: <a href="mailto:hackathon@icode.org">hackathon@icode.org</a></p>
			</div>
		</div>
	</div>
</section>

<?php require __DIR__ . '/calendar.php'; ?>

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
			$('#zoom-note').html('<?php echo _li('Your webinar has been started..'); ?>');
			loaded = true;
		}
	}
})
</script>
<?php } ?>

<script>
$(function() {
	$.getJSON('<?php echo base_url('home/ajax_webinar_schedule/upcoming'); ?>', function(json) {
		if (json.upcoming_schedule) {
			$('#upcoming-schedule').html(moment.utc(json.upcoming_schedule).local().format('MMM DD, YYYY hh:mm A'))
		}
	});
})
</script>
