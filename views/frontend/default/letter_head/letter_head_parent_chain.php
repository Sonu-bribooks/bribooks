<?php include_once __DIR__ . '/header.php' ?>
<?php $color = '#800000'; ?>

<div>
	<div style="position: relative; font-weight: bold;">Dear School Leader, <span style="position: absolute; top:0px; right: 24px;">NYAF-<?=$school_id?></span></div><br>

	<div>I hope this message finds you in good health and high spirits!.</div><br>

	<div>I am excited to extend an exclusive invitation to all schools under <b><?= strtoupper($school_name) ?> GROUP</b> 
        to participate in the <b>National Young Authors' Fair (NYAF) 2024-25</b>, the world’s largest book-writing competition for students in grades 3-12.</div><br>

	<div>In India, NYAF is organized by <b>Education World</b> in partnership with <b>BriBooks</b> and supported by leading names like <b>NDTV</b>, 
    <b>Times of India</b> (NIE), <b>Disney</b>, <b>Amazon</b> AWS, BW <b>Business World</b>, and <b>Crossword</b>.</div><br>

    <div>To streamline your school’s participation, we’ve created a custom registration page just for your group. 
        Please take a moment to register by scanning the QR code below or clicking the link provided:</div><br>

	<div style="width: 100%; text-align: center;">
		<img src="<?= $qrcode_url; ?>" alt="QR Code" style="height: 130px;">
	</div><br>

    <div>Click to Register: <a href="<?= $student_url ?>" target="_blank" style="text-decoration: none;"><?= $student_url ?></a></div><br>

	<div>Once you register, our organising team will send a personalised communication kit for each of your schools, which you can easily share with teachers, students, and parents. This will ensure a smooth process to register all your students in this prestigious event of both national and global significance.</div><br>

	<div><b>Exciting Opportunities for Schools:</b><br>
    - Students can win Jury and Best-Seller Awards.<br>
    - School leaders and teachers will be recognized with Literary Leadership Awards at City, State, and National levels.</div><br>

    <div>I look forward to seeing the schools of <b><?= strtoupper($school_name) ?> GROUP</b> shine this year in the NYAF 2024-25 India Edition. Let’s inspire the next generation of young authors!</div><br>

	<div>Should you have any questions, feel free to reach out to us at <span style="color:blue">schools@bribooks.com</span>.</div><br>

	<div>Warm regards,<br>
	Bhavin Shah <br>
	CEO, Education World</div>

</div>

<?php include_once __DIR__ . '/footer.php' ?>
