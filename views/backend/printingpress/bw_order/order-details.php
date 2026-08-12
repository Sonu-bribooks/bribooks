<?php

array_push($orderHistory,[
	'description' => "order created",
	"date_added" => "00"
]);
?>
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
				<h6>Order : <?php echo $order_info['order_code']; ?></h6>
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
							<td><?php echo $order_info["weight"] ?>gm</td>
							<td>
								<?php
								$courierData = json_decode($order_info['shipping_tracking_info'], true);
								$courierInfo = json_decode($order_info['shipping_info'], true);
								echo '<p>Courier : ' . $courierInfo['courier_name'] . '</p>';
								echo '<p>AWB : ' . $courierData['awb_code'] . '</p>';
								?>
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
						<tr>
							<td>
								<?php echo $user['first_name'] . ' ' . $user['last_name']; ?><br />
								<?php echo $user['email']; ?><br />
								<?php echo $user['mobile']; ?><br />
							</td>
							<td>
								<?php echo $address['name']; ?><br />
								<?php echo $address['mobile']; ?><br />
								<?php echo $address['address']; ?><br />
								<?php echo $address['landmark']; ?><br />
								<?php echo $address['city'] . ', ' . $order_info['state']; ?><br />
								<?php echo $address['country'] . ', ' . $order_info['zipcode']; ?><br />
							</td>
						</tr>
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
							<th>Cover Type</th>
							<th>SKU & HSN</th>
							<th>Quantity</th>
							<th>Unit Price</th>
							<th>Total</th>
						</tr>
						<?php
						if (!empty($products)) {
							$total = 0;
							foreach ($products as $product) {
								$type = json_decode($product["option"], 1);
								$total = $total + $product['subtotal'];
						?>
								<tr>
									<td><?= $product['name']; ?></td>
									<td><?php echo $type["name"]; ?></td>
									<td><?php echo 'BRIBOOK_' . $order_info['product_id']; ?></td>
									<td><?= $product['quantity']; ?></td>
									<td><?= $product['price']; ?></td>
									<td><?= $order_info['currency_symbol'] . ' ' . $product['subtotal']; ?></td>
								</tr>
							<?php } ?>
							<tr>
								<td colspan="5" align="right">Total</td>
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
				<h6>Activities</h6>
			</div>
			<div class="card-body">
				<ul>
					<?php
					foreach ($orderHistory as $key =>  $value) {
						$value['date_added'] = date('d-M-Y',strtotime($value['date_added']));
						echo '<li>'._li($value['description']).' <small class="float-right">' . $value['date_added'] . '</small></li>';
					}
					?>
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
