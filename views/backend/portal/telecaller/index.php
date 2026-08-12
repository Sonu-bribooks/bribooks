<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<a href = "<?php echo site_url('portal/telecaller_form/add'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add_telecaller'); ?></a>
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
						 foreach ($results as $key => $result): ?>
							<tr>
								<td><?php echo $key+1; ?></td>
								<td>
									<img src="<?php echo $this->telecaller_model->get_image_url($result['id']);?>" alt="" height="50" width="50" class="img-fluid rounded-circle img-thumbnail">
								</td>
								<td><?php echo $result['first_name'].' '.$result['last_name']; ?></td>
								<td><?php echo $result['email']; ?></td>
								<td><?php echo $result['mobile']; ?></td>
								<td>
									<?php if (!$result['status']): ?>
										<i class="mdi mdi-circle" style="color: #FFC107; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo _l('disabled'); ?>"></i>
									<?php else:?>
										<i class="mdi mdi-circle" style="color: #4CAF50; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo _l('enabled'); ?>"></i>
									<?php endif; ?>
								</td>
								<td>
									<div class="dropright dropright">
										<button
											type="button"
											class="btn btn-sm btn-outline-primary btn-rounded btn-icon"
											data-toggle="dropdown"
											aria-haspopup="true"
											aria-expanded="false"
										>
											<i class="mdi mdi-dots-vertical"></i>
										</button>
										<ul class="dropdown-menu">
											<li>
												<a
													class="dropdown-item"
													href="<?php echo site_url('portal/telecaller_form/edit/' . $result['id']) ?>"
												><?php echo _l('edit'); ?></a>
											</li>
											<li>
												<a
													class="dropdown-item"
													href="#"
													onclick="confirm_modal('<?php echo site_url('portal/telecallers/status/' . $result['id']); ?>');"
												><?php echo $result['status'] ? _l('mark_inactive') : _l('mark_active'); ?></a>
											</li>
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
