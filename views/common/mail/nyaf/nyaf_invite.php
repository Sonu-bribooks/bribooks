
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
			/*background-color: #E3E8FE;*/
		}
		.container {
			/*background-color: #E3E8FE;*/
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
			/*background-color: #E3E8FE;*/
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
			/*background-color: #E3E8FE;*/
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
			
		</div>
		<div class="box">

        <p>Dear <?php echo $author_name ?></p>
        <p>Yay! You made it to the India’s Best-Seller Young Authors’ List with your book, <?php echo $book_name ?>.</p>
		<p>Your final rank is #<?php echo $book_rank ?></p>
        <p>Check out your fellow award winners here:<?php echo $dashboard_url ?></p>
        <p>Do share your achievement with friends and family, and don't forget to tag us on social media:</p>
        <p>Facebook -  https://www.facebook.com/bribooksglobal</p>
        <p>Instagram -  https://instagram.com/bribooks_global</p>
        <p>Linkedin -    https://www.linkedin.com/company/bribooks/</p>
        <p>You have also won the exclusive invite to the National Awards & Exhibition event scheduled from 2 pm to 6:30 pm on 31st March 2024, in Gurugram, Haryana.</p>
        <p>Since this is a high security event, only confirmed participants will be allowed in the event. 
			Please confirm your participation by completing the form below by 29th Feb 2024. 
		</p>
        <p><?php echo $url ?></p>
        <p>Best Regards,</p>
       <p> Team BriBooks<br>
        NYAF 2023 <br>India</p>   
</div>
</div>
<footer class="text-center footer">
<div class="footer-link-cover">
	
</div>


    <div class="text-center">
        <a href="https://www.facebook.com/bribooksglobal"><img src="<?=site_url('assets/icons/facebook.png')?>" class="social-icon" /></a>
        <a href="https://www.instagram.com/bribooks_global/"><img src="<?=site_url('assets/icons/instagram.png')?>" class="social-icon" /></a>
        <a href="https://twitter.com/BriBooks_global"><img src="<?=site_url('assets/icons/twitter.png')?>" class="social-icon" /></a>
    </div>

<span class="copyright">&copy; <?= date('Y'); ?> BriBooks</span>
<a href="<?= !empty($unsubscribe_url) ? $unsubscribe_url : 'mailto:support@bribooks.com' ?>" class="copyright">Unsubscribe</a>
</footer>
</body>
</html>

