<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo site_url('admin/telecaller_form/add_telecaller_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add_telecaller'); ?></a>
			</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo _l('telecallers'); ?></h4>
				<div class="table-responsive-sm mt-4">
				<table id="basic-datatable" class="table table-striped table-centered mb-0">
					<thead>
					<tr>
						<th>#</th>
						<th><?php echo _l('photo'); ?></th>
						<th><?php echo _l('name'); ?></th>
						<th><?php echo _l('email'); ?></th>
						<th><?php echo _l('mobile'); ?></th>
						<th><?php echo _l('status'); ?></th>
						<th><?php echo _l('actions'); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php
						 foreach ($telecallers as $key => $telecaller): ?>
							<tr>
								<td><?php echo $key+1; ?></td>
								<td>
									<img src="<?php echo $this->user_model->get_user_image_url($telecaller['id']);?>" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail" style="max-height: 50px;">
								</td>
								<td><?php echo $telecaller['first_name'].' '.$telecaller['last_name']; ?></td>
								<td><?php echo $telecaller['email']; ?></td>
								<td><?php echo $telecaller['mobile']; ?></td>
								<td>
									<?php if (!$telecaller['status']): ?>
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
										<li><a class="dropdown-item" href="<?php echo site_url('admin/telecaller_form/edit_telecaller_form/'.$telecaller['id']) ?>"><?php echo _l('edit'); ?></a></li>
										<li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/telecallers/status/'.$telecaller['id']); ?>');"><?php echo $telecaller['status'] ? _l('mark_inactive') : _l('mark_active'); ?></a></li>
										<li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/telecallers/delete/'.$telecaller['id']); ?>');"><?php echo _l('delete'); ?></a></li>
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
