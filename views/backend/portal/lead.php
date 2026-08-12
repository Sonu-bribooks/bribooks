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

				<div class="form-group row mb-3">
					<label class="col-md-9 col-form-label text-right" for="site_id"><?php echo _l('select_site'); ?> </label>
					<div class="col-md-3">
						<select class="form-control select2" data-toggle="select2" onchange="window.location='<?=$action_filter?>?site_id=' + this.value">
							<?php foreach ($sites as $site) {
								if ($site_id == $site['id']) {
							?>
							<option value="<?php echo $site['id']; ?>" selected><?php echo $site['name']; ?></option>
							<?php } else { ?>
							<option value="<?php echo $site['id']; ?>"><?php echo $site['name']; ?></option>
							<?php } } ?>
						</select>
					</div>
				</div>

				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th><?php _el('sn'); ?></th>
								<th><?php _el('lead_id'); ?></th>
								<th><?php _el('date_added'); ?></th>
								<th><?php _el('telecaller_assigned'); ?></th>
								<th><?php _el('name'); ?></th>
								<th><?php _el('grade'); ?></th>
								<th><?php _el('section'); ?></th>
								<th><?php _el('parent_name'); ?></th>
								<th><?php _el('mobile'); ?></th>
								<th><?php _el('email'); ?></th>
								<th><?php _el('feedback'); ?></th>
								<th><?php _el('status'); ?></th>
								<th><?php _el('actions'); ?></th>
							</tr>
						</thead>
					</table>

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
								<form action="<?php echo site_url('portal/status/add'); ?>" method="post" id="form-status">
									<input type="hidden" name="lead_id" value="" />
									<div class="form-group">
										<label for="status"><?php _el('status'); ?></label>
										<select
											class="form-control select2"
											data-toggle="select2"
											name="status"
											id="status"
										>
											<?php foreach (LEAD_STATUSES as $key => $value) { ?>
											<option value="<?php echo $key; ?>"><?php _el($value); ?></option>
											<?php } ?>
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
								<form action="<?php echo site_url('portal/send_payment_link'); ?>" method="post" id="form-payment">
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
								<form action="<?php echo site_url('portal/assign_telecaller'); ?>" method="post" id="form-reassign">
									<input type="hidden" name="lead_id" value="" />

									<div class="form-group">
										<label for="telecaller"><?php _el('telecaller'); ?></label>
										<select
											class="form-control select2"
											data-toggle="select2"
											name="telecaller_id"
											id="telecaller"
										>
											<?php foreach ($this->telecaller_model->get_all(['site_id' => $this->config->item('site_id')])['rows'] as $telecaller) { ?>
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

	submitForm('<?php echo site_url('portal/lead_status'); ?>', fd, json => {
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

	submitForm('<?php echo site_url('portal/lead_detail'); ?>', fd, json => {
		$('#details-modal').modal('hide');
		$('#details-modal').remove();

		const lead = json.lead;

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

									<tr>
										<th><?php _el('student_name'); ?></th>
										<td>${lead.name}</td>
									</tr>
									<tr>
										<th><?php _el('student_grade'); ?></th>
										<td>${lead.grade}<?php _el('years'); ?></td>
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

const edit_lead = (id) => {
	let fd = new FormData();
	fd.append('lead_id', id);

	submitForm('<?php echo site_url('portal/lead_detail'); ?>', fd, json => {
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
						<form action="<?php echo site_url('portal/edit_lead'); ?>" method="post" id="form-lead-edit">
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
											<th><?php _el('student_grade'); ?></th>
											<td><input type="text" name="grade" value="${lead.grade}" placeholder="<?php _el('student_grade'); ?>" class="form-control" /></td>
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

		lead.course_id && $('#programs').val(lead.course_id).trigger('change');
	});
}

const sendPaymentLink = (id) => {
	let fd = new FormData();
	fd.append('lead_id', id);
	fd.append('amount', $('#amount').val());
	fd.append('emi_type', $('#emi_type option:selected').text());

	submitForm('<?php echo site_url('portal/send_payment_link'); ?>', fd, json => {
		if (json.success) {
			success_notify(json.success);
		} else {
			error_notify(json.error)
		}
	});
}

const archived = (id) => {
	if (confirm('<?php _el('Are you sure?'); ?>')) {
		let fd = new FormData();
		fd.append('lead_id', id);

		submitForm('<?php echo site_url('portal/archived'); ?>', fd, json => {
			if (json.success) {
				success_notify(json.success);
				json.redirect && (setTimeout(() => location = json.redirect, 300));
			} else {
				error_notify(json.error)
			}
		});
	}
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

	submitForm('<?php echo site_url('portal/getEmis'); ?>', fd, json => {
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
		"ajax": "<?php echo $ajax_url; ?>",
		"processing": true,
		"serverSide": true,
		"columns": [
			{ "data": "sn" },
			{ "data": "id" },
			{ "data": "date_added" },
			{ "data": "telecaller" },
			{ "data": "name" },
			{ "data": "grade" },
			{ "data": "section" },
			{ "data": "parent_name" },
			{ "data": "mobile" },
			{ "data": "email" },
			{ "data": "feedback" },
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

					<?php if ($archived) { ?>
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
										onclick="archived(${data.id});"
									><?php echo !$archived ? _li('archive') : _li('restore'); ?></a>
								</li>
							</ul>
						</div>`;
					<?php } else { ?>
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
									><?php _el('assign_telecaller'); ?></a>
								</li>
								<li>
									<a class="dropdown-item"
										onclick="archived(${data.id});"
									><?php echo !$archived ? _li('archive') : _li('restore'); ?></a>
								</li>
							</ul>
						</div>`
					<?php } ?>
				}

				return data;
			}}
		]
	} );
});
</script>
