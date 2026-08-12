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
	<div class="col-12">
		<div class="card widget-inline">
			<div class="card-body p-0">
				<div class="row no-gutters">
					<div class="col-sm-6 col-xl-3">
						<a href="#" class="text-secondary">
							<div class="card shadow-none m-0 border-left">
								<div class="card-body text-center">
									<i class="dripicons-link-broken text-muted" style="font-size: 24px;"></i>
									<h3><span><?php echo currency($today_revenue); ?></span></h3>
									<p class="text-muted font-15 mb-0"><?php echo _l('today'); ?></p>
								</div>
							</div>
						</a>
					</div>

					<div class="col-sm-6 col-xl-3">
						<a href="#" class="text-secondary">
							<div class="card shadow-none m-0 border-left">
								<div class="card-body text-center">
									<i class="dripicons-star text-muted" style="font-size: 24px;"></i>
									<h3><span><?php echo currency($last_week_revenue); ?></span></h3>
									<p class="text-muted font-15 mb-0"><?php echo _l('last_7_days'); ?></p>
								</div>
							</div>
						</a>
					</div>

					<div class="col-sm-6 col-xl-3">
						<a href="#" class="text-secondary">
							<div class="card shadow-none  m-0 border-left">
								<div class="card-body text-center">
									<i class="dripicons-link text-muted" style="font-size: 24px;"></i>
									<h3><span><?php echo currency($last_month_revenue); ?></span></h3>
									<p class="text-muted font-15 mb-0"><?php echo _l('last_30_days'); ?></p>
								</div>
							</div>
						</a>
					</div>

					<div class="col-sm-6 col-xl-3">
						<a href="#" class="text-secondary">
							<div class="card shadow-none m-0 border-left">
								<div class="card-body text-center">
									<i class="dripicons-link-broken text-muted" style="font-size: 24px;"></i>
									<h3><span><?php echo currency($last_year_revenue); ?></span></h3>
									<p class="text-muted font-15 mb-0"><?php echo _l('last_one_year'); ?></p>
								</div>
							</div>
						</a>
					</div>

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

				<div class="row justify-content-md-center">
					<div class="col-xl-6">
						<form class="form-inline" action="<?php echo $action_filter; ?>" method="get">
							<div class="col-xl-10">
								<div class="form-group">
									<div id="reportrange" class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"  data-cancel-class="btn-light" style="width: 100%;">
										<i class="mdi mdi-calendar"></i>&nbsp;
										<span id="selectedValue">
											<?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y" , strtotime('-1 day', $timestamp_end));?>
										</span> <i class="mdi mdi-menu-down"></i>
									</div>
									<input
										id="date_range"
										type="hidden"
										name="date_range"
										value="<?php echo date("d F, Y" , $timestamp_start) . " - " . date("d F, Y" , strtotime('-1 day', $timestamp_end));?>"
									>
								</div>
							</div>
							<div class="col-xl-2">
								<button type="submit" class="btn btn-info" id="submit-button" onclick="update_date_range();"> <?php echo _l('filter');?></button>
							</div>
						</form>
					</div>
				</div>

				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
						<tr>
							<th>#</th>
							<th><?php echo _l('id'); ?></th>
							<th><?php echo _l('name'); ?></th>
							<th><?php echo _l('email'); ?></th>
							<th><?php echo _l('mobile'); ?></th>
							<th><?php echo _l('amount'); ?></th>
							<th><?php echo _l('provider'); ?></th>
							<th><?php echo _l('status'); ?></th>
							<th><?php echo _l('date_added'); ?></th>
							<th><?php echo _l('actions'); ?></th>
						</tr>
						</thead>
					</table>
				</div>

				<div class="card">
					<div class="card-body">
						<div class="row">
							<p class="col text-right border-right"><b><?php _el('total_revenue_for'); ?></b> <br><i><?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y" , strtotime('-1 day', $timestamp_end));?></i></p>
							<h2 class="col"><?php echo currency($total); ?></h2>
						</div>
					</div>
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
			'amount',
			'provider',
			'date_added',
			'status',
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

<script type="text/javascript">
function update_date_range()
{
	var x = $("#selectedValue").html();
	$("#date_range").val(x);
}
</script>
