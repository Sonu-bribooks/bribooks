<style type="text/css">
.tdCls {
	padding: 10px;
	font-size:20px;
	font-weight:bolder;
}
</style>

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
				<h4 class="header-title"><?php echo _l('total_orders_and_printed_copies'); ?></h4>
				<div class="table-responsive mb-3">
					<table border="2" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th><?=_l('total_ordered_copies')?></th>
								<th><?=_l('In-Print')?></th>
								<th><?=_l('Printed')?></th>
								<th><?=_l('Balance')?></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th>Total</th>
								<td class="tdCls"><?php print_r($order_stats['total_orders']); ?></td>
								<td class="tdCls"><?php print_r($order_stats['total_under_printing']); ?></td>
								<td class="tdCls"><?php print_r($order_stats['total_orders_printed']); ?></td>
								<td class="tdCls"><?php print_r($order_stats['balance']); ?></td>
							</tr>
							<tr>
								<th>Paperback</th>
								<td class="tdCls"><?php print_r($order_stats_paperback['total_orders']); ?></td>
								<td class="tdCls"><?php print_r($order_stats_paperback['total_under_printing']); ?></td>
								<td class="tdCls"><?php print_r($order_stats_paperback['total_orders_printed']); ?></td>
								<td class="tdCls"><?php print_r($order_stats_paperback['balance']); ?></td>
							</tr>
							<tr>
								<th>Black White</th>
								<td class="tdCls"><?php print_r($order_stats_blackwhite['total_orders']); ?></td>
								<td class="tdCls"><?php print_r($order_stats_blackwhite['total_under_printing']); ?></td>
								<td class="tdCls"><?php print_r($order_stats_blackwhite['total_orders_printed']); ?></td>
								<td class="tdCls"><?php print_r($order_stats_blackwhite['balance']); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="card">
	<div class="card-body">
		<div class="table-responsive">
			<table border="2" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th colspan="2"><?=_l('printer')?></th>
						<th colspan="2">Backlog</th>
						<th colspan="2">Today Order</th>
						<th colspan="2">Total</th>
						<th colspan="2">Total Delivered</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th colspan="2"></th>
						<th>Paperback</th>
						<th>Black White</th>
						<th>Paperback</th>
						<th>Black White</th>
						<th>Paperback</th>
						<th>Black White</th>
						<th>Paperback</th>
						<th>Black White</th>
					</tr>
					<?php foreach ($printers as $key => $printer) { ?>
					<tr>
						<td colspan="2">
							<?= strtoupper($printer['name']) ?>
							ID::<?= strtoupper($printer['id']) ?>
						</td>
						<td><?= $printer['backlog']['paperback'] ?></td>
						<td><?= $printer['backlog']['blackwhite'] ?></td>
						<td><?= $printer['today']['paperback'] ?></td>
						<td><?= $printer['today']['blackwhite'] ?></td>
						<td><?= $printer['total']['paperback'] ?></td>
						<td><?= $printer['total']['blackwhite'] ?></td>
						<td><?= $printer['delivered']['paperback'] ?></td>
						<td><?= $printer['delivered']['blackwhite'] ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
