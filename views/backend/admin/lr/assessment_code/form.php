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
						<label for="code"><?php echo _l('code'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="code" name="code" readonly value="<?php echo $details['code'] ?? ''; ?>" required>
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
						<label for="city"><?php echo _l('student/teacher'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="user_id" id="user_id">
							<option value="0"><?php echo _l('select_student/teacher'); ?></option>
							<optgroup label="<?php _el('teacher'); ?>">
								<?php foreach ($teachers as $user) { ?>
								<?php if (($details['user_id'] ?? '') == $user['id']) { ?>
								<option value="<?php echo $user['id']; ?>" selected><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $user['id']; ?>"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></option>
								<?php } ?>
								<?php } ?>
							</optgroup>

							<optgroup label="<?php _el('student'); ?>">
								<?php foreach ($students as $user) { ?>
								<?php if (($details['user_id'] ?? '') == $user['id']) { ?>
								<option value="<?php echo $user['id']; ?>" selected><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $user['id']; ?>"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></option>
								<?php } ?>
								<?php } ?>
							</optgroup>
						</select>
					</div>

					<div class="form-group">
						<label for="city"><?php echo _l('category'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="category_id" id="category_id">
							<option value="0"><?php echo _l('select_category'); ?></option>
							<?php foreach ($categories as $category) { ?>
							<?php if (($details['category_id'] ?? '') == $category['id']) { ?>
							<option value="<?php echo $category['id']; ?>" selected><?php echo $this->lr_category_model->formatName($category['id']); ?></option>
							<?php } else { ?>
							<option value="<?php echo $category['id']; ?>"><?php echo $this->lr_category_model->formatName($category['id']); ?></option>
							<?php } ?>
							<?php } ?>
						</select>
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
