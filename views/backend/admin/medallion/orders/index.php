<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				<a href = "<?php echo $action_add; ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-plus"></i><?php echo _l('add'); ?></a>
			</h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<div class="row">
	<div class="col-xl-12">
		<div class="card mb-2">
			<div class="card-body p-2">
				<div class="row">
					<div class="col-sm-8"></div>
					<div class="col-sm-4 alignToTitle text-right">
						<div class="input-group">
							<select name="bulk-send" id="bulk-send" class="form-control bulk-send">
								<option value=""><?=_l('select_bulk_action')?></option>
								<option value="21"><?=_l('move_to_afs')?></option>
								<option value="9"><?=_l('ready_to_ship')?></option>
								<option value="3"><?=_l('send_ship_now')?></option>
								<option value="4"><?=_l('complete_order')?></option>
								<option value="15"><?=_l('mark_as_return')?></option>
							</select>
							<div class="input-group-append">
								<button type="button" class="btn btn-primary" id="bulk-action">
									<?=_l('apply')?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- assign awb modal -->
<div class="modal fade" id="awbModal" role="dialog" aria-labelledby="awbModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="awbModalLabel"><?= _l('assign_awb') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/add_medallion_order_awb'); ?>" method="post" id="form-awb-order">
					<input type="hidden" name="order_id" value="" id="awb_order_id" />
					<div class="form-group">
						<label for="awb"><?php _el('awb'); ?></label>
						<input name="awb" class="form-control" placeholder="<?=_l('enter_awb')?>"/>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-awb-order" class="btn btn-primary"><?=_l('submit')?></button>
			</div>
		</div>
	</div>
</div>
<!-- assign awb modal -->

<!-- comment Modal -->
<div class="modal fade" id="commentModal" role="dialog" aria-labelledby="commentModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="commentModalLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/add_medallion_order_comment'); ?>" method="post" id="form-comment-order">
					<input type="hidden" name="order_id" value="" id="order_id" />
					<div class="form-group">
						<label for="comment"><?php _el('comment'); ?></label>
						<textarea name="comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-comment-order" class="btn btn-primary"><?=_l('submit')?></button>
			</div>
		</div>
	</div>
</div>
<!-- comment Modal -->

<!-- cancelled Modal -->
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
				<form action="<?php echo base_url('admin/cancel_medallion_order'); ?>" method="post" id="form-cancel-order">
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
<!-- cancelled Modal -->

<!-- escalate Modal -->
<div class="modal fade" id="escalateModal" role="dialog" aria-labelledby="escalateModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="escalateModalLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/escalate_medallion_order'); ?>" method="post" id="form-escalate-order">
					<input type="hidden" name="order_id" value="" id="escalate_order_id" />
					<div class="form-group">
						<label for="escalate_comment"><?php _el('comment_for_escalate_order'); ?></label>
						<textarea name="comment" id="escalate_comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-escalate-order" class="btn btn-danger"><?=_l('escalate_order')?></button>
			</div>
		</div>
	</div>
</div>
<!-- escalate Modal -->

<!-- escalate restore Modal -->
<div class="modal fade" id="escalateRestoreModal" role="dialog" aria-labelledby="escalateRestoreModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="escalateRestoreModalLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/escalate_restore_medallion_order'); ?>" method="post" id="form-escalate-restore-order">
					<input type="hidden" name="order_id" value="" id="escalate_restore_order_id" />
					<div class="form-group">
						<label for="escalate_restore_comment"><?php _el('comment_for_escalate_restore_order'); ?></label>
						<textarea name="comment" id="escalate_restore_comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-escalate-restore-order" class="btn btn-danger"><?=_l('escalate_restore_order')?></button>
			</div>
		</div>
	</div>
</div>
<!-- escalate restore Modal -->

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
								<label><?=_l('shiprocket_status')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="shipping_status"
								>
									<option value=""><?=_l('all')?></option>
									<option value="2"><?=_l('not_synced')?></option>
									<option value="1"><?=_l('synced')?></option>
								</select>
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
									<?php foreach ($events as $key => $event) { ?>
										<option value="<?=$event['id']?>"><?=$event['name']?></option>
									<?php } ?>
								</select>
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
								<label><?=_l('order_status')?></label>
								<select
									class="form-control input-filter select2"
									data-toggle="select2"
									name="status"
								>
									<option value=""><?=_l('all')?></option>
									<option value="1"><?=_l('new')?></option>
									<option value="9"><?=_l('ready_to_ship')?></option>
									<option value="21"><?=_l('afs')?></option>
									<option value="3"><?=_l('shipped')?></option>
									<option value="4"><?=_l('delivered')?></option>
									<option value="15"><?=_l('returned')?></option>
									<option value="91"><?=_l('cancelled')?></option>
									<option value="93"><?=_l('escalated')?></option>
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
									<option value="9"><?=_l('ready_to_ship')?></option>
									<option value="3"><?=_l('shipped')?></option>
									<option value="4"><?=_l('delivered')?></option>
									<option value="15"><?=_l('returned')?></option>
									<option value="21"><?=_l('afs')?></option>
									<option value="91"><?=_l('cancelled')?></option>
									<option value="93"><?=_l('escalated')?></option>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-4">
							<button type="button" class="btn btn-warning" id="btn-export" data-type="<?= $medallion_type ?>"> <?php echo _l('export');?></button>
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
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>
				<div class="table-responsive">

					<?php
						$medallion_type == 'school' ? include('school_nav.php') : include('nav.php');
					 ?>

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
								<th><?php echo _l('actions'); ?></th>
							</tr>
						</thead>
					</table>
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

