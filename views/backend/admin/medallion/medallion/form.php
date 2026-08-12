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
						<label for="is-demo"><?php echo _l('type'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="type" id="type">
							<option value="user" <?= $details['type'] == 'user' ? 'selected' : '' ?>><?php echo _l('user'); ?></option>
							<option value="school" <?= $details['type'] == 'school' ? 'selected' : '' ?>><?php echo _l('school'); ?></option>
						</select>
					</div>

					<div class="form-group">
						<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required>
					</div>

					<div class="form-group">
						<label for="quantity"><?php echo _l('stock'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="quantity" name="quantity" value="<?php echo $details['quantity'] ?? 1; ?>" required>
					</div>

					<div class="form-group">
						<label for="sold"><?php echo _l('trigger_copies'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="sold" name="sold" value="<?php echo $details['sold'] ?? 1; ?>" required>
					</div>

					<div class="form-group">
						<label for="price"><?php echo _l('price'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="price" name="price" value="<?php echo $details['price'] ?? 1; ?>" required>
					</div>

					<div class="form-group">
						<label for="weight"><?php echo _l('weight'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="weight" name="weight" value="<?php echo $details['weight'] ?? 1; ?>" required>
					</div>

					<div class="form-group">
						<label for="weight"><?php echo _l('min_published'); ?></label>
						<input type="number" class="form-control" id="min_published" name="min_published" value="<?php echo $details['min_published'] ?? 0; ?>">
					</div>

					<div class="form-group">
						<label for="weight"><?php echo _l('max_published'); ?></label>
						<input type="number" class="form-control" id="max_published" name="max_published" value="<?php echo $details['max_published'] ?? 0; ?>">
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

					<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l('submit'); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>
<script>
$(function() {
	$('.datetimepicker').datetimepicker({

	})
})
</script>
