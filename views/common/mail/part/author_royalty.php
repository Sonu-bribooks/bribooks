<style type="text/css">
.royaltyInfoWrapper {
	display:flex;
	max-width:80%;
	margin:auto;
}
.royaltyInfoButton {
	width:50%;
	justify-content: space-between;
	color:white;
	background-color:green;
	border-color:green;
	box-shadow:none;
	border-style:none;
	padding:10px 5px;
}
@media screen and (max-width: 680px) {
	.royaltyInfoWrapper {
		max-width: 100%;
		display: block;
	}
	.royaltyInfoButton {
		width: 100%;
		min-width: 100%;
	}
}
</style>
<div style="padding: 20px; text-align:center;">
	<div style="text-align: center;">
		<img src="<?=base_url('assets/icons/BriBoo.gif')?>" width="80" height="85"/>
	</div>

	<p>Dear <b><?=$book['author_name']?></b>,<br></p>
	<p>Congratulations!</p>
	<p>
		<b><?=$product['quantity']?></b> <?= _getCopyTextLabel($product['quantity'])?> of your book,
		<b><?=$book['name']?></b>, has been bought at <b>
			<?=format_date($order['date_added'], $author['timezone'], 'h:i A')?>
		</b> on <b><?=format_date($order['date_added'], $author['timezone'], 'M j, Y')?></b>.
		<br>
		by <?=$buyer?>.
		<br>
	</p>

	<div class="royaltyInfoWrapper">
		<button class="royaltyInfoButton">
			<div style="display:inline-block">
				<p style="font-weight:bold;font-size:22px;margin:0;margin-bottom:10px;"><?=$author_royalty?></p>
				Author Stipend Earned
			</div>
			<span style="float:right;">
				<img src="<?=base_url('assets/icons/earning.png')?>" width="40px" height="40px"/>
			</span>
		</button>
		<button class="royaltyInfoButton" style="color:green;background-color:white;">
			<div style="display:inline-block">
				<p style="font-weight:bold;font-size:22px;margin:0;margin-bottom:10px;"><?=$no_sold?></p>
				    Number of copies sold
			</div>
			<span style="float:right;">
				<img src="<?=base_url('assets/icons/books.png')?>" width="40px" height="40px"/>
			</span>
		</button>
	</div>

	<p style="margin-top:20px;">You can check your total stipend earnings here: <a href="https://www.bribooks.com/account/myearnings">My Earnings</a></p>

	<p style="margin-top:20px;">Regards<br>Team BriBooks</p>
</div>
