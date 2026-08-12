
<!-- start page title -->
<div class="row">
	<div class="col-12">
		<div class="page-title-box ">
			<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('update_slot'); ?></h4>
		</div>
	</div>
</div>

<div class="row justify-content-md-center">
	<div class="col-xl-6">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo _l('update_slot_form'); ?></h4>

					<form class="required-form" action="<?php echo site_url('admin/slots/edit/'.$slot_id); ?>" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="name" name="name" value="<?php echo $slot_details['name']; ?>" required>
						</div>

						<div class="form-group">
							<label for="type"><?php echo _l('type'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="type" id="type">
								<?php if ($slot_details['type'] == 'daily') { ?>
								<option value="daily" selected><?php echo _l('daily'); ?></option>
								<option value="weekend"><?php echo _l('weekend'); ?></option>
								<?php } else { ?>
								<option value="daily"><?php echo _l('daily'); ?></option>
								<option value="weekend" selected><?php echo _l('weekend'); ?></option>
								<?php } ?>
							</select>
						</div>

						<div class="form-group" id="time-picker-area">
							<label for="slot"><?php echo _l('time'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="slot" id="slot">
								<?php for ($i = 6; $i < 23; $i++) { ?>
								<?php $j = $i < 10 ? '0' . $i : $i; ?>
								<?php if ($slot_details['slot_start'] == "{$j}:00:00") { ?>
								<option value="<?php echo $i; ?>:00" selected><?php echo $i; ?>:00</option>
								<?php } else { ?>
								<option value="<?php echo $i; ?>:00"><?php echo $i; ?>:00</option>
								<?php } ?>
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

<script type="text/javascript">
function checkCategoryType(slot_type) {
	if (slot_type > 0) {
		$('#thumbnail-picker-area').hide();
		$('#icon-picker-area').hide();
	}else {
		$('#thumbnail-picker-area').show();
		$('#icon-picker-area').show();
	}
}

$(document).ready(function () {
	var parent_slot = $('#parent').val();
	checkCategoryType(parent_slot);
});
</script>
