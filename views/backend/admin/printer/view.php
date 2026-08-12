<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
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
								<th><?php echo _l('status'); ?></th>
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
	$(function() {
		let columns = JSON.parse(atob('<?php echo _render_column([
			'keys' 		=> [
				'sn',
				'id',
				'name',
				'status',
				'date_added',
			],
			'actions'	=> [
				[
					'key' 		=> 'edit',
					'url' 		=> 'admin/printer_form/edit/',
				],
				[
					'key' 		=> 'status',
					'type' 		=> 'status',
					'url' 		=> 'admin/printers/status/',
				],
				[
					'key' 		=> 'delete',
					'type' 		=> 'confirm',
					'url' 		=> 'admin/printers/delete/',
				],
				[
					'key' 		=> 'costing',
					'type' 		=> 'costing',
					'url' 		=> 'admin/printer_costing/edit/',
				],
			]
		]); ?>'));

		const action = columns.pop()
		const callback = eval(action.render)
		columns.push({
			"data": "actions",
			render: callback
		});

		$('#ajax-datatable').DataTable({
			"ajax": "<?php echo $action_ajax; ?>",
			"processing": true,
			"serverSide": true,
			"order": [
				[0, "desc"]
			],
			"columns": columns,
		})
	});
</script>