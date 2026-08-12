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
</script>

<script>
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
				'url' 		=> 'printingPress/export_assignment_csv/',
			],
		]
	]); ?>'));

	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		"data": "actions",
		render: callback
	});

	table = $('#ajax-datatable').DataTable( {
		"ajax": "<?php echo $action_ajax; ?>",
		"processing": true,
		"serverSide": true,
		"order": [[ 0, "desc" ]],
		"columns": columns
	})
});
</script>

<script>
function update_date_range() {
	var x = $('.selectedValue').html();
	$('.date_range').val(x);
}
</script>

<script>
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

			window.location = '<?=base_url('printingPress/export_assignment_by_filter/')?>?' + filters.join('&');
		}
	})
});
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
</script>
