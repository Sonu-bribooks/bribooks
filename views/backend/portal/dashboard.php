<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('dashboard'); ?></h4>
				<div id="clocker"></div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">

				<h4 class="header-title mb-4"><?php echo _l('total_leads'); ?></h4>

				<div class="mt-3 chartjs-chart" style="height: 320px;">
					<canvas id="lead-area-chart"></canvas>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-12">
		<div class="card widget-inline">
			<div class="card-body p-0">
				<div class="row no-gutters">
					<div class="col-sm-6 col-xl-3">
						<!-- <a href="<?php echo site_url('portal/courses'); ?>" class="text-secondary"> -->
							<div class="card shadow-none m-0">
								<div class="card-body text-center">
									<i class="dripicons-archive text-muted" style="font-size: 24px;"></i>
									<h3><span><?php echo @$total_leads; ?></span></h3>
									<p class="text-muted font-15 mb-0"><?php echo _l('total_leads'); ?></p>
								</div>
							</div>
						<!-- </a> -->
					</div>
					<div class="col-sm-6 col-xl-3">
						<!-- <a href="<?php echo site_url('portal/enrol_history'); ?>" class="text-secondary"> -->
							<div class="card shadow-none m-0 border-left">
								<div class="card-body text-center">
									<i class="dripicons-network-3 text-muted" style="font-size: 24px;"></i>
									<h3><span><?php echo @$registered_students; ?></span></h3>
									<p class="text-muted font-15 mb-0"><?php echo _l('registered_students'); ?></p>
								</div>
							</div>
						<!-- </a> -->
					</div>


					<div class="col-sm-6 col-xl-3">
						<!-- <a href="<?php echo site_url('portal/courses'); ?>" class="text-secondary"> -->
							<div class="card shadow-none m-0 border-left">
								<div class="card-body text-center">
									<i class="dripicons-camcorder text-muted" style="font-size: 24px;"></i>
									<h3><span><?php echo $book_written; ?></span></h3>
									<p class="text-muted font-15 mb-0"><?php echo _l('book_written'); ?></p>
								</div>
							</div>
						<!-- </a> -->
					</div>
					<div class="col-sm-6 col-xl-3">
						<!-- <a href="<?php echo site_url('portal/users'); ?>" class="text-secondary"> -->
							<div class="card shadow-none m-0 border-left">
								<div class="card-body text-center">
									<i class="dripicons-user-group text-muted" style="font-size: 24px;"></i>
									<h3><span><?php echo $book_published; ?></span></h3>
									<p class="text-muted font-15 mb-0"><?php echo _l('book_published'); ?></p>
								</div>
							</div>
						<!-- </a> -->
					</div>

				</div> <!-- end row -->
			</div>
		</div> <!-- end card-box-->
	</div> <!-- end col-->
