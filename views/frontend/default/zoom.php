<!DOCTYPE html>
<head>
	<title>Zoom Testing</title>
	<meta charset="utf-8"/>
	<link type="text/css" rel="stylesheet" href="https://source.zoom.us/1.9.6/css/bootstrap.css" />
	<link type="text/css" rel="stylesheet" href="https://source.zoom.us/1.9.6/css/react-select.css" />
	<meta name="format-detection" content="telephone=no">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

	<style>
		body {
			padding-top: 50px;
		}
		.selectpicker {
			height: 34px;
			border-radius: 4px;
		}
	</style>
</head>

<body>
	<nav id="nav-tool" class="navbar navbar-inverse navbar-fixed-top" style="<?php echo $debug ? '' : 'display:none;'; ?>">
		<div class="container">
			<div class="navbar-header">
				<a class="navbar-brand" href="#">Zoom Testing</a>
			</div>
			<div id="navbar">
				<form class="navbar-form navbar-right" id="meeting_form">
					<div class="form-group">
						<input type="text" name="display_name" id="display_name"
							   value="<?php echo $name; ?>" maxLength="100"
							   placeholder="Name" class="form-control" required>
					</div>
					<div class="form-group">
						<input type="email" name="email" id="email"
							   value="<?php echo $email; ?>" maxLength="100"
							   placeholder="Email" class="form-control" required>
					</div>
					<div class="form-group">
						<input type="text" name="meeting_number" id="meeting_number"
							   value="<?php echo $meeting_id; ?>" maxLength="11"
							   style="width:150px" placeholder="Meeting Number" class="form-control" required>
					</div>
					<div class="form-group">
						<input type="text" name="meeting_pwd" id="meeting_pwd"
							   value="<?php echo $meeting_password; ?>" style="width:150px"
							   maxLength="32" placeholder="Meeting Password" class="form-control">
					</div>

					<div class="form-group">
						<select id="meeting_role" class="selectpicker">
							<option value="0" selected>Attendee</option>
							<option value="1">Host</option>
							<option value="5">Assistant</option>
						</select>
					</div>
					<div class="form-group">
						<select id="meeting_lang" class="selectpicker dropdown">
							<option value="en-US" selected>English</option>
							<option value="de-DE">German Deutsch</option>
							<option value="es-ES">Spanish Español</option>
							<option value="fr-FR">French Français</option>
							<option value="jp-JP">Japanese 日本語</option>
							<option value="pt-PT">Portuguese Portuguese</option>
							<option value="ru-RU">Russian Русский</option>
							<option value="zh-CN">Chinese 简体中文</option>
							<option value="zh-TW">Chinese 繁体中文</option>
							<option value="ko-KO">Korean 한국어</option>
						</select>
					</div>

					<button type="submit" class="btn btn-success" id="join_meeting" style="padding: 6px 25px; text-transform: uppercase; font-weight: 700;">Join</button>
					<button type="submit" class="btn btn-default" id="clear_all">Clear</button>
				</form>
			</div>
			<!--/.navbar-collapse -->
		</div>
	</nav>

	<div id="zmmtg-root"></div>
	<div id="aria-notify-area"></div>

<?php if (!$debug) { ?>
<script>
window.addEventListener('load', () => {
	setTimeout(() => {
		document.getElementById('join_meeting').click();
	}, 5000);
});

console.log({meeting_id: '<?php echo $meeting_id; ?>', meeting_pwd: '<?php echo $meeting_password; ?>'})
</script>
<?php } ?>

<script>
	var API_KEY = '<?php echo ZOOM_API_KEY; ?>';
	var API_SECRET = '<?php echo ZOOM_API_SECRET; ?>';
	var leaveUrl = '<?php echo $action; ?>'
</script>

<?php if (0) { ?>
<script src="<?php echo site_url('assets/global/zoom/vendor/react.min.js');?>"></script>
<script src="<?php echo site_url('assets/global/zoom/vendor/react-dom.min.js');?>"></script>
<script src="<?php echo site_url('assets/global/zoom/vendor/redux.min.js');?>"></script>
<script src="<?php echo site_url('assets/global/zoom/vendor/redux-thunk.min.js');?>"></script>
<script src="<?php echo site_url('assets/global/zoom/vendor/jquery.min.js');?>"></script>
<script src="<?php echo site_url('assets/global/zoom/vendor/lodash.min.js');?>"></script>

<script src="<?php echo site_url('assets/global/zoom/vendor/zoom-meeting-1.7.7.min.js');?>"></script>
<?php } ?>

<script src="<?php echo site_url('assets/global/zoom/tool.js');?>"></script>

<script src="https://source.zoom.us/1.9.6/lib/vendor/react.min.js"></script>
<script src="https://source.zoom.us/1.9.6/lib/vendor/react-dom.min.js"></script>
<script src="https://source.zoom.us/1.9.6/lib/vendor/redux.min.js"></script>
<script src="https://source.zoom.us/1.9.6/lib/vendor/redux-thunk.min.js"></script>
<script src="https://source.zoom.us/1.8.3/lib/vendor/jquery.min.js"></script>
<script src="https://source.zoom.us/1.9.6/lib/vendor/lodash.min.js"></script>

<script src="https://source.zoom.us/zoom-meeting-1.9.6.min.js"></script>
<script src="<?php echo site_url('assets/global/zoom/index.js?v=1.1.6');?>"></script>
<script>

</script>
</body>

</html>
