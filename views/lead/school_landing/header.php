<!Doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo _li('ICode Signup'); ?></title>
<meta name="description" content="" />

<link rel="favicon" href="<?php echo base_url().'assets/frontend/default/img/icons/favicon.ico' ?>">
<link rel="apple-touch-icon" href="<?php echo base_url().'assets/frontend/default/img/icons/icon.png'; ?>">
<!-- font awesome 5 -->
<link rel="stylesheet" href="<?php echo base_url().'assets/frontend/default/css/fontawesome-all.min.css'; ?>">

<link rel="stylesheet" href="<?php echo base_url().'assets/frontend/default/css/bootstrap.min.css'; ?>">
<link rel="stylesheet" href="<?php echo base_url().'assets/frontend/default/css/bootstrap-tagsinput.css'; ?>">
<link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet' />
<script src="<?php echo base_url('assets/backend/js/jquery-3.3.1.min.js'); ?>"></script>

<meta name="google-signin-client_id" content="555807529387-9meglfos33tc25rufdq2440stskgp9tt.apps.googleusercontent.com">
<script src="https://apis.google.com/js/platform.js" async defer></script>

<script type="text/javascript" src="https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js"></script>


<style>
body {
	background-color: transparent;
	background-size: cover;
	position: inherit;
	width: auto;
	background-position: top center;
	background-repeat: no-repeat;
	overflow: hidden;
	padding-bottom: 40px;
	height: 100%;
	min-height: 100vh;
}
.bdr {
	display:none;
}
h1, .h1 {
	font-size: 27px;
}
h2, .h2 {
	font-size: 25px;
	margin: 0px;
}
h1, .h1, h2, .h2, h3, .h3 {
	margin:0px;
}
.m-t-20 {
	margin-top: 20px !important;
}
.btn-custom {
	color: #fff;
	background-color: #f0ad4e00;
	border-color: #fff;
}
.form-title {
	color: #fff;
}
.btn {
	font-weight: 600;
}
.hide {
	display: none;
}
.error {
	font-size: 80%;
	color: <?=$theme_color?> !important;
}
table tr th {
	background-color: unset !important;
	color: #000000 !important;
}
.details label {
	color: #888888;
}
.details label > span {
	font-weight: 200;
}
.clearfix > h3 {
	color: #FFFFFF !important;
	/*color: #f2903d !important;*/
}
.sweet-alert p {
	color: #575757 !important;
	font-weight: 400 !important;
}
.btn-verify {
	padding: 11px 12px !important;
}
.tagline {
	margin-bottom:5px; color:#fff;
}
h4 {
	line-height: 1.4 !important;
}
p.sty{font-size:17px;}
h1{margin-top: -10px;}
h1{color:#000;}
h2{color:#000;}
p{color:#000;}
.box {
	box-shadow: 0 2px 4px 2px rgba(81,81,81,.22);
	padding:35px 25px 5px 25px;
}
.info_items {
	display: flex;
	margin-bottom: 40px;
}
.info_items .icons.icon-do-not-disturb-rounded-sign {
	color: #7ac29a;
}
.info_items .icons {
	font-size: 35px;
	margin-right: 15px;
}
.bgg{background-color: #042344;}
[class*=" icon-"], [class^=icon-] {
	font-family: icomoon!important;
	speak: none;
	font-style: normal;
	font-weight: 400;
	font-feature-settings: normal;
	font-variant: normal;
	text-transform: none;
	line-height: 1;
	-webkit-font-smoothing: antialiased;
	-moz-osx-font-smoothing: grayscale;
}
.info_items .icons.icon-do-not-disturb-rounded-sign {
	color: #7ac29a;
}
.icon-do-not-disturb-rounded-sign:before {
	content: "\E905";
}
.info_items .icons.icon-lock {
	color: #7a9e9f;
}
.icon-lock:before {
	content: "\E907";
}
.info_items .icons.icon-support {
	color: #f3bb45;
}
.icon-support:before {
	content: "\E908";
}
.info_items .icons.icon-exclamation-mark {
	color: #f90517;
}
.icon-exclamation-mark:before {
	content: "\E906";
}
.info_details .header {	margin-bottom: 15px;	font-size:20px;}
.trust-icons h4{ margin-top:5px; margin-bottom:10px;}
.reg_div{  font-size:12px; padding:10px; margin:25px 0 75px 0;}
.trust-icons img {width: 50px;}
.trust-icons h4{ font-size:15px;}
.form-control, .input-group>.custom-select:not(:first-child), .input-group>.form-control:not(:first-child), .form-control:focus, .form-control:active{
	border: unset;
	border-radius: 5px;
	background: #f2f2f2;
	outline: unset;
	box-shadow: unset;
	height: 50px !important;
}
.input-group>.custom-select:not(:first-child), .input-group>.form-control:not(:first-child) {
	margin-left: 10px;
}
.heading {
    margin-top: 20px;
    color: #263787;
    font-weight: bolder;
    font-size: 200%;
	border-right: 2px solid;
    padding-right: 20px;
}
.subheading {
    font-weight: normal;
    margin-top: 10px;
    font-size: 80%;
}
#carousel-form {
	padding-bottom: 50px;
}
.carousel-indicators li {
	width: 15px;
    height: 15px;
    border-radius: 7.5px;
	background-color: #f3f3f3;
}
.carousel-indicators .active {
    background-color: #263787;
}

.abcRioButtonBlue, .abcRioButtonBlue:hover {
    background-color: #f2f2f2 !important;
    border-radius: 7px !important;
    box-shadow: unset !important;
	color: #000 !important;
}
.abcRioButtonBlue .abcRioButtonIcon {
    background-color: transparent !important;
}
@media (min-width: 992px) {
	.m-t10 {
		margin-top: 10%;
	}
}
@media screen and (max-width: 1080px) {
	body {
		background-position: center top;
	}
	h1{font-size:20px;}
	h2{font-size:18px; margin-top:10px;}
	.trust-icons h4{ font-size:12px;}
	h3, .h3 {
		font-size: 20px;
	}
	.trust-icons img {width: 65px;}
}
@media (max-width: 768px) {
	.select2 {
		padding-left: 0;
		padding-right: 0;
	}
	.container {
		padding: 0;
	}
	.fm {
		padding: 0;
	}
	.abcRioButtonBlue {
		margin: auto;
		margin-bottom: 20px;
	}
}
@media screen and (max-width: 480px) {
	.bgg { background-color: #042344; }
	.bdr {
		width: 59%;display:block;
		border-bottom: 4px solid #d5f8f39e;
		padding: 0px;
		margin-top: 4px;margin-bottom: 0px;
	}
	.tagline {
		padding: 14px 0px 0px 0px;
		font-size: 14px;
	}
}
@media only screen and (min-device-width: 767px) {
	.col-sm-6 {
        -webkit-box-flex: 0;
        -ms-flex: 0 0 50%;
        flex: 0 0 50%;
        max-width: 50%
    }
	.btn-verify {
	    min-width: 200px;
	}
	.heading {
	    margin-top: 0px;
	}
}
.theme-color {
	color: <?php echo $theme_color; ?>;
}
.btn-warning {
	background-color: <?=$theme_color?>;
	border-color: <?=$theme_color?>;
	color: #ffffff;
	border-radius: 25px;
	box-shadow: 2px 4px 7px -3px #000000;
}
.term {
	margin-top: 20px;
	font-size: 13;
}
.term a {
	color: <?=$theme_color?>;
}
.btn-kb {
	background: #273787;
	color: #fff;
	padding: 10px 25px !important;
}
#amount {
	font-weight: bolder;
}
</style>
</head>
<body>