</div>
<div class="row">
	<div class="col-xl-4">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-4"><?php echo _l('student_overview'); ?></h4>
				<div class="my-4 chartjs-chart" style="height: 202px;">
					<canvas id="lead-status-chart"></canvas>
				</div>
				<div class="row text-center mt-2 py-2">
					<div class="col-6">
						<i class="mdi mdi-trending-up text-default mt-3 h3"></i>
						<h3 class="font-weight-normal">
							<span><?php echo @$total_leads; ?></span>
						</h3>
						<p class="text-muted mb-0"><?php echo _l('total_leads'); ?></p>
					</div>
					<div class="col-6">
						<i class="mdi mdi-trending-down text-warning mt-3 h3"></i>
						<h3 class="font-weight-normal">
							<span><?php echo $registered_students; ?></span>
						</h3>
						<p class="text-muted mb-0"> <?php echo _l('registered_students'); ?></p>
					</div>
				</div>
				<div class="row text-center mt-2 py-2">
					<div class="col-6">
						<i class="mdi mdi-trending-up text-success mt-3 h3"></i>
						<h3 class="font-weight-normal">
							<span><?php echo @$book_written; ?></span>
						</h3>
						<p class="text-muted mb-0"><?php echo _l('book_written'); ?></p>
					</div>
					<div class="col-6">
						<i class="mdi mdi-trending-down text-danger mt-3 h3"></i>
						<h3 class="font-weight-normal">
							<span><?php echo $book_published; ?></span>
						</h3>
						<p class="text-muted mb-0"> <?php echo _l('book_published'); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-8">
		<div class="card" id = 'unpaid-instructor-revenue'>
			<div class="card-body">
				<?php if (0) { ?>
				<h4 class="header-title mb-3">
					<?php _el('today_schedules'); ?>
				</h4>
				<div class="table-responsive">
					<table class="table table-centered table-hover mb-0">
						<thead>
							<th>#</th>
							<th><?php echo _li('Schedule'); ?></th>
							<th><?php echo _li('Course'); ?></th>
							<th><?php echo _li('Teacher'); ?></th>
							<th><?php echo _li('Students'); ?></th>
						</thead>
						<tbody>

							<?php
								foreach ($schedules as $schedule) {
									$demo_students = $this->lead_model->getDemoStudents($schedule['id']);
									$enrolled_students = $this->class_model->get_all_students($schedule['class_id']);

									$students = array_merge($demo_students, $enrolled_students);

									/*if (count($students) === 0) {
										continue;
									}*/
							?>
							<tr>
								<td>
									<?php echo $schedule['id']; ?>
								</td>
								<td>
									<?php echo $schedule['schedule']; ?><br />
									<?php echo $schedule['mode']; ?>
									<?php echo $schedule['is_demo'] ? '<span class="badge badge-info">' . _l('demo') . '<span>' : ''; ?>
								</td>
								<td>
									<?php echo $schedule['course']; ?>
								</td>
								<td>
									<?php echo $schedule['name']; ?><br>
									<small><?php echo $schedule['email']; ?></small>
									<?php if($teacher_info = $this->teacher_model->get($this->schedule_model->getReassignedSchedules(['schedule_id' => $schedule['id'], 'single_row' => true])['teacher_id'] ?? 0)->row()) { ?>
									<br><b><?php _el('reassigned_to'); ?>---></b>
									<span class="text-danger">
										<?php echo $teacher_info->first_name . ' ' . $teacher_info->last_name; ?><br>
										<small><?php echo $teacher_info->email; ?></small>
									</span>
									<?php } ?>
									<button
										data-id="<?php echo $schedule['id']; ?>"
										class="btn btn-sm btn-primary btn-reassign"
										data-toggle="modal"
										data-target="#reassign-modal"
										onclick="getBackupTeachers(<?php echo $schedule['id']; ?>, <?php echo $schedule['course_id']; ?>, <?php echo $schedule['user_id']; ?>, {course: '<?php echo $schedule['course']; ?>', schedule: '<?php echo $schedule['id']; ?>', teacher: '<?php echo $schedule['name']; ?>'})"
									><?php _el('reassign_teacher'); ?></button>
								</td>
								<td>
									<?php foreach ($students as $student_id) { ?>
									<div style="display: block; padding:5px; margin: 2px 0; border: 1px solid #eee;border-radius: 10px;">
									<?php
										$student_info = $this->student_model->get($student_id)->row_array();
										echo $student_info['first_name'] . ' ' . $student_info['last_name'] . '<br>';
									?>
									<small><?php echo $student_info['mobile']; ?></small>
									</div>
									<?php } ?>
								</td>
							</tr>
							<?php } ?>

						</tbody>
					</table>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$('#unpaid-instructor-revenue').mouseenter(function() {
		$('#go-to-instructor-revenue').show();
	});
	$('#unpaid-instructor-revenue').mouseleave(function() {
		$('#go-to-instructor-revenue').hide();
	});
</script>

<div class="modal fade" id="reassign-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php _el('reassign_teacher'); ?></h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>

			<div class="modal-body p-3">
				<form action="<?php echo site_url('portal/updateReassign'); ?>" method="post" id="form-status">
					<input type="hidden" name="schedule_id" value="" />
					<input type="hidden" name="original_teacher_id" value="" />

					<div class="table-responsive-sm">
						<table class="table table-striped table-centered">
							<tbody>
							</tbody>
						</table>
					</div>
				</form>

				<div class="text-right pt-2">
					<button
						type="button"
						class="btn btn-light"
						data-dismiss="modal"
					><?php _el('close'); ?>
					</button>
					<button
						onclick="setTimeout(function(){ $('#status-modal').modal('hide'); }, 2000)"
						type="submit"
						form="form-status"
						class="btn btn-primary ml-1 save"
					><?php _el('assign'); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
$('#form-status').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	submitForm($el.attr('action'), new FormData($el[0]), json => {
		if (json.success) {
			success_notify(json.success);
			setTimeout(() => window.location.reload(), 1000);
		} else {
			error_notify(json.error)
		}
	});
});

const getBackupTeachers = (schedule_id, course_id, teacher_id, extra) => {
	$('input[name=original_teacher_id]').val(teacher_id);
	$('input[name=schedule_id]').val(schedule_id);

	let fd = new FormData();
	fd.append('schedule_id', schedule_id);
	fd.append('course_id', course_id);
	fd.append('teacher_id', teacher_id);

	submitForm('<?php echo site_url('portal/getTeachers'); ?>', fd, json => {
		let html = '',
		options = '';

		if(json.teachers) {
			json.teachers.map(teacher => {
				options += `<option value="${teacher.id}">${teacher.first_name} ${teacher.last_name}</option>`;
			});
		}

		html += `
		<tr>
			<th><?php _el('course'); ?></th>
			<td>${extra.course}</td>
		</tr>`;

		html += `
		<tr>
			<th><?php _el('schedule'); ?></th>
			<td>${extra.schedule}</td>
		</tr>`;

		html += `
		<tr>
			<th><?php _el('existing_teacher'); ?></th>
			<td>${extra.teacher}</td>
		</tr>`;

		html += `
		<tr>
			<th><?php _el('new_teacher'); ?></th>
			<td>
				<select
					class="form-control select2"
					data-toggle="select2"
					name="teacher_id"
				>${options}</select>
			</td>
		</tr>`;

		$('#reassign-modal tbody').html(html);

		$('.select2').select2();
	});
};
</script>
<?php if (0) { ?>
<script>
$(function() {
	$('#clocker').clock({
		now: '<?=date('Y-m-d H:i:s')?>',
	});
});
</script>
<?php } ?>
