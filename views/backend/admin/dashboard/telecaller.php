<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
					<span class="mb-2 mb-md-0">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					</span>

					<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center">
						<div style="width: 270px;" class="text-truncate">
							<select class="form-control select2 text-truncate" id="event_data" data-toggle="select2" onchange="window.location='<?= $action_filter ?>/' + this.value">
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

<div class="modal fade" id="viewModel" tabindex="-1" role="dialog" aria-labelledby="viewModelLabel" aria-hidden="true">
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
</div>

<script>
function getStats() {
	$.ajax({
		url: '<?=base_url('admin/ajax_telecaller_dashboard/' . $event_id)?>',
		method: 'GET',
		timeout: 10000,
		success: function(json) {
			let rows = [];
			let dropdownAdded = false;

			for (const [key, value] of Object.entries(json?.data?.stats ?? {})) {
				let content = value.map(item => {
					let col = Math.ceil(12 / value.length);
					return `
					<div class="col-sm-6 col-xl-${col}">
						<div class="card shadow-none m-0 border-left">
							<div class="card-body text-center">
								<i class="${item.icon} text-muted" style="font-size: 24px;"></i>
								<a href="#" data-model="viewModel" data-duration="all" data-label="${item.label}" data-type="${item.key}" data-user="${item.user_id}"><h3 class="text-secondary"><span id="all_registrations">${item.total}</span></h3></a>
								<p class="text-muted font-15 mb-0">${item.label}</p>
								<a href="#" data-model="viewModel" data-duration="today" data-label="${item.label}" data-type="${item.key}" data-user="${item.user_id}"><small class="text-success">
									<b id="all_new_registrations">${item.today}</b> <?= _l('today') ?>
								</small></a>
							</div>
						</div>
					</div>`;
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
		},
		error: function(jqXHR, textStatus, errorThrown) {
			if (textStatus === 'timeout') {
				console.log('Request timed out');
			} else {
				console.log('Error:', textStatus, errorThrown);
			}
		}
	});
}

$(function() {
	getStats();
	setInterval(getStats, 180000);
});

$(document).ready(function() {
	$('#event_data').on('change', function() {
		var event_id = $(this).val();
		if (event_id) {
			window.location.href = '<?= $action_filter ?>/' + event_id;
		}
	});

});
</script>

<script>
$(document).on('click', '[data-model="viewModel"]',function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	let url = '<?=base_url('admin/ajax_telecaller_dashboard_details/' . $event_id)?>';

	let filters = [];

	if ($el.data('type')) {
		filters.push(`type=${$el.data('type')}`);
	}

	if ($el.data('user')) {
		filters.push(`user_id=${$el.data('user')}`);
	}

	if ($el.data('duration')) {
		filters.push(`duration=${$el.data('duration')}`);
	}

	if (filters.length > 0) {
		url += '?' + filters.join('&');
	}

	$.get(url, function (json) {
		if (json.view) {
			$('#viewModel .modal-body').html(json.view);
			$('#viewModel .modal-title').html($el.data('label'));
			$('#viewModel').modal('show');
		}
	});
});
</script>
