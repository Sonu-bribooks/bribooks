<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
				<a href="#" id="btn-export-csv" class="btn btn-outline-primary btn-rounded alignToTitle ml-2"><i class="mdi mdi-download"></i> <?php echo _li(' Author CSV'); ?></a>
				<a href="#" id="btn-export-pdf" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-download"></i> <?php echo _li(' Author PDFs'); ?></a>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div id="accordion">
	<div class="card mb-2">
		<div class="card-header" id="heading-1">
			<h5 class="m-0">
				<a class="collapsed" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<?=_l('filters')?>
				</a>

				<a class="float-right" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<i class="dripicons-view-apps"></i>
				</a>
			</h5>
		</div>
		<div id="collapse-1" class="collapse hide" data-parent="#accordion" aria-labelledby="heading-1">
			<div class="card-body">
				<form class="form" action="#" method="post" id="form-filter">
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group mb-2">
								<label><?=_l('select_event')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="event_id"
								>
									<option value="-1"><?=_l('all')?></option>
									<?php foreach ($events as $key => $event) { ?>
										<option value="<?=$event['id']?>"><?=$event['name']?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group mb-2">
								<label><?=_l('select_status')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="status"
								>
									<option value="-1" selected><?=_l('all')?></option>
									<option value="0"><?=_l('pending')?></option>
									<option value="1"><?=_l('accepted')?></option>
									<option value="2"><?=_l('rejected')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group mb-2">
								<label><?=_l('select_verified')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="verified"
								>
									<option value="-1" selected><?=_l('all')?></option>
									<option value="1"><?=_l('verified')?></option>
									<option value="0"><?=_l('not_verified')?></option>
								</select>
							</div>
						</div>

						<div class="col-sm-6">
							<div class="form-group mb-2">
								<label><?=_l('select_slot')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="slot_id"
								>
									<option value="-1"><?=_l('all')?></option>
									<?php foreach ($slots as $key => $slot) { ?>
										<option value="<?=$slot['id']?>"><?=$slot['slot_start']?> - <?=$slot['slot_end']?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4">
							<button type="button" class="btn btn-warning" id="btn-export"> <?php echo _l('export');?></button>
						</div>
						<div class="col-sm-8 text-right">
							<div class="btn-group">
								<button type="submit" class="btn btn-info" id="submit-button"> <?php echo _l('search');?></button>
								<button type="button" class="btn btn-danger ml-2" id="filter-reset"> <?php echo _l('reset');?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th>#</th>
								<th><?php echo _l('id'); ?></th>
								<th><?php echo _l('event'); ?></th>
								<th><?php echo _l('name'); ?></th>
								<th><?php echo _l('slot'); ?></th>
								<th><?php echo _l('invite_status'); ?></th>
								<th><?php echo _l('guest_count'); ?></th>
								<th><?php echo _l('location'); ?></th>
								<th><?php echo _l('source'); ?></th>
								<th><?php echo _l('verified'); ?></th>
								<th><?php echo _l('date_added'); ?></th>
								<th><?php echo _l('actions'); ?></th>
							</tr>
						</thead>
					</table>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<script>
var table = null;
$(function() {
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> [
			'sn',
			'id',
			'event',
			'name',
			'slot',
			'invite_status',
			'guest_count',
			'location',
			'source',
			'verified',
			'date_added',
			'actions',
		]
	]); ?>'));

	table = $('#ajax-datatable').DataTable({
		"ajax": "<?php echo $action_ajax; ?>",
		"processing": true,
		"serverSide": true,
		"order": [
			[0, "desc"]
		],
		"columns": columns
	})
});
</script>

<script>
setInterval(() => {
	table.ajax.reload();
}, 10000)
</script>

<script>
$(function() {
	$(document).on('click', '#filter-reset', function(e) {
		table.ajax.url('<?= $action_ajax ?>').load();
		$('.input-filter').val('').trigger('change');
	});

	$(document).on('submit', '#form-filter', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$el = $(this);
		let filters = [];
		$el.find('.input-filter').each(function() {
			filters.push($(this).attr('name') + '=' + $(this).val());
		});

		table.ajax.url('<?= $action_ajax ?>?' + filters.join('&')).load();
	})
});

$(function() {
	$(document).on('click', '#btn-export', function(e) {
		e.preventDefault();
		e.stopPropagation();
		if (confirm('<?=_l('are_you_sure?')?>')) {
			$el = $('#form-filter');
			let filters = [];
			$el.find('.input-filter').each(function() {
				filters.push($(this).attr('name') + '=' + $(this).val());
			});

			window.location = '<?= base_url('admin/export_exhibition_invites/'); ?>1?' + filters.join('&');
		}
	});

	$(document).on('click', '#btn-export-csv', function(e) {
		e.preventDefault();
		e.stopPropagation();
		if (confirm('<?=_l('are_you_sure?')?>')) {
			$el = $('#form-filter');
			let filters = [];
			$el.find('.input-filter').each(function() {
				filters.push($(this).attr('name') + '=' + $(this).val());
			});

			window.location = '<?= $action_csv ?>/1?' + filters.join('&');
		}
	});

	$(document).on('click', '#btn-export-pdf', function(e) {
		e.preventDefault();
		e.stopPropagation();
		if (confirm('<?=_l('are_you_sure?')?>')) {
			$el = $('#form-filter');
			let filters = [];
			$el.find('.input-filter').each(function() {
				filters.push($(this).attr('name') + '=' + $(this).val());
			});

			window.location = '<?= $action_zip ?>/1?' + filters.join('&');
		}
	});
});
</script>
