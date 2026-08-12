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
						<label for="parent">Select Category</label>
						<select class="form-control select2" data-toggle="select2" name="category_id" id="category" >
							<option value="0"><?php echo get_phrase('none'); ?></option>
							<?php foreach ($categories as $category) : ?>
								<option
									value="<?php echo $category['id']; ?>"
									<?=$category['id'] == ($details['category_id'] ?? '') ? ' selected' : ''?>
								><?php echo $category['name']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="form-group">
						<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required>
					</div>

					<div class="form-group">
						<label for="description"><?php echo _l('meta_description'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="name" name="meta_description" value="<?php echo $details['meta_description'] ?? ''; ?>" required>
					</div>

					<div class="form-group">
						<label for="description"><?php echo _l('description'); ?><span class="required">*</span></label>
						<textarea name="description" id="summernote-basic" class="form-control"><?php echo $details['description'] ?? ''; ?></textarea>
					</div>

					<div class="form-group">
						<label for="sort_order"><?php echo _l('sort_order'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="sort_order" name="sort_order" value="<?php echo $details['sort_order'] ?? 0; ?>" required>
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

					<div class="form-group">
						<label for="related"><?php echo _l('related'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="related[]" id="related" multiple>
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

	$('#related').select2({
		ajax: {
			url: '<?php echo site_url('admin/ajax_get_blogs'); ?>',
			data: function (params) {
				var query = {
					search: params.term,
				}

				return query;
			},
			processResults: function(data) {
				return {
					results: data.items
				};
			}
		}
	});
})
</script>
