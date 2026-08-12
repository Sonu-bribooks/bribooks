
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('add_new_class'); ?></h4>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo _l('center_add_form'); ?></h4>

					<form class="required-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required>
						</div>
						<div class="form-group">
							<label for="address"><?php echo _l('address'); ?><span class="required">*</span></label>
							<textarea class="form-control" id="address" name="address" rows="5" required><?php echo $details['address'] ?? ''; ?></textarea>
						</div>
						<div class="form-group">
							<label for="city"><?php echo _l('city'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="city_id" id="city">
								<?php foreach ($cities as $city) { ?>
								<?php if (($details['city_id'] ?? '') == $city['id']) { ?>
								<option value="<?php echo $city['id']; ?>" selected><?php echo $city['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $city['id']; ?>"><?php echo $city['name']; ?></option>
								<?php } ?>
								<?php } ?>
							</select>
						</div>
						<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
