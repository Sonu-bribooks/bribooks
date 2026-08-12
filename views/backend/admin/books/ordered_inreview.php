<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
				<div class="col-md-5 float-right">
					<select class="form-control select2" data-toggle="select2" id="filter_site_id" onchange="window.location='<?= $action_filter ?>?site_id=' + this.value">
						<?php foreach ($sites as $site) {
							if ($site_id == $site['id']) {
						?>
								<option value="<?php echo $site['id']; ?>" selected><?php echo $site['name']; ?></option>
							<?php } else { ?>
								<option value="<?php echo $site['id']; ?>"><?php echo $site['name']; ?></option>
						<?php }
						} ?>
					</select>
					<select class="form-control select2" data-toggle="select2" id="filter_site_id" onchange="window.location='<?= $action_filter ?>?isbn=' + this.value">
						<option value="">select isbn</option>
						<option <?= ($isbn == "empty")?"selected":"" ?> value="empty">empty isbn</option>
						<option <?= ($isbn == "filled")?"selected":"" ?> value="filled">filled isbn</option>
					</select>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>
				<?php
                if (in_array($this->session->userdata('user_id'), book_edit)) {
                    echo '<button type="button" class="btn btn-primary alignToTitle" data-toggle="modal" data-target="#exampleModal">
                        Assign
                    </button>';
                }
                ?>
				<?php include('nav.php'); ?>

				<!-- Modal -->
				<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<select name="reviewr" id="" class="form-control select2 reviewr" data-toggle="select2" name="reviewr" required>
									<option value=''>Select reviewr</option>
									<?php
									foreach ($review_list as $key => $value) {
										echo '<option value=' . $value['id'] . '>' . $value['first_name'] . ' </option>';
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

				<div class="tab-content">
					<div class="tab-pane active" id="home" role="tabpanel">
						<div class="table-responsive mt-4">
							<table id="ajax-datatable" class="table table-striped table-centered mb-0 open-link">
								<thead>
									<tr>
										<th><input type="checkbox" class="select-all"></th>
										<th><?php echo _l('id'); ?></th>
										<th><?php echo _l('theme'); ?></th>
										<th><?php echo _l('user'); ?></th>
										<th><?php echo _l('country'); ?></th>
										<th><?php echo _l('name'); ?></th>
										<th><?php echo _l('isbn'); ?></th>
										<th><?php echo _l('author_name'); ?></th>
										<th><?php echo _l('reviewer'); ?></th>
										<th><?php echo _l('date_added'); ?></th>
										<th><?php echo _l('date_published'); ?></th>
										<th><?php echo _l('date_approved'); ?></th>
										<th><?php echo _l('status'); ?></th>
										<th><?php echo _l('page_count'); ?></th>
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
				'sn',
				'id',
				'theme',
				'user',
				'country',
				'name',
				'isbn',
				'author_name',
				'reviewer',
				'date_added',
				'date_published',
				'date_approved',
				'status',
				'page_count'
			],
			'actions'	=> [
				[
					'key' 		=> 'review',
					'type' 		=> 'review',
					'url' 		=> 'admin/reviewbook/',
				],

			]
		]); ?>'));

		const action = columns.pop()
		const callback = eval(action.render)
		columns.push({
			"data": "actions",
			render: callback
		});

		$('#ajax-datatable').DataTable({
			'aoColumnDefs': [{
				'bSortable': false,
				'aTargets': 0
			}],
			"ajax": "<?php echo $action_ajax; ?>",
			"lengthMenu": [20, 50, 100, 200, 300, 500, 1000],
			"processing": true,
			"serverSide": true,
			"order": [
				[0, "desc"]
			],
			"columns": columns
		})
	});

	$(document).on('click', '.search', function(event) {
		event.preventDefault();
		var endDate = $('.end-date').val();
		var startDate = $('.start-date').val();

		if (Date(startDate) >= Date(endDate)) {
			let columns = JSON.parse(atob('<?php echo _render_column([
				'keys' 		=> [
					'sn',
					'id',
					'theme',
					'user',
					'country',
					'name',
					'author_name',
					'reviewer',
					'date_added',
					'date_published',
					'date_approved',
					'status',
					'page_count'
				],
				'actions'	=> [
					[
						'key' 		=> 'review',
						'type' 		=> 'review',
						'url' 		=> 'admin/reviewbook/',
					],
				]
			]); ?>'));

			const action = columns.pop()
			const callback = eval(action.render)
			columns.push({
				"data": "actions",
				render: callback
			});

			$('#ajax-datatable').DataTable({
				'aoColumnDefs': [{
					'bSortable': false,
					'aTargets': 0
				}],
				"ajax": "<?= $action_ajax ?>&startdate=" + startDate + "&enddate=" + endDate,
				"lengthMenu": [200, 300, 500, 1000],
				"processing": true,
				"serverSide": true,
				"paging": false,
				"destroy": true,
				"order": [
					[0, "desc"]
				],
				"columns": columns
			})
		}
	});

	// $(document).ready(function() {
	// 	$('.open-link .dropright .dropdown-item').addClass('amit');
	// });
</script>
