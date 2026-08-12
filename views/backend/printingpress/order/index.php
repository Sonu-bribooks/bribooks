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
<?php if (in_array($this->session->userdata('role_id'), [1, 13])) { ?>
<button type="button" class="btn btn-primary alignToTitle" data-toggle="modal" data-target="#printerAssignModal">
	<?=_l('Assign')?>
</button>
<?php } ?>
<!-- Modal -->
<div class="modal fade" id="printerAssignModal" tabindex="-1" role="dialog" aria-labelledby="printerAssignModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="printerAssignModalLabel"><?= _l('printer') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<select name="reviewr" id="" class="form-control select2 reviewr" data-toggle="select2" name="reviewr" required>
					<option value=''>Select Printer</option>

					<?php
					foreach ($printer_list as $key => $value) {
						echo '<option value=' . $value['id'] . '>' . $value['first_name'] . ' ' . $value['last_name'] . ' </option>';
					}
					?>
				</select>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary assign">Save changes</button>
			</div>
		</div>
	</div>
</div>

<div id="accordion">
	<div class="card">
		<div class="card-header" id="heading-1">
			<h5 class="mb-0">
				<a class="collapsed" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					Filters
				</a>

				<a class="float-right" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<i class="dripicons-view-apps"></i>
				</a>
			</h5>
		</div>

		<div id="collapse-1" class="collapse hide" data-parent="#accordion" aria-labelledby="heading-1">
			<div class="card-body p-5">
				<div>
					<span>Enter Start Date</span>

					<input class="form-control alignToTitle start-date" name="start-date" data-provide="datepicker" placeholder="Enter Starting date">
				</div>
				<div>
					<span>Enter End Date</span>
					<input class="form-control alignToTitle end-date" name="end-date" data-provide="datepicker" placeholder="Enter Starting date">
				</div>
				<button class="btn btn-primary alignToTitle search">Search</button>
				<!-- <button class="btn btn-primary alignToTitle export-csv">Export </button> -->
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
								<th>#</th>
								<th><?php echo _l('order_code'); ?></th>
								<th><?php echo _l('product'); ?></th>
								<th><?php echo _l('weight_dimension'); ?></th>
								<th><?php echo _l('order_date'); ?></th>
								<th><?php echo _l('printer name'); ?></th>
								<th><?php echo _l('printed'); ?></th>
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
var table = null;
</script>
<script>
	$(function() {
		let columns = JSON.parse(atob('<?php echo _render_column([
			'keys' 		=> [
				'sn',
				'#',
				'order_code',
				'product',
				'weight_dimension',
				'order_date',
				'assign',
				'printed',
			],
			'actions' => [
				[
					'key' => 'printed',
					'url' => 'printingPress/printed/'
				]
			]
		]); ?>'));

		const action = columns.pop()
		const callback = eval(action.render)
		columns.push({
			"data": "actions",
			render: callback
		});

		table = $('#ajax-datatable').DataTable({
			'aoColumnDefs': [{
				'bSortable': false,
				'aTargets': 0
			}],
			"ajax": "<?php echo $action_ajax; ?>",
			"lengthMenu": [200, 300, 500, 1000],
			"processing": true,
			"serverSide": true,
			"order": [
				[0, "desc"]
			],
			"columns": columns
		})
	});

	$(document).on("click", '.select-me', function(event) {
		if (this.checked) {
			$(this).prop('checked', true).trigger('change');
		} else {
			$(this).prop('checked', false).trigger('change');
		}
		$('.select-all').prop('checked', false).trigger('change');
	});

	$(".bulk-inprint").on('click', function(event) {
		event.preventDefault();
		var ids = [];
		$.each($("input[class='select-me']:checked"), function() {
			ids.push($(this).val());
		});
		if (confirm("Are you sure?")) {
			// $.ajax({
			// 	url: '/admin/delete_book',
			// 	type: "POST",
			// 	data: {
			// 		ids: ids,
			// 	},
			// 	cache: false,
			// 	success: function(response) {
			// 		var data = JSON.parse(response);
			// 		if (data.status)
			// 			location.reload();
			// 		else
			// 			alert(data.message);
			// 	}
			// });
		}
	});

	$(document).on("click", '.order-complete', function(event) {
		if (confirm("Are you sure?")) {
			$.ajax({
				type: "POST",
				data: 'orderid=' + $(this).data("id") + '&status=' + $(this).data("orderstatus") + '&description=<?php echo _order_status(2); ?>',
				url: "/admin/order_history/",
				success: function(rsp) {
					alert(rsp);
					window.location.href = ''
				}
			});
		}
	});

	$(document).on("click", '.ship-order', function(event) {
		if (confirm("Are you sure?")) {
			$.ajax({
				type: "POST",
				data: 'orderid=' + $(this).data("id") + '&status=' + $(this).data("orderstatus") + '&description=<?php echo _order_status(1); ?>',
				url: "/admin/order_history/",
				success: function(rsp) {
					alert(rsp);
					window.location.href = ''
				}
			});
		}
	});

	$(document).on('click', '.download-ebook', function(event) {
		var id = $(this).data('id');
		if (confirm("Are you sure?")) {
			$.ajax({
				type: "get",
				// data: 'orderid='+$(this).data("id")+'&status='+$(this).data("orderstatus")+'&description=<?php echo _order_status(0); ?>',
				url: "/PrintingPress/printBook/" + id,
				success: function(rsp) {
					console.log(rsp);
				}
			});
		}
	})

	$(document).on('click', '.search', function(event) {
		event.preventDefault();
		var endDate = $('.end-date').val();
		var startDate = $('.start-date').val();

		if (Date(startDate) >= Date(endDate)) {
			table.ajax.url("<?= $action_ajax ?>?startdate=" + startDate + "&enddate=" + endDate).load();
		}
	})

	$(document).on("click", '.in-print', function(event) {
		console.log(this)
		if (confirm("Are you sure?")) {
			$.ajax({
				type: "POST",
				data: 'orderid=' + $(this).data("id") + '&status=' + $(this).data("orderstatus") + '&description=<?php echo _order_status(0); ?>',
				url: "/admin/order_history/",
				success: function(rsp) {
					table.ajax.reload();
				}
			});
		}
	});

	$('.assign').click(function() {
		event.preventDefault();
		var ids = [];
		$.each($("input[class='select-me']:checked"), function() {
			ids.push($(this).val());
		});
		let status = $('.reviewr').val()
		if (confirm("Are you sure?")) {
			$.ajax({
				url: '/printingPress/submit_assign',
				type: "POST",
				data: {
					ids: ids,
					reviewer_id: status
				},
				cache: false,
				success: function(response) {
					table.ajax.reload();
				}
			});
		}
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
