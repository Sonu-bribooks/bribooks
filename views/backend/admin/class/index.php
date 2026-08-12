<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo site_url('admin/class_form/add'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add_class'); ?></a>
			</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo _l('classes'); ?></h4>
				<div class="table-responsive-sm mt-4">
				<table id="basic-datatable" class="table table-striped table-centered mb-0">
					<thead>
					<tr>
						<th>#</th>
						<th><?php echo _l('name'); ?></th>
						<th><?php echo _l('mode'); ?></th>
						<th><?php echo _l('slot'); ?></th>
						<th><?php echo _l('center'); ?></th>
						<th><?php echo _l('city'); ?></th>
						<th><?php echo _l('course'); ?></th>
						<th><?php echo _l('teacher'); ?></th>
						<th><?php echo _l('is_demo'); ?></th>
						<th><?php echo _l('status'); ?></th>
						<th><?php echo _l('actions'); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php
						 foreach ($classes->result_array() as $key => $class): ?>
							<tr>
								<td><?php echo $class['id']; ?></td>
								<td>
									<?php echo $class['name']; ?>
									<?php if (($count = count($this->class_model->get_all_students($class['id']))) > 0) { ?>
									<span class="badge badge-info"><?php _el('students'); ?>: <?php echo $count; ?></span>
									<?php } else { ?>
									<span class="badge badge-warning"><?php _el('no_students'); ?></span>
									<?php } ?>
								</td>
								<td><?php echo $class['mode']; ?></td>
								<td><?php echo $class['slot']; ?></td>
								<td><?php echo $class['mode'] == 'offline' ? $class['center'] : ''; ?></td>
								<td><?php echo $class['mode'] == 'offline' ? $this->city_model->get($class['city_id'])['name'] : ''; ?></td>
								<td><?php echo $class['course']; ?></td>
								<td><?php echo $class['teacher']; ?></td>
								<td><?php echo $class['is_demo'] ? '<span class="badge badge-info">' . _l('demo') . '<span>' : ''; ?></td>
								<td>
									<?php if (!$class['status']): ?>
										<i class="mdi mdi-circle" style="color: #FFC107; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo _l('disabled'); ?>"></i>
									<?php else:?>
										<i class="mdi mdi-circle" style="color: #4CAF50; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo _l('enabled'); ?>"></i>
									<?php endif; ?>
								</td>
								<td>
									<div class="dropright dropright">
									<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="mdi mdi-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu">
										<li><a class="dropdown-item" href="<?php echo site_url('admin/schedules') ?>?class_id=<?php echo $class['id']; ?>"><?php echo _l('check_schedules'); ?></a></li>
										<li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/classes/status/'.$class['id']); ?>');"><?php echo $class['status'] ? _l('mark_inactive') : _l('mark_active'); ?></a></li>
										<li><a class="dropdown-item" href="<?php echo site_url('admin/attendance/'.$class['id']) ?>"><?php echo _l('check_attendance'); ?></a></li>
										<li><a class="dropdown-item" href="<?php echo site_url('admin/class_form/edit/'.$class['id']) ?>"><?php echo _l('edit'); ?></a></li>
										<li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/classes/delete/'.$class['id']); ?>');"><?php echo _l('delete'); ?></a></li>
									</ul>
								</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
