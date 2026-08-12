<h2>Dear <?=$user['name']?>,</h2>
<p>We are delighted to have you as part of BriBooks’ young writers’ community 🙂</p>
<p>Thank you for choosing BriBooks as your book writing and publishing partner.</p>
<hr style="width: 80%;">
<p>Below are the details of your order:</p>
<div class="inner-box">
	<p><b>Date of Purchase:</b> <?=$info['start_date']?></p>
	<p><b>Amount paid:</b> <?=$plan['currency']?><?=$plan['price']?></p>
</div>
<p>This is what you get with this plan:</p>
<ul class="inner-box">
	<?=$plan['description']?>
</ul>
<p>You will find additional details is available in your <b>account section</b> on BriBooks platform.</p>
<p>We hope you enjoy the BriBooks writing and publishing experience.</p>
<br>
