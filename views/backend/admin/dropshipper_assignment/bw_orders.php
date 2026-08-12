<style type="text/css">
	.btn {
		padding: .3rem .5rem;
	}
</style>
<div class="row">
	<div class="col-xl-12">
		<div class="card mb-2">
			<div class="card-body p-2">
				<h5 class="page-title float-left">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				</h5>
				
				<div class="row">
					<div class="col-sm-8"></div>
					<div class="col-sm-4 alignToTitle text-right">
						<div class="input-group">
							<select name="bulk-send" id="bulk-send" class="form-control bulk-send">
								<option value=""><?=_l('select_bulk_action')?></option>
								<option value="2"><?=_l('send_to_print')?></option>
								<option value="8"><?=_l('mark_as_printed')?></option>
								<option value="21"><?=_l('move_to_afs')?></option>
								<option value="9"><?=_l('ready_to_ship')?></option>
								<option value="3"><?=_l('send_ship_now')?></option>
								<option value="4"><?=_l('complete_order')?></option>
								<option value="10"><?=_l('mark_as_reprint')?></option>
								<option value="15"><?=_l('mark_as_return')?></option>
							</select>
							<div class="input-group-append">
								<button type="button" class="btn btn-primary" id="bulk-dropshipper-action">
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

<!-- change version modal-->
<div class="modal fade" id="changeVersionModal" role="dialog" aria-labelledby="changeVersionModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="changeVersionModalLabel"><?= _l('change_version') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/change_order_version'); ?>" method="post" id="form-change-version">
					<input type="hidden" name="order_id" value="" />
					<div id="change-version-form-content"></div>
					<div class="form-group">
						<label for="comment"><?php _el('comment'); ?></label>
						<textarea name="comment" rows="6" class="form-control" required></textarea>
					</div>
					<div class="form-group">
						<label for="order_history"><?php _el('add_to_order_history'); ?></label>
						<select name="order_history" id="order_history" class="form-control select2" data-toggle="select2">
							<option value="1"><?=_l('yes')?></option>
							<option value="0"><?=_l('no')?></option>
						</select>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-change-version" class="btn btn-primary"><?=_l('submit')?></button>
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
				<form action="<?php echo base_url('admin/ajax_cancel_dropshipper_order'); ?>" method="post" id="form-cancel-order">
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

<!-- rollback Modal -->
<div class="modal fade" id="rollbackModal" role="dialog" aria-labelledby="rollbackModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="rollbackModalLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/ajax_dropshipper_rollback'); ?>" method="post" id="form-rollback-order">
					<input type="hidden" name="order_id" value="" id="rollback_order_id" />
					<div class="form-group">
						<label for="rollback_comment"><?php _el('comment_for_rollback'); ?></label>
						<textarea name="comment" id="rollback_comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-rollback-order" class="btn btn-danger"><?=_l('rollback_order')?></button>
			</div>
		</div>
	</div>
</div>

<div id="accordion">
	<div class="card">
		<div class="card-header" id="heading-1">
			<h5 class="mb-0">
				<a class="collapsed" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<?=_l('filters')?>
				</a>

				<a class="float-right" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<i class="dripicons-view-apps"></i>
				</a>
			</h5>
		</div>

		<div id="collapse-1" class="collapse hide" data-parent="#accordion" aria-labelledby="heading-1">
			<div class="card-body p-5">
				<div>
					<span><?=_l('enter_start_date')?></span>
					<input class="form-control alignToTitle start-date" name="start-date" data-provide="datepicker" placeholder="<?=_l('enter_start_date')?>">
				</div>
				<div>
					<span><?=_l('enter_end_date')?></span>
					<input class="form-control alignToTitle end-date" name="end-date" data-provide="datepicker" placeholder="<?=_l('enter_end_date')?>">
				</div>
				<button class="btn btn-primary alignToTitle search"><?=_l('search')?></button>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<?php if ($status == 2) { ?>
				<div class="text-right d-none">
					<button data-href="<?= base_url('dropShipper/bulkDownloadBookPdf') ?>" class="btn btn-info" id="gen-pdf">
						<?=_l('generate_bulk_download_pdf') ?>
					</button><br /><br />
					<span class="badge badge-dark" id="tentative_time">
					<?php if (!empty($last_request['date_tentative'])) { ?>
						<?=_li('Expected_time_for_download ') . formatDate($last_request['date_tentative'])?>
					<?php } ?>
					</span><br />
					<?php if (!empty($last_download['file'])) { ?>
					<a href="<?=base_url('dropShipper/downloadZip/' . $last_download['id'])?>">
						<?=_l('download_pdf')?><br />
						<?=formatDate($last_download['date_modified'])?>
					</a>
					<?php } ?>
				</div>
				<?php } ?>
				<div class="table-responsive mt-4">
					<?php include('bw_nav.php'); ?>

					<?php if (in_array($status, [3,4]) && false) { ?>
					<div class="col-lg-12">
						<div
							id="calendar"
							data-event-url="<?=$action_event?>"
							data-type="agendaWeek"
							data-action="filterData"
							style="max-height: 200px;"
						></div>
					</div>
					<?php } ?>

					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
							<?php foreach ($fields as $field) { ?>
								<?php if($field == "#") { ?>
								<th><input type="checkbox" class="select-all"></th>
								<?php } else {  ?>
								<th><?= _l($field) ?></th>
								<?php } ?>
							<?php } ?>
							</tr>
						</thead>
					</table>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>


