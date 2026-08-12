<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i>
					<?php echo _l('attendance'); ?>
				</h4>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-3">
					<?php echo _l('attendance'); ?>
				</h4>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-lg-12">
										<div
											id="calendar"
											data-event-url="<?php echo site_url('teacher/events'); ?>"
											data-schedule-url="<?php echo site_url('teacher/save_attendance'); ?>"
											data-select="false"
											data-action="renderStudent"
										></div>
									</div>
								</div>
							</div>
						</div>


						<div class="modal fade" id="event-modal" tabindex="-1">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title">
											<span class="change-title"></span>
											<?php echo _l('attendance'); ?>
										</h4>
										<button
											type="button"
											class="close"
											data-dismiss="modal"
											aria-hidden="true"
										>×</button>
									</div>
									<div class="modal-body p-3">
									</div>
									<div class="text-right p-3">
										<button
											type="button"
											class="btn btn-light save-class"
										>
											<?php echo _l('save'); ?>
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
</div>

<script>
function renderStudent(data) {
	$.post('<?php echo site_url('teacher/get_students'); ?>', data, function(json) {
		var html = '';

		html += `
		<div class="list-group-item active">
			<div class="row">
				<div class="col-sm-8">
					<h5><?php echo _l('student_name'); ?></h5>
				</div>
				<div class="col-sm-4">
					<h5><?php echo _l('mark_present'); ?></h5>
				</div>
			</div>
		</div>`;

		json.students && json.students.forEach(function(student) {
			let checked = student.present ? 'checked' : '';

			html += `
			<div class="list-group-item">
				<div class="row">
					<div class="col-sm-10">
						${student.name}
					</div>
					<div class="col-sm-2">
						<div class="custom-control custom-switch custom-switch-lg">
							<input
								type="checkbox"
								name="attendance[]"
								value="${student.student_id}"
								class="custom-control-input"
								id="input${student.student_id}" ${checked}
							/>
							<label class="custom-control-label" for="input${student.student_id}"></label>
						</div>
					</div>
				</div>
			</div>`;
		});

		$('#event-modal .modal-body').html(`
			<form method="post" action="" enctype="multipart/formdata">
				<div class="list-group list-group-flush">${html}</div>
			</form>
		`);
	});
}
</script>
