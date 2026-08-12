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
				<h4 class="mb-3 header-title"><?= _l('apply_filter') ?></h4>

				<div class="row">
					<div class="col-sm-2">
						<div class="form-group mb-3">
							<label class="col-form-label text-right" for="filter_lead_status"><?php echo _l('select_lead_status'); ?> </label>
							<select class="form-control select2" data-toggle="select2" id="filter_lead_status">
								<?php foreach ($lead_statuses as $lead_status) { ?>
									<option value="<?php echo $lead_status['id']; ?>"><?php echo $lead_status['name']; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group mb-3">
							<label class="col-form-label text-right" for="filter_book_status"><?php echo _l('select_book_status'); ?> </label>
							<select class="form-control select2" data-toggle="select2" id="filter_book_status">
								<?php foreach ($book_statuses ?? [] as $book_status) { ?>
									<option value="<?php echo $book_status['id']; ?>"><?php echo $book_status['name']; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group mb-3">
							<label class="col-form-label text-right" for="filter_page_count"><?php echo _l('select_page_count'); ?> </label>
							<select class="form-control select2" data-toggle="select2" id="filter_page_count">
								<?php foreach ($page_counts ?? [] as $page_count) { ?>
									<option value="<?php echo $page_count['id']; ?>"><?php echo $page_count['name']; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group mb-3">
							<label class="col-form-label text-right" for="filter_site_id"><?php echo _l('select_school'); ?> </label>
							<select class="form-control select2" data-toggle="select2" id="filter_site_id">
								<?php foreach ($sites ?? [] as $site) { ?>
									<option value="<?php echo $site['id']; ?>"><?php echo $site['name']; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group mb-3">
							<label class="col-form-label text-right" for="filter_location"><?php echo _l('select_location'); ?> </label>
							<select class="form-control select2" data-toggle="select2" id="filter_location">
								<?php foreach ($countries ?? [] as $country) { ?>
									<option value="<?php echo $country['name']; ?>"><?php echo $country['name']; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
				</div>
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

				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered dt-responsive nowrap mb-0" width="100%" data-page-length='25'>
						<thead>
							<tr>
								<th><?php _el('sn'); ?></th>
								<th><?php _el('lead_id'); ?></th>
								<th><?php _el('feedback'); ?></th>
								<th><?php _el('school'); ?></th>
								<th><?php _el('name'); ?></th>
								<th><?php _el('mobile'); ?></th>
								<th><?php _el('email'); ?></th>
								<th><?php _el('location'); ?></th>
								<th><?php _el('state'); ?></th>
								<th><?php _el('city'); ?></th>
								<th><?php _el('status'); ?></th>
								<th><?php _el('date_added'); ?></th>
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
								<form action="<?php echo base_url('telecaller/status/add'); ?>" method="post" id="form-status">
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

				<div class="modal fade" id="reassign-modal">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title"><?php _el('assign_telecaller'); ?></h4>
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							</div>

							<div class="modal-body p-3">
								<form action="<?php echo base_url('telecaller/assign_telecaller'); ?>" method="post" id="form-reassign">
									<input type="hidden" name="lead_id" value="" />

									<div class="form-group">
										<label for="telecaller"><?php _el('telecaller'); ?></label>
										<select
											class="form-control select2"
											data-toggle="select2"
											name="telecaller_id"
											id="telecaller"
										>
											<?php foreach ($this->telecaller_model->get_all()['rows'] as $telecaller) { ?>
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
var table = null;
</script>
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

	submitForm('<?php echo base_url('telecaller/lead_status'); ?>', fd, json => {
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

	submitForm('<?php echo base_url('telecaller/lead_detail'); ?>', fd, json => {
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
										<th><?php _el('country'); ?></th>
										<td>${lead.location}</td>
									</tr>
									<tr>
										<th><?php _el('student_name'); ?></th>
										<td>${lead.name}</td>
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
										<th><?php _el('location'); ?></th>
										<td>${lead.location}</td>
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

const editLead = (id) => {
	let fd = new FormData();
	fd.append('lead_id', id);

	submitForm('<?php echo base_url('telecaller/lead_detail'); ?>', fd, json => {
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
						<form action="<?php echo base_url('telecaller/edit_lead'); ?>" method="post" id="form-lead-edit">
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
											<th><?php _el('country'); ?></th>
											<td><input type="text" name="location" value="${lead.location}" placeholder="<?php _el('country'); ?>" class="form-control" /></td>
										</tr>
										<tr>
											<th><?php _el('student_name'); ?></th>
											<td><input type="text" name="name" value="${lead.name}" placeholder="<?php _el('student_name'); ?>" class="form-control" /></td>
										</tr>
										<tr>
											<th><?php _el('email'); ?></th>
											<td><input type="email" name="email" value="${lead.email}" placeholder="<?php _el('email'); ?>" class="form-control" /></td>
										</tr>
										<tr>
											<th><?php _el('mobile'); ?></th>
											<td><input type="text" name="mobile" value="${lead.mobile}" placeholder="<?php _el('mobile'); ?>" class="form-control" /></td>
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

const archived = (id) => {
	let fd = new FormData();
	fd.append('lead_id', id);

	submitForm('<?php echo base_url('telecaller/archived'); ?>', fd, json => {
		if (json.success) {
			success_notify(json.success);
			json.redirect && (setTimeout(() => location = json.redirect, 300));
		} else {
			error_notify(json.error)
		}
	});
}

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

$(document).ready(function() {
	table = $('#ajax-datatable').DataTable({
		"processing": true,
		"serverSide": true,
		"ajax": "<?php echo base_url('telecaller/ajax_lead/' . $archived); ?>",
		"columns": [
			{ "data": "sn" },
			{ "data": "id" },
			{ "data": "feedback" },
			{ "data": "site" },
			{ "data": "name" },
			{ "data": "mobile" },
			{ "data": "email" },
			{ "data": "location" },
			{ "data": "state_name" },
			{ "data": "city_name" },
			{ "data": "status" },
			{ "data": "date_added" },
			{ "data": "actions", render: function(data, type) {
				if (type === 'display') {
					let course = '';

					let email = '';

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
				}

				return data;
			}}
		]
	});
});
</script>

<script>
$(function() {
	$(document).on('change', '#filter_site_id, #filter_book_status, #filter_lead_status, #filter_page_count, #filter_location', function() {
		table.ajax.url("<?= base_url('telecaller/ajax_lead/' . $archived); ?>?site_id=" + $('#filter_site_id').val() + '&book_status=' + $('#filter_book_status').val() + '&verified=' + $('#filter_lead_status').val() + '&page_count=' + $('#filter_page_count').val() + '&location=' + $('#filter_location').val()).load();
	})
});
</script>
