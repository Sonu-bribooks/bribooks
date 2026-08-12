<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				</h4>

				<button
					type="button"
					class="btn btn-warning d-none d-sm-block"
					onclick="$('.left-side-menu').toggle()"
				><?php _el('toggle_menu'); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title float-left"><?php echo $page_title; ?></h4>

				<div class="form-group row mb-3">
					<label class="col-md-9 col-form-label text-right" for="site_id"><?php echo _l('select_site'); ?> </label>
					<div class="col-md-3">
						<select class="form-control select2" data-toggle="select2" onchange="window.location='<?=$action_filter?>?site_id=' + this.value">
							<?php foreach ($sites as $site) {
								if ($site_id == $site['id']) {
							?>
							<option value="<?php echo $site['id']; ?>" selected><?php echo $site['name']; ?></option>
							<?php } else { ?>
							<option value="<?php echo $site['id']; ?>"><?php echo $site['name']; ?></option>
							<?php } } ?>
						</select>
					</div>
				</div>
					<div class="table-responsive mt-2">
						<table id="ajax-datatable" class="table table-striped table-centered mb-0">
							<thead>
								<tr>
									<th><input type="checkbox" class="select-all"></th>
									<th><?php echo _l('id'); ?></th>
									<th><?php echo _l('theme'); ?></th>
									<th><?php echo _l('user'); ?></th>
									<th><?php echo _l('name'); ?></th>
									<th><?php echo _l('author_name'); ?></th>
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
				'name',
				'author_name',
				'date_added',
				'date_published',
				'date_approved',
				'status',
				'page_count'
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
			"lengthMenu": [200, 300, 500, 1000],
			"processing": true,
			"serverSide": true,
			"order": [
				[0, "desc"]
			],
			"columns": columns,

		})
	});

</script>
