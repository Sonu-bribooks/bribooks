<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				</h4>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php _el('request_reassign'); ?></h4>
				<div class="table-responsive mt-4">
					<table id="basic-datatable" class="table table-striped table-centered mb-0">
						<thead>
						<tr>
							<th>#</th>
							<th><?php _el('course'); ?></th>
							<th><?php _el('schedule'); ?></th>
							<th><?php _el('teacher'); ?></th>
							<th><?php _el('reason'); ?></th>
							<th><?php _el('date_added'); ?></th>
							<th><?php _el('actions'); ?></th>
						</tr>
						</thead>
						<tbody>
							<?php
								foreach ($requests as $key => $request): ?>
								<tr>
									<td><?php echo $key+1; ?></td>
									<td><?php echo $request['course']; ?></td>
									<td><?php echo $request['schedule']; ?></td>
									<td><?php echo $request['name']; ?></td>
									<td><?php echo $request['comment']; ?></td>
									<td><?php echo $request['date_added']; ?></td>
									<td>
										<div class="dropright dropright">
										<button
											type="button"
											class="btn btn-sm btn-outline-primary btn-rounded btn-icon"
											data-toggle="dropdown"
											aria-haspopup="true"
											aria-expanded="false"
										>
											<i class="mdi mdi-dots-vertical"></i>
										</button>

										<ul class="dropdown-menu">
											<li>
												<a class="dropdown-item"
													data-toggle="modal"
													data-target="#status-modal"
													onclick="$('input[name=request_id]').val(<?php echo $request['id']; ?>); $('input[name=original_teacher_id]').val(<?php echo $request['teacher_id']; ?>); getBackupTeachers(<?php echo $request['schedule_id']; ?>, <?php echo $request['course_id']; ?>, <?php echo $request['teacher_id']; ?>, {course: '<?php echo $request['course']; ?>', schedule: '<?php echo $request['schedule']; ?>', teacher: '<?php echo $request['name']; ?>'})"
												><?php _el('reassign'); ?></a>
											</li>
										</ul>
									</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="modal fade" id="status-modal" tabindex="-1">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title"><?php _el('assign_teacher'); ?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							</div>

							<div class="modal-body p-3">
								<form action="<?php echo site_url('admin/update_reassign'); ?>" method="post" id="form-status">
									<input type="hidden" name="request_id" value="" />
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
			// getLeadStatus($el.find('input[name="request_id"]').val());
		} else {
			error_notify(json.error)
		}
	});
});

const getBackupTeachers = (schedule_id, course_id, teacher_id, extra) => {
	let fd = new FormData();
	fd.append('schedule_id', schedule_id);
	fd.append('course_id', course_id);
	fd.append('teacher_id', teacher_id);

	submitForm('<?php echo site_url('admin/get_teachers'); ?>', fd, json => {
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

		$('#status-modal tbody').html(html);

		$('.select2').select2();
	});
};

const showDetails = (id) => {
	let fd = new FormData();
	fd.append('request_id', id);

	submitForm('<?php echo site_url('admin/request_detail'); ?>', fd, json => {
		$('#details-modal').modal('hide');
		$('#details-modal').remove();

		const request = json.request;
		const center = request.mode == 'offline' ?
			`<tr>
				<th><?php _el('center'); ?></th>
				<td>${request.center}</td>
			</tr>` : '';

		const html = `<div class="modal fade" id="details-modal" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">${request.course} - ${request.name}</h4>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>

					<div class="modal-body p-3">
						<div class="table-responsive-sm">
							<table class="table table-striped table-centered">
								<tbody>
									<tr>
										<th><?php _el('status'); ?></th>
										<td>${request.status}</td>
									</tr>
									<tr>
										<th><?php _el('program_choice'); ?></th>
										<td>${request.course}</td>
									</tr>
									<tr>
										<th><?php _el('learning_mode'); ?></th>
										<td>${request.mode}</td>
									</tr>
									${center}
									<tr>
										<th><?php _el('requested_schedule'); ?></th>
										<td>${request.schedule}</td>
									</tr>
									<tr>
										<th><?php _el('confirmed_schedule'); ?></th>
										<td>${request.confirmed_schedule}</td>
									</tr>
									<tr>
										<th><?php _el('student_name'); ?></th>
										<td>${request.name}</td>
									</tr>
									<tr>
										<th><?php _el('student_age'); ?></th>
										<td>${request.age}<?php _el('years'); ?></td>
									</tr>
									<tr>
										<th><?php _el('parent_name'); ?></th>
										<td>${request.parent_name}</td>
									</tr>
									<tr>
										<th><?php _el('mobile'); ?></th>
										<td>${request.mobile}</td>
									</tr>
									<tr>
										<th><?php _el('email'); ?></th>
										<td>${request.email}</td>
									</tr>
									<tr>
										<th><?php _el('utm_source'); ?></th>
										<td>${request.utm_source}</td>
									</tr>
									<tr>
										<th><?php _el('utm_medium'); ?></th>
										<td>${request.utm_medium}</td>
									</tr>
									<tr>
										<th><?php _el('utm_campaign'); ?></th>
										<td>${request.utm_campaign}</td>
									</tr>
									<tr>
										<th><?php _el('date_added'); ?></th>
										<td>${request.date_added}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>`;

		$('body').append(html);
		$('#details-modal').modal('show');
	});
}

const sendPaymentLink = (id) => {
	let fd = new FormData();
	fd.append('request_id', id);

	submitForm('<?php echo site_url('admin/send_payment_link'); ?>', fd, json => {
		if (json.success) {
			success_notify(json.success);
		} else {
			error_notify(json.error)
		}
	});
}

$('#form-email').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	submitForm($el.attr('action'), new FormData($el[0]), json => {
		if (json.success) {
			success_notify(json.success);
		} else {
			error_notify(json.error)
		}
	});
});
</script>
