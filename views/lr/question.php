<style>
body {
	font-size: 100%;
	background-image: url('<?php echo site_url('assets/frontend/default/lr/bgs/question.png'); ?>');
	background-position: center;
	background-color: #f28f3d;
}
</style>
<div class="container">
<div class="question-container">
	<div id="quiz-counter"></div>
	<header>
		<img src="<?php echo site_url('assets/frontend/default/lr/images/chotu.png'); ?>" class="user">
		<p><b><?php _el('name'); ?></b><br><?php echo $user['first_name'] ?? ''; ?> <?php echo $user['last_name'] ?? ''; ?></p>
		<img src=<?php echo base_url('uploads/system/logo-light.png'); ?> class="qlogo">
		<label>
			<img src=<?php echo site_url('assets/frontend/default/lr/images/chotu.png'); ?> class="qlogo">
		</label>
	</header>

	<?php if (!empty($question)) { ?>
	<div class="time-and-questions">
		<div class="wid50">
			<p class="left35">
				<span><?php _el('question'); ?> : </span>
				<label id="current_index"><?php echo $current_index; ?></label>
				<span>/</span>
				<label><?php echo $total_questions; ?></label>
			</p>

			<p class="right65">
				<span id="timer"><?php _el('time_remaining'); ?> : 29 Min 59 Seconds</span>
				<img
					src=<?php echo site_url('assets/frontend/default/lr/images/hourglass.png'); ?>
					style="width:15px; float:right;padding: 4px 0px;"
				/>
			</p>
		</div>
	</div>

	<form class="required-form" id="form-quiz" action="<?php echo $action ; ?>" method="post" enctype="multipart/form-data">
		<div id="question-layout">
			<?php echo $question; ?>
		</div>

		<div class="answer">
			<a href="<?php echo site_url('assessment'); ?>" id="retake" style="display:none;"><?php _el('home'); ?></a>
			<div><a id="skip-question"><?php _el('skip_question'); ?></a></div>
			<input type="submit" name="submit" id="button-submit" value="<?php _el('submit_&amp;_proceed'); ?>">
			<img src=<?php echo site_url('assets/frontend/default/lr/images/chotu.png'); ?>>
		</div>
	</form>
	<?php } elseif ($summary) { ?>
	<div class="time-and-questions">
		<div class="wid50">
			<p class="left35">
				<span><?php _el('question'); ?> : </span>
				<label id="current_index"><?php echo $current_index; ?></label>
				<span>/</span>
				<label><?php echo $total_questions; ?></label>
			</p>

			<p class="right65">
				<span id="timer"><?php _el('time_remaining'); ?> : 29 Min 59 Seconds</span>
				<img
					src=<?php echo site_url('assets/frontend/default/lr/images/hourglass.png'); ?>
					style="width:15px; float:right;padding: 4px 0px;"
				/>
			</p>
		</div>
	</div>
	<form class="required-form" id="form-quiz" action="<?php echo $action ; ?>" method="post" enctype="multipart/form-data">
		<div id="question-layout">
			<?php echo $summary; ?>
		</div>
		<div class="answer">
			<a href="<?php echo site_url('assessment'); ?>" id="retake" style="display:none;"><?php _el('home'); ?></a>
		</div>
	</form>
	<?php } elseif ($report) { ?>
	<div id="question-layout">
		<?php echo $report; ?>
	</div>
	<div class="answer">
		<a href="<?php echo site_url('assessment'); ?>" id="retake"><?php _el('home'); ?></a>
	</div>
	<?php } else { ?>
	<div class="answer">
		<h2 style="color:#fff;"><?php echo _li('assessment_not_created_yet'); ?></h2>
		<a href="<?php echo site_url('assessment'); ?>" id="retake"><?php _el('home'); ?></a>
	</div>
	<?php } ?>

	<div class="bottom">
		<label class="lf">
			<img
				src=<?php echo site_url('assets/frontend/default/lr/images/lightbulb.png'); ?>
				style="width: 25px"
			/>
		</label>
		<label class="ri">
			<img
				src=<?php echo site_url('assets/frontend/default/lr/images/lightbulb.png'); ?>
				style="width: 25px"
			/>
		</label>
	</div>

	<video
		id="recorder"
		playsinline
		autoplay=""
		style="width:200px;height:200px;position:absolute;top:70px;right:40px;"
	></video>
</div>
</div>

