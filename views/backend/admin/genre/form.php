<!-- Page Title -->
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title">
					<i class="mdi mdi-apple-keyboard-command title_icon"></i>
					<?php echo $page_title; ?>
					<a href="<?php echo base_url('admin/genre'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle">
						<i class="mdi mdi-hand-pointing-left"></i> <?= _l('back') ?>
					</a>
				</h4>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<form class="required-form" action="<?php echo base_url('admin/save_genre/') . (!empty($genre_info) ? $genre_info['id'] : ''); ?>" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label for="country"><?= _l('select_country') ?></label>
							<select class="form-control select2" data-toggle="select2" name="country_code[]" id="select-country" multiple>
								<option value="all" <?= empty($genre_locale) || in_array('all', $genre_locale) ? 'selected' : '' ?>><?= _l('all') ?></option>
								<?php
								$countries = $this->country_model->get_all()['rows'];
								$genre_locale = $genre_locale ?? [];
								foreach ($countries as $country): ?>
									<option value="<?= $country['code'] ?>" <?= in_array($country['code'], $genre_locale) && !in_array('all', $genre_locale) ? 'selected' : '' ?>>
										<?= $country['name'] ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-group">
							<label for="code"><?= _l('code') ?></label>
							<input type="text" class="form-control" id="code" name="code" value="<?php echo substr(md5(rand(0, 1000000)), 0, 10); ?>" readonly>
						</div>
						<div class="form-group">
							<label for="name"><?= _l('name') ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="name" name="name" value="<?= (!empty($genre_info)) ? $genre_info['name'] : ''; ?>" required>
						</div>
						<div class="form-group" id="thumbnail-picker-area">
							<label><?= _l('thumbnail') ?> <?= (empty($genre_info)) ? '<span class="required">*</span>' : ''; ?> <small>(<?= _l('The_Image_Size_Should_Be:_400_X_255') ?>)</small></label>
							<div class="input-group">
								<div class="custom-file">
									<input type="file" class="custom-file-input" id="genre_thumbnail" name="imageFile" accept="image/*" onchange="changeTitleOfImageUploader(this)" <?= (empty($genre_info)) ? 'required=""' : ''; ?>>
									<label class="custom-file-label" for="genre_thumbnail"><?= _l('choose_thumbnail') ?></label>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="sort_order"><?= _l('sort_order') ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="sort_order" name="sort_order" required value="<?= (!empty($genre_info)) ? $genre_info['sort_order'] : 0; ?>">
						</div>
						<div class="form-group" id="thumbnail-picker-area">
							<label><?= _l('status') ?></label>
							<select class="form-control" name="status">
								<option value="1" <?= (!empty($genre_info) && $genre_info['status'] == '1') ? 'selected="selected"' : ''; ?>><?= _l('enable') ?></option>
								<option value="0" <?= (!empty($genre_info) && $genre_info['status'] == '0') ? 'selected="selected"' : ''; ?>><?= _l('disable') ?></option>
							</select>
						</div>
						<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?= _l('submit'); ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Script Section -->
<script>
	$(document).ready(function() {
		$('#select-country').select2();

		$('#select-country').on('change', function() {
			const selectedValues = $(this).val();

			if (selectedValues.includes('all') && selectedValues.length > 1) {
				$(this).val(selectedValues.filter(value => value !== 'all'));
			} else if (selectedValues.includes('all')) {
				$(this).val(['all']);
			}

			$(this).trigger('change.select2');
		});
	});
</script>
