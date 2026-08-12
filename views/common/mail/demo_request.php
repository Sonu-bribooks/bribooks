<!Doctype html>
<html>
<head>
	<meta name="viewport" content="width=device-width" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?php echo $title; ?></title>
	<style>
		/* -------------------------------------
				GLOBAL RESETS
		------------------------------------- */

		/*All the styling goes here*/

		img {
			border: none;
			-ms-interpolation-mode: bicubic;
			max-width: 100%;
		}

		body {
			background-color: #f6f6f6;
			font-family: sans-serif;
			-webkit-font-smoothing: antialiased;
			font-size: 14px;
			line-height: 1.4;
			margin: 0;
			padding: 0;
			-ms-text-size-adjust: 100%;
			-webkit-text-size-adjust: 100%;
		}

		table {
			border-collapse: separate;
			mso-table-lspace: 0pt;
			mso-table-rspace: 0pt;
			width: 100%; }
			table td {
				font-family: sans-serif;
				font-size: 14px;
				vertical-align: top;
		}

		/* -------------------------------------
				BODY & CONTAINER
		------------------------------------- */

		.body {
			background-color: #f6f6f6;
			width: 100%;
		}

		/* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
		.container {
			display: block;
			margin: 0 auto !important;
			/* makes it centered */
			max-width: 580px;
			padding: 10px;
			width: 580px;
		}

		/* This should also be a block element, so that it will fill 100% of the .container */
		.content {
			box-sizing: border-box;
			display: block;
			margin: 0 auto;
			max-width: 580px;
			padding: 10px;
		}

		/* -------------------------------------
				HEADER, FOOTER, MAIN
		------------------------------------- */
		.main {
			background: #ffffff;
			border-radius: 3px;
			width: 100%;
		}

		.wrapper {
			box-sizing: border-box;
			padding: 20px;
		}

		.wrapper-text {
			box-sizing: border-box;
			padding: 5px 20px;
		}

		.content-block {
			padding-bottom: 10px;
			padding-top: 10px;
		}

		.footer {
			clear: both;
			margin-top: 10px;
			text-align: center;
			width: 100%;
		}
			.footer td,
			.footer p,
			.footer span,
			.footer a {
				color: #999999;
				font-size: 12px;
				text-align: center;
		}

		/* -------------------------------------
				TYPOGRAPHY
		------------------------------------- */
		h1,
		h2,
		h3,
		h4 {
			color: #000000;
			font-family: sans-serif;
			font-weight: 400;
			line-height: 1.4;
			margin: 0;
			margin-bottom: 30px;
		}

		h1 {
			font-size: 35px;
			font-weight: 300;
			text-align: center;
			text-transform: capitalize;
		}

		p,
		ul,
		ol {
			font-family: sans-serif;
			font-size: 14px;
			font-weight: normal;
			margin: 0;
			margin-bottom: 15px;
		}
			p li,
			ul li,
			ol li {
				list-style-position: inside;
				margin-left: 5px;
		}

		a {
			color: #f2903d;
			text-decoration: underline;
		}

		/* -------------------------------------
				BUTTONS
		------------------------------------- */
		.btn {
			box-sizing: border-box;
			width: 100%; }
			.btn > tbody > tr > td {
				padding-bottom: 15px; }
			.btn table {
				width: auto;
		}
			.btn table td {
				background-color: #ffffff;
				border-radius: 5px;
				text-align: center;
		}
			.btn a {
				background-color: #ffffff;
				border: solid 1px #f2903d;
				border-radius: 5px;
				box-sizing: border-box;
				color: #f2903d;
				cursor: pointer;
				display: inline-block;
				font-size: 14px;
				font-weight: bold;
				margin: 0;
				padding: 12px 25px;
				text-decoration: none;
				text-transform: capitalize;
		}

		.btn-primary table td {
			background-color: #f2903d;
		}

		.btn-primary a {
			background-color: #f2903d;
			border-color: #f2903d;
			color: #ffffff;
		}

		/* -------------------------------------
				OTHER STYLES THAT MIGHT BE USEFUL
		------------------------------------- */
		.last {
			margin-bottom: 0;
		}

		.first {
			margin-top: 0;
		}

		.align-center {
			text-align: center;
		}

		.align-right {
			text-align: right;
		}

		.align-left {
			text-align: left;
		}

		.clear {
			clear: both;
		}

		.mt0 {
			margin-top: 0;
		}

		.mb0 {
			margin-bottom: 0;
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

		.powered-by a {
			text-decoration: none;
		}

		hr {
			border: 0;
			border-bottom: 1px solid #f6f6f6;
			margin: 20px 0;
		}
		.social-icon {
			width: 50px;
		}

		/* -------------------------------------
				RESPONSIVE AND MOBILE FRIENDLY STYLES
		------------------------------------- */
		@media only screen and (max-width: 620px) {
			table[class=body] h1 {
				font-size: 28px !important;
				margin-bottom: 10px !important;
			}
			table[class=body] p,
			table[class=body] ul,
			table[class=body] ol,
			table[class=body] td,
			table[class=body] span,
			table[class=body] a {
				font-size: 16px !important;
			}
			table[class=body] .wrapper,
			table[class=body] .article {
				padding: 10px !important;
			}
			table[class=body] .content {
				padding: 0 !important;
			}
			table[class=body] .container {
				padding: 0 !important;
				width: 100% !important;
			}
			table[class=body] .main {
				border-left-width: 0 !important;
				border-radius: 0 !important;
				border-right-width: 0 !important;
			}
			table[class=body] .btn table {
				width: 100% !important;
			}
			table[class=body] .btn a {
				width: 100% !important;
			}
			table[class=body] .img-responsive {
				height: auto !important;
				max-width: 100% !important;
				width: auto !important;
			}
		}

		/* -------------------------------------
				PRESERVE THESE STYLES IN THE HEAD
		------------------------------------- */
		@media all {
			.ExternalClass {
				width: 100%;
			}
			.ExternalClass,
			.ExternalClass p,
			.ExternalClass span,
			.ExternalClass font,
			.ExternalClass td,
			.ExternalClass div {
				line-height: 100%;
			}
			.apple-link a {
				color: inherit !important;
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				text-decoration: none !important;
			}
			#MessageViewBody a {
				color: inherit;
				text-decoration: none;
				font-size: inherit;
				font-family: inherit;
				font-weight: inherit;
				line-height: inherit;
			}
			.btn-primary table td:hover {
				background-color: #34495e !important;
			}
			.btn-primary a:hover {
				background-color: #34495e !important;
				border-color: #34495e !important;
			}
		}

	</style>
