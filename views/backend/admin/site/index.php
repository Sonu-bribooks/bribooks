<meta name="format-detection" content="telephone=yes"/>

<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
						<a href="<?php echo site_url('admin/site_form/add'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
							<i class="mdi mdi-plus"></i><?php echo _l('add_site'); ?>
						</a>
				</h4>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>
				<div class="table-responsive-sm mt-4">
				<table id="ajax-datatable" class="table table-striped table-siteed mb-0">
					<thead>
						<tr>
							<th>#</th>
							<th><?php echo _l('site_id'); ?></th>
							<th><?php echo _l('name'); ?></th>
							<th><?php echo _l('license'); ?></th>
							<th><?php echo _l('country_code'); ?></th>
							<th><?php echo _l('landing_code'); ?>/<?php echo _l('school_code'); ?></th>
							<th><?php echo _l('owner_details'); ?></th>
							<th><?php echo _l('verified'); ?></th>
							<th><?php echo _l('date_added'); ?></th>
							<th><?php echo _l('actions'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (0) {foreach ($sites as $key => $site): ?>
						<tr>
							<td><?php echo $key+1; ?></td>
							<td><?php echo $site['id']; ?></td>
							<td><?php echo $site['name']; ?></td>
							<td><?php echo $this->student_model->get_all(['site_id' => $site['id']])['total']; ?> / <?php echo $site['license_total']; ?></td>
							<td><?php echo $site['country_code']; ?></td>
							<td><?php echo $site['site_code']; ?></td>
							<td><?php echo $site['owner_email']; ?><br /><a href="tel:+<?php echo $site['owner_mobile']; ?>"><?php echo $site['owner_mobile']; ?></a></td>
							<td><?php echo $site['date_added']; ?></td>
							<td>
								<div class="dropright dropright">
								<button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<i class="mdi mdi-dots-vertical"></i>
								</button>
								<ul class="dropdown-menu">
									<li>
										<a class="dropdown-item" href="<?php echo site_url('admin/site_form/edit/'.$site['id']) ?>"><?php echo _l('edit'); ?></a>
									</li>
									<li>
										<a class="dropdown-item" href="#" onclick="confirm_modal('<?php echo site_url('admin/sites/delete/'.$site['id']); ?>');"><?php echo _l('delete'); ?></a>
									</li>
									<li>
										<a class="dropdown-item" href="<?php echo site_url('admin/export_site_books_details/'.$site['id']) ?>"><?php echo _l('export'); ?></a>
									</li>
								</ul>
							</div>
							</td>
						</tr>
						<?php endforeach; } ?>
					</tbody>
				</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
$(function() {
	<?php
	$actions = [];
	$actions = [
		[
			'key' 		=> 'edit',
			'url' 		=> 'admin/site_form/edit/',
		],
		[
			'key' 		=> 'export',
			'type' 		=> 'confirm',
			'url' 		=> 'admin/export_site_books_details/',
		],
		[
			'key' 		=> 'delete',
			'type' 		=> 'confirm',
			'url' 		=> 'admin/sites/delete/',
		],
		[
			'key' 		=> 'export',
			'type' 		=> 'confirm',
			'url' 		=> 'admin/export_site_books_details/',
		],
		[
			'key' 		=> 'update',
			'url' 		=> 'admin/site_form/update_site/',
		],
	];

	?>
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> [
			'sn',
			'id',
			'name',
			'total',
			'country_code',
			'site_code',
			'owner_details',
			'verified',
			'date_added'
		],
		'actions'	=> $actions
	]); ?>'));

	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		"data": "actions",
		render: callback
	});

	$('#ajax-datatable').DataTable( {
		"ajax": "<?php echo $action_ajax; ?>",
		"processing": true,
		"serverSide": true,
		"order": [[ 0, "desc" ]],
		"columns": columns
	})
});
</script>
