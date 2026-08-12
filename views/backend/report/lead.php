<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				</h4>

				<button
					type="button"
					class="btn btn-warning d-none d-sm-block"
					onclick="$('.left-side-menu').toggle()"
				><?php _el('toggle_menu'); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title float-left"><?php echo $page_title; ?></h4>
				<a href="<?php echo $action_lead; ?>" class="float-right"><?php echo $text_archived; ?></a>

				<div class="alert alert-info alert-dismissible fade show" role="alert">
					<i class="fa fa-exclamation-circle"></i> <?php echo _li('system can\'t send payment link if course is not selected'); ?>
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>

				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th><?php _el('sn'); ?></th>
								<th><?php _el('lead_id'); ?></th>
								<th><?php _el('date_added'); ?></th>
								<th><?php _el('name'); ?></th>
								<th><?php _el('mobile'); ?></th>
								<th><?php _el('program_choice'); ?></th>
								<th><?php _el('mode'); ?></th>
								<th><?php _el('center'); ?></th>
								<th><?php _el('requested_schedule'); ?></th>
								<th><?php _el('confirmed_schedule'); ?></th>
								<th><?php _el('status'); ?></th>
								<th><?php _el('actions'); ?></th>
							</tr>
						</thead>
					</table>

					<?php if ($this->input->get('kb')) { ?>
					<table id="basic-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th>#</th>
								<th><?php _el('name'); ?></th>
								<th><?php _el('mobile'); ?></th>
								<th><?php _el('program_choice'); ?></th>
								<th><?php _el('mode'); ?></th>
								<th><?php _el('center'); ?></th>
								<th><?php _el('requested_schedule'); ?></th>
								<th><?php _el('confirmed_schedule'); ?></th>
								<th><?php _el('status'); ?></th>
								<th><?php _el('actions'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
								foreach ($leads as $key => $lead): ?>
								<tr>
									<td><?php echo $key+1; ?></td>
									<td><?php echo $lead['name']; ?></td>
									<td><?php echo $lead['mobile']; ?></td>
									<td><?php echo $lead['course']; ?></td>
									<td><?php echo $lead['mode']; ?></td>
									<td><?php echo $lead['center']; ?></td>
									<td><?php echo $lead['schedule']; ?></td>
									<td><?php echo $lead['confirmed_schedule']; ?></td>
									<td><?php echo _ls($lead['status']); ?><?php echo _mv($lead['mobile_verified']); ?></td>
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
														onclick="$('input[name=lead_id]').val(<?php echo $lead['id']; ?>);getLeadStatus(<?php echo $lead['id']; ?>);"
													><?php _el('add_feedback'); ?></a>
												</li>
												<?php if ($lead['course']) { ?>
												<li>
													<a class="dropdown-item"
														data-toggle="modal"
														data-target="#payment-modal"
														onclick="$('input[name=lead_id]').val(<?php echo $lead['id']; ?>);getEmis(<?php echo $lead['id']; ?>);"
													><?php _el('send_payment_link'); ?></a>
												</li>
												<?php } ?>
												<li>
													<a class="dropdown-item"
														onclick="editLead(<?php echo $lead['id']; ?>);"
													><?php _el('edit_lead'); ?></a>
												</li>
												<?php if (!$lead['email']) { ?>
												<li>
													<a class="dropdown-item"
														data-toggle="modal"
														data-target="#email-modal"
														onclick="$('input[name=lead_id]').val(<?php echo $lead['id']; ?>);"
													><?php _el('update_email'); ?></a>
												</li>
												<?php } ?>
												<li>
													<a class="dropdown-item"
														onclick="showDetails(<?php echo $lead['id']; ?>);"
													><?php _el('details'); ?></a>
												</li>
												<li>
													<a class="dropdown-item"
														onclick="archived(<?php echo $lead['id']; ?>);"
													><?php _el('archive'); ?></a>
												</li>
												<li>
													<a class="dropdown-item"
														href="<?php echo site_url('telecaller/schedule/' . $lead['id']); ?>"
													><?php _el('schedule'); ?></a>
												</li>
											</ul>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php } ?>

				</div>

				<div class="modal fade" id="email-modal">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title"><?php _el('update_email'); ?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							</div>

							<div class="modal-body p-3">
								<form action="<?php echo site_url('telecaller/email_update'); ?>" method="post" id="form-email">
									<input type="hidden" name="lead_id" value="" />
									<div class="form-group">
										<label for="email"><?php _el('email'); ?></label>
										<input
											type="email"
											name="email"
											placeholder="<?php _el('enter_email'); ?>"
											class="form-control"
											id="email"
										/>
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
										type="submit"
										form="form-email"
										class="btn btn-primary ml-1 save"
									><?php _el('update'); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="modal fade" id="status-modal">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title"><?php _el('update_feedback'); ?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							</div>

							<div class="modal-body p-3">
								<div class="table-responsive-sm">
									<table class="table table-striped table-centered" id="table-lead-status">
										<thead>
											<th><?php _el('status'); ?></th>
											<th><?php _el('comment'); ?></th>
											<th><?php _el('date_added'); ?></th>
										</thead>
										<tbody>

										</tbody>
									</table>
								</div>
								<form action="<?php echo site_url('telecaller/status/add'); ?>" method="post" id="form-status">
									<input type="hidden" name="lead_id" value="" />
									<div class="form-group">
										<label for="status"><?php _el('status'); ?></label>
										<select
											class="form-control select2"
											data-toggle="select2"
											name="status"
											id="status"
										>
											<option value="not_responding"><?php _el('not_responding'); ?></option>
											<option value="not_interested"><?php _el('not_interested'); ?></option>
											<option value="course_fee_details"><?php _el('course fee and details'); ?></option>
											<option value="demo_rescheduled"><?php _el('demo_rescheduled'); ?></option>
										</select>
									</div>

									<div class="form-group">
										<label for="comment"><?php _el('comment'); ?></label>
										<textarea name="comment" rows="6" class="form-control"></textarea>
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
										type="submit"
										form="form-status"
										class="btn btn-primary ml-1 save"
									><?php _el('save'); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="modal fade" id="payment-modal">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title"><?php _el('amount'); ?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							</div>

							<div class="modal-body p-3">
								<form action="<?php echo site_url('telecaller/send_payment_link'); ?>" method="post" id="form-payment">
									<input type="hidden" name="lead_id" value="" />
									<div class="form-group">
										<label for="amount"><?php _el('frequency'); ?></label>
										<select
											class="form-control select2"
											data-toggle="select2"
											name="emi_type"
											id="emi_type"
										>

										</select>
									</div>

									<div class="form-group">
										<label for="amount"><?php _el('amount'); ?></label>
										<input
											type="amount"
											name="amount"
											placeholder="<?php _el('enter_amount'); ?>"
											class="form-control"
											id="amount"
										/>
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
										type="submit"
										form="form-payment"
										class="btn btn-primary ml-1"
									><?php _el('send'); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="modal fade" id="reassign-modal">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title"><?php _el('assign_telecaller'); ?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							</div>

							<div class="modal-body p-3">
								<form action="<?php echo site_url('telecaller/assign_telecaller'); ?>" method="post" id="form-reassign">
									<input type="hidden" name="lead_id" value="" />

									<div class="form-group">
										<label for="telecaller"><?php _el('telecaller'); ?></label>
										<select
											class="form-control select2"
											data-toggle="select2"
											name="telecaller_id"
											id="telecaller"
										>
											<?php foreach ($this->telecaller_model->get_all()->result_array() as $telecaller) { ?>
											<option
												value="<?php echo $telecaller['id']; ?>"
											><?php echo $telecaller['first_name'] . ' ' . $telecaller['last_name']; ?></option>
											<?php } ?>
										</select>
									</div>

									<div class="form-group">
										<label for="comment"><?php _el('comment'); ?></label>
										<textarea
											class="form-control"
											name="comment"
											rows="5"
											id="comment"
										></textarea>
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
										type="submit"
										form="form-reassign"
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
$('#form-reassign').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	if (confirm('<?php echo _li('Are you sure?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]) , json => {
			if (json.success) {
				success_notify(json.success);
				window.location.reload()
			} else {
				error_notify(json.error)
			}
		});
	}

	return false;
});
</script>