$('#bulk-action').on('click', function(event) {
	event.preventDefault();

	var ids = [];
	$.each($('input[class="select-me"]:checked'), function() {
		ids.push($(this).val());
	});

	if (ids.length == 0) {
		error_notify('<?=_l('select_atleast_one_order')?>')
		return false;
	}

	let status = $('#bulk-send').val()

	if (confirm('<?=_l('Are you sure?')?>')) {
		$.ajax({
			url: '<?=base_url('admin/bulk_medallion_order_update')?>',
			type: 'POST',
			data: {
				ids: ids,
				status: status
			},
			cache: false,
			success: function(json) {
				table.ajax.reload(null, false);
				json.success && success_notify(json.success)
				json.error && error_notify(json.error)
			}
		});
	}
});

$(document).on('click', '.order-complete', function(event) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: 'POST',
			data: 'orderid=' + $(this).data('id') + '&status=' + $(this).data('orderstatus') + '&description=<?php echo _order_status(2); ?>',
			url: '<?=base_url('admin/medallion_order_history')?>',
			success: function(rsp) {
				table.ajax.reload(null, false);
			}
		});
	}
});

$(document).on('click', '.ship-order', function(event) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: 'POST',
			data: 'orderid=' + $(this).data('id') + '&status=' + $(this).data('orderstatus') + '&description=<?php echo _order_status(1); ?>',
			url: '<?=base_url('admin/medallion_order_history')?>',
			success: function(rsp) {
				table.ajax.reload(null, false);
			}
		});
	}
});

$(document).on('click', '.sync-order', function(event) {
	if (confirm('Are you sure?')) {
		const fd = new FormData();
		fd.append('order_id', $(this).data('id'));
		fd.append('order_id', $(this).data('type'));
		submitForm('<?=base_url('admin/sync_medallion_order')?>', fd, json => {
			if (json.success) {
				success_notify(json.success);
				setTimeout(() => table.ajax.reload(null, false), 800);
			}
			json.error && error_notify(json.error);
		});
	}
});

$(document).on('click', '.btn-readyship', function(event) {
	if (confirm('Are you sure?')) {
		const fd = new FormData();
		fd.append('order_id', $(this).data('id'));
		submitForm('<?=base_url('admin/medallion_order_ready_to_ship')?>', fd, json => {
			if (json.success) {
				success_notify(json.success);
				table.ajax.reload(null, false);
			}
			json.error && error_notify(json.error);
		});
	}
});

$(document).on('click', '.btn-fetchawb', function(event) {
	if (confirm('Are you sure?')) {
		const fd = new FormData();
		fd.append('order_id', $(this).data('id'));
		submitForm('<?=base_url('admin/medallion_fetch_awb')?>', fd, json => {
			if (json.success) {
				success_notify(json.success);
				table.ajax.reload(null, false);
			}
			json.error && error_notify(json.error);
		});
	}
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
		e.preventDefault();
		e.stopPropagation();
		if (confirm('<?=_l('are_you_sure?')?>')) {
			$el = $('#form-filter');
			let filters = [];
			$el.find('.input-filter').each(function() {
				filters.push($(this).attr('name') + '=' + $(this).val());
			});

			window.location = '<?= base_url('admin/export_medallion_orders'); ?>/1?' + filters.join('&');
		}
	})
});

function update_date_range() {
	var x = $('.selectedValue').html();
	$('.date_range').val(x);
}

$(document).on('click', '.btn-comment', function() {
	$('#order_id').val($(this).data('id'));
});

$(document).on('click', '.btn-cancel', function() {
	$('#cancel_order_id').val($(this).data('id'));
});

$(document).on('click', '.btn-escalate', function() {
	$('#escalate_order_id').val($(this).data('id'));
});

$(document).on('click', '.btn-escalate-restore', function() {
	$('#escalate_restore_order_id').val($(this).data('id'));
});

$('#form-comment-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);
	if (confirm('<?php echo _li('Are you sure?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#commentModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

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

$('#form-escalate-restore-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	if ($('#escalate_restore_order_id').val() === '') {
		error_notify('<?=_li('invalid_order')?>')
		return false;
	}

	if ($('#escalate_restore_comment').val() === '') {
		error_notify('<?=_li('comment_required_for_escalate_the_order')?>')
		return false;
	}

	if (confirm('<?php echo _li('Are you sure to restore the escalated order?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#escalateRestoreModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$('#form-escalate-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	if ($('#escalate_order_id').val() === '') {
		error_notify('<?=_li('invalid_order')?>')
		return false;
	}

	if ($('#escalate_comment').val() === '') {
		error_notify('<?=_li('comment_required_for_escalate_the_order')?>')
		return false;
	}

	if (confirm('<?php echo _li('Are you sure to escalate the order?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#escalateModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$(document).on('click', '.btn-awb-assign', function() {
	$('#awb_order_id').val($(this).data('id'));
	$('#awbModal').modal('show');
});

$('#form-awb-order').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);
	if (confirm('<?php echo _li('Are you sure?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.success);
				$('#awbModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

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

$(document).on('click', '[data-copy]', function() {
	navigator.clipboard.writeText($(this).data('copy'));
});
</script>
