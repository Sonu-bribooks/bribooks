<div class="tab-pane" id="signup">
	<form class="required-form" action="<?php echo $action; ?>" enctype="multipart/form-data" method="post">
		<?php foreach ($signup_fields as $key => $item) { ?>
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

		<?php if (!empty($multi_images)) { include('multi_image.php'); } ?>

		<button class="d-none save" />
	</form>
</div>
