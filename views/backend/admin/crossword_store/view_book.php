<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo $action_add; ?>" class="btn btn-outline-primary btn-rounded alignToTitle ml-1"><i class="mdi mdi-plus"></i><?php echo _l('add_book'); ?></a>
				<a href = "javascript:void(0)" class="btn btn-outline-primary btn-rounded alignToTitle" onclick="history.back()"><i class="mdi mdi-hand-pointing-left"></i><?php echo _l('back'); ?></a>
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
				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
						<tr>
							<th>#</th>
							<th><?php echo _l('store_name'); ?></th>
							<th><?php echo _l('book_name'); ?></th>
							<th><?php echo _l('book_isbn'); ?></th>
							<th><?php echo _l('date_added'); ?></th>
							<th><?php echo _l('status'); ?></th>
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

    var myColumnDefs = [
        { title: "Sn", data: "sn", width: "15%"},
        { title: "Store Name", data: "store_name", width: "15%"},
        { title: "Book Name", data: "book_name", width: "15%"},
        { title: "Book Isbn", data: "book_isbn", width: "15%"},
        { title: "Date Added", data: "date_added", width: "20%"},
        { title: "Status", data: "status", width: "15%"},
        { title: "Action", data: "action", width: "20%", searchable: false, sortable: false}
    ]

	var table = $('#ajax-datatable').DataTable( {
        "ajax" : {
            'url': '<?php echo $action_ajax; ?>',
            'type': 'GET',
            'data': { store_id: '<?php echo $id; ?>' },
        },
		"processing": true,
		"serverSide": true,
		"order": [[ 0, "desc" ]],
		"columns": myColumnDefs

	})

});

	$(document).on('click', '.statusBtn', function(event) {
		event.preventDefault();

    	var book_store_id = $(this).attr('store_id');
    	var status        = $(this).attr('store_book_status');

        if (confirm('<?=_l('Are you sure?')?>')) {
			$.ajax({
				url: '<?=base_url('admin/update_crossword_book_status')?>',
				type: 'POST',
				data: {
					id: book_store_id,
					status: status
				},
				cache: false,
				success: function(json) {
					location.reload();
				}
			});
		}
	});
</script>
