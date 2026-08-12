<div style="padding: 20px; text-align:center;">
	<!-- <div style="text-align: center;">
		<img src="<?=base_url('assets/icons/BriBoo.gif')?>" width="80" height="85"/>
	</div> -->

	<p>Hey <b><?=$book['author_name']?></b>,<br></p>

	<?php if(!empty($location) && strtolower($location) == 'india') { ?>
		<p>Your book, <b><?=$book['name']?></b>, has been allotted the ISBN Number <?=$book['isbn']?> by the <b>Ministry of Education, Government of India</b>.</p>

		<p>You can verify the same at https://isbn.gov.in/Home</p>
	<?php } else { ?>
		<p>Your book, <b><?=$book['name']?></b>, has been allotted the ISBN Number <?=$book['isbn']?>.</p>
	<?php } ?>

	<p style="margin-top:20px;">Regards<br>Team BriBooks</p>
</div>
