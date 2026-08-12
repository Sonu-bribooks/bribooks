<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div style="overflow: hidden;">
        <img src="<?= $image1 ?>" style="width: 100%;"/>
    </div>
    <div style="overflow: hidden;">
        <img src="<?= $image2 ?>" style="width: 100%;"/>
    </div>
    <div style="overflow: hidden;">
        <img src="<?= $image3 ?>" style="width: 100%;"/>
    </div>
    <div style="overflow: hidden;">
        <img src="<?= $image4 ?>" style="width: 100%;"/>
    </div>
    <div style="overflow: hidden; position: relative">
        <img src="<?= $image5 ?>" style="width: 100%;"/>
        <div style="
            position: absolute; 
            left:50%; 
            z-index: 1; 
            color: #5351EC; 
            font-weight: bold; 
            font-size: 45px;
            top: 80%;
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
            top: 55%;
            transform: translate(-50%, 0%);">
            <img src="<?= $qr_file ?>" style="width: 75%;"/>
        </div>
    </div>
</body>
</html>