<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo $action_add; ?>" class="btn btn-outline-primary btn-rounded alignToTitle ml-1"><i class="mdi mdi-plus"></i><?php echo _l('add_event_school'); ?></a>
				<div class="col-md-3 float-right">
					<select class="form-control select2" id="event_filter" data-toggle="select2">
						<option value="" selected><?=_l('all')?></option>
						<?php foreach ($events as $event) { ?>
						<option value="<?php echo $event['id']; ?>" ><?php echo $event['name']; ?></option>
						<?php } ?>
					</select>
				</div>
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
							<th><?php echo _l('site_name'); ?></th>
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

	tableLoad()
});	

function tableLoad(event_id = 0) {
	var myColumnDefs = [
        { title: "Sn", data: "sn", width: "15%"},
        { title: "Event Name", data: "event_name", width: "15%"},
        { title: "Site Name", data: "site_name", width: "15%"},
        { title: "Date Added", data: "date_added", width: "15%"},
    ]

	$('#ajax-datatable').DataTable().destroy();
	var table = $('#ajax-datatable').DataTable( {
		"ajax" : {
            'url': '<?php echo $action_ajax; ?>',
            'type': 'GET',
            'data': { event_id: event_id },
        },
		"processing": true,
		"serverSide": true,
		"order": [[ 0, "desc" ]],
		"columns": myColumnDefs
        
	})
}
</script>
<script>
$(function() {
	$(document).on('change', '#event_filter', function(e) {
		e.preventDefault();
		e.stopPropagation();
		tableLoad($(this).val())
	})
});
</script>

