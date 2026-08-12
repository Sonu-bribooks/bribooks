<!Doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $name; ?></title>
<meta name="description" content="" />

<link rel="favicon" href="<?php echo base_url().'assets/frontend/default/img/icons/favicon.ico' ?>">
<link rel="apple-touch-icon" href="<?php echo base_url().'assets/frontend/default/img/icons/icon.png'; ?>">
<!-- font awesome 5 -->
<link rel="stylesheet" href="<?php echo base_url().'assets/frontend/default/css/fontawesome-all.min.css'; ?>">

<link rel="stylesheet" href="<?php echo base_url().'assets/frontend/default/css/bootstrap.min.css'; ?>">
<link rel="stylesheet" href="<?php echo base_url().'assets/frontend/default/css/bootstrap-tagsinput.css'; ?>">
<script src="<?php echo base_url('assets/backend/js/jquery-3.3.1.min.js'); ?>"></script>
</head>
<body>
	<div style="width: 30%; margin: auto;">
		<h1>Theme 2</h1>
		<?php echo $form; ?>
	</div>
<script>
document.addEventListener('FORM_SAVED', function(e) {
	console.log(e);
});
</script>

<script src="<?php echo base_url().'assets/frontend/default/js/bootstrap.min.js'; ?>"></script>
</body>
</html>
