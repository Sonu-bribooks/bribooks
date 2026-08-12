<?php $column_class = !empty($column) ? (' col-sm-' . (12 / $column)) : '' ?>
<?php $field_class = !empty($field_class) ? ' ' . $field_class : '' ?>

<?php foreach ($fields as $field) { ?>
	<?php $id = preg_replace('/[^\w\-_]/ims', '', $field['key']); ?>
	<div class="form-group<?= !empty($field['group']) ? ' ' . $field['group'] : '' ?><?= $column_class ?>">
		<?php if ($field['type'] != 'group') { ?>
			<label for="<?= $id ?>">
				<?= $field['label'] ?>
				<?php if (!empty($field['required'])) { ?>
					<span class="required">*</span>
				<?php } ?>
			</label>
		<?php } ?>

		<?php if ($field['type'] == 'text') { ?>
			<input
				type="<?= $field['type'] ?>"
				class="form-control<?=$field_class?>"
				id="<?= $id ?>"
				name="<?= $field['key'] ?>"
				value="<?= $field['value'] ?>"
				placeholder="<?= $field['placeholder'] ?? '' ?>"
				<?= $field['required'] ? ' required' : '' ?>
			/>
		<?php } ?>

		<?php if ($field['type'] == 'datetime') { ?>
		<input
			type="<?= $field['type'] ?>"
			class="form-control datetimepicker<?=$field_class?>"
			id="<?= $field['key'] ?>"
			name="<?= $field['key'] ?>"
			value="<?= !empty($field['value']) ? date('m/d/Y h:i:s A', strtotime($field['value'])) : '' ?>"
			<?= $field['required'] ? ' required' : '' ?>
		>
		<?php } ?>

		<?php if ($field['type'] == 'number') { ?>
			<input
				type="<?= $field['type'] ?>"
				class="form-control<?=$field_class?>"
				id="<?= $id ?>"
				name="<?= $field['key'] ?>"
				value="<?= $field['value'] ?>"
				placeholder="<?= $field['placeholder'] ?? '' ?>"
				<?= $field['required'] ? ' required' : '' ?>
			/>
		<?php } ?>

		<?php if ($field['type'] == 'html') { ?>
			<textarea
				rows="4"
				class="form-control tinymce<?=$field_class?>"
				id="<?= $id ?>"
				name="<?= $field['key'] ?>"
				<?= $field['required'] ? ' required' : '' ?>
			><?= $field['value'] ?></textarea>
		<?php } ?>

		<?php if ($field['type'] == 'textarea') { ?>
			<textarea
				rows="4"
				class="form-control<?=$field_class?>"
				id="<?= $id ?>"
				name="<?= $field['key'] ?>"
				<?= $field['required'] ? ' required' : '' ?>
			><?= $field['value'] ?></textarea>
		<?php } ?>

		<?php if ($field['type'] == 'checkbox') { ?>
			<div
				id="<?= $id ?>"
				data-value="<?= is_array($field['value']) ? implode(',', $field['value']) : $field['value'] ?>"
				data-name="<?= $field['key'] ?>"
			>
				<?php foreach ($field['options'] ?? [] as $option) { ?>
					<div class="form-check">
						<input
							class="form-check-input<?=$field_class?>"
							type="checkbox"
							name="<?= $field['key'] ?>"
							value="<?= $option['value'] ?>"
							<?= in_array($option['value'], is_array($field['value']) ? $field['value'] : explode(',', $field['value'])) ? 'checked' : '' ?>
						/>
						<label class="form-check-label" for="defaultCheck1">
							<?= $option['label'] ?>
						</label>
					</div>
				<?php } ?>
			</div>
		<?php } ?>

		<?php if ($field['type'] == 'select2') { ?>
			<select
				class="form-control select2 filter_select<?= empty($field['options']) ? ' select_ajax' : '' ?><?=$field_class?>"
				data-toggle="select2"
				name="<?= $field['key'] ?>"
				id="<?= $id ?>"
				<?= $field['required'] ? ' required' : '' ?>
				data-value="<?= is_array($field['value']) ? implode(',', $field['value']) : $field['value'] ?>"
				data-ajax-url="<?= $field['ajax_url'] ?>"
				data-ajax-options="<?= $field['ajax_options'] ?>"
			>
				<option value=""><?= $field['label'] ?></option>
				<?php if (!empty($field['value'])) { ?>
					<option
						value="<?= $field['value']['value'] ?>"
						selected="selected"
					><?= $field['value']['label'] ?></option>
				<?php } ?>
			</select>
		<?php } ?>

		<?php if ($field['type'] == 'select') { ?>
			<select
				class="form-control select2<?=$field_class?>"
				data-toggle="select2"
				name="<?= $field['key'] ?>"
				id="<?= $id ?>"
				data-value="<?= is_array($field['value']) ? implode(',', $field['value']) : $field['value'] ?>"
				data-ajax-options="<?= $field['ajax_options'] ?>"
				<?= $field['required'] ? ' required' : '' ?>
			>
				<option value=""><?= $field['label'] ?></option>

				<?php foreach ($field['options'] ?? [] as $option) { ?>
					<option
						value="<?= $option['value'] ?>"
						<?= $option['value'] == $field['value'] ? ' selected' : '' ?>
					><?= $option['label'] ?></option>
				<?php } ?>
			</select>
		<?php } ?>

		<?php if ($field['type'] == 'multi_select2') { ?>
			<select
				class="form-control select2 filter_select<?= empty($field['options']) ? ' select_ajax' : '' ?><?=$field_class?>"
				data-toggle="select2"
				name="<?= $field['key'] ?>[]"
				id="<?= $id ?>"
				multiple
				data-value="<?= is_array($field['value']) ? implode(',', $field['value']) : $field['value'] ?>"
				data-ajax-url="<?= $field['ajax_url'] ?>"
				data-ajax-options="<?= $field['ajax_options'] ?>"
				<?= $field['required'] ? ' required' : '' ?>
			>
				<?php if (!empty($field['options'])) { ?>
					<?php foreach ($field['options'] ?? [] as $option) { ?>
						<option
							value="<?= $option['value'] ?>"
							<?= in_array($option['value'], is_array($field['value']) ? $field['value'] : explode(',', $field['value'])) ? ' selected' : '' ?>
						><?= $option['label'] ?></option>
					<?php } ?>
				<?php } else { ?>
					<?php if (!empty($field['value']) && is_array($field['value'])) {
						foreach ($field['value'] as $selected) { ?>
							<option value="<?= $selected['value'] ?>" selected="selected">
								<?= $selected['label'] ?>
							</option>
					<?php } } ?>
				<?php } ?>
			</select>
		<?php } ?>

		<?php if ($field['type'] == 'image') { ?>
			<div  class="image-field" data-image-base="<?= $id ?>">
				<a
					href="<?= $this->image_model->thumb($field['value']) ?>"
					id="logo-thumb-<?= $id ?>"
					data-toggle="image"
					class="img-thumbnail bg-info"
					data-target="#<?= $id ?>"
					style="display:inline-block;"
				>
					<img
						src="<?= $this->image_model->thumb($field['value']) ?>"
						data-placeholder="<?= $this->image_model->resize('no_image.png', 100, 100) ?>"
						style="width:100px; height:100px;"
					/>
				</a>
				<input
					type="hidden"
					class="form-control image-input<?=$field_class?>"
					id="<?= $id ?>"
					name="<?= $field['key'] ?>"
					value="<?= $field['value'] ?>"
					<?= $field['required'] ? ' required' : '' ?>
				/>
			</div>
		<?php } ?>

		<?php if ($field['type'] == 'file') { ?>
			<div>
				<a
					href="<?= $this->image_model->resize('no_doc.png', 100, 100) ?>"
					id="file-thumb-<?= $id ?>"
					data-toggle="image"
					class="img-thumbnail"
					data-target="#<?= $id ?>"
					<?=!empty($field['s3_bucket']) ? sprintf('data-bucket="%s"', $field['s3_bucket']) : ''?>
					<?=!empty($field['s3_region']) ? sprintf('data-region="%s"', $field['s3_region']) : ''?>
					style="display:inline-block;"
				>
					<img
						src="<?= $this->image_model->resize('no_doc.png', 100, 100) ?>"
						data-placeholder="<?= $this->image_model->resize('no_doc.png', 100, 100) ?>"
						style="width:100px; height:100px;"
					/>
					<span class="caption d-block"><?= $field['value'] ?></span>
				</a>
				<input
					type="hidden"
					class="form-control<?=$field_class?>"
					id="<?= $id ?>"
					name="<?= $field['key'] ?>"
					value="<?= $field['value'] ?>"
					<?= $field['required'] ? ' required' : '' ?>
				/>
			</div>
		<?php } ?>

		<?php if ($field['type'] == 'color') { ?>
			<div style="gap:4px;" class="d-flex align-items-center gap-2 color-field" data-color-base="<?= $id ?>">
				<input
					type="<?= $field['type'] ?>"
					class="form-control form-control-color color-picker<?=$field_class?>"
					id="<?= $id ?>"
					data-target="<?= $id ?>_hex"
					value="<?= !empty($field['value']) ? $field['value'] : '#ffffff' ?>"
				/>

				<input
					type="text"
					class="form-control color-hex<?=$field_class?>"
					id="<?= $id ?>_hex"
					name="<?= $field['key'] ?>"
					value="<?= !empty($field['value']) ? $field['value'] : '#ffffff' ?>"
					placeholder="#ffffff"
					pattern="^#([A-Fa-f0-9]{6})$"
					<?= $field['required'] ? ' required' : '' ?>
				/>
			</div>
		<?php } ?>

		<?php if ($field['type'] == 'gen_code') { ?>
			<div class="d-flex align-items-center" style="gap:4px;">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span
							class="input-group-text"
							onclick="generate()"
							style="cursor:pointer;"
							title="Generate Code"
						>
							🧬
						</span>
					</div>

					<input
						type="text"
						class="form-control"
						id="gen_code"
						name="<?= $field['key'] ?>"
						value="<?= $field['value'] ?? '' ?>"
						placeholder="Generate Coupon"
						<?= $field['required'] ? ' required' : '' ?>
					>
				</div>
			</div>
		<?php } ?>

		<!-- MULTIPLE FIELD START -->
		<?php if ($field['type'] == 'group') { ?>
			<div class="card">
				<div class="card-header">
					<?= $field['label'] ?>
					<?php if (!empty($field['required'])) { ?>
						<span class="required">*</span>
					<?php } ?>
				</div>
				<div class="card-body group-container <?= $id ?>-container">
					<?php foreach ($field['fields'] as $group_index => $group_field) { ?>
						<div class="p-1 group-item">
							<?= $this->load->view('backend/admin/generic/form_item', ['fields' => $group_field, 'group' => true], true) ?>

								<div class="remove-container text-right<?= empty($group_index) ? ' d-none' : '' ?>">
									<span
										class="btn btn-danger btn-close"
										onclick="$(this).closest('.group-item').remove();"
									>
										<i class="fa fa-times-circle text-white"></i>
										<?= _l('remove') ?>
									</span>
								</div>
							<hr />
						</div>
					<?php } ?>
				</div>

				<div class="card-footer text-right">
					<button
						type="button"
						class="btn btn-info btn-add-item"
						onclick="loadGroupItem(this)"
					>
						<?=_l('add_more')?>
					</button>

					<span class="d-none group-item-index"><?= count($field['fields']) ?></span>
				</div>
			</div>
		<?php } ?>
		<!-- MULTIPLE FIELD END -->
	</div>
<?php } ?>
