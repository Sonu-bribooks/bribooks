<style type="text/css">
table {
	border-collapse: collapse;
	width: 100%;
	border: 1px solid #ddd;
}
th, td {
	text-align: left;
	padding: 7px;
	border: 1px solid #ddd;
}
</style>
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				</h4>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12 col-xs-12 col-md-12 col-lg-12 col-xl-12">
		<div class="card">
			<div class="card-header">
				<h6>Parent Order Code : <?php echo $order_info['order_code']; ?></h6>
			</div>
			<div class="card-body">
				<form id="clone-order-form" action="<?php echo $action ?? ''; ?>" method="post">
					<div class="tab-pane" id="basic_info">
						<div class="row">
							<h4><?php echo $this->session->error; ?></h4>
						</div>
						<div class="col-12">
							<div class="form-group row mb-3">
								<label class="col-md-3 col-form-label" for="new_order_code"><?php echo _l('New Order Code'); ?> <span class="required">*</span> </label>
								<div class="col-md-9">
									<input type="text" class="form-control" id="new_order_code" name="new_order_code" value="<?php echo $new_order_code ?? ''; ?>" required readonly>
								</div>
							</div>
							<div class="form-group row mb-3">
								<label class="col-md-3 col-form-label" for="customer_name"><?php echo _l('customer name'); ?> <span class="required">*</span> </label>
								<div class="col-md-9">
									<input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo $address['name'] ?? ''; ?>" required>
								</div>
							</div>
							<div class="form-group row mb-3">
								<label class="col-md-3 col-form-label" for="customer_mobile"><?php echo _l('customer mobile'); ?> <span class="required">*</span> </label>
								<div class="col-md-9">
									<input type="text" class="form-control" id="customer_mobile" name="customer_mobile" value="<?php echo $address['mobile'] ?? ''; ?>" required>
								</div>
							</div>
							<div class="form-group row mb-3">
								<label class="col-md-3 col-form-label" for="customer_state"><?php echo _l('customer state'); ?> <span class="required">*</span> </label>
								<div class="col-md-9">
									<select class="form-control" name="customer_state" id="customer_state" required>
										<?php $states = _get_country_state($address['country']);
										foreach ($states ?? [] as $state) {
											if (($address['state'] ?? '') === $state['name']) {
										?>
										<option value="<?php echo $state['name']; ?>" selected><?php echo $state['name']; ?></option>
										<?php } else { ?>
										<option value="<?php echo $state['name']; ?>"><?php echo $state['name']; ?></option>
										<?php } } ?>
									</select>
								</div>
							</div>
							<div class="form-group row mb-3">
								<label class="col-md-3 col-form-label" for="customer_city"><?php echo _l('customer city'); ?> <span class="required">*</span> </label>
								<div class="col-md-9">
									<input type="text" class="form-control" id="customer_city" name="customer_city" value="<?php echo $address['city'] ?? ''; ?>" required>
								</div>
							</div>
							<div class="form-group row mb-3">
								<label class="col-md-3 col-form-label" for="customer_address"><?php echo _l('customer address'); ?> <span class="required">*</span> </label>
								<div class="col-md-9">
									<input type="text" class="form-control" id="customer_address" name="customer_address" value="<?php echo $address['address'] ?? ''; ?>" required>
								</div>
							</div>
							<div class="form-group row mb-3">
								<label class="col-md-3 col-form-label" for="customer_landmark"><?php echo _l('customer landmark'); ?> </label>
								<div class="col-md-9">
									<input type="text" class="form-control" id="customer_landmark" name="customer_landmark" value="<?php echo $address['landmark'] ?? ''; ?>">
								</div>
							</div>
							<div class="form-group row mb-3">
								<label class="col-md-3 col-form-label" for="customer_zipcode"><?php echo _l('customer zipcode'); ?> <span class="required">*</span> </label>
								<div class="col-md-9">
									<input type="text" class="form-control" id="customer_zipcode" name="customer_zipcode" value="<?php echo $address['zipcode'] ?? ''; ?>" required>
								</div>
							</div>
							<div class="form-group row mb-12">
								<h4 class="col-md-12 col-form-label"><?php echo _l('Total Products: ' . count($products)); ?></h4>
							</div>
							<div class="form-group mb-12">
								<table>
									<thead>
										<tr>
											<th class="text-center"><input type="checkbox" class="select-all" checked></th>
											<th>Book Name</th>
											<th>Sku</th>
											<th>Page Count</th>
											<th>Quantity</th>
											<th class="text-center"><i class="mdi mdi-plus-circle" title="Add Book Stock"></i> Re-Print</th>
										</tr>
									</thead>
									<tbody>
										<?php if(!empty($products)) { foreach ($products as $key => $order_product) {
											$option = json_decode($order_product['option'], 1);
										?>
										<tr class="product order_product_<?= $key; ?>" data-id="<?= $order_product['product_id']; ?>">
											<td class="text-center">
												<input type="checkbox" class="select-me" name="products[<?= $key; ?>][checkbox]" value="1" checked>
											</td>
											<td>
												<?php if(in_array($order_info['status'], [1])) { ?>
												<select class="form-control select2 products" data-toggle="select2" id="product_id_<?= $key; ?>" name="products[<?= $key; ?>][book_id]" data-key="<?= $key; ?>">
													<option value="<?php echo $order_product['product_id']; ?>"><?php echo $order_product['name'] . ' by ' . $order_product['author_name']; ?></option>
												</select>
												<?php } else { ?>
												<select class="form-control" id="product_id_<?= $key; ?>" name="products[<?= $key; ?>][book_id]">
													<option value="<?php echo $order_product['product_id']; ?>"><?php echo $order_product['name'] . ' by ' . $order_product['author_name']; ?></option>
												</select>
												<?php } ?>
											</td>
											<td>
												<input type="text" class="form-control" id="product_sku_<?= $key; ?>" name="products[<?= $key; ?>][sku]" value="<?php echo _o_b_code($order_product['product_id'], $order_product['version'], $option['name']); ?>" readonly>
											</td>
											<td>
												<?php
												$pages = $this->book_model->getTotalPages($order_product['product_id']) * 2 + 5;
												?>
												<input type="text" class="form-control" id="product_pages_<?= $key; ?>" name="products[<?= $key; ?>][pages]" value="<?php echo $pages ?? ''; ?>" readonly>
											</td>
											<td>
												<?php if ($parent_order_status == 15) { ?>
													<input type="text" class="form-control" id="product_quantity_<?= $key; ?>" name="products[<?= $key; ?>][quantity]" value="<?= $order_product['quantity'] ?>" readonly>
												<?php } else { ?>
													<select class="form-control" id="product_quantity_<?= $key; ?>" name="products[<?= $key; ?>][quantity]">
													<?php for($i=1; $i<=$order_product['quantity']; $i++) {
														if ($i == $order_product['quantity']) {
													?>
														<option value="<?php echo $i; ?>" selected><?php echo $i; ?></option>
													<?php } else { ?>
														<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
													<?php } } ?>
													</select>
												<?php } ?>
											</td>
											<td>
												<select class="form-control" id="product_need_stock_<?= $key; ?>" name="products[<?= $key; ?>][need_stock]"<?=$parent_order_status != 15 ? ' disabled' : ''?>>
												<?php for($i=0; $i<=$order_product['quantity']; $i++) { ?>
													<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
												<?php } ?>
												</select>
											</td>
										</tr>
										<?php } } ?>
									</tbody>
								</table>
							</div>
							<div class="form-group">
								<button type="button" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle btn-clone-order">Submit</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
