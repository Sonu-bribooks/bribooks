<?php include_once __DIR__ . '/header.php' ?>
<?php $color = '#FFB9D7'; ?>

<?php include_once __DIR__ . '/header.php' ?>
<?php $color = '#800000'; ?>

<div>

    <div style="position: relative; font-weight: bold;">Dear <?= $authorized_person ?>, <span style="position: absolute; top:0px; right: 24px;">NYAF-<?=$school_id?></span></div><br>
	
    <div>I hope this letter finds you well.</div><br>

    <div>As we gear up for the 2024 edition of the National Young Authors Fair (NYAF), I wanted to share some insights that caught my attention. <b><?= $reference_school; ?></b>, a member of your esteemed group of schools, has consistently ranked among India’s Top 100 Literary Leader Schools, with over 500 students participating annually.</div><br>

    <div>I’m confident that <b><?= $target_school; ?></b>, being part of the same prestigious network, has the potential to join this elite group of India’s Top 100 schools.</div><br>

    <div>The path to this distinction is clear: inspire more of your students to register, write, and publish their books. As the world’s largest book-writing event for school students, NYAF offers an unparalleled platform for your students to shine.</div><br>

    <div>To make this as seamless as possible, I encourage you to appoint a dedicated team member to take charge of this initiative. They can easily enroll your school by scanning the QR code below.</div><br>

    <div style="width: 100%; text-align: center;">
        <img src="<?= $qrcode_url; ?>" alt="QR Code" style="height: 130px;">
    </div><br>

    <div>Upon enrollment, our team will provide a personalized communication kit tailored for teachers, students, and parents. Schools across the globe, particularly in the US, UK, and Singapore, have successfully integrated NYAF into their English curricula, ensuring that every student has the opportunity to write and publish a book.</div><br>

    <div>This year, in addition to participation certificates for all students, we’re thrilled to introduce city, state, and national awards for teachers, alongside our prestigious annual Literary Leadership Awards for schools.</div><br>

    <div>You’ll soon receive a follow-up email from me, as well as Bhavin Shah, CEO of Education World, with more details and updates.</div><br>

    <div>If you have any questions or need assistance, please feel free to reach out to my team in India at <span style="color:blue">schools@bribooks.com</span> or call <span style="color:blue">1800 309 9917</span>.</div><br>

    <div>Warm regards,<br>
    Ami Dror<br>
    Founder<br>
    National Young Authors’ Fair & BriBooks</div>
</div>

<?php include_once __DIR__ . '/footer.php' ?>
