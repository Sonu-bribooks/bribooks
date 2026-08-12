<?php /*echo "<pre>"; print_r($teachers); die;*/ ?>
<!-- start page title -->
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
			  <div class="col-lg-12">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

				<form class="required-form" action="<?php echo $action ; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
						<label for="message"><?php echo _l('site_id'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="site_id" name="site_id" value="<?php echo $details['site_id'] ?? ''; ?>" required>
					</div>

					<div class="form-group">
						<label for="parent">Select Country<span class="required">*</span></label>
						<select class="form-control select2" data-toggle="select2" name="country_id" id="country_id" >
							<option value="<?php echo !empty($details['country_id']) ? ($details['country_id']) : 0; ?>"><?php echo !empty($details['country']) ? ($details['country']) : get_phrase('please_select'); ?></option>
						</select>
					</div>

					<div class="form-group">
						<label for="parent">Select State<span class="required">*</span></label>
						<select class="form-control select2" data-toggle="select2" name="state_id" id="state_id" >
							<option value="<?php echo !empty($details['state_id']) ? ($details['state_id']) : 0; ?>"><?php echo !empty($details['state']) ? ($details['state']) : get_phrase('please_select'); ?></option>
						</select>
					</div>

                    <div class="form-group">
						<label for="parent">Select City<span class="required">*</span></label>
						<select class="form-control select2" data-toggle="select2" name="city_id" id="city_id" >
							<option value="<?php echo !empty($details['city_id']) ? ($details['city_id']) : 0; ?>"><?php echo !empty($details['city']) ? ($details['city']) : get_phrase('please_select'); ?></option>
						</select>
					</div>

					<div class="form-group">
						<label for="message"><?php echo _l('grade'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="grade" name="grade" value="<?php echo $details['grade'] ?? ''; ?>" required>
					</div>

                    <div class="form-group">
						<label for="message"><?php echo _l('section'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="section" name="section" value="<?php echo $details['section'] ?? ''; ?>" required>
					</div>

                    <button type="button" class="btn btn-primary" id="subBtn" onclick="checkRequiredFields()"><?php echo _l('submit'); ?></button>
                    <button type="button" class="btn btn-dark" id="subBtn" onclick="history.back()"><?php echo _l('back'); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<script>
$(function() {
	$('#country_id').select2({
		ajax: {
			delay: 250,
			url: '<?php echo site_url('admin/ajax_search_country'); ?>',
			data: function (params) {
				var query = {
					search: params.term,
				}

				return query;
			},
			processResults: function(data) {
				return {
					results: data
				};
			}
		},
		minimumInputLength: 3
	});

	$('#state_id').select2({
		ajax: {
			delay: 250,
			url: '<?php echo site_url('admin/get_states'); ?>',
			data: function (params) {
				var query = {
					search: params.term,
				}

				return query;
			},
			processResults: function(data) {
				return {
					results: data.items
				};
			}
		},
		minimumInputLength: 3
	});

    $('#city_id').select2({
		ajax: {
			delay: 250,
			url: '<?php echo site_url('admin/get_cities'); ?>',
			data: function (params) {
				var query = {
					search: params.term,
				}

				return query;
			},
			processResults: function(data) {
				return {
					results: data.items
				};
			}
		},
		minimumInputLength: 3
	});
});
</script>
