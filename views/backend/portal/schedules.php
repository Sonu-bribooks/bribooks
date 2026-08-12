<?php if ($lead_info) { ?>
<style>
.fc-day[data-date="<?php echo date('Y-m-d', strtotime($lead_info['schedule'])); ?>"] {
	background-color: #9C27B0 !important;
}
</style>
<?php } ?>
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

				<?php if ($lead_info) { ?>
				<div class="card">
					<div class="card-body">
						<h4><?php echo _el('lead_details'); ?></h4>
						<div class="bg-white text-dark">
							<div class="row">
								<div class="col">
									<div class="row"><b class="col"><?php echo _el('student_name'); ?></b> <span class="col"><?php echo $lead_info['name']; ?></span></div>
									<div class="row"><b class="col"><?php echo _el('parent_name'); ?></b> <span class="col"><?php echo $lead_info['parent_name']; ?></span></div>
									<div class="row"><b class="col"><?php echo _el('mobile'); ?></b> <span class="col"><?php echo $lead_info['mobile']; ?></span></div>
								</div>

								<div class="col">
									<div class="row"><b class="col"><?php echo _el('program'); ?></b> <span class="col"><?php echo $lead_info['course']; ?></span></div>
									<div class="row"><b class="col"><?php echo _el('mode'); ?></b> <span class="col"><?php echo $lead_info['mode']; ?></span></div>
									<?php if ($lead_info['mode'] == 'offline') { ?>
									<div class="row"><b class="col"><?php echo _el('center'); ?></b> <span class="col"><?php echo $lead_info['center']; ?></span></div>
									<?php } ?>
									<div class="row"><b class="col"><?php echo _el('requested_schedule'); ?></b> <span class="col"><?php echo $lead_info['schedule']; ?></span></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>

				<div class="row">
					<div class="col-12">

						<div class="card">
							<div class="card-body">
								<div class="row">
									<div class="col-lg-12">
										<ul class="list-inline">
											<li>
												<span class="badge badge-info color-swatch"></span>
												<?php _el('free_slot'); ?>
											</li>
											<li>
												<span class="badge badge-success color-swatch" style="background-color: #ff8a70;"></span>
												<?php _el('occupied_slot'); ?>
											</li>
											<?php if ($lead_id) { ?>
											<li>
												<span class="badge badge-info color-swatch" style="background-color: #9C27B0;"></span>
												<?php _el('request_day'); ?>
											</li>
											<li>
												<span class="badge badge-success color-swatch" style="background-color: green;"></span>
												<?php _el('assigned_slot'); ?>
											</li>
											<?php } ?>
										</ul>

										<div
											id="calendar"
											data-event-url="<?php echo $action_event; ?>"
											data-schedule-url="<?php echo $action_schedule; ?>"
											<?php echo $lead_id ? 'data-select="false"' : ''; ?>
											<?php echo $class_id ? 'data-event-form="false"' : ''; ?>
											data-type="month"
											data-action="renderSchedule"
										></div>
									</div>

								</div>
							</div>
						</div>

						<?php if ($lead_id) { ?>
						<div class="modal fade" id="event-modal" tabindex="-1">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title"><?php echo _l('assign_classs'); ?></h4>
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
									</div>
									<div class="modal-body p-3">
										<div id="schedule-info"></div>
										<form action="<?php echo site_url('portal/add'); ?>" method="post">
											<input type="hidden" name="schedule" id="schedule" value="" />
											<input type="hidden" name="lead_id" id="lead-id" value="<?php echo $lead_id; ?>" />
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
												<?php echo _l('assign'); ?>
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<?php } elseif ($class_id) { ?>
						<div class="modal fade" id="event-modal">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title"><?php echo _l('edit_class_schedule'); ?></h4>
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
									</div>
									<div class="modal-body p-3">
										<form action="<?php echo site_url('portal/update_schedule'); ?>" method="post">
											<input type="hidden" name="schedule" id="schedule" value="">
											<input type="hidden" name="class_ids[]" id="class_ids" value="<?php echo $class_id; ?>">

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
						<?php } ?>

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
					content: '<span class="badge badge-info color-swatch"></span> <?php echo _li('Select slot and click on it, to assign'); ?>.',
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
	$.post('<?php echo site_url('portal/scheduleDetail'); ?>', data, json => {
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
</script>