$(function() {
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

	$('.products').select2({
		minimumInputLength: 3,
		ajax: {
			url: '<?php echo site_url('admin/ajax_filter_books'); ?>',
			dataType: 'json',
			delay: 300,
			data: function (params) {
				var query = {
					search: params.term,
				}
				return query;
			},
			processResults: function(data) {
				return {
					results: data.items
				};
			}
		}
	});

	$('.products').on('change', function() {
		var row = $(this).attr('data-key');
		var old_book_id = $('.order_product_'+row).attr('data-id');
		var book_id = $(this).val();
		if(old_book_id && book_id) {
			$.post('<?php echo site_url('admin/order_book_detail'); ?>', {
				'old_book_id': old_book_id,
				'book_id': book_id
			}, function(json) {
				if(json.success) {
					const book = json.book;
					$('#product_sku_'+row).val(book.sku);
					$('#product_pages_'+row).val(book.pages);
				} else {
					$("#product_id_"+row).val(old_book_id).trigger("change");
					alert(json.error);
				}
			});
		}
	});

	$('#customer_state').on('change', function() {
		let ele = document.getElementById('customer_city');
		$.post({
			url: "<?= base_url("api/getCities") ?>",
			data: JSON.stringify({
				state_id: $('#customer_state').val()
			}),
			dataType: 'json',
			success: function(response) {
				let cities = response.cities

				$("#customer_city").empty();
				ele.innerHTML = ele.innerHTML + '<option value="">Select</option>';

				for (let i = 0; i < cities.length; i++) {
					ele.innerHTML = ele.innerHTML + '<option value="' + cities[i]['id'] + '">' + cities[i]['name'] + '</option>';
				}
			}
		});
	});

	$(document).on('click', '.btn-clone-order', function(event) {
		if (confirm('Are you sure?')) {
			$('#clone-order-form').submit();
		}
	});
});
</script>
