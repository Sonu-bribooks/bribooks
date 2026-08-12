<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
					<h4 class="page-title" style="margin: 0; display: flex; align-items: center;">
					<i class="mdi mdi-apple-keyboard-command title_icon" style="margin-right: 6px;"></i>
					<?=$page_title?>
					</h4>

					<div style="display: flex; gap: 8px;">
					<?php if (!empty($action_add)) { ?>
						<a href="<?=$action_add?>"
							class="btn btn-outline-primary btn-rounded"
							style="display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 20px; text-decoration: none; font-size: 14px;">
							<i class="mdi mdi-plus" style="margin-right: 4px;"></i> <?=_l('add')?>
						</a>
					<?php } ?>
					<?php if (!empty($action_import)) { ?>
						<a href="<?=$action_import?>"
							class="btn btn-outline-primary btn-rounded"
							style="display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 20px; text-decoration: none; font-size: 14px;">
							<i class="mdi mdi-import" style="margin-right: 4px;"></i> <?=_l('import')?>
						</a>
					<?php } ?>
					</div>
				</div>
			</div>

			<!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<?php if (!empty($filters)) { ?>
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
				<form class="form row" action="#" method="post" id="form-filter">
					<?= $this->load->view('backend/admin/generic/form_item', ['fields' => $filters, 'column' => 2, 'field_class' => 'input-filter'], true) ?>

					<div class="col-sm-12">
						<div class="row">
							<div class="col-sm-8 text-left">
								<div class="btn-group">
									<button type="submit" class="btn btn-info" id="submit-button"> <?=_l('search')?></button>
									<button type="button" class="btn btn-danger ml-2" id="filter-reset"> <?=_l('reset')?></button>
								</div>
							</div>

							<?php if (!empty($action_export)) { ?>
							<div class="col-sm-4 text-right">
								<button type="button" class="btn btn-warning" id="btn-export"> <?=_l('export')?></button>
							</div>
							<?php } ?>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<div class="row">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="mb-3 header-title"><?=$page_title ?></h4>
				<div class="table-responsive mt-4">
					<table id="ajax-datatable" class="table table-striped table-centered mb-0">
						<thead>
						<tr>
							<?php foreach ($fields as $field) { ?>
								<th><?= _l($field) ?></th>
							<?php } ?>
						</tr>
						</thead>
					</table>
				</div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<?php if (!empty($filters)) { ?>
<?= $this->load->view('backend/admin/generic/script', [], true) ?>
<?php } ?>

<script>
var table = null;
$(function() {
	let columns = JSON.parse(atob('<?=_render_column(!empty($actions) ? [
		'keys' 		=> array_slice($fields, 0, count($fields) - 1),
		'actions'	=> $actions,
	] : [
		'keys' 		=> $fields,
	]); ?>'));

	columns = columns.map(item => {
		if (item.render) {
			const callback = eval(item.render);
			item.render = callback;
		}

		return item;
	});

	table = $('#ajax-datatable').DataTable({
		ajax: '<?=$action_ajax?>',
		processing: true,
		serverSide: true,
		order: [[ 0, 'desc' ]],
		columns: columns
	})
});
</script>
<?php if (!empty($is_auto_reload)) { ?>
<script>
setInterval(() => {
	table.ajax.reload();
}, <?=$is_auto_reload?>)
</script>
<?php } ?>

<script>
$(function() {
	<?php if (!empty($filters)) { ?>
	$(document).on('click', '#filter-reset', function(e) {
		table.ajax.url('<?= $action_ajax ?>').load();
		$('.input-filter').val('').trigger('change');
	});

	$(document).on('submit', '#form-filter', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$el = $(this);
		const query = $el.serialize();
		table.ajax.url('<?= $action_ajax ?>?' + query).load();
	});
	<?php } ?>

	<?php if (!empty($action_export)) { ?>
	$(document).on('click', '#btn-export', function(e) {
		e.preventDefault();
		e.stopPropagation();
		if (confirm('<?=_l('are_you_sure?')?>')) {
			$el = $('#form-filter');
			let filters = [];
			$el.find('.input-filter').each(function() {
				filters.push($(this).attr('name') + '=' + $(this).val());
			});

			window.location = '<?= $action_export ?>?' + filters.join('&');
		}
	});
	<?php } ?>
});
</script>
<script>
$(function() {
	$(document).on('click', '.btn-popup', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$el = $(this);
		showLargeModal($el.prop('href'), $el.data('title'));
	});
});
</script>
