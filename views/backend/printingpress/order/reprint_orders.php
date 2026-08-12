<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<?php if ($status == 1) { ?>
					<button
						class="btn btn-primary bulk-send float-right alignToTitle"
						data-orderstatus=""
						data-href="<?=base_url('printingPress/reprint_send_bulk_inprint')?>"
					>
						<?=_l('bulk_send_to_inprint')?>
					</button>
					<?php } elseif ($status == 2) { ?>
					<button
						class="btn btn-primary bulk-send float-right alignToTitle"
						data-orderstatus=""
						data-href="<?=base_url('printingPress/reprint_send_bulk_verify_print')?>"
					>
						<?=_l('bulk_send_to_verify_print')?>
					</button>
					<?php } elseif (0 && $status == 4) { ?>
					<button
						class="btn btn-primary bulk-send float-right alignToTitle"
						data-orderstatus=""
						data-href="<?=base_url('printingPress/reprint_send_bulk_prited')?>"
					>
						<?=_l('bulk_send_to_printed')?>
					</button>
					<?php } ?>
				</h4>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive mt-4">
					<?php include('reprint_nav.php'); ?>
					<?php if ($status == 4) { ?>
					<div class="mb-3 mt-3">
						<p class="text-info"><?=_l('filter_by_assign_date')?></p>
						<?php for ($i = 0; $i < 10; $i++) { ?>
							<button
								class="btn btn-primary btn-filter"
								data-value="<?=date('Y-m-d', strtotime("-${i} days"))?>"
							>
								<?=date('M j, Y', strtotime("-${i} days"))?>
							</button>
						<?php } ?>
					</div>
					<?php } ?>
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
							<tr>
								<th><input type="checkbox" class="select-all"></th>
								<th><?=_l('SN.')?></th>
								<th><?php echo _l('name'); ?></th>
								<th><?php echo _l('sku'); ?></th>
								<th><?php echo _l('author_name'); ?></th>
								<th><?php echo _l('download_link'); ?></th>
								<th><?php echo _l('type'); ?></th>
								<th><?php echo _l('quantity'); ?></th>
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
					'url' => 'printingPress/reprint_send_in_print/'
				],
			];
		} elseif ($status == 2) {
			$actions = [
				[
					'key' => 'send_to_verify_print',
					'url' => 'printingPress/reprint_send_verify_print/'
				]
			];
		} elseif ($status == 4) {
			$actions = [
				[
					'key' => 'printed',
					'url' => 'printingPress/reprint_send_printed/'
				]
			];
		}
	?>
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys'		 => [
			'#',
			'sn',
			'name',
			'sku',
			'author_name',
			'download_link',
			'type',
			'quantity',
			'assign_date',
			'action',
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
		'lengthMenu': [200, 300, 500, 1000],
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
			json.redirect && setTimeout(() => window.location = json.redirect, 300);
			json.success && success_notify(json.success);
			json.error && error_notify(json.error);
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

$(function() {
	setTimeout(() => $('.btn-filter:nth-of-type(1)').trigger('click'), 300);
});
</script>
