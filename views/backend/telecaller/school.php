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

				<a href="<?php echo $action_lead; ?>" class="float-right"><?php echo $text_archived; ?></a>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

				<div class="buttons text-right">
					<button class="btn btn-danger" onclick="archive()">
						<?php _el($archived ? 'restore' : 'archive'); ?>
					</button>
					<?php if (!$archived) { ?>
					<button
						class="btn btn-info"
						data-toggle="modal"
						data-target="#telecaller-modal"
					><?php _el('assign_telecaller'); ?></button>
					<?php } ?>
				</div>

				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered dt-responsive nowrap mb-0" width="100%" data-page-length='25'>
						<thead>
							<tr>
								<th><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);"></th>
								<th><?php _el('school_lead_id'); ?></th>
								<th><?php _el('site'); ?></th>
								<th><?php _el('telecaller'); ?></th>
								<th><?php _el('date_added'); ?></th>
								<th><?php _el('school_name'); ?></th>
								<th><?php _el('no_of_students'); ?></th>
								<th><?php _el('authorized_person'); ?></th>
								<th><?php _el('email'); ?></th>
								<th><?php _el('mobile'); ?></th>
								<th><?php _el('state'); ?></th>
								<th><?php _el('city'); ?></th>
								<th><?php _el('total_register'); ?></th>
								<th><?php _el('total_writing'); ?></th>
								<th><?php _el('total_published'); ?></th>
								<th><?php _el('total_sold'); ?></th>
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
								<form action="<?php echo site_url('telecaller/school_status/add'); ?>" method="post" id="form-status">
									<input type="hidden" name="school_lead_id" value="" />
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

				<div class="modal fade" id="telecaller-modal">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title"><?php _el('assign_telecaller'); ?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							</div>

							<div class="modal-body p-3">
								<form action="<?php echo site_url('telecaller/assign_school_lead_telecaller'); ?>" method="post" id="form-telecaller">
									<div class="form-group">
										<label for="class"><?php _el('telecaller'); ?></label>
										<select
											class="form-control select2"
											data-toggle="select2"
											name="telecaller_id"
											id="telecaller"
										>
											<?php foreach ($this->telecaller_model->get_all(['status' => 1])['rows'] ?? [] as $telecaller) { ?>
											<option
												value="<?php echo $telecaller['id']; ?>"
											><?php echo $telecaller['first_name'] . ' ' . $telecaller['last_name']; ?></option>
											<?php } ?>
										</select>
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
										form="form-telecaller"
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
</script>
<script>
$('#form-status').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	submitForm($el.attr('action'), new FormData($el[0]), json => {
		if (json.success) {
			success_notify(json.success);
			getLeadStatus($el.find('input[name="school_lead_id"]').val());
		} else {
			error_notify(json.error)
		}
	});
});

$('#form-telecaller').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	let fd = new FormData($el[0]);

	let selected = $('input[name^="selected"]:checked').map(function() { return this.value}).get()

	fd.append('selected', selected);

	submitForm($el.attr('action'), fd , json => {
		if (json.success) {
			success_notify(json.success);
			window.location.reload()
		} else {
			error_notify(json.error)
		}
	});
});

const archive = () => {
	if (confirm('<?php echo _li('Are you sure?'); ?>')) {
		let fd = new FormData();

		let selected = $('input[name^="selected"]:checked').map(function() { return this.value}).get()

		fd.append('selected', selected);

		submitForm('<?php echo site_url('telecaller/school_lead_bulk_archive'); ?>', fd , json => {
			if (json.success) {
				success_notify(json.success);
				window.location.reload()
			} else {
				error_notify(json.error)
			}
		});
	}
}

