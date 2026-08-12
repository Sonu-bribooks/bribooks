<?php include_once __DIR__ . '/header.php' ?>
<?php $color = '#AC1EFF'; ?>

<div>
	<div style="position: relative; font-weight: bold;">Dear <?= $authorized_person ?>, <span style="position: absolute; top:0px; right: 24px;">NYAF-<?=$school_id?></span></div><br>

    <div>Hope this letter finds you well!</div><br>

    <div>I am writing to you to share that the 2024-25 edition of the National Young Authors’ Fair, India is back with an exciting national goal of 1 million Indian students transforming into young published authors.</div><br>
    
    <div><b><?= $school_name ?></b>, under your leadership, has been an amazing partner, contributing to this event of national importance every year.</div><br>

    <div>Your enthusiastic participation will be critical for India to achieve its goal of 1 million published authors and cement its place globally as the powerhouse of creative excellence. It is also an amazing opportunity for your school to break into the National Top 100 this year.</div><br>

    <div>May I please request you to delegate someone responsible in your team to scan the QR code below and register <b><?= $school_name ?></b> for this year’s event.</div><br>

    <div style="width: 100%; text-align: center;">
        <img src="<?= $qrcode_url; ?>" alt="QR Code" style="height: 130px;">
    </div><br>

    <div>Once registered, our team will provide you with a personalised communication kit for teachers, students, and parents. Globally, especially in the US, UK, and Singapore, participating schools often incorporate NYAF into their English class assignments, ensuring that every student writes and publishes a book.</div><br>

    <div>This year, along with participation certificates for all students, we’re excited to introduce city, state, and national awards for teachers, as well as the annual Literary Leadership Awards for schools.</div><br>

    <div>You will receive a follow-up email from me as well as Bhavin Shah, the CEO of Education World, with additional details and updates.</div><br>

    <div>If you have any questions, please don’t hesitate to contact my team in India at <span style="color:blue">schools@bribooks.com</span> or call <span style="color:blue">1800 309 9917</span>.</div><br>

    <div>Warm regards,<br>

    Ami Dror<br>
    Founder<br>
    National Young Authors’ Fair<br>
    & BriBooks</div>
</div>

<?php include_once __DIR__ . '/footer.php' ?>