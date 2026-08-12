
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
		*{
			font-family: 'Signika', sans-serif;
		}
		body {
			padding: 0;
			margin: 0;
			width: 100%;
			height: 100%;
		}
		.box {
			/* text-align: center; */
			border-radius: 15px;
			margin:auto;
			width: 75% ;
			background-color: #E3E8FE;
			padding: 20px;
		}
		.box1 {
			background-color: white;
			width: 70%;
			margin: auto;
			border-radius: 15px;
			padding: 10px;
			font-size: 20px;
		}
		p {
			font-size: 20px;
		}
		footer {
			text-align: center;
			padding-top: 20px;
		}
		ul, ul li {
			list-style-type: none;
		}
		@media (max-width: 767px) {
			.box, .box1 {
				width: auto;
				display: block;
				margin: auto 15px;
			}
		}
		.social-icon {
			width: 50px;
		}
	</style>
<body>
	<div class="container">
		<?php if (0) { ?>
		<img
			src="<?=site_url('uploads/system/logo-dark.png')?>"
			height="35"
			style="margin: auto;display: block;padding: 20px;"
		/>
		<?php } ?>
		<div class="box">
