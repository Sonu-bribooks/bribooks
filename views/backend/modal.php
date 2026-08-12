<script type="text/javascript">
function showAjaxModal(url, header)
{
	// SHOWING AJAX PRELOADER IMAGE
	jQuery('#scrollable-modal .modal-body').html('<div style="text-align:center;margin-top:200px;"><img src="<?php echo base_url().'assets/global/bg-pattern-light.svg'; ?>" /></div>');
	jQuery('#scrollable-modal .modal-title').html('...');
	// LOADING THE AJAX MODAL
	jQuery('#scrollable-modal').modal('show', {backdrop: 'true'});

	// SHOW AJAX RESPONSE ON REQUEST SUCCESS
	$.ajax({
		url: url,
		success: function(response)
		{
			jQuery('#scrollable-modal .modal-body').html(response);
			jQuery('#scrollable-modal .modal-title').html(header);
		}
	});
}
function showLargeModal(url, header)
{
	// SHOWING AJAX PRELOADER IMAGE
	jQuery('#large-modal .modal-body').html('<div style="text-align:center;margin-top:200px;"><img src="<?php echo base_url().'assets/global/bg-pattern-light.svg'; ?>" height = "50px" /></div>');
	jQuery('#large-modal .modal-title').html('...');
	// LOADING THE AJAX MODAL
	jQuery('#large-modal').modal('show', {backdrop: 'true'});

	// SHOW AJAX RESPONSE ON REQUEST SUCCESS
	$.ajax({
		url: url,
		success: function(response)
		{
			jQuery('#large-modal .modal-body').html(response);
			jQuery('#large-modal .modal-title').html(header);
		}
	});
}
</script>

<!-- (Large Modal)-->
<div class="modal fade" id="large-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myLargeModalLabel">Large modal</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body">
				...
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>

<!-- Scrollable modal -->
<div class="modal fade" id="scrollable-modal" tabindex="-1" role="dialog" aria-labelledby="scrollableModalTitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-scrollable" role="document">
	<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="scrollableModalTitle">Modal title</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body ml-2 mr-2">

		</div>
		<div class="modal-footer">
			<button class="btn btn-secondary" data-dismiss="modal"><?php echo get_phrase("close"); ?></button>
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>

<script type="text/javascript">
function confirm_modal(url, ajax = false) {
	$('#alert-modal').modal('show', {backdrop: 'static'});
	$('#update_link').attr('href', url);
	$('#update_link').data('ajax', ajax);
}

function comment_modal(data) {
	$('#textModalLabel').text(data.title);
	$('#text_id').val(data.id);
	$('#text-form').attr('action', data.url);

	$('#text-modal').modal('show', {backdrop: 'static'});

}
</script>

<!-- Info Alert Modal -->
<div id="alert-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-body p-4">
				<div class="text-center">
					<i class="dripicons-information h1 text-info"></i>
					<h4 class="mt-2"><?php echo get_phrase("heads_up"); ?>!</h4>
					<p class="mt-3"><?php echo get_phrase("are_you_sure"); ?>?</p>
					<button type="button" class="btn btn-info my-2" data-dismiss="modal"><?php echo get_phrase("cancel"); ?></button>
					<a href="#" id="update_link" class="btn btn-danger my-2" data-ajax="false"><?php echo get_phrase("continue"); ?></a>
				</div>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade fulfillment_info" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content" id="fulfillment_info">
		</div>
	</div>
</div>

<!-- Common Comment Modal -->
<div class="modal fade" id="text-modal" tabindex="-1" role="dialog" aria-labelledby="textModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">

			<div class="modal-header">
				<h5 class="modal-title" id="textModalLabel">Add Comment</h5>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>

			<div class="modal-body">
				<form id="text-form">

					<!-- Dynamic ID -->
					<input type="hidden" name="id" id="text_id">

					<div class="form-group">
						<label for="text_field">Comment</label>
						<textarea
							name="text"
							id="text_field"
							rows="5"
							class="form-control"
							placeholder="Enter description"
							required></textarea>
					</div>

				</form>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					Close
				</button>

				<button type="submit" form="text-form" class="btn btn-primary">
					Submit
				</button>
			</div>

		</div>
	</div>
</div>

<script>
$('#update_link').on('click', function(e) {
	$el = $(this);

	if ($el.data('ajax')) {
		e.preventDefault();
		e.stopPropagation();

		$.get($el.attr('href'), function(json) {
			json.success && success_notify(json.success);
			json.error && error_notify(json.error);

			$('#alert-modal').modal('hide');
			$(document).trigger('confirm_modal', { url: $el.attr('href'), status: json });
		});
	}
})

$('#text-form').on('submit', function(e) {
	e.preventDefault();
	$el = $(this);

	if (confirm('<?=_el('are_you_sure?')?>')) {
		submitForm($(this).attr('action'), new FormData($el[0]), json => {
			if (json.success) {
				success_notify(json.message);
				$('#text_id').val('');
				$('#text_field').val('');
			}

			if (json.error) {
				error_notify(json.error);
			}

			$('#text-modal').modal('hide');

			$('#ajax-datatable').DataTable().ajax.reload(null, false);
		});
	}	
});
</script>
