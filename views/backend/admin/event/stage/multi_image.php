<?php foreach($multi_images as $key => $value) {?>
	<div id="ebrochure-container">
		<div class="form-group row mb-3">
			<label for="ebrochure" class="col-md-3 col-form-label">
				<?= $value['label'] ?>
				<span class="required">*</span>
			</label>
			<div class="col-md-9">
				<div class="card">
					<div class="card-body image-item-container_<?= $key ?>">
						<?php foreach ($value['images'] as $index => $item) { ?>
							<div class="position-relative image-item d-inline-block mr-1">
								<a
									href="<?= $this->image_model->resize(!empty($item) ? $this->config->item('cloudfront_url') . ($this->config->item('s3_user_gallery') . $item) : 'no_image.png', 100, 100) ?>"
									id="logo-thumb-<?= $key . $index ?>"
									data-toggle="image"
									class="img-thumbnail"
									data-target="#<?= $key . $index ?>"
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
									name="<?= $key . '[images][' . $index . ']' ?>"
									value="<?= $item ?>"
									required
									id="<?= $index ? $key . $index : 'school_landing_page0' ?>"
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
							<?php foreach ($value['fields'] as $fields_key => $field) { ?>
								<div class="col-sm-12 mt-2">
									<div class="row align-items-center">
										<!-- Label -->
										<label for="<?= $key . $field['key'] ?>" class="col-sm-3 col-form-label">
											<?= $field['label'] ?>
										</label>

										<!-- Input Field -->
										<div class="col-sm-9">
											<input
												id="<?= $key . $field['key'] ?>"
												type="text"
												name="<?= $key . '[' . $field['key'] . ']' ?>"
												class="form-control"
												value="<?= $field['value'] ?>"
												placeholder="<?= $field['label'] ?>"
												required
											/>
										</div>
									</div>
								</div>
							<?php } ?>

							<!-- Add Button -->
							<div class="col-sm-<?= !empty($value['fields']) ? '12' : '12' ?> text-right mt-3">
								<button type="button" class="btn btn-info btn-add-image" id="btn-add-image" data-field_type="<?= $key ?>">
									<?= _l('add') ?>
								</button>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
<?php } ?>

<script>
index = <?= $index ?> + 1;

$('.btn-add-image').on('click', function() {
	var field_type = $(this).data('field_type');

	const html = `
		<div class="position-relative image-item d-inline-block mr-1">
			<a
				href="<?= $this->image_model->resize('no_image.png', 100, 100) ?>"
				id="logo-thumb-${field_type + index}"
				data-toggle="image"
				class="img-thumbnail"
				data-target="#${field_type + index}"
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
				name="${field_type}[images][${index}]"
				id="${field_type}${index}""
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

	var container = '.image-item-container_' + field_type;

	$(container).append(html);

	index++;
});
</script>
<style>
.btn-close {
	right: 1px;
	top: 1px;
}
</style>
