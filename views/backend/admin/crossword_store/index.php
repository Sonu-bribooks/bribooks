
<style>
div.dataTables_wrapper div.dataTables_processing {
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    text-align: center;
    padding: 1em 0;
    background-color: rgb(255 255 255 / 20%);
    display: flex;
    justify-content: center;
    align-items: center;
	width: unset;
	margin-left: unset;
    margin-top: unset;
}
.btn-group-sm>.btn, .btn-sm {
    padding: 0.15rem 0.4rem;
    font-weight: 700;
    font-size: .73rem;
    line-height: 1.3;
}
</style>
<div class="row ">
	<div class="col-xl-12">
		<div class="card mb-2">
			<div class="card-body p-2">
				<h5 class="page-title float-left">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				</h5>
				<a href = "<?php echo $action_add; ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add'); ?></a>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link <?= (empty( $this->uri->segment(3)) && ($this->uri->segment(2)) == 'crossword_store')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/crossword_store')?>" role="tab">
								<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
								<span class="d-none d-sm-block"><?=_l('stores')?></span>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link <?= (($this->uri->segment(2) . '/' . $this->uri->segment(3)) == 'crossword_store/book')?'active':'';?>" data-bs-toggle="tab" href="<?=base_url('admin/crossword_store/book')?>" role="tab">
								<span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
								<span class="d-none d-sm-block"><?=_l('books')?></span>
							</a>
						</li>
					</ul>
					<br />

					<?php if (empty( $this->uri->segment(3)) && (($this->uri->segment(2)) == 'crossword_store')) { ?>
						<table id="ajax-datatable" class="table table-striped table-centered mb-0">
							<thead>
								<tr>
								<th>#</th>
								<th><?php echo _l('city'); ?></th>
								<th><?php echo _l('store_name'); ?></th>
								<th><?php echo _l('date_added'); ?></th>
								<th><?php echo _l('date_modified'); ?></th>
								<th><?php echo _l('status'); ?></th>
								<th><?php echo _l('actions'); ?></th>
								</tr>
							</thead>
						</table>
					<?php } else if ((($this->uri->segment(2) . '/' . $this->uri->segment(3)) == 'crossword_store/book')) { ?>
						<table id="ajax-datatable-book" class="table table-striped table-centered mb-0">
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
					<?php } ?>
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
			'city',
			'store_name',
			'date_added',
			'date_modified',
			'status',
		],
		'actions'	=> [
			[
				'key' 		=> 'edit',
				'url' 		=> 'admin/crossword_store_form/edit/',
			],
			[
				'key' 		=> 'delete',
				'type' 		=> 'confirm',
				'url' 		=> 'admin/crossword_store/delete/',
			],
			[
				'key' 		=> 'view_books',
				'url' 		=> 'admin/crossword_store_form/view_books/',
			],
		]
	]); ?>'));

	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		"data": "actions",
		render: callback
	});

	<?php if (empty( $this->uri->segment(3)) && (($this->uri->segment(2)) == 'crossword_store')) { ?>
		$('#ajax-datatable').DataTable( {
		"ajax": "<?php echo $action_ajax; ?>",
		"processing": true,
		"serverSide": true,
		"order": [[ 0, "desc" ]],
		"columns": columns
	})
	<?php } else if ((($this->uri->segment(2) . '/' . $this->uri->segment(3)) == 'crossword_store/book')) { ?>

		var myColumnDefs = [
			{ title: "Sn", data: "sn", width: "15%"},
			{ title: "Store Name", data: "store_name", width: "15%"},
			{ title: "Book Name", data: "book_name", width: "15%"},
			{ title: "Book Isbn", data: "book_isbn", width: "15%"},
			{ title: "Date Added", data: "date_added", width: "20%"},
			{ title: "Status", data: "status", width: "15%"},
			{ title: "Action", data: "action", width: "20%", searchable: false, sortable: false}
		]

		var table = $('#ajax-datatable-book').DataTable( {
			"ajax": "<?php echo $action_ajax_book; ?>",
			"processing": true,
			"serverSide": true,
			"order": [[ 0, "desc" ]],
			"columns": myColumnDefs

		})
	<?php } ?>

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
