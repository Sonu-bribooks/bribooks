<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo site_url('admin/center_form/add'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add_center'); ?></a>
			</h4>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo _l('centers'); ?></h4>
				<div class="table-responsive-sm mt-4">
				<table id="basic-datatable" class="table table-striped table-centered mb-0">
					<thead>
					<tr>
						<th>#</th>
						<th><?php echo _l('name'); ?></th>
						<th><?php echo _l('city'); ?></th>
						<th><?php echo _l('classes'); ?></th>
						<th><?php echo _l('enrolled'); ?></th>
						<th><?php echo _l('date_added'); ?></th>
						<th><?php echo _l('actions'); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php
							foreach ($centers->result_array() as $key => $center): ?>
							<tr>
								<td><?php echo $key+1; ?></td>
								<td><?php echo $center['name']; ?></td>
								<td><?php echo $center['city']; ?></td>
								<td><?php echo $this->class_model->get_all(['center_id' => $center['id']])->num_rows(); ?></td>
								<td><?php echo $this->center_model->getEnrolledStudentsByCenterId($center['id'])->num_rows(); ?></td>
								<td><?php echo $center['date_added']; ?></td>
								<td>
									<div class="dropright dropright">
									<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="mdi mdi-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu">
										<li><a class="dropdown-item" href="<?php echo site_url('admin/center_form/edit/'.$center['id']) ?>"><?php echo _l('edit'); ?></a></li>
										<li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/centers/delete/'.$center['id']); ?>');"><?php echo _l('delete'); ?></a></li>
									</ul>
								</div>
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
