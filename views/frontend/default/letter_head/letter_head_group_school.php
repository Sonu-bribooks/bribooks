<?php include_once __DIR__ . '/header.php' ?>
<?php $color = '#800000'; ?>

<div>
	<div style="position: relative; font-weight: bold;">Dear <?= $authorized_person ?>, <span style="position: absolute; top:0px; right: 24px;">NYAF-<?=$school_id?></span></div><br>

	<div>I hope this letter finds you well.</div><br>

	<div>Earlier this year, during my conversations with Bhavin, the CEO of Education World, I was informed that <b><?= $parent_school_name ?></b> has had an amazing participation in the previous editions of National Young Authors’ Fair. This includes <b><?= $top_school_1 ?></b><b><?= $top_school_2 ?></b>. <br>
	Bhavin suggested that we extend an invitation to all schools in the <b><?= $network_name ?></b> to participate in the 2024-25 edition of the <b>National Young Authors’ Fair (NYAF)</b> in India.</div><br>

	<div>As the world’s largest book-writing event for school students, NYAF is an invaluable opportunity for the students of <b><?= $school_name ?></b>. <br>
	I encourage you to delegate this to a responsible team member who can register your school by simply scanning the QR code below.</div><br>

	<div style="width: 100%; text-align: center;">
		<img src="<?= $qrcode_url; ?>" alt="QR Code" style="height: 130px;">
	</div><br>

	<div>Once registered, our team will provide you with a personalised communication kit for teachers, students, and parents. Globally, especially in the US, UK, and Singapore, participating schools often incorporate NYAF into their English class assignments, ensuring that every student writes and publishes a book.</div><br>

	<div>This year, along with participation certificates for all students, we’re excited to introduce city, state, and national awards for teachers, as well as the annual Literary Leadership Awards for schools.</div><br>

	<div>You will receive a follow up email from me as well as Bhavin Shah, the CEO of Education World , with additional details and updates.</div><br>

	<div>If you have any questions, please don’t hesitate to contact my team in India at <span style="color:blue">schools@bribooks.com</span> or call <span style="color:blue">1800 309 9917</span>.</div><br>

	<div>Warm regards,<br>
	Ami Dror<br>
	Founder<br>
	National Young Authors’ Fair<br>
	& BriBooks</div>

</div>

<?php include_once __DIR__ . '/footer.php' ?>