<script>
$(function() {
	$('.datepicker-autoclose').datepicker({
		autoclose: true,
		todayHighlight: true,
		format: "mm/dd/yyyy",
		startDate: "+0d",
		// endDate: "+2d"
	});
});

$('#form-status').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	submitForm($el.attr('action'), new FormData($el[0]), json => {
		if (json.success) {
			success_notify(json.success);
			getLeadStatus($el.find('input[name="lead_id"]').val());
		} else {
			error_notify(json.error)
		}
	});
});

const getLeadStatus = (id) => {
	let fd = new FormData();
	fd.append('lead_id', id);

	submitForm('<?php echo site_url('telecaller/lead_status'); ?>', fd, json => {
		let html = '';

		json.statuses.map(status => {
			html += `
			<tr>
				<td>${status.status}</td>
				<td>${status.comment}</td>
				<td>${status.date_added}</td>
			</tr>`
		});

		$('#table-lead-status tbody').html(html);
	});
};

const showDetails = (id) => {
	let fd = new FormData();
	fd.append('lead_id', id);

	submitForm('<?php echo site_url('telecaller/lead_detail'); ?>', fd, json => {
		$('#details-modal').modal('hide');
		$('#details-modal').remove();

		const lead = json.lead;
		const center = lead.mode == 'offline' ?
			`<tr>
				<th><?php _el('center'); ?></th>
				<td>${lead.center}</td>
			</tr>` : '';

		const html = `<div class="modal fade" id="details-modal">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">${lead.course} - ${lead.name}</h4>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>

					<div class="modal-body p-3">
						<div class="table-responsive-sm">
							<table class="table table-striped table-centered">
								<tbody>
									<tr>
										<th><?php _el('status'); ?></th>
										<td>${lead.status}</td>
									</tr>
									<tr>
										<th><?php _el('program_choice'); ?></th>
										<td>${lead.course}</td>
									</tr>
									<tr>
										<th><?php _el('learning_mode'); ?></th>
										<td>${lead.mode}</td>
									</tr>
									<tr>
										<th><?php _el('teacher_assigned'); ?></th>
										<td>${lead.teacher}</td>
									</tr>
									${center}
									<tr>
										<th><?php _el('requested_schedule'); ?></th>
										<td>${lead.schedule}</td>
									</tr>
									<tr>
										<th><?php _el('confirmed_schedule'); ?></th>
										<td>${lead.confirmed_schedule}</td>
									</tr>
									<tr>
										<th><?php _el('student_name'); ?></th>
										<td>${lead.name}</td>
									</tr>
									<tr>
										<th><?php _el('student_age'); ?></th>
										<td>${lead.age}<?php _el('years'); ?></td>
									</tr>
									<tr>
										<th><?php _el('parent_name'); ?></th>
										<td>${lead.parent_name}</td>
									</tr>
									<tr>
										<th><?php _el('mobile'); ?></th>
										<td>${lead.mobile}</td>
									</tr>
									<tr>
										<th><?php _el('email'); ?></th>
										<td>${lead.email}</td>
									</tr>
									<tr>
										<th><?php _el('utm_source'); ?></th>
										<td>${lead.utm_source}</td>
									</tr>
									<tr>
										<th><?php _el('utm_medium'); ?></th>
										<td>${lead.utm_medium}</td>
									</tr>
									<tr>
										<th><?php _el('utm_campaign'); ?></th>
										<td>${lead.utm_campaign}</td>
									</tr>
									<tr>
										<th><?php _el('date_added'); ?></th>
										<td>${lead.date_added}</td>
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

var centerId = 0, demoTime = null;

const editLead = (id) => {
	let fd = new FormData();
	fd.append('lead_id', id);

	submitForm('<?php echo site_url('telecaller/lead_detail'); ?>', fd, json => {
		$('#edit-modal').modal('hide');
		$('#edit-modal').remove();

		const lead = json.lead;
		const center = lead.mode == 'offline' ?
			`<tr>
				<th><?php _el('center'); ?></th>
				<td>${lead.center}</td>
			</tr>` : '';

		const schedule = lead.schedule.split(' ');

		const html = `<div class="modal fade" id="edit-modal">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">${lead.course} - ${lead.name}</h4>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>

					<div class="modal-body p-3">
						<form action="<?php echo site_url('telecaller/edit_lead'); ?>" method="post" id="form-lead-edit">
							<input type="hidden" name="lead_id" value="${lead.id}" />
							<div class="table-responsive-sm">
								<table class="table table-striped table-centered">
									<tbody>
										<tr>
											<th><?php _el('status'); ?></th>
											<td>${lead.status}</td>
										</tr>
										<tr>
											<th><?php _el('date_added'); ?></th>
											<td>${lead.date_added}</td>
										</tr>
										<tr>
											<th><?php _el('program_choice'); ?></th>
											<td><select
												class="form-control select2"
												data-toggle="select2"
												name="programs"
												id="programs"
											>
												<?php foreach ($programs as $program) { ?>
												<option value="<?php echo $program['program_id']; ?>"><?php echo $program['name']; ?></option>
												<?php } ?>
											</select></td>
										</tr>
										<tr>
											<th><?php _el('student_name'); ?></th>
											<td><input type="text" name="name" value="${lead.name}" placeholder="<?php _el('student_name'); ?>" class="form-control" /></td>
										</tr>
										<tr>
											<th><?php _el('student_age'); ?></th>
											<td><select
												class="form-control select2"
												data-toggle="select2"
												name="student_age"
												id="student_age"
											>
												<?php foreach ($ages as $age) { ?>
												<option value="<?php echo $age['key']; ?>"><?php echo $age['value']; ?></option>
												<?php } ?>
											</select></td>
										</tr>
										<tr>
											<th><?php _el('parent_name'); ?></th>
											<td><input type="text" name="parent_name" value="${lead.parent_name}" placeholder="<?php _el('parent_name'); ?>" class="form-control" /></td>
										</tr>
										<tr>
											<th><?php _el('mobile'); ?></th>
											<td><input type="tel" name="mobile" value="${lead.mobile}" placeholder="<?php _el('mobile'); ?>" class="form-control" /></td>
										</tr>
										<tr>
											<th><?php _el('email'); ?></th>
											<td><input type="email" name="email" value="${lead.email}" placeholder="<?php _el('email'); ?>" class="form-control" /></td>
										</tr>
										<tr>
											<th><?php _el('learning_mode'); ?></th>
											<td>
												<select name="learning_mode" id="learning_mode" onchange="getClassType(this)" data-toggle="select2" class="form-control select2">
													<option value=""><?php _el('select_learning_mode'); ?></option>
													<option value="online"><?php _el('online'); ?></option>
													<option value="offline"><?php _el('offline'); ?></option>
												</select>
											</td>
										</tr>
										<tr class="city-center d-none">
											<th><?php _el('city'); ?></th>
											<td>
												<select name="city_id" id="city" onchange="getCenter(this)" data-toggle="select2" class="form-control select2">
													<option value=""><?php _el('select_city'); ?></option>
													<?php foreach ($cities as $city) { ?>
													<option value="<?php echo $city['city_id']; ?>"><?php echo $city['name']; ?></option>
													<?php } ?>
												</select>
											</td>
										</tr>
										<tr class="city-center d-none">
											<th><?php _el('center'); ?></th>
											<td>
												<select name="center" id="center" data-toggle="select2" class="form-control select2">
													<option value=""><?php _el('select_center'); ?></option>
												</select>
											</td>
										</tr>
										<tr>
											<th><?php _el('demo_date'); ?></th>
											<td>
												<input type="text" id="demo_date" name="demo_date" value="${schedule.shift()}" class="form-control datepicker-autoclose" placeholder="<?php _el('select_date_of_demo'); ?>" />
											</td>
										</tr>
										<tr>
											<th><?php _el('demo_time'); ?></th>
											<td>
												<select name="demo_time" id="demo_time" data-toggle="select2" class="form-control select2">
													<option value=""><?php _el('select_time_of_demo'); ?></option>
												</select>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<div class="text-right pt-2">
								<button
									type="submit"
									form="form-lead-edit"
									class="btn btn-primary ml-1"
								><?php _el('update'); ?>
								</button>
							</div>
						</form>

					</div>
				</div>
			</div>
		</div>`;

		$('body').append(html);
		$('#edit-modal').modal('show');
		$('.select2').select2();

		lead.mode && $('#learning_mode').val(lead.mode).trigger('change');
		lead.course_id && $('#programs').val(lead.course_id).trigger('change');
		lead.city_id && $('#city').val(lead.city_id).trigger('change');
		lead.age && $('#student_age').val(lead.age).trigger('change');

		centerId = lead.center_id;
		centerId && $('#center').val(centerId).trigger('change');

		demoTime = schedule.shift();

		//demoTime = lead.class_id;
		//demoTime && $('#demo_time').val(demoTime).trigger('change');

		$('.datepicker-autoclose').datepicker({
			autoclose: true,
			todayHighlight: true,
			format: "mm/dd/yyyy",
			startDate: "+0d",
			// endDate: "+2d"
		});

		$('.datepicker-autoclose').datepicker().on('changeDate', function(e) {
			getSlots(lead.course_id, lead.mode);
		});

		getSlots(lead.course_id, lead.mode);
	});
}

const sendPaymentLink = (id) => {
	let fd = new FormData();
	fd.append('lead_id', id);
	fd.append('amount', $('#amount').val());
	fd.append('emi_type', $('#emi_type option:selected').text());

	submitForm('<?php echo site_url('telecaller/send_payment_link'); ?>', fd, json => {
		if (json.success) {
			success_notify(json.success);
		} else {
			error_notify(json.error)
		}
	});
}

const archived = (id) => {
	let fd = new FormData();
	fd.append('lead_id', id);

	submitForm('<?php echo site_url('telecaller/archived'); ?>', fd, json => {
		if (json.success) {
			success_notify(json.success);
			json.redirect && (setTimeout(() => location = json.redirect, 300));
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

$(document).on('submit', '#form-lead-edit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	submitForm($el.attr('action'), new FormData($el[0]), json => {
		if (json.success) {
			success_notify(json.success);
		} else {
			error_notify(json.error)
		}

		json.redirect && setTimeout(() => (window.location = json.redirect), 300);
	});
});

const getClassType = (el) => {
	let program = $('#programs').val();

	if (el.value == 'offline') {
		$('.city-center').removeClass('d-none')
	} else if (el.value == 'online') {
		if (!$('.city-center').hasClass('d-none')) {
			$('.city-center').addClass('d-none');
		}

		$('#center').val();
		$('#city').val();
		$('#center').html('<option value=""><?php _el('select_center'); ?></option>');
	}

	getSlots(program, el.value);
}

const getSlots = (pId, mode) => {
	console.log(demoTime);

	if (mode) {
		let fd = new FormData();
		fd.append('program_id', pId);
		fd.append('mode', mode);
		fd.append('demo_date', $('#demo_date').val());

		submitForm('<?php echo site_url('api/classes'); ?>', fd, json => {
			if (json.classes) {
				let result = '<option value=""><?php _el('select_time_of_demo'); ?></option>';

				$.each(json.classes, function(k, v) {
					let selected = '';

					if (v.slot + ':00' == demoTime) {
						selected = 'selected';
					}

					result += '<option value="' + v.class_id + '"' + selected + '>'+ v.slot +'</option>'
				});

				$('#demo_time').html(result);

				//demoTime && $('#demo_time').val(demoTime).trigger('change');
			} else {
				error_notify(json.error)
			}
		});
	}
}

const getCenter = (c) => {
	if (c.value == 0) {
		error_notify('<?php _el('City is not listed for the offline courses, so switched to the online learning mode'); ?>');

		let pId = $('#programs').val();
		let mode = 'online';
		$('.city-center').addClass('d-none')

		$('#learning_mode').val('online').trigger('change');

		$('#center').val();
		$('#center').html('<option value=""><?php _el('select_center'); ?></option>');
		$('#city').val();

		getSlots(pId, mode);
	} else {
		let fd = new FormData();
		fd.append('city_id', c.value);

		submitForm('<?php echo site_url('api/centers'); ?>', fd, json => {
			if (json.centers) {
				let result = '<option value=""><?php _el('select_center'); ?></option>';

				$.each(json.centers, function(k, v) {
					result += '<option value="' + v.center_id + '">'+ v.name +'</option>'
				});

				$('#center').html(result);
				centerId && $('#center').val(centerId).trigger('change');
			} else {
				error_notify(json.error)
			}
		});
	}
}

$('#form-payment').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	let fd = new FormData($el[0]);
	fd.set('emi_type', $('#emi_type option:selected').text());

	submitForm($el.attr('action'), fd, json => {
		if (json.success) {
			$('#payment-modal').modal('hide');
			success_notify(json.success);
		} else {
			error_notify(json.error)
		}
	});
});

const getEmis = id => {
	let fd = new FormData();
	fd.append('lead_id', id);

	submitForm('<?php echo site_url('telecaller/getEmis'); ?>', fd, json => {
		if (json.emis) {
			let html = '';
			emis = json.emis;

			json.emis.map(emi => {
				emi.amount > 0 && (html += `<option value="${emi.amount}">${emi.key}</option>`);
			});

			html += '<option value=""><?php echo _li('other'); ?></option>'

			$('#emi_type').html(html);
			$('#emi_type').select2();

			setTimeout(() => ($('#emi_type').trigger('change')), 500);

			$('#emi_type').on('change', function() {
				$('#amount').val($(this).val());
			});
		} else {
			error_notify(json.error)
		}
	});
}

$(document).ready(function() {
	$('#ajax-datatable').DataTable( {
		"ajax": "<?php echo site_url('telecaller/ajax_lead/' . $archived); ?>",
		"columns": [
			{ "data": "sn" },
			{ "data": "id" },
			{ "data": "date_added" },
			{ "data": "name" },
			{ "data": "mobile" },
			{ "data": "program_choice" },
			{ "data": "mode" },
			{ "data": "center" },
			{ "data": "requested_schedule" },
			{ "data": "confirmed_schedule" },
			{ "data": "status" },
			{ "data": "actions", render: function(data, type) {
				if (type === 'display') {
					let course = data.course ?
					`<li>
						<a class="dropdown-item"
							data-toggle="modal"
							data-target="#payment-modal"
							onclick="$('input[name=lead_id]').val(${data.id});getEmis(${data.id});"
						><?php _el('send_payment_link'); ?></a>
					</li>` : '';

					let email = data.email ?
					`<li>
						<a class="dropdown-item"
							data-toggle="modal"
							data-target="#email-modal"
							onclick="$('input[name=lead_id]').val(${data.id});"
						><?php _el('update_email'); ?></a>
					</li>` : '';

					return `<div class="dropright dropright">
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
									onclick="$('input[name=lead_id]').val(${data.id});getLeadStatus(${data.id});"
								><?php _el('add_feedback'); ?></a>
							</li>
							<li>
								<a class="dropdown-item"
									data-toggle="modal"
									data-target="#reassign-modal"
									onclick="$('#reassign-modal input[name=lead_id]').val(${data.id});"
								><?php _el('reassign_to_other'); ?></a>
							</li>
							${course}
							<li>
								<a class="dropdown-item"
									onclick="editLead(${data.id});"
								><?php _el('edit_lead'); ?></a>
							</li>
							${email}
							<li>
								<a class="dropdown-item"
									onclick="showDetails(${data.id});"
								><?php _el('details'); ?></a>
							</li>
							<li>
								<a class="dropdown-item"
									onclick="archived(${data.id});"
								><?php _el('archive'); ?></a>
							</li>
							<li>
								<a class="dropdown-item"
									href="<?php echo site_url('telecaller/schedule/'); ?>${data.id}"
								><?php _el('schedule'); ?></a>
							</li>
						</ul>
					</div>`
				}

				return data;
			}}
		]
	} );
});
</script>
