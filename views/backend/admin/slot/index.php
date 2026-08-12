<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo site_url('admin/slot_form/add_slot_form'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add_slot'); ?></a>
			</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
			  <h4 class="mb-3 header-title"><?php echo _l('slots'); ?></h4>
			  <div class="table-responsive-sm mt-4">
				<table id="basic-datatable" class="table table-striped table-centered mb-0">
				  <thead>
					<tr>
					  <th>#</th>
					  <th><?php echo _l('name'); ?></th>
					  <th><?php echo _l('type'); ?></th>
					  <th><?php echo _l('time'); ?></th>
					  <th><?php echo _l('actions'); ?></th>
					</tr>
				  </thead>
				  <tbody>
					  <?php
					   foreach ($slots->result_array() as $key => $slot): ?>
						  <tr>
							  <td><?php echo $key+1; ?></td>
							  <td><?php echo $slot['name']; ?></td>
							  <td><?php echo $slot['type']; ?></td>
							  <td><?php echo $slot['slot_start']; ?></td>
							  <td>
								  <div class="dropright dropright">
									<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="mdi mdi-dots-vertical"></i>
									</button>
									<ul class="dropdown-menu">
										<li><a class="dropdown-item" href="<?php echo site_url('admin/slot_form/edit_slot_form/'.$slot['id']) ?>"><?php echo _l('edit'); ?></a></li>
										<li><a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/slots/delete/'.$slot['id']); ?>');"><?php echo _l('delete'); ?></a></li>
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
