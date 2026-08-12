<div class="tab-pane" id="communication_kit">
	<form class="required-form" action="<?php echo $action; ?>" enctype="multipart/form-data" method="post">
		<?php foreach ($communication_kit_fields as $key => $item) { ?>
			<div class="card">
				<div class="card-header">
					<?= _l($key) ?>
					<span class="text-danger small">
						<?= _li('**clear_the_field_to_remove_from_the_list') ?>
					</span>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-12">
							<?=$item?>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>
		
		<button class="d-none save" />
	</form>
</div>
