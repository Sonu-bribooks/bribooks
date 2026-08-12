<?php
$start_time = 0 * 60;
$end_time = $start_time + 160;

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

//hc($schedule);
//hc($this->db->last_query());

$enrol_id = $schedule ? $this->class_model->getEnrolId($schedule['class_id'], $this->session->user_id)['enrol_id'] : 0;
?>

<style>
	.card {
		border-radius: 0px;
		padding: 5px 5px 0 5px;
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
</style>
<section class="page-header-area my-course-area">
	<div class="container">
		<div class="row">
			<div class="col">
				<h1 class="page-title"><?php echo _l('welcome'); ?> <?php echo $this->session->name; ?></h1>
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
						<?php echo $schedule ? vsprintf(_li('Your %sClass is Scheduled at %s Please call %s(%s) if it does not start within 5 mins of Scheduled time.'), [
							(($schedule['is_demo'] ?? '') ? 'Demo ' : ''),
							date('h:iA, j F Y', strtotime($schedule['schedule'] ?? time())),
							$schedule['mobile'] ?? '',
							$schedule['name'] ?? '',
						]) : _li('no_schedule'); ?>
					</p>
				</div>
			</div>
			<div class="col-sm-2 col-lg-2">
				<div class="card card-light-yellow">
					<p><?php echo $this->session->name; ?><br><?php echo ($schedule['is_demo'] ?? '') ? _li('Trial Student') : sprintf(_li('Enrol ID:#%s'), $enrol_id); ?></p>
				</div>
			</div>
			<div class="col-sm-3 col-lg-3">
				<div class="card card-blue">
					<p><?php echo vsprintf(_li('You are student #%s Of the LeapLearner Global Cohort'), [
						number_format(1559999 + $this->session->user_id)
					]); ?></p>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="my-dashboard-area">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-3 col-lg-3 course">
				<h5><?php echo $schedule['course'] ?? ''; ?></h5>
				<p><?php echo $schedule['course_description'] ?? ''; ?></p>
<!--				<h3 class="tet-center">&lt;/&gt;</h3>-->
			</div>
			<div class="col-sm-7 col-lg-7 zoom-video">
				<?php if ($schedule) { ?>
				<p class="text-center" id="zoom-note"><?php echo _li('Your Class will start after'); ?></p>
				<div id="zoom-counter"></div>
				<?php } ?>
				<img src="<?= base_url().'assets/frontend/default/img/zoom-banner.png'; ?>" id="zoom-banner"/>
				<?php if ($schedule) { ?>
				<iframe src="" style="width: 100%; height: 600px;display:none;" id="zoom-iframe"></iframe>
				<hr />
				<div class="text-right"><a href="<?php echo $this->schedule_model->getScheduleLink(['schedule_id' => $schedule['id']]); ?>" class="btn btn-lg d-none" target="_blank" id="zoom-client"><?php echo _li('Open_zoom_client'); ?></a></div>
				<?php } ?>
			</div>
			<div class="col-sm-2 col-lg-2 quick">
				<h5>Quick facts</h5>
				<p>All LeapLearner Students participate in Global Coding Hackathons with students participating from 20 Countries All our students are eligible for graded assessment & certification by ICode.org, a Global non-profit focused on Computational Thinking & Algorithmic Intelligence.Our Students also get opportunities to participate in Collaborative Global Boot Camps with children participating from 20+ Countries.</p>
			</div>
		</div>
	</div>
</section>

<section class="my-dashboard-area stage">
	<div class="container">
		<div class="row">
			<div class="col-sm-12 col-lg-12">
				<ul class="progressbar">
					<?php if ($enrol_id > 0) { ?>
					<li class="active"><?php echo _li('Demo Class'); ?></li>
					<li class="active"><?php echo _li('Demo Completed'); ?></li>
					<li class="active"><?php echo _li('Enrolled'); ?></li>
					<?php } else { ?>
					<li class="active"><?php echo _li('Demo Class'); ?></li>
					<li><?php echo _li('Demo Completed'); ?></li>
					<li><?php echo _li('Enrolled'); ?></li>
					<?php } ?>
					<li><?php echo _li('Course Completed'); ?></li>
					<li><?php echo _li('ICode Certification'); ?></li>
				</ul>
			</div>
		</div>
	</div>
</section>

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
