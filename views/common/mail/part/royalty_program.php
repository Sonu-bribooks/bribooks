<p><strong>Dear <?=$author_name?></strong></p>
<p style="font-size: 14px;">
	Congratulations! You are now eligible for the BriBooks Author Royalty Program.
	You will now earn Royalty every time your books are sold.
</p>
<div style="justify-content: center;text-align: center; display: flex;">
	<?php foreach ($books as $book) { ?>
	<div style="margin-right:10px;">
		<img
			src="<?=$this->config->item('s3_base_url') . 'public/' . $book['cover_image']?>"
			width="100px"
			height="150px"
		/><br/>
		<a href="<?=USER_URL?>bookstore/<?=$book['slug']?>">
			<?=$book['name']?>
		</a>
	</div>
	<?php } ?>
</div>
<p style="font-size: 14px;">
	The higher the number of books sold; the more the Royalty earned by you.
	Please <a href="<?=USER_URL?>pricing/newpricing" target="_blank" style="color:black;">
		Click here
	</a> for more details.
</p>
<p style="font-size: 14px;">
	Please <a
		href="<?=USER_URL?>account/bank"
		style="color:black;"
	>
		Click here
	</a> to enter your Bank Details for Remittance
</p>
<p style="font-size: 14px;">
	You can track your earnings in the <a
		href="<?=USER_URL?>account/myearnings"
		target="_blank"
		style="color:black;"
	>
		Click here
	</a> section of your account.
</p>
