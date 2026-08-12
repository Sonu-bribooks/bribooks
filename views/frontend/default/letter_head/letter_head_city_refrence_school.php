<?php include_once __DIR__ . '/header.php' ?>
<?php $color = '#0077FF'; ?>

<div style="padding: 0; ">
	<div style="position: relative; font-weight: bold;">Dear <?= $authorized_person ?>, <span style="position: absolute; top:0px; right: 24px;">NYAF-<?=$school_id?></span></div><br>

	<div>I trust you are doing well.</div><br>

	<div>Last month, during a discussion with Bhavin Shah, CEO of Education World, we were thrilled to discover that <b><?= $reference_school ?></b> from <b><?= $city ?></b> consistently ranks among the top 100 schools in the Literary Leadership Awards, part of the National Young Authors’ Fair (NYAF). My team believes that <b><?= $target_school ?></b>, with its commitment to academic excellence, has the potential to join this prestigious list of India’s top literary leader schools.</div><br>

	<div>NYAF is the world’s largest book-writing event for school students, and it offers an unparalleled opportunity for your students to write, publish, and showcase their creativity. We encourage you to seize this moment and ensure that <b><?= $target_school ?></b> participates in this transformative event.</div><br>

	<div>To register, simply delegate this task to a responsible team member who can quickly scan the QR code below.</div><br>

	<div style="width: 100%; text-align: center;">
		<img src="<?= $qrcode_url; ?>" alt="QR Code" style="height: 130px;">
	</div><br>

	<div>Upon registration, we will provide you with a personalized communication kit to engage teachers, students, and parents. Schools in the US, UK, Singapore, and beyond have integrated NYAF into their English curriculum, leading to remarkable outcomes where every student becomes a published author.</div><br>

	<div>This year, every participant will receive a certificate, and we are excited to introduce city, state, and national awards for teachers, along with the esteemed Literary Leadership Awards for schools.</div><br>

	<div>Expect a follow-up email from me and Bhavin Shah with further details. Should you have any questions, please feel free to reach out to our team in India at <span style="color:blue">schools@bribooks.com</span> or call <span style="color:blue">1800 309 9917</span>.</div><br>

	<div>Let’s make your school a beacon of literary excellence.</div><br>

	<div>Warm regards,<br>
	Ami Dror, Founder<br>
	National Young Authors’ Fair & BriBooks</div>
</div>

<?php include_once __DIR__ . '/footer.php' ?>
