<div class="card-body">
	<a
		href="<?= $action_form ?>"
		class="btn btn-primary btn-rounded alignToTitle mb-2"
		id="action-add"
	><i class="mdi mdi-plus"></i><?=_l('add')?></a>
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
</div>

<div class="modal fade" id="form-modal">
	<div class="modal-dialog modal-lg">
		<div class="modal-content"></div>
	</div>
</div>

<script>
function form_edit(id) {
	loadForm('<?= $action_form ?>' + id);
}
function form_status(id) {
	if (confirm('<?= _l('are_you_sure?') ?>')) {
		$.get('<?= $action_crud ?>status/' + id, function(json) {
			table.ajax.reload(null, false);
			json.success && success_notify(json.success);
			json.error && error_notify(json.error);
		});
	}
}
function form_delete(id) {
	if (confirm('<?= _l('are_you_sure?') ?>')) {
		$.get('<?= $action_crud ?>delete/' + id, function(json) {
			table.ajax.reload(null, false);
			json.success && success_notify(json.success);
			json.error && error_notify(json.error);
		});
	}
}
</script>
<script>
function loadForm(href) {
	$('#form-modal .modal-content').html('');
	$('#form-modal .modal-content').load(href, function() {
		$('#form-modal').modal('show');
		setTimeout(() => {
			$('.filter_select').select2({
				dropdownParent: $('#form-modal'),
				ajax: {
					url: $(this).data('ajax-url'),
					dataType: 'json',
					delay: 250,
					data: function (params) {
						return {
							search: params.term,
						};
					},
					processResults: function (data) {
						return {
							results: data
						};
					},
					cache: true
				},
				placeholder: '<?=_l('select')?>',
				minimumInputLength: 2
			});
			$('.filter_multi_select').select2({
				dropdownParent: $('#form-modal'),
				multiple: true,
				ajax: {
					url: $(this).data('ajax-url'),
					dataType: 'json',
					delay: 250,
					data: function (params) {
						return {
							search: params.term,
						};
					},
					processResults: function (data) {
						return {
							results: data
						};
					},
					cache: true
				},
				placeholder: '<?=_l('select')?>',
				minimumInputLength: 2
			});
		}, 0);
	});
}

$('#action-add').on('click', function(e) {
	e.preventDefault();
	e.stopPropagation();
	const target = $(this);
	loadForm(target.attr('href'));
});
</script>
<script>
table = null
$(function() {
	let columns = JSON.parse(atob('<?php echo _render_column([
		'keys' 		=> array_slice($fields, 0, count($fields) - 1),
		'actions'	=> $actions,
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
