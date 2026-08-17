<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<!-- <button type="button" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-manifest-button"><i class="mdi mdi-printer"></i> Manifest</button>
				<button type="button" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-invoice-button"><i class="mdi mdi-printer"></i> Invoice</button>
				<button type="button" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-label-button"><i class="mdi mdi-printer"></i> Label</button> -->
				</h4>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12 col-xs-12 col-md-7 col-lg-7 col-xl-7">
		<div class="card">
			<div class="card-header">
				<h6><?=_l('Order') ?> : <?php echo $order_info['order_code']; ?></h6>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped table-sm table-centered mb-0">
						<tr>
							<th><?=_l('Order_Date') ?></th>
							<th><?=_l('Order_Amount_&_Mode') ?></th>
							<th><?=_l('Dimension_&_Weight') ?></th>
							<th><?=_l('Shipment_Details') ?></th>
						</tr>
						<tr>
							<td><?php echo $order_info['date_added']; ?></td>
							<td><?php echo $order_info['currency_symbol'] . ' ' . $order_info['total']; ?><br /><small class="badge badge-success"><?=_l('Prepaid') ?></small></td>
							<td><?php echo $order_info['weight'] ?><?=_l('gm') ?></td>
							<td>
								<?php
								$courier_data = json_decode($order_info['shipping_tracking_info'] ?? '', true);
								$courier_info = json_decode($order_info['shipping_info'], true);
								echo '<p>Courier : ' . $courier_info['courier_name'] ?? ''. '</p>';
								echo '<p>AWB : ' . (!empty($courier_data['awb_code']) ? '<a href="https://shiprocket.co/tracking/'.$courier_data['awb_code'].'" target="_blank">'.$courier_data['awb_code'].'</a>' : '') . '</p>';
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
				<h6><?=_l('Customer_Details') ?></h6>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped table-sm table-centered mb-0">
						<tr>
							<th><?=_l('Customer_Detail') ?></th>
							<th><?=_l('Customer_Address') ?></th>
						</tr>
						<tr>
							<td>
								<?php echo trim($user['first_name'] . ' ' . $user['last_name']); ?><br />
								<?php echo $user['email']; ?><br />
								<?php echo $user['mobile']; ?><br />
							</td>
							<td>
								<?php 
								  if(!empty($address)){ 
									?>
									<?=$address['name'] ?? '' ?><br />
									<?=$address['mobile'] ?? '' ?><br />
									<?=$address['address'] ?? '' ?><br />
									<?=$address['landmark'] ?? '' ?><br />
									<?=trim($address['city'] ?? '' . ', ' . $address['state'] ?? ''); ?><br />
									<?=$address['country'] ?? '' ?><br />
									<?=$address['zipcode'] ?? '' ?><br />
								<?php
								  }
								?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div class="card">
			<div class="card-header">
				<h6><?=_l('Product_Details') ?></h6>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-striped table-sm table-centered mb-0">
						<tr>
							<th><?=_l('Product_Name') ?></th>
							<th><?=_l('Cover_Type') ?></th>
							<th><?=_l('SKU & HSN') ?></th>
							<th><?=_l('Quantity') ?></th>
							<th><?=_l('Unit_Price') ?></th>
							<th><?=_l('Total') ?></th>
						</tr>
						<?php
						if (!empty($products)) {
							$total = 0;
							foreach ($products as $key => $product) {
								$option = json_decode($product['option'], 1);
								$total = $total + $product['subtotal'];
						?>
								<tr>
									<td><?= $product['name'].' by '.$product['author_name']; ?></td>
									<td><?php echo $option['name']; ?></td>
									<td><?= 'BB_' . mb_strtoupper($option['name']) . '_' . $key . 'V' . $product['version'] . '_' . $product['product_id']?></td>
									<td><?= $product['quantity']; ?></td>
									<td><?= $product['price']; ?></td>
									<td><?= $order_info['currency_symbol'] . ' ' . $product['subtotal']; ?></td>
								</tr>
							<?php } ?>
							<tr>
								<td colspan="5" align="right"><?=_l('total')?></td>
								<td><?= $order_info['currency_symbol'] . ' ' . $total; ?></td>
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
		<!-- <div class="card">
			<div class="card-header">
				<h6>Update courier status</h6>
			</div>
			<div class="card-body">
				<form id="form">
					<input type="hidden" name="orderid" value="<?= $order_info['id'] ?>">
					<select class="form-control" name="description" id="status" required>
					<option value="">Update Status</option>
						<option value="<?= _l('book_is_sent_for_printing') ?>"><?= _l('in_print') ?></option>
						<option value="<?= _l('your_order_is_shipped') ?>"><?= _l('shipped') ?></option> -->
						<!-- <option value="In-Transit">intransit</option>
						<option value="<?= _l('your_order_is_successfully_delivered') ?>">delivered</option> -->
						<!-- <option value="<?= _l('order_completed') ?>"><?= _l('order_completed') ?></option> -->
					<!-- </select>
					<button class="btn btn-primary mt-2"> status update </button>
				</form>
			</div> -->
		<!-- </div> -->
	</div>
</div>
<script>
	$(".bulk-label-button").on('click', function(event) {
		event.preventDefault();
		var order_ids = [];
		$.each($("input[class='select-me']:checked"), function() {
			order_ids.push($(this).val());
		});

		$.ajax({
			url: '/admin/genrate_label',
			type: "POST",
			data: {
				order_ids: order_ids,
			},
			cache: false,
			success: function(response) {
				var data = JSON.parse(response);
				if (data.status)
					window.open(data.url, '_blank'); // location.reload();
				else
					alert(data.message);
			}
		});
	});

	$(".bulk-invoice-button").on('click', function(event) {
		event.preventDefault();
		var order_ids = [];
		$.each($("input[class='select-me']:checked"), function() {
			order_ids.push($(this).val());
		});

		$.ajax({
			url: '/admin/genrate_invoice',
			type: "POST",
			data: {
				order_ids: order_ids,
			},
			cache: false,
			success: function(response) {
				var data = JSON.parse(response);
				if (data.status)
					window.open(data.url, '_blank'); // location.reload();
				else
					alert(data.message);
			}
		});
	});

	$(".bulk-manifest-button").on('click', function(event) {
		event.preventDefault();
		var order_ids = [];
		$.each($("input[class='select-me']:checked"), function() {
			order_ids.push($(this).val());
		});

		$.ajax({
			url: '/admin/genrate_manifest',
			type: "POST",
			data: {
				order_ids: order_ids,
			},
			cache: false,
			success: function(response) {
				var data = JSON.parse(response);
				if (data.status)
					window.open(data.url, '_blank'); // location.reload();
				else
					alert(data.message);
			}
		});
	});
	// upadte status
	$('form').on('submit', (e) => {
		e.preventDefault();
		request = $.ajax({
			url: "/admin/order_history",
			type: "post",
			data: $('form').serialize()
		});

		// Callback handler that will be called on success
		request.done(function(response, textStatus, jqXHR) {
			// Log a message to the console
			console.log(jqXHR.responseText);
			window.location.href = ""
		});

		// Callback handler that will be called on failure
		request.fail(function(jqXHR, textStatus, errorThrown) {
			// Log the error to the console
			console.error(
				"The following error occurred: " +
				textStatus, errorThrown
			);
		});
	});


	$(".select-all").click(function() {
		if (this.checked) {
			$(":checkbox").each(function() {
				$(this).prop('checked', true).trigger('change');
			});

		} else {
			$('.select-me').each(function() {
				$(this).prop('checked', false).trigger('change');
			});
		}
	});

	$(document).on("click", '.select-me', function(event) {
		if (this.checked) {
			$(this).prop('checked', true).trigger('change');
		} else {
			$(this).prop('checked', false).trigger('change');
		}
		$('.select-all').prop('checked', false).trigger('change');
	});
</script>
