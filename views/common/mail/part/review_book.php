<div class="text-center">
	<img
		src="<?=site_url('uploads/system/logo-dark.png')?>"
		class="logo"
		alt="BriBooks"
		style="height: 30px"
	/>
</div>
<br>
Hi <?=$book['author_name']?>,
<br><br>Great news! A new review has been posted on your book <b><?=$book['name'] ?></b> by <b><?=$review['author_name'] ?></b> on <?=$review['date'] ?>. Take a moment to check the ratings here.
<br><br><a href="<?=$book['url']?>?utm_source=bookreview">View Review</a>
<br><br>Best regards,
<br>Team BriBooks
