<?php
$event_list = $this->event_model->get_all()['rows'] ?? [];
?>
<div class="row ">
	<div class="col-xl-12">
		<div class="card mb-2">
			<div class="card-body p-2">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					
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
				<?php include('nav.php'); ?>
				<div class="tab-content">
					<div class="tab-pane active" id="home" role="tabpanel">
						<div class="table-responsive mt-2">
							<table id="ajax-datatable" class="table table-striped table-centered mb-0">
								<thead>
									<tr>
										<th><input type="checkbox" class="select-all"></th>
										<th><?php echo _l('id'); ?></th>
										<th><?php echo _l('theme'); ?></th>
										<th><?php echo _l('user'); ?></th>
										<th><?php echo _l('country'); ?></th>
										<th><?php echo _l('name'); ?></th>
										<th><?php echo _l('author_name'); ?></th>
										<th><?php echo _l('date_added'); ?></th>
										<th><?php echo _l('date_published'); ?></th>
										<th><?php echo _l('date_approved'); ?></th>
										<th><?php echo _l('date_title_verso'); ?></th>
										<th><?php echo _l('status'); ?></th>
										<th><?php echo _l('sold_book'); ?></th>
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
var table = null;
</script>
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
			'author_name',
			'date_added',
			'date_published',
			'date_approved',
			'date_title_verso',
			'status',
			'sold_book',
			'page_count',
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
		'aoColumnDefs': [{
			'bSortable': false,
			'aTargets': 0
		}],
		"ajax": "<?php echo $action_ajax; ?>",
		"lengthMenu": [10, 20, 50, 100, 200, 500, 1000],
		"processing": true,
		"serverSide": true,
		"order": [
			[0, "desc"]
		],
		"columns": columns,
		'createdRow': function(row, data, dataIndex) {
            if(data.event_id == 9) {
                $(row).css('background-color', '#EBFFE5');
            } else if(data.custom_theme == 'YES') {
                $(row).css('background-color', '#fde0dc');
            }
        }
	})
});
</script>

<script>
function update_date_range() {
	var x = $('.selectedValue').html();
	$('.date_range').val(x);
}
</script>
