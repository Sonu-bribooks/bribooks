<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<!-- <button class="btn btn-primary bulk-inprint float-right alignToTitle" data-orderstatus="">Bulk Send Inprint</button> -->
				</h4>
			</div>
		</div>
	</div>
</div>


<div class="row w-75 m-auto" style="margin-bottom: 2em !important;">
	<div class="col col-sm-4">
		<div class="input-group mb-3">
			<input type="number" placeholder="<?=_l('Enter quantity')?>" id="stock" class="form-control" />
			<div class="input-group-append">
				<button type="button" class="btn btn-primary">
					<?=_li('submit')?>
				</button>
			</div>
		</div>
	</div>
	<div class="col">
		<span class="text-warning"> Remaining <span id="remaining"></span></span>
	</div>
	<div class="col">
		<span class="text-success"> Process Quantity <span class="quantity"></span></span>
	</div>
	<div class="col">

		<button class="btn btn-info assign"><?=_li('Continue To Print')?> (<b class="quantity"></b>)</button>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive mt-4">
					<?php //include('nav.php');
					?>
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<!-- <th><input type="checkbox" class="select-all"></th> -->
								<th><input type="checkbox" class="select-all" disabled></th>
								<th><?php echo _l('book_name'); ?></th>
								<th><?php echo _l('author_name'); ?></th>
								<th><?php echo _l('type'); ?></th>
								<th><?php echo _l('quantity'); ?></th>
								<th><?php echo _l('date'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($results['rows'] as $key => $value) {
								$type = json_decode($value['option'], 1)['name'];
							?>
								<tr>
									<td><input type="checkbox" class="select-me" value="<?= $value['order_id'] ?>" data-stock='<?= $value['quantity'] ?>' data-pid='<?= $value['product_id'] ?>' data-type='<?= $type ?>'></td>
									<td><?= $value['name'] . '(version' . $value['version'] . ')' ?></td>
									<td><?= $value['author_name'] ?></td>
									<td><?= $type ?></td>
									<td><?= $value['quantity'] ?></td>
									<td><?= $value['date_added'] ?></td>
								</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<script>
	var totalSeen = $("input.select-me").length;
	$('#stock').change(function() {
		var stock = document.getElementById('stock').value;
		var selected = 0;
		var rowCount = $('#ajax-datatable tbody tr').length;
		$('#ajax-datatable tbody tr td input.select-me').prop('checked', false);
		$('#ajax-datatable tbody tr td input.select-me').each((key, ele) => {
			if (stock > 0) {
				if (stock >= parseInt(ele.dataset.stock)) {
					stock = stock - parseInt(ele.dataset.stock)
					console.log($(ele).prop('checked', true))
					selected = selected + parseInt(ele.dataset.stock);
				}
			}

		})
		document.getElementById("remaining").innerHTML = stock
		$('.quantity').text(selected);
	});
	$('.assign').click(function() {
		event.preventDefault();
		var ids = [];
		var pid = ''
		var type = ''
		$.each($("input[class='select-me']:checked"), function() {
			ids.push($(this).val());
		});
		$.each($("input[class='select-me']:checked"), function() {
			pid = $(this).data('pid')
		});
		$.each($("input[class='select-me']:checked"), function() {
			type = $(this).data('type')
		});
		if (confirm("Are you sure?")) {
			$.ajax({
				url: '/printingPress/send_partial_in_print',
				type: "POST",
				data: {
					ids: ids,
					product_id: pid,
					type: type,
				},
				cache: false,
				success: function(json) {
					if (json.redirect) {
						window.location = json.redirect;
					}

					json.error && error_notify(json.error);
				}
			});
		}
	})
	$(document).on('click', '.select-me', function(event) {
		if (this.checked) {
			$(this).prop('checked', true).trigger('change');
		} else {
			$(this).prop('checked', false).trigger('change');
		}
		$('.select-all').prop('checked', false).trigger('change');
	});


	$(document).on('click', '.select-me', function(event) {
		if (this.checked) {
			$(this).prop('checked', true).trigger('change');
		} else {
			$(this).prop('checked', false).trigger('change');
		}
		$('.select-all').prop('checked', false).trigger('change');
	});

	$('.select-me').on('change', function() {
		var selected = 0;
		$('.select-me').each(function() {
			if ($(this).prop('checked')) {
				selected += parseInt($(this).data('stock'));
			}
		});

		$('.quantity').text(selected);
	})
</script>
