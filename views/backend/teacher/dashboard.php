<?php
	$reassigned_schedules = array_map(function($item) {
		return $item['schedule_id'];
	}, $this->schedule_model->getReassignedSchedules(['original_teacher_id' => $this->session->user_id]));

	$total_classes = $this->class_model->get_all([
		'teacher_id'	=> $this->session->user_id,
		'status'		=> 1,
	])->num_rows();

	$online_classes = $this->class_model->get_all([
		'teacher_id'	=> $this->session->user_id,
		'mode'			=> 'online',
		'status'		=> 1,
	])->num_rows();

	$offline_classes = $this->class_model->get_all([
		'teacher_id'	=> $this->session->user_id,
		'mode'			=> 'offline',
		'status'		=> 1,
	])->num_rows();

	$today_online_classes = $this->schedule_model->get_all([
		'teacher_id'	=> $this->session->user_id,
		'mode'			=> 'online',
		'status'		=> 1,
		'date_start'	=> date('Y-m-d'),
		'date_end'		=> date('Y-m-d', strtotime('+1 day')),
	])->result_array();

	$today_offline_classes = $this->schedule_model->get_all([
		'teacher_id'	=> $this->session->user_id,
		'mode'			=> 'offline',
		'status'		=> 1,
		'date_start'	=> date('Y-m-d'),
		'date_end'		=> date('Y-m-d', strtotime('+1 day')),
	])->result_array();
?>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('dashboard'); ?></h4>
				<?php if (0) { ?>
				<a href="<?php echo $this->teacher_model->getLmsLink(['teacher_id' => $this->session->userdata('user_id')]); ?>" class="btn btn-sm btn-receipt float-right" style="color:#fff; border-color: #0c801b;background-color: #0c801b;" target="_blank"><?php echo _l('login_to_lms'); ?></a>
				<a href="<?php echo site_url('home/about_us'); ?>" class="btn btn-sm btn-receipt float-right" style="color:#fff; border-color: #727cf5;background-color: #727cf5;margin-right: 10px;" target="_blank"><?php echo _l('download_lms'); ?></a>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-12">
		<div class="card widget-inline">
			<div class="card-body p-0">
				<div class="row no-gutters">
					<div class="col-sm-6 col-xl-4">
						<div class="card shadow-none m-0">
							<div class="card-body text-center">
								<i class="dripicons-archive text-muted" style="font-size: 24px;"></i>
								<h3><span><?php echo $total_classes; ?></span></h3>
								<p class="text-muted font-15 mb-0"><?php echo _l('total_classes'); ?></p>
							</div>
						</div>
					</div>

					<div class="col-sm-6 col-xl-4">
						<div class="card shadow-none m-0 border-left">
							<div class="card-body text-center">
								<i class="dripicons-network-3 text-muted" style="font-size: 24px;"></i>
								<h3><span><?php echo $online_classes; ?></span></h3>
								<p class="text-muted font-15 mb-0"><?php echo _l('online_classes'); ?></p>
							</div>
						</div>
					</div>


					<div class="col-sm-6 col-xl-4">
						<div class="card shadow-none m-0 border-left">
							<div class="card-body text-center">
								<i class="dripicons-camcorder text-muted" style="font-size: 24px;"></i>
								<h3><span><?php echo $offline_classes; ?></span></h3>
								<p class="text-muted font-15 mb-0"><?php echo _l('offline_classes'); ?></p>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-5">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-3"><?php echo _l('today_online_class'); ?></h4>
				<div class="table-responsive">
					<table class="table table-centered table-hover mb-0">
						<tbody>

							<?php
								foreach ($today_online_classes as $key => $row):
									if (in_array($row['id'], $reassigned_schedules)) continue;
							?>
							<tr>
								<td>
									<h5 class="font-14 my-1"><?php echo _l('course'); ?></h5>
									<span class="text-muted font-13"><?php echo $row['course']; ?></span>

									<?php $count = count($this->class_model->get_all_students($row['class_id'])) ?>

									<?php if ($count === 0) {
										$count = count($this->lead_model->getDemoStudents($row['id']));
									} ?>

									<?php if ($count > 0) { ?>
									<span class="badge badge-info"><?php _el('students'); ?>: <?php echo $count; ?></span>
									<?php } else { ?>
									<span class="badge badge-warning"><?php _el('no_students'); ?></span>
									<?php } ?>

									<?php echo $row['is_demo'] ? '<span class="badge badge-info">' . _l('demo') . '<span>' : ''; ?>
								</td>
								<td>
									<h5 class="font-14 my-1"><?php echo _l('schedule'); ?></h5>
									<small><span class="text-muted font-13"><?php echo @array_pop(explode(' ', $row['schedule'])); ?></span></small>
								</td>
								<td>
									<a href="<?php echo site_url('teacher/attendance/' . $row['id']); ?>" class="btn btn-primary"><?php _el('attend') ; ?></a>
								</td>
							</tr>
							<?php endforeach; ?>

						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-3"><?php echo _l('today_offline_class'); ?></h4>
				<div class="table-responsive">
					<table class="table table-centered table-hover mb-0">
						<tbody>

							<?php
								foreach ($today_offline_classes as $key => $row):
									if (in_array($row['id'], $reassigned_schedules)) continue;
									$center_info = $this->center_model->get($row['center_id'])->row_array();
							?>
							<tr>
								<td>
									<h5 class="font-14 my-1"><?php echo _l('course'); ?></h5>
									<span class="text-muted font-13"><?php echo $row['course']; ?></span>

									<?php $count = count($this->class_model->get_all_students($row['class_id'])) ?>

									<?php if ($count === 0) {
										$count = count($this->lead_model->getDemoStudents($row['id']));
									} ?>

									<?php if ($count > 0) { ?>
									<span class="badge badge-info"><?php _el('students'); ?>: <?php echo $count; ?></span>
									<?php } else { ?>
									<span class="badge badge-warning"><?php _el('no_students'); ?></span>
									<?php } ?>

									<?php echo $row['is_demo'] ? '<span class="badge badge-info">' . _l('demo') . '<span>' : ''; ?>
								</td>
								<td>
									<h5 class="font-14 my-1"><?php echo _l('center'); ?></h5>
									<small><span class="text-muted font-13"><?php echo $center_info['name']; ?></span></small>
								</td>
								<td>
									<h5 class="font-14 my-1"><?php echo _l('schedule'); ?></h5>
									<small><span class="text-muted font-13"><?php echo @array_pop(explode(' ', $row['schedule'])); ?></span></small>
								</td>
								<td>
									<a href="<?php echo site_url('teacher/attendance/' . $row['id']); ?>" class="btn btn-primary"><?php _el('attend') ; ?></a>
								</td>
							</tr>
							<?php endforeach; ?>

						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

</div>
