<?php
$this->db->select_sum('amount');
$this->db->where('date_added >=' , strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-1 year')))));
$this->db->where('date_added <=' , strtotime(date('Y-m-d', strtotime('+1 day'))));
$this->db->where('site_id', (int)$this->config->item('site_id'));
$this->db->order_by('date_added' , 'DESC');
$last_year_revenue = $this->db->get('payment')->row()->amount;

$this->db->select_sum('amount');
$this->db->where('date_added >=' , strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-1 month')))));
$this->db->where('date_added <=' , strtotime(date('Y-m-d', strtotime('+1 day'))));
$this->db->where('site_id', (int)$this->config->item('site_id'));
$this->db->order_by('date_added' , 'DESC');
$last_month_revenue = $this->db->get('payment')->row()->amount;

$this->db->select_sum('amount');
$this->db->where('date_added >' , strtotime('+1 day', strtotime(date('Y-m-d', strtotime('-1 week')))));
$this->db->where('date_added <=' , strtotime(date('Y-m-d', strtotime('+1 day'))));
$this->db->where('site_id', (int)$this->config->item('site_id'));
$this->db->order_by('date_added' , 'DESC');
$last_week_revenue = $this->db->get('payment')->row()->amount;

$this->db->select_sum('amount');
$this->db->where('date_added >' , strtotime(date('Y-m-d', strtotime('-0 day'))));
$this->db->where('date_added <=' , strtotime(date('Y-m-d', strtotime('+1 day'))));
$this->db->where('site_id', (int)$this->config->item('site_id'));
$this->db->order_by('date_added' , 'DESC');
$today_revenue = $this->db->get('payment')->row()->amount;

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
				<h4 class="mb-3 header-title"><?php echo _l('revenue'); ?></h4>
				<div class="row justify-content-md-center">
					<div class="col-xl-6">
						<form class="form-inline" action="<?php echo site_url('portal/revenue/filter_by_date_range') ?>" method="get">
							<div class="col-xl-10">
								<div class="form-group">
									<div id="reportrange" class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"  data-cancel-class="btn-light" style="width: 100%;">
										<i class="mdi mdi-calendar"></i>&nbsp;
										<span id="selectedValue"><?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y" , strtotime('-1 day', $timestamp_end));?></span> <i class="mdi mdi-menu-down"></i>
									</div>
									<input id="date_range" type="hidden" name="date_range" value="<?php echo date("d F, Y" , $timestamp_start) . " - " . date("d F, Y" , strtotime('-1 day', $timestamp_end));?>">
								</div>
							</div>
							<div class="col-xl-2">
								<button type="submit" class="btn btn-info" id="submit-button" onclick="update_date_range();"> <?php echo _l('filter');?></button>
							</div>
						</form>
					</div>
				</div>

				<div class="form-group row mb-3">
					<label class="col-md-9 col-form-label text-right" for="site_id"><?php echo _l('select_site'); ?> </label>
					<div class="col-md-3">
						<select class="form-control select2" data-toggle="select2" onchange="window.location='<?=$action_filter?>?site_id=' + this.value">
							<?php foreach ($sites as $site) {
								if ($site_id == $site['id']) {
							?>
							<option value="<?php echo $site['id']; ?>" selected><?php echo $site['name']; ?></option>
							<?php } else { ?>
							<option value="<?php echo $site['id']; ?>"><?php echo $site['name']; ?></option>
							<?php } } ?>
						</select>
					</div>
				</div>

				<div class="table-responsive-sm mt-4">
					<table id="basic-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th>#</th>
								<th><?php echo _l('student'); ?></th>
								<th><?php echo _l('enrolled_plan'); ?></th>
								<th><?php echo _l('total_amount'); ?></th>
								<th><?php echo _l('date'); ?></th>
								<th><?php echo _l('payment_type'); ?></th>
								<th><?php echo _l('actions'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php $total = 0; ?>
							<?php foreach ($results as $key => $payment):
								$user_info = $this->student_model->get($payment['user_id']);
								$subscription_plan_info = $this->subscription_plan_model->get($payment['subscription_plan_id']);
								$total += $payment['amount'];
							?>
								<tr class="gradeU">
									<td><?php echo $key + 1; ?></td>
									<td><?php echo $user_info['first_name'] . ' ' . $user_info['last_name']; ?></td>
									<td><?php echo ellipsis($subscription_plan_info['title']); ?></td>
									<td><?php echo currency($payment['amount']); ?></td>
									<td><?php echo date('D, d-M-Y', $payment['date_added']); ?></td>
									<td><?php echo $payment['payment_type']; ?></td>
									<td>
										<div class="dropright dropright">
										<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<i class="mdi mdi-dots-vertical"></i>
										</button>
										<ul class="dropdown-menu">
											<li><a class="dropdown-item" target="_blank" href="<?php echo site_url('home/invoice/' . $payment['order_id']) ?>"><?php echo _l('check_invoice'); ?></a></li>
										</ul>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<div class="row">
					<p class="col text-right border-right"><b><?php _el('total_revenue_for'); ?></b> <br><i><?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y" , strtotime('-1 day', $timestamp_end));?></i></p>
					<h2 class="col"><?php echo currency($total); ?></h2>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
function update_date_range()
{
	var x = $("#selectedValue").html();
	$("#date_range").val(x);
}
</script>
