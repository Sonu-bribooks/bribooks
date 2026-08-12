<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window,document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
 fbq('init', '2317796231854998');
fbq('track', 'PageView');
</script>
<noscript>
 <img height="1" width="1"
src="https://www.facebook.com/tr?id=2317796231854998&ev=PageView
&noscript=1"/>
</noscript>
<!-- End Facebook Pixel Code -->

<!-- Event snippet for Purchases on Icode Hackathon conversion page -->
<script>
  gtag('event', 'conversion', {
      'send_to': 'AW-658784717/HKlTCNq43PYCEM2DkboC',
      'transaction_id': ''
  });
</script>

<!-- Taboola Pixel Code -->
<script type='text/javascript'>
  window._tfa = window._tfa || [];
  window._tfa.push({notify: 'event', name: 'page_view', id: 1308648});

  !function (t, f, a, x) {
    if (!document.getElementById(x)) {
        t.async = 1;t.src = a;t.id=x;f.parentNode.insertBefore(t, f);
    }
  }(document.createElement('script'),
  document.getElementsByTagName('script')[0],
  '//cdn.taboola.com/libtrc/unip/1308648/tfa.js',
  'tb_tfa_script');
</script>
<!-- End of Taboola Pixel Code -->

<style>
	.user-dashboard-content .content-title-box .subtitle {
		font-size: 17px;
		line-height: 27px;
		font-weight: 400;
		color: #29303b;
	}
</style>
<!-- <section class="category-header-area">
	<div class="container-lg">
		<div class="row">
			<div class="col">
				<nav>
					<ol class="breadcrumb">
						<li class="breadcrumb-item">
							<a href="<?php echo site_url('home'); ?>"><i class="fas fa-home"></i></a>
						</li>
						<li class="breadcrumb-item">
							<a href="#">
								<?php echo $page_title; ?>
							</a>
						</li>
					</ol>
				</nav>
				<h1 class="category-name">
					<?php echo _l('enrolment'); ?>
				</h1>
			</div>
		</div>
	</div>
</section> -->

<section class="category-course-list-area">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-9">
				<div class="user-dashboard-box mt-3">
					<div class="user-dashboard-content w-100 register-form">
						<div class="content-title-box">
							<i class="fa fa-check-circle fa-3x" style="color: green"></i>
							<div class="title text-success"><?php echo _l('thank_you'); ?></div>
							<div class="subtitle">Your payment of  <strong><?= @$amount;?></strong> has been received for the <?=@$type; ?> of <strong><?= @$course; ?></strong> course.</div>
							<div class="subtitle">Your order transaction id is <strong><?= @$payment_id; ?></strong>.</div>
							<div class="subtitle"><?=@$type; ?> details have been sent on your mail id and SMS sent on your mobile no.</div>
						</div>
						<div class="text-center m-t-20">
							<?php if ($this->input->cookie('login_code', TRUE)) {
								$redirect = base_url('login/code/' . $this->input->cookie('login_code', TRUE));
							} else {
								$redirect = base_url();
							} ?>
							<a href="<?php echo $redirect; ?>" class="btn btn-success">
								<?php echo _l('continue_to_dashboard'); ?>
							</a>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
</section>
