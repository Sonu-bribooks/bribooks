<style>
.radio-layout {
	position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.radio-layout:checked + img {
    border-radius: 10px;
    border: 4px solid #0F9ED5;
}
</style>
<!-- start page title -->
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
			  <div class="col-lg-12">
				<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

				<form class="required-form" action="<?php echo $action ; ?>" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<label for="description"><?php echo _l('question'); ?><span class="required">*</span></label>
						<textarea rows="5" class="form-control" id="question" name="question"><?php echo $details['question'] ?? ''; ?></textarea>
					</div>

					<div class="form-group">
						<label for="image"><?php echo _l('question_image'); ?></label>
						<div class="input-group">
							<div class="custom-file">
								<input type="file" class="custom-file-input" id="image" name="image" onchange="changeTitleOfImageUploader(this)">
								<label class="custom-file-label" for="image"><?php echo $details['image'] ?? _l('choose_image'); ?></label>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="level"><?php echo _l('level'); ?><span class="required">*</span></label>
						<select class="form-control select2" data-toggle="select2" name="level" id="level">
							<option value=""><?php echo _l('select_level'); ?></option>
							<?php foreach (ICODE_LEVEL as $i) { ?>
							<?php if (($details['level'] ?? '') == $i) { ?>
							<option value="<?php echo $i; ?>" selected><?php echo $i; ?></option>
							<?php } else { ?>
							<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="category"><?php echo _l('category'); ?><span class="required">*</span></label>
						<select class="form-control select2" data-toggle="select2" name="category_id" id="category_id">
							<option value=""><?php echo _l('select_category'); ?></option>
							<?php foreach ($categories as $category) { ?>
							<?php $category_name = $this->lr_category_model->formatName($category['id']); ?>
							<?php if (($details['category_id'] ?? '') == $category['id']) { ?>
							<option value="<?php echo $category['id']; ?>" selected><?php echo $category_name; ?></option>
							<?php } else { ?>
							<option value="<?php echo $category['id']; ?>"><?php echo $category_name; ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="card bg-light card-body">
						<div class="form-group">
							<label for="options"><?php echo _l('options'); ?><span class="required">*</span></label>
							<?php for ($i = 1; $i < 5; $i++) { ?>
							<div class="form-check" style="margin-bottom: 10px;">
								<input class="form-check-input" type="radio" name="answer" value="<?=$i?>" id="answer<?=$i;?>" <?php echo ($details['answer'] ?? '') == $i ? ' checked' : ''; ?> required>
								<label class="form-check-label col-12" for="flexRadioDefault<?=$i;?>">
									<span class="row">
										<span class="col-1"><?=$i?></span>
										<input type="text" class="form-control col" id="option_<?=$i?>" name="option_<?=$i?>" value="<?php echo $details['option_' . $i] ?? ''; ?>" required>
										<div class="input-group col-6">
											<div class="custom-file">
												<input type="file" class="custom-file-input" id="option_<?=$i?>_image" name="option_<?=$i?>_image" onchange="changeTitleOfImageUploader(this)">
												<label class="custom-file-label" for="option_<?=$i?>_image"><?php echo $details['option_' . $i . '_image'] ?? _l('choose_image'); ?></label>
											</div>
										</div>
									</span>
								</label>
							</div>
							<?php } ?>
						</div>
					</div>

					<div class="form-group">
						<label for="name"><?php echo _l('explanation_heading'); ?></label>
						<input type="text" class="form-control" id="explanation_heading" name="explanation_heading" value="<?php echo $details['explanation_heading'] ?? ''; ?>">
					</div>

					<div class="form-group">
						<label for="explanation_details"><?php echo _l('explanation_details'); ?></label>
						<textarea rows="3" class="form-control" id="explanation_details" name="explanation_details"><?php echo $details['explanation_details'] ?? ''; ?></textarea>
					</div>

					<div class="form-group">
						<label for="image"><?php echo _l('explanation_image'); ?></label>
						<div class="input-group">
							<div class="custom-file">
								<input type="file" class="custom-file-input" id="explanation_image" name="explanation_image" onchange="changeTitleOfImageUploader(this)">
								<label class="custom-file-label" for="explanation_image"><?php echo $details['explanation_image'] ?? _l('choose_image'); ?></label>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="is-demo"><?php echo _l('status'); ?><span class="required">*</span></label>
						<select class="form-control select2" data-toggle="select2" name="status" id="status">
							<?php if (($details['status'] ?? 1)  == 1) { ?>
							<option value="0"><?php echo _l('disable'); ?></option>
							<option value="1" selected><?php echo _l('enable'); ?></option>
							<?php } else { ?>
							<option value="0" selected><?php echo _l('disable'); ?></option>
							<option value="1"><?php echo _l('enable'); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="name"><?php echo _l('layout'); ?><span class="required">*</span></label>
						<label>
							<input type="radio" name="layout" class="radio-layout" value="1"<?php echo ($details['layout'] ?? 1) == 1 ? ' checked' : ''; ?>>
							<img src="<?php echo site_url('assets/backend/images/layout1.jpeg'); ?>" style="width:150px; margin-right:20px">
						</label>
						<label>
							<input type="radio" name="layout" class="radio-layout" value="2"<?php echo ($details['layout'] ?? 1) == 2 ? ' checked' : ''; ?>>
							<img src="<?php echo site_url('assets/backend/images/layout2.jpeg'); ?>" style="width:150px; margin-right:20px">
						</label>
					</div>

					<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
