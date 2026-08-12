<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
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
				<div class="table-responsive mt-4">
					<table id="basic-datatable" class="table table-striped table-centered mb-0">
						<thead>
						<tr>
							<th>#</th>
							<th><?php _el('lead_id'); ?></th>
							<th><?php _el('name'); ?></th>
							<th><?php _el('mobile'); ?></th>
							<th><?php _el('telecaller_assigned'); ?></th>
							<th><?php _el('original_telecaller'); ?></th>
							<th><?php _el('comment'); ?></th>
							<th><?php _el('date_added'); ?></th>
						</tr>
						</thead>
						<tbody>
							<?php
								foreach ($reassigns as $key => $reassign): ?>
								<tr>
									<td><?php echo $key+1; ?></td>
									<td><?php echo $reassign['lead_id']; ?></td>
									<td><?php echo $reassign['name']; ?></td>
									<td><?php echo $reassign['mobile']; ?></td>
									<td><?php echo $reassign['telecaller']; ?></td>
									<td><?php echo $reassign['original_telecaller']; ?></td>
									<td><?php echo $reassign['comment']; ?></td>
									<td><?php echo $reassign['date_added']; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

			</div>
		</div>
	</div>
</div>
