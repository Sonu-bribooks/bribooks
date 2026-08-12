<div class="row ">
	<div class="col-xl-12">
		<div class="card">
			<div class="card-body">
				<h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?> </h4>
			</div>
		</div>
	</div>
</div>
<div class="row justify-content-center">
	<div class="col-xl-8">
		<div class="card">
			<div class="card-body">
				<form class="required-form" action="<?php echo $action ?? ''; ?>" method="post">
					<div>
						<div class="tab-content b-0 mb-0">
							<div class="row">
								<div class="col-12">
									<div class="form-group row mb-3">
										<label class="col-md-3 col-form-label" for="currency"><?php echo _l('currency'); ?> <span class="required">*</span></label>
										<div class="col-md-9">
											<select class="form-control select2" data-toggle="select2" id="currency" name="currency" required>
												<option value="">Select</option>
												<option <?php if (!empty($details['currency']) && $details['currency'] == 'inr') { echo 'selected'; } ?> value="inr">INR</option>
												<option <?php if (!empty($details['currency']) && $details['currency'] == 'usd') { echo 'selected'; } ?> value="usd">USD</option>
											</select>
										</div>
									</div>
									<div class="form-group row mb-3">
										<label class="col-md-3 col-form-label" for="page"><?php echo _l('page'); ?> <span class="required">*</span></label>
										<div class="col-md-9">
											<select class="form-control select2" data-toggle="select2" id="page" name="page" required>
												<option value="">Select</option>
												<option <?php if (!empty($details['page']) && $details['page'] == '1') { echo 'selected'; } ?> value="1">Multiple of 1</option>
												<option <?php if (!empty($details['page']) && $details['page'] == '2') { echo 'selected'; } ?> value="2">Multiple of 2</option>
												<option <?php if (!empty($details['page']) && $details['page'] == '4') { echo 'selected'; } ?> value="4">Multiple of 4</option>
												<option <?php if (!empty($details['page']) && $details['page'] == '8') { echo 'selected'; } ?> value="8">Multiple of 8</option>
											</select>
										</div>
									</div>
									<div class="form-group row mb-3">
										<label class="col-md-3 col-form-label" for="rate_per_page"><?php echo _l('rate_per_page'); ?> <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control" id="rate_per_page" name="rate_per_page" value="<?php echo $details['rate_per_page'] ?? ''; ?>">
										</div>
									</div>
									<div class="form-group row mb-3">
										<label class="col-md-3 col-form-label" for="rate_per_page"><?php echo _l('rate_per_page_b&w'); ?> <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control" id="rate_per_page_bw" name="rate_per_page_bw" value="<?php echo $details['rate_per_page_bw'] ?? ''; ?>">
										</div>
									</div>
									<div class="form-group row mb-3">
										<label class="col-md-3 col-form-label" for="binding_cost_per_book"><?php echo _l('binding_cost_per_book'); ?> <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control" id="binding_cost_per_book" name="binding_cost_per_book" value="<?php echo $details['binding_cost_per_book'] ?? ''; ?>" >
										</div>
									</div>
									<div class="form-group row mb-3">
										<label class="col-md-3 col-form-label" for="add_cover_pages_per_book"><?php echo _l('add_cover_pages_per_book'); ?> </label>
										<div class="col-md-9">
											<input type="text" class="form-control" id="add_cover_pages_per_book" name="add_cover_pages_per_book" value="<?php echo $details['add_cover_pages_per_book'] ?? '0'; ?>">
										</div>
									</div>
									<!-- <div class="form-group row mb-3">
										<label class="col-md-3 col-form-label" for="page_cost"><?php echo _l('page_cost'); ?> <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control" id="page_cost" name="page_cost" value="<?php echo $details['page_cost'] ?? ''; ?>">
										</div>
									</div>
									<div class="form-group row mb-3">
										<label class="col-md-3 col-form-label" for="book_cost"><?php echo _l('book_cost'); ?> <span class="required">*</span></label>
										<div class="col-md-9">
											<input type="text" class="form-control" id="book_cost" name="book_cost" value="<?php echo $details['book_cost'] ?? ''; ?>">
										</div>
									</div> -->
									<div class="form-group text-center">
										<button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
