<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo $action_add; ?>" class="btn btn-outline-primary btn-rounded alignToTitle ml-2"><i class="mdi mdi-plus"></i><?php echo _l('add'); ?></a>
				<a href = "<?php echo $action_export; ?>" class="btn btn-outline-primary btn-rounded alignToTitle" id="btn-export"><i class="mdi mdi-download"></i><?php echo _l('Export'); ?></a>
			</h4>
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
						
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('award_status')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="ship_status"
								>
									<option value=""><?=_l('all')?></option>
									<option value="0"><?=_l('not_shipped')?></option>
									<option value="1"><?=_l('shipped')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('events')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									id="event_id"
									name="event_id"
								>
									<option value=""><?=_l('all')?></option>
									<?php foreach ($events as $event) { ?>
										<option value="<?= $event['id'] ?>"><?=$event['name'] . ' (' . $event['id'] . ')'?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4">
							<!-- <button type="button" class="btn btn-warning" id="btn-export"> <?php echo _l('export');?></button> -->
						</div>
						<div class="col-sm-8 text-right">
							<div class="btn-group">
								<button type="submit" class="btn btn-info" id="submit-button" > <?php echo _l('search');?></button>
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
							<?php foreach ($fields as $field) { ?>
								<th><?= _l($field) ?></th>
							<?php } ?>
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
		'keys' 		=> array_slice($fields, 0, count($fields) - 1),
		'actions'	=> $actions,
	]); ?>'));

	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		"data": "actions",
		render: callback
	});

	table  = $('#ajax-datatable').DataTable( {
		"ajax": "<?php echo $action_ajax; ?>",
		"processing": true,
		"serverSide": true,
		"order": [[ 0, "desc" ]],
		"columns": columns
	})
});


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

	$(document).on('click', '#btn-export', function(e) {
		e.preventDefault();
		e.stopPropagation();
		if (confirm('<?=_l('are_you_sure?')?>')) {
			$el = $('#form-filter');
			let filters = [];
			$el.find('.input-filter').each(function() {
				filters.push($(this).attr('name') + '=' + $(this).val());
			});

			window.location = '<?= base_url('admin/export_award_address?' ); ?>' + filters.join('&');
		}
	})
});
</script>
