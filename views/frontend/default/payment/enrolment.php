<?php
 // echo "<pre>";print_r($price_plan);die;
 // echo $city_id;die;
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
					<?php echo _l('enrolment'); ?>
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
							<div class="title"><?php echo _l('enrolment_form'); ?></div>
							<div class="subtitle"><?php echo _l('enrol_and_start_learning'); ?>.</div>
						</div>

						<form action="<?php echo site_url('home/enrol/'.$code); ?>" method="post" id="form-enrol">
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
										<label for="parent_name">
											<span class="input-field-icon"><i class="fas fa-user"></i></span>
											<?php echo _l('parent_name'); ?>:
										</label>
										<input
											type="text"
											class="form-control"
											name = "parent_name"
											id="parent_name"
											placeholder="<?php echo _l('parent_name'); ?>"
											value="<?= @$parent_name ?>"
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
											id="registration-email"
											placeholder="<?php echo _l('email'); ?>"
											value="<?= @$email?>"
											disabled
										/>
									</div>

									<div class="form-group">
										<label for="parent_mobile">
											<span class="input-field-icon"><i class="fas fa-mobile"></i></span>
											<?php echo _l('parent_mobile'); ?>:
										</label>
										<input
											type="tel"
											class="form-control"
											name="parent_mobile"
											id="parent_mobile"
											placeholder="<?php echo _l('parent_mobile'); ?>"
											value="<?= @$mobile ?>"
											disabled
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

									<?php if (@$mode == 'offline') { ?>
									<div class="city-center">
										<div class="form-group">
											<label for="city">
												<span class="input-field-icon"><i class="fas fa-building"></i></span>
												<?php echo _l('city'); ?>:
											</label>
											<select
												class="form-control select2"
												data-toggle="select2"
												name="city"
												id="city"
												disabled
											>
												<?php if (isset($city_data) && !empty($city_data)) {
															foreach ($city_data as $c) {
																	$selected = $c['id'] == $city_id ? 'selected' : '';

																 ?>
																<option <?= $selected; ?> value="<?= $c['id'] ?>"><?= $c['name'] ?></option>";
														<?php	}
														}
													?>

											</select>
										</div>
										<div class="form-group">
											<label for="center">
												<span class="input-field-icon"><i class="fas fa-map-marker-alt"></i></span>
												<?php echo _l('center'); ?>:
											</label>
											<select
												class="form-control select2"
												data-toggle="select2"
												name="center"
												id="center"
												disabled
											>
												<?php if (isset($centers) && !empty($centers)) {
															foreach ($centers as $c) {
																	$selected = $c['id'] == $center_id ? 'selected' : '';

																 ?>
																<option <?= $selected; ?> value="<?= $c['id'] ?>"><?= $c['name'] ?></option>";
														<?php	}
														}
													?>

											</select>
										</div>
									</div>
									<?php } ?>

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
											<span class="input-field-icon" style='text-align: left'><i class="far fa-money-bill-alt"></i></span>
											<?php echo _l('payment') . ' ( in ' . strtoupper(@$currency_code) . ')'; ?>:
                                            <!-- <span style='text-align: right'><?= @$mrp_amount; ?></span> -->
										</label>
										<input
											type="text"
											class="form-control"
											name="amount"
											id="amount"
											placeholder="Payment"
											value="<?= @$amount?>"
											disabled
										/>
									</div>

									<?php if(0) { ?>
									<div class="form-group">
										<label for="course-level">
											<span class="input-field-icon"><i class="fas fa-thermometer-half"></i></span>
											<?php echo _l('level'); ?>:
										</label>
										<select
											class="form-control select2"
											data-toggle="select2"
											name="course_level"
											id="course_level"
											onchange="chooseEmi(this.value)"
										>
											<?php
											foreach ($price_plan as $key => $value) {
												echo "<option value='".$key."'>".ucwords($key)."</option>";
											}
											 ?>

										</select>
									</div>
									<div class="form-group">
										<label for="payment-mode">
											<span class="input-field-icon"><i class="far fa-money-bill-alt"></i></span>
											<?php echo _l('payment'); ?>:
										</label>
										<div class="level_div">
											<select
												class="form-control select2"
												data-toggle="select2"
												name="price"
												id="price"
											>
											<?php
											$i = 0;
											foreach ($price_plan as $key => $value) {
												foreach ($value as $k => $v) {
													echo '<option value="'.$k.','.$v.'">&#8377;'.$v.' ('.ucfirst(str_replace("_", " ", $k)).')</option>';
												}
												break;
											}
											 ?>
											</select>
										</div>
										<div class="other_text_div hide">
											<input
												type="text"
												class="form-control"
												name="other_price"
												id="other_price"
												placeholder=""
												value=""
											/>
										</div>
									</div>
									<?php } ?>
								</div>
							</div>
							<div class="content-update-box">
								<button type="submit" class="btn" id="button-pay"><?php echo _l('pay'); ?></button>
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

function chooseEmi(emi) {
	console.log("NNN " + emi);
	var emi_type = emi;
	var html = "";
	var pricePlanArr  = <?php echo json_encode($price_plan);?>;
	console.log(pricePlanArr);
	if (emi === 'other') {
		$('.other_text_div').removeClass('hide');
		$('.level_div').addClass('hide');
	} else {
		$('.other_text_div').addClass('hide');
		$('.level_div').removeClass('hide');
		$.each(pricePlanArr, function (index, value) {
	         if (emi_type.localeCompare(index)  == 0) {
	        	$.each(value, function (ind, val) {
					var mValue = ind.replace("_", " ");
					html += "<option value = '"+ ind + "," + val +"'>&#8377;"+ val +" ("+ mValue.replace(/^./, mValue[0].toUpperCase()) +")</option>";
					console.log('Inner ' + ind + " / " + val);
				});
	         }
	    });
		$('#price').html(html);
	}
	//console.log(pp);
}

$(function() {
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
							// console.log(response.razorpay_payment_id);
							// console.log(response);
							$('#button-pay').prop('disabled', true);
							$('#button-pay').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> processing...');

							$.post("<?= site_url('home/updateTransaction') ?>", {
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

					toastr.success(`json.success`);
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
