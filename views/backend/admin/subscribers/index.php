<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                </h4>
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
							<th><?php echo _l('name'); ?></th>
							<th><?php echo _l('email'); ?></th>
							<th><?php echo _l('mobile'); ?></th>
							<th><?php echo _l('plan_name'); ?></th>
							<th><?php echo _l('plan_price'); ?></th>
							<th><?php echo _l('paid_amount'); ?></th>
                            <th><?php echo _l('start_date'); ?></th>
							<th><?php echo _l('end_date'); ?></th>
							<th><?php echo _l('status'); ?></th>
							<th><?php echo _l('paid_on'); ?></th>
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
			'email',
			'mobile',
			'plan_name',
			'plan_price',
            'paid_amount',
			'start_date',
			'end_date',
			'status',
            'paid_on',
		],
		'actions'	=> [
			[
				'key' 		=> 'edit',
				'url' 		=> '#',
			],
			[
				'key' 		=> 'status',
				'type' 		=> 'status',
				'url' 		=> '#',
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
