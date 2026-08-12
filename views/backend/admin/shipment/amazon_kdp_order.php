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
			</div>
		</div>
	</div>
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
						<div class="col-sm-4">
							<div class="form-group mb-2">
								<label><?=_l('order_date')?></label>
								<div class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"  data-cancel-class="btn-light" style="width: 100%;">
									<i class="mdi mdi-calendar"></i>&nbsp;
									<span id="selectedValue" class="selectedValue">
										<?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y" , $timestamp_end);?>
									</span> <i class="mdi mdi-menu-down"></i>
								</div>
								<input
									id="date_range1"
									type="hidden"
									name="date_range"
									class="input-filter date_range"
									value="<?php echo date("d F, Y" , $timestamp_start) . " - " . date("d F, Y" , $timestamp_end);?>"
								>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4">
							<button type="button" class="btn btn-warning" id="btn-export"> <?php echo _l('export');?></button>
						</div>
						<div class="col-sm-8 text-right">
							<div class="btn-group">
								<button type="submit" class="btn btn-info" id="submit-button" onclick="update_date_range();"> <?php echo _l('search');?></button>
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
				<div class="table-responsive">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th><?php echo _l('sn'); ?></th>
								<th><?php echo _l('book_name'); ?></th>
								<th><?php echo _l('author_name'); ?></th>
								<th><?php echo _l('quantity'); ?></th>
								<th><?php echo _l('isbn'); ?></th>
								<th><?php echo _l('marketplace'); ?></th>
								<th><?php echo _l('currency'); ?></th>
								<th><?php echo _l('price'); ?></th>
								<th><?php echo _l('order_date'); ?></th>
								<th><?php echo _l('status'); ?></th>
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
var table = null;

$(document).on('click', '.search', function(event) {
	event.preventDefault();
	var endDate = $('.end-date').val();
	var startDate = $('.start-date').val();

	table.ajax.url('<?= $action_ajax ?>?startdate=' + startDate + '&enddate=' + endDate).load();
});

$(function() {
	let columns_length = <?= json_encode([10, 20, 50, 100, 200, 500, 1000]); ?>;
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> [
			'sn',
			'book_name',
			'author_name',
			'quantity',
			'isbn',
			'marketplace',
			'currency_code',
			'price',
			'order_date',
			'status',
			'date_added'
		]
	]); ?>'));

	table = $('#ajax-datatable').DataTable({
		'aoColumnDefs': [{
			'bSortable': false,
			'aTargets': 0
		}],
		'ajax': '<?php echo $action_ajax; ?>',
		'lengthMenu': columns_length,
		'processing': true,
		'serverSide': true,
		'order': [
			[0, 'desc']
		],
		'columns': columns,
		'language': {
			'loadingRecords': '&nbsp;',
			'processing': '<div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"><span class="sr-only">Loading...</span></div>'
		},
		'createdRow': function(row, data, dataIndex) {
            if(data.is_duplicate) {
                $(row).css('background-color', '#fde0dc');
            }
        }
	})
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

		table.ajax.url('<?= $action_ajax ?>?' + filters.join('&')).load();
	})
});

$(function() {
	$(document).on('click', '#btn-export', function(e) {
		update_date_range();

		e.preventDefault();
		e.stopPropagation();
		if (confirm('<?=_l('are_you_sure?')?>')) {
			$el = $('#form-filter');
			let filters = [];
			$el.find('.input-filter').each(function() {
				filters.push($(this).attr('name') + '=' + $(this).val());
			});

			window.location = '<?=base_url('admin/export_amazon_book_published')?>?' + filters.join('&');
		}
	})
});

function update_date_range() {
	var x = $('.selectedValue').html();
	$('.date_range').val(x);
}
</script>
