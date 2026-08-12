<?php
$printer_list = $this->student_model->get_by_role_id_in([12, 15]);
?>

<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
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
								<label><?=_l('order_date')?></label>
								<div class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"  data-cancel-class="btn-light" style="width: 100%;">
									<i class="mdi mdi-calendar"></i>&nbsp;
									<span id="selectedValue" class="selectedValue">
										<?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y" , $timestamp_end);?>
									</span> <i class="mdi mdi-menu-down"></i>
								</div>
								<input
									id="date_range1"
									type="hidden"
									name="date_range"
									class="input-filter date_range"
									value="<?php echo date("d F, Y" , $timestamp_start) . " - " . date("d F, Y" , $timestamp_end);?>"
								>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_printer')?></label>
								<select class="form-control input-filter select2" data-toggle="select2" name="printer_id" id="printer_id">
									<option value=""><?=_l('all')?></option>
									<?php foreach ($printer_list ?? [] as $key => $value) { ?>
										<option value="<?=$value['id']?>"><?=$value['first_name'] . ' ' . $value['last_name']?></option>
									<?php } ?>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4">
							<button type="button" class="btn btn-info" id="btn-export">
								<?php echo _l('export');?>
							</button>
							<button type="button" class="btn btn-info" id="btn-export-po-total">
								<?php echo 'Export PO Total Value';?>
							</button>
							<button type="button" class="btn btn-info" id="btn-export-printer-csv">
								<?php echo 'Export Printer CSV';?>
							</button>
							<button type="button" class="btn btn-info" id="btn-export-printer-po-month">
								<?php echo 'Export Printer PO Monthly';?>
							</button>

						</div>
						<div class="col-sm-8 text-right">
							<div class="btn-group">
								<button type="submit" class="btn btn-info" id="submit-button" onclick="update_date_range();"> <?php echo _l('search');?></button>
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
							<th><?php echo _l('printer'); ?></th>
							<th><?php echo _l('assignment_code'); ?></th>
							<th><?php echo _l('stats'); ?></th>
							<th><?php echo _l('assignment_date'); ?></th>
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

$(document).on('click', '.search', function(event) {
	event.preventDefault();
	var endDate = $('.end-date').val();
	var startDate = $('.start-date').val();

	table.ajax.url('<?= $action_ajax ?>?startdate=' + startDate + '&enddate=' + endDate).load();
});

$(function() {
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> [
			'sn',
			'id',
			'printer',
			'assignment_code',
			'stats',
			'assignment_date',
		],
		'actions'	=> [
			[
				'key' 		=> 'export_csv',
				'type' 		=> 'exportcsv',
				'url' 		=> 'admin/export_assignment_csv/',
			],
			[
				'key' 		=> 'export_printer_csv',
				'type' 		=> 'exportprintercsv',
				'url' 		=> 'admin/export_printer_csv_by_assignment/',
			],
			[
				'key' 		=> 'export_printer_po',
				'type' 		=> 'exportprinterpo',
				'url' 		=> 'admin/export_printer_csv_po_by_assignment/',
			],
		]
	]); ?>'));

	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		data: 'actions',
		render: callback
	});

	table = $('#ajax-datatable').DataTable( {
		ajax: "<?php echo $action_ajax; ?>",
		processing: true,
		serverSide: true,
		order: [[ 0, 'desc' ]],
		columns: columns
	})
});
</script>

<script>
function update_date_range() {
	var x = $('.selectedValue').html();
	$('.date_range').val(x);
}

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
	});

	$(document).on('click', '#btn-export', function(e) {
		e.preventDefault();
		e.stopPropagation();
		if (confirm('<?=_l('are_you_sure?')?>')) {
			$el = $('#form-filter');
			let filters = [];
			$el.find('.input-filter').each(function() {
				filters.push($(this).attr('name') + '=' + $(this).val());
			});

			window.location = '<?=base_url('admin/export_assignment_by_filter/')?>?' + filters.join('&');
		}
	});

	$(document).on('click', '#btn-export-po-total', function(e) {
		e.preventDefault();
		e.stopPropagation();

		if($('#printer_id').val() == '') {
			alert('Please select printer');
			return false;
		} else {
			if (confirm('<?=_l('are_you_sure?')?>')) {
				$el = $('#form-filter');
				let filters = [];
				$el.find('.input-filter').each(function() {
					filters.push($(this).attr('name') + '=' + $(this).val());
				});

				window.location = '<?=base_url('admin/export_printer_csv_po_total_by_printer_id/')?>?' + filters.join('&');
			}
		}
	});

	$(document).on('click', '#btn-export-printer-csv', function(e) {
		e.preventDefault();
		e.stopPropagation();

		if($('#printer_id').val() == '') {
			alert('Please select printer');
			return false;
		} else {
			if (confirm('<?=_l('are_you_sure?')?>')) {
				$el = $('#form-filter');
				let filters = [];
				$el.find('.input-filter').each(function() {
					filters.push($(this).attr('name') + '=' + $(this).val());
				});

				window.location = '<?=base_url('admin/export_printer_csv_by_printer_id/')?>?' + filters.join('&');
			}
		}
	});

	$(document).on('click', '#btn-export-printer-po-month', function(e) {
		e.preventDefault();
		e.stopPropagation();

		if($('#printer_id').val() == '') {
			alert('Please select printer');
			return false;
		} else {
			if (confirm('<?=_l('are_you_sure?')?>')) {
				$el = $('#form-filter');
				let filters = [];
				$el.find('.input-filter').each(function() {
					filters.push($(this).attr('name') + '=' + $(this).val());
				});

				window.location = '<?=base_url('admin/export_printer_csv_po_monthly/')?>?' + filters.join('&');
			}
		}
	});
});
</script>
