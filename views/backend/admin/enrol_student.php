<!-- start page title -->
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('enrol_a_student'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
			  <div class="col-lg-12">
				<h4 class="mb-3 header-title"><?php echo _l('enrolment_form'); ?></h4>

				<form class="required-form" action="<?php echo site_url('admin/enrol_student/enrol'); ?>" method="post" enctype="multipart/form-data">

					<div class="form-group">
						<label for="user_id"><?php echo _l('user'); ?><span class="required">*</span> </label>
						<select class="form-control select2" data-toggle="select2" name="user_id" id="user_id" required>
							<option value=""><?php echo _l('select_a_student'); ?></option>

						</select>
						<a href="<?php echo site_url('admin/user_form/add_user_form'); ?>" target="_blank" class="btn btn-outline btn-info"><?php _el('add_student'); ?></a>
					</div>

					<div class="form-group">
						<label for="course_id"><?php echo _l('course_to_enrol'); ?><span class="required">*</span> </label>
						<select class="form-control select2" data-toggle="select2" name="course_id" id="course_id" required>
							<?php $course_list = $this->crud_model->get_courses()->result_array();
								foreach ($course_list as $course):
								//if ($course['status'] != 'active')
								//	continue;
							?>
								<option value="<?php echo $course['id'] ?>"><?php echo $course['title']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="form-group">
						<label for="mode"><?php echo _l('mode'); ?><span class="required">*</span> </label>
						<select class="form-control select2" data-toggle="select2" name="mode" id="mode" required>
							<option value="online" selected><?php echo _l('online'); ?></option>
							<option value="offline"><?php echo _l('offline'); ?></option>
						</select>
					</div>

					<div class="form-group">
						<label for="emi_type"><?php echo _l('emi_type'); ?><span class="required">*</span> </label>
						<select class="form-control select2" data-toggle="select2" name="emi_type" id="emi_type" required>
							<option value=""><?php echo _l('select_emi_type'); ?></option>
						</select>
					</div>

					<div class="form-group">
						<label for="amount"><?php echo _l('amount'); ?><span class="required">*</span> </label>
						<input type="text" class="form-control" data-toggle="select2" name="amount" id="amount" required>
					</div>

					<div class="form-group center d-none">
						<label for="city_id"><?php echo _l('city'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="city_id" id="city_id">
							<option value=""><?php echo _l('select_city'); ?></option>
							<?php foreach ($this->city_model->get_all()['rows'] as $city) { ?>
							<option value="<?php echo $city['id']; ?>"><?php echo $city['name']; ?></option>
							<?php  } ?>
						</select>
					</div>

					<div class="form-group center d-none">
						<label for="center_id"><?php echo _l('center'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="center_id" id="center_id">
							<option value=""><?php echo _l('select_center'); ?></option>
						</select>
					</div>

					<div class="form-group class-wrapper">
						<label for="class_id"><?php echo _l('class'); ?><span class="required">*</span> </label>
						<select class="form-control select2" data-toggle="select2" name="class_id" id="class_id" required>
							<option value=""><?php echo _l('select_class'); ?></option>
						</select>
					</div>

					<div class="form-group">
						<label for="payment_mode"><?php echo _l('payment_mode'); ?><span class="required">*</span> </label>
						<select class="form-control select2" data-toggle="select2" name="payment_mode" id="payment_mode" required>
							<option value="offline" selected><?php echo _l('offline'); ?></option>
							<option value="online"><?php echo _l('online'); ?></option>
						</select>
						<span class="text-danger"><?php _el('payment_mode_online_under_development'); ?></span>
					</div>

					<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l('enrol_student'); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<script>
$(function() {
	$('#user_id').select2({
		ajax: {
			url: '<?php echo site_url('admin/get_students'); ?>',
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
		}
	});

	$('#course_id').on('change', function() {
		getEmis($('#course_id').val(), $('#mode').val());
		getClasses($('#course_id').val(), $('#mode').val(), $('#center_id').val());
	});

	$('#mode').on('change', function() {
		if ($('#mode').val() == 'offline') {
			$('.center').removeClass('d-none');
		} else {
			$('.center').addClass('d-none');
		}

		getEmis($('#course_id').val(), $('#mode').val());

		getClasses($('#course_id').val(), $('#mode').val(), $('#center_id').val());
	});

	$('#city_id').select2();
	$('#center_id').select2();
	$('#class_id').select2();
	$('#mode').trigger('change');

	$('#city_id').on('change', function() {
		getCenters($('#city_id').val());
		getClasses($('#course_id').val(), $('#mode').val(), $('#center_id').val());
	});

	$('#center_id').on('change', function() {
		getClasses($('#course_id').val(), $('#mode').val(), $('#center_id').val());
	});
});
</script>

<script>
const getEmis = (course_id, mode) => {
	let fd = new FormData();
	fd.append('course_id', course_id);
	fd.append('mode', mode);

	submitForm('<?php echo site_url('admin/get_emis'); ?>', fd, json => {
		if (json.emis) {
			let html = '';
			emis = json.emis;

			json.emis.map(emi => {
				emi.amount > 0 && (html += `<option value="${emi.key}" data-amount="${emi.amount}">${emi.key}</option>`);
			});

			$('#emi_type').html(html);
			$('#emi_type').select2();

			$('#emi_type').val(emi_type).trigger('change');

			$('#emi_type').on('change', function() {
				$('#amount').val($(this).find('option:selected').data('amount'));
			});
		} else {
			error_notify(json.error)
		}
	});
}
const getCenters = (city_id) => {
	let fd = new FormData();
	fd.append('city_id', city_id);

	submitForm('<?php echo site_url('api/centers'); ?>', fd, json => {
		if (json.centers) {
			let result = '<option value=""><?php _el('select_center'); ?></option>';

			$.each(json.centers, function(k, v) {
				result += '<option value="' + v.center_id + '">'+ v.name +'</option>'
			});

			$('#center_id').html(result);
		} else {
			error_notify(json.error)
		}
	});
}

const getClasses = (course_id, mode, center_id) => {
	let fd = new FormData();
	fd.append('course_id', course_id);
	fd.append('mode', mode);
	fd.append('center_id', center_id);
	fd.append('is_demo', 0);

	submitForm('<?php echo site_url('admin/get_classes'); ?>', fd, json => {
		if (json.classes) {
			let result = '<option value=""><?php _el('select_class'); ?></option>';

			$.each(json.classes, function(k, v) {
				result += '<option value="' + v.id + '">'+ v.name +'</option>'
			});

			$('#class_id').html(result);
		} else {
			error_notify(json.error)
		}
	});
}
</script>
<script>
$(function() {
	$('#payment_mode').select2();
});
</script>
