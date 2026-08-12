<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Helvetica:wght@400&display=swap" rel="stylesheet">
    <title>Exhibition Entry Pass</title>
</head>
<body style="font-family: helvetica;">
    <div>
        <div style="max-width: 480px; max-height: 640px; text-align: center; background-color: #cf99d630;">
            <img src="<?= $head_logo; ?>" style=" width: 100%; max-height: 115px; max-width: 400px; padding-bottom: 20px;" />
            <table style="margin: auto; padding: auto; text-align: center; padding-bottom: 10px;" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                        <td style="border-radius: 20px;">
                            <div style="height: 240px; width: 240px; overflow: hidden;">
                                <img src="<?= $qr_code; ?>" style="height: 100%; width: 100%; max-width: 768px; object-fit: cover;" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table style="margin: auto; padding: auto; padding-bottom: 30px; text-align: center;" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr style="height: 4rem; padding-bottom: 10px;">
                        <td width="40">
                            <img src="<?= $location; ?>" style="height: 1.8rem; margin-left: 15px;">
                        </td>
                        <td width="480">
                            <div style="font-weight: bold; font-size: 16px; text-align: left; margin-left:10px">APPAREL HOUSE, SECTOR - 44, GURUGRAM</div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table style="padding-bottom: 20px;" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr style="height: 8rem;">
                        <td width="20"></td>
                        <td width="260">
                            <div style="text-align: left;">
                                <h2 style="text-transform: uppercase; margin: 0; font-weight: bolder; font-size: 18px;"><?= $name; ?></h2>
                                <span style="font-size: 12px; padding-bottom: 20px;">No of Guest - <?= $guest_count?></span><br />
                                <span style="font-size: 12px; padding-bottom: 20px;">Slot - <?= $slot?></span><br />
                                <span style="font-size: 10px; padding-bottom: 20px;"><b>Access ONLY to Exhibition Area. Awards Venue Access NOT Allowed</b></span><br />
                            </div>
                        </td>
                        <td width="120">
                            <img src="<?= $author_image; ?>" style="height: 90px; width: 90px; max-width: 90px; border-radius: 100%;" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div style="position: absolute; top: 0; left: 520px;">
        <div style="max-width: 480px; max-height: 640px; text-align: center; background-color: #cf99d630;">
            <img src="<?= $head_logo; ?>" style="width: 100%; max-height: 115px; max-width: 400px; padding-bottom: 20px;" />
            <table style="margin: auto; padding: auto; text-align: center; padding-bottom: 20px;" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                        <td style="border-radius: 20px;">
                            <div style="height: 240px; width: 240px; overflow: hidden;">
                                <img src="<?= $qr_code; ?>" style="height: 100%; width: 100%; max-width: 768px; object-fit: cover;" />
                            </div>
                        </td>
                        
                    </tr>
                </tbody>
            </table>
            <table style="margin: auto; padding: auto; padding-bottom: 30px; text-align: center;" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr style="height: 4rem; padding-bottom: 10px;">
                        <td width="40">
                            <img src="<?= $location; ?>" style="height: 1.8rem; margin-left: 15px;">
                        </td>
                        <td width="480">
                            <div style="font-weight: bold; font-size: 16px; text-align: left; margin-left:10px">APPAREL HOUSE, SECTOR - 44, GURUGRAM</div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table style="padding-bottom: 20px;" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr style="height: 5rem;">
                        <td width="20"></td>
                        <td width="260">
                            <div style="text-align: left; height: 5rem;">
                                <span style="font-size: 16px;">GUEST OF</span><br />
                                <span style="text-transform: uppercase; font-size: 18px; font-weight: bold;"><?= $name; ?></span><br />
                                <span style="font-size: 10px; padding-bottom: 20px;"><b>Access ONLY to Exhibition Area. Awards Venue Access NOT Allowed</b></span><br />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($guest_count > 1) { ?>
    <div style="margin-top: 2rem;">
        <div style="max-width: 480px; max-height: 640px; text-align: center; background-color: #cf99d630;">
            <img src="<?= $head_logo; ?>" style="width: 100%; max-height: 115px; max-width: 400px; padding-bottom: 20px;" />
            <table style="margin: auto; padding: auto; text-align: center; padding-bottom: 20px;" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                        <td style="border-radius: 20px;">
                            <div style="height: 240px; width: 240px; overflow: hidden;">
                                <img src="<?= $qr_code; ?>" style="height: 100%; width: 100%; max-width: 768px; object-fit: cover;" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table style="margin: auto; padding: auto; padding-bottom: 30px; text-align: center;" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr style="height: 4rem; padding-bottom: 10px;">
                        <td width="40">
                            <img src="<?= $location; ?>" style="height: 1.8rem; margin-left: 15px;">
                        </td>
                        <td width="480">
                            <div style="font-weight: bold; font-size: 16px; text-align: left; margin-left:10px">APPAREL HOUSE, SECTOR - 44, GURUGRAM</div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table style="padding-bottom: 20px;" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr style="height: 5rem;">
                        <td width="20"></td>
                        <td width="260">
                            <div style="text-align: left; height: 5rem;">
                                <span style="font-size: 16px;">GUEST OF</span><br />
                                <span style="text-transform: uppercase; font-size: 18px; font-weight: bold;"><?= $name; ?></span><br />
                                <span style="font-size: 10px; padding-bottom: 20px;"><b>Access ONLY to Exhibition Area. Awards Venue Access NOT Allowed</b></span><br />
                            </div>
                        </td>
                        <td width="120">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php } ?>
</body>
</html>