<div class="row ">
	<div class="col-xl-12">
		<div class="card mb-2">
			<div class="card-body p-2">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                	<a href="<?= $action_add; ?>" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle ml-2"><i class="mdi mdi-plus"></i> <?=_li('add') ?></a>

                </h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?= $page_title . (($active_tab === 'event') ? ' '. _l('event_list') : ' ' . _l('country_list')); ?></h4>
				<?php include('nav.php'); ?>
				<div class="tab-content">
					<div class="tab-pane active" id="home" role="tabpanel">
						<div class="table-responsive mt-2">
							<table id="ajax-datatable" class="table table-striped table-centered mb-0">
								<thead>
									<tr>
										<th>#</th>
										<th><?php echo _l('id'); ?></th>
                                        <?php if ($active_tab === 'event') { ?>
                                            <th><?php echo _l('event_id'); ?></th>
										    <th><?php echo _l('event_name'); ?></th>
                                        <?php } ?>
										<th><?php echo _l('country_code'); ?></th>
										<th><?php echo _l('country_name'); ?></th>
										<th><?php echo _l('book_sold'); ?></th>
										<th><?php echo _l('priority'); ?></th>
										<!-- <th><?php echo _l('date_added'); ?></th>
										<th><?php echo _l('status'); ?></th> -->
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
var table = null;
</script>
<script>
$(function() {
	let columns = JSON.parse(atob('<?=_render_column([
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

	table = $('#ajax-datatable').DataTable({
		aoColumnDefs: [{
			bSortable: false,
			aTargets: 0
		}],
		ajax: '<?php echo $action_ajax; ?>',
		lengthMenu: [10, 20, 50, 100, 200, 500, 1000],
		processing: true,
		serverSide: true,
		searching: true,
		order: [
			[0, 'desc']
		],
		columns: columns,
		createdRow: function(row, data, dataIndex) {
			if(data.custom_theme == 'YES') {
				$(row).css('background-color', '#fde0dc');
			}
		}
	})
});

</script>