<?php if (!empty($question) || !empty($summary)) { ?>
<script>
$(document).on('change', 'input[name=answer]', function() {
	$('input[name=answer]').closest('label').css({"background-color": "#fff"});
	$('input[name=answer]:checked').closest('label').css({"background-color": "rgba(255,255,255,0.5)"});
});
</script>

<script>
const processResponse = json => {
	$('#button-submit').val('<?php _el('submit_&_proceed'); ?>');
	$('#skip-question').show();

	if (json.error) {
		error_notify(json.error)
	}

	if (json.success) {
		success_notify(json.success)
	}

	if (json.current_index) {
		$('#current_index').html(json.current_index);
	}

	if (json.question) {
		$('#skip-question').show();
		$('.time-and-questions').show();
		$('#button-submit').show();
		$('#question-layout').html(json.question);
	}

	if (json.answer) {
		$('#question-layout').html(json.answer);
		$('#button-submit').val('<?php _el('next_question'); ?>');
		$('#skip-question').hide();
	}

	if (json.last) {
		$('#button-submit').val('<?php _el('submit'); ?>');
	}

	if (json.summary) {
		$('#button-submit').hide();
		$('#skip-question').hide();
		$('#question-layout').html(json.summary);
	}
	if (json.finished) {
		stopVideo();
		$('#retake').show();
		$('#skip-question').hide();
		$('.time-and-questions').hide();
		$('#button-submit').hide();
		$('#question-layout').html(json.finished);

		window.onbeforeunload = null;
	}
}
</script>
<script>
var message = "<?php _el('are_u_sure?'); ?>";
window.onbeforeunload = function(event) {
	var e = e || window.event;
	if (e) {
		e.returnValue = message;
	}
	return message;
};
</script>
<script>
$('#skip-question').on('click', function(e) {
	e.preventDefault();
	const data = new FormData();
	data.append('skip', true);
	data.append('question_id', $('#question_id').val());
	submitForm('<?php echo site_url('assessment/nextQuestion'); ?>', data, json => {
		processResponse(json)
	})
});
</script>
<script>
$('#form-quiz').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	let url = $el.attr('action')

	if (!camera) {
		error_notify('<?php _el('allow_camera_to_continue_the_assessment'); ?>');
		return;
	}

	if ($('input[name=answer]').length > 0) {
		if (!$('input[name=answer]:checked').val() || $('input[name=answer]:checked').val() == '') {
			error_notify('<?php _el('select_option'); ?>');
			return;
		}
	} else {
		url = '<?php echo site_url('assessment/nextQuestion'); ?>';
	}

	submitForm(url, new FormData($el[0]), json => {
		processResponse(json)
	})
})
</script>
<script>
const completeQuiz = () => {
	submitForm('<?php echo site_url('assessment/finish'); ?>', null, json => {
		processResponse(json)
	})
}
</script>

<script>
const countDownDate = new Date("<?php echo $quiz_end_time; ?>").getTime();

var x = setInterval(function() {
	const now = new Date().getTime();
	let distance = countDownDate - now;

	let days = Math.floor(distance / (1000 * 60 * 60 * 24));
	let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
	let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
	let seconds = Math.floor((distance % (1000 * 60)) / 1000);

	document.getElementById('timer').innerHTML = "<?php _el('time_remaining'); ?> : " + minutes + " Min " + seconds + " Seconds";
	/*document.title = minutes + " Min " + seconds + " Seconds";*/

	if (distance <= 0) {
		clearInterval(x);
		completeQuiz();
	}
}, 1000);
</script>
<?php } ?>

<?php if (!empty($question)) { ?>
<script>
var recorder = null;
var camera = null;
var recodingInterval = null;

function stopVideo() {
	document.getElementById('recorder').srcObject = null;
	camera && camera.getTracks().forEach(function(track) {
		track.stop();
	});
	clearInterval(recodingInterval);
}

function recordVideo() {
	recorder.stopRecording(function() {
		const fileObject = new File([recorder.getBlob()], getFileName('webm'), {
			type: 'video/webm'
		});

		uploadToServer(fileObject);
	});

	recorder.startRecording();
}

function checkCamera() {
	navigator.mediaDevices.getUserMedia({ video: true, audio: false }).then(function(cameraRes) {
		const video = document.getElementById('recorder');
		video.muted = false;
		video.srcObject = cameraRes;
		video.controls = false;

		recorder = RecordRTC(cameraRes, {
			type: 'video'
		});

		recorder.startRecording();
		recorder.camera = cameraRes;
		camera = cameraRes;

		recodingInterval = setInterval(recordVideo, 10000);
		cameraCheckInterval && clearInterval(cameraCheckInterval);
	}).catch(error => {
		/*alert('<?php _el('Camera permission is required'); ?>')*/
		console.log(error)

		if (typeof cameraCheckInterval === 'undefined') {
			cameraCheckInterval = setInterval(checkCamera, 1000)
		}
	})
}

checkCamera();

function uploadToServer(blob) {
	const fd = new FormData();
	fd.append('filename', blob.name);
	fd.append('video', blob);

	submitForm('<?php echo base_url('assessment/recordVideo'); ?>', fd, json => {
		console.log(json)
	})
}

function getFileName(fileExtension) {
	const d = new Date();
	return 'Vid-' + d.getUTCFullYear() + d.getUTCMonth() + d.getUTCDate() + '-' + getRandomString() + '.' + fileExtension;
}

function getRandomString() {
	if (window.crypto && window.crypto.getRandomValues && navigator.userAgent.indexOf('Safari') === -1) {
		const a = window.crypto.getRandomValues(new Uint32Array(3)),
			token = '';
		for (let i = 0, l = a.length; i < l; i++) {
			token += a[i].toString(36);
		}
		return token;
	} else {
		return (Math.random() * new Date().getTime()).toString(36).replace(/\./g, '');
	}
}
</script>
<?php } ?>
