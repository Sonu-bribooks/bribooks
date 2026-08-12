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
						<label for="book_id"><?php echo _l('book'); ?><span class="required">*</span> </label>
						<select class="form-control select2" data-toggle="select2" name="book_id" id="book_id" required>
							<option value=""><?php echo _l('select_a_book'); ?></option>
							<?php if (!empty($details['book'])) { ?>
							<option value="<?=$details['book_id']?>" selected><?=$details['book']?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="version"><?php echo _l('select_version'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="version" id="version">
							<option value=""><?php _el('select_version'); ?></option>
							<?php if (!empty($details['version'])) { ?>
							<option value="<?=$details['version']?>" selected><?=$details['version']?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="option"><?php echo _l('cover_type'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="option" id="option">
							<?php if (($details['option'] ?? '') == 'hardcover') { ?>
							<option value="paperback"><?php echo _l('paperback'); ?></option>
							<option value="hardcover" selected><?php echo _l('hard_cover'); ?></option>
							<?php } else { ?>
							<option value="paperback" selected><?php echo _l('paperback'); ?></option>
							<option value="hardcover"><?php echo _l('hard_cover'); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label for="quantity"><?php echo _l('quantity'); ?><span class="required">*</span></label>
						<input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo $details['quantity'] ?? ''; ?>" placeholder="<?=_l('enter_quantity')?>" required>
					</div>

					<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<script>
$(function() {
	$('#book_id').select2({
		ajax: {
			url: '<?php echo site_url('admin/ajax_filter_books'); ?>',
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

	$('#book_id').on('select2:select', function (e) {
		var data = e.params.data;
		let options = [];

		if (!data.selected) return;

		for (let i = 1; i <= parseInt(data.version); i++) {
			options.push(`<option value="${i}">${i}</option>`)
		}

		$('#version').html(options.join('')).select2();
	});
});
</script>
