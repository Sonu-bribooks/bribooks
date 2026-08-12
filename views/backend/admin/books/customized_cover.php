<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
			</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>


<div id="accordion">
	<div class="card mb-2">
		<div class="card-header" id="heading-1">
			<h5 class="m-0">
				<a class="collapsed" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<?=_l('filters')?>
				</a>

				<a class="float-right" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<i class="dripicons-view-apps"></i>
				</a>
			</h5>
		</div>
		<div id="collapse-1" class="collapse hide" data-parent="#accordion" aria-labelledby="heading-1">
			<div class="card-body">
				<form class="form" action="#" method="post" id="form-filter">
					<div class="row">
						
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('book_status')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="custom_review_status"
								>
									<option value=""><?=_l('all')?></option>
									<option value="1"><?=_l('accept')?></option>
									<option value="2"><?=_l('reject')?></option>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-8 text-right">
							<div class="btn-group">
								<button type="submit" class="btn btn-info" id="submit-button" > <?php echo _l('search');?></button>
								<button type="button" class="btn btn-danger ml-2" id="filter-reset"> <?php echo _l('reset');?></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
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
							<?php foreach ($fields as $field) { ?>
								<th><?= _l($field) ?></th>
							<?php } ?>
						</tr>
						</thead>
					</table>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<!-- custom cover comment Modal -->
<div class="modal fade" id="customCoverReject" role="dialog" aria-labelledby="holdModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="holdModalLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/add_custom_cover_comment'); ?>" method="post" id="form-custom-cover">
					<input type="hidden" name="book_id" value="" id="cover_book_id" />
					<input type="hidden" name="version" value="" id="cover_version" />
					<div class="form-group">
						<label for="emi_type"><?php echo _l('comment'); ?><span class="required">*</span> </label>
						<select class="form-control select2" data-toggle="select2" name="comment" id="cover_comment" required>
							<?php foreach (CUSTOM_THEME_COMMENT as $value) { ?>
								<option value="<?= $value ?>"><?= $value ?></option>
							<?php } ?>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-custom-cover" class="btn btn-primary"><?=_l('submit')?></button>
			</div>
		</div>
	</div>
</div>

<script>

var table =null;

$(function() {
    var status = '';
	var table_id = '#ajax-datatable';
	var myColumnDefs = [
		{ title: "Sn", data: "sn", width: "15%"},
		{ title: "Id", data: "id", width: "15%"},
		{ title: "Theme", data: "theme", width: "15%"},
		{ title: "User", data: "user", width: "15%"},
		{ title: "Country", data: "country", width: "15%"},
		{ title: "Name", data: "name", width: "15%"},
		{ title: "Author Name", data: "author_name", width: "15%"},
		{ title: "Date Added", data: "date_added", width: "20%"},
		{ title: "Date Published", data: "date_published", width: "20%"},
		{ title: "Date Approved", data: "date_approved", width: "20%"},
		{ title: "Status", data: "status", width: "20%"},
		{ title: "Page Count", data: "page_count", width: "20%"},
		{ title: "Action", data: "actions", width: "20%", searchable: false, sortable: false}
	];

    table = $(table_id).DataTable( {
            "ajax" : {
                'url': '<?php echo $action_ajax; ?>',
                'type': 'GET',
            },
			"processing": true,
			"serverSide": true,
			"order": [[ 0, "desc" ]],
			"columns": myColumnDefs,
            'createdRow': function(row, data, dataIndex) {
                if (data.is_flag) {
                    $(row).css('background-color', '#fde0dc');
                } 
		    }
	   });

});

$(function() {

	$(document).on('click', '#filter-reset', function(e) {
		table.ajax.url('<?= $action_ajax ?>').load();
		$('.input-filter').val('').trigger('change');
	});

	$(document).on('submit', '#form-filter', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$el = $(this);
		let filters = [];
		$el.find('.input-filter').each(function() {
			filters.push($(this).attr('name') + '=' + $(this).val());
		});

		console.log("filter" + filters);

		table.ajax.url('<?= $action_ajax ?>?' + filters.join('&')).load();
	})

	$(document).on('click', '.reject_custom_cover', function(event) {
		event.preventDefault();
		var book_id = $(this).attr('book_id');
		var book_version = $(this).attr('book_version');
        console.log(book_id);
		if (book_id != '') {
			$('#customCoverReject').modal('show');
			$('#cover_book_id').val(book_id);
			$('#cover_version').val(book_version);
		}
	});
});
</script>
