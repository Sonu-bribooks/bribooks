<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Gift Card</title>

    <!-- Import Poppins SemiBold -->
    <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap' rel='stylesheet'>

    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }
        .overlay-text {
            position: absolute;
            z-index: 1;
            color: #10284B;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }
        .page {
            position: relative;
            width: 432pt;
            height: 648pt;
            overflow: hidden;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <?php
        $author_font_size = '90px';

        if (strlen($author_name) > 15) {
            $author_font_size = '60px';
        }
        if (strlen($author_name) > 25) {
            $author_font_size = '45px';
        }
    ?>
    <!-- Front Page -->
    <div class='page page-break'>
        <img src='<?= $front_image ?>' style='width: 432pt; height: 648pt;'/>

        <!-- Author Name -->
        <div class='overlay-text' style='
            top: 10%;
            left: 50%;
            transform: translateX(-50%);
            font-size: <?= $author_font_size ?>;
            text-align: center;
        '>
            <?= $author_name ?>
        </div>
    </div>

    <!-- Back Page -->
    <div class='page'>
        <img src='<?= $back_image ?>' style='width: 432pt; height: 648pt;'/>
        <div class='overlay-text' style='
            bottom: 5%;
            right: 5%;
            font-size: 28px;
            text-align: right;
        '>
            <?= $sku ?>
        </div>
    </div>
</body>
</html>
