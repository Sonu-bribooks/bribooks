<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo site_url('portal/site_form/add'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
					<i class="mdi mdi-plus"></i><?php echo _l('add_site'); ?>
				</a>
			</h4>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>
				<div class="table-responsive-sm mt-4">
				<table id="basic-datatable" class="table table-striped table-siteed mb-0">
					<thead>
					<tr>
						<th>#</th>
						<th><?php echo _li('School/Site ID'); ?></th>
						<th><?php echo _l('name'); ?></th>
						<th><?php echo _l('landing_code'); ?>/<?php echo _l('school_code'); ?></th>
						<th><?php echo _l('owner_mobile'); ?></th>
						<th><?php echo _l('owner_email'); ?></th>
						<th><?php echo _l('status'); ?></th>
						<th><?php echo _l('date_added'); ?></th>
						<th><?php echo _l('actions'); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php
							foreach ($sites as $key => $site): ?>
							<tr>
								<td><?php echo $key+1; ?></td>
								<td><?php echo $site['id']; ?></td>
								<td><?php echo $site['name']; ?></td>
								<td><?php echo $site['site_code']; ?></td>
								<td><?php echo $site['owner_mobile']; ?></td>
								<td><?php echo $site['owner_email']; ?></td>
								<td>
									<?php if (!$site['status']): ?>
										<i class="mdi mdi-circle" style="color: #FFC107; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo _l('disabled'); ?>"></i>
									<?php else:?>
										<i class="mdi mdi-circle" style="color: #4CAF50; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo _l('enabled'); ?>"></i>
									<?php endif; ?>
								</td>
								<td><?php echo $site['date_added']; ?></td>
								<td>
									<div class="dropright dropright">
									<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="mdi mdi-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu">
										<li>
											<a
												class="dropdown-item"
												href="<?php echo site_url('portal/site_form/edit/' . $site['id']) ?>"
											><?php echo _l('edit'); ?></a>
										</li>
										<li>
											<a
												class="dropdown-item"
												href="#"
												onclick="confirm_modal('<?php echo site_url('portal/sites/status/' . $site['id']); ?>');"
											><?php echo $site['status'] ? _l('mark_inactive') : _l('mark_active'); ?></a>
										</li>
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
