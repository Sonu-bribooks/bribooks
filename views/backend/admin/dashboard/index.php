<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
					<span class="mb-2 mb-md-0">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					</span>

					<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center">
						<div class="mb-2 mb-md-0 me-md-2 mr-1 text-truncate" style="width: 270px;">
							<select class="form-control select2 text-truncate" id="financial_year" data-toggle="select2" onchange="">
							<option value="" selected><?=_l('select_year')?></option>
							<?php foreach ($event_years as $event_year) { ?>
								<option value="<?php echo $event_year; ?>" <?php echo ($event_year == $financial_year) ? 'selected' : ''; ?> data-id="<?php echo $event_year; ?>">
								<?php echo ($event_year-1) . '-' . $event_year; ?>
								</option>
							<?php } ?>
							</select>
						</div>

						<div style="width: 270px;" class="text-truncate">
							<select class="form-control select2 text-truncate" id="event_data" data-toggle="select2" onchange="window.location='<?= $action_filter ?>/' + this.value + '/' + $financial_year">
							<option value="" selected><?=_l('select_event')?></option>
							<?php foreach ($events as $event) { ?>
								<option value="<?php echo $event['id']; ?>" <?php echo ($event['id'] == $event_id) ? 'selected' : ''; ?>><?php echo $event['name']; ?></option>
							<?php } ?>
							</select>
						</div>
					</div>
				</h4>
			</div><!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div id="content-stats">
	<i class="fa fa-cog fa-spin fa-3x fa-fw" aria-hidden="true"></i>
	<span class="sr-only">Loading...</span>
</div>

<!-- <div class="modal fade" id="viewModel" tabindex="-1" role="dialog" aria-labelledby="viewModelLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="viewModelLabel"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="viewModelBody"></div>
		</div>
	</div>
</div> -->


<div class="modal fade" id="viewModel" tabindex="-1" role="dialog" aria-labelledby="viewModelLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header d-flex justify-content-between align-items-center">
				<h5 class="modal-title" id="viewModelLabel"></h5>
				<div class="d-flex align-items-center">
					<a href="#" id="exportLink" class="mr-3" title="Export">
						<i class="fa fa-download fa-lg"></i>
					</a>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			</div>

			<div class="modal-body" id="viewModelBody"></div>
		</div>
	</div>
</div>



<script>
function getStats() {
	$.get('<?=base_url('admin/ajax_get_dashboard_count/' . $event_id)?>', function (json) {
		let rows = [];
		let dropdownAdded = false;

		var sourceNameConvensions = <?php echo json_encode($this->utm_source_model->get_all()['rows'] ?? []); ?>;

		var utmSourceKeyValue = sourceNameConvensions.reduce((acc, item) => {
			if (item._deleted === "0" && item.status === "1") {
				acc[item.key] = item.value;
			}
			return acc;
		}, {});

		for (const [key, value] of Object.entries(json?.data?.stats ?? {})) {
			let content = value.map(item => {
				if (key === 'enrolled_schools' && item.label.toLowerCase().includes('sources') && !dropdownAdded) {
					dropdownAdded = true;

					let options = Object.entries(item.options).map(([key, name]) => {
						let displayName = utmSourceKeyValue[name] || name.replace('_', ' ').toUpperCase();
						return `<option value="${name}">${displayName}</option>`;
					}).join('');

					return `
					<div class="col-sm-6 col-xl-4">
						<div class="card shadow-none m-0 border-left">
							<div class="card-body text-center">
								<i class="${item.icon} text-muted" style="font-size: 24px;"></i>
								<h3><span id="source_total">${item.total}</span></h3>
								<p class="text-muted font-15 mb-0">${item.label}</p>
								<select class="form-control select2" id="source_select">
									<option value="all" ${!item.selected ? 'selected' : ''}>All Source</option>
									${options}
								</select>
							</div>
						</div>
					</div>`;
				} else {
					if (key === 'enrolled_students') {
						let options = Object.entries(item.options).map(([key, name]) => {
						let displayName = utmSourceKeyValue[name] || name.replace('_', ' ').toUpperCase();
						return `<option value="${name}">${displayName}</option>`;
						}).join('');

						return `
						<div class="col-sm-6 col-xl-4">
							<div class="card shadow-none m-0 border-left">
								<div class="card-body text-center">
									<i class="${item.icon} text-muted" style="font-size: 24px;"></i>
									<h3><span id="student_source_total">${item.total}</span></h3>
									<p class="text-muted font-15 mb-0">${item.label}</p>
									<select class="form-control select2" id="student_source_select">
										<option value="all" ${!item.selected ? 'selected' : ''}>All Source</option>
										${options}
									</select>
								</div>
							</div>
						</div>`;
					} else if (key === 'enrolled_teachers') {
						let options = Object.entries(item.options).map(([key, name]) => {
						let displayName = utmSourceKeyValue[name] || name.replace('_', ' ').toUpperCase();
						return `<option value="${name}">${displayName}</option>`;
						}).join('');

						return `
						<div class="col-sm-6 col-xl-4">
							<div class="card shadow-none m-0 border-left">
								<div class="card-body text-center">
									<i class="${item.icon} text-muted" style="font-size: 24px;"></i>
									<h3><span id="teacher_source_total">${item.total}</span></h3>
									<p class="text-muted font-15 mb-0">${item.label}</p>
									<select class="form-control select2" id="teacher_source_select">
										<option value="all" ${!item.selected ? 'selected' : ''}>All Source</option>
										${options}
									</select>
								</div>
							</div>
						</div>`;
					} else {
						let col = Math.ceil(12 / value.length);
						return `
						<div class="col-sm-6 col-xl-${col}">
							<div class="card shadow-none m-0 border-left">
								<div class="card-body text-center">
									<i class="${item.icon} text-muted" style="font-size: 24px;"></i>
									<a href="#" data-model="viewModel" data-duration="all" data-label="${item.label}" data-type="${item.key}"><h3 class="text-secondary"><span id="all_registrations">${item.total}</span></h3></a>
									<p class="text-muted font-15 mb-0">${item.label}</p>
									<a href="#" data-model="viewModel" data-duration="today" data-label="${item.label}" data-type="${item.key}"><small class="text-success">
										<b id="all_new_registrations">${item.today}</b> <?= _l('today') ?>
									</small></a>
									<div>${item?.extra ?? ''}</div>
								</div>
							</div>
						</div>`;
					}
				}
			}).join('');

			rows.push(`
			<div class="row ${key}">
				<div class="col-12">
					<h6 class="text-center">${key.replace('_', ' ').toUpperCase()}</h6>
					<div class="card widget-inline">
						<div class="card-body p-0">
							<div class="row no-gutters">
								${content}
							</div>
						</div>
					</div>
				</div>
			</div>`);
		}

		$('#content-stats').html(rows.join(''));
	});
}