</head>

	<body class="">
		<span class="preheader">Demo Requested for <?php echo $course; ?> - <?php echo $user['name']; ?></span>
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
			<tr>
				<td>&nbsp;</td>
				<td class="container">
					<div class="content">

						<!-- START CENTERED WHITE CONTAINER -->
						<table role="presentation" class="main">

							<!-- START MAIN CONTENT AREA -->
							<tr>
								<td align="center" background="https://leaplearner.in/wp-content/uploads/2019/12/05-768x507.jpg" bgcolor="#414a51" style="background-position:center; background-size:cover;border-top-left-radius:6px;border-top-right-radius:6px;">
									<table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
										<tbody><tr>
											<td height="60"></td>
										</tr>
										<!--Logo-->
										<tr>
											<td align="center" style="line-height: 0px;">
												<img mc:edit="noti-7-2" editable="" label="logo" src="<?php echo site_url('uploads/system/logo-dark.png'); ?>" width="150" alt="img" style="display:block; line-height:0px; font-size:0px; border:0px;">
											</td>
										</tr>
										<!--end Logo-->
										<tr>
											<td height="10"></td>
										</tr>
										<tr>
											<td height="40"></td>
										</tr>
									</tbody></table>
								</td>
							</tr>

							<tr>
								<td height="10"></td>
							</tr>

							<tr>
								<td data-color="Title" data-size="Title" mc:edit="noti-7-4" align="center" style="font-family: 'Open sans', Arial, sans-serif; color:#3b3b3b; font-size:22px;font-weight: bold;">
									<singleline label="title">Demo Requested for <?php echo $course; ?></singleline>
								</td>
							</tr>

							<tr>
								<td align="center" class="wrapper" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; line-height: 28px;">
									<multiline label="content">Hi, <br />New User has requested demo for <?php echo $course; ?></multiline>
								</td>
							</tr>

							<tr>
								<td align="center">
									<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
										<tbody>
											<tr>
												<td align="right" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; font-weight: bold; line-height: 28px;">
													<multiline label="content">Name:</multiline>
												</td>
												<td align="left" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; line-height: 28px;">
													<multiline label="content"><?php echo $user['name']; ?></multiline>
												</td>
											</tr>

											<tr>
												<td align="right" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; font-weight: bold; line-height: 28px;">
													<multiline label="content">Age:</multiline>
												</td>
												<td align="left" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; line-height: 28px;">
													<multiline label="content"><?php echo $user['age']; ?></multiline>
												</td>
											</tr>

											<tr>
												<td align="right" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; font-weight: bold; line-height: 28px;">
													<multiline label="content">Parent Name:</multiline>
												</td>
												<td align="left" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; line-height: 28px;">
													<multiline label="content"><?php echo $user['parent_name']; ?></multiline>
												</td>
											</tr>

											<tr>
												<td align="right" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; font-weight: bold; line-height: 28px;">
													<multiline label="content">Mobile:</multiline>
												</td>
												<td align="left" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; line-height: 28px;">
													<multiline label="content"><?php echo $user['mobile']; ?></multiline>
												</td>
											</tr>

											<tr>
												<td align="right" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; font-weight: bold; line-height: 28px;">
													<multiline label="content">Course:</multiline>
												</td>
												<td align="left" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; line-height: 28px;">
													<multiline label="content"><?php echo $course; ?></multiline>
												</td>
											</tr>

											<tr>
												<td align="right" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; font-weight: bold; line-height: 28px;">
													<multiline label="content">Schedule:</multiline>
												</td>
												<td align="left" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; line-height: 28px;">
													<multiline label="content"><?php echo $schedule; ?></multiline>
												</td>
											</tr>

											<tr>
												<td align="right" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; font-weight: bold; line-height: 28px;">
													<multiline label="content">Mode:</multiline>
												</td>
												<td align="left" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; line-height: 28px;">
													<multiline label="content"><?php echo $mode; ?></multiline>
												</td>
											</tr>

											<tr>
												<td align="right" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; font-weight: bold; line-height: 28px;">
													<multiline label="content">Center:</multiline>
												</td>
												<td align="left" class="wrapper-text" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:14px; line-height: 28px;">
													<multiline label="content"><?php echo $center; ?></multiline>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>

							<tr>
								<td class="wrapper">
									<table role="presentation" border="0" cellpadding="0" cellspacing="0">
										<tr>
											<td align="center">
												<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
													<tbody>
														<tr>
															<td align="center">
																<table role="presentation" border="0" cellpadding="0" cellspacing="0">
																	<tbody>
																		<tr>
																			<td> <a href="<?php echo $link; ?>" target="_blank">Login</a> </td>
																		</tr>
																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>

												<p style="color:#3b3b3b;margin-top: 20px;">Good luck! Happy Coding.</p>
											</td>
										</tr>
									</table>
								</td>
							</tr>

						<!-- END MAIN CONTENT AREA -->
						</table>
						<!-- END CENTERED WHITE CONTAINER -->

						<!-- START FOOTER -->
						<div class="footer">
							<table role="presentation" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td class="align-center">
										<a href="#"><img src="<?php echo site_url('assets/icons/facebook.png'); ?>" class="social-icon" /></a>
										<a href="#"><img src="<?php echo site_url('assets/icons/instagram.png'); ?>" class="social-icon" /></a>
										<a href="#"><img src="<?php echo site_url('assets/icons/twitter.png'); ?>" class="social-icon" /></a>
										<a href="#"><img src="<?php echo site_url('assets/icons/snapchat.png'); ?>" class="social-icon" /></a>
									</td>
								</tr>
								<tr>
									<td class="content-block">
										<span class="apple-link">&copy; <?php echo date('Y'); ?> LeapLearner Inc</span>
										<br><a href="#">Unsubscribe</a>.
									</td>
								</tr>
							</table>
						</div>
						<!-- END FOOTER -->

					</div>
				</td>
				<td>&nbsp;</td>
			</tr>
		</table>
	</body>
</html>
