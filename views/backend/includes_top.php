<link rel="shortcut icon" href="<?php echo base_url();?>uploads/system/favicon.png">
<!-- third party css -->
<link href="<?php echo base_url('assets/backend/css/vendor/jquery-jvectormap-1.2.2.css');?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('assets/backend/css/vendor/dataTables.bootstrap4.css');?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('assets/backend/css/vendor/responsive.bootstrap4.css');?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('assets/backend/css/vendor/buttons.bootstrap4.css');?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('assets/backend/css/vendor/select.bootstrap4.css');?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('assets/backend/css/vendor/summernote-bs4.css') ?>" rel="stylesheet" type="text/css" />
<!--link href="<?php echo base_url('assets/backend/css/vendor/fullcalendar.min.css'); ?>" rel="stylesheet" type="text/css" /-->
<link href="<?php echo base_url('assets/backend/css/vendor/dropzone.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('assets/global/fullcalendar/fullcalendar.min.css'); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('assets/global/hopscotch/hopscotch.css'); ?>" rel="stylesheet" type="text/css" />
<!-- third party css end -->
<!-- App css -->
<?php if ($is_dark_mode) { ?>
	<link href="<?php echo base_url('assets/backend/css/app-dark.min.css') ?>" rel="stylesheet" type="text/css" id="main-theme-link" />
<?php } else { ?>
	<link href="<?php echo base_url('assets/backend/css/app.min.css') ?>" rel="stylesheet" type="text/css" id="main-theme-link" />
<?php } ?>
<link href="<?php echo base_url('assets/backend/css/icons.min.css'); ?>" rel="stylesheet" type="text/css" />

<link href="<?php echo base_url('assets/backend/css/main.css') ?>" rel="stylesheet" type="text/css" />

<!-- font awesome 5 -->
<link href="<?php echo base_url('assets/backend/css/fontawesome-all.min.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('assets/backend/css/font-awesome-icon-picker/fontawesome-iconpicker.min.css') ?>" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css">
<link href="<?php echo base_url('assets/global/datetimepicker/bootstrap-datetimepicker.min.css?v=1.3') ?>" rel="stylesheet" type="text/css" />

<meta name="google-signin-client_id" content="752448992196-m67fvv98n9q6923fh0nve58iehkfn1ud.apps.googleusercontent.com">

<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">

<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
<script src="<?php echo base_url('assets/backend/js/jquery-3.3.1.min.js'); ?>" charset="utf-8"></script>
<script src="<?php echo base_url('assets/backend/js/onDomChange.js');?>"></script>

<script src="<?php echo base_url('assets/backend/js/jquery-3.3.1.min.js'); ?>"></script>
<style>

	/*
	i wish this required CSS was better documented :(
	https://github.com/FezVrasta/popper.js/issues/674
	derived from this CSS on this page: https://popper.js.org/tooltip-examples.html
	*/

.popper, .tooltip {
	position: absolute;
	z-index: 9999;
	background: #FFC107;
	color: black;
	width: 150px;
	border-radius: 3px;
	box-shadow: 0 0 2px rgba(0,0,0,0.5);
	padding: 10px;
	text-align: center;
}
.style5 .tooltip {
	background: #1E252B;
	color: #FFFFFF;
	max-width: 200px;
	width: auto;
	font-size: .8rem;
	padding: .5em 1em;
}
.popper .popper__arrow,
.tooltip .tooltip-arrow {
	width: 0;
	height: 0;
	border-style: solid;
	position: absolute;
	margin: 5px;
}

.tooltip .tooltip-arrow,
.popper .popper__arrow {
	border-color: #FFC107;
}
.style5 .tooltip .tooltip-arrow {
	border-color: #1E252B;
}
.popper[x-placement^="top"],
.tooltip[x-placement^="top"] {
	margin-bottom: 5px;
}
.popper[x-placement^="top"] .popper__arrow,
.tooltip[x-placement^="top"] .tooltip-arrow {
	border-width: 5px 5px 0 5px;
	border-left-color: transparent;
	border-right-color: transparent;
	border-bottom-color: transparent;
	bottom: -5px;
	left: calc(50% - 5px);
	margin-top: 0;
	margin-bottom: 0;
}
.popper[x-placement^="bottom"],
.tooltip[x-placement^="bottom"] {
	margin-top: 5px;
}
.tooltip[x-placement^="bottom"] .tooltip-arrow,
.popper[x-placement^="bottom"] .popper__arrow {
	border-width: 0 5px 5px 5px;
	border-left-color: transparent;
	border-right-color: transparent;
	border-top-color: transparent;
	top: -5px;
	left: calc(50% - 5px);
	margin-top: 0;
	margin-bottom: 0;
}
.tooltip[x-placement^="right"],
.popper[x-placement^="right"] {
	margin-left: 5px;
}
.popper[x-placement^="right"] .popper__arrow,
.tooltip[x-placement^="right"] .tooltip-arrow {
	border-width: 5px 5px 5px 0;
	border-left-color: transparent;
	border-top-color: transparent;
	border-bottom-color: transparent;
	left: -5px;
	top: calc(50% - 5px);
	margin-left: 0;
	margin-right: 0;
}
.popper[x-placement^="left"],
.tooltip[x-placement^="left"] {
	margin-right: 5px;
}
.popper[x-placement^="left"] .popper__arrow,
.tooltip[x-placement^="left"] .tooltip-arrow {
	border-width: 5px 0 5px 5px;
	border-top-color: transparent;
	border-right-color: transparent;
	border-bottom-color: transparent;
	right: -5px;
	top: calc(50% - 5px);
	margin-left: 0;
	margin-right: 0;
}

.error{
	color: #df1515;
}
</style>
