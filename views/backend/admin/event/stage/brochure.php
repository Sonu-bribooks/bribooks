<div class="tab-pane" id="brochure">
	<form class="required-form" action="<?php echo $action; ?>" enctype="multipart/form-data" method="post">
		<div class="row">
			<div class="col-12">
				<?=$fields?>

				<div id="ebrochure-container">
					<div class="form-group row mb-3">
						<label for="ebrochure" class="col-md-3 col-form-label">
							<?= _l('e_brochure') ?>
							<span class="required">*</span>
						</label>
						<div class="col-md-9">
							<div class="card">
								<div class="card-body image-item-container">
									<?php foreach ($ebrochure as $index => $item) { ?>
										<div class="position-relative image-item d-inline-block mr-1">
											<a
												href="<?= $this->image_model->resize(!empty($item) ? $this->config->item('cloudfront_url') . ($this->config->item('s3_user_gallery') . $item) : 'no_image.png', 100, 100) ?>"
												id="logo-thumb-<?= $index ?>"
												data-toggle="image"
												class="img-thumbnail"
												data-target="#<?= $index ?>"
												style="display:inline-block;"
											>
												<img
													src="<?= $this->image_model->resize(!empty($item) ? $this->config->item('cloudfront_url') . ($this->config->item('s3_user_gallery') . $item) : 'no_image.png', 100, 100) ?>"
													data-placeholder="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>"
													style="width:100px; height:100px;"
												/>
											</a>
											<input
												class="form-control"
												type="hidden"
												name="ebrochure[<?=$index?>]"
												value="<?= $item ?>"
												required
												id="ebrochure<?= $index ? $index : '' ?>"
											/>
											<?php if ($index != 0) { ?>
												<span
													class="position-absolute close btn-close"
													onclick="$(this).parent().remove()"
												>
													<i class="fa fa-times-circle text-danger"></i>
												</span>
											<?php } ?>
										</div>
									<?php } ?>
								</div>
								<div class="card-footer">
									<div class="row">
										<div class="col-sm-8">
											<input
												id="ebrochure_dynamic"
												type="text"
												name="ebrochure_dynamic"
												class="form-control"
												value="<?= $ebrochure_dynamic ?>"
												placeholder="<?= _l('ebrochure_dynamic_image_index') ?>"
												required
											/>
										</div>
										<div class="col-sm-4 text-right">
											<button type="button" class="btn btn-info" id="btn-add-image">
												<?=_l('add')?>
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<button class="d-none save" />
	</form>
</div>
<script>
index = <?= $index ?> + 1;

$('#btn-add-image').on('click', function() {
	const html = `
		<div class="position-relative image-item d-inline-block mr-1">
			<a
				href="<?= $this->image_model->resize('no_image.png', 100, 100) ?>"
				id="logo-thumb-${index}"
				data-toggle="image"
				class="img-thumbnail"
				data-target="#${index}"
				style="display:inline-block;"
			>
				<img
					src="<?= $this->image_model->resize('no_image.png', 100, 100) ?>"
					data-placeholder="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>"
					style="width:100px; height:100px;"
				/>
			</a>
			<input
				class="form-control"
				type="hidden"
				name="ebrochure[${index}]"
				id="ebrochure${index}""
				required
			/>
			<span
				class="position-absolute close btn-close"
				onclick="$(this).parent().remove()"
			>
				<i class="fa fa-times-circle text-danger"></i>
			</span>
		</div>
	`;

	$('.image-item-container').append(html);

	index++;
});
</script>
<style>
.btn-close {
	right: 1px;
	top: 1px;
}
</style>
