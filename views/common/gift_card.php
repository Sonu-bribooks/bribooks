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
            background-color: #fff;
        }
        img {
            display: block; 
            width: 432pt; 
            height: 648pt;
            border: 0;
            margin: 0;
            padding: 0;
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
            margin: 0;
            padding: 0;
            page-break-after: always; 
        }
        .page:last-child {
            page-break-after: avoid;
        }
    </style>
</head>
<body>
    <?php
        $author_font_size = '22px';

        if (strlen($author_name) > 15) {
            $author_font_size = '20px';
        }
        if (strlen($author_name) > 25) {
            $author_font_size = '16px';
        }
    ?>
    <!-- Front Page -->
    <div class='page'>
        <img src='<?= $front_image ?>'/>

        <!-- Author Name -->
        <div class='overlay-text' style='
            top: 38%;
            left: 50%;
            transform: translateX(-50%);
            font-size: <?= $author_font_size ?>;
            font-weight: 600;
            text-align: center;
            color: #fff;
            line-height: 0.85;
            margin: 0;
            padding: 0;
        '>
        <?= ucwords($author_name)?>
		
        </div>
    </div>

    <!-- Back Page -->
    <div class='page'>
        <img src='<?= $back_image ?>'/>
        <div class='overlay-text' style='
            bottom: 1%;
            right: 5%;
            font-size: 8px;
            text-align: right;
            color:#fff
        '>
            <?= $sku ?>
        </div>
    </div>
</body>
</html>
