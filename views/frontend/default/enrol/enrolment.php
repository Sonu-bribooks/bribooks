<style>
.card {
	border: unset;
	border-radius: unset;
	box-shadow: 0 1px 1px -1px #000;
	margin-bottom: 15px;
}
.heading {
	font-size: 190%;
}
.subheading {
    font-size: 125%;
    font-weight: 600;
    text-transform: uppercase;
}
span.select2-selection.select2-selection--single {
	border-width: 2px;
	border-radius: unset;
}
.text-dark {
	color: #000 !important;
}
.text-grey {
	color: #999 !important;
}
.user-image {

}
.toggle {
	position: relative;
}
.toggle:after {
	position: absolute;
	content: "\f107";
	font-family: "Font Awesome 5 Free";
	right: 0;
}
.toggle.open:after {
	position: absolute;
	content: "\f106";
	font-family: "Font Awesome 5 Free";
	right: 0;
}
@media (max-width: 767px) {
	section.category-course-list-area {
	    min-height: 100vh;
	}
	.user-image {
	    max-width: 25vh;
	    margin: auto;
	    margin-bottom: 20px;
	}
	button#button-pay {
	    position: fixed;
	    bottom: 0;
	    left: 0;
	    right: 0;
	    line-height: 55px;
		z-index: 9;
	}
}
</style>
<h1 class="heading text-uppercase text-center font-weight-bold mt-5">
	<?php echo _l('checkout'); ?>
