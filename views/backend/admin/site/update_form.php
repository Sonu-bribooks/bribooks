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
                    <div class="text-danger"></div>
                    <div class="text-success"></div>
                </div>
            </div>
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

					<!-- <form class="required-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data"> -->
					<form class="required-form" id="site_form" method="post">
                        <input type="hidden" value="<?= $details['id']?>" name="site_id">
						<div class="form-group">
							<label for="name"><?php echo _l('site_id'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" name="id" value="<?php echo $details['id'] ?? ''; ?>" disabled>
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
							<label for="name"><?php echo _l('school_name'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="owner_email"><?php echo _l('email'); ?><span class="required">*</span></label>
							<input type="email" class="form-control" id="owner_email" name="owner_email" value="<?php echo $details['owner_email'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="owner_mobile"><?php echo _l('mobile'); ?></label>
							<input type="text" class="form-control" id="owner_mobile" name="owner_mobile" value="<?php echo $details['owner_mobile'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<label for="owner_name"><?php echo _l('owner_name'); ?></label>
							<input type="text" class="form-control" id="owner_name" name="owner_name" value="<?php echo $details['owner_name'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<label for="authorized_person"><?php echo _l('authorized_person'); ?></label>
							<input type="text" class="form-control" id="authorized_person" name="authorized_person" value="<?php echo $details['authorized_person'] ?? ''; ?>">
						</div>

						<!-- <button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button> -->
						<button type="submit" class="btn btn-primary"><?php echo _l("submit"); ?></button>
					</form>
				</div>
			</div>
            <div class="card-body">
				<div class="col-lg-12">
                    <div class="text-danger"></div>
                    <div class="text-success"></div>
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


        $('#site_form').submit(function(e) {
            e.preventDefault();

            $.ajax({
            	url: '<?php echo base_url('admin/ajax_site_update'); ?>',
            	type: 'POST',
            	data: $(this).serialize(),
            	cache: false,
            	success: function(data) {
                    console.log('success-error', data);
            		if (data.success) {
            			// alert(data.success);
                        $('.text-danger').text('')
                        $('.text-success').text(data.success)
                        setTimeout(function(){ location.reload() }, 3000);
            		} else if (data.error) {
            			// alert(data.error);
                        $('.text-success').text('')
                        $('.text-danger').text(data.error)
            		}
            	},
                error: function(error) {
            	}
            });
        });
	})
</script>
