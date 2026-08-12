
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
	<title><?php _el('icode assessment'); ?> | <?php echo get_settings('system_name'); ?></title>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="author" content="<?php echo get_settings('author') ?>" />

	<meta name="keywords" content="<?php echo get_settings('website_keywords'); ?>"/>
	<meta name="description" content="<?php echo get_settings('website_description'); ?>" />

	<link name="favicon" type="image/x-icon" href="<?php echo base_url('uploads/system/favicon.png'); ?>" rel="shortcut icon" />

	<link rel="stylesheet" href="<?php echo base_url().'assets/frontend/default/css/bootstrap.min.css'; ?>">
	<link rel="stylesheet" href="<?php echo base_url().'assets/frontend/default/css/bootstrap-tagsinput.css'; ?>">
	<link rel="stylesheet" href="<?php echo base_url().'assets/frontend/default/css/responsive.css'; ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/global/toastr/toastr.css'); ?>">

	<link href="<?php echo base_url('assets/frontend/default/lr/style.css?v=1.0.6'); ?>" rel="stylesheet" type="text/css" />

	<script src="<?php echo base_url('assets/frontend/default/js/vendor/jquery-3.2.1.min.js'); ?>"></script>
	<script src="<?php echo base_url('assets/global/webrtc/RecordRTC.js'); ?>"></script>
</head>
<body>

<?php require_once __DIR__ . '/' . $page_name . '.php'; ?>

<script src="<?php echo base_url().'assets/frontend/default/js/bootstrap.min.js'; ?>"></script>
<script src="<?php echo site_url('assets/frontend/default/lr/script.js');?>"></script>
<script src="<?php echo base_url().'assets/global/toastr/toastr.min.js'; ?>"></script>
<script type="text/javascript">
function success_notify(message) {
	toastr.success(message);
}

function error_notify(message) {
	toastr.error(message);
}
</script>
<script>
const submitForm = (url, data, cb) => {
	$.ajax({
		url: url,
		type: 'post',
		dataType: 'json',
		data: data,
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function() {
		},
		complete: function() {
		},
		success: function(json) {
			cb(json);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
};
</script>
</body>
</html>
