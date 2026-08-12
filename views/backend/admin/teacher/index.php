<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo site_url('admin/teacher_form/add'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add_teacher'); ?></a>
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
				<div class="table-responsive-sm mt-4">
				<table id="basic-datatable" class="table table-striped table-centered mb-0">
					<thead>
					<tr>
						<th>#</th>
						<th><?php echo _l('photo'); ?></th>
						<th><?php echo _l('name'); ?></th>
						<th><?php echo _l('email'); ?></th>
						<th><?php echo _l('mobile'); ?></th>
						<th><?php echo _l('course'); ?></th>
						<th><?php echo _l('status'); ?></th>
						<th><?php echo _l('actions'); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php foreach ($teachers->result_array() as $key => $teacher): ?>
							<tr>
								<td><?php echo $key+1; ?></td>
								<td>
									<img src="<?php echo $this->teacher_model->get_image_url($teacher['id']);?>" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail">
								</td>
								<td><?php echo $teacher['first_name'].' '.$teacher['last_name']; ?></td>
								<td><?php echo $teacher['email']; ?></td>
								<td><?php echo $teacher['mobile']; ?></td>
								<td><?php echo $teacher['course']; ?></td>
								<td>
									<?php if (!$teacher['status']): ?>
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
											<li><a class="dropdown-item" href="<?php echo site_url('admin/teacher_form/edit/'.$teacher['id']) ?>"><?php echo _l('edit'); ?></a></li>
											<li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/teachers/status/'.$teacher['id']); ?>');"><?php echo $teacher['status'] ? _l('mark_inactive') : _l('mark_active'); ?></a></li>
											<li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/teachers/delete/'.$teacher['id']); ?>');"><?php echo _l('delete'); ?></a></li>
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