<!-- escalate Modal -->
<div class="modal fade" id="dropshipperEscalateModal" role="dialog" aria-labelledby="dropshipperEscalateLable" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="dropshipperEscalateLable"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/escalate_dropshipper_order'); ?>" method="post" id="form-escalate-dropshipper-order">
					<input type="hidden" name="order_id" value="" id="escalate_order_id" />
					<div class="form-group">
						<label for="escalate_comment"><?php _el('comment_for_escalate_order'); ?></label>
						<textarea name="comment" id="escalate_comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-escalate-dropshipper-order" class="btn btn-danger"><?=_l('escalate_bw_order')?></button>
			</div>
		</div>
	</div>
</div>

<!-- escalate restore Modal -->
<div class="modal fade" id="restoreDropEscalateModal" role="dialog" aria-labelledby="restoreDropEscalateModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="restoreDropEscalateModalLabel"><?= _l('add_comment') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('admin/restore_dropshipper_escalate_order'); ?>" method="post" id="form-restore-drop-escalate-order">
					<input type="hidden" name="order_id" value="" id="escalate_restore_order_id" />
					<div class="form-group">
						<label for="escalate_restore_comment"><?php _el('comment_for_escalate_restore_order'); ?></label>
						<textarea name="comment" id="escalate_restore_comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-restore-drop-escalate-order" class="btn btn-danger"><?=_l('escalate_restore_bw_order')?></button>
			</div>
		</div>
	</div>
</div>

<script>
var table = null;
$(document).on('click', '.btn-cancel', function() {
	$('#cancel_order_id').val($(this).data('id'));
});
$(document).on('click', '.btn-rollback', function() {
	$('#rollback_order_id').val($(this).data('id'));
});
$(function() {
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> $fields
	]); ?>'));
	<?php if($status != 0) { ?>
	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		data: 'actions',
		render: ''
	});
	<?php } ?>

	table  = $('#ajax-datatable').DataTable( {
		aoColumnDefs: [{
			bSortable: false,
			aTargets: 0
		}],
		ajax: '<?php echo $action_ajax; ?>',
		processing: true,
		serverSide: true,
		lengthMenu: [10, 25, 50, 100, 500, 1000],
		order: [[ 0, 'desc' ]],
		columns: columns
	})

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
	$('#form-rollback-order').on('submit', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$el = $(this);

		if ($('#rollback_order_id').val() === '') {
			error_notify('<?=_li('invalid_order')?>')
			return false;
		}

		if ($('#rollback_comment').val() === '') {
			error_notify('<?=_li('comment_required_for_rollback_the_order')?>')
			return false;
		}

		if (confirm('<?php echo _li('Are you sure to rollback the order?'); ?>')) {
			submitForm($el.attr('action'), new FormData($el[0]), json => {
				if (json.success) {
					success_notify(json.success);
					$('#rollbackModal').modal('hide');
					table.ajax.reload(null, false);
				} else {
					error_notify(json.error)
				}
			});
		}
		return false;
	});
});
</script>
<script>
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

