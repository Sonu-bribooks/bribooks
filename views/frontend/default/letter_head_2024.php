<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div style="display: flex; justify-content: space-between; flex-direction: column; height: 100%;">
        <div style="padding: 0.5rem; display: flex; justify-content: space-between; border-bottom: 1px solid #0000004D">
            <div>
                <img src="<?= base_url('assets/images/svg/NYAF_letter_head_logo.svg')?>" />
                <div>India, 2024/25</div>
            </div>
            <div>
                <img src="<?= base_url('assets/images/svg/letter_head_globe_logo.svg')?>" />
            </div>
        </div>
        <div style="margin: 2rem 0; ">
            <div>Dear User</div><br/><?= base_url('assets/images/svg/NYAF_letter_head_logo.svg')?>
            <div>Hope this letter finds you well!</div>
            <div>I am writing to you to share that the 2024-25 edition of the National Young Authors’ Fair , India is back with an exciting national goal of 1 million Indian students transforming into young published authors.</div>
            <div>_School_Name_ , under your leadership has been an amazing partner, contributing to the event of national importance and ranking as India’s Top 10 school.</div>
            <div>Your enthusiastic participation will be critical for India to achieve it’s goal of 1 million published authors and cement its place globally as the powerhouse of creative excellence. </div><br/>
            
            <div>May I please request you to delegate someone responsible in your team to scan the QR code below and register _School_Name_, for this year’s event.</div><br/>
            
            <div>
                <img src="<?= $qrcode_url ?>" />
            </div>
            
            <div>Once registered, our team will provide you with a personalised communication kit for teachers, students, and parents. Globally, especially in the US, UK, and Singapore, participating schools often incorporate NYAF into their English class assignments, ensuring that every student writes and publishes a book.</div><br/>
            
            <div>This year, along with participation certificates for all students, we’re excited to introduce city, state, and national awards for teachers, as well as the annual Literary Leadership Awards for schools.</div><br/>
            
            <div>You will receive a follow up email from me as well as Bhavin Shah, the CEO of Education World, with additional details and updates.</div><br/>
            
            <div>If you have any questions, please don’t hesitate to contact my team in India at schools@bribooks.com or call 1800 309 9917.</div><br/>
            
            <div>Warm regards,</div>
            <div>Ami Dror<br />
                Founder</div>
            <div>National Young Authors’ Fair <br/>
                & BriBooks</div>
                
        </div>
        <div>
            <img src="<?= base_url('assets/images/svg/letter_head_footer.svg')?>" style="width: 100%;" />
        </div>
    </div>
</body>
</html>