const getLeadStatus = (id) => {
	let fd = new FormData();
	fd.append('school_lead_id', id);

	submitForm('<?php echo site_url('telecaller/school_lead_status'); ?>', fd, json => {
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

const archived = (id) => {
	if (confirm('<?php _eli('Are u sure?'); ?>')) {
		let fd = new FormData();
		fd.append('school_lead_id', id);

		submitForm('<?php echo site_url('telecaller/school_lead_archived'); ?>', fd, json => {
			if (json.success) {
				success_notify(json.success);
				json.redirect && (setTimeout(() => location = json.redirect, 300));
			} else {
				error_notify(json.error)
			}
		});
	}
}

const showDetails = (id) => {
	let fd = new FormData();
	fd.append('school_lead_id', id);

	submitForm('<?php echo site_url('telecaller/school_lead_detail'); ?>', fd, json => {
		$('#details-modal').modal('hide');
		$('#details-modal').remove();

		const lead = json.lead;
		const center = lead.mode == 'offline' ?
			`<tr>
				<th><?php _el('center'); ?></th>
				<td>${lead.center}</td>
			</tr>` : '';

		const html = `<div class="modal fade" id="details-modal" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">${lead.name}</h4>
						<!--<h4 class="modal-title">${lead.course} - ${lead.name}</h4>-->
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
										<th><?php _el('school_name'); ?></th>
										<td>${lead.name}</td>
									</tr>
									<tr>
										<th><?php _el('authorized_person'); ?></th>
										<td>${lead.authorized_person}</td>
									</tr>
									<tr>
										<th><?php _el('no_of_students'); ?></th>
										<td>${lead.no_of_students}</td>
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
										<th><?php _el('state'); ?></th>
										<td>${lead.state_name}</td>
									</tr>
									<tr>
										<th><?php _el('city'); ?></th>
										<td>${lead.city_name}</td>
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
</script>

<script>
const editLead = (id) => {
	let fd = new FormData();
	fd.append('school_lead_id', id);

	submitForm('<?php echo site_url('telecaller/school_lead_detail'); ?>', fd, json => {
		$('#edit-modal').modal('hide');
		$('#edit-modal').remove();

		const lead = json.lead;

		const html = `<div class="modal fade" id="edit-modal">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">${lead.name}</h4>
						<!--<h4 class="modal-title">${lead.course} - ${lead.name}</h4>-->
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>

					<div class="modal-body p-3">
						<form action="<?php echo site_url('telecaller/edit_school_lead'); ?>" method="post" id="form-lead-edit">
							<input type="hidden" name="school_lead_id" value="${lead.id}" />
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
											<th><?php _el('country'); ?></th>
											<td><input
												type="text"
												name="name"
												value="${lead.name}"
												placeholder="<?php _el('school_name'); ?>"
												class="form-control"
											/></td>
										</tr>
										<tr>
											<th><?php _el('authorized_person'); ?></th>
											<td><input
												type="text"
												name="authorized_person"
												value="${lead.authorized_person}"
												placeholder="<?php _el('authorized_person'); ?>"
												class="form-control"
											/></td>
										</tr>
										<tr>
											<th><?php _el('no_of_students'); ?></th>
											<td><input
												type="text"
												name="no_of_students"
												value="${lead.no_of_students}"
												placeholder="<?php _el('no_of_students'); ?>"
												class="form-control"
											/></td>
										</tr>
										<tr>
											<th><?php _el('mobile'); ?></th>
											<td><input
												type="tel"
												name="mobile"
												value="${lead.mobile}"
												placeholder="<?php _el('mobile'); ?>"
												class="form-control"
											/></td>
										</tr>
										<tr>
											<th><?php _el('email'); ?></th>
											<td><input
												type="email"
												name="email"
												value="${lead.email}"
												placeholder="<?php _el('email'); ?>"
												class="form-control"
											/></td>
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
</script>

<script>
$(document).ready(function() {
	$('#ajax-datatable').DataTable({
		"ajax": "<?php echo site_url('telecaller/ajax_school_lead/' . $archived); ?>",
		"processing": true,
		"serverSide": true,
		"order": [[ 1, "desc" ]],
		"aoColumnDefs": [
			{ "bSortable": false, "aTargets": [0] },
			{ "bSearchable": false, "aTargets": [0] }
		],
		"columns": [
			{ "data": "checkbox", "ordering": false, render: function(data, type) {
				if (type === 'display') {
					return `<input type="checkbox" name="selected[]" value="${data}">`;
				}

				return data
			}},
			{ "data": "id" },
			{ "data": "site" },
			{ "data": "telecaller" },
			{ "data": "date_added" },
			{ "data": "name" },
			{ "data": "no_of_students" },
			{ "data": "authorized_person" },
			{ "data": "email" },
			{ "data": "mobile" },
			{ "data": "state_name" },
			{ "data": "city_name" },
			{ "data": "total_register" },
			{ "data": "total_books" },
			{ "data": "total_published" },
			{ "data": "total_sold" },
			{ "data": "status" },
			{ "data": "actions", render: function(data, type) {
				if (type === 'display') {
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
									><?php _el('restore'); ?></a>
								</li>
							</ul>
						</div>`
					<?php } else { ?>
						let course 	= '';
						let email 	= '';

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
										onclick="$('input[name=school_lead_id]').val(${data.id});getLeadStatus(${data.id});"
									><?php _el('add_feedback'); ?></a>
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
