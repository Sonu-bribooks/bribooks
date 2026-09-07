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

<script>
function getStats() {
	$.get('<?=base_url('admin/ajax_bs_get_dashboard_count/' . $event_id)?>', function (json) {
		let rows = [];
		
		for (const [key, value] of Object.entries(json?.data?.stats ?? {})) {
			let content = value.map(item => {
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

$(document).ready(function() {

	$('#financial_year').on('change', function() {
		var year = $(this).find(':selected').attr('data-id');

		$.post({
			url: "<?= base_url('/admin/ajax_bs_get_events') ?>",
			data: JSON.stringify({
				end_date_ge		:  `${year - 1}-04-01 00:00:00`,
				start_date_le	:  `${year}-03-31 23:59:59`,
				'order' 		: 'DESC'
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

