<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i>
					<?php echo _l('schedule'); ?>
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
					<?php echo _l('schedule_class'); ?>
				</h4>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form>
									<div class="row">
										<div class="col">
											<div class="form-group">
												<label for="mode"><?php _el('select_mode'); ?></label>
												<select class="form-control select2" data-toggle="select2" name="select_mode" id="select_mode">
													<option value="all"<?php echo 0 == $select_mode ? ' selected' : ''; ?>><?php _el('all'); ?></option>
													<option value="online"<?php echo 'online' == $select_mode ? ' selected' : ''; ?>><?php _el('online'); ?></option>
													<option value="offline"<?php echo 'offline' == $select_mode ? ' selected' : ''; ?>><?php _el('offline'); ?></option>
												</select>
											</div>
										</div>
										<div class="col">
											<div class="form-group">
												<label for="center_id"><?php _el('select_center'); ?></label>
												<select class="form-control select2" data-toggle="select2" name="center_id" id="center_id">
													<option value="all"<?php echo 0 == $center_id ? ' selected' : ''; ?>><?php _el('all'); ?></option>
													<?php foreach ($centers as $center) { ?>
													<option value="<?php echo $center['id']; ?>"<?php echo $center['id'] == $center_id ? ' selected' : ''; ?>><?php echo $center['name']; ?></option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="col">
											<div class="form-group">
												<label for="course_id"><?php _el('course'); ?></label>
												<select class="form-control select2" data-toggle="select2" name="course_id" id="course_id">
													<option value="all"<?php echo 0 == $course_id ? ' selected' : ''; ?>><?php _el('all'); ?></option>
													<?php foreach ($courses as $course) { ?>
													<option value="<?php echo $course['id']; ?>"<?php echo $course['id'] == $course_id ? ' selected' : ''; ?>><?php echo $course['title']; ?></option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="col">
											<label for=".." class="text-white">..</label>
											<button type="submit" class="btn btn-primary btn-block" id="button-filter"><?php _el('filter'); ?></button>
										</div>
									</div>
								</form>

								<div class="row mt-4">
									<div class="col-lg-3">
										<div id="external-events" class="m-t-20">
											<br>
											<p class="text-muted">
												<?php echo _l('drag_and_drop_your_class'); ?>
												<?php echo _l('or'); ?>
												<?php echo _l('click_in_the_calendar'); ?>
											</p>
											<input type="text" autocomplete="off" id="filter-class" placeholder="<?php _el('search_class'); ?>" class="form-control" />

											<?php foreach ($classes as $i => $class) { ?>
											<div
												class="external-event bg-<?php echo $class['color']; ?>"
												data-class="bg-<?php echo $class['color']; ?>"
												data-id="<?php echo $class['id']; ?>"
												style="display: <?php echo $i > 9 ? 'none' : 'block'; ?>;"
											>
												<i class="mdi mdi-checkbox-blank-circle mr-2 vertical-middle"></i>
												<?php echo $class['name']; ?><?php echo $class['is_demo'] ? ' <span class="badge badge-dark">' . _l('demo') . '</span>' : ''; ?>
											</div>
											<?php } ?>
										</div>

										<div class="custom-control custom-checkbox mt-3 d-none">
											<input type="checkbox" class="custom-control-input" id="drop-remove">
											<label class="custom-control-label" for="drop-remove">
												<?php echo _l('remove_after_drop'); ?>
											</label>
										</div>

									</div>

									<div class="col-lg-9">
										<div
											id="calendar"
											data-event-url="<?php echo $action_event; ?>"
											data-schedule-url="<?php echo site_url('admin/update_schedule'); ?>"
											data-type="month",
											data-action="renderSchedule",
										></div>
									</div>

								</div>
							</div>
						</div>

						<div class="modal fade" id="event-modal">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title"><?php echo _l('edit_class_schedule'); ?></h4>
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
									</div>
									<div class="modal-body p-3">
										<form action="<?php echo site_url('admin/update_schedule'); ?>" method="post">
											<input type="hidden" name="schedule" id="schedule" value="">

											<div class="form-group">
												<label for="course"><?php echo _l('course'); ?></label>
												<select
													class="form-control select2"
													data-toggle="select2"
													name="course_id"
													id="course"
												>
													<?php foreach ($courses as $course) { ?>
													<option value="<?php echo $course['id']; ?>">
														<?php echo $course['title']; ?>
													</option>
													<?php } ?>
												</select>
											</div>

											<div class="form-group">
												<label for="mode"><?php echo _l('mode'); ?></label>
												<select
													class="form-control select2"
													data-toggle="select2"
													name="mode"
													id="mode"
												>
													<option value="" selected><?php _el('select_mode'); ?></option>
													<option value="online"><?php _el('online'); ?></option>
													<option value="offline"><?php _el('offline'); ?></option>
												</select>
											</div>

											<div class="form-group">
												<label for="class"><?php echo _l('class'); ?></label>
												<select
													class="form-control select2"
													data-toggle="select2"
													name="class_ids[]"
													id="class"
													multiple
												>

												</select>
											</div>

											<div class="form-group">
												<label for="days"><?php echo _l('days'); ?></label>
												<select
													class="form-control select2"
													data-toggle="select2"
													name="days[]"
													id="days"
													multiple="multiple"
												>
													<?php $timestamp = strtotime('next Sunday'); ?>
													<?php for ($i = 0; $i < 7; $i++) { ?>
													<option value="<?php echo $i; ?>">
														<?php echo strftime('%A', $timestamp); ?>
														<?php $timestamp = strtotime('+1 day', $timestamp); ?>
													</option>
													<?php } ?>
												</select>
											</div>

											<div class="form-group">
												<label for="month"><?php echo _l('month'); ?></label>
												<select
													class="form-control select2"
													data-toggle="select2"
													name="month"
													id="month"
												>
													<?php for ($i = 1; $i < 13; $i++) { ?>
													<option value="<?php echo $i; ?>">
														<?php echo $i; ?>
													</option>
													<?php } ?>
												</select>
											</div>

										</form>

										<div class="text-right pt-2">
											<button
												type="button"
												class="btn btn-light"
												data-dismiss="modal"
											>
												<?php echo _l('close'); ?>
											</button>
											<button
												type="button"
												class="btn btn-primary ml-1 save-class"
											>
												<?php echo _l('save'); ?>
											</button>
											<button
												type="button"
												class="btn btn-danger delete-class"
											>
												<?php echo _l('delete'); ?>
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
</div>

<script>
<?php if ($lead_id) { ?>
$(function() {
	setTimeout(() => {
		hopscotch.startTour({
			id:"calendar-intro",
			steps:[
				{
					target: '.fc-event-container:first-child',
					title: '<?php _el('Pick the slot'); ?>',
					content: '<span class="badge badge-info color-swatch"></span> <?php _el('Select slot and click on it, to assign'); ?>.',
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
<?php } ?>

const renderSchedule = (data) => {
	<?php if ($lead_id) { ?>
	$.post('<?php echo site_url('admin/schedule_detail'); ?>', data, json => {
		const schedule = json.schedule;
		const html = `
			<div class="text-center"><i class="fa fa-check-circle fa-6x text-success mb-3"></i></div>
			<div class="table-responsive">
				<table class="table table-striped">
					<tbody>
						<tr>
							<th><?php _el('program_choice'); ?></th>
							<td>${schedule.course}</td>
						</tr>
						<tr>
							<th><?php _el('instructor'); ?></th>
							<td>${schedule.teacher}</td>
						</tr>
						<tr>
							<th><?php _el('schedule'); ?></th>
							<td>${schedule.schedule}</td>
						</tr>
					</tbody>
				</table>
			</div>
		`;

		$('#schedule-info').html(html);
	});
	<?php } ?>
}

$('#filter-class').on('keyup', function() {
	var v = $(this).val().trim();

	if (v) {
		$('#external-events .external-event').each(function() {
			if ($(this).text().trim().match(new RegExp(`${v}\w*`, 'ig'))) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
	}
});


$(function() {
	$('#course').select2();

	$('#course').on('select2:select', function(e) {
		refershClass();
	});
	$('#mode').on('select2:select', function(e) {
		refershClass();
	});

	$('#select_mode').on('select2:select', function(e) {
		if (e.params.data.id != 'online') {
			$('#center_id').parent('div').show();
		} else {
			$('#center_id').parent('div').hide();
		}
	});

	$('#select_mode').trigger('change');
});

const refershClass = () => {
	let fd = new FormData();
	fd.append('course_id', $('#course').val());
	fd.append('mode', $('#mode').val());
	$("#class").html('');

	submitForm('<?php echo site_url('admin/get_filtered_class'); ?>', fd, json => {
		if (json.classes) {
			var data = [];

			json.classes.map(item => {
				data.push({
					id: item.id,
					text: item.name
				})
			});

			$("#class").select2({
				data: data
			});
		} else {
			error_notify(json.error)
		}
	});
}
</script>
