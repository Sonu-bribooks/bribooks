<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<button type="button" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-delete-button">
						<i class="mdi mdi-trash"></i> <?=_l('delete_review')?>
					</button>
				</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>
				<div class="tab-content">
					<div class="tab-pane active" id="home" role="tabpanel">
						<div class="table-responsive mt-2">
							<table id="ajax-datatable" class="table table-striped table-centered mb-0">
								<thead>
									<tr>
										<th><input type="checkbox" class="select-all"></th>
										<th><?php echo _l('id'); ?></th>
										<th><?php echo _l('auhtor'); ?></th>
										<th><?php echo _l('book_name'); ?></th>
										<th><?php echo _l('review'); ?></th>
										<th><?php echo _l('rating'); ?></th>
										<th><?php echo _l('date'); ?></th>
										<th><?php echo _l(''); ?></th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
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
				'id',
				'author',
				'book',
				'review',
				'rating',
				'date_added',
				'actions'
			],
		]); ?>'));

		const action = columns.pop()
		const callback = eval(action.render)
		columns.push({
			"data": "actions",
			render: callback
		});

		table = $('#ajax-datatable').DataTable({
			"ajax": "<?php echo $action_ajax; ?>",
			"lengthMenu":  [50, 100, 250,500],
			"processing": true,
			"serverSide": true,
			"order": [
				[0, "desc"]
			],
			"columns": columns
		})
	});

	$(".bulk-delete-button").on('click', function(event) {
		event.preventDefault();
		var ids = [];
		$.each($("input[class='select-me']:checked"), function() {
			ids.push($(this).val());
		});
		if (confirm("Are you sure?")) {
			$.ajax({
				url: '/admin/delete_book_review',
				type: "POST",
				data: {
					ids: ids,
				},
				cache: false,
				success: function(json) {
					if (json.status) {
						success_notify(json.message);
						table.ajax.reload();
					} else {
						error_notify(json.message);
					}
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
