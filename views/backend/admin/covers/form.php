<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<a href="<?php echo base_url('admin/covers'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-hand-pointing-left"></i> <?= _l('back'); ?></a>
				</h4>
			</div>
		</div>
	</div>
</div>
<div class="row justify-content-center">
	<div class="col-lg-6 col-md-6 col-xl-6 col-sm-12 col-xs-12">
		<div class="card">
			<div class="card-header">
				<h4 class="header-title"> <?= _l('add_cover'); ?></h4>
			</div>
			<div class="card-body">
				<form class="required-form" action="<?= base_url('admin/save_cover/'); ?><?= (!empty($cover_info)) ? $cover_info['id'] : ''; ?>" method="post" enctype="multipart/form-data">
				    <div class="form-group">
						<label for="country"><?=_l('select_country')?></label>
						<select class="form-control select2" data-toggle="select2" name="country_code[]" id="select-country" multiple>
							<option value="all" <?= empty($cover_locale) || in_array('all', $cover_locale) ? 'selected' : '' ?>><?= _l('all') ?></option>
							<?php 
							$countries = $this->country_model->get_all()['rows'];
							$cover_locale = $cover_locale ?? [];
							foreach ($countries as $country): ?>
								<option value="<?= $country['code'] ?>" <?= in_array($country['code'], $cover_locale) && !in_array('all', $cover_locale) ? 'selected' : '' ?>>
									<?= $country['name'] ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-group">
						<label for="parent_id"> <?= _l('select_category'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="category" id="category">
							<option value="0"><?= _l('none') ?></option>
							<?php foreach ($categories as $parent): ?>
								<optgroup label="<?= $parent['name'] ?>">
									<?php 
										$child_categories = $this->category_model->get_all(['parent_id' => $parent['id']])['rows'] ?? []; 
										
										foreach ($child_categories as $category): ?>
										<option value="<?= $category['id'] ?>" 
											<?= (!empty($cover_info) && $cover_info['category_id'] == $category['id']) ? 'selected' : ''; ?>>
											<?= $category['name'] ?>
										</option>
									<?php endforeach; ?>
								</optgroup>
							<?php endforeach; ?>
						</select>
					</div>
					<h6> <?= _l('header_style'); ?></h6>
					<hr />
					<?php
					$heading_style = [];
					$footer_style  = [];
					if (!empty($cover_info)) {
						if (!empty($cover_info['heading_style']))
							$heading_style = json_decode($cover_info['heading_style'], true);

						if (!empty($cover_info['footer_style']))
							$footer_style = json_decode($cover_info['footer_style'], true);
					}
					?>
					<div class="form-group">
						<label for="name"> <?= _l('limit'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="hsl" name="hs[limit]" required="" value="<?= (!empty($heading_style['limit'])) ? $heading_style['limit'] : '30'; ?>">
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('space_from_top'); ?><span class="required">*</span>(in px)</label>
						<input type="text" class="form-control" id="hsl" name="hs[style][top]" required="" value="<?= (!empty($heading_style['style']['top'])) ? $heading_style['style']['top'] : '30'; ?>">
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('space_from_left'); ?><span class="required">*</span>(in px)</label>
						<input type="text" class="form-control" id="hsl" name="hs[style][left]" required="" value="<?= (!empty($heading_style['style']['left'])) ? $heading_style['style']['left'] : '30'; ?>">
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('space_from_right'); ?><span class="required">*</span>(in px)</label>
						<input type="text" class="form-control" id="hsl" name="hs[style][right]" required="" value="<?= (!empty($heading_style['style']['right'])) ? $heading_style['style']['right'] : '30'; ?>">
					</div>
					<?php /* ?>
					<div class="form-group">
						<label for="name">Height<span class="required">*</span>(in px)</label>
						<input type="text" class="form-control" id="hsh" name="hs[style][height]" required="" value="<?= (!empty($heading_style->style->height)) ? $heading_style->style->height : ''; ?>">
					</div>
					<?php */ ?>
					<div class="form-group">
						<label for="name"> <?= _l('font_size'); ?><span class="required">*</span>(in px)</label>
						<input type="text" class="form-control" id="hss" name="hs[style][fontSize]" required="" value="<?= (!empty($heading_style['style']['fontSize'])) ? $heading_style['style']['fontSize'] : '55'; ?>">
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('text_align'); ?><span class="required">*</span></label>
						<select class="form-control" name="hs[style][textAlign]" required="">
							<option value=""> <?= _l('select_option'); ?></option>
							<option value="left" <?= (!empty($heading_style['style']['textAlign']) && $heading_style['style']['textAlign'] == 'left') ? 'selected="selected"' : ''; ?>> <?= _l('left'); ?></option>
							<option value="right" <?= (!empty($heading_style['style']['textAlign']) && $heading_style['style']['textAlign'] == 'right') ? 'selected="selected"' : ''; ?>> <?= _l('right'); ?></option>
							<option value="center" <?= (!empty($heading_style['style']['textAlign']) && $heading_style['style']['textAlign'] == 'center' || empty($heading_style['style']['textAlign'])) ? 'selected="selected"' : ''; ?>> <?= _l('center'); ?></option>
							<option value="justify" <?= (!empty($heading_style['style']['textAlign']) && $heading_style['style']['textAlign'] == 'justify') ? 'selected="selected"' : ''; ?>> <?= _l('justify'); ?></option>
						</select>
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('font_family'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="hsff" name="hs[style][fontFamily]" required="" value="<?= (!empty($heading_style['style']['fontFamily'])) ? $heading_style['style']['fontFamily'] : 'Impact'; ?>">
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('font_color'); ?><span class="required">*</span></label>
						<input type="color" class="form-control" id="hsc" name="hs[style][color]" required="" value="<?= (!empty($heading_style['style']['color'])) ? $heading_style['style']['color'] : '#ffffff'; ?>">
					</div>
					<hr />
					<h6> <?= _l('footer_style'); ?></h6>
					<hr />
					<?php
					$footer_style  = [];
					if (!empty($cover_info)) {
						if (!empty($cover_info['footer_style']))
							$footer_style = json_decode($cover_info['footer_style'], true);
					}
					?>
					<div class="form-group">
						<label for="name"> <?= _l('limit'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="fsl" name="fs[limit]" required="" value="<?= (!empty($footer_style['limit'])) ? $footer_style['limit'] : '50'; ?>">
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('space_from_top'); ?><span class="required">*</span>(in px)</label>
						<input type="text" class="form-control" id="fst" name="fs[style][top]" required="" value="<?= (!empty($footer_style['style']['top'])) ? $footer_style['style']['top'] : '0'; ?>">
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('Ssace_from_left'); ?><span class="required">*</span>(in px)</label>
						<input type="text" class="form-control" id="fsle" name="fs[style][left]" required="" value="<?= (!empty($footer_style['style']['left'])) ? $footer_style['style']['left'] : '0'; ?>">
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('space_from_right'); ?><span class="required">*</span>(in px)</label>
						<input type="text" class="form-control" id="fsri" name="fs[style][right]" required="" value="<?= (!empty($footer_style['style']['right'])) ? $footer_style['style']['right'] : '0'; ?>">
					</div>
					<?php /* ?>
					<div class="form-group">
						<label for="name">Height<span class="required">*</span>(in px)</label>
						<input type="text" class="form-control" id="hsh" name="fs[style][height]" required="" value="<?= (!empty($footer_style->style->height)) ? $footer_style->style->height : ''; ?>">
					</div>
					<?php */ ?>
					<div class="form-group">
						<label for="name"> <?= _l('font_size'); ?><span class="required">*</span>(in px)</label>
						<input type="text" class="form-control" id="hss" name="fs[style][fontSize]" required="" value="<?= (!empty($footer_style['style']['fontSize'])) ? $footer_style['style']['fontSize'] : '14'; ?>">
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('text_align'); ?><span class="required">*</span></label>
						<select class="form-control" name="fs[style][textAlign]" required="">
							<option value=""> <?= _l('select_option'); ?></option>
							<option value="left" <?= (!empty($footer_style['style']['textAlign']) && $footer_style['style']['textAlign'] == 'left') ? 'selected="selected"' : ''; ?>> <?= _l('left'); ?></option>
							<option value="right" <?= (!empty($footer_style['style']['textAlign']) && $footer_style['style']['textAlign'] == 'right') ? 'selected="selected"' : ''; ?>> <?= _l('right'); ?></option>
							<option value="center" <?= (!empty($footer_style['style']['textAlign']) && $footer_style['style']['textAlign'] == 'center' || empty($footer_style)) ? 'selected="selected"' : ''; ?>> <?= _l('center'); ?></option>
							<option value="justify" <?= (!empty($footer_style['style']['textAlign']) && $footer_style['style']['textAlign'] == 'justify') ? 'selected="selected"' : ''; ?>> <?= _l('justify'); ?></option>
						</select>
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('tags'); ?><span class="required">*</span></label>
						<textarea rows="4" name="tags" class="form-control"><?= (!empty($cover_info)) ? $cover_info['tags'] : ""; ?></textarea>
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('font_family'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="fsff" name="fs[style][fontFamily]" required="" value="<?= (!empty($footer_style['style']['fontFamily'])) ? $footer_style['style']['fontFamily'] : 'Signika'; ?>">
					</div>
					<div class="form-group">
						<label for="name"> <?= _l('font_color'); ?><span class="required">*</span></label>
						<input type="color" class="form-control" id="fsc" name="fs[style][color]" required="" value="<?= (!empty($footer_style['style']['color'])) ? $footer_style['style']['color'] : '#000000'; ?>">
					</div>
					<hr />
					<div class="form-group">
						<label for="name"> <?= _l('sort_order'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="sort_order" name="sort_order" required="" value="<?= (!empty($cover_info)) ? $cover_info['sort_order'] : 0; ?>">
					</div>
					<div class="form-group" id="thumbnail-picker-area">
						<label> <?= _l('cover_thumbnail'); ?> <?= (empty($cover_info)) ? '<span class="required">*</span>': ''; ?><small> <?= _l('(_the_image_size_should_be:_400_X_255_)'); ?></small> </label>
						<div class="input-group">
							<div class="custom-file">
								<input type="file" class="custom-file-input" id="cover_thumbnail" name="imageFile" accept="image/*" onchange="changeTitleOfImageUploader(this)" <?= (empty($cover_info)) ? 'required=""': ''; ?>>
								<label class="custom-file-label" for="cover_thumbnail"> <?= _l('choose_thumbnail'); ?></label>
							</div>
						</div>
					</div>
					<div class="form-group" id="thumbnail-picker-area">
						<label> <?= _l('status'); ?> </label>
						<select class="form-control" name="status">
							<option value="1" <?= (!empty($cover_info) && $cover_info['status'] == '1') ? 'selected="selected"' : ''; ?>> <?= _l('enable'); ?></option>
							<option value="0" <?= (!empty($cover_info) && $cover_info['status'] == '0') ? 'selected="selected"' : ''; ?>> <?= _l('disable'); ?></option>
						</select>
					</div>

					<button type="submit" class="btn btn-primary"> <?= _l('submit'); ?></button>
				</form>
			</div> <!-- end card body-->
		</div>
	</div>
</div>

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