<style type="text/css">
	.btn {
		padding: .3rem .5rem;
	}
</style>
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<?php if ($status == 1) { ?>
					<button
						class="btn btn-primary bulk-send float-right alignToTitle"
						data-orderstatus=""
						data-href="<?=$in_print_action?>"
						<?= $in_print_attr ?>
					>
						<?=$in_print_text?>
					</button>
					<?php } elseif ($status == 2) { ?>
					<button
						class="btn btn-primary bulk-send float-right alignToTitle"
						data-orderstatus=""
						data-href="<?=base_url('dropShipper/send_bulk_verify_print')?>"
					>
						<?=_l('bulk_send_to_QA/QC') ?>
					</button>
					<?php } elseif ($status == 8) { ?>
					<button
						class="btn btn-primary bulk-send float-right alignToTitle"
						data-orderstatus=""
						data-href="<?=base_url('dropShipper/send_bulk_qaqc')?>"
					>
						<?=_l('bulk_send_to_AFS') ?>
					</button>
					<?php } ?>
				</h4>
			</div>
		</div>
	</div>
</div>

<div class="" id="accordion">
	<div class="card">
		<div class="card-header" id="heading-1">
			<h5 class="mb-0">
				<a class="collapsed" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<?=_l('filters') ?>
				</a>

				<a class="float-right" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<i class="dripicons-view-apps"></i>
				</a>
			</h5>

		</div>
		<div id="collapse-1" class="collapse hide" data-parent="#accordion" aria-labelledby="heading-1">
			<div class="card-body p-5">
				<div>
					<span><?=_l('enter_start_date') ?></span>

					<input class="form-control alignToTitle start-date" name="start-date" data-provide="datepicker" placeholder="<?=_l('enter_start_date') ?>">
				</div>
				<div>
					<span><?=_l('enter_end_date') ?></span>
					<input class="form-control alignToTitle end-date" name="end-date" data-provide="datepicker" placeholder="<?=_l('enter_end_date') ?>">
				</div>
				<button class="btn btn-primary alignToTitle search"><?=_l('search') ?></button>
				<button class="btn btn-warning alignToTitle reset-filter mr-2"><?=_l('reset') ?></button>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<?php if ($status == 2) { ?>
				<div class="text-right">
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
					<?php include('nav.php'); ?>

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
				<form action="<?php echo base_url('dropShipper/add_order_comment'); ?>" method="post" id="form-hold-order">
					<input type="hidden" name="order_id" value="" id="order_id" />
					<div class="form-group">
						<label for="comment"><?php _el('comment'); ?></label>
						<textarea name="comment" rows="6" class="form-control"></textarea>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_l('close')?></button>
				<button type="submit" form="form-hold-order" class="btn btn-primary"><?=_l('submit')?></button>
			</div>
		</div>
	</div>
</div>

<script>
var table = null;
$(document).on('click', '.btn-cancel', function() {
	$('#cancel_order_id').val($(this).data('id'));
});

$('#form-hold-order').on('submit', function(e) {
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

$(document).on('click', '.btn-hold', function() {
	$('#order_id').val($(this).data('id'));
});

$(function() {
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> $fields
	]); ?>'));
	<?php if($status != 0) { ?>
	const action = columns.pop()
	const callback = eval(action.render)
	columns.push({
		"data": "actions",
		render: ''
	});
	<?php } ?>

	table  = $('#ajax-datatable').DataTable( {
		'aoColumnDefs': [{
			'bSortable': false,
			'aTargets': 0
		}],
		"ajax": "<?php echo $action_ajax; ?>",
		"processing": true,
		"serverSide": true,
		"lengthMenu": [10, 25, 50, 100, 500, 1000],
		"order": [[ 0, "desc" ]],
		"columns": columns
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
});
</script>
<script>


$(document).on('click', '.select-me', function(event) {
	if (this.checked) {
		$(this).prop('checked', true).trigger('change');
	} else {
		$(this).prop('checked', false).trigger('change');
	}
	$('.select-all').prop('checked', false).trigger('change');
});

$('.bulk-send').on('click', function(event) {
	event.preventDefault();

	$el = $(this);

	const fd = new FormData()

	var ids = [];
	var order_ids = [];
	$.each($('input[class="select-me"]:checked'), function() {
		ids.push($(this).val());
		if ($(this).attr('data-order') !== undefined) {
			order_ids.push($(this).data('order'));
		}
	});

	fd.append('ids', ids.join(','));

	if(order_ids.length > 0)
	fd.append('order_ids', order_ids.join(','));

	if (confirm('<?=_l('are_you_sure?')?>')) {
		submitForm($el.data('href'), fd, json => {
			console.log(json)
			// json.redirect && setTimeout(() => window.location = json.redirect, 300);
			json.success && success_notify(json.success);
			json.error && error_notify(json.error);
			table.ajax.reload(null, true);
		})
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

$(document).on("click", '.qaqc-btn', function(event) {
	if (this.checked) {
		// $(this).prop('checked', true).trigger('change');
		if (confirm('Are you sure?')) {
			$.ajax({
				type: "POST",
				data: {'order_id':$(this).data('id'),'status':21},
				url: "./ajax_status/",
				success: function(rsp) {
					table.ajax.reload(null, false);
				}
			});
		}
		else{
			$(this).prop('checked', false).trigger('change');
		}
	}

});

$(document).on("click", '.send_in_print', function(event) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: "POST",
			data: {'id':$(this).data('id'),'status':2},
			url: "./send_in_print/",
			success: function(rsp) {
				table.ajax.reload(null, false);
			}
		});
	}

});

$(document).on("click", '.send_verify_print', function(event) {
	if (confirm('Are you sure?')) {
		$.ajax({
			type: "POST",
			data: {'id':$(this).data('id'),'order_id':$(this).data('orderid'),'status':4},
			url: "./send_verify_print/",
			success: function(rsp) {
				table.ajax.reload(null, false);
			}
		});
	}

});

$(document).on('click', '.shipbtn', function(e) {
	$el = $(this);
	if ($(this).data('id')) {
		$.ajax({
			type: 'GET',
			data: { 'order_id' : $(this).data('id')},
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

$(document).on('click', '.reset-filter', function(event) {
	$('.start-date,.end-date').val('');
	table.ajax.url('<?= $action_ajax ?>').load()
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
let barcode = '';
let reading = false;

document.addEventListener('keydown', e => {
	if (e.key == 'Enter') {
		if (barcode.length == 13) {
			table.search(barcode).draw();
			barcode = '';
		}
	} else {
		if (e.key != 'Shift') {
			barcode += e.key;
		}
	}

	if (!reading) {
		reading = true;
		setTimeout( () => {
			barcode = '';
			reading = false;
		}, 200);
	}
}, true)
</script>
