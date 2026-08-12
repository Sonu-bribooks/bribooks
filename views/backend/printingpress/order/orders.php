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
						data-href="<?=base_url('printingPress/send_bulk_verify_print')?>"
					>
						<?=_l('bulk_send_to_verify_print')?>
					</button>
					<?php } elseif (0 && $status == 4) { ?>
					<button
						class="btn btn-primary bulk-send float-right alignToTitle"
						data-orderstatus=""
						data-href="<?=base_url('printingPress/send_bulk_prited')?>"
					>
						<?=_l('bulk_send_to_printed')?>
					</button>
					<?php } ?>
				</h4>
			</div>
		</div>
	</div>
</div>
<?php if (in_array($this->session->userdata('role_id'), [1, 13])) { ?>
<button type="button" class="btn btn-primary alignToTitle" data-toggle="modal" data-target="#exampleModal">
	<?=_l('assign')?>
</button>
<?php } ?>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel"><?= _l('printer') ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<select name="reviewr" id="" class="form-control select2 reviewr" data-toggle="select2" name="reviewr" required>
					<option value=''>Select Printer</option>

					<?php
					foreach ($printer_list as $key => $value) {
						echo '<option value=' . $value['id'] . '>' . $value['first_name'] . ' ' . $value['last_name'] . ' </option>';
					}
					?>
				</select>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary assign">Save changes</button>
			</div>
		</div>
	</div>
</div>

<div class="" id="accordion">
	<div class="card">
		<div class="card-header" id="heading-1">
			<h5 class="mb-0">
				<a class="collapsed" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					Filters
				</a>

				<a class="float-right" role="button" data-toggle="collapse" href="#collapse-1" aria-expanded="false" aria-controls="collapse-1">
					<i class="dripicons-view-apps"></i>
				</a>
			</h5>

		</div>
		<div id="collapse-1" class="collapse hide" data-parent="#accordion" aria-labelledby="heading-1">
			<div class="card-body p-5">
				<div>
					<span>Enter Start Date</span>

					<input class="form-control alignToTitle start-date" name="start-date" data-provide="datepicker" placeholder="Enter Starting date">
				</div>
				<div>
					<span>Enter End Date</span>
					<input class="form-control alignToTitle end-date" name="end-date" data-provide="datepicker" placeholder="Enter Starting date">
				</div>
				<button class="btn btn-primary alignToTitle search">Search</button>
				<!-- <button class="btn btn-primary alignToTitle export-csv">Export </button> -->
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
					<button data-href="<?= base_url('printingPress/bulkDownloadBookPdf') ?>" class="btn btn-info" id="gen-pdf">
						<?=_l('generate_bulk_download_pdf') ?>
					</button><br /><br />
					<span class="badge badge-dark" id="tentative_time">
					<?php if (!empty($last_request['date_tentative'])) { ?>
						<?=_li('Expected_time_for_download ') . formatDate($last_request['date_tentative'])?>
					<?php } ?>
					</span><br />
					<?php if (!empty($last_download['file'])) { ?>
					<a href="<?=base_url('printingPress/downloadZip/' . $last_download['id'])?>">
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

					<?php if ($status == 4) { ?>
					<div class="mb-3 mt-3">
						<p class="text-info"><?=_l('filter_by_assign_date')?></p>
						<?php for ($i = 0; $i < 30; $i++) { ?>
							<button
								class="btn btn-primary btn-filter mb-2"
								data-value="<?=date('Y-m-d', strtotime("-${i} days"))?>"
							>
								<?=date('M d, Y', strtotime("-${i} days"))?>
							</button>
						<?php } ?>
					</div>
					<?php } ?>
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th><input type="checkbox" class="select-all"></th>
								<th><?=_l('SN.')?></th>
								<th><?=_l('sku')?></th>
								<th><?php echo _l('name'); ?></th>
								<th><?php echo _l('author_name'); ?></th>
								<th><?php echo _l('stats'); ?></th>
								<th><?php echo _l('download_link'); ?></th>
								<th><?php echo _l('type'); ?></th>
								<th><?php echo _l('quantity'); ?></th>
								<th><?php echo _l('assignment_code'); ?></th>
								<th><?php echo _l('assign_date'); ?></th>
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
	<?php
		$actions = [];

		if ($status == 1) {
			$actions = [
				[
					'key' => 'send_to_in_print',
					'url' => 'printingPress/send_in_print/'
				],
			];
		} elseif ($status == 2) {
			$actions = [
				[
					'key' => 'send_to_verify_print',
					'url' => 'printingPress/send_verify_print/'
				]
			];
		} elseif ($status == 4) {
			$actions = [
				[
					'key' => 'printed',
					'url' => 'printingPress/send_printed/'
				]
			];
		}
	?>
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys'		 => [
			'#',
			'sn',
			'book_id',
			'name',
			'author_name',
			'stats',
			'download_link',
			'type',
			'quantity',
			'assignment_code',
			'assign_date',
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
	$.each($('input[class="select-me"]:checked'), function() {
		ids.push($(this).val());
	});

	fd.append('ids', ids.join(','));

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
			url: '/printingPress/submit_assign',
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

$(document).on('click', '#gen-pdf', function(event) {
	if (confirm('Are you sure?')) {
		$.get($(this).data('href'), json => {
			json.success && success_notify(json.success);
			json.error && error_notify(json.error);
			json.tentative_time && $('#tentative_time').text(json.tentative_time);
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
