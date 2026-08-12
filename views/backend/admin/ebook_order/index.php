<?php
$printed_status = array('/admin/in_print_order', '/admin/orders');
$role_id  =  $this->session->userdata('role_id');
$printer_list = $this->student_model->get_by_role_id(12);
$event_list = $this->event_model->get_all()['rows'] ?? [];
$site_list = $this->site_model->get_all(['site_codes' => PARENT_SITE_CODES])['rows'] ?? [];
$printer_assignment_list = $this->printer_assignment_model->get_all();
?>
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

<!-- cancel comment Modal -->
<div class="modal fade" id="cancelModal" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="cancelModalLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/ajax_cancel_order'); ?>" method="post" id="form-cancel-order">
					<input type="hidden" name="order_id" value="" id="cancel_order_id" />
					<div class="form-group">
						<label for="cancel_comment"><?php _el('comment_for_cancel_order'); ?></label>
						<textarea name="comment" id="cancel_comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-cancel-order" class="btn btn-danger"><?=_l('cancel_order')?></button>
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
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label for="book_id"><?php echo _l('select_book'); ?></label>
								<select class="form-control input-filter select2" data-toggle="select2" name="book_id" id="book_id">
									<option value=""><?php echo _l('select_a_book'); ?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('order_status_not_equal_to')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="ne_status"
								>
									<option value=""><?=_l('all')?></option>
									<option value="1"><?=_l('new')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('has_isbn')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="has_isbn"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('no')?></option>
									<option value="1"><?=_l('yes')?></option>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('transaction_id')?></label>
								<input
									id="ext_transaction_id"
									type="text"
									name="ext_transaction_id"
									class="form-control input-filter"
									value=""
								>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('page_count_less_than')?></label>
								<input
									id="page_count_le"
									type="number"
									name="page_count_le"
									class="form-control input-filter"
									value="0"
								>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('page_count_greater_than')?></label>
								<input
									id="page_count_ge"
									type="number"
									name="page_count_ge"
									class="form-control input-filter"
									value="0"
								>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_event')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="event_id"
								>
									<option value=""><?=_l('all')?></option>
									<?php foreach ($event_list ?? [] as $key => $value) { ?>
										<option <?php if(!empty($event_id) && ($event_id == $value['id'])) { echo 'selected'; } ?> value="<?= $value['id']; ?>"><?= $value['name'] . ' (' . $value['id'] . ')'; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="form-group mb-2">
								<label><?=_l('select_site')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="site_code"
								>
									<option value=""><?=_l('all')?></option>
									<?php foreach ($site_list ?? [] as $key => $value) { ?>
										<option value="<?=$value['site_code']?>"><?=$value['name']?></option>
									<?php } ?>
								</select>
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
					<?php include($navigation . '.php'); ?>

					<br />

					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th><input type="checkbox" class="select-all"></th>
								<th><?php echo _l('sn'); ?></th>
								<th><?php echo _l('order_code'); ?></th>
								<th><?php echo _l('customer'); ?></th>
								<th style="width: 160px;"><?php echo _l('product'); ?></th>
								<th><?php echo _l('weight_amount'); ?></th>
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
	$(document).on('click', '.search', function(event) {
		event.preventDefault();
		var endDate = $('.end-date').val();
		var startDate = $('.start-date').val();

		table.ajax.url('<?= $action_ajax ?>?startdate=' + startDate + '&enddate=' + endDate).load();
	});

	$(function() {
		let columns_length = <?=in_array($this->session->userdata('role_id'), [1]) ? json_encode([10, 20, 50, 100, 200, 500, 1000]) : json_encode([10, 20, 50])?>;
		let columns = JSON.parse(atob('<?php echo _render_column([
			'keys' 		=> [
				'#',
				'sn',
				'order_code',
				'customer',
				'product',
				'weight_amount',
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

	$(document).on('click', '.export-csv', function(event) {
		var endDate = $('.end-date').val();
		var startDate = $('.start-date').val();
		if (startDate == "") {
			alert('Kindly Fill start date')
		}
	});

	$('.select-all').click(function() {
		if (this.checked) {
			$(':checkbox').each(function() {
				$(this).prop('checked', true).trigger('change');
			});
		} else {
			$('.select-me').each(function() {
				$(this).prop('checked', false).trigger('change');
			});
		}
	});

	$(document).on('click', '.select-me', function(event) {
		if (this.checked) {
			$(this).prop('checked', true).trigger('change');
		} else {
			$(this).prop('checked', false).trigger('change');
		}
		$('.select-all').prop('checked', false).trigger('change');
	});
</script>
<script>
$(function() {
	$(document).on('change', '#currency_code', function() {
		$el = $(this);
		table.ajax.url('<?= $action_ajax ?>?currency=' + $el.val()).load();
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
$(function() {
	$(document).on('click', '#btn-export', function(e) {
		e.preventDefault();
		e.stopPropagation();
		if (confirm('<?=_l('are_you_sure?')?>')) {
			$el = $('#form-filter');
			let filters = [];
			$el.find('.input-filter').each(function() {
				filters.push($(this).attr('name') + '=' + $(this).val());
			});

			window.location = '<?= base_url('admin/export_orders/' . ($navigation == 'ge_nav' ? 2 : 47)); ?>/0?' + filters.join('&');
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
<script>
$(document).on('click', '.btn-cancel', function() {
	$('#cancel_order_id').val($(this).data('id'));
});

$(document).on('click', '.btn-refund', function(event) {
	if (confirm('Are you sure to refund the order?')) {
		$.ajax({
			type: 'POST',
			data: 'orderid=' + $(this).data('id'),
			url: '<?=base_url('admin/refund_order')?>',
			success: function(rsp) {
				table.ajax.reload(null, false);
			}
		});
	}
});
</script>
<script>
$('#form-cancel-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	if ($('#cancel_order_id').val() === '') {
		error_notify('<?=_li('invalid_order')?>')
		return false;
	}

	if ($('#cancel_comment').val() === '') {
		error_notify('<?=_li('comment_required_for_cancel_the_order')?>')
		return false;
	}

	if (confirm('<?php echo _li('Are you sure to cancel the order?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#cancelModal').modal('hide');
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
$(function() {
	$('#book_id').select2({
		ajax: {
			url: '<?php echo site_url('admin/ajax_filter_books'); ?>',
			data: function (params) {
				var query = {
					search: params.term,
				}

				return query;
			},
			processResults: function(data) {
				return {
					results: data.items
				};
			}
		}
	});
});
</script>

<script>
$(document).on('click', '[data-copy]', function() {
	navigator.clipboard.writeText($(this).data('copy'));
});
</script>
