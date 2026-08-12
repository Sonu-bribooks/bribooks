<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo $action_add; ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add'); ?></a>
			</h4>
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
							<th><?php echo _l('name'); ?></th>
							<th><?php echo _l('type'); ?></th>
							<th><?php echo _l('stock'); ?></th>
							<th><?php echo _l('trigger_copies'); ?></th>
							<th><?php echo _l('price'); ?></th>
							<th><?php echo _l('weight'); ?></th>
							<th><?php echo _l('min_published'); ?></th>
							<th><?php echo _l('max_published'); ?></th>
							<th><?php echo _l('status'); ?></th>
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
			'name',
			'type',
			'quantity',
			'sold',
			'price',
			'weight',
			'min_published',
			'max_published',
			'status',
			'date_modified',
		],
		'actions'	=> [
			[
				'key' 		=> 'edit',
				'url' 		=> 'admin/medallion_form/edit/',
			],
			[
				'key' 		=> 'status',
				'type' 		=> 'status',
				'url' 		=> 'admin/medallion/status/',
			],
			[
				'key' 		=> 'delete',
				'type' 		=> 'confirm',
				'url' 		=> 'admin/medallion/delete/',
			],
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
