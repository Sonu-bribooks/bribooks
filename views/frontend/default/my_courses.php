<?php
$enrols = $this->enrol_model->getAll([
	'user_id'	=> $this->session->user_id,
	'no_payment'=> true,
	'archived'	=> 0,
	'status'	=> 1
]);

foreach ($enrols as &$enrol) {
	if (strtotime($enrol['renewal_date'] . ' -1 week') < time()) {
		$enrol['renewal'] 		= true;
	}

	if (strtotime($enrol['renewal_date']) < time()) {
		$enrol['expired'] 		= true;
	}
}
?>
<style>

	.re_schedule > a {
		width: -webkit-fill-available;
	}
</style>

<section class="page-header-area my-course-area">
	<div class="container">
		<div class="row">
			<div class="col">
				<h1 class="page-title"><?php echo _l('welcome'); ?> <?php echo $this->session->name; ?></h1>
				<br />
			</div>
		</div>
	</div>
</section>

<section class="my-courses-area">
	<div class="container" style="min-height: 600px;">
		<div class="text-right">
			<a href="<?php echo site_url('home/about_us'); ?>" class="btn btn-inverse" style="color:#fff; border-color: #727cf5;background-color: #727cf5;margin-right: 10px;" target="_blank"><?php echo _l('download_lms'); ?></a>
			<a href="<?php echo site_url('home/payment'); ?>" class="btn btn-inverse"><?php _el('payment_logs'); ?></a>
			<br />
			<br />
		</div>

		<div class="row">
			<div class="col">
				<ul class="purchase-history-list">
					<li class="purchase-history-list-header">
						<div class="row">
							<div class="col-sm-3"><h4 class="purchase-history-list-title"> <?php echo _l('enrolled_courses'); ?> </h4></div>
							<div class="col-sm-9 hidden-xxs hidden-xs">
								<div class="row">
									<div class="col-sm-2"><b><?php echo _l('mode'); ?></b></div>
									<div class="col-sm-4"><b><?php echo _l('center'); ?></b></div>
									<div class="col-sm-2"><b><?php echo _l('enrolment_date'); ?></b></div>
									<div class="col-sm-2"><b><?php echo _l('renewal_date'); ?></b></div>
									<div class="col-sm-2"><b><?php echo _l('certificate'); ?></b></div>
								</div>
							</div>
						</div>
					</li>
					<?php if ($enrols):
						foreach($enrols as $enrol_i):
					?>
					<li class="purchase-history-items mb-2">
						<div class="row">
							<div class="col-sm-3">
								<a class="purchase-history-course-title" href="#" >
									<?php echo $enrol_i['course']; ?>
								</a>

								<?php if (!empty($enrol_i['expired'])) { ?>
								<span class="badge badge-danger"><?php _el('expired'); ?></span>
								<?php } elseif (!empty($enrol_i['renewal'])) { ?>
								<span class="badge badge-warning"><?php _el('expiring_soon'); ?></span>
								<?php } else { ?>
								<?php echo _cs($enrol_i['status']); ?>
								<?php } ?>

								<div class="progress" style="height: 7px;">
									<div class="progress-bar progress-bar-striped bg-info progress-bar-animated" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:40%"></div>
								</div>
							</div>
							<div class="col-sm-9 purchase-history-detail">
								<div class="row">
									<div class="col-sm-2 date">
										<?php _el($enrol_i['mode']); ?>

										<?php if ($enrol_i['mode'] == 'online') { ?>
										<a href="<?php echo $this->schedule_model->getScheduleLink(['enrol_id' => $enrol_i['id']]); ?>" class="btn btn-sm btn-receipt" target="_blank"><?php echo _l('live_learning'); ?></a>
										<?php } else { ?>
										<a href="<?php echo $this->student_model->getLmsLink(['student_id' => $enrol_i['user_id']]); ?>" class="btn btn-sm btn-receipt" style="color:#fff; border-color: #0c801b;background-color: #0c801b;" target="_blank"><?php echo _l('login_to_lms'); ?></a>
										<?php } ?>
									</div>
									<div class="col-sm-4 date">
										<?php if ($enrol_i['mode'] == 'offline') { ?>
										<?php echo ($center_info = $this->enrol_model->getCenter($enrol_i['id'])) ? $center_info['center'] : ''; ?>
										<?php } ?>
									</div>
									<div class="col-sm-2 date">
										<?php echo date('F j, Y', $enrol_i['date_added']); ?>
									</div>
									<div class="col-sm-2 price">
										<?php echo date('F j, Y', strtotime($enrol_i['renewal_date'])); ?>
									</div>
									<div class="col-sm-2">
										<a href="#" class="btn btn-block btn-sm"><i class="fa fa-download"></i> <?php echo _l('download'); ?></a>
									</div>
								</div>
							</div>
						</div>
					</li>
					<?php endforeach; ?>
					<?php else: ?>
					<li>
						<p class="text-center">
							<?php echo _l('no_records_found'); ?>
						</p>
					</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>

	</div>
</section>
