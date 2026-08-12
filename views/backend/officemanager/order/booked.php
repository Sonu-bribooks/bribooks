<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<button type="button" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-manifest-button"><i class="mdi mdi-printer"></i> Manifest</button>
					<button type="button" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-invoice-button"><i class="mdi mdi-printer"></i> Invoice</button>
					<button type="button" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-label-button"><i class="mdi mdi-printer"></i> Label</button>
				</h4>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th><input type="checkbox" class="select-all"></th>
								<th><?php echo _l('order_code'); ?></th>
								<th><?php echo _l('customer'); ?></th>
								<th><?php echo _l('product'); ?></th>
								<th><?php echo _l('order_amount'); ?></th>
								<th><?php echo _l('weight_dimension'); ?></th>
								<th><?php echo _l('order_date'); ?></th>
								<th><?php echo _l('actions'); ?></th>
							</tr>
						</thead>
					</table>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<script>
	$(function() {
		let columns = JSON.parse(atob('<?php echo _render_column([
											'keys' 		=> [
												'orders',
												'order_code',
												'customer',
												'product',
												'order_amount',
												'weight_dimension',
												'order_date',
											],
											'actions'	=> [
												// [
												// 	'key' 		=> 'edit',
												// 	'url' 		=> 'admin/add_theme/',
												// ],
												// [
												// 	'key' 		=> 'status',
												// 	'type' 		=> 'status',
												// 	'url' 		=> '#',
												// ],
											]
										]); ?>'));

		const action = columns.pop()
		const callback = eval(action.render)
		columns.push({
			"data": "actions",
			render: callback
		});

		$('#ajax-datatable').DataTable({
			"ajax": "<?php echo $action_ajax; ?>",
			"processing": true,
			"serverSide": true,
			"order": [
				[0, "desc"]
			],
			"columns": columns,
			"drawCallback": function(settings) {
				// Here the response
				var response = settings.json;
				console.log(response);
			},
		})
	});

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
					window.open('/admin/books');
				else
					alert(data.message);
			}
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
