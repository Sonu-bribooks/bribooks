<style>

    html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,dl,dt,dd,ol,nav ul,nav li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline;}
    article, aside, details, figcaption, figure,footer, header, hgroup, menu, nav, section {display: block;}
    ol,ul{list-style:none;margin:0;padding:0;}
    blockquote,q{quotes:none;}
    blockquote:before,blockquote:after,q:before,q:after{content:'';content:none;}
    table{border-collapse:collapse;border-spacing:0;}
    /* start editing from here */
    a{text-decoration:none;}
    .txt-rt{text-align:right;}/* text align right */
    .txt-lt{text-align:left;}/* text align left */
    .txt-center{text-align:center;}/* text align center */
    .float-rt{float:right;}/* float right */
    .float-lt{float:left;}/* float left */
    .clear{clear:both;}/* clear float */
    .pos-relative{position:relative;}/* Position Relative */
    .pos-absolute{position:absolute;}/* Position Absolute */
    .vertical-base{	vertical-align:baseline;}/* vertical align baseline */
    .vertical-top{	vertical-align:top;}/* vertical align top */
    .underline{	padding-bottom:5px;	border-bottom: 1px solid #eee; margin:0 0 20px 0;}/* Add 5px bottom padding and a underline */
    nav.vertical ul li{	display:block;}/* vertical menu */
    nav.horizontal ul li{	display: inline-block;}/* horizontal menu */
    img{max-width:100%;}
    body.gray-bg {
        background: url(https://learn.icode.org/lp/assets/images/blocklyC.png);
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;

    }
    section.category-header-area {
        background: #36899d;
        color: #fff;
    }
    @media (min-width: 1200px)
        .container {
            max-width: 100;
    }

    .wrap{
        width: 50%;
        margin: 0 auto;
    }
    /*start-contact-form*/
    .contact_form {
        width: 55%;
        background: url("<?= base_url('assets/frontend/default/img/login/border.png')?>") no-repeat 331px 77px;
        float: left;
        position: relative;
    }
    .contact-form{
        background:#fff;
        padding: 2%;
        margin: 5% 23% 0 23%;
        position: relative;
        border-radius: 12px;
        -webkit-border-radius: 12px;
        -moz-border-radius: 12px;
        -o-border-radius: 12px;
    }
    .contact-form h1{
        font-size: 1.7em;
        color:#474646;
    }
    .contact_form ul {
        list-style-type:none;
        list-style-position:outside;
        margin:0px;
        padding:0px;
    }
    .contact_form li{
        position:relative;
    } 
    /* form element visual styles */
    .contact_form li{ 
        float: left;
        outline: none;
        border: 1px solid #DDDDDD;
        font-size: 1.2em;
        color: #B6B6B6;
        width: 78.5%;
        font-weight: 600;
        margin-top: 8%;
        position: relative;
        height: 42px;
        border-radius: 2px;
        -webkit-border-radius: 2px;
        -moz-border-radius: 2px;
        -o-border-radius: 2px;
        transition: all 0.5s ease-out;
        -webkit-transition: all 0.5s ease-out;
        -moz-transition: all 0.5s ease-out;
        -ms-transition: all 0.5s ease-out;
        -o-transition: all 0.5s ease-out;
    }
    .contact_form li:hover{ 
        border: 1px solid #79B42B;
        color:#79B42B;
    }
    .contact_form input {
        float: left;
        font-size: 1.1em;
        padding: 7px 14px;
        width: 75%;
        border: none;
        outline: none;
        color:#000;
    }
    .contact_form p {
        float: right;
        padding: 8px 7px;
        width: 9.3%;
        cursor: pointer;
    }
    .contact_form button {
        border: none;
        outline: none;
        cursor: pointer;
        color: #fff;
        background: #79B42B;
        width: 79%;
        padding: 12px;
        font-size: 1.3em;
        letter-spacing:1px;
        margin: 28px 0 30px;
        transition: all 0.5s ease-out;
        -webkit-transition: all 0.5s ease-out;
        -moz-transition: all 0.5s ease-out;
        -ms-transition: all 0.5s ease-out;
        -o-transition: all 0.5s ease-out;
        border-radius: 2px;
        -webkit-border-radius: 2px;
        -moz-border-radius: 2px;
        -o-border-radius: 2px;
    }	
    .contact_form button:hover {
        background:#88C470;
    }	
    .contact_form input[type="checkbox"] {
        width: 21px;
        vertical-align: middle;
        padding: 5px;
        float:left;
    }
    .contact_form i{
        font-size: 1.2em;
        color:#B6B6B6;
        width: 40%;
        float: left;
    }
    .forgot{
        float: right;
        margin-right: 77px;
        width: 35%;
        margin-top: 4px;
    }
    .forgot a{
        color:#B6B6B6;
        font-size: 1.1em;
        -webkit-transition: all 0.5s ease-out;
        -moz-transition: all 0.5s ease-out;
        -ms-transition: all 0.5s ease-out;
        -o-transition: all 0.5s ease-out;
    }
    .forgot a:hover{
        color:#79B42B;
    }
    /* === Form hints === */
    .form_hint {
        color: #fff;
        text-align: center;
        background:#79B42B;
        border-radius: 3px 3px 3px 3px;
        font-size:1.2em;
        margin-left: 8px;
        padding: 6px 23px;
        z-index: 999;
        position: absolute;
        right:-224px;
        bottom: 3px;
        font-weight:100;
        display: none;
    }
    .form_hint::before {
        content: '';
        position: absolute;
        bottom: 9px;
        left: -9px;
        width: 0;
        height: 0;
        border-bottom: 10px solid transparent;
        border-right: 10px solid #79B42B;
        border-top: 10px solid transparent;
    }
    .contact_form input:focus + .form_hint {display: inline;}
    .contact_form input:required:valid + .form_hint {color: #000;
        background: #79B42B;}
    .contact_form input:required:valid + .form_hint::before {color:#28921f;}
    /*end-contact-form*/
    /*start-account*/
    .account{
        float:right;
        width: 45%;
    }
    .account h2 a{
        color:#71B8E4;
        display: block;
        font-size: 1.3em;
        font-weight: 400;
        text-align:right;
        margin-top: 3px;
        transition: all 0.5s ease-out;
        -webkit-transition: all 0.5s ease-out;
        -moz-transition: all 0.5s ease-out;
        -ms-transition: all 0.5s ease-out;
        -o-transition: all 0.5s ease-out;
    }
    .account h2 a:hover{
        color:#79B42B;
    }
    .span {
        margin-top: 10.4%;
        display: block;
        width: 100%;
        background: #3B5998;
        transition: all 0.5s ease-out;
        -webkit-transition: all 0.5s ease-out;
        -moz-transition: all 0.5s ease-out;
        -ms-transition: all 0.5s ease-out;
        -o-transition: all 0.5s ease-out;
    }
    .span img {
        background:#354F88;
        padding: 7px;
        float: left;
    }
    .span i {
        color: #fff;
        padding: 9px 14px;
        float: left;
        font-size: 1.6em;
        font-weight: 400;
    }
    .span:hover {
        background:#354F88;
    }
    .span1 {
        margin-top: 9%;
        width: 100%;
        background: #45B0E3;
        transition: all 0.5s ease-out;
        -webkit-transition: all 0.5s ease-out;
        -moz-transition: all 0.5s ease-out;
        -ms-transition: all 0.5s ease-out;
        -o-transition: all 0.5s ease-out;
    }
    .span1 img {
        background: #40A2D1;
        padding: 7px;
        float: left;
    }
    .span1 i {
        color: #fff;
        padding: 9px 20px;
        float: left;
        font-size: 1.6em;
        font-weight: 400;
    }
    .span1:hover {
        background:#40A2D1;
    }
    .span2 {
        /*        margin-top: 9%;*/
        width: 100%;
        background: #f2903d;
        transition: all 0.5s ease-out;
        -webkit-transition: all 0.5s ease-out;
        -moz-transition: all 0.5s ease-out;
        -ms-transition: all 0.5s ease-out;
        -o-transition: all 0.5s ease-out;
    }
    .span2 img {
        height: 44px;
        background: #bb6a26;
        padding: 7px;
        float: left;
    }
    .span2 i {
        color: #fff;
        padding: 9px 17px;
        float: left;
        font-size: 1.6em;
        font-weight: 400;
    }
    .span2:hover {
        background: #bb6a26;
    }
    /*end-account*/
    /*start-footer*/
    .footer p{
        color:#fff;
        display: block;
        font-size: 1.3em;
        font-weight: 400;
        text-align:center;
        padding: 2em 0 1em;
    }
    .footer p a{
        color:#68B74A;
        -webkit-transition: all 0.5s ease-out;
        -moz-transition: all 0.5s ease-out;
        -ms-transition: all 0.5s ease-out;
        -o-transition: all 0.5s ease-out;
    }
    .footer p a:hover{
        color:#fff;
    }
    /*end-footer*/
    /*start-checkbox*/
    .checkbox {
        margin-bottom: 4px;
        padding-left: 27px;
        font-size: 1.1em;
        line-height: 27px;
        color: #B6B6B6;
        cursor: pointer;
    }
    .checkbox:last-child {
        margin-bottom: 0;
    }
    .checkbox input {
        position: absolute;
        left: -9999px;
    }

    .checkbox i {
        position: absolute;
        bottom: 9px;
        left: 0;
        display: block;
        width: 13px;
        height: 13px;
        outline: none;
        border: 2px solid #DDDDDD;
        background: #DDDDDD;
    }
    .checkbox input + i:after {
        position: absolute;
        opacity: 0;
        transition: opacity 0.1s;
        -o-transition: opacity 0.1s;
        -ms-transition: opacity 0.1s;
        -moz-transition: opacity 0.1s;
        -webkit-transition: opacity 0.1s;
    }
    .checkbox input + i:after {
        content: '';
        background: url("../images/mark.png") no-repeat 3px 2px;
        top: -1px;
        left: -1px;
        width: 15px;
        height: 15px;
        font: normal 12px/16px FontAwesome;
        text-align: center;
    }

    .checkbox input:checked + i:after {
        opacity: 1;
    }
    .checkbox {
        float: left;
        margin-right: 30px;
    }
    .checkbox:last-child {
        margin-bottom: 4px;
    }
    .sign-up {
        padding: 0 42%;
        margin-top: 20px;
        color: #fff;
    }
    
    .sign-up a u {
        color: #f2903d;
    }
    /*end-checkbox*/
    /*-----start-responsive-design------*/
    @media only screen and (max-width: 1440px){
        .wrap{
            width:56%;
        }
        .span i {
            font-size: 1.4em;
        }
        .span1 i {
            font-size: 1.4em;
        }
        .span2 i {
            font-size: 1.4em;
        }
        .contact_form p {
            padding: 8px 7px;
        }
    }
    @media only screen and (max-width: 1366px){
        .wrap{
            width:59%;
        }
        .span i {
            font-size: 1.4em;
        }
        .span1 i {
            font-size: 1.4em;
        }
        .span2 i {
            font-size: 1.4em;
        }
        .contact_form p {
            padding: 8px 3px;
        }
    }
    @media only screen and (max-width: 1280px){
        .wrap{
            width:63%;
        }
        .span i {
            font-size: 1.3em;
        }
        .span1 i {
            font-size: 1.3em;
        }
        .span2 i {
            font-size: 1.3em;
        }
    }
    @media only screen and (max-width: 1024px){
        .wrap{
            width:79%;
        }
        .contact_form input {
            width: 77%;
        }
        .span i {
            font-size: 1.2em;
            padding: 13px 14px;
        }
        .span1 i {
            font-size: 1.2em;
            padding: 13px 14px;
        }
        .span2 i {
            font-size: 1.2em;
            padding: 13px 14px;
        }
        .checkbox {
            margin-right: 0px;
        }
    }
    @media only screen and (max-width: 800px){
        .wrap{
            width:95%;
        }
        .span i {
            padding: 14px 12px;
            font-size: 1.2em;
        }
        .span1 i {
            font-size: 1.2em;
            padding: 13px 20px;
        }
        .span2 i {
            font-size: 1.2em;
            padding: 13px 20px;
        }
        .contact_form {
            background: url("<?= base_url('assets/frontend/default/img/login/border.png') ?>") no-repeat 313px 77px;
        }
        .forgot {
            margin-right: 65px;
            width: 38%;
        }
        .checkbox {
            margin-right: 0px;
        }
    }
    @media only screen and (max-width: 640px){
        .wrap{
            width:95%;
        }
        .contact-form h1 {
            font-size: 1.3em;
        }
        .contact_form li {
            height: 37px;
            margin-top: 7.3%;
        }
        .contact_form input {
            font-size: 1em;
            padding: 5px 14px;
        }
        .contact_form button {
            font-size: 1.2em;
            padding: 10px;
            margin: 22px 0 30px;
        }

        .forgot a {
            font-size: 1em;
        }
        .span i {
            padding: 11px 6px;
            font-size: 1em;
        }
        .span img {
            padding: 2px;
        }
        .span1 img {
            padding: 2px;
        }
        .span1 i {
            padding: 11px 6px;
            font-size: 1em;
        }
        .span2 img {
            padding: 2px;
        }
        .span2 i {
            padding: 11px 6px;
            font-size: 1em;
        }
        .contact_form {
            background: url("<?= base_url('assets/frontend/default/img/login/border.png') ?>") no-repeat 247px 41px;
        }
        .forgot {
            margin-right: 27px;
            width: 39%;
            margin-top: 6px;
        }
        .account h2 a {
            font-size: 0.9em;
        }
        .checkbox i {
            bottom: 10px;
        }

    }
    @media only screen and (max-width: 480px){
        .wrap{
            width:56%;
        }
        .contact_form {
            width: 100%;
            background:none;
        }
        .contact-form {
            margin: 5%;
        }
        .contact_form li {
            width: 99.5%;
        }
        .contact_form button {
            width: 100%;
            margin: 22px 0 13px;
        }
        .forgot {
            width: 82%;
            margin-top: 7px;
            margin-right: 41px;
        }
        .account {
            width: 100%;
        }
        .account h2 a {
            text-align: left;
            margin-top: 15px;
        }
        .span {
            margin-top: 3.4%;
        }
        .form_hint {
            font-size: 0.8em;
            padding: 6px 5px;
            right: -128px;
            bottom: 6px;
        }
        .form_hint::before {
            bottom: 5px;
        }
        .checkbox i {
            bottom: 34px;
        }
        .footer p {
            font-size: 1.1em;
        }
        .sign-up {
            padding: 0 22%;
        }
    }
    @media only screen and (max-width: 320px){
        .wrap{
            width:85%;
        }
        .contact_form {
            width: 100%;
            background:none;
        }
        .contact_form li {
            width: 99.5%;
        }
        .contact_form button {
            width: 100%;
            margin: 22px 0 13px;
        }
        .forgot {
            width: 82%;
            margin-top: 7px;
            margin-right: 41px;
        }
        .account {
            width: 100%;
        }
        .account h2 a {
            text-align: left;
            margin-top: 15px;
        }
        .span {
            margin-top: 3.4%;
        }
        .form_hint {
            font-size: 0.8em;
            padding: 6px 5px;
            right: -128px;
            bottom: 6px;
        }
        .form_hint::before {
            bottom: 5px;
        }
        .checkbox i {
            bottom: 34px;
        }
        .footer p {
            font-size: 1em;
        }
    }

    .footer-area {
        display: none;
    }
</style>


<div class="contact-form">
    <!-- start-form -->
    <form class="contact_form" action="#" method="post" name="contact_form">
        <h1>Login Into Your Account</h1>
        <ul>
            <li class="d-none" id="school-code-box">
                <input type="school_code" class="textbox1" name="school_code" id="school-code" placeholder="<?php echo _l('school_code'); ?>" value="" required>
                <!--	            <span class="form_hint">Enter a valid email</span>-->
                <p><img src="<?= base_url("assets/frontend/default/img/login/contact.png")?>" alt=""></p>
            </li>
            <li>
                <input type="email" class="textbox1" name="email" id="login-email" placeholder="<?php echo _l('email'); ?>" value="" required>
                <!--	            <span class="form_hint">Enter a valid email</span>-->
                <p><img src="<?= base_url("assets/frontend/default/img/login/contact.png")?>" alt=""></p>
            </li>
            <li id="otp-box" class="d-none">
                <input type="text" name="otp" id="login-otp" class="textbox2" placeholder="Validation Code">
                <p><img src="<?= base_url("assets/frontend/default/img/login/lock.png")?>" alt=""></p>
            </li>
        </ul>
        <button class="" id="button-send-otp" type="button" onClick="sendOtp();"><?php _el('send_validation_code'); ?></button>
        <button type="button" class="d-none" id="button-login" onclick="validateOtp()"><?php echo _l('submit_validation_code'); ?></button>
<!--        <input type="submit" name="Sign In" value="Sign In">-->
        <div class="clear"></div>	



    </form>
    <!-- end-form -->
    <!-- start-account -->
    <div class="account">
        <!--	<h2><a href="<?= base_url('lp')?>">Don' have an account? Sign Up!</a></h2>-->

        <div class="span2"><a href="javascript:void(0);" onclick="$('#school-code-box').toggleClass('d-none');"><img src="<?= base_url('lp/assets/images/school.png') ?>" alt=""><i>Sign In with school code</i><div class="clear"></div></a></div>
    </div>	
    <!-- end-account -->
    <div class="clear"></div>	
</div>
<h2 class="sign-up">Don' have an account?<a href="<?= base_url('lp')?>"> <u>Sign Up!</u></a></h2>

<script type="text/javascript">
    function toggoleForm(form_type) {
        if (form_type === 'login') {
            $('.login-form').show();
            $('.forgot-password-form').hide();
            $('.register-form').hide();
        }else if (form_type === 'registration') {
            $('.login-form').hide();
            $('.forgot-password-form').hide();
            $('.register-form').show();
        }else if (form_type === 'forgot_password') {
            $('.login-form').hide();
            $('.forgot-password-form').show();
            $('.register-form').hide();
        }
    }
</script>
<script>
    function sendOtp() {
        $el = $('#login-email');
        $('#send-otp-wrapper').addClass('d-none');

        $.post('<?php echo site_url('login/sendOtp'); ?>', {email: $el.val()}, function(json) {
            if (json.success) {
                $('#otp-box').removeClass('d-none');
                
                $('#button-send-otp').addClass('d-none');
                $('#send-otp-wrapper').addClass('d-none');
                $('#button-login').removeClass('d-none');

                setTimeout(() => {
                    $('#send-otp-wrapper').removeClass('d-none');
                }, 30000);
                success_notify(json.success);
            } else {
                error_notify(json.error);
            }
        });
    }

    function validateOtp() {
        $.post('<?php echo site_url('login/validateOtp'); ?>', {
            email: $('#login-email').val(),
            school_code: $('#school-code').val(),
            otp: $('#login-otp').val()
        }, function(json) {
            if (json.success) {
                success_notify(json.success);
            }

            if (json.redirect) {
                window.location = json.redirect;
            } else {
                json.error && error_notify(json.error);
            }
        });
    }

    <?php if (0) { ?>
    $('#login-otp').on('keyup', function() {
        $el = $(this);

        if ($el.val().length == 6) {

        }
    });
    <?php } ?>
</script>
