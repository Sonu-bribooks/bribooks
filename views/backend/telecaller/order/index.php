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

<!-- comment Modal -->
<div class="modal fade" id="commentModel" role="dialog" aria-labelledby="commentModelLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="commentModelLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('telecaller/add_order_comment'); ?>" method="post" id="form-comment">
					<input type="hidden" name="order_id" value="" id="order_id" />
					<div class="form-group">
						<label for="comment"><?php _el('comment'); ?></label>
						<textarea name="comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-comment" class="btn btn-primary"><?=_l('submit')?></button>
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
						<div class="col-sm-6">
							<div class="form-group mb-2">
								<label><?=_l('order_date')?></label>
								<div class="form-control" data-toggle="date-picker-range" data-target-display="#selectedValue"  data-cancel-class="btn-light" style="width: 100%;">
									<i class="mdi mdi-calendar"></i>&nbsp;
									<span id="selectedValue" class="selectedValue">
										<?php echo date("F d, Y" , $timestamp_start) . " - " . date("F d, Y" , strtotime('-1 day', $timestamp_end));?>
									</span> <i class="mdi mdi-menu-down"></i>
								</div>
								<input
									id="date_range1"
									type="hidden"
									name="date_range"
									class="input-filter date_range"
									value="<?php echo date("d F, Y" , $timestamp_start) . " - " . date("d F, Y" , strtotime('-1 day', $timestamp_end));?>"
								>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('printing_status')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="printing_status"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('not_printed')?></option>
									<option value="1"><?=_l('printed')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('Region')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="currency_id"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('Globals')?></option>
									<option value="47"><?=_l('Domestic')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('order_status')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="status"
								>
									<option value=""><?=_l('all')?></option>
									<option value="1"><?=_l('new')?></option>
									<option value="2"><?=_l('in_print')?></option>
									<option value="8"><?=_l('printed')?></option>
									<option value="9"><?=_l('ready_to_ship')?></option>
									<option value="3"><?=_l('shipped')?></option>
									<option value="4"><?=_l('delivered')?></option>
									<option value="10"><?=_l('reprint')?></option>
									<option value="15"><?=_l('return')?></option>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4">
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
								<th><?php echo _l('order_code'); ?></th>
								<th><?php echo _l('customer'); ?></th>
								<th><?php echo _l('product'); ?></th>
								<th><?php echo _l('history'); ?></th>
								<th><?php echo _l('order_date'); ?></th>
								<th><?php echo _l('printer'); ?></th>
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
var table = null;
</script>
<script>
	$(function() {
		let columns_length = <?=json_encode([10, 20, 50])?>;
		let columns = JSON.parse(atob('<?php echo _render_column([
			'keys' 		=> [
				'sn',
				'order_code',
				'customer',
				'product',
				'history',
				'date_added',
				'printer',
				'actions'
			]
		]); ?>'));

		const action = columns.pop()
		const callback = eval(action.render)
		columns.push({
			'data': 'actions',
			render: callback
		});

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
			}
		})
	});
</script>

<script>
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
</script>

<script>
function update_date_range() {
	var x = $('.selectedValue').html();
	$('.date_range').val(x);
}
</script>

<script>
$(document).on('click', '.btn-comment', function() {
	$('#order_id').val($(this).data('id'));
});
</script>

<script>
$('#form-comment').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);
	if (confirm('<?php echo _li('Are you sure?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#commentModel').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});
</script>

<script>
$(document).on('click', '[data-copy]', function() {
	navigator.clipboard.writeText($(this).data('copy'));
});
</script>
