<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

					<form class="required-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">

						<div class="input-group mb-3">
							<a
								href="<?php echo $this->image_model->resize(!empty($details['image']) ? ($this->config->item('s3_user_gallery') . $details['image']) : 'no_image.png', 100, 100) ?>"
								id="logo-thumb-image-0"
								data-toggle="image"
								class="img-thumbnail"
								data-target="#site_image"
							>
								<img
									src="<?php echo $this->image_model->resize(!empty($details['image']) ? $this->config->item('cloudfront_url') . ($this->config->item('s3_user_gallery') . $details['image']) : 'no_image.png', 100, 100) ?>"
									alt="" title=""
									data-placeholder="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>"
									style="width:100px; height:100px;"
								/>
							</a>
							<input
								type="hidden"
								name="site_image"
								value="<?php echo !empty($details['image']) ? $details['image'] : ''; ?>"
								id="site_image"
							/>
						</div>

						<div class="form-group">
							<label for="country"><?php echo _l('parent_site'); ?></label>
							<select class="form-control select2" data-toggle="select2" data-site="<?=$details['parent_id'] ?? ''?>" name="parent_id" id="parent_site">
								<option value="0" selected><?php _el('select'); ?></option>
								<?php foreach ($event_details as $event_detail) { ?>
									<option value="<?php echo $event_detail['parent_site_id']; ?>" <?php echo ($event_detail['parent_site_id'] == $details['parent_id']) ? 'selected' : ''; ?>><?php echo $event_detail['name']; ?></option>
								<?php } ?>
							</select>
						</div>

						<div class="form-group">
							<label for="country"><?php echo _l('country'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="country_code" id="country">
								<option value="0" selected><?php _el('select'); ?></option>
								<?php $country_id = 1 ; foreach ($this->country_model->get_all()['rows'] ?? [] as $country) { ?>
									<?php if (($details['country_code'] ?? '') === $country['code']) { $country_id = $country['id']?>

										<option value="<?php echo $country['code']; ?>" data-id="<?php echo $country['id']; ?>" selected><?php echo $country['name']; ?></option>
									<?php } else { ?>
										<option value="<?php echo $country['code']; ?>" data-id="<?php echo $country['id']; ?>"><?php echo $country['name']; ?></option>
									<?php } ?>
								<?php } ?>
							</select>
						</div>

						<div class="form-group">
							<label for="state"><?php echo _l('state'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="state_id" id="state">
								<option value="0" selected><?php _el('select'); ?></option>
								<?php foreach ($this->state_model->get_all(['country_id' => $country_id])['rows'] ?? [] as $state) {
									if (($details['state_id'] ?? '') === $state['id']) {
								?>
								<option value="<?php echo $state['id']; ?>" selected><?php echo $state['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $state['id']; ?>"><?php echo $state['name']; ?></option>
								<?php } } ?>
							</select>
						</div>

						<div class="form-group">
							<label for="state"><?php echo _l('city'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="city_id" id="city">
								<option value="" selected><?php _el('select'); ?></option>
								<?php foreach ($this->city_model->get_all(['state_id' => $details['state_id']])['rows'] ?? [] as $city) {
									if (($details['city_id'] ?? '') === $city['id']) {
								?>
								<option value="<?php echo $city['id']; ?>" selected><?php echo $city['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $city['id']; ?>"><?php echo $city['name']; ?></option>
								<?php } } ?>
							</select>
						</div>

						<div class="form-group">
							<label><?php echo _l('currency'); ?></label>
							<select class="form-control select2" data-toggle="select2" id="currency_code" name="currency_code" required>
								<option value=""><?php echo _l('select_currency'); ?></option>
								<?php
								$currencies = $this->crud_model->get_currencies();
								foreach ($currencies as $currency) : ?>
									<option value="<?php echo $currency['code']; ?>" <?php if (($details['currency_code'] ?? '') === $currency['code']) echo 'selected'; ?>> <?php echo $currency['code']; ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-group">
							<label><?php echo _l('site_type'); ?><span class="required">*</span></label>
							<select class="form-control select2" data-toggle="select2" id="site_type" name="site_type" required>
								<option value=""><?php echo _l('select_site_type'); ?></option>
								<?php foreach ($site_types as $item) { ?>
									<option value="<?=$item['id']?>" <?=$details['site_type'] == $item['id'] ? 'selected' : ''?>><?=$item['name']?>(<?=$item['id']?>)</option>
								<?php } ?>
							</select>
						</div>

						<div class="form-group">
							<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="mobile_length"><?php echo _l('mobile_number_length'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="mobile_length" name="mobile_length" value="<?php echo $details['mobile_length'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="owner_email"><?php echo _l('owner_email'); ?><span class="required">*</span></label>
							<input type="email" class="form-control" id="owner_email" name="owner_email" value="<?php echo $details['owner_email'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="owner_mobile"><?php echo _l('owner_mobile'); ?></label>
							<input type="text" class="form-control" id="owner_mobile" name="owner_mobile" value="<?php echo $details['owner_mobile'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<label for="owner_name"><?php echo _l('owner_name'); ?></label>
							<input type="text" class="form-control" id="owner_name" name="owner_name" value="<?php echo $details['owner_name'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<label for="authorized_person"><?php echo _l('contact_person'); ?></label>
							<input type="text" class="form-control" id="authorized_person" name="authorized_person" value="<?php echo $details['authorized_person'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<label for="address"><?php echo _l('address'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="address" name="address" value="<?php echo $details['address'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="address"><?php echo _l('pincode'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="pincode" name="pincode" value="<?php echo $details['pincode'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="license_total"><?php echo _l('total_license'); ?><span class="required">*</span></label>
							<input type="number" class="form-control" id="license_total" name="license_total" value="<?php echo $details['license_total'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="name"><?php echo _l('site_code'); ?></label>
							<input type="text" class="form-control" id="site_code" name="site_code" value="<?php echo $details['site_code'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<label for="name"><?php echo _l('discount_code'); ?></label>
							<input type="text" class="form-control" id="discount_code" name="discount_code" value="<?php echo $details['discount_code'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<label for="name"><?php echo _l('discount_percentage'); ?></label>
							<input type="text" class="form-control" id="discount_percentage" name="discount_percentage" value="<?php echo $details['discount_percentage'] ?? ''; ?>">
						</div>



						<div class="form-group">
							<label for="country"><?php echo _l('timezone'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="timezone" id="timezone">
								<?php echo timezonechoice($details['timezone'] ?? ''); ?>
							</select>
						</div>

						<div class="form-group">
							<div class="row">
								<div class="col">
									<label for="base_price"><?php echo _l('base_price'); ?><span class="required">*</span></label>
									<input type="text" class="form-control" id="base_price" name="base_price" value="<?php echo $details['base_price'] ?? ''; ?>" required>
								</div>
								<div class="col">
									<label for="price_per_page"><?php echo _l('price_per_page'); ?><span class="required">*</span></label>
									<input type="text" class="form-control" id="price_per_page" name="price_per_page" value="<?php echo $details['price_per_page'] ?? ''; ?>" required>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="row">
								<div class="col">
									<label for="free_page_limit"><?php echo _l('free_page_limit'); ?><span class="required">*</span></label>
									<input type="text" class="form-control" id="free_page_limit" name="free_page_limit" value="<?php echo $details['free_page_limit'] ?? ''; ?>" required>
								</div>
								<div class="col">
									<label for="hard_cover_price"><?php echo _l('hard_cover_price'); ?><span class="required">*</span></label>
									<input type="text" class="form-control" id="hard_cover_price" name="hard_cover_price" value="<?php echo $details['hard_cover_price'] ?? ''; ?>" required>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="row">
								<div class="col">
									<label for="black_white_price_per_page"><?php echo _l('black_white_price_per_page'); ?><span class="required">*</span></label>
									<input type="text" class="form-control" id="black_white_price_per_page" name="black_white_price_per_page" value="<?php echo $details['black_white_price_per_page'] ?? ''; ?>" required>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="payment_gateway"><?php echo _l('payment_gateway'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="payment_gateway" id="payment_gateway">
								<?php foreach (PAYMENT_GATEWAYS as $payment_gateway) { ?>
									<?php if (($details['payment_gateway'] ?? '') === $payment_gateway['code']) { ?>
										<option value="<?php echo $payment_gateway['code']; ?>" selected><?php echo $payment_gateway['name']; ?></option>
									<?php } else { ?>
										<option value="<?php echo $payment_gateway['code']; ?>"><?php echo $payment_gateway['name']; ?></option>
									<?php } ?>
								<?php } ?>
							</select>
						</div>

						<div class="form-group">
							<label for="sms_gateway"><?php echo _l('sms_gateway'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="sms_gateway" id="sms_gateway">
								<?php foreach (SMS_GATEWAYS as $sms_gateway) { ?>
									<?php if (($details['sms_gateway'] ?? '') === $sms_gateway['code']) { ?>
										<option value="<?php echo $sms_gateway['code']; ?>" selected><?php echo $sms_gateway['name']; ?></option>
									<?php } else { ?>
										<option value="<?php echo $sms_gateway['code']; ?>"><?php echo $sms_gateway['name']; ?></option>
									<?php } ?>
								<?php } ?>
							</select>
						</div>

						<div class="form-group">
							<div class="row">
								<div class="col">
									<label for="tax_text"><?php echo _l('tax_text'); ?><span class="required">*</span></label>
									<input type="text" class="form-control" id="tax_text" name="tax_text" value="<?php echo $details['tax_text'] ?? ''; ?>" required>
								</div>
								<div class="col">
									<label for="tax"><?php echo _l('tax_percentage'); ?><span class="required">*</span></label>
									<input type="text" class="form-control" id="tax" name="tax" value="<?php echo $details['tax'] ?? ''; ?>" required>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="email_alert"><?php echo _l('email_alerts(comma_separated)'); ?></label>
							<input type="text" class="form-control" id="email_alert" name="email_alert" value="<?php echo $details['email_alert'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<?php if (empty($details)) { ?>
								<label for="owner_password"><?php echo _l('owner_password'); ?><span class="required">*</span></label>
								<input type="password" class="form-control" id="owner_password" name="owner_password" value="" required>
							<?php } else { ?>
								<label for="owner_password"><?php echo _l('owner_password'); ?></label>
								<input type="password" class="form-control" id="owner_password" name="owner_password" value="">
							<?php } ?>
						</div>

						<div class="form-group">
							<label for="status"><?php echo _l('can_add_site'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="can_add_site" id="can_add_site">
								<?php if (($details['can_add_site'] ?? '')) { ?>
									<option value="1" selected><?php echo _l('yes'); ?></option>
									<option value="0"><?php echo _l('no'); ?></option>
								<?php } else { ?>
									<option value="1"><?php echo _l('yes'); ?></option>
									<option value="0" selected><?php echo _l('no'); ?></option>
								<?php } ?>
							</select>
						</div>

						<div class="form-group">
							<label for="status"><?php echo _l('status'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="status" id="status">
								<?php if (($details['status'] ?? '')) { ?>
									<option value="1" selected><?php echo _l('enabled'); ?></option>
									<option value="0"><?php echo _l('disabled'); ?></option>
								<?php } else { ?>
									<option value="1"><?php echo _l('enabled'); ?></option>
									<option value="0" selected><?php echo _l('disabled'); ?></option>
								<?php } ?>
							</select>
						</div>

						<div class="form-group">
							<label for="status"><?php echo _l('verified'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="verified" id="verified">
								<?php if (($details['verified'] ?? '')) { ?>
									<option value="1" selected><?php echo _l('verified'); ?></option>
									<option value="0"><?php echo _l('non_verified'); ?></option>
								<?php } else { ?>
									<option value="1"><?php echo _l('verified'); ?></option>
									<option value="0" selected><?php echo _l('non_verified'); ?></option>
								<?php } ?>
							</select>
						</div>

						<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
$(function() {
	window['FILEMANAGER'] = '<?php echo base_url('servermanager'); ?>';
});
</script>
<script src="<?php echo base_url('assets/global/filemanager.js?v=1.0.2') ?>"></script>

<script>
	$('#state').on("change", myFunction);

	function myFunction() {
		$('#state').val()

	}


	$(function() {
		$('#parent_site').on('change', function() {
			$.post('<?php echo site_url('admin/site_detail'); ?>', {
				'site_id': $('#parent_site').val()
			}, function(json) {
				if (json.site) {
					const site = json.site;

					$('#timezone').val(site.timezone).trigger('change');
					$('#country').val(site.country_code).trigger('change');
					$('#state').val(site.timezone).trigger('change');
					$('#currency_code').val(site.currency_code).trigger('change');
					$('#payment_gateway').val(site.payment_gateway).trigger('change');
					$('#sms_gateway').val(site.sms_gateway).trigger('change');
					$('#tax_text').val(site.tax_text).trigger('change');
					$('#tax').val(site.tax).trigger('change');
					$('#mobile_length').val(site.mobile_length).trigger('change');
					$('#status').val(site.status).trigger('change');
					$('#base_price').val(site.base_price).trigger('change');
					$('#price_per_page').val(site.price_per_page).trigger('change');
					$('#free_page_limit').val(site.free_page_limit).trigger('change');
					$('#hard_cover_price').val(site.hard_cover_price).trigger('change');
					$('#black_white_price_per_page').val(site.black_white_price_per_page).trigger('change');
				}
			});
		});

		$('#country').on('change', function() {

			var country = $(this).find(':selected').attr('data-id');

			$.post({
				url: "<?= base_url("/api/getStates") ?>",
				data: JSON.stringify({
					country_id: country
				}),
				success: function(response) {
					const state = response.states;
					$("#state").empty();
					$("#city").empty();

					let ele = document.getElementById('state');
					ele.innerHTML = ele.innerHTML + '<option value="">Select</option>';
					document.getElementById('city').innerHTML = '<option value="">Select</option>';
					for (let i = 0; i < state.length; i++) {
						ele.innerHTML = ele.innerHTML + '<option value="' + state[i]['id'] + '">' + state[i]['name'] + '</option>';
					}
				}
			})

		})

		$('#state').on('change', function() {
			let ele = document.getElementById('city');
			$.post({
				url: "<?= base_url("/api/getCities") ?>",
				data: JSON.stringify({
					state_id: $('#state').val()
				}),
				dataType: 'json',
				success: function(response) {
					let cities = response.cities

					$("#city").empty();
					ele.innerHTML = ele.innerHTML + '<option value="">Select</option>';

					for (let i = 0; i < cities.length; i++) {
						ele.innerHTML = ele.innerHTML + '<option value="' + cities[i]['id'] + '">' + cities[i]['name'] + '</option>';
					}
				}
			})
		})
	})
</script>
