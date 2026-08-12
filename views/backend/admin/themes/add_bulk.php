<style>
	html * { box-sizing: border-box; }

	p { margin: 0; }
	.upload__box { padding: 5px; border: 1px dashed #dee2e6; }
	.upload__inputfile { width: .1px; height: .1px; opacity: 0; overflow: hidden; position: absolute; z-index: -1; }
	.upload__btn { display: inline-block; font-weight: 600; color: #000; text-align: center; min-width: 116px; width: 100%;
		padding: 5px; transition: all .3s ease; cursor: pointer; border: 1px dashed #dee2e6; border-radius: 10px; line-height: 26px;
		font-size: 14px; }
	.upload:hover { background-color: unset; color: #4045ba; transition: all .3s ease; }
	.upload-box { margin-bottom: 10px; }
	.img-bg { background-repeat: no-repeat; background-position: center; background-size: cover; position: relative; padding-bottom: 100%;}
	.upload__img-wrap { display: flex; flex-wrap: wrap; margin: 0 -10px; }
	.upload__img-box { width: 70px; padding: 0 10px; margin-bottom: 12px; }
	.upload__img-close { width: 24px; height: 24px; border-radius: 50%; background-color: rgba(0, 0, 0, 0.5); position: absolute;
		top: 10px; right: 10px; text-align: center; line-height: 24px; z-index: 1; cursor: pointer; }
	.upload__img-close:after { content: "✖"; font-size: 14px; color: white; }
</style>
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
				<form action="<?= base_url('admin/save_bulk_theme/'); ?><?= (!empty($theme)) ? $theme['id'] : ''; ?>" method="post" enctype="multipart/form-data">
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
							<?php foreach ($categories as $category): ?>
								<option value="<?= $category['id'] ?>" 
									<?= (!empty($theme) && $theme['category_id'] == $category['id']) ? 'selected' : ''; ?>>
									<?= $category['name'] ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="form-group">
						<label for="name"> <?=_l('Name')?><span class="required">*</span></label>
						<input type="text" class="form-control" id="theme_name" name="theme_name" required="" value="<?= (!empty($theme)) ? $theme['name'] : ''; ?>">
					</div>

					<div class="form-group">
						<label for="name"> <?=_l('font_Family')?><span class="required">*</span></label>
						<input type="text" class="form-control" id="font_family" name="font_family" required="" value="<?= (!empty($theme))?$theme['font_family']:'Signika'; ?>">
					</div>

					<div class="form-group">
						<label for="name"> <?=_l('font_Size')?><span class="required">*</span>(in px)</label>
						<input type="text" class="form-control" id="font_size" name="font_size" required="" value="<?= (!empty($theme))?$theme['font_size']:'16'; ?>">
					</div>

					<div class="form-group">
						<label for="name"> <?=_l('font_color')?><span class="required">*</span></label>
						<input type="color" class="form-control" id="font_color" name="font_color" required="" value="<?= (!empty($theme))?$theme['font_color']:''; ?>">
					</div>

					<div class="form-group">
						<label for="name"> <?=_l('font_Weight')?><span class="required">*</span> <small>(eg. 100/200/300...)</small></label>
						<input type="text" class="form-control" id="font_weight" name="font_weight" required="" value="<?= (!empty($theme))?$theme['font_weight']:'600'; ?>">
					</div>
					<hr />
					<?php
					$text_boxes = [];
					if (!empty($theme)) {
						if (!empty($theme['text_boxes']))
							$text_boxes = json_decode($theme['text_boxes']);
					}
					?>
					<div class="form-group">
						<input type="hidden" name="parameter[0][s]" value="1">
						<label for="name"> <?=_l('height')?><span class="required">*</span></label>
						<input type="text" class="form-control" id="ph" name="parameter[0][p][h]" required="" value="<?= (!empty($text_boxes[0]['p']['h']))?$text_boxes[0]['p']['h']:'400';?>">
					</div>
					<div class="form-group">
						<label for="name"> <?=_l('width')?><span class="required">*</span></label>
						<input type="text" class="form-control" id="pw" name="parameter[0][p][w]" required="" value="<?= (!empty($text_boxes[0]['p']['w']))?$text_boxes[0]['p']['w']:'250';?>">
					</div>
					<div class="form-group">
						<label for="name"> <?=_l('limit')?><span class="required">*</span></label>
						<input type="text" class="form-control" id="pw" name="parameter[0][p][l]" required="" value="<?= (!empty($text_boxes[0]['p']['l']))?$text_boxes[0]['p']['l']:'400';?>">
					</div>
					<div class="form-group">
						<label for="name"> <?=_l('space_From_X_Axis')?><span class="required">*</span></label>
						<input type="text" class="form-control" id="pw" name="parameter[0][p][x]" required="" value="<?= (!empty($text_boxes[0]['p']['x']))?$text_boxes[0]['p']['x']:'60';?>">
					</div>
					<div class="form-group">
						<label for="name"> <?=_l('space_From_Y_Axis')?><span class="required">*</span></label>
						<input type="text" class="form-control" id="pw" name="parameter[0][p][y]" required="" value="<?= (!empty($text_boxes[0]['p']['y']))?$text_boxes[0]['p']['y']:'60';?>">
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
								<label for="name"> <?=_l('height')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="ph" name="parameter[1][p][h]" required="" value="<?= (!empty($text_boxes[1]['p']['h']))?$text_boxes[1]['p']['h']:'400';?>">
							</div>
							<div class="form-group">
								<label for="name"> <?=_l('width')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="pw" name="parameter[1][p][w]" required="" value="<?= (!empty($text_boxes[1]['p']['w']))?$text_boxes[1]['p']['w']:'250';?>">
							</div>
							<div class="form-group">
								<label for="name"> <?=_l('limit')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="pw" name="parameter[1][p][l]" required="" value="<?= (!empty($text_boxes[1]['p']['l']))?$text_boxes[1]['p']['l']:'400';?>">
							</div>
							<div class="form-group">
								<label for="name"> <?=_l('space_From_X_Axis')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="pw" name="parameter[1][p][x]" required="" value="<?= (!empty($text_boxes[1]['p']['x']))?$text_boxes[1]['p']['x']:'60';?>">
							</div>
							<div class="form-group">
								<label for="name"> <?=_l('space_From_Y_Axis')?><span class="required">*</span></label>
								<input type="text" class="form-control" id="pw" name="parameter[1][p][y]" required="" value="<?= (!empty($text_boxes[1]['p']['y']))?$text_boxes[1]['p']['y']:'60';?>">
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="name"> <?=_l('sort_order')?><span class="required">*</span></label>
						<input type="text" class="form-control" id="sort_order" name="sort_order" required="" value="<?= (!empty($theme)) ? $theme['sort_order'] : 0; ?>">
					</div>
					<div class="form-group upload__box">
						<div class="upload__btn-box">
							<label class="upload__btn">
								<p> <?=_l('upload_images')?> <?= (empty($theme)) ? '<span class="required">*</span>': ''; ?></p>
								<input type="file" name="image[]" multiple="" data-max_length="20" class="upload__inputfile" <?= (empty($theme)) ? 'required=""': ''; ?>>
							</label>
						</div>
						<div class="upload__img-wrap row"></div>
					</div>
					<div class="form-group" id="thumbnail-picker-area">
						<label> <?=_l('status')?></label>
						<select class="form-control" name="status">
							<option value="1" <?= (!empty($theme) && $theme['status'] == '1') ? 'selected="selected"' : ''; ?>> <?=_l('enable')?></option>
							<option value="0" <?= (!empty($theme) && $theme['status'] == '0') ? 'selected="selected"' : ''; ?>> <?=_l('disable')?></option>
						</select>
					</div>
					<button type="submit" class="btn btn-primary"> <?=_l('submit')?></button>
				</form>
			</div> <!-- end card body-->
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function() {
		ImgUpload();
	});

	function ImgUpload() {
		var imgWrap = "";
		var imgArray = [];

		$('.upload__inputfile').each(function() {
			$(this).on('change', function(e) {
				imgWrap = $(this).closest('.upload__box').find('.upload__img-wrap');
				var maxLength = $(this).attr('data-max_length');

				var files = e.target.files;
				var filesArr = Array.prototype.slice.call(files);
				var iterator = 0;
				filesArr.forEach(function(f, index) {

					if (!f.type.match('image.*')) {
						return;
					}

					if (imgArray.length > maxLength) {
						return false
					} else {
						var len = 0;
						for (var i = 0; i < imgArray.length; i++) {
							if (imgArray[i] !== undefined) {
								len++;
							}
						}
						if (len > maxLength) {
							return false;
						} else {
							imgArray.push(f);

							var reader = new FileReader();
							reader.onload = function(e) {
								var html = "<div class='upload__img-box col-md-4 col-lg-4'><div style='background-image: url(" + e.target.result + ")' data-number='" + $(".upload__img-close").length + "' data-file='" + f.name + "' class='img-bg'><div class='upload__img-close'></div></div></div>";
								imgWrap.append(html);
								iterator++;
							}
							reader.readAsDataURL(f);
						}
					}
				});
			});
		});

		$('body').on('click', ".upload__img-close", function(e) {
			var file = $(this).parent().data("file");
			for (var i = 0; i < imgArray.length; i++) {
				if (imgArray[i].name === file) {
					imgArray.splice(i, 1);
					break;
				}
			}
			$(this).parent().parent().remove();
		});
	}

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
