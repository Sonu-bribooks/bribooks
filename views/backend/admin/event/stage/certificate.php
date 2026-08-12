<div class="tab-pane" id="certificate">
	<div id="accordion">
		<div class="card mb-0">
			<div class="card-header bg-info" id="certificate-message">
				<h5 class="mb-0">
					<button
						class="btn btn-link btn-certificate text-white collapsed"
						data-toggle="collapse"
						data-target="#collapse-certificate-message"
						aria-expanded="true"
						aria-controls="collapse-certificate-message"
						data-href="<?= $ajax_certificate_message_template ?>"
					>
						<i class="far fa-map"></i> <?=_l('certificate_message_template')?>
					</button>
				</h5>
			</div>

			<div id="collapse-certificate-message" class="collapse" aria-labelledby="certificate_message" data-parent="#accordion">
				<div id="collapse-certificate-message-load"></div>
			</div>

			<div class="card-header bg-warning" id="certificate-template">
				<h5 class="mb-0">
					<button
						class="btn btn-link btn-certificate text-white collapsed"
						data-toggle="collapse"
						data-target="#collapse-certificate-template"
						aria-expanded="true"
						aria-controls="collapse-certificate-template"
						data-href="<?= $ajax_certificate_template ?>"
					>
						<i class="far fa-map"></i> <?=_l('certificate_template')?>
					</button>
				</h5>
			</div>

			<div id="collapse-certificate-template" class="collapse" aria-labelledby="certificate_template" data-parent="#accordion">
				<div id="collapse-certificate-template-load"></div>
			</div>
		</div>
	</div>
</div>

<script>
$('.btn-certificate').on('click', function(e) {
	e.preventDefault();

	$el = $(this);
	const target = $el.data('target');
	$('#collapse-certificate-message-load').html('');
	$('#collapse-certificate-template-load').html('');
	$(target + '-load').load($el.data('href'));
});
$('.btn-certificate').first().trigger('click');
</script>
