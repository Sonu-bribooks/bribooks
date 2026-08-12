<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
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
						<th><?php echo _l('name'); ?></th>
						<th><?php echo _l('status'); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php
						 foreach ($results as $key => $result): ?>
							<tr>
								<td><?php echo $result['id']; ?></td>
								<td><?php echo $result['title']; ?></td>
								<td>
									<?php if ($result['status'] === 'pending'): ?>
										<i class="mdi mdi-circle" style="color: #FFC107; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo _l('disabled'); ?>"></i>
									<?php else:?>
										<i class="mdi mdi-circle" style="color: #4CAF50; font-size: 19px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo _l('enabled'); ?>"></i>
									<?php endif; ?>
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
