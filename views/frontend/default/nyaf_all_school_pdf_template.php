<?php
if ($user_details = $this->db->get('school_details_nyaf_guest')->result_array()) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Helvetica:wght@400&display=swap" rel="stylesheet">
    <title>Entry Pass</title>
    <style>
        html {
            margin: 0;
            padding: 0;
        }
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body style="font-family: helvetica;">
<?php
    $s3_dirname = $this->config->item('s3_base_url') . $this->config->item('s3_users_img_nyaf');

    $letterhead_head = base_url('assets/images/letterhead_head.png');
    $location = base_url('assets/images/location.svg');

    $i = 1;

    foreach ($user_details as $school_details_guest_info) {
        $school_info = $this->db->get_where('site', ['id' => $school_details_guest_info['site_id']])->row_array();
        $state_info = $this->db->get_where('state', ['id' => $school_info['state_id']])->row_array();
        $city_info = $this->db->get_where('city', ['id' => $school_info['city_id']])->row_array();

        $qr_code = base_url('uploads/eventpass/qrcode_' . $school_details_guest_info['code'] . '.png');

        $school = $school_info['name'];
        $state = $state_info['name'];
        $city = $city_info['name'];

        $guest_1_name = $school_details_guest_info['guest_name_1'];

        $guest_1_image = base_url('assets/images/man.svg');
        if($school_details_guest_info['relation_1'] === 'female') {
            $guest_1_image = base_url('assets/images/woman.svg');
        }
?>

    <div <?= getStyle($i); ?>>
        <div style="max-width: 480px; min-height: 640px; text-align: center; background-color: #E3E8FE; border: 24px solid #E3E8FE;">
            <img src="<?= $letterhead_head; ?>" style="height: 100%; width: 100%; max-height: 115px; max-width: 768px; padding-bottom: 40px;" />
            <table style="margin: auto; padding: auto; text-align: center; padding-bottom: 30px;" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr>
                        <td style="border-radius: 20px;">
                            <div style="height: 280px; width: 280px; overflow: hidden;">
                                <img src="<?= $qr_code; ?>" style="height: 100%; width: 100%; max-width: 768px; object-fit: cover;" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table style="margin: auto; padding: auto; padding-bottom: 40px; text-align: center;" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr style="height: 8rem; padding-bottom: 20px;">
                        <td width="40">
                            <img src="<?= $location; ?>" style="height: 1.8rem; margin-left: 15px;">
                        </td>
                        <td width="480">
                            <div style="font-weight: bold; font-size: 16px; text-align: left; margin-left:10px">APPAREL HOUSE, SECTOR - 44, GURUGRAM</div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table style="padding-bottom: 30px;" cellspacing="0" cellpadding="0">
                <tbody>
                    <tr style="height: 10rem;">
                        <td width="10"></td>
                        <td width="260">
                            <div style="text-align: left;">
                                <span style="text-transform: uppercase; font-size: 16px; padding-bottom: 20px;"><?= $school; ?></span><br />
                                <span style="text-transform: uppercase; font-size: 14px;"><?= $city . ', ' . $state; ?></span>
                            </div>
                        </td>
                        <td width="120">
                            <img src="<?= $guest_1_image; ?>" style="height: 90px; width: 90px; max-width: 90px;" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php $i++; } ?>
</body>
</html>
<?php } ?>

<?php
function getStyle($row_num = 1) {
    $entry_pass_1 = 'style="position: absolute; top: 20px; left: 20px;"';
    $entry_pass_2 = 'style="position: absolute; top: 20px; left: 570px;"';
    $entry_pass_3 = 'style="position: absolute; top: 20px; left: 1120px;"';
    $entry_pass_4 = 'style="position: absolute; top: 760px; left: 20px;"';
    $entry_pass_5 = 'style="position: absolute; top: 760px; left: 570px;"';
    $entry_pass_6 = 'style="position: absolute; top: 760px; left: 1120px;"';
    $entry_pass_7 = 'style="position: absolute; top: 1520px; left: 20px;"';
    $entry_pass_8 = 'style="position: absolute; top: 1520px; left: 570px;"';
    $entry_pass_9 = 'style="position: absolute; top: 1520px; left: 1120px; page-break-after:always;"';

    $row_num = $row_num%9;

    $return_style = '';
    switch ($row_num) {
        case '1':
            $return_style = $entry_pass_1;
            break;

        case '2':
            $return_style = $entry_pass_2;
            break;

        case '3':
            $return_style = $entry_pass_3;
            break;

        case '4':
            $return_style = $entry_pass_4;
            break;

        case '5':
            $return_style = $entry_pass_5;
            break;

        case '6':
            $return_style = $entry_pass_6;
            break;

        case '7':
            $return_style = $entry_pass_7;
            break;

        case '8':
            $return_style = $entry_pass_8;
            break;

        case '0':
        case '9':
            $return_style = $entry_pass_9;
            break;
        
        default:
            break;
    }

    return $return_style;
}
?>