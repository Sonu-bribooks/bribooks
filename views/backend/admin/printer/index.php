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
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?=_l('dashboard')?>
				</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title"><?=_l('total_orders_and_printed_copies')?></h4>
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
								<th><?=_l('total')?></th>
								<td class="tdCls"><?=$order_stats['total_orders']?></td>
								<td class="tdCls"><?=$order_stats['total_under_printing']?></td>
								<td class="tdCls"><?=$order_stats['total_orders_printed']?></td>
								<td class="tdCls"><?=$order_stats['balance']?></td>
							</tr>
							<tr>
								<th><?=_l('paperback')?></th>
								<td class="tdCls"><?=$order_stats_paperback['total_orders']?></td>
								<td class="tdCls"><?=$order_stats_paperback['total_under_printing']?></td>
								<td class="tdCls"><?=$order_stats_paperback['total_orders_printed']?></td>
								<td class="tdCls"><?=$order_stats_paperback['balance']?></td>
							</tr>
							<tr>
								<th><?=_l('hard_cover')?></th>
								<td class="tdCls"><?=$order_stats_hardcover['total_orders']?></td>
								<td class="tdCls"><?=$order_stats_hardcover['total_under_printing']?></td>
								<td class="tdCls"><?=$order_stats_hardcover['total_orders_printed']?></td>
								<td class="tdCls"><?=$order_stats_hardcover['balance']?></td>
							</tr>
							<tr>
								<th><?=_l('black_white')?></th>
								<td class="tdCls"><?=$order_stats_blackwhite['total_orders']?></td>
								<td class="tdCls"><?=$order_stats_blackwhite['total_under_printing']?></td>
								<td class="tdCls"><?=$order_stats_blackwhite['total_orders_printed']?></td>
								<td class="tdCls"><?=$order_stats_blackwhite['balance']?></td>
							</tr>
						</tbody>
					</table>
				</div>

				<h4 class="header-title"><?=_l('total_reprinted_copies')?></h4>
				<div class="table-responsive">
					<table border="2" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th><?=_l('total_copies')?></th>
								<th><?=_l('In-Print')?></th>
								<th><?=_l('Printed')?></th>
								<th><?=_l('Balance')?></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<th><?=_l('total')?></th>
								<td class="tdCls"><?=$reprint_stats['total_orders']?></td>
								<td class="tdCls"><?=$reprint_stats['total_under_printing']?></td>
								<td class="tdCls"><?=$reprint_stats['total_orders_printed']?></td>
								<td class="tdCls"><?=$reprint_stats['balance']?></td>
							</tr>
							<tr>
								<th><?=_l('paperback')?></th>
								<td class="tdCls"><?=$reprint_stats_paperback['total_orders']?></td>
								<td class="tdCls"><?=$reprint_stats_paperback['total_under_printing']?></td>
								<td class="tdCls"><?=$reprint_stats_paperback['total_orders_printed']?></td>
								<td class="tdCls"><?=$reprint_stats_paperback['balance']?></td>
							</tr>
							<tr>
								<th><?=_l('hard_cover')?></th>
								<td class="tdCls"><?=$reprint_stats_hardcover['total_orders']?></td>
								<td class="tdCls"><?=$reprint_stats_hardcover['total_under_printing']?></td>
								<td class="tdCls"><?=$reprint_stats_hardcover['total_orders_printed']?></td>
								<td class="tdCls"><?=$reprint_stats_hardcover['balance']?></td>
							</tr>
							<tr>
								<th><?=_l('black_white')?></th>
								<td class="tdCls"><?=$reprint_stats_blackwhite['total_orders']?></td>
								<td class="tdCls"><?=$reprint_stats_blackwhite['total_under_printing']?></td>
								<td class="tdCls"><?=$reprint_stats_blackwhite['total_orders_printed']?></td>
								<td class="tdCls"><?=$reprint_stats_blackwhite['balance']?></td>
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
						<th colspan="3"><?=_l('printer')?></th>
						<th colspan="3">Backlog</th>
						<th colspan="3">Today Order</th>
						<th colspan="3">Total</th>
						<th colspan="3">Total Printed</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th colspan="3"></th>
						<th>Paperback</th>
						<th>Hard Cover</th>
						<th>Black White</th>
						<th>Paperback</th>
						<th>Hard Cover</th>
						<th>Black White</th>
						<th>Paperback</th>
						<th>Hard Cover</th>
						<th>Black White</th>
						<th>Paperback</th>
						<th>Hard Cover</th>
						<th>Black White</th>
					</tr>
					<?php foreach ($printers as $key => $printer) { ?>
					<tr>
						<td colspan="3">
							<?= strtoupper($printer['name']) ?>
							ID::<?= strtoupper($printer['id']) ?>
						</td>
						<td><?= $printer['backlog']['paperback'] ?></td>
						<td><?= $printer['backlog']['hardcover'] ?></td>
						<td><?= $printer['backlog']['blackwhite'] ?></td>
						<td>
							<?= $printer['today']['paperback'] ?><br>
							<span class="text-warning"><?=_l('reprint')?>:: <?= $printer['today']['reprint']['paperback'] ?>
						</td>
						<td>
							<?= $printer['today']['hardcover'] ?><br>
							<span class="text-warning"><?=_l('reprint')?>:: <?= $printer['today']['reprint']['hardcover'] ?></span>
						</td>
						<td>
							<?= $printer['today']['blackwhite'] ?><br>
							<span class="text-warning"><?=_l('reprint')?>:: <?= $printer['today']['reprint']['blackwhite'] ?></span>
						</td>
						<td>
							<?= $printer['total']['paperback'] ?><br>
							<span class="text-info"><?=_l('reprint')?>:: <?= $printer['total']['reprint']['paperback'] ?></span>
						</td>
						<td>
							<?= $printer['total']['hardcover'] ?><br>
							<span class="text-info"><?=_l('reprint')?>:: <?= $printer['total']['reprint']['hardcover'] ?></span>
						</td>
						<td>
							<?= $printer['total']['blackwhite'] ?><br>
							<span class="text-info"><?=_l('reprint')?>:: <?= $printer['total']['reprint']['blackwhite'] ?></span>
						</td>
						<td>
							<?= $printer['printed']['paperback'] ?><br>
							<span class="text-success"><?=_l('reprint')?>:: <?= $printer['printed']['reprint']['paperback'] ?></span>
						</td>
						<td>
							<?= $printer['printed']['hardcover'] ?><br>
							<span class="text-success"><?=_l('reprint')?>:: <?= $printer['printed']['reprint']['hardcover'] ?></span>
						</td>
						<td>
							<?= $printer['printed']['blackwhite'] ?><br>
							<span class="text-success"><?=_l('reprint')?>:: <?= $printer['printed']['reprint']['blackwhite'] ?></span>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
