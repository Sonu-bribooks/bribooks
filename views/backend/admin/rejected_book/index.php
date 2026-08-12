<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="card">
	<div class="card-body">
		<div class="col-xl-12 text-right">
			<button type="button" class="btn btn-info" id="btn-export"> <?php echo _l('export');?></button>
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
							<th><?php echo _l('book'); ?></th>
							<th><?php echo _l('version'); ?></th>
							<th><?php echo _l('option'); ?></th>
							<th><?php echo _l('quantity'); ?></th>
							<th><?php echo _l('printer'); ?></th>
							<th><?php echo _l('comment'); ?></th>
							<th><?php echo _l('status'); ?></th>
							<th><?php echo _l('date_added'); ?></th>
							<th><?php echo _l('date_modified'); ?></th>
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
$(function() {
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> [
			'sn',
			'id',
			'book',
			'version',
			'option',
			'quantity',
			'printer',
			'comment',
			'status',
			'date_added',
			'date_modified',
			'actions'
		]
	]); ?>'));

	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		"data": "actions",
		render: callback
	});

	$('#ajax-datatable').DataTable( {
		"ajax": "<?php echo $action_ajax; ?>",
		"processing": true,
		"serverSide": true,
		"order": [[ 0, "desc" ]],
		"columns": columns
	})
});
</script>

<script>
$(function() {
	$(document).on('click', '#btn-export', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$el = $('#form-filter');
		let filters = [];
		$el.find('.input-filter').each(function() {
			filters.push($(this).attr('name') + '=' + $(this).val());
		});

		window.location = '<?=base_url('admin/export_rejected_book/' . ($navigation == 'ge_nav' ? 2 : 47))?>?' + filters.join('&');
	})
});
</script>
