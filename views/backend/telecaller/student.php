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
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

				<div class="form-group row mb-3">
					<label class="col-md-9 col-form-label text-right" for="site_id"><?php echo _l('select_site'); ?> </label>
					<div class="col-md-3">
						<select class="form-control select2" data-toggle="select2" id="filter_site_id" data-site="<?=$site_id?>" onchange="window.location='<?= $action_filter ?>?site_id=' + this.value"></select>
					</div>
				</div>
				<div class="form-group row mb-3">
					<label class="col-md-9 col-form-label text-right" for="country"><?php echo _l('select_country'); ?> </label>
					<div class="col-md-3">
						<select class="form-control select2" data-toggle="select2" id="filter_country" onchange="window.location='?country=' + this.value">
							<?php foreach ($country as $countries) {
								if ($country_name == $countries['name']) {
							?>
									<option value="<?php echo ""; ?>" selected><?php echo $countries['name']; ?></option>
								<?php } else { ?>
									<option value="<?php echo $countries['name']; ?>"><?php echo $countries['name']; ?></option>
							<?php }
							} ?>
						</select>
					</div>
				</div>

				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th>#</th>
								<th><?php echo _l('id'); ?></th>
								<th><?php echo _l('image'); ?></th>
								<th><?php echo _l('name'); ?></th>
								<th><?php echo _l('books'); ?></th>
								<th><?php echo _l('country'); ?></th>
								<th><?php echo _l('source'); ?></th>
								<th><?php echo _l('status'); ?></th>
								<th><?php echo _l('date_added'); ?></th>
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
												'image',
												'name',
												'books',
												'location',
												'source',
												'status',
												'date_added',
											],
										]); ?>'));

		$('#ajax-datatable').DataTable({
			"ajax": "<?php echo $action_ajax; ?>",
			"processing": true,
			"serverSide": true,
			"order": [
				[0, "desc"]
			],
			"columns": columns,

		})
	});
</script>
