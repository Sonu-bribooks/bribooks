<style>
	.no-gutters { margin-right: 0; margin-left: 0; }
	.card { height: 100%; }
	.widget-inline .row { display: flex; flex-wrap: wrap; }
	.widget-inline .col-sm-6 { flex: 0 0 20%; max-width: 20%; }
</style>
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i>
					<?php echo $page_title; ?>
				</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body p-0">
				<div class="row no-gutters">
					<?php foreach ($event_types as $key => $value) { ?>
					<div class="col-sm-6 col-md-2 col-lg-2">
						<div class="card">
							<div class="card-body text-center">
								<i class="dripicons-information text-muted" style="font-size: 24px;"></i>
								<h5 class="my-2"><?php echo ucfirst($key); ?></h5>
								<p class="text-muted font-15 mb-0"><?php echo $value; ?></p>

								<?php if ($value > 0) { ?>
									<a href="/admin/download_campaign/<?php echo $key; ?>/<?php echo $campaign_id; ?>">
										<small class="text-success">
											<b>View In CSV</b>
										</small>
									</a>
								<?php } else { ?>
									<small class="text-muted">
										<b>View In CSV</b>
									</small>
								<?php } ?>
							</div>
						</div>
					</div>
					<?php } ?>
				</div> <!-- end row -->
			</div>
		</div> <!-- end card-box-->
	</div> <!-- end col-->
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
								<th><?php echo _l('email'); ?></th>
								<th><?php echo _l('campaign_name'); ?></th>
								<th><?php echo _l('event_type'); ?></th>
								<th><?php echo _l('bounce_type'); ?></th>
								<th><?php echo _l('timestamp'); ?></th>
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
		'keys' => [
			'sn',
			'id',
			'email',
			'campaign_name',
			'event_type',
			'bounce_type',
			'timestamp',
		],
	]); ?>'));

	if (columns.length > 0) {
		const action = columns.pop();
		if (action.render) {
			const callback = eval(action.render);
			action.render = callback;
		}
		columns.push(action);
	}

	$('#ajax-datatable').DataTable({
		'ajax': '<?php echo $action_ajax; ?>',
		'processing': true,
		'serverSide': true,
		'order': [[ 0, 'desc' ]],
		'columns': columns
	});
});
</script>
