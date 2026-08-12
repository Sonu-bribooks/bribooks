<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?></h4>
			</div>
		</div>
	</div>
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
				<div class="col-lg-12">
					<h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

					<form class="required-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
						<div class="form-group">
							<label for="license_total"><?php echo _l('total_license'); ?><span class="required">*</span></label>
							<input type="number" class="form-control" id="license_total" name="license_total" value="<?php echo $details['license_total'] ?? ''; ?>" required>
						</div>
						
						<div class="form-group">
							<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="name" name="name" value="<?php echo $details['name'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="name"><?php echo _l('site_code'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="site_code" name="site_code" value="<?php echo $details['site_code'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="email_alert"><?php echo _l('email_alerts(comma_separated)'); ?></label>
							<input type="text" class="form-control" id="email_alert" name="email_alert" value="<?php echo $details['email_alert'] ?? ''; ?>">
						</div>

						<div class="form-group">
							<label for="address"><?php echo _l('address'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="address" name="address" value="<?php echo $details['address'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="owner_email"><?php echo _l('owner_email'); ?><span class="required">*</span></label>
							<input type="email" class="form-control" id="owner_email" name="owner_email" value="<?php echo $details['owner_email'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="owner_mobile"><?php echo _l('owner_mobile'); ?><span class="required">*</span></label>
							<input type="text" class="form-control" id="owner_mobile" name="owner_mobile" value="<?php echo $details['owner_mobile'] ?? ''; ?>" required>
						</div>

						<div class="form-group">
							<label for="status"><?php echo _l('status'); ?></label>
							<select class="form-control select2" data-toggle="select2" name="status" id="status">
								<?php if (($details['status'] ?? '')) { ?>
								<option value="1" selected><?php echo _l('enabled'); ?></option>
								<option value="0"><?php echo _l('disabled'); ?></option>
								<?php } else { ?>
								<option value="1"><?php echo _l('enabled'); ?></option>
								<option value="0" selected><?php echo _l('disabled'); ?></option>
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
