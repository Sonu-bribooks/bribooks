<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<div class="col-md-3 float-right">
						<button type="button" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-qr-button" data-toggle="modal" data-target="#exampleModal"><i class="mdi mdi-qr"></i> Add Classes & Sections
						</button>
						<select class="form-control select2" data-toggle="select2" id="filter_site_id" data-site="<?=$site_id?>" onchange="window.location='<?= $action_filter ?>?site_id=' + this.value"></select>
					</div>
				</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<div class="modal fade" id="exampleModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Add Class</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="form">
					<label for="">Select School </label>
					<select name="site_id" class="form-control select2" data-toggle="select2" id="filter_site_id" data-site="<?=$site_id?>"></select>
					<br>
					<br>
					<label for=""> Add Grade</label>
					<br>
					<!-- <select name="name" id="" data-toggle="select2" required class="select2 form-control">
						<option value=""> Select Grade</option>
						<?php

						// for ($i = 1; $i < 13; $i++) {

						// 	echo '<option value="' . $i . '">' . $i . 'th</option>';
						// }
						?>
					</select> -->
					<input type="text" name="name" required class="form-control" id="name" placeholder="Enter grade">
					<br>
					<label for=""> Add Section</label>
					<br>
					<input type="text" name="section" id="name" required class="form-control" placeholder="Enter class Name">
					<br>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary add-class">Save changes</button>
			</div>
		</div>
	</div>
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
										<!-- <th><input type="checkbox" class="select-all"></th> -->
										<!-- <th><?php echo _l('id'); ?></th> -->
										<th><?php echo _l('school name'); ?></th>
										<th><?php echo _l('class name'); ?></th>
										<th><?php echo _l('section'); ?></th>
										<th><?php echo _l('date'); ?></th>
										<!-- <th><?php echo _l('date_published'); ?></th> -->
										<th><?php echo _l('actions'); ?></th>
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
	$(function() {
		let columns = JSON.parse(atob('<?php echo _render_column([
			'keys' 		=> [
				'school_name',
				'class_name',
				'section',
				'date'
			],
			'actions' => [
				[
					'key'	=> 'delete',
					'url'	=> 'admin/school/delete/'
				]
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
			"lengthMenu": [10, 300, 500, 1000],
			"processing": true,
			"serverSide": true,
			"order": [
				[0, "desc"]
			],
			"columns": columns,
			"drawCallback": function(settings) {
				// Here the response
				var response = settings.json;
				console.log(response.date);
			}
		})
	});

	$('.add-class').on('click', function(event) {
		event.preventDefault();

		$.ajax({
			url: 'school/add',
			type: "POST",
			data: $('form').serialize(),
			success: function(response) {
				console.log(response);
				location.reload();
			}
		})
	})

	// $('.bulk-qr-button').on('click',function(){
	// 	event.preventDefault();
	// 	var ids = [];
	// 	$.each($("input[class='select-me']:checked"), function() {
	// 		ids.push($(this).val());
	// 	});
	// 	if (confirm("Are you sure?")) {

	// 		// $.ajax({
	// 		// 	url: '/admin/multi_qr',
	// 		// 	type: "POST",
	// 		// 	data: {
	// 		// 		ids: ids,
	// 		// 	},
	// 		// 	contentType: "image/png",
	// 		// 	cache: false,
	// 		// 	success: function(response) {

	// 		// 		console.log(response);
	// 		// 		// var data = JSON.parse(response);
	// 		// 		// if (data.status)
	// 		// 		// 	location.reload();
	// 		// 		// else
	// 		// 		// 	alert(data.message);
	// 		// 	}
	// 		// });
	// 	}

	// })

	$(".bulk-delete-button").on('click', function(event) {
		event.preventDefault();
		var ids = [];
		$.each($("input[class='select-me']:checked"), function() {
			ids.push($(this).val());
		});
		if (confirm("Are you sure?")) {
			$.ajax({
				url: '/admin/delete_book',
				type: "POST",
				data: {
					ids: ids,
				},
				cache: false,
				success: function(response) {
					var data = JSON.parse(response);
					if (data.status)
						location.reload();
					else
						alert(data.message);
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
