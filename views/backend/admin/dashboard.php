<style type="text/css">
.table td, .table th { padding: 0.25rem; }
.card-body { padding-bottom: 5px; }
</style>
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?= _l('dashboard') ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-12">
		<div class="card widget-inline">
			<div class="card-body p-0">
				<div class="row no-gutters">
					<div class="col-sm-6 col-xl-3">
						<a href="<?= base_url('admin/students') ?>" class="text-secondary">
							<div class="card shadow-none m-0">
								<div class="card-body text-center">
									<i class="dripicons-user-group text-muted" style="font-size: 24px;"></i>
									<h3><span id="registrations">0</span></h3>
									<p class="text-muted font-15 mb-0"><?= _l('registrations') ?></p>
									<small class="text-success"><b id="new_registrations">0</b> <?= _l('new_registrations') ?></small>
									<span class="text-danger" title="<?= _l('online_users') ?>"><i class="fa fa-user-secret"></i><span id="online_users">0</span></span>
								</div>
							</div>
						</a>
					</div>

					<div class="col-sm-6 col-xl-3">
						<a href="<?= base_url('admin/books') ?>" class="text-secondary">
							<div class="card shadow-none m-0 border-left">
								<div class="card-body text-center">
									<i class="dripicons-bookmark text-muted" style="font-size: 24px;"></i>
									<h3><span id="books">0</span></h3>
									<p class="text-muted font-15 mb-0"><?= _l('books') ?></p>
									<small class="text-success">
										<b id="published_books">0</b> <?= _l('published_books') ?>
										<span class="text-info" title="<?= _l('new_published') ?>"><i class="fa fa-arrow-up"></i><span id="new_published_books">0</span></span>
									</small>
								</div>
							</div>
						</a>
					</div>

					<div class="col-sm-6 col-xl-3">
						<a href="<?= base_url('admin/subscribers') ?>" class="text-secondary">
							<div class="card shadow-none m-0 border-left">
								<div class="card-body text-center">
									<i class="dripicons-network-3 text-muted" style="font-size: 24px;"></i>
									<h3><span id="subscribers">0</span></h3>
									<p class="text-muted font-15 mb-0"><?= _l('subscribers') ?></p>
									<small class="text-success"><b id="new_subscribers">0</b> <?= _l('new_subscribers') ?></small>
								</div>
							</div>
						</a>
					</div>

					<div class="col-sm-6 col-xl-3">
						<a href="<?= base_url('admin/orders') ?>" class="text-secondary">
							<div class="card shadow-none m-0 border-left">
								<div class="card-body text-center">
									<i class="dripicons-cart text-muted" style="font-size: 24px;"></i>
									<h3><span id="orders">0</span></h3>
									<p class="text-muted font-15 mb-0"><?= _l('orders') ?></p>
									<small class="text-success"><b id="new_orders">0</b> <?= _l('new_orders') ?></small>
								</div>
							</div>
						</a>
					</div>

				</div> <!-- end row -->
			</div>
		</div> <!-- end card-box-->
	</div> <!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-4"><?= _l('revenue_this_year') ?></h4>
				<div class="mt-3 chartjs-chart">
					<button class="btn btn-info" onclick="renderRevenueChart();$(this).remove();"><?=_l('load_chart')?></button>
					<canvas id="task-area-chart"></canvas>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card" style="min-height: 240px;">
			<div class="card-body">
				<h4 class="header-title mb-3">
					<?=_l('tickets') ?>
				</h4>
				<div class="table-responsive">
					<table id="ajax-ticket-datatable" class="table table-striped table-centered mb-0">
						<?php
							$fields = [
								'sn',
								'ticket_no',
								'student/school',
								'escalated_by',
								'assigned_department',
								'category',
								'subject',
								'priority',
								'status',
								'date_added',
								'actions',
							];
							$actions = [
								[
									'key'	=> 'view',
									'url'	=> 'admin/ticket_details/',
								],
							];
						?>
						<thead>
							<?php foreach ($fields as $field) { ?>
								<th><?= _l($field) ?></th>
							<?php } ?>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-4"><?= _l('users_this_year_vs_last_year') ?></h4>
				<div class="mt-3 chartjs-chart">
					<button class="btn btn-info" onclick="renderUserChart();$(this).remove();"><?=_l('load_chart')?></button>
					<canvas id="task-bar-chart"></canvas>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="header-title mb-4"><?= _l('book_published_this_year_vs_last_year') ?></h4>
				<div class="mt-3 chartjs-chart">
					<button class="btn btn-info" onclick="renderBookChart();$(this).remove();"><?=_l('load_chart')?></button>
					<canvas id="task-line-chart"></canvas>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card" style="min-height: 240px; max-height: 460px; overflow: auto;">
			<div class="card-body">
				<h4 class="header-title mb-3">
					<?php _el('generate_isbn') ?>
				</h4>
				<div class="table-responsive">
					<table id="ajax-isbn-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<th><?= _l('book_id') ?></th>
							<th><?= _l('book_name') ?></th>
							<th><?= _l('author_name') ?></th>
							<th><?= _l('version') ?></th>
							<th><?= _l('copies') ?></th>
							<th><?= _l('location') ?></th>
							<th><?= _l('source') ?></th>
							<th><?= _l('action') ?></th>
						</thead>
						<tbody>

						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
$.getJSON('<?= base_url('admin/ajax_dashboard_report') ?>', json => {
	for (const [key, value] of Object.entries(json)) {
		$('#' + key).text(value);
	}
})

function ship_medals(user_id = false, book_id = false) {
	alert('In processing...');
	console.log(user_id);
	console.log(book_id);
}
</script>

<script>
function renderISBNBooks() {
	let columns = JSON.parse(atob('<?= _render_column([
		'keys' 		=> [
			'id',
			'book_name',
			'author_name',
			'version',
			'sold',
			'location',
			'source',
			'actions'
		],
	]) ?>'));

	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		data: 'actions',
		render: callback
	});

	$('#ajax-isbn-datatable').DataTable({
		ajax: '<?= base_url('admin/ajax_isbn_eligible_books') ?>',
		processing: true,
		serverSide: false,
		paging: false,
		searching: false,
		ordering:  false,
		order: [[ 0, 'desc' ]],
		columns: columns
	})
}
</script>

<script>
function renderTickets() {
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> array_slice($fields, 0, count($fields) - 1),
		'actions'	=> $actions,
	]); ?>'));

	columns = columns.map(item => {
		if (item.render) {
			const callback = eval(item.render);
			item.render = callback;
		}

		return item;
	});

	$('#ajax-ticket-datatable').DataTable({
		ajax: '<?= base_url('admin/ajax_tickets/1') ?>',
		processing: true,
		serverSide: false,
		searching: false,
		ordering:  false,
		order: [[ 0, 'desc' ]],
		columns: columns
	})
}

$(function() {
	renderTickets();
});
</script>
