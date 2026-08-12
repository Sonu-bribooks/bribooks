<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo site_url('lead/form/add'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add_form'); ?></a>
			</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo _l('lead_forms'); ?></h4>

				<div class="table-responsive-sm mt-4">
					<table id="basic-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th>#</th>
								<th><?php echo _l('name'); ?></th>
								<th><?php echo _l('seo'); ?></th>
								<th><?php echo _l('date_added'); ?></th>
								<th><?php echo _l('date_modified'); ?></th>
								<th><?php echo _l('actions'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($forms as $key => $form): ?>
							<tr>
								<td><?php echo $key+1; ?></td>
								<td><?php echo $form['name']; ?></td>
								<td><?php echo $form['seo']; ?></td>
								<td><?php echo $form['date_added']; ?></td>
								<td><?php echo $form['date_modified']; ?></td>
								<td>
									<div class="dropright dropright">
										<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<i class="mdi mdi-dots-vertical"></i>
										</button>
										<ul class="dropdown-menu">
											<li><a class="dropdown-item" href="<?php echo site_url('lead/form/edit/'.$form['form_id']) ?>"><?php echo _l('edit'); ?></a></li>
											<li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('lead/form/delete/'.$form['form_id']); ?>');"><?php echo _l('delete'); ?></a></li>
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
