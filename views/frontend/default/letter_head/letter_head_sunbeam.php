<?php include __DIR__ . '/header.php' ?>
<?php $color = '#AC1EFF'; ?>

<div>
	<div style="position: relative; font-weight: bold;">Dear <?= $authorized_person ?>, <span style="position: absolute; top:0px; right: 24px;">NYAF-<?=$school_id?></span></div><br>

	<div>Hope this letter finds you well!</div><br>

    <div>Earlier this year, I had the privilege of shooting an episode with <b>Dr. Anupama Mishra</b>, Principal of <b>Sunbeam School, Varuna Branch</b> for my podcast series, Educators of the World. During our conversation, we discussed how <b>Sunbeam School, Varuna Branch and Ballia Branch</b> have consistently been top-performing schools in NYAF over the years.<br>
    <b>Dr. Anupama</b> then suggested that we extend an invitation to all schools in the <b><?= $network_name ?></b> to participate in the 2024-25 edition of the National Young Authors’ Fair (NYAF) in India, including <b><?= $target_school ?></b>.</div><br>

    <div>As the world’s largest book-writing event for school students, NYAF is an invaluable opportunity for the students of <b><?= $target_school ?></b>. </div><br>

    <div>I encourage you to delegate this to a responsible team member who can register your school by simply scanning the QR code below.</div><br>

    <div style="width: 100%; text-align: center;">
        <img src="<?= $qrcode_url; ?>" alt="QR Code" style="height: 130px;">
    </div><br>

    <div> registered, our team will provide you with a personalised communication kit for teachers, students, and parents. 
    Globally, especially in the US, UK, and Singapore, participating schools often incorporate NYAF into their English class assignments, ensuring that every student writes and publishes a book.</div><br>

    <div>OnceThis year, along with participation certificates for all students, we’re excited to introduce city, state, and national awards for teachers, as well as the annual Literary Leadership Awards for schools.</div><br>

    <div>OnceYou will receive a follow up email from me as well as Bhavin Shah, the CEO of Education World , with additional details and updates.</div><br>

    <div>Warm regards,<br>

    Ami Dror<br>
    Founder<br>
    National Young Authors’ Fair<br>
    & BriBooks</div>
</div>

<?php include __DIR__ . '/footer.php' ?>




