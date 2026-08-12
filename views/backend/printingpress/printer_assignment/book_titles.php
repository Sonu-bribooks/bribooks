<style type="text/css">
.btn { padding: .3rem .5rem; }
.rejected { display: none; }
</style>

<!-- qa qc modal-->
<div class="modal fade" id="qaqcModal" role="dialog" aria-labelledby="qaqcModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="qaqcModalLabel">QA QC Action</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="<?php echo base_url('printingPress/qaqc_action'); ?>" method="post" id="form-qaqc-order">
					<input type="hidden" name="assignment_id" id="assignment_id" value="<?= $assignment_id ?? ''; ?>" />
					<input type="hidden" name="book_id" id="book_id" value="" />
					<input type="hidden" name="version" id="version" value="" />
					<input type="hidden" name="option" id="option" value="" />
					<div class="form-group">
						<div id="qaqc-form-content"></div>
					</div>
					<div class="form-group">
						<label><?php _el('quantity'); ?></label>
						<input type="text" name="quantity" id="quantity" class="form-control" value="0" />
					</div>
					<div class="form-group">
						<label><?php _el('select_action'); ?></label>
						<select name="action" class="form-control printer" id="action" required>
							<option value=""><?=_l('select_action')?></option>
							<option value="3">Accepted with Short Quantity</option>
							<option value="2">Rejected</option>
							<option value="1">Accepted</option>
						</select>
					</div>
					<div class="form-group rejected">
						<label><?php _el('select_reason'); ?></label>
						<select name="reason" class="form-control" id="reason">
							<option value=""><?=_l('select_reason')?></option>
							<option class="action_reason action_2" value="binding">Binding</option>
							<option class="action_reason action_2" value="content">Content</option>
							<option class="action_reason action_3" value="short_quantity">Short Quantity</option>
							<option class="action_reason action_2" value="sku_mismatch">SKU Mismatch</option>
							<option class="action_reason action_2" value="torn_paper">Torn Paper</option>
							<option class="action_reason action_2" value="version">Version</option>
							<option class="action_reason action_2" value="other">Other</option>
						</select>
					</div>
					<div class="form-group rejected">
						<label for="comment"><?php _el('comment'); ?></label>
						<textarea name="comment" id="comment" rows="4" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-qaqc-order" class="btn btn-primary btn-qaqc-submit"><?=_l('submit')?></button>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card mb-2">
			<div class="card-body p-2">
				<h5 class="page-title float-left">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
				</h5>

				<?php if (in_array($this->session->userdata('role_id'), [15])) { ?>
				<a href="javascript:;" class="btn btn-outline-primary btn-rounded alignToTitle ml-2 bulk-qaqc-action"><i class="mdi mdi-checkbox-marked-circle-outline"></i> <?php echo _li(' Bulk Accept QA QC'); ?></a>
				<?php if ($action_visible) { ?>
				<a href="<?php echo $action_csv_logs ?? ''; ?>" class="btn btn-outline-primary btn-rounded alignToTitle ml-2"><i class="mdi mdi-download"></i> <?php echo _li(' QA QC Logs CSV'); ?></a>
				<a href="<?php echo $action_csv ?? ''; ?>" class="btn btn-outline-primary btn-rounded alignToTitle ml-2"><i class="mdi mdi-download"></i> <?php echo _li(' QA QC Printer CSV'); ?></a>
				<?php if (!empty($qa_qc_complete_btn)) { ?>
				<a href="<?php echo $action_complete ?? ''; ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-checkbox-marked-circle-outline"></i> <?php echo _li(' QA QC Complete'); ?></a>
				<?php } } } else if (in_array($this->session->userdata('role_id'), [12])) { ?>
				<?php if ($action_visible) { ?>
				<a href="<?php echo $action_csv ?? ''; ?>" class="btn btn-outline-primary btn-rounded alignToTitle ml-2"><i class="mdi mdi-download"></i> <?php echo _li(' QA QC Printer CSV'); ?></a>
				<?php } } ?>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th><input type="checkbox" class="select-all"></th>
								<th><?=_l('SN.')?></th>
								<th><?=_l('sku')?></th>
								<th><?php echo _l('name'); ?></th>
								<th><?php echo _l('author_name'); ?></th>
								<th><?php echo _l('type'); ?></th>
								<th><?php echo _l('quantity'); ?></th>
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
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys'		 => [
			'#',
			'sn',
			'book_id',
			'name',
			'author_name',
			'type',
			'quantity',
			'actions',
		],
	]); ?>'));

	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		"data": "actions",
		render: callback
	});

	table = $('#ajax-datatable').DataTable({
		'aoColumnDefs': [{
			'bSortable': false,
			'aTargets': 0
		}],
		'ajax': '<?php echo $action_ajax; ?>',
		'lengthMenu': [50, 100, 200, 300, 500, 1000],
		'processing': true,
		'serverSide': true,
		'order': [
			[0, "desc"]
		],
		'columns': columns
	})
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

$(document).on('click', '.btn-qaqc', function() {
	$el = $(this);

	$('#action option:first').prop('selected',true).trigger("change");
	$('input[name=quantity]').val(0);
	$('#reason option:first').prop('selected',true).trigger("change");
	$('#comment').val('');

	$('input[name=book_id]').val($el.data('id'));
	$('input[name=version]').val($el.data('version'));
	$('input[name=option]').val($el.data('option'));

	$.post('<?=base_url('printingPress/ajax_books_details')?>', {book_id: $el.data('id'), assignment_id: $("#assignment_id").val(), version: $("#version").val(), option: $("#option").val()}, json => {
		if (json.products) {
			const html = json.products.map((item, index) => '<label><?=_l('book_name')?>: ' + item.name + '</label><br /><label>Total Book Quantity</label><input type="text" class="form-control" name="book_quantity" id="book_quantity" value="' + item.quantity + '" placeholder="Total Book Quantity" readonly="" />');
			$('#qaqc-form-content').html(html.join());
		}
	});
});

