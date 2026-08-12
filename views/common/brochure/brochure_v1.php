<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php foreach($brochures as $brochure) { ?>
        <?php if ($brochure['has_url']) { ?>
            <div style="overflow: hidden; position: relative">
                <img src="<?= $base_url . $brochure['image'] ?>" style="width: 100%;"/>
                <div style="
                    position: absolute; 
                    left:50%; 
                    z-index: 1; 
                    color: #5351EC; 
                    font-weight: bold; 
                    font-size: 45px;
                    top: 87%;
                    transform: translate(-50%, 0%);">
                    <a href="<?= $url ?>" target="_blank">
                    <?= $url ?>
                    </a>
                </div>
                <div style="
                    position: absolute; 
                    left:42%; 
                    z-index: 1; 
                    color: #000; 
                    font-weight: bold; 
                    font-size: 30px;
                    top: 60%;
                    transform: translate(-50%, 0%);">
                    <img src="<?= $qr_file ?>" style="width: 75%;"/>
                </div>
            </div>
        <?php } else { ?> 
            <div style="overflow: hidden;">
                <img src="<?= $base_url . $brochure['image'] ?>" style="width: 100%;"/>
            </div>
        <?php } ?>
    <?php } ?>
</body>
</html>