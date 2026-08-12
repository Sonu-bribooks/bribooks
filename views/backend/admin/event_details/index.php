<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo $action_add; ?>" class="btn btn-outline-primary btn-rounded alignToTitle ml-1"><i class="mdi mdi-plus"></i><?php echo _l('add_event_detail'); ?></a>
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
							<th><?php echo _l('event_name'); ?></th>
							<th><?php echo _l('powered_by'); ?></th>
							<th><?php echo _l('status'); ?></th>
							<th><?php echo _l('date_added'); ?></th>
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
        { title: "Event Name", data: "event_name", width: "15%"},
        { title: "Powered By", data: "powered_by", width: "15%"},
        { title: "Status", data: "status", width: "15%"},
        { title: "Date Added", data: "date_added", width: "15%"},
        { title: "Action", data: "action", width: "20%", searchable: false, sortable: false}
    ]

	var table = $('#ajax-datatable').DataTable( {
        "ajax": "<?php echo $action_ajax; ?>",
		"processing": true,
		"serverSide": true,
		"order": [[ 0, "desc" ]],
		"columns": myColumnDefs
        
	})

});	
</script>

