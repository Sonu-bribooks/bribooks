<?php include_once __DIR__ . '/header.php' ?>
<?php $color = '#9CCC65'; ?>

<div>
	<div style="position: relative; font-weight: bold;">Dear <?= $authorized_person ?>, <span style="position: absolute; top:0px; right: 24px;">NYAF-<?=$school_id?></span></div><br>

	<div>Hope this letter finds you well!</div><br>

	<div>I am writing to share that the 2024-25 edition of the <b>National Young Authors’ Fair, India</b>, is back with an exciting national goal of transforming 1 million Indian students into young published authors.</div><br>

	<div><b><?= $school_name; ?></b>,  has an amazing opportunity to enable its students to become published authors and contribute to this national mission of positioning India as a creative leader on the global map.</div><br>

	<div>May I please request you to delegate someone responsible on your team to scan the QR code below and register <b><?= $school_name; ?></b> for this year’s event.</div><br>

	<div style="width: 100%; text-align: center;">
		<img src="<?= $qrcode_url; ?>" alt="QR Code" style="height: 130px;">
	</div><br>

	<div>Once registered, our team will provide you with a personalised communication kit for teachers, students, and parents. Globally, especially in the US, UK, and Singapore, participating schools often incorporate NYAF into their English class assignments, ensuring that every student writes and publishes a book.</div><br>

	<div>This year, along with participation certificates for all students, we’re excited to introduce city, state, and national awards for teachers, as well as the annual Literary Leadership Awards for schools.</div><br>

	<div>You will receive a follow-up email from me as well as Bhavin Shah, the CEO of Education World, with additional details and updates.</div><br>

	<div>If you have any questions, please don’t hesitate to contact my team in India at <span style="color:blue">schools@bribooks.com</span> or call <span style="color:blue">1800 309 9917</span>.</div><br>

	<div>Warm regards,</div>

	<div>Ami Dror<br>
	Founder<br>
	National Young Authors’ Fair & BriBooks</div>

</div>

<?php include_once __DIR__ . '/footer.php' ?>
