<style type="text/css">
	div.form-group label:first-child {
		font-weight: 700;
	}
</style>
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
						<div class="form-group">
							<label for="parent">Select Event<span class="required">*</span></label>
							<select class="form-control select2" data-toggle="select2" name="event_id" id="event_id" required>
								<option value="<?php echo !empty($details['event_id']) ? ($details['event_id']) : ''; ?>"><?php echo !empty($details['event_id']) ? ($details['event_id']) : get_phrase('please_select'); ?></option>
							</select>
						</div>
						<div class="form-group">
							<label for="parent">Select Site<span class="required">*</span></label>
							<select class="form-control select2" data-toggle="select2" name="site_id" id="site_id" required>
								<option value="<?php echo !empty($details['site_id']) ? ($details['site_id']) : ''; ?>"><?php echo !empty($details['site_id']) ? ($details['site_id']) : get_phrase('please_select'); ?></option>
							</select>
						</div>
						<button type="button" class="btn btn-primary" onclick="checkRequiredFields()" id="subBtn"><?php echo _l("submit"); ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
$(function() {
	$('#event_id').select2({
		ajax: {
			delay: 250,
			url: '<?php echo site_url('admin/get_events'); ?>',
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

	$('#site_id').select2({
		ajax: {
			delay: 250,
			url: '<?php echo site_url('admin/get_sites'); ?>',
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

	function checkSchoolEvent (event_id, site_id) {
		$.ajax({
			url: '<?php echo site_url('admin/check_school_in_event'); ?>',
			type: 'POST',
			dataType: 'json',
			data: {
				event_id : event_id,
				site_id  : site_id
			},
			success: function(data){
				console.log(data);
				if(data.status == true){
					error_notify('This school is already added in this event.');
					$('#subBtn').prop('disabled', true);
				}else{
					$('#subBtn').prop('disabled', false);
				}
			}
		});
	}

	$(document).on('change', '#event_id', function(e) {
		e.preventDefault();
		e.stopPropagation();

		if ($(this).val() != '' && $('#site_id').val() != '' && $(this).val() != 0 && $('#site_id').val() != 0) {
			checkSchoolEvent($(this).val(), $('#event_id').val());
		}
	})

	$(document).on('change', '#site_id', function(e) {
		e.preventDefault();
		e.stopPropagation();

		if ($('#event_id').val() != '' && $(this).val() != '' && $('#event_id').val() != 0 && $(this).val() != 0) {
			checkSchoolEvent($('#event_id').val(),$(this).val());
		}
	})
});
</script>