$(document).on('click', '.btn-qaqc-reset', function() {
	$el = $(this);
	if (confirm('<?php echo _li('Are you sure to reset?'); ?>')) {
		$.post('<?=base_url('printingPress/ajax_books_details_reset')?>', {book_id: $el.data('id'), assignment_id: $("#assignment_id").val(), version: $el.data('version'), option: $el.data('option')}, json => {
			if (json.success) {
				$('.rejected').css('display', 'none');
				$('#action').val('');

				success_notify(json.success);
				$('#qaqcModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$(document).on('click', '.btn-qaqc-reset-rejected-count', function() {
	$el = $(this);
	if (confirm('<?php echo _li('Are you sure to reset rejected count?'); ?>')) {
		$.post('<?=base_url('printingPress/ajax_books_details_reset_rejected_count')?>', {book_id: $el.data('id'), assignment_id: $("#assignment_id").val(), version: $el.data('version'), option: $el.data('option')}, json => {
			if (json.success) {
				$('.rejected').css('display', 'none');
				$('#action').val('');

				success_notify(json.success);
				$('#qaqcModal').modal('hide');
				table.ajax.reload(null, false);
			} else {
				error_notify(json.error)
			}
		});
	}
	return false;
});

$(function() {
	$('#action').on('change', function() {
		$('#reason option:first').prop('selected',true).trigger("change");
		$('#comment').val('');

		$('.rejected').css('display', 'none');

		$('.action_reason').css('display', 'none');
		$('.action_'+$(this).val()).css('display', 'block');

		if ($(this).val() == '2') {
			$('.rejected').css('display', 'block');
		}

		if ($(this).val() == '3') {
			$('.rejected').css('display', 'block');
			$('#reason option[value=short_quantity]').prop('selected',true).trigger("change");
		}
	});

	$('#form-qaqc-order').on('submit', function(e) {
		$('.btn-qaqc-submit').prop('disabled', true);

		e.preventDefault();
		e.stopPropagation();

		if (isNaN($('#assignment_id').val()) || isNaN($('#book_id').val())) {
			error_notify('Please select valid details');
			$('.btn-qaqc-submit').prop('disabled', false);
			return;
		}

		var book_quantity = parseInt($('#book_quantity').val());
		var quantity = parseInt($('#quantity').val());

		if (quantity === undefined || quantity == null || quantity.length <= 0) {
			error_notify('Please enter the quantity');
			$('.btn-qaqc-submit').prop('disabled', false);
			return;
		}

		if (isNaN(quantity) || quantity <= 0) {
			error_notify('Please enter the valid quantity');
			$('.btn-qaqc-submit').prop('disabled', false);
			return;
		}

		if ($('#action').val() != '1') {
			if ($('#action').val() == '3' && book_quantity == quantity) {
				error_notify('Please enter the quantity less than book quantity');
				$('.btn-qaqc-submit').prop('disabled', false);
				return;
			}

			if (quantity > book_quantity) {
				error_notify('Please check the quantity');
				$('.btn-qaqc-submit').prop('disabled', false);
				return;
			}

			if ($('#reason').val() == '') {
				error_notify('Please select the reason');
				$('.btn-qaqc-submit').prop('disabled', false);
				return;
			}

			if ($('#reason').val() == 'other' && $('#comment').val() == '') {
				error_notify('Please fill the comment');
				$('.btn-qaqc-submit').prop('disabled', false);
				return;
			}
		} else {
			if (book_quantity != quantity) {
				error_notify('Please enter the valid quantity');
				$('.btn-qaqc-submit').prop('disabled', false);
				return;
			}
		}

		$el = $(this);
		if (confirm('<?php echo _li('Are you sure?'); ?>')) {
			submitForm($el.attr('action'), new FormData($el[0]), json => {
				if (json.success) {
					$('.rejected').css('display', 'none');
					$('#action').val('');

					success_notify(json.success);
					$('#qaqcModal').modal('hide');
					table.ajax.reload(null, false);

					$('.btn-qaqc-submit').prop('disabled', false);
				} else {
					error_notify(json.error)
					$('.btn-qaqc-submit').prop('disabled', false);
				}
			});
		}
		return false;
	});
});

$(document).on('click', '.qaqc-complete', function(event) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: 'POST',
			data: 'assignment_id=<?= $assignment_id; ?>',
			url: '<?=base_url('printingPress/qaqc_complete')?>',
			success: function(json) {
				if (json.success) {
					success_notify(json.success);
				} else {
					error_notify(json.error)
				}
			}
		});
	}
});

$(document).on('click', '.bulk-qaqc-action', function(event) {
	event.preventDefault();

	var ids = [];
	$.each($('input[class="select-me"]:checked'), function() {
		ids.push($(this).val());
	});

	if (ids.length == 0) {
		error_notify('<?=_l('select_atleast_one_book_title')?>')
		return false;
	}

	if (confirm('<?=_li('Are you sure? Total Book Titles: ')?>' + ids.length)) {
		$.ajax({
			url: '<?=base_url('printingPress/qaqcBulkAction')?>',
			type: 'POST',
			data: {
				ids: ids,
				assignment_id: '<?= $assignment_id; ?>'
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
</script>
