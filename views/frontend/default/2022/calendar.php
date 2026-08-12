<div class="modal fade" id="event-modal">
	<div class="modal-dialog modal-lg" style="min-height: 50%;">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">
					<?php _el('webinar_schedule'); ?>
				</h4>
				<button
					type="button"
					class="close"
					data-dismiss="modal"
					aria-hidden="true"
				>×</button>
			</div>

			<div class="modal-body p-3">
				<div
					id="calendar"
					data-event-url="<?php echo site_url('home/ajax_webinar_schedule/event/'); ?>"
					data-select="false"
				></div>
			</div>
		</div>
	</div>
</div>
