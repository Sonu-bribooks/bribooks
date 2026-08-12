<style>
.form-control.input-filter {
	width: 100% !important;
}
</style>
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<div class="col-md-3 float-right">
						<button type="button" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-qr-button" data-toggle="modal" data-target="#exampleModal"><i class="mdi mdi-qr"></i> Add School
						</button>
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
					<label for="">Select City</label>
					<select class="select2 form-control" data-toggle="select2" required name="city_id">
						<option value="">Select</option>
						<?php
						foreach ($cities as $key => $value) {
							if ($city_id == $value['id']) {
								echo '<option value="' . $value['id'] . '" selected>' . $value['name'] . ' - ' . $value['id'] . '</option>';
							} else {
								echo '<option value="' . $value['id'] . '">' . $value['name'] . ' - ' . $value['id'] . '</option>';
							}
						}
						?>
					</select>
					<br>
					<br>
					<label for=""> School (use comma for multiple)</label>
					<br>
					<input type="text" name="name" required class="form-control" id="name" placeholder="Enter school">
					<br>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary add-school">Save changes</button>
			</div>
		</div>
	</div>
</div>

<div id="accordion">
	<div class="card mb-2">
		<div class="card-header" id="heading-1">
			<h5 class="m-0">
				<a class="collapsed" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<?=_l('filters')?>
				</a>

				<a class="float-right" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<i class="dripicons-view-apps"></i>
				</a>
			</h5>
		</div>
		<div id="collapse-1" class="collapse hide" data-parent="#accordion" aria-labelledby="heading-1">
			<div class="card-body">
				<form class="form-inline" action="#" method="post" id="form-filter">
					<div class="row col-sm-12">
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_country')?></label>
								<select class="form-control input-filter select2" data-toggle="select2" name="country_id" required>
									<option value="">Select</option>
									<?php foreach ($countries as $country) {
										if (strtolower($country['name']) == 'india') {
											echo '<option value="' . $country['id'] . '" selected>' . $country['name'] . '</option>';
										} else {
											echo '<option value="' . $country['id'] . '">' . $country['name'] . '</option>';
										}
									?>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_state')?></label>
								<select class="form-control input-filter select2" data-toggle="select2" name="state_id">
									<option value="">Select</option>
									<?php foreach ($states as $state) { ?>
									<option value="<?php echo $state['id']; ?>"><?php echo $state['name']; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_city')?></label>
								<select class="form-control input-filter select2" data-toggle="select2" name="city_id">
									<option value="">Select</option>
									<?php foreach ($cities as $city) { ?>
									<option value="<?php echo $city['id']; ?>"><?php echo $city['name']; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('has_registered')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="has_registered"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('no')?></option>
									<option value="1"><?=_l('yes')?></option>
								</select>
							</div>
						</div>
					</div>
					<div class="col-sm-4 pl-0">
						<button type="button" class="btn btn-warning" id="btn-export"> <?php echo _l('export');?></button>
					</div>
					<div class="col-sm-8 text-right">
						<div class="btn-group">
							<button type="submit" class="btn btn-info" id="submit-button"> <?php echo _l('search');?></button>
							<button type="button" class="btn btn-danger ml-2" id="filter-reset"> <?php echo _l('reset');?></button>
						</div>
					</div>
				</form>
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
										<th><?php echo _l('school_id'); ?></th>
										<th><?php echo _l('school name'); ?></th>
										<th><?php echo _l('city'); ?></th>
										<th><?php echo _l('state'); ?></th>
										<th><?php echo _l('date'); ?></th>
										<!-- <th><?php echo _l('actions'); ?></th> -->
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

	$(function() {
		$(document).on('click', '#filter-reset', function(e) {
			table.ajax.url('<?= $action_ajax ?>').load();
			$('.input-filter').val('').trigger('change');
		});

		$(document).on('submit', '#form-filter', function(e) {
			e.preventDefault();
			e.stopPropagation();
			$el = $(this);
			let filters = [];
			$el.find('.input-filter').each(function() {
				filters.push($(this).attr('name') + '=' + $(this).val());
			});

			table.ajax.url('<?= $action_ajax ?>?' + filters.join('&')).load();
		});

		$(document).on('click', '#btn-export', function(e) {
			e.preventDefault();
			e.stopPropagation();
			if (confirm('<?=_l('are_you_sure?')?>')) {
				$el = $('#form-filter');
				let filters = [];
				$el.find('.input-filter').each(function() {
					filters.push($(this).attr('name') + '=' + $(this).val());
				});

				window.location = '<?=base_url('admin/export_school_input/')?>?' + filters.join('&');
			}
		});

		let columns_length = <?=in_array($this->session->userdata('role_id'), [1]) ? json_encode([10, 20, 50, 100, 200, 500, 1000]) : json_encode([10, 20, 50])?>;

		let columns = JSON.parse(atob('<?php echo _render_column([
			'keys' 		=> [
				'id',
				'school_name',
				'city',
				'state',
				'date'
			],
			/*'actions' => [
				[
					'key'	=> 'delete',
					'url'	=> 'admin/all_school/delete/'
				]
			]*/

		]); ?>'));

		/*const action = columns.pop()
		const callback = eval(action.render)
		columns.push({
			"data": "actions",
			render: callback
		});*/

		table = $('#ajax-datatable').DataTable({
			"ajax": "<?php echo $action_ajax; ?>",
			"lengthMenu": columns_length,
			"processing": true,
			"serverSide": true,
			"order": [
				[0, "desc"]
			],
			"columns": columns,
			"language": {
				"loadingRecords": '&nbsp;',
				"processing": '<div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"><span class="sr-only">Loading...</span></div>'
			}
		})
	});

	$('.add-school').on('click', function(event) {
		event.preventDefault();

		$.ajax({
			url: 'all_school/add',
			type: "POST",
			data: $('#form').serialize(),
			success: function(response) {
				location.reload();
			}
		})
	})

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
