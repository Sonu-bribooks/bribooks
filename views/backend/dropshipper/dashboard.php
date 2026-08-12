<?php
?>
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('dashboard'); ?>
					<a
						class="btn btn-primary bulk-send float-right alignToTitle"
						data-orderstatus=""
						href="<?=$download_report_action?>"
					>
						<?=_l('download_today_report')?>
					</a>
				</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-4"><?php echo _l('total_order_and_printed_copies'); ?></h4>

					<div class="table-responsive">
						<table border="2" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th><?=_l('orders') ?></th>
									<th><?=_l('printed') ?></th>
									<th><?=_l('balance') ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><?= $order_stats['total_orders'] ?></td>
									<td><?= $order_stats['total_orders_printed'] ?></td>
									<td><?= $order_stats['balance'] ?></td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="table-responsive">
						<table border="2" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th colspan="2"><?=_l('backlog') ?></th>
									<th colspan="2"><?=_l('today') ?></th>
									<th colspan="2"><?=_l('total_assigned') ?></th>
									<th colspan="2"><?=_l('total_delivered') ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><?=_l('paperback') ?></td>
									<td><?=_l('black_white') ?></td>
									<td><?=_l('paperback') ?></td>
									<td><?=_l('black_white') ?></td>
									<td><?=_l('paperback') ?></td>
									<td><?=_l('black_white') ?></td>
									<td><?=_l('paperback') ?></td>
									<td><?=_l('black_white') ?></td>
								</tr>
								<tr>
									<td><?= $backlogs['backlog']['paperback'] ?></td>
									<td><?= $backlogs['backlog']['blackwhite'] ?></td>
									<td><?= $backlogs['today']['paperback'] ?></td>
									<td><?= $backlogs['today']['blackwhite'] ?></td>
									<td><?= $backlogs['total']['paperback'] ?></td>
									<td><?= $backlogs['total']['blackwhite'] ?></td>
									<td><?= $backlogs['delivered']['paperback'] ?></td>
									<td><?= $backlogs['delivered']['blackwhite'] ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
