
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
			font-size: 16px;
			background-color: #E3E8FE;
		}
		.container {
			background-color: #E3E8FE;
			padding-bottom: 20px;
		}
		.header {
			/* background: transparent radial-gradient(closest-side at 50% 50%, #1268ACFE 0%, #023157 100%) 0% 0% no-repeat padding-box; */
			background: #fff;
			margin-bottom: 20px;
		}
		@media screen and (prefers-color-scheme: light) {
			.header {
				background: #fff;
			}
		}
		.box {
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
		}
		footer {
			text-align: center;
			background-color: #E3E8FE;
		}
		.footer-text {
			color: #fff!important;
			margin: 0;
			padding: 30px;
			font-size: 2rem;
		}
		.footer-img {
			height: auto;
			width: 100%;
			display: block;
			margin: auto;
		}
		.footer-link-cover {
			/* background-image: url(<?=base_url('assets/images/nyaf/bgfooter.png')?>); */
			background-size: 100%
		}
		.footer-link {
			cursor: pointer;
			background: transparent linear-gradient(108deg, #1488cc 0%, #a551d0 47%, #e34c56 100%) 0% 0% no-repeat padding-box;
			padding: .5rem 1.5rem;
			line-height: 1.5;
			color: white !important;
			border-radius: 50rem;
			border: none;
			font-weight: bold;
			display: inline-block;
			text-decoration: none;
			margin-top: 20px;
			margin-bottom: 20px;
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
		<div class="header">
			<img
				src="<?=base_url('assets/images/nyaf/headerjury.png?v=1.1')?>"
				height="150"
				style="margin: auto;display: block;padding: 20px;"
			/>
		</div>
		<div class="box">
