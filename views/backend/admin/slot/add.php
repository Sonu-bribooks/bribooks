<!-- start page title -->
<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo _l('add_new_slot'); ?></h4>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>

<div class="row justify-content-center">
	<div class="col-xl-7">
		<div class="card">
			<div class="card-body">
			  <div class="col-lg-12">
				<h4 class="mb-3 header-title"><?php echo _l('slot_add_form'); ?></h4>

				<form class="required-form" action="<?php echo site_url('admin/slots/add'); ?>" method="post" enctype="multipart/form-data">
					<div class="form-group">
						<label for="name"><?php echo _l('name'); ?><span class="required">*</span></label>
						<input type="text" class="form-control" id="name" name = "name" required>
					</div>

					<div class="form-group">
						<label for="type"><?php echo _l('type'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="type" id="type">
							<option value="daily"><?php echo _l('daily'); ?></option>
							<option value="weekend"><?php echo _l('weekend'); ?></option>
						</select>
					</div>

					<div class="form-group">
						<label for="slot"><?php echo _l('time'); ?></label>
						<select class="form-control select2" data-toggle="select2" name="slot" id="slot">
							<?php for ($i = 6; $i < 23; $i++) { ?>
							<option value="<?php echo $i; ?>:00"><?php echo $i; ?>:00</option>
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
