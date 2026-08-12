<?php

	$demo_schedule_lead = 0;
	$demo_not_completed_lead = 0;
	$registered_students = 0;
	$enrolled_lead = 0;
	$today_lead = 0;
	$total_lead = 0;

	if (isset($lead_result)) {
		$total_lead = count($lead_result);
		foreach ($lead_result as $value) {
			if ($value['status'] == 1) {
				$demo_schedule_lead++;
			}
			if (!empty($value['student_id'])) {
				$registered_students++;
			}
			if ($value['status'] == 3) {
				$demo_not_completed_lead++;
			}
			if ($value['status'] == 4) {
				$enrolled_lead++;
			}
			if (date('Y-m-d', strtotime($value['date_added'])) == date('Y-m-d')) {
				$today_lead++;
			}
		}
	}
?>
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('dashboard'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">

				<h4 class="header-title mb-4"><?php echo _l('total_leads'); ?></h4>

				<div class="mt-3 chartjs-chart" style="height: 320px;">
					<canvas id="lead-area-chart"></canvas>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-12">
		<div class="card widget-inline">
			<div class="card-body p-0">
				<div class="row no-gutters">
					<div class="col-sm-6 col-xl-3">
						<div class="card shadow-none m-0">
							<div class="card-body text-center">
								<i class="dripicons-archive text-muted" style="font-size: 24px;"></i>
								<h3><span><?php echo @$total_lead; ?></span></h3>
								<p class="text-muted font-15 mb-0"><?php echo _l('number_leads'); ?></p>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-xl-3">
						<div class="card shadow-none m-0 border-left">
							<div class="card-body text-center">
								<i class="dripicons-network-3 text-muted" style="font-size: 24px;"></i>
								<h3><span><?php echo @$registered_students; ?></span></h3>
								<p class="text-muted font-15 mb-0"><?php echo _l('registered_students'); ?></p>
							</div>
						</div>
					</div>


					<div class="col-sm-6 col-xl-3">
						<div class="card shadow-none m-0 border-left">
							<div class="card-body text-center">
								<i class="dripicons-camcorder text-muted" style="font-size: 24px;"></i>
								<h3><span><?php echo $demo_schedule_lead; ?></span></h3>
								<p class="text-muted font-15 mb-0"><?php echo _l('demo_scheduled'); ?></p>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-xl-3">
						<div class="card shadow-none m-0 border-left">
							<div class="card-body text-center">
								<i class="dripicons-user-group text-muted" style="font-size: 24px;"></i>
								<h3><span><?php echo $enrolled_lead; ?></span></h3>
								<p class="text-muted font-15 mb-0"><?php echo _l('enrolled'); ?></p>
							</div>
						</div>
					</div>

				</div> <!-- end row -->
			</div>
		</div> <!-- end card-box-->
	</div> <!-- end col-->
</div>
<div class="row">
	<div class="col-xl-4">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-4"><?php echo _l('lead_overview'); ?></h4>
				<div class="my-4 chartjs-chart" style="height: 202px;">
					<canvas id="lead-status-chart"></canvas>
				</div>
				<div class="row text-center mt-2 py-2">
					<div class="col-6">
						<i class="mdi mdi-trending-up text-default mt-3 h3"></i>
						<h3 class="font-weight-normal">
							<span><?php echo @$total_lead; ?></span>
						</h3>
						<p class="text-muted mb-0"><?php echo _l('number_leads'); ?></p>
					</div>
					<div class="col-6">
						<i class="mdi mdi-trending-down text-warning mt-3 h3"></i>
						<h3 class="font-weight-normal">
							<span><?php echo $demo_schedule_lead; ?></span>
						</h3>
						<p class="text-muted mb-0"> <?php echo _l('demo_scheduled'); ?></p>
					</div>
				</div>
				<div class="row text-center mt-2 py-2">
					<div class="col-6">
						<i class="mdi mdi-trending-up text-success mt-3 h3"></i>
						<h3 class="font-weight-normal">
							<span><?php echo @$registered_students; ?></span>
						</h3>
						<p class="text-muted mb-0"><?php echo _l('demo_completed'); ?></p>
					</div>
					<div class="col-6">
						<i class="mdi mdi-trending-down text-danger mt-3 h3"></i>
						<h3 class="font-weight-normal">
							<span><?php echo $demo_not_completed_lead; ?></span>
						</h3>
						<p class="text-muted mb-0"> <?php echo _l('demo_not_completed'); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-8">
		<div class="card" id = 'unpaid-instructor-revenue'>
			<div class="card-body">
				<h4 class="header-title mb-3"><?php echo _l(''); ?>
				</h4>
				<div class="table-responsive">
				</div>
			</div>
		</div>
	</div>
</div>
