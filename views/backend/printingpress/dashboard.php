<?php
?>
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('dashboard'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-4"><?php echo _l('total_order_and_printed_copies'); ?></h4>

					<?php if ($this->session->userdata('role_id') == '13') { ?>
					<div class="table-responsive">
						<table border="2" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>Orders</th>
									<th>Printed</th>
									<th>Balance</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><?php print_r($order_stats['total_orders']); ?></td>
									<td><?php print_r($order_stats['total_orders_printed']); ?></td>
									<td><?php print_r($order_stats['balance']); ?></td>
								</tr>
							</tbody>
						</table>
					</div>
					<?php } ?>

					<?php if (in_array($this->session->userdata('role_id'), ['12','15'])) { ?>
					<div class="table-responsive">
						<table border="2" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th colspan="3">Backlog</th>
									<th colspan="3">Today</th>
									<th colspan="3">Total Assigned</th>
									<th colspan="3">Total Printed</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Paperback</td>
									<td>Hard Cover</td>
									<td>Black White</td>
									<td>Paperback</td>
									<td>Hard Cover</td>
									<td>Black White</td>
									<td>Paperback</td>
									<td>Hard Cover</td>
									<td>Black White</td>
									<td>Paperback</td>
									<td>Hard Cover</td>
									<td>Black White</td>
								</tr>
								<tr>
									<td><?= $backlogs['backlog']["paperback"] ?></td>
									<td><?= $backlogs['backlog']["hardcover"] ?></td>
									<td><?= $backlogs['backlog']["blackwhite"] ?></td>
									<td><?= $backlogs['today']["paperback"] ?></td>
									<td><?= $backlogs['today']["hardcover"] ?></td>
									<td><?= $backlogs['today']["blackwhite"] ?></td>
									<td><?= $backlogs['total']["paperback"] ?></td>
									<td><?= $backlogs['total']["hardcover"] ?></td>
									<td><?= $backlogs['total']["blackwhite"] ?></td>
									<td><?= $backlogs['printed']["paperback"] ?></td>
									<td><?= $backlogs['printed']["hardcover"] ?></td>
									<td><?= $backlogs['printed']["blackwhite"] ?></td>
								</tr>
							</tbody>
						</table>
					</div>
					<?php }?>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<div>
	<?php if ($this->session->userdata('role_id') == '13') {
		foreach ($list as $key => $value) {
	?>
		<div class="table-responsive">
			<table border="2" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th colspan="9"><?= $value['name'] ?></th>
					</tr>
					<tr>
						<th colspan="3">Backlog</th>
						<th colspan="3">Today Order</th>
						<th colspan="3">Total</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Paperback</td>
						<td>Hard Cover</td>
						<td>Black White</td>
						<td>Paperback</td>
						<td>Hard Cover</td>
						<td>Black White</td>
						<td>Paperback</td>
						<td>Hard Cover</td>
						<td>Black White</td>
					</tr>
					<tr>
						<td><?= $value['backlog']["paperback"] ?></td>
						<td><?= $value['backlog']["hardcover"] ?></td>
						<td><?= $value['backlog']["blackwhite"] ?></td>
						<td><?= $value['today']["paperback"] ?></td>
						<td><?= $value['today']["hardcover"] ?></td>
						<td><?= $value['today']["blackwhite"] ?></td>
						<td><?= $value['total']["paperback"] ?></td>
						<td><?= $value['total']["hardcover"] ?></td>
						<td><?= $value['total']["blackwhite"] ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php } } ?>
</div>
