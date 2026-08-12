<?php

$width = 630;
$height = 900;

$text = '<p> Sahilk </p>';
?>
<!-- <html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Document</title>
    <style>
       @page {size: 630px 900px; margin:0!important; padding:0!important}
    </style>
</head>

<body>
    <table>
        <tr>
            <td>
                <img width="100%" height="100%" src="<?php echo  FCPATH . 'assets/images/NYAF_LetterHead.png'; ?>" />
                <p>Sahil</p>
            </td>
        </tr>

    </table>
</body>

</html> -->


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        @page {
            size: 635px 900px;
            margin: 0 !important;
            padding: 0 !important
        }
    </style>
</head>

<body>
    <div class="book-page">
        <div class="page">
            <div class="background-cover">
                <img style="width: 100%; height:100%;" src="<?= FCPATH . 'assets/images/NYAF_LetterHead.png' ?>" />
            </div>
            <div style="position: absolute; top: 17%; left: 10%; right:10%; font-size:12px; font-family: Arial, Helvetica, sans-serif;
">
                <p>Dear <?= $data['Proper Name'] ?>, </p>
                <p>Congratulations for being nominated by <b>Education World</b> to the National Young Authors Fair - World’s Largest Book Writing Competition.</p>
                <p>Not every day do we get to make history, yet together we will break a world record and <b><?= $data['School Names'] ?></b> will become part of the world’s biggest literary event in the history of our planet. </p>
                <p>You will be a part of over 3,000 Schools that were carefully selected by Education World, and our collective goal is simple - we will publish <b>ONE MILLION BOOKS</b> by children for children.
                </p>
                <p>Once the school will be officially accepted, your students will be invited to write and publish a book on BriBooks.com. The students will become published authors, and their books will have an ISBN, issued by the Ministry of Education.</p>
                <p>Both the school and the individual authors will get to compete with other schools, with the opportunity to win multiple awards and to be showcased at the New York Public Library in Manhattan. Schools will also get an opportunity to win <b>Global Literary Leadership</b> awards in multiple categories.</p>
                <p>The event is <b>COMPLETELY FREE</b> for both schools and authors and the young authors will even be invited to promote their books on BriBooks and on Amazon.com and earn royalties.</p>
                <p>Next Steps:</p>
                <ul>
                    <li>Please complete your school registration at <a href="https://www.yaf.bribooks.com/india/school">https://www.yaf.bribooks.com/india/school</a> by <b>5th Dec 2022; 06:00 pm</b> </li>
                    <li>Once confirmed, we will send the Communication Kit to the school with everything one needs in order to excel at the historic national event. </li>
                    <li>If you face any issues while registering, feel free to connect with us at schools@bribooks.com or call 1800 309 9917</li>
                </ul>
                <p>It's time to make history! India has been selected to be the first country in the world to host a national Young Authors Fair - let's work together and make India the world champion in writing.
                </p>
                <!-- <div style="display: flex;margin-top: 100px;">
                    <p style="margin-right: 250px;">Bhavin Shah<br /> <span>CEO</span><br /><span>Education World</span></p>
                    <p>Ami Dror <br /<span>Founder & CEO</span><br /><span>BriBooks</span></p>
                </div> -->
            </div>
        </div>
    </div>
    </div>
    </div>
</body>

</html>