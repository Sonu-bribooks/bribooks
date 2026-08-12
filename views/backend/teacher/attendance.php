<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i>
					<?php _el('class'); ?>
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
					<?php _el('class'); ?>
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
											data-schedule-url="<?php echo site_url('teacher/saveAttendance'); ?>"
											data-select="false"
											data-action="renderStudent"
										></div>
									</div>
								</div>
							</div>
						</div>

						<div class="modal fade" id="event-modal">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title">
											<span class="change-title"></span>
										</h4>
										<button
											type="button"
											class="close"
											data-dismiss="modal"
											aria-hidden="true"
										>×</button>
									</div>

									<div class="modal-body p-3">

										<div id="accordion">
											<div class="card mb-0">
												<div class="card-header bg-primary" id="attendance">
													<h5 class="mb-0">
														<button
															class="btn btn-link text-white collapsed"
															data-toggle="collapse"
															data-target="#collapse-attendance"
															aria-expanded="true"
															aria-controls="collapse-attendance"
														>
															<i class="far fa-calendar-check"></i> <?php _el('attendance'); ?>
														</button>
													</h5>
												</div>

												<div id="collapse-attendance" class="collapse show" aria-labelledby="attendance" data-parent="#accordion">
													<div class="card-body">
														<form method="post" action="<?php echo site_url('teacher/saveAttendance'); ?>" enctype="multipart/formdata">

														</form>
													</div>
												</div>
											</div>

											<div class="card mb-0">
												<div class="card-header bg-warning" id="reassign">
													<h5 class="mb-0">
														<button
															class="btn btn-link text-white collapsed"
															data-toggle="collapse"
															data-target="#collapse-reassign"
															aria-expanded="false"
															aria-controls="collapse-reassign"
														>
															<i class="fa fa-paper-plane"></i> <?php _el('request_reassign_class'); ?>
														</button>
													</h5>
												</div>

												<div id="collapse-reassign" class="collapse" aria-labelledby="reassign" data-parent="#accordion">
													<div class="card-body">
														<form action="<?php echo site_url('teacher/reassign'); ?>" method="post">
															<input type="hidden" name="schedule" id="schedule" value="">
															<input type="hidden" name="id" value="">
															<input type="hidden" name="class_id" value="">

															<div class="form-group">
																<label for="comment"><?php _el('reason'); ?></label>
																<textarea
																	name="comment"
																	id="comment"
																	placeholder="<?php _el('reason'); ?>"
																	rows="6"
																	class="form-control"
																></textarea>
															</div>

															<div class="p-3 mt-10">
																<button
																	type="submit"
																	class="btn btn-light save-class btn-block"
																>
																	<?php _el('save'); ?>
																</button>
															</div>
														</form>
													</div>
												</div>
											</div>

											<div class="card mb-0">
												<div class="card-header bg-info" id="payment">
													<h5 class="mb-0">
														<button
															class="btn btn-link text-white collapsed"
															data-toggle="collapse"
															data-target="#payment-collection"
															aria-expanded="false"
															aria-controls="payment-collection"
														>
															<i class="far fa-money-bill-alt"></i> <?php _el('payment_collection'); ?>
														</button>
													</h5>
												</div>
												<div id="payment-collection" class="collapse" aria-labelledby="payment" data-parent="#accordion">
													<div class="card-body">
														<form method="post" action="<?php echo site_url('teacher/save_payment'); ?>" enctype="multipart/formdata">

														</form>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="text-right p-3">
										<button
											type="button"
											class="btn btn-light save-class d-none"
										>
											<?php _el('save'); ?>
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
	$('input[name="id"]').val(data.id);
	$('input[name="class_id"]').val(data.class_id);

	$.post('<?php echo site_url('teacher/getStudents'); ?>', data, function(json) {
		if (json.students && json.students.length > 0) {
			var html = '';

			html += `
			<div class="list-group-item active">
				<div class="row">
					<div class="col-sm-8">
						<h5><?php _el('student_name'); ?></h5>
					</div>
					<div class="col-sm-4">
						<h5><?php _el('mark_present'); ?></h5>
					</div>
				</div>
			</div>`;

			json.students && json.students.forEach(function(student) {
				let checked = student.present ? 'checked' : '';
				let demo = student.demo ? '<span class="badge badge-info"><?php _el('demo'); ?></span>' : '';
				let link = student.link ? '<a class="btn btn-sm btn-primary" href="' + student.link + '" target="_blank"><?php _el('attend_live_class'); ?></a>' : '';

				html += `
				<div class="list-group-item">
					<div class="row">
						<div class="col-sm-10">
							${student.name} ${demo} ${link}
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

			$('#event-modal .modal-body #collapse-attendance .card-body>form').html(`
				<div class="alert alert-info"><i class="fa fa-exclamation-triangle"></i> <?php echo _li('You can mark attendance during classs'); ?></div>
				<input type="hidden" name="id" value="${data.id}">
				<input type="hidden" name="class_id" value="${data.class_id}">
				<div class="list-group list-group-flush">${html}</div>
				<div class="p-3 mt-10">
					<button
						type="submit"
						class="btn btn-light save-class btn-block"
					>
						<?php _el('save'); ?>
					</button>
				</div>
			`);
		} else {
			$('#event-modal .modal-body #collapse-attendance .card-body>form').html('<div class="text-center"><span class="badge badge-danger"><?php _el('no_students'); ?></span></div>');
		}
	});

	$.post('<?php echo site_url('teacher/pendingPayments'); ?>', data, function(json) {
		if (json.students && json.students.length > 0) {
			var html = '';

			html += `
			<div class="list-group-item active">
				<div class="row">
					<div class="col-sm-6">
						<h5><?php _el('student_name'); ?></h5>
					</div>
					<div class="col-sm-3">
						<h5><?php _el('amount'); ?></h5>
					</div>
					<div class="col-sm-3">
						<h5><?php _el('mark_collected'); ?></h5>
					</div>
				</div>
			</div>`;

			json.students && json.students.forEach(function(student) {
				let checked = student.present ? 'checked' : '';

				html += `
				<div class="list-group-item">
					<div class="row">
						<div class="col-sm-7">
							${student.name}
						</div>
						<div class="col-sm-3">
							${student.amount}
						</div>
						<div class="col-sm-2">
							<div class="custom-control custom-switch custom-switch-lg">
								<input
									type="checkbox"
									name="payment[]"
									value="${student.enrol_id}"
									class="custom-control-input"
									id="inputpayment${student.enrol_id}" ${checked}
								/>
								<label class="custom-control-label" for="inputpayment${student.enrol_id}"></label>
							</div>
						</div>
					</div>
				</div>`;
			});

			$('#event-modal .modal-body #payment-collection .card-body>form').html(`
				<input type="hidden" name="id" value="${data.id}">
				<input type="hidden" name="class_id" value="${data.class_id}">
				<div class="list-group list-group-flush">${html}</div>
				<div class="p-3 mt-10">
					<button
						type="submit"
						class="btn btn-light save-class btn-block"
					>
						<?php _el('save'); ?>
					</button>
				</div>
			`);
		} else {
			$('#event-modal .modal-body #payment-collection .card-body>form').html('<div class="text-center"><span class="badge badge-danger"><?php _el('no_students'); ?></span></div>');
		}
	});
}

$('form').on('submit', function(e) {
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
<script>
$(function() {
	setTimeout(() => {
		hopscotch.startTour({
			id:"calendar-intro",
			steps:[
				{
					target: '.fc-event-container:first-child',
					title: '<?php _el('Pick the slot'); ?>',
					content: '<span class="badge badge-info color-swatch"></span> <?php _el('Select class and click on it, to access these features attendance, request reassign, payment collection'); ?>.',
					placement: 'top',
					yOffset: 0,
					xOffset: 10,
					zindex: 999
				}
			],
			showPrevButton:!0,
			showNextButton:!0,
		})
	}, 2000)
});
</script>

<?php if ($schedule_id && $class_id) { ?>
<script>
setTimeout(() => ($('#event-modal .change-title').text('<?php echo $title; ?>'), $('#event-modal').modal('show')), 1000);

renderStudent({
	id: <?php echo $schedule_id; ?>,
	class_id: <?php echo $class_id; ?>,
});
</script>
<?php } ?>
