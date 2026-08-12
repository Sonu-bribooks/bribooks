<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php foreach($brochures as $index => $brochure) { ?>
        <?php if ($index == ($dynamic - 1)) { ?>
            <div style="overflow: hidden; position: relative">
                <img src="<?= $base_url . $brochure ?>" style="width: 100%;"/>
                <div style="
                    position: absolute; 
                    left:50%; 
                    z-index: 1; 
                    color: #5351EC; 
                    font-weight: bold; 
                    font-size: 45px;
                    top: 23%;
                    transform: translate(-50%, 0%);">
                    <a href="<?= $student_url ?>" target="_blank">
                    <?= $student_url ?>
                    </a>
                </div>
                <div style="
                    position: absolute; 
                    left:50%; 
                    z-index: 1; 
                    color: #000; 
                    font-weight: bold; 
                    font-size: 30px;
                    top: 45%;
                    transform: translate(-50%, 0%);">
                    <img src="<?= $qrcode_url ?>" style="width: 100%;" alt="<?= $qrcode_url ?>"/>
                </div>
            </div>
        <?php } else { ?> 
            <div style="overflow: hidden;">
                <img src="<?= $base_url . $brochure ?>" style="width: 100%;"/>
            </div>
        <?php } ?>
    <?php } ?>
</body>
</html>