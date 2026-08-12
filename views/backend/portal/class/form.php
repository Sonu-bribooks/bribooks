<!-- start page title -->
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('add_new_class'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
			  <div class="col-lg-12">
				<h4 class="mb-3 header-title"><?php echo _l('class_add_form'); ?></h4>

				<form class="required-form" action="<?php echo $action ; ?>" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required>
					</div>

					<div class="form-group">
						<label><?php _el('site'); ?><span class="required">*</span></label>
						<select class="form-control select2" data-toggle="select2" name="site_id" id="site_id">
							<?php foreach ($sites as $site) {
								if (($details['site_id'] ?? '') == $site['id']) {
							?>
							<option value="<?php echo $site['id']; ?>" selected><?php echo $site['name']; ?></option>
							<?php } else { ?>
							<option value="<?php echo $site['id']; ?>"><?php echo $site['name']; ?></option>
							<?php } } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="mode"><?php echo _l('mode'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="mode" id="mode" onchange="$(this).val() == 'offline' ? $('.center-div').show() : $('#center-div').hide();">
							<?php if (($details['mode'] ?? '') == 'offline') { ?>
							<option value="online"><?php echo _l('online'); ?></option>
							<option value="offline" selected><?php echo _l('offline'); ?></option>
							<?php } else { ?>
							<option value="online" selected><?php echo _l('online'); ?></option>
							<option value="offline"><?php echo _l('offline'); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group center-div" style="display: <?php echo ($details['mode'] ?? '') == 'offline' ? 'block' : 'none'; ?>">
						<label for="city"><?php echo _l('city'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="city_id" id="city" onchange="getCenter(this)">
							<?php foreach ($cities as $city) { ?>
							<?php if (($details['city_id'] ?? '') == $city['id']) { ?>
							<option value="<?php echo $city['id']; ?>" selected><?php echo $city['name']; ?></option>
							<?php } else { ?>
							<option value="<?php echo $city['id']; ?>"><?php echo $city['name']; ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group center-div" style="display: <?php echo ($details['mode'] ?? '') == 'offline' ? 'block' : 'none'; ?>">
						<label for="center"><?php echo _l('center'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="center_id" id="center">
							<option value=""><?php _el('select_center'); ?></option>
						</select>
					</div>

					<div class="form-group">
						<label for="slot"><?php echo _l('slot'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="slot_id" id="slot">
							<?php foreach ($slots as $slot) { ?>
							<?php if (($details['slot_id'] ?? '') == $slot['id']) { ?>
							<option value="<?php echo $slot['id']; ?>" selected><?php echo $slot['slot_start']; ?></option>
							<?php } else { ?>
							<option value="<?php echo $slot['id']; ?>"><?php echo $slot['slot_start']; ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="course"><?php echo _l('course'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="course_id" id="course">
							<?php foreach ($courses as $course) { ?>
							<?php if (($details['course_id'] ?? '') == $course['id']) { ?>
							<option value="<?php echo $course['id']; ?>" selected><?php echo $course['title']; ?></option>
							<?php } else { ?>
							<option value="<?php echo $course['id']; ?>"><?php echo $course['title']; ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="teacher"><?php echo _l('teacher'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="teacher_id" id="teacher">
							<?php foreach ($teachers as $teacher) { ?>
							<?php if (($details['teacher_id'] ?? '') == $teacher['id']) { ?>
							<option value="<?php echo $teacher['id']; ?>" selected><?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?></option>
							<?php } else { ?>
							<option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="backup_teacher"><?php echo _l('backup_teachers'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="backup_teacher_id[]" id="backup_teacher"  multiple="multiple">
							<?php foreach ($teachers as $teacher) { ?>
							<?php if (in_array($teacher['id'], ($details['backup_teacher_id'] ?? []))) { ?>
							<option value="<?php echo $teacher['id']; ?>" selected><?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?></option>
							<?php } else { ?>
							<option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="student"><?php echo _l('students'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="student_id[]" id="student"  multiple="multiple">
							<?php foreach ($students as $student) { ?>
							<?php if (in_array($student['id'], ($details['student_id'] ?? []))) { ?>
							<option value="<?php echo $student['id']; ?>" selected><?php echo $student['user'] . ' ' . $student['course']  . '-' . $student['mode']; ?></option>
							<?php } else { ?>
							<option value="<?php echo $student['id']; ?>"><?php echo $student['user'] . ' ' . $student['course'] . '-' . $student['mode']; ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="is-demo"><?php echo _l('is_demo'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="is_demo" id="is-demo">
							<?php if (($details['is_demo'] ?? '') == 1) { ?>
							<option value="0"><?php echo _l('no'); ?></option>
							<option value="1" selected><?php echo _l('yes'); ?></option>
							<?php } else { ?>
							<option value="0" selected><?php echo _l('no'); ?></option>
							<option value="1"><?php echo _l('yes'); ?></option>
							<?php } ?>
						</select>
					</div>

					<?php $colors = ['primary', 'success', 'danger', 'info', 'warning', 'dark']; ?>

					<div class="form-group">
						<label for="color"><?php echo _l('color'); ?><span class="required">*</span></label>
						<select
							class="form-control select2"
							data-toggle="select2"
							name="color"
						>
							<?php foreach ($colors as $color_i) { ?>
							<?php if (($details['color'] ?? '') == $color_i) { ?>
							<option value="<?php echo $color_i; ?>" selected><?php echo _l($color_i); ?></option>
							<?php } else { ?>
							<option value="<?php echo $color_i; ?>"><?php echo _l($color_i); ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<script>
const getCenter = (c) => {
	let fd = new FormData();
	fd.append('city_id', c.value);

	let center_id = <?php echo $details['center_id'] ?? 0; ?>

	submitForm('<?php echo site_url('api/centers'); ?>', fd, json => {
		if (json.centers) {
			let result = '<option value=""><?php _el('select_center'); ?></option>';

			$.each(json.centers, function(k, v) {
				if (center_id == v.center_id ) {
					result += '<option value="' + v.center_id + '" selected>'+ v.name +'</option>';
				} else {
					result += '<option value="' + v.center_id + '">'+ v.name +'</option>';
				}
			});

			$('#center').html(result);
		} else {
			error_notify(json.error)
		}
	});
}
$(function() {
	$('#city').trigger('change');
});
</script>
