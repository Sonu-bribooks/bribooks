<div class="tab-pane" id="config">
	<form class="required-form" action="<?php echo $action; ?>" enctype="multipart/form-data" method="post">
		<?php foreach ($config_fields as $key => $item) { ?>
			<div class="card">
				<div class="card-header">
					<?= _l($key) ?>
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
