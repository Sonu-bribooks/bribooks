<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title><?=$title?></title>
	<link
		rel="preconnect"
		href="https://fonts.googleapis.com"
	/>
	<link
		rel="preconnect"
		href="https://fonts.gstatic.com"
		crossOrigin="anonymous"
	/>
	<link
		href="https://fonts.googleapis.com/css2?family=Signika:wght@300;400;500;600;700&display=swap"
		rel="stylesheet"
	/>
	<style>
		body {
			font-family: 'Signika', Arial, sans-serif;
			font-weight: 300;
			padding: 0;
			margin: 0;
			width: 100%;
			height: 100%;
		}
		.text-center {
			text-align: center;
		}
		.bold {
			font-size: 120%;
			font-weight: bold;
		}
		.text-upper {
			text-transform: uppercase;
		}
		.preheader {
			color: transparent;
			display: none;
			height: 0;
			max-height: 0;
			max-width: 0;
			opacity: 0;
			overflow: hidden;
			mso-hide: all;
			visibility: hidden;
			width: 0;
		}
		.logo {
			height: 30px;
			position: relative;
		}
		.logo-text {
			font-size: 110%;
			font-weight: bold;
		}
		.container {
			width: 400px;
			margin: auto;
			display: block;
		}
		.footer {
			background-color: #e3e8fe;
			padding-bottom: 30px;
		}
		a {
			color: black;
		}
		.main {
			position: relative;
			padding-top: 30px;
			background-color: #e3e8fe;
		}
		.main-white {
			position: absolute;
			background-color: #FDF5F1;
			top: 0;
			left: 0;
			right: 0;
			height: 30%;
		}
		.book-wrapper {
			background-color: #162748;
			position: relative;
			margin: auto;
			display: block;
			margin-top: 30px;
			margin-bottom: 50px;
			width: 245px;
			height: 340px;
		}
		.text-horizontal, .text-vertical {
			text-transform: uppercase;
			color: #fff;
			font-size: 90%;
			text-align: left;
		}
		.text-horizontal {
			letter-spacing: 6px;
			display: block;
			padding-top: 15px;
			text-align: left;
			margin-left: 15px;
		}
		.text-vertical {
			width: 10px;
			height: 100%;
			word-break: break-all;
			line-height: 17px;
			float: left;
		}
		.book-inner {
			width: 250px;
			height: 350px;
			margin-left: 15px;
		}
		.book-thumb {
			width: 220px;
			height: 308px;
			margin-top: 15px;
			margin-left: 15px;
		}
		.social-icon {
			width: 50px;
		}
	</style>
</head>
<body>
<span class="preheader"><?=$heading?></span>
<div class="main">
<span class="main-white"></span>
<div class="text-center">
	<img
		src="<?=site_url('uploads/system/logo-dark.png')?>"
		class="logo"
		alt="BriBooks"
	/>
</div>
<div class="container text-center">