</h1>
<section class="category-course-list-area mt-4">
	<div class="container">
		<form action="<?php echo $action; ?>" method="post" id="form-enrol">
		<div class="row">
			<div class="col-lg-8">
				<div class="card card-body">
					<h3 class="subheading"><?php _el('subscription_type'); ?></h3>
					<select class="form-control select2" data-toggle="select2"  name="emi_type" id="emi_type"<?=$locked ? ' disabled' : ''?> required="">
						<option value=""><?= _l('select_subscription_type'); ?></option>
						<?php foreach ($prices as $key => $value) { ?>
						<option value="<?=$key?>"<?= $key == $emi_type ? ' selected' : '' ?>><?=_l($key)?></option>
						<?php } ?>
					</select>
				</div>
				<div class="card card-body">
					<h3
						class="subheading toggle"
						data-toggle="collapse"
						href="#collapseStudent"
						aria-expanded="false"
						aria-controls="collapseStudent"
					><?php _el('student_information'); ?></h3>
					<div class="collapse" id="collapseStudent">
						<div class="row">
							<image
								src="<?php echo $user_image; ?>"
								alt="<?php echo $name; ?>"
								class="col-sm-3 user-image"
							/>
							<div class="col-sm-9">
								<p class="text-dark"><?php _el('student_name'); ?>: <?php echo $name; ?></p>
								<p class="text-grey"><?php _el('parent_name'); ?>: <?php echo $parent_name; ?></p>
								<p class="text-grey"><i class="fas fa-mobile text-dark"></i> <?php echo $mobile; ?></p>
								<p class="text-grey"><i class="fas fa-envelope text-dark"></i> <?php echo $email; ?></p>
							</div>
						</div>
					</div>
				</div>

				<button type="submit" class="btn btn-lg btn-block text-uppercase" id="button-pay">
					<?php echo _l('pay'); ?> <span class="price"></span>
				</button>
			</div>
			<div class="col-lg-4">
				<div class="card card-body">
					<h3 class="subheading"><?php _el('course'); ?></h3>
					<p class="text-grey">
						<?php _el('course'); ?>: <?php echo $course; ?>
					</p>
					<hr>
					<?php if ($this->config->item('site_discount_code') && $this->config->item('site_premium_plan')) { ?>
					<div class="row text-dark">
						<p class="col-5"><?php _el('school_name'); ?></p>
						<p class="col-7 text-right">
							<?php echo $this->config->item('site_name'); ?>
						</p>
					</div>
					<hr>
					<?php } ?>
					<div class="row text-grey">
						<p class="col-7"><?php _el('subtotal'); ?></p>
						<p class="col-5 text-right">
							<?php if ($scholarship) { ?>
							<span class="base-price"><span><?php echo $this->config->item('site_currency_symbol'); ?></span> <?php echo round($base_amount / $instalment); ?></span>
							<?php } else { ?>
							<span class="price"></span>
							<?php } ?>
						</p>
					</div>
					<?php if ($scholarship) { ?>
					<div class="row text-grey small">
						<p class="col-7"><?php _el('scholarship_discount'); ?> (<?php echo $scholarship; ?>%)</p>
						<p class="col-5 text-right">
							<span class="scholarship">- <span><?php echo $this->config->item('site_currency_symbol'); ?></span> <?php echo round($base_amount / $instalment - $amount); ?></span>
						</p>
					</div>
					<hr>
					<?php } ?>

					<div class="row text-dark text-uppercase">
						<p class="col-7"><?php _el('total'); ?></p>
						<p class="col-5 text-right">
							<span class="price"></span>
						</p>
					</div>

					<div class="text-right text-grey">
						<?php if ($this->config->item('site_tax')) { ?>
						<small>*<?php echo $this->config->item('site_tax_text'); ?> <?php echo $this->config->item('site_tax'); ?> % <?php _el('included'); ?></small><br>
						<?php } ?>
						<?php if ($instalment > 1) { ?>
						<small class="text-info">
							<i class="fas fa-exclamation-circle"></i> <?php echo $instalment_text; ?>
						</small>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		</form>
	</div>
</section>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
$('.toggle').on('click', function() {
	$(this).toggleClass('open');
});
$(function() {
	if (window.innerWidth > 767) {
		$('.toggle').trigger('click');
	}
})
</script>

<script>
$(function() {
	<?php if ($locked) { ?>
	$('.price').html('<span><?php echo $this->config->item('site_currency_symbol'); ?></span> <?php echo $amount; ?>');
	<?php } else { ?>
	$('#emi_type').on('change', function(e) {
		var emi_type = $(this).val();
		var prices = <?php echo json_encode($prices); ?>;

		$.each(prices, function(key, value) {
			if (key == emi_type) {
				$('.price').html('<span><?php echo $this->config->item('site_currency_symbol'); ?></span> ' + value);
			}
		});
	});

	$('#emi_type').trigger('change');
	<?php } ?>

	$('#form-enrol').on('submit', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$el = $(this);

		$.ajax({
			url: $el.attr('action'),
			type: 'post',
			dataType: 'json',
			data: new FormData($el[0]),
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function() {
				$('#button-pay').prop('disabled', true);
			},
			complete: function() {
				$('#button-pay').prop('disabled', false);
			},
			success: function(json) {
				if (json.order) {
					var rzp1 = new Razorpay({    
						"key": json.order.key,     
						"amount": json.order.amount * 100,
						"currency": json.order.currency_code,
						"name": json.order.name,    
						"description": json.order.description,    
						"image": json.order.image,
						"order_id": json.order.order_id,
						"handler": function (response) {
							$('#button-pay').prop('disabled', true);
							$('#button-pay').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> processing...');

							$.post("<?= $action_pay ?>", {
								payment_id: response.razorpay_payment_id,
								signature: response.razorpay_signature,
								id: json.order.id,
								order_id: json.order.order_id,
								code: json.order.code,
							}, json => {
								json.redirect && setTimeout(() => (location = json.redirect), 300);
							});    
						},    
						"prefill": {
							"name": json.order.user.name,
							"email": json.order.user.email,
							"contact": json.order.user.mobile
						},    
						"notes": {        
							"address": json.order.address    
						},    
						"theme": {        
							"color": "#f08b3c"    
						}
					});

					rzp1.open();

					toastr.success(json.success);
					json.redirect && setTimeout(() => (location = json.redirect), 300);
				}

				if (json.error) {
					toastr.error(`${json.error}`, 'Error', {timeOut: 5000});
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});
});
</script>