$(function() {
	getStats();
	setInterval(getStats, 30000);
});

function handleSourceChange(modelName, source, event_id, updateElementId) {
	$.ajax({
		url: '<?= base_url('admin/ajax_get_total_for_source') ?>',
		method: 'GET',
		data: {
			source: source,
			event_id: event_id,
			model_name: modelName
		},
		dataType: 'json',
		success: function(response) {
			$(updateElementId).text(response.total);
			success_notify('<?php _el('success'); ?>');
		},
		error: function(xhr, status, error) {
			error_notify(error);
		}
	});
}

$(document).ready(function() {
	// Handle change for source_select
	$('#content-stats').on('change', '#source_select', function() {
		var value = $(this).val();
		handleSourceChange('school_lead_model', value, <?= $event_id ?>, '#source_total');
	});

	// Handle change for student_source_select
	$('#content-stats').on('change', '#student_source_select', function() {
		var value = $(this).val();
		handleSourceChange('lead_model', value, <?= $event_id ?>, '#student_source_total');
	});

	// Handle change for teacher_source_select
	$('#content-stats').on('change', '#teacher_source_select', function() {
		var value = $(this).val();
		handleSourceChange('teacher_lead_model', value, <?= $event_id ?>, '#teacher_source_total');
	});

	$('#financial_year').on('change', function() {
		var year = $(this).find(':selected').attr('data-id');

		$.post({
			url: "<?= base_url('/api/getEvents') ?>",
			data: JSON.stringify({
				selling_end_date_ge		:  `${year - 1}-04-01 00:00:00`,
				selling_start_date_le	:  `${year}-03-31 23:59:59`,
				'order' 				: 'DESC'
			}),
			success: function(response) {
				const events = response.events;
				$("#event_data").empty();

				let ele = document.getElementById('event_data');
				ele.innerHTML = ele.innerHTML + '<option value=""><?=_l('select_event') ?></option>';
				document.getElementById('event_data').innerHTML = '<option value=""><?=_l('select_event') ?></option>';
				for (let i = 0; i < events.length; i++) {
					ele.innerHTML = ele.innerHTML + '<option value="' + events[i]['id'] + '">' + events[i]['name'] + '</option>';
				}
			}
		})
	})

	$('#event_data').on('change', function() {
		var event_id = $(this).val();
		var year 	 = $('#financial_year').val();

		if (event_id) {
			if (year) {
				window.location.href = '<?= $action_filter ?>/' + event_id + '/' + year;
			} else {
				window.location.href = '<?= $action_filter ?>/' + event_id;
			}
		}
	});

});
</script>

<script>
$(document).on('click', '[data-model="viewModel"]',function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	let url 		= '<?=base_url('admin/ajax_dashboard_details_report/' . $event_id)?>';
	let export_url 	= '<?=base_url('admin/ajax_dashboard_details_report/' . $event_id . '/true')?>';

	let filters = [];

	if ($el.data('type')) {
		filters.push(`type=${$el.data('type')}`);
	}

	if ($el.data('duration')) {
		filters.push(`duration=${$el.data('duration')}`);
	}

	if (filters.length > 0) {
		url 		+= '?' + filters.join('&');
		export_url 	+= '?' + filters.join('&');
	}

	$.get(url, function (json) {
		if (json.view) {
			$('#viewModel .modal-body').html(json.view);
			$('#viewModel .modal-title').html($el.data('label'));
			$('#exportLink').attr('href', export_url);
			$('#viewModel').modal('show');
		}
	});
});
</script>
