<?php /*echo "<pre>"; print_r($teachers); die;*/ ?>
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
						<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required>
					</div>

					<div class="form-group">
						<label for="description"><?php echo _l('description'); ?></label>
						<textarea rows="7" class="form-control" id="description" name="description"><?php echo $details['description'] ?? ''; ?></textarea>
					</div>

					<div class="form-group">
						<label for="city"><?php echo _l('parent_category'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="parent_id" id="parent_id">
							<option value="0"><?php echo _l('select_parent_category'); ?></option>
							<?php foreach ($categories as $category) { ?>
							<?php if (($details['parent_id'] ?? '') == $category['id']) { ?>
							<option value="<?php echo $category['id']; ?>" selected><?php echo $this->lr_category_model->formatName($category['id']); ?></option>
							<?php } else { ?>
							<option value="<?php echo $category['id']; ?>"><?php echo $this->lr_category_model->formatName($category['id']); ?></option>
							<?php } ?>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="image"><?php echo _l('image'); ?></label>
						<div class="input-group">
							<div class="custom-file">
								<input type="file" class="custom-file-input" id="image" name="image" onchange="changeTitleOfImageUploader(this)">
								<label class="custom-file-label" for="image"><?php echo $details['image'] ?? _l('choose_image'); ?></label>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="is-demo"><?php echo _l('status'); ?></label>
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

					<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
