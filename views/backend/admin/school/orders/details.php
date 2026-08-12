<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12 col-xs-12 col-md-7 col-lg-7 col-xl-7">
		<div class="card">
			<div class="card-header">
				<h6><?=_l('order') ?> : <?php echo $order_info['order_code']; ?></h6>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped table-sm table-centered mb-0">
						<tr>
							<th>Order Date</th>
							<th>Order Amount & Mode</th>
							<th>Dimension & Weight</th>
							<th>Shipment Details</th>
						</tr>
						<tr>
							<td><?php echo $order_info['date_added']; ?></td>
							<td><?php echo $order_info['currency_symbol'] . ' ' . $order_info['total']; ?><br /><small class="badge badge-success">Prepaid</small></td>
							<td><?php echo $order_info['weight'] ?>gm</td>
							<td>
								<?php
								$courier_data = json_decode($order_info['shipping_tracking_info'], true);
								$courier_info = json_decode($order_info['shipping_info'], true);
								if ($courier_data && $courier_info) {
									echo '<p>Courier : ' . $courier_info['courier_name'] . '</p>';
									if (strpos($courier_info['courier_name'], 'Dtdc') !== false) {
									    echo '<p>AWB : ' . (!empty($courier_data['awb_code']) ? '<a href="https://www.dtdc.in/tracking.asp" target="_blank">' . $courier_data['awb_code'] .'</a>' : '') . '</p>';
									} else {
										echo '<p>AWB : ' . (!empty($courier_data['awb_code']) ? '<a href="https://shiprocket.co/tracking/' . $courier_data['awb_code'] . '" target="_blank">' . $courier_data['awb_code'] .'</a>' : '') . '</p>';
									}
								}
								?>
								<?=_l('shipping_charge')?>::<?=$order_info['currency_symbol']?><?=$order_info['shipping_cost']?>
							</td>
						</tr>
					</table>
				</div>
			</div> <!-- end card body-->
		</div>
		<div class="card">
			<div class="card-header">
				<h6>Customer Details</h6>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped table-sm table-centered mb-0">
						<tr>
							<th>Customer Detail</th>
							<th>Customer Address</th>
						</tr>
						<?php if (!empty($school_info)) {
							?>
						<tr>
							<td>
								<?php echo trim($school_info['authorized_person']) ?? ''; ?><br />
								<?php echo $school_info['owner_email'] ?? ''; ?><br />
								<?php echo $school_info['owner_mobile'] ?? ''; ?><br />
							</td>
							<td>
								<?php echo $school_info['name']; ?><br />
								<?php echo $school_info['address']; ?><br />
								<?php echo $school_info['landmark']; ?><br />
								<?php echo isset($school_info['city']) ? trim($school_info['city'] . ', ' . $school_info['state']) : ''; ?><br />
								<?php echo $school_info['country'] ?? ''; ?><br />
								<?php echo $school_info['pincode'] ?? ''; ?><br />
							</td>
						</tr>
						<?php
							}?>
					</table>
				</div>
			</div>
		</div>
		<div class="card">
			<div class="card-header">
				<h6>Product Details</h6>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped table-sm table-centered mb-0">
						<tr>
							<th>Product Name</th>
							<th>Quantity</th>
							<th>Unit Price</th>
							<th>Total</th>
						</tr>
						<?php
						if (!empty($order_info && !empty($school_info['base_price']))) {
						?>
							<tr>
								<td><?= $order_info['type'] . ' for ' . $order_info['school_name'] ?></td>
								<td>1</td>
								<td><?= $order_info['currency_symbol'] . ' ' . isset($school_info['base_price']) ? apply_currency_exchange($school_info['base_price'], $order_info['currency_code']) : 0; ?></td>
								<td><?= $order_info['currency_symbol'] . ' ' . $order_info['total']; ?></td>
							</tr>
							<tr>
								<td colspan="3" align="right"><?=_l('total')?></td>
								<td><b><?= $order_info['currency_symbol'] . ' ' . $order_info['total']; ?></b></td>
							</tr>
						<?php } ?>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="col-sm-12 col-xs-12 col-md-5 col-lg-5 col-xl-5">
		<div class="card">
			<div class="card-header">
				<h6><?=_l('activities')?></h6>
			</div>
			<div class="card-body">
				<ul>
					<?php foreach ($histories as $key =>  $value) { ?>
					<li>
						<?=_li($value['description'])?> --<b><?=formatDate($value['date_added'])?></b>
					</li>
					<?php } ?>
					<li>
						<?=_l('order_created')?> --<b><?=formatDate($order_info['date_added'])?></b>
					</li>
				</ul>
			</div>
		</div>

		<div class="card">
			<div class="card-header">
				<h6><?=_l('comments')?></h6>
			</div>
			<div class="card-body">
				<ul>
					<?php foreach ($comments as $key =>  $value) { ?>
					<li>
						<?=_li($value['description'])?> --<b><?=formatDate($value['date_added'])?></b>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
	</div>
</div>
