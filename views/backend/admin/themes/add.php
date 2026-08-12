<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
					<a href="<?php echo site_url('admin/themes'); ?>" class="btn btn-outline-primary btn-rounded alignToTitle"><i class="mdi mdi-hand-pointing-left"></i> <?=_l('back')?></a>
				</h4>
			</div>
		</div>
	</div>
</div>
<div class="row justify-content-center">
	<div class="col-lg-6 col-md-6 col-xl-6 col-sm-12 col-xs-12">
		<div class="card">
			<div class="card-header">
				<h4 class="header-title"><?php echo $page_title; ?></h4>
			</div>
			<div class="card-body">
				<form action="<?= base_url('admin/save_theme/'); ?><?=$theme['id'] ?? ''?>" method="post" enctype="multipart/form-data">
				    <div class="form-group">
						<label for="country"><?=_l('select_country')?></label>
						<select class="form-control select2" data-toggle="select2" name="country_code[]" id="select-country" multiple>
							<option value="all" <?= empty($theme_locale) || in_array('all', $theme_locale) ? 'selected' : '' ?>><?= _l('all') ?></option>
							<?php 
							$countries = $this->country_model->get_all()['rows'];
							$theme_locale = $theme_locale ?? [];
							foreach ($countries as $country): ?>
								<option value="<?= $country['code'] ?>" <?= in_array($country['code'], $theme_locale) && !in_array('all', $theme_locale) ? 'selected' : '' ?>>
									<?= $country['name'] ?>
								</option>
							<?php endforeach; ?>
						</select>

					</div>
					<div class="form-group">
					<label for="parent_id"><?=_l('select_category')?></label>
						<select class="form-control select2" data-toggle="select2" name="category" id="category">
							<option value="0"><?= _l('none') ?></option>
							<?php foreach ($categories['rows'] as $parent): ?>
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

					<div class="form-group">
						<label for="name"><?=_l('name')?><span class="required">*</span></label>
						<input type="text" class="form-control" id="theme_name" name="theme_name" required="" value="<?= (!empty($theme))?$theme['name']:''; ?>">
					</div>

					<div class="form-group">
						<label for="name"><?=_l('font_Family')?><span class="required">*</span></label>
						<input type="text" class="form-control" id="font_family" name="font_family" required="" value="<?= (!empty($theme))?$theme['font_family']:'Signika'; ?>">
					</div>

					<div class="form-group">
						<label for="name"><?=_l('font_Size')?><span class="required">*</span>(in px)</label>
						<input type="text" class="form-control" id="font_size" name="font_size" required="" value="<?= (!empty($theme))?$theme['font_size']:'16'; ?>">
					</div>

					<div class="form-group">
						<label for="name"><?=_l('font_Color')?><span class="required">*</span></label>
						<input type="color" class="form-control" id="font_color" name="font_color" required="" value="<?= (!empty($theme))?$theme['font_color']:''; ?>">
					</div>

					<div class="form-group">
						<label for="name"><?=_l('font_Weight')?><span class="required">*</span> <small>(eg. 100/200/300...)</small></label>
						<input type="text" class="form-control" id="font_weight" name="font_weight" required="" value="<?= (!empty($theme))?$theme['font_weight']:'600'; ?>">
					</div>
					<hr />
					<?php
						$text_boxes = array();
						$double_side_writing = false;
						if (!empty($theme)) {
							if (!empty($theme['text_boxes'])) {
								$text_boxes = json_decode($theme['text_boxes']);

								$double_side_writing = count($text_boxes) > 1;
							}
						}
					?>

					<div class="card">
						<div class="card-body">
							<h5 class="card-title"><?=_l('left_writing_area')?></h5>
							<input type="hidden" name="parameter[0][s]" value="1">
							<div class="form-group">
								<label for="name"><?=_l('height')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="ph" name="parameter[0][p][h]" required="" value="<?= (!empty($text_boxes[0]->p->h))?$text_boxes[0]->p->h:'400';?>">
							</div>
							<div class="form-group">
								<label for="name"><?=_l('width')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="pw" name="parameter[0][p][w]" required="" value="<?= (!empty($text_boxes[0]->p->w))?$text_boxes[0]->p->w:'250';?>">
							</div>
							<div class="form-group">
								<label for="name"><?=_l('limit')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="pw" name="parameter[0][p][l]" required="" value="<?= (!empty($text_boxes[0]->p->l))?$text_boxes[0]->p->l:'400';?>">
							</div>
							<div class="form-group">
								<label for="name"><?=_l('space_From_X_Axis')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="pw" name="parameter[0][p][x]" required="" value="<?= (!empty($text_boxes[0]->p->x))?$text_boxes[0]->p->x:'60';?>">
							</div>
							<div class="form-group">
								<label for="name"><?=_l('space_From_Y_Axis')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="pw" name="parameter[0][p][y]" required="" value="<?= (!empty($text_boxes[0]->p->y))?$text_boxes[0]->p->y:'60';?>">
							</div>
						</div>
					</div>

					<div class="form-check ml-1 mb-3">
						<input class="form-check-input" type="checkbox" name="double_side_writing" value="1" <?=!empty($double_side_writing) ? 'checked' : '' ?> onclick="this.checked ? $('#double_side_writing').show() : $('#double_side_writing').hide()"/>
						<label class="form-check-label" for="flexCheckDefault">
							<b class="text-danger"><?=_l('check_for_double_side_writing')?></b>
						</label>
					</div>

					<div class="card" style="display:<?=!empty($double_side_writing) ? 'block' : 'none' ?>" id="double_side_writing">
						<div class="card-body">
							<h5 class="card-title"><?=_l('right_writing_area')?></h5>
							<input type="hidden" name="parameter[1][s]" value="2">
							<div class="form-group">
								<label for="name"><?=_l('height')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="ph" name="parameter[1][p][h]" required="" value="<?= (!empty($text_boxes[1]->p->h))?$text_boxes[1]->p->h:'400';?>">
							</div>
							<div class="form-group">
								<label for="name"><?=_l('width')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="pw" name="parameter[1][p][w]" required="" value="<?= (!empty($text_boxes[1]->p->w))?$text_boxes[1]->p->w:'250';?>">
							</div>
							<div class="form-group">
								<label for="name"><?=_l('limit')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="pw" name="parameter[1][p][l]" required="" value="<?= (!empty($text_boxes[1]->p->l))?$text_boxes[1]->p->l:'400';?>">
							</div>
							<div class="form-group">
								<label for="name"><?=_l('space_From_X_Axis')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="pw" name="parameter[1][p][x]" required="" value="<?= (!empty($text_boxes[1]->p->x))?$text_boxes[1]->p->x:'60';?>">
							</div>
							<div class="form-group">
								<label for="name"><?=_l('space_From_Y_Axis')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="pw" name="parameter[1][p][y]" required="" value="<?= (!empty($text_boxes[1]->p->y))?$text_boxes[1]->p->y:'60';?>">
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="name"><?=_l('sort_Order')?><span class="required">*</span></label>
						<input type="text" class="form-control" id="sort_order" name="sort_order" required="" value="<?= (!empty($theme))?$theme['sort_order']:0; ?>">
					</div>
					<div class="form-group" id="thumbnail-picker-area">
						<label> <?=_l('theme_Thumbnail')?> <?= (empty($theme)) ? '<span class="required">*</span>': ''; ?><small>(The Image Size Should Be: 400 X 255)</small> </label>
						<div class="input-group">
							<div class="custom-file">
								<input type="file" class="custom-file-input" id="category_thumbnail" name="image" accept="image/*" onchange="changeTitleOfImageUploader(this)" <?= (empty($theme)) ? 'required=""': ''; ?>>
								<label class="custom-file-label" for="category_thumbnail"><?=_l('choose_Thumbnail')?></label>
							</div>
						</div>
					</div>

					<div class="form-group" id="thumbnail-picker-area">
						<label><?=_l('status')?> </label>
						<select class="form-control" name="status">
						<option value="1" <?= (!empty($theme) && $theme['status']=='1')?'selected="selected"':''; ?>><?=_l('enable')?></option>
							<option value="0" <?= (!empty($theme) && $theme['status']=='0')?'selected="selected"':''; ?>><?=_l('disable')?></option>
						</select>
					</div>

					<button type="submit" class="btn btn-primary"><?=_l('submit')?></button>
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