$('#bulk-dropshipper-action').on('click', function(event) {
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
			url: '<?=base_url('admin/dropshipper_bulk_order_update')?>',
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

$(document).on('click', '.search', function(event) {
	event.preventDefault();
	var endDate = $('.end-date').val();
	var startDate = $('.start-date').val();

	if (Date(startDate) >= Date(endDate)) {
		table.ajax.url('<?= $action_ajax ?>?startdate=' + startDate + '&enddate=' + endDate).load()
	}
});

$(document).on('click', '.btn-filter', function(event) {
	event.preventDefault();
	$el = $(this);
	table.ajax.url('<?= $action_ajax ?>?assign_date=' + $el.data('value')).load();
	$('.btn-filter').removeClass('btn-danger');
	$el.addClass('btn-danger');
});

$('.assign').click(function() {
	event.preventDefault();
	var ids = [];
	$.each($('input[class="select-me"]:checked'), function() {
		ids.push($(this).val());
	});
	let status = $('.reviewr').val()
	if (confirm('Are you sure?')) {
		$.ajax({
			url: '/dropShipper/submit_assign',
			type: 'POST',
			data: {
				ids: ids,
				reviewer_id: status
			},
			cache: false,
			success: function(response) {
				console.log(response);
			}
		});
	}
})

$('.select-all').click(function() {
	if (this.checked) {
		$('input.select-me').each(function() {
			$(this).prop('checked', true).trigger('change');
		});
	} else {
		$('input.select-me').each(function() {
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

$(document).on('click', '#gen-pdf', function(event) {
	if (confirm('Are you sure?')) {
		$.get($(this).data('href'), json => {
			json.success && success_notify(json.success);
			json.error && error_notify(json.error);
			json.tentative_time && $('#tentative_time').text(json.tentative_time);
		});
	}
});

$(document).on('click', '.qaqc-btn', function(event) {
	if (this.checked) {
		// $(this).prop('checked', true).trigger('change');
		if (confirm('Are you sure?')) {
			$.ajax({
				type: 'POST',
				data: {
					order_id: $(this).data('id'),
					status: 21
				},
				url: './ajax_status/',
				success: function(rsp) {
					table.ajax.reload(null, false);
				}
			});
		} else {
			$(this).prop('checked', false).trigger('change');
		}
	}
});

$(document).on('click', '.send_in_print', function(event) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: 'POST',
			data: {
				id: $(this).data('id'),
				status: 2
			},
			url: './send_in_print/',
			success: function(rsp) {
				table.ajax.reload(null, false);
			}
		});
	}
});

$(document).on('click', '.escalate-dropshipper-btn', function(event) {
	if ($(this).data('id') != '' && $(this).data('id') != undefined) {
		$('#escalate_order_id').val($(this).data('id'));
		$('#escalate_comment').val('');
		$('#dropshipperEscalateModal').modal('show');
	} else {
		alert('Something went wrong!')
	}
});

$('#form-escalate-dropshipper-order').on('submit', function(e) {
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
			console.log('form-escalate-dropshipper-order');
			console.log(json);
			
			if (json.success) {
				success_notify(json.success);
				$('#dropshipperEscalateModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$(document).on('click', '.restore-escalate-dropshipper-btn', function(event) {
	if ($(this).data('id') != '' && $(this).data('id') != undefined) {
		$('#escalate_restore_order_id').val($(this).data('id'));
		$('#escalate_restore_comment').val('');
		$('#restoreDropEscalateModal').modal('show');
	} else {
		alert('Something went wrong!')
	}
});

$('#form-restore-drop-escalate-order').on('submit', function(e) {
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

	if (confirm('<?php echo _li('Are you sure to escalate the order?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			console.log('form-restore-drop-escalate-order');
			console.log(json);
			
			if (json.success) {
				success_notify(json.success);
				$('#restoreDropEscalateModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$(document).on('click', '.rollback', function(event) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: 'POST',
			data: { id: $(this).data('id') },
			url: './rollback/',
			success: function(rsp) {
				table.ajax.reload(null, false);
			}
		});
	}
});

$(document).on('click', '.send_verify_print', function(event) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: 'POST',
			data: {
				id: $(this).data('id'),
				order_id: $(this).data('orderid'),
				status: 4
			},
			url: './send_verify_print/',
			success: function(rsp) {
				table.ajax.reload(null, false);
			}
		});
	}
});

$('#form-change-version').on('submit', function(e) {
	e.preventDefault();
	e.stopPropagation();
	$el = $(this);

	if (confirm('<?php echo _li('Are you sure to change version of order?'); ?>')) {
		submitForm($el.attr('action'), new FormData($el[0]), json => {
			
			if (json.success) {
				success_notify(json.success);
				$('#changeVersionModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$(document).on('click', '.shipbtn', function(e) {
	$el = $(this);

	if ($(this).data('id')) {
		$.ajax({
			type: 'GET',
			data: {
				order_id: $(this).data('id'),
				order_type: $(this).data('type')
			},
			url: './get_delivery_info',
			beforeSend: function() {
				$('.fulfillment_info').modal('show');
				$('#fulfillment_info').html('');
				$el.prop('disabled', true);
			},
			complete: function() {
				$el.prop('disabled', false);
			},
			success: function (data) {
				$('#fulfillment_info').html(data.couriers);
			},
		});
	}
});

$(function() {
	setTimeout(() => $('.btn-filter:nth-of-type(1)').trigger('click'), 300);
});
</script>
<script>
function filterData(data) {
	console.log(data)
}
</script>
<script>
$(document).on('click', '.change-order-version', function() {
	$el = $(this);
	$('input[name=order_id]').val($el.data('id'));

	$.post('<?=base_url('admin/ajax_order_products')?>', {order_id: $el.data('id')}, json => {
		if (json.products) {
			const html = json.products.map((item, index) => {
				let html = '<label><?=_l('product')?> #' + (index + 1) + ': ' + item.name + '</label>';
				let versions = item.versions.map(version => `<option value="${version}" ${item.version == version ? 'selected' : ''}>${version}</option>`)
				versions = versions.join('')
				html += `<select class="form-control" name="product[${item.product_id}]">${versions}</select>`;

				return html;
			});
			$('#change-version-form-content').html(html.join());
			$('#changeVersionModal').modal('show');
		}
	});
});
</script>
