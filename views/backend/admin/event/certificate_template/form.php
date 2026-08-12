<div class="modal-header">
	<h4 class="modal-title"><?= $page_title ?></h4>
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>

<div class="modal-body p-3">
	<form action="<?= $action ?>" method="post" id="popup-form">
		<?= $fields ?>
	</form>
	<div class="text-right pt-2">
		<button
			type="button"
			class="btn btn-light"
			data-dismiss="modal"
		><?php _el('close'); ?>
		</button>
		<button
			type="button"
			form="popup-form"
			class="btn btn-primary ml-1 btn-save-model"
		><?php _el('save'); ?>
		</button>
	</div>
</div>
<script>
$('.btn-save-model').on('click', function() {
	const form = $('#stage-load').find('form');
	submitEventForm(form, function(json) {
		if (json.success) {
			setTimeout(() => $('#form-modal').modal('hide'), 300);
			if (window.table) {
				table.ajax.reload(null, false);
			}
		}
	});
});
</script>
