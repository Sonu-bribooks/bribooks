<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo site_url('admin/cluster_admin_form/add_cluster_admin_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add_cluster_admin'); ?></a>
			</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo _l('cluster_admin'); ?></h4>
				<div class="table-responsive-sm mt-4">
				<table id="basic-datatable" class="table table-striped table-centered mb-0">
					<thead>
					<tr>
						<th>#</th>
						<th><?php echo _l('photo'); ?></th>
						<th><?php echo _l('name'); ?></th>
						<th><?php echo _l('email'); ?></th>
						<th><?php echo _l('mobile'); ?></th>
						<th><?php echo _l('actions'); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php
						 foreach ($cluster_admin->result_array() as $key => $programadmin): ?>
							<tr>
								<td><?php echo $key+1; ?></td>
								<td>
									<img src="<?php echo $this->user_model->get_user_image_url($programadmin['id']);?>" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail">
								</td>
								<td><?php echo $programadmin['first_name'].' '.$programadmin['last_name']; ?></td>
								<td><?php echo $programadmin['email']; ?></td>
								<td><?php echo $programadmin['mobile']; ?></td>
								<td>
									<div class="dropright dropright">
									<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="mdi mdi-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu">
										<li><a class="dropdown-item" href="<?php echo site_url('admin/cluster_admin_form/edit_cluster_admin_form/'.$programadmin['id']) ?>"><?php echo _l('edit'); ?></a></li>
										<li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/cluster_admin/delete/'.$programadmin['id']); ?>');"><?php echo _l('delete'); ?></a></li>
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
