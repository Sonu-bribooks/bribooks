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
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th>#</th>
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
												'sn',
												'order_code',
												'customer',
												'product',
												'order_amount',
												'weight_dimension',
												'order_date',
												'actions'
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
			"columns": columns
		})
	});

	$(document).on("click", '.ship-order', function(event) {
		if (confirm("Are you sure?")) {
			$.ajax({
				type: "POST",
				url: "/admin/ship_order/" + $(this).data("id"),
				success: function(rsp) {
					alert(rsp);
				}
			});
		}
	});
</script>
