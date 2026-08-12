<?php
 // echo "Name ".$name;
?>
<style>
	.hide {
		display: none;
	}
</style>

<section class="category-header-area">
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
					<?php echo _l('renewal'); ?>
				</h1>
			</div>
		</div>
	</div>
</section>

<section class="category-course-list-area">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-6">
				<div class="user-dashboard-box mt-3">
					<div class="user-dashboard-content w-100 register-form">
						<div class="content-title-box">
							<div class="title"><?php echo _l('renewal_form'); ?></div>
							<div class="subtitle"><?php echo _l('renewal_your plan'); ?>.</div>
						</div>

						<form action="<?php echo site_url('home/renew/'.$code); ?>" method="post" id="form-renewal">
							<div class="content-box">
								<div class="basic-group">
									<div class="form-group">
										<label for="name">
											<span class="input-field-icon"><i class="fas fa-user-graduate"></i></span>
											<?php echo _l('_name'); ?>:
										</label>
										<input
											type="text"
											class="form-control"
											name="name"
											id="name"
											placeholder="<?php echo _l('name'); ?>"
											value="<?= @$name?>"
											disabled
										/>
									</div>

									<div class="form-group">
										<label for="registration-email">
											<span class="input-field-icon"><i class="fas fa-envelope"></i></span>
											<?php echo _l('email'); ?>:
										</label>
										<input
											type="email"
											class="form-control"
											name="email1"
											disabled
											id="registration-email"
											placeholder="<?php echo _l('email'); ?>"
											value="<?= @$email?>"
										/>
									</div>
									<div class="form-group">
										<label for="course">
											<span class="input-field-icon"><i class="fas fa-book"></i></span>
											<?php echo _l('course'); ?>:
										</label>
										<input
											type="text"
											class="form-control"
											name="course"
											disabled
											id="course"
											value="<?= @$course?>"
										/>
									</div>

									<div class="form-group">
										<label for="registration-mode">
											<span class="input-field-icon"><i class="fas fa-school"></i></span>
											<?php echo _l('mode_of_learning'); ?>:
										</label>
										<select
											class="form-control select2"
											data-toggle="select2"
											name="mode1"
											id="mode1"
											disabled
										>
											<option value="online" <?= @$mode == 'online' ? 'selected' : ''?>>
												<?php echo _l('online'); ?>
											</option>
											<option value="offline" <?= @$mode == 'offline' ? 'selected' : ''?>>
												<?php echo _l('offline'); ?>
											</option>

										</select>
									</div>

									<div class="form-group">
										<label for="emi-type">
											<span class="input-field-icon"><i class="far fa-money-bill-alt"></i></span>
											<?php echo _l('emi_type'); ?>:
										</label>
										<input
											type="text"
											class="form-control"
											name="emi_type"
											id="emi_type"
											placeholder="EMI Type"
											value="<?= @$emi_type?>"
											disabled
										/>
									</div>

									<div class="form-group">
										<label for="payment-mode">
											<span class="input-field-icon"><i class="far fa-money-bill-alt"></i></span>
											<?php echo _l('payment') . ' ( in ' . strtoupper(@$currency_code) . ')'; ?>:
                                            <!-- <span style='text-align: right'><?= @$mrp_amount; ?></span> -->
										</label>

										<?php if (isset($emi_type) && $emi_type == 'other') { ?>
											<div class="other_text_div">
												<input
													type="text"
													class="form-control"
													name="other_price"
													id="other_price"
													placeholder=""
													value=""
												/>
											</div>
										<?php } else { ?>
											<div class="level_div">
												<input
													type="text"
													class="form-control"
													name="price"
													id="price"
													placeholder=""
													value="<?= @$amount;?>"
													disabled
												/>
											</div>
										<?php } ?>
									</div>
								</div>
							</div>
							<div class="content-update-box">
								<button type="submit" class="btn" id="button-pay"><?php echo _l('Pay'); ?></button>
							</div>
							<!-- <div class="account-have text-center">
								<?php //echo _l('already_have_an_account'); ?>?
								<a href="javascript::" onclick="toggoleForm('login')"><?php //echo _l('login'); ?></a>
							</div> -->
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>

$(function() {
	$('#form-renewal').on('submit', function(e) {
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
			},
			complete: function() {
			},
			success: function(json) {
				// console.log(json);
				if (json.order) {
					var rzp1 = new Razorpay({    
						"key": json.order.key,     
						"amount": json.order.amount,
						"currency": json.order.currency_code,
						"name": json.order.name,    
						"description": json.order.description,    
						"image": json.order.logo,
						"order_id": json.order.order_id,
						"handler": function (response) {  
							$('#button-pay').prop('disabled', true);
							$('#button-pay').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> processing...');
      
							// console.log(response.razorpay_payment_id);    
							// console.log(response);
							$.post("<?= site_url('home/renewTransaction') ?>", {
								payment_id: response.razorpay_payment_id,
								signature: response.razorpay_signature,
								id: json.order.id,
								order_id: json.order.order_id,
								code: json.order.code,
							}, json => {
								json.redirect && setTimeout(() => (location = json.redirect), 300);
								// console.log(json);
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

					//toastr.success(`json.success`);
					json.redirect && setTimeout(() => (location = json.redirect), 300);
				}

				if (json.error) {
					toastr.error(`${json.error}`, 'Error', {timeOut: 150});
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	})
});
</script>
