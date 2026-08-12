<?php foreach ($fields as $field) { ?>
	<div class="form-group row mb-3<?= !empty($field['group']) ? ' ' . $field['group'] : '' ?>">
		<?php $id = preg_replace('/[^\w\-_]/ims', '', $field['key']); ?>

		<?php if ($field['type'] != 'group') { ?>
			<label for="<?= $id ?>" class="col-md-3 col-form-label">
				<?= $field['label'] ?>
				<?php if (!empty($field['required'])) { ?>
					<span class="required">*</span>
				<?php } ?>
			</label>
		<?php } ?>

		<div class="col-md-<?= $field['type'] != 'group' ? 9 : 12 ?>">
			<?php if ($field['type'] == 'text') { ?>
				<input
					type="<?= $field['type'] ?>"
					class="form-control<?=!empty($field['datetime']) ? ' datetimepicker' : ''?>"
					id="<?= $id ?>"
					name="<?= $field['key'] ?>"
					value="<?= !empty($field['datetime']) ? date('m/d/Y h:i:s A', strtotime($field['value'])) : $field['value'] ?>"
					placeholder="<?= $field['placeholder'] ?? '' ?>"
					<?= $field['required'] ? ' required' : '' ?>
				>
			<?php } ?>

			<?php if ($field['type'] == 'number') { ?>
				<input
					type="<?= $field['type'] ?>"
					class="form-control"
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
					class="form-control tinymce"
					id="<?= $id ?>"
					name="<?= $field['key'] ?>"
					<?= $field['required'] ? ' required' : '' ?>
				><?= $field['value'] ?></textarea>
			<?php } ?>
			
			<?php if ($field['type'] == 'textarea') { ?>
				<textarea
					rows="4"
					class="form-control"
					id="<?= $id ?>"
					name="<?= $field['key'] ?>"
					<?= $field['required'] ? ' required' : '' ?>
				><?= $field['value'] ?></textarea>
			<?php } ?>

			<?php if ($field['type'] == 'image') { ?>
				<div>
					<a
						href="<?= $this->image_model->resize(!empty($field['value']) ? ($this->config->item('s3_user_gallery') . $field['image']) : 'no_image.png', 100, 100) ?>"
						id="logo-thumb-<?= $id ?>"
						data-toggle="image"
						class="img-thumbnail"
						data-target="#<?= $field['key'] ?>"
						style="display:inline-block;"
					>
						<img
							src="<?= $this->image_model->resize(!empty($field['value']) ? $this->config->item('cloudfront_url') . ($this->config->item('s3_user_gallery') . $field['value']) : 'no_image.png', 100, 100) ?>"
							data-placeholder="<?php echo $this->image_model->resize('no_image.png', 100, 100) ?>"
							style="width:100px; height:100px;"
						/>
					</a>
					<input
						type="hidden"
						class="form-control"
						id="<?= $id ?>"
						name="<?= $field['key'] ?>"
						value="<?= $field['value'] ?>"
						<?= $field['required'] ? ' required' : '' ?>
					/>
				</div>
			<?php } ?>

			<?php if ($field['type'] == 'checkbox') { ?>
				<div id="<?= $id ?>">
					<?php foreach ($field['options'] ?? [] as $option) { ?>
						<div class="form-check">
							<input
								class="form-check-input"
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
					class="form-control select2 filter_select"
					data-toggle="select2"
					name="<?= $field['key'] ?>"
					id="<?= $id ?>"
					<?= $field['required'] ? ' required' : '' ?>
					data-ajax-url=<?= $field['ajax_url'] ?>
					<?= !empty($field['target']) ? sprintf('data-target="%s"', $field['target']) : '' ?>
				>
					<?php if (!empty($field['value'])) { ?>
						<option
							value="<?= $field['value']['value'] ?>"
							selected="selected"
						><?= $field['value']['label'] ?></option>
					<?php } ?>
				</select>
			<?php } ?>

			<?php if ($field['type'] == 'multi_select2') { ?>
				<select
					class="form-control select2 filter_multi_select"
					data-toggle="select2"
					name="<?= $field['key'] ?>[]"
					id="<?= $id ?>"
					multiple
					<?= $field['required'] ? ' required' : '' ?>
					data-ajax-url=<?= $field['ajax_url'] ?>
					<?= !empty($field['target']) ? sprintf('data-target="%s"', $field['target']) : '' ?>
				>
					<?php if (!empty($field['value']) && is_array($field['value'])) {
						foreach ($field['value'] as $selected) { ?>
							<option value="<?= $selected['value'] ?>" selected="selected">
								<?= $selected['label'] ?>
							</option>
					<?php } } ?>
				</select>
			<?php } ?>

			<?php if ($field['type'] == 'select') { ?>
				<select
					class="form-control select2"
					data-toggle="select2"
					name="<?= $field['key'] ?>"
					id="<?= $id ?>"
					<?= $field['required'] ? ' required' : '' ?>
					<?= !empty($field['target']) ? sprintf('data-target="%s"', $field['target']) : '' ?>
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
								<?= $this->load->view('backend/admin/event/stage/generic', ['fields' => $group_field, 'group' => true], true) ?>

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
							<?=_l('add')?>
						</button>

						<span class="d-none group-item-index"><?= count($field['fields']) ?></span>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
<?php } ?>

<?php if (empty($group)) { ?>
<script>
function initDatetime(inputTarget) {
	$(inputTarget).datetimepicker({
		format: 'MM/DD/YYYY hh:mm:ss A',
		showClose: true,
	});
}

function fixSelect2(target) {
	$(target).select2({
		ajax: {
			url: $(this).data('ajax-url'),
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					search: params.term,
				};
			},
			processResults: function (data) {
				return {
					results: data
				};
			},
			cache: true
		},
		placeholder: '<?=_l('select')?>',
		minimumInputLength: 2
	});
}

function fixTinymce(target) {
	console.log(target)
	tinymce.init({
		selector: '#' + target,
		branding: false,
		force_br_newlines: true,
		force_p_newlines: false,
		forced_root_block: '',
		plugins: 'lists code emoticons link',
		toolbar: 'undo redo | styleselect | bold italic | ' +
			'alignleft aligncenter alignright alignjustify | ' +
			'outdent indent | numlist bullist | emoticons',
	});
}

function loadGroupItem(target) {
	const rootContainer = $(target).parent().parent();
	const groupContainer = rootContainer.find('.group-container');
	const itemClone = rootContainer.find('.group-item').first().clone();
	const indexContainer = $(target).parent().find('.group-item-index');
	const currentIndex = parseInt(indexContainer.text());

	itemClone.find('.select2-container').remove();
	itemClone.find('.tox-tinymce').remove();
	itemClone.find('.remove-container').removeClass('d-none')

	itemClone.find('input, select, textarea').each(function() {
		const el = $(this);
		el.prop('value', '');
		el.prop('checked', false);
		el.prop('name', el.prop('name').replace(/\[\d\]/, `[${currentIndex}]`));
		el.prop('id', el.prop('id').replace(/\d/, `${currentIndex}`));
		el.attr('data-select2-id') && el.removeAttr('data-select2-id');
	});
	itemClone.appendTo(groupContainer);
	indexContainer.text(currentIndex + 1);

	initDatetime('.datetimepicker');

	itemClone.find('.filter_select, .filter_multi_select').each(function() {
		$el = $(this);
		fixSelect2($el);
	});

	itemClone.find('.tinymce').each(function() {
		$el = $(this);
		$el.removeAttr('aria-hidden');
		$el.show();
		fixTinymce($el.attr('id'));
	});
}

$(function() {
	initDatetime('.datetimepicker');
	// initSelectAjax('.filter_select.select_ajax');
	// initTinymce('.tinymce');
	// initSelectAjaxWithOptions('select[data-ajax-options]');
	// initSelectChange('select[data-target]');
});
</script>
<script>
$(function() {
	// $('.datetimepicker').datetimepicker({
	// 	format: 'MM/DD/YYYY hh:mm:ss A',
	// 	showClose: true,
	// });
	$('.filter_select').select2({
		ajax: {
			url: $(this).data('ajax-url'),
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					search: params.term,
				};
			},
			processResults: function (data) {
				return {
					results: data
				};
			},
			cache: true
		},
		placeholder: '<?=_l('select')?>',
		minimumInputLength: 2
	});
	$('.filter_multi_select').select2({
		multiple: true,
		ajax: {
			url: $(this).data('ajax-url'),
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					search: params.term,
				};
			},
			processResults: function (data) {
				return {
					results: data
				};
			},
			cache: true
		},
		placeholder: '<?=_l('select')?>',
		minimumInputLength: 2
	});
	$('select[data-target]').on('change', function() {
		$el = $(this);
		const target = $el.data('target');

		if (this.value == 0) {
			$(`.${target}`).hide();
			$(`.${target} select, .${target} input`).prop('required', false);
			$(`.${target} label .required`).remove();
		} else {
			$(`.${target}`).show();
			$(`.${target} select, .${target} input`).prop('required', true);
			$(`.${target} label`).append('<span class="required">*</span>');
		}
	});

	$('select[data-target]').trigger('change');
	tinymce.remove();
	// tinymce.init(tinyconfig);

	tinymce.init({
		selector: '.tinymce',
		branding: false,
		force_br_newlines: true,
		force_p_newlines: false,
		forced_root_block: '',
		plugins: 'lists code emoticons link',
		toolbar: 'undo redo | styleselect | bold italic | ' +
			'alignleft aligncenter alignright alignjustify | ' +
			'outdent indent | numlist bullist | emoticons',
	});
});
</script>
<?php } ?>
