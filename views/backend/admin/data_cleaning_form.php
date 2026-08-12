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

				<form class="required-form" action="<?php echo $action ; ?>" method="post">

					<div class="form-group">
						<div class="row">
							<label class="col-sm-2 control-label" for="input-type"><?php echo _l('type'); ?><span class="required">*</span></label>
							<div class="col-sm-12">
								<select class="form-control select2 type" data-toggle="select2" name="type" required>
									<option value="">Please select type</option>
									<option value="school">Site</option>
									<option value="school">School</option>
									<option value="state">State</option>
									<option value="city">City</option>
								</select>
							</div>
						</div>
					</div>
					
					<div class="form-group">
						<label for="parent"><?php echo _l('id'); ?> <small>(Will be deleted)</small> <span class="required">*</span></label>
                        <input type="text" class="form-control" id="id" name="id" value="" required>
					</div>

					<div class="form-group">
						<label for="message"><?php echo _l('new_id'); ?> <small>(Will be retained)</small> <span class="required">*</span></label>
						<input type="text" class="form-control" id="new_id" name="new_id" value="" required>
					</div>

					<button type="button" class="btn btn-primary" id="subBtn" onclick="checkRequiredFields()"><?php echo _l('merge'); ?></button>
				</form>
			  </div>
			</div> <!-- end card body-->
		</div> <!-- end card -->
	</div><!-- end col-->
</div>


