<?php
$enrols = $this->enrol_model->getAll([
    'user_id'	=> $this->session->userdata('user_id'),
    'site_id'	=> $this->config->item('site_id')
]);

// echo "<pre>"; print_r($enrols); die;

$python 	= false;
$blockly 	= false;

foreach ($enrols as &$enrol) {
    $center_info = $this->enrol_model->getCenter($enrol['id']);

    $enrol['center'] = $center_info['center'] ?? '-';

    if (strtotime($enrol['renewal_date'] . ' -1 week') < time()) {
        $enrol['renewal'] 		= true;
        $enrol['amount'] 		= $this->enrol_model->getRenewalAmount($enrol['id']);
    }

    if (strtotime($enrol['renewal_date']) < time()) {
        $enrol['expired'] 		= true;
    }

    if (strtolower($enrol['course']) === 'python') {
        $python = $enrol;
    }

    if (strtolower($enrol['course']) === 'blockly') {
        $blockly = $enrol;
    }
}

/*echo "<pre>"; print_r($python);
echo "<pre>"; print_r($blockly);
die;*/

// hc($enrols);
?>
<style>
    .card {
        border-radius: 0px;
        padding: 5px 5px 5px 5px;
        min-height: 84px;
    }
    .card-green, .card.active {
        border: 1px solid #4ca95a;
        background-color: #4ca95a;
        color: #ffffff;
    }

    .card-yellow {
        border: 1px solid #e7953c;
        background-color: #e7953c;
    }

    .card-light-yellow {
        border: 1px solid #c3d444;
        background-color: #c3d444;
    }
    .card-blue {
        border: 1px solid #3a99ca;
        background-color: #3a99ca;
    }
    section.my-dashboard-area {
        padding: 40px 0;
    }
    .my-dashboard-area .card p {
        color: #ffffff;
    }
    .course h5, .quick h5 {
        text-transform: uppercase;
        background-color: #e66f38;
        border: 1px solid #e66f38;
        border-radius: 20px;
        width: fit-content;
        padding: 0 10px;
        color: #ffffff;
        font-size: 120%;
        font-weight: 600;
    }
    .course h3 {
        margin-top: 50px;
        text-align: center;
        background-color: #e7953c;
        border: 1px solid #e7953c;
        border-radius: 0px;
        width: fit-content;
        padding: 0 10px;
        color: #ffffff;
        font-weight: 600;
        margin-left: auto;
        margin-right: auto;
    }

    .quick h2 {
        margin-top: 50px;
        text-align: center;
        color: #e7953c;
        width: fit-content;
        font-weight: 600;
        font-size: 50px;
        margin-left: auto;
        margin-right: auto;
    }
    .course p {
        font-weight: 600;
    }
    .stage {
        background-image: url("<?=base_url().'assets/frontend/default/img/footer-top.png'; ?>");
        background-size: cover;
        background-position: bottom;
        background-repeat: no-repeat;
        padding: 147px 0 !important;
        margin-top: -163px;
    }

    .cards {
        background-image: url("<?=base_url().'assets/frontend/default/img/card-active.png'; ?>");
        background-size: contain;
        background-repeat: no-repeat;
    }

    .zoom-video img {
        width: 100%;
    }
    .stage .card {
        min-height: 1px;
        float: left;
        margin-left: 20px;
        margin-top: 10px;
    }

    .stage .card p {
        color: #000000;
    }
    .stage .active p {
        color: #ffffff;
    }

    .countdownHolder{
        margin:0 auto;
        font: 40px/1.5 'Open Sans Condensed',sans-serif;
        text-align:center;
        letter-spacing:-3px;
    }

    .position{
        display: inline-block;
        height: 1.6em;
        overflow: hidden;
        position: relative;
        width: 1.05em;
    }

    .digit{
        position:absolute;
        display:block;
        width:1em;
        background-color:#e7953c;
        border-radius:0.2em;
        text-align:center;
        color:#fff;
        letter-spacing:-1px;
    }

    .digit.static{
        box-shadow:1px 1px 1px rgba(4, 4, 4, 0.35);

        background-image: linear-gradient(bottom, #df8423 50%, #e7953c 50%);
        background-image: -o-linear-gradient(bottom, #df8423 50%, #e7953c 50%);
        background-image: -moz-linear-gradient(bottom, #df8423 50%, #e7953c 50%);
        background-image: -webkit-linear-gradient(bottom, #df8423 50%, #e7953c 50%);
        background-image: -ms-linear-gradient(bottom, #df8423 50%, #e7953c 50%);

        background-image: -webkit-gradient(
            linear,
            left bottom,
            left top,
            color-stop(0.5, #df8423),
            color-stop(0.5, #e7953c)
        );
    }

    /**
    * You can use these classes to hide parts
    * of the countdown that you don't need.
    */

    .countDays{ /* display:none !important;*/ }
    .countDiv0{ /* display:none !important;*/ }
    .countHours{}
    .countDiv1{}
    .countMinutes{}
    .countDiv2{}
    .countSeconds{}


    .countDiv{
        display:inline-block;
        width:16px;
        height:1.6em;
        position:relative;
    }

    .countDiv:before,
    .countDiv:after{
        position:absolute;
        width:5px;
        height:5px;
        background-color:#df8423;
        border-radius:50%;
        left:50%;
        margin-left:-3px;
        top:0.5em;
        box-shadow:1px 1px 1px rgba(4, 4, 4, 0.5);
        content:'';
    }

    .countDiv:after{
        top:0.9em;
    }

    .countDays, .countDays+.countDiv {
        display: none;
    }

    .progressbar{
        counter-reset: step;
        list-style: none;
    }
    .progressbar li{
        float: left;
        width: 20%;
        position: relative;
        text-align: center;
    }
    .progressbar li:before{
        content:counter(step);
        counter-increment: step;
        width: 50px;
        height: 50px;
        border: 2px solid #bebebe;
        display: block;
        margin: 0 auto 10px auto;
        border-radius: 50%;
        line-height: 47px;
        background: white;
        color: #bebebe;
        text-align: center;
        font-weight: bold;
    }
    .progressbar li:after{
        content: '';
        position: absolute;
        width:100%;
        height: 3px;
        background: #979797;
        top: 25px;
        left: -50%;
        z-index: -1;
    }
    .progressbar li.active:after{
        background: #df8423;
    }
    .progressbar li.active:before{
        border-color: #df8423;
        background: #df8423;
        color: white
    }
    .progressbar li:first-child:after{
        content: none;
    }
    .mt-40 {
        margin-top: 40px;
    }

    section.page-header-area.my-course-area {
        background-size: cover;
        background-position: 90% 30%;
        background-image: url(https://learn.icode.org/lp/assets/images/home_bg.jpg);
    }
    section.page-header-area .page-title {
        line-height: 100px;
    }



    .course {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 10px 10px rgba(0, 0, 0, 0.2);
        display: flex;
        max-width: 100%;
        margin: 20px;
        overflow: hidden;
        /*        width: 700px;*/
    }

    .course h6 {
        opacity: 0.6;
        margin: 0;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .course h2 {
        letter-spacing: 1px;
        margin: 10px 0;
    }

    .course-preview {
        background-color: #2A265F;
        color: #fff;
        padding: 30px;
        max-width: 250px;
    }

    .course-preview a {
        color: #fff;
        display: inline-block;
        font-size: 12px !important;
        opacity: 0.6;
        margin-top: 30px;
        text-decoration: none;
        text-transform: capitalize;
    }
    .badge {
        font-size: 12px;
    }

    .course-info {
        padding: 30px;
        position: relative;
        width: 100%;
    }

    .progress-container {
        position: absolute;
        top: 30px;
        right: 50px;
        text-align: right;
        width: 150px;
    }

    .progress {
        background-color: #ddd;
        border-radius: 3px;
        height: 5px;
        width: 100%;
    }

    .progress::after {
        border-radius: 3px;
        background-color: #2A265F;
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 5px;
        width: 66%;
    }

    .progress-text {
        font-size: 10px;
        opacity: 0.6;
        letter-spacing: 1px;
    }

    .btn {
        background-color: #32744f;
    border: 0;
    border-radius: 2px;
    box-shadow: 0 10px 10px rgb(0 0 0 / 20%);
    color: #fff;
    font-size: 16px;
    padding: 2px 20px;
    position: absolute;
    bottom: 30px;
    right: 30px;
    letter-spacing: 1px;
    }

    @media only screen and (max-width: 480px) {
        .course-preview {
            max-width: 100%;
        }
        .course {
            display: table-cell;
        }
        .courses-container {
            margin-top: 20px;
        }
        .progress-container {
            right: 30px;
        }
    }
    h2 {
        margin-left: 20px;
    }

    ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
        padding-bottom: 40px;
        overflow: hidden;
    }

    li {
        margin-right: 20px;
        float: left;
    }

    .subscription-invoice {
        background-color: #f7d449;
    border: 0;
    border-radius: 2px;
    box-shadow: 0 10px 10px rgb(0 0 0 / 20%);
    color: #fff;
    font-size: 16px;
    padding: 2px 20px;
    position: absolute;
    bottom: 30px;
    /* right: 30px; */
    letter-spacing: 1px;
}
    .invoice {
        background-color: #31744f !important;
    }



</style>
<section class="page-header-area my-course-area">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="page-title"><?php echo _l('welcome'); ?> <?php echo $this->session->name; ?></h1>
                <br />
            </div>
        </div>
    </div>
</section>

<!--
<section class="my-dashboard-area">
<div class="container">
<div class="row">
<div class="col-sm-2 col-lg-2">
<div class="card card-green">
<p>World’s#1 &amp; Largest Coding Education Company</p>
</div>
</div>
<div class="col-sm-5 col-lg-5">
<div class="card card-yellow">
<p>
<?php echo _li('no_schedule'); ?>
</p>
</div>
</div>
<div class="col-sm-2 col-lg-2">
<div class="card card-light-yellow">
<p><?php echo $this->session->name; ?></p>
</div>
</div>
<div class="col-sm-3 col-lg-3">
<div class="card card-blue">
<p>
<?php echo vsprintf(_li('You are student #%s Of the Icode Cohort'), [
    number_format(1559999 + $this->session->user_id)
]); ?>
</p>
</div>
</div>
</div>
</div>
</section>
-->

<section class="my-dashboard-area">
    <div class="container">
        <h2>Courses</h2>
        <div class="row">
            <?php foreach ($enrols as $enrol_i) {
    $user_info = $this->user_model->get($enrol_i['user_id']);

    $lead_info = $this->lead_model->get($user_info['lead_id']);

    $site_info = $this->site_model->get($enrol_i['site_id']);

    $payment_info = $this->enrol_model->getPaymentByEnrolId($enrol_i['id']);

    $plan = '';
    $amount = '';
    if ($enrol_i['emi_type'] === 'premium') {
        $plan = 'premium';
        $amount = $site_info['discount_premium_plan'];
    } else {
        $plan = 'base';
        $amount = $site_info['discount_base_plan'];
    }


            ?>

            <div class="col-md-12">
                <div class="courses-container">
                    <div class="course">
                        <div class="course-preview">
                            <h6>Course</h6>
                            <h2><?php echo $enrol_i['course']; ?></h2>
                            <a href="#"><?php echo _et($plan); ?>
                                <!--                                <i class="fas fa-chevron-right"></i>-->
                            </a>
                            <div class="progress-container">
                                <!--                                <div class="progress"></div>-->
                                <span class="progress-text">
                                    <?php if (!empty($enrol_i['expired'])) { ?>
                                    <span class="badge badge-danger"><?php _el('expired'); ?></span>
                                    <?php } else if (!empty($enrol_i['renewal'])) { ?>
                                    <span class="badge badge-warning"><?php _el('expiring_soon'); ?></span>
                                    <?php } else { ?>
                                    <?php echo _cs($enrol_i['status']); ?>
                                    <?php } ?>
                                </span>
                            </div>
                        </div>
                        <div class="course-info">

                            <!--                            <h6>Chapter 4</h6>-->
                            <ul>
                                <li>Total Points: 0</li>
                                <li>National Ranking: 0</li>
                                <li>Current Level: 0</li>
                            </ul>
<!--                            <h2>Callbacks &amp; Closures</h2>-->
                            <button class="btn btn-start">Start</button>
                            <?php if ($amount) { ?>
								<a
									id="downloadCertificate<?php echo $enrol_i['course']; ?>"
									href="<?php echo site_url('home/invoice/'); ?>"
									class="subscription-invoice invoice"
									target="_blank"
									style="display: none;"
								><?php _el('Download Certificate'); ?></a>
								<?php if(@$payment_info['order_id']) { ?>
								<a
									href="<?php echo site_url('home/invoice/' . $payment_info['order_id']); ?>"
									class="subscription-invoice invoice"
									target="_blank"
								><?php _el('Download Invoice'); ?></a>
								<?php } } if (empty($lead_info['discount_code']) && empty($payment_info['order_id'])) { ?>
								<a
									href="<?php echo site_url('home/renewal/' . $this->enrol_model->generatePaymentLink($enrol_i['id'], $amount, $plan)); ?>"
									class="subscription-invoice"
									target="_blank"
								><?php _el('Subscribe'); ?></a>
								<?php } ?>
                        </div>
                    </div>
                </div>

            </div>
            <?php } ?>
        </div>
    </div>
</section>

<script>
    $("#totalPointsPython").val(1);
    const formatHtml = data => {
        var html = '';

        $.each(data, function(key, value) {
            $.ajax({
                url: '<?php echo site_url('api/queryUserGameInfo'); ?>',
                type: 'POST',
                data: {
                    mode: value.gameMode,
                    level: value.gameLevel,
                    gameId: value.gameId,
                },
                success: function(json) {
                    if(json.code == 0) {
                        // alert(json.data.levelId);
                        /*$("#totalPoints"+value.gameName).text(value.gameLevel);
					$("#nationalRanking"+value.gameName).text(value.gameLevel);*/
                        $("#currentLevel"+value.gameName).text(json.data.levelId);
                        $("#downloadCertificate"+value.gameName).css('display', 'inline-block');
                        if(json.data.levelId == 0) {
                            $("#downloadCertificate"+value.gameName).remove();
                            $("#downloadCertificate"+value.gameName).css('display', 'none');
                        }
                    } else {
                        $("#downloadCertificate"+value.gameName).remove();
                    }
                },
                error: function(xhr) {
                    console.warn(xhr);
                }
            });

            // alert(Object.values(value));
            html += '<a class="btn btn-success" onclick="enterGame(' + value.gameMode + ',' + value.gameId + ')"><?php _eli('Start Learning'); ?></a>'
        });

        return html;
    };

    <?php if ($blockly) { ?>
    $.ajax({
        url: '<?php echo site_url('api/getGameList'); ?>',
        type: 'POST',
        data: {
            mode: 0,
            level: 0,
        },
        success: function(json) {
            console.log(json);
            if(json.code == 0) {
                let html = formatHtml(json.data);
                $("#gameListBlockly").html(html);
            }

        },
        error: function(xhr) {
            console.warn(xhr);
        }
    });
    <?php } ?>

    <?php if ($python) { ?>
    $.ajax({
        url: '<?php echo site_url('api/getGameList'); ?>',
        type: 'POST',
        data: {
            mode: 1,
            level: 0,
        },
        success: function(json) {
            console.log(json);
            if(json.code == 0) {
                let html = formatHtml(json.data);
                $("#gameListPython").html(html);
            }

        },
        error: function(xhr) {
            console.warn(xhr);
        }
    });
    <?php } ?>

    function enterGame(mode,gameId) {
        $.ajax({
            url: '<?php echo site_url('api/enterGame'); ?>',
            type: 'POST',
            data: {
                mode: mode,
                gameId: gameId,
            },
            success: function(json) {
                console.log(json);
                if(json.code == 0) {
                    window.location.href = "<?=GAME_URL?>?highLevel=1&mode="+mode+"&game="+gameId;
                }
            },
            error: function(xhr) {
                console.warn(xhr);
            }
        });
    }
</script>
