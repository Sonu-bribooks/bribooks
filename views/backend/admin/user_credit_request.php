<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<!-- <a href = "<?php echo $action_add; ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add'); ?></a> -->
				</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>


<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<form action="<?php echo base_url('admin/import_bank_data'); ?>" method="post" enctype="multipart/form-data" id="form-import" class="form-horizontal">
					<div class="form-group">
						<div class="row">
							<label class="col-sm-4 control-label text-right" for="input-file"><?php _el('import_processed_csv_file'); ?></label>
							<div class="col-sm-6">
								<input type="file" name="file" class="form-control" />
							</div>
							<div class="col-sm-2 text-right">
								<button type="submit" class="btn btn-primary"><?php _el('continue'); ?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

				<div class="form-group row mb-3">
					<label class="col-md-9 col-form-label text-right" for="site_id"><?php echo _l('select_site'); ?> </label>
					<div class="col-md-3">
						<select class="form-control select2" data-toggle="select2" id="filter_site_id" data-site="<?=$site_id?>" onchange="window.location='<?= $action_filter ?>?site_id=' + this.value"></select>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-sm-8"></div>
					<div class="col-sm-4 alignToTitle">
						<div class="input-group">
							<div class="flex-fill">
								<select class="form-control select2" data-toggle="select2" id="filter_export_csv">
									<option value="<?= base_url('admin/user_credit_request_export/'. $site_id . '/text/1/inr') ?>"><?=_el('export_to_bank_server')?></option>
									<option value="<?= base_url('admin/user_credit_request_export/'. $site_id . '/csv/1/inr') ?>"><?=_el('export_inr_transfer_requests')?></option>
									<option value="<?= base_url('admin/user_credit_request_export/'. $site_id . '/csv/1/usd') ?>"><?=_el('export_usd_transfer_requests')?></option>
									<option value="<?= base_url('admin/user_credit_request_export/'. $site_id . '/csv/1/aed') ?>"><?=_el('export_aed_transfer_requests')?></option>
									<option value="<?= base_url('admin/user_credit_request_export/'. $site_id . '/csv/2/inr') ?>"><?=_el('export_inr_donation_requests')?></option>
									<option value="<?= base_url('admin/user_credit_request_export/'. $site_id . '/csv/2/usd') ?>"><?=_el('export_usd_donation_requests')?></option>
									<option value="<?= base_url('admin/user_credit_request_export/'. $site_id . '/csv/2/aed') ?>"><?=_el('export_aed_donation_requests')?></option>
								</select>
							</div>
							<div class="input-group-append">
								<button type="button" class="btn btn-primary" id="button_export_csv"><?php _el('export'); ?></button>
							</div>
						</div>
					</div>
				</div>

				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0 open-link">
						<thead>
							<tr>
								<th><input type="checkbox" class="select-all"></th>
								<th><?php echo _l('id'); ?></th>
								<th><?php echo _l('name'); ?></th>
								<th><?php echo _l('user'); ?></th>
								<th><?php echo _l('amount'); ?></th>
								<th><?php echo _l('status'); ?></th>
								<th><?php echo _l('date_added'); ?></th>
								<th><?php echo _l('date_modified'); ?></th>
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
				'id',
				'name',
				'user',
				'amount',
				'status',
				'date_added',
				'date_modified',
			],
			'actions'	=> [

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
			"lengthMenu": [100, 200, 300, 500, 1000],
			"processing": true,
			"serverSide": true,
			"order": [
				[0, "desc"]
			],
			"columns": columns
		})
	});
</script>

<script>
$('form').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();

	$el = $(this);

	submitForm($el.attr('action'), new FormData($el[0]), json => {
		if (json.success) {
			$('form').trigger('reset');
		}

		json.success && success_notify(json.success);
		json.error && error_notify(json.error);
	});
})
</script>
<script>
$('#button_export_csv').on('click', function(e) {
	if (confirm('<?=_l('are_you_sure?')?>')) {
		window.location = $('#filter_export_csv').val();
	}
})
</script